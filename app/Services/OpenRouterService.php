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
     * @return array{reservation_id: string, guest_name: string, checkin_date: string, checkout_date: string, room_type: string, guest_count: int, status: string, ota_source: string}|null
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
                'max_tokens'  => 1024,
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
                Log::error('OpenRouter returned empty content');
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

Extract booking information from the following OTA email.

Return ONLY valid JSON. Do not explain anything. No markdown. No additional text.

Format must EXACTLY follow this JSON structure:
{
  "reservation_id": "",
  "guest_name": "",
  "checkin_date": "",
  "checkout_date": "",
  "room_type": "",
  "guest_count": 1,
  "status": "confirmed",
  "ota_source": "{$otaSource}"
}

Rules:
- If booking cancelled: status = "cancelled"
- If booking modified: status = "modified"
- If new booking: status = "confirmed"
- checkin_date and checkout_date must be in YYYY-MM-DD format
- guest_count must be an integer (default 1)
- reservation_id is the OTA booking reference number
- Output only JSON, no markdown, no explanation, no additional text

Email Subject: {$emailSubject}

Email Body:
{$emailBody}
PROMPT;
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
