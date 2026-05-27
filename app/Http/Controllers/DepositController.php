<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Guest;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    const DEFAULT_NOMINAL = 100000;

    /**
     * List semua deposit.
     */
    public function index(Request $request)
    {
        $query = Deposit::with(['guest', 'reservation.room', 'createdBy']);

        // Filter tanggal
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Pencarian
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%");
                  });
            });
        }

        $deposits = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // AJAX: return table partial only
        if ($request->expectsJson()) {
            $table = view('deposits.partials.table', compact('deposits', 'dateFrom', 'dateTo', 'search'))->render();
            return response()->json([
                'success' => true,
                'table' => $table,
            ]);
        }

        return view('deposits.index', compact('deposits', 'dateFrom', 'dateTo', 'search'));
    }

    /**
     * Form tambah deposit.
     */
    public function create(Request $request)
    {
        $guests = Guest::orderBy('guest_name')->get();
        $reservations = Reservation::with(['guest', 'room'])
            ->whereIn('status', ['pending', 'checked_in'])
            ->orderBy('check_in', 'desc')
            ->get();

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        $selectedReservation = null;
        if ($request->get('reservation_id')) {
            $selectedReservation = Reservation::with(['guest', 'room'])->find($request->get('reservation_id'));
        }

        // AJAX: return modal view
        if ($request->expectsJson()) {
            $view = view('deposits.modal-create', compact('guests', 'reservations', 'selectedReservation', 'paymentMethods'))->render();
            return response()->json([
                'success' => true,
                'view' => $view,
            ]);
        }

        return view('deposits.create', compact('guests', 'reservations', 'selectedReservation', 'paymentMethods'));
    }

    /**
     * Simpan deposit baru.
     */
    public function store(Request $request)
    {
        $paymentSlugs = PaymentMethod::where('is_active', true)->pluck('slug')->implode(',');
        $validated = $request->validate([
            'guest_id'        => 'required|exists:guests,id',
            'reservation_id'  => 'nullable|exists:reservations,id',
            'number_of_cards' => 'required|integer|min:1|max:10',
            'nominal_per_card' => 'required|numeric|min:0',
            'payment_method'  => 'required|in:' . $paymentSlugs,
            'notes'           => 'nullable|string|max:500',
        ]);

        $deposit = Deposit::create([
            'guest_id'         => $validated['guest_id'],
            'reservation_id'   => $validated['reservation_id'] ?? null,
            'number_of_cards'  => $validated['number_of_cards'],
            'nominal_per_card' => $validated['nominal_per_card'],
            'total_amount'     => $validated['number_of_cards'] * $validated['nominal_per_card'],
            'payment_method'   => $validated['payment_method'],
            'notes'            => $validated['notes'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        // Check if request is AJAX — return the show view for modal display
        if (request()->expectsJson()) {
            $deposit->load(['guest', 'reservation.room', 'createdBy']);
            $view = view('deposits.modal-show', compact('deposit'))->render();
            return response()->json([
                'success' => true,
                'message' => 'Deposit berhasil disimpan.',
                'view' => $view,
                'deposit' => $deposit
            ]);
        }

        return redirect()->route('deposits.show', $deposit)
            ->with('success', 'Deposit berhasil disimpan.');
    }

    /**
     * Detail deposit + print.
     */
    public function show(Deposit $deposit)
    {
        $deposit->load(['guest', 'reservation.room', 'createdBy']);

        // AJAX: return modal view
        if (request()->expectsJson()) {
            $view = view('deposits.modal-show', compact('deposit'))->render();
            return response()->json([
                'success' => true,
                'view' => $view,
            ]);
        }

        return view('deposits.show', compact('deposit'));
    }

    /**
     * Tandai deposit sudah dikembalikan.
     */
    public function returnDeposit(Deposit $deposit)
    {
        if ($deposit->status === 'returned') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit ini sudah dikembalikan sebelumnya.'
                ]);
            }
            return back()->with('error', 'Deposit ini sudah dikembalikan sebelumnya.');
        }

        $deposit->update(['status' => 'returned']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Deposit {$deposit->receipt_number} berhasil ditandai sebagai dikembalikan.",
                'redirect_url' => route('deposits.index')
            ]);
        }

        return redirect()->route('deposits.index')
            ->with('success', "Deposit {$deposit->receipt_number} berhasil ditandai sebagai dikembalikan.");
    }
}
