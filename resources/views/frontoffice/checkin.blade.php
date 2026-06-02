@extends('layouts.app')

@section('title', 'Check-in')

@section('content')

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Daftar Tamu Siap Check-in</h2>
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center">
            <i class="fas fa-print mr-2"></i> Print
        </button>
    </div>

    <form method="GET" action="{{ route('checkin.index') }}" class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
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
                <a href="{{ route('checkin.index') }}" class="flex-1 text-center bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="px-4 py-3 border border-gray-200">No. Reservasi</th>
                    <th class="px-4 py-3 border border-gray-200">Nama Tamu</th>
                    <th class="px-4 py-3 border border-gray-200">Kamar</th>
                    <th class="px-4 py-3 border border-gray-200">Check-in</th>
                    <th class="px-4 py-3 border border-gray-200">Check-out</th>
                    <th class="px-4 py-3 border border-gray-200">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingReservations as $reservation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 border border-gray-200">{{ $reservation->reservation_number }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $reservation->guest->guest_name }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $reservation->room->room_number }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $reservation->check_in->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 border border-gray-200">{{ $reservation->check_out->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 border border-gray-200">
                            <form method="POST" action="{{ route('reservations.checkin', $reservation->id) }}" class="inline" data-ajax="true">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                    <i class="fas fa-sign-in-alt mr-1"></i> Check-in
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-600">Tidak ada tamu yang siap check-in.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        body {
            background: white;
        }
        .bg-white {
            border: none;
            box-shadow: none;
        }
        button, form, a {
            display: none !important;
        }
        table {
            page-break-inside: avoid;
        }
        tr {
            page-break-inside: avoid;
        }
    }
</style>

@endsection
