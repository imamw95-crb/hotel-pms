@extends('layouts.app')

@section('title', 'Daftar Deposit')
@section('header', 'Daftar Deposit Kartu')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-500">Kelola deposit kartu tamu. Nominal default Rp 100.000 per kartu.</p>
        </div>
        <a href="{{ route('deposits.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Deposit
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="border rounded px-3 py-2 text-sm">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-gray-700 text-sm font-bold mb-1">Cari</label>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="No. receipt atau nama tamu..."
                       class="border rounded px-3 py-2 text-sm w-full">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            @if($dateFrom || $dateTo || $search)
                <a href="{{ route('deposits.index') }}" class="text-gray-500 hover:text-gray-700 text-sm px-3 py-2">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="text-left p-3 font-semibold">No. Tanda Terima</th>
                    <th class="text-left p-3 font-semibold">Tanggal</th>
                    <th class="text-left p-3 font-semibold">Tamu</th>
                    <th class="text-center p-3 font-semibold">Jumlah Kartu</th>
                    <th class="text-right p-3 font-semibold">Total</th>
                    <th class="text-center p-3 font-semibold">Metode</th>
                    <th class="text-center p-3 font-semibold w-24">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="p-3 font-mono font-bold text-blue-600">{{ $deposit->receipt_number }}</td>
                    <td class="p-3 text-gray-600">{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3">
                        <div class="font-medium">{{ $deposit->guest->guest_name ?? '-' }}</div>
                        @if($deposit->reservation)
                            <div class="text-xs text-gray-400">{{ $deposit->reservation->reservation_number }}</div>
                        @endif
                    </td>
                    <td class="p-3 text-center">{{ $deposit->number_of_cards }}</td>
                    <td class="p-3 text-right font-bold">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                    <td class="p-3 text-center">
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">
                            {{ ucwords(str_replace('_', ' ', $deposit->payment_method)) }}
                        </span>
                    </td>
                    <td class="p-3 text-center">
                        <a href="{{ route('deposits.show', $deposit) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                           title="Lihat / Print">
                            <i class="fas fa-eye mr-1"></i> Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-400">
                        <i class="fas fa-inbox text-3xl mb-2 block"></i>
                        Belum ada data deposit.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($deposits->hasPages())
    <div class="mt-4">
        {{ $deposits->withQueryString()->links() }}
    </div>
    @endif

</div>
@endsection
