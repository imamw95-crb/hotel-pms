<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\HotelSetting;
use App\Models\NightAuditLog;
use App\Models\Reservation;
use App\Models\RestoTransaction;
use App\Models\Room;
use App\Models\ServiceCharge;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NightAuditController extends Controller
{
    /**
     * Display night audit page (preview, draft, or locked)
     */
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::yesterday()->format('Y-m-d'));
        $auditLog = NightAuditLog::where('audit_date', $date)->first();

        $mode = 'preview';
        $snapshot = null;

        if ($auditLog) {
            $mode = $auditLog->isLocked() ? 'locked' : 'draft';
            $snapshot = $auditLog;
        }

        $history = NightAuditLog::where('status', 'locked')
            ->with(['lockedBy', 'createdBy'])
            ->orderBy('audit_date', 'desc')
            ->limit(30)
            ->get();

        // For preview mode, compute live data
        $data = [];
        if ($mode === 'preview') {
            $data = $this->buildSnapshotData($date);
        } elseif ($snapshot && $snapshot->snapshot_data) {
            $data = $snapshot->snapshot_data;
            // Normalize roomTypeSummary from array (JSON) → Collection of objects for consistent view rendering
            if (isset($data['roomTypeSummary']) && is_array($data['roomTypeSummary'])) {
                $data['roomTypeSummary'] = collect($data['roomTypeSummary'])->map(fn ($item) => (object) $item);
            }
        }

        return view('reports.night-audit-v2.index', compact(
            'date', 'mode', 'snapshot', 'auditLog', 'history', 'data'
        ));
    }

    /**
     * Get preview data (real-time) — AJAX endpoint
     */
    public function preview(Request $request)
    {
        $date = $request->get('date', Carbon::yesterday()->format('Y-m-d'));
        $data = $this->buildSnapshotData($date);

        if ($request->expectsJson()) {
            $html = view('reports.night-audit-v2.partials.report-content', $data)->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'summary' => [
                    'total_rooms' => $data['totalRooms'],
                    'occupied_rooms' => $data['occupiedRooms'],
                    'total_revenue' => $data['totalRevenue'],
                ],
            ]);
        }

        return $data;
    }

    /**
     * Save snapshot as draft
     */
    public function saveDraft(Request $request)
    {
        $date = $request->get('date', Carbon::yesterday()->format('Y-m-d'));
        $data = $this->buildSnapshotData($date);

        NightAuditLog::updateOrCreate(
            ['audit_date' => $date],
            [
                'status' => 'draft',
                'total_rooms' => $data['totalRooms'],
                'occupied_rooms' => $data['occupiedRooms'],
                'available_rooms' => $data['availableRooms'],
                'maintenance_rooms' => $data['maintenanceRooms'],
                'occupancy_rate' => $data['occupancyRate'],
                'room_revenue' => $data['revenueToday'],
                'resto_revenue' => $data['restoRevenueToday'],
                'sc_revenue' => $data['serviceChargeRevenueToday'],
                'total_revenue' => $data['totalRevenue'],
                'checkins_count' => $data['checkinsToday']->count(),
                'checkouts_count' => $data['checkoutsToday']->count(),
                'in_house_count' => $data['inHouseGuests']->count(),
                'new_bookings_count' => ($data['otaBookings']->count() ?? 0) + ($data['webBookings']->count() ?? 0) + ($data['directBookings']->count() ?? 0),
                'snapshot_data' => $data,
                'draft_notes' => $request->notes,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->route('reports.night-audit-v2.index', ['date' => $date])
            ->with('success', 'Draft Night Audit berhasil disimpan.');
    }

    /**
     * Lock & finalize audit
     */
    public function lock(Request $request)
    {
        $date = $request->get('date', Carbon::yesterday()->format('Y-m-d'));
        $data = $this->buildSnapshotData($date);

        NightAuditLog::updateOrCreate(
            ['audit_date' => $date],
            [
                'status' => 'locked',
                'total_rooms' => $data['totalRooms'],
                'occupied_rooms' => $data['occupiedRooms'],
                'available_rooms' => $data['availableRooms'],
                'maintenance_rooms' => $data['maintenanceRooms'],
                'occupancy_rate' => $data['occupancyRate'],
                'room_revenue' => $data['revenueToday'],
                'resto_revenue' => $data['restoRevenueToday'],
                'sc_revenue' => $data['serviceChargeRevenueToday'],
                'total_revenue' => $data['totalRevenue'],
                'checkins_count' => $data['checkinsToday']->count(),
                'checkouts_count' => $data['checkoutsToday']->count(),
                'in_house_count' => $data['inHouseGuests']->count(),
                'new_bookings_count' => ($data['otaBookings']->count() ?? 0) + ($data['webBookings']->count() ?? 0) + ($data['directBookings']->count() ?? 0),
                'snapshot_data' => $data,
                'draft_notes' => $request->notes,
                'locked_by' => auth()->id(),
                'locked_at' => now(),
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->route('reports.night-audit-v2.index', ['date' => $date])
            ->with('success', 'Night Audit berhasil di-lock! Data report sudah tidak berubah.');
    }

    /**
     * Delete draft (or unlock & recreate)
     */
    public function deleteDraft(Request $request)
    {
        if (! auth()->user()->isOwner() && ! auth()->user()->isAdmin() && ! auth()->user()->isUserManager()) {
            abort(403, 'Unauthorized — hanya Owner, Admin, dan Manager yang bisa Unlock & Buat Baru.');
        }

        $date = $request->get('date');
        NightAuditLog::where('audit_date', $date)->delete();

        return redirect()->route('reports.night-audit-v2.index', ['date' => $date])
            ->with('success', 'Draft/Lock berhasil dihapus. Silakan buat baru.');
    }

    /**
     * Show locked report detail by ID
     */
    public function show($id)
    {
        $auditLog = NightAuditLog::with(['lockedBy', 'createdBy'])->findOrFail($id);
        if (! $auditLog->isLocked()) {
            return redirect()->route('reports.night-audit-v2.index')
                ->with('error', 'Report ini belum di-lock.');
        }

        $data = $auditLog->snapshot_data ?? [];
        // Normalize roomTypeSummary from array (JSON) → Collection of objects
        if (isset($data['roomTypeSummary']) && is_array($data['roomTypeSummary'])) {
            $data['roomTypeSummary'] = collect($data['roomTypeSummary'])->map(fn ($item) => (object) $item);
        }
        $date = $auditLog->audit_date->format('Y-m-d');

        return view('reports.night-audit-v2.locked', compact('auditLog', 'data', 'date'));
    }

    /**
     * Export locked report to CSV
     */
    public function export($id)
    {
        $auditLog = NightAuditLog::findOrFail($id);
        $data = $auditLog->snapshot_data ?? [];
        $date = $auditLog->audit_date->format('Y-m-d');
        $filename = 'night-audit-locked-'.$date.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($data, $date) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['NIGHT AUDIT REPORT (LOCKED)']);
            fputcsv($file, ['Tanggal', $date]);
            fputcsv($file, []);

            fputcsv($file, ['ROOM STATUS']);
            fputcsv($file, ['Total Kamar', $data['totalRooms'] ?? 0]);
            fputcsv($file, ['Occupied', $data['occupiedRooms'] ?? 0]);
            fputcsv($file, ['Available', $data['availableRooms'] ?? 0]);
            fputcsv($file, ['Maintenance', $data['maintenanceRooms'] ?? 0]);
            fputcsv($file, []);

            fputcsv($file, ['REVENUE']);
            fputcsv($file, ['Pendapatan Kamar', $data['revenueToday'] ?? 0]);
            fputcsv($file, ['  - Cash', $data['cashRevenueToday'] ?? 0]);
            fputcsv($file, ['  - OTA', $data['otaRevenueToday'] ?? 0]);
            fputcsv($file, ['  - Web / Direct', $data['webRevenueToday'] ?? 0]);
            fputcsv($file, ['Pendapatan Resto', $data['restoRevenueToday'] ?? 0]);
            fputcsv($file, ['Other Revenue', $data['serviceChargeRevenueToday'] ?? 0]);
            fputcsv($file, ['Deposit Key Card', $data['depositRevenueToday'] ?? 0]);
            fputcsv($file, ['Total', $data['totalRevenue'] ?? 0]);
            fputcsv($file, []);

            // Detail per metode pembayaran
            $txByMethod = $data['transactionsByMethod'] ?? [];
            foreach ($txByMethod as $method => $txns) {
                fputcsv($file, [strtoupper(str_replace('_', ' ', $method))]);
                fputcsv($file, ['No.', 'No. Transaksi', 'Tamu', 'Kamar', 'Tipe/Item', 'Sumber', 'Status', 'Nominal']);
                $i = 1;
                foreach ($txns as $txn) {
                    $source = $txn['source'] ?? '-';
                    if (! empty($txn['ota_source'])) {
                        $source .= ' ('.$txn['ota_source'].')';
                    }
                    $typeLabel = $txn['type'] ?? '-';
                    $notes = $txn['notes'] ?? '';
                    $itemInfo = ucwords(str_replace('_', ' ', $typeLabel));
                    if (! empty($notes)) {
                        $itemInfo .= ' - '.$notes;
                    }
                    fputcsv($file, [$i++, $txn['transaction_number'] ?? '-', $txn['guest_name'] ?? '-', $txn['room_number'] ?? '-', $itemInfo, $source, $txn['status'] ?? '-', $txn['amount'] ?? 0]);
                }
                fputcsv($file, []);
            }

            // ── Expenses ──
            $expensesList = $data['expensesList'] ?? [];
            $expensesByMethod = $data['expensesByMethod'] ?? [];
            if (count($expensesList) > 0) {
                fputcsv($file, ['PENGELUARAN (EXPENSES)']);
                fputcsv($file, ['Total Pengeluaran', $data['expensesToday'] ?? 0]);
                fputcsv($file, []);

                foreach ($expensesByMethod as $method => $total) {
                    fputcsv($file, [strtoupper(str_replace('_', ' ', $method))]);
                    fputcsv($file, ['No.', 'No. Expense', 'Deskripsi', 'Keterangan', 'Nominal']);
                    $i = 1;
                    foreach ($expensesList as $e) {
                        if (($e['payment_method'] ?? '') === $method) {
                            fputcsv($file, [$i++, $e['expense_number'] ?? '-', $e['description'] ?? '-', $e['notes'] ?? '-', $e['amount'] ?? 0]);
                        }
                    }
                    fputcsv($file, []);
                }
            }

            // ── Deposits ──
            $depositList = $data['depositList'] ?? [];
            $depositByMethod = $data['depositByMethod'] ?? [];
            if (count($depositList) > 0) {
                fputcsv($file, ['DEPOSIT KEY CARD']);
                fputcsv($file, ['Total Deposit Key Card', $data['depositRevenueToday'] ?? 0]);
                fputcsv($file, []);

                foreach ($depositByMethod as $method => $total) {
                    fputcsv($file, [strtoupper(str_replace('_', ' ', $method))]);
                    fputcsv($file, ['No.', 'No. Receipt', 'Tamu', 'Kamar', 'Kartu', 'Per Kartu', 'Nominal']);
                    $i = 1;
                    foreach ($depositList as $d) {
                        if (($d['payment_method'] ?? '') === $method) {
                            fputcsv($file, [$i++, $d['receipt_number'] ?? '-', $d['guest_name'] ?? '-', $d['room_number'] ?? '-', $d['number_of_cards'] ?? 0, $d['nominal_per_card'] ?? 0, $d['total_amount'] ?? 0]);
                        }
                    }
                    fputcsv($file, []);
                }
            }

            // ── Cash Flow ──
            fputcsv($file, ['RINGKASAN KAS (CASH FLOW)']);
            fputcsv($file, ['Total Pemasukan Tunai', ($data['cashRevenue'] ?? 0) + ($data['cashDeposits'] ?? 0)]);
            fputcsv($file, ['  - Deposits (Key Card)', $data['cashDeposits'] ?? 0]);
            fputcsv($file, ['Total Pengeluaran Tunai', $data['cashExpenses'] ?? 0]);
            fputcsv($file, ['Sisa Kas (Cash Balance)', $data['cashFlowBalance'] ?? 0]);
            fputcsv($file, []);

            fputcsv($file, ['CHECK-IN HARI INI']);
            fputcsv($file, ['No.', 'Reservasi', 'Tamu', 'Kamar', 'Sarapan', 'Check-out']);
            foreach ($data['checkinsToday'] ?? [] as $i => $r) {
                $sarapan = ! empty($r['include_breakfast']) ? 'Ya' : 'Tidak';
                fputcsv($file, [$i + 1, $r['reservation_number'] ?? '-', $r['guest_name'] ?? '-', $r['room_number'] ?? '-', $sarapan, $r['check_out'] ?? '-']);
            }
            fputcsv($file, []);

            fputcsv($file, ['CHECK-OUT HARI INI']);
            fputcsv($file, ['No.', 'Reservasi', 'Tamu', 'Kamar', 'Sarapan', 'Check-in']);
            foreach ($data['checkoutsToday'] ?? [] as $i => $r) {
                $sarapan = ! empty($r['include_breakfast']) ? 'Ya' : 'Tidak';
                fputcsv($file, [$i + 1, $r['reservation_number'] ?? '-', $r['guest_name'] ?? '-', $r['room_number'] ?? '-', $sarapan, $r['check_in'] ?? '-']);
            }
            fputcsv($file, []);

            fputcsv($file, ['IN-HOUSE GUESTS']);
            fputcsv($file, ['No.', 'Reservasi', 'Tamu', 'Kamar', 'Check-in', 'Check-out', 'Malam', 'Sarapan']);
            foreach ($data['inHouseGuests'] ?? [] as $i => $r) {
                $sarapan = ! empty($r['include_breakfast']) ? 'Ya' : 'Tidak';
                fputcsv($file, [$i + 1, $r['reservation_number'] ?? '-', $r['guest_name'] ?? '-', $r['room_number'] ?? '-', $r['check_in'] ?? '-', $r['check_out'] ?? '-', $r['total_nights'] ?? '-', $sarapan]);
            }
            fputcsv($file, []);

            // ── OTA Bookings ──
            $otaList = $data['otaBookings'] ?? [];
            fputcsv($file, ['OTA BOOKINGS ('.count($otaList).')']);
            if (count($otaList) > 0) {
                fputcsv($file, ['No.', 'Reservasi', 'Tamu', 'Kamar', 'Check-in', 'Check-out', 'Nominal', 'OTA', 'Status', 'Sarapan']);
                foreach ($otaList as $i => $r) {
                    $sarapan = ! empty($r['include_breakfast']) ? 'Ya' : 'Tidak';
                    fputcsv($file, [$i + 1, $r['reservation_number'] ?? '-', $r['guest_name'] ?? '-', $r['room_number'] ?? '-', $r['check_in'] ?? '-', $r['check_out'] ?? '-', $r['total_amount'] ?? 0, $r['ota_source'] ?? '-', $r['status'] ?? '-', $sarapan]);
                }
            }
            fputcsv($file, []);

            // ── Web Bookings ──
            $webList = $data['webBookings'] ?? [];
            fputcsv($file, ['WEB BOOKINGS ('.count($webList).')']);
            if (count($webList) > 0) {
                fputcsv($file, ['No.', 'Reservasi', 'Tamu', 'Kamar', 'Check-in', 'Check-out', 'Nominal', 'Pembayaran', 'Status', 'Sarapan']);
                foreach ($webList as $i => $r) {
                    $sarapan = ! empty($r['include_breakfast']) ? 'Ya' : 'Tidak';
                    fputcsv($file, [$i + 1, $r['reservation_number'] ?? '-', $r['guest_name'] ?? '-', $r['room_number'] ?? '-', $r['check_in'] ?? '-', $r['check_out'] ?? '-', $r['total_amount'] ?? 0, $r['payment_method'] ?? '-', $r['status'] ?? '-', $sarapan]);
                }
            }
            fputcsv($file, []);

            // ── Direct Bookings ──
            $directList = $data['directBookings'] ?? [];
            fputcsv($file, ['DIRECT BOOKINGS ('.count($directList).')']);
            if (count($directList) > 0) {
                fputcsv($file, ['No.', 'Reservasi', 'Tamu', 'Kamar', 'Check-in', 'Check-out', 'Nominal', 'Status', 'Sarapan']);
                foreach ($directList as $i => $r) {
                    $sarapan = ! empty($r['include_breakfast']) ? 'Ya' : 'Tidak';
                    fputcsv($file, [$i + 1, $r['reservation_number'] ?? '-', $r['guest_name'] ?? '-', $r['room_number'] ?? '-', $r['check_in'] ?? '-', $r['check_out'] ?? '-', $r['total_amount'] ?? 0, $r['status'] ?? '-', $sarapan]);
                }
            }
            fputcsv($file, []);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get business date range for a given calendar date.
     * Business day runs from cutoff_time today to cutoff_time next day.
     * Example: date=2026-07-22, cutoff=06:00 → range [2026-07-22 06:00, 2026-07-23 05:59:59]
     */
    private function getBusinessDateRange(string $date): array
    {
        $cutoff = HotelSetting::get()->cutoff_time ?? '06:00';
        $start = Carbon::parse($date)->format('Y-m-d') . ' ' . $cutoff . ':00';
        $end = Carbon::parse($date)->addDay()->format('Y-m-d') . ' ' . $cutoff . ':00';

        return [$start, $end];
    }

    /**
     * Build full snapshot data array from database (real-time)
     */
    public function buildSnapshotData(string $date): array
    {
        $totalRooms = Room::whereNotIn('status', ['out_of_order'])->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0;

        // ─── Business Date Range ───────────────────────────────────
        [$bizStart, $bizEnd] = $this->getBusinessDateRange($date);

        // Check-ins today (business date)
        $checkinsToday = Reservation::where('check_in', '>=', $bizStart)
            ->where('check_in', '<', $bizEnd)
            ->where('status', 'checked_in')
            ->with(['guest', 'room'])
            ->get()
            ->map(fn ($r) => [
                'reservation_number' => $r->reservation_number,
                'guest_name' => $r->guest->guest_name ?? '-',
                'room_number' => $r->room->room_number ?? '-',
                'room_type' => $r->room->room_type_name ?? '-',
                'check_in' => $r->check_in->format('d/m/Y H:i'),
                'check_out' => $r->check_out->format('d/m/Y H:i'),
                'include_breakfast' => $r->include_breakfast,
            ]);

        // Check-outs today (business date)
        $checkoutsToday = Reservation::where('check_out', '>=', $bizStart)
            ->where('check_out', '<', $bizEnd)
            ->where('status', 'checked_out')
            ->with(['guest', 'room'])
            ->get()
            ->map(fn ($r) => [
                'reservation_number' => $r->reservation_number,
                'guest_name' => $r->guest->guest_name ?? '-',
                'room_number' => $r->room->room_number ?? '-',
                'room_type' => $r->room->room_type_name ?? '-',
                'check_in' => $r->check_in->format('d/m/Y H:i'),
                'check_out' => $r->check_out->format('d/m/Y H:i'),
                'include_breakfast' => $r->include_breakfast,
            ]);

        // Revenue
        $revenueToday = Transaction::whereDate('created_at', $date)->sum('amount');
        $restoRevenueToday = RestoTransaction::whereDate('created_at', $date)->sum('total_amount');
        $serviceChargeRevenueToday = ServiceCharge::whereDate('charge_date', $date)->sum('total_amount');
        $totalRevenue = $revenueToday + $restoRevenueToday + $serviceChargeRevenueToday;

        // ─── OTA payment method list ───────────────────────────────
        $otaPaymentMethods = ['ota_tiket_com', 'ota_traveloka', 'tiket.com', 'traveloka.com', 'ota_payment'];

        // Revenue by method with details
        $transactions = Transaction::whereDate('created_at', $date)
            ->with(['reservation.guest', 'reservation.room'])
            ->orderBy('payment_method')
            ->orderBy('created_at', 'desc')
            ->get();

        $transactionsByMethod = $transactions->groupBy('payment_method')->map(function ($txns) use ($otaPaymentMethods) {
            return $txns->map(function ($t) use ($otaPaymentMethods) {
                $isOta = ($t->reservation && $t->reservation->ota_source && $t->reservation->ota_source !== 'website')
                    || in_array($t->payment_method, $otaPaymentMethods);

                return [
                    'transaction_number' => $t->transaction_number,
                    'guest_name' => $t->reservation?->guest?->guest_name ?? '-',
                    'room_number' => $t->reservation?->room?->room_number ?? '-',
                    'room_type' => $t->reservation?->room?->room_type_name ?? '-',
                    'type' => $t->type,
                    'notes' => $t->notes,
                    'status' => $t->reservation?->status ?? '-',
                    'amount' => $t->amount,
                    'source' => $t->payment_method === 'cash' ? 'Cash' : ($isOta ? 'OTA' : 'Web'),
                    'ota_source' => $t->reservation?->ota_source,
                ];
            });
        });

        $revenueByMethod = Transaction::whereDate('created_at', $date)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // ─── Revenue by Source Category (Cash, OTA, Web) ──────────

        $cashRevenueToday = $transactions->where('payment_method', 'cash')->sum('amount');

        $otaRevenueToday = $transactions->filter(function ($t) use ($otaPaymentMethods) {
            return ($t->reservation && $t->reservation->ota_source && $t->reservation->ota_source !== 'website')
                || in_array($t->payment_method, $otaPaymentMethods);
        })->sum('amount');

        $webRevenueToday = $revenueToday - $cashRevenueToday - $otaRevenueToday;
        if ($webRevenueToday < 0) {
            $webRevenueToday = 0;
        }

        // Resto transactions
        $restoTransactions = RestoTransaction::with(['guest'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($t) => [
                'transaction_number' => $t->transaction_number,
                'created_at' => $t->created_at->format('H:i'),
                'guest_name' => $t->guest->guest_name ?? 'Walk-in',
                'table_number' => $t->table_number ?? '-',
                'items' => $t->items ?? [],
                'payment_method' => $t->payment_method,
                'total_amount' => $t->total_amount,
            ]);

        $restoRevenueByMethod = RestoTransaction::whereDate('created_at', $date)
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // Other revenues
        $serviceCharges = ServiceCharge::with(['guest', 'reservation.room'])
            ->whereDate('charge_date', $date)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($s) => [
                'charge_number' => $s->charge_number,
                'guest_name' => $s->guest->guest_name ?? ($s->reservation->guest->guest_name ?? '-'),
                'room_number' => $s->reservation->room->room_number ?? '-',
                'service_name' => $s->service_name,
                'quantity' => $s->quantity,
                'payment_method' => $s->payment_method,
                'total_amount' => $s->total_amount,
            ]);

        $serviceChargeByMethod = ServiceCharge::whereDate('charge_date', $date)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // ─── Deposits (Key Card Deposit) ─────────────────────────────
        $depositRevenueToday = Deposit::whereDate('created_at', $date)->sum('total_amount');

        $depositList = Deposit::with(['guest', 'reservation.room', 'createdBy'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($d) => [
                'receipt_number' => $d->receipt_number,
                'guest_name' => $d->guest->guest_name ?? '-',
                'room_number' => $d->reservation?->room?->room_number ?? '-',
                'number_of_cards' => $d->number_of_cards,
                'nominal_per_card' => $d->nominal_per_card,
                'total_amount' => $d->total_amount,
                'payment_method' => $d->payment_method,
                'notes' => $d->notes,
                'created_by' => $d->createdBy?->name ?? '-',
                'created_at' => $d->created_at->format('H:i'),
            ]);

        $depositByMethod = Deposit::whereDate('created_at', $date)
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $cashDeposits = Deposit::whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        // ─── Expenses (Pengeluaran) ─────────────────────────────────
        $expensesToday = Expense::whereDate('expense_date', $date)->sum('amount');

        $expensesList = Expense::with('createdBy')
            ->whereDate('expense_date', $date)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($e) => [
                'expense_number' => $e->expense_number,
                'description' => $e->description,
                'amount' => $e->amount,
                'payment_method' => $e->payment_method,
                'notes' => $e->notes,
                'created_by' => $e->createdBy?->name ?? '-',
            ]);

        $expensesByMethod = Expense::whereDate('expense_date', $date)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // ─── Cash Flow (Ringkasan Kas) ──────────────────────────────
        $cashRevenue = Transaction::whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cashExpenses = Expense::whereDate('expense_date', $date)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cashFlowBalance = ($cashRevenue + $cashDeposits) - $cashExpenses;

        // In-house guests
        $inHouseGuests = Reservation::where(function ($q) use ($date) {
            $q->where('status', 'checked_in')
                ->orWhere(function ($sub) use ($date) {
                    $sub->where('status', 'checked_out')
                        ->whereDate('check_out', $date);
                });
        })
            ->with(['guest', 'room'])
            ->orderBy('check_out', 'asc')
            ->get()
            ->map(fn ($r) => [
                'reservation_number' => $r->reservation_number,
                'guest_name' => $r->guest->guest_name ?? '-',
                'room_number' => $r->room->room_number ?? '-',
                'room_type' => $r->room->room_type_name ?? '-',
                'check_in' => $r->check_in->format('d/m/Y'),
                'check_out' => $r->check_out->format('d/m/Y'),
                'total_nights' => $r->nights,
                'include_breakfast' => $r->include_breakfast,
            ]);

        // New bookings — split OTA vs Web vs Direct
        $webPaymentMethods = ['bank_transfer', 'credit_card', 'debit_card', 'virtual_account', 'ewallet', 'qris'];

        $allNewBookings = Reservation::whereDate('created_at', $date)
            ->with(['guest', 'room'])
            ->get()
            ->map(fn ($r) => [
                'reservation_number' => $r->reservation_number,
                'guest_name' => $r->guest->guest_name ?? '-',
                'room_number' => $r->room->room_number ?? '-',
                'room_type' => $r->room->room_type_name ?? '-',
                'check_in' => $r->check_in->format('d/m/Y'),
                'check_in_full' => $r->check_in->format('Y-m-d H:i:s'),
                'check_out' => $r->check_out->format('d/m/Y'),
                'nights' => $r->nights,
                'total_amount' => $r->total_amount,
                'status' => $r->status,
                'include_breakfast' => $r->include_breakfast,
                'ota_source' => $r->ota_source,
                'payment_method' => $r->payment_method,
            ]);

        $otaBookings = $allNewBookings
            ->filter(fn ($r) => ! empty($r['ota_source']) && $r['ota_source'] !== 'website')
            ->filter(function ($r) use ($bizStart, $bizEnd) {
                return $r['check_in_full'] >= $bizStart && $r['check_in_full'] < $bizEnd;
            })
            ->values();
        $webBookings = $allNewBookings->filter(fn ($r) => $r['ota_source'] === 'website' || (empty($r['ota_source']) && in_array($r['payment_method'], $webPaymentMethods)))->values();
        $directBookings = $allNewBookings
            ->filter(fn ($r) => empty($r['ota_source']) && ! in_array($r['payment_method'], $webPaymentMethods) && $r['ota_source'] !== 'website' && $r['status'] !== 'cancelled')
            ->filter(function ($r) use ($bizStart, $bizEnd) {
                // Hanya tampilkan yang check_in-nya dalam business date range (hari H)
                return $r['check_in_full'] >= $bizStart && $r['check_in_full'] < $bizEnd;
            })
            ->values();

        // ─── Room Type Summary ─────────────────────────────────────
        $roomTypeSummary = Room::selectRaw("
                room_type_name,
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'cleaning' THEN 1 ELSE 0 END) as cleaning,
                SUM(CASE WHEN status IN ('maintenance','out_of_order') THEN 1 ELSE 0 END) as maintenance,
                COUNT(*) as total
            ")
            ->whereNotNull('room_type_name')
            ->where('room_type_name', '!=', '')
            ->groupBy('room_type_name')
            ->orderBy('room_type_name')
            ->get()
            ->keyBy('room_type_name');

        return compact(
            'totalRooms', 'occupiedRooms', 'availableRooms', 'maintenanceRooms', 'occupancyRate',
            'revenueToday', 'restoRevenueToday', 'serviceChargeRevenueToday', 'totalRevenue',
            'cashRevenueToday', 'otaRevenueToday', 'webRevenueToday',
            'revenueByMethod', 'transactionsByMethod',
            'restoTransactions', 'restoRevenueByMethod',
            'serviceCharges', 'serviceChargeByMethod',
            'expensesToday', 'expensesList', 'expensesByMethod',
            'depositRevenueToday', 'depositList', 'depositByMethod', 'cashDeposits',
            'cashRevenue', 'cashExpenses', 'cashFlowBalance',
            'checkinsToday', 'checkoutsToday', 'inHouseGuests',
            'otaBookings', 'webBookings', 'directBookings',
            'roomTypeSummary'
        );
    }
}
