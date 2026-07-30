@extends('layouts.app')

@section('title', 'Checkout')
@section('header', 'Checkout Kamar')

@section('content')
<!-- Stats -->
<div class="stats-grid mb-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
        <p class="text-xs text-red-600 font-medium">Occupied</p>
        <p class="text-2xl font-bold text-red-600">{{ $reservations->count() }}</p>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center">
        <p class="text-xs text-amber-600 font-medium">Due Out Hari Ini</p>
        <p class="text-2xl font-bold text-amber-600">{{ $reservations->where('check_out', '<=', \Carbon\Carbon::today()->setTime(12,0,0))->count() }}</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
        <p class="text-xs text-blue-600 font-medium">Total Tagihan</p>
        <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($reservations->sum('total_amount'), 0, ',', '.') }}</p>
    </div>
</div>

<!-- Filter -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('checkout.index') }}" class="mb-2">
        <div class="grid gap-4 lg:grid-cols-5">
            <div>
                <label class="block text-gray-700 mb-2 text-sm">Cari (Nama / Kode / Kamar)</label>
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
            <div>
                <label class="block text-gray-700 mb-2 text-sm">Kamar</label>
                <select name="room_id" class="w-full border rounded px-3 py-2">
                    <option value="">Semua Kamar</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->room_number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('checkout.index') }}" class="flex-1 text-center bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Batch Checkout Button -->
<div class="mb-4">
    <button id="btnBatchCheckout" onclick="batchCheckout()" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 flex items-center font-semibold shadow disabled:opacity-50 disabled:cursor-not-allowed" disabled>
        <i class="fas fa-sign-out-alt mr-2"></i> <span id="batchCheckoutCount">0</span> Checkout <strong>ALL</strong>
    </button>
</div>

<!-- Tabel Checkout -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left p-3 text-sm font-semibold w-10">
                        <input type="checkbox" id="checkAll" onchange="toggleAllCheckboxes(this)" class="w-4 h-4 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 cursor-pointer">
                    </th>
                    <th class="text-left p-3 text-sm font-semibold">No. Reservasi</th>
                    <th class="text-left p-3 text-sm font-semibold">Nama Tamu</th>
                    <th class="text-left p-3 text-sm font-semibold">Kamar</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-in</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-out</th>
                    <th class="text-left p-3 text-sm font-semibold">Total</th>
                    <th class="text-left p-3 text-sm font-semibold">Status</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-in Oleh</th>
                    <th class="text-left p-3 text-sm font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                @php
                    $isDueOut = $res->check_out <= \Carbon\Carbon::today()->setTime(12, 0, 0);
                @endphp
                <tr class="border-b hover:bg-gray-50 {{ $isDueOut ? 'bg-amber-50' : '' }}">
                    <td class="p-3 text-center">
                        <input type="checkbox" name="checkout_ids[]" value="{{ $res->id }}" class="checkout-checkbox w-4 h-4 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 cursor-pointer" onchange="updateBatchCheckoutBtn()">
                    </td>
                    <td class="p-3 font-medium text-blue-600">{{ $res->reservation_number }}</td>
                    <td class="p-3">
                        <div class="font-medium">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="p-3">
                        <span class="font-bold">{{ $res->room->room_number ?? '-' }}</span>
                        <div class="text-xs text-gray-500">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="p-3 text-sm">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                    <td class="p-3 text-sm {{ $isDueOut ? 'text-red-600 font-bold' : '' }}">
                        {{ $res->check_out->format('d/m/Y H:i') }}
                        @if($isDueOut)
                            <span class="block text-xs text-red-500">⚠ Due Out</span>
                        @endif
                    </td>
                    <td class="p-3 font-medium">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</td>
                    <td class="p-3">
                        @if($isDueOut)
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">DUE OUT</span>
                        @else
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">CHECKED IN</span>
                        @endif
                    </td>
                    <td class="p-3 text-sm">
                        @if($res->checkedInBy)
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-[10px] font-bold">
                                    {{ substr($res->checkedInBy->name ?? 'U', 0, 1) }}
                                </span>
                                <span>{{ $res->checkedInBy->name }}</span>
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex space-x-1">
                            <a href="{{ route('reservations.show', $res) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" onclick="confirmCheckout({{ $res->id }}, '{{ $res->reservation_number }}', '{{ addslashes($res->guest->guest_name ?? '') }}', '{{ $res->room->room_number ?? '' }}', '{{ $res->room->room_type_name ?? '' }}')" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs hover:bg-yellow-600" title="Check-out">
                                <i class="fas fa-sign-out-alt"></i> Checkout
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="p-8 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-2 text-green-400"></i>
                        <p>Tidak ada kamar yang perlu di-checkout</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
