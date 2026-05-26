@extends('layouts.app')

@section('title', 'Master Tamu')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Master Tamu</h2>
        <div class="flex gap-2">
            <a href="{{ route('guests.export', array_merge(request()->query())) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center">
                <i class="fas fa-download mr-2"></i> Export CSV
            </a>
            <a href="{{ route('guests.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Tamu
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('guests.index') }}" class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
        <div class="grid gap-4 lg:grid-cols-4">
            <div>
                <label class="block text-gray-700 mb-2 text-sm">Cari Nama / No. ID / Telepon</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full border rounded px-3 py-2" placeholder="Cari...">
            </div>
            <div>
                <label class="block text-gray-700 mb-2 text-sm">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 mb-2 text-sm">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('guests.index') }}" class="flex-1 text-center bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="px-4 py-3 border border-gray-200">Nama Tamu</th>
                    <th class="px-4 py-3 border border-gray-200">No. Identitas</th>
                    <th class="px-4 py-3 border border-gray-200">Telepon</th>
                    <th class="px-4 py-3 border border-gray-200">Email</th>
                    <th class="px-4 py-3 border border-gray-200">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $guest)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 border border-gray-200">{{ $guest->guest_name }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $guest->id_number ?? '-' }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $guest->phone ?? '-' }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $guest->email ?? '-' }}</td>
                        <td class="px-4 py-3 border border-gray-200">
                            <a href="{{ route('guests.edit', $guest->id) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 mr-2">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('guests.destroy', $guest->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus tamu ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-600">Tidak ada data tamu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $guests->links() }}
    </div>
</div>
@endsection
