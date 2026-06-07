<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCancelPendingBookingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hotel:auto-cancel-pending
                            {--hours=3 : Jumlah jam sebelum booking otomatis dibatalkan}
                            {--dry-run : Hitung saja, tanpa benar-benar membatalkan}';

    /**
     * The console command description.
     */
    protected $description = 'Batalkan otomatis booking pending (website + OTA) yang melebihi batas waktu';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        $threshold = Carbon::now()->subHours($hours);

        $this->info("⏰ Auto-Cancel Pending Bookings (threshold: {$hours} jam)");
        $this->newLine();

        // Cari booking dari api/web yang masih menunggu pembayaran / pending
        // dan sudah melebihi batas waktu sejak dibuat (created_at)
        // Booking langsung dari PMS (ota_source IS NULL) dan OTA lain TIDAK ikut dibatalkan
        $pendingBookings = Reservation::whereIn('status', ['menunggu_pembayaran', 'pending'])
            ->where(function ($q) {
                $q->where('ota_source', 'website')
                    ->orWhere('ota_source', 'api');
            })
            ->where('created_at', '<', $threshold)
            ->orderBy('created_at', 'asc')
            ->get();

        $total = $pendingBookings->count();

        if ($total === 0) {
            $this->info("✅ Tidak ada booking pending yang melebihi {$hours} jam.");

            return self::SUCCESS;
        }

        $this->warn("⚠️  Ditemukan {$total} booking pending yang sudah >{$hours} jam:");
        $this->newLine();

        $headers = ['ID', 'Reservasi', 'Tamu', 'Kamar', 'Status', 'Sumber', 'Dibuat'];
        $rows = $pendingBookings->map(fn ($r) => [
            $r->id,
            $r->reservation_number,
            $r->guest?->guest_name ?? '-',
            $r->room?->room_number ?? '-',
            $r->status,
            $r->ota_source ?: '-',
            $r->created_at->format('d/m/Y H:i'),
        ])->toArray();

        $this->table($headers, $rows);
        $this->newLine();

        if ($dryRun) {
            $this->info('═══════════════════════════════════════');
            $this->info('  ✅ DRY RUN — Tidak ada perubahan data');
            $this->info('═══════════════════════════════════════');

            return self::SUCCESS;
        }

        // Konfirmasi
        if (! $this->confirm("Yakin ingin membatalkan {$total} booking ini?", true)) {
            $this->info('Dibatalkan oleh user.');

            return self::SUCCESS;
        }

        $cancelled = 0;
        $failed = 0;

        foreach ($pendingBookings as $reservation) {
            try {
                $reservation->update([
                    'status' => 'cancelled',
                    'notes' => ($reservation->notes ? $reservation->notes."\n" : '')
                        .'[Auto-cancel] Dibatalkan otomatis oleh sistem (pending >'.$hours.' jam pada '.now()->format('d/m/Y H:i').')',
                ]);

                // Kembalikan status kamar jika perlu
                if ($reservation->room) {
                    $reservation->room->update(['status' => 'available']);
                }

                $this->line("  ✔ {$reservation->reservation_number} — {$reservation->guest?->guest_name} → dibatalkan");
                $cancelled++;
            } catch (\Exception $e) {
                $this->error("  ✘ {$reservation->reservation_number} — Gagal: {$e->getMessage()}");
                Log::error("Auto-cancel gagal untuk reservasi {$reservation->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("  ✅ Selesai — {$cancelled} dibatalkan, {$failed} gagal");
        $this->info("═══════════════════════════════════════");

        Log::info("Auto-cancel pending bookings: {$cancelled} cancelled, {$failed} failed (threshold: {$hours} jam)");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
