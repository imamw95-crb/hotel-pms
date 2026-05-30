<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Room List Report - {{ $today->format('d/m/Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @page { size: A4 landscape; margin: 10mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background: #fff; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .page-break { page-break-before: always; }
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { padding: 4px 6px; text-align: left; border: 1px solid #ccc; font-size: 9px; }
        th { background: #1e293b; color: #fff; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 8px; }
        td { vertical-align: middle; }

        .section-title {
            font-size: 12px; font-weight: bold; padding: 6px 10px;
            border-left: 4px solid #000; margin: 10px 0 6px 0;
        }
        .section-title.arrivals { border-left-color: #16a34a; }
        .section-title.departures { border-left-color: #dc2626; }
        .section-title.staying { border-left-color: #2563eb; }
        .section-title.upcoming { border-left-color: #d97706; }

        .badge {
            display: inline-block; padding: 1px 6px; border-radius: 3px;
            font-size: 7px; font-weight: bold; text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-amber { background: #fef3c7; color: #92400e; }

        .hotel-header {
            text-align: center; border-bottom: 2px solid #000;
            padding-bottom: 8px; margin-bottom: 10px;
        }
        .hotel-name { font-size: 18px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .hotel-info { font-size: 9px; color: #555; margin-top: 2px; }
        .report-title { font-size: 14px; font-weight: bold; text-align: center; margin: 8px 0; letter-spacing: 1px; text-transform: uppercase; }
        .report-date { text-align: center; font-size: 10px; color: #666; margin-bottom: 10px; }

        .summary-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 6px; margin-bottom: 12px;
        }
        .summary-card {
            border: 1px solid #ddd; border-radius: 4px; padding: 6px 10px; text-align: center;
        }
        .summary-card .num { font-size: 18px; font-weight: bold; }
        .summary-card .label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card.green { border-left: 3px solid #16a34a; }
        .summary-card.red { border-left: 3px solid #dc2626; }
        .summary-card.blue { border-left: 3px solid #2563eb; }
        .summary-card.amber { border-left: 3px solid #d97706; }

        .footer {
            text-align: center; font-size: 8px; color: #999;
            border-top: 1px solid #ddd; padding-top: 6px; margin-top: 10px;
        }
    </style>
    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>
</head>
<body>

    {{-- Action Buttons (screen only) --}}
    <div class="no-print" style="padding: 12px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('room-list.index') }}" class="text-gray-500 hover:text-gray-700 font-medium text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-print"></i> Print / Cetak
            </button>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────── --}}
    {{-- HOTEL HEADER --}}
    {{-- ──────────────────────────────────────────────── --}}
    <div class="hotel-header">
        @if($hotel->logo_path)
            <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height: 45px; margin-bottom: 4px; display: block; margin-left: auto; margin-right: auto;">
        @endif
        <div class="hotel-name">{{ $hotel->hotel_name ?? 'Hotel PMS' }}</div>
        @if($hotel->address)<div class="hotel-info">{{ $hotel->address }}</div>@endif
        <div class="hotel-info">
            @if($hotel->phone)Telp: {{ $hotel->phone }}@endif
            @if($hotel->phone && $hotel->email) | @endif
            @if($hotel->email){{ $hotel->email }}@endif
        </div>
    </div>

    {{-- REPORT TITLE --}}
    <div class="report-title">Room List Report</div>
    <div class="report-date">{{ $today->format('l, d F Y') }}</div>

    {{-- ──────────────────────────────────────────────── --}}
    {{-- SUMMARY CARDS --}}
    {{-- ──────────────────────────────────────────────── --}}
    <div class="summary-grid">
        <div class="summary-card green">
            <div class="num">{{ $checkInToday->count() }}</div>
            <div class="label">Check-in Hari Ini</div>
        </div>
        <div class="summary-card red">
            <div class="num">{{ $dueOutToday->count() }}</div>
            <div class="label">Due Out Hari Ini</div>
        </div>
        <div class="summary-card blue">
            <div class="num">{{ $currentlyStaying->count() }}</div>
            <div class="label">In House</div>
        </div>
        <div class="summary-card amber">
            <div class="num">{{ $upcoming->count() }}</div>
            <div class="label">Akan Datang</div>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────── --}}
    {{-- 1. CHECK-IN HARI INI --}}
    {{-- ──────────────────────────────────────────────── --}}
    <div class="section-title arrivals">
        <i class="fas fa-sign-in-alt"></i> Check-in Hari Ini
        <span style="font-weight:normal;font-size:9px;color:#666;margin-left:6px;">({{ $checkInToday->count() }} tamu)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Reservasi</th>
                <th>Tamu</th>
                <th>Kamar</th>
                <th>Tipe Kamar</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Malam</th>
                <th>Sarapan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($checkInToday as $i => $res)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="font-size:8px;">{{ $res->reservation_number }}</td>
                <td>
                    <strong>{{ $res->guest->guest_name ?? '-' }}</strong>
                    @if($res->guest->phone)
                        <br><span style="font-size:7px;color:#666;">{{ $res->guest->phone }}</span>
                    @endif
                </td>
                <td><strong>{{ $res->room->room_number ?? '-' }}</strong></td>
                <td>{{ $res->room->room_type_name ?? '-' }}</td>
                <td>{{ $res->check_in ? $res->check_in->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}</td>
                <td style="text-align:center;">{{ $res->nights }}</td>
                <td style="text-align:center;">
                    @if($res->include_breakfast)
                        <span class="badge badge-amber">Ya</span>
                    @else
                        <span style="color:#999;">—</span>
                    @endif
                </td>
                <td>
                    @if($res->status === 'checked_in')
                        <span class="badge badge-blue">Checked In</span>
                    @elseif($res->status === 'pending')
                        <span class="badge badge-amber">Pending</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center;padding:10px;color:#999;">Tidak ada check-in hari ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ──────────────────────────────────────────────── --}}
    {{-- 2. DUE OUT HARI INI --}}
    {{-- ──────────────────────────────────────────────── --}}
    <div class="section-title departures">
        <i class="fas fa-sign-out-alt"></i> Due Out Hari Ini
        <span style="font-weight:normal;font-size:9px;color:#666;margin-left:6px;">({{ $dueOutToday->count() }} tamu)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Reservasi</th>
                <th>Tamu</th>
                <th>Kamar</th>
                <th>Tipe Kamar</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Malam</th>
                <th>Sarapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dueOutToday as $i => $res)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="font-size:8px;">{{ $res->reservation_number }}</td>
                <td>
                    <strong>{{ $res->guest->guest_name ?? '-' }}</strong>
                    @if($res->guest->phone)
                        <br><span style="font-size:7px;color:#666;">{{ $res->guest->phone }}</span>
                    @endif
                </td>
                <td><strong>{{ $res->room->room_number ?? '-' }}</strong></td>
                <td>{{ $res->room->room_type_name ?? '-' }}</td>
                <td>{{ $res->check_in ? $res->check_in->format('d/m/Y') : '-' }}</td>
                <td>{{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}</td>
                <td style="text-align:center;">{{ $res->nights }}</td>
                <td style="text-align:center;">
                    @if($res->include_breakfast)
                        <span class="badge badge-amber">Ya</span>
                    @else
                        <span style="color:#999;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:10px;color:#999;">Tidak ada due out hari ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ──────────────────────────────────────────────── --}}
    {{-- 3. IN HOUSE (TIDAK DUE OUT HARI INI) --}}
    {{-- ──────────────────────────────────────────────── --}}
    <div class="section-title staying">
        <i class="fas fa-door-open"></i> In House (Menginap)
        <span style="font-weight:normal;font-size:9px;color:#666;margin-left:6px;">({{ $currentlyStaying->count() }} kamar)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Reservasi</th>
                <th>Tamu</th>
                <th>Kamar</th>
                <th>Tipe Kamar</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Malam</th>
                <th>Sarapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($currentlyStaying as $i => $res)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="font-size:8px;">{{ $res->reservation_number }}</td>
                <td>
                    <strong>{{ $res->guest->guest_name ?? '-' }}</strong>
                    @if($res->guest->phone)
                        <br><span style="font-size:7px;color:#666;">{{ $res->guest->phone }}</span>
                    @endif
                </td>
                <td><strong>{{ $res->room->room_number ?? '-' }}</strong></td>
                <td>{{ $res->room->room_type_name ?? '-' }}</td>
                <td>{{ $res->check_in ? $res->check_in->format('d/m/Y') : '-' }}</td>
                <td>{{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}</td>
                <td style="text-align:center;">{{ $res->nights }}</td>
                <td style="text-align:center;">
                    @if($res->include_breakfast)
                        <span class="badge badge-amber">Ya</span>
                    @else
                        <span style="color:#999;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:10px;color:#999;">Tidak ada tamu menginap</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ──────────────────────────────────────────────── --}}
    {{-- 4. AKAN DATANG --}}
    {{-- ──────────────────────────────────────────────── --}}
    <div class="section-title upcoming">
        <i class="fas fa-calendar-alt"></i> Akan Datang
        <span style="font-weight:normal;font-size:9px;color:#666;margin-left:6px;">({{ $upcoming->count() }} reservasi)</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Reservasi</th>
                <th>Tamu</th>
                <th>Kamar</th>
                <th>Tipe Kamar</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Malam</th>
                <th>Sarapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($upcoming as $i => $res)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="font-size:8px;">{{ $res->reservation_number }}</td>
                <td>
                    <strong>{{ $res->guest->guest_name ?? '-' }}</strong>
                    @if($res->guest->phone)
                        <br><span style="font-size:7px;color:#666;">{{ $res->guest->phone }}</span>
                    @endif
                </td>
                <td><strong>{{ $res->room->room_number ?? '-' }}</strong></td>
                <td>{{ $res->room->room_type_name ?? '-' }}</td>
                <td>{{ $res->check_in ? $res->check_in->format('d/m/Y') : '-' }}</td>
                <td>{{ $res->check_out ? $res->check_out->format('d/m/Y') : '-' }}</td>
                <td style="text-align:center;">{{ $res->nights }}</td>
                <td style="text-align:center;">
                    @if($res->include_breakfast)
                        <span class="badge badge-amber">Ya</span>
                    @else
                        <span style="color:#999;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:10px;color:#999;">Tidak ada reservasi mendatang</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — {{ $hotel->hotel_name ?? 'Hotel PMS' }}
    </div>

</body>
</html>
