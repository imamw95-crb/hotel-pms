<?php

declare(strict_types=1);

use App\Models\InvoiceTimestamp;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Services\OpenTimestampService;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| OpenTimestamps (OTS) Helper Functions
|--------------------------------------------------------------------------
|
| Fungsi-fungsi bantuan untuk verifikasi dan manipulasi OpenTimestamps.
| Dapat dipanggil dari Blade view atau controller mana pun.
|
*/

if (! function_exists('ots_verify_invoice')) {
    /**
     * Verifikasi integritas invoice via OTS.
     *
     * @param  Reservation $reservation
     * @param  int|null    $revision Revision spesifik (null = terbaru)
     * @return array{verified: bool, status: string, message: string, timestamp: array|null}
     */
    function ots_verify_invoice(Reservation $reservation, ?int $revision = null): array
    {
        try {
            return app(OpenTimestampService::class)->verifyInvoice($reservation, $revision);
        } catch (\Exception $e) {
            Log::error('OTS Helper: verify_invoice error', [
                'reservation' => $reservation->reservation_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'verified' => false,
                'status' => 'error',
                'message' => 'Error verifikasi: ' . $e->getMessage(),
                'timestamp' => null,
            ];
        }
    }
}

if (! function_exists('ots_verify_transaction')) {
    /**
     * Verifikasi integritas transaksi via OTS.
     *
     * @param  Transaction $transaction
     * @param  int|null    $revision
     * @return array
     */
    function ots_verify_transaction(Transaction $transaction, ?int $revision = null): array
    {
        try {
            return app(OpenTimestampService::class)->verifyTransaction($transaction, $revision);
        } catch (\Exception $e) {
            Log::error('OTS Helper: verify_transaction error', [
                'transaction' => $transaction->transaction_number,
                'error' => $e->getMessage(),
            ]);

            return [
                'verified' => false,
                'status' => 'error',
                'message' => 'Error verifikasi: ' . $e->getMessage(),
                'timestamp' => null,
            ];
        }
    }
}

if (! function_exists('ots_format_status_badge')) {
    /**
     * Generate HTML badge untuk status OTS (Bootstrap 5).
     *
     * @param  string $status ots_status: pending, confirming, confirmed, failed
     * @return string HTML badge
     */
    function ots_format_status_badge(string $status): string
    {
        return match ($status) {
            InvoiceTimestamp::STATUS_CONFIRMED => '<span class="badge bg-success">Terkonfirmasi</span>',
            InvoiceTimestamp::STATUS_CONFIRMING => '<span class="badge bg-info text-dark">Sedang Dikonfirmasi</span>',
            InvoiceTimestamp::STATUS_PENDING => '<span class="badge bg-warning text-dark">Pending</span>',
            InvoiceTimestamp::STATUS_FAILED => '<span class="badge bg-danger">Gagal</span>',
            default => '<span class="badge bg-secondary">Tidak Diketahui</span>',
        };
    }
}

if (! function_exists('ots_short_hash')) {
    /**
     * Potong SHA-256 untuk display.
     */
    function ots_short_hash(string $hash, int $length = 16): string
    {
        if (strlen($hash) <= $length) {
            return $hash;
        }

        return substr($hash, 0, $length) . '...';
    }
}

if (! function_exists('ots_download_url')) {
    /**
     * Generate URL untuk download file .ots.
     *
     * @param  Reservation|Transaction $model
     * @param  int|null                $revision
     * @return string|null
     */
    function ots_download_url($model, ?int $revision = null): ?string
    {
        if ($model instanceof Reservation) {
            $url = route('invoice.ots.download', [
                'reservationNumber' => $model->reservation_number,
            ]);

            if ($revision !== null) {
                $url .= '?revision=' . $revision;
            }

            return $url;
        }

        if ($model instanceof Transaction && $model->relationLoaded('reservation')) {
            $url = route('invoice.ots.download.transaction', [
                'reservationNumber' => $model->reservation->reservation_number,
                'transactionId' => $model->id,
            ]);

            if ($revision !== null) {
                $url .= '?revision=' . $revision;
            }

            return $url;
        }

        return null;
    }
}

if (! function_exists('ots_verify_url')) {
    /**
     * Generate URL untuk verifikasi publik berdasarkan SHA-256.
     */
    function ots_verify_url(string $sha256): string
    {
        return route('ots.verify', ['sha256' => $sha256]);
    }
}

if (! function_exists('ots_format_timestamp')) {
    /**
     * Format timestamp untuk display dengan timezone WIB.
     */
    function ots_format_timestamp(?string $timestamp): string
    {
        if (! $timestamp) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($timestamp)
                ->timezone('Asia/Jakarta')
                ->format('d F Y H:i:s') . ' WIB';
        } catch (\Exception) {
            return $timestamp ?? '-';
        }
    }
}

if (! function_exists('ots_block_explorer_url')) {
    /**
     * Generate URL block explorer untuk Bitcoin transaction.
     */
    function ots_block_explorer_url(?string $txid): ?string
    {
        if (! $txid) {
            return null;
        }

        return "https://www.blockchain.com/btc/tx/{$txid}";
    }
}
