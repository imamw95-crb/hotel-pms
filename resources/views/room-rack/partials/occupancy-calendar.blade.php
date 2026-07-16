@php
use Carbon\Carbon;

/**
 * Occupancy Calendar Partial — 7-Day Range View
 *
 * Features:
 * - 7-day range with guest names displayed on occupied cells
 * - Color-coded cells: check-in (green), occupied (red), check-out (amber)
 * - Room type badges with colors
 * - Weekly statistics summary
 */
$totalDays = $start->diffInDays($end) + 1;
$roomsCount = count($calendar);

// Calculate stats for the 7-day range
$totalOccupiedNights = 0;
$totalAvailableNights = 0;
$dirtyNights = 0;
$maintenanceNights = 0;
$outOfOrderNights = 0;

foreach ($calendar as $row) {
    foreach ($row['days'] as $cell) {
        if ($cell['status'] === 'occupied') {
            $totalOccupiedNights++;
        } elseif ($cell['status'] === 'available') {
            $totalAvailableNights++;
        } elseif ($cell['status'] === 'dirty') {
            $dirtyNights++;
        } elseif ($cell['status'] === 'maintenance') {
            $maintenanceNights++;
        } elseif ($cell['status'] === 'out_of_order') {
            $outOfOrderNights++;
        }
    }
}

$totalCells = $roomsCount * $totalDays;
$occupancyRate = $totalCells > 0 ? round(($totalOccupiedNights / $totalCells) * 100, 1) : 0;
@endphp

