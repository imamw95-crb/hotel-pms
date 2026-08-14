<?php

namespace Database\Seeders;

use App\Models\BookingGroupMaster;
use App\Models\BookingGroupMasterPrice;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Migrasi data reservasi group yang sudah ada menjadi Master Group & Master Harga.
 *
 * Jalankan (sekali saja, di server live):
 *   php artisan db:seed --class=GroupReservationMigrationSeeder --force
 *
 * Idempotent: group yang sudah dimigrasi (ditandai di kolom description)
 * akan dilewati, jadi aman dijalankan ulang.
 */
class GroupReservationMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $reservations = Reservation::with(['room', 'guest'])
            ->whereNotNull('booking_group_id')
            ->orderBy('booking_group_id')
            ->orderBy('check_in')
            ->get();

        if ($reservations->isEmpty()) {
            $this->command?->info('Tidak ada reservasi group untuk dimigrasi.');

            return;
        }

        $groups = $reservations->groupBy('booking_group_id');
        $created = 0;
        $skipped = 0;

        foreach ($groups as $groupId => $items) {
            $marker = 'Diimpor dari reservasi group '.$groupId;

            // Lewati jika group ini sudah pernah dimigrasi
            if (BookingGroupMaster::where('description', $marker)->exists()) {
                $skipped++;

                continue;
            }

            $first = $items->first();
            $guestName = trim($first->guest?->guest_name ?? 'Group');
            $name = mb_substr($guestName.' ('.$items->count().' kamar)', 0, 100);

            $master = BookingGroupMaster::create([
                'code' => BookingGroupMaster::generateCode(),
                'name' => $name,
                'description' => $marker,
                'is_active' => true,
                'created_by' => User::whereKey($first->created_by)->exists() ? $first->created_by : null,
            ]);

            // Harga rata-rata per malam per tipe kamar
            $pricesByType = [];
            foreach ($items as $r) {
                $roomTypeId = $r->room?->room_type_id;
                if (! $roomTypeId) {
                    continue;
                }
                $nights = $r->nights;
                $price = $nights > 0 ? round((float) $r->total_amount / $nights) : 0;
                if ($price > 0) {
                    $pricesByType[$roomTypeId][] = $price;
                }
            }

            foreach ($pricesByType as $roomTypeId => $prices) {
                BookingGroupMasterPrice::create([
                    'booking_group_master_id' => $master->id,
                    'room_type_id' => $roomTypeId,
                    'price_per_night' => (int) round(array_sum($prices) / count($prices)),
                    'include_breakfast' => true,
                ]);
            }

            $created++;
        }

        $this->command?->info("Selesai: {$created} master group dibuat, {$skipped} dilewati (sudah ada).");
    }
}
