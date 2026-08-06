<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'owner']);
    }

    public function test_reservation_list_page_loads()
    {
        Reservation::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/reservations');

        $response->assertStatus(200);
        $response->assertSee('Reservasi');
    }

    public function test_reservation_show_page_loads()
    {
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/reservations/{$reservation->id}");

        $response->assertStatus(200);
        $response->assertSee($reservation->reservation_number);
    }

    public function test_search_reservation_by_number()
    {
        $target = Reservation::factory()->create();
        Reservation::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->get('/reservations?search='.$target->reservation_number);

        $response->assertStatus(200);
        $response->assertSee($target->reservation_number);
    }

    public function test_filter_reservation_by_status()
    {
        Reservation::factory()->count(3)->create(['status' => 'pending']);
        $checkedIn = Reservation::factory()->checkedIn()->create();

        $response = $this->actingAs($this->user)
            ->get('/reservations?status=checked_in');

        $response->assertStatus(200);
        $response->assertSee($checkedIn->reservation_number);
    }

    public function test_cancel_reservation()
    {
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/cancel");

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $reservation->fresh()->status);
    }

    public function test_checkin_reservation_flow()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/checkin");

        $response->assertSessionHas('success');
        $this->assertEquals('checked_in', $reservation->fresh()->status);
        $this->assertEquals('occupied', $room->fresh()->status);
    }

    public function test_checkout_reservation_flow()
    {
        $room = Room::factory()->create(['status' => 'occupied']);
        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/checkout");

        $response->assertSessionHas('success');
        $this->assertEquals('checked_out', $reservation->fresh()->status);
        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_cannot_checkin_non_pending_reservation()
    {
        $reservation = Reservation::factory()->checkedIn()->create();

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/checkin");

        $response->assertSessionHas('error');
        $this->assertEquals('checked_in', $reservation->fresh()->status);
    }

    public function test_recheckin_checked_out_reservation()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $reservation = Reservation::factory()->checkedOut()->create([
            'room_id' => $room->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/recheckin");

        $response->assertSessionHas('success');
        $this->assertEquals('checked_in', $reservation->fresh()->status);
        $this->assertEquals('occupied', $room->fresh()->status);
        $this->assertNotNull($reservation->fresh()->checked_in_at);
        $this->assertEquals($this->user->id, $reservation->fresh()->checked_in_by);
    }

    public function test_cannot_recheckin_non_checked_out_reservation()
    {
        $reservation = Reservation::factory()->checkedIn()->create();

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/recheckin");

        $response->assertSessionHas('error');
        $this->assertEquals('checked_in', $reservation->fresh()->status);
    }

    public function test_recheckin_with_new_check_in_date()
    {
        $room = Room::factory()->create(['status' => 'available']);
        $reservation = Reservation::factory()->checkedOut()->create([
            'room_id' => $room->id,
        ]);
        $newCheckIn = '2026-08-10';
        $newCheckOut = '2026-08-12';

        $response = $this->actingAs($this->user)
            ->post("/reservations/{$reservation->id}/recheckin", [
                'check_in' => $newCheckIn,
                'check_out' => $newCheckOut,
            ]);

        $response->assertSessionHas('success');
        $fresh = $reservation->fresh();
        $this->assertEquals('checked_in', $fresh->status);
        $this->assertEquals($newCheckIn, $fresh->check_in->format('Y-m-d'));
        $this->assertEquals($newCheckOut, $fresh->check_out->format('Y-m-d'));
        $this->assertEquals('occupied', $room->fresh()->status);
    }
}
