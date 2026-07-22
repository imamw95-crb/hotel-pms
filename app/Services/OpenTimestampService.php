<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InvoiceTimestamp;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Repositories\InvoiceTimestampRepository;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Layanan utama untuk integrasi OpenTimestamps (OTS).
 *
 * Bertanggung jawab atas:
 * - Pembuatan timestamp baru untuk invoice/transaksi
 * - Verifikasi integritas dokumen
 * - Upgrade proof ke blockchain Bitcoin
 * - Manajemen revision
 *
 * @see https://opentimestamps.org/
 */
class OpenTimestampService
{
    private const OTS_STAMP_TIMEOUT = 30;
    private const OTS_UPGRADE_TIMEOUT = 120;

    public function __construct(
        private readonly InvoiceTimestampRepository $repository,
    ) {}

    // ══════════════════════════════════════════════════════════════════════
    //  PUBLIC API — TIMESTAMP
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Timestamp sebuah invoice reservasi.
     *
     * @param  Reservation  $reservation  Model reservasi
     * @param  int|null     $revision     Revision spesifik (null=auto)
     * @return InvoiceTimestamp           Record timestamp yang dibuat
     *
     * @throws RuntimeException Jika gagal membuat timestamp
     */
    public function timestampInvoice(Reservation $reservation, ?int $revision = null): InvoiceTimestamp
    {
        $data = $this->buildInvoiceData($reservation);
        $sha256 = hash('sha256', json_encode($data));

        // Cek apakah hash ini sudah pernah di-timestamp
        $existing = $this->repository->findLatestRevision(
            $reservation->id,
            'reservation'
        );

        if ($existing && $existing->sha256 === $sha256) {
            Log::info('OTS: Invoice already timestamped with same hash, skipping', [
                'reservation' => $reservation->reservation_number,
                'sha256' => $sha256,
                'revision' => $existing->revision,
            ]);

            return $existing;
        }

        $calendar = $this->getCalendarUrl();

        // Buat record timestamp baru
        $timestamp = $this->repository->create([
            'invoice_id' => $reservation->id,
            'invoice_type' => 'reservation',
            'sha256' => $sha256,
            'revision' => $revision,
            'calendar' => $calendar,
            'ots_status' => InvoiceTimestamp::STATUS_PENDING,
            'timestamped_at' => now(),
        ]);

        // Jalankan OTS stamp secara asynchronous (submit ke calendar)
        $this->runOtsStamp($timestamp);

        // ── Backward compatibility: simpan juga di JSON kolom ──
        $this->saveLegacyProof($reservation, $sha256, 'issued', $timestamp);

        Log::info('OTS: Invoice timestamp created', [
            'reservation' => $reservation->reservation_number,
            'sha256' => $sha256,
            'revision' => $timestamp->revision,
            'id' => $timestamp->id,
        ]);

        return $timestamp;
    }

    /**
     * Timestamp sebuah transaksi pembayaran.
     */
    public function timestampTransaction(Transaction $transaction, ?int $revision = null): InvoiceTimestamp
    {
        $data = $this->buildTransactionData($transaction);
        $sha256 = hash('sha256', json_encode($data));

        $existing = $this->repository->findLatestRevision(
            $transaction->id,
            'transaction'
        );

        if ($existing && $existing->sha256 === $sha256) {
            return $existing;
        }

        $calendar = $this->getCalendarUrl();

        $timestamp = $this->repository->create([
            'invoice_id' => $transaction->id,
            'invoice_type' => 'transaction',
            'sha256' => $sha256,
            'revision' => $revision,
            'calendar' => $calendar,
            'ots_status' => InvoiceTimestamp::STATUS_PENDING,
            'timestamped_at' => now(),
        ]);

        $this->runOtsStamp($timestamp);

        // ── Backward compatibility ──
        $this->saveLegacyTransactionProof($transaction, $sha256, $timestamp);

        Log::info('OTS: Transaction timestamp created', [
            'transaction' => $transaction->transaction_number,
            'sha256' => $sha256,
            'revision' => $timestamp->revision,
            'id' => $timestamp->id,
        ]);

        return $timestamp;
    }

