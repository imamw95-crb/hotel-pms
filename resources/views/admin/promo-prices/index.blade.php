@extends('layouts.app')

@section('title', 'Promo Harga')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-bold">Promo Harga per Tanggal</h2>
            <p class="text-sm text-gray-500">Atur harga khusus untuk tanggal tertentu per tipe kamar</p>
        </div>
        <button onclick="Modal.open('{{ route('promo-prices.create') }}')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-pointer hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-1"></i> Tambah Promo
        </button>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('promo-prices.index') }}" class="flex flex-wrap items-end gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
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
        <a href="{{ route('promo-prices.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition cursor-pointer">
            <i class="fas fa-undo mr-1"></i> Reset
        </a>
    </form>

    {{-- Promo Price List --}}
    @if($promoPrices->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-tag text-4xl mb-3 block"></i>
            <p class="text-lg font-medium">Belum ada harga promo</p>
            <p class="text-sm">Klik "Tambah Promo" untuk menetapkan harga khusus untuk tanggal tertentu.</p>
        </div>
    @else
        @foreach($promoPrices as $roomTypeName => $prices)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 bg-gray-50 px-3 py-2 rounded-lg mb-2">
                    <i class="fas fa-door-open text-blue-500 mr-2"></i>{{ $roomTypeName }}
                    <span class="text-xs text-gray-400 font-normal ml-2">({{ $prices->count() }} tanggal)</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b text-xs text-gray-500 uppercase">
                                <th class="text-left p-2 font-medium">Tanggal</th>
                                <th class="text-left p-2 font-medium">Harga Promo</th>
                                <th class="text-left p-2 font-medium">Label</th>
                                <th class="text-left p-2 font-medium">Dibuat</th>
                                <th class="text-left p-2 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prices as $price)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-2 text-sm font-medium">{{ \Carbon\Carbon::parse($price->date)->isoFormat('DD MMM YYYY') }}</td>
                                    <td class="p-2 text-sm font-semibold text-green-600">Rp {{ number_format($price->price, 0, ',', '.') }}</td>
                                    <td class="p-2">
                                        @if($price->label)
                                            <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ $price->label }}</span>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-xs text-gray-400">{{ $price->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                                    <td class="p-2">
                                        <div class="flex items-center gap-1">
                                            <button onclick="Modal.open('{{ route('promo-prices.edit', $price) }}')" class="text-blue-600 hover:text-blue-800 text-sm px-2 py-1 hover:bg-blue-50 rounded transition cursor-pointer" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('promo-prices.destroy', $price) }}" method="POST" data-ajax="true" class="inline" onsubmit="return confirm('Hapus promo harga ini?')">
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
