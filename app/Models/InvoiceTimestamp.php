<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\InvoiceTimestamp
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $invoice_type reservation, transaction
 * @property int $revision
 * @property string $sha256 SHA-256 hash of the invoice data
 * @property string|null $ots_file Base64-encoded .ots binary proof file
 * @property string $ots_status pending, confirming, confirmed, failed
 * @property string|null $calendar OpenTimestamps calendar URL
 * @property string|null $bitcoin_txid Bitcoin transaction ID
 * @property int|null $bitcoin_block Bitcoin block number
 * @property string|null $bitcoin_block_hash Bitcoin block hash
 * @property Carbon|null $timestamped_at When the OTS timestamp was created
 * @property Carbon|null $confirmed_at When confirmed on blockchain
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|InvoiceTimestamp pending()
 * @method static Builder|InvoiceTimestamp confirmed()
 * @method static Builder|InvoiceTimestamp forInvoice(int $invoiceId, string $invoiceType = 'reservation')
 * @method static Builder|InvoiceTimestamp latestRevision(int $invoiceId, string $invoiceType = 'reservation')
 */
class InvoiceTimestamp extends Model
{
    protected $table = 'invoice_timestamps';

    protected $fillable = [
        'invoice_id',
        'invoice_type',
        'revision',
        'sha256',
        'ots_file',
        'ots_status',
        'calendar',
        'bitcoin_txid',
        'bitcoin_block',
        'bitcoin_block_hash',
        'timestamped_at',
        'confirmed_at',
    ];

    protected $casts = [
        'revision' => 'integer',
        'invoice_id' => 'integer',
        'bitcoin_block' => 'integer',
        'timestamped_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    // ─── Status Constants ───────────────────────────────────────────────

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMING = 'confirming';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_FAILED = 'failed';

    public const ALLOWED_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMING,
        self::STATUS_CONFIRMED,
        self::STATUS_FAILED,
    ];

    // ─── Scopes ─────────────────────────────────────────────────────────

    /**
     * Scope: hanya yang masih pending (belum dikonfirmasi blockchain).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('ots_status', self::STATUS_PENDING);
    }

    /**
     * Scope: hanya yang sudah confirmed.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('ots_status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope: cari berdasarkan invoice ID dan tipe.
     */
    public function scopeForInvoice(Builder $query, int $invoiceId, string $invoiceType = 'reservation'): Builder
    {
        return $query->where('invoice_id', $invoiceId)
            ->where('invoice_type', $invoiceType);
    }

    /**
     * Scope: ambil revision terbaru untuk suatu invoice.
     */
    public function scopeLatestRevision(Builder $query, int $invoiceId, string $invoiceType = 'reservation'): Builder
    {
        return $query->forInvoice($invoiceId, $invoiceType)
            ->orderByDesc('revision')
            ->limit(1);
    }

    // ─── Accessors ──────────────────────────────────────────────────────

    /**
     * Apakah status sudah confirmed di blockchain.
     */
    public function getIsConfirmedAttribute(): bool
    {
        return $this->ots_status === self::STATUS_CONFIRMED;
    }

    /**
     * Label status dalam Bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->ots_status) {
            self::STATUS_PENDING => 'Menunggu Konfirmasi',
            self::STATUS_CONFIRMING => 'Sedang Dikonfirmasi',
            self::STATUS_CONFIRMED => 'Terkonfirmasi',
            self::STATUS_FAILED => 'Gagal',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Nama file .ots untuk didownload.
     */
    public function getOtsFilenameAttribute(): string
    {
        return $this->invoice_type . '_' . $this->invoice_id
            . '_rev' . $this->revision . '.ots';
    }
}