    /**
     * Hapus / reset OTS proof untuk invoice.
     * Tidak menghapus record lama dari database — hanya buat revision baru.
     * Method ini dipanggil saat data invoice berubah (total, tanggal, dll).
     */
    public function resetInvoiceProof(Reservation $reservation): void
    {
        // Buat revision baru dengan hash baru
        try {
            $this->createRevision($reservation);

            Log::info('OTS: Invoice proof reset (new revision created)', [
                'reservation' => $reservation->reservation_number,
            ]);
        } catch (\Exception $e) {
            Log::error('OTS: Failed to reset invoice proof', [
                'reservation' => $reservation->reservation_number,
                'error' => $e->getMessage(),
            ]);
        }

        // ── Backward compatibility: hapus legacy proof ──
        $reservation->ots_proof = null;
        $reservation->ots_timestamped_at = null;
        $reservation->saveQuietly();
    }

    /**
     * Hapus / reset OTS proof untuk transaksi.
     */
    public function resetTransactionProof(Transaction $transaction): void
    {
        try {
            $nextRev = $this->repository->getNextRevision($transaction->id, 'transaction');
            $this->timestampTransaction($transaction, $nextRev);

            Log::info('OTS: Transaction proof reset (new revision created)', [
                'transaction' => $transaction->transaction_number,
            ]);
        } catch (\Exception $e) {
            Log::error('OTS: Failed to reset transaction proof', [
                'transaction' => $transaction->transaction_number,
                'error' => $e->getMessage(),
            ]);
        }

        // ── Backward compatibility ──
        $transaction->ots_proof = null;
        $transaction->ots_timestamped_at = null;
        $transaction->saveQuietly();
    }

