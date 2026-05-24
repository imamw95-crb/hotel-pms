@extends('layouts.app')

@section('title', 'Reservasi')
@section('header', 'Data Reservasi')

@section('content')
<!-- Pencarian & Filter -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" action="{{ route('reservations.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Pencarian -->
            <div class="md:col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    <i class="fas fa-search mr-1"></i> Pencarian
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Cari no. reservasi, nama tamu, no. kamar..."
                        class="w-full border rounded px-3 py-2 pl-10">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="checked_in" {{ ($status ?? '') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ ($status ?? '') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full border rounded px-3 py-2">
            </div>

            <!-- Tanggal Sampai -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div class="flex justify-between items-center mt-4">
            <div class="flex space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
                <a href="{{ route('reservations.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('booking.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-plus mr-1"></i> Booking Baru
                </a>
                <a href="{{ route('booking.group.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    <i class="fas fa-users mr-1"></i> Booking Group
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tabel Reservasi -->
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
                <tr class="border-b hover:bg-gray-50">
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
                    <td class="p-3 text-sm">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                    <td class="p-3 font-medium">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</td>
                    <td class="p-3">
                        @if($res->status === 'pending')
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold">PENDING</span>
                        @elseif($res->status === 'checked_in')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">CHECKED IN</span>
                        @elseif($res->status === 'checked_out')
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">CHECKED OUT</span>
                        @elseif($res->status === 'cancelled')
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">CANCELLED</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold">{{ strtoupper($res->status) }}</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex space-x-1">
                            <a href="{{ route('reservations.show', $res) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($res->status === 'pending')
                                <form action="{{ route('reservations.checkin', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600" title="Check-in">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                </form>
                                <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600" title="Cancel" onclick="return confirm('Batalkan reservasi ini?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif
                            @if($res->status === 'checked_in')
                                <form action="{{ route('reservations.checkout', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs hover:bg-yellow-600" title="Check-out">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Tidak ada data reservasi ditemukan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($reservations->hasPages())
    <div class="p-4 border-t">
        {{ $reservations->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
