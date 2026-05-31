<?php

namespace Tests\Feature\Api;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['role' => 'admin']);
        $plainKey = Str::random(48);

        $user->tokens()->create([
            'name' => 'api-key',
            'token' => hash('sha256', $plainKey),
            'abilities' => ['*'],
        ]);

        $this->headers = [
            'X-API-Key' => $plainKey,
            'Accept' => 'application/json',
        ];
    }

    public function test_api_stats_endpoint()
    {
        Room::factory()->count(10)->create(['status' => 'available']);
        Room::factory()->count(3)->create(['status' => 'occupied']);

        $response = $this->getJson('/api/stats', $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }
}
