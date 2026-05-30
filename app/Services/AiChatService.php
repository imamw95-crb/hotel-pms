<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    /**
     * Process a chat message and return AI response.
     */
    public function chat(string $message, ?string $currentRoute = null): array
    {
        $today = Carbon::now()->startOfDay();
        $systemContext = $this->buildSystemContext($today);

        $prompt = <<<PROMPT
{$systemContext}

Current page: {$currentRoute}

User message: {$message}

Instructions:
- Answer in Bahasa Indonesia, friendly but professional.
- Use real data from the context above.
- If user asks about availability, give specific room numbers and types.
- If user wants to book, confirm details before proceeding — ask for missing info (guest name, dates, room type).
- If user asks something outside hotel operations, politely redirect.
- Keep answers concise, maximum 3-4 paragraphs.
- Do NOT mention internal functions or technical details.
- Do NOT mention that you are looking at database data — just answer naturally.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => config('app.url'),
                'X-Title'       => config('app.name', 'Hotel PMS'),
            ])
            ->timeout(config('services.openrouter.timeout', 120))
            ->post(config('services.openrouter.base_url', 'https://openrouter.ai/api/v1') . '/chat/completions', [
                'model'    => config('services.openrouter.model', 'qwen/qwen3-8b'),
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens'  => 1024,
            ]);

            if (!$response->successful()) {
                Log::error('AI Chat API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Maaf, terjadi kesalahan koneksi ke AI. Coba lagi nanti.',
                ];
            }

            $content = $response->json('choices.0.message.content');

            if (!$content) {
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
            Log::error('AI Chat exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan sistem. Coba lagi nanti.',
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
        $occupied  = Room::where('status', 'occupied')->count();
        $available = Room::where('status', 'available')->count();
        $cleaning  = Room::where('status', 'cleaning')->count();
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
            ->map(fn($r) => $r->room && $r->guest ? "Kamar {$r->room->room_number} - {$r->guest->guest_name}" : null)
            ->filter()
            ->implode(', ');

        $dueOutText = $dueOutList ?: 'Tidak ada';

        // Active guests
        $activeGuestsList = Reservation::where('status', 'checked_in')
            ->with('room', 'guest')
            ->get()
            ->map(fn($r) => $r->guest && $r->room ? "{$r->guest->guest_name} (Kamar {$r->room->room_number})" : null)
            ->filter()
            ->implode('; ');

        $activeGuestsText = $activeGuestsList ?: 'Tidak ada';

        // Occupancy rate
        $occupancyRate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;

        // All rooms list
        $roomsList = Room::orderBy('room_number')->get()
            ->map(fn($r) => "{$r->room_number} ({$r->room_type_name}) - {$r->status}" . ($r->price_per_night > 0 ? " - Rp " . number_format($r->price_per_night, 0, ',', '.') : ''))
            ->implode("\n");

        return <<<CONTEXT
You are an AI assistant for Hotel PMS (Property Management System). You help hotel staff with daily operations.

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

All Rooms:
{$roomsList}
CONTEXT;
    }
}
