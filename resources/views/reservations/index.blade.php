@extends('layouts.app')

@section('title', 'Reservasi')
@section('header', 'Data Reservasi')

@section('content')

<!-- Statistik Ringkasan -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $reservations->total() }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Pending</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Checked In</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['checked_in'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-green-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Checked Out</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['checked_out'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-blue-500"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Pencarian -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <form method="GET" action="{{ route('reservations.index') }}">
        <div class="flex flex-col md:flex-row md:items-end gap-4">
            <!-- Pencarian -->
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Pencarian
                </label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="No. reservasi, nama tamu, no. kamar..."
                        class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Filter Status -->
            <div class="w-full md:w-44">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="checked_in" {{ ($status ?? '') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ ($status ?? '') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div class="w-full md:w-40">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Tanggal Sampai -->
            <div class="w-full md:w-40">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Tombol -->
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-1.5">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('reservations.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition flex items-center gap-1.5">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tabel Reservasi -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reservasi</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reservations as $res)
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-blue-600 text-sm">{{ $res->reservation_number }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-gray-800 text-sm">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-md flex items-center justify-center text-xs font-bold">{{ $res->room->room_number ?? '-' }}</span>
                        </span>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_in ? $res->check_in->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_out ? $res->check_out->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-800">
                        Rp {{ number_format($res->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5">
                        @if($res->status === 'pending')
                            <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 border border-yellow-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Pending
                            </span>
                        @elseif($res->status === 'checked_in')
                            <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 border border-green-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Checked In
                            </span>
                        @elseif($res->status === 'checked_out')
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span> Checked Out
                            </span>
                        @elseif($res->status === 'cancelled')
                            <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Cancelled
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-700 border border-gray-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                {{ strtoupper($res->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('reservations.show', $res) }}"
                               class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition" title="Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @if($res->status === 'pending')
                                <form action="{{ route('reservations.checkin', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 bg-green-50 text-green-600 rounded-lg flex items-center justify-center hover:bg-green-100 transition" title="Check-in">
                                        <i class="fas fa-sign-in-alt text-xs"></i>
                                    </button>
                                </form>
                                <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 bg-red-50 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-100 transition" title="Cancel"
                                        onclick="return confirm('Batalkan reservasi ini?')">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </form>
                            @endif
                            @if($res->status === 'checked_in')
                                <form action="{{ route('reservations.checkout', $res) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Check-out kamar {{ $res->room->room_number ?? '' }}? Status kamar akan berubah menjadi Available.')">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center hover:bg-amber-100 transition" title="Check-out">
                                        <i class="fas fa-sign-out-alt text-xs"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-inbox text-2xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-400 font-medium">Tidak ada data reservasi ditemukan</p>
                            <p class="text-gray-300 text-sm mt-1">Coba ubah filter atau tambah reservasi baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($reservations->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
        {{ $reservations->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Tombol Floating: Booking Baru -->
<div class="fixed bottom-6 right-6 flex flex-col gap-2 z-50">
    <button type="button" onclick="Modal.open('{{ route('booking.group.create') }}')"
        class="w-12 h-12 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700 transition flex items-center justify-center"
        title="Booking Group">
        <i class="fas fa-users"></i>
    </button>
    <button type="button" onclick="Modal.open('{{ route('booking.create') }}')"
        class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition flex items-center justify-center"
        title="Booking Baru">
        <i class="fas fa-plus text-lg"></i>
    </button>
</div>

@endsection
