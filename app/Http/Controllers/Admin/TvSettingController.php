<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotelSetting;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TvSettingController extends Controller
{
    /**
     * Tampilkan halaman management TV Welcome Settings.
     */
    public function index()
    {
        $setting = HotelSetting::first();

        // Ambil semua room beserta guest yang sedang check-in
        $rooms = Room::with(['reservations' => function ($q) {
            $q->where('status', 'checked_in');
        }, 'reservations.guest'])
            ->orderBy('room_number')
            ->get();

        return view('admin.tv-settings.index', compact('setting', 'rooms'));
    }

    /**
     * Simpan setting TV Welcome.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_video_url' => 'nullable|string|max:500',
            'tv_refresh_interval' => 'nullable|integer|min:5|max:300',
            'tv_welcome_message' => 'nullable|string|max:200',
            'company_video' => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $setting = HotelSetting::first();

        // Handle video upload
        if ($request->hasFile('company_video')) {
            $uploadedFile = $request->file('company_video');

            if ($uploadedFile && $uploadedFile->isValid()) {
                // Hapus video lama
                if ($setting->company_video_path && Storage::disk('public')->exists($setting->company_video_path)) {
                    Storage::disk('public')->delete($setting->company_video_path);
                }

                // Simpan video
                $path = Storage::disk('public')->putFile('videos', $uploadedFile);

                if ($path) {
                    $setting->company_video_path = $path;
                    // Jika upload video, hapus URL eksternal
                    $setting->company_video_url = null;
                }
            }
        }

        // Jika hanya URL (tanpa upload file)
        if ($request->filled('company_video_url') && ! $request->hasFile('company_video')) {
            $setting->company_video_url = $validated['company_video_url'];
        } elseif (! $request->hasFile('company_video') && ! $request->filled('company_video_url')) {
            // Kosongkan keduanya hanya jika user eksplisit mengirim kosong
            if ($request->has('clear_video')) {
                if ($setting->company_video_path && Storage::disk('public')->exists($setting->company_video_path)) {
                    Storage::disk('public')->delete($setting->company_video_path);
                }
                $setting->company_video_path = null;
                $setting->company_video_url = null;
            }
        }

        // Hapus video jika tombol clear ditekan
        if ($request->has('clear_video_path') && $request->clear_video_path) {
            if ($setting->company_video_path && Storage::disk('public')->exists($setting->company_video_path)) {
                Storage::disk('public')->delete($setting->company_video_path);
            }
            $setting->company_video_path = null;
        }

        if ($request->has('clear_video_url') && $request->clear_video_url) {
            $setting->company_video_url = null;
        }

        if (isset($validated['tv_refresh_interval'])) {
            $setting->tv_refresh_interval = $validated['tv_refresh_interval'];
        }

        if (isset($validated['tv_welcome_message'])) {
            $setting->tv_welcome_message = $validated['tv_welcome_message'];
        }

        $setting->save();
        HotelSetting::forgetCache();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'TV Welcome Settings berhasil diperbarui.',
                'redirect_url' => route('admin.tv-settings'),
            ]);
        }

        return redirect()->route('admin.tv-settings')
            ->with('success', 'TV Welcome Settings berhasil diperbarui.');
    }
}
