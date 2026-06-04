@extends('layouts.app')

@section('title', 'Tugas Saya')

@section('header', 'Tugas Housekeeping Saya')

@section('content')
<div class="mb-4">
    <p class="text-gray-600">Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Berikut tugas housekeeping Anda.</p>
</div>

<!-- Staff Stats -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded shadow-sm">
        <p class="text-xs text-gray-500">Menunggu</p>
        <p class="text-xl font-bold">{{ $stats['pending'] }}</p>
    </div>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded shadow-sm">
        <p class="text-xs text-gray-500">Sedang Dikerjakan</p>
        <p class="text-xl font-bold">{{ $stats['in_progress'] }}</p>
    </div>
    <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded shadow-sm">
        <p class="text-xs text-gray-500">Selesai Hari Ini</p>
        <p class="text-xl font-bold">{{ $stats['completed'] }}</p>
    </div>
    <div class="bg-gray-50 border-l-4 border-gray-500 p-3 rounded shadow-sm">
        <p class="text-xs text-gray-500">Total</p>
        <p class="text-xl font-bold">{{ $stats['total'] }}</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
    <a href="{{ route('housekeeping.my-tasks') }}" class="px-3 py-1.5 rounded text-sm {{ $statusFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        Semua
    </a>
    <a href="{{ route('housekeeping.my-tasks', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded text-sm whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        ⏳ Menunggu
    </a>
    <a href="{{ route('housekeeping.my-tasks', ['status' => 'in_progress']) }}" class="px-3 py-1.5 rounded text-sm whitespace-nowrap {{ $statusFilter === 'in_progress' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        🔧 Dikerjakan
    </a>
    <a href="{{ route('housekeeping.my-tasks', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded text-sm whitespace-nowrap {{ $statusFilter === 'completed' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        ✅ Selesai
    </a>
    <a href="{{ route('housekeeping.available-rooms') }}" class="px-3 py-1.5 rounded text-sm bg-purple-100 text-purple-700 hover:bg-purple-200 whitespace-nowrap">
        <i class="fas fa-plus-circle mr-1"></i> Ambil Tugas Baru
    </a>
</div>

<!-- Task Cards (mobile-first) -->
@if($tasks->count() > 0)
    <div class="space-y-3">
        @foreach($tasks as $task)
        <div class="bg-white rounded-lg shadow p-4 border-l-4 
            {{ $task->priority === 'urgent' ? 'border-red-500' : ($task->priority === 'high' ? 'border-orange-500' : ($task->status === 'in_progress' ? 'border-blue-500' : 'border-gray-300')) }}">
            
            <div class="flex items-start justify-between mb-2">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-bold">{{ $task->room->room_number ?? '-' }}</span>
                        <span class="text-xs text-gray-500">{{ $task->room->room_type_name ?? '' }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full border {{ $task->priority_color }}">
                            {{ $task->priority_label }}
                        </span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full border {{ $task->status_color }}">
                            {{ $task->status_label }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    @if($task->status === 'pending')
                        <button onclick="updateMyTaskStatus({{ $task->id }}, 'in_progress')" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition active:scale-95">
                            <i class="fas fa-play mr-1"></i> Mulai
                        </button>
                    @elseif($task->status === 'in_progress')
                        <button onclick="updateMyTaskStatus({{ $task->id }}, 'completed')" 
                                class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-600 transition active:scale-95">
                            <i class="fas fa-check mr-1"></i> Selesai
                        </button>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fas {{ $task->task_type_icon }}"></i>
                <span>{{ $task->task_type_label }}</span>
                @if($task->description)
                    <span class="text-gray-300">|</span>
                    <span class="truncate max-w-[150px]">{{ $task->description }}</span>
                @endif
            </div>

            @if($task->status === 'in_progress' && $task->started_at)
                <div class="mt-2 text-xs text-blue-600">
                    <i class="fas fa-hourglass-half mr-1"></i> 
                    Dimulai {{ $task->started_at->diffForHumans() }}
                </div>
            @endif

            @if($task->status === 'completed' && $task->duration_label)
                <div class="mt-2 text-xs text-green-600">
                    <i class="fas fa-clock mr-1"></i> 
                    Selesai dalam {{ $task->duration_label }}
                </div>
            @endif

            <!-- Checklist progress -->
            @if($task->checklistItems->count() > 0)
                @php
                    $totalItems = $task->checklistItems->count();
                    $checkedItems = $task->checklistItems->where('is_checked', true)->count();
                    $progress = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;
                @endphp
                <div class="mt-2">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Checklist: {{ $checkedItems }}/{{ $totalItems }}</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @endif
        </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12 text-gray-500">
        <i class="fas fa-clipboard-check text-5xl mb-3"></i>
        <p class="text-lg font-medium">Tidak ada tugas</p>
        <p class="text-sm mb-4">
            @if($statusFilter === 'all')
                Anda belum memiliki tugas housekeeping.
            @else
                Tidak ada tugas dengan status ini.
            @endif
        </p>
        <a href="{{ route('housekeeping.available-rooms') }}" class="inline-block bg-purple-500 text-white px-5 py-2 rounded-lg text-sm hover:bg-purple-600">
            <i class="fas fa-plus-circle mr-1"></i> Ambil Tugas Baru
        </a>
    </div>
@endif

@endsection

@section('scripts')
<script>
// ─── Update Status from My Tasks ────────────────────────────────────
function updateMyTaskStatus(taskId, status) {
    var labels = {
        'in_progress': 'Mulai mengerjakan tugas ini?',
        'completed': 'Selesaikan tugas ini?',
    };
    
    if (!confirm(labels[status] || 'Update status?')) return;
    
    fetch('{{ url("housekeeping") }}/' + taskId + '/status', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status: status }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Toast.success(data.message);
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            Toast.error('Gagal update status');
        }
    })
    .catch(function() { Toast.error('Gagal update status'); });
}
</script>
@endsection
