<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Housekeeping - {{ $dateFrom }} s/d {{ $dateTo }}</title>
    @vite('resources/css/app.css')
    <link href="/hotel-pms/public/assets/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        @page { size: A4 landscape; margin: 10mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background: #fff; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { padding: 4px 6px; text-align: left; border: 1px solid #ccc; font-size: 9px; }
        th { background: #1e293b; color: #fff; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 8px; }
        td { vertical-align: middle; }

        .section-title {
            font-size: 12px; font-weight: bold; padding: 6px 10px;
            border-left: 4px solid #6366f1; margin: 10px 0 6px 0;
        }

        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 3px;
            font-size: 7px; font-weight: bold; text-transform: uppercase;
        }
        .badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .badge-in_progress { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-completed { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-cancelled { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
        .badge-urgent { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-high { background: #ffedd5; color: #9a3412; border: 1px solid #fdba74; }
        .badge-normal { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-low { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }

        .hotel-header {
            text-align: center; border-bottom: 2px solid #000;
            padding-bottom: 8px; margin-bottom: 10px;
        }
        .hotel-name { font-size: 18px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .hotel-info { font-size: 9px; color: #555; margin-top: 2px; }
        .report-title { font-size: 14px; font-weight: bold; text-align: center; margin: 8px 0; letter-spacing: 1px; text-transform: uppercase; }
        .report-date { text-align: center; font-size: 10px; color: #666; margin-bottom: 10px; }

        .summary-grid {
            display: grid; grid-template-columns: repeat(6, 1fr);
            gap: 6px; margin-bottom: 12px;
        }
        .summary-card {
            border: 1px solid #ddd; border-radius: 4px; padding: 6px 8px; text-align: center;
        }
        .summary-card .num { font-size: 18px; font-weight: bold; }
        .summary-card .label { font-size: 7px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card.total { border-left: 3px solid #1e293b; }
        .summary-card.pending { border-left: 3px solid #d97706; }
        .summary-card.progress { border-left: 3px solid #2563eb; }
        .summary-card.completed { border-left: 3px solid #059669; }
        .summary-card.cancelled { border-left: 3px solid #9ca3af; }
        .summary-card.urgent { border-left: 3px solid #dc2626; }

        .filter-info {
            font-size: 8px; color: #666; padding: 4px 8px;
            background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 3px; margin-bottom: 10px;
        }

        .footer {
            text-align: center; font-size: 8px; color: #999;
            border-top: 1px solid #ddd; padding-top: 6px; margin-top: 10px;
        }

        .sign-off {
            margin-top: 20px; display: flex; justify-content: space-around;
        }
        .sign-box { text-align: center; width: 25%; }
        .sign-line { border-bottom: 1px solid #999; height: 35px; margin-bottom: 3px; }
        .sign-label { font-size: 9px; font-weight: 600; }
        .sign-role { font-size: 8px; color: #999; }
    </style>
</head>
<body>

    {{-- Action Buttons (screen only) --}}
    <div class="no-print" style="padding: 12px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('housekeeping.index') }}" class="text-gray-500 hover:text-gray-700 font-medium text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-print"></i> Print / Cetak
            </button>
        </div>
    </div>

    {{-- Hotel Header --}}
    <div class="hotel-header">
        @if($hotel->logo_path)
            <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height:45px;margin-bottom:4px;display:block;margin-left:auto;margin-right:auto;">
        @endif
        <div class="hotel-name">{{ $hotel->hotel_name ?? 'Hotel PMS' }}</div>
        @if($hotel->address)<div class="hotel-info">{{ $hotel->address }}</div>@endif
        <div class="hotel-info">
            @if($hotel->phone)Telp: {{ $hotel->phone }}@endif
            @if($hotel->phone && $hotel->email) | @endif
            @if($hotel->email){{ $hotel->email }}@endif
        </div>
    </div>

    {{-- Report Title --}}
    <div class="report-title">Laporan Housekeeping</div>
    <div class="report-date">
        Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d F Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d F Y') }}
    </div>

    {{-- Summary Cards --}}
    <div class="summary-grid">
        <div class="summary-card total">
            <div class="num">{{ $stats['total'] }}</div>
            <div class="label">Total Tugas</div>
        </div>
        <div class="summary-card pending">
            <div class="num">{{ $stats['pending'] }}</div>
            <div class="label">Menunggu</div>
        </div>
        <div class="summary-card progress">
            <div class="num">{{ $stats['in_progress'] }}</div>
            <div class="label">Dikerjakan</div>
        </div>
        <div class="summary-card completed">
            <div class="num">{{ $stats['completed'] }}</div>
            <div class="label">Selesai</div>
        </div>
        <div class="summary-card cancelled">
            <div class="num">{{ $stats['cancelled'] }}</div>
            <div class="label">Dibatalkan</div>
        </div>
        <div class="summary-card urgent">
            <div class="num">{{ $stats['urgent'] }}</div>
            <div class="label">Urgent</div>
        </div>
    </div>

    {{-- Filter Info --}}
    <div class="filter-info">
        <strong>Filter:</strong>
        Status: {{ $statusFilter === 'all' ? 'Semua' : (\App\Models\HousekeepingTask::STATUSES[$statusFilter] ?? $statusFilter) }}
        | Tipe: {{ $typeFilter === 'all' ? 'Semua' : (\App\Models\HousekeepingTask::TASK_TYPES[$typeFilter] ?? $typeFilter) }}
        | Prioritas: {{ $priorityFilter === 'all' ? 'Semua' : (\App\Models\HousekeepingTask::PRIORITIES[$priorityFilter] ?? $priorityFilter) }}
        @if($roomFilter !== 'all')
            | Kamar: {{ \App\Models\Room::find($roomFilter)?->room_number ?? '-' }}
        @endif
    </div>

    {{-- Task Table --}}
    <div class="section-title"><i class="fas fa-tasks"></i> Daftar Tugas Housekeeping</div>
    <table>
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:7%;">Kamar</th>
                <th style="width:13%;">Tipe Tugas</th>
                <th style="width:8%;">Prioritas</th>
                <th style="width:9%;">Status</th>
                <th style="width:11%;">Ditugaskan Ke</th>
                <th>Deskripsi</th>
                <th style="width:8%;">Dibuat</th>
                <th style="width:8%;">Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $i => $task)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $task->room->room_number ?? '-' }}</strong>
                    @if($task->room->room_type_name)
                        <br><span style="font-size:7px;color:#666;">{{ $task->room->room_type_name }}</span>
                    @endif
                </td>
                <td>{{ $task->task_type_label }}</td>
                <td>
                    @php
                        $pBadge = match($task->priority) {
                            'urgent' => 'badge-urgent',
                            'high' => 'badge-high',
                            'normal' => 'badge-normal',
                            'low' => 'badge-low',
                            default => 'badge-low',
                        };
                    @endphp
                    <span class="badge {{ $pBadge }}">{{ $task->priority_label }}</span>
                </td>
                <td>
                    @php
                        $sBadge = match($task->status) {
                            'pending' => 'badge-pending',
                            'in_progress' => 'badge-in_progress',
                            'completed' => 'badge-completed',
                            'cancelled' => 'badge-cancelled',
                            default => 'badge-pending',
                        };
                    @endphp
                    <span class="badge {{ $sBadge }}">{{ $task->status_label }}</span>
                </td>
                <td>
                    @if($task->assignedTo)
                        {{ $task->assignedTo->name }}
                    @else
                        <em style="color:#999;">-</em>
                    @endif
                </td>
                <td>{{ $task->description ?: '-' }}</td>
                <td>{{ $task->created_at->format('d/m H:i') }}</td>
                <td>{{ $task->completed_at ? $task->completed_at->format('d/m H:i') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:15px;color:#999;">
                    Tidak ada data tugas housekeeping untuk periode & filter yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary by Type --}}
    <div class="section-title"><i class="fas fa-chart-pie"></i> Ringkasan per Tipe Tugas</div>
    <table>
        <thead>
            <tr>
                <th>Tipe Tugas</th>
                <th style="width:10%;text-align:center;">Total</th>
                <th style="width:10%;text-align:center;">Menunggu</th>
                <th style="width:10%;text-align:center;">Dikerjakan</th>
                <th style="width:10%;text-align:center;">Selesai</th>
                <th style="width:10%;text-align:center;">Dibatalkan</th>
            </tr>
        </thead>
        <tbody>
            @forelse(\App\Models\HousekeepingTask::TASK_TYPES as $typeKey => $typeLabel)
                @php $typeTasks = $tasks->where('task_type', $typeKey); @endphp
                @if($typeTasks->count() > 0)
                <tr>
                    <td><strong>{{ $typeLabel }}</strong></td>
                    <td style="text-align:center;">{{ $typeTasks->count() }}</td>
                    <td style="text-align:center;">{{ $typeTasks->where('status', 'pending')->count() }}</td>
                    <td style="text-align:center;">{{ $typeTasks->where('status', 'in_progress')->count() }}</td>
                    <td style="text-align:center;">{{ $typeTasks->where('status', 'completed')->count() }}</td>
                    <td style="text-align:center;">{{ $typeTasks->where('status', 'cancelled')->count() }}</td>
                </tr>
                @endif
            @empty
                <tr><td colspan="6" style="text-align:center;color:#999;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Sign-off --}}
    <div class="sign-off">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="sign-label">Dibuat Oleh</div>
            <div class="sign-role">Housekeeping Staff</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="sign-label">Diperiksa Oleh</div>
            <div class="sign-role">Supervisor</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="sign-label">Disetujui Oleh</div>
            <div class="sign-role">Manager</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Dicetak pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} — {{ $hotel->hotel_name ?? 'Hotel PMS' }}
    </div>

</body>
</html>
