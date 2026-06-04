@extends('layouts.tv')

@section('title', $room->room_number . ' - ' . $hotelSetting->hotel_name)

@section('content')
@php
    $hasGuest = $reservation && $reservation->guest;
    $guestName = $hasGuest ? 'Mr/Ms. ' . $reservation->guest->guest_name : null;
    $checkOut = $hasGuest ? \Carbon\Carbon::parse($reservation->check_out)->format('d M Y H:i') : null;
    $welcomeMsg = $hotelSetting->tv_welcome_message ?: 'Selamat Datang';

    // Determine video source
    $videoSrc = null;
    $isExternalVideo = false;
    $youtubeVideoId = null;
    if ($hotelSetting->company_video_path) {
        $videoSrc = asset('storage/' . $hotelSetting->company_video_path);
    } elseif ($hotelSetting->company_video_url) {
        $url = $hotelSetting->company_video_url;
        // Extract YouTube video ID from various URL formats
        preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
        if (!empty($matches[1])) {
            $youtubeVideoId = $matches[1];
            $videoSrc = 'https://www.youtube.com/embed/' . $youtubeVideoId;
        } else {
            $videoSrc = $url;
        }
        $isExternalVideo = true;
    }
@endphp

{{-- Video Background --}}
@if($youtubeVideoId)
{{-- YouTube: zoom-in biar fullscreen tanpa batas hitam --}}
<div class="fixed inset-0 overflow-hidden" style="z-index:0">
    <iframe id="bgVideo"
            src="https://www.youtube.com/embed/{{ $youtubeVideoId }}?autoplay=1&mute=1&loop=1&playlist={{ $youtubeVideoId }}&controls=0&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1&enablejsapi=1&fs=0"
            class="absolute pointer-events-none"
            style="top:50%;left:50%;width:177.78vh;height:100vh;min-width:100vw;min-height:56.25vw;transform:translate(-50%,-50%);"
            frameborder="0"
            allow="autoplay; encrypted-media"
            loading="eager"></iframe>
</div>
@elseif($videoSrc && !$isExternalVideo)
<video id="bgVideo" autoplay muted loop playsinline
       class="fixed inset-0 w-full h-full object-cover" style="z-index:0">
    <source src="{{ $videoSrc }}" type="video/mp4">
</video>
@elseif($videoSrc && $isExternalVideo)
{{-- Video eksternal: zoom-in biar fullscreen --}}
<div class="fixed inset-0 overflow-hidden" style="z-index:0">
    <iframe id="bgVideo" src="{{ $videoSrc }}?autoplay=1&mute=1&loop=1&controls=0&fs=0&enablejsapi=1"
            class="absolute pointer-events-none"
            style="top:50%;left:50%;width:177.78vh;height:100vh;min-width:100vw;min-height:56.25vw;transform:translate(-50%,-50%);"
            frameborder="0" allow="autoplay; encrypted-media" loading="lazy"></iframe>
</div>
@else
{{-- Fallback gradient background --}}
<div class="fixed inset-0 bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900" style="z-index:0"></div>
@endif

{{-- Overlay gelap agar teks terbaca --}}
<div class="fixed inset-0 bg-black/50" style="z-index:1"></div>

