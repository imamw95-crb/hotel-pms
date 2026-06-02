<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomRackController extends Controller
{
    protected $availability;

    public function __construct(AvailabilityService $availability)
    {
        $this->availability = $availability;
    }

    /**
     * Room Rack View — Timeline booking + Grid kamar
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::today();
        $days = (int) $request->input('days', 14);
        $days = max(7, min(90, $days));

        $rack = $this->availability->getRoomRack($startDate, $days);
        $forecast = $this->availability->getForecast(30);
        $stats = $this->getStats($startDate);

        // ─── Data untuk Grid Kamar (dari rooms dashboard) ───
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));
        $statusFilter = $request->input('status_filter', 'all');
        $roomTypeFilter = $request->input('room_type', 'all');

        $availableRoomsCount = 0;
        $checkinsToday = Reservation::whereDate('check_in', '>=', $dateFrom)
            ->whereDate('check_in', '<=', $dateTo)->where('status', 'pending')->count();
        $checkoutsToday = Reservation::whereDate('check_out', '>=', $dateFrom)
            ->whereDate('check_out', '<=', $dateTo)->where('status', 'checked_in')->count();

        $dueOutRoomIds = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')->pluck('room_id')->toArray();

        $roomTypes = Room::select('room_type_name')->distinct()->orderBy('room_type_name')
            ->pluck('room_type_name')->filter()->values();

        $bookedRoomIds = Reservation::whereIn('status', ['pending', 'checked_in'])
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where('check_in', '<', $dateTo)->where('check_out', '>', $dateFrom);
            })->pluck('room_id')->unique()->toArray();

        $roomsQuery = Room::with(['roomType', 'reservations' => function ($q) use ($dateFrom, $dateTo) {
            $q->where('status', 'checked_in')
                ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                    $sub->where('status', 'pending')
                        ->whereDate('check_in', '>=', $dateFrom)
                        ->whereDate('check_in', '<=', $dateTo);
                });
        }, 'reservations.guest']);

        if ($roomTypeFilter !== 'all') {
            $roomsQuery->where('room_type_name', $roomTypeFilter);
        }
        if ($statusFilter === 'due_out') {
            $roomsQuery->where('status', 'occupied')->whereIn('id', $dueOutRoomIds);
        } elseif ($statusFilter !== 'all') {
            $roomsQuery->where('status', $statusFilter);
        }

        $rooms = $roomsQuery->orderBy('room_number')->get();

        if ($statusFilter === 'available' || $statusFilter === 'all') {
            $availableRoomsCount = $rooms->whereNotIn('id', $bookedRoomIds)->count();
        } else {
            $availableRoomsCount = $rooms->where('status', 'available')->count();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('room-rack.partials.rack-table', compact('rack', 'startDate', 'days'))->render(),
            ]);
        }

        return view('room-rack.index', compact(
            'rack', 'forecast', 'stats', 'startDate', 'days',
            'rooms', 'availableRoomsCount', 'checkinsToday', 'checkoutsToday',
            'dateFrom', 'dateTo', 'statusFilter', 'roomTypeFilter', 'roomTypes', 'dueOutRoomIds'
        ));
    }

    /**
     * Cek ketersediaan kamar (AJAX)
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'room_type' => 'nullable|string',
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $rooms = $this->availability->getAvailableRooms($checkIn, $checkOut, $request->room_type);

        return response()->json([
            'success' => true,
            'rooms' => $rooms,
            'total' => $rooms->count(),
        ]);
    }

    /**
     * Occupancy Calendar (AJAX)
     */
    public function occupancyCalendar(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $data = $this->availability->getOccupancyCalendar($start, $end);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'view' => view('room-rack.partials.occupancy-calendar', array_merge($data, ['start' => $start, 'end' => $end]))->render(),
            ]);
        }

        return view('room-rack.occupancy', array_merge($data, ['start' => $start, 'end' => $end]));
    }

    /**
     * Forecast data (AJAX)
     */
    public function forecast(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $forecast = $this->availability->getForecast($days);

        return response()->json([
            'success' => true,
            'forecast' => $forecast,
        ]);
    }

    /**
     * Quick stats untuk dashboard header
     */
    protected function getStats(Carbon $date): array
    {
        $today = Carbon::today();

        return [
            'available_now' => Room::where('status', 'available')->count(),
            'occupied_now' => Room::where('status', 'occupied')->count(),
            'checkins_today' => Reservation::whereDate('check_in', $today)
                ->where('status', 'pending')->count(),
            'checkouts_today' => Reservation::whereDate('check_out', $today)
                ->where('status', 'checked_in')->count(),
            'maintenance' => Room::where('status', 'maintenance')->count(),
            'dirty' => Room::where('status', 'cleaning')->count(),
            'occupancy_pct' => 0,
        ];
    }
}