    /**
     * Buat revision baru untuk invoice.
     * Tidak menghapus proof lama.
     */
    public function createRevision(Reservation $reservation): InvoiceTimestamp
    {
        $nextRev = $this->repository->getNextRevision($reservation->id, 'reservation');

        Log::info('OTS: Creating new revision for invoice', [
            'reservation' => $reservation->reservation_number,
            'revision' => $nextRev,
        ]);

        return $this->timestampInvoice($reservation, $nextRev);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PUBLIC API — VERIFICATION
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Verifikasi integritas invoice dengan membandingkan hash.
     *
     * @param  Reservation  $reservation  Model reservasi
     * @param  int|null     $revision     Revision spesifik (null=terbaru)
     * @return array{verified: bool, status: string, message: string, timestamp: array|null}
     */
    public function verifyInvoice(Reservation $reservation, ?int $revision = null): array
    {
        $timestamp = $revision !== null
            ? $this->repository->findByRevision($reservation->id, $revision, 'reservation')
            : $this->repository->findLatestRevision($reservation->id, 'reservation');

        if (! $timestamp) {
            // Fallback ke legacy proof
            return $this->verifyLegacy($reservation->ots_proof, 'invoice');
        }

        return $this->verifyHash($reservation, $timestamp, 'reservation');
    }

    /**
     * Verifikasi integritas transaksi.
     */
    public function verifyTransaction(Transaction $transaction, ?int $revision = null): array
    {
        $timestamp = $revision !== null
            ? $this->repository->findByRevision($transaction->id, $revision, 'transaction')
            : $this->repository->findLatestRevision($transaction->id, 'transaction');

        if (! $timestamp) {
            return $this->verifyLegacy($transaction->ots_proof, 'transaction');
        }

        return $this->verifyHash($transaction, $timestamp, 'transaction');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PUBLIC API — PROOF UPGRADE
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Upgrade proof: submit .ots ke calendar untuk mendapatkan konfirmasi
     * blockchain. Panggil dari cron job.
     *
     * @param  InvoiceTimestamp  $timestamp  Record yang akan di-upgrade
     * @return array{success: bool, message: string, data?: array}
     */
    public function upgradeProof(InvoiceTimestamp $timestamp): array
    {
        if ($timestamp->is_confirmed) {
            return [
                'success' => true,
                'message' => 'Proof sudah terkonfirmasi.',
            ];
        }

        // Jika belum punya file .ots, buat dulu
        $otsFileContent = $timestamp->ots_file;

        if (! $otsFileContent) {
            // Coba stamp ulang
            $result = $this->runOtsStamp($timestamp);
            if (! $result['success']) {
                return $result;
            }
            $timestamp->refresh();
            $otsFileContent = $timestamp->ots_file;
        }

        if (! $otsFileContent) {
            return [
                'success' => false,
                'message' => 'File .ots tidak tersedia.',
            ];
        }

        // Simpan .ots ke temp file untuk upgrade
        $tmpDir = sys_get_temp_dir();
        $otsFilePath = $tmpDir . '/ots_upgrade_' . $timestamp->id . '.ots';
        file_put_contents($otsFilePath, base64_decode($otsFileContent));

        try {
            $otsBin = $this->getOtsBinPath();

            // Upgrade: submit ke calendar untuk mendapatkan konfirmasi
            $cmd = sprintf(
                '%s upgrade %s 2>&1',
                escapeshellcmd($otsBin),
                escapeshellarg($otsFilePath)
            );

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                // Mungkin masih pending, coba info
                $infoResult = $this->getOtsInfo($otsFilePath);
                $this->repository->updateStatus(
                    $timestamp,
                    InvoiceTimestamp::STATUS_CONFIRMING,
                    otsFile: base64_encode(file_get_contents($otsFilePath))
                );

                return [
                    'success' => true,
                    'message' => 'Proof masih dalam proses upgrade. ' . ($infoResult['info'] ?? ''),
                    'info' => $infoResult['info'] ?? null,
                ];
            }

            // Upgrade sukses — baca info
            $infoResult = $this->getOtsInfo($otsFilePath);
            $updatedOtsFile = base64_encode(file_get_contents($otsFilePath));

            // Parse informasi dari output
            $txid = $infoResult['txid'] ?? null;
            $block = $infoResult['block'] ?? null;
            $blockHash = $infoResult['block_hash'] ?? null;

            if ($txid) {
                // ✅ Sudah terkonfirmasi di Bitcoin blockchain
                $this->repository->updateStatus(
                    $timestamp,
                    InvoiceTimestamp::STATUS_CONFIRMED,
                    bitcoinTxid: $txid,
                    bitcoinBlock: $block,
                    bitcoinBlockHash: $blockHash,
                    otsFile: $updatedOtsFile,
                );

                Log::info('OTS: Proof confirmed on Bitcoin', [
                    'id' => $timestamp->id,
                    'sha256' => $timestamp->sha256,
                    'txid' => $txid,
                    'block' => $block,
                ]);

                // ── Backward compatibility ──
                $this->updateLegacyProof($timestamp);

                return [
                    'success' => true,
                    'message' => 'Proof berhasil dikonfirmasi di Bitcoin Blockchain.',
                    'txid' => $txid,
                    'block' => $block,
                ];
            }

            // ⏳ Upgrade sukses tapi belum masuk block Bitcoin — set confirming untuk retry nanti
            $this->repository->updateStatus(
                $timestamp,
                InvoiceTimestamp::STATUS_CONFIRMING,
                otsFile: $updatedOtsFile,
            );

            Log::info('OTS: Proof submitted to calendar, awaiting Bitcoin block', [
                'id' => $timestamp->id,
                'sha256' => $timestamp->sha256,
                'info' => $infoResult['info'] ?? '',
            ]);

            return [
                'success' => true,
                'message' => 'Proof telah dikirim ke calendar, menunggu konfirmasi block Bitcoin. Cron job akan coba lagi nanti.',
                'info' => $infoResult['info'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('OTS: Upgrade proof failed', [
                'id' => $timestamp->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal upgrade proof: ' . $e->getMessage(),
            ];
        } finally {
            // Cleanup temp file
            if (file_exists($otsFilePath)) {
                unlink($otsFilePath);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PUBLIC API — DOWNLOAD
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Dapatkan file .ts untuk didownload.
     *
     * @return array{content: string, filename: string}|null
     */
    public function downloadOtsFile(InvoiceTimestamp $timestamp): ?array
    {
        if (! $timestamp->ots_file) {
            return null;
        }

        return [
            'content' => base64_decode($timestamp->ots_file),
            'filename' => $timestamp->ots_filename,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PROTECTED — OTS CLI OPERATIONS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Jalankan `ots stamp` untuk mengirim hash ke calendar.
     */
    protected function runOtsStamp(InvoiceTimestamp $timestamp): array
    {
        $otsBin = $this->getOtsBinPath();
        $tmpDir = sys_get_temp_dir();
        $digestFile = $tmpDir . '/ots_digest_' . $timestamp->id . '.txt';
        $otsFilePath = $digestFile . '.ots';

        try {
            // Tulis hash ke file
            file_put_contents($digestFile, $timestamp->sha256);

            // Jalankan ots stamp
            $cmd = sprintf(
                '%s stamp %s 2>&1',
                escapeshellcmd($otsBin),
                escapeshellarg($digestFile)
            );

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && file_exists($otsFilePath)) {
                $otsContent = base64_encode(file_get_contents($otsFilePath));

                // Baca info dari ots
                $infoResult = $this->getOtsInfo($otsFilePath);

                $this->repository->updateOtsFile($timestamp, $otsContent);

                Log::info('OTS: Stamp successful', [
                    'id' => $timestamp->id,
                    'sha256' => $timestamp->sha256,
                    'info' => $infoResult['info'] ?? '',
                ]);

                return [
                    'success' => true,
                    'message' => 'OTS stamp berhasil.',
                    'info' => $infoResult['info'] ?? null,
                ];
            }

            Log::warning('OTS: Stamp command returned non-zero', [
                'id' => $timestamp->id,
                'output' => $output,
                'returnVar' => $returnVar,
            ]);

            return [
                'success' => false,
                'message' => 'OTS stamp gagal: ' . implode("\n", $output),
            ];
        } catch (\Exception $e) {
            Log::error('OTS: Stamp exception', [
                'id' => $timestamp->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'OTS stamp exception: ' . $e->getMessage(),
            ];
        } finally {
            // Cleanup
            if (file_exists($digestFile)) {
                unlink($digestFile);
            }
            // Keep .ots file for now (it's in DB as base64)
            if (file_exists($otsFilePath)) {
                unlink($otsFilePath);
            }
        }
    }

    /**
     * Baca informasi dari file .ots menggunakan `ots info`.
     *
     * @return array{info?: string, txid?: string, block?: int, block_hash?: string}
     */
    protected function getOtsInfo(string $otsFilePath): array
    {
        $otsBin = $this->getOtsBinPath();

        $cmd = sprintf(
            '%s info %s 2>&1',
            escapeshellcmd($otsBin),
            escapeshellarg($otsFilePath)
        );

        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        $info = implode("\n", $output);
        $result = ['info' => $info];

        // Parse Bitcoin timestamp information if available
        foreach ($output as $line) {
            // Contoh output:
            // Bitcoin transaction: 123abc...
            // Bitcoin block: 876543
            if (preg_match('/Bitcoin transaction:\s*(\S+)/i', $line, $m)) {
                $result['txid'] = $m[1];
            }
            if (preg_match('/Bitcoin block:\s*(\d+)/i', $line, $m)) {
                $result['block'] = (int) $m[1];
            }
            if (preg_match('/Block hash:\s*(\S+)/i', $line, $m)) {
                $result['block_hash'] = $m[1];
            }
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PROTECTED — DATA BUILDERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Bangun data invoice untuk dijadikan SHA-256 hash.
     * Data harus deterministic (urutan key fixed).
     */
    protected function buildInvoiceData(Reservation $reservation): array
    {
        return [
            'reservation_number' => $reservation->reservation_number,
            'guest_name' => $reservation->guest?->guest_name,
            'room_number' => $reservation->room?->room_number,
            'check_in' => $reservation->check_in?->format('Y-m-d H:i'),
            'check_out' => $reservation->check_out?->format('Y-m-d H:i'),
            'nights' => $reservation->nights,
            'total_amount' => $reservation->total_amount,
            'paid_amount' => $reservation->paid_amount,
            'status' => $reservation->status,
            'paid_date' => $reservation->paid_date?->format('Y-m-d H:i'),
        ];
    }

    /**
     * Bangun data transaksi untuk dijadikan SHA-256 hash.
     */
    protected function buildTransactionData(Transaction $transaction): array
    {
        return [
            'transaction_number' => $transaction->transaction_number,
            'reservation_number' => $transaction->reservation?->reservation_number,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method,
            'notes' => $transaction->notes,
            'created_at' => $transaction->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PROTECTED — VERIFICATION HELPERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Verifikasi hash antara data terkini dengan timestamp yang tersimpan.
     *
     * @param  Reservation|Transaction $model
     */
    protected function verifyHash($model, InvoiceTimestamp $timestamp, string $type): array
    {
        $currentData = $type === 'reservation'
            ? $this->buildInvoiceData($model)
            : $this->buildTransactionData($model);

        $currentHash = hash('sha256', json_encode($currentData));
        $match = hash_equals($timestamp->sha256, $currentHash);

        $otsStatus = $timestamp->ots_status; // pending | confirming | confirmed | failed

        return [
            'verified' => $match,
            'status' => $match
                ? ($otsStatus === InvoiceTimestamp::STATUS_CONFIRMED ? 'verified' : $otsStatus)
                : 'tampered',
            'message' => $match
                ? match ($otsStatus) {
                    InvoiceTimestamp::STATUS_CONFIRMED => 'Dokumen telah di-timestamp blockchain dan tidak berubah.',
                    InvoiceTimestamp::STATUS_CONFIRMING => 'Proof telah dikirim ke calendar, menunggu konfirmasi block Bitcoin.',
                    InvoiceTimestamp::STATUS_FAILED => 'Proof gagal dikonfirmasi, akan dicoba ulang oleh sistem.',
                    default => 'Hash cocok, menunggu proses stamping ke blockchain.',
                }
                : 'DATA TELAH BERUBAH SEJAK DI-TIMESTAMP!',
            'timestamp' => [
                'id' => $timestamp->id,
                'revision' => $timestamp->revision,
                'sha256' => $timestamp->sha256,
                'ots_status' => $timestamp->ots_status,
                'calendar' => $timestamp->calendar,
                'bitcoin_txid' => $timestamp->bitcoin_txid,
                'bitcoin_block' => $timestamp->bitcoin_block,
                'bitcoin_block_hash' => $timestamp->bitcoin_block_hash,
                'timestamped_at' => $timestamp->timestamped_at?->toIso8601String(),
                'confirmed_at' => $timestamp->confirmed_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * Fallback: verifikasi dari legacy JSON proof (kolom ots_proof).
     */
    protected function verifyLegacy(?string $otsProof, string $type): array
    {
        if (! $otsProof) {
            return [
                'verified' => false,
                'status' => 'no_proof',
                'message' => 'Belum di-timestamp OTS.',
                'timestamp' => null,
            ];
        }

        $stored = json_decode($otsProof, true);
        if (! $stored || ! isset($stored['hash'])) {
            return [
                'verified' => false,
                'status' => 'invalid_proof',
                'message' => 'Data OTS proof rusak.',
                'timestamp' => null,
            ];
        }

        return [
            'verified' => isset($stored['verified']) ? (bool) $stored['verified'] : false,
            'status' => isset($stored['verified']) && $stored['verified'] ? 'verified' : 'pending',
            'message' => 'Menggunakan legacy proof. Buat timestamp baru untuk upgrade.',
            'timestamp' => $stored,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PROTECTED — BACKWARD COMPATIBILITY
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Simpan proof ke kolom legacy (ots_proof JSON) di tabel reservations.
     */
    protected function saveLegacyProof(
        Reservation $reservation,
        string $sha256,
        string $context,
        InvoiceTimestamp $timestamp,
    ): void {
        $reservation->ots_proof = json_encode([
            'hash' => $sha256,
            'context' => $context,
            'revision' => $timestamp->revision,
            'calendar' => $timestamp->calendar,
            'ots_status' => $timestamp->ots_status,
            'created_at' => $timestamp->timestamped_at?->toIso8601String(),
        ]);
        $reservation->ots_timestamped_at = $timestamp->timestamped_at;
        $reservation->saveQuietly();
    }

    /**
     * Simpan proof ke kolom legacy di tabel transactions.
     */
    protected function saveLegacyTransactionProof(
        Transaction $transaction,
        string $sha256,
        InvoiceTimestamp $timestamp,
    ): void {
        $transaction->ots_proof = json_encode([
            'hash' => $sha256,
            'context' => 'payment',
            'revision' => $timestamp->revision,
            'calendar' => $timestamp->calendar,
            'ots_status' => $timestamp->ots_status,
            'created_at' => $timestamp->timestamped_at?->toIso8601String(),
        ]);
        $transaction->ots_timestamped_at = $timestamp->timestamped_at;
        $transaction->saveQuietly();
    }

    /**
     * Update legacy proof setelah upgrade sukses.
     */
    protected function updateLegacyProof(InvoiceTimestamp $timestamp): void
    {
        if ($timestamp->invoice_type === 'reservation') {
            $reservation = Reservation::find($timestamp->invoice_id);
            if ($reservation && $reservation->ots_proof) {
                $proof = json_decode($reservation->ots_proof, true) ?? [];
                $proof['ots_status'] = InvoiceTimestamp::STATUS_CONFIRMED;
                $proof['bitcoin_txid'] = $timestamp->bitcoin_txid;
                $proof['bitcoin_block'] = $timestamp->bitcoin_block;
                $proof['confirmed_at'] = $timestamp->confirmed_at?->toIso8601String();
                $reservation->ots_proof = json_encode($proof);
                $reservation->saveQuietly();
            }
        } elseif ($timestamp->invoice_type === 'transaction') {
            $transaction = Transaction::find($timestamp->invoice_id);
            if ($transaction && $transaction->ots_proof) {
                $proof = json_decode($transaction->ots_proof, true) ?? [];
                $proof['ots_status'] = InvoiceTimestamp::STATUS_CONFIRMED;
                $proof['bitcoin_txid'] = $timestamp->bitcoin_txid;
                $proof['bitcoin_block'] = $timestamp->bitcoin_block;
                $proof['confirmed_at'] = $timestamp->confirmed_at?->toIso8601String();
                $transaction->ots_proof = json_encode($proof);
                $transaction->saveQuietly();
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PROTECTED — HELPERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Dapatkan path binary OTS CLI dari config.
     */
    protected function getOtsBinPath(): string
    {
        return config('services.opentimestamps.bin_path', 'ots');
    }

    /**
     * Dapatkan URL calendar OTS dari config.
     */
    protected function getCalendarUrl(): string
    {
        return config('services.opentimestamps.calendar', 'https://a.pool.opentimestamps.org');
    }
}
