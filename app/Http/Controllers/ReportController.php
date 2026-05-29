<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Room;
use App\Models\RestoTransaction;
use App\Models\ServiceCharge;
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

        // Pendapatan Resto hari ini
        $restoRevenueToday = RestoTransaction::whereDate('created_at', $date)->sum('total_amount');
        $restoTransactions = RestoTransaction::with(['guest'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();
        $restoRevenueByMethod = RestoTransaction::whereDate('created_at', $date)
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // Service Charge hari ini
        $serviceChargeRevenueToday = ServiceCharge::whereDate('charge_date', $date)->sum('total_amount');
        $serviceCharges = ServiceCharge::with(['guest', 'reservation.room', 'createdBy'])
            ->whereDate('charge_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();
        $serviceChargeByMethod = ServiceCharge::whereDate('charge_date', $date)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

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
            'checkinsToday', 'checkoutsToday', 'revenueToday', 'revenueByMethod', 'transactionsByMethod', 'inHouseGuests', 'newBookings',
            'restoRevenueToday', 'restoTransactions', 'restoRevenueByMethod',
            'serviceChargeRevenueToday', 'serviceCharges', 'serviceChargeByMethod'
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

    /**
     * Export Night Audit to CSV
     */
    public function exportNightAudit(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $filename = 'night-audit-' . $date . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($date) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // Header info
            fputcsv($file, ['NIGHT AUDIT REPORT']);
            fputcsv($file, ['Tanggal', $date]);
            fputcsv($file, []);

            // Room status
            fputcsv($file, ['ROOM STATUS']);
            fputcsv($file, ['Total Kamar', Room::count()]);
            fputcsv($file, ['Occupied', Room::where('status', 'occupied')->count()]);
            fputcsv($file, ['Available', Room::where('status', 'available')->count()]);
            fputcsv($file, ['Maintenance', Room::where('status', 'maintenance')->count()]);
            fputcsv($file, []);

            // Revenue
            $revenueToday = Transaction::whereDate('created_at', $date)->sum('amount');
            $restoRevenue = RestoTransaction::whereDate('created_at', $date)->sum('total_amount');
            fputcsv($file, ['REVENUE']);
            fputcsv($file, ['Pendapatan Kamar', $revenueToday]);
            fputcsv($file, ['Pendapatan Resto', $restoRevenue]);
            fputcsv($file, ['Total Pendapatan', $revenueToday + $restoRevenue]);
            fputcsv($file, []);

            // Check-ins
            fputcsv($file, ['CHECK-IN HARI INI']);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'Kamar', 'Check-in', 'Check-out']);
            $checkins = Reservation::whereDate('check_in', $date)->where('status', 'checked_in')->with(['guest', 'room'])->get();
            foreach ($checkins as $r) {
                fputcsv($file, [$r->reservation_number, $r->guest->guest_name ?? '-', $r->room->room_number ?? '-', $r->check_in->format('d/m/Y H:i'), $r->check_out->format('d/m/Y H:i')]);
            }
            fputcsv($file, []);

            // Check-outs
            fputcsv($file, ['CHECK-OUT HARI INI']);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'Kamar', 'Check-in', 'Check-out']);
            $checkouts = Reservation::whereDate('check_out', $date)->where('status', 'checked_out')->with(['guest', 'room'])->get();
            foreach ($checkouts as $r) {
                fputcsv($file, [$r->reservation_number, $r->guest->guest_name ?? '-', $r->room->room_number ?? '-', $r->check_in->format('d/m/Y H:i'), $r->check_out->format('d/m/Y H:i')]);
            }
            fputcsv($file, []);

            // Resto transactions
            fputcsv($file, ['PENDAPATAN RESTO/F&B']);
            fputcsv($file, ['No. Transaksi', 'Waktu', 'Tamu', 'Meja', 'Item', 'Metode', 'Nominal']);
            $restoTxns = RestoTransaction::with(['guest'])->whereDate('created_at', $date)->get();
            foreach ($restoTxns as $txn) {
                $items = collect($txn->items)->map(fn($i) => $i['name'] . ' x' . $i['qty'])->implode(', ');
                fputcsv($file, [$txn->transaction_number, $txn->created_at->format('H:i'), $txn->guest->guest_name ?? 'Walk-in', $txn->table_number ?? '-', $items, $txn->payment_method, $txn->total_amount]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Guest List to CSV
     */
    public function exportGuestList(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        $filename = 'guest-list-' . $startDate . '-to-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $query = Reservation::with(['guest', 'room'])
            ->whereBetween('check_in', [$startDate, $endDate]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                    ->orWhereHas('guest', function ($q) use ($search) {
                        $q->where('guest_name', 'like', "%{$search}%");
                    });
            });
        }

        $guests = $query->orderBy('check_in', 'desc')->get();

        $callback = function () use ($guests, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['GUEST LIST REPORT']);
            fputcsv($file, ['Periode', $startDate . ' s/d ' . $endDate]);
            fputcsv($file, []);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'No. Identitas', 'Kamar', 'Check-in', 'Check-out', 'Status', 'Total Amount', 'Paid Amount']);
            foreach ($guests as $g) {
                fputcsv($file, [
                    $g->reservation_number,
                    $g->guest->guest_name ?? '-',
                    $g->guest->id_number ?? '-',
                    $g->room->room_number ?? '-',
                    $g->check_in->format('d/m/Y H:i'),
                    $g->check_out->format('d/m/Y H:i'),
                    $g->status,
                    $g->total_amount,
                    $g->paid_amount,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Occupancy to CSV
     */
    public function exportOccupancy(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $filename = 'occupancy-' . $startDate . '-to-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $totalRooms = Room::count();
        $dates = [];
        $occupancyData = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $dates[] = $current->format('d M');
            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->count();
            $occupancyData[] = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;
            $current->addDay();
        }

        $callback = function () use ($dates, $occupancyData, $totalRooms) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['OCCUPANCY REPORT']);
            fputcsv($file, ['Total Kamar', $totalRooms]);
            fputcsv($file, []);
            fputcsv($file, ['Tanggal', 'Okupansi (%)']);
            foreach ($dates as $i => $date) {
                fputcsv($file, [$date, $occupancyData[$i] . '%']);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Revenue to CSV
     */
    public function exportRevenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $filename = 'revenue-' . $startDate . '-to-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $transactions = Transaction::with('reservation.room')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function () use ($transactions, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['REVENUE REPORT']);
            fputcsv($file, ['Periode', $startDate . ' s/d ' . $endDate]);
            fputcsv($file, []);
            fputcsv($file, ['No. Transaksi', 'Tanggal', 'Tipe', 'Metode', 'Kamar', 'Nominal']);
            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->transaction_number,
                    $t->created_at->format('d/m/Y H:i'),
                    $t->type,
                    $t->payment_method,
                    $t->reservation->room->room_number ?? '-',
                    $t->amount,
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', '', '', $transactions->sum('amount')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Reservations to CSV
     */
    public function exportReservations(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $filename = 'reservations-' . $startDate . '-to-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $reservations = Reservation::with(['room', 'guest', 'createdBy'])
            ->whereBetween('check_in', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('check_in', 'desc')
            ->get();

        $callback = function () use ($reservations, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['RESERVATIONS REPORT']);
            fputcsv($file, ['Periode', $startDate . ' s/d ' . $endDate]);
            fputcsv($file, []);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'Kamar', 'Check-in', 'Check-out', 'Status', 'Total', 'Paid', 'Dibuat Oleh']);
            foreach ($reservations as $r) {
                fputcsv($file, [
                    $r->reservation_number,
                    $r->guest->guest_name ?? '-',
                    $r->room->room_number ?? '-',
                    $r->check_in->format('d/m/Y H:i'),
                    $r->check_out->format('d/m/Y H:i'),
                    $r->status,
                    $r->total_amount,
                    $r->paid_amount,
                    $r->createdBy->name ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}