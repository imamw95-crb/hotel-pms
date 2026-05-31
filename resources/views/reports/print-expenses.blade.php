<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Pengeluaran</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', 'Georgia', 'Serif';
            color: #000;
            padding: 30px;
            background: #fff;
            font-size: 12px;
            line-height: 1.5;
        }
        .print-container {
            max-width: 800px;
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
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }

        /* ── RINGKASAN ── */
        .ringkasan-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .ringkasan-table td {
            padding: 3px 8px;
            font-size: 12px;
            vertical-align: top;
        }
        .ringkasan-table td.label { width: 55%; font-weight: 600; }
        .ringkasan-table td.isi {
            width: 45%;
            text-align: right;
            font-weight: 700;
        }
        .ringkasan-table tr.sep td {
            border-top: 1px solid #999;
            padding-top: 4px;
        }

        /* ── BREAKDOWN ── */
        .breakdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }
        .breakdown-box {
            border: 1px solid #ccc;
            padding: 10px 12px;
        }
        .breakdown-box .box-title {
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
        }
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 11px;
        }
        .breakdown-row .label { color: #333; }
        .breakdown-row .amount { font-weight: 700; }
        .breakdown-row.total-row {
            border-top: 1px solid #999;
            margin-top: 3px;
            padding-top: 4px;
            font-size: 12px;
        }

        /* ── MAIN TABLE ── */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 14px;
        }
        table.main-table thead th {
            background: #e5e7eb;
            color: #000;
            padding: 5px 6px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #999;
        }
        table.main-table thead th.right { text-align: right; }
        table.main-table tbody td {
            padding: 4px 6px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        table.main-table tbody td.right { text-align: right; }
        table.main-table tfoot td {
            padding: 5px 6px;
            font-weight: 700;
            background: #f3f4f6;
            border: 1px solid #999;
            font-size: 11px;
        }
        table.main-table tfoot td.right { text-align: right; }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }

        /* ── FOOTER ── */
        .report-footer {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            margin-top: 10px;
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
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button onclick="window.print()"><i class="fas fa-print"></i> Cetak / Print</button>
        <a href="{{ route('reports.expenses', request()->query()) }}" class="back-link">← Kembali ke Laporan</a>
    </div>

    <div class="print-container">

        {{-- HEADER --}}
        <div class="report-header">
            <div class="header-top">
                @if($hotel->logo_path)
                <div class="header-logo">
                    <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo {{ $hotel->hotel_name }}">
                </div>
                @endif
                <div class="header-text">
                    <div class="hotel-name">{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</div>
                    <div class="report-title">LAPORAN PENGELUARAN</div>
                    <div class="report-period">
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </div>
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

        {{-- RINGKASAN --}}
        <div class="section-title">Ringkasan</div>
        <table class="ringkasan-table">
            <tr>
                <td class="label">Total Pengeluaran</td>
                <td class="isi">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Transaksi</td>
                <td class="isi">{{ $expenses->count() }} transaksi</td>
            </tr>
            <tr>
                <td class="label">Rata-rata Pengeluaran</td>
                <td class="isi">Rp {{ number_format($expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Pengeluaran Tertinggi</td>
                <td class="isi">Rp {{ number_format($expenses->max('amount') ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- BREAKDOWN PER METODE --}}
        @if($byMethod->count() > 0)
        <div class="section-title">Pengeluaran per Metode Pembayaran</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th>Metode Pembayaran</th>
                    <th class="right">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byMethod as $method => $amount)
                <tr>
                    <td>{{ str_replace('_', ' ', $method) }}</td>
                    <td class="right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        {{-- DETAIL TRANSAKSI --}}
        <div class="section-title">Detail Pengeluaran</div>
        <table class="main-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Metode</th>
                    <th class="right">Jumlah (Rp)</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $i => $expense)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td>
                            {{ $expense->description }}
                            @if($expense->notes)
                                <br><span style="font-size:10px;color:#666;font-style:italic;">{{ $expense->notes }}</span>
                            @endif
                        </td>
                        <td>{{ str_replace('_', ' ', $expense->payment_method) }}</td>
                        <td class="right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                        <td>{{ $expense->createdBy?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Tidak ada pengeluaran pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">TOTAL</td>
                    <td class="right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- TANDA TANGAN --}}
        <table style="width:100%;margin-top:24px;border-collapse:collapse;font-size:11px;">
            <tr>
                <td style="width:33%;text-align:center;">
                    <div style="margin-bottom:50px;">Dibuat Oleh,</div>
                    <div style="font-weight:700;">{{ auth()->user()->name ?? '-' }}</div>
                    <div style="font-size:10px;color:#666;">{{ now()->format('d/m/Y H:i') }}</div>
                </td>
                <td style="width:33%;"></td>
                <td style="width:33%;text-align:center;">
                    <div style="margin-bottom:50px;">Mengetahui,</div>
                    <div style="font-weight:700;">&nbsp;</div>
                    <div style="font-size:10px;color:#666;">&nbsp;</div>
                </td>
            </tr>
        </table>

        {{-- FOOTER --}}
        <div class="report-footer">
            <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
            <span>{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</span>
        </div>
    </div>

    {{-- Auto-print --}}
    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>

</body>
</html>