<div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
    <!-- Weekly Statistics Summary -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200 p-3">
        <div class="grid grid-cols-4 md:grid-cols-8 gap-3 text-center">
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-purple-600">{{ $roomsCount }}</div>
                <div class="text-[10px] text-gray-600">Rooms</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-red-600">{{ $totalOccupiedNights }}</div>
                <div class="text-[10px] text-gray-600">Occupied</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-emerald-600">{{ $totalAvailableNights }}</div>
                <div class="text-[10px] text-gray-600">Available</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-amber-600">{{ $dirtyNights }}</div>
                <div class="text-[10px] text-gray-600">Dirty</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-indigo-600">{{ $maintenanceNights }}</div>
                <div class="text-[10px] text-gray-600">Maint.</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-gray-500">{{ $outOfOrderNights }}</div>
                <div class="text-[10px] text-gray-600">OOO</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-blue-600">{{ $occupancyRate }}%</div>
                <div class="text-[10px] text-gray-600">Rate</div>
            </div>
            <div class="bg-white rounded-lg p-2 shadow-sm">
                <div class="text-lg font-bold text-gray-600">{{ $start->format('M j') }} - {{ $end->format('j, Y') }}</div>
                <div class="text-[10px] text-gray-600">Period</div>
            </div>
        </div>
    </div>

    <div class="min-w-max">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="text-left px-2 py-2 border-r border-b w-[130px] sticky left-0 bg-gray-50 z-20">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-semibold">Room / Type</span>
                        </div>
                    </th>
                    @foreach($days as $day)
                        @php
                            $isToday = $day->isToday();
                            $isWeekend = $day->isWeekend();
                            $dayClass = $isToday ? 'bg-blue-50' : ($isWeekend ? 'bg-gray-50' : '');
                        @endphp
                        <th class="text-center px-1 py-1.5 border-r border-b text-xs {{ $dayClass }}" style="min-width:90px;" title="{{ $day->format('l, M j, Y') }}">
                            <div class="flex flex-col items-center">
                                <span class="font-bold {{ $isToday ? 'text-blue-700' : ($isWeekend ? 'text-gray-500' : 'text-gray-700') }}">{{ $day->format('D') }}</span>
                                <span class="text-[10px] {{ $isToday ? 'text-blue-600 font-bold' : 'text-gray-400' }}">{{ $day->format('M j') }}</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($calendar as $row)
                    @php
                        $room = $row['room'];
                        $roomType = $room->roomType;
                        $typeBadge = $roomType ? ($roomType->color_code ?? '#6B7280') : '#6B7280';
                    @endphp
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="px-2 py-1 border-r sticky left-0 bg-white text-sm" style="min-width:130px;">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-gray-800">{{ $room->room_number }}</span>
                                @if($roomType)
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full font-medium" style="background-color: {{ $typeBadge }}20; color: {{ $typeBadge }};">
                                        {{ $roomType->name ?? $roomType->room_type_name ?? '' }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[9px] text-gray-400 mt-0.5 capitalize">{{ $room->status }}</div>
                        </td>
                        @foreach($row['days'] as $cell)
                            @php
                                $bg = match($cell['status']) {
                                    'occupied' => $cell['is_checkin'] ? 'bg-emerald-300' : ($cell['is_checkout'] ? 'bg-amber-300' : 'bg-red-400'),
                                    'maintenance' => 'bg-indigo-200',
                                    'dirty' => 'bg-amber-100',
                                    'out_of_order' => 'bg-gray-300',
                                    default => 'bg-emerald-100',
                                };

                                $guestName = '';
                                $guestNotes = '';
                                if ($cell['status'] === 'occupied' && $cell['booking']) {
                                    $booking = $cell['booking'];
                                    $guestName = $booking->guest->guest_name ?? '';
                                    if (strlen($guestName) > 15) {
                                        $guestName = substr($guestName, 0, 13) . '..';
                                    }
                                    $guestNotes = $booking->notes ?? '';
                                    if (strlen($guestNotes) > 20) {
                                        $guestNotes = substr($guestNotes, 0, 18) . '..';
                                    }
                                }

                                $tooltipText = $cell['status'];
                                if ($cell['status'] === 'occupied' && $cell['booking']) {
                                    $b = $cell['booking'];
                                    $tooltipText = $b->guest->guest_name . ' (' . $b->reservation_number . ')';
                                    $tooltipText .= "\n" . $b->check_in->format('M j, Y') . ' - ' . $b->check_out->format('M j, Y');
                                    if ($b->notes) {
                                        $tooltipText .= "\n📝 " . $b->notes;
                                    }
                                    if ($cell['is_checkin']) $tooltipText .= "\n✓ Check-in today";
                                    if ($cell['is_checkout']) {
                                        $tooltipText .= "\n✓ Check-out today (after 12:00)";
                                        $tooltipText .= "\n⬇ Room available for same-day booking";
                                    }
                                } elseif ($cell['status'] === 'out_of_order' && isset($cell['ooo'])) {
                                    $tooltipText = '⛔ Out of Order';
                                    $tooltipText .= "\nReason: " . ($cell['ooo']->reason ?? 'N/A');
                                    if ($cell['ooo']->notes) $tooltipText .= "\n📝 " . $cell['ooo']->notes;
                                    $tooltipText .= "\n" . $cell['ooo']->start_date->format('M j') . ' - ' . ($cell['ooo']->end_date ? $cell['ooo']->end_date->format('M j, Y') : '∞');
                                }
                            @endphp
                            <td class="border-r p-0.5 align-top" style="min-width:90px; height:44px;">
                                <div class="w-full h-full {{ $bg }} rounded px-1 py-0.5 text-xs leading-tight relative" title="{{ $tooltipText }}">
                                    @if($cell['status'] === 'occupied' && $guestName)
                                        @php
                                            $coDate = $cell['booking']->check_out ?? null;
                                            $outLabel = $coDate ? 'Out: ' . $coDate->format('M j') : '';
                                        @endphp
                                        <a href="{{ route('reservations.show', $cell['booking']->id) }}"
                                           draggable="true"
                                           data-reservation-id="{{ $cell['booking']->id }}"
                                           data-guest-name="{{ $cell['booking']->guest->guest_name ?? '' }}"
                                           data-room-number="{{ $room->room_number }}"
                                           data-check-in="{{ $cell['booking']->check_in->format('Y-m-d') }}"
                                           data-check-out="{{ $cell['booking']->check_out->format('Y-m-d') }}"
                                           data-room-id="{{ $room->id }}"
                                           class="block w-full h-full occupancy-drag {{ $cell['is_checkin'] ? 'text-emerald-900' : ($cell['is_checkout'] ? 'text-amber-900' : 'text-white') }} hover:ring-2 hover:ring-inset hover:ring-white/50 rounded"
                                           ondragstart="onOccupancyDragStart(event)">
                                            <div class="font-medium leading-tight">
                                                {{ $guestName }}
                                            </div>
                                            <div class="flex items-center gap-1 text-[8px] leading-tight">
                                                @if($cell['is_checkin'])
                                                    <span class="font-bold text-emerald-900">↗ IN</span>
                                                @elseif($cell['is_checkout'])
                                                    <span class="font-bold text-amber-900">↘ OUT</span>
                                                @endif
                                                @if(!$cell['is_checkout'] && $outLabel)
                                                    <span class="opacity-80">📅 {{ $outLabel }}</span>
                                                @endif
                                            </div>
                                            @if($guestNotes)
                                                <div class="text-[9px] leading-tight truncate {{ $cell['is_checkin'] ? 'text-emerald-800' : ($cell['is_checkout'] ? 'text-amber-800' : 'text-white/80') }}">
                                                    📝 {{ $guestNotes }}
                                                </div>
                                            @endif
                                        </a>
                                    @elseif($cell['status'] === 'available')
                                        @php $cellDate = $days[$loop->index] ?? null; @endphp
                                        @if($cellDate)
                                        @php
                                            $checkOutDate = $cellDate->copy()->addDay();
                                            $bookingUrl = route('booking.create', ['room_id' => $room->id, 'check_in' => $cellDate->format('Y-m-d'), 'check_out' => $checkOutDate->format('Y-m-d')]);
                                        @endphp
                                        <div class="w-full h-full cursor-pointer hover:ring-2 hover:ring-inset hover:ring-emerald-400 rounded flex items-center justify-center occupancy-drop"
                                             data-room-id="{{ $room->id }}"
                                             data-room-number="{{ $room->room_number }}"
                                             data-date="{{ $cellDate->format('Y-m-d') }}"
                                             ondragover="onOccupancyDragOver(event)"
                                             ondrop="onOccupancyDrop(event)"
                                             ondragleave="onOccupancyDragLeave(event)"
                                             onclick="Modal.open('{{ $bookingUrl }}')"
                                             title="Book {{ $room->room_number }} {{ $cellDate->format('M j') }} → {{ $checkOutDate->format('M j') }}">
                                            <span class="text-emerald-700 text-[10px]">●</span>
                                        </div>
                                        @endif
                                    @elseif($cell['status'] === 'dirty')
                                        <span class="text-amber-700 text-[10px]">🧹</span>
                                    @elseif($cell['status'] === 'maintenance')
                                        <span class="text-indigo-700 text-[10px]">🔧</span>
                                    @elseif($cell['status'] === 'out_of_order')
                                        <span class="text-gray-600 text-[10px]">⛔ OOO</span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Legend -->
    <div class="border-t border-gray-200 bg-gray-50 p-2">
        <div class="flex flex-wrap gap-3 text-[10px] text-gray-600 items-center justify-center">
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-emerald-100"></span>
                <span>Available</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-red-400"></span>
                <span>Occupied</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-emerald-300"></span>
                <span>Check-in</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-amber-300"></span>
                <span>Check-out</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-amber-100"></span>
                <span>Dirty</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-indigo-200"></span>
                <span>Maintenance</span>
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 rounded bg-gray-300"></span>
                <span>Out of Order</span>
            </span>
            <span class="flex items-center gap-1 ml-4">
                <span class="inline-block w-2.5 h-2.5 rounded bg-green-200 ring-2 ring-green-400"></span>
                <span>Drop Target</span>
            </span>
        </div>
    </div>
</div>

{{-- Drag & Drop Confirmation Modal --}}
<div id="dragMoveModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeDragMoveModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
        <button onclick="closeDragMoveModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
            <i class="fas fa-times"></i>
        </button>
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-exchange-alt text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Pindah Kamar</h3>
            <p class="text-sm text-gray-500 mt-1">Konfirmasi pemindahan tamu ke kamar lain</p>
        </div>

        <div id="dragMoveInfo" class="bg-gray-50 rounded-lg p-4 mb-4 text-sm space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-500">Tamu:</span>
                <span class="font-medium" id="dragGuestName">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Dari Kamar:</span>
                <span class="font-medium text-red-600" id="dragFromRoom">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Ke Kamar:</span>
                <span class="font-medium text-green-600" id="dragToRoom">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Check-in:</span>
                <span class="font-medium" id="dragCheckIn">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Check-out:</span>
                <span class="font-medium" id="dragCheckOut">-</span>
            </div>
            <div id="dragAvailability" class="mt-2 text-center font-semibold hidden"></div>
        </div>

        <div class="mb-3">
            <label class="block text-xs font-medium text-gray-600 mb-1">Alasan <span class="text-gray-400">(opsional)</span></label>
            <input type="text" id="dragMoveReason" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Upgrade, perbaikan, dll.">
        </div>

        <div class="flex gap-2">
            <button onclick="closeDragMoveModal()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition">
                Batal
            </button>
            <button id="dragMoveConfirmBtn" onclick="confirmDragMove()" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-exchange-alt mr-1"></i> Pindah
            </button>
        </div>
    </div>
</div>

<script>
// ─── Drag & Drop State ───
var _dragData = null;
var _dragTargetRoomId = null;

function onOccupancyDragStart(e) {
    var el = e.target.closest('[data-reservation-id]');
    if (!el) return;
    _dragData = {
        reservationId: el.dataset.reservationId,
        guestName: el.dataset.guestName,
        fromRoom: el.dataset.roomNumber,
        fromRoomId: el.dataset.roomId,
        checkIn: el.dataset.checkIn,
        checkOut: el.dataset.checkOut,
    };
    e.dataTransfer.setData('text/plain', el.dataset.reservationId);
    e.dataTransfer.effectAllowed = 'move';
    el.style.opacity = '0.5';
    setTimeout(function() { el.style.opacity = '1'; }, 0);
}

function onOccupancyDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    var dropZone = e.target.closest('.occupancy-drop');
    if (dropZone) {
        dropZone.classList.add('ring-2', 'ring-green-400', 'bg-green-50');
    }
}

