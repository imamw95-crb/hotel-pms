<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\InvoiceSignatureService;

class InvoiceController extends Controller
{
    /**
     * Tampilkan invoice secara publik (via QR code / link)
     * GET /invoice/{reservationNumber}
     */
    public function publicShow($reservationNumber, InvoiceSignatureService $signatureService)
    {
        $reservation = Reservation::with([
            'guest', 'room', 'room.roomType', 'createdBy',
            'serviceCharges', 'serviceCharges.createdBy',
            'restoTransactions', 'restoTransactions.createdBy',
        ])
            ->where('reservation_number', $reservationNumber)
            ->firstOrFail();

        // ── Validasi HMAC Signature ──
        $signature = request('sig');
        $isValid = false;
        $signatureStatus = 'no_signature';

        if ($signature && $reservation->invoice_signature) {
            $isValid = $signatureService->verify($reservation, $signature);
            $signatureStatus = $isValid ? 'valid' : 'invalid';
        }

        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalServiceCharge = $reservation->serviceCharges->sum('total_amount');
        $totalResto = $reservation->restoTransactions->sum('total_amount');
        $grandTotal = $reservation->total_amount + $totalServiceCharge + $totalResto;

        return view('invoices.public-show', compact(
            'reservation', 'transactions',
            'totalServiceCharge', 'totalResto', 'grandTotal',
            'signatureStatus', 'isValid'
        ));
    }
}
