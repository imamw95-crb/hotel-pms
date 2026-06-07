@extends('layouts.app')

@section('title', 'Reservasi')
@section('header')
    <div class="flex items-center gap-2 w-full flex-wrap">
        <span class="whitespace-nowrap">Data Reservasi</span>
        <div class="flex items-center gap-1.5 ml-auto">
            <button type="button" onclick="Modal.open('{{ route('booking.create') }}')"
                class="bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-700 transition flex items-center gap-1 whitespace-nowrap">
                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Booking</span> Single
            </button>
            <button type="button" onclick="Modal.open('{{ route('booking.group.create') }}')"
                class="bg-indigo-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-700 transition flex items-center gap-1 whitespace-nowrap">
                <i class="fas fa-users"></i> <span class="hidden sm:inline">Booking</span> Group
            </button>
            <button type="button" onclick="AiChat.toggle()"
                class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:from-blue-600 hover:to-indigo-700 transition flex items-center gap-1 shadow-sm whitespace-nowrap"
                title="AI Assistant">
                <i class="fas fa-robot"></i> <span class="hidden sm:inline">AI</span>
            </button>
        </div>
    </div>
@endsection

@section('content')

<!-- Statistik Ringkasan -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Total</p>
                <p class="text-xl font-bold text-gray-800 mt-0.5">{{ $reservations->total() }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Pending</p>
                <p class="text-xl font-bold text-yellow-600 mt-0.5">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Website</p>
                <p class="text-xl font-bold text-sky-600 mt-0.5">{{ $stats['website'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-sky-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-globe text-sky-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">OTA</p>
                <p class="text-xl font-bold text-purple-600 mt-0.5">{{ $stats['ota'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-link text-purple-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Checked In</p>
                <p class="text-xl font-bold text-green-600 mt-0.5">{{ $stats['checked_in'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-green-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Checked Out</p>
                <p class="text-xl font-bold text-blue-600 mt-0.5">{{ $stats['checked_out'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-blue-500"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Cancelled</p>
                <p class="text-xl font-bold text-red-500 mt-0.5">{{ $stats['cancelled'] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-ban text-red-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Pencarian -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('reservations.index') }}" data-turbo="false">
        <!-- Baris 1: Pencarian -->
        <div class="mb-3">
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">
                Pencarian
            </label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari no. reservasi, nama tamu, no. kamar..."
                    class="w-full border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <!-- Baris 2: Filter + Tombol -->
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <!-- Filter Status -->
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="checked_in" {{ ($status ?? '') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ ($status ?? '') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Filter Sumber -->
            <div class="w-full sm:w-24">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Sumber</label>
                <select name="sumber" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua</option>
                    <option value="website" {{ ($sumber ?? '') === 'website' ? 'selected' : '' }}>🌐 Website</option>
                    <option value="ota" {{ ($sumber ?? '') === 'ota' ? 'selected' : '' }}>🔗 OTA</option>
                    <option value="local" {{ ($sumber ?? '') === 'local' ? 'selected' : '' }}>🏨 Local</option>
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Tanggal Sampai -->
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Tombol -->
            <div class="flex gap-2 flex-shrink-0">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('reservations.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition flex items-center gap-1.5 whitespace-nowrap">
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
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Reservasi</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Sarapan</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $today = \Carbon\Carbon::today(); @endphp
                @forelse($reservations as $res)
                @php
                    $isDueOut = $res->status === 'checked_in' && $res->check_out && \Carbon\Carbon::parse($res->check_out)->toDateString() === $today->toDateString();
                @endphp
                <tr class="hover:bg-blue-50/30 transition-colors {{ $isDueOut ? 'bg-amber-50/60' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('reservations.show', $res) }}" class="font-semibold text-blue-600 text-sm hover:text-blue-800 hover:underline">
                                {{ $res->reservation_number }}
                            </a>
                            @if($res->ota_source === 'website')
                                <span class="inline-flex items-center gap-0.5 bg-sky-100 text-sky-700 border border-sky-200 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" title="Booking dari Website">
                                    <i class="fas fa-globe text-[8px]"></i> Web
                                </span>
                            @endif
                            @if($res->ota_reservation_number)
                                <span class="inline-flex items-center gap-0.5 bg-purple-100 text-purple-700 border border-purple-200 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" title="OTA: {{ $res->ota_reservation_number }}">
                                    <i class="fas fa-globe text-[8px]"></i> OTA
                                </span>
                            @endif
                            @if($isDueOut)
                                <span class="inline-flex items-center gap-0.5 bg-amber-100 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase">
                                    <i class="fas fa-exclamation-triangle text-[8px]"></i> Due Out
                                </span>
                            @endif
                        </div>
                        @if($res->ota_reservation_number)
                            <p class="text-[10px] text-purple-500 mt-0.5"><i class="fas fa-globe mr-0.5"></i>{{ $res->ota_reservation_number }}</p>
                        @endif
                        @if($res->status === 'cancelled')
                            <p class="text-[10px] text-red-400 mt-0.5"><i class="fas fa-ban mr-0.5"></i>Dibatalkan</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 text-sm">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg inline-flex items-center justify-center text-xs font-bold">{{ $res->room->room_number ?? '-' }}</span>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $res->check_in ? \Carbon\Carbon::parse($res->check_in)->format('d/m/Y') : '-' }}
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->check_in ? \Carbon\Carbon::parse($res->check_in)->format('H:i') : '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $res->check_out ? \Carbon\Carbon::parse($res->check_out)->format('d/m/Y') : '-' }}
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->check_out ? \Carbon\Carbon::parse($res->check_out)->format('H:i') : '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button"
                            onclick="toggleBreakfast({{ $res->id }}, this)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm
                                @if($res->include_breakfast) bg-amber-100 text-amber-700 border-amber-300
                                @else bg-gray-50 text-gray-400 border-gray-200 hover:text-amber-600 hover:border-amber-300 @endif"
                            title="Klik untuk toggle sarapan">
                            @if($res->include_breakfast)
                                <i class="fas fa-coffee"></i>
                            @else
                                <i class="fas fa-coffee text-[8px] opacity-40"></i>
                            @endif
                        </button>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-800 text-right">
                        Rp {{ number_format($res->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($res->status === 'pending')
                            @if($res->ota_source === 'website')
                                <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 border border-orange-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                    <span class="w-1.5 h-1.5 bg-orange-500 rounded-full"></span> Menunggu Pembayaran
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Pending
                                </span>
                            @endif
                        @elseif($res->status === 'checked_in')
                            <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Checked In
                            </span>
                        @elseif($res->status === 'checked_out')
                            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span> Checked Out
                            </span>
                        @elseif($res->status === 'cancelled')
                            <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Cancelled
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-700 border border-gray-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                {{ strtoupper($res->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center flex-wrap gap-1.5">
                            {{-- Detail --}}
                            <a href="{{ route('reservations.show', $res) }}"
                               class="bg-blue-50 text-blue-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-blue-100 transition text-xs font-medium whitespace-nowrap" title="Detail Reservasi">
                                <i class="fas fa-eye text-[10px]"></i> <span>Detail</span>
                            </a>

                            {{-- Check-in (pending) --}}
                            @if($res->status === 'pending')
                                <form action="{{ route('reservations.checkin', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-50 text-green-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-green-100 transition text-xs font-medium whitespace-nowrap" title="Check-in">
                                        <i class="fas fa-sign-in-alt text-[10px]"></i> <span>Check-in</span>
                                    </button>
                                </form>
                            @endif

                            {{-- Checkout (checked_in) --}}
                            @if($res->status === 'checked_in')
                                <form action="{{ route('reservations.checkout', $res) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Check-out kamar {{ $res->room->room_number ?? '' }}? Status kamar akan berubah menjadi Available.')">
                                    @csrf
                                    <button type="submit" class="bg-amber-50 text-amber-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-amber-100 transition text-xs font-medium whitespace-nowrap" title="Check-out">
                                        <i class="fas fa-sign-out-alt text-[10px]"></i> <span>Checkout</span>
                                    </button>
                                </form>
                            @endif

                            {{-- Pindah Kamar (pending & checked_in) --}}
                            @if(in_array($res->status, ['pending', 'checked_in']))
                                <a href="{{ route('reservations.room-change', $res) }}"
                                   class="bg-purple-50 text-purple-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-purple-100 transition text-xs font-medium whitespace-nowrap" title="Pindah Kamar">
                                    <i class="fas fa-exchange-alt text-[10px]"></i> <span>Pindah Kamar</span>
                                </a>
                            @endif

                            {{-- Cancel (pending) --}}
                            @if($res->status === 'pending')
                                <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-red-50 text-red-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-red-100 transition text-xs font-medium whitespace-nowrap" title="Cancel"
                                        onclick="return confirm('Batalkan reservasi ini?')">
                                        <i class="fas fa-times text-[10px]"></i> <span>Cancel</span>
                                    </button>
                                </form>
                            @endif

                            {{-- Print (checked_out) --}}
                            @if($res->status === 'checked_out')
                                <a href="{{ route('reservations.print-invoice', $res) }}" target="_blank"
                                   class="bg-slate-50 text-slate-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-slate-100 transition text-xs font-medium whitespace-nowrap" title="Print Invoice">
                                    <i class="fas fa-file-invoice text-[10px]"></i> <span>Invoice</span>
                                </a>
                                <a href="{{ route('reservations.print-kwitansi', $res) }}" target="_blank"
                                   class="bg-slate-50 text-slate-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-slate-100 transition text-xs font-medium whitespace-nowrap" title="Print Kwitansi">
                                    <i class="fas fa-receipt text-[10px]"></i> <span>Kwitansi</span>
                                </a>
                            @endif

                            {{-- Edit Total --}}
                            <button type="button" onclick="openEditTotalModal({{ $res->id }}, '{{ $res->reservation_number }}', {{ $res->total_amount }})"
                                class="bg-gray-50 text-gray-500 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-gray-100 transition text-xs font-medium whitespace-nowrap" title="Edit Total">
                                <i class="fas fa-edit text-[10px]"></i> <span>Edit Total</span>
                            </button>

                            {{-- Edit Harga Kamar --}}
                            <button type="button" onclick="openEditRateModal({{ $res->id }}, '{{ $res->reservation_number }}', {{ $res->room->price_per_night ?? 0 }}, {{ $res->custom_room_rate ?? 'null' }})"
                                class="bg-teal-50 text-teal-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-teal-100 transition text-xs font-medium whitespace-nowrap" title="Edit Harga Kamar">
                                <i class="fas fa-bed text-[10px]"></i> <span>Edit Rate</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-inbox text-xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-400 font-medium text-sm">Tidak ada data reservasi ditemukan</p>
                            <p class="text-gray-300 text-xs mt-1">Coba ubah filter atau tambah reservasi baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($reservations->hasPages())
    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $reservations->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Tombol Floating: Aksi Cepat -->
<div class="fixed bottom-6 right-6 flex flex-col gap-2 z-50">
    <a href="{{ route('checkout.index') }}"
        class="w-11 h-11 bg-amber-500 text-white rounded-full shadow-lg hover:bg-amber-600 transition flex items-center justify-center group relative"
        title="Checkout">
        <i class="fas fa-sign-out-alt"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Checkout</span>
    </a>
    <a href="{{ route('room-change.index') }}"
        class="w-11 h-11 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700 transition flex items-center justify-center group relative"
        title="Pindah Kamar">
        <i class="fas fa-exchange-alt"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Pindah Kamar</span>
    </a>
    <button type="button" onclick="Modal.open('{{ route('booking.group.create') }}')"
        class="w-11 h-11 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition flex items-center justify-center group relative"
        title="Booking Group">
        <i class="fas fa-users"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Grup</span>
    </button>
    <button type="button" onclick="Modal.open('{{ route('booking.create') }}')"
        class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition flex items-center justify-center group relative"
        title="Booking Baru">
        <i class="fas fa-plus text-lg"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Booking Baru</span>
    </button>
</div>

<!-- Edit Room Rate Modal -->
<div id="editRateModal" class="fixed inset-0 z-[100] hidden items-center justify-center" style="background: rgba(0,0,0,0.5);">
    <div class="rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-white">
            <h3 class="text-base font-bold text-gray-800">
                <i class="fas fa-bed text-teal-500 mr-2"></i>Edit Harga Kamar
            </h3>
            <button onclick="closeEditRateModal()" class="text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-5 py-4 space-y-3 bg-white">
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">No. Reservasi</label>
                <input type="text" id="rateModalReservationNumber" readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Harga Default Kamar (Rp/malam)</label>
                <input type="text" id="rateModalDefaultPrice" readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-400">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Custom Harga Kamar (Rp/malam)</label>
                <input type="text" id="rateModalCustomRate" placeholder="Kosongkan untuk pakai harga default"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                <p class="text-[10px] text-gray-400 mt-1">Biarkan kosong untuk mengembalikan ke harga default kamar.</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-700">Jumlah Malam:</span>
                    <span class="font-bold text-blue-800" id="rateModalNights">-</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-blue-700">Total Baru:</span>
                    <span class="font-bold text-blue-800" id="rateModalNewTotal">-</span>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50">
            <button onclick="closeEditRateModal()"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-500 transition">
                Batal
            </button>
            <button id="btnSaveRate"
                class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition flex items-center gap-1.5">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- Edit Total Modal -->
<div id="editTotalModal" class="fixed inset-0 z-[100] hidden items-center justify-center" style="background: rgba(0,0,0,0.5);">
    <div class="rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-white">
            <h3 class="text-base font-bold text-gray-800">
                <i class="fas fa-edit text-blue-500 mr-2"></i>Edit Total Reservasi
            </h3>
            <button onclick="closeEditTotalModal()" class="text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-5 py-4 space-y-3 bg-white">
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">No. Reservasi</label>
                <input type="text" id="modalReservationNumber" readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Amount (Rp)</label>
                <input type="text" id="modalTotalAmount"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50">
            <button onclick="closeEditTotalModal()"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
            <button id="btnSaveTotal"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-1.5">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var editTotalReservationId = null;

    function openEditTotalModal(id, reservationNumber, totalAmount) {
        editTotalReservationId = id;
        document.getElementById('modalReservationNumber').value = reservationNumber;
        document.getElementById('modalTotalAmount').value = new window.Intl.NumberFormat('id-ID').format(totalAmount);
        const modal = document.getElementById('editTotalModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            const input = document.getElementById('modalTotalAmount');
            input.focus();
            input.select();
        }, 100);
    }

    function closeEditTotalModal() {
        const modal = document.getElementById('editTotalModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        editTotalReservationId = null;
    }

    // Format rupiah input on typing
    document.getElementById('modalTotalAmount').addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = new window.Intl.NumberFormat('id-ID').format(value);
        }
    });

    document.getElementById('btnSaveTotal').addEventListener('click', function() {
        if (!editTotalReservationId) return;

        const amountInput = document.getElementById('modalTotalAmount').value.replace(/[^0-9]/g, '');
        const amount = parseInt(amountInput);

        if (isNaN(amount) || amount < 0) {
            alert('Nominal tidak valid');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('/reservations/' + editTotalReservationId + '/update-total', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ total_amount: amount }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditTotalModal();
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || 'Gagal menyimpan');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });


    // ===== Edit Room Rate Modal =====
    var editRateReservationId = null;
    var editRateNights = 1;
    var editRateDefaultPrice = 0;

    function openEditRateModal(id, reservationNumber, defaultPrice, customRate) {
        editRateReservationId = id;
        editRateDefaultPrice = defaultPrice;
        editRateNights = 1;

        document.getElementById('rateModalReservationNumber').value = reservationNumber;
        document.getElementById('rateModalDefaultPrice').value = 'Rp ' + new window.Intl.NumberFormat('id-ID').format(defaultPrice);

        // Get nights from the table row
        const row = document.querySelector('button[onclick*="openEditRateModal(' + id + '"]')?.closest('tr');
        if (row) {
            const checkInCell = row.querySelector('td:nth-child(4)');
            const checkOutCell = row.querySelector('td:nth-child(5)');
            if (checkInCell && checkOutCell) {
                const ciText = checkInCell.innerText.trim().split('\n')[0];
                const coText = checkOutCell.innerText.trim().split('\n')[0];
                const ciParts = ciText.split('/');
                const coParts = coText.split('/');
                if (ciParts.length === 3 && coParts.length === 3) {
                    const ci = new window.Date(ciParts[2], ciParts[1]-1, ciParts[0]);
                    const co = new window.Date(coParts[2], coParts[1]-1, coParts[0]);
                    editRateNights = Math.max(1, Math.round((co - ci) / (1000 * 60 * 60 * 24)));
                }
            }
        }

        document.getElementById('rateModalNights').textContent = editRateNights + ' malam';

        const customRateInput = document.getElementById('rateModalCustomRate');
        if (customRate && customRate !== null) {
            customRateInput.value = new window.Intl.NumberFormat('id-ID').format(customRate);
        } else {
            customRateInput.value = '';
        }

        updateRatePreview();

        const modal = document.getElementById('editRateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => customRateInput.focus(), 100);
    }

    function closeEditRateModal() {
        const modal = document.getElementById('editRateModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        editRateReservationId = null;
    }

    function updateRatePreview() {
        const val = document.getElementById('rateModalCustomRate').value.replace(/[^0-9]/g, '');
        const rate = parseInt(val) || editRateDefaultPrice;
        const total = rate * editRateNights;
        document.getElementById('rateModalNewTotal').textContent = 'Rp ' + new window.Intl.NumberFormat('id-ID').format(total);
    }

    document.getElementById('rateModalCustomRate').addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = new window.Intl.NumberFormat('id-ID').format(value);
        }
        updateRatePreview();
    });

    document.getElementById('btnSaveRate').addEventListener('click', function() {
        if (!editRateReservationId) return;

        const rawVal = document.getElementById('rateModalCustomRate').value.replace(/[^0-9]/g, '');
        const customRate = rawVal === '' ? null : parseInt(rawVal);

        if (customRate !== null && customRate < 0) {
            alert('Nominal tidak valid');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('{{ url('reservations') }}/' + editRateReservationId + '/update-room-rate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ custom_room_rate: customRate }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditRateModal();
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || 'Gagal menyimpan');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    document.getElementById('editRateModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditRateModal();
    });

    // Close modal on Escape key (guard untuk Turbo agar tidak menumpuk listener)
    if (!window._reservationsKeydownInit) {
        window._reservationsKeydownInit = true;
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditTotalModal();
        });
    }
    document.getElementById('editTotalModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditTotalModal();
    });

    // ─── Toggle Sarapan ──────────────────────────────────────────
    function toggleBreakfast(reservationId, btn) {
        fetch('{{ url("reservations") }}/' + reservationId + '/toggle-breakfast', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.include_breakfast) {
                    btn.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm bg-amber-100 text-amber-700 border-amber-300';
                    btn.innerHTML = '<i class="fas fa-coffee"></i>';
                } else {
                    btn.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm bg-gray-50 text-gray-400 border-gray-200 hover:text-amber-600 hover:border-amber-300';
                    btn.innerHTML = '<i class="fas fa-coffee text-[8px] opacity-40"></i>';
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
            }
        })
        .catch(function() {
            if (typeof Toast !== 'undefined') {
                Toast.error('Gagal mengubah status sarapan');
            }
        });
    }

    {{-- ─── Auto-Refresh: Deteksi Booking Baru (DINONAKTIFKAN) ───
    (function() {
        var pageLoadedAt = new window.Date().toISOString();
        var refreshInterval = 20000;
        var refreshTimer = null;
        var isRefreshing = false;

        function checkNewBookings() {
            if (isRefreshing) return;
            fetch('{{ route("reservations.check-new") }}?since=' + encodeURIComponent(pageLoadedAt), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.has_new) {
                    isRefreshing = true;
                    if (typeof Toast !== 'undefined') {
                        Toast.info(data.count + ' booking baru ditemukan. Memperbarui halaman...');
                    }
                    setTimeout(function() { location.reload(); }, 1500);
                }
            })
            .catch(function() {});
        }

        if (document.readyState === 'complete') {
            refreshTimer = setInterval(checkNewBookings, refreshInterval);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                refreshTimer = setInterval(checkNewBookings, refreshInterval);
            });
        }

        document.addEventListener('turbo:before-visit', function() {
            if (refreshTimer) clearInterval(refreshTimer);
        });
    })();
    --}}
</script>
@endsection