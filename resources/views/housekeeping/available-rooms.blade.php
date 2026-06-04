@extends('layouts.app')

@section('title', 'Ambil Tugas')

@section('header', 'Ambil Tugas Housekeeping')

@section('content')
<div class="mb-4">
    <a href="{{ route('housekeeping.my-tasks') }}" class="text-blue-500 hover:underline text-sm mb-2 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tugas Saya
    </a>
    <h2 class="text-xl font-bold mt-1">Kamar yang Membutuhkan Pembersihan</h2>
</div>

<!-- Today's Checkouts -->
@if($todayCheckouts->count() > 0)
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <h3 class="font-bold mb-3">
        <i class="fas fa-sign-out-alt text-red-500 mr-1"></i> 
        Check-out Hari Ini ({{ $todayCheckouts->count() }} kamar)
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
        @foreach($todayCheckouts as $res)
            <button onclick="selfAssign({{ $res->room->id }}, '{{ $res->room->room_number }}')"
                    class="bg-red-50 border border-red-200 rounded-lg p-3 text-center hover:bg-red-100 transition active:scale-95">
                <div class="text-lg font-bold text-red-700">{{ $res->room->room_number }}</div>
                <div class="text-xs text-gray-500 truncate">{{ $res->guest->guest_name ?? '-' }}</div>
                <div class="text-xs text-red-500 mt-1">
                    <i class="fas fa-clock mr-0.5"></i> {{ $res->check_out->format('H:i') }}
                </div>
            </button>
        @endforeach
    </div>
</div>
@endif

<!-- Dirty Rooms (available + cleaning status) -->
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-bold mb-3">
        <i class="fas fa-broom text-yellow-500 mr-1"></i>
        Kamar Perlu Dibersihkan ({{ $dirtyRooms->count() }} kamar)
    </h3>
    @if($dirtyRooms->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
            @foreach($dirtyRooms as $room)
                <button onclick="selfAssign({{ $room->id }}, '{{ $room->room_number }}')"
                        class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 text-center hover:bg-yellow-100 transition active:scale-95">
                    <div class="text-lg font-bold text-yellow-800">{{ $room->room_number }}</div>
                    <div class="text-xs text-gray-500">{{ $room->room_type_name ?? '' }}</div>
                    <div class="text-xs text-yellow-600 mt-1">
                        <i class="fas fa-plus-circle mr-0.5"></i> Ambil Tugas
                    </div>
                </button>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-check-circle text-green-500 text-4xl mb-2"></i>
            <p class="font-medium">Semua kamar sudah bersih!</p>
            <p class="text-sm">Tidak ada kamar yang membutuhkan pembersihan saat ini.</p>
        </div>
    @endif
</div>

<script>
function selfAssign(roomId, roomNumber) {
    if (!confirm('Ambil tugas cleaning untuk kamar ' + roomNumber + '?')) return;
    
    fetch('{{ route("housekeeping.self-assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            room_id: roomId,
            task_type: 'cleaning',
        }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Toast.success(data.message);
            setTimeout(function() {
                window.location.href = '{{ route("housekeeping.my-tasks") }}';
            }, 1000);
        } else {
            Toast.error(data.message || 'Gagal mengambil tugas');
        }
    })
    .catch(function() { Toast.error('Gagal mengambil tugas'); });
}
</script>
@endsection
