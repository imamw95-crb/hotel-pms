@extends('layouts.app')

@section('title', 'Permissions List')

@section('content')
<div class="p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('admin.permissions.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Permission Dashboard</a>
            <h1 class="text-3xl font-bold mt-2">All Permissions</h1>
            <p class="text-gray-600 mt-1">Total: {{ $permissions->total() }} permissions</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Slug</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Group</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($permissions as $permission)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold">{{ $permission->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $permission->slug }}</code>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                {{ ucfirst($permission->group) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $permission->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No permissions found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $permissions->links() }}
    </div>
</div>
@endsection
