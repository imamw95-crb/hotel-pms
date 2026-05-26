<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Night Audit Report
     */
    public function nightAudit(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        // Check-in: jam 12:00 siang — tamu yang check-in hari ini
        $checkinsToday = Reservation::whereDate('check_in', $date)
            ->where('status', 'checked_in')
            ->with(['guest', 'room'])
            ->get();

        // Check-out: jam 12:00 siang — tamu yang check-out hari ini
        // (masih in-house sampai jam 12:00 siang hari ini)
        $checkoutsToday = Reservation::whereDate('check_out', $date)
            ->where('status', 'checked_out')
            ->with(['guest', 'room'])
            ->get();

        $revenueToday = Transaction::whereDate('created_at', $date)->sum('amount');

        // Pendapatan per metode pembayaran (summary)
        $revenueByMethod = Transaction::whereDate('created_at', $date)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // Detail transaksi per metode (dengan nama tamu & status)
        $transactionsByMethod = Transaction::whereDate('created_at', $date)
            ->with(['reservation.guest', 'reservation.room'])
            ->orderBy('payment_method')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('payment_method');

        // In-house: checked_in ATAU checked_out hari ini (check-out jam 12:00 siang, masih in-house sampai siang)
        $inHouseGuests = Reservation::where(function ($q) use ($date) {
                $q->where('status', 'checked_in')
                    ->orWhere(function ($sub) use ($date) {
                        $sub->where('status', 'checked_out')
                            ->whereDate('check_out', $date);
                    });
            })
            ->with(['guest', 'room'])
            ->orderBy('check_out', 'asc')
            ->get();

        $newBookings = Reservation::whereDate('created_at', $date)
            ->with(['guest', 'room'])
            ->get();

        return view('reports.night-audit', compact(
            'date', 'totalRooms', 'occupiedRooms', 'availableRooms', 'maintenanceRooms',
            'checkinsToday', 'checkoutsToday', 'revenueToday', 'revenueByMethod', 'transactionsByMethod', 'inHouseGuests', 'newBookings'
        ));
    }

    /**
     * Guest List Report dengan range tanggal
     */
    public function guestList(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        $query = Reservation::with(['guest', 'room'])
            ->whereBetween('check_in', [$startDate, $endDate]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function ($q) use ($search) {
                      $q->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        $guests = $query->orderBy('check_in', 'desc')->paginate(25);

        return view('reports.guest-list', compact(
            'guests', 'startDate', 'endDate', 'status', 'search'
        ));
    }

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
            
            // Occupancy: kamar terisi jika check_in <= hari ini DAN check_out >= hari ini
            // Check-out jam 12:00 siang = kamar masih terisi sampai siang hari itu
            // Jadi tamu yang check-out hari ini MASIH terhitung occupied
            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->whereIn('status', ['checked_in', 'checked_out'])
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