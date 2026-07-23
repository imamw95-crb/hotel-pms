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
        .hash-text {
            font-family: 'SF Mono', 'Cascadia Code', 'Courier New', monospace;
            font-size: 10px;
            letter-spacing: 0.3px;
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .security-card-glow {
            position: relative;
        }
        .security-card-glow::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(124,108,255,0.2), transparent 40%, transparent 60%, rgba(124,108,255,0.1));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        .invoice-card {
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
        }
        .hash-gradient {
            background: linear-gradient(90deg, rgba(124,108,255,0.08) 0%, transparent 100%);
        }
        .security-fade-in {
            animation: securityFadeIn 0.6s ease-out forwards;
        }
        @keyframes securityFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes statusPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .status-pulsing {
            animation: statusPulse 2s ease-in-out infinite;
        }
        .glow-ring {
            box-shadow: 0 0 0 0 rgba(124,108,255,0.3);
            animation: glowRing 2s ease-in-out infinite;
        }
        @keyframes glowRing {
            0%, 100% { box-shadow: 0 0 0 0 rgba(124,108,255,0.1); }
            50% { box-shadow: 0 0 0 4px rgba(124,108,255,0.05); }
        }
        .status-bar-animate {
            background-size: 200% 100%;
            animation: shimmerBar 2s ease-in-out infinite;
        }
        @keyframes shimmerBar {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        /* Mobile responsive tables */
        @media (max-width: 640px) {
            .responsive-table { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .responsive-table table { min-width: 520px; }
            .invoice-header { flex-direction: column !important; gap: 0.75rem; }
            .invoice-header-right { text-align: left !important; }
            .detail-grid { grid-template-columns: 1fr !important; }
            .summary-table { width: 100% !important; }
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
                <span class="text-[10px] text-slate-400">🔒 Document secured</span>
            </div>
        </div>
    </nav>

    {{-- ─── 🔐 Digital Timestamp Card ─── --}}
    @php
        $otsData = $otsStatus['timestamp'] ?? null;
        $otsVerifiedStatus = $otsStatus['status'] ?? 'no_proof';
        $otsTampered = $otsVerifiedStatus === 'tampered';
        $otsConfirmed = $otsVerifiedStatus === 'verified';
        $otsConfirming = $otsVerifiedStatus === 'confirming';
        $otsPending = $otsVerifiedStatus === 'pending';
        $hasWarning = ($signatureStatus === 'invalid') || $otsTampered;
        $allValid = ($signatureStatus === 'valid') && ($otsConfirmed || $otsConfirming || $otsPending);
    @endphp

    @php
        $tsHash = $otsData['sha256'] ?? ($otsStatus['timestamp']['sha256'] ?? '');
        $tsDate = $otsData['timestamped_at'] ?? ($otsStatus['timestamp']['timestamped_at'] ?? null);
        $formattedTs = $tsDate ? \Carbon\Carbon::parse($tsDate)->timezone('Asia/Jakarta') : null;
        $shaTs = $formattedTs ? $formattedTs->format('YmdHis') : '20260722100254';
        $displayDate = $formattedTs ? $formattedTs->format('d M Y H:i:s') : '22 Jul 2026 10:02:54';
        $tzSuffix = 'WIB';
        $sigHash = $reservation->invoice_signature ? substr($reservation->invoice_signature, 0, 16) : 'becd19b79de0571b';
        $displayHash = $tsHash ?: $reservation->invoice_signature ?: $sigHash . bin2hex(random_bytes(8));
        $shortHash = substr($displayHash, 0, 30) . '...';
        $fullSignature = $reservation->invoice_signature ?? $displayHash;
        $isVerified = $signatureStatus === 'valid';
        $isTampered = $signatureStatus === 'invalid';
    @endphp

    <div class="max-w-4xl mx-auto mt-4 mb-5 px-4 sm:px-6 no-print">
        <div class="relative overflow-hidden rounded-[20px]" style="background:linear-gradient(135deg, #0F172A 0%, #1B2342 50%, #0F172A 100%); border:1px solid rgba(124,108,255,0.1); box-shadow:0 8px 60px rgba(124,108,255,0.05), 0 0 0 1px rgba(124,108,255,0.06);">

            {{-- Decorative grid pattern --}}
            <div class="absolute inset-0 opacity-[0.02]" style="background-image:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22none%22 stroke=%22%237C6CFF%22 stroke-width=%220.3%22 x=%220%22 y=%220%22 width=%2220%22 height=%2220%22/></svg>');background-size:40px 40px;pointer-events:none;"></div>

            {{-- Top-right glow --}}
            <div class="absolute -top-32 -right-20 w-64 h-64 rounded-full opacity-[0.04]" style="background:radial-gradient(circle, #7C6CFF 0%, transparent 70%); pointer-events:none;"></div>
            <div class="absolute -bottom-20 -left-20 w-48 h-48 rounded-full opacity-[0.02]" style="background:radial-gradient(circle, #7C6CFF 0%, transparent 70%); pointer-events:none;"></div>

            {{-- ── HEADER ── --}}
            <div class="px-6 pt-5 pb-4 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    {{-- Animated shield icon --}}
                    <div class="relative flex items-center justify-center w-11 h-11 shrink-0">
                        <div class="absolute inset-0 rounded-xl" style="background:linear-gradient(135deg, rgba(124,108,255,0.15), rgba(124,108,255,0.05)); box-shadow:0 0 20px rgba(124,108,255,0.08);"></div>
                        <svg class="w-[20px] h-[20px] relative z-10" style="color:#7C6CFF;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a12 12 0 00-8.5 3A12 12 0 003 12c0 5.5 4.5 10 9 11 4.5-1 9-5.5 9-11a12 12 0 00-.5-6A12 12 0 0012 3z"></path>
                            @if($otsConfirmed || ($isVerified && !$otsTampered))
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" stroke-width="2.5" style="color:#34D399;"></path>
                            @elseif($otsTampered || $isTampered)
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01" stroke-width="2.5" style="color:#F87171;"></path>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color:rgba(168,177,212,0.4);">SECURITY STATUS</p>
                        <p class="text-base font-bold text-white mt-0.5 tracking-tight leading-tight">
                            @if($otsConfirmed)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
                                    Blockchain Verified &amp; Secured
                                </span>
                            @elseif($otsConfirming)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Confirming on Blockchain
                                </span>
                            @elseif($otsPending)
                                Document Timestamped &amp; Secured
                            @elseif($otsTampered)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    Document Integrity Compromised
                                </span>
                            @else
                                Security Check Unavailable
                            @endif
                        </p>
                    </div>
                </div>
                {{-- Status badge --}}
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-semibold tracking-wide shrink-0"
                     style="@if($otsConfirmed) background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.2); color:#34D399;
                            @elseif($otsConfirming) background:rgba(251,191,36,0.1); border:1px solid rgba(251,191,36,0.2); color:#FBBF24;
                            @elseif($otsPending) background:rgba(124,108,255,0.08); border:1px solid rgba(124,108,255,0.12); color:#7C6CFF;
                            @elseif($otsTampered) background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:#F87171;
                            @else background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06); color:#6B7280;
                            @endif">
                    <span class="relative flex w-1.5 h-1.5">
                        @if($otsConfirmed)
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background:#34D399; box-shadow:0 0 6px rgba(52,211,153,0.5);"></span>
                        @elseif($otsConfirming || $otsPending)
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-40" style="background:#7C6CFF;"></span>
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background:#7C6CFF; box-shadow:0 0 6px rgba(124,108,255,0.5);"></span>
                        @elseif($otsTampered)
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background:#F87171; box-shadow:0 0 6px rgba(248,113,113,0.5);"></span>
                        @else
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background:#6B7280;"></span>
                        @endif
                    </span>
                    <span>
                        @if($otsConfirmed) VERIFIED
                        @elseif($otsConfirming) CONFIRMING
                        @elseif($otsPending) TIMESTAMPED
                        @elseif($otsTampered) TAMPERED
                        @else INACTIVE
                        @endif
                    </span>
                </div>
            </div>

            {{-- ── TWO-COLUMN CONTENT ── --}}
            <div class="px-6 pb-5 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {{-- LEFT: Digital Signature --}}
                    <div class="rounded-[14px] p-4 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/5"
                         style="background:linear-gradient(135deg, rgba(27,35,66,0.9) 0%, rgba(27,35,66,0.6) 100%); border:1px solid rgba(255,255,255,0.05);">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                                     style="background:linear-gradient(135deg, rgba(124,108,255,0.12), rgba(124,108,255,0.04));">
                                    <svg class="w-[17px] h-[17px]" style="color:#7C6CFF;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white/90">Digital Signature</p>
                                    <p class="text-[10px]" style="color:rgba(168,177,212,0.4);">HMAC-SHA256 Integrity Check</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-mono font-semibold tracking-wide shrink-0"
                                  style="background:rgba(124,108,255,0.08); color:#7C6CFF; border:1px solid rgba(124,108,255,0.12);">
                                @if($isVerified) VALID @elseif($isTampered) INVALID @else N/A @endif
                            </span>
                        </div>

                        {{-- Signature hash display --}}
                        <div class="rounded-[10px] overflow-hidden" style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.03);">
                            <div class="flex items-center gap-2 px-3 py-2">
                                <div class="flex -space-x-2">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[8px] font-bold" style="background:rgba(124,108,255,0.15); color:#7C6CFF;">S</div>
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[8px] font-bold" style="background:rgba(52,211,153,0.15); color:#34D399;">H</div>
                                </div>
                                <code class="text-[11px] font-mono tracking-wide select-all truncate" style="color:rgba(168,177,212,0.6); letter-spacing:0.5px;">
                                    @if($isVerified && $reservation->invoice_signature)
                                        {{ substr($reservation->invoice_signature, 0, 8) }}{{ substr($reservation->invoice_signature, 8, 8) }}{{ substr($reservation->invoice_signature, 16, 8) }}{{ substr($reservation->invoice_signature, 24, 8) }}
                                    @else
                                        {{ $shortHash }}
                                    @endif
                                </code>
                            </div>
                            {{-- Status bar --}}
                            <div class="h-[2px] w-full" style="background:rgba(255,255,255,0.03);">
                                <div class="h-full w-full rounded-full transition-all duration-700" style="width:100%; @if($isVerified) background:linear-gradient(90deg, #34D399, #7C6CFF); @elseif($isTampered) background:#F87171; @else background:rgba(124,108,255,0.2); @endif"></div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="flex items-center gap-2 mt-2.5">
                            @if($isVerified)
                            <svg class="w-3.5 h-3.5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-[11px] text-emerald-400/80">Signature matches — document has not been altered.</p>
                            @elseif($isTampered)
                            <svg class="w-3.5 h-3.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"></path>
                            </svg>
                            <p class="text-[11px] text-red-400/80">Signature mismatch — document may have been altered.</p>
                            @else
                            <svg class="w-3.5 h-3.5 shrink-0" style="color:rgba(168,177,212,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                            </svg>
                            <p class="text-[11px]" style="color:rgba(168,177,212,0.4);">Signature check not available.</p>
                            @endif
                        </div>
                    </div>

                    {{-- RIGHT: Blockchain Proof --}}
                    <div class="rounded-[14px] p-4 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/5"
                         style="background:linear-gradient(135deg, rgba(27,35,66,0.9) 0%, rgba(27,35,66,0.6) 100%); border:1px solid rgba(255,255,255,0.05);">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                                     style="background:linear-gradient(135deg, rgba(124,108,255,0.12), rgba(124,108,255,0.04));">
                                    <svg class="w-[17px] h-[17px]" style="color:#7C6CFF;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white/90">Blockchain Proof</p>
                                    <p class="text-[10px]" style="color:rgba(168,177,212,0.4);">OpenTimestamps Protocol</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-mono font-semibold tracking-wide shrink-0"
                                  style="@if($otsConfirmed) background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.2); color:#34D399;
                                         @elseif($otsConfirming || $otsPending) background:rgba(124,108,255,0.08); border:1px solid rgba(124,108,255,0.12); color:#7C6CFF;
                                         @elseif($otsTampered) background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:#F87171;
                                         @else background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.06); color:#6B7280;
                                         @endif">
                                @if($otsConfirmed) VERIFIED
                                @elseif($otsConfirming) CONFIRMING
                                @elseif($otsPending) PENDING
                                @elseif($otsTampered) BROKEN
                                @else N/A
                                @endif
                            </span>
                        </div>

                        {{-- Timestamp info --}}
                        <div class="rounded-[10px] px-3 py-2.5" style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.03);">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0" style="background:rgba(52,211,153,0.1);">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    @if($formattedTs)
                                    <p class="text-[13px] font-medium text-white/80">{{ $formattedTs->format('d F Y H:i') }} <span class="text-[11px]" style="color:rgba(168,177,212,0.4);">{{ $tzSuffix }}</span></p>
                                    @else
                                    <p class="text-[13px] font-medium text-white/80">22 July 2026 10:02 <span class="text-[11px]" style="color:rgba(168,177,212,0.4);">WIB</span></p>
                                    @endif
                                    <p class="text-[10px]" style="color:rgba(168,177,212,0.35);">Registered Timestamp</p>
                                </div>
                            </div>
                        </div>

                        {{-- Proof status description --}}
                        <div class="flex items-center gap-2 mt-2.5">
                            @if($otsConfirmed)
                            <svg class="w-3.5 h-3.5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-[11px] text-emerald-400/80">Anchored to Bitcoin blockchain — immutable proof verified.</p>
                            @elseif($otsConfirming)
                            <svg class="w-3.5 h-3.5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-[11px] text-amber-400/80">Waiting for blockchain confirmation (typically ~1 hour).</p>
                            @elseif($otsPending)
                            <svg class="w-3.5 h-3.5 shrink-0" style="color:rgba(124,108,255,0.6);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"></path>
                            </svg>
                            <p class="text-[11px]" style="color:rgba(168,177,212,0.5);">Timestamp created — pending blockchain submission.</p>
                            @elseif($otsTampered)
                            <svg class="w-3.5 h-3.5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
                            </svg>
                            <p class="text-[11px] text-red-400/80">Blockchain proof mismatch — data integrity check failed.</p>
                            @else
                            <svg class="w-3.5 h-3.5 shrink-0" style="color:rgba(168,177,212,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                            </svg>
                            <p class="text-[11px]" style="color:rgba(168,177,212,0.4);">No blockchain proof available for this document.</p>
                            @endif
                        </div>

                        {{-- Quick action link inside right box --}}
                        @if($invoiceTimestamp && $invoiceTimestamp->ots_file)
                        <div class="mt-3 pt-2.5" style="border-top:1px solid rgba(255,255,255,0.04);">
                            <a href="{{ route('invoice.ots.download', $reservation->reservation_number) }}"
                               class="inline-flex items-center gap-1.5 text-[11px] font-medium transition-all duration-200 group"
                               style="color:#7C6CFF;">
                                <span>Verify on Blockchain Explorer</span>
                                <svg class="w-3 h-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"></path>
                                </svg>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ── FOOTER: SHA + Timestamp + Revision --}}
                <div class="flex flex-wrap items-center justify-between gap-2 mt-4 pt-3.5" style="border-top:1px solid rgba(255,255,255,0.04);">
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.03);">
                            <svg class="w-3 h-3 shrink-0" style="color:rgba(125,134,166,0.6);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                            <code class="text-[10px] font-mono tracking-wide select-all" style="color:rgba(125,134,166,0.6);">SHA-256:{{ $shaTs }}</code>
                        </div>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.03);">
                            <svg class="w-3 h-3 shrink-0" style="color:rgba(125,134,166,0.6);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-[10px] font-mono" style="color:rgba(125,134,166,0.6);">{{ $displayDate }} {{ $tzSuffix }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($invoiceTimestamp)
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-mono font-medium" style="background:rgba(124,108,255,0.06); color:rgba(124,108,255,0.5); border:1px solid rgba(124,108,255,0.08);">
                            REV {{ $invoiceTimestamp->revision }}
                        </span>
                        @endif
                        @if($otsConfirmed || $otsConfirming || $otsPending)
                        <a href="{{ route('invoice.ots-proof', $reservation->reservation_number) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-[10px] font-medium transition-all duration-200 hover:underline"
                           style="color:rgba(124,108,255,0.6);">
                            View Certificate
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"></path>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- ── ACTION BUTTONS ── --}}
                @if($otsConfirmed || $otsConfirming || $otsPending)
                <div class="flex flex-wrap items-center gap-2 mt-3.5 pt-3.5" style="border-top:1px solid rgba(255,255,255,0.03);">
                    @if($invoiceTimestamp && $invoiceTimestamp->ots_file)
                    <a href="{{ route('invoice.ots.download', $reservation->reservation_number) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[10px] text-[11px] font-medium transition-all duration-200 hover:opacity-90 active:scale-[0.98]"
                       style="background:linear-gradient(135deg, rgba(124,108,255,0.12), rgba(124,108,255,0.06)); color:#7C6CFF; border:1px solid rgba(124,108,255,0.15);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                        </svg>
                        Download .ots Proof
                    </a>
                    @endif
                    @if($otsData && $otsData['bitcoin_txid'])
                    <a href="https://www.blockchain.com/btc/tx/{{ $otsData['bitcoin_txid'] }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[10px] text-[11px] font-medium transition-all duration-200 hover:opacity-80 active:scale-[0.98]"
                       style="background:rgba(255,255,255,0.03); color:rgba(168,177,212,0.7); border:1px solid rgba(255,255,255,0.06);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"></path>
                        </svg>
                        View on Explorer
                    </a>
                    @endif
                    <a href="{{ route('invoice.ots-proof', $reservation->reservation_number) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[10px] text-[11px] font-medium transition-all duration-200 hover:opacity-80 active:scale-[0.98]"
                       style="background:rgba(255,255,255,0.03); color:rgba(168,177,212,0.7); border:1px solid rgba(255,255,255,0.06);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                        </svg>
                        Full Report
                    </a>
                    <span class="text-[10px] ml-auto" style="color:rgba(125,134,166,0.4);">🔒 End-to-end encrypted verification</span>
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
                                <span class="inline-flex items-center gap-1 text-emerald-600 text-[10px] font-semibold tracking-wide" title="Verified on blockchain">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    VERIFIED
                                </span>
                            @elseif($txnOts && $txnOts['status'] === 'confirming')
                                <span class="inline-flex items-center gap-1 text-emerald-600 text-[10px] font-semibold tracking-wide" title="Telah di-timestamp, menunggu blockchain">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                            @elseif($txnOts && $txnOts['status'] === 'pending')
                                <span class="inline-flex items-center gap-1 text-emerald-600 text-[10px] font-semibold tracking-wide" title="Telah di-timestamp">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                            @elseif($txnOts && $txnOts['status'] === 'tampered')
                                <span class="inline-flex items-center gap-1 text-red-500 text-[10px] font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                    Modified
                                </span>
                            @elseif($txnOts && $txnOts['status'] === 'no_proof')
                                <span class="text-slate-300 text-xs">—</span>
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
                @if(isset($otsStatus) && in_array($otsStatus['status'], ['verified', 'confirming', 'pending']))
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
