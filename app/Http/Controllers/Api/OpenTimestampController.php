<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceTimestamp;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Repositories\InvoiceTimestampRepository;
use App\Services\OpenTimestampService;
use Illuminate\Http\JsonResponse;


/**
 * API Controller untuk operasi OpenTimestamps.
 *
 * Endpoint:
 * - POST   /api/ots/timestamp/invoice/{reservation}   — Timestamp invoice
 * - POST   /api/ots/timestamp/transaction/{transaction} — Timestamp transaksi
 * - GET    /api/ots/verify/invoice/{reservation}        — Verifikasi invoice
 * - GET    /api/ots/verify/transaction/{transaction}    — Verifikasi transaksi
 * - GET    /api/ots/proof/{invoiceTimestamp}            — Download proof
 * - POST   /api/ots/upgrade/{invoiceTimestamp}          — Upgrade proof ke blockchain
 * - GET    /api/ots/pending                             — Daftar pending timestamps
 */
class OpenTimestampController extends Controller
{
    public function __construct(
        private readonly OpenTimestampService $otsService,
        private readonly InvoiceTimestampRepository $repository,
    ) {}

    /**
     * Timestamp sebuah invoice reservasi.
     *
     * POST /api/ots/timestamp/invoice/{reservation}
     */
    public function timestampInvoice(Reservation $reservation): JsonResponse
    {
        try {
            $timestamp = $this->otsService->timestampInvoice($reservation);

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil di-timestamp.',
                'data' => $this->formatTimestamp($timestamp),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal timestamp invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Timestamp sebuah transaksi pembayaran.
     *
     * POST /api/ots/timestamp/transaction/{transaction}
     */
    public function timestampTransaction(Transaction $transaction): JsonResponse
    {
        try {
            $timestamp = $this->otsService->timestampTransaction($transaction);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil di-timestamp.',
                'data' => $this->formatTimestamp($timestamp),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal timestamp transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verifikasi integritas invoice.
     *
     * GET /api/ots/verify/invoice/{reservation}
     */
    public function verifyInvoice(Reservation $reservation): JsonResponse
    {
        $revision = request()->integer('revision', -1);
        $revision = $revision >= 0 ? $revision : null;

        $result = $this->otsService->verifyInvoice($reservation, $revision);

        return response()->json([
            'success' => $result['verified'],
            'data' => $result,
        ]);
    }

    /**
     * Verifikasi integritas transaksi.
     *
     * GET /api/ots/verify/transaction/{transaction}
     */
    public function verifyTransaction(Transaction $transaction): JsonResponse
    {
        $revision = request()->integer('revision', -1);
        $revision = $revision >= 0 ? $revision : null;

        $result = $this->otsService->verifyTransaction($transaction, $revision);

        return response()->json([
            'success' => $result['verified'],
            'data' => $result,
        ]);
    }

    /**
     * Download file .ots proof.
     *
     * GET /api/ots/proof/{invoiceTimestamp}
     */
    public function downloadProof(InvoiceTimestamp $timestamp): JsonResponse
    {
        $file = $this->otsService->downloadOtsFile($timestamp);

        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'File .ots belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'filename' => $file['filename'],
                'content_base64' => base64_encode($file['content']),
                'sha256' => $timestamp->sha256,
                'revision' => $timestamp->revision,
                'status' => $timestamp->ots_status,
            ],
        ]);
    }

    /**
     * Upgrade proof — kirim ulang ke calendar untuk konfirmasi blockchain.
     *
     * POST /api/ots/upgrade/{invoiceTimestamp}
     */
    public function upgradeProof(InvoiceTimestamp $timestamp): JsonResponse
    {
        if ($timestamp->is_confirmed) {
            return response()->json([
                'success' => true,
                'message' => 'Proof sudah terkonfirmasi.',
                'data' => $this->formatTimestamp($timestamp),
            ]);
        }

        $result = $this->otsService->upgradeProof($timestamp);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['success']
                ? $this->formatTimestamp($timestamp->fresh())
                : null,
        ]);
    }

    /**
     * Daftar timestamp yang masih pending.
     *
     * GET /api/ots/pending
     */
    public function pendingTimestamps(): JsonResponse
    {
        $limit = min((int) request('limit', 50), 100);
        $timestamps = $this->repository->getPendingTimestamps($limit);

        return response()->json([
            'success' => true,
            'count' => $timestamps->count(),
            'data' => $timestamps->map(fn (InvoiceTimestamp $t) => $this->formatTimestamp($t)),
        ]);
    }

    /**
     * Buat revision baru untuk invoice.
     *
     * POST /api/ots/revision/invoice/{reservation}
     */
    public function createRevision(Reservation $reservation): JsonResponse
    {
        try {
            $timestamp = $this->otsService->createRevision($reservation);

            return response()->json([
                'success' => true,
                'message' => 'Revision baru berhasil dibuat.',
                'data' => $this->formatTimestamp($timestamp),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat revision: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Riwayat timestamp untuk suatu invoice.
     *
     * GET /api/ots/history/invoice/{reservation}
     */
    public function invoiceHistory(Reservation $reservation): JsonResponse
    {
        $timestamps = $this->repository->findByInvoice($reservation->id, 'reservation');

        return response()->json([
            'success' => true,
            'count' => $timestamps->count(),
            'data' => $timestamps->map(fn (InvoiceTimestamp $t) => $this->formatTimestamp($t)),
        ]);
    }

    /**
     * Format timestamp untuk response JSON.
     */
    private function formatTimestamp(InvoiceTimestamp $timestamp): array
    {
        return [
            'id' => $timestamp->id,
            'invoice_id' => $timestamp->invoice_id,
            'invoice_type' => $timestamp->invoice_type,
            'revision' => $timestamp->revision,
            'sha256' => $timestamp->sha256,
            'ots_status' => $timestamp->ots_status,
            'status_label' => $timestamp->status_label,
            'is_confirmed' => $timestamp->is_confirmed,
            'calendar' => $timestamp->calendar,
            'bitcoin_txid' => $timestamp->bitcoin_txid,
            'bitcoin_block' => $timestamp->bitcoin_block,
            'bitcoin_block_hash' => $timestamp->bitcoin_block_hash,
            'timestamped_at' => $timestamp->timestamped_at?->toIso8601String(),
            'confirmed_at' => $timestamp->confirmed_at?->toIso8601String(),
            'created_at' => $timestamp->created_at?->toIso8601String(),
        ];
    }
}
