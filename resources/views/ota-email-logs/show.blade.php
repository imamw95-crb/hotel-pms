@extends('layouts.app')

@section('title', 'Detail Email OTA')
@section('header', '📧 Detail Email OTA #' . $log->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ─── Back Button ─── ──}}
    <a href="{{ route('ota-email-logs.index') }}" class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center gap-1">
        <i class="fas fa-arrow-left"></i> Kembali ke Log
    </a>

    {{-- ─── Info Card ─── ──}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">Informasi Email</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Status</label>
                <span class="inline-block text-sm font-semibold px-3 py-1 rounded-full mt-1 {{ $log->status_badge }}">
                    {{ $log->status_label }}
                </span>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Sumber OTA</label>
                <div class="text-sm font-medium mt-1">
                    @if($log->ota_source)
                        <span class="inline-block bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $log->ota_source }}
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Tipe Email</label>
                <div class="text-sm mt-1">{{ $log->email_type_label }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Reservation ID</label>
                <div class="text-sm mt-1">
                    @if($log->reservation_id)
                        <span class="font-mono text-blue-600">{{ $log->reservation_id }}</span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Pengirim</label>
                <div class="text-sm mt-1">{{ $log->sender ?: '-' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Diproses Pada</label>
                <div class="text-sm mt-1">
                    {{ $log->processed_at ? $log->processed_at->format('d/m/Y H:i:s') : '-' }}
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Dibuat Pada</label>
                <div class="text-sm mt-1">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Retry Count</label>
                <div class="text-sm mt-1">{{ $log->retry_count ?? 0 }}x</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Email UID</label>
                <div class="text-sm mt-1 font-mono text-xs text-gray-500">{{ $log->email_uid ?: '-' }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Subject ─── ──}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-bold text-gray-700 mb-2">Subjek Email</h3>
        <div class="bg-gray-50 rounded p-3 text-sm font-medium">
            {{ $log->subject ?: '(tanpa subjek)' }}
        </div>
    </div>

    {{-- ─── Error Message ─── ──}}
    @if($log->error_message)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h3 class="text-sm font-bold text-red-700 mb-2 flex items-center gap-1">
                <i class="fas fa-exclamation-triangle"></i> Pesan Error
            </h3>
            <pre class="text-sm text-red-600 font-mono whitespace-pre-wrap">{{ $log->error_message }}</pre>
        </div>
    @endif

    {{-- ─── Raw Body ─── ──}}
    @if($log->raw_body)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700">
                    <i class="fas fa-file-alt mr-1"></i> Body Email (Raw)
                </h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">{{ number_format(strlen($log->raw_body)) }} karakter</span>
                    <button onclick="toggleRawBody(this)" class="text-blue-600 hover:text-blue-800 text-xs underline">
                        Tampilkan Semua
                    </button>
                </div>
            </div>
            <pre id="rawBody" class="bg-gray-50 rounded p-4 text-xs font-mono whitespace-pre-wrap max-h-96 overflow-y-auto border">{{ $log->raw_body }}</pre>
        </div>
    @endif

    {{-- ─── Actions ─── ──}}
    <div class="flex items-center gap-3">
        <a href="{{ route('ota-email-logs.index') }}"
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>

        @if($log->status === 'failed' && $log->raw_body)
            <form method="POST" action="{{ route('ota-email-logs.retry', $log->id) }}" class="inline"
                onsubmit="return confirm('Retry dan proses ulang email ini?')">
                @csrf
                <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded text-sm hover:bg-amber-600">
                    <i class="fas fa-redo-alt mr-1"></i> Proses Ulang (Retry)
                </button>
            </form>
        @endif
    </div>
</div>

<script>
    function toggleRawBody(btn) {
        const el = document.getElementById('rawBody');
        if (el && btn) {
            const isCollapsed = el.style.maxHeight === '' || el.style.maxHeight === '96px';
            if (isCollapsed) {
                el.style.maxHeight = 'none';
                btn.textContent = 'Sembunyikan';
            } else {
                el.style.maxHeight = '96px';
                btn.textContent = 'Tampilkan Semua';
            }
        }
    }
</script>
@endsection
