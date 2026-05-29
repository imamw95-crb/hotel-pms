<?php

namespace App\Console\Commands;

use App\Services\OpenRouterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AiReservationCommand extends Command
{
    protected $signature = 'hotel:ai-reservation
                            {input : Natural language reservation input (e.g. "Budi Santoso check-in besok Deluxe Room 2 malam")}
                            {--dry-run : Parse only, don\'t save to database}';

    protected $description = 'AI Auto-Reservation — Create reservation from natural language input';

    public function handle(OpenRouterService $openRouter): int
    {
        $input = $this->argument('input');
        $dryRun = $this->option('dry-run');

        $this->info('🤖 AI Auto-Reservation');
        $this->newLine();
        $this->info("Input: {$input}");
        $this->newLine();

        // Step 1: AI Parsing
        $this->info('─── Step 1: AI Parsing ───');
        $this->info('⏳ Sending to AI...');

        $aiData = $openRouter->parseNaturalLanguage($input);

        if (!$aiData) {
            $this->error('❌ AI parsing failed');
            return self::FAILURE;
        }

        $this->info('✅ AI parsing successful');
        $this->newLine();
        $this->table(['Field', 'Value'], collect($aiData)->map(fn($v, $k) => [$k, is_array($v) ? json_encode($v) : ($v ?: '(empty)')])->toArray());
        $this->newLine();

        // Step 2: Validate
        $this->info('─── Step 2: Validation ───');
        $errors = [];

        if (empty($aiData['guest_name'])) {
            $errors[] = 'Missing guest_name';
        }
        if (empty($aiData['checkin_date'])) {
            $errors[] = 'Missing checkin_date';
        }
        if (empty($aiData['checkout_date'])) {
            $errors[] = 'Missing checkout_date';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->error("❌ {$error}");
            }
            return self::FAILURE;
        }

        try {
            $checkIn  = \Carbon\Carbon::parse($aiData['checkin_date'])->setTime(14, 0);
            $checkOut = \Carbon\Carbon::parse($aiData['checkout_date'])->setTime(12, 0);
        } catch (\Exception $e) {
            $this->error('❌ Invalid date format');
            return self::FAILURE;
        }

        if ($checkIn->gte($checkOut)) {
            $this->error('❌ check-in date must be before check-out date');
            return self::FAILURE;
        }

        $this->info('✅ All validations passed');
        $this->newLine();

        // Dry run — stop here
        if ($dryRun) {
            $this->info('─── Dry Run Mode ───');
            $this->info("Would create reservation for: {$aiData['guest_name']}");
            $this->info("Check-in:  {$checkIn->format('Y-m-d H:i')}");
            $this->info("Check-out: {$checkOut->format('Y-m-d H:i')}");
            $this->info("Room type: " . ($aiData['room_type'] ?: 'Any available'));
            $this->info("Guests:    " . ($aiData['guest_count'] ?? 1));
            $this->info("Payment:   " . ($aiData['payment_method'] ?: 'cash'));
            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info('  ✅ DRY RUN PASSED — No data saved');
            $this->info('═══════════════════════════════════════');
            return self::SUCCESS;
        }

        // Step 3: Find available room
        $this->info('─── Step 3: Room Availability ───');
        $roomId = null;
        $roomTypeName = $aiData['room_type'] ?? null;

        if ($roomTypeName) {
            $availableRooms = \App\Models\Room::where('room_type_name', $roomTypeName)
                ->where('status', '!=', 'maintenance')
                ->whereNotIn('id', function ($q) use ($checkIn, $checkOut) {
                    $q->select('room_id')
                        ->from('reservations')
                        ->whereIn('status', ['pending', 'checked_in'])
                        ->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->orderBy('room_number')
                ->get();

            if ($availableRooms->isNotEmpty()) {
                $roomId = $availableRooms->first()->id;
                $this->info("✅ Found room type '{$roomTypeName}': Kamar {$availableRooms->first()->room_number}");
            } else {
                $this->warn("⚠️ No available room for type '{$roomTypeName}'");
            }
        }

        if (!$roomId) {
            $anyAvailable = \App\Models\Room::where('status', '!=', 'maintenance')
                ->whereNotIn('id', function ($q) use ($checkIn, $checkOut) {
                    $q->select('room_id')
                        ->from('reservations')
                        ->whereIn('status', ['pending', 'checked_in'])
                        ->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->orderBy('room_number')
                ->first();

            if ($anyAvailable) {
                $roomId = $anyAvailable->id;
                $roomTypeName = $anyAvailable->room_type_name;
                $this->info("✅ Fallback: Kamar {$anyAvailable->room_number} ({$anyAvailable->room_type_name})");
            } else {
                $this->error('❌ No available rooms for the given dates');
                return self::FAILURE;
            }
        }
        $this->newLine();

        // Step 4: Create reservation
        $this->info('─── Step 4: Creating Reservation ───');

        try {
            $reservation = DB::transaction(function () use ($aiData, $roomId, $checkIn, $checkOut) {
                $guest = \App\Models\Guest::firstOrCreate(
                    ['guest_name' => $aiData['guest_name']],
                    ['phone' => null, 'email' => null, 'address' => null]
                );

                $totalAmount = $aiData['total_price'] ?? 0;
                if ($totalAmount <= 0 && $roomId) {
                    $room = \App\Models\Room::find($roomId);
                    if ($room) {
                        $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
                    }
                }

                $reservation = \App\Models\Reservation::create([
                    'guest_id'        => $guest->id,
                    'room_id'         => $roomId,
                    'check_in'        => $checkIn,
                    'check_out'       => $checkOut,
                    'number_of_cards' => $aiData['guest_count'] ?? 1,
                    'total_amount'    => $totalAmount,
                    'payment_method'  => $aiData['payment_method'] ?: 'cash',
                    'paid_amount'     => 0,
                    'status'          => 'pending',
                    'notes'           => ($aiData['notes'] ? $aiData['notes'] . ' ' : '') . '(AI Auto-Reservation)',
                    'ota_source'      => 'ai_auto',
                    'created_by'      => 1,
                ]);

                return $reservation;
            });

            $reservation->load(['guest', 'room']);

            $this->info('✅ Reservation created successfully!');
            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info("  Reservation: {$reservation->reservation_number}");
            $this->info("  Guest:       {$reservation->guest->guest_name}");
            $this->info("  Room:        {$reservation->room->room_number} ({$reservation->room->room_type_name})");
            $this->info("  Check-in:    {$checkIn->format('Y-m-d H:i')}");
            $this->info("  Check-out:   {$checkOut->format('Y-m-d H:i')}");
            $this->info("  Total:       Rp " . number_format($reservation->total_amount, 0, ',', '.'));
            $this->info("  Payment:     " . ($aiData['payment_method'] ?: 'cash'));
            $this->info('═══════════════════════════════════════');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to create reservation: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
