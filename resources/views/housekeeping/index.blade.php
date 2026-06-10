@extends('layouts.app')

@section('title', 'Housekeeping')

@section('header', 'Housekeeping')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold">Manajemen Housekeeping</h1>
            <p class="text-gray-600">Kelola tugas pembersihan, perbaikan, dan inspeksi kamar</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span id="lastUpdate">⏱ Memuat...</span>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-8">
    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-clock text-yellow-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Menunggu</p>
                <p class="text-2xl font-bold stat-value" id="statPending">{{ $stats['pending'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-spinner text-blue-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Sedang Dikerjakan</p>
                <p class="text-2xl font-bold stat-value" id="statInProgress">{{ $stats['in_progress'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Selesai Hari Ini</p>
                <p class="text-2xl font-bold stat-value" id="statCompleted">{{ $stats['completed'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Urgent</p>
                <p class="text-2xl font-bold stat-value" id="statUrgent">{{ $stats['urgent'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-orange-100 border-l-4 border-orange-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-hourglass-half text-orange-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Overdue</p>
                <p class="text-2xl font-bold stat-value" id="statOverdue">{{ $stats['overdue'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-indigo-100 border-l-4 border-indigo-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-stopwatch text-indigo-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Rata-rata Durasi</p>
                <p class="text-2xl font-bold stat-value" id="statAvgDuration">
                    {{ $stats['avg_duration_minutes'] ? intdiv($stats['avg_duration_minutes'], 60) . 'j ' . ($stats['avg_duration_minutes'] % 60) . 'm' : '-' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold mb-2 text-sm">
            <i class="fas fa-chart-bar text-blue-500 mr-1"></i> Penyelesaian 7 Hari Terakhir
        </h3>
        <div class="relative" style="height:200px;">
            <canvas id="completionChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold mb-2 text-sm">
            <i class="fas fa-chart-pie text-purple-500 mr-1"></i> Distribusi Tipe Tugas
        </h3>
        <div class="relative" style="height:200px;">
            <canvas id="distributionChart"></canvas>
        </div>
    </div>
</div>

<!-- Staff Workload -->
<div class="bg-white rounded-lg shadow p-4 mb-8">
    <h3 class="font-bold mb-3 text-sm">
        <i class="fas fa-users text-green-500 mr-1"></i> Beban Kerja Staff
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        @forelse($staffWorkload as $staff)
            <div class="border rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-bold">
                            {{ substr($staff->name, 0, 1) }}
                        </div>
                        <span class="font-medium text-sm">{{ $staff->name }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ $staff->active_tasks }} aktif</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                         style="width: {{ $staff->workload_percent }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>{{ $staff->completed_today }} selesai hari ini</span>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm col-span-full text-center py-4">Tidak ada staff housekeeping</p>
        @endforelse
    </div>
</div>

<!-- Action Bar -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button onclick="openCreateModal()" class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600 transition">
                <i class="fas fa-plus mr-1"></i> Buat Tugas
            </button>
            <button onclick="openBulkModal()" class="bg-purple-500 text-white px-4 py-2 rounded text-sm hover:bg-purple-600 transition">
                <i class="fas fa-layer-group mr-1"></i> Bulk Create
            </button>
            <a href="{{ route('housekeeping.print', request()->query()) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
                <i class="fas fa-print mr-1"></i> Print
            </a>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <select onchange="filterTasks()" id="filterStatus" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <select onchange="filterTasks()" id="filterType" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                @foreach(\App\Models\HousekeepingTask::TASK_TYPES as $key => $label)
                    <option value="{{ $key }}" {{ $typeFilter === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select onchange="filterTasks()" id="filterPriority" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ $priorityFilter === 'all' ? 'selected' : '' }}>Semua Prioritas</option>
                @foreach(\App\Models\HousekeepingTask::PRIORITIES as $key => $label)
                    <option value="{{ $key }}" {{ $priorityFilter === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select onchange="filterTasks()" id="filterRoom" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ $roomFilter === 'all' ? 'selected' : '' }}>Semua Kamar</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ $roomFilter == $room->id ? 'selected' : '' }}>{{ $room->room_number }}</option>
                @endforeach
            </select>
            <input type="date" id="filterDateFrom" value="{{ $dateFrom }}" onchange="filterTasks()" class="border rounded px-2 py-1 text-sm w-[130px]">
            <span class="text-sm text-gray-500">s/d</span>
            <input type="date" id="filterDateTo" value="{{ $dateTo }}" onchange="filterTasks()" class="border rounded px-2 py-1 text-sm w-[130px]">
            <button onclick="filterTasks()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
</div>

<!-- Room Grid -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-bold">
            <i class="fas fa-th text-gray-500 mr-1"></i> Status Kamar
        </h3>
        <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-400 inline-block"></span> Available</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-400 inline-block"></span> Cleaning</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-400 inline-block"></span> Occupied</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-400 inline-block"></span> Maintenance</span>
        </div>
    </div>
    <div class="flex flex-wrap gap-2" id="roomGrid">
        @php
            use App\Models\Room;
            $allRooms = Room::with(['housekeepingTasks' => function($q) {
                $q->whereIn('status', ['pending', 'in_progress'])->latest();
            }])->orderBy('room_number')->get();
        @endphp
        @foreach($allRooms as $room)
            @php
                $statusColor = match($room->status) {
                    'available' => 'bg-green-400 hover:bg-green-500',
                    'cleaning' => 'bg-yellow-400 hover:bg-yellow-500',
                    'occupied' => 'bg-red-400 hover:bg-red-500',
                    'maintenance' => 'bg-gray-400 hover:bg-gray-500',
                    default => 'bg-gray-300 hover:bg-gray-400',
                };
                $activeTask = $room->housekeepingTasks->first();
            @endphp
            <button onclick="showRoomDetail({{ $room->id }}, '{{ $room->room_number }}')"
                    class="{{ $statusColor }} text-white px-3 py-2 rounded text-sm font-medium transition relative"
                    title="{{ $room->room_number }} - {{ ucfirst($room->status) }}{{ $activeTask ? ' (Task: '.$activeTask->task_type.')' : '' }}">
                {{ $room->room_number }}
                @if($activeTask)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border border-white"></span>
                @endif
            </button>
        @endforeach
    </div>
</div>

<!-- Task List -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold">Daftar Tugas Housekeeping</h2>
        <span class="text-sm text-gray-500">{{ $tasks->count() }} tugas</span>
    </div>

    @if($tasks->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kamar</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe Tugas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioritas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ditugaskan Ke</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tasks as $task)
                    <tr class="hover:bg-gray-50 transition {{ $task->status === 'in_progress' ? 'bg-blue-50/30' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="font-medium">{{ $task->room->room_number ?? '-' }}</span>
                            <span class="text-xs text-gray-500 block">{{ $task->room->room_type_name ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $task->task_type_icon }} text-gray-500"></i>
                                <span>{{ $task->task_type_label }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border {{ $task->priority_color }}">
                                {{ $task->priority_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border {{ $task->status_color }}">
                                {{ $task->status_label }}
                            </span>
                            @if($task->status === 'in_progress' && $task->started_at)
                                <span class="text-xs text-gray-400 block mt-0.5">
                                    {{ $task->started_at->diffForHumans(['parts' => 1]) }}
                                </span>
                            @endif
                            @if($task->status === 'completed' && $task->duration_label)
                                <span class="text-xs text-gray-400 block mt-0.5">
                                    ⏱ {{ $task->duration_label }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($task->assignedTo)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold">
                                        {{ substr($task->assignedTo->name, 0, 1) }}
                                    </div>
                                    <span>{{ $task->assignedTo->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400 italic">Belum ditugaskan</span>
                                <button onclick="autoAssignTask({{ $task->id }})" class="ml-1 text-xs text-blue-500 hover:underline" title="Auto-assign">
                                    <i class="fas fa-magic"></i>
                                </button>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate" title="{{ $task->description }}">
                            {{ $task->description ?: '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $task->created_at->format('d/m/Y H:i') }}
                        </td>
                         <td class="px-4 py-3 whitespace-nowrap">
                             <div class="flex items-center gap-1">
                                 @if($task->status === 'pending')
                                     <button onclick="updateTaskStatus({{ $task->id }}, 'in_progress')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Mulai">
                                         <i class="fas fa-play"></i>
                                     </button>
                                     <button onclick="assignTask({{ $task->id }})" class="p-1.5 text-purple-600 hover:bg-purple-50 rounded transition" title="Tugaskan">
                                         <i class="fas fa-user-plus"></i>
                                     </button>
                                 @endif
                                 @if($task->status === 'in_progress')
                                     <button onclick="updateTaskStatus({{ $task->id }}, 'completed')" class="p-1.5 text-green-600 hover:bg-green-50 rounded transition" title="Selesai">
                                         <i class="fas fa-check"></i>
                                     </button>
                                 @endif
                                 <button onclick="showTaskDetail({{ $task->id }})" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded transition" title="Detail">
                                     <i class="fas fa-eye"></i>
                                 </button>
                                 <form method="POST" action="{{ route('housekeeping.destroy', $task) }}" class="inline" onsubmit="return confirm('Hapus tugas ini?')">
                                     @csrf @method('DELETE')
                                     <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Hapus">
                                         <i class="fas fa-trash"></i>
                                     </button>
                                 </form>
                                 <!-- Ubah Status Manual -->
                                 <form method="POST" action="{{ route('housekeeping.update-status', $task) }}" class="inline-flex items-center gap-0.5">
                                     @csrf @method('PATCH')
                                     <select name="status" class="border border-gray-200 text-xs rounded-l p-0.5 bg-white text-gray-600 w-20" title="Ubah Status Manual">
                                         @php
                                             $statusLabels = ['pending' => 'Menunggu', 'in_progress' => 'Kerja', 'completed' => 'Selesai', 'cancelled' => 'Batal'];
                                         @endphp
                                         @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $optStatus)
                                             @if($optStatus !== $task->status)
                                                 <option value="{{ $optStatus }}">{{ $statusLabels[$optStatus] }}</option>
                                             @endif
                                         @endforeach
                                     </select>
                                     <button type="submit" class="border border-gray-200 rounded-r px-1 py-0.5 text-xs bg-gray-50 hover:bg-gray-100 text-gray-500">
                                         <i class="fas fa-arrow-right"></i>
                                     </button>
                                 </form>
                             </div>
                         </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-clipboard-list text-4xl mb-3"></i>
            <p class="text-lg font-medium">Belum ada tugas housekeeping</p>
            <p class="text-sm">Buat tugas baru untuk mulai mengelola housekeeping</p>
        </div>
    @endif
</div>

<!-- Rooms Needing Cleaning -->
@if($dirtyRooms->count() > 0)
<div class="bg-white rounded-lg shadow p-4 mt-6">
    <h3 class="font-bold text-lg mb-3">
        <i class="fas fa-broom text-yellow-500 mr-2"></i>
        Kamar yang Membutuhkan Pembersihan
        <span class="text-sm font-normal text-gray-500">({{ $dirtyRooms->count() }} kamar)</span>
    </h3>
    <div class="flex flex-wrap gap-2">
        @foreach($dirtyRooms as $room)
            <button onclick="quickCreateCleaning({{ $room->id }}, '{{ $room->room_number }}')" 
                    class="px-3 py-2 bg-yellow-50 border border-yellow-300 rounded-lg text-sm hover:bg-yellow-100 transition flex items-center gap-2">
                <i class="fas fa-door-open text-yellow-600"></i>
                <span class="font-medium">{{ $room->room_number }}</span>
                <span class="text-xs text-gray-500">{{ $room->room_type_name ?? '' }}</span>
                <i class="fas fa-plus text-green-500 text-xs"></i>
            </button>
        @endforeach
    </div>
</div>
@endif

<!-- Create Task Modal -->
<div id="createModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Buat Tugas Housekeeping</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('housekeeping.store') }}" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kamar <span class="text-red-500">*</span></label>
                <select name="room_id" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Pilih Kamar</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Tugas <span class="text-red-500">*</span></label>
                <select name="task_type" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach(\App\Models\HousekeepingTask::TASK_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas <span class="text-red-500">*</span></label>
                <select name="priority" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach(\App\Models\HousekeepingTask::PRIORITIES as $key => $label)
                        <option value="{{ $key }}" {{ $key === 'normal' ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="Deskripsi tugas..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tugaskan Ke</label>
                <select name="assigned_to" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Pilih Staff</option>
                    @foreach($staffUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Create Modal -->
<div id="bulkModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Bulk Create Tugas Housekeeping</h3>
            <button onclick="closeBulkModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('housekeeping.bulk-create') }}" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kamar <span class="text-red-500">*</span></label>
                <div class="max-h-48 overflow-y-auto border rounded p-2 space-y-1">
                    @foreach($rooms as $room)
                        <label class="flex items-center gap-2 text-sm hover:bg-gray-50 p-1 rounded">
                            <input type="checkbox" name="room_ids[]" value="{{ $room->id }}" class="rounded">
                            <span>{{ $room->room_number }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Tugas <span class="text-red-500">*</span></label>
                <select name="task_type" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach(\App\Models\HousekeepingTask::TASK_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas <span class="text-red-500">*</span></label>
                <select name="priority" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach(\App\Models\HousekeepingTask::PRIORITIES as $key => $label)
                        <option value="{{ $key }}" {{ $key === 'normal' ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tugaskan Ke</label>
                <select name="assigned_to" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Pilih Staff</option>
                    @foreach($staffUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeBulkModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-purple-500 text-white rounded hover:bg-purple-600">Buat Tugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Modal -->
<div id="assignModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Tugaskan Staff</h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="assignForm" method="POST" action="" class="p-4 space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Staff <span class="text-red-500">*</span></label>
                <select name="assigned_to" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Pilih Staff</option>
                    @foreach($staffUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAssignModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">Tugaskan</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Update Modal (for notes when cancelling) -->
<div id="statusModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold" id="statusModalTitle">Update Status</h3>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="statusForm" method="POST" action="" class="p-4 space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="statusInput">
            <div id="notesField" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="Alasan pembatalan..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Task Detail Modal (enhanced with checklists + logs) -->
<div id="detailModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold">Detail Tugas</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="detailContent" class="p-4">
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
                <p class="mt-2">Memuat...</p>
            </div>
        </div>
    </div>
</div>

<!-- Room Detail Modal -->
<div id="roomModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-bold" id="roomModalTitle">Detail Kamar</h3>
            <button onclick="closeRoomModal()" class="text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="roomModalContent" class="p-4">
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
                <p class="mt-2">Memuat...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ─── Chart.js Initialization ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    // Completion chart (7 days)
    var completionCtx = document.getElementById('completionChart');
    if (completionCtx && typeof window.Chart !== 'undefined') {
        var chartData = @json($chartData ?? []);
        new window.Chart(completionCtx, {
            type: 'bar',
            data: {
                labels: chartData.map(function(d) { return d.label; }),
                datasets: [{
                    label: 'Selesai',
                    data: chartData.map(function(d) { return d.completed; }),
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                }, {
                    label: 'Dibuat',
                    data: chartData.map(function(d) { return d.created; }),
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Distribution chart (pie)
    fetch('{{ route("housekeeping.distribution") }}')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && document.getElementById('distributionChart') && typeof window.Chart !== 'undefined') {
                var distCtx = document.getElementById('distributionChart');
                new window.Chart(distCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.data.map(function(d) { return d.type; }),
                        datasets: [{
                            data: data.data.map(function(d) { return d.count; }),
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(139, 92, 246, 0.7)',
                                'rgba(239, 68, 68, 0.7)',
                                'rgba(34, 197, 94, 0.7)',
                                'rgba(234, 179, 8, 0.7)',
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } }
                    }
                });
            }
        });
    
    // Start auto-polling
    startStatsPolling();
});

// ─── Stats Auto-Polling (every 15s) ─────────────────────────────────
function startStatsPolling() {
    setInterval(function() {
        fetch('{{ route("housekeeping.stats") }}')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var s = data.stats;
                    updateStat('statPending', s.pending);
                    updateStat('statInProgress', s.in_progress);
                    updateStat('statCompleted', s.completed);
                    updateStat('statUrgent', s.urgent);
                    updateStat('statOverdue', s.overdue || 0);
                    if (s.avg_duration_minutes) {
                        var avgEl = document.getElementById('statAvgDuration');
                        if (avgEl) {
                            var h = Math.floor(s.avg_duration_minutes / 60);
                            var m = s.avg_duration_minutes % 60;
                            avgEl.textContent = h + 'j ' + m + 'm';
                        }
                    }
                    document.getElementById('lastUpdate').textContent = '⏱ Terakhir: ' + new window.Date().toLocaleTimeString('id-ID');
                }
            });
    }, 15000);
}

function updateStat(id, value) {
    var el = document.getElementById(id);
    if (el) {
        el.textContent = value;
        el.style.transform = 'scale(1.15)';
        setTimeout(function() { el.style.transform = 'scale(1)'; }, 300);
    }
}

// ─── Filter ──────────────────────────────────────────────────────────
function filterTasks() {
    var fields = ['status','type','priority','room_id','date_from','date_to'];
    var parts = [];
    for (var i = 0; i < fields.length; i++) {
        var el = document.getElementById('filter' + fields[i].charAt(0).toUpperCase() + fields[i].slice(1));
        if (el) parts.push(encodeURIComponent(fields[i]) + '=' + encodeURIComponent(el.value));
    }
    window.location.href = '{{ route("housekeeping.index") }}?' + parts.join('&');
}

// ─── Modal Helpers ───────────────────────────────────────────────────
function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); }
function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); }

function openBulkModal() { document.getElementById('bulkModal').classList.remove('hidden'); }
function closeBulkModal() { document.getElementById('bulkModal').classList.add('hidden'); }

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.getElementById('notesField').classList.add('hidden');
}

function closeAssignModal() { document.getElementById('assignModal').classList.add('hidden'); }
function closeDetailModal() { document.getElementById('detailModal').classList.add('hidden'); }
function closeRoomModal() { document.getElementById('roomModal').classList.add('hidden'); }

// ─── Quick Create Cleaning ───────────────────────────────────────────
function quickCreateCleaning(roomId, roomNumber) {
    if (!confirm('Buat tugas pembersihan untuk kamar ' + roomNumber + '?')) return;
    
    fetch('{{ route("housekeeping.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ room_id: roomId, task_type: 'cleaning', priority: 'normal' }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Toast.success(data.message);
            setTimeout(function() { window.location.reload(); }, 1000);
        }
    })
    .catch(function() { Toast.error('Gagal membuat tugas'); });
}

// ─── Update Status ───────────────────────────────────────────────────
function updateTaskStatus(taskId, status) {
    if (status === 'cancelled') {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusModalTitle').textContent = 'Batalkan Tugas';
        document.getElementById('notesField').classList.remove('hidden');
        document.getElementById('statusForm').action = '{{ url("housekeeping") }}/' + taskId + '/status';
        document.getElementById('statusModal').classList.remove('hidden');
        return;
    }
    
    var labels = {
        'pending': 'Ubah status menjadi Menunggu?',
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
        }
    })
    .catch(function() { Toast.error('Gagal update status'); });
}

// ─── Assign Task ─────────────────────────────────────────────────────
function assignTask(taskId) {
    document.getElementById('assignForm').action = '{{ url("housekeeping") }}/' + taskId + '/assign';
    document.getElementById('assignModal').classList.remove('hidden');
}

// ─── Auto-Assign Task ────────────────────────────────────────────────
function autoAssignTask(taskId) {
    if (!confirm('Auto-assign tugas ini ke staff dengan beban kerja paling ringan?')) return;
    
    fetch('{{ url("housekeeping") }}/' + taskId + '/auto-assign', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Toast.success(data.message + ' (' + data.task.assigned_to?.name + ')');
            setTimeout(function() { window.location.reload(); }, 1000);
        }
    })
    .catch(function() { Toast.error('Gagal auto-assign'); });
}

// ─── Show Room Detail ────────────────────────────────────────────────
function showRoomDetail(roomId, roomNumber) {
    document.getElementById('roomModalTitle').textContent = 'Kamar ' + roomNumber;
    document.getElementById('roomModal').classList.remove('hidden');
    document.getElementById('roomModalContent').innerHTML =
        '<div class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p></div>';
    
    fetch('{{ url("housekeeping/room") }}/' + roomId + '/history', {
        headers: { 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var html = '<div class="space-y-3">' +
                '<p class="text-sm text-gray-600">Riwayat pembersihan kamar <strong>' + data.room + '</strong></p>';
            if (data.history.length === 0) {
                html += '<p class="text-gray-400 text-center py-4">Belum ada riwayat pembersihan</p>';
            } else {
                html += '<div class="divide-y">';
                for (var i = 0; i < data.history.length; i++) {
                    var t = data.history[i];
                    html += '<div class="py-2 flex items-center justify-between">' +
                        '<div><span class="text-sm">' + (t.task_type_label || t.task_type) + '</span>' +
                        '<span class="text-xs text-gray-400 ml-2">oleh ' + (t.completed_by?.name || '-') + '</span></div>' +
                        '<span class="text-xs text-gray-500">' + (t.completed_at ? t.completed_at.substring(0, 10) : '-') + '</span></div>';
                }
                html += '</div>';
            }
            html += '<div class="flex gap-2 pt-2">' +
                '<button onclick="quickCreateCleaning(' + roomId + ', \'' + roomNumber + '\')" class="bg-blue-500 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-600">Buat Tugas Cleaning</button>' +
                '</div></div>';
            document.getElementById('roomModalContent').innerHTML = html;
        }
    });
}

// ─── Task Detail (enhanced with checklists + logs) ───────────────────
function showTaskDetail(taskId) {
    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('detailContent').innerHTML =
        '<div class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p></div>';
    
    fetch('{{ url("housekeeping") }}/' + taskId, {
        headers: { 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            renderTaskDetail(data.task);
        }
    })
    .catch(function() {
        document.getElementById('detailContent').innerHTML =
            '<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-circle text-2xl"></i><p class="mt-2">Gagal memuat detail tugas</p></div>';
    });
}

function renderTaskDetail(task) {
    var statusLabels = { 'pending': 'Menunggu', 'in_progress': 'Sedang Dikerjakan', 'completed': 'Selesai', 'cancelled': 'Dibatalkan' };
    var priorityLabels = { 'low': 'Rendah', 'normal': 'Normal', 'high': 'Tinggi', 'urgent': 'Urgent' };
    var typeLabels = {
        'cleaning': 'Pembersihan Reguler', 'deep_clean': 'Pembersihan Mendalam',
        'maintenance': 'Perbaikan/Maintenance', 'inspection': 'Inspeksi Kamar', 'turndown': 'Turndown Service'
    };
    
    var assignedToName = '-';
    if (task.assignedTo && typeof task.assignedTo === 'object' && task.assignedTo.name) {
        assignedToName = task.assignedTo.name;
    } else if (task.assigned_to && typeof task.assigned_to === 'object' && task.assigned_to.name) {
        assignedToName = task.assigned_to.name;
    }
    
    var createdByName = '-';
    if (task.createdBy && typeof task.createdBy === 'object' && task.createdBy.name) {
        createdByName = task.createdBy.name;
    } else if (task.created_by && typeof task.created_by === 'object' && task.created_by.name) {
        createdByName = task.created_by.name;
    }
    
    var completedByName = '-';
    if (task.completedBy && typeof task.completedBy === 'object' && task.completedBy.name) {
        completedByName = task.completedBy.name;
    } else if (task.completed_by && typeof task.completed_by === 'object' && task.completed_by.name) {
        completedByName = task.completed_by.name;
    }
    
    // Checklist section
    var checklistHtml = '';
    if (task.checklist_items && task.checklist_items.length > 0) {
        checklistHtml = '<div class="mt-4"><h4 class="font-semibold text-sm mb-2"><i class="fas fa-list-check text-green-500 mr-1"></i> Checklist</h4><div class="space-y-1">';
        for (var i = 0; i < task.checklist_items.length; i++) {
            var ci = task.checklist_items[i];
            var checked = ci.is_checked ? 'checked' : '';
            var checkedClass = ci.is_checked ? 'line-through text-gray-400' : '';
            checklistHtml += '<label class="flex items-center gap-2 text-sm p-1 rounded hover:bg-gray-50">' +
                '<input type="checkbox" ' + checked + ' onclick="toggleChecklist(' + ci.id + ', this.checked)" class="rounded">' +
                '<span class="' + checkedClass + '">' + ci.item_name + '</span>' +
                (ci.checked_by ? '<span class="text-xs text-gray-400 ml-auto">oleh ' + (ci.checked_by?.name || '-') + '</span>' : '') +
                '</label>';
        }
        checklistHtml += '</div></div>';
    }
    
    // Logs section
    var logsHtml = '';
    if (task.logs && task.logs.length > 0) {
        logsHtml = '<div class="mt-4"><h4 class="font-semibold text-sm mb-2"><i class="fas fa-history text-blue-500 mr-1"></i> Riwayat</h4><div class="space-y-1 max-h-40 overflow-y-auto">';
        for (var i = 0; i < task.logs.length; i++) {
            var log = task.logs[i];
            var fromStatus = log.old_status ? (statusLabels[log.old_status] || log.old_status) : '-';
            var toStatus = statusLabels[log.new_status] || log.new_status;
            logsHtml += '<div class="flex items-center gap-2 text-xs text-gray-600 py-1 border-b border-gray-100 last:border-b-0">' +
                '<span class="font-medium">' + (log.user?.name || 'System') + '</span>' +
                '<span class="text-gray-300">→</span>' +
                '<span>' + fromStatus + ' → ' + toStatus + '</span>' +
                (log.notes ? '<span class="text-gray-400">(' + log.notes + ')</span>' : '') +
                '<span class="ml-auto text-gray-400">' + (log.created_at ? log.created_at.substring(0, 16).replace('T', ' ') : '') + '</span>' +
                '</div>';
        }
        logsHtml += '</div></div>';
    }
    
    // Photo section
    var photoHtml = '';
    if (task.photo_before_url || task.photo_after_url) {
        photoHtml = '<div class="mt-4"><h4 class="font-semibold text-sm mb-2"><i class="fas fa-camera text-purple-500 mr-1"></i> Foto</h4><div class="grid grid-cols-2 gap-2">';
        if (task.photo_before_url) {
            photoHtml += '<div><p class="text-xs text-gray-500 mb-1">Sebelum</p><img src="' + task.photo_before_url + '" class="rounded border w-full h-24 object-cover"></div>';
        }
        if (task.photo_after_url) {
            photoHtml += '<div><p class="text-xs text-gray-500 mb-1">Sesudah</p><img src="' + task.photo_after_url + '" class="rounded border w-full h-24 object-cover"></div>';
        }
        photoHtml += '</div></div>';
    }
    
    var durationHtml = task.duration_label ? '<div><p class="text-sm text-gray-500">Durasi</p><p class="font-medium">' + task.duration_label + '</p></div>' : '';
    var conditionHtml = '';
    if (task.room_condition_before || task.room_condition_after) {
        conditionHtml = '<div class="mt-4 border-t pt-2"><h4 class="font-semibold text-sm mb-1"><i class="fas fa-clipboard text-gray-500 mr-1"></i> Kondisi Kamar</h4>';
        if (task.room_condition_before) conditionHtml += '<p class="text-sm"><span class="text-gray-500">Sebelum:</span> ' + task.room_condition_before + '</p>';
        if (task.room_condition_after) conditionHtml += '<p class="text-sm"><span class="text-gray-500">Sesudah:</span> ' + task.room_condition_after + '</p>';
        conditionHtml += '</div>';
    }
    
    document.getElementById('detailContent').innerHTML =
        '<div class="space-y-4">' +
            '<div class="grid grid-cols-2 gap-4">' +
                '<div><p class="text-sm text-gray-500">Kamar</p><p class="font-medium">' + (task.room?.room_number || '-') + '</p></div>' +
                '<div><p class="text-sm text-gray-500">Tipe Tugas</p><p class="font-medium">' + (typeLabels[task.task_type] || task.task_type) + '</p></div>' +
                '<div><p class="text-sm text-gray-500">Prioritas</p><p><span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border ' + (task.priority_color || 'bg-gray-100') + '">' + (priorityLabels[task.priority] || task.priority) + '</span></p></div>' +
                '<div><p class="text-sm text-gray-500">Status</p><p><span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border ' + (task.status_color || 'bg-gray-100') + '">' + (statusLabels[task.status] || task.status) + '</span></p></div>' +
                '<div><p class="text-sm text-gray-500">Ditugaskan Ke</p><p class="font-medium">' + assignedToName + '</p></div>' +
                '<div><p class="text-sm text-gray-500">Dibuat Oleh</p><p class="font-medium">' + createdByName + '</p></div>' +
                (durationHtml || '') +
                (completedByName !== '-' ? '<div><p class="text-sm text-gray-500">Diselesaikan Oleh</p><p class="font-medium">' + completedByName + '</p></div>' : '') +
            '</div>' +
            (task.description ? '<div><p class="text-sm text-gray-500">Deskripsi</p><p class="text-sm mt-1">' + task.description + '</p></div>' : '') +
            (task.notes ? '<div><p class="text-sm text-gray-500">Catatan</p><p class="text-sm mt-1">' + task.notes + '</p></div>' : '') +
            photoHtml +
            conditionHtml +
            checklistHtml +
            logsHtml +
            '<div class="grid grid-cols-2 gap-4 text-sm text-gray-500 pt-2 border-t">' +
                '<div><p>Dibuat: ' + (task.created_at ? task.created_at.substring(0, 16).replace('T', ' ') : '-') + '</p></div>' +
                (task.completed_at ? '<div><p>Selesai: ' + task.completed_at.substring(0, 16).replace('T', ' ') + '</p></div>' : '') +
            '</div>' +
        '</div>';
}

// ─── Toggle Checklist (AJAX) ─────────────────────────────────────────
function toggleChecklist(itemId, isChecked) {
    fetch('{{ url("housekeeping/checklist") }}/' + itemId + '/toggle', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ is_checked: isChecked }),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            Toast.success(data.message);
        }
    })
    .catch(function() {});
}

// ─── Close modals on overlay click ───────────────────────────────────
// Tutup modal saat mengklik latar belakang gelap
document.addEventListener('click', function(e) {
    // Cek apakah yang diklik adalah elemen overlay (punya class bg-black/50)
    if (e.target.classList.contains('bg-black/50')) {
        var modalIds = ['createModal', 'bulkModal', 'assignModal', 'statusModal', 'detailModal', 'roomModal'];
        for (var i = 0; i < modalIds.length; i++) {
            var m = document.getElementById(modalIds[i]);
            if (m && !m.classList.contains('hidden')) {
                m.classList.add('hidden');
                // Reset notes field untuk status modal
                if (modalIds[i] === 'statusModal') {
                    document.getElementById('notesField').classList.add('hidden');
                }
                break;
            }
        }
    }
});
</script>
@endsection
