@extends('layouts.app')

@section('title', 'TV Welcome Settings')
@section('header', 'TV Welcome Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <div class="text-sm text-blue-700">
            <p>Atur tampilan <strong>TV Welcome Screen</strong> untuk setiap kamar. URL yang bisa dibuka di Smart TV browser:</p>
            <code class="block mt-1 text-xs bg-blue-100 px-2 py-1 rounded">{{ url('/tv/{nomor_kamar}') }}</code>
            <p class="mt-1">Contoh: <code class="bg-blue-100 px-1 rounded">{{ url('/tv/101') }}</code></p>
        </div>
    </div>

    {{-- Form Settings --}}
    <form method="POST" action="{{ route('admin.tv-settings.update') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow" data-ajax="true">
        @csrf

        {{-- Video Company Profile --}}
        <div class="p-6 border-b">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-video text-blue-500 mr-1"></i> Video Company Profile
            </label>
            <p class="text-xs text-gray-400 mb-4">Upload video MP4 atau masukkan URL eksternal (YouTube, Vimeo, dll). Video akan diputar sebagai background di TV Welcome Screen.</p>

            {{-- Preview video saat ini --}}
            @if($setting->company_video_path)
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-2">Video saat ini:</p>
                <video controls class="w-full max-w-md rounded-lg border" style="max-height: 200px;">
                    <source src="{{ asset('storage/' . $setting->company_video_path) }}" type="video/mp4">
                </video>
                <label class="inline-flex items-center gap-2 mt-2 text-sm text-red-600 cursor-pointer">
                    <input type="checkbox" name="clear_video_path" value="1" onchange="this.form.submit()">
                    <i class="fas fa-trash-alt"></i> Hapus video
                </label>
            </div>
            @endif

            @if($setting->company_video_url)
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">URL Video Eksternal saat ini:</p>
                <code class="text-xs bg-gray-200 px-2 py-1 rounded break-all">{{ $setting->company_video_url }}</code>
                <label class="inline-flex items-center gap-2 mt-2 text-sm text-red-600 cursor-pointer">
                    <input type="checkbox" name="clear_video_url" value="1" onchange="this.form.submit()">
                    <i class="fas fa-trash-alt"></i> Hapus URL
                </label>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="company_video" class="block text-sm font-medium text-gray-600 mb-1">Upload Video (MP4, WebM, OGG — max 50MB)</label>
                    <input type="file" name="company_video" id="company_video" accept="video/mp4,video/webm,video/ogg"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    @error('company_video')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="company_video_url" class="block text-sm font-medium text-gray-600 mb-1">Atau URL Video Eksternal</label>
                    <input type="url" name="company_video_url" id="company_video_url"
                           value="{{ old('company_video_url', $setting->company_video_url) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="https://www.youtube.com/embed/xxxxx">
                    @error('company_video_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Pengaturan Lainnya --}}
        <div class="p-6 border-b">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-sliders-h text-blue-500 mr-1"></i> Pengaturan Tampilan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Refresh Interval --}}
                <div>
                    <label for="tv_refresh_interval" class="block text-sm font-medium text-gray-600 mb-1">
                        <i class="fas fa-sync text-blue-500 mr-1"></i> Interval Refresh (detik)
                    </label>
                    <input type="number" name="tv_refresh_interval" id="tv_refresh_interval"
                           value="{{ old('tv_refresh_interval', $setting->tv_refresh_interval ?? 30) }}"
                           min="5" max="300"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <p class="text-xs text-gray-400 mt-1">Berapa detik sekali halaman TV mengecek perubahan status tamu (min 5, max 300)</p>
                    @error('tv_refresh_interval')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Welcome Message --}}
                <div>
                    <label for="tv_welcome_message" class="block text-sm font-medium text-gray-600 mb-1">
                        <i class="fas fa-comment text-blue-500 mr-1"></i> Teks Sambutan
                    </label>
                    <input type="text" name="tv_welcome_message" id="tv_welcome_message"
                           value="{{ old('tv_welcome_message', $setting->tv_welcome_message) }}"
                           maxlength="200"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Selamat Datang">
                    <p class="text-xs text-gray-400 mt-1">Teks yang muncul di atas nama tamu. Kosongkan untuk default "Selamat Datang"</p>
                    @error('tv_welcome_message')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-6 bg-gray-50 rounded-b-lg flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Setting
            </button>
        </div>
    </form>

    {{-- Daftar Kamar + Preview --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-tv text-blue-500 mr-1"></i> Preview per Kamar
            </h3>
            <p class="text-xs text-gray-400 mt-1">Klik tombol "Buka TV Screen" untuk melihat tampilan welcome di masing-masing kamar.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="text-left p-3">No. Kamar</th>
                        <th class="text-left p-3">Tipe Kamar</th>
                        <th class="text-center p-3">Status Kamar</th>
                        <th class="text-left p-3">Nama Tamu</th>
                        <th class="text-center p-3">Check-Out</th>
                        <th class="text-center p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                    @php
                        $activeReservation = $room->reservations->where('status', 'checked_in')->first();
                        $guestName = $activeReservation?->guest?->guest_name ?? '-';
                        $checkOut = $activeReservation ? \Carbon\Carbon::parse($activeReservation->check_out)->format('d/m/Y H:i') : '-';
                        $tvUrl = route('tv.welcome', $room->room_number);
                    @endphp
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 font-semibold">{{ $room->room_number }}</td>
                        <td class="p-3 text-gray-600">{{ $room->room_type_name ?? '-' }}</td>
                        <td class="p-3 text-center">
                            @switch($room->status)
                                @case('occupied')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle text-[8px]"></i> Occupied
                                    </span>
                                    @break
                                @case('available')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-circle text-[8px]"></i> Available
                                    </span>
                                    @break
                                @case('cleaning')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-circle text-[8px]"></i> Cleaning
                                    </span>
                                    @break
                                @case('maintenance')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-circle text-[8px]"></i> Maintenance
                                    </span>
                                    @break
                                @default
                                    <span class="text-gray-400">{{ $room->status }}</span>
                            @endswitch
                        </td>
                        <td class="p-3">{{ $guestName }}</td>
                        <td class="p-3 text-center text-sm">{{ $checkOut }}</td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ $tvUrl }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                                    <i class="fas fa-external-link-alt"></i> Buka TV Screen
                                </a>
                                <button type="button" onclick="copyUrl('{{ $tvUrl }}', this)"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition">
                                    <i class="fas fa-copy"></i> Salin URL
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">
                            <i class="fas fa-bed text-2xl mb-2 block"></i>
                            Belum ada kamar terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function copyUrl(url, btn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
                btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                btn.classList.add('bg-green-600');
                setTimeout(() => {
                    btn.innerHTML = original;
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
                }, 2000);
            });
        } else {
            // Fallback
            const input = document.createElement('input');
            input.value = url;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            alert('URL tersalin: ' + url);
        }
    }
</script>
@endpush
