@extends('layouts.app')

@section('title', 'Room List')
@section('header', 'Room List')

@section('content')

<!-- ── Summary Stats ────────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <!-- Sedang Menginap -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Sedang Menginap</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['staying'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-door-open text-green-500 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Sudah Checkout -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Sudah Checkout</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['checked_out'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-blue-500 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Akan Datang -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Akan Datang</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $stats['upcoming'] }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-alt text-amber-500 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- ── Tab: Sedang Menginap ─────────────────────────────────────── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-green-600 text-sm"></i>
            </div>
            <div>
                <h2 class="font-semibold text-gray-800">Sedang Menginap</h2>
                <p class="text-xs text-gray-400">Tamu yang sedang menempati kamar</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 border border-green-200 px-3 py-1 rounded-full text-xs font-semibold">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
            {{ $stats['staying'] }} tamu
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reservasi</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Malam</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($currentlyStaying as $res)
                <tr class="hover:bg-green-50/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-blue-600 text-sm">{{ $res->reservation_number }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-gray-800 text-sm">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-8 h-8 bg-green-50 text-green-600 rounded-lg flex items-center justify-center text-xs font-bold">
                                <i class="fas fa-bed"></i>
                            </span>
                            <span class="font-semibold text-gray-800 text-sm">{{ $res->room->room_number ?? '-' }}</span>
                        </span>
                        <div class="text-xs text-gray-400 mt-0.5 ml-9">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_in ? $res->check_in->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->nights }} malam
                    </td>
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-800">
                        Rp {{ number_format($res->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('reservations.show', $res) }}"
                               class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition" title="Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <form action="{{ route('reservations.checkout', $res) }}" method="POST" class="inline"
                                onsubmit="return confirm('Check-out kamar {{ $res->room->room_number ?? '' }}?')">
                                @csrf
                                <button type="submit" class="w-8 h-8 bg-yellow-50 text-yellow-600 rounded-lg flex items-center justify-center hover:bg-yellow-100 transition" title="Check-out">
                                    <i class="fas fa-sign-out-alt text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-bed text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Tidak ada tamu yang sedang menginap</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ── Tab: Akan Datang ────────────────────────────────────────── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-amber-600 text-sm"></i>
            </div>
            <div>
                <h2 class="font-semibold text-gray-800">Akan Datang</h2>
                <p class="text-xs text-gray-400">Reservasi dengan check-in di masa mendatang</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs font-semibold">
            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
            {{ $stats['upcoming'] }} reservasi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reservasi</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Malam</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($upcoming as $res)
                <tr class="hover:bg-amber-50/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-blue-600 text-sm">{{ $res->reservation_number }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-gray-800 text-sm">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xs font-bold">
                                <i class="fas fa-bed"></i>
                            </span>
                            <span class="font-semibold text-gray-800 text-sm">{{ $res->room->room_number ?? '-' }}</span>
                        </span>
                        <div class="text-xs text-gray-400 mt-0.5 ml-9">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_in ? $res->check_in->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->nights }} malam
                    </td>
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-800">
                        Rp {{ number_format($res->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('reservations.show', $res) }}"
                               class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition" title="Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
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
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-calendar-times text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Tidak ada reservasi mendatang</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ── Tab: Sudah Checkout ─────────────────────────────────────── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-blue-600 text-sm"></i>
            </div>
            <div>
                <h2 class="font-semibold text-gray-800">Sudah Checkout</h2>
                <p class="text-xs text-gray-400">Tamu yang telah menyelesaikan menginap</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full text-xs font-semibold">
            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
            {{ $stats['checked_out'] }} tamu
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reservasi</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Malam</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($checkedOut as $res)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-blue-600 text-sm">{{ $res->reservation_number }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="font-medium text-gray-800 text-sm">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xs font-bold">
                                <i class="fas fa-bed"></i>
                            </span>
                            <span class="font-semibold text-gray-800 text-sm">{{ $res->room->room_number ?? '-' }}</span>
                        </span>
                        <div class="text-xs text-gray-400 mt-0.5 ml-9">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_in ? $res->check_in->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $res->nights }} malam
                    </td>
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-800">
                        Rp {{ number_format($res->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('reservations.show', $res) }}"
                               class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-100 transition" title="Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('reservations.print-invoice', $res) }}"
                               class="w-8 h-8 bg-gray-50 text-gray-600 rounded-lg flex items-center justify-center hover:bg-gray-100 transition" title="Print Invoice">
                                <i class="fas fa-print text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-clipboard-check text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Belum ada data checkout</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
