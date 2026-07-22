<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $isGroupInvoice ? 'Group Invoice' : 'Invoice Online' }} - {{ $reservation->reservation_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        /* Mobile responsive tables */
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
                    <span class="text-sm font-semibold text-slate-700">Invoice Online</span>
                </div>
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print / PDF
                </button>
            </div>
        </div>
    </nav>

    {{-- ─── 🔐 Digital Timestamp Card ─── --}}
    @php
        $otsData = $otsStatus['timestamp'] ?? null;
        $otsVerifiedStatus = $otsStatus['status'] ?? 'no_proof';
        $otsTampered = $otsVerifiedStatus === 'tampered';
        $otsConfirmed = $otsVerifiedStatus === 'verified';
        $otsPending = $otsVerifiedStatus === 'pending';
        $hasWarning = ($signatureStatus === 'invalid') || $otsTampered;
        $allValid = ($signatureStatus === 'valid') && ($otsConfirmed || $otsPending);
    @endphp

    <div class="max-w-4xl mx-auto mt-4 mb-5 px-4 sm:px-6 no-print">
        <div class="{{ $hasWarning ? 'crypto-card-danger' : ($allValid ? 'crypto-card-valid' : 'crypto-card') }} rounded-xl shadow-lg">
            {{-- Header ──}}
            <div class="px-4 pt-3.5 pb-3 flex items-center justify-between border-b {{ $hasWarning ? 'border-red-500/10' : 'border-white/5' }}">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full {{ $otsConfirmed ? 'bg-emerald-500/15' : ($otsPending ? 'bg-amber-500/15' : ($otsTampered ? 'bg-red-500/15' : 'bg-slate-500/15')) }} flex items-center justify-center shrink-0">
                        @if($otsConfirmed)
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        @elseif($otsPending)
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @elseif($otsTampered)
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        @else
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest {{ $otsConfirmed ? 'text-emerald-300/70' : ($otsPending ? 'text-amber-300/70' : ($otsTampered ? 'text-red-300/70' : 'text-slate-400/70')) }}">🔐 Digital Timestamp</p>
                        <p class="text-sm font-semibold {{ $otsConfirmed ? 'text-emerald-200' : ($otsPending ? 'text-amber-200' : ($otsTampered ? 'text-red-200' : 'text-slate-300')) }}">
                            @if($otsConfirmed)
                                Verified on Bitcoin Blockchain
                            @elseif($otsPending)
                                Pending Blockchain Confirmation
                            @elseif($otsTampered)
                                Document Integrity Compromised!
                            @else
                                Not Yet Timestamped
                            @endif
                        </p>
                    </div>
                </div>
                @if($otsConfirmed)
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-300 text-[10px] font-semibold tracking-wide border border-emerald-500/20">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 glow-dot"></span>
                        CONFIRMED
                    </span>
                @elseif($otsPending)
                    <span class="px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-300 text-[10px] font-semibold tracking-wide border border-amber-500/20">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 mr-1"></span>
                        PENDING
                    </span>
                @elseif($otsTampered)
                    <span class="px-2.5 py-1 rounded-full bg-red-500/15 text-red-300 text-[10px] font-semibold tracking-wide border border-red-500/20">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-400 mr-1 glow-dot-warn"></span>
                        TAMPERED
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-slate-500/15 text-slate-400 text-[10px] font-semibold tracking-wide border border-slate-500/20">
                        UNAVAILABLE
                    </span>
                @endif
            </div>

            {{-- Body ──}}
            <div class="p-4 space-y-3">
                {{-- Status & SHA-256 --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 crypto-grid">
                    {{-- Status --}}
                    <div class="flex items-start gap-2.5 p-2.5 rounded-lg {{ $otsConfirmed ? 'bg-emerald-950/20' : ($otsPending ? 'bg-amber-950/20' : ($otsTampered ? 'bg-red-950/20' : 'bg-slate-800/30')) }}">
                        <div class="mt-0.5 shrink-0">
                            @if($otsConfirmed)
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @elseif($otsPending)
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @else
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-medium {{ $otsConfirmed ? 'text-emerald-200' : ($otsPending ? 'text-amber-200' : ($otsTampered ? 'text-red-200' : 'text-slate-300')) }}">Status</span>
                            </div>
                            <p class="text-[11px] {{ $otsConfirmed ? 'text-emerald-300/60' : ($otsPending ? 'text-amber-300/60' : ($otsTampered ? 'text-red-300/60' : 'text-slate-400/60')) }} mt-0.5 leading-relaxed">
                                @if($otsConfirmed)
                                    Timestamp telah dikonfirmasi di Bitcoin Blockchain.
                                @elseif($otsPending)
                                    Proof telah dibuat, menunggu konfirmasi blockchain.
                                @elseif($otsTampered)
                                    Data telah berubah sejak di-timestamp!
                                @else
                                    Invoice ini belum di-timestamp.
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- SHA-256 --}}
                    @if($otsData || $otsStatus['status'] !== 'no_proof')
                    <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-slate-800/30">
                        <div class="mt-0.5 shrink-0">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-medium text-slate-300">SHA-256</span>
                            <div class="hash-text text-[10px] text-slate-400/70 mt-0.5 truncate select-all">{{ $otsData['sha256'] ?? ($otsStatus['timestamp']['sha256'] ?? '-') }}</div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Detail Grid --}}
                @if($otsData || ($otsConfirmed || $otsPending))
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 crypto-grid">
                    {{-- Blockchain --}}
                    <div class="p-2.5 rounded-lg bg-slate-800/20">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Blockchain</span>
                        </div>
                        <p class="text-[11px] text-slate-300 font-medium">Bitcoin</p>
                    </div>

                    {{-- Calendar --}}
                    <div class="p-2.5 rounded-lg bg-slate-800/20">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Calendar</span>
                        </div>
                        <p class="text-[11px] text-slate-300/80 truncate" title="{{ $otsData['calendar'] ?? ($invoiceTimestamp?->calendar ?? 'OpenTimestamps') }}">{{ $otsData['calendar'] ?? ($invoiceTimestamp?->calendar ?? 'OpenTimestamps') }}</p>
                    </div>

                    {{-- Timestamp --}}
                    @if($otsData && $otsData['timestamped_at'])
                    <div class="p-2.5 rounded-lg bg-slate-800/20">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Timestamp</span>
                        </div>
                        <p class="text-[11px] text-slate-300">{{ \Carbon\Carbon::parse($otsData['timestamped_at'])->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</p>
                    </div>
                    @endif

                    {{-- Bitcoin Block --}}
                    @if($otsData && $otsData['bitcoin_block'])
                    <div class="p-2.5 rounded-lg bg-slate-800/20">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Bitcoin Block</span>
                        </div>
                        <p class="text-[11px] text-slate-300 font-mono">{{ number_format($otsData['bitcoin_block']) }}</p>
                    </div>
                    @endif

                    {{-- Bitcoin TX --}}
                    @if($otsData && $otsData['bitcoin_txid'])
                    <div class="p-2.5 rounded-lg bg-slate-800/20">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Bitcoin TX</span>
                        </div>
                        <a href="https://www.blockchain.com/btc/tx/{{ $otsData['bitcoin_txid'] }}" target="_blank" rel="noopener noreferrer" class="text-[11px] text-indigo-400 hover:text-indigo-300 truncate block transition">
                            {{ substr($otsData['bitcoin_txid'], 0, 20) }}...
                            <svg class="w-2.5 h-2.5 inline-block ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Actions --}}
                @if($otsConfirmed || $otsPending)
                <div class="pt-2.5 border-t {{ $hasWarning ? 'border-red-500/10' : 'border-white/5' }}">
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Download OTS --}}
                        @if($invoiceTimestamp && $invoiceTimestamp->ots_file)
                        <a href="{{ route('invoice.ots.download', $reservation->reservation_number) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-300 hover:bg-indigo-500/20 text-[10px] font-medium transition border border-indigo-500/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Download Proof (.ots)
                        </a>
                        @endif

                        {{-- Verify on Blockchain --}}
                        @if($otsData && $otsData['bitcoin_txid'])
                        <a href="https://www.blockchain.com/btc/tx/{{ $otsData['bitcoin_txid'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700/30 text-slate-300 hover:bg-slate-700/50 text-[10px] font-medium transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            Verify on Blockchain
                        </a>
                        @endif

                        {{-- View Proof JSON --}}
                        <a href="{{ route('invoice.ots-proof', $reservation->reservation_number) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700/30 text-slate-300 hover:bg-slate-700/50 text-[10px] font-medium transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            View Proof
                        </a>

                        {{-- Revision --}}
                        @if($invoiceTimestamp)
                        <span class="text-[10px] text-slate-500 ml-auto">Revision {{ $invoiceTimestamp->revision }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Invoice Content -->
    <div class="max-w-4xl mx-auto mb-12 px-4 sm:px-6">
        <div class="bg-white rounded-xl invoice-card print:shadow-none print:rounded-none print:!p-2 print-compact p-6 sm:p-8">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-slate-200 pb-6 mb-8 invoice-header">
                <div>
                    @php $hotel = \App\Models\HotelSetting::first(); @endphp
                    @if($hotel->logo_path)
                        <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-10 mb-2.5">
                    @endif
                    <h1 class="text-2xl font-bold text-slate-900">{{ strtoupper($hotel->hotel_name ?? 'DYNAMIC PMS V.2') }}</h1>
                    @if($hotel->address)<p class="text-sm text-slate-400 mt-1">{{ $hotel->address }}</p>@endif
                    @if($hotel->phone || $hotel->email)
                        <p class="text-sm text-slate-400">
                            @if($hotel->phone){{ $hotel->phone }}@endif
                            @if($hotel->phone && $hotel->email) &middot; @endif
                            @if($hotel->email){{ $hotel->email }}@endif
                        </p>
                    @endif
                    @if($hotel->website)<p class="text-sm text-slate-400">{{ $hotel->website }}</p>@endif
                </div>
                <div class="text-right shrink-0 invoice-header-right">
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ $isGroupInvoice ? 'GROUP INVOICE' : 'INVOICE' }}</h2>
                    <div class="mt-2 space-y-1">
                        @if($isGroupInvoice)
                            <p class="text-sm text-slate-500"><span class="text-slate-400">Group No.</span> {{ $reservation->booking_group_id }}</p>
                            <p class="text-sm text-slate-500"><span class="text-slate-400">Jumlah Kamar</span> {{ $reservations->count() }}</p>
                        @else
                            <p class="text-sm text-slate-500"><span class="text-slate-400">No.</span> {{ $reservation->reservation_number }}</p>
                        @endif
                        <p class="text-sm text-slate-500"><span class="text-slate-400">Date</span> {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 detail-grid">
            <div class="border border-slate-200 rounded-lg p-5">
                <h3 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2 mb-3 uppercase tracking-wide">Info Tamu</h3>
                @php
                    // ── Helper masking sesuai UU PDP ──
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
                    <tr><td class="text-slate-400 w-1/3 py-1">Name</td><td class="text-slate-700">: {{ $reservation->guest->guest_name ?? '-' }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-1">ID No.</td><td class="text-slate-700">: {{ maskIdNumber($reservation->guest->id_number ?? '') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-1">Phone</td><td class="text-slate-700">: {{ maskPhone($reservation->guest->phone ?? '') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-1">Email</td><td class="text-slate-700">: {{ maskEmail($reservation->guest->email ?? '') }}</td></tr>
                    <tr><td class="text-slate-400 w-1/3 py-1">Address</td><td class="text-slate-700">: {{ $reservation->guest->address ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="border border-slate-200 rounded-lg p-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest pb-2 mb-3 border-b border-slate-100">{{ $isGroupInvoice ? 'Group Info Menginap' : 'Room Information' }}</h3>
                <table class="w-full text-sm">
                    @if($isGroupInvoice)
                        <tr><td class="text-slate-400 w-1/3 py-1">Check-in</td><td class="text-slate-700">: {{ $reservation->check_in->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Check-out</td><td class="text-slate-700">: {{ $reservation->check_out->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Durasi</td><td class="text-slate-700">: {{ $reservation->nights }} night(s)</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Total Kamar</td><td class="text-slate-700">: {{ $reservations->count() }} kamar</td></tr>
                    @else
                        <tr><td class="text-slate-400 w-1/3 py-1">Room Type</td><td class="text-slate-700">: {{ $reservation->room->roomType->name ?? $reservation->room->room_type_name ?? '-' }}</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Room No.</td><td class="text-slate-700">: {{ $reservation->room->room_number ?? '-' }}</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Check-in</td><td class="text-slate-700">: {{ $reservation->check_in->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Check-out</td><td class="text-slate-700">: {{ $reservation->check_out->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-slate-400 w-1/3 py-1">Nights</td><td class="text-slate-700">: {{ $reservation->nights }} night(s)</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <div class="responsive-table">
        <table class="w-full border-collapse mb-6 text-sm">
            <thead>
                <tr class="bg-slate-800 text-slate-100">
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Description</th>
                    <th class="p-3 text-center font-medium text-xs uppercase tracking-wider">Room</th>
                    <th class="p-3 text-center font-medium text-xs uppercase tracking-wider">Duration</th>
                    <th class="p-3 text-right font-medium text-xs uppercase tracking-wider">Rate/Night</th>
                    <th class="p-3 text-right font-medium text-xs uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody>
                @if($isGroupInvoice)
                    @foreach($reservations as $idx => $res)
                    <tr class="{{ $idx % 2 === 0 ? 'bg-slate-50' : '' }} border-b border-slate-100">
                        <td class="p-3 text-slate-700">Room {{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-3 text-center text-slate-600">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-3 text-center text-slate-600">{{ $res->nights }} night(s)</td>
                        <td class="p-3 text-right text-slate-600">Rp {{ number_format($res->total_amount / max(1, $res->nights), 0, ',', '.') }}</td>
                        <td class="p-3 text-right font-semibold text-slate-800">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @else
                <tr class="border-b border-slate-100">
                    <td class="p-3 text-slate-700">Room {{ $reservation->room->room_number ?? '-' }}</td>
                    <td class="p-3 text-center text-slate-600">{{ $reservation->room->room_number ?? '-' }}</td>
                    <td class="p-3 text-center text-slate-600">{{ $reservation->nights }} night(s)</td>
                    <td class="p-3 text-right text-slate-600">Rp {{ number_format($reservation->total_amount / max(1, $reservation->nights), 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-semibold text-slate-800">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        </div>

        @php
            $allServiceCharges = $isGroupInvoice
                ? $reservations->flatMap(fn($r) => $r->serviceCharges)
                : $reservation->serviceCharges;
        @endphp
        @if($allServiceCharges->count() > 0)
        <div class="responsive-table">
        <table class="w-full border-collapse mb-6 text-sm">
            <thead>
                <tr class="bg-slate-800 text-slate-100">
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Other Revenue</th>
                    <th class="p-3 text-center font-medium text-xs uppercase tracking-wider">Date</th>
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Service</th>
                    <th class="p-3 text-center font-medium text-xs uppercase tracking-wider">Qty</th>
                    <th class="p-3 text-right font-medium text-xs uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allServiceCharges as $sc)
                <tr class="border-b border-slate-100">
                    <td class="p-3 text-slate-700">{{ $sc->charge_number }}</td>
                    <td class="p-3 text-center text-slate-600">{{ $sc->charge_date->format('d/m/Y') }}</td>
                    <td class="p-3 text-slate-600">{{ $sc->service_name }}</td>
                    <td class="p-3 text-center text-slate-600">{{ $sc->quantity }} × Rp {{ number_format($sc->amount, 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-medium text-slate-700">Rp {{ number_format($sc->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 font-medium">
                    <td colspan="4" class="p-3 text-right text-slate-500 text-xs">Subtotal Other Revenue</td>
                    <td class="p-3 text-right text-slate-800">Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif

        @php
            $allRestoTransactions = $isGroupInvoice
                ? $reservations->flatMap(fn($r) => $r->restoTransactions)
                : $reservation->restoTransactions;
        @endphp
        @if($allRestoTransactions->count() > 0)
        <div class="responsive-table">
        <table class="w-full border-collapse mb-6 text-sm">
            <thead>
                <tr class="bg-slate-800 text-slate-100">
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Resto</th>
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Transaction</th>
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Date</th>
                    <th class="p-3 text-left font-medium text-xs uppercase tracking-wider">Items</th>
                    <th class="p-3 text-right font-medium text-xs uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allRestoTransactions as $rt)
                <tr class="border-b border-slate-100">
                    <td class="p-3"></td>
                    <td class="p-3 font-mono text-xs text-slate-600">{{ $rt->transaction_number }}</td>
                    <td class="p-3 text-slate-600">{{ $rt->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3 text-slate-600">
                        @if(is_array($rt->items))
                            @foreach($rt->items as $item)
                                {{ $item['name'] ?? $item['menu_name'] ?? 'Item' }} × {{ $item['quantity'] ?? 1 }}@if(!$loop->last), @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-3 text-right font-medium text-slate-700">Rp {{ number_format($rt->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 font-medium">
                    <td colspan="4" class="p-3 text-right text-slate-500 text-xs">Subtotal Resto</td>
                    <td class="p-3 text-right text-slate-800">Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif

        <!-- Summary -->
        <div class="flex justify-end mb-8">
            <table class="w-64 text-sm summary-table">
                @if($isGroupInvoice)
                <tr>
                    <td class="py-2 text-right text-slate-500">Total Kamar</td>
                    <td class="py-2 text-right font-medium text-slate-800">Rp {{ number_format($groupTotal, 0, ',', '.') }}</td>
                </tr>
                @else
                <tr>
                    <td class="py-2 text-right text-slate-500">Subtotal</td>
                    <td class="py-2 text-right font-medium text-slate-800">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($totalServiceCharge > 0)
                <tr>
                    <td class="py-2 text-right text-slate-500">Other Revenue</td>
                    <td class="py-2 text-right font-medium text-slate-800">Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($totalResto > 0)
                <tr>
                    <td class="py-2 text-right text-slate-500">Resto</td>
                    <td class="py-2 text-right font-medium text-slate-800">Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="border-t border-slate-200">
                    <td class="py-2 text-right font-semibold text-slate-900">Grand Total</td>
                    <td class="py-2 text-right font-semibold text-slate-900">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2 text-right text-slate-500">Paid</td>
                    <td class="py-2 text-right font-medium text-emerald-600">Rp {{ number_format($isGroupInvoice ? $groupPaid : $reservation->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-slate-800">
                    <td class="px-3 py-2 text-right font-semibold text-white text-xs uppercase tracking-wider">Balance Due</td>
                    <td class="px-3 py-2 text-right font-bold text-white">Rp {{ number_format(max(0, $grandTotal - ($isGroupInvoice ? $groupPaid : $reservation->paid_amount)), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment History -->
        @if($transactions->count() > 0)
        <div class="mb-8">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-widest pb-3 mb-4 border-b border-slate-200">Payment History</h3>
            <div class="responsive-table">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="p-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Transaction</th>
                        <th class="p-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Date</th>
                        <th class="p-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Type</th>
                        <th class="p-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Method</th>
                        <th class="p-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">Amount</th>
                        <th class="p-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider border-b border-slate-200">
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
                        <td class="p-3 border-b border-slate-100 text-slate-600 font-mono text-xs">{{ $txn->transaction_number }}</td>
                        <td class="p-3 border-b border-slate-100 text-slate-600">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-3 border-b border-slate-100 text-slate-700 capitalize">{{ str_replace('_', ' ', $txn->type) }}</td>
                        <td class="p-3 border-b border-slate-100 text-slate-600">{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                        <td class="p-3 border-b border-slate-100 text-right font-medium text-slate-700">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                        <td class="p-3 border-b border-slate-100 text-center">
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
                <tfoot>
                    <tr class="bg-slate-100 font-semibold">
                        <td colspan="4" class="p-3 text-right text-slate-600 text-xs uppercase tracking-wider">Total Pembayaran</td>
                        <td class="p-3 text-right text-slate-900">Rp {{ number_format($transactions->sum('amount'), 0, ',', '.') }}</td>
                        <td class="p-3"></td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center border-t border-slate-200 pt-6 mt-8">
            <p class="text-base font-medium text-slate-700 mb-2">Thank you for your stay</p>
            <p class="text-sm text-slate-400">This invoice serves as an official payment receipt</p>
            <div class="flex items-center justify-center gap-4 mt-3">
                @if($signatureStatus === 'valid')
                    <span class="inline-flex items-center gap-1 text-[10px] text-indigo-500 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        HMAC-SHA256
                    </span>
                @elseif($signatureStatus === 'invalid')
                    <span class="inline-flex items-center gap-1 text-[10px] text-red-500 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Signature Invalid
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
</body>
</html>
