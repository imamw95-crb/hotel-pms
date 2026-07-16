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
        @if($res->status === 'menunggu_pembayaran')
            <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 border border-orange-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                <span class="w-1.5 h-1.5 bg-orange-500 rounded-full"></span> Menunggu Pembayaran
            </span>
        @elseif($res->status === 'pending')
            <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-[10px] font-semibold">
                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Pending
            </span>
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

            {{-- Check-in (pending & menunggu_pembayaran) --}}
            @if(in_array($res->status, ['pending', 'menunggu_pembayaran']))
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
            @if(in_array($res->status, ['pending', 'menunggu_pembayaran', 'checked_in']))
                <a href="{{ route('reservations.room-change', $res) }}"
                   class="bg-purple-50 text-purple-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1 hover:bg-purple-100 transition text-xs font-medium whitespace-nowrap" title="Pindah Kamar">
                    <i class="fas fa-exchange-alt text-[10px]"></i> <span>Pindah Kamar</span>
                </a>
            @endif

            {{-- Cancel (pending & menunggu_pembayaran) --}}
            @if(in_array($res->status, ['pending', 'menunggu_pembayaran']))
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
    <td colspan="9" class="px-4 py-16 text-center">
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
