@forelse($rooms as $room)
    @php
        $isDueOut = in_array($room->id, $dueOutRoomIds ?? []);
        $statusColor = [
            'available' => 'bg-green-100 border-green-500 text-green-800',
            'occupied' => $isDueOut ? 'bg-orange-100 border-orange-500 text-orange-800' : 'bg-red-100 border-red-500 text-red-800',
            'maintenance' => 'bg-gray-100 border-gray-500 text-gray-800',
            'cleaning' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
            'out_of_order' => 'bg-pink-100 border-pink-500 text-pink-800',
        ][$room->status] ?? 'bg-gray-100 border-gray-400';
        
        $statusIcon = [
            'available' => 'fa-check-circle',
            'occupied' => $isDueOut ? 'fa-clock' : 'fa-ban',
            'maintenance' => 'fa-tools',
            'cleaning' => 'fa-broom',
            'out_of_order' => 'fa-plug',
        ][$room->status] ?? 'fa-bed';

        $statusLabel = $isDueOut ? 'Due Out' : ($room->status === 'out_of_order' ? 'Out of Order' : ucfirst($room->status));
        $activeReservation = $room->reservations->first();
        $guestName = $activeReservation && $activeReservation->guest ? $activeReservation->guest->guest_name : null;
    @endphp
    <div class="room-card border-2 rounded-xl p-3 text-center cursor-pointer hover:shadow-lg transition-all duration-200 relative group"
         data-room-id="{{ $room->id }}"
         data-room-number="{{ $room->room_number }}"
         data-room-type="{{ $room->room_type_name ?? 'Standard' }}"
         data-status="{{ $room->status }}">
        <div class="rounded-lg p-2 {{ $statusColor }}">
            <i class="fas {{ $statusIcon }} text-lg"></i>
            <p class="font-bold text-lg">{{ $room->room_number }}</p>
            <p class="text-xs">{{ $room->room_type_name ?? 'Standard' }}</p>
            <p class="text-xs mt-1 font-semibold">{{ $statusLabel }}</p>
            <p class="text-[10px] mt-1 text-gray-600">
                <i class="fas fa-tag mr-0.5"></i>Wd: Rp {{ number_format($room->price_weekday ?? $room->price_per_night, 0, ',', '.') }} · We: Rp {{ number_format($room->price_weekend ?? $room->price_per_night, 0, ',', '.') }}
            </p>
            @if($isDueOut && $activeReservation)
                <p class="text-xs mt-1 text-orange-700"><i class="fas fa-clock mr-1"></i>Due Out: {{ $activeReservation->check_out->format('H:i') }}</p>
            @endif
            @if($guestName)
                <p class="text-xs mt-1 truncate text-blue-700 font-medium" title="{{ $guestName }}"><i class="fas fa-user mr-1"></i>{{ $guestName }}</p>
            @endif
            @if($activeReservation && $activeReservation->include_breakfast)
                <p class="text-xs mt-0.5 text-amber-700 font-medium"><i class="fas fa-coffee mr-0.5"></i>Sarapan</p>
            @endif
            @if($activeReservation && !$isDueOut)
                <p class="text-xs mt-1 text-gray-600">
                    <i class="fas fa-calendar mr-1"></i>{{ $activeReservation->check_in->format('d/m') }} - {{ $activeReservation->check_out->format('d/m') }}
                </p>
            @endif
        </div>
    </div>
@empty
    <div class="col-span-full text-center py-8 text-gray-500">
        <i class="fas fa-search text-3xl mb-2"></i>
        <p>Tidak ada kamar yang sesuai filter.</p>
    </div>
@endforelse