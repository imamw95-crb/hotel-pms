<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class BookingTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'owner']);
    }

    public function test_booking_create_page_loads()
    {
        Room::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/booking/create');

        $response->assertStatus(200);
    }

    public function test_booking_check_availability_returns_json()
    {
        Room::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/booking/check-availability', [
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
    }

    public function test_booking_store_creates_reservation()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $guest = Guest::factory()->create();

        $response = $this->actingAs($this->user)->post('/booking', [
            'room_id' => $room->id,
            'guest_name' => $guest->guest_name,
            'id_number' => $guest->id_number,
            'phone' => $guest->phone,
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'notes' => 'Test booking',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'room_id' => $room->id,
            'status' => 'pending',
        ]);
    }
}
