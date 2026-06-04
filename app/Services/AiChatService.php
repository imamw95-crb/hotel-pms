<?php

namespace App\Services;

use App\Models\BookingNotification;
use App\Models\Reservation;
use App\Models\RestoTransaction;
use App\Models\Room;
use App\Models\ServiceCharge;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private ?OpenRouterService $openRouter = null;

    private AiActionService $actionService;

    /** Confirmation keywords (Bahasa Indonesia + English) */
    private const CONFIRM_WORDS = ['ya', 'yes', 'oke', 'ok', 'okay', 'lanjut', 'lanjutkan', 'setuju', 'confirm', 'iya', 'y', 'siap', 'aye'];

    private const REJECT_WORDS = ['tidak', 'no', 'nope', 'batal', 'cancel', 'jangan', 'gak', 'ga', 'nggak', 'enggak', 'batalkan'];

    public function __construct(AiActionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Get or create OpenRouterService instance.
     */
    private function openRouter(): OpenRouterService
    {
        if (! $this->openRouter) {
            $this->openRouter = app(OpenRouterService::class);
        }

        return $this->openRouter;
    }

    /**
     * Check if message is a confirmation or rejection.
     */
    private function isConfirmation(string $message): ?bool
    {
        $msg = trim(strtolower($message));

        // Check exact match or simple words
        if (in_array($msg, self::CONFIRM_WORDS)) {
            return true;
        }
        if (in_array($msg, self::REJECT_WORDS)) {
            return false;
        }

        return null;
    }

    /**
     * Process a chat message and return AI response.
     * Flow: pending action? → booking? → front office action? → AI chat
     */
    public function chat(string $message, ?string $currentRoute = null, array $history = []): array
    {
        $today = Carbon::now()->startOfDay();

        // ─── Step 1: Check pending action (confirmation workflow) ───
        $pendingAction = session('ai_pending_action');
        if ($pendingAction) {
            $confirmed = $this->isConfirmation($message);

            if ($confirmed === true) {
                // User confirmed — execute pending action
                session()->forget('ai_pending_action');

                $result = $this->actionService->execute(array_merge(
                    $pendingAction,
                    ['data' => array_merge($pendingAction['data'] ?? [], ['confirmed' => true])]
                ));

                return $result;
            }

            if ($confirmed === false) {
                // User rejected — cancel pending action
                session()->forget('ai_pending_action');

                return [
                    'success' => true,
                    'message' => 'Baik, aksi dibatalkan. Ada yang bisa saya bantu lain? 😊',
                ];
            }

            // User said something else — treat as new message, clear pending
            session()->forget('ai_pending_action');
        }

        // ─── Step 2: Coba deteksi booking dari pesan user ───
        $bookingData = $this->openRouter()->parseNaturalLanguage($message);

        if ($bookingData && ! empty($bookingData['guest_name'])) {
            $hasCheckin = ! empty($bookingData['checkin_date']);
            $hasCheckout = ! empty($bookingData['checkout_date']);
            $hasRoomType = ! empty($bookingData['room_type']);
            $complete = $hasCheckin && $hasCheckout && $hasRoomType;

            if ($complete) {
                // Data lengkap (nama + tanggal + tipe kamar) — langsung buat booking
                return $this->actionService->execute([
                    'action' => 'create_booking',
                    'data' => $bookingData,
                ]);
            }

            // Data tidak lengkap — AI akan tanya sisanya, beri konteks
            $missing = [];
            if (! $hasRoomType) {
                $missing[] = 'tipe kamar';
            }
            if (! $hasCheckin) {
                $missing[] = 'tanggal check-in';
            }
            if (! $hasCheckout) {
                $missing[] = 'tanggal check-out';
            }
            $missingText = implode(', ', $missing);

            $partialInfo = "User ingin booking untuk {$bookingData['guest_name']}".
                ($bookingData['room_type'] ? ", tipe kamar {$bookingData['room_type']}" : '').
                ($bookingData['guest_count'] > 1 ? ", {$bookingData['guest_count']} orang" : '').
                ". Butuh info: {$missingText}. Jangan buat reservasi sampai semua info lengkap.";
        }

        // ─── Step 3: Coba deteksi front office action ───
        $actionData = $this->openRouter()->parseAction($message);

        if ($actionData && $actionData['action'] !== 'chat') {
            $result = $this->actionService->execute($actionData);

            // If action needs confirmation, store pending action in session
            if (isset($result['needs_confirmation']) && $result['needs_confirmation']) {
                session(['ai_pending_action' => $actionData]);
            }

            return $result;
        }

        // ─── Step 4: Chat normal via AI ───
        $systemContext = $this->buildSystemContext($today);
        $partialContext = isset($partialInfo) ? "\n\nPartial booking info: {$partialInfo}" : '';

        // Build conversation history
        $historyText = '';
        if (! empty($history)) {
            $historyLines = [];
            foreach ($history as $h) {
                $role = $h['role'] === 'user' ? 'User' : 'Asisten';
                $historyLines[] = "{$role}: {$h['text']}";
            }
            $historyText = "\n\n=== PERCAKAPAN SEBELUMNYA ===\n".implode("\n", array_slice($historyLines, 0, -1));
        }

        $prompt = <<<PROMPT
{$systemContext}
{$historyText}

Current page: {$currentRoute}

Pesan user sekarang: {$message}{$partialContext}

Instructions: B.Indonesia, ramah-pro. Gunakan data real. Format: **bold** utk angka penting, emoji minimal ✅📋👤🛏️💰. Maks 3 paragraf singkat.

Booking: tanya nama tamu → tanggal CI/CO → TIPE KAMAR (wajib, tampilkan harga) → konfirmasi. Jangan buat reservasi sampai semua lengkap. Jika ada partial info, tanya yg kurang saja.

Jika tanya notifikasi: sebut jumlah & isi, tanya mau ditandai dibaca.
Jika di luar operasional hotel: tolak halus.
Jangan sebut teknis internal.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.openrouter.api_key'),
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name', 'Dynamic PMS V.2'),
            ])
                ->timeout(config('services.openrouter.timeout', 120))
                ->post(config('services.openrouter.base_url', 'https://openrouter.ai/api/v1').'/chat/completions', [
                    'model' => config('services.openrouter.model', 'qwen/qwen3-8b'),
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1024,
                ]);

            if (! $response->successful()) {
                Log::error('AI Chat API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Maaf, terjadi kesalahan koneksi ke AI. Coba lagi nanti.',
                ];
            }

            $content = $response->json('choices.0.message.content');

            if (! $content) {
                return [
                    'success' => false,
                    'message' => 'Maaf, AI tidak memberikan respons. Coba lagi.',
                ];
            }

            return [
                'success' => true,
                'message' => trim($content),
            ];
        } catch (\Exception $e) {
            Log::error('AI Chat exception: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan sistem. Coba lagi nanti.',
            ];
        }
    }

    /**
     * Build compact real-time system context — low token version.
     */
    private function buildSystemContext(Carbon $today): string
    {
        $stats = [
            'total' => Room::count(),
            'occupied' => Room::where('status', 'occupied')->count(),
            'available' => Room::where('status', 'available')->count(),
            'cleaning' => Room::where('status', 'cleaning')->count(),
            'maintenance' => Room::where('status', 'maintenance')->count(),
        ];
        $stats['occupancy'] = $stats['total'] > 0 ? round(($stats['occupied'] / $stats['total']) * 100) : 0;

        $checkIns = Reservation::whereDate('check_in', $today)->whereIn('status', ['pending', 'checked_in'])->count();
        $checkOuts = Reservation::whereDate('check_out', $today)->where('status', 'checked_in')->count();
        $activeCount = Reservation::where('status', 'checked_in')->count();

        $roomRev = (float) Transaction::whereDate('created_at', $today)->whereHas('reservation')->sum('amount');
        $restoRev = (float) RestoTransaction::whereDate('created_at', $today)->sum('total_amount');
        $serviceRev = (float) ServiceCharge::whereDate('created_at', $today)->sum('total_amount');
        $totalRev = $roomRev + $restoRev + $serviceRev;

        $unread = BookingNotification::unread()->recent(5)->get();
        $notifText = $unread->isNotEmpty()
            ? 'Notif: '.BookingNotification::unread()->count().' baru — '.$unread->map(fn ($n) => $n->message)->implode('; ')
            : 'Notif: tidak ada';

        $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');

        return "Hotel: {$stats['total']} kmr ({$stats['occupied']} isi/{$stats['available']} kosong/{$stats['cleaning']} bersihin/{$stats['maintenance']} rusak, {$stats['occupancy']}% okupansi)."
            ." CI hari ini: {$checkIns}. CO hari ini: {$checkOuts}. Tamu aktif: {$activeCount}."
            ." Revenue: {$fmt($totalRev)} (Room {$fmt($roomRev)}, Resto {$fmt($restoRev)}, SC {$fmt($serviceRev)})."
            ." {$notifText}";
    }
}
