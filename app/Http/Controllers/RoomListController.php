<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomListController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // ── Sedang Menginap: check_in <= today AND check_out >= today AND status = checked_in ──
        $currentlyStaying = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->where('check_in', '<=', $today->copy()->endOfDay())
            ->where('check_out', '>=', $today->copy()->startOfDay())
            ->orderBy('check_out', 'asc')
            ->get();

        // ── Sudah Checkout: status = checked_out ──
        $checkedOut = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_out')
            ->orderBy('check_out', 'desc')
            ->limit(50)
            ->get();

        // ── Akan Datang: check_in > today AND status = pending ──
        $upcoming = Reservation::with(['guest', 'room'])
            ->where('status', 'pending')
            ->where('check_in', '>', $today->copy()->endOfDay())
            ->orderBy('check_in', 'asc')
            ->get();

        // Summary counts
        $stats = [
            'staying' => $currentlyStaying->count(),
            'checked_out' => $checkedOut->count(),
            'upcoming' => $upcoming->count(),
        ];

        return view('room-list.index', compact('currentlyStaying', 'checkedOut', 'upcoming', 'stats'));
    }

    /**
     * Print report: hari ini check-in, check-out (due out), dan akan datang.
     */
    public function print(Request $request)
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        // ── Check-in Hari Ini ──
        $checkInToday = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereDate('check_in', $today)
            ->orWhere(function ($q) use ($today) {
                $q->where('status', 'pending')
                    ->whereDate('check_in', $today);
            })
            ->get()
            ->sortBy('check_in');

        // ── Due Out Hari Ini (check_out = hari ini, status checked_in) ──
        $dueOutToday = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->whereDate('check_out', $today)
            ->orderBy('check_out', 'asc')
            ->get();

        // ── Akan Datang (check_in > hari ini) ──
        $upcoming = Reservation::with(['guest', 'room'])
            ->where('status', 'pending')
            ->where('check_in', '>', $today->copy()->endOfDay())
            ->orderBy('check_in', 'asc')
            ->get();

        // ── Sedang Menginap (tamu in-house yang bukan due out hari ini) ──
        $currentlyStaying = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->where('check_in', '<=', $today->copy()->endOfDay())
            ->where('check_out', '>', $today->copy()->startOfDay())
            ->whereDate('check_out', '>', $today)
            ->orderBy('room_id', 'asc')
            ->get();

        $hotel = HotelSetting::first();

        return view('room-list.print', compact(
            'checkInToday', 'dueOutToday', 'upcoming', 'currentlyStaying', 'today', 'hotel'
        ));
    }
}
