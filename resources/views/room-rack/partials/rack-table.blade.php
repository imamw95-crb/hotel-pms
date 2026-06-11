<div class="min-w-max">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 sticky top-0 z-10">
            <tr>
                <th class="text-left px-3 py-2 border-r border-b w-[120px] sticky left-0 bg-gray-50 z-30">Room</th>
                @foreach($rack['period'] as $day)
                    @php
                        $isToday = $day->isToday();
                        $isWeekend = $day->isWeekend();
                        $dayClass = $isToday ? 'bg-blue-50 text-blue-700' : ($isWeekend ? 'bg-gray-50' : '');
                    @endphp
                    <th class="text-center px-1 py-1 border-r border-b text-[10px] font-medium {{ $dayClass }}" 
                        style="min-width:36px; max-width:36px;">
                        <div>{{ $day->format('d') }}</div>
                        <div class="text-[8px] text-gray-400">{{ $day->format('D') }}</div>
                    </th>
                @endforeach
                <th class="text-center px-2 py-2 border-b text-xs text-gray-500 w-16">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rack['rack'] as $row)
                @php
                    $room = $row['room'];
                    $statusDot = match($room->status) {
                        'available' => 'bg-emerald-400',
                        'occupied' => 'bg-red-400',
                        'cleaning' => 'bg-amber-300',
                        'maintenance' => 'bg-purple-300',
                        'out_of_order' => 'bg-pink-400',
                        default => 'bg-gray-300',
                    };
                @endphp
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-3 py-2 border-r sticky left-0 bg-white z-20">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $statusDot }} flex-shrink-0"></span>
                            <div>
                                <span class="font-bold text-sm cursor-pointer hover:text-blue-600 transition" 
                                      onclick="Modal.open('{{ route('booking.create') }}?room_id={{ $room->id }}')"
                                      title="Booking kamar {{ $room->room_number }}">
                                    {{ $room->room_number }}
                                </span>
                                <span class="text-[10px] text-gray-400 block leading-tight">{{ $room->room_type_name ?? 'Standard' }}</span>
                                <span class="text-[8px] text-green-600 block leading-tight">Wd {{ number_format($room->price_weekday ?? $room->price_per_night, 0, ',', '.') }} · We {{ number_format($room->price_weekend ?? $room->price_per_night, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </td>
                    @foreach($rack['period'] as $day)
                        @php
                            $dayEnd = $day->copy()->endOfDay();
                            $block = null;
                            foreach ($row['blocks'] as $b) {
                                if ($b['check_in']->lte($dayEnd) && $b['check_out']->gt($day)) {
                                    $block = $b;
                                    break;
                                }
                            }
                            $isFirst = $block && $block['check_in']->format('Y-m-d') === $day->format('Y-m-d');
                            $isLast = $block && $block['check_out']->format('Y-m-d') === $day->format('Y-m-d');
                        @endphp
                        <td class="border-r p-0 text-center relative {{ $day->isToday() ? 'bg-blue-50/50' : '' }}" style="min-width:36px; max-width:36px; height:36px;">
                            @if($block)
                                <div class="absolute inset-0 {{ $block['status'] === 'checked_in' ? 'bg-red-400' : 'bg-amber-400' }} 
                                            {{ $isFirst ? 'rounded-l-sm' : '' }} {{ $isLast ? 'rounded-r-sm' : '' }}
                                            opacity-80"></div>
                                @if($isFirst)
                                    <div class="absolute inset-0 flex items-center justify-center overflow-hidden px-0.5 z-10" 
                                         title="{{ $block['guest_name'] }} — {{ $block['reservation_number'] }}">
                                        <span class="text-[7px] text-white font-semibold leading-none truncate block w-full text-center">
                                            {{ $block['guest_name'] }}
                                        </span>
                                    </div>
                                @endif
                            @elseif($room->status === 'maintenance')
                                <div class="absolute inset-0 bg-purple-200/60"></div>
                                <span class="text-[8px] text-purple-500 relative z-10">M</span>
                            @elseif($room->status === 'cleaning')
                                <div class="absolute inset-0 bg-amber-200/60"></div>
                                <span class="text-[8px] text-amber-500 relative z-10">D</span>
                            @elseif($room->status === 'out_of_order')
                                <div class="absolute inset-0 bg-pink-200/60"></div>
                                <span class="text-[8px] text-pink-500 relative z-10">OO</span>
                            @else
                                <span class="text-[10px] text-emerald-500 relative z-10">✓</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center px-2 py-2">
                        <button onclick="Modal.open('{{ route('booking.create') }}?room_id={{ $room->id }}')"
                                class="text-blue-600 hover:text-blue-800 text-xs" title="Book {{ $room->room_number }}">
                            <i class="fas fa-calendar-plus"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="100" class="p-8 text-center text-gray-500">No rooms found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>