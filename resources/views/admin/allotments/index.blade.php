@extends('layouts.app')

@section('title', 'Allotment Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-bold">Allotment Kamar</h2>
            <p class="text-sm text-gray-500">Atur jumlah kamar yang dialokasikan per tipe kamar per tanggal</p>
        </div>
        <button onclick="Modal.open('{{ route('allotments.create') }}')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-pointer hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-1"></i> Tambah Allotment
        </button>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('allotments.index') }}" class="flex flex-wrap items-end gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Kamar</label>
            <select name="room_type_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ request('room_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700 transition cursor-pointer">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('allotments.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition cursor-pointer">
            <i class="fas fa-undo mr-1"></i> Reset
        </a>
    </form>

    {{-- Summary Info --}}
    @php
        $totalAllotments = 0;
        $totalRooms = 0;
        foreach($allotments as $roomTypeName => $items) {
            $totalAllotments += $items->count();
        }
    @endphp
    <div class="flex items-center gap-4 mb-4 text-sm text-gray-500">
        <span><i class="fas fa-layer-group mr-1"></i> Total allotment: <strong>{{ $totalAllotments }}</strong> entri</span>
        <span><i class="fas fa-door-open mr-1"></i> Tipe kamar: <strong>{{ $allotments->count() }}</strong></span>
    </div>

    {{-- Allotment List --}}
    @if($allotments->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-warehouse text-4xl mb-3 block"></i>
            <p class="text-lg font-medium">Belum ada allotment</p>
            <p class="text-sm">Klik "Tambah Allotment" untuk mengatur alokasi kamar per tipe kamar.</p>
        </div>
    @else
        @foreach($allotments as $roomTypeName => $items)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 bg-gray-50 px-3 py-2 rounded-lg mb-2">
                    <i class="fas fa-door-open text-blue-500 mr-2"></i>{{ $roomTypeName }}
                    <span class="text-xs text-gray-400 font-normal ml-2">({{ $items->count() }} entri)</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b text-xs text-gray-500 uppercase">
                                <th class="text-left p-2 font-medium">Tanggal</th>
                                <th class="text-left p-2 font-medium">Allotment</th>
                                <th class="text-left p-2 font-medium">Terbooking</th>
                                <th class="text-left p-2 font-medium">Sisa</th>
                                <th class="text-left p-2 font-medium">Harga</th>
                                <th class="text-left p-2 font-medium">Status</th>
                                <th class="text-left p-2 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $allotment)
                                @php
                                    $remaining = $allotment->allotment - $allotment->booked;
                                    $statusClass = $remaining <= 0 ? 'text-red-600 bg-red-50' : ($remaining <= 3 ? 'text-yellow-600 bg-yellow-50' : 'text-green-600 bg-green-50');
                                    $statusIcon = $remaining <= 0 ? 'fa-times-circle' : ($remaining <= 3 ? 'fa-exclamation-circle' : 'fa-check-circle');
                                @endphp
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-2 text-sm font-medium">{{ \Carbon\Carbon::parse($allotment->date)->isoFormat('DD MMM YYYY') }}</td>
                                    <td class="p-2 text-sm font-semibold">{{ $allotment->allotment }}</td>
                                    <td class="p-2 text-sm">{{ $allotment->booked }}</td>
                                    <td class="p-2 text-sm font-semibold {{ $remaining <= 0 ? 'text-red-600' : ($remaining <= 3 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $remaining }}
                                    </td>
                                    <td class="p-2 text-sm">
                                        @if($allotment->price)
                                            <span class="font-semibold text-gray-800">Rp {{ number_format($allotment->price, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-400">Rp {{ number_format($allotment->getEffectivePrice(), 0, ',', '.') }}</span>
                                            <span class="text-xs text-gray-400 ml-1">(master)</span>
                                        @endif
                                    </td>
                                    <td class="p-2">
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $statusClass }}">
                                            <i class="fas {{ $statusIcon }}"></i>
                                            @if($remaining <= 0) Penuh
                                            @elseif($remaining <= 3) Hampir Penuh
                                            @else Tersedia
                                            @endif
                                        </span>
                                    </td>
                                    <td class="p-2">
                                        <div class="flex items-center gap-1">
                                            <button onclick="Modal.open('{{ route('allotments.edit', $allotment) }}')" class="text-blue-600 hover:text-blue-800 text-sm px-2 py-1 hover:bg-blue-50 rounded transition cursor-pointer" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('allotments.destroy', $allotment) }}" method="POST" data-ajax="true" class="inline" onsubmit="return confirm('Hapus allotment ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm px-2 py-1 hover:bg-red-50 rounded transition cursor-pointer" title="Hapus">
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
            </div>
        @endforeach
    @endif
</div>
@endsection
