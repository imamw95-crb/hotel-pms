<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Group Invoice - {{ $reservations->first()->reservation_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } }
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 8px; padding: 0; margin: 0; }
            .print-compact { padding: 0 !important; margin: 0 !important; }
            .print-compact .p-2\.5 { padding: 3px 4px !important; }
            .print-compact .p-3\.5 { padding: 4px 6px !important; }
            .print-compact .p-2 { padding: 2px 4px !important; }
            .print-compact .py-1\.5 { padding-top: 1px !important; padding-bottom: 1px !important; }
            .print-compact .py-0\.5 { padding-top: 0px !important; padding-bottom: 0px !important; }
            .print-compact .mb-5 { margin-bottom: 3px !important; }
            .print-compact .mb-6 { margin-bottom: 4px !important; }
            .print-compact .pb-5 { padding-bottom: 3px !important; }
            .print-compact .pb-2 { padding-bottom: 1px !important; }
            .print-compact .gap-4 { gap: 3px !important; }
            .print-compact .mt-6 { margin-top: 4px !important; }
            .print-compact .pt-4 { padding-top: 3px !important; }
            .print-compact .border-b { border-bottom-width: 0.5px !important; }
            .print-compact table { font-size: 7px !important; }
            .print-compact th { font-size: 6.5px !important; padding: 2px 4px !important; }
            .print-compact td { padding: 1px 4px !important; }
            .print-compact .w-56 { width: 180px !important; }
            .print-compact h1 { font-size: 13px !important; }
            .print-compact h2 { font-size: 11px !important; }
            .print-compact .h-10 { height: 24px !important; }
            .print-compact .mb-2\.5 { margin-bottom: 2px !important; }
            .print-compact .text-sm { font-size: 7px !important; }
            .print-compact .text-xs { font-size: 6.5px !important; }
            .print-compact .text-\[11px\] { font-size: 6.5px !important; }
            .print-compact .text-\[10px\] { font-size: 6px !important; }
            .print-compact .leading-relaxed { line-height: 1.2 !important; }
            .print-compact .tracking-wide { letter-spacing: 0.3px !important; }
            .print-compact .tracking-wider { letter-spacing: 0.3px !important; }
            .print-compact .rounded-xl { border-radius: 0 !important; }
            .print-compact .rounded-lg { border-radius: 0 !important; }
            .print-compact .border { border-width: 0.5px !important; }
            .print-compact .shadow-sm, .print-compact .shadow { box-shadow: none !important; }
            .print-compact .gap-3 { gap: 2px !important; }
            .print-compact img.h-10 { margin-bottom: 1px !important; }
            .print-compact .sign-section { margin-top: 8px !important; padding-top: 5px !important; }
            .print-compact .sign-line { margin-top: 15px !important; }
            .print-compact .qr-section { margin: 3px 0 !important; }
        }
        .crypto-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border: 1px solid rgba(148, 163, 184, 0.15);
        }
        .crypto-card-valid {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border: 1px solid rgba(99, 102, 241, 0.25);
        }
        .crypto-card-danger {
            background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 100%);
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .glow-dot {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }
        .glow-dot-warn {
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
        }
        .hash-text {
            font-family: 'SF Mono', 'Cascadia Code', 'Courier New', monospace;
            font-size: 10px;
            letter-spacing: 0.3px;
        }
        .invoice-card {
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
        }
        @media (max-width: 640px) {
            .responsive-table { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .responsive-table table { min-width: 520px; }
            .invoice-header { flex-direction: column !important; gap: 0.75rem; }
            .invoice-header-right { text-align: left !important; }
            .detail-grid { grid-template-columns: 1fr !important; }
            .summary-table { width: 100% !important; }
            .crypto-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body class="bg-slate-50 antialiased">
    @php $firstReservation = $reservations->first(); @endphp

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 no-print sticky top-0 z-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Group Invoice</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reservations.show', $firstReservation) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-200 transition shadow-sm no-print">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali
                    </a>
                    <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print / PDF
                    </button>
                </div>
            </div>
        </div>
    </nav>
    {{-- ─── Security Status Card ─── --}}
    @php
        $allValid = ($signatureStatus === 'valid') && (!isset($otsStatus) || $otsStatus['status'] === 'verified' || $otsStatus['status'] === 'no_proof');
        $hasWarning = ($signatureStatus === 'invalid') || (isset($otsStatus) && $otsStatus['status'] === 'tampered');
        $otsVerified = isset($otsStatus) && $otsStatus['status'] === 'verified';
        $otsTampered = isset($otsStatus) && $otsStatus['status'] === 'tampered';
    @endphp

    <div class="max-w-4xl mx-auto mt-4 mb-5 px-4 sm:px-6 no-print">
        <div class="{{ $hasWarning ? 'crypto-card-danger' : ($allValid ? 'crypto-card-valid' : 'crypto-card') }} rounded-xl shadow-lg">
            <div class="px-4 pt-3.5 pb-3 flex items-center justify-between border-b {{ $hasWarning ? 'border-red-500/10' : 'border-white/5' }}">
                <div class="flex items-center gap-3">
                    @if($hasWarning)
                        <div class="w-7 h-7 rounded-full bg-red-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-red-300/70">Security Status</p>
                            <p class="text-sm font-semibold text-red-200">Document integrity compromised</p>
                        </div>
                    @elseif($allValid)
                        <div class="w-7 h-7 rounded-full bg-indigo-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-300/70">Security Status</p>
                            <p class="text-sm font-semibold text-indigo-200">Document verified & secured</p>
                        </div>
                    @else
                        <div class="w-7 h-7 rounded-full bg-indigo-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-300/70">Security Status</p>
                            <p class="text-sm font-semibold text-indigo-200">Information</p>
                        </div>
                    @endif
                </div>
                @if($hasWarning)
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500 glow-dot-warn"></div>
                @elseif($allValid)
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-400 glow-dot"></div>
                @endif
            </div>
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 crypto-grid">
                    {{-- HMAC --}}
                    <div class="flex items-start gap-2.5 p-2.5 rounded-lg {{ $hasWarning ? 'bg-red-950/20' : ($signatureStatus === 'valid' ? 'bg-indigo-950/30' : 'bg-slate-800/30') }}">
                        <div class="mt-0.5 shrink-0">
                            @if($signatureStatus === 'valid')
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @elseif($signatureStatus === 'invalid')
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            @else
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-medium {{ $signatureStatus === 'valid' ? 'text-indigo-200' : ($signatureStatus === 'invalid' ? 'text-red-200' : 'text-slate-300') }}">Digital Signature</span>
                                @if($signatureStatus === 'valid')
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-500/15 text-indigo-300 font-medium tracking-wide">HMAC-SHA256</span>
                                @endif
                            </div>
                            <p class="text-[11px] {{ $signatureStatus === 'valid' ? 'text-indigo-300/60' : ($signatureStatus === 'invalid' ? 'text-red-300/60' : 'text-slate-400/60') }} mt-0.5 leading-relaxed">
                                @if($signatureStatus === 'valid')
                                    Signature matches — document has not been altered.
                                @elseif($signatureStatus === 'invalid')
                                    Signature mismatch — document has been modified!
                                @else
                                    Not yet signed.
                                @endif
                            </p>
                            @if($signatureStatus === 'valid' && $firstReservation->invoice_signature)
                                <div class="hash-text text-indigo-300/35 mt-1 truncate">{{ substr($firstReservation->invoice_signature, 0, 24) }}...</div>
                            @endif
                        </div>
                    </div>
                    {{-- OTS --}}
                    <div class="flex items-start gap-2.5 p-2.5 rounded-lg {{ $otsTampered ? 'bg-red-950/20' : ($otsVerified ? 'bg-indigo-950/30' : 'bg-slate-800/30') }}">
                        <div class="mt-0.5 shrink-0">
                            @if($otsVerified)
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            @elseif($otsTampered)
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                            @else
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-medium {{ $otsVerified ? 'text-indigo-200' : ($otsTampered ? 'text-red-200' : 'text-slate-300') }}">Blockchain Proof</span>
                                @if($otsVerified)
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-500/15 text-indigo-300 font-medium tracking-wide">OpenTimestamps</span>
                                @endif
                            </div>
                            <p class="text-[11px] {{ $otsVerified ? 'text-indigo-300/60' : ($otsTampered ? 'text-red-300/60' : 'text-slate-400/60') }} mt-0.5 leading-relaxed">
                                @if($otsVerified)
                                    Registered on {{ \Carbon\Carbon::parse($otsStatus['timestamped_at'])->format('d F Y H:i') }}.
                                @elseif($otsTampered)
                                    Data has changed since blockchain timestamp!
                                @else
                                    Not yet registered on blockchain.
                                @endif
                            </p>
                            @if($otsVerified)
                                <a href="{{ route('invoice.ots-proof', $firstReservation->reservation_number) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-400/70 hover:text-indigo-300 mt-1 transition">
                                    View proof
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @if($otsVerified && isset($otsStatus['timestamped_at']))
                <div class="pt-2.5 border-t {{ $hasWarning ? 'border-red-500/10' : 'border-white/5' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-indigo-300/40">SHA-256:{{ \Carbon\Carbon::parse($otsStatus['timestamped_at'])->format('YmdHis') }}</span>
                        <span class="text-[10px] text-indigo-300/30 font-mono">{{ \Carbon\Carbon::parse($otsStatus['timestamped_at'])->format('d M Y H:i:s') }} WIB</span>
                    </div>
                    @if(isset($otsStatus['hash']))
                        <div class="hash-text text-[10px] text-indigo-300/25 mt-0.5 truncate">{{ $otsStatus['hash'] }}</div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Invoice Content -->
    <div class="max-w-4xl mx-auto mb-8 px-4 sm:px-6">
        <div class="bg-white rounded-xl invoice-card print:shadow-none print:rounded-none print:!p-2 print-compact">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-slate-200 pb-5 mb-6 invoice-header">
                <div>
                    @php $hotel = \App\Models\HotelSetting::first(); @endphp
                    @if($hotel->logo_path)
                        <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-10 mb-2.5">
                    @endif
                    <h1 class="text-xl font-bold text-slate-900">{{ strtoupper($hotel->hotel_name ?? 'DYNAMIC PMS V.2') }}</h1>
                    @if($hotel->address)<p class="text-xs text-slate-400 mt-0.5">{{ $hotel->address }}</p>@endif
                    @if($hotel->phone || $hotel->email)
                        <p class="text-xs text-slate-400">
                            @if($hotel->phone){{ $hotel->phone }}@endif
                            @if($hotel->phone && $hotel->email) &middot; @endif
                            @if($hotel->email){{ $hotel->email }}@endif
                        </p>
                    @endif
                    @if($hotel->website)<p class="text-xs text-slate-400">{{ $hotel->website }}</p>@endif
                </div>
                <div class="text-right shrink-0 invoice-header-right">
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">GROUP INVOICE</h2>
                    <div class="mt-1 space-y-0.5">
                        <p class="text-xs text-slate-500"><span class="text-slate-400">No. Group</span> {{ $firstReservation->booking_group_id }}</p>
                        <p class="text-xs text-slate-500"><span class="text-slate-400">Date</span> {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                        <p class="text-xs text-slate-500"><span class="text-slate-400">Rooms</span> {{ $reservations->count() }} kamar</p>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 detail-grid">
            <div class="border border-slate-200 rounded-lg p-3.5">
                <h3 class="text-xs font-bold text-gray-800 border-b border-gray-100 pb-2 mb-2 uppercase tracking-wide">Info Tamu</h3>
                @php
                    function maskIdNumber($val) {
                        if (!$val) return '-';
                        $len = strlen($val);
                        if ($len <= 4) return str_repeat('*', $len);
                        return substr($val, 0, 2) . str_repeat('*', $len - 4) . substr($val, -2);
                    }
                    function maskPhone($val) {
                        if (!$val) return '-';
                        $len = strlen($val);
                        if ($len <= 4) return str_repeat('*', $len);
                        return str_repeat('*', $len - 4) . substr($val, -4);
                    }
                    function maskEmail($val) {
                        if (!$val) return '-';
                        $parts = explode('@', $val);
                        $name = $parts[0] ?? '';
                        $domain = $parts[1] ?? '';
                        if (strlen($name) <= 2) {
                            $masked = substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 1));
                        } else {
                            $masked = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
                        }
                        return $masked . '@' . $domain;
                    }
                @endphp
                <table class="w-full text-sm">
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Name</td><td class="text-slate-700">: {{ $firstReservation->guest->guest_name ?? '-' }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">ID No.</td><td class="text-slate-700">: {{ maskIdNumber($firstReservation->guest->id_number ?? '') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Phone</td><td class="text-slate-700">: {{ maskPhone($firstReservation->guest->phone ?? '') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Email</td><td class="text-slate-700">: {{ maskEmail($firstReservation->guest->email ?? '') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Address</td><td class="text-slate-700">: {{ $firstReservation->guest->address ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="border border-slate-200 rounded-lg p-3.5">
                <h3 class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest pb-2 mb-2.5 border-b border-slate-100">Stay Information</h3>
                <table class="w-full text-sm">
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Check-in</td><td class="text-slate-700">: {{ $firstReservation->check_in->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Check-out</td><td class="text-slate-700">: {{ $firstReservation->check_out->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Duration</td><td class="text-slate-700">: {{ $firstReservation->nights }} night(s)</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-0.5">Total Rooms</td><td class="text-slate-700">: {{ $reservations->count() }} kamar</td></tr>
                </table>
            </div>
            </div>

            <!-- Items Table per Room -->
            <div class="responsive-table">
            <table class="w-full border-collapse mb-5 text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-100">
                        <th class="p-2.5 text-left font-medium text-[11px] uppercase tracking-wider">No.</th>
                        <th class="p-2.5 text-left font-medium text-[11px] uppercase tracking-wider">Reservation</th>
                        <th class="p-2.5 text-left font-medium text-[11px] uppercase tracking-wider">Room</th>
                        <th class="p-2.5 text-left font-medium text-[11px] uppercase tracking-wider">Type</th>
                        <th class="p-2.5 text-left font-medium text-[11px] uppercase tracking-wider">Guest</th>
                        <th class="p-2.5 text-center font-medium text-[11px] uppercase tracking-wider">Nights</th>
                        <th class="p-2.5 text-right font-medium text-[11px] uppercase tracking-wider">Total</th>
                        <th class="p-2.5 text-right font-medium text-[11px] uppercase tracking-wider">Paid</th>
                        <th class="p-2.5 text-right font-medium text-[11px] uppercase tracking-wider">Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $idx => $res)
                    @php
                        $sisa = $res->total_amount - $res->paid_amount;
                        $totalSc = $res->serviceCharges->sum('total_amount');
                        $totalResto = $res->restoTransactions->sum('total_amount');
                        $subTotal = $res->total_amount + $totalSc + $totalResto;
                    @endphp
                    <tr class="border-b border-slate-100 {{ $idx % 2 === 0 ? 'bg-slate-50/50' : '' }}">
                        <td class="p-2.5 text-slate-600">{{ $idx + 1 }}</td>
                        <td class="p-2.5 font-mono text-[11px] text-slate-600">{{ $res->reservation_number }}</td>
                        <td class="p-2.5 text-slate-700">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2.5 text-slate-600">{{ $res->room->room_type_name ?? '-' }}</td>
                        <td class="p-2.5 text-slate-700">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2.5 text-center text-slate-600">{{ $res->nights }}</td>
                        <td class="p-2.5 text-right font-medium text-slate-700">Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                        <td class="p-2.5 text-right text-emerald-600">Rp {{ number_format($res->paid_amount, 0, ',', '.') }}</td>
                        <td class="p-2.5 text-right {{ $sisa > 0 ? 'text-red-600 font-medium' : 'text-slate-400' }}">Rp {{ number_format(max(0, $sisa), 0, ',', '.') }}</td>
                    </tr>
                    @if($totalSc > 0 || $totalResto > 0)
                    <tr class="text-[10px] text-slate-400 border-b border-slate-100">
                        <td></td>
                        <td colspan="4" class="py-1 px-2.5">
                            @if($totalSc > 0) <span class="text-slate-500">Other Revenue:</span> Rp {{ number_format($totalSc, 0, ',', '.') }} @endif
                            @if($totalSc > 0 && $totalResto > 0) <span class="mx-1">|</span> @endif
                            @if($totalResto > 0) <span class="text-slate-500">Resto:</span> Rp {{ number_format($totalResto, 0, ',', '.') }} @endif
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            </div>

            <!-- Summary -->
            <div class="flex justify-end mb-6">
                <table class="w-56 text-sm summary-table">
                    <tr>
                        <td class="py-1.5 text-right text-slate-500">Total Kamar</td>
                        <td class="py-1.5 text-right font-medium text-slate-800">Rp {{ number_format($groupTotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($totalServiceCharge > 0)
                    <tr>
                        <td class="py-1.5 text-right text-slate-500">Other Revenue</td>
                        <td class="py-1.5 text-right font-medium text-slate-800">Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($totalResto > 0)
                    <tr>
                        <td class="py-1.5 text-right text-slate-500">Resto</td>
                        <td class="py-1.5 text-right font-medium text-slate-800">Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="border-t border-slate-200">
                        <td class="py-1.5 text-right font-semibold text-slate-900">Grand Total</td>
                        <td class="py-1.5 text-right font-semibold text-slate-900">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-right text-slate-500">Paid</td>
                        <td class="py-1.5 text-right font-medium text-emerald-600">Rp {{ number_format($groupPaid, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-slate-800">
                        <td class="px-2 py-1.5 text-right font-semibold text-white text-xs uppercase tracking-wider">Balance Due</td>
                        <td class="px-2 py-1.5 text-right font-bold text-white">Rp {{ number_format(max(0, $grandTotal - $groupPaid), 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Payment History -->
            @if($transactions->count() > 0)
            <div class="mb-6">
                <h3 class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest pb-2 mb-3 border-b border-slate-200">Payment History</h3>
                <div class="responsive-table">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="p-2 text-left text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Transaction</th>
                            <th class="p-2 text-left text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Date</th>
                            <th class="p-2 text-left text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Reservation</th>
                            <th class="p-2 text-left text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Type</th>
                            <th class="p-2 text-left text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Method</th>
                            <th class="p-2 text-right text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Amount</th>
                            <th class="p-2 text-center text-[10px] font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                <span class="inline-flex items-center gap-1" title="OpenTimestamps Blockchain Proof">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    OTS
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                        @php $txnOts = $transactionsOts[$txn->id] ?? null; @endphp
                        <tr>
                            <td class="p-2 border-b border-slate-100 text-slate-600 font-mono text-[11px]">{{ $txn->transaction_number }}</td>
                            <td class="p-2 border-b border-slate-100 text-slate-600">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-2 border-b border-slate-100 text-slate-600 font-mono text-[11px]">{{ $txn->reservation->reservation_number ?? '-' }}</td>
                            <td class="p-2 border-b border-slate-100 text-slate-700 capitalize">{{ str_replace('_', ' ', $txn->type) }}</td>
                            <td class="p-2 border-b border-slate-100 text-slate-600">{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                            <td class="p-2 border-b border-slate-100 text-right font-medium text-slate-700">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                            <td class="p-2 border-b border-slate-100 text-center">
                                @if($txnOts && $txnOts['status'] === 'verified')
                                    <span class="inline-flex items-center gap-1 text-indigo-600 text-[10px] font-medium" title="Verified on blockchain {{ \Carbon\Carbon::parse($txnOts['timestamped_at'])->format('d/m/Y H:i') }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        Verified
                                    </span>
                                @elseif($txnOts && $txnOts['status'] === 'tampered')
                                    <span class="inline-flex items-center gap-1 text-red-500 text-[10px] font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                        Modified
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @endif

            <!-- QR Code -->
            <div class="border-t border-slate-200 pt-4 mt-6">
                {{-- QR Code --}}
                @php
                    if ($firstReservation) {
                        if (!$firstReservation->invoice_signature) {
                            $sigService = app(\App\Services\InvoiceSignatureService::class);
                            $firstReservation->invoice_signature = $sigService->generate($firstReservation);
                            $firstReservation->saveQuietly();
                        }
                        $baseUrl = config('app.url');
                        $shortSig = substr($firstReservation->invoice_signature, 0, 16);
                        $invoiceUrl = $baseUrl . '/invoice/' . $firstReservation->reservation_number . '?sig=' . $shortSig;
                    } else {
                        $invoiceUrl = '#';
                    }
                @endphp
                <div class="text-center qr-section">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($invoiceUrl) }}"
                         alt="QR Code"
                         class="inline-block" style="width:60px; height:60px;">
                    <p class="text-[9px] text-slate-400 mt-1">Scan for online invoice</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center border-t border-slate-200 pt-4 mt-4">
                <p class="text-sm font-medium text-slate-700 mb-1">Thank you for your stay</p>
                <p class="text-xs text-slate-400">This invoice serves as an official payment receipt</p>
                <div class="flex items-center justify-center gap-3 mt-2">
                    @if($signatureStatus === 'valid')
                        <span class="inline-flex items-center gap-1 text-[10px] text-indigo-500 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            HMAC-SHA256
                        </span>
                    @endif
                    @if(isset($otsStatus) && $otsStatus['status'] === 'verified')
                        <span class="inline-flex items-center gap-1 text-[10px] text-indigo-500 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            OpenTimestamps
                        </span>
                    @endif
                </div>
                <p class="text-[10px] text-slate-300 mt-2">{{ $hotel->hotel_name ?? 'Dynamic PMS v2' }} &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
