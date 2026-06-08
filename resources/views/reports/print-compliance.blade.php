<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Bulanan Hotel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', 'Georgia', 'Serif';
            color: #000;
            padding: 30px;
            background: #fff;
            font-size: 11px;
            line-height: 1.5;
        }
        .print-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 30px;
        }

        /* ── HEADER ── */
        .report-header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px double #000;
            margin-bottom: 18px;
        }
        .report-header .header-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }
        .report-header .header-logo { flex-shrink: 0; }
        .report-header .header-logo img {
            height: 50px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        .report-header .header-text { text-align: center; }
        .report-header .hotel-name {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .report-header .report-title {
            font-size: 14px;
            font-weight: 700;
            margin-top: 2px;
        }
        .report-header .report-period {
            font-size: 11px;
            color: #444;
            margin-top: 2px;
        }
        .report-header .report-contact {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }

        /* ── SECTION TITLE ── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-align: left;
            margin-bottom: 8px;
            margin-top: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        .section-title:first-child { margin-top: 0; }

        /* ── RINGKASAN CARDS ── */
        .ringkasan-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        .ringkasan-card {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: center;
        }
        .ringkasan-card .card-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
        }
        .ringkasan-card .card-value {
            font-size: 14px;
            font-weight: 700;
            margin-top: 2px;
        }
        .ringkasan-card .card-sub {
            font-size: 8px;
            color: #777;
            margin-top: 1px;
        }

        /* ── TABLES ── */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 10px;
        }
        table.main-table thead th {
            background: #e5e7eb;
            color: #000;
            padding: 4px 6px;
            text-align: left;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #999;
        }
        table.main-table thead th.right { text-align: right; }
        table.main-table tbody td {
            padding: 3px 6px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        table.main-table tbody td.right { text-align: right; }
        table.main-table tbody td.center { text-align: center; }
        table.main-table tfoot td {
            padding: 4px 6px;
            font-weight: 700;
            background: #f3f4f6;
            border: 1px solid #999;
            font-size: 10px;
        }
        table.main-table tfoot td.right { text-align: right; }

        /* ── BREAKDOWN BOX ── */
        .breakdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 10px;
        }
        .breakdown-box {
            border: 1px solid #ccc;
            padding: 8px 10px;
        }
        .breakdown-box .box-title {
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ccc;
        }
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 10px;
        }
        .breakdown-row .label { color: #333; }
        .breakdown-row .amount { font-weight: 700; }
        .breakdown-row.total-row {
            border-top: 1px solid #999;
            margin-top: 3px;
            padding-top: 4px;
            font-size: 11px;
        }

        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            margin: 3px 0 6px;
            overflow: hidden;
        }
        .progress-bar .fill {
            height: 100%;
            border-radius: 4px;
        }
        .progress-bar .fill.green { background: #22c55e; }
        .progress-bar .fill.yellow { background: #eab308; }
        .progress-bar .fill.red { background: #ef4444; }
        .progress-bar .fill.blue { background: #3b82f6; }

        .flex-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .flex-row .label { font-size: 10px; }
        .flex-row .value { font-weight: 700; font-size: 10px; }
        .gap { margin-bottom: 6px; }

        .indicator-good { color: #16a34a; }
        .indicator-warn { color: #ca8a04; }
        .indicator-bad { color: #dc2626; }

        .empty-state {
            text-align: center;
            padding: 14px;
            color: #999;
            font-style: italic;
            font-size: 10px;
        }

        /* ── FOOTER ── */
        .report-footer {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            margin-top: 10px;
        }
        .report-footer .signatures {
            display: flex;
            justify-content: space-around;
            width: 100%;
            text-align: center;
        }
        .report-footer .signatures .sig-box {
            width: 30%;
        }
        .report-footer .signatures .sig-line {
            margin-top: 40px;
            padding-top: 2px;
            border-top: 1px solid #000;
            font-size: 10px;
            font-weight: 600;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            text-align: center;
            margin-bottom: 14px;
        }
        .toolbar button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 24px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
        }
        .toolbar button:hover { background: #1d4ed8; }
        .toolbar .back-link {
            margin-left: 10px;
            color: #555;
            font-size: 13px;
        }
        .toolbar .back-link:hover { color: #000; }

        @media print {
            body { background: #fff; padding: 0; }
            .print-container { max-width: 100%; padding: 15px 20px; }
            .toolbar { display: none !important; }
            table.main-table thead th { background: #e5e7eb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.main-table tfoot td { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 12mm; }
            .ringkasan-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button onclick="window.print()"><i class="fas fa-print"></i> Cetak / Print</button>
        <a href="{{ route('reports.compliance', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="back-link">← Kembali ke Laporan</a>
    </div>

    <div class="print-container">

        {{-- ═══════════ HEADER ═══════════ --}}
        <div class="report-header">
            <div class="header-top">
                @if($hotel->logo_path)
                <div class="header-logo">
                    <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo">
                </div>
                @endif
                <div class="header-text">
                    <div class="hotel-name">{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</div>
                    <div class="report-title">LAPORAN BULANAN HOTEL</div>
                    <div class="report-period">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
                    @if($hotel->address || $hotel->phone || $hotel->email)
                    <div class="report-contact">
                        @if($hotel->address){{ $hotel->address }}@endif
                        @if($hotel->phone) | {{ $hotel->phone }}@endif
                        @if($hotel->email) | {{ $hotel->email }}@endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══════════ A. RINGKASAN PENDAPATAN ═══════════ --}}
        <div class="section-title">A. Ringkasan Pendapatan</div>

        <div class="ringkasan-grid">
            <div class="ringkasan-card" style="background:#eff6ff;">
                <div class="card-label">Pendapatan Kamar</div>
                <div class="card-value">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</div>
                <div class="card-sub">Bln lalu: Rp {{ number_format($roomRevenuePrev, 0, ',', '.') }}</div>
            </div>
            <div class="ringkasan-card" style="background:#f0fdf4;">
                <div class="card-label">Pendapatan Resto</div>
                <div class="card-value">Rp {{ number_format($restoRevenue, 0, ',', '.') }}</div>
                <div class="card-sub">Bln lalu: Rp {{ number_format($restoRevenuePrev, 0, ',', '.') }}</div>
            </div>
            <div class="ringkasan-card" style="background:#faf5ff;">
                <div class="card-label">Other Revenue</div>
                <div class="card-value">Rp {{ number_format($scRevenue, 0, ',', '.') }}</div>
                <div class="card-sub">Bln lalu: Rp {{ number_format($scRevenuePrev, 0, ',', '.') }}</div>
            </div>
            <div class="ringkasan-card" style="background:#fffbeb;">
                <div class="card-label">Pengeluaran</div>
                <div class="card-value">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
                <div class="card-sub">Bln lalu: Rp {{ number_format($totalExpensesPrev, 0, ',', '.') }}</div>
            </div>
            <div class="ringkasan-card" style="background:#ecfdf5;">
                <div class="card-label">Pendapatan Bersih</div>
                <div class="card-value">Rp {{ number_format($netRevenue, 0, ',', '.') }}</div>
                <div class="card-sub">Growth: {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%</div>
            </div>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th class="right">Bulan Ini (Rp)</th>
                    <th class="right">Bulan Lalu (Rp)</th>
                    <th class="right">Perubahan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pendapatan Kamar</td>
                    <td class="right">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($roomRevenuePrev, 0, ',', '.') }}</td>
                    <td class="right">{{ $roomRevenuePrev > 0 ? round((($roomRevenue - $roomRevenuePrev) / $roomRevenuePrev) * 100, 1) : '-' }}%</td>
                </tr>
                <tr>
                    <td>Pendapatan Resto/F&B</td>
                    <td class="right">Rp {{ number_format($restoRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($restoRevenuePrev, 0, ',', '.') }}</td>
                    <td class="right">{{ $restoRevenuePrev > 0 ? round((($restoRevenue - $restoRevenuePrev) / $restoRevenuePrev) * 100, 1) : '-' }}%</td>
                </tr>
                <tr>
                    <td>Other Revenue</td>
                    <td class="right">Rp {{ number_format($scRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($scRevenuePrev, 0, ',', '.') }}</td>
                    <td class="right">{{ $scRevenuePrev > 0 ? round((($scRevenue - $scRevenuePrev) / $scRevenuePrev) * 100, 1) : '-' }}%</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Pendapatan</td>
                    <td class="right">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($grandRevenuePrev, 0, ',', '.') }}</td>
                    <td class="right">{{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%</td>
                </tr>
                <tr>
                    <td>Total Pengeluaran</td>
                    <td class="right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($totalExpensesPrev, 0, ',', '.') }}</td>
                    <td class="right">{{ $expenseGrowth >= 0 ? '+' : '' }}{{ $expenseGrowth }}%</td>
                </tr>
                <tr>
                    <td>Pendapatan Bersih</td>
                    <td class="right">Rp {{ number_format($netRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($grandRevenuePrev - $totalExpensesPrev, 0, ',', '.') }}</td>
                    <td class="right"></td>
                </tr>
            </tfoot>
        </table>

        {{-- ═══════════ B. OKUPANSI ═══════════ --}}
        <div class="section-title">B. Okupansi &amp; Statistik Kamar</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th class="right">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Kamar</td>
                    <td class="right">{{ $totalRooms }}</td>
                </tr>
                <tr>
                    <td>Rata-rata Okupansi</td>
                    <td class="right">{{ $avgOccupancy }}%</td>
                </tr>
            </tbody>
        </table>

        <div class="flex-row gap">
            <span class="label">Tingkat Okupansi</span>
            <span class="value">{{ $avgOccupancy }}%</span>
        </div>
        <div class="progress-bar">
            <div class="fill {{ $avgOccupancy >= 60 ? 'green' : ($avgOccupancy >= 30 ? 'yellow' : 'red') }}" style="width: {{ min($avgOccupancy, 100) }}%"></div>
        </div>

        {{-- ═══════════ C. RESERVASI ═══════════ --}}
        <div class="section-title">C. Data Reservasi</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th class="right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Total Reservasi</td><td class="right">{{ $totalReservations }}</td></tr>
                <tr><td>Check-in</td><td class="right">{{ $checkins }}</td></tr>
                <tr><td>Check-out</td><td class="right">{{ $checkouts }}</td></tr>
                <tr><td>Dibatalkan</td><td class="right">{{ $cancelled }}</td></tr>
                <tr><td>Booking OTA</td><td class="right">{{ $otaBookings }}</td></tr>
            </tbody>
        </table>

        @if($otaBookings > 0)
        <table class="main-table" style="margin-top:-6px;margin-bottom:10px;">
            <thead>
                <tr>
                    <th>Sumber OTA</th>
                    <th class="right">Jumlah</th>
                    <th class="right">Pendapatan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($otaBySource as $ota)
                <tr>
                    <td class="capitalize">{{ $ota->ota_source }}</td>
                    <td class="right">{{ $ota->total_bookings }}</td>
                    <td class="right">Rp {{ number_format($ota->total_revenue, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total OTA</td>
                    <td class="right">{{ $otaBookings }}</td>
                    <td class="right">Rp {{ number_format($otaRevenue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        {{-- ═══════════ D. CASH vs TRANSFER ═══════════ --}}
        <div class="section-title">D. Ringkasan Cash vs Transfer</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th>Metode</th>
                    <th class="right">Kamar (Rp)</th>
                    <th class="right">Resto (Rp)</th>
                    <th class="right">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Cash</strong></td>
                    <td class="right">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($cashResto, 0, ',', '.') }}</td>
                    <td class="right"><strong>Rp {{ number_format($grandCash, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Transfer BCA</strong></td>
                    <td class="right">Rp {{ number_format($transferRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($transferResto, 0, ',', '.') }}</td>
                    <td class="right"><strong>Rp {{ number_format($grandTransfer, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td>Lainnya</td>
                    <td class="right">Rp {{ number_format($otherRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($otherResto, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($grandOther, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="right">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($restoRevenue, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- ═══════════ E. METODE PEMBAYARAN ═══════════ --}}
        <div class="section-title">E. Pendapatan per Metode Pembayaran (Detail)</div>

        <div class="breakdown-grid">
            <div class="breakdown-box">
                <div class="box-title">Pendapatan Kamar</div>
                @forelse($revenueByMethod as $method => $amount)
                    <div class="breakdown-row">
                        <span class="label">{{ str_replace('_', ' ', $method) }}</span>
                        <span class="amount">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="empty-state">Tidak ada data</div>
                @endforelse
                @if($revenueByMethod->count() > 0)
                <div class="breakdown-row total-row">
                    <span class="label">Total Kamar</span>
                    <span class="amount">Rp {{ number_format($roomRevenue, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
            <div class="breakdown-box">
                <div class="box-title">Pendapatan Resto</div>
                @forelse($restoByMethod as $method => $amount)
                    <div class="breakdown-row">
                        <span class="label">{{ str_replace('_', ' ', $method) }}</span>
                        <span class="amount">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="empty-state">Tidak ada data</div>
                @endforelse
                @if($restoByMethod->count() > 0)
                <div class="breakdown-row total-row">
                    <span class="label">Total Resto</span>
                    <span class="amount">Rp {{ number_format($restoRevenue, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════ F. PENGELUARAN ═══════════ --}}
        <div class="section-title">F. Pengeluaran per Kategori</div>
        @if($expensesByDesc->count() > 0)
        <table class="main-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th class="right">Jumlah (Rp)</th>
                    <th class="right">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByDesc as $exp)
                <tr>
                    <td>{{ $exp->description }}</td>
                    <td class="right">Rp {{ number_format($exp->total, 0, ',', '.') }}</td>
                    <td class="right">{{ $totalExpenses > 0 ? round(($exp->total / $totalExpenses) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL</td>
                    <td class="right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    <td class="right">100%</td>
                </tr>
            </tfoot>
        </table>
        @else
        <div class="empty-state">Tidak ada pengeluaran pada bulan ini</div>
        @endif

        {{-- ═══════════ G. PAJAK ═══════════ --}}
        <div class="section-title">G. Estimasi Pajak (PPN 11%)</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th class="right">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Total Pendapatan (sebelum PPN)</td><td class="right">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</td></tr>
                <tr><td>Dasar Pengenaan Pajak (DPP)</td><td class="right">Rp {{ number_format(round($grandRevenue / 1.11, 2), 0, ',', '.') }}</td></tr>
                <tr><td>Estimasi PPN 11%</td><td class="right">Rp {{ number_format($ppnEstimate, 0, ',', '.') }}</td></tr>
                <tr><td>Pendapatan Setelah PPN</td><td class="right">Rp {{ number_format($grandRevenue - $ppnEstimate, 0, ',', '.') }}</td></tr>
            </tbody>
        </table>

        {{-- ═══════════ H. KEPATUHAN TAMU ═══════════ --}}
        <div class="section-title">H. Kepatuhan Data Tamu</div>
        <div class="flex-row gap">
            <span class="label">Tamu dengan ID Card</span>
            <span class="value">{{ $guestsWithId }} / {{ $totalGuests }} ({{ $guestCompliancePct }}%)</span>
        </div>
        <div class="progress-bar">
            <div class="fill {{ $guestCompliancePct >= 90 ? 'green' : ($guestCompliancePct >= 70 ? 'yellow' : 'red') }}" style="width: {{ $guestCompliancePct }}%"></div>
        </div>

        {{-- ═══════════ FOOTER / TANDA TANGAN ═══════════ --}}
        <div class="report-footer">
            <div class="signatures">
                <div class="sig-box">
                    <div>Mengetahui,</div>
                    <div class="sig-line">{{ $hotel->city ?? '' }}, {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
                </div>
                <div class="sig-box">
                    <div>General Manager</div>
                    <div class="sig-line">&nbsp;</div>
                </div>
                <div class="sig-box">
                    <div>Front Office Manager</div>
                    <div class="sig-line">&nbsp;</div>
                </div>
            </div>
        </div>

        <div class="report-footer" style="margin-top:6px;">
            <span>Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
            <span>Laporan Bulanan Hotel — {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

    </div>

    <script>
        // Auto trigger print dialog saat halaman selesai loading
        window.onload = function() {
            // Tunggu sebentar agar konten termuat sempurna, lalu print
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>

</body>
</html>