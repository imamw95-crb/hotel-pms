@extends('layouts.app')

@section('title', 'Guest List')
@section('header', 'Guest List')

@section('content')
<!-- Filter & Print (hidden on print) -->
<div class="bg-white rounded-lg shadow p-4 mb-6 no-print">
    <form method="GET">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="checked_in" {{ $status === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ $status === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Pencarian</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / No. Kamar / No. Reservasi" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
                <a href="{{ route('reports.guest-list.export', request()->query()) }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
                <button type="button" onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Print Header -->
<div class="hidden print:block mb-6">
    <h1 class="text-2xl font-bold text-center">GUEST LIST REPORT</h1>
    <p class="text-center text-gray-600">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    <p class="text-center text-gray-600">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <p class="text-center text-gray-600">Total Tamu: {{ $guests->total() }}</p>
    <hr class="my-4">
</div>

<!-- Tabel Guest List -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left p-3 font-semibold">No. Reservasi</th>
                    <th class="text-left p-3 font-semibold">Nama Tamu</th>
                    <th class="text-left p-3 font-semibold">No. Identitas</th>
                    <th class="text-left p-3 font-semibold">Telepon</th>
                    <th class="text-left p-3 font-semibold">Kamar</th>
                    <th class="text-left p-3 font-semibold">Check-in</th>
                    <th class="text-left p-3 font-semibold">Check-out</th>
                    <th class="text-left p-3 font-semibold">Total</th>
                    <th class="text-center p-3 font-semibold">Sarapan</th>
                    <th class="text-left p-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $res)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium text-blue-600">{{ $res->reservation_number }}</td>
                    <td class="p-3 font-medium">{{ $res->guest->guest_name ?? '-' }}</td>
                    <td class="p-3">{{ $res->guest->id_number ?? '-' }}</td>
                    <td class="p-3">{{ $res->guest->phone ?? '-' }}</td>
                    <td class="p-3 font-bold">{{ $res->room->room_number ?? '-' }}</td>
                    <td class="p-3">{{ $res->check_in->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $res->check_out->format('d/m/Y') }}</td>
                    <td class="p-3 font-medium">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</td>
                    <td class="p-3 text-center">
                        @if($res->include_breakfast)
                            <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs font-semibold">
                                <i class="fas fa-coffee"></i> Ya
                            </span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-bold
                            @if($res->status === 'pending') bg-indigo-100 text-indigo-800
                            @elseif($res->status === 'checked_in') bg-green-100 text-green-800
                            @elseif($res->status === 'checked_out') bg-blue-100 text-blue-800
                            @elseif($res->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ strtoupper($res->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Tidak ada data tamu ditemukan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($guests->hasPages())
    <div class="p-4 border-t no-print">
        {{ $guests->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Print Footer -->
<div class="hidden print:block mt-6">
    <hr class="my-4">
    <div class="flex justify-between text-sm text-gray-500">
        <span>Dynamic PMS V.2 - Guest List Report</span>
        <span>Halaman 1</span>
    </div>
</div>

<style>
    @media print {
        .no-print,
        aside,
        nav,
        header,
        .sidebar-item,
        .bg-blue-800,
        .bg-white.shadow-sm,
        form,
        button,
        nav.pagination,
        .pagination { display: none !important; }

        body {
            background: white !important;
            margin: 0 !important;
            padding: 10px !important;
            font-size: 11px !important;
        }

        .flex.h-screen,
        .flex-1,
        .overflow-y-auto,
        .container.mx-auto {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        .print\:block { display: block !important; }
        .shadow { box-shadow: none !important; }
        .rounded-lg { border: 1px solid #ccc !important; border-radius: 0 !important; }
        table { font-size: 10px !important; width: 100% !important; border-collapse: collapse !important; }
        th, td { padding: 3px 5px !important; border: 1px solid #999 !important; }
        th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h1 { font-size: 16px !important; }
        .text-3xl { font-size: 16px !important; }
        .text-sm { font-size: 9px !important; }
        .text-xs { font-size: 8px !important; }
        .bg-indigo-100 { background: #e0e7ff !important; -webkit-print-color-adjust: exact; }
        .bg-green-100 { background: #dcfce7 !important; -webkit-print-color-adjust: exact; }
        .bg-blue-100 { background: #dbeafe !important; -webkit-print-color-adjust: exact; }
        .bg-red-100 { background: #fee2e2 !important; -webkit-print-color-adjust: exact; }
        .text-indigo-800 { color: #3730a3 !important; }
        .text-green-800 { color: #166534 !important; }
        .text-blue-800 { color: #1e40af !important; }
        .text-red-800 { color: #991b1b !important; }
    }
</style>
@endsection
