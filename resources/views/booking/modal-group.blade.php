{{-- Booking Group Modal Content — no layout, pure HTML for AJAX modal --}}
<div class="p-6">
    <h2 class="text-2xl font-bold mb-6"><i class="fas fa-users text-green-500 mr-2"></i>Booking Group</h2>

    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.group.store') }}" id="bookingGroupForm" data-ajax="true">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" value="{{ old('check_in', date('Y-m-d')) }}" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" value="{{ old('check_out', date('Y-m-d', strtotime('+1 day'))) }}" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Pilih Kamar</label>
            <div id="roomsContainer" class="grid grid-cols-3 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded p-2">
                <p class="text-gray-500 text-sm col-span-full text-center py-4">Pilih tanggal terlebih dahulu</p>
            </div>
        </div>

        {{-- Selected Rooms Section — shows after rooms are checked --}}
        <div id="selectedRoomsSection" class="hidden mb-4 p-3 border rounded-lg bg-gray-50">
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-bold text-gray-700"><i class="fas fa-bed text-green-500 mr-1"></i> Kamar Dipilih</h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">Harga bulk:</span>
                    <input type="number" id="bulkPrice" class="w-24 border rounded px-2 py-1 text-sm" min="0" step="1000" placeholder="Rp">
                    <button type="button" onclick="BookingGroup.applyBulkPrice()" class="bg-blue-500 text-white text-xs px-2 py-1 rounded hover:bg-blue-600">Terapkan ke Semua</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-500 text-xs">
                            <th class="p-2 text-left">Kamar</th>
                            <th class="p-2 text-left">Tipe</th>
                            <th class="p-2 text-center">Harga Normal (Wd/We)</th>
                            <th class="p-2 text-center">Harga Edit (Rp)</th>
                            <th class="p-2"></th>
                        </tr>
                    </thead>
                    <tbody id="selectedRoomsTable"></tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-200">
                <span class="font-bold text-gray-700">Total Tagihan:</span>
                <span id="totalTagihanGroup" class="font-bold text-lg text-blue-700">Rp 0</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Nama Tamu</label>
                <input type="text" name="guest_name" class="w-full border rounded px-3 py-2" required placeholder="Nama tamu">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">No. Identitas</label>
                <input type="text" name="id_number" class="w-full border rounded px-3 py-2" placeholder="KTP / SIM">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Telepon</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2" placeholder="No. HP">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Harga per Malam (Rp)</label>
                <input type="number" name="price_per_night" id="groupPrice" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Kosongkan = gunakan harga weekday/weekend">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk gunakan harga weekday/weekend otomatis per kamar</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
                <select name="payment_method" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih --</option>
                    @php $pms = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                    @foreach($pms as $pm)
                        <option value="{{ $pm->slug }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Tipe Pembayaran</label>
                <div class="flex space-x-4 mt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="payment_type" value="full" checked onchange="toggleDpFields()">
                        <span>Lunas</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="payment_type" value="dp" onchange="toggleDpFields()">
                        <span>DP</span>
                    </label>
                </div>
                {{-- DP Amount Section (hidden by default) --}}
                <div id="dpAmountSection" class="hidden mt-3 p-3 border border-amber-200 rounded-lg bg-amber-50">
                    <label class="block text-gray-700 font-bold mb-1 text-sm">Jumlah DP (Rp)</label>
                    <input type="number" name="dp_amount" id="dpAmount" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Masukkan jumlah DP">
                    <p class="text-xs text-gray-500 mt-1">Sisa bayar: <span id="sisaBayarGroup" class="font-bold text-blue-600">Rp 0</span></p>
                </div>
            </div>
        </div>

        {{-- Sarapan --}}
        <div class="mb-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="include_breakfast" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-gray-700 font-medium"><i class="fas fa-coffee text-amber-600 mr-1"></i> Termasuk Sarapan</span>
            </label>
            <p class="text-xs text-gray-500 mt-1 ml-6">Sarapan sudah termasuk secara default</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" onclick="Modal.close()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</button>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700" id="btnSubmit" disabled>
                <i class="fas fa-save mr-1"></i> Booking Group
            </button>
        </div>
    </form>
</div>

<meta name="booking-check-url" content="{{ route('booking.check-availability') }}">
<script src="{{ asset('js/booking-group.js') }}"></script>
