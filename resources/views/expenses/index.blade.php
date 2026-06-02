@extends('layouts.app')

@section('title', 'Pengeluaran')
@section('header', 'Pengeluaran (Expenses)')

@section('content')
<div class="mb-6">
    <div class="flex flex-wrap items-end gap-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
        </form>
        <a href="{{ route('expenses.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            <i class="fas fa-plus mr-1"></i> Tambah Pengeluaran
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Pengeluaran</div>
        <div class="text-3xl font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Jumlah Transaksi</div>
        <div class="text-3xl font-bold">{{ $expenses->count() }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Rata-rata per Transaksi</div>
        <div class="text-3xl font-bold">Rp {{ number_format($expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Per Payment Method --}}
@if($byMethod->count() > 0)
<div class="bg-white p-5 rounded shadow mb-6">
    <h3 class="text-lg font-bold mb-4">Pengeluaran per Metode Pembayaran</h3>
    <div class="space-y-2">
        @foreach($byMethod as $method => $amount)
            <div class="flex justify-between">
                <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                <span class="font-bold text-red-600">Rp {{ number_format($amount, 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Table --}}
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 text-left">No. Expense</th>
                <th class="px-4 py-2 text-left">Tanggal</th>
                <th class="px-4 py-2 text-left">Deskripsi</th>
                <th class="px-4 py-2 text-left">Metode</th>
                <th class="px-4 py-2 text-right">Jumlah (Rp)</th>
                <th class="px-4 py-2 text-left">Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium">{{ $expense->expense_number }}</td>
                    <td class="px-4 py-2">{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">
                        {{ $expense->description }}
                        @if($expense->notes)
                            <br><span class="text-xs text-gray-400 italic">{{ $expense->notes }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $expense->payment_method) }}</td>
                    <td class="px-4 py-2 text-right font-bold text-red-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-2">{{ $expense->createdBy?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">Tidak ada pengeluaran pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr class="bg-red-50 font-bold">
                <td colspan="4" class="px-4 py-3 text-right text-red-800">TOTAL</td>
                <td class="px-4 py-3 text-right text-red-700">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection
