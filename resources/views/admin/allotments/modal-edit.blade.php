{{-- Allotment Edit Modal --}}
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-edit text-yellow-600"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Allotment</h2>
            <p class="text-sm text-gray-500">
                {{ $allotment->roomType->name ?? 'Tipe Kamar' }} —
                {{ \Carbon\Carbon::parse($allotment->date)->isoFormat('DD MMM YYYY') }}
                <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full uppercase ml-1">API</span>
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('allotments.update', $allotment) }}" data-ajax="true" data-refresh="true">
        @csrf @method('PUT')

        {{-- Allotment --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Allotment <span class="text-red-500">*</span></label>
            <input type="number" name="allotment" value="{{ old('allotment', $allotment->allotment) }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                required min="0" placeholder="Maksimal kamar yang dijual">
            <p class="text-xs text-gray-400 mt-1">Jumlah maksimal kamar yang dapat dijual untuk tipe ini</p>
            @error('allotment')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Booked --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Terbooking</label>
            <input type="number" name="booked" value="{{ old('booked', $allotment->booked) }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                min="0" placeholder="Jumlah terbooking">
            <p class="text-xs text-gray-400 mt-1">Jumlah yang sudah terbooking (otomatis bertambah saat booking via API)</p>
            @error('booked')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Price --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Spesial (Rp)</label>
            <input type="number" name="price" value="{{ old('price', $allotment->price) }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                min="0" placeholder="Kosongkan untuk pakai harga master">
            <p class="text-xs text-gray-400 mt-1">Harga per malam untuk allotment ini. Kosongkan = pakai harga master kamar ({{ number_format($allotment->getEffectivePrice(), 0, ',', '.') }}).</p>
            @error('price')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Status Info --}}
        @php
            $remaining = $allotment->allotment - $allotment->booked;
        @endphp
        <div class="bg-gray-50 rounded-lg p-3 mb-6">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Sisa allotment saat ini:</span>
                <span class="font-bold {{ $remaining <= 0 ? 'text-red-600' : ($remaining <= 3 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ $remaining }} kamar
                </span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" onclick="Modal.close()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition cursor-pointer">Batal</button>
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer">
                <i class="fas fa-save mr-1"></i> Update Allotment
            </button>
        </div>
    </form>
</div>
