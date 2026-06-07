<div class="p-6">
    <h3 class="text-lg font-bold mb-4">
        <i class="fas fa-info-circle text-blue-500 mr-2"></i> Detail Out of Order
    </h3>
    <div class="space-y-3">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wider">Kamar</label>
                <p class="font-medium">{{ $outOfOrder->room->room_number ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wider">Tipe Kamar</label>
                <p class="font-medium">{{ $outOfOrder->room->room_type_name ?? $outOfOrder->room->roomType->name ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wider">Tanggal Mulai</label>
                <p class="font-medium">{{ $outOfOrder->start_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wider">Tanggal Selesai</label>
                <p class="font-medium">{{ $outOfOrder->end_date ? $outOfOrder->end_date->format('d/m/Y') : '-' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wider">Durasi</label>
                <p class="font-medium">{{ $outOfOrder->duration_days }} hari</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wider">Status</label>
                <p><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $outOfOrder->status_color }}">{{ $outOfOrder->status_label }}</span></p>
            </div>
        </div>
        <div>
            <label class="text-xs text-gray-500 uppercase tracking-wider">Alasan</label>
            <p class="font-medium">{{ $outOfOrder->reason }}</p>
        </div>
        @if($outOfOrder->notes)
        <div>
            <label class="text-xs text-gray-500 uppercase tracking-wider">Catatan</label>
            <p class="text-gray-700">{{ $outOfOrder->notes }}</p>
        </div>
        @endif
        <div>
            <label class="text-xs text-gray-500 uppercase tracking-wider">Dibuat Oleh</label>
            <p class="font-medium">{{ $outOfOrder->createdBy->name ?? '-' }}</p>
        </div>
        <div class="text-xs text-gray-400">
            Dibuat: {{ $outOfOrder->created_at->format('d/m/Y H:i') }}
            @if($outOfOrder->updated_at != $outOfOrder->created_at)
                | Diupdate: {{ $outOfOrder->updated_at->format('d/m/Y H:i') }}
            @endif
        </div>
    </div>
    <div class="flex justify-end gap-2 pt-4 border-t mt-4">
        <button type="button" onclick="Modal.close()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border rounded hover:bg-gray-50 transition">Tutup</button>
    </div>
</div>
