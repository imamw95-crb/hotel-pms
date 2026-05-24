<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        // Ambil beberapa reservasi yang sudah ada
        $reservations = Reservation::take(5)->get();

        foreach ($reservations as $reservation) {
            Transaction::create([
                'transaction_number' => 'TRX-' . strtoupper(uniqid()),
                'reservation_id' => $reservation->id,
                'type' => 'checkin_payment',
                'amount' => 500000,
                'payment_method' => 'cash',
                'notes' => 'Pembayaran DP check-in',
                'created_by' => 1, // admin user ID
            ]);
        }
    }
}