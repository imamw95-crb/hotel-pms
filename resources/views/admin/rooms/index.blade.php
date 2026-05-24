@extends('layouts.app')

@section('title', 'Manajemen Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Daftar Kamar</h2>
        <a href="{{ route('rooms.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">
            <i class="fas fa-plus"></i> Tambah Kamar
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">No. Kamar</th>
                    <th class="text-left p-2">Tipe Kamar</th>
                    <th class="text-left p-2">Harga/malam</th>
                    <th class="text-left p-2">Max Occupancy</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                <tr class="border-b">
                    <td class="p-2">{{ $room->room_number }}</td>
                    <td class="p-2">{{ $room->room_type_name ?? '-' }}</td>
                    <td class="p-2">Rp {{ number_format($room->price_per_night,0,',','.') }}</td>
                    <td class="p-2">{{ $room->max_occupancy }} orang</td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded text-xs 
                            @if($room->status == 'available') bg-green-100 text-green-800
                            @elseif($room->status == 'occupied') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($room->status) }}
                        </span>
                    </td>
                    <td class="p-2">
                        <a href="{{ route('rooms.edit', $room) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2" onclick="return confirm('Hapus kamar ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection