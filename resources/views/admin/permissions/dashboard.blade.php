@extends('layouts.app')

@section('title', 'Admin - Permission Management')

@section('content')
<div class="p-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Permissions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-blue-600">{{ $totalPermissions }}</div>
            <div class="text-gray-600">Total Permissions</div>
        </div>

        <!-- Role Statistics -->
        @foreach($roleStats as $role => $count)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-3xl font-bold text-green-600">{{ $count }}</div>
                <div class="text-gray-600">{{ $role === 'user_manager' ? 'Manager' : ucfirst($role) }} Permissions</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Permission Management -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Manage Permissions by Role</h2>
                <div class="space-y-3">
                    @foreach(['owner', 'admin', 'frontoffice', 'housekeeping', 'user_manager'] as $role)
                        <a href="{{ route('admin.permissions.manage-role', $role) }}" 
                           class="block p-4 border rounded-lg hover:bg-blue-50 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold">{{ $role === 'user_manager' ? 'Manager' : ucfirst($role) }}</h3>
                                    <p class="text-sm text-gray-600">{{ $roleStats[$role] ?? 0 }} permissions assigned</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- User Permissions -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h2 class="text-xl font-bold mb-4">User Permissions</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.permissions.user-permissions') }}" 
                       class="block p-3 border rounded hover:bg-blue-50 transition">
                        View all user permissions
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Recent Users</h2>
            <div class="space-y-3">
                @forelse($recentUsers as $user)
                    <div class="border-b pb-3 last:border-b-0">
                        <div class="font-semibold text-sm">{{ $user->name }}</div>
                        <div class="text-xs text-gray-600">{{ $user->email }}</div>
                        <div class="text-xs text-gray-500">
                            <span class="inline-block mt-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded">
                                {{ $user->role === 'user_manager' ? 'Manager' : ucfirst($user->role) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No users found</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="bg-white rounded-lg shadow p-6 mt-8">
        <h2 class="text-xl font-bold mb-4">Quick Links</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.permissions.index') }}" class="p-3 border rounded hover:bg-gray-50 transition">
                <i class="fas fa-list mr-2"></i> All Permissions
            </a>
            <a href="{{ route('admin.users.index') }}" class="p-3 border rounded hover:bg-gray-50 transition">
                <i class="fas fa-users mr-2"></i> Manage Users
            </a>
            <a href="{{ route('admin.users.create') }}" class="p-3 border rounded hover:bg-gray-50 transition">
                <i class="fas fa-user-plus mr-2"></i> Create User
            </a>
        </div>
    </div>
</div>
@endsection
