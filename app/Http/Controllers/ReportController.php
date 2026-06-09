<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\HotelSetting;
use App\Models\Reservation;
use App\Models\RestoTransaction;
use App\Models\Room;
use App\Models\ServiceCharge;
use App\Models\Transaction;
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

        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();
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

        // Other Revenue hari ini
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

        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();

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
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
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
            ->whereBetween('check_in', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
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
        $filename = 'night-audit-'.$date.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($date) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            // Header info
            fputcsv($file, ['NIGHT AUDIT REPORT']);
            fputcsv($file, ['Tanggal', $date]);
            fputcsv($file, []);

            // Room status
            fputcsv($file, ['ROOM STATUS']);
            fputcsv($file, ['Total Kamar', Room::whereNotIn('status', ['out_of_order'])->count()]);
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
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'Kamar', 'Sarapan', 'Check-in', 'Check-out']);
            $checkins = Reservation::whereDate('check_in', $date)->where('status', 'checked_in')->with(['guest', 'room'])->get();
            foreach ($checkins as $r) {
                $sarapan = $r->include_breakfast ? 'Ya' : 'Tidak';
                fputcsv($file, [$r->reservation_number, $r->guest->guest_name ?? '-', $r->room->room_number ?? '-', $sarapan, $r->check_in->format('d/m/Y H:i'), $r->check_out->format('d/m/Y H:i')]);
            }
            fputcsv($file, []);

            // Check-outs
            fputcsv($file, ['CHECK-OUT HARI INI']);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'Kamar', 'Sarapan', 'Check-in', 'Check-out']);
            $checkouts = Reservation::whereDate('check_out', $date)->where('status', 'checked_out')->with(['guest', 'room'])->get();
            foreach ($checkouts as $r) {
                $sarapan = $r->include_breakfast ? 'Ya' : 'Tidak';
                fputcsv($file, [$r->reservation_number, $r->guest->guest_name ?? '-', $r->room->room_number ?? '-', $sarapan, $r->check_in->format('d/m/Y H:i'), $r->check_out->format('d/m/Y H:i')]);
            }
            fputcsv($file, []);

            // Resto transactions
            fputcsv($file, ['PENDAPATAN RESTO/F&B']);
            fputcsv($file, ['No. Transaksi', 'Waktu', 'Tamu', 'Meja', 'Item', 'Metode', 'Nominal']);
            $restoTxns = RestoTransaction::with(['guest'])->whereDate('created_at', $date)->get();
            foreach ($restoTxns as $txn) {
                $items = collect($txn->items)->map(fn ($i) => $i['name'].' x'.$i['qty'])->implode(', ');
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

        $filename = 'guest-list-'.$startDate.'-to-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
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
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['GUEST LIST REPORT']);
            fputcsv($file, ['Periode', $startDate.' s/d '.$endDate]);
            fputcsv($file, []);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'No. Identitas', 'Kamar', 'Check-in', 'Check-out', 'Sarapan', 'Status', 'Total Amount', 'Paid Amount']);
            foreach ($guests as $g) {
                $sarapan = $g->include_breakfast ? 'Ya' : 'Tidak';
                fputcsv($file, [
                    $g->reservation_number,
                    $g->guest->guest_name ?? '-',
                    $g->guest->id_number ?? '-',
                    $g->room->room_number ?? '-',
                    $g->check_in->format('d/m/Y H:i'),
                    $g->check_out->format('d/m/Y H:i'),
                    $sarapan,
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
        $filename = 'occupancy-'.$startDate.'-to-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();
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
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['OCCUPANCY REPORT']);
            fputcsv($file, ['Total Kamar', $totalRooms]);
            fputcsv($file, []);
            fputcsv($file, ['Tanggal', 'Okupansi (%)']);
            foreach ($dates as $i => $date) {
                fputcsv($file, [$date, $occupancyData[$i].'%']);
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
        $filename = 'revenue-'.$startDate.'-to-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $transactions = Transaction::with('reservation.room')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function () use ($transactions, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['REVENUE REPORT']);
            fputcsv($file, ['Periode', $startDate.' s/d '.$endDate]);
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
        $filename = 'reservations-'.$startDate.'-to-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $reservations = Reservation::with(['room', 'guest', 'createdBy'])
            ->whereBetween('check_in', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('check_in', 'desc')
            ->get();

        $callback = function () use ($reservations, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['RESERVATIONS REPORT']);
            fputcsv($file, ['Periode', $startDate.' s/d '.$endDate]);
            fputcsv($file, []);
            fputcsv($file, ['No. Reservasi', 'Nama Tamu', 'Kamar', 'Check-in', 'Check-out', 'Sarapan', 'Status', 'Total', 'Paid', 'Dibuat Oleh']);
            foreach ($reservations as $r) {
                $sarapan = $r->include_breakfast ? 'Ya' : 'Tidak';
                fputcsv($file, [
                    $r->reservation_number,
                    $r->guest->guest_name ?? '-',
                    $r->room->room_number ?? '-',
                    $r->check_in->format('d/m/Y H:i'),
                    $r->check_out->format('d/m/Y H:i'),
                    $sarapan,
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

    /**
     * Group Booking Report — daftar booking grup beserta total revenue per grup
     */
    public function groupReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Ambil semua grup booking yang memiliki booking_group_id
        $groups = Reservation::whereNotNull('booking_group_id')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['guest', 'room', 'transactions'])
            ->get()
            ->groupBy('booking_group_id')
            ->map(function ($reservations, $groupId) {
                $first = $reservations->first();
                $roomNumbers = $reservations->pluck('room.room_number')->implode(', ');
                $totalAmount = $reservations->sum('total_amount');
                $paidAmount = $reservations->sum('paid_amount');
                $remainingPayment = $totalAmount - $paidAmount;
                $totalTransactions = $reservations->flatMap->transactions->sum('amount');

                return (object) [
                    'booking_group_id' => $groupId,
                    'guest_name' => $first->guest->guest_name ?? '-',
                    'check_in' => $first->check_in,
                    'check_out' => $first->check_out,
                    'rooms' => $reservations,
                    'room_numbers' => $roomNumbers,
                    'total_rooms' => $reservations->count(),
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'remaining_payment' => $remainingPayment,
                    'total_transactions' => $totalTransactions,
                    'created_at' => $first->created_at,
                    'created_by' => $first->createdBy->name ?? '-',
                ];
            })->sortByDesc('created_at');

        $grandTotalAmount = $groups->sum('total_amount');
        $grandTotalPaid = $groups->sum('paid_amount');
        $grandTotalRemaining = $groups->sum('remaining_payment');
        $totalGroups = $groups->count();

        return view('reports.group', compact(
            'groups', 'startDate', 'endDate',
            'grandTotalAmount', 'grandTotalPaid', 'grandTotalRemaining', 'totalGroups'
        ));
    }

    /**
     * Export Group Report to CSV
     */
    public function exportGroupReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $filename = 'group-report-'.$startDate.'-to-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $groups = Reservation::whereNotNull('booking_group_id')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['guest', 'room', 'transactions'])
            ->get()
            ->groupBy('booking_group_id')
            ->map(function ($reservations) {
                $first = $reservations->first();
                $roomNumbers = $reservations->pluck('room.room_number')->implode(', ');
                $totalAmount = $reservations->sum('total_amount');
                $paidAmount = $reservations->sum('paid_amount');

                return (object) [
                    'guest_name' => $first->guest->guest_name ?? '-',
                    'check_in' => $first->check_in,
                    'check_out' => $first->check_out,
                    'room_numbers' => $roomNumbers,
                    'total_rooms' => $reservations->count(),
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                ];
            })->sortByDesc('check_in');

        $callback = function () use ($groups, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['GROUP BOOKING REPORT']);
            fputcsv($file, ['Periode', $startDate.' s/d '.$endDate]);
            fputcsv($file, []);
            fputcsv($file, ['Nama Tamu', 'Kamar', 'Jumlah Kamar', 'Check-in', 'Check-out', 'Total', 'Terbayar', 'Sisa']);
            foreach ($groups as $g) {
                fputcsv($file, [
                    $g->guest_name,
                    $g->room_numbers,
                    $g->total_rooms,
                    $g->check_in->format('d/m/Y'),
                    $g->check_out->format('d/m/Y'),
                    $g->total_amount,
                    $g->paid_amount,
                    $g->total_amount - $g->paid_amount,
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', $groups->sum('total_rooms'), '', '', $groups->sum('total_amount'), $groups->sum('paid_amount'), $groups->sum('total_amount') - $groups->sum('paid_amount')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Laporan Pengeluaran (Expenses Report)
     */
    public function expenses(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $byMethod = $expenses->groupBy('payment_method')->map->sum('amount');
        $byDescription = $expenses->groupBy('description')->map->sum('amount')->sortDesc();

        return view('reports.expenses', compact(
            'startDate', 'endDate', 'expenses', 'totalExpenses', 'byMethod', 'byDescription'
        ));
    }

    /**
     * Print Laporan Pengeluaran — clean layout tanpa sidebar/menu
     */
    public function printExpenses(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $byMethod = $expenses->groupBy('payment_method')->map->sum('amount');
        $byDescription = $expenses->groupBy('description')->map->sum('amount')->sortDesc();
        $hotel = HotelSetting::first();

        return view('reports.print-expenses', compact(
            'startDate', 'endDate', 'expenses', 'totalExpenses', 'byMethod', 'byDescription', 'hotel'
        ));
    }

    /**
     * Export Expenses Report to CSV
     */
    public function exportExpenses(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $filename = 'expenses-'.$startDate.'-to-'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $callback = function () use ($expenses, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['LAPORAN PENGELUARAN']);
            fputcsv($file, ['Periode', $startDate.' s/d '.$endDate]);
            fputcsv($file, []);
            fputcsv($file, ['No. Expense', 'Tanggal', 'Deskripsi', 'Metode', 'Jumlah', 'Catatan', 'Dibuat Oleh']);
            foreach ($expenses as $e) {
                fputcsv($file, [
                    $e->expense_number,
                    $e->expense_date->format('d/m/Y'),
                    $e->description,
                    $e->payment_method,
                    $e->amount,
                    $e->notes ?? '',
                    $e->createdBy?->name ?? '-',
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', '', $expenses->sum('amount'), '', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Laporan Bulanan Hotel — rekap semua pendapatan & kepatuhan
     */
    public function complianceReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $month = Carbon::parse($startDate)->format('Y-m');
        $prevMonth = Carbon::parse($startDate)->subMonth()->format('Y-m');
        $prevStart = Carbon::parse($startDate)->subMonth()->format('Y-m-d');
        $prevEnd = Carbon::parse($startDate)->subMonth()->endOfMonth()->format('Y-m-d');

        // ─── Statistik Kamar ──────────────────────────────────────────
        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();
        $avgOccupancy = 0;
        $occupiedCount = 0;
        $daysInMonth = Carbon::parse($startDate)->daysInMonth;
        $current = Carbon::parse($startDate);
        $occupancyDays = [];
        while ($current <= Carbon::parse($endDate)) {
            $date = $current->format('Y-m-d');
            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->count();
            $occupancyDays[$date] = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0;
            $occupiedCount += $occupied;
            $current->addDay();
        }
        $avgOccupancy = $totalRooms > 0 && $daysInMonth > 0
            ? round(($occupiedCount / ($totalRooms * $daysInMonth)) * 100, 2)
            : 0;

        // ─── Pendapatan Kamar ─────────────────────────────────────────
        $roomRevenue = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')
            ->sum('amount');
        $roomRevenuePrev = Transaction::whereBetween('created_at', [$prevStart.' 00:00:00', $prevEnd.' 23:59:59'])
            ->where('type', '!=', 'refund')
            ->sum('amount');

        // ─── Pendapatan Resto ─────────────────────────────────────────
        $restoRevenue = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->sum('total_amount');
        $restoRevenuePrev = RestoTransaction::whereBetween('created_at', [$prevStart.' 00:00:00', $prevEnd.' 23:59:59'])->sum('total_amount');
        $restoCount = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->count();

        // ─── Other Revenue ──────────────────────────────────────────
        $scRevenue = ServiceCharge::whereBetween('charge_date', [$startDate, $endDate])->sum('total_amount');
        $scRevenuePrev = ServiceCharge::whereBetween('charge_date', [$prevStart, $prevEnd])->sum('total_amount');

        // ─── Pengeluaran ────────────────────────────────────────────
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $totalExpensesPrev = Expense::whereBetween('expense_date', [$prevStart, $prevEnd])->sum('amount');
        $expensesByDesc = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('description, SUM(amount) as total')
            ->groupBy('description')
            ->orderByDesc('total')
            ->get();

        // ─── Grand Totals ───────────────────────────────────────────
        $grandRevenue = $roomRevenue + $restoRevenue + $scRevenue;
        $grandRevenuePrev = $roomRevenuePrev + $restoRevenuePrev + $scRevenuePrev;
        $netRevenue = $grandRevenue - $totalExpenses;
        $netRevenuePrev = $grandRevenuePrev - $totalExpensesPrev;

        // ─── Revenue by Payment Method ──────────────────────────────
        $revenueByMethod = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // ─── Cash vs Transfer Breakdown ────────────────────────────
        $cashRevenue = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')
            ->where('payment_method', 'cash')
            ->sum('amount');
        $transferRevenue = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')
            ->where('payment_method', 'bank_transfer')
            ->sum('amount');
        $otherRevenue = $roomRevenue - $cashRevenue - $transferRevenue;

        $cashResto = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('payment_method', 'cash')
            ->sum('total_amount');
        $transferResto = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('payment_method', 'bank_transfer')
            ->sum('total_amount');
        $otherResto = $restoRevenue - $cashResto - $transferResto;

        $grandCash = $cashRevenue + $cashResto;
        $grandTransfer = $transferRevenue + $transferResto;
        $grandOther = $otherRevenue + $otherResto;

        // ─── Reservasi Stats ────────────────────────────────────────
        $totalReservations = Reservation::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->count();
        $checkins = Reservation::whereBetween('check_in', [$startDate, $endDate])
            ->where('status', 'checked_in')
            ->count();
        $checkouts = Reservation::whereBetween('check_out', [$startDate, $endDate])
            ->where('status', 'checked_out')
            ->count();
        $cancelled = Reservation::where('status', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                    ->orWhereBetween('updated_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
            })
            ->count();

        // ─── OTA Bookings ──────────────────────────────────────────
        $otaBySource = Reservation::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->whereNotNull('ota_source')
            ->selectRaw('ota_source, COUNT(*) as total_bookings, SUM(total_amount) as total_revenue')
            ->groupBy('ota_source')
            ->get();
        $otaBookings = $otaBySource->sum('total_bookings');
        $otaRevenue = $otaBySource->sum('total_revenue');

        // ─── Pajak (estimasi PPN 11%) ──────────────────────────────
        $ppnEstimate = round($grandRevenue * 0.11 / 1.11, 2);
        $ppnRoom = round($roomRevenue * 0.11 / 1.11, 2);
        $ppnResto = round($restoRevenue * 0.11 / 1.11, 2);

        // ─── Growth ────────────────────────────────────────────────
        $revenueGrowth = $grandRevenuePrev > 0
            ? round((($grandRevenue - $grandRevenuePrev) / $grandRevenuePrev) * 100, 2)
            : 0;
        $expenseGrowth = $totalExpensesPrev > 0
            ? round((($totalExpenses - $totalExpensesPrev) / $totalExpensesPrev) * 100, 2)
            : 0;

        // ─── Transaksi Resto Detail ────────────────────────────────
        $restoByMethod = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // ─── Revenue per Day (Chart) ───────────────────────────────
        $dailyRevenue = [];
        $current = Carbon::parse($startDate);
        while ($current <= Carbon::parse($endDate)) {
            $date = $current->format('Y-m-d');
            $dayRoom = Transaction::whereDate('created_at', $date)
                ->where('type', '!=', 'refund')
                ->sum('amount');
            $dayResto = RestoTransaction::whereDate('created_at', $date)->sum('total_amount');
            $dailyRevenue[] = [
                'date' => $current->format('d M'),
                'room' => (float) $dayRoom,
                'resto' => (float) $dayResto,
            ];
            $current->addDay();
        }

        // ─── Guest Compliance ──────────────────────────────────────
        $totalGuests = Reservation::whereBetween('check_in', [$startDate, $endDate])
            ->whereHas('guest')
            ->count();
        $guestsWithId = Reservation::whereBetween('check_in', [$startDate, $endDate])
            ->whereHas('guest', fn ($q) => $q->whereNotNull('id_number')->where('id_number', '!=', ''))
            ->count();
        $guestCompliancePct = $totalGuests > 0 ? round(($guestsWithId / $totalGuests) * 100, 1) : 0;

        return view('reports.compliance', compact(
            'month', 'startDate', 'endDate', 'prevMonth',
            'totalRooms', 'avgOccupancy', 'occupancyDays',
            'roomRevenue', 'roomRevenuePrev',
            'restoRevenue', 'restoRevenuePrev', 'restoCount',
            'scRevenue', 'scRevenuePrev',
            'totalExpenses', 'totalExpensesPrev', 'expensesByDesc',
            'grandRevenue', 'grandRevenuePrev',
            'netRevenue', 'netRevenuePrev',
            'revenueByMethod', 'restoByMethod',
            'cashRevenue', 'transferRevenue', 'otherRevenue',
            'cashResto', 'transferResto', 'otherResto',
            'grandCash', 'grandTransfer', 'grandOther',
            'totalReservations', 'checkins', 'checkouts', 'cancelled',
            'otaBookings', 'otaRevenue', 'otaBySource',
            'ppnEstimate', 'ppnRoom', 'ppnResto',
            'revenueGrowth', 'expenseGrowth',
            'dailyRevenue',
            'totalGuests', 'guestsWithId', 'guestCompliancePct',
        ));
    }

    /**
     * Export Laporan Bulanan Hotel ke CSV
     */
    public function exportComplianceReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $monthLabel = Carbon::parse($startDate)->format('Y-m');
        $filename = 'laporan-bulanan-'.$monthLabel.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();

        $callback = function () use ($startDate, $endDate, $totalRooms) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['LAPORAN BULANAN HOTEL']);
            fputcsv($file, ['Periode', Carbon::parse($startDate)->format('F Y')]);
            fputcsv($file, []);

            // ─── Ringkasan ──────────────────────────────────────────
            fputcsv($file, ['A. RINGKASAN PENDAPATAN']);
            $roomRev = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->where('type', '!=', 'refund')->sum('amount');
            $restoRev = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->sum('total_amount');
            $scRev = ServiceCharge::whereBetween('charge_date', [$startDate, $endDate])->sum('total_amount');
            $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
            $grandTotal = $roomRev + $restoRev + $scRev;
            fputcsv($file, ['Pendapatan Kamar', $roomRev]);
            fputcsv($file, ['Pendapatan Resto/F&B', $restoRev]);
            fputcsv($file, ['Other Revenue', $scRev]);
            fputcsv($file, ['Total Pendapatan', $grandTotal]);
            fputcsv($file, ['Total Pengeluaran', $expenses]);
            fputcsv($file, ['Pendapatan Bersih', $grandTotal - $expenses]);
            fputcsv($file, []);

            // ─── Okupansi ──────────────────────────────────────────
            fputcsv($file, ['B. OKUPANSI']);
            fputcsv($file, ['Total Kamar', $totalRooms]);
            $occupiedCount = 0;
            $daysInMonth = Carbon::parse($startDate)->daysInMonth;
            $current = Carbon::parse($startDate);
            while ($current <= Carbon::parse($endDate)) {
                $occupied = Reservation::whereDate('check_in', '<=', $current->format('Y-m-d'))
                    ->whereDate('check_out', '>=', $current->format('Y-m-d'))
                    ->whereIn('status', ['checked_in', 'checked_out'])
                    ->count();
                $occupiedCount += $occupied;
                $current->addDay();
            }
            $avgOcc = $totalRooms > 0 && $daysInMonth > 0
                ? round(($occupiedCount / ($totalRooms * $daysInMonth)) * 100, 2)
                : 0;
            fputcsv($file, ['Rata-rata Okupansi', $avgOcc.'%']);
            fputcsv($file, []);

            // ─── Reservasi ─────────────────────────────────────────
            fputcsv($file, ['C. RESERVASI']);
            $totalRes = Reservation::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->count();
            $checkins = Reservation::whereBetween('check_in', [$startDate, $endDate])->where('status', 'checked_in')->count();
            $checkouts = Reservation::whereBetween('check_out', [$startDate, $endDate])->where('status', 'checked_out')->count();
            $cancelled = Reservation::where('status', 'cancelled')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                        ->orWhereBetween('updated_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                })
                ->count();
            $otaBySrc = Reservation::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->whereNotNull('ota_source')
                ->selectRaw('ota_source, COUNT(*) as total_bookings, SUM(total_amount) as total_revenue')
                ->groupBy('ota_source')
                ->get();
            fputcsv($file, ['Total Reservasi', $totalRes]);
            fputcsv($file, ['Check-in', $checkins]);
            fputcsv($file, ['Check-out', $checkouts]);
            fputcsv($file, ['Dibatalkan', $cancelled]);
            fputcsv($file, ['Total Booking OTA', $otaBySrc->sum('total_bookings')]);
            foreach ($otaBySrc as $ota) {
                fputcsv($file, ['  - '.$ota->ota_source, $ota->total_bookings.' booking', 'Rp '.number_format($ota->total_revenue, 0, ',', '.')]);
            }
            fputcsv($file, []);

            // ─── Metode Pembayaran ─────────────────────────────────
            fputcsv($file, ['D. PENDAPATAN PER METODE PEMBAYARAN']);
            $byMethod = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->where('type', '!=', 'refund')
                ->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method');
            foreach ($byMethod as $method => $amount) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $method)), $amount]);
            }
            fputcsv($file, []);

            // ─── Estimasi Pajak ────────────────────────────────────
            fputcsv($file, ['E. ESTIMASI PAJAK (PPN 11%)']);
            $ppnTotal = round($grandTotal * 0.11 / 1.11, 2);
            fputcsv($file, ['Estimasi PPN', $ppnTotal]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print Laporan Bulanan Hotel — clean layout tanpa sidebar
     */
    public function printComplianceReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $month = Carbon::parse($startDate)->format('Y-m');
        $prevStart = Carbon::parse($startDate)->subMonth()->format('Y-m-d');
        $prevEnd = Carbon::parse($startDate)->subMonth()->endOfMonth()->format('Y-m-d');

        // ─── Statistik Kamar ──────────────────────────────────────────
        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();
        $avgOccupancy = 0;
        $occupiedCount = 0;
        $daysInMonth = Carbon::parse($startDate)->daysInMonth;
        $current = Carbon::parse($startDate);
        $occupancyDays = [];
        while ($current <= Carbon::parse($endDate)) {
            $date = $current->format('Y-m-d');
            $occupied = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->count();
            $occupancyDays[$date] = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0;
            $occupiedCount += $occupied;
            $current->addDay();
        }
        $avgOccupancy = $totalRooms > 0 && $daysInMonth > 0
            ? round(($occupiedCount / ($totalRooms * $daysInMonth)) * 100, 2)
            : 0;

        // ─── Pendapatan ──────────────────────────────────────────────
        $roomRevenue = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')->sum('amount');
        $restoRevenue = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->sum('total_amount');
        $scRevenue = ServiceCharge::whereBetween('charge_date', [$startDate, $endDate])->sum('total_amount');
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $grandRevenue = $roomRevenue + $restoRevenue + $scRevenue;
        $netRevenue = $grandRevenue - $totalExpenses;

        // ─── Metode Pembayaran ──────────────────────────────────────
        $revenueByMethod = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $restoByMethod = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // ─── Cash vs Transfer Breakdown ────────────────────────────
        $cashRevenue = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')->where('payment_method', 'cash')->sum('amount');
        $transferRevenue = Transaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('type', '!=', 'refund')->where('payment_method', 'bank_transfer')->sum('amount');
        $otherRevenue = $roomRevenue - $cashRevenue - $transferRevenue;

        $cashResto = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('payment_method', 'cash')->sum('total_amount');
        $transferResto = RestoTransaction::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('payment_method', 'bank_transfer')->sum('total_amount');
        $otherResto = $restoRevenue - $cashResto - $transferResto;

        $grandCash = $cashRevenue + $cashResto;
        $grandTransfer = $transferRevenue + $transferResto;
        $grandOther = $otherRevenue + $otherResto;

        // ─── Pengeluaran per Kategori ───────────────────────────────
        $expensesByDesc = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('description, SUM(amount) as total')
            ->groupBy('description')
            ->orderByDesc('total')
            ->get();

        // ─── Reservasi ──────────────────────────────────────────────
        $totalReservations = Reservation::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->count();
        $checkins = Reservation::whereBetween('check_in', [$startDate, $endDate])->where('status', 'checked_in')->count();
        $checkouts = Reservation::whereBetween('check_out', [$startDate, $endDate])->where('status', 'checked_out')->count();
        $cancelled = Reservation::where('status', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                    ->orWhereBetween('updated_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
            })
            ->count();
        $otaBySource = Reservation::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->whereNotNull('ota_source')
            ->selectRaw('ota_source, COUNT(*) as total_bookings, SUM(total_amount) as total_revenue')
            ->groupBy('ota_source')
            ->get();
        $otaBookings = $otaBySource->sum('total_bookings');
        $otaRevenue = $otaBySource->sum('total_revenue');

        // ─── Pajak ──────────────────────────────────────────────────
        $ppnEstimate = round($grandRevenue * 0.11 / 1.11, 2);

        // ─── Growth ─────────────────────────────────────────────────
        $roomRevenuePrev = Transaction::whereBetween('created_at', [$prevStart.' 00:00:00', $prevEnd.' 23:59:59'])
            ->where('type', '!=', 'refund')->sum('amount');
        $restoRevenuePrev = RestoTransaction::whereBetween('created_at', [$prevStart.' 00:00:00', $prevEnd.' 23:59:59'])->sum('total_amount');
        $scRevenuePrev = ServiceCharge::whereBetween('charge_date', [$prevStart, $prevEnd])->sum('total_amount');
        $totalExpensesPrev = Expense::whereBetween('expense_date', [$prevStart, $prevEnd])->sum('amount');
        $grandRevenuePrev = $roomRevenuePrev + $restoRevenuePrev + $scRevenuePrev;
        $revenueGrowth = $grandRevenuePrev > 0 ? round((($grandRevenue - $grandRevenuePrev) / $grandRevenuePrev) * 100, 2) : 0;
        $expenseGrowth = $totalExpensesPrev > 0 ? round((($totalExpenses - $totalExpensesPrev) / $totalExpensesPrev) * 100, 2) : 0;

        // ─── Guest Compliance ──────────────────────────────────────
        $totalGuests = Reservation::whereBetween('check_in', [$startDate, $endDate])->whereHas('guest')->count();
        $guestsWithId = Reservation::whereBetween('check_in', [$startDate, $endDate])
            ->whereHas('guest', fn ($q) => $q->whereNotNull('id_number')->where('id_number', '!=', ''))
            ->count();
        $guestCompliancePct = $totalGuests > 0 ? round(($guestsWithId / $totalGuests) * 100, 1) : 0;

        $hotel = HotelSetting::first();

        return view('reports.print-compliance', compact(
            'month', 'startDate', 'endDate', 'hotel',
            'totalRooms', 'avgOccupancy', 'occupancyDays',
            'roomRevenue', 'restoRevenue', 'scRevenue',
            'totalExpenses', 'expensesByDesc',
            'grandRevenue', 'netRevenue',
            'revenueByMethod', 'restoByMethod',
            'cashRevenue', 'transferRevenue', 'otherRevenue',
            'cashResto', 'transferResto', 'otherResto',
            'grandCash', 'grandTransfer', 'grandOther',
            'totalReservations', 'checkins', 'checkouts', 'cancelled',
            'otaBookings', 'otaRevenue', 'otaBySource',
            'ppnEstimate',
            'revenueGrowth', 'expenseGrowth',
            'totalGuests', 'guestsWithId', 'guestCompliancePct',
            'roomRevenuePrev', 'restoRevenuePrev', 'scRevenuePrev',
            'totalExpensesPrev', 'grandRevenuePrev',
        ));
    }
}
