<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    private string $apiKey;
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['role' => 'admin']);
        $plainKey = Str::random(48);

        // Create Sanctum token with name 'api-key'
        $user->tokens()->create([
            'name' => 'api-key',
            'token' => hash('sha256', $plainKey),
            'abilities' => ['*'],
        ]);

        $this->apiKey = $plainKey;
        $this->headers = [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    public function test_api_requires_api_key()
    {
        $response = $this->getJson('/api/reservations');

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_api_list_reservations()
    {
        Reservation::factory()->count(5)->create();

        $response = $this->getJson('/api/reservations', $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data',
            ],
        ]);
    }

    public function test_api_create_reservation_full_flow()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $guest = Guest::factory()->create();

        $checkIn = Carbon::tomorrow()->setHour(14)->format('Y-m-d');
        $checkOut = Carbon::tomorrow()->addDay()->setHour(12)->format('Y-m-d');

        $response = $this->postJson('/api/reservations', [
            'room_id' => $room->id,
            'guest_name' => $guest->guest_name,
            'guest_phone' => $guest->phone,
            'guest_id_number' => $guest->id_number,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'total_amount' => $room->price_per_night,
        ], $this->headers);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('reservations', [
            'room_id' => $room->id,
            'status' => 'pending',
        ]);
    }

    public function test_api_show_reservation_detail()
    {
        $reservation = Reservation::factory()->create();

        $response = $this->getJson("/api/reservations/{$reservation->id}", $this->headers);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'reservation_number' => $reservation->reservation_number,
        ]);
    }

    public function test_api_cancel_reservation()
    {
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/reservations/{$reservation->id}/cancel", [], $this->headers);

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $reservation->fresh()->status);
    }

    public function test_api_checkin_checkout_flow()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Check-in via API
        $checkinResponse = $this->postJson(
            "/api/reservations/{$reservation->id}/checkin",
            [],
            $this->headers
        );

        $checkinResponse->assertStatus(200);
        $this->assertEquals('checked_in', $reservation->fresh()->status);
        $this->assertEquals('occupied', $room->fresh()->status);

        // Check-out via API
        $checkoutResponse = $this->postJson(
            "/api/reservations/{$reservation->id}/checkout",
            [],
            $this->headers
        );

        $checkoutResponse->assertStatus(200);
        $this->assertEquals('checked_out', $reservation->fresh()->status);
        $this->assertEquals('available', $room->fresh()->status);
    }
}
