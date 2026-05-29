@extends('layouts.app')

@section('title', 'API Keys')
@section('header', 'API Keys — Reservation')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-start gap-3">
        <i class="fas fa-key text-blue-500 mt-0.5"></i>
        <div class="text-sm text-blue-700">
            <p class="font-semibold">API Key untuk Aplikasi Booking Web</p>
            <p class="mt-1">Gunakan API key untuk mengautentikasi request dari aplikasi booking web ke sistem PMS.</p>
            <p class="mt-1">Kirim key via header <code class="bg-blue-100 px-1 rounded">X-API-Key</code> atau query <code class="bg-blue-100 px-1 rounded">?api_key=</code></p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
            <div class="text-sm text-green-700">{!! session('success') !!}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            <div class="text-sm text-red-700">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Generate New Key --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-plus-circle text-green-500 mr-2"></i>Generate API Key Baru
            </h3>
        </div>
        <div class="p-6">
            <form id="generateKeyForm" method="POST" action="{{ route('admin.api-keys.generate') }}" class="flex flex-wrap gap-4 items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select name="user_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($ownerAdminUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) — {{ $u->role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama / Tujuan</label>
                    <input type="text" name="name" placeholder="contoh: reservation, mobile-app, ota-sync"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-key mr-1"></i> Generate
                </button>
            </form>

            {{-- Key Result --}}
            <div id="keyResult" class="hidden mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm font-semibold text-green-800 mb-2">
                    <i class="fas fa-check-circle mr-1"></i>API Key berhasil dibuat!
                </p>
                <div class="flex items-center gap-2">
                    <code id="generatedKey" class="flex-1 bg-white border border-green-300 rounded px-3 py-2 text-sm font-mono text-green-900 select-all break-all"></code>
                    <button onclick="copyKey()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm whitespace-nowrap" title="Copy to clipboard">
                        <i class="fas fa-copy mr-1"></i> Copy
                    </button>
                </div>
                <p class="text-xs text-red-600 mt-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>SIMPAN KEY INI! Key tidak bisa ditampilkan lagi.
                </p>
            </div>
        </div>
    </div>

    {{-- Existing Keys --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-list text-gray-500 mr-2"></i>API Keys Aktif
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Key</th>
                        <th class="px-4 py-3 text-left">Dibuat</th>
                        <th class="px-4 py-3 text-left">Terakhir Dipakai</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keys as $key)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $key['user_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $key['user_email'] }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium">{{ $key['name'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <code class="text-gray-500 text-xs">••••••••••••••••••••••••••••••••</code>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $key['created_at'] ? \Carbon\Carbon::parse($key['created_at'])->locale('id')->isoFormat('DD MMM YYYY, HH:mm') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $key['last_used_at'] ? \Carbon\Carbon::parse($key['last_used_at'])->locale('id')->isoFormat('DD MMM YYYY, HH:mm') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.api-keys.revoke', $key['id']) }}" onsubmit="return confirm('Hapus API key &quot;{{ $key['name'] }}&quot;?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Revoke">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada API key. Generate key baru di atas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- API Usage Example --}}
    <div class="bg-white rounded-lg shadow mt-6">
        <div class="p-4 border-b">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-code text-purple-500 mr-2"></i>Contoh Penggunaan
            </h3>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-3">Base URL API: <code class="bg-gray-100 px-2 py-1 rounded">{{ url('/api') }}</code></p>

            <div class="bg-gray-900 rounded-lg p-4 text-sm font-mono text-gray-100 overflow-x-auto">
                <p class="text-gray-500"># GET list reservasi</p>
                <p>curl -X GET <span class="text-green-400">"{{ url('/api/reservations') }}?status=pending&per_page=10"</span> \</p>
                <p>&nbsp;&nbsp;-H <span class="text-yellow-400">"X-API-Key: YOUR_API_KEY"</span></p>
                <p>&nbsp;&nbsp;-H <span class="text-yellow-400">"Accept: application/json"</span></p>
                <br>
                <p class="text-gray-500"># POST buat reservasi baru</p>
                <p>curl -X POST <span class="text-green-400">"{{ url('/api/reservations') }}"</span> \</p>
                <p>&nbsp;&nbsp;-H <span class="text-yellow-400">"X-API-Key: YOUR_API_KEY"</span> \</p>
                <p>&nbsp;&nbsp;-H <span class="text-yellow-400">"Content-Type: application/json"</span> \</p>
                <p>&nbsp;&nbsp;-d <span class="text-yellow-400">'{"guest_name":"John Doe","room_id":1,"check_in":"2026-06-01","check_out":"2026-06-03"}'</span></p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('generateKeyForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type="submit"]');
    const resultBox = document.getElementById('keyResult');
    const keyEl = document.getElementById('generatedKey');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generating...';
    resultBox.classList.add('hidden');

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                user_id: form.user_id.value,
                name: form.name.value,
            }),
        });
        const data = await res.json();
        if (data.success) {
            keyEl.textContent = data.data.api_key;
            resultBox.classList.remove('hidden');
            // Scroll to result
            resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            alert(data.message || 'Gagal generate API key.');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function copyKey() {
    const key = document.getElementById('generatedKey').textContent;
    if (!key) return;

    // Try modern clipboard API (HTTPS)
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(key).then(() => {
            const btn = document.querySelector('#keyResult button');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        }).catch(() => fallbackCopy(key));
    } else {
        fallbackCopy(key);
    }
}

function fallbackCopy(text) {
    // Create temporary textarea
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        const btn = document.querySelector('#keyResult button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    } catch (e) {
        // Ultimate fallback: select text manually
        const range = document.createRange();
        const el = document.getElementById('generatedKey');
        range.selectNodeContents(el);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        alert('Tekan Ctrl+C untuk copy key ini');
    }
    document.body.removeChild(ta);
}
</script>
@endsection