function onOccupancyDragLeave(e) {
    var dropZone = e.target.closest('.occupancy-drop');
    if (dropZone) {
        dropZone.classList.remove('ring-2', 'ring-green-400', 'bg-green-50');
    }
}

function onOccupancyDrop(e) {
    e.preventDefault();
    var dropZone = e.target.closest('.occupancy-drop');
    if (!dropZone) return;
    dropZone.classList.remove('ring-2', 'ring-green-400', 'bg-green-50');

    if (!_dragData) {
        Toast.error('Data booking tidak ditemukan. Silakan coba drag lagi.');
        return;
    }

    _dragTargetRoomId = dropZone.dataset.roomId;
    var targetRoomNumber = dropZone.dataset.roomNumber;

    // Cek validasi: tidak boleh drop ke kamar yang sama
    if (_dragData.fromRoomId === _dragTargetRoomId) {
        Toast.warning('Kamar tujuan sama dengan kamar asal.');
        return;
    }

    // Tampilkan info di modal
    document.getElementById('dragGuestName').textContent = _dragData.guestName;
    document.getElementById('dragFromRoom').textContent = _dragData.fromRoom + ' (#' + _dragData.fromRoomId + ')';
    document.getElementById('dragToRoom').textContent = targetRoomNumber + ' (#' + _dragTargetRoomId + ')';
    document.getElementById('dragCheckIn').textContent = _dragData.checkIn;
    document.getElementById('dragCheckOut').textContent = _dragData.checkOut;

    var availEl = document.getElementById('dragAvailability');
    availEl.className = 'mt-2 text-center font-semibold hidden';
    availEl.textContent = '';

    var confirmBtn = document.getElementById('dragMoveConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cek...';

    document.getElementById('dragMoveModal').classList.remove('hidden');

    // Cek ketersediaan kamar via AJAX
    var checkUrl = '{{ route('room-rack.check-room-available') }}' +
        '?room_id=' + _dragTargetRoomId +
        '&check_in=' + _dragData.checkIn +
        '&check_out=' + _dragData.checkOut +
        '&exclude_reservation_id=' + _dragData.reservationId;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', checkUrl, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success && data.available) {
                    availEl.className = 'mt-2 text-center font-semibold text-green-600';
                    availEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Kamar tersedia!';
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindah';
                } else {
                    availEl.className = 'mt-2 text-center font-semibold text-red-500';
                    availEl.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Kamar tidak tersedia untuk tanggal tersebut.';
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindah';
                }
            } catch(e) {
                availEl.className = 'mt-2 text-center font-semibold text-red-500';
                availEl.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Gagal memeriksa ketersediaan.';
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindah';
            }
        } else {
            availEl.className = 'mt-2 text-center font-semibold text-red-500';
            availEl.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Gagal memeriksa ketersediaan.';
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindah';
        }
    };
    xhr.onerror = function() {
        availEl.className = 'mt-2 text-center font-semibold text-red-500';
        availEl.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Koneksi gagal.';
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindah';
    };
    xhr.send();
}

