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
            </div>
        </div>
    </div>

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

        <!-- Form Tambah Pembayaran (DP / Pelunasan) -->
        @if($reservation->status !== 'cancelled' && $reservation->status !== 'checked_out' && $reservation->paid_amount < $reservation->total_amount)
        <div class="border-t pt-4 mt-4">
            <h4 class="font-bold text-sm text-gray-600 mb-3 uppercase">
                <i class="fas fa-plus-circle mr-1"></i>Tambah Pembayaran
            </h4>
            <form action="{{ route('reservations.add-payment', $reservation) }}" method="POST" id="paymentForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe Pembayaran</label>
                        <select name="payment_type" class="w-full border rounded px-2 py-2 text-sm" required>
                            <option value="dp">DP (Down Payment)</option>
                            <option value="pelunasan">Pelunasan</option>
                            <option value="tambahan">Tambahan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Metode</label>
                        <select name="payment_method" class="w-full border rounded px-2 py-2 text-sm" required>
                            <option value="cash">Tunai</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="debit_card">Kartu Debit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" id="paymentAmount" class="w-full border rounded px-2 py-2 text-sm" min="1" step="1000" placeholder="Masukkan nominal" required>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="button" onclick="setMaxPayment()" class="bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300 w-full">Sisa: Rp {{ number_format($reservation->total_amount - $reservation->paid_amount, 0, ',', '.') }}</button>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                        <i class="fas fa-money-bill-wave mr-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Catatan -->
    @if($reservation->notes)
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h3 class="font-bold text-lg mb-2"><i class="fas fa-sticky-note text-purple-500 mr-2"></i>Catatan</h3>
        <p class="text-gray-700">{{ $reservation->notes }}</p>
    </div>
    @endif

    <!-- Tombol Aksi -->
    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('reservations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div class="flex space-x-2">
            @if($reservation->status === 'pending')
                <form action="{{ route('reservations.checkin', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-sign-in-alt mr-1"></i> Check-in
                    </button>
                </form>
                <form action="{{ route('reservations.cancel', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Batalkan reservasi ini?')">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </form>
            @endif
            @if($reservation->status === 'checked_in')
                <form action="{{ route('reservations.checkout', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                        <i class="fas fa-sign-out-alt mr-1"></i> Check-out
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
    function setMaxPayment() {
        const sisa = {{ $reservation->total_amount - $reservation->paid_amount }};
        document.getElementById('paymentAmount').value = sisa;
    }

    document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
        const amount = parseInt(document.getElementById('paymentAmount').value) || 0;
        const sisa = {{ $reservation->total_amount - $reservation->paid_amount }};
        if (amount <= 0) {
            alert('Nominal pembayaran harus lebih dari 0!');
            e.preventDefault();
            return;
        }
        if (amount > sisa) {
            alert('Nominal pembayaran melebihi sisa bayar (Rp ' + sisa.toLocaleString('id-ID') + ')!');
            e.preventDefault();
            return;
        }
        if (!confirm('Simpan pembayaran sebesar Rp ' + amount.toLocaleString('id-ID') + '?')) {
            e.preventDefault();
        }
    });
</script>
@endsection
