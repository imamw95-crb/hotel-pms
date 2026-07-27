<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenRouterService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->model = config('services.openrouter.model', 'qwen/qwen3-8b');
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->timeout = (int) config('services.openrouter.timeout', 120);
    }

    /**
     * Parse OTA email content into structured booking data.
     * Returns an array of bookings — single item for 1 room, multiple for multi-room.
     *
     * @param  string  $emailBody    Email body text
     * @param  string  $emailSubject Email subject line
     * @param  string  $otaSource    OTA source (tiket.com, traveloka.com, etc.)
     * @param  string  $extraHint    Optional extra hint appended to prompt (e.g., for retry)
     * @return array[]|null Array of booking arrays, or null on failure
     */
    public function parseBookingEmail(string $emailBody, string $emailSubject = '', string $otaSource = '', string $extraHint = ''): ?array
    {
        $prompt = $this->buildPrompt($emailBody, $emailSubject, $otaSource, $extraHint);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app_name', 'Dynamic PMS V.2'),
            ])
                ->timeout($this->timeout)
                ->retry(2, 100)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 4096,
                ]);

            if (! $response->successful()) {
                Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (! $content) {
                Log::error('OpenRouter returned empty content', [
                    'full_response' => $response->json(),
                    'model' => $this->model,
                    'prompt_length' => strlen($prompt),
                ]);

                return null;
            }

            $parsed = $this->extractJson($content);

            // Always return as array of bookings
            if ($parsed === null) {
                Log::error('OpenRouter extractJson failed', [
                    'content_snippet' => Str::limit($content, 500),
                    'model' => $this->model,
                ]);
                return null;
            }

            // If single object (associative), wrap in array
            if (! isset($parsed[0]) || ! is_array($parsed[0])) {
                $parsed = [$parsed];
            }

            // Log full AI response for debugging (truncated)
            Log::info('OpenRouter AI response parsed', [
                'room_count' => count($parsed),
                'reservation_ids' => collect($parsed)->pluck('reservation_id')->unique()->values()->toArray(),
                'guest_names' => collect($parsed)->pluck('guest_name')->toArray(),
                'room_types' => collect($parsed)->pluck('room_type')->toArray(),
                'checkins' => collect($parsed)->pluck('checkin_date')->toArray(),
                'checkouts' => collect($parsed)->pluck('checkout_date')->toArray(),
                'model' => $this->model,
            ]);

            return $parsed;
        } catch (\Exception $e) {
            Log::error('OpenRouter service exception: '.$e->getMessage(), [
                'exception' => get_class($e),
            ]);

            return null;
        }
    }

    private function buildPrompt(string $emailBody, string $emailSubject, string $otaSource, string $extraHint = ''): string
    {
        // Limit body length to control token usage
        $emailBody = Str::limit($emailBody, 4000);

        $hintSection = $extraHint ? "\n\n{$extraHint}\n" : '';

        return <<<PROMPT
You are a hotel OTA booking parser. Extract booking info from the email below.

CRITICAL: Count the number of rooms FIRST before extracting data.
Look for patterns like:
- "2 Kamar", "2 Room", "2x", "x 2", "2 Kamar Deluxe" → means 2 separate room entries
- Multiple guest names listed → each is a separate room
- "Booking for 2 rooms" / "Pemesanan 2 kamar"
- Room type repeated with quantity (e.g., "Deluxe (2)" or "Deluxe x 2")

Return ONLY valid JSON array — no markdown, no explanation.

Single room example (the array has 1 object):
[{"reservation_id":"HTL-123","guest_name":"Budi Santoso","checkin_date":"2026-06-02","checkout_date":"2026-06-04","room_type":"Deluxe","guest_count":2,"total_price":500000,"payment_method":"tiket.com","payment_date":"2026-06-01","status":"confirmed","ota_source":"{$otaSource}"}]

Multi-room example (the array has 2 objects, same reservation_id, different guest_name):
[
  {"reservation_id":"HTL-456","guest_name":"Siti Rahma","checkin_date":"2026-06-02","checkout_date":"2026-06-04","room_type":"Deluxe","guest_count":2,"total_price":500000,"payment_method":"tiket.com","payment_date":"2026-06-01","status":"confirmed","ota_source":"{$otaSource}"},
  {"reservation_id":"HTL-456","guest_name":"Ahmad Fauzi","checkin_date":"2026-06-02","checkout_date":"2026-06-04","room_type":"Deluxe","guest_count":2,"total_price":500000,"payment_method":"tiket.com","payment_date":"2026-06-01","status":"confirmed","ota_source":"{$otaSource}"}
]

Important: For multi-room bookings, ALWAYS return one JSON object per room.
If the email says "2 rooms" / "2 kamar" / "x 2" / "quantity: 2", you MUST return 2 objects.
If guest names are not listed per room, use the same guest name for all rooms.

Rules:
- ALWAYS return an array, even for 1 room: [{...}]
- status: cancelled|modified|confirmed
- Dates: YYYY-MM-DD format
- reservation_id = OTA booking reference number (same for ALL rooms in one booking)
- guest_name = guest name for THIS room (if one name covers all, repeat same name for each room)
- room_type = room type for THIS room
- guest_count = number of guests for THIS room
- total_price = price for THIS ROOM ONLY (not grand total). If only grand total given, divide by room count.
- payment_method: tiket.com|traveloka.com|ota_payment|bank_transfer|credit_card|cash|"" (empty if unknown)
  * "pay at hotel" / "bayar di hotel" / unpaid → cash
  * OTA already collected (paid, confirmed payment) → OTA source name
- payment_date: YYYY-MM-DD. Default = checkin_date.

{$hintSection}Email Subject: {$emailSubject}

Email Body:
{$emailBody}
PROMPT;
    }

    /**
     * Parse natural language input into structured reservation data (low-token).
     */
    public function parseNaturalLanguage(string $input): ?array
    {
        $today = date('Y-m-d');
        $prompt = <<<PROMPT
Extract hotel reservation from user input into JSON. Today: {$today}. No markdown, JSON only.

{"guest_name":"","checkin_date":"","checkout_date":"","room_type":"","guest_count":1,"total_price":0,"payment_method":"","notes":"","status":"pending"}

Rules: guest_name required. Dates YYYY-MM-DD. "besok"=tomorrow, "lusa"=+2d, "2 malam"=CI+2d. room_type e.g. Deluxe/Superior/Standard. guest_count from "2 orang". total_price from "Rp 500rb"=500000. payment_method=cash|bank_transfer|credit_card|debit_card|tiket.com|traveloka.com|"" (default cash). notes for extra info. status always "pending".

User Input: {$input}
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name', 'Dynamic PMS V.2'),
            ])
                ->timeout($this->timeout)
                ->retry(2, 100)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 1024,
                ]);

            if (! $response->successful()) {
                Log::error('OpenRouter API error (natural language)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (! $content) {
                Log::error('OpenRouter returned empty content (natural language)', [
                    'full_response' => $response->json(),
                ]);

                return null;
            }

            return $this->extractJson($content);
        } catch (\Exception $e) {
            Log::error('OpenRouter natural language exception: '.$e->getMessage(), [
                'exception' => get_class($e),
            ]);

            return null;
        }
    }

    /**
     * Parse natural language into a front office action intent (low-token).
     */
    public function parseAction(string $input): ?array
    {
        $today = date('Y-m-d');
        $prompt = <<<PROMPT
Classify hotel front office request into JSON. Today: {$today}. No markdown, JSON only.

Actions (data fields, 1 example each):
1. checkin {query,card_count:1} → "Check-in Budi 2 kartu"={"action":"checkin","data":{"query":"Budi","card_count":2}}
2. checkout {query,amount:0,payment_method:"cash"} → "Check-out Budi bayar 500k cash"={"action":"checkout","data":{"query":"Budi","amount":500000,"payment_method":"cash"}}
3. payment {query,amount,payment_method:"cash",type:"dp"} → "Bayar DP 300k Budi transfer"={"action":"payment","data":{"query":"Budi","amount":300000,"payment_method":"bank_transfer","type":"dp"}}
4. cancel {query} → "Cancel INV-001"={"action":"cancel","data":{"query":"INV-001"}}
5. change_room {query,new_room_number,reason:"pindah kamar"} → "Pindah Budi ke 205"={"action":"change_room","data":{"query":"Budi","new_room_number":"205"}}
6. search_guest {query} → "Cari Siti"={"action":"search_guest","data":{"query":"Siti"}}
7. deposit_create {query,card_count:2,nominal_per_card:50000,payment_method:"cash"} → "Deposit Budi 2 kartu 50rb"={"action":"deposit_create","data":{"query":"Budi","card_count":2,"nominal_per_card":50000}}
8. deposit_return {query,receipt_number:""} → "Kembalikan deposit Budi"={"action":"deposit_return","data":{"query":"Budi"}}
9. housekeeping {room_number,task_type:"cleaning",priority:"normal",description:""} → "Cleaning kamar 101 urgent"={"action":"housekeeping","data":{"room_number":"101","task_type":"cleaning","priority":"urgent"}}
10. extend_stay {query,additional_nights:1,new_checkout:""} → "Perpanjang Budi 1 malam"={"action":"extend_stay","data":{"query":"Budi","additional_nights":1}}
11. update_rate {query,new_rate} → "Rate Budi jadi 650rb"={"action":"update_rate","data":{"query":"Budi","new_rate":650000}}
12. toggle_breakfast {query} → "Sarapan Budi"={"action":"toggle_breakfast","data":{"query":"Budi"}}

Types: payment_method=cash|bank_transfer|credit_card|debit_card|qris|ewallet|virtual_account. task_type=cleaning|deep_clean|maintenance|inspection|turndown. priority=low|normal|high|urgent. payment type=dp|pelunasan|additional.
General chat or booking request → {"action":"chat","data":{}}. Booking is handled separately, return chat.
"50rb"=50000. query=name/room/reservation#. Default card_count=1, payment_method="cash". amount=0 if not mentioned.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name', 'Dynamic PMS V.2'),
            ])
                ->timeout($this->timeout)
                ->retry(2, 100)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 1024,
                ]);

            if (! $response->successful()) {
                Log::error('OpenRouter API error (parseAction)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (! $content) {
                Log::error('OpenRouter returned empty content (parseAction)', [
                    'full_response' => $response->json(),
                ]);

                return null;
            }

            return $this->extractJson($content);
        } catch (\Exception $e) {
            Log::error('OpenRouter parseAction exception: '.$e->getMessage(), [
                'exception' => get_class($e),
            ]);

            return null;
        }
    }

    /**
     * Extract and decode JSON from AI response.
     * Handles both single objects ({...}) and arrays ([{...}]).
     */
    private function extractJson(string $content): ?array
    {
        // Try direct JSON decode first
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Try to extract JSON from markdown code blocks (array or object)
        if (preg_match('/```(?:json)?\s*(\[.*\]|\{.*\})\s*```/s', $content, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Try to find any JSON array in the content (greedy match from first [ to last ])
        if (preg_match('/\[.*\]/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Try to find any JSON object in the content
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        Log::error('Failed to extract JSON from AI response', ['content' => $content]);

        return null;
    }
}
