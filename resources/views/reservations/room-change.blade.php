@extends('layouts.app')

@section('title', 'Pindah Kamar')
@section('header', 'Pindah Kamar')

@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Info Reservasi Saat Ini -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2">
            <i class="fas fa-exchange-alt text-blue-500 mr-2"></i>Reservasi Saat Ini
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-gray-500 text-sm">No. Reservasi</span>
                <p class="font-bold text-blue-600">{{ $reservation->reservation_number }}</p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Status</span>
                <p>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-bold">CHECKED IN</span>
                </p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Tamu</span>
                <p class="font-medium">{{ $reservation->guest->guest_name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Kamar Saat Ini</span>
                <p class="font-medium text-xl text-red-600">
                    <i class="fas fa-door-open mr-1"></i>{{ $reservation->room->room_number ?? '-' }}
                    <span class="text-sm text-gray-500">({{ $reservation->room->room_type_name ?? '-' }})</span>
                </p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Check-in</span>
                <p class="font-medium">{{ $reservation->check_in->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Check-out</span>
                <p class="font-medium">{{ $reservation->check_out->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Harga Kamar Lama</span>
                <p class="font-medium">
                    Rp {{ number_format($reservation->room->price_per_night ?? 0, 0, ',', '.') }}
                    @if($reservation->room->price_weekday > 0 || $reservation->room->price_weekend > 0)
                        <span class="text-xs text-gray-500">(Wd: {{ number_format($reservation->room->price_weekday, 0, ',', '.') }} / We: {{ number_format($reservation->room->price_weekend, 0, ',', '.') }})</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-gray-500 text-sm">Total Tagihan Saat Ini</span>
                <p class="font-medium">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Form Pindah Kamar -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2">
            <i class="fas fa-bed text-green-500 mr-2"></i>Pilih Kamar Baru
        </h3>

        @if($availableRooms->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-2"></i>
                <p class="text-yellow-700 font-medium">Tidak ada kamar yang tersedia untuk periode ini.</p>
                <p class="text-yellow-600 text-sm mt-1">Semua kamar sudah terbooking untuk tanggal {{ $reservation->check_in->format('d/m/Y') }} - {{ $reservation->check_out->format('d/m/Y') }}.</p>
            </div>
        @else
            <form action="{{ route('reservations.room-change.store', $reservation) }}" method="POST" id="roomChangeForm" data-ajax="true">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kamar Tersedia</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($availableRooms as $room)
                            <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition {{ $loop->first ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} room-option">
                                <input type="radio" name="new_room_id" value="{{ $room->id }}" class="mt-1 mr-3" {{ $loop->first ? 'checked' : '' }} onchange="updatePriceInfo(this)" data-price-weekday="{{ $room->price_weekday ?? $room->price_per_night }}" data-price-weekend="{{ $room->price_weekend ?? $room->price_per_night }}" data-type="{{ $room->room_type_name }}" data-number="{{ $room->room_number }}">
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-bold text-lg">Kamar {{ $room->room_number }}</span>
                                            <span class="text-sm text-gray-500 ml-2">({{ $room->room_type_name }})</span>
                                        </div>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold">AVAILABLE</span>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <p><i class="fas fa-tag mr-1"></i>Harga: <span class="font-medium">Weekday Rp {{ number_format($room->price_weekday ?? $room->price_per_night, 0, ',', '.') }}</span> / <span class="font-medium">Weekend Rp {{ number_format($room->price_weekend ?? $room->price_per_night, 0, ',', '.') }}</span></p>
                                        <p><i class="fas fa-users mr-1"></i>Kapasitas: {{ $room->max_occupancy }} orang</p>
                                        @if($room->facilities)
                                            <p class="mt-1">
                                                @foreach($room->facilities as $facility)
                                                    <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded mr-1">{{ $facility }}</span>
                                                @endforeach
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('new_room_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Harga Baru -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4" id="pricePreview">
                    <h4 class="font-bold text-sm text-blue-700 mb-2"><i class="fas fa-calculator mr-1"></i>Estimasi Tagihan Baru (Weekday/Weekend)</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Harga Weekday (Baru)</span>
                            <p class="font-bold" id="newPriceWeekday">Rp {{ number_format($availableRooms->first()->price_weekday ?? $availableRooms->first()->price_per_night ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Harga Weekend (Baru)</span>
                            <p class="font-bold" id="newPriceWeekend">Rp {{ number_format($availableRooms->first()->price_weekend ?? $availableRooms->first()->price_per_night ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Total Tagihan Baru</span>
                            <p class="font-bold text-blue-600" id="newTotalAmount">Rp {{ number_format($availableRooms->first()->calculateTotalForRange($reservation->check_in, $reservation->check_out), 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Selisih</span>
                            <p class="font-bold" id="priceDifference">
                                @php
                                    $firstRoomNewTotal = $availableRooms->first()->calculateTotalForRange($reservation->check_in, $reservation->check_out);
                                    $diff = $firstRoomNewTotal - $reservation->total_amount;
                                @endphp
                                @if($diff > 0)
                                    <span class="text-red-600">+Rp {{ number_format($diff, 0, ',', '.') }}</span>
                                @elseif($diff < 0)
                                    <span class="text-green-600">-Rp {{ number_format(abs($diff), 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-600">Rp 0</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pindah Kamar <span class="text-gray-400">(opsional)</span></label>
                    <textarea name="reason" rows="2" class="w-full border rounded px-3 py-2 text-sm" placeholder="Contoh: Permintaan tamu, kamar bermasalah, upgrade, dll.">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between items-center pt-4 border-t">
                    <a href="{{ route('reservations.show', $reservation) }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-1"></i> Batal
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-exchange-alt mr-1"></i> Proses Pindah Kamar
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

@php
    $checkInDate = $reservation->check_in->format('Y-m-d');
    $checkOutDate = $reservation->check_out->format('Y-m-d');
@endphp
<script>
    const currentTotal = {{ $reservation->total_amount }};
    const ci = '{{ $checkInDate }}';
    const co = '{{ $checkOutDate }}';

    function calcRangeTotal(wd, we, start, end) {
        if (!start || !end || !wd) return 0;
        const d1 = new Date(start);
        const d2 = new Date(end);
        if (d2 <= d1) return 0;
        let total = 0;
        const cur = new Date(d1);
        while (cur < d2) {
            const day = cur.getDay();
            total += (day === 0 || day === 6) ? (we || wd) : wd;
            cur.setDate(cur.getDate() + 1);
        }
        return total;
    }

    function updatePriceInfo(radio) {
        const wd = parseFloat(radio.dataset.priceWeekday);
        const we = parseFloat(radio.dataset.priceWeekend);
        const newTotal = calcRangeTotal(wd, we, ci, co);
        const diff = newTotal - currentTotal;

        document.getElementById('newPriceWeekday').textContent = 'Rp ' + wd.toLocaleString('id-ID');
        document.getElementById('newPriceWeekend').textContent = 'Rp ' + we.toLocaleString('id-ID');
        document.getElementById('newTotalAmount').textContent = 'Rp ' + newTotal.toLocaleString('id-ID');

        const diffEl = document.getElementById('priceDifference');
        if (diff > 0) {
            diffEl.innerHTML = '<span class="text-red-600">+Rp ' + diff.toLocaleString('id-ID') + '</span>';
        } else if (diff < 0) {
            diffEl.innerHTML = '<span class="text-green-600">-Rp ' + Math.abs(diff).toLocaleString('id-ID') + '</span>';
        } else {
            diffEl.innerHTML = '<span class="text-gray-600">Rp 0</span>';
        }

        // Update selected styling
        document.querySelectorAll('.room-option').forEach(el => {
            el.classList.remove('border-blue-500', 'bg-blue-50');
            el.classList.add('border-gray-200');
        });
        radio.closest('.room-option').classList.remove('border-gray-200');
        radio.closest('.room-option').classList.add('border-blue-500', 'bg-blue-50');
    }

    document.getElementById('roomChangeForm')?.addEventListener('submit', function(e) {
        const selectedRoom = document.querySelector('input[name="new_room_id"]:checked');
        if (!selectedRoom) {
            alert('Pilih kamar tujuan terlebih dahulu!');
            e.preventDefault();
            return;
        }

        const roomNumber = selectedRoom.dataset.number;
        const roomType = selectedRoom.dataset.type;

        if (!confirm('Pindah kamar ke Kamar ' + roomNumber + ' (' + roomType + ')?\n\nKamar lama akan diubah statusnya menjadi Cleaning.')) {
            e.preventDefault();
        }
    });
</script>
@endsection
