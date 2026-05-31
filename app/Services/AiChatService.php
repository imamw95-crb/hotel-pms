<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\RestoTransaction;
use App\Models\Room;
use App\Models\ServiceCharge;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private ?OpenRouterService $openRouter = null;

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
     * Process a chat message and return AI response.
     * Jika terdeteksi booking, akan langsung dibuatkan reservasi.
     */
    public function chat(string $message, ?string $currentRoute = null, array $history = []): array
    {
        $today = Carbon::now()->startOfDay();

        // ─── Coba deteksi booking dari pesan user ───
        $bookingData = $this->openRouter()->parseNaturalLanguage($message);

        if ($bookingData && ! empty($bookingData['guest_name'])) {
            $hasCheckin = ! empty($bookingData['checkin_date']);
            $hasCheckout = ! empty($bookingData['checkout_date']);
            $hasRoomType = ! empty($bookingData['room_type']);
            $complete = $hasCheckin && $hasCheckout && $hasRoomType;

            if ($complete) {
                // Data lengkap (nama + tanggal + tipe kamar) — langsung buat booking
                return $this->createBooking($bookingData);
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

        // ─── Chat normal via AI ───
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

Instructions:
- Answer in Bahasa Indonesia, friendly but professional.
- Use real data from the context above.
- If user asks about availability, give specific room numbers and types.
- FORMAT: Use simple formatting. Use **bold** for important numbers/statuses. Use emoji icons (✅, 📋, 🏨, 👤, 🛏️, 📅, 💰). Keep paragraphs short (2-3 lines max).

- When user wants to BOOK A ROOM, follow this process:
  1. First ask: guest name, check-in date, check-out date (or number of nights)
  2. Then MUST ask about ROOM TYPE — list available room types from "All Rooms" data above.
     Show the tipe kamar names and prices (e.g. "Deluxe Rp 650.000", "Superior Rp 450.000", etc.)
  3. Do NOT proceed until the user picks a room type.
  4. After user picks a room type, confirm all details before saying "Baik, saya akan buatkan reservasi sekarang".

- If you already have partial booking info (see "Partial booking info" above), only ask for what's still missing. Always prioritize asking for ROOM TYPE if not specified.
- After the booking is created (handled by system), just confirm the result briefly.
- If user asks something outside hotel operations, politely redirect.
- Keep answers concise, maximum 3-4 paragraphs.
- Do NOT mention internal functions or technical details.
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
     * Create a booking from parsed natural language data.
     */
    private function createBooking(array $data): array
    {
        $today = Carbon::now()->startOfDay();

        try {
            // Validate dates
            $checkIn = Carbon::parse($data['checkin_date'])->setTime(14, 0);
            $checkOut = Carbon::parse($data['checkout_date'])->setTime(12, 0);
        } catch (\Exception $e) {
            return [
                'success' => true,
                'message' => 'Maaf, format tanggal tidak valid. Coba lagi dengan format yang benar (contoh: 30 Mei 2026).',
            ];
        }

        if ($checkIn->gte($checkOut)) {
            return [
                'success' => true,
                'message' => 'Maaf, tanggal check-out harus setelah check-in. Silakan periksa kembali tanggalnya.',
            ];
        }

        if ($checkIn->lt($today)) {
            return [
                'success' => true,
                'message' => 'Maaf, tanggal check-in tidak boleh di masa lalu. Silakan pilih tanggal hari ini atau setelahnya.',
            ];
        }

        // Find available room
        $roomId = null;
        $roomTypeName = $data['room_type'] ?? null;

        $availableQuery = Room::where('status', '!=', 'maintenance')
            ->whereNotIn('id', function ($q) use ($checkIn, $checkOut) {
                $q->select('room_id')
                    ->from('reservations')
                    ->whereIn('status', ['pending', 'checked_in'])
                    ->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            });

        if ($roomTypeName) {
            $availableRooms = (clone $availableQuery)
                ->where('room_type_name', $roomTypeName)
                ->orderBy('room_number')
                ->get();

            if ($availableRooms->isNotEmpty()) {
                $roomId = $availableRooms->first()->id;
            }
        }

        // Fallback: any available room
        if (! $roomId) {
            $anyAvailable = (clone $availableQuery)
                ->orderBy('room_number')
                ->first();

            if (! $anyAvailable) {
                return [
                    'success' => true,
                    'message' => 'Maaf, tidak ada kamar tersedia untuk tanggal tersebut. Silakan coba tanggal lain.',
                ];
            }

            $roomId = $anyAvailable->id;
            $roomTypeName = $anyAvailable->room_type_name;
        }

        $room = Room::find($roomId);

        // Create reservation in transaction
        try {
            $reservation = DB::transaction(function () use ($data, $roomId, $checkIn, $checkOut, $room) {
                // Find or create guest
                $guest = Guest::firstOrCreate(
                    ['guest_name' => $data['guest_name']],
                    ['phone' => null, 'email' => null, 'address' => null]
                );

                // Calculate total
                $totalAmount = (float) ($data['total_price'] ?? 0);
                if ($totalAmount <= 0 && $room) {
                    $totalAmount = $room->calculateTotalForRange($checkIn, $checkOut);
                }

                $reservation = Reservation::create([
                    'guest_id' => $guest->id,
                    'room_id' => $roomId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'number_of_cards' => $data['guest_count'] ?? 1,
                    'total_amount' => $totalAmount,
                    'payment_method' => $data['payment_method'] ?: 'cash',
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => ($data['notes'] ? $data['notes'].' ' : '').'(via AI Chat)',
                    'ota_source' => 'ai_chat',
                    'created_by' => auth()->id() ?? 1,
                ]);

                $reservation->load(['guest', 'room']);

                return $reservation;
            });

            $roomInfo = $reservation->room
                ? "Kamar {$reservation->room->room_number} ({$reservation->room->room_type_name})"
                : 'Kamar belum ditentukan';

            $checkInFormatted = $reservation->check_in->format('d M Y');
            $checkOutFormatted = $reservation->check_out->format('d M Y');
            $totalFormatted = 'Rp '.number_format((float) $reservation->total_amount, 0, ',', '.');

            return [
                'success' => true,
                'message' => "✅ **Reservasi berhasil dibuat!**\n\n".
                    "📋 No. Reservasi: `{$reservation->reservation_number}`\n".
                    "👤 Tamu: {$reservation->guest->guest_name}\n".
                    "🛏️ {$roomInfo}\n".
                    "📅 Check-in: {$checkInFormatted} (14:00)\n".
                    "📅 Check-out: {$checkOutFormatted} (12:00)\n".
                    "💰 Total: {$totalFormatted}\n\n".
                    'Status: **Pending** — silakan lanjutkan ke proses check-in saat tamu datang.',
            ];
        } catch (\Exception $e) {
            Log::error('AI Chat booking failed: '.$e->getMessage(), [
                'data' => $data,
            ]);

            return [
                'success' => true,
                'message' => 'Maaf, gagal membuat reservasi karena kesalahan sistem. Silakan coba lagi atau buat manual.',
            ];
        }
    }

    /**
     * Build real-time system context from database.
     */
    private function buildSystemContext(Carbon $today): string
    {
        // Room stats
        $totalRooms = Room::count();
        $occupied = Room::where('status', 'occupied')->count();
        $available = Room::where('status', 'available')->count();
        $cleaning = Room::where('status', 'cleaning')->count();
        $maintenance = Room::where('status', 'maintenance')->count();

        // Today's reservations
        $checkInsToday = Reservation::whereDate('check_in', $today)
            ->whereIn('status', ['pending', 'checked_in'])
            ->count();

        $checkOutsToday = Reservation::whereDate('check_out', $today)
            ->whereIn('status', ['checked_in'])
            ->count();

        // Due out — occupied but check-out today
        $dueOutList = Reservation::whereDate('check_out', $today)
            ->where('status', 'checked_in')
            ->with('room', 'guest')
            ->get()
            ->map(fn ($r) => $r->room && $r->guest ? "Kamar {$r->room->room_number} - {$r->guest->guest_name}" : null)
            ->filter()
            ->implode(', ');

        $dueOutText = $dueOutList ?: 'Tidak ada';

        // Active guests
        $activeGuestsList = Reservation::where('status', 'checked_in')
            ->with('room', 'guest')
            ->get()
            ->map(fn ($r) => $r->guest && $r->room ? "{$r->guest->guest_name} (Kamar {$r->room->room_number})" : null)
            ->filter()
            ->implode('; ');

        $activeGuestsText = $activeGuestsList ?: 'Tidak ada';

        // Occupancy rate
        $occupancyRate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;

        // All rooms list
        $roomsList = Room::orderBy('room_number')->get()
            ->map(fn ($r) => "{$r->room_number} ({$r->room_type_name}) - {$r->status}".($r->price_per_night > 0 ? ' - Rp '.number_format($r->price_per_night, 0, ',', '.') : ''))
            ->implode("\n");

        // ─── REVENUE DATA ────────────────────────────────────────
        $revenueData = $this->buildRevenueContext($today);

        return <<<CONTEXT
You are an AI assistant for Dynamic PMS V.2 (Property Management System). You help hotel staff with daily operations.

TODAY: {$today->format('l, d F Y')}

=== REAL-TIME DATA ===
Total Rooms: {$totalRooms}
Occupied: {$occupied} ({$occupancyRate}%)
Available: {$available}
Cleaning: {$cleaning}
Maintenance: {$maintenance}

Check-in Today: {$checkInsToday} guests
Check-out Today: {$checkOutsToday} guests
Due Out: {$dueOutText}

Active Guests: {$activeGuestsText}

{$revenueData}

All Rooms:
{$roomsList}
CONTEXT;
    }

    /**
     * Build revenue summary context for today.
     */
    private function buildRevenueContext(Carbon $today): string
    {
        // Room revenue — transactions created today linked to reservations
        $roomRevenue = (float) Transaction::whereDate('created_at', $today)
            ->whereHas('reservation')
            ->sum('amount');

        // Resto revenue today
        $restoRevenue = (float) RestoTransaction::whereDate('created_at', $today)
            ->sum('total_amount');

        // Service charge revenue today
        $serviceRevenue = (float) ServiceCharge::whereDate('created_at', $today)
            ->sum('total_amount');

        $totalRevenue = $roomRevenue + $restoRevenue + $serviceRevenue;

        // Counts
        $transactionCount = Transaction::whereDate('created_at', $today)
            ->whereHas('reservation')
            ->count();

        $restoCount = RestoTransaction::whereDate('created_at', $today)->count();
        $serviceCount = ServiceCharge::whereDate('created_at', $today)->count();

        // Payment method breakdown (transactions)
        $paymentMethods = Transaction::whereDate('created_at', $today)
            ->whereHas('reservation')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($t) => "{$t->payment_method}: Rp ".number_format((float) $t->total, 0, ',', '.'))
            ->implode(', ');

        $fmt = fn ($v) => 'Rp '.number_format($v, 0, ',', '.');

        return <<<REVENUE
=== PENDAPATAN HARI INI ===
Total Pendapatan: {$fmt($totalRevenue)}
  - Room Revenue (Reservasi): {$fmt($roomRevenue)} ({$transactionCount} transaksi)
  - Resto Revenue: {$fmt($restoRevenue)} ({$restoCount} transaksi)
  - Service Charge: {$fmt($serviceRevenue)} ({$serviceCount} transaksi)

Metode Pembayaran: {$paymentMethods}
REVENUE;
    }
}
