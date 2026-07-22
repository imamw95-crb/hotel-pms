<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\InvoiceTimestamp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Repository untuk mengelola data invoice_timestamp.
 *
 * Mengikuti Repository Pattern untuk abstraksi akses data,
 * memudahkan testing dan penggantian implementation di masa depan.
 */
class InvoiceTimestampRepository
{
    /**
     * Cari berdasarkan ID.
     */
    public function findById(int $id): ?InvoiceTimestamp
    {
        return InvoiceTimestamp::find($id);
    }

    /**
     * Cari berdasarkan invoice_id + invoice_type, urut revision DESC.
     *
     * @return Collection|InvoiceTimestamp[]
     */
    public function findByInvoice(int $invoiceId, string $invoiceType = 'reservation'): Collection
    {
        return InvoiceTimestamp::forInvoice($invoiceId, $invoiceType)
            ->orderByDesc('revision')
            ->get();
    }

    /**
     * Ambil revision terbaru.
     */
    public function findLatestRevision(int $invoiceId, string $invoiceType = 'reservation'): ?InvoiceTimestamp
    {
        return InvoiceTimestamp::latestRevision($invoiceId, $invoiceType)->first();
    }

    /**
     * Cari berdasarkan revision tertentu.
     */
    public function findByRevision(int $invoiceId, int $revision, string $invoiceType = 'reservation'): ?InvoiceTimestamp
    {
        return InvoiceTimestamp::forInvoice($invoiceId, $invoiceType)
            ->where('revision', $revision)
            ->first();
    }

    /**
     * Hitung jumlah revision yang ada.
     */
    public function countRevisions(int $invoiceId, string $invoiceType = 'reservation'): int
    {
        return InvoiceTimestamp::forInvoice($invoiceId, $invoiceType)->count();
    }

    /**
     * Buat timestamp record baru.
     *
     * @param array{
     *     invoice_id: int,
     *     invoice_type?: string,
     *     sha256: string,
     *     revision?: int,
     *     calendar?: string|null,
     *     ots_status?: string,
     * } $data
     */
    public function create(array $data): InvoiceTimestamp
    {
        return DB::transaction(function () use ($data) {
            // Tentukan revision secara otomatis
            if (! isset($data['revision'])) {
                $data['revision'] = $this->getNextRevision(
                    $data['invoice_id'],
                    $data['invoice_type'] ?? 'reservation'
                );
            }

            $data['ots_status'] ??= InvoiceTimestamp::STATUS_PENDING;

            return InvoiceTimestamp::create($data);
        });
    }

    /**
     * Update status OTS setelah upgrade proof.
     */
    public function updateStatus(
        InvoiceTimestamp $timestamp,
        string $otsStatus,
        ?string $bitcoinTxid = null,
        ?int $bitcoinBlock = null,
        ?string $bitcoinBlockHash = null,
        ?string $otsFile = null,
    ): InvoiceTimestamp {
        $updateData = [
            'ots_status' => $otsStatus,
        ];

        if ($bitcoinTxid !== null) {
            $updateData['bitcoin_txid'] = $bitcoinTxid;
        }
        if ($bitcoinBlock !== null) {
            $updateData['bitcoin_block'] = $bitcoinBlock;
        }
        if ($bitcoinBlockHash !== null) {
            $updateData['bitcoin_block_hash'] = $bitcoinBlockHash;
        }
        if ($otsFile !== null) {
            $updateData['ots_file'] = $otsFile;
        }

        if ($otsStatus === InvoiceTimestamp::STATUS_CONFIRMED) {
            $updateData['confirmed_at'] = now();
        }

        $timestamp->update($updateData);
        $timestamp->refresh();

        return $timestamp;
    }

    /**
     * Update file OTS proof (base64).
     */
    public function updateOtsFile(InvoiceTimestamp $timestamp, string $base64Ots): InvoiceTimestamp
    {
        $timestamp->update(['ots_file' => $base64Ots]);
        $timestamp->refresh();

        return $timestamp;
    }

    /**
     * Hapus record timestamp tertentu.
     */
    public function delete(InvoiceTimestamp $timestamp): bool
    {
        return $timestamp->delete();
    }

    /**
     * Hapus semua timestamp untuk suatu invoice.
     */
    public function deleteByInvoice(int $invoiceId, string $invoiceType = 'reservation'): int
    {
        return InvoiceTimestamp::forInvoice($invoiceId, $invoiceType)->delete();
    }

    /**
     * Ambil semua timestamp yang masih pending atau confirming untuk di-upgrade.
     * Termasuk confirming karena perlu dicek ulang apakah sudah masuk block Bitcoin.
     */
    public function getPendingTimestamps(int $limit = 50): Collection
    {
        return InvoiceTimestamp::whereIn('ots_status', [
                InvoiceTimestamp::STATUS_PENDING,
                InvoiceTimestamp::STATUS_CONFIRMING,
            ])
            ->orderBy('timestamped_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Ambil semua timestamp yang gagal untuk dicoba ulang.
     */
    public function getFailedTimestamps(int $limit = 20): Collection
    {
        return InvoiceTimestamp::where('ots_status', InvoiceTimestamp::STATUS_FAILED)
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Cek apakah ada timestamp yang masih pending untuk invoice tertentu.
     */
    public function hasPendingTimestamp(int $invoiceId, string $invoiceType = 'reservation'): bool
    {
        return InvoiceTimestamp::forInvoice($invoiceId, $invoiceType)
            ->pending()
            ->exists();
    }

    /**
     * Dapatkan nomor revision berikutnya untuk invoice.
     */
    public function getNextRevision(int $invoiceId, string $invoiceType = 'reservation'): int
    {
        $latest = $this->findLatestRevision($invoiceId, $invoiceType);

        return $latest ? $latest->revision + 1 : 0;
    }

    /**
     * Simpan file .ots ke storage (jika tidak disimpan sebagai BLOB di DB).
     *
     * @return string Path relatif file yang disimpan
     */
    public function storeOtsFile(string $base64Content, string $filename): string
    {
        $binaryData = base64_decode($base64Content, true);

        if ($binaryData === false) {
            throw new RuntimeException('Invalid base64 OTS file content');
        }

        $relativePath = 'ots/' . $filename;
        $fullPath = storage_path('app/' . $relativePath);

        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $bytesWritten = file_put_contents($fullPath, $binaryData);

        if ($bytesWritten === false) {
            throw new RuntimeException('Failed to write OTS file to storage');
        }

        return $relativePath;
    }

    /**
     * Baca file .ots dari storage.
     */
    public function readOtsFile(string $relativePath): ?string
    {
        $fullPath = storage_path('app/' . $relativePath);

        if (! file_exists($fullPath)) {
            return null;
        }

        return base64_encode(file_get_contents($fullPath));
    }
}
