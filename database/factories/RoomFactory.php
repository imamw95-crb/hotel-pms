<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $roomType = RoomType::factory()->create();

        return [
            'room_number' => fake()->unique()->numerify('###'),
            'room_type_id' => $roomType->id,
            'room_type_name' => $roomType->name,
            'price_per_night' => 500000,
            'price_weekday' => 500000,
            'price_weekend' => 600000,
            'max_occupancy' => 2,
            'status' => 'available',
            'facilities' => ['AC', 'TV', 'WiFi'],
        ];
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'occupied',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'maintenance',
        ]);
    }

    public function cleaning(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'cleaning',
        ]);
    }
}
