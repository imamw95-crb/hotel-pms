{{-- Deposit Index Modal Content — no layout, pure HTML for AJAX modal --}}
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold"><i class="fas fa-credit-card text-blue-500 mr-2"></i>Daftar Deposit Kartu</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola deposit kartu tamu. Nominal default Rp 100.000 per kartu.</p>
        </div>
        <a href="javascript:void(0)" onclick="Deposit.openCreateModal()"
           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2 text-sm">
            <i class="fas fa-plus"></i> Tambah Deposit
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-gray-50 rounded-lg p-4 mb-6 border">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Dari Tanggal</label>
                <input type="date" id="filterDateFrom" value="{{ $dateFrom ?? '' }}"
                       class="border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Sampai Tanggal</label>
                <input type="date" id="filterDateTo" value="{{ $dateTo ?? '' }}"
                       class="border rounded px-3 py-2 text-sm">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-gray-700 text-sm font-bold mb-1">Cari</label>
                <input type="text" id="filterSearch" value="{{ $search ?? '' }}"
                       placeholder="No. receipt atau nama tamu..."
                       class="border rounded px-3 py-2 text-sm w-full">
            </div>
            <button onclick="Deposit.applyFilters()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="javascript:void(0)" onclick="Deposit.resetFilters()" class="text-gray-500 hover:text-gray-700 text-sm px-3 py-2">
                <i class="fas fa-times mr-1"></i> Reset
            </a>
        </div>
    </div>

    {{-- Table Container --}}
    <div id="depositTableContainer">
        @include('deposits.partials.table', ['deposits' => $deposits])
    </div>
</div>

<script>
    // Override URLs in case they changed
    window._depositIndexUrl = '{{ route('deposits.index') }}';
    window._depositCreateUrl = '{{ route('deposits.create') }}';
    window._depositShowUrlTemplate = '{{ route('deposits.show', '__ID__') }}';
    window._depositReturnUrlTemplate = '{{ route('deposits.return', '__ID__') }}';
</script>
