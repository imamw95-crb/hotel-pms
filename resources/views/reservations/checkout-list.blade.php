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

<!-- Tabel Checkout -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left p-3 text-sm font-semibold">No. Reservasi</th>
                    <th class="text-left p-3 text-sm font-semibold">Nama Tamu</th>
                    <th class="text-left p-3 text-sm font-semibold">Kamar</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-in</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-out</th>
                    <th class="text-left p-3 text-sm font-semibold">Total</th>
                    <th class="text-left p-3 text-sm font-semibold">Status</th>
                    <th class="text-left p-3 text-sm font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                @php
                    $isDueOut = $res->check_out <= \Carbon\Carbon::today()->setTime(12, 0, 0);
                @endphp
                <tr class="border-b hover:bg-gray-50 {{ $isDueOut ? 'bg-amber-50' : '' }}">
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
                    <td class="p-3">
                        <div class="flex space-x-1">
                            <a href="{{ route('reservations.show', $res) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('reservations.checkout', $res) }}" method="POST" class="inline" onsubmit="return confirm('Check-out kamar {{ $res->room->room_number ?? '' }}? Status kamar akan berubah menjadi Available.')">
                                @csrf
                                <button type="submit" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs hover:bg-yellow-600" title="Check-out">
                                    <i class="fas fa-sign-out-alt"></i> Checkout
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-gray-500">
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
