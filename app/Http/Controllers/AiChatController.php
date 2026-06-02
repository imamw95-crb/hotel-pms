<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    /**
     * Handle AI chat message.
     */
    public function chat(Request $request, AiChatService $aiChat)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'current_page' => 'nullable|string|max:255',
            'history' => 'nullable|array',
            'history.*.role' => 'string|in:user,assistant',
            'history.*.text' => 'string|max:2000',
        ]);

        $result = $aiChat->chat(
            $validated['message'],
            $validated['current_page'] ?? null,
            $validated['history'] ?? []
        );

        return response()->json($result);
    }
}
