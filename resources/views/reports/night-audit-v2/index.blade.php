@extends('layouts.app')

@section('title', 'Night Audit v2')
@section('header', 'Night Audit v2')

@section('content')
<!-- Filter -->
<div class="mb-6 no-print">
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Audit</label>
                <input type="date" name="date" value="{{ $date }}" class="border rounded px-3 py-2" id="auditDate">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-search mr-1"></i> Tampilkan
            </button>
            <button type="button" onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            @if($mode === 'locked' && $snapshot)
                <a href="{{ route('reports.night-audit-v2.export', $snapshot->id) }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Status Banner --}}
@if($mode === 'locked')
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm flex items-center justify-between">
        <div>
            <i class="fas fa-lock mr-2"></i>
            <strong>LOCKED</strong> — Night Audit tanggal <strong>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong> sudah di-finalize oleh {{ $snapshot->lockedBy?->name ?? 'System' }} pada {{ $snapshot->locked_at?->format('d/m/Y H:i') ?? '-' }}.
            <br><span class="text-sm">Data report sudah tidak berubah (snapshot).</span>
        </div>
        @if(auth()->user()->isOwner() || auth()->user()->isAdmin() || auth()->user()->isUserManager())
        <form method="POST" action="{{ route('reports.night-audit-v2.delete-draft') }}" onsubmit="return confirm('Hapus lock? Data yang sudah di-lock akan dihapus dan bisa dibuat ulang. Lanjutkan?')">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button class="text-red-600 text-sm hover:underline bg-white px-3 py-1 rounded border border-red-300"><i class="fas fa-unlock"></i> Unlock & Buat Baru</button>
        </form>
        @endif
    </div>
@elseif($mode === 'draft')
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded shadow-sm flex items-center justify-between">
        <div>
            <i class="fas fa-pen mr-2"></i>
            <strong>DRAFT</strong> — Night Audit tanggal <strong>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong> masih draft (disimpan {{ $auditLog->updated_at->format('d/m/Y H:i') }}).
            <br><span class="text-sm">Data dari snapshot draft. Refresh untuk update data terbaru.</span>
        </div>
        <div class="flex gap-2">
            <button onclick="refreshPreview('{{ $date }}')" class="text-blue-600 text-sm hover:underline bg-white px-3 py-1 rounded border border-blue-300"><i class="fas fa-sync-alt"></i> Refresh</button>
            <form method="POST" action="{{ route('reports.night-audit-v2.delete-draft') }}" onsubmit="return confirm('Hapus draft?')">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button class="text-red-600 text-sm hover:underline bg-white px-3 py-1 rounded border border-red-300"><i class="fas fa-trash"></i> Hapus Draft</button>
            </form>
        </div>
    </div>
@else
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded shadow-sm">
        <i class="fas fa-eye mr-2"></i>
        <strong>PREVIEW</strong> — Data real-time dari database. Simpan sebagai draft atau lock untuk finalisasi.
    </div>
@endif

{{-- Tab Navigation --}}
<div class="flex gap-1 mb-4 bg-gray-100 p-1 rounded-lg w-fit">
    <button onclick="switchTab('report')" data-tab="report" class="px-4 py-2 text-sm font-medium rounded-md bg-white shadow-sm">
        <i class="fas fa-file-alt mr-1"></i> Night Audit Report
    </button>
    <button onclick="switchTab('history')" data-tab="history" class="px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white/50">
        <i class="fas fa-history mr-1"></i> Riwayat Locked
    </button>
</div>

{{-- Tab: Report --}}
<div id="tab-report">
    <div id="reportContent">
        @if($mode === 'preview' || !empty($data))
            @include('reports.night-audit-v2.partials.report-content', $data)
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
                <i class="fas fa-moon text-5xl mb-4"></i>
                <p class="text-lg">Pilih tanggal dan klik "Tampilkan" untuk memulai Night Audit.</p>
            </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    @if($mode === 'preview' && !empty($data))
    <div class="mt-6 flex gap-3 no-print">
        <form method="POST" action="{{ route('reports.night-audit-v2.save-draft') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold">
                <i class="fas fa-save mr-2"></i> Simpan Draft
            </button>
        </form>
        <form method="POST" action="{{ route('reports.night-audit-v2.lock') }}" onsubmit="return confirm('Lock report? Data akan di-snapshot dan tidak bisa diubah lagi. Lanjutkan?')">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                <i class="fas fa-lock mr-2"></i> Lock & Finalize
            </button>
        </form>
    </div>
    @elseif($mode === 'draft')
    <div class="mt-6 flex gap-3 no-print">
        <button onclick="refreshPreview('{{ $date }}')" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 font-semibold">
            <i class="fas fa-sync-alt mr-2"></i> Refresh Data
        </button>
        <form method="POST" action="{{ route('reports.night-audit-v2.lock') }}" onsubmit="return confirm('Lock report? Data akan di-snapshot ulang dan tidak bisa diubah lagi. Lanjutkan?')">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                <i class="fas fa-lock mr-2"></i> Lock & Finalize
            </button>
        </form>
    </div>
    @endif
