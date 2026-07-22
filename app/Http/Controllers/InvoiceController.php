<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;

class InvoiceController extends Controller
{
    /**
     * Tampilkan invoice secara publik (via QR code / link)
     * GET /invoice/{reservationNumber}
     */
    public function publicShow($reservationNumber)
    {
        $reservation = Reservation::with([
            'guest', 'room', 'room.roomType', 'createdBy',
            'serviceCharges', 'serviceCharges.createdBy',
            'restoTransactions', 'restoTransactions.createdBy',
        ])
            ->where('reservation_number', $reservationNumber)
            ->firstOrFail();

        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalServiceCharge = $reservation->serviceCharges->sum('total_amount');
        $totalResto = $reservation->restoTransactions->sum('total_amount');
        $grandTotal = $reservation->total_amount + $totalServiceCharge + $totalResto;

        return view('invoices.public-show', compact(
            'reservation', 'transactions',
            'totalServiceCharge', 'totalResto', 'grandTotal'
        ));
    }
}
