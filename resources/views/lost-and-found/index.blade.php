@extends('layouts.app')

@section('title', 'Lost & Found')

@section('header', 'Lost & Found')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
        <h2 class="text-xl font-bold">Barang Temuan</h2>
        <p class="text-gray-600 text-sm">Kelola barang temuan housekeeping</p>
    </div>
    <button onclick="openCreateModal()" class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600 transition">
        <i class="fas fa-plus mr-1"></i> Lapor Barang Temuan
    </button>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-3 mb-4">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="status" class="border rounded px-2 py-1 text-sm">
            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
            <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Dilaporkan</option>
            <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Sudah Diambil</option>
            <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Dibuang</option>
        </select>
        <input type="text" name="search" placeholder="Cari barang..." value="{{ request('search') }}" class="border rounded px-2 py-1 text-sm w-40">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1 text-sm w-[130px]">
        <span class="text-sm text-gray-500">s/d</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1 text-sm w-[130px]">
        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm"><i class="fas fa-search"></i></button>
        <a href="{{ route('lost-and-found.index') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
    </form>
</div>

<!-- Items List -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Ditemukan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ $item->item_name }}</span>
                        @if($item->description)
                            <span class="text-xs text-gray-500 block truncate max-w-[200px]">{{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $item->room->room_number ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $item->guest_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $item->found_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $item->storage_location ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border {{ $item->status_color }}">
                            {{ $item->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <button onclick="showItemDetail({{ $item->id }})" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($item->status === 'reported')
                            <button onclick="claimItem({{ $item->id }})" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Tandai Diambil">
                                <i class="fas fa-hand"></i>
                            </button>
                            @endif
                            <form method="POST" action="{{ route('lost-and-found.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-500">
                        <i class="fas fa-box-open text-3xl mb-2"></i>
                        <p>Belum ada barang temuan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="p-3 border-t">
            {{ $items->links() }}
        </div>
    @endif
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Lapor Barang Temuan</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('lost-and-found.store') }}" enctype="multipart/form-data" class="p-4 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="item_name" required class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kamar</label>
                    <select name="room_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Pilih Kamar</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tamu</label>
                    <input type="text" name="guest_name" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Ditemukan <span class="text-red-500">*</span></label>
                    <input type="date" name="found_date" required value="{{ date('Y-m-d') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border rounded px-3 py-2 text-sm" placeholder="Deskripsi barang..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="storage_location" class="w-full border rounded px-3 py-2 text-sm" placeholder="Mis: Lemari lost & found">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Detail Barang Temuan</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <div id="detailContent" class="p-4">
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p>
            </div>
        </div>
    </div>
</div>

<!-- Claim Modal -->
<div id="claimModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Tandai Sudah Diambil</h3>
            <button onclick="closeClaimModal()" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="claimForm" method="POST" action="" class="p-4 space-y-4">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="claimed">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Diambil Oleh <span class="text-red-500">*</span></label>
                <input type="text" name="claimed_by" required class="w-full border rounded px-3 py-2 text-sm" placeholder="Nama pengambil">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeClaimModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-green-500 text-white rounded hover:bg-green-600">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); }
function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); }
function closeDetailModal() { document.getElementById('detailModal').classList.add('hidden'); }
function closeClaimModal() { document.getElementById('claimModal').classList.add('hidden'); }

function showItemDetail(id) {
    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('detailContent').innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p></div>';

    fetch('{{ url("lost-and-found") }}/' + id, { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var item = data.item;
            var statusLabels = { 'reported': 'Dilaporkan', 'claimed': 'Sudah Diambil', 'disposed': 'Dibuang' };
            document.getElementById('detailContent').innerHTML =
                '<div class="space-y-3">' +
                    '<div class="grid grid-cols-2 gap-3">' +
                        '<div><p class="text-sm text-gray-500">Barang</p><p class="font-medium">' + item.item_name + '</p></div>' +
                        '<div><p class="text-sm text-gray-500">Kamar</p><p class="font-medium">' + (item.room?.room_number || '-') + '</p></div>' +
                        '<div><p class="text-sm text-gray-500">Tamu</p><p class="font-medium">' + (item.guest_name || '-') + '</p></div>' +
                        '<div><p class="text-sm text-gray-500">Tanggal</p><p class="font-medium">' + (item.found_date || '-') + '</p></div>' +
                        '<div><p class="text-sm text-gray-500">Lokasi</p><p class="font-medium">' + (item.storage_location || '-') + '</p></div>' +
                        '<div><p class="text-sm text-gray-500">Status</p><p><span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border ' + (item.status_color || 'bg-gray-100') + '">' + (statusLabels[item.status] || item.status) + '</span></p></div>' +
                    '</div>' +
                    (item.description ? '<div><p class="text-sm text-gray-500">Deskripsi</p><p class="text-sm">' + item.description + '</p></div>' : '') +
                    (item.notes ? '<div><p class="text-sm text-gray-500">Catatan</p><p class="text-sm">' + item.notes + '</p></div>' : '') +
                    (item.claimed_by ? '<div><p class="text-sm text-gray-500">Diambil Oleh</p><p class="text-sm">' + item.claimed_by + '</p></div>' : '') +
                    (item.photo_url ? '<div><img src="' + item.photo_url + '" class="rounded border w-full h-32 object-cover"></div>' : '') +
                '</div>';
        }
    });
}

function claimItem(id) {
    document.getElementById('claimForm').action = '{{ url("lost-and-found") }}/' + id + '/status';
    document.getElementById('claimModal').classList.remove('hidden');
}

// Close modals on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('bg-black/50')) {
        var modalIds = ['createModal', 'detailModal', 'claimModal'];
        for (var i = 0; i < modalIds.length; i++) {
            var m = document.getElementById(modalIds[i]);
            if (m && !m.classList.contains('hidden')) {
                m.classList.add('hidden');
                break;
            }
        }
    }
});
</script>
@endsection
