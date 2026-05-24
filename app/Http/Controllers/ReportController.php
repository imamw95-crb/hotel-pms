<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function occupancy(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $dates = [];
        $occupancyData = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $totalRooms = Room::count();
        
        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $dates[] = $current->format('d M');
            
            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->where('status', 'checked_in')
                ->count();
            
            $occupancyData[] = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;
            
            $current->addDay();
        }
        
        return view('reports.occupancy', compact('startDate', 'endDate', 'dates', 'occupancyData', 'totalRooms'));
    }

    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $transactions = Transaction::with('reservation.room')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalRevenue = $transactions->sum('amount');
        $byMethod = $transactions->groupBy('payment_method')->map->sum('amount');
        
        return view('reports.revenue', compact('startDate', 'endDate', 'transactions', 'totalRevenue', 'byMethod'));
    }

    public function reservations(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $reservations = Reservation::with(['room', 'guest', 'createdBy'])
            ->whereBetween('check_in', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('check_in', 'desc')
            ->get();
        
        return view('reports.reservations', compact('startDate', 'endDate', 'reservations'));
    }
}