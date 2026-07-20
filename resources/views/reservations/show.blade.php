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
            <form action="{{ route('reservations.group-payment', $reservation->booking_group_id) }}" method="POST" data-ajax="true" data-confirm="Lakukan pelunasan untuk semua {{ $allGroup->count() }} kamar (total Rp {{ number_format($sisaGroup, 0, ',', '.') }})?">
                @csrf
                <input type="hidden" name="payment_method" value="{{ $reservation->payment_method ?? 'cash' }}">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fas fa-credit-card"></i> Pelunasan Semua Kamar (Rp {{ number_format($sisaGroup, 0, ',', '.') }})
                </button>
            </form>
            @endif
            <a href="{{ route('reservations.group-invoice', $reservation->booking_group_id) }}" target="_blank"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="fas fa-file-invoice"></i> Print Group Invoice
            </a>
            <a href="{{ route('reservations.group-kwitansi', $reservation->booking_group_id) }}" target="_blank"
               class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
                <i class="fas fa-receipt"></i> Print Kwitansi Group
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
                        var display = document.getElementById('guestDisplay');
                        display.innerHTML =
                            '<div><span class="text-gray-500 text-sm">Nama</span><p class="font-medium">' + (guest.guest_name || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">No. Identitas</span><p class="font-medium">' + (guest.id_number || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Telepon</span><p class="font-medium">' + (guest.phone || '-') + '</p></div>' +
                            '<div><span class="text-gray-500 text-sm">Email</span><p class="font-medium">' + (guest.email || '-') + '</p></div>';

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
                <div><span class="text-gray-500 text-sm">Check-in</span><p class="font-medium">{{ $reservation->check_in->format('d/m/Y H:i') }}</p></div>
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
