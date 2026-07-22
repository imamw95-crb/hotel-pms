<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\InvoiceTimestamp;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\OpenTimestampService;
use Illuminate\Http\RedirectResponse;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Web controller untuk operasi publik OpenTimestamps.
 *
 * Melayani:
 * - Download file .ots
 * - Verifikasi publik
 * - Download langsung proof file
 */
class OpenTimestampWebController extends Controller
{
    public function __construct(
        private readonly OpenTimestampService $otsService,
    ) {}

    /**
     * Download file .ots untuk invoice.
     *
     * GET /invoice/{reservationNumber}/ots/download
     */
    public function downloadInvoiceOts(string $reservationNumber): StreamedResponse|RedirectResponse
    {
        $reservation = Reservation::where('reservation_number', $reservationNumber)->firstOrFail();

        $revision = request()->integer('revision', -1);
        $revision = $revision >= 0 ? $revision : null;

        /** @var InvoiceTimestamp|null $timestamp */
        $timestamp = null;

        if ($revision !== null) {
            $repo = app(\App\Repositories\InvoiceTimestampRepository::class);
            $timestamp = $repo->findByRevision($reservation->id, $revision, 'reservation');
        } else {
            $repo = app(\App\Repositories\InvoiceTimestampRepository::class);
            $timestamp = $repo->findLatestRevision($reservation->id, 'reservation');
        }

        if (! $timestamp || ! $timestamp->ots_file) {
            return redirect()->back()->with('error', 'File .ots belum tersedia.');
        }

        return $this->streamOtsFile($timestamp);
    }

    /**
     * Download file .ots untuk transaksi.
     *
     * GET /invoice/{reservationNumber}/ots/download/transaction/{transactionId}
     */
    public function downloadTransactionOts(string $reservationNumber, int $transactionId): StreamedResponse|RedirectResponse
    {
        $reservation = Reservation::where('reservation_number', $reservationNumber)->firstOrFail();

        /** @var Transaction|null $transaction */
        $transaction = Transaction::where('id', $transactionId)
            ->where('reservation_id', $reservation->id)
            ->firstOrFail();

        $revision = request()->integer('revision', -1);
        $revision = $revision >= 0 ? $revision : null;

        $repo = app(\App\Repositories\InvoiceTimestampRepository::class);
        $timestamp = $revision !== null
            ? $repo->findByRevision($transaction->id, $revision, 'transaction')
            : $repo->findLatestRevision($transaction->id, 'transaction');

        if (! $timestamp || ! $timestamp->ots_file) {
            return redirect()->back()->with('error', 'File .ots belum tersedia.');
        }

        return $this->streamOtsFile($timestamp);
    }

    /**
     * Verifikasi publik via web — redirect ke halaman invoice.
     *
     * GET /ots/verify/{sha256}
     */
    public function publicVerify(string $sha256): RedirectResponse
    {
        // Validasi format SHA-256
        if (! preg_match('/^[a-f0-9]{64}$/i', $sha256)) {
            return redirect()->back()->with('error', 'Format SHA-256 tidak valid.');
        }

        $repo = app(\App\Repositories\InvoiceTimestampRepository::class);

        /** @var InvoiceTimestamp|null $timestamp */
        $timestamp = InvoiceTimestamp::where('sha256', strtolower($sha256))->first();

        if (! $timestamp) {
            return redirect()->back()->with('error', 'Hash tidak ditemukan dalam database.');
        }

        // Redirect ke halaman invoice yang sesuai
        if ($timestamp->invoice_type === 'reservation') {
            $reservation = Reservation::find($timestamp->invoice_id);
            if ($reservation) {
                return redirect()->to('/invoice/' . $reservation->reservation_number);
            }
        }

        return redirect()->back()->with('error', 'Invoice tidak ditemukan.');
    }

    /**
     * Stream file .ots ke browser untuk didownload.
     */
    private function streamOtsFile(InvoiceTimestamp $timestamp): StreamedResponse
    {
        $binaryContent = base64_decode($timestamp->ots_file, true);

        if ($binaryContent === false) {
            return redirect()->back()->with('error', 'File .ots rusak.');
        }

        return response()->stream(function () use ($binaryContent): void {
            echo $binaryContent;
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $timestamp->ots_filename . '"',
            'Content-Length' => (string) strlen($binaryContent),
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
