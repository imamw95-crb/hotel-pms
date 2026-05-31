<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * GET /api/api-keys
     * List semua API keys (tanpa token plain)
     */
    public function index()
    {
        $users = User::with(['tokens' => function ($q) {
            $q->select('id', 'tokenable_id', 'name', 'created_at', 'last_used_at');
        }])->whereHas('tokens')->get();

        $keys = $users->map(function ($user) {
            return $user->tokens->map(function ($token) use ($user) {
                return [
                    'id' => $token->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                    'masked_key' => '••••••••••••••••••••••••••••••••',
                ];
            });
        })->flatten(1);

        return response()->json([
            'success' => true,
            'data' => $keys,
        ]);
    }

    /**
     * POST /api/api-keys
     * Generate API key baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:100',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $apiKey = Str::random(48);

        // Hapus token lama dengan nama yang sama
        $user->tokens()->where('name', $validated['name'])->delete();

        // Simpan token baru (hashed)
        $user->tokens()->create([
            'name' => $validated['name'],
            'token' => hash('sha256', $apiKey),
            'abilities' => ['*'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API Key berhasil dibuat.',
            'data' => [
                'api_key' => $apiKey, // Hanya ditampilkan sekali
                'name' => $validated['name'],
                'user' => $user->name,
            ],
        ], 201);
    }

    /**
     * DELETE /api/api-keys/{id}
     * Hapus API key
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $token = $user->tokens()->where('id', $id)->first();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak ditemukan.',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'API Key berhasil dihapus.',
        ]);
    }
}
