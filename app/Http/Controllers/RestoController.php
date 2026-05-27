<?php

namespace App\Http\Controllers;

use App\Models\RestoTransaction;
use App\Models\Guest;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use Illuminate\Http\Request;

class RestoController extends Controller
{
    /**
     * List transaksi resto.
     */
    public function index(Request $request)
    {
        $query = RestoTransaction::with(['guest', 'reservation.room', 'createdBy']);

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('guest_name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        // Summary
        $totalToday = RestoTransaction::whereDate('created_at', today())->sum('total_amount');
        $totalPeriod = $query->sum('total_amount');

        return view('resto.index', compact('transactions', 'dateFrom', 'dateTo', 'search', 'totalToday', 'totalPeriod'));
    }

    /**
     * Form tambah transaksi resto.
     */
    public function create(Request $request)
    {
        $guests = Guest::orderBy('guest_name')->get();
        $reservations = Reservation::with(['guest', 'room'])
            ->where('status', 'checked_in')
            ->orderBy('check_in', 'desc')
            ->get();

        return view('resto.create', compact('guests', 'reservations'));
    }

    /**
     * Simpan transaksi resto.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id'       => 'nullable|exists:guests,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'table_number'   => 'nullable|string|max:20',
            'items'          => 'required|array|min:1',
            'items.*.name'   => 'required|string|max:200',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.price'  => 'required|numeric|min:0',
            'tax'            => 'nullable|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:' . PaymentMethod::where('is_active', true)->pluck('slug')->implode(','),
            'notes'          => 'nullable|string|max:500',
        ]);

        // Calculate subtotal & total per item
        $items = [];
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $itemSubtotal = $item['qty'] * $item['price'];
            $items[] = [
                'name'     => $item['name'],
                'qty'      => $item['qty'],
                'price'    => $item['price'],
                'subtotal' => $itemSubtotal,
            ];
            $subtotal += $itemSubtotal;
        }

        $tax = $validated['tax'] ?? 0;
        $discount = $validated['discount'] ?? 0;
        $totalAmount = $subtotal + $tax - $discount;

        $transaction = RestoTransaction::create([
            'guest_id'       => $validated['guest_id'] ?? null,
            'reservation_id' => $validated['reservation_id'] ?? null,
            'table_number'   => $validated['table_number'] ?? null,
            'items'          => $items,
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'discount'       => $discount,
            'total_amount'   => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi resto berhasil disimpan.',
                'redirect_url' => route('resto.show', $transaction),
                'transaction' => $transaction
            ]);
        }

        return redirect()->route('resto.show', $transaction)
            ->with('success', 'Transaksi resto berhasil disimpan.');
    }

    /**
     * Detail transaksi + print.
     */
    public function show(RestoTransaction $restoTransaction)
    {
        $restoTransaction->load(['guest', 'reservation.room', 'createdBy']);
        return view('resto.show', ['transaction' => $restoTransaction]);
    }
}
