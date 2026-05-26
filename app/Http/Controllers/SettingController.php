<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Tampilkan form setting hotel.
     */
    public function index()
    {
        $setting = HotelSetting::get();
        return view('admin.settings', compact('setting'));
    }

    /**
     * Simpan setting hotel.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'hotel_name' => 'required|string|max:100',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:100',
            'address'    => 'nullable|string|max:500',
            'website'    => 'nullable|string|max:200',
            'logo'       => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        $setting = HotelSetting::get();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $path = $request->file('logo')->store('logo', 'public');
            $setting->logo_path = $path;
        }

        $setting->hotel_name = $validated['hotel_name'];
        $setting->phone      = $validated['phone'] ?? null;
        $setting->email      = $validated['email'] ?? null;
        $setting->address    = $validated['address'] ?? null;
        $setting->website    = $validated['website'] ?? null;
        $setting->save();

        return redirect()->route('admin.settings')
            ->with('success', 'Setting hotel berhasil diperbarui.');
    }
}
