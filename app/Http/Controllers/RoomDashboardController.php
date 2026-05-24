<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomDashboardController extends Controller
{
    public function index()
    {
        $availableRoomsCount = Room::where('status', 'available')->count();
        $checkinsToday = Reservation::whereDate('check_in', Carbon::today())
            ->where('status', 'pending')
            ->count();
        $checkoutsToday = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')
            ->count();
        $upcomingBookings = Reservation::whereDate('check_in', '>', Carbon::today())
            ->where('status', 'pending')
            ->orderBy('check_in', 'asc')
            ->limit(5)
            ->get();

        $rooms = Room::with('roomType')->orderBy('room_number')->get();

        return view('rooms.dashboard', compact(
            'availableRoomsCount', 'checkinsToday', 'checkoutsToday', 
            'upcomingBookings', 'rooms'
        ));
    }

    public function apiRoomsStatus()
    {
        $rooms = Room::with('roomType')->orderBy('room_number')->get();
        return response()->json([
            'rooms' => $rooms,
            'available_count' => $rooms->where('status', 'available')->count(),
            'checkins_today' => Reservation::whereDate('check_in', Carbon::today())->where('status', 'pending')->count(),
            'checkouts_today' => Reservation::whereDate('check_out', Carbon::today())->where('status', 'checked_in')->count(),
        ]);
    }
}