<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['guest', 'room', 'createdBy']);

        // Pencarian
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function ($q) use ($search) {
                      $q->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter status
        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        }

        // Filter tanggal
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('check_in', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('check_out', '<=', $dateTo);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('reservations.index', compact('reservations', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('reservations.show', compact('reservation', 'transactions'));
    }

    /**
     * Tambah pembayaran (DP / Pelunasan / Multi Payment)
     */
    public function addPayment(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'cancelled' || $reservation->status === 'checked_out') {
            return back()->with('error', 'Tidak bisa menambah pembayaran untuk reservasi ini.');
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:dp,pelunasan,tambahan',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,debit_card',
            'amount' => 'required|numeric|min:0',
        ]);

        $sisaBayar = $reservation->total_amount - $reservation->paid_amount;

        if ($validated['amount'] > $sisaBayar) {
            return back()->with('error', 'Nominal pembayaran melebihi sisa bayar (Rp ' . number_format($sisaBayar, 0, ',', '.') . ')');
        }

        // Buat transaksi
        Transaction::create([
            'transaction_number' => 'TRX-' . strtoupper(uniqid()),
            'reservation_id' => $reservation->id,
            'type' => $validated['payment_type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'created_by' => auth()->id(),
        ]);

        // Update paid_amount di reservasi
        $reservation->paid_amount += $validated['amount'];
        $reservation->save();

        $typeLabel = $validated['payment_type'] === 'dp' ? 'DP' : ($validated['payment_type'] === 'pelunasan' ? 'Pelunasan' : 'Pembayaran tambahan');

        return redirect()->route('reservations.show', $reservation)
            ->with('success', "{$typeLabel} sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil ditambahkan.");
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->status === 'checked_in') {
            return back()->with('error', 'Reservasi yang sudah check-in tidak bisa dibatalkan.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')->with('success', "Reservasi {$reservation->reservation_number} berhasil dibatalkan.");
    }

    public function checkin(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Hanya reservasi dengan status pending yang bisa di-check-in.');
        }

        $reservation->update(['status' => 'checked_in']);
        $reservation->room->update(['status' => 'occupied']);

        return redirect()->route('reservations.show', $reservation)->with('success', "Check-in berhasil untuk kamar {$reservation->room->room_number}.");
    }

    public function checkout(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Hanya reservasi yang sudah check-in yang bisa di-check-out.');
        }

        $reservation->update(['status' => 'checked_out']);
        $reservation->room->update(['status' => 'available']);

        return redirect()->route('reservations.show', $reservation)->with('success', "Check-out berhasil untuk kamar {$reservation->room->room_number}.");
    }

    /**
     * Print Kwitansi
     */
    public function printKwitansi(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('reservations.print-kwitansi', compact('reservation', 'transactions'));
    }

    /**
     * Print Invoice
     */
    public function printInvoice(Reservation $reservation)
    {
        $reservation->load(['guest', 'room', 'createdBy']);
        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('reservations.print-invoice', compact('reservation', 'transactions'));
    }
}
