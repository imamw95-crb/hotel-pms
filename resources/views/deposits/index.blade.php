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
        <a href="javascript:void(0)" onclick="Deposit.openCreateModal()"
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

    {{-- Table Container --}}
    <div id="depositTableContainer">
        @include('deposits.partials.table', ['deposits' => $deposits])
    </div>

</div>

{{-- Scripts for AJAX actions --}}
<script>
    window._depositCreateUrl = '{{ route('deposits.create') }}';
    window._depositShowUrlTemplate = '{{ route('deposits.show', '__ID__') }}';
    window._depositReturnUrlTemplate = '{{ route('deposits.return', '__ID__') }}';
    window._depositIndexUrl = '{{ route('deposits.index') }}';
</script>
@endsection
