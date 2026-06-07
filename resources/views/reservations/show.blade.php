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
                @if($reservation->status === 'pending')
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Info Tamu -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-user text-blue-500 mr-2"></i>Info Tamu</h3>
            <div class="space-y-3">
                <div><span class="text-gray-500 text-sm">Nama</span><p class="font-medium">{{ $reservation->guest->guest_name ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">No. Identitas</span><p class="font-medium">{{ $reservation->guest->id_number ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Telepon</span><p class="font-medium">{{ $reservation->guest->phone ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Email</span><p class="font-medium">{{ $reservation->guest->email ?? '-' }}</p></div>
            </div>
        </div>

        <!-- Info Kamar -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4 border-b pb-2"><i class="fas fa-bed text-green-500 mr-2"></i>Info Kamar</h3>
            <div class="space-y-3">
                <div><span class="text-gray-500 text-sm">No. Kamar</span><p class="font-medium text-xl">{{ $reservation->room->room_number ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Tipe Kamar</span><p class="font-medium">{{ $reservation->room->room_type_name ?? '-' }}</p></div>
                <div><span class="text-gray-500 text-sm">Check-in</span><p class="font-medium">{{ $reservation->check_in->format('d/m/Y H:i') }}</p></div>
                <div><span class="text-gray-500 text-sm">Check-out</span><p class="font-medium">{{ $reservation->check_out->format('d/m/Y H:i') }}</p></div>
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
        @if($transactions->count() > 0)
        <h4 class="font-bold text-sm text-gray-600 mb-2 uppercase">Riwayat Pembayaran</h4>
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="text-left p-2 font-bold">No. Transaksi</th>
                    <th class="text-left p-2 font-bold">Tanggal</th>
                    <th class="text-left p-2 font-bold">Metode</th>
                    <th class="text-left p-2 font-bold">Tipe</th>
                    <th class="text-right p-2 font-bold">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $txn)
                <tr class="border-b border-gray-100">
                    <td class="p-2 font-medium">{{ $txn->transaction_number }}</td>
                    <td class="p-2">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-2 capitalize">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                    <td class="p-2">
                        <span class="px-2 py-0.5 rounded text-xs font-bold
                            @if($txn->type === 'dp') bg-blue-100 text-blue-800
                            @elseif($txn->type === 'pelunasan') bg-green-100 text-green-800
                            @elseif($txn->type === 'checkin_payment') bg-purple-100 text-purple-800
                            @elseif($txn->type === 'refund') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ strtoupper(str_replace('_', ' ', $txn->type)) }}
                        </span>
                    </td>
                    <td class="p-2 text-right font-bold">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 border-t-2">
                    <td colspan="4" class="p-2 font-bold text-right">TOTAL DIBAYAR</td>
                    <td class="p-2 text-right font-bold text-green-700">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
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
                        <input type="number" name="ota_paid_amount" id="otaPaidAmount" class="w-full border rounded px-2 py-2 text-sm" min="0" step="1000" placeholder="0" value="{{ $otaPaid > 0 ? $otaPaid : '' }}" oninput="calcSisaBayar()">
                        <p class="text-[10px] text-gray-400 mt-0.5">Nominal yang sudah dibayarkan OTA</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nominal Bayar Hotel (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="paymentAmount" class="w-full border rounded px-2 py-2 text-sm" min="0" step="1000" placeholder="0" value="0" required oninput="calcSisaBayar()">
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

        <script>
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
    @if($reservation->notes)
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-2"><i class="fas fa-sticky-note text-purple-500 mr-2"></i>Catatan</h3>
        <p class="text-gray-700">{{ $reservation->notes }}</p>
    </div>
    @endif

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
            @if($reservation->status === 'pending')
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
            @if(in_array($reservation->status, ['pending', 'checked_in']))
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
