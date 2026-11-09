@extends('layouts.app')

@section('title', 'Tutup Buku')
@section('header', 'Akunting — Tutup Buku Periode')

@section('content')
@include('accounting.partials.nav')

<div class="bg-white rounded shadow p-5 mb-6 text-sm text-gray-600">
    <h3 class="text-base font-bold text-gray-800 mb-2">Tutup buku bulanan</h3>
    <p>
        Setelah periode ditutup, jurnal pada bulan tersebut tidak dapat dibuat, diubah, maupun dibatalkan.
        Tutup periode hanya setelah laporan bulan tersebut final (night audit selesai dan rekonsiliasi pembayaran OTA bersih).
    </p>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-4 py-2 text-left">Periode</th>
                    <th class="px-4 py-2 text-right">Jumlah Jurnal</th>
                    <th class="px-4 py-2 text-right">Total Nilai</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-left">Ditutup</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ $row->label }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $row->period }}</div>
                        </td>
                        <td class="px-4 py-2 text-right">{{ number_format($row->entries, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($row->status === 'closed')
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs">Ditutup</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs">Terbuka</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-500">{{ $row->closed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                            @if(hasPermission('close_accounting_period'))
                                @if($row->status === 'closed')
                                    <form method="POST" action="{{ route('accounting.periods.reopen', $row->period) }}" class="inline"
                                          onsubmit="return confirm('Buka kembali periode {{ $row->period }}?')">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline">Buka Kembali</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('accounting.periods.close', $row->period) }}" class="inline"
                                          onsubmit="return confirm('Tutup periode {{ $row->period }}? Jurnal bulan ini tidak bisa diubah lagi.')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:underline">Tutup Buku</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