{{-- Konten Welcome --}}
<div class="fixed inset-0 flex flex-col text-white px-12" style="z-index:2">

    {{-- Top Bar: Logo + Nama Hotel (kiri) & Jam + Sound (kanan) --}}
    <div class="flex items-start justify-between pt-8">
        <div class="flex items-center gap-4">
            @if($hotelSetting->logo_path)
            <img src="{{ asset('storage/' . $hotelSetting->logo_path) }}" alt="Logo" class="h-16 w-auto object-contain">
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-wide">{{ $hotelSetting->hotel_name }}</h1>
                @if($hotelSetting->address)
                <p class="text-sm text-white/70">{{ $hotelSetting->address }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-start gap-4">
            {{-- Tombol Sound Toggle --}}
            <button id="soundToggle" onclick="toggleSound()"
                    class="text-white/50 hover:text-white transition cursor-pointer p-2 text-2xl" title="Aktifkan/Nonaktifkan suara">
                🔇
            </button>
            <div class="text-right">
                <div id="clock" class="text-3xl font-light tabular-nums">--:--:--</div>
                <div id="dateDisplay" class="text-sm text-white/70 mt-1">---</div>
            </div>
        </div>
    </div>

    {{-- Konten Utama --}}
    <div class="flex-1 flex flex-col items-center justify-center -mt-16">
        <div class="text-center">
            @if($hasGuest)
                {{-- Ada Tamu --}}
                <p class="text-lg text-white/80 tracking-widest uppercase mb-4">{{ $welcomeMsg }}</p>

                <h2 class="text-7xl md:text-8xl font-bold mb-6 drop-shadow-lg leading-tight">
                    {{ $guestName }}
                </h2>

                <div class="inline-flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-2xl px-8 py-4 mb-6">
                    <i class="fas fa-door-open text-3xl text-white/80"></i>
                    <span class="text-5xl md:text-6xl font-bold tracking-widest">{{ $room->room_number }}</span>
                </div>

                <div class="flex items-center justify-center gap-6 text-white/80 text-lg">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar-check"></i>
                        <span>Check-In: {{ \Carbon\Carbon::parse($reservation->check_in)->format('d M Y H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar-times"></i>
                        <span>Check-Out: {{ $checkOut }}</span>
                    </div>
                </div>
            @else
                {{-- Kamar Kosong --}}
                <div class="animate-fadeIn">
                    <i class="fas fa-bed text-6xl text-white/40 mb-6"></i>
                    <p class="text-xl text-white/60 tracking-widest uppercase mb-2">Room</p>
                    <h2 class="text-8xl md:text-9xl font-bold drop-shadow-lg mb-4">
                        {{ $room->room_number }}
                    </h2>
                    <p class="text-2xl text-white/50 tracking-wider">Room Available</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="pb-8 text-center text-sm text-white/50">
        <p>&copy; {{ date('Y') }} {{ $hotelSetting->hotel_name }}. All rights reserved.</p>
    </div>
</div>

@push('scripts')
<script>
    // ─── Real-time Clock ──────────────────────────────────────
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID', { hour12: false });
        const date = now.toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        document.getElementById('clock').textContent = time;
        document.getElementById('dateDisplay').textContent = date;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ─── YouTube IFrame API ────────────────────────────────
    let ytPlayer = null;
    let isMuted = true;

    function onYouTubeIframeAPIReady() {
        const el = document.getElementById('bgVideo');
        if (!el || el.tagName !== 'IFRAME') return;
        ytPlayer = new YT.Player('bgVideo', {
            events: {
                'onReady': function(event) {
                    // Video siap — coba unmute otomatis setelah 1 detik
                    setTimeout(() => {
                        try {
                            event.target.unMute();
                            event.target.setVolume(50);
                            isMuted = false;
                            const btn = document.getElementById('soundToggle');
                            if (btn) btn.textContent = '🔊';
                        } catch(e) {}
                    }, 1000);
                }
            }
        });
    }

    // Load YouTube IFrame API
    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // ─── Sound Toggle ────────────────────────────────────────
    function toggleSound() {
        const btn = document.getElementById('soundToggle');
        isMuted = !isMuted;

        const player = document.getElementById('bgVideo');

        if (player.tagName === 'IFRAME' && ytPlayer && typeof ytPlayer.unMute === 'function') {
            // YouTube IFrame API
            try {
                if (isMuted) {
                    ytPlayer.mute();
                    btn.textContent = '🔇';
                } else {
                    ytPlayer.unMute();
                    ytPlayer.setVolume(50);
                    btn.textContent = '🔊';
                }
            } catch(e) {
                fallbackToggle(player, btn);
            }
        } else if (player.tagName === 'IFRAME') {
            // External iframe — reload with mute parameter
            fallbackToggle(player, btn);
        } else if (player.tagName === 'VIDEO') {
            // Local video element
            player.muted = isMuted;
            btn.textContent = isMuted ? '🔇' : '🔊';
            if (!isMuted) {
                player.play().catch(() => {});
            }
        }
    }

    function fallbackToggle(player, btn) {
        const url = new URL(player.src);
        if (isMuted) {
            url.searchParams.set('mute', '1');
            btn.textContent = '🔇';
        } else {
            url.searchParams.delete('mute');
            btn.textContent = '🔊';
        }
        player.src = url.toString();
    }

    // ─── Auto Fullscreen (support semua browser TV) ─────────
    function goFullscreen() {
        const el = document.documentElement;
        if (el.requestFullscreen) {
            el.requestFullscreen().catch(() => {});
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        } else if (el.mozRequestFullScreen) {
            el.mozRequestFullScreen();
        } else if (el.msRequestFullscreen) {
            el.msRequestFullscreen();
        }
    }
    // Coba fullscreen setelah halaman siap
    setTimeout(goFullscreen, 2000);
    // Klik/tap di mana saja → fullscreen
    document.addEventListener('click', goFullscreen, { once: true });
    // Enter / OK pada remote TV → fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === 'OK') {
            goFullscreen();
        }
    }, { once: true });

    // ─── Auto-unmute untuk local video ──────────────────────
    const bgVideo = document.getElementById('bgVideo');
    if (bgVideo && bgVideo.tagName === 'VIDEO') {
        setTimeout(() => {
            bgVideo.muted = false;
            isMuted = false;
            const btn = document.getElementById('soundToggle');
            if (btn) btn.textContent = '🔊';
            bgVideo.play().catch(() => {});
        }, 1500);
    }

    // ─── Polling Status Kamar ────────────────────────────────
    const refreshInterval = {{ $hotelSetting->tv_refresh_interval ?? 30 }} * 1000;

    async function checkRoomStatus() {
        try {
            const res = await fetch('{{ route("tv.status", $room->room_number) }}');
            const data = await res.json();

            const currentHasGuest = {{ $hasGuest ? 'true' : 'false' }};

            if (data.has_guest !== currentHasGuest) {
                // Ada perubahan status — reload halaman
                location.reload();
            } else if (data.has_guest && data.guest_name !== '{{ $guestName ?? "" }}') {
                // Nama tamu berubah — reload
                location.reload();
            }
        } catch (err) {
            console.warn('TV polling error:', err);
        }
    }

    @if($hasGuest)
    // Polling hanya aktif jika ada tamu (untuk update real-time)
    setInterval(checkRoomStatus, refreshInterval);
    @else
    // Jika kamar kosong, polling lebih jarang (setiap 2 menit)
    setInterval(checkRoomStatus, 120000);
    @endif
</script>
@endpush

<style>
    .animate-fadeIn {
        animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .tabular-nums { font-variant-numeric: tabular-nums; }

    /* Fullscreen cross-browser */
    :-webkit-full-screen { width: 100%; height: 100%; }
    :-moz-full-screen { width: 100%; height: 100%; }
    :-ms-fullscreen { width: 100%; height: 100%; }
    :fullscreen { width: 100%; height: 100%; }
</style>
@endsection
