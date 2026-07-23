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

        $bookedRoomIds = Reservation::whereIn('status', Reservation::ACTIVE_STATUSES)
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
     * Occupancy Calendar — 7-day range view
     */
    public function occupancyCalendar(Request $request)
    {
        $start = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : Carbon::today();
        $end = $start->copy()->addDays(6);

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
     * AJAX: Cek ketersediaan kamar untuk drag-and-drop move
     */
    public function checkRoomAvailabilityForMove(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date',
            'exclude_reservation_id' => 'nullable|exists:reservations,id',
        ]);

        $room = Room::findOrFail($request->room_id);

        // Gunakan datetime asli dari reservasi agar back-to-back (check-out 12:00, check-in 14:00)
        // tidak salah dianggap bentrok — lihat #isAvailable yang pakai strict < dan >
        if ($request->exclude_reservation_id) {
            $reservation = Reservation::find($request->exclude_reservation_id);
            if ($reservation) {
                $checkIn = $reservation->check_in->format('Y-m-d H:i:s');
                $checkOut = $reservation->check_out->format('Y-m-d H:i:s');
            } else {
                $checkIn = $request->check_in;
                $checkOut = $request->check_out;
            }
        } else {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;
        }

        $isAvailable = $room->isAvailable($checkIn, $checkOut, $request->exclude_reservation_id);

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
            'room' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type_name' => $room->room_type_name ?? '',
            ],
        ]);
    }

    /**
     * AJAX: Proses pindah kamar via drag-and-drop
     */
    public function moveBooking(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'new_room_id' => 'required|exists:rooms,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $reservation = Reservation::findOrFail($request->reservation_id);
        $newRoom = Room::findOrFail($request->new_room_id);

        if (!in_array($reservation->status, Reservation::CHANGEABLE_STATUSES)) {
            return response()->json([
                'success' => false,
                'message' => 'Pindah kamar hanya bisa dilakukan untuk reservasi dengan status pending/menunggu pembayaran/check-in.',
            ], 422);
        }

        $checkIn = $reservation->check_in->format('Y-m-d H:i:s');
        $checkOut = $reservation->check_out->format('Y-m-d H:i:s');

        if (!$newRoom->isAvailable($checkIn, $checkOut, $reservation->id)) {
            return response()->json([
                'success' => false,
                'message' => "Kamar {$newRoom->room_number} tidak tersedia untuk periode tersebut.",
            ], 422);
        }

        $oldRoom = $reservation->room;
        $oldRoomNumber = $oldRoom->room_number;
        $newRoomNumber = $newRoom->room_number;
        $newTotalAmount = $newRoom->calculateTotalForRange($reservation->check_in, $reservation->check_out);

        $reason = $request->reason ?: 'Drag-drop dari occupancy calendar';
        $reservation->room_id = $newRoom->id;
        $reservation->total_amount = $newTotalAmount;
        $reservation->notes = ($reservation->notes ? $reservation->notes . "\n" : '') .
            '[' . now()->format('d/m/Y H:i') . '] Pindah kamar dari ' . $oldRoomNumber . ' ke ' . $newRoomNumber . ': ' . $reason;
        $reservation->save();

        if ($reservation->status === 'checked_in') {
            $oldRoom->update(['status' => 'cleaning']);
            $newRoom->update(['status' => 'occupied']);
        } else {
            $oldRoom->update(['status' => 'available']);
        }

        return response()->json([
            'success' => true,
            'message' => "Pindah kamar dari {$oldRoomNumber} ke {$newRoomNumber} berhasil.",
        ]);
    }

    /**
     * Quick stats untuk dashboard header
     */
    protected function getStats(Carbon $date): array
    {
        $today = Carbon::today();

        $totalActive = Room::whereNotIn('status', ['out_of_order'])->count();

        return [
            'available_now' => Room::where('status', 'available')->count(),
            'occupied_now' => Room::where('status', 'occupied')->count(),
            'checkins_today' => Reservation::whereDate('check_in', $today)
                ->where('status', 'pending')->count(),
            'checkouts_today' => Reservation::whereDate('check_out', $today)
                ->where('status', 'checked_in')->count(),
            'maintenance' => Room::where('status', 'maintenance')->count(),
            'dirty' => Room::where('status', 'cleaning')->count(),
            'out_of_order' => Room::where('status', 'out_of_order')->count(),
            'occupancy_pct' => 0,
        ];
    }
}
