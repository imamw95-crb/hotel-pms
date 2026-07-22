<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\InvoiceSignatureService;
use App\Services\OpenTimestampService;

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
            // Bandingkan 16 karakter pertama (short signature di QR)
            $storedShort = substr($reservation->invoice_signature, 0, 16);
            $isValid = hash_equals($storedShort, $signature);
            $signatureStatus = $isValid ? 'valid' : 'invalid';
        }

        // ── Validasi OTS ──
        $otsService = app(OpenTimestampService::class);
        $otsStatus = $otsService->verifyInvoice($reservation);

        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // ── OTS untuk setiap transaksi ──
        $transactionsOts = [];
        foreach ($transactions as $txn) {
            $transactionsOts[$txn->id] = $otsService->verifyTransaction($txn);
        }

        $totalServiceCharge = $reservation->serviceCharges->sum('total_amount');
        $totalResto = $reservation->restoTransactions->sum('total_amount');
        $grandTotal = $reservation->total_amount + $totalServiceCharge + $totalResto;

        return view('invoices.public-show', compact(
            'reservation', 'transactions',
            'totalServiceCharge', 'totalResto', 'grandTotal',
            'signatureStatus', 'isValid',
            'otsStatus', 'transactionsOts'
        ));
    }

    /**
     * Download OTS proof untuk invoice
     * GET /invoice/{reservationNumber}/ots-proof
     */
    public function downloadOtsProof($reservationNumber)
    {
        $reservation = Reservation::where('reservation_number', $reservationNumber)->firstOrFail();

        if (!$reservation->ots_proof) {
            return response()->json([
                'success' => false,
                'message' => 'OTS proof belum tersedia untuk invoice ini.',
            ], 404);
        }

        $proof = json_decode($reservation->ots_proof, true);

        return response()->json([
            'success' => true,
            'data' => $proof,
            'message' => 'OTS proof — Invoice ' . $reservation->reservation_number,
        ]);
    }

    /**
     * Download OTS proof untuk transaksi tertentu
     * GET /invoice/{reservationNumber}/ots-proof/transaction/{transactionId}
     */
    public function downloadTransactionOtsProof($reservationNumber, $transactionId)
    {
        $reservation = Reservation::where('reservation_number', $reservationNumber)->firstOrFail();
        $transaction = Transaction::where('id', $transactionId)
            ->where('reservation_id', $reservation->id)
            ->firstOrFail();

        if (!$transaction->ots_proof) {
            return response()->json([
                'success' => false,
                'message' => 'OTS proof belum tersedia untuk transaksi ini.',
            ], 404);
        }

        $proof = json_decode($transaction->ots_proof, true);

        return response()->json([
            'success' => true,
            'data' => $proof,
            'message' => 'OTS proof — Transaksi ' . $transaction->transaction_number,
        ]);
    }
}
