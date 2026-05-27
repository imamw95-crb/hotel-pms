<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Users jika belum ada
        if (User::count() == 0) {
            User::create([
                'name' => 'Owner',
                'email' => 'owner@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]);
            User::create([
                'name' => 'Admin',
                'email' => 'admin@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
            User::create([
                'name' => 'Front Office',
                'email' => 'frontoffice@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'frontoffice',
            ]);
            $this->command->info('Users created.');
        }

        // 2. Buat Room Types jika belum ada
        if (RoomType::count() == 0) {
            $types = [
                ['code' => 'STD', 'name' => 'Standard', 'sequence' => 1],
                ['code' => 'DLX', 'name' => 'Deluxe', 'sequence' => 2],
                ['code' => 'STE', 'name' => 'Suite', 'sequence' => 3],
            ];
            foreach ($types as $type) {
                RoomType::create($type);
            }
            $this->command->info('Room types created.');
        }

        // 3. Buat Rooms jika belum ada
        if (Room::count() == 0) {
            // Format: [room_number, type_code, price_weekday, max_occupancy, price_weekend]
            $rooms = [
                ['101', 'STD', 500000, 2, 600000],
                ['102', 'STD', 500000, 2, 600000],
                ['103', 'DLX', 750000, 2, 900000],
                ['104', 'DLX', 750000, 2, 900000],
                ['105', 'STE', 1200000, 3, 1440000],
                ['201', 'STD', 500000, 2, 600000],
                ['202', 'DLX', 750000, 2, 900000],
                ['203', 'STE', 1200000, 3, 1440000],
            ];
            foreach ($rooms as $room) {
                $type = RoomType::where('code', $room[1])->first();
                Room::create([
                    'room_number' => $room[0],
                    'room_type_id' => $type->id,
                    'room_type_name' => $type->name,
                    'price_per_night' => $room[2],
                    'price_weekday' => $room[2],
                    'price_weekend' => $room[4],
                    'max_occupancy' => $room[3],
                    'status' => 'available',
                ]);
            }
            $this->command->info('Rooms created.');
        }

        // 4. Buat Guests (5 tamu dummy)
        if (Guest::count() == 0) {
            $guests = [
                ['John Doe', '1234567890', '081234567890', 'john@example.com'],
                ['Jane Smith', '0987654321', '081298765432', 'jane@example.com'],
                ['Michael Johnson', '1122334455', '081355667788', 'michael@example.com'],
                ['Sarah Williams', '5544332211', '081377889900', 'sarah@example.com'],
                ['David Brown', '9988776655', '081399001122', 'david@example.com'],
            ];
            foreach ($guests as $g) {
                Guest::create([
                    'guest_name' => $g[0],
                    'id_number' => $g[1],
                    'phone' => $g[2],
                    'email' => $g[3],
                ]);
            }
            $this->command->info('Guests created.');
        }

        // 5. Buat Reservasi (check-in skrg sampai 3 hari ke depan) dan Transaksi
        $users = User::pluck('id')->toArray();
        $rooms = Room::all();
        $guests = Guest::all();

        if (Reservation::count() == 0) {
            for ($i = 0; $i < 10; $i++) {
                $room = $rooms->random();
                $guest = $guests->random();
                // Standard hotel time: check-in/check-out jam 12:00 siang
                $checkIn = Carbon::today()->addDays(rand(-2, 2))->setTime(12, 0, 0);
                $checkOut = (clone $checkIn)->addDays(rand(1, 4))->setTime(12, 0, 0);
                $nights = $checkIn->diffInDays($checkOut);
                $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);

                $reservation = Reservation::create([
                    'reservation_number' => 'RES-' . strtoupper(uniqid()),
                    'room_id' => $room->id,
                    'guest_id' => $guest->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'number_of_cards' => 1,
                    'status' => $checkIn->lte(now()) ? 'checked_in' : 'pending',
                    'total_amount' => $totalAmount,
                    'paid_amount' => rand(0, $totalAmount),
                    'created_by' => $users[array_rand($users)],
                ]);

                // 6. Buat Transaksi untuk setiap reservasi (1 atau 2 transaksi)
                // Transaksi check-in payment
                $paid = $reservation->paid_amount;
                if ($paid > 0) {
                    Transaction::create([
                        'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                        'reservation_id' => $reservation->id,
                        'type' => 'checkin_payment',
                        'amount' => $paid,
                        'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                        'notes' => 'Pembayaran awal check-in',
                        'created_by' => $reservation->created_by,
                    ]);
                }

                // Jika status checked_out, mungkin ada transaksi checkout payment
                if ($reservation->status == 'checked_out' && $reservation->remaining_payment > 0) {
                    Transaction::create([
                        'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                        'reservation_id' => $reservation->id,
                        'type' => 'checkout_payment',
                        'amount' => $reservation->remaining_payment,
                        'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                        'notes' => 'Pelunasan saat check-out',
                        'created_by' => $reservation->created_by,
                    ]);
                }

                // Update room status jika check-in <= sekarang
                if ($reservation->status == 'checked_in') {
                    $room->update(['status' => 'occupied']);
                }
            }
            $this->command->info('Reservations and Transactions created.');
        } else {
            // Jika sudah ada reservasi, buat transaksi untuk reservasi yang belum punya transaksi
            $reservationsWithoutTransactions = Reservation::doesntHave('transactions')->get();
            foreach ($reservationsWithoutTransactions as $reservation) {
                $paid = rand(0, $reservation->total_amount);
                if ($paid > 0) {
                    Transaction::create([
                        'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                        'reservation_id' => $reservation->id,
                        'type' => 'checkin_payment',
                        'amount' => $paid,
                        'payment_method' => ['cash', 'card', 'transfer'][rand(0, 2)],
                        'notes' => 'Pembayaran awal (dummy)',
                        'created_by' => $reservation->created_by,
                    ]);
                }
            }
            $this->command->info('Transactions added for existing reservations.');
        }

        $this->command->info('All dummy data seeded successfully!');
    }
}