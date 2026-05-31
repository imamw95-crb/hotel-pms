<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\HousekeepingTask;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'owner']);
    }

    public function test_housekeeping_index_page_loads()
    {
        Room::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/housekeeping');

        $response->assertStatus(200);
    }

    public function test_create_housekeeping_task()
    {
        $room = Room::factory()->create();
        $staff = User::factory()->create(['role' => 'housekeeping']);

        $response = $this->actingAs($this->user)->post('/housekeeping', [
            'room_id' => $room->id,
            'task_type' => 'cleaning',
            'priority' => 'normal',
            'description' => 'Clean the room',
            'assigned_to' => $staff->id,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('housekeeping_tasks', [
            'room_id' => $room->id,
            'task_type' => 'cleaning',
            'status' => 'pending',
        ]);
    }

    public function test_update_housekeeping_task_status()
    {
        $room = Room::factory()->create();
        $task = HousekeepingTask::factory()->create([
            'room_id' => $room->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/housekeeping/{$task->id}/status", [
                'status' => 'completed',
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('completed', $task->fresh()->status);
    }
}
