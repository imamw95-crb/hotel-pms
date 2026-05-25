@extends('layouts.app')

@section('title', 'User Permissions')

@section('content')
<div class="p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('admin.permissions.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Permission Dashboard</a>
            <h1 class="text-3xl font-bold mt-2">User Permissions</h1>
            <p class="text-gray-600 mt-1">List direct permissions assigned to each user</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Direct Permissions</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($user->permissions->isEmpty())
                                <span class="text-gray-500">No direct permissions</span>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach($user->permissions as $permission)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $permission->slug }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.permissions.manage-user', $user) }}" class="text-blue-600 hover:text-blue-800">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No users found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection
