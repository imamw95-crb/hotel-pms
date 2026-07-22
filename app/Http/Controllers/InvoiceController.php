<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Repositories\InvoiceTimestampRepository;
use App\Services\OpenTimestampService;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly OpenTimestampService $otsService,
        private readonly InvoiceTimestampRepository $repository,
    ) {}

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

        // ── Validasi HMAC Signature ──
        $signature = request('sig');
        $isValid = false;
        $signatureStatus = 'no_signature';

        if ($signature && $reservation->invoice_signature) {
            $storedShort = substr($reservation->invoice_signature, 0, 16);
            $isValid = hash_equals($storedShort, $signature);
            $signatureStatus = $isValid ? 'valid' : 'invalid';
        }

        // Blok akses jika tidak ada signature atau signature tidak valid
        if ($signatureStatus !== 'valid') {
            abort(404);
        }

        // ── OTS: Auto-timestamp jika belum ada proof ──
        $invoiceTimestamp = $this->repository->findLatestRevision($reservation->id, 'reservation');

        if (! $invoiceTimestamp) {
            try {
                $this->otsService->timestampInvoice($reservation);
                $invoiceTimestamp = $this->repository->findLatestRevision($reservation->id, 'reservation');
            } catch (\Exception $e) {
                Log::error('Invoice: auto-timestamp failed', [
                    'reservation' => $reservation->reservation_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $otsStatus = $this->otsService->verifyInvoice($reservation);

        // ── Cek apakah ini bagian dari booking group ──
        $isGroupInvoice = ! empty($reservation->booking_group_id);

        if ($isGroupInvoice) {
            $reservations = Reservation::with([
                'guest', 'room', 'room.roomType', 'createdBy',
                'serviceCharges', 'serviceCharges.createdBy',
                'restoTransactions', 'restoTransactions.createdBy',
            ])
                ->where('booking_group_id', $reservation->booking_group_id)
                ->orderBy('room_id')
                ->get();

            $reservationIds = $reservations->pluck('id');
            $transactions = Transaction::whereIn('reservation_id', $reservationIds)
                ->orderBy('created_at', 'desc')
                ->get();

            // ── OTS untuk setiap transaksi ──
            $transactionsOts = [];
            foreach ($transactions as $txn) {
                $txnTimestamp = $this->repository->findLatestRevision($txn->id, 'transaction');
                if (! $txnTimestamp) {
                    try {
                        $this->otsService->timestampTransaction($txn);
                    } catch (\Exception $e) {
                        Log::error('Invoice: auto-timestamp transaction failed', [
                            'transaction' => $txn->transaction_number,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                $transactionsOts[$txn->id] = $this->otsService->verifyTransaction($txn);
            }

            $groupTotal = $reservations->sum('total_amount');
            $groupPaid = $reservations->sum('paid_amount');
            $totalServiceCharge = $reservations->sum(fn ($r) => $r->serviceCharges->sum('total_amount'));
            $totalResto = $reservations->sum(fn ($r) => $r->restoTransactions->sum('total_amount'));
            $grandTotal = $groupTotal + $totalServiceCharge + $totalResto;

            return view('invoices.public-show', compact(
                'reservation', 'reservations', 'transactions',
                'groupTotal', 'groupPaid',
                'totalServiceCharge', 'totalResto', 'grandTotal',
                'signatureStatus', 'isValid',
                'otsStatus', 'invoiceTimestamp', 'transactionsOts',
                'isGroupInvoice'
            ));
        }

        $transactions = Transaction::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // ── OTS untuk setiap transaksi ──
        $transactionsOts = [];
        foreach ($transactions as $txn) {
            $txnTimestamp = $this->repository->findLatestRevision($txn->id, 'transaction');
            if (! $txnTimestamp) {
                try {
                    $this->otsService->timestampTransaction($txn);
                } catch (\Exception $e) {
                    Log::error('Invoice: auto-timestamp transaction failed', [
                        'transaction' => $txn->transaction_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            $transactionsOts[$txn->id] = $this->otsService->verifyTransaction($txn);
        }

        $totalServiceCharge = $reservation->serviceCharges->sum('total_amount');
        $totalResto = $reservation->restoTransactions->sum('total_amount');
        $grandTotal = $reservation->total_amount + $totalServiceCharge + $totalResto;

        return view('invoices.public-show', compact(
            'reservation', 'transactions',
            'totalServiceCharge', 'totalResto', 'grandTotal',
            'signatureStatus', 'isValid',
            'otsStatus', 'invoiceTimestamp', 'transactionsOts',
            'isGroupInvoice'
        ));
    }

    /**
     * Download OTS proof untuk invoice
     * GET /invoice/{reservationNumber}/ots-proof
     */
    public function downloadOtsProof($reservationNumber)
    {
        $reservation = Reservation::where('reservation_number', $reservationNumber)->firstOrFail();

        $timestamp = $this->repository->findLatestRevision($reservation->id, 'reservation');

        if (! $timestamp) {
            // Fallback ke legacy
            if ($reservation->ots_proof) {
                $proof = json_decode($reservation->ots_proof, true);
                return response()->json([
                    'success' => true,
                    'data' => $proof,
                    'message' => 'OTS proof (legacy) — Invoice ' . $reservation->reservation_number,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'OTS proof belum tersedia untuk invoice ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $timestamp->id,
                'revision' => $timestamp->revision,
                'sha256' => $timestamp->sha256,
                'ots_status' => $timestamp->ots_status,
                'calendar' => $timestamp->calendar,
                'bitcoin_txid' => $timestamp->bitcoin_txid,
                'bitcoin_block' => $timestamp->bitcoin_block,
                'timestamped_at' => $timestamp->timestamped_at?->toIso8601String(),
                'confirmed_at' => $timestamp->confirmed_at?->toIso8601String(),
                'has_ots_file' => ! empty($timestamp->ots_file),
            ],
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

        $timestamp = $this->repository->findLatestRevision($transaction->id, 'transaction');

        if (! $timestamp) {
            if ($transaction->ots_proof) {
                $proof = json_decode($transaction->ots_proof, true);
                return response()->json([
                    'success' => true,
                    'data' => $proof,
                    'message' => 'OTS proof (legacy) — Transaksi ' . $transaction->transaction_number,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'OTS proof belum tersedia untuk transaksi ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $timestamp->id,
                'revision' => $timestamp->revision,
                'sha256' => $timestamp->sha256,
                'ots_status' => $timestamp->ots_status,
                'calendar' => $timestamp->calendar,
                'timestamped_at' => $timestamp->timestamped_at?->toIso8601String(),
                'confirmed_at' => $timestamp->confirmed_at?->toIso8601String(),
            ],
            'message' => 'OTS proof — Transaksi ' . $transaction->transaction_number,
        ]);
    }
}
