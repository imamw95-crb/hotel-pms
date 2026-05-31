<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Permission;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    public function test_owner_has_all_permissions()
    {
        $user = User::factory()->create(['role' => 'owner']);

        // Owner should have any permission, even if not assigned
        $this->assertTrue($user->hasPermission('non_existent_permission'));
    }

    public function test_user_with_direct_permission()
    {
        $permission = Permission::factory()->create([
            'name' => 'View Reports',
            'slug' => 'view_reports',
            'group' => 'report',
        ]);

        $user = User::factory()->create(['role' => 'frontoffice']);
        $user->permissions()->attach($permission->id);

        $this->assertTrue($user->hasPermission('view_reports'));
    }

    public function test_user_with_role_permission()
    {
        $permission = Permission::factory()->create([
            'name' => 'View Reports',
            'slug' => 'view_reports',
            'group' => 'report',
        ]);

        // Assign to 'frontoffice' role
        \DB::table('role_permission')->insert([
            'role' => 'frontoffice',
            'permission_id' => $permission->id,
        ]);

        $user = User::factory()->create(['role' => 'frontoffice']);

        $this->assertTrue($user->hasPermission('view_reports'));
    }

    public function test_user_without_permission()
    {
        // Create some permission but don't assign it
        Permission::factory()->create([
            'name' => 'Admin Access',
            'slug' => 'admin_access',
            'group' => 'admin',
        ]);

        $user = User::factory()->create(['role' => 'frontoffice']);

        $this->assertFalse($user->hasPermission('admin_access'));
    }
}
