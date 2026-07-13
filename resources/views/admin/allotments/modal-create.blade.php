{{-- Allotment Create Modal --}}
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-warehouse text-blue-600"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Tambah Allotment</h2>
            <p class="text-sm text-gray-500">Atur alokasi kamar untuk tipe kamar pada tanggal tertentu</p>
        </div>
    </div>

    <form method="POST" action="{{ route('allotments.store') }}" data-ajax="true" data-refresh="true">
        @csrf

        {{-- Room Type --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kamar <span class="text-red-500">*</span></label>
            <select name="room_type_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                <option value="">-- Pilih Tipe Kamar --</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }} ({{ $type->code }})</option>
                @endforeach
            </select>
            @error('room_type_id')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Date Range --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="date_from" value="{{ old('date_from', date('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                @error('date_from')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ old('date_to') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika hanya 1 hari</p>
                @error('date_to')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Allotment --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Allotment <span class="text-red-500">*</span></label>
            <input type="number" name="allotment" value="{{ old('allotment') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required min="0" placeholder="Maksimal kamar yang dijual">
            <p class="text-xs text-gray-400 mt-1">Jumlah maksimal kamar yang dapat dijual untuk tipe ini via API</p>
            @error('allotment')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Price --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Spesial (Rp)</label>
            <input type="number" name="price" value="{{ old('price') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                min="0" step="1000" placeholder="Kosongkan untuk pakai harga master">
            <p class="text-xs text-gray-400 mt-1">Harga per malam untuk allotment ini. Kosongkan = pakai harga master kamar.</p>
            @error('price')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Info --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
            <div class="flex items-start gap-2">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div class="text-xs text-blue-700">
                    <p class="font-medium mb-1">💡 Cara Kerja Allotment:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li>Allotment membatasi jumlah kamar yang bisa dijual per tipe kamar per tanggal via <strong>API</strong></li>
                        <li>Jika mengisi range tanggal, allotment akan dibuat untuk <strong>setiap tanggal</strong> dalam range</li>
                        <li>Booking dari OTA (traveloka, dll) <strong>tidak</strong> dicek allotment</li>
                        <li>Jika allotment sudah penuh, booking dari API akan ditolak meskipun masih ada kamar fisik</li>
                        <li>Jika sudah ada allotment untuk tipe kamar & tanggal yang sama, akan <strong>diupdate</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" onclick="Modal.close()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition cursor-pointer">Batal</button>
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer">
                <i class="fas fa-save mr-1"></i> Simpan Allotment
            </button>
        </div>
    </form>
</div>
