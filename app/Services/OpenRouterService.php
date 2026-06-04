<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     *
     * @return array{reservation_id: string, guest_name: string, checkin_date: string, checkout_date: string, room_type: string, guest_count: int, total_price: float, payment_method: string, payment_date: string, status: string, ota_source: string}|null
     */
    public function parseBookingEmail(string $emailBody, string $emailSubject = '', string $otaSource = ''): ?array
    {
        $prompt = $this->buildPrompt($emailBody, $emailSubject, $otaSource);

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
                    'max_tokens' => 2048,
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

            return $this->extractJson($content);
        } catch (\Exception $e) {
            Log::error('OpenRouter service exception: '.$e->getMessage(), [
                'exception' => get_class($e),
            ]);

            return null;
        }
    }

    private function buildPrompt(string $emailBody, string $emailSubject, string $otaSource): string
    {
        return <<<PROMPT
You are a hotel OTA booking parser.

Extract booking and payment information from the following OTA email.

Return ONLY valid JSON. Do not explain anything. No markdown. No additional text.

Format must EXACTLY follow this JSON structure:
{
  "reservation_id": "",
  "guest_name": "",
  "checkin_date": "",
  "checkout_date": "",
  "room_type": "",
  "guest_count": 1,
  "total_price": 0,
  "payment_method": "",
  "payment_date": "",
  "status": "confirmed",
  "ota_source": "{$otaSource}"
}

Rules:
- If booking cancelled: status = "cancelled"
- If booking modified: status = "modified"
- If new booking: status = "confirmed"
- checkin_date and checkout_date must be in YYYY-MM-DD format
- guest_count must be an integer (default 1)
- reservation_id is the OTA booking reference number (booking confirmation number)
- total_price: the TOTAL price/amount from the email (number only, no currency symbol). Search VERY carefully for: "total", "grand total", "amount", "harga total", "total bayar", "total harga", "room rate", "price", "harga", "Rp", "IDR", "Rp.", "biaya", "tagihan", "nilai". Look in tables, bullet points, and key-value pairs. Extract the FINAL total (not per-night rate). If the amount uses dots as thousand separators (e.g. "500.000" = 500000), convert correctly. If not found, set 0.
- payment_method: how the guest/OTA pays. Use one of these exact values:
  * "tiket.com" — if paid via tiket.com
  * "traveloka.com" — if paid via traveloka.com
  * "ota_payment" — if paid via OTA but specific method not mentioned
  * "bank_transfer" — if bank transfer
  * "credit_card" — if credit card
  * "debit_card" — if debit card
  * "cash" — if cash / bayar di hotel / pay at hotel
  * "" — if not mentioned at all
- payment_date: the date payment was made or will be made (YYYY-MM-DD format). If not found, use checkin_date.
- IMPORTANT: If the email says "pay at hotel", "bayar di hotel", "payment at check-in", "unpaid", or similar — set payment_method to "cash" (guest pays at hotel)
- IMPORTANT: If the email says the OTA already collected payment (e.g. "paid", "confirmed payment", "payment received") — set payment_method to the OTA source (tiket.com, traveloka.com, etc.)
- Output only JSON, no markdown, no explanation, no additional text

Email Subject: {$emailSubject}

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
     */
    private function extractJson(string $content): ?array
    {
        // Try direct JSON decode first
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
            $decoded = json_decode($matches[1], true);
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
