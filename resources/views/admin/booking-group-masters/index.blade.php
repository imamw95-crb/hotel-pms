@extends('layouts.app')

@section('title', 'Master Group & Harga Booking Group')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-bold">Master Group & Harga</h2>
            <p class="text-sm text-gray-500">Atur master grup beserta master harga per tipe kamar untuk booking group</p>
        </div>
        <a href="{{ route('booking-group-masters.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-1"></i> Tambah Master Group
        </a>
    </div>

    <form method="GET" action="{{ route('booking-group-masters.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
        <i class="fas fa-search text-gray-400"></i>
        <div class="flex-1 min-w-[220px] max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama grup, atau deskripsi..."
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <i class="fas fa-magnifying-glass mr-1"></i> Cari
        </button>
        @if(request('search'))
            <a href="{{ route('booking-group-masters.index') }}" class="text-gray-500 hover:text-gray-700 text-sm px-2 py-2">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
        @endif
    </form>

    @if($masters->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-layer-group text-4xl mb-3 block"></i>
            @if(request('search'))
                <p class="text-lg font-medium">Tidak ada hasil untuk "{{ request('search') }}"</p>
                <p class="text-sm">Coba kata kunci lain atau klik "Reset" untuk menampilkan semua master group.</p>
            @else
                <p class="text-lg font-medium">Belum ada master group</p>
                <p class="text-sm">Klik "Tambah Master Group" untuk membuat master grup & master harga booking group.</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b text-xs text-gray-500 uppercase">
                        <th class="text-left p-2 font-medium">Kode</th>
                        <th class="text-left p-2 font-medium">Nama Grup</th>
                        <th class="text-left p-2 font-medium">Deskripsi</th>
                        <th class="text-left p-2 font-medium">Master Harga</th>
                        <th class="text-left p-2 font-medium">Status</th>
                        <th class="text-left p-2 font-medium">Dibuat Oleh</th>
                        <th class="text-left p-2 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($masters as $master)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-2 text-sm font-semibold">{{ $master->code }}</td>
                        <td class="p-2 text-sm font-medium">{{ $master->name }}</td>
                        <td class="p-2 text-sm text-gray-500 max-w-xs">
                            {{ Str::limit($master->description ?? '-', 60) }}
                        </td>
                        <td class="p-2 text-sm">
                            @if($master->prices->isEmpty())
                                <span class="text-gray-400">-</span>
                            @else
                                <div class="space-y-0.5">
                                    @foreach($master->prices->take(4) as $price)
                                        <span class="inline-flex items-center gap-1 text-xs bg-gray-100 rounded px-1.5 py-0.5">
                                            <span class="text-gray-600">{{ $price->roomType->name ?? 'Tipe #'.$price->room_type_id }}</span>
                                            <span class="font-semibold text-gray-800">Rp {{ number_format($price->price_per_night, 0, ',', '.') }}</span>
                                        </span>
                                    @endforeach
                                    @if($master->prices->count() > 4)
                                        <span class="text-xs text-gray-400">+{{ $master->prices->count() - 4 }} lainnya</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($master->is_active)
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                                    <i class="fas fa-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500">
                                    <i class="fas fa-ban"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="p-2 text-sm text-gray-500">{{ $master->creator->name ?? '-' }}</td>
                        <td class="p-2">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('booking-group-masters.edit', $master) }}" class="text-blue-600 hover:text-blue-800 text-sm px-2 py-1 hover:bg-blue-50 rounded transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('booking-group-masters.destroy', $master) }}" method="POST" data-ajax="true" class="inline" onsubmit="return confirm('Hapus master grup ini? Master harga ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm px-2 py-1 hover:bg-red-50 rounded transition" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
