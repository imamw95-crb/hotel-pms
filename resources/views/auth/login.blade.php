<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $setting->hotel_name }}</title>
    @vite('resources/css/app.css')
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <style>
        /* ── Elegant black background ── */
        .login-bg {
            background: #000000;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(212, 168, 83, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(255, 255, 255, 0.04) 0%, transparent 40%),
                radial-gradient(ellipse at 50% 80%, rgba(212, 168, 83, 0.05) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
            position: relative;
            overflow: hidden;
        }

        /* ── Network canvas ── */
        #networkCanvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* ── Gold shimmer overlay ── */
        .mesh-overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background:
                radial-gradient(ellipse at 15% 30%, rgba(212, 168, 83, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 70%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
        }

        /* ── Login card — dark elegant glass ── */
        .login-card {
            background: rgba(18, 18, 18, 0.92);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(212, 168, 83, 0.15);
            box-shadow:
                0 30px 60px -15px rgba(0, 0, 0, 0.8),
                0 0 0 1px rgba(212, 168, 83, 0.05) inset,
                0 0 40px rgba(212, 168, 83, 0.03);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .login-card:hover {
            border-color: rgba(212, 168, 83, 0.35);
            box-shadow:
                0 30px 60px -15px rgba(0, 0, 0, 0.8),
                0 0 0 1px rgba(212, 168, 83, 0.15) inset,
                0 0 60px rgba(212, 168, 83, 0.06);
            transform: translateY(-2px);
        }

        /* ── Form inputs — dark ── */
        .form-input-custom {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }
        .form-input-custom::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }
        .form-input-custom:hover {
            border-color: rgba(212, 168, 83, 0.25);
            background: rgba(255, 255, 255, 0.06);
        }
        .form-input-custom:focus {
            border-color: rgba(212, 168, 83, 0.5);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(212, 168, 83, 0.08);
            outline: none;
        }
        .form-input-custom.error {
            border-color: rgba(239, 68, 68, 0.5);
        }
        .form-input-custom.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        /* ── Login button — gold elegance ── */
        .btn-login {
            background: linear-gradient(135deg, #c9a84c, #d4a853, #e8c66a);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            color: #1a1a1a;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .btn-login:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 12px 35px -5px rgba(212, 168, 83, 0.5);
        }
        .btn-login:active {
            transform: translateY(0) scale(0.98);
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.6s ease;
        }
        .btn-login:hover::before {
            left: 100%;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1));
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .btn-login:hover::after {
            opacity: 1;
        }

        /* ── Entry animations ── */
        .animate-in {
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
        }
        .animate-in-d1 { animation-delay: 0.1s; }
        .animate-in-d2 { animation-delay: 0.2s; }
        .animate-in-d3 { animation-delay: 0.3s; }
        .animate-in-d4 { animation-delay: 0.4s; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Divider with text ── */
        .divider-text {
            display: flex; align-items: center;
        }
        .divider-text::before,
        .divider-text::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(212, 168, 83, 0.2), transparent);
        }

        /* ── Loading overlay ── */
        .loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .loading-overlay.show {
            display: flex;
            opacity: 1;
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid rgba(212, 168, 83, 0.15);
            border-top-color: #d4a853;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            margin-top: 16px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            letter-spacing: 1px;
            animation: pulseText 1.5s ease-in-out infinite;
        }
        @keyframes pulseText {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* ── Button loading state ── */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        .btn-login.loading .btn-text {
            visibility: hidden;
        }
        .btn-login.loading .btn-loader {
            display: flex;
        }
        .btn-loader {
            display: none;
            position: absolute;
            inset: 0;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .btn-loader .dot {
            width: 6px;
            height: 6px;
            background: #1a1a1a;
            border-radius: 50%;
            animation: dotBounce 1s ease-in-out infinite;
        }
        .btn-loader .dot:nth-child(2) { animation-delay: 0.15s; }
        .btn-loader .dot:nth-child(3) { animation-delay: 0.3s; }
        @keyframes dotBounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .login-card { margin: 1rem; }
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4 font-sans antialiased">

    {{-- Loading overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">MEMVERIFIKASI</div>
    </div>

    {{-- Network animation canvas --}}
    <canvas id="networkCanvas"></canvas>
    <div class="mesh-overlay"></div>

    {{-- Main card --}}
    <div class="login-card relative w-full max-w-md rounded-2xl p-8 sm:p-10 z-10">

        {{-- Logo & Header --}}
        <div class="text-center mb-8 animate-in animate-in-d1">
            @if($setting->logo_path)
                <img src="{{ asset('storage/' . $setting->logo_path) }}"
                     alt="{{ $setting->hotel_name }}"
                     class="h-16 w-auto mx-auto object-contain mb-4">
            @else
                <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-yellow-500 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg shadow-amber-500/20 transition-all duration-500 hover:scale-110 hover:shadow-xl hover:shadow-amber-500/30">
                    <i class="fas fa-hotel text-white text-2xl"></i>
                </div>
            @endif
            <h2 class="text-2xl font-bold text-white transition-all duration-500 hover:text-amber-400">{{ $setting->hotel_name }}</h2>
            <p class="text-amber-400/80 mt-1.5 text-sm tracking-wide">Silakan masuk ke akun Anda</p>
        </div>

        {{-- Error Alert --}}
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-950/30 border border-red-800/30 rounded-xl animate-in animate-in-d1">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-red-300">Login gagal</p>
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-400 mt-1">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" class="animate-in animate-in-d2">
            @csrf

            {{-- Username / Email --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                    <i class="fas fa-user text-amber-400/70 mr-1.5"></i>Username atau Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-amber-400/50">
                        <i class="fas fa-envelope text-sm"></i>
                    </div>
                    <input type="text" name="login" value="{{ old('login') }}"
                           class="form-input-custom w-full pl-10 pr-4 py-2.5 rounded-xl @error('login') error @enderror"
                           placeholder="Masukkan username atau email"
                           required autofocus autocomplete="username">
                </div>
                @error('login')
                    <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                    <i class="fas fa-lock text-amber-400/70 mr-1.5"></i>Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-amber-400/50">
                        <i class="fas fa-key text-sm"></i>
                    </div>
                    <input type="password" name="password" id="password"
                           class="form-input-custom w-full pl-10 pr-10 py-2.5 rounded-xl"
                           placeholder="Masukkan password"
                           required autocomplete="current-password">
                    <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-500 hover:text-amber-400 transition">
                        <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" id="loginBtn" class="btn-login w-full py-2.5 rounded-xl font-semibold text-sm tracking-wider uppercase">
                <span class="btn-text relative z-10 flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </span>
                <span class="btn-loader">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </span>
            </button>
        </form>

        {{-- Footer --}}
        <div class="mt-8 text-center animate-in animate-in-d4">
            <p class="text-xs text-gray-600">
                &copy; 2026 Dynamic PMS v2. All rights reserved.
            </p>
        </div>
    </div>

    {{-- Toggle Password Visibility --}}
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // ── Loading screen on login submit ──
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btn = document.getElementById('loginBtn');
            const overlay = document.getElementById('loadingOverlay');

            form.addEventListener('submit', function(e) {
                // Show button loading
                btn.classList.add('loading');

                // Show overlay after a tiny delay so button animates first
                setTimeout(function() {
                    overlay.classList.add('show');
                }, 200);
            });
        });
    </script>

    {{-- Network animation with mouse interaction --}}
    <script>
        (function() {
            const canvas = document.getElementById('networkCanvas');
            const ctx = canvas.getContext('2d');
            let W, H;
            const nodes = [];
            const NODE_COUNT = 90;
            const CONNECT_DIST = 180;
            const MOUSE_CONNECT_DIST = 300;
            const MOUSE_ATTRACT_DIST = 280;
            const MOUSE_FORCE = 0.18;
            const SPEED = 0.3;

            let mouse = { x: -9999, y: -9999, active: false };

            function resize() {
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }
            window.addEventListener('resize', resize);
            resize();

            // Track mouse on document (canvas has pointer-events:none, so track globally)
            document.addEventListener('mousemove', function(e) {
                mouse.x = e.clientX;
                mouse.y = e.clientY;
                mouse.active = true;
            });
            document.addEventListener('mouseout', function(e) {
                if (!e.relatedTarget || e.relatedTarget === document.documentElement) {
                    mouse.active = false;
                }
            });

            // Create nodes with random positions and velocities
            for (let i = 0; i < NODE_COUNT; i++) {
                nodes.push({
                    x: Math.random() * W,
                    y: Math.random() * H,
                    vx: (Math.random() - 0.5) * SPEED,
                    vy: (Math.random() - 0.5) * SPEED,
                    r: Math.random() * 2 + 1.5
                });
            }

            function animate() {
                ctx.clearRect(0, 0, W, H);

                // Update positions — with mouse attraction
                for (const n of nodes) {
                    // Mouse attraction force
                    if (mouse.active) {
                        const dx = mouse.x - n.x;
                        const dy = mouse.y - n.y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < MOUSE_ATTRACT_DIST && dist > 1) {
                            const force = (1 - dist / MOUSE_ATTRACT_DIST) * MOUSE_FORCE;
                            n.vx += (dx / dist) * force;
                            n.vy += (dy / dist) * force;
                        }
                    }

                    // Apply velocity
                    n.x += n.vx;
                    n.y += n.vy;

                    // Damping
                    n.vx *= 0.99;
                    n.vy *= 0.99;

                    // Keep within bounds with bounce
                    if (n.x < 0) { n.x = 0; n.vx *= -1; }
                    if (n.x > W) { n.x = W; n.vx *= -1; }
                    if (n.y < 0) { n.y = 0; n.vy *= -1; }
                    if (n.y > H) { n.y = H; n.vy *= -1; }
                }

                // ── Draw node-to-node connections ──
                for (let i = 0; i < nodes.length; i++) {
                    for (let j = i + 1; j < nodes.length; j++) {
                        const dx = nodes[i].x - nodes[j].x;
                        const dy = nodes[i].y - nodes[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < CONNECT_DIST) {
                            const alpha = (1 - dist / CONNECT_DIST) * 0.25;
                            ctx.beginPath();
                            ctx.moveTo(nodes[i].x, nodes[i].y);
                            ctx.lineTo(nodes[j].x, nodes[j].y);
                            ctx.strokeStyle = `rgba(212, 168, 83, ${alpha})`;
                            ctx.lineWidth = 0.6;
                            ctx.stroke();
                        }
                    }
                }

                // ── Draw mouse-to-node connections ──
                if (mouse.active) {
                    for (const n of nodes) {
                        const dx = mouse.x - n.x;
                        const dy = mouse.y - n.y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < MOUSE_CONNECT_DIST) {
                            const alpha = (1 - dist / MOUSE_CONNECT_DIST) * 0.4;
                            ctx.beginPath();
                            ctx.moveTo(mouse.x, mouse.y);
                            ctx.lineTo(n.x, n.y);
                            ctx.strokeStyle = `rgba(255, 215, 120, ${alpha})`;
                            ctx.lineWidth = 1;
                            ctx.stroke();
                        }
                    }

                    // ── Mouse glow ring ──
                    const grad = ctx.createRadialGradient(mouse.x, mouse.y, 0, mouse.x, mouse.y, 80);
                    grad.addColorStop(0, 'rgba(212, 168, 83, 0.12)');
                    grad.addColorStop(1, 'rgba(212, 168, 83, 0)');
                    ctx.beginPath();
                    ctx.arc(mouse.x, mouse.y, 80, 0, Math.PI * 2);
                    ctx.fillStyle = grad;
                    ctx.fill();

                    // Mouse center dot
                    ctx.beginPath();
                    ctx.arc(mouse.x, mouse.y, 2.5, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(255, 215, 120, 0.7)';
                    ctx.fill();
                }

                // ── Draw node dots ──
                for (const n of nodes) {
                    ctx.beginPath();
                    ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(212, 168, 83, 0.4)';
                    ctx.fill();
                }

                requestAnimationFrame(animate);
            }

            animate();
        })();
    </script>
</body>
</html>