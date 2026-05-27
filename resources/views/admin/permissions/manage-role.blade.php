@extends('layouts.app')

@section('title', 'Manage ' . ucfirst($role) . ' Permissions')

@section('content')
<div class="p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('admin.permissions.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Permission Dashboard</a>
            <h1 class="text-3xl font-bold mt-2">Manage {{ ucfirst($role) }} Permissions</h1>
            <p class="text-gray-600 mt-1">{{ count($rolePermissionIds) }} permissions assigned</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="font-semibold text-red-700">Error:</div>
            <ul class="list-disc list-inside text-red-600 text-sm mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            ✓ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.permissions.update-role', $role) }}" method="POST" class="bg-white rounded-lg shadow p-8" data-ajax="true">
        @csrf
        
        <div class="space-y-6">
            @foreach($permissions as $group => $groupPermissions)
                <div>
                    <h3 class="text-lg font-semibold mb-3 capitalize">{{ $group }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($groupPermissions as $permission)
                            <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="{{ $permission->id }}"
                                       {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-3">
                                    <span class="font-medium">{{ $permission->name }}</span>
                                    <span class="text-xs text-gray-600 block">{{ $permission->description }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-between mt-8 pt-8 border-t">
            <a href="{{ route('admin.permissions.dashboard') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save Permissions
            </button>
        </div>
    </form>
</div>
@endsection
