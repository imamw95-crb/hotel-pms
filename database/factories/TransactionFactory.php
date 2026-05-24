<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'transaction_number' => 'TRX-' . $this->faker->unique()->numerify('########'),
            'reservation_id' => Reservation::factory(),
            'type' => $this->faker->randomElement(['checkin_payment', 'additional', 'checkout_payment', 'refund']),
            'amount' => $this->faker->numberBetween(100000, 5000000),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'transfer']),
            'notes' => $this->faker->sentence,
            'created_by' => 1,
        ];
    }
}