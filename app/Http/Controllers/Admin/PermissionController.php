<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Dashboard - Tampilkan statistik permission
     */
    public function dashboard()
    {
        $totalPermissions = Permission::count();
        $permissionGroups = Permission::select('group')->distinct()->get()->pluck('group');
        $roleStats = [];

        foreach (['owner', 'admin', 'frontoffice', 'housekeeping', 'user_manager'] as $role) {
            $roleStats[$role] = DB::table('role_permission')
                ->where('role', $role)
                ->count();
        }

        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.permissions.dashboard', compact(
            'totalPermissions', 'permissionGroups', 'roleStats', 'recentUsers'
        ));
    }

    /**
     * Manage role permissions
     */
    public function manageRolePermissions($role)
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $rolePermissionIds = DB::table('role_permission')
            ->where('role', $role)
            ->pluck('permission_id')
            ->toArray();

        return view('admin.permissions.manage-role', compact(
            'role', 'permissions', 'rolePermissionIds'
        ));
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request, $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Clear existing permissions
        DB::table('role_permission')->where('role', $role)->delete();

        // Assign new permissions
        $permissionIds = $validated['permissions'] ?? [];
        foreach ($permissionIds as $permissionId) {
            DB::table('role_permission')->insert([
                'role' => $role,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permissions untuk role '{$role}' berhasil diupdate!",
                'redirect_url' => route('admin.permissions.dashboard'),
            ]);
        }

        return back()->with('success', "Permissions untuk role '{$role}' berhasil diupdate!");
    }

    /**
     * Manage user permissions (direct assignment)
     */
    public function manageUserPermissions(User $user)
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $userPermissionIds = $user->permissions()->pluck('permission_id')->toArray();

        return view('admin.permissions.manage-user', compact(
            'user', 'permissions', 'userPermissionIds'
        ));
    }

    /**
     * Update user permissions
     */
    public function updateUserPermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Sync permissions
        $user->permissions()->sync($validated['permissions'] ?? []);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permissions untuk user '{$user->name}' berhasil diupdate!",
                'redirect_url' => route('admin.permissions.user-permissions'),
            ]);
        }

        return back()->with('success', "Permissions untuk user '{$user->name}' berhasil diupdate!");
    }

    /**
     * View permission list
     */
    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->paginate(15);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * View user permissions
     */
    public function userPermissions()
    {
        $users = User::with(['permissions'])->paginate(15);

        return view('admin.permissions.user-permissions', compact('users'));
    }
}
