<?php

namespace Database\Factories;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HousekeepingTaskFactory extends Factory
{
    protected $model = HousekeepingTask::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'task_type' => fake()->randomElement(['cleaning', 'deep_clean', 'maintenance', 'inspection']),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
