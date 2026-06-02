<?php

namespace Tests\Feature\Api;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoomApiTest extends TestCase
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

    public function test_api_list_rooms()
    {
        Room::factory()->count(5)->create();

        $response = $this->getJson('/api/rooms', $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    public function test_api_available_rooms_with_date_range()
    {
        $room = Room::factory()->create(['status' => 'available']);

        $checkIn = Carbon::tomorrow()->format('Y-m-d');
        $checkOut = Carbon::tomorrow()->addDays(2)->format('Y-m-d');

        $response = $this->getJson('/api/rooms/available?'.http_build_query([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]), $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    public function test_api_available_rooms_excludes_booked()
    {
        $availableRoom = Room::factory()->create(['status' => 'available', 'room_number' => '101']);
        $bookedRoom = Room::factory()->create(['status' => 'available', 'room_number' => '102']);

        $checkIn = Carbon::tomorrow()->setHour(14);
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12);

        // Create a reservation for bookedRoom
        Reservation::factory()->create([
            'room_id' => $bookedRoom->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => 'checked_in',
        ]);

        $response = $this->getJson('/api/rooms/available?'.http_build_query([
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
        ]), $this->headers);

        $response->assertStatus(200);
        $roomNumbers = collect($response->json('data'))->pluck('room_number');
        $this->assertContains('101', $roomNumbers);
        $this->assertNotContains('102', $roomNumbers);
    }
}
