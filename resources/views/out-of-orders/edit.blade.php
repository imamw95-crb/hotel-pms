<div class="p-6">
    <h3 class="text-lg font-bold mb-4"><i class="fas fa-edit text-amber-500 mr-2"></i> Edit Out of Order</h3>
    <form id="oooEditForm" method="POST" action="{{ route('out-of-orders.update', $outOfOrder) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kamar <span class="text-red-500">*</span></label>
            <select name="room_id" required class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Pilih Kamar</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ $outOfOrder->room_id == $room->id ? 'selected' : '' }}>{{ $room->room_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" required value="{{ $outOfOrder->start_date->format('Y-m-d') }}"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $outOfOrder->end_date ? $outOfOrder->end_date->format('Y-m-d') : '' }}"
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label>
            <select name="reason" required class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Pilih Alasan</option>
                <option value="renovasi" {{ $outOfOrder->reason == 'renovasi' ? 'selected' : '' }}>Renovasi</option>
                <option value="perbaikan" {{ $outOfOrder->reason == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                <option value="kebersihan" {{ $outOfOrder->reason == 'kebersihan' ? 'selected' : '' }}>Kebersihan Ekstra</option>
                <option value="banjir" {{ $outOfOrder->reason == 'banjir' ? 'selected' : '' }}>Banjir/Kebocoran</option>
                <option value="rusak" {{ $outOfOrder->reason == 'rusak' ? 'selected' : '' }}>Rusak Berat</option>
                <option value="lainnya" {{ $outOfOrder->reason == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="active" {{ $outOfOrder->status == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="completed" {{ $outOfOrder->status == 'completed' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Catatan tambahan...">{{ $outOfOrder->notes }}</textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t">
            <button type="button" onclick="Modal.close()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border rounded hover:bg-gray-50 transition">Batal</button>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-amber-500 rounded hover:bg-amber-600 transition">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('oooEditForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Modal.close();
            window.location.reload();
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(() => {
        form.submit();
    });
});
</script>
