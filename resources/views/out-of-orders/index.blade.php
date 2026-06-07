@extends('layouts.app')

@section('title', 'Out of Order')
@section('header', 'Out of Order')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
        <h2 class="text-xl font-bold">Daftar Kamar Out of Order</h2>
        <p class="text-gray-600 text-sm">Kelola kamar yang tidak tersedia untuk sementara</p>
    </div>
    <button onclick="Modal.open('{{ route('out-of-orders.create') }}')" class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600 transition">
        <i class="fas fa-ban mr-1"></i> Set Out of Order
    </button>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-3 mb-4">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="status" class="border rounded px-2 py-1 text-sm">
            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
        </select>
        <select name="room_id" class="border rounded px-2 py-1 text-sm">
            <option value="">Semua Kamar</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->room_number }}
                </option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1 text-sm w-[130px]">
        <span class="text-sm text-gray-500">s/d</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1 text-sm w-[130px]">
        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm"><i class="fas fa-search"></i></button>
        <a href="{{ route('out-of-orders.index') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
    </form>
</div>

<!-- Items Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mulai</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selesai</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat Oleh</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm font-medium">{{ $item->room->room_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->room->room_type_name ?? $item->room->roomType->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->start_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->end_date ? $item->end_date->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->duration_days }} hari</td>
                        <td class="px-4 py-3 text-sm max-w-[200px] truncate" title="{{ $item->reason }}">{{ $item->reason }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $item->status_color }}">
                                {{ $item->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->createdBy->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <button onclick="Modal.open('{{ route('out-of-orders.show', $item) }}')" class="text-blue-600 hover:text-blue-800 p-1" title="Lihat Detail">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @if($item->status === 'active')
                                    <button onclick="Modal.open('{{ route('out-of-orders.edit', $item) }}')" class="text-amber-600 hover:text-amber-800 p-1" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <form method="POST" action="{{ route('out-of-orders.complete', $item) }}" class="inline" onsubmit="return confirm('Tandai Out of Order selesai? Status kamar akan dikembalikan.')">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Tandai Selesai">
                                            <i class="fas fa-check text-sm"></i>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('out-of-orders.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus data Out of Order ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Hapus">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-ban text-3xl mb-2 text-gray-300"></i>
                            <p>Tidak ada data Out of Order</p>
                            <button onclick="Modal.open('{{ route('out-of-orders.create') }}')" class="mt-2 text-blue-600 hover:underline text-sm">
                                Set Out of Order sekarang
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="px-4 py-3 border-t">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
