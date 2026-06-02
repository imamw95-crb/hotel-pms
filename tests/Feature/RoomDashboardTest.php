<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Tests\TestCase;

class RoomDashboardTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'owner']);
    }

    public function test_room_dashboard_page_loads()
    {
        Room::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->get('/rooms-dashboard');

        $response->assertStatus(200);
    }

    public function test_rooms_api_returns_json()
    {
        Room::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/api/rooms-status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'rooms',
        ]);
    }

    public function test_update_room_status()
    {
        $room = Room::factory()->create(['status' => 'available']);

        $response = $this->actingAs($this->user)
            ->patch("/rooms/{$room->id}/status", [
                'status' => 'maintenance',
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('maintenance', $room->fresh()->status);
    }
}
