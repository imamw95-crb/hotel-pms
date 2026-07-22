<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class OpenTimestampService
{
    /**
     * Generate OTS proof untuk invoice reservation.
     * Simpan .ots proof (base64) + timestamp ke DB.
     */
    public function timestampInvoice(Reservation $reservation, string $context = 'issued'): bool
    {
        try {
            $data = $this->buildInvoiceData($reservation, $context);
            $hash = hash('sha256', json_encode($data));
            $proof = $this->createProof($hash, $context);

            $reservation->ots_proof = json_encode([
                'hash' => $hash,
                'context' => $context,
                'proof' => $proof,
                'created_at' => now()->toIso8601String(),
            ]);
            $reservation->ots_timestamped_at = now();
            $reservation->saveQuietly();

            Log::info("OTS: Invoice {$reservation->reservation_number} timestamped ({$context})", [
                'hash' => $hash,
                'reservation_id' => $reservation->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("OTS: Failed to timestamp invoice {$reservation->reservation_number}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate OTS proof untuk transaksi pembayaran (DP/Pelunasan/Tambahan).
     */
    public function timestampTransaction(Transaction $transaction): bool
    {
        try {
            $data = $this->buildTransactionData($transaction);
            $hash = hash('sha256', json_encode($data));
            $proof = $this->createProof($hash, 'payment');

            $transaction->ots_proof = json_encode([
                'hash' => $hash,
                'context' => 'payment',
                'proof' => $proof,
                'created_at' => now()->toIso8601String(),
            ]);
            $transaction->ots_timestamped_at = now();
            $transaction->saveQuietly();

            Log::info("OTS: Transaction {$transaction->transaction_number} timestamped", [
                'hash' => $hash,
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'type' => $transaction->type,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("OTS: Failed to timestamp transaction {$transaction->transaction_number}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verifikasi OTS proof — cocokkan hash dengan data terkini.
     */
    public function verifyInvoice(Reservation $reservation): array
    {
        if (!$reservation->ots_proof) {
            return [
                'verified' => false,
                'status' => 'no_proof',
                'message' => 'Belum di-timestamp OTS.',
            ];
        }

        $stored = json_decode($reservation->ots_proof, true);
        if (!$stored || !isset($stored['hash'])) {
            return [
                'verified' => false,
                'status' => 'invalid_proof',
                'message' => 'Data OTS proof rusak.',
            ];
        }

        // Hitung hash dari data saat ini
        $currentHash = hash('sha256', json_encode(
            $this->buildInvoiceData($reservation, $stored['context'] ?? 'issued')
        ));

        $match = hash_equals($stored['hash'], $currentHash);

        return [
            'verified' => $match,
            'status' => $match ? 'verified' : 'tampered',
            'timestamped_at' => $reservation->ots_timestamped_at,
            'context' => $stored['context'] ?? 'unknown',
            'message' => $match
                ? 'Dokumen telah di-timestamp dan tidak berubah sejak diterbitkan.'
                : 'Data telah berubah sejak di-timestamp!',
        ];
    }

    /**
     * Verifikasi OTS proof untuk transaksi.
     */
    public function verifyTransaction(Transaction $transaction): array
    {
        if (!$transaction->ots_proof) {
            return [
                'verified' => false,
                'status' => 'no_proof',
                'message' => 'Belum di-timestamp OTS.',
            ];
        }

        $stored = json_decode($transaction->ots_proof, true);
        if (!$stored || !isset($stored['hash'])) {
            return [
                'verified' => false,
                'status' => 'invalid_proof',
                'message' => 'Data OTS proof rusak.',
            ];
        }

        $currentHash = hash('sha256', json_encode($this->buildTransactionData($transaction)));
        $match = hash_equals($stored['hash'], $currentHash);

        return [
            'verified' => $match,
            'status' => $match ? 'verified' : 'tampered',
            'timestamped_at' => $transaction->ots_timestamped_at,
            'message' => $match
                ? 'Bukti pembayaran telah di-timestamp dan tidak berubah.'
                : 'Data bukti pembayaran telah berubah sejak di-timestamp!',
        ];
    }

    /**
     * Reset OTS proof — panggil saat data invoice berubah.
     */
    public function resetInvoiceProof(Reservation $reservation): void
    {
        $reservation->ots_proof = null;
        $reservation->ots_timestamped_at = null;
        $reservation->saveQuietly();

        Log::info("OTS: Invoice {$reservation->reservation_number} proof reset");
    }

    /**
     * Reset OTS proof untuk transaksi.
     */
    public function resetTransactionProof(Transaction $transaction): void
    {
        $transaction->ots_proof = null;
        $transaction->ots_timestamped_at = null;
        $transaction->saveQuietly();

        Log::info("OTS: Transaction {$transaction->transaction_number} proof reset");
    }

    /**
     * Buat data invoice untuk di-hash.
     */
    protected function buildInvoiceData(Reservation $reservation, string $context = 'issued'): array
    {
        $data = [
            'context' => $context,
            'reservation_number' => $reservation->reservation_number,
            'total_amount' => $reservation->total_amount,
            'paid_amount' => $reservation->paid_amount,
            'guest_name' => $reservation->guest?->guest_name,
            'room_number' => $reservation->room?->room_number,
            'check_in' => $reservation->check_in?->format('Y-m-d H:i'),
            'check_out' => $reservation->check_out?->format('Y-m-d H:i'),
            'nights' => $reservation->nights,
            'status' => $reservation->status,
        ];

        // Jika final (paid), sertakan paid_date
        if ($context === 'final' && $reservation->paid_date) {
            $data['paid_date'] = $reservation->paid_date->format('Y-m-d H:i');
            $data['paid_amount'] = $reservation->paid_amount;
        }

        return $data;
    }

    /**
     * Buat data transaksi untuk di-hash.
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

    /**
     * Buat proof OTS menggunakan OTS CLI.
     * Hash dikirim ke blockchain calendar OpenTimestamps.
     */
    protected function createProof(string $hash, string $context): array
    {
        $otsBin = config('services.opentimestamps.bin_path', 'ots');
        $calendar = config('services.opentimestamps.calendar', 'https://a.pool.opentimestamps.org');
        $proof = [
            'algorithm' => 'SHA-256',
            'hash' => $hash,
            'timestamp' => now()->toIso8601String(),
            'ots_version' => 'pending',
            'calendar' => $calendar,
            'note' => 'OTS CLI integration pending — hash siap dikirim ke blockchain.',
        ];

        // Coba submit ke OTS CLI
        try {
            $tmpDir = sys_get_temp_dir();
            $digestFile = $tmpDir . '/ots_' . $hash . '.txt';
            $otsFile = $digestFile . '.ots';

            // Tulis hash ke file
            file_put_contents($digestFile, $hash);

            // Jalankan ots stamp
            $cmd = sprintf('%s stamp -c %s %s 2>&1', escapeshellcmd($otsBin), escapeshellarg($calendar), escapeshellarg($digestFile));
            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && file_exists($otsFile)) {
                $otsContent = base64_encode(file_get_contents($otsFile));
                $proof['ots_version'] = 'confirmed';
                $proof['proof_file'] = $otsContent;
                $proof['note'] = 'OTS proof confirmed — hash telah dikirim ke blockchain calendar.';

                // Baca info dari ots
                $infoCmd = sprintf('%s info %s 2>&1', escapeshellcmd($otsBin), escapeshellarg($otsFile));
                exec($infoCmd, $infoOutput);
                $proof['info'] = implode("\n", $infoOutput);
            } elseif ($returnVar === 0) {
                $proof['note'] = 'OTS stamp completed but .ots file not found. Output: ' . implode("\n", $output);
            } else {
                $proof['note'] = 'OTS CLI error: ' . implode("\n", $output);
                Log::warning('OTS CLI stamp failed', ['hash' => $hash, 'output' => $output]);
            }

            // Cleanup temp files
            if (file_exists($digestFile)) unlink($digestFile);
            // Keep .ots file for verification (will be cleaned up later)

        } catch (\Exception $e) {
            $proof['note'] = 'OTS CLI exception: ' . $e->getMessage();
            Log::error('OTS CLI exception', ['hash' => $hash, 'error' => $e->getMessage()]);
        }

        return $proof;
    }
}
