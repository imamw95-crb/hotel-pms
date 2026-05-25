<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Single dashboard view that adapts to user role
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            return $this->ownerData();
        }

        if ($user->isAdmin()) {
            return $this->adminData();
        }

        return $this->frontOfficeData();
    }

    /**
     * Owner dashboard data
     */
    private function ownerData()
    {
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        $todayRevenue = Transaction::whereDate('created_at', Carbon::today())->sum('amount');
        $monthRevenue = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $checkinsToday = Reservation::whereDate('check_in', Carbon::today())
            ->where('status', 'pending')->count();
        $checkoutsToday = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')->count();

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $last7Days['labels'][] = $date->format('D');

            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->where('status', 'checked_in')
                ->count();
            $last7Days['occupancy'][] = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;

            $last7Days['revenue'][] = Transaction::whereDate('created_at', $date)->sum('amount');
        }

        $recentReservations = Reservation::with(['room', 'guest'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalRooms', 'occupiedRooms', 'occupancyRate',
            'todayRevenue', 'monthRevenue', 'checkinsToday', 'checkoutsToday',
            'last7Days', 'recentReservations'
        ));
    }

    /**
     * Admin dashboard data
     */
    private function adminData()
    {
        $totalUsers = User::count();
        $totalRooms = Room::count();
        $totalReservations = Reservation::count();
        $totalRevenue = Transaction::sum('amount');

        return view('dashboard.index', compact(
            'totalUsers', 'totalRooms', 'totalReservations', 'totalRevenue'
        ));
    }

    /**
     * Front Office dashboard data
     */
    private function frontOfficeData()
    {
        $availableRooms = Room::where('status', 'available')->count();
        $todayCheckins = Reservation::whereDate('check_in', Carbon::today())
            ->where('status', 'pending')->count();
        $todayCheckouts = Reservation::whereDate('check_out', Carbon::today())
            ->where('status', 'checked_in')->count();

        return view('dashboard.index', compact(
            'availableRooms', 'todayCheckins', 'todayCheckouts'
        ));
    }
}
