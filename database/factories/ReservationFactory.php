<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $checkIn = Carbon::today()->addDays(rand(1, 10))->setHour(14)->setMinute(0);
        $checkOut = (clone $checkIn)->addDay()->setHour(12)->setMinute(0);

        return [
            'reservation_number' => 'RES-' . strtoupper(fake()->unique()->bothify('??????')),
            'room_id' => Room::factory(),
            'guest_id' => Guest::factory(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'number_of_cards' => 1,
            'status' => 'pending',
            'total_amount' => 500000,
            'paid_amount' => 0,
            'notes' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function checkedIn(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'checked_in',
        ]);
    }

    public function checkedOut(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'checked_out',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'cancelled',
        ]);
    }
}
