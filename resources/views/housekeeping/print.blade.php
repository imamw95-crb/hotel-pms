<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Housekeeping - {{ $dateFrom }} s/d {{ $dateTo }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #fff;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #1f2937;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header h2 {
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }
        .header p {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }
        .print-info {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 6px;
        }

        /* Stats */
        .stats-grid {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .stat-card .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }
        .stat-card .value {
            font-size: 22px;
            font-weight: 700;
            margin-top: 2px;
        }
        .stat-total .value { color: #1f2937; }
        .stat-pending .value { color: #d97706; }
        .stat-progress .value { color: #2563eb; }
        .stat-completed .value { color: #059669; }
        .stat-cancelled .value { color: #9ca3af; }
        .stat-urgent .value { color: #dc2626; }

        /* Filter info */
        .filter-info {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 12px;
            padding: 6px 10px;
            background: #f9fafb;
            border-radius: 4px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        thead th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: left;
        }
        tbody td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) { background: #fafafa; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            border: 1px solid;
        }
        .badge-pending { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .badge-in_progress { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        .badge-completed { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .badge-cancelled { background: #f3f4f6; color: #4b5563; border-color: #d1d5db; }
        .badge-urgent { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .badge-high { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
        .badge-normal { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
        .badge-low { background: #f3f4f6; color: #4b5563; border-color: #d1d5db; }

        /* Section title */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #1f2937;
            padding-bottom: 4px;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        /* Sign-off */
        .sign-off {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .sign-box {
            text-align: center;
            width: 30%;
        }
        .sign-line {
            border-bottom: 1px solid #9ca3af;
            height: 40px;
            margin-bottom: 4px;
        }
        .sign-label {
            font-size: 10px;
            font-weight: 600;
        }
        .sign-role {
            font-size: 9px;
            color: #9ca3af;
        }

        /* Print button */
        .no-print {
            text-align: center;
            margin-bottom: 16px;
        }
        .btn-print {
            background: #059669;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-print:hover { background: #047857; }
        .btn-back {
            background: #6b7280;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 8px;
        }
        .btn-back:hover { background: #4b5563; }

        /* Print styles */
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
            .header { border-bottom-color: #000; }
            thead th { background: #e5e7eb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr:nth-child(even) { background: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stat-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- Print Button -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak Laporan
        </button>
        <button class="btn-back" onclick="window.history.back()">
            ← Kembali
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        @if($hotel->logo_path)
            <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height:40px;margin-bottom:6px;">
        @endif
        <h2>{{ $hotel->hotel_name ?? 'Hotel PMS' }}</h2>
        @if($hotel->address)<p>{{ $hotel->address }}</p>@endif
        @if($hotel->phone)<p>Telp: {{ $hotel->phone }}</p>@endif
        <h1 style="margin-top:12px;">Laporan Housekeeping</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d F Y') }} &mdash; {{ \Carbon\Carbon::parse($dateTo)->format('d F Y') }}</p>
        <p class="print-info">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="label">Total Tugas</div>
            <div class="value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card stat-pending">
            <div class="label">Menunggu</div>
            <div class="value">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-card stat-progress">
            <div class="label">Dikerjakan</div>
            <div class="value">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="stat-card stat-completed">
            <div class="label">Selesai</div>
            <div class="value">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat-card stat-cancelled">
            <div class="label">Dibatalkan</div>
            <div class="value">{{ $stats['cancelled'] }}</div>
        </div>
        <div class="stat-card stat-urgent">
            <div class="label">Urgent</div>
            <div class="value">{{ $stats['urgent'] }}</div>
        </div>
    </div>

    <!-- Filter Info -->
    <div class="filter-info">
        <strong>Filter:</strong>
        Status: {{ $statusFilter === 'all' ? 'Semua' : \App\Models\HousekeepingTask::STATUSES[$statusFilter] ?? $statusFilter }}
        | Tipe: {{ $typeFilter === 'all' ? 'Semua' : \App\Models\HousekeepingTask::TASK_TYPES[$typeFilter] ?? $typeFilter }}
        | Prioritas: {{ $priorityFilter === 'all' ? 'Semua' : \App\Models\HousekeepingTask::PRIORITIES[$priorityFilter] ?? $priorityFilter }}
        @if($roomFilter !== 'all')
            | Kamar: {{ \App\Models\Room::find($roomFilter)?->room_number ?? '-' }}
        @endif
    </div>

    <!-- Task Table -->
    <div class="section-title">Daftar Tugas Housekeeping</div>

    @if($tasks->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th style="width:8%;">Kamar</th>
                <th style="width:16%;">Tipe Tugas</th>
                <th style="width:10%;">Prioritas</th>
                <th style="width:12%;">Status</th>
                <th style="width:14%;">Ditugaskan Ke</th>
                <th style="width:18%;">Deskripsi</th>
                <th style="width:8%;">Dibuat</th>
                <th style="width:8%;">Selesai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $i => $task)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $task->room->room_number ?? '-' }}</strong>
                    <br><span style="color:#9ca3af;font-size:9px;">{{ $task->room->room_type_name ?? '' }}</span>
                </td>
                <td>
                    {{ $task->task_type_label }}
                </td>
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
                        <em style="color:#9ca3af;">Belum ditugaskan</em>
                    @endif
                </td>
                <td style="max-width:160px;">{{ $task->description ?: '-' }}</td>
                <td>{{ $task->created_at->format('d/m H:i') }}</td>
                <td>
                    @if($task->completed_at)
                        {{ $task->completed_at->format('d/m H:i') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary by Type -->
    <div class="section-title">Ringkasan per Tipe Tugas</div>
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
            @foreach(\App\Models\HousekeepingTask::TASK_TYPES as $typeKey => $typeLabel)
                @php
                    $typeTasks = $tasks->where('task_type', $typeKey);
                @endphp
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
            @endforeach
        </tbody>
    </table>

    @else
    <p style="text-align:center;color:#9ca3af;padding:30px;font-style:italic;">
        Tidak ada data tugas housekeeping untuk periode dan filter yang dipilih.
    </p>
    @endif

    <!-- Sign-off -->
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

</body>
</html>