</div>

{{-- Tab: History --}}
<div id="tab-history" class="hidden">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold text-lg mb-4"><i class="fas fa-history text-blue-500 mr-2"></i>Riwayat Night Audit Locked</h3>
        @if($history->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left p-2 font-bold">Tanggal</th>
                        <th class="text-center p-2 font-bold">Total Kamar</th>
                        <th class="text-center p-2 font-bold">Occupied</th>
                        <th class="text-center p-2 font-bold">Okupansi</th>
                        <th class="text-right p-2 font-bold">Total Revenue</th>
                        <th class="text-center p-2 font-bold">Di-lock oleh</th>
                        <th class="text-center p-2 font-bold">Waktu Lock</th>
                        <th class="text-center p-2 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $log)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $log->audit_date->format('d/m/Y') }}</td>
                        <td class="p-2 text-center">{{ $log->total_rooms }}</td>
                        <td class="p-2 text-center">{{ $log->occupied_rooms }}</td>
                        <td class="p-2 text-center">{{ $log->occupancy_rate }}%</td>
                        <td class="p-2 text-right font-semibold">Rp {{ number_format($log->total_revenue, 0, ',', '.') }}</td>
                        <td class="p-2 text-center">{{ $log->lockedBy?->name ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $log->locked_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="p-2 text-center">
                            <a href="{{ route('reports.night-audit-v2.show', $log->id) }}" class="text-blue-600 hover:underline text-xs" target="_blank">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <button onclick="printLocked({{ $log->id }})" class="text-green-600 hover:underline text-xs ml-2" title="Print">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <a href="{{ route('reports.night-audit-v2.export', $log->id) }}" class="text-orange-600 hover:underline text-xs ml-2">
                                <i class="fas fa-download"></i> CSV
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-400 text-center py-6 italic">Belum ada Night Audit yang di-lock.</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('[data-tab]').forEach(function(btn) {
        btn.classList.remove('bg-white', 'shadow-sm');
        btn.classList.add('text-gray-600');
    });
    var activeBtn = document.querySelector('[data-tab="' + tab + '"]');
    if (activeBtn) {
        activeBtn.classList.add('bg-white', 'shadow-sm');
        activeBtn.classList.remove('text-gray-600');
    }
    document.getElementById('tab-report').classList.toggle('hidden', tab !== 'report');
    document.getElementById('tab-history').classList.toggle('hidden', tab !== 'history');
}

function printLocked(id) {
    var iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.top = '-9999px';
    iframe.style.left = '-9999px';
    iframe.style.width = '1px';
    iframe.style.height = '1px';
    iframe.onload = function() {
        setTimeout(function() {
            iframe.contentWindow.print();
        }, 800);
    };
    iframe.src = '{{ url("reports/night-audit-v2") }}/' + id;
    document.body.appendChild(iframe);
}

function refreshPreview(date) {
    var container = document.getElementById('reportContent');
    if (!container) return;
    container.innerHTML = '<div class="bg-white rounded-lg shadow p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3"></i><p>Memuat data terbaru...</p></div>';

    var xhr = new window.XMLHttpRequest();
    xhr.open('GET', '{{ route("reports.night-audit-v2.preview") }}?date=' + date, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success && resp.html) {
                    container.innerHTML = resp.html;
                } else {
                    container.innerHTML = '<div class="bg-white rounded-lg shadow p-8 text-center text-red-500">Gagal memuat data.</div>';
                }
            } catch(e) {
                container.innerHTML = '<div class="bg-white rounded-lg shadow p-8 text-center text-red-500">Error parsing response.</div>';
            }
        } else {
            container.innerHTML = '<div class="bg-white rounded-lg shadow p-8 text-center text-red-500">Error: ' + xhr.status + '</div>';
        }
    };
    xhr.onerror = function() {
        container.innerHTML = '<div class="bg-white rounded-lg shadow p-8 text-center text-red-500">Network error.</div>';
    };
    xhr.send();
}
</script>
@endsection
