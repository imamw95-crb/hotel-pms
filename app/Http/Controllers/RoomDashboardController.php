<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Default tanggal: hari ini
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $availableRoomsCount = Room::where('status', 'available')->count();
        // Check-in/check-out jam 12:00 siang
        $checkinsToday = Reservation::whereDate('check_in', '>=', $dateFrom)
            ->whereDate('check_in', '<=', $dateTo)
            ->where('status', 'pending')
            ->count();
        $checkoutsToday = Reservation::whereDate('check_out', '>=', $dateFrom)
            ->whereDate('check_out', '<=', $dateTo)
            ->where('status', 'checked_in')
            ->count();
        $upcomingBookings = Reservation::whereDate('check_in', '>', Carbon::today())
            ->where('status', 'pending')
            ->orderBy('check_in', 'asc')
            ->limit(5)
            ->get();

        $rooms = Room::with(['roomType', 'reservations' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'checked_in')
                    ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                        $sub->where('status', 'pending')
                            ->whereDate('check_in', '>=', $dateFrom)
                            ->whereDate('check_in', '<=', $dateTo);
                    });
            }, 'reservations.guest'])
            ->orderBy('room_number')
            ->get();

        return view('rooms.dashboard', compact(
            'availableRoomsCount', 'checkinsToday', 'checkoutsToday',
            'upcomingBookings', 'rooms', 'dateFrom', 'dateTo'
        ));
    }

    public function apiRoomsStatus(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        $rooms = Room::with(['roomType', 'reservations' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'checked_in')
                    ->orWhere(function ($sub) use ($dateFrom, $dateTo) {
                        $sub->where('status', 'pending')
                            ->whereDate('check_in', '>=', $dateFrom)
                            ->whereDate('check_in', '<=', $dateTo);
                    });
            }, 'reservations.guest'])
            ->orderBy('room_number')
            ->get();

        return response()->json([
            'rooms' => $rooms,
            'available_count' => $rooms->where('status', 'available')->count(),
            'checkins_today' => Reservation::whereDate('check_in', '>=', $dateFrom)->whereDate('check_in', '<=', $dateTo)->where('status', 'pending')->count(),
            'checkouts_today' => Reservation::whereDate('check_out', '>=', $dateFrom)->whereDate('check_out', '<=', $dateTo)->where('status', 'checked_in')->count(),
        ]);
    }
}