function confirmDragMove() {
    if (!_dragData || !_dragTargetRoomId) return;

    var reason = document.getElementById('dragMoveReason').value.trim();
    var confirmBtn = document.getElementById('dragMoveConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route('room-rack.move-booking') }}', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    xhr.onload = function() {
        closeDragMoveModal();
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    Toast.success(data.message || 'Pindah kamar berhasil!');
                    // Refresh occupancy calendar
                    refreshOccupancyCalendar();
                } else {
                    Toast.error(data.message || 'Gagal memindahkan kamar.');
                }
            } catch(e) {
                Toast.error('Response tidak valid.');
            }
        } else {
            try {
                var err = JSON.parse(xhr.responseText);
                Toast.error(err.message || 'Gagal memindahkan kamar.');
            } catch(e) {
                Toast.error('Terjadi kesalahan server.');
            }
        }
    };
    xhr.onerror = function() {
        closeDragMoveModal();
        Toast.error('Koneksi gagal. Silakan coba lagi.');
    };

    xhr.send(JSON.stringify({
        reservation_id: _dragData.reservationId,
        new_room_id: _dragTargetRoomId,
        reason: reason || 'Drag-drop dari occupancy calendar',
    }));
}

function closeDragMoveModal() {
    document.getElementById('dragMoveModal').classList.add('hidden');
    document.getElementById('dragMoveReason').value = '';
    var confirmBtn = document.getElementById('dragMoveConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindah';
}

function refreshOccupancyCalendar() {
    // Reload halaman untuk memperbarui data
    location.reload();
}
</script>