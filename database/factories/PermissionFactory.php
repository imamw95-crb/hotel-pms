<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'name' => str_replace('-', ' ', ucwords($slug, '-')),
            'slug' => $slug,
            'description' => fake()->sentence(),
            'group' => fake()->randomElement(['report', 'room', 'booking', 'user', 'housekeeping', 'other']),
        ];
    }
}
