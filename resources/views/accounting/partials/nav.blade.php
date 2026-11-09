@php
    $tabs = [
        ['label' => 'Dashboard', 'route' => 'accounting.dashboard', 'icon' => 'gauge-high', 'permission' => 'view_accounting'],
        ['label' => 'Jurnal Umum', 'route' => 'accounting.journals.index', 'icon' => 'book', 'permission' => 'view_accounting'],
        ['label' => 'Buku Besar', 'route' => 'accounting.ledger', 'icon' => 'list', 'permission' => 'view_accounting'],
        ['label' => 'Neraca Saldo', 'route' => 'accounting.reports.trial-balance', 'icon' => 'scale-balanced', 'permission' => 'view_accounting'],
        ['label' => 'Laba Rugi', 'route' => 'accounting.reports.income-statement', 'icon' => 'chart-line', 'permission' => 'view_accounting'],
        ['label' => 'Neraca', 'route' => 'accounting.reports.balance-sheet', 'icon' => 'file-invoice', 'permission' => 'view_accounting'],
        ['label' => 'Arus Kas', 'route' => 'accounting.reports.cash-flow', 'icon' => 'money-bill-transfer', 'permission' => 'view_accounting'],
        ['label' => 'Chart of Accounts', 'route' => 'accounting.accounts.index', 'icon' => 'sitemap', 'permission' => 'view_accounting'],
        ['label' => 'Pemetaan Akun', 'route' => 'accounting.settings.index', 'icon' => 'diagram-project', 'permission' => 'manage_accounting_settings'],
        ['label' => 'Tutup Buku', 'route' => 'accounting.periods.index', 'icon' => 'lock', 'permission' => 'close_accounting_period'],
    ];
@endphp

<div class="bg-white rounded-lg shadow mb-6 overflow-x-auto print:hidden">
    <div class="flex flex-wrap gap-1 p-2">
        @foreach($tabs as $tab)
            @continue(! hasPermission($tab['permission']))
            <a href="{{ route($tab['route']) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap {{ request()->routeIs($tab['route']) ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <i class="fas fa-{{ $tab['icon'] }} mr-1"></i>{{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
