@extends('layouts.app')

@section('title', 'Log Email OTA')
@section('header', '📧 Monitoring Log Email OTA')

@section('content')
<div class="space-y-6">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Total Email</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Berhasil</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['processed']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Gagal</div>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ number_format($stats['failed']) }}</div>
            @if($stats['failed_today'] > 0)
                <div class="text-xs text-red-500 font-semibold">{{ $stats['failed_today'] }} hari ini</div>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-400">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Duplikat</div>
            <div class="text-2xl font-bold text-gray-600 mt-1">{{ number_format($stats['duplicate']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Dilewati</div>
            <div class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($stats['skipped']) }}</div>
        </div>
    </div>

    {{-- Service Monitoring Card --}}
    <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $serviceStatus['is_running'] ? 'border-emerald-500' : 'border-red-500' }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="text-2xl {{ $serviceStatus['is_running'] ? 'text-emerald-500' : 'text-red-500' }}">
                    <i class="fas {{ $serviceStatus['status_icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-700">
                        Monitoring Service Email OTA
                    </h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $serviceStatus['is_running'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            <i class="fas {{ $serviceStatus['status_icon'] }} mr-1"></i>
                            {{ $serviceStatus['status_label'] }}
                        </span>
                        <span class="text-xs text-gray-500">
                            Interval: setiap {{ $serviceStatus['schedule_interval'] }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right text-xs text-gray-500 space-y-1">
                    @if($serviceStatus['last_activity'])
                        <div>Aktivitas terakhir: <strong>{{ $serviceStatus['last_activity'] }}</strong></div>
                    @endif
                    @if($serviceStatus['last_email_subject'])
                        <div>Email terakhir: <span class="text-gray-700">{{ \Illuminate\Support\Str::limit($serviceStatus['last_email_subject'], 40) }}</span></div>
                    @endif
                    @if($serviceStatus['minutes_since_last_email'] !== null)
                        <div>
                            @if($serviceStatus['minutes_since_last_email'] <= 1)
                                <span class="text-emerald-600 font-medium">🔹 Baru saja</span>
                            @else
                                🔹 {{ $serviceStatus['minutes_since_last_email'] }} menit yang lalu
                            @endif
                        </div>
                    @else
                        <div class="text-gray-400">Belum ada aktivitas email</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('ota-email-logs.refresh-service-status') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-gray-100 text-gray-600 px-3 py-2 rounded text-sm hover:bg-gray-200" title="Refresh Status">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </form>
            </div>
        </div>
        @if(!$serviceStatus['is_running'])
            <div class="mt-3 pt-3 border-t border-red-100">
                <div class="flex items-start gap-2 text-sm text-red-700 bg-red-50 p-3 rounded">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    <div>
                        <strong>Service tidak aktif!</strong> Tidak ada aktivitas email dalam 15 menit terakhir.
                        Pastikan scheduler Laravel berjalan dengan menambahkan cron job:
                        <code class="block mt-1 text-xs bg-red-100 px-2 py-1 rounded font-mono">
                            * * * * * cd {{ base_path() }} && php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1
                        </code>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Breakdown by Source & Type --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if(!empty($stats['by_source']))
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-globe mr-1"></i> Per Sumber OTA</h3>
            <div class="space-y-2">
                @foreach($stats['by_source'] as $source => $count)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $source }}</span>
                        <span class="font-semibold">{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($stats['by_type']))
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-bold text-gray-700 mb-3"><i class="fas fa-tag mr-1"></i> Per Tipe Email</h3>
            <div class="space-y-2">
                @foreach($stats['by_type'] as $type => $count)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ ucfirst($type) }}</span>
                        <span class="font-semibold">{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Subject atau Pengirim..."
                    class="border rounded px-3 py-2 text-sm w-48">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="border rounded px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="processed" @selected(request('status') === 'processed')>Berhasil</option>
                    <option value="failed" @selected(request('status') === 'failed')>Gagal</option>
                    <option value="duplicate" @selected(request('status') === 'duplicate')>Duplikat</option>
                    <option value="skipped" @selected(request('status') === 'skipped')>Dilewati</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sumber OTA</label>
                <select name="ota_source" class="border rounded px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach($otaSources as $source => $count)
                        <option value="{{ $source }}" @selected(request('ota_source') === $source)>
                            {{ $source }} ({{ $count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Email</label>
                <select name="email_type" class="border rounded px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach($emailTypes as $type => $count)
                        <option value="{{ $type }}" @selected(request('email_type') === $type)>
                            {{ ucfirst($type) }} ({{ $count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border rounded px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('ota-email-logs.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300">
                <i class="fas fa-undo mr-1"></i> Reset
            </a>
            <form method="POST" action="{{ route('ota-email-logs.refresh-stats') }}" class="inline">
                @csrf
                <button type="submit" class="bg-gray-100 text-gray-600 px-4 py-2 rounded text-sm hover:bg-gray-200">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Statistik
                </button>
            </form>
        </form>
    </div>

    {{-- Email Logs Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">#</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Subjek</th>
                        <th class="px-4 py-3 text-left">Pengirim</th>
                        <th class="px-4 py-3 text-center">Sumber</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-400">{{ $log->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <div class="truncate font-medium text-gray-800">
                                    @if($log->subject)
                                        {{ $log->subject }}
                                    @else
                                        <span class="text-gray-400 italic">(tanpa subjek)</span>
                                    @endif
                                </div>
                                @if($log->reservation_id)
                                    <span class="text-xs text-blue-500 font-medium">Ref: {{ $log->reservation_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $log->sender }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($log->ota_source)
                                    <span class="inline-block bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">
                                        {{ $log->ota_source }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-gray-500">{{ $log->email_type_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full {{ $log->status_badge }}">
                                    {{ $log->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('ota-email-logs.show', $log->id) }}"
                                        class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 hover:bg-blue-50 rounded"
                                        title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($log->status === 'failed' && $log->raw_body)
                                        <form method="POST" action="{{ route('ota-email-logs.retry', $log->id) }}"
                                            class="inline"
                                            onsubmit="return confirm('Retry email ini?')">
                                            @csrf
                                            <button type="submit"
                                                class="text-amber-600 hover:text-amber-800 text-xs px-2 py-1 hover:bg-amber-50 rounded"
                                                title="Proses Ulang">
                                                <i class="fas fa-redo-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                Belum ada email OTA yang diproses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Last 10 Notifications from cache --}}
    @php $notifications = cache('ota_notifications', []); @endphp
    @if(count($notifications) > 0)
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-bold text-gray-700 mb-3">
                <i class="fas fa-bell mr-1"></i> Notifikasi Terbaru (dari cache)
            </h3>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach(array_reverse($notifications) as $notif)
                    <div class="flex items-start gap-2 text-sm p-2 bg-gray-50 rounded">
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            @php $notifTime = \Carbon\Carbon::parse($notif['created_at']); @endphp
                            {{ $notifTime->diffForHumans() }}
                        </span>
                        <span class="text-gray-700">{{ $notif['message'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
