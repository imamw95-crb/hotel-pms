<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Tampilkan form setting hotel.
     */
    public function index()
    {
        $setting = HotelSetting::first();

        return view('admin.settings', compact('setting'));
    }

    /**
     * Simpan setting hotel.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'hotel_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:200',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'theme' => 'nullable|in:light,dark,system',
        ]);

        $setting = HotelSetting::first();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $uploadedFile = $request->file('logo');

            if ($uploadedFile && $uploadedFile->isValid()) {
                // Hapus logo lama
                if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                    Storage::disk('public')->delete($setting->logo_path);
                }

                // Simpan logo — fallback manual jika putFileAs gagal
                try {
                    $path = Storage::disk('public')->putFile('logo', $uploadedFile);
                } catch (\Throwable $e) {
                    logger()->warning('Logo putFile failed: '.$e->getMessage());
                    $path = null;
                }

                if (! $path) {
                    // Manual copy via getContent()
                    $ext = $uploadedFile->getClientOriginalExtension() ?: 'png';
                    $filename = Str::random(40).'.'.$ext;
                    $destDir = storage_path('app/public/logo');

                    if (! is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    if (file_put_contents($destDir.'/'.$filename, $uploadedFile->get()) !== false) {
                        $path = 'logo/'.$filename;
                    }
                }

                if ($path) {
                    $setting->logo_path = $path;
                }
            }
        }

        $setting->hotel_name = $validated['hotel_name'];
        $setting->phone = $validated['phone'] ?? null;
        $setting->email = $validated['email'] ?? null;
        $setting->address = $validated['address'] ?? null;
        $setting->website = $validated['website'] ?? null;
        if (isset($validated['theme'])) {
            $setting->theme = $validated['theme'];
        }
        $setting->save();

        // Clear cached settings so dashboard picks up changes immediately
        HotelSetting::forgetCache();

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Setting hotel berhasil diperbarui.',
                'redirect_url' => route('admin.settings'),
            ]);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Setting hotel berhasil diperbarui.');
    }
}
