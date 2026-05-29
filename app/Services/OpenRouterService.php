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
        $this->apiKey   = config('services.openrouter.api_key');
        $this->model    = config('services.openrouter.model', 'qwen/qwen3-8b');
        $this->baseUrl  = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->timeout  = (int) config('services.openrouter.timeout', 120);
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
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => config('app.url'),
                'X-Title'       => config('app_name', 'Hotel PMS'),
            ])
            ->timeout($this->timeout)
            ->retry(2, 100)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
                'max_tokens'  => 2048,
            ]);

            if (!$response->successful()) {
                Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (!$content) {
                Log::error('OpenRouter returned empty content', [
                    'full_response' => $response->json(),
                    'model'         => $this->model,
                    'prompt_length' => strlen($prompt),
                ]);
                return null;
            }

            return $this->extractJson($content);
        } catch (\Exception $e) {
            Log::error('OpenRouter service exception: ' . $e->getMessage(), [
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
     * Parse natural language input into structured reservation data.
     * AI directly creates the reservation in the system.
     *
     * @return array{guest_name: string, checkin_date: string, checkout_date: string, room_type: string, guest_count: int, total_price: float, payment_method: string, notes: string, status: string}|null
     */
    public function parseNaturalLanguage(string $input): ?array
    {
        $today = date('Y-m-d');
        $prompt = <<<PROMPT
You are a hotel reservation assistant. Convert the user's natural language request into a structured reservation JSON.

Today's date is {$today}.

Return ONLY valid JSON. Do not explain anything. No markdown. No additional text.

Format must EXACTLY follow this JSON structure:
{
  "guest_name": "",
  "checkin_date": "",
  "checkout_date": "",
  "room_type": "",
  "guest_count": 1,
  "total_price": 0,
  "payment_method": "",
  "notes": "",
  "status": "pending"
}

Rules:
- guest_name: the guest full name (required)
- checkin_date: check-in date in YYYY-MM-DD format (required). If user says "today", use {$today}. If "tomorrow", use tomorrow's date. If "besok", use tomorrow. If "lusa", use day after tomorrow.
- checkout_date: check-out date in YYYY-MM-DD format (required). If user says "2 malam" or "2 nights", calculate from checkin_date.
- room_type: the room type/name (e.g. "Deluxe", "Superior", "Standard", "Suite"). Extract from input. If not mentioned, set "".
- guest_count: number of guests (integer, default 1). Look for "2 orang", "3 tamu", "2 guests", etc.
- total_price: the total price in number (0 if not mentioned). Look for "Rp", "harga", "total", "tarif".
- payment_method: one of these exact values:
  * "cash" — if cash / bayar di hotel / pay at hotel (DEFAULT if not specified)
  * "bank_transfer" — if bank transfer / transfer
  * "credit_card" — if credit card / kartu kredit
  * "debit_card" — if debit card / kartu debit
  * "tiket.com" — if via tiket.com
  * "traveloka.com" — if via traveloka.com
  * "" — if not mentioned at all
- notes: any additional notes from the input (e.g. "request lantai atas", "extra bed"). If none, set "".
- status: always "pending" (for new reservations via AI)

User Input:
{$input}
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => config('app.url'),
                'X-Title'       => config('app.name', 'Hotel PMS'),
            ])
            ->timeout($this->timeout)
            ->retry(2, 100)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
                'max_tokens'  => 1024,
            ]);

            if (!$response->successful()) {
                Log::error('OpenRouter API error (natural language)', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (!$content) {
                Log::error('OpenRouter returned empty content (natural language)', [
                    'full_response' => $response->json(),
                ]);
                return null;
            }

            return $this->extractJson($content);
        } catch (\Exception $e) {
            Log::error('OpenRouter natural language exception: ' . $e->getMessage(), [
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
