<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_owner_can_access_admin_routes()
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get('/admin/permissions');

        $response->assertStatus(200);
    }

    public function test_frontoffice_cannot_access_admin_routes()
    {
        $staff = User::factory()->create(['role' => 'frontoffice']);

        $response = $this->actingAs($staff)->get('/admin/permissions');

        $response->assertStatus(403);
    }

    public function test_user_without_permission_gets_403()
    {
        // Housekeeping role doesn't have 'manage_guests' permission by default
        $staff = User::factory()->create(['role' => 'housekeeping']);

        // Guest index requires 'manage_guests' permission
        $response = $this->actingAs($staff)->get('/guests');

        $response->assertStatus(403);
    }

    public function test_frontoffice_can_access_front_desk_routes()
    {
        $staff = User::factory()->create(['role' => 'frontoffice']);

        $response = $this->actingAs($staff)->get('/reservations');

        $response->assertStatus(200);
    }
}
