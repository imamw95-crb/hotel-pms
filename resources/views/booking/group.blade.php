@extends('layouts.app')

@section('title', 'Booking Group')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Booking Group (Multiple Kamar)</h2>

    <!-- Status Ketersediaan -->
    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.group.store') }}" id="bookingGroupForm" data-ajax="true">
        @csrf

        <!-- Check-in & Check-out -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>

        <!-- Pilih Kamar (filter otomatis by tanggal) -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Pilih Kamar (bisa lebih dari satu)</label>
            <div id="roomsContainer" class="grid grid-cols-2 md:grid-cols-3 gap-2 border rounded p-3 max-h-60 overflow-y-auto bg-gray-50">
                <p class="col-span-full text-gray-500 text-center py-4">Pilih tanggal check-in & check-out dulu untuk melihat kamar tersedia</p>
            </div>
            <p class="text-xs text-gray-500 mt-1" id="roomInfo">Kamar yang tersedia akan muncul setelah memilih tanggal</p>
        </div>

        <!-- Selected Rooms with Individual Prices -->
        <div id="selectedRoomsSection" class="hidden mb-6">
            <div class="flex justify-between items-center mb-3">
                <label class="block text-gray-700 font-bold">Kamar Terpilih & Harga per Malam</label>
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">Harga Semua Kamar:</label>
                    <input type="number" id="bulkPrice" placeholder="Rp" class="w-32 border rounded px-2 py-1 text-sm" min="0" step="1000">
                    <button type="button" onclick="BookingGroup.applyBulkPrice()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">Apply</button>
                </div>
            </div>
            <div class="border rounded overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="text-left p-2 font-bold">Kamar</th>
                            <th class="text-left p-2 font-bold">Tipe</th>
                            <th class="text-center p-2 font-bold">Harga Default</th>
                            <th class="text-center p-2 font-bold">Harga per Malam (Rp)</th>
                            <th class="text-center p-2 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="selectedRoomsTable">
                    </tbody>
                    <tfoot>
                        <tr class="bg-green-50 border-t-2 border-green-300">
                            <td colspan="4" class="p-2 font-bold text-green-800 text-right">Total per Malam:</td>
                            <td class="p-2 text-center font-bold text-green-700" id="totalPerNight">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Row: Nama Tamu, Identitas, Telepon, Alamat -->
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Nama Tamu</label>
                <input type="text" name="guest_name" class="w-full border rounded px-3 py-2" required placeholder="Nama tamu">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">No. Identitas</label>
                <input type="text" name="id_number" class="w-full border rounded px-3 py-2" placeholder="KTP / SIM / Passport">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Telepon</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2" placeholder="No. HP">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Alamat</label>
                <input type="text" name="address" class="w-full border rounded px-3 py-2" placeholder="Alamat (opsional)">
            </div>
        </div>

        <!-- Row: Email -->
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div class="col-span-2">
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" placeholder="Email tamu (opsional)">
            </div>
        </div>

        {{-- Row: Metode Bayar + Tipe Pembayaran + Total Tagihan + Nominal DP --}}
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
                <select name="payment_method" id="paymentMethod" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih --</option>
                    @php $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->slug }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Tipe Pembayaran</label>
                <div class="flex space-x-3 mt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="payment_type" value="full" checked onchange="BookingGroup.toggleDpFields()">
                        <span>Lunas</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="payment_type" value="dp" onchange="BookingGroup.toggleDpFields()">
                        <span>DP</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Total Tagihan</label>
                <div class="w-full border rounded px-3 py-2 bg-gray-100 font-bold text-blue-700" id="totalTagihanGroup">Rp 0</div>
            </div>
            <div id="dpAmountSection" class="hidden">
                <label class="block text-gray-700 font-bold mb-2">Nominal DP (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="dp_amount" id="dpAmount" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Masukkan nominal DP">
                <p class="text-xs text-gray-500 mt-1">Sisa: <span id="sisaBayarGroup" class="font-semibold text-orange-600">Rp 0</span></p>
            </div>
        </div>

        <!-- Catatan -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" onclick="closeModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</button>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" id="btnSubmit" disabled>Booking Group</button>
        </div>
    </form>
</div>

<meta name="booking-check-url" content="{{ route('booking.check-availability') }}">
<script src="{{ asset('js/booking-group.js') }}"></script>
@endsection
