<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'transaction_number' => 'TRX-' . fake()->unique()->numerify('########'),
            'reservation_id' => Reservation::factory(),
            'type' => fake()->randomElement(['dp', 'pelunasan', 'checkin_payment', 'additional', 'checkout_payment', 'refund']),
            'amount' => fake()->randomFloat(2, 100000, 5000000),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'qris', 'transfer']),
            'notes' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}