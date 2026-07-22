@extends('layouts.app')

@section('title', 'Detail Reservasi')
@section('header', 'Detail Reservasi')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Header Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-blue-600">{{ $reservation->reservation_number }}</h2>
                <p class="text-gray-500 mt-1">Dibuat: {{ $reservation->created_at->format('d/m/Y H:i') }} oleh {{ $reservation->createdBy->name ?? '-' }}</p>
            </div>
            <div>
                @if($reservation->status === 'menunggu_pembayaran')
                    <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded font-bold">MENUNGU PEMBAYARAN</span>
                @elseif($reservation->status === 'pending')
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded font-bold">PENDING</span>
                @elseif($reservation->status === 'checked_in')
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded font-bold">CHECKED IN</span>
                @elseif($reservation->status === 'checked_out')
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-bold">CHECKED OUT</span>
                @elseif($reservation->status === 'cancelled')
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded font-bold">CANCELLED</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Group Booking Info --}}
    @if($isGroup)
    @php
        $allGroup = collect([$reservation])->merge($groupReservations);
        $totalGroup = $allGroup->sum('total_amount');
        $paidGroup = $allGroup->sum('paid_amount');
        $sisaGroup = $totalGroup - $paidGroup;
        $allLunas = $sisaGroup <= 0;
    @endphp
    <div class="bg-indigo-50 border-2 border-indigo-300 rounded-lg shadow p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-lg text-indigo-800 flex items-center gap-2">
                <i class="fas fa-layer-group"></i> Group Booking — {{ $allGroup->count() }} Kamar
            </h3>
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">GROUP</span>
        </div>

        <!-- Table Ringkasan Group -->
        <div class="overflow-x-auto mb-3">
            <table class="w-full text-sm bg-white rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-indigo-600 text-white text-xs">
                        <th class="p-2 text-left">No.</th>
                        <th class="p-2 text-left">Reservasi</th>
                        <th class="p-2 text-left">Kamar</th>
                        <th class="p-2 text-left">Tamu</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2 text-right">Dibayar</th>
                        <th class="p-2 text-right">Sisa</th>
                        <th class="p-2 text-center">Status</th>
                        <th class="p-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allGroup as $idx => $res)
                    @php
                        $sisaRes = $res->total_amount - $res->paid_amount;
                        $isActive = $res->id === $reservation->id;
                    @endphp
                    <tr class="border-b border-gray-100 {{ $isActive ? 'bg-indigo-100 font-semibold' : 'hover:bg-gray-50' }}">
                        <td class="p-2">{{ $idx + 1 }}</td>
                        <td class="p-2">
                            @if($isActive)
                                <span class="text-indigo-700">{{ $res->reservation_number }}</span>
                            @else
                                <a href="{{ route('reservations.show', $res) }}" class="text-blue-600 hover:text-blue-800 underline">
                                    {{ $res->reservation_number }}
                                </a>
                            @endif
                        </td>
                        <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2 text-right">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</td>
                        <td class="p-2 text-right text-green-600">Rp {{ number_format($res->paid_amount, 0, ',', '.') }}</td>
                        <td class="p-2 text-right {{ $sisaRes > 0 ? 'text-red-600' : 'text-green-600' }}">
                            @if($sisaRes > 0)
                                Rp {{ number_format($sisaRes, 0, ',', '.') }}
                            @else
                                <span class="text-green-600">✓ Lunas</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if($res->status === 'checked_in')
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Check-in</span>
                            @elseif($res->status === 'checked_out')
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">Check-out</span>
                            @elseif($res->status === 'cancelled')
                                <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full">Batal</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">Pending</span>
                            @endif
                        </td>
                        <td class="p-2 text-center">
                            @if(!$isActive)
                                <a href="{{ route('reservations.show', $res) }}" class="text-blue-600 hover:text-blue-800 text-xs" title="Lihat Detail">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @else
                                <span class="text-indigo-400 text-xs">▼ aktif</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-indigo-100 font-bold text-sm">
                        <td colspan="4" class="p-2 text-right">TOTAL GROUP</td>
                        <td class="p-2 text-right">Rp {{ number_format($totalGroup, 0, ',', '.') }}</td>
                        <td class="p-2 text-right text-green-700">Rp {{ number_format($paidGroup, 0, ',', '.') }}</td>
                        <td class="p-2 text-right {{ $sisaGroup > 0 ? 'text-red-700' : 'text-green-700' }}">
                            @if($sisaGroup > 0)
                                Rp {{ number_format($sisaGroup, 0, ',', '.') }}
                            @else
                                LUNAS
                            @endif
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Progress Bar Group -->
        @php $groupPercent = $totalGroup > 0 ? round(($paidGroup / $totalGroup) * 100) : 0; @endphp
        <div class="mb-3">
            <div class="flex justify-between text-xs mb-1">
                <span class="text-indigo-700 font-medium">Progress Pembayaran Group</span>
                <span class="font-bold {{ $groupPercent >= 100 ? 'text-green-600' : 'text-indigo-600' }}">{{ $groupPercent }}%</span>
            </div>
            <div class="w-full bg-indigo-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full {{ $groupPercent >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}" style="width: {{ $groupPercent }}%"></div>
            </div>
        </div>

        <!-- Aksi Group -->
        <div class="flex flex-wrap gap-2 mt-2">
            @if(!$allLunas)
            <form action="{{ route('reservations.group-payment', $reservation->booking_group_id) }}" method="POST" data-ajax="true" data-refresh="true" data-confirm="Lakukan pelunasan untuk semua {{ $allGroup->count() }} kamar (total Rp {{ number_format($sisaGroup, 0, ',', '.') }})?">
                @csrf
                <div class="flex items-center gap-2">
                    <select name="payment_method" class="border rounded px-2 py-1.5 text-sm" required>
                        <option value="">-- Pilih Metode --</option>
                        @php $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->slug }}" {{ $reservation->payment_method === $pm->slug ? 'selected' : '' }}>{{ $pm->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fas fa-credit-card"></i> Pelunasan Semua Kamar (Rp {{ number_format($sisaGroup, 0, ',', '.') }})
                    </button>
                </div>
            </form>
            @endif
            <button type="button" onclick="openAddRoomModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="fas fa-plus-circle"></i> Tambah Kamar
            </button>
            <a href="{{ route('reservations.group-invoice', $reservation->booking_group_id) }}" target="_blank"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="fas fa-file-invoice"></i> Print Group Invoice
            </a>
            <a href="{{ route('reservations.group-kwitansi', $reservation->booking_group_id) }}" target="_blank"
               class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="fas fa-receipt"></i> Print Kwitansi Group
            </a>
            <a href="{{ route('reservations.group-registration-card', $reservation->booking_group_id) }}"
               onclick="window.open(this.href, 'printGroupRegCard', 'width=900,height=700,scrollbars=1'); return false;"
               class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="fas fa-id-card"></i> Registration Card Group
            </a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Info Tamu -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4 border-b pb-2 flex items-center justify-between">
                <span><i class="fas fa-user text-blue-500 mr-2"></i>Info Tamu</span>
                <button type="button" onclick="toggleEditGuest()"
                    class="text-blue-600 hover:text-blue-800 text-sm font-normal flex items-center gap-1 px-2 py-1 rounded hover:bg-blue-50 transition"
                    id="editGuestBtn">
                    <i class="fas fa-pen"></i> Edit
                </button>
            </h3>

            {{-- Display Mode --}}
            <div id="guestDisplay" class="space-y-3">
                <div><span class="text-gray-500 text-sm">Nama</span><p class="font-medium">{{ $reservation->guest->guest_name ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">No. Identitas</span><p class="font-medium">{{ $reservation->guest->id_number ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Telepon</span><p class="font-medium">{{ $reservation->guest->phone ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Email</span><p class="font-medium">{{ $reservation->guest->email ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Tempat Lahir</span><p class="font-medium">{{ $reservation->guest->place_of_birth ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Tanggal Lahir</span><p class="font-medium">{{ $reservation->guest->date_of_birth ? \Carbon\Carbon::parse($reservation->guest->date_of_birth)->format('d/m/Y') : '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">No. Reservasi OTA</span><p class="font-medium">{{ $reservation->ota_reservation_number ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Alamat</span><p class="font-medium">{{ $reservation->guest->address ?? '-' }}</p></div>
            </div>

            {{-- Edit Mode (hidden by default) --}}
            <div id="guestEditForm" style="display:none;">
                <form onsubmit="saveGuest(event)" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="guest_name" value="{{ $reservation->guest->guest_name ?? '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">No. Identitas</label>
                        <input type="text" name="id_number" value="{{ $reservation->guest->id_number ?? '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ $reservation->guest->phone ?? '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Email</label>
                        <input type="email" name="email" value="{{ $reservation->guest->email ?? '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tempat Lahir</label>
                        <input type="text" name="place_of_birth" value="{{ $reservation->guest->place_of_birth ?? '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Tempat lahir (opsional)">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" value="{{ $reservation->guest->date_of_birth ? \Carbon\Carbon::parse($reservation->guest->date_of_birth)->format('Y-m-d') : '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">No. Reservasi OTA</label>
                        <input type="text" name="ota_reservation_number" value="{{ $reservation->ota_reservation_number ?? '' }}"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Opsional — jika booking dari OTA">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Alamat</label>
                        <textarea name="address" rows="2"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Alamat lengkap">{{ $reservation->guest->address ?? '' }}</textarea>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-1.5">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" onclick="cancelEditGuest()"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                            Batal
                        </button>
                        <span id="guestEditStatus" class="text-xs"></span>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function toggleEditGuest() {
                document.getElementById('guestDisplay').style.display = 'none';
                document.getElementById('guestEditForm').style.display = 'block';
                document.getElementById('editGuestBtn').style.display = 'none';
            }
            function cancelEditGuest() {
                document.getElementById('guestDisplay').style.display = 'block';
                document.getElementById('guestEditForm').style.display = 'none';
                document.getElementById('editGuestBtn').style.display = 'flex';
                document.getElementById('guestEditStatus').textContent = '';
            }
            function saveGuest(e) {
                e.preventDefault();
                var form = e.target;
                var btn = form.querySelector('button[type="submit"]');
                var status = document.getElementById('guestEditStatus');
                var data = new FormData(form);

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

                fetch('{{ route("reservations.update-guest", $reservation) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: data,
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        // Update display values
                        var guest = res.guest;
                        var otaNum = res.ota_reservation_number || '-';
                        var display = document.getElementById('guestDisplay');
                        display.innerHTML =
                            '<div><span class="text-gray-500 text-sm">Nama</span><p class="font-medium">' + (guest.guest_name || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">No. Identitas</span><p class="font-medium">' + (guest.id_number || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Telepon</span><p class="font-medium">' + (guest.phone || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Email</span><p class="font-medium">' + (guest.email || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Tempat Lahir</span><p class="font-medium">' + (guest.place_of_birth || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Tanggal Lahir</span><p class="font-medium">' + (guest.date_of_birth || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">No. Reservasi OTA</span><p class="font-medium">' + otaNum + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Alamat</span><p class="font-medium">' + (guest.address || '-') + '</p></div>';

                        status.textContent = '✓ Tersimpan';
                        status.className = 'text-xs text-green-600';
                        setTimeout(function() { status.textContent = ''; }, 3000);
                        cancelEditGuest();

                        if (typeof Toast !== 'undefined') {
                            Toast.success(res.message);
                        }
                    } else {
                        status.textContent = '✗ ' + (res.message || 'Gagal menyimpan');
                        status.className = 'text-xs text-red-600';
                    }
                })
                .catch(function() {
                    status.textContent = '✗ Gagal menyimpan';
                    status.className = 'text-xs text-red-600';
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
                });
            }
        </script>

        <!-- Info Kamar -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-bed text-green-500 mr-2"></i>Info Kamar</h3>
            <div class="space-y-3">
                <div><span class="text-gray-500 text-sm">No. Kamar</span><p class="font-medium text-xl">{{ $reservation->room->room_number ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Tipe Kamar</span><p class="font-medium">{{ $reservation->room->room_type_name ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Check-in</span><p class="font-medium">
                    <span id="checkinDisplay">{{ $reservation->check_in->format('d/m/Y H:i') }}</span>
                </p></div>
                <div>
                    <span class="text-gray-500 text-sm">Check-out</span>
                    <p class="font-medium flex items-center gap-2">
                        <span id="checkoutDisplay">{{ $reservation->check_out->format('d/m/Y H:i') }}</span>
                        @if(in_array($reservation->status, ['pending', 'menunggu_pembayaran', 'checked_in']))
                        <button type="button" onclick="openExtendModal()"
                            class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold flex items-center gap-1 px-2 py-0.5 rounded border border-indigo-300 hover:bg-indigo-50 transition">
                            <i class="fas fa-clock"></i> Extend
                        </button>
                        @endif
                    </p>
                </div>
                @if(in_array($reservation->status, ['pending', 'menunggu_pembayaran', 'checked_in']))
                <div>
                    <span class="text-gray-500 text-sm">&nbsp;</span>
                    <button type="button" onclick="openChangeDatesModal()"
                        class="text-blue-600 hover:text-blue-800 text-xs font-semibold flex items-center gap-1 px-2 py-0.5 rounded border border-blue-300 hover:bg-blue-50 transition">
                        <i class="fas fa-calendar-alt"></i> Ubah Tanggal
                    </button>
                </div>
                @endif
                <div>
                    <span class="text-gray-500 text-sm">Harga/Malam</span>
                    <p class="font-medium">
                        @php
                            $displayRate = $reservation->custom_room_rate ?? ($reservation->room->price_per_night ?? 0);
                            $defaultRate = $reservation->room->price_per_night ?? 0;
                        @endphp
                        <span id="rateDisplay">Rp {{ number_format($displayRate, 0, ',', '.') }}</span>
                        @if(in_array($reservation->status, ['pending', 'menunggu_pembayaran', 'checked_in']))
                        <button type="button" onclick="openEditRateModal()"
                            class="text-amber-600 hover:text-amber-800 text-xs font-semibold ml-2 px-2 py-0.5 rounded border border-amber-300 hover:bg-amber-50 transition">
                            <i class="fas fa-pen"></i> Ubah
                        </button>
                        @endif
                    </p>
                </div>
                <div><span class="text-gray-500 text-sm">Jumlah Malam</span><p class="font-medium" id="nightsDisplay">{{ $reservation->nights }} malam</p></div>
                <div><span class="text-gray-500 text-sm">Total Kamar</span><p class="font-medium text-green-700 text-lg" id="roomTotalDisplay">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</p></div>
                <div><span class="text-gray-500 text-sm">Sarapan</span><p class="font-medium">
                    <button type="button"
                        onclick="toggleBreakfast({{ $reservation->id }}, this)"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm
                            @if($reservation->include_breakfast) bg-amber-100 text-amber-700 border-amber-300
                            @else bg-gray-50 text-gray-400 border-gray-200 hover:text-amber-600 hover:border-amber-300 @endif"
                        title="Klik untuk toggle sarapan">
                        <i class="fas fa-coffee"></i>
                        @if($reservation->include_breakfast)
                            <span>Termasuk</span>
                        @else
                            <span>Tidak termasuk</span>
                        @endif
                    </button>
                </p></div>
            </div>
        </div>
    </div>

    {{-- Modal Ubah Tanggal Check-in / Check-out --}}
    <div id="changeDatesModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center" onclick="if(event.target===this)closeChangeDatesModal()">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4" onclick="event.stopPropagation()">
            <h3 class="font-bold text-lg mb-4 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-blue-500"></i> Ubah Tanggal Menginap
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Check-in <span class="text-red-500">*</span></label>
                    <input type="date" id="changeCheckIn" value="{{ $reservation->check_in->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Check-out <span class="text-red-500">*</span></label>
                    <input type="date" id="changeCheckOut" value="{{ $reservation->check_out->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div id="changeDatesPreview" class="bg-blue-50 rounded-lg p-3 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Harga/Malam:</span>
                        <span id="changeRatePreview" class="font-bold">Rp {{ number_format($displayRate, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jumlah Malam:</span>
                        <span id="changeNightsPreview" class="font-bold">{{ $reservation->nights }} malam</span>
                    </div>
                    <div class="flex justify-between border-t pt-1 mt-1">
                        <span class="text-gray-700 font-semibold">Estimasi Total:</span>
                        <span id="changeTotalPreview" class="font-bold text-blue-700">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="button" onclick="submitChangeDates()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <button type="button" onclick="closeChangeDatesModal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <span id="changeDatesStatus" class="text-xs"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Harga Kamar --}}
    <div id="editRateModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center" onclick="if(event.target===this)closeEditRateModal()">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4" onclick="event.stopPropagation()">
            <h3 class="font-bold text-lg mb-4 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-tag text-amber-500"></i> Ubah Harga Kamar
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Harga Default</label>
                    <p class="font-bold text-gray-700">Rp {{ number_format($defaultRate, 0, ',', '.') }}/malam</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Harga Kustom (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="editRateInput" value="{{ $reservation->custom_room_rate ?? $defaultRate }}" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <p class="text-[10px] text-gray-400 mt-1">Kosongkan untuk kembali ke harga default</p>
                </div>
                <div id="editRatePreview" class="bg-amber-50 rounded-lg p-3 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Harga/Malam:</span>
                        <span id="editRatePerNight" class="font-bold">Rp {{ number_format($displayRate, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jumlah Malam:</span>
                        <span id="editRateNights" class="font-bold">{{ $reservation->nights }} malam</span>
                    </div>
                    <div class="flex justify-between border-t pt-1 mt-1">
                        <span class="text-gray-700 font-semibold">Total Baru:</span>
                        <span id="editRateTotal" class="font-bold text-amber-700">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="button" onclick="submitEditRate()"
                        class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-700 transition flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Simpan Harga
                    </button>
                    <button type="button" onclick="closeEditRateModal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <span id="editRateStatus" class="text-xs"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Kamar ke Group --}}
    <div id="addRoomModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center" onclick="if(event.target===this)closeAddRoomModal()">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <h3 class="font-bold text-lg mb-4 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-plus-circle text-blue-500"></i> Tambah Kamar ke Group
            </h3>
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-lg p-3 text-sm">
                    <p class="text-blue-700 font-medium">Periode Group: <span id="addRoomPeriod" class="font-bold"></span></p>
                    <p class="text-blue-600 text-xs mt-1">Kamar baru akan menggunakan tanggal yang sama dengan group ini.</p>
                </div>

                {{-- Data Tamu --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1 font-semibold">Data Tamu</label>
                    <p class="text-sm text-gray-600 mb-2">Kosongkan jika ingin menggunakan data tamu yang sama dengan group.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="text" id="addRoomGuestName" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="Nama tamu">
                        </div>
                        <div>
                            <input type="text" id="addRoomIdNumber" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="No. Identitas">
                        </div>
                        <div>
                            <input type="text" id="addRoomPhone" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="Telepon">
                        </div>
                        <div>
                            <input type="email" id="addRoomEmail" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" placeholder="Email">
                        </div>
                    </div>
                </div>

                {{-- Daftar Kamar Tersedia --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1 font-semibold">Pilih Kamar</label>
                    <div id="addRoomList" class="space-y-1 max-h-48 overflow-y-auto border rounded-lg p-2 bg-gray-50">
                        <p class="text-gray-400 text-sm text-center py-4">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat kamar tersedia...
                        </p>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div id="addRoomSummary" class="hidden bg-green-50 rounded-lg p-3 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kamar dipilih:</span>
                        <span id="addRoomSelectedCount" class="font-bold">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total harga/malam:</span>
                        <span id="addRoomTotalPreview" class="font-bold text-green-700">Rp 0</span>
                    </div>
                </div>

                {{-- Pembayaran --}}
                <div class="border-t pt-3">
                    <label class="block text-xs text-gray-500 mb-1 font-semibold">Pembayaran (Opsional)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <select id="addRoomPaymentType" class="w-full border rounded px-2 py-1.5 text-sm" onchange="toggleAddRoomDpInput()">
                                <option value="full">Lunas</option>
                                <option value="dp">DP</option>
                                <option value="none">Tidak Bayar</option>
                            </select>
                        </div>
                        <div>
                            <select id="addRoomPaymentMethod" class="w-full border rounded px-2 py-1.5 text-sm">
                                <option value="">-- Metode Bayar --</option>
                                @php $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->slug }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="addRoomDpWrap" class="hidden mt-2">
                        <label class="block text-xs text-gray-500 mb-1">Nominal DP (Rp)</label>
                        <input type="number" id="addRoomDpAmount" class="w-full border rounded px-2 py-1.5 text-sm" min="0" placeholder="0">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" onclick="submitAddRoom()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-1.5">
                        <i class="fas fa-plus-circle"></i> Tambahkan Kamar
                    </button>
                    <button type="button" onclick="closeAddRoomModal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <span id="addRoomStatus" class="text-xs"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Extend --}}
    <div id="extendModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center" onclick="if(event.target===this)closeExtendModal()">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4" onclick="event.stopPropagation()">
            <h3 class="font-bold text-lg mb-4 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-clock text-indigo-500"></i> Extend Masa Menginap
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Check-out Saat Ini</label>
                    <p class="font-bold text-lg text-gray-700">{{ $reservation->check_out->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1" for="extendDate">Check-out Baru <span class="text-red-500">*</span></label>
                    <input type="date" id="extendDate" min="{{ $reservation->check_out->copy()->addDay()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-[10px] text-gray-400 mt-1">Check-out minimal +1 hari dari tanggal saat ini</p>
                </div>
                <div id="extendPreview" class="hidden bg-indigo-50 rounded-lg p-3 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tambahan malam:</span>
                        <span id="extendNights" class="font-bold">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Estimasi tambahan biaya:</span>
                        <span id="extendCost" class="font-bold text-indigo-700">Rp 0</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="button" onclick="submitExtend()"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                        <i class="fas fa-clock"></i> Extend
                    </button>
                    <button type="button" onclick="closeExtendModal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <span id="extendStatus" class="text-xs"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        var extendPricePerNight = {{ $reservation->custom_room_rate ?? ($reservation->room->price_per_night ?? 0) }};

        document.getElementById('extendDate')?.addEventListener('change', function() {
            var currentCheckout = new Date('{{ $reservation->check_out->format("Y-m-d") }}');
            var newDate = new Date(this.value);
            if (!this.value) {
                document.getElementById('extendPreview').classList.add('hidden');
                return;
            }
            var diffTime = newDate.getTime() - currentCheckout.getTime();
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays > 0) {
                document.getElementById('extendNights').textContent = diffDays + ' malam';
                document.getElementById('extendCost').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(diffDays * extendPricePerNight);
                document.getElementById('extendPreview').classList.remove('hidden');
            } else {
                document.getElementById('extendPreview').classList.add('hidden');
            }
        });

        function openExtendModal() {
            document.getElementById('extendModal').classList.remove('hidden');
            document.getElementById('extendStatus').textContent = '';
        }

        function closeExtendModal() {
            document.getElementById('extendModal').classList.add('hidden');
            document.getElementById('extendStatus').textContent = '';
        }

        function submitExtend() {
            var dateInput = document.getElementById('extendDate');
            var status = document.getElementById('extendStatus');
            var btn = document.querySelector('#extendModal button[type="button"]');
            if (!dateInput.value) {
                status.textContent = '✗ Pilih tanggal check-out baru';
                status.className = 'text-xs text-red-600';
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            status.textContent = '';

            fetch('{{ route("reservations.extend", $reservation) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ new_check_out: dateInput.value }),
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    status.textContent = '✓ ' + res.message;
                    status.className = 'text-xs text-green-600';
                    // Update display
                    var r = res.reservation;
                    document.getElementById('checkoutDisplay').textContent = new Date(r.check_out).toLocaleString('id-ID', {
                        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    setTimeout(function() {
                        closeExtendModal();
                        if (typeof Toast !== 'undefined') Toast.success(res.message);
                        location.reload();
                    }, 1500);
                } else {
                    status.textContent = '✗ ' + (res.message || 'Gagal');
                    status.className = 'text-xs text-red-600';
                }
            })
            .catch(function() {
                status.textContent = '✗ Gagal memproses extend';
                status.className = 'text-xs text-red-600';
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-clock"></i> Extend';
            });
        }

        // ─── Modal Ubah Tanggal ───
        var currentRate = {{ $displayRate }};
        var defaultRate = {{ $defaultRate }};

        document.getElementById('changeCheckIn')?.addEventListener('change', updateChangeDatesPreview);
        document.getElementById('changeCheckOut')?.addEventListener('change', updateChangeDatesPreview);

        function updateChangeDatesPreview() {
            var ci = document.getElementById('changeCheckIn').value;
            var co = document.getElementById('changeCheckOut').value;
            if (!ci || !co) return;
            var d1 = new Date(ci);
            var d2 = new Date(co);
            if (d2 <= d1) {
                document.getElementById('changeNightsPreview').textContent = '0 malam';
                document.getElementById('changeTotalPreview').textContent = 'Rp 0';
                return;
            }
            var diffTime = d2.getTime() - d1.getTime();
            var nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            var total = nights * currentRate;
            document.getElementById('changeNightsPreview').textContent = nights + ' malam';
            document.getElementById('changeTotalPreview').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }

        function openChangeDatesModal() {
            document.getElementById('changeDatesModal').classList.remove('hidden');
            document.getElementById('changeDatesStatus').textContent = '';
            updateChangeDatesPreview();
        }

        function closeChangeDatesModal() {
            document.getElementById('changeDatesModal').classList.add('hidden');
            document.getElementById('changeDatesStatus').textContent = '';
        }

        function submitChangeDates() {
            var ci = document.getElementById('changeCheckIn');
            var co = document.getElementById('changeCheckOut');
            var status = document.getElementById('changeDatesStatus');
            var btn = document.querySelector('#changeDatesModal .bg-blue-600');

            if (!ci.value || !co.value) {
                status.textContent = '✗ Pilih tanggal check-in dan check-out';
                status.className = 'text-xs text-red-600';
                return;
            }
            if (new Date(co.value) <= new Date(ci.value)) {
                status.textContent = '✗ Check-out harus setelah check-in';
                status.className = 'text-xs text-red-600';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            status.textContent = '';

            fetch('{{ route("reservations.update-dates", $reservation) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ check_in: ci.value, check_out: co.value }),
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    status.textContent = '✓ ' + res.message;
                    status.className = 'text-xs text-green-600';
                    setTimeout(function() {
                        closeChangeDatesModal();
                        if (typeof Toast !== 'undefined') Toast.success(res.message);
                        location.reload();
                    }, 1500);
                } else {
                    status.textContent = '✗ ' + (res.message || 'Gagal');
                    status.className = 'text-xs text-red-600';
                }
            })
            .catch(function() {
                status.textContent = '✗ Gagal menyimpan perubahan tanggal';
                status.className = 'text-xs text-red-600';
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
            });
        }

        // ─── Modal Edit Harga Kamar ───
        document.getElementById('editRateInput')?.addEventListener('input', updateEditRatePreview);

        function updateEditRatePreview() {
            var input = document.getElementById('editRateInput');
            var rate = parseFloat(input.value) || 0;
            var nights = {{ $reservation->nights }};
            var total = rate * nights;

            document.getElementById('editRatePerNight').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(rate);
            document.getElementById('editRateTotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }

        function openEditRateModal() {
            document.getElementById('editRateModal').classList.remove('hidden');
            document.getElementById('editRateStatus').textContent = '';
            updateEditRatePreview();
        }

        function closeEditRateModal() {
            document.getElementById('editRateModal').classList.add('hidden');
            document.getElementById('editRateStatus').textContent = '';
        }

        function submitEditRate() {
            var input = document.getElementById('editRateInput');
            var status = document.getElementById('editRateStatus');
            var btn = document.querySelector('#editRateModal .bg-amber-600');
            var rate = input.value.trim() === '' ? null : parseFloat(input.value);

            if (rate !== null && (isNaN(rate) || rate < 0)) {
                status.textContent = '✗ Masukkan harga yang valid';
                status.className = 'text-xs text-red-600';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            status.textContent = '';

            fetch('{{ route("reservations.update-room-rate", $reservation) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ custom_room_rate: rate }),
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    status.textContent = '✓ ' + res.message;
                    status.className = 'text-xs text-green-600';
                    setTimeout(function() {
                        closeEditRateModal();
                        if (typeof Toast !== 'undefined') Toast.success(res.message);
                        location.reload();
                    }, 1500);
                } else {
                    status.textContent = '✗ ' + (res.message || 'Gagal');
                    status.className = 'text-xs text-red-600';
                }
            })
            .catch(function() {
                status.textContent = '✗ Gagal menyimpan harga';
                status.className = 'text-xs text-red-600';
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Harga';
            });
        }
    </script>

    <!-- Info OTA (jika ada) -->
    @if($reservation->ota_reservation_number)
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-globe text-purple-500 mr-2"></i>Info OTA</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-purple-50 p-4 rounded">
                <span class="text-gray-500 text-sm">No. Reservasi OTA</span>
                <p class="text-lg font-bold text-purple-700">{{ $reservation->ota_reservation_number }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Status Bayar OTA</span>
                <p class="text-lg font-bold">
                    @if($reservation->ota_payment_status === 'paid_ota')
                        <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Lunas via OTA</span>
                    @elseif($reservation->ota_payment_status === 'partial_ota')
                        <span class="text-yellow-600"><i class="fas fa-adjust mr-1"></i>DP via OTA</span>
                    @elseif($reservation->ota_payment_status === 'unpaid_ota')
                        <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Belum Dibayar</span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </p>
            </div>
            <div class="bg-green-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Nominal Dibayar OTA</span>
                <p class="text-lg font-bold text-green-700">
                    {{ $reservation->ota_paid_amount ? 'Rp ' . number_format($reservation->ota_paid_amount, 0, ',', '.') : '-' }}
                </p>
            </div>
            <div class="bg-orange-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Sisa Tagihan Hotel</span>
                <p class="text-lg font-bold text-orange-600">
                    Rp {{ number_format($reservation->total_amount - ($reservation->ota_paid_amount ?? 0), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Info Pembayaran -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-money-bill text-yellow-500 mr-2"></i>Info Pembayaran</h3>

        <!-- Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-gray-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Total Tagihan</span>
                <p class="text-xl font-bold">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Sudah Dibayar</span>
                <p class="text-xl font-bold text-green-600">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Sisa Bayar</span>
                <p class="text-xl font-bold text-red-600">Rp {{ number_format($reservation->total_amount - $reservation->paid_amount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded">
                <span class="text-gray-500 text-sm">Status Bayar</span>
                <p class="text-xl font-bold">
                    @if($reservation->paid_amount >= $reservation->total_amount)
                        <span class="text-green-600">LUNAS</span>
                    @elseif($reservation->paid_amount > 0)
                        <span class="text-yellow-600">DP</span>
                    @else
                        <span class="text-red-600">BELUM BAYAR</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Progress Bar Pembayaran -->
        @php
            $paymentPercent = $reservation->total_amount > 0 ? round(($reservation->paid_amount / $reservation->total_amount) * 100) : 0;
        @endphp
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Progress Pembayaran</span>
                <span class="font-bold {{ $paymentPercent >= 100 ? 'text-green-600' : 'text-yellow-600' }}">{{ $paymentPercent }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full {{ $paymentPercent >= 100 ? 'bg-green-500' : 'bg-yellow-500' }}" style="width: {{ $paymentPercent }}%"></div>
            </div>
        </div>

        <!-- Riwayat Pembayaran (Multi Payment) -->
        <h4 class="font-bold text-sm text-gray-600 mb-2 uppercase">Riwayat Pembayaran</h4>
        @if($transactions->count() > 0)
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="text-left p-2 font-bold">No. Transaksi</th>
                    <th class="text-left p-2 font-bold">Tanggal</th>
                    <th class="text-left p-2 font-bold">Metode</th>
                    <th class="text-left p-2 font-bold">Sumber</th>
                    <th class="text-left p-2 font-bold">Tipe</th>
                    <th class="text-right p-2 font-bold">Nominal</th>
                    @if(hasPermission('edit_payment') || hasPermission('delete_payment'))
                    <th class="text-center p-2 font-bold">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $txn)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium">{{ $txn->transaction_number }}</td>
                    <td class="p-2">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-2 capitalize">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                    <td class="p-2">
                        @if($txn->source_type)
                        <span class="px-2 py-0.5 rounded text-xs font-bold
                            @if($txn->source_type === 'tunai') bg-yellow-100 text-yellow-800
                            @elseif($txn->source_type === 'transfer') bg-blue-100 text-blue-800
                            @elseif($txn->source_type === 'kartu') bg-purple-100 text-purple-800
                            @elseif($txn->source_type === 'e-wallet') bg-teal-100 text-teal-800
                            @elseif($txn->source_type === 'ota') bg-orange-100 text-orange-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ strtoupper($txn->source_type) }}
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="p-2">
                        <span class="px-2 py-0.5 rounded text-xs font-bold
                            @if($txn->type === 'dp') bg-blue-100 text-blue-800
                            @elseif($txn->type === 'pelunasan') bg-green-100 text-green-800
                            @elseif($txn->type === 'checkin_payment') bg-purple-100 text-purple-800
                            @elseif($txn->type === 'refund') bg-red-100 text-red-800
                            @elseif($txn->type === 'extend') bg-indigo-100 text-indigo-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ strtoupper(str_replace('_', ' ', $txn->type)) }}
                        </span>
                    </td>
                    <td class="p-2 text-right font-bold">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                    @if(hasPermission('edit_payment') || hasPermission('delete_payment'))
                    <td class="p-2 text-center whitespace-nowrap">
                        @if(hasPermission('edit_payment'))
                        <button type="button" onclick="openEditModal({{ $txn->id }})" class="text-blue-600 hover:text-blue-800 mx-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endif
                        @if(hasPermission('delete_payment'))
                        <button type="button" onclick="confirmDelete({{ $txn->id }})" class="text-red-600 hover:text-red-800 mx-1" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 border-t-2">
                    <td colspan="5" class="p-2 font-bold text-right">TOTAL DIBAYAR</td>
                    <td class="p-2 text-right font-bold text-green-700">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
                    @if(hasPermission('edit_payment') || hasPermission('delete_payment'))
                    <td></td>
                    @endif
                </tr>
            </tfoot>
        </table>
        @else
        <p class="text-gray-400 text-sm italic mb-4">Belum ada pembayaran.</p>
        @endif

        <!-- Form Input Pembayaran (1 Input Transaksi Universal) -->
        @if($reservation->status !== 'cancelled' && $reservation->status !== 'checked_out')
        <div class="border-t pt-4 mt-4">
            <h4 class="font-bold text-sm text-gray-600 mb-3 uppercase">
                <i class="fas fa-money-bill-wave mr-1"></i>Input Pembayaran
            </h4>

            @php
                $isOta = !empty($reservation->ota_reservation_number);
                $sisaBayar = $reservation->total_amount - $reservation->paid_amount;
                $otaPaid = $reservation->ota_paid_amount ?? 0;
            @endphp

            {{-- OTA Payment Status Info --}}
            @if($isOta)
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-globe text-purple-500"></i>
                        <span class="text-sm font-medium text-purple-700">OTA: {{ $reservation->ota_reservation_number }}</span>
                    </div>
                    <div class="text-sm">
                        @if($reservation->ota_payment_status === 'paid_ota')
                            <span class="text-green-600 font-bold"><i class="fas fa-check-circle mr-1"></i>Lunas via OTA</span>
                        @elseif($reservation->ota_payment_status === 'partial_ota')
                            <span class="text-yellow-600 font-bold"><i class="fas fa-adjust mr-1"></i>DP via OTA</span>
                        @elseif($reservation->ota_payment_status === 'unpaid_ota')
                            <span class="text-red-600 font-bold"><i class="fas fa-times-circle mr-1"></i>Belum Dibayar</span>
                        @else
                            <span class="text-gray-400">Status belum di-set</span>
                        @endif
                    </div>
                </div>
                @if($otaPaid > 0)
                <div class="mt-1 text-xs text-purple-600">
                    OTA sudah bayar: Rp {{ number_format($otaPaid, 0, ',', '.') }} — Sisa tagihan hotel: Rp {{ number_format($sisaBayar, 0, ',', '.') }}
                </div>
                @endif
            </div>
            @endif

            <form action="{{ route('reservations.add-payment', $reservation) }}" method="POST" id="paymentForm" data-ajax="true">
                @csrf

                {{-- Baris 1: Status OTA (jika OTA) + Tipe Pembayaran --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    @if($isOta)
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status Bayar OTA</label>
                        <select name="ota_payment_status" id="otaPaymentStatus" class="w-full border rounded px-2 py-2 text-sm" onchange="updateOtaPaidAmount()">
                            <option value="">-- Pilih Status --</option>
                            <option value="paid_ota" {{ $reservation->ota_payment_status === 'paid_ota' ? 'selected' : '' }}>Sudah Dibayar OTA (Lunas)</option>
                            <option value="partial_ota" {{ $reservation->ota_payment_status === 'partial_ota' ? 'selected' : '' }}>DP via OTA (Sebagian)</option>
                            <option value="unpaid_ota" {{ $reservation->ota_payment_status === 'unpaid_ota' ? 'selected' : '' }}>Belum Dibayar (Bayar di Hotel)</option>
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe Pembayaran</label>
                        <select name="payment_type" id="paymentType" class="w-full border rounded px-2 py-2 text-sm" required>
                            <option value="dp">DP (Down Payment)</option>
                            <option value="pelunasan" {{ $sisaBayar <= 0 ? 'selected' : '' }}>Pelunasan</option>
                            <option value="tambahan">Tambahan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Metode Pembayaran</label>
                        <select name="payment_method" class="w-full border rounded px-2 py-2 text-sm" required>
                            @php $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->slug }}" {{ $reservation->payment_method === $pm->slug ? 'selected' : '' }}>{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Baris 2: Nominal OTA (jika OTA partial/paid) + Nominal Bayar Hotel --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    @if($isOta)
                    <div id="otaPaidAmountWrap" style="{{ in_array($reservation->ota_payment_status, ['paid_ota', 'partial_ota']) ? '' : 'display:none;' }}">
                        <label class="block text-xs text-gray-500 mb-1">Nominal Dibayar OTA (Rp)</label>
                        <input type="number" name="ota_paid_amount" id="otaPaidAmount" class="w-full border rounded px-2 py-2 text-sm" min="0" step="any" placeholder="0" value="{{ $otaPaid > 0 ? $otaPaid : '' }}" oninput="calcSisaBayar()">
                        <p class="text-[10px] text-gray-400 mt-0.5">Nominal yang sudah dibayarkan OTA</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nominal Bayar Hotel (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="paymentAmount" class="w-full border rounded px-2 py-2 text-sm" min="0" step="any" placeholder="0" value="0" required oninput="calcSisaBayar()">
                        <p class="text-[10px] text-gray-400 mt-0.5">Nominal yang dibayar tamu di hotel</p>
                    </div>
                    <div class="flex items-end">
                        <div class="w-full bg-gray-100 rounded px-3 py-2 text-sm">
                            <span class="text-gray-500">Sisa Bayar:</span>
                            <span id="sisaBayarDisplay" class="font-bold {{ $sisaBayar > 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($sisaBayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- ─── Modal Edit Pembayaran ─── --}}
        <div id="editPaymentModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center" style="display:none;">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-bold text-lg">Edit Pembayaran</h4>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>
                <form id="editPaymentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tipe Pembayaran</label>
                            <select name="payment_type" id="editPaymentType" class="w-full border rounded px-2 py-2 text-sm" required>
                                <option value="dp">DP (Down Payment)</option>
                                <option value="pelunasan">Pelunasan</option>
                                <option value="tambahan">Tambahan</option>
                                <option value="checkin_payment">Check-in Payment</option>
                                <option value="refund">Refund</option>
                                <option value="extend">Extend</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Metode Pembayaran</label>
                            <select name="payment_method" id="editPaymentMethod" class="w-full border rounded px-2 py-2 text-sm" required>
                                @php $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->slug }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Nominal (Rp)</label>
                            <input type="number" name="amount" id="editPaymentAmount" class="w-full border rounded px-2 py-2 text-sm" min="0" step="any" placeholder="0" required>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─── Form Hapus Pembayaran (hidden) ─── --}}
        <form id="deletePaymentForm" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>

        <script>
            // ─── Edit Payment Modal ───
            @php
                $transactionsJson = $transactions->keyBy('id')->map(function($t) {
                    return [
                        'id' => $t->id,
                        'type' => $t->type,
                        'payment_method' => $t->payment_method,
                        'amount' => $t->amount,
                    ];
                });
            @endphp
            var transactions = @json($transactionsJson);

            function openEditModal(txnId) {
                var txn = transactions[txnId];
                if (!txn) return;
                var form = document.getElementById('editPaymentForm');
                form.action = '{{ url("transactions") }}/' + txnId + '/edit-payment';
                document.getElementById('editPaymentType').value = txn.type;
                document.getElementById('editPaymentMethod').value = txn.payment_method;
                document.getElementById('editPaymentAmount').value = txn.amount;
                document.getElementById('editPaymentModal').style.display = 'flex';
            }

            function closeEditModal() {
                document.getElementById('editPaymentModal').style.display = 'none';
            }

            // Close modal on overlay click
            document.getElementById('editPaymentModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeEditModal();
            });

            // ─── Delete Payment Confirmation ───
            function confirmDelete(txnId) {
                if (!confirm('Hapus pembayaran ini? Tindakan ini tidak bisa dibatalkan.')) return;
                var form = document.getElementById('deletePaymentForm');
                form.action = '{{ url("transactions") }}/' + txnId + '/delete-payment';
                form.submit();
            }

            function updateOtaPaidAmount() {
                var status = document.getElementById('otaPaymentStatus').value;
                var wrap = document.getElementById('otaPaidAmountWrap');
                var otaInput = document.getElementById('otaPaidAmount');
                var totalAmount = {{ $reservation->total_amount }};
                if (status === 'paid_ota') {
                    wrap.style.display = 'block';
                    otaInput.value = totalAmount;
                } else if (status === 'partial_ota') {
                    wrap.style.display = 'block';
                    if (!otaInput.value || otaInput.value == '0') otaInput.value = '';
                } else {
                    wrap.style.display = 'none';
                    otaInput.value = 0;
                }
                calcSisaBayar();
            }
            function calcSisaBayar() {
                var totalAmount = {{ $reservation->total_amount }};
                var alreadyPaid = {{ $reservation->paid_amount }};
                var otaPaid = parseInt(document.getElementById('otaPaidAmount')?.value) || 0;
                var hotelPay = parseInt(document.getElementById('paymentAmount')?.value) || 0;
                var sisa = totalAmount - alreadyPaid - otaPaid - hotelPay;
                var el = document.getElementById('sisaBayarDisplay');
                el.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, sisa));
                el.className = sisa > 0 ? 'font-bold text-red-600' : 'font-bold text-green-600';
            }

            // ─── Toggle Sarapan ───────────────────────────────────
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
                            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm bg-amber-100 text-amber-700 border-amber-300';
                            btn.innerHTML = '<i class="fas fa-coffee"></i> <span>Termasuk</span>';
                        } else {
                            btn.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm bg-gray-50 text-gray-400 border-gray-200 hover:text-amber-600 hover:border-amber-300';
                            btn.innerHTML = '<i class="fas fa-coffee"></i> <span>Tidak termasuk</span>';
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

            // ─── Tambah Kamar ke Group Booking ───────────────────
            var addRoomCheckIn = '{{ $reservation->check_in->format("Y-m-d") }}';
            var addRoomCheckOut = '{{ $reservation->check_out->format("Y-m-d") }}';
            var addRoomBookingGroupId = '{{ $reservation->booking_group_id }}';
            var addRoomSelected = {};

            function openAddRoomModal() {
                document.getElementById('addRoomModal').classList.remove('hidden');
                document.getElementById('addRoomStatus').textContent = '';
                document.getElementById('addRoomPeriod').textContent = addRoomCheckIn + ' s/d ' + addRoomCheckOut;
                addRoomSelected = {};
                loadAddRoomAvailability();
            }

            function closeAddRoomModal() {
                document.getElementById('addRoomModal').classList.add('hidden');
                document.getElementById('addRoomStatus').textContent = '';
            }

            function loadAddRoomAvailability() {
                var list = document.getElementById('addRoomList');
                list.innerHTML = '<p class="text-gray-400 text-sm text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat kamar tersedia...</p>';

                fetch('{{ route("booking.check-availability") }}?check_in=' + addRoomCheckIn + '&check_out=' + addRoomCheckOut)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.rooms || data.rooms.length === 0) {
                        list.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">Tidak ada kamar tersedia.</p>';
                        return;
                    }
                    var html = '';
                    data.rooms.forEach(function(room) {
                        var price = new Intl.NumberFormat('id-ID').format(room.price_per_night);
                        html += '<label class="flex items-center gap-2 p-2 rounded hover:bg-white cursor-pointer border border-transparent hover:border-gray-200 transition">';
                        html += '<input type="checkbox" class="add-room-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="' + room.id + '" data-price="' + room.price_per_night + '" data-number="' + room.room_number + '" data-type="' + (room.room_type_name || '') + '" onchange="updateAddRoomSummary()">';
                        html += '<div class="flex-1 flex items-center justify-between">';
                        html += '<span class="text-sm font-medium">' + room.room_number + '</span>';
                        html += '<span class="text-xs text-gray-500">' + (room.room_type_name || '') + '</span>';
                        html += '<span class="text-sm font-semibold text-green-700">Rp ' + price + '</span>';
                        html += '</div>';
                        html += '</label>';
                    });
                    list.innerHTML = html;
                    updateAddRoomSummary();
                })
                .catch(function() {
                    list.innerHTML = '<p class="text-red-500 text-sm text-center py-4">Gagal memuat kamar. Coba lagi.</p>';
                });
            }

            function updateAddRoomSummary() {
                var checkboxes = document.querySelectorAll('.add-room-checkbox:checked');
                var summary = document.getElementById('addRoomSummary');
                var count = document.getElementById('addRoomSelectedCount');
                var totalPreview = document.getElementById('addRoomTotalPreview');
                var days = 1;

                // Calculate days
                if (addRoomCheckIn && addRoomCheckOut) {
                    var d1 = new Date(addRoomCheckIn);
                    var d2 = new Date(addRoomCheckOut);
                    var diffTime = d2.getTime() - d1.getTime();
                    days = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
                }

                var total = 0;
                var names = [];
                checkboxes.forEach(function(cb) {
                    total += parseFloat(cb.dataset.price) * days;
                    names.push(cb.dataset.number);
                    addRoomSelected[cb.value] = {
                        room_id: cb.value,
                        price: parseFloat(cb.dataset.price),
                        room_number: cb.dataset.number
                    };
                });

                // Remove unselected
                document.querySelectorAll('.add-room-checkbox:not(:checked)').forEach(function(cb) {
                    delete addRoomSelected[cb.value];
                });

                count.textContent = checkboxes.length + ' kamar';
                totalPreview.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);

                if (checkboxes.length > 0) {
                    summary.classList.remove('hidden');
                } else {
                    summary.classList.add('hidden');
                }
            }

            function toggleAddRoomDpInput() {
                var type = document.getElementById('addRoomPaymentType').value;
                var wrap = document.getElementById('addRoomDpWrap');
                if (type === 'dp') {
                    wrap.classList.remove('hidden');
                } else {
                    wrap.classList.add('hidden');
                }
            }

            function submitAddRoom() {
                var checkboxes = document.querySelectorAll('.add-room-checkbox:checked');
                var status = document.getElementById('addRoomStatus');
                var btn = document.querySelector('#addRoomModal .bg-blue-600');

                if (checkboxes.length === 0) {
                    status.textContent = '✗ Pilih minimal 1 kamar';
                    status.className = 'text-xs text-red-600';
                    return;
                }

                var roomIds = [];
                var roomPrices = {};
                checkboxes.forEach(function(cb) {
                    roomIds.push(parseInt(cb.value));
                    roomPrices[cb.value] = parseFloat(cb.dataset.price);
                });

                var paymentType = document.getElementById('addRoomPaymentType').value;
                var paymentMethod = document.getElementById('addRoomPaymentMethod').value;
                var dpAmount = document.getElementById('addRoomDpAmount').value || 0;

                if (paymentType !== 'none' && !paymentMethod) {
                    status.textContent = '✗ Pilih metode pembayaran';
                    status.className = 'text-xs text-red-600';
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
                status.textContent = '';

                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                roomIds.forEach(function(id) {
                    formData.append('room_ids[]', id);
                });
                Object.keys(roomPrices).forEach(function(key) {
                    formData.append('room_prices[' + key + ']', roomPrices[key]);
                });

                var guestName = document.getElementById('addRoomGuestName').value.trim();
                var idNumber = document.getElementById('addRoomIdNumber').value.trim();
                var phone = document.getElementById('addRoomPhone').value.trim();
                var email = document.getElementById('addRoomEmail').value.trim();

                if (guestName) formData.append('guest_name', guestName);
                if (idNumber) formData.append('id_number', idNumber);
                if (phone) formData.append('phone', phone);
                if (email) formData.append('email', email);

                if (paymentType !== 'none') {
                    formData.append('payment_type', paymentType);
                    formData.append('payment_method', paymentMethod);
                    if (paymentType === 'dp' && dpAmount > 0) {
                        formData.append('dp_amount', dpAmount);
                    }
                }

                fetch('{{ $reservation->booking_group_id ? route("reservations.group-add-room", $reservation->booking_group_id) : "" }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        status.textContent = '✓ ' + res.message;
                        status.className = 'text-xs text-green-600';
                        setTimeout(function() {
                            closeAddRoomModal();
                            if (typeof Toast !== 'undefined') Toast.success(res.message);
                            if (res.redirect_url) {
                                window.location.href = res.redirect_url;
                            } else {
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        status.textContent = '✗ ' + (res.message || 'Gagal');
                        status.className = 'text-xs text-red-600';
                    }
                })
                .catch(function() {
                    status.textContent = '✗ Gagal menambahkan kamar';
                    status.className = 'text-xs text-red-600';
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plus-circle"></i> Tambahkan Kamar';
                });
            }
        </script>
    </div>

    <!-- Catatan -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-2"><i class="fas fa-sticky-note text-purple-500 mr-2"></i>Catatan</h3>
        <form id="notesForm" onsubmit="saveNotes(event)" class="space-y-3">
            @csrf
            <textarea name="notes" id="notesInput" rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                placeholder="Tambahkan catatan untuk reservasi ini...">{{ $reservation->notes }}</textarea>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-700 transition flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Catatan
                </button>
                <span id="notesStatus" class="text-xs text-gray-400"></span>
            </div>
        </form>
    </div>

    <script>
        function saveNotes(e) {
            e.preventDefault();
            var form = e.target;
            var btn = form.querySelector('button[type="submit"]');
            var status = document.getElementById('notesStatus');
            var notes = document.getElementById('notesInput').value;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            fetch('{{ route("reservations.update-notes", $reservation) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notes: notes }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    status.textContent = '✓ Tersimpan';
                    status.className = 'text-xs text-green-600';
                    setTimeout(function() { status.textContent = ''; }, 3000);
                    if (typeof Toast !== 'undefined') {
                        Toast.success(data.message);
                    }
                } else {
                    status.textContent = '✗ Gagal menyimpan';
                    status.className = 'text-xs text-red-600';
                }
            })
            .catch(function() {
                status.textContent = '✗ Gagal menyimpan';
                status.className = 'text-xs text-red-600';
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Catatan';
            });
        }
    </script>

    <!-- Other Revenue (Service Charge) -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg border-b pb-2 flex-1"><i class="fas fa-receipt text-blue-500 mr-2"></i>Other Revenue</h3>
            @if($reservation->status !== 'cancelled' && $reservation->status !== 'checked_out')
            <button type="button"
                    onclick="ServiceChargeForm.open('{{ route('service-charge.create', ['reservation_id' => $reservation->id]) }}')"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-2 no-print">
                <i class="fas fa-plus"></i> Tambah Other Revenue
            </button>
            @endif
        </div>

        @if($reservation->serviceCharges->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="text-left p-2 font-bold">No. Charge</th>
                            <th class="text-left p-2 font-bold">Tanggal</th>
                            <th class="text-left p-2 font-bold">Layanan</th>
                            <th class="text-center p-2 font-bold">Qty</th>
                            <th class="text-right p-2 font-bold">Total</th>
                            <th class="text-center p-2 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservation->serviceCharges as $sc)
                        <tr class="border-b border-gray-100">
                            <td class="p-2 font-mono text-blue-600 font-bold">{{ $sc->charge_number }}</td>
                            <td class="p-2 text-gray-600">{{ $sc->charge_date->format('d/m/Y') }}</td>
                            <td class="p-2">{{ $sc->service_name }}</td>
                            <td class="p-2 text-center">{{ $sc->quantity }} × Rp {{ number_format($sc->amount, 0, ',', '.') }}</td>
                            <td class="p-2 text-right font-bold">Rp {{ number_format($sc->total_amount, 0, ',', '.') }}</td>
                            <td class="p-2 text-center">
                                <a href="{{ route('service-charge.show', $sc) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm" title="Lihat / Print">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-50 border-t-2 font-bold">
                            <td colspan="4" class="p-2 text-right">TOTAL OTHER REVENUE</td>
                            <td class="p-2 text-right text-blue-700">Rp {{ number_format($reservation->serviceCharges->sum('total_amount'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-sm italic">Belum ada other revenue untuk reservasi ini.</p>
        @endif
    </div>

    <!-- Resto Transactions -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-utensils text-orange-500 mr-2"></i>Resto Transactions</h3>

        @if($reservation->restoTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="text-left p-2 font-bold">No. Transaksi</th>
                            <th class="text-left p-2 font-bold">Tanggal</th>
                            <th class="text-left p-2 font-bold">Items</th>
                            <th class="text-right p-2 font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservation->restoTransactions as $rt)
                        <tr class="border-b border-gray-100">
                            <td class="p-2 font-mono text-orange-600 font-bold">{{ $rt->transaction_number }}</td>
                            <td class="p-2 text-gray-600">{{ $rt->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-2">
                                @if(is_array($rt->items))
                                    @foreach($rt->items as $item)
                                        <span class="inline-block bg-gray-100 rounded px-2 py-0.5 text-xs mr-1 mb-1">
                                            {{ $item['name'] ?? $item['menu_name'] ?? 'Item' }} × {{ $item['quantity'] ?? 1 }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-2 text-right font-bold">Rp {{ number_format($rt->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-orange-50 border-t-2 font-bold">
                            <td colspan="3" class="p-2 text-right">TOTAL RESTO</td>
                            <td class="p-2 text-right text-orange-700">Rp {{ number_format($reservation->restoTransactions->sum('total_amount'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-sm italic">Belum ada transaksi resto untuk reservasi ini.</p>
        @endif
    </div>

    <!-- Tombol Aksi -->
    <div class="flex justify-between items-center mt-6 no-print">
        <a href="{{ route('reservations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div class="flex space-x-2">
            <!-- Print Buttons -->
            <a href="{{ route('reservations.print-kwitansi', $reservation) }}" target="_blank" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-receipt mr-1"></i> Print Kwitansi
            </a>
            <a href="{{ route('reservations.print-invoice', $reservation) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                <i class="fas fa-file-invoice mr-1"></i> Print Invoice
            </a>
            <a href="{{ route('reservations.print-registration-card', $reservation) }}"
               onclick="window.open(this.href, 'printRegCard', 'width=900,height=700,scrollbars=1'); return false;"
               class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
                <i class="fas fa-id-card mr-1"></i> Registration Card
            </a>
            @if(in_array($reservation->status, ['pending', 'menunggu_pembayaran']))
                <form action="{{ route('reservations.checkin', $reservation) }}" method="POST" data-ajax="true">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-sign-in-alt mr-1"></i> Check-in
                    </button>
                </form>
                <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" data-ajax="true">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </form>
            @endif
            @if(in_array($reservation->status, ['pending', 'menunggu_pembayaran', 'checked_in']))
                <a href="{{ route('reservations.room-change', $reservation) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-exchange-alt mr-1"></i> Pindah Kamar
                </a>
            @endif
            @if($reservation->status === 'checked_in')
                <form action="{{ route('reservations.checkout', $reservation) }}" method="POST" data-ajax="true" data-refresh="true">
                    @csrf
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                        <i class="fas fa-sign-out-alt mr-1"></i> Check-out
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
