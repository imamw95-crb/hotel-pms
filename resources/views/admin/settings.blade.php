@extends('layouts.app')

@section('title', 'Setting Hotel')
@section('header', 'Setting Hotel')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <p class="text-sm text-blue-700">
            Setting ini akan ditampilkan di <strong>Invoice</strong>, <strong>Kwitansi</strong>, dan <strong>Night Audit</strong>.
        </p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow" data-ajax="true">
        @csrf

        {{-- Logo --}}
        <div class="p-6 border-b">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-image text-blue-500 mr-1"></i> Logo Hotel
            </label>

            @if($setting->logo_path)
                <div class="mb-4 flex items-center gap-4">
                    <img src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo" class="h-20 w-auto object-contain border rounded-lg p-1 bg-gray-50">
                    <div class="text-sm text-gray-500">
                        <p>Logo saat ini</p>
                        <p class="text-xs">{{ $setting->logo_path }}</p>
                    </div>
                </div>
            @else
                <div class="mb-4 h-20 w-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 text-sm">
                    <i class="fas fa-image mr-1"></i> Belum ada logo
                </div>
            @endif

            <input type="file" name="logo" id="logo" accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            @error('logo')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nama Hotel --}}
        <div class="p-6 border-b">
            <label for="hotel_name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-hotel text-blue-500 mr-1"></i> Nama Hotel
            </label>
            <input type="text" name="hotel_name" id="hotel_name"
                   value="{{ old('hotel_name', $setting->hotel_name) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Nama Hotel" required>
            @error('hotel_name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- No Telepon --}}
        <div class="p-6 border-b">
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-phone text-blue-500 mr-1"></i> No. Telepon
            </label>
            <input type="text" name="phone" id="phone"
                   value="{{ old('phone', $setting->phone) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Contoh: (021) 123-4567">
            @error('phone')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="p-6 border-b">
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-envelope text-blue-500 mr-1"></i> Email
            </label>
            <input type="email" name="email" id="email"
                   value="{{ old('email', $setting->email) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Contoh: info@hotel.com">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat --}}
        <div class="p-6 border-b">
            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i> Alamat
            </label>
            <textarea name="address" id="address" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                      placeholder="Alamat lengkap hotel">{{ old('address', $setting->address) }}</textarea>
            @error('address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Website --}}
        <div class="p-6 border-b">
            <label for="website" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-globe text-blue-500 mr-1"></i> Website
            </label>
            <input type="text" name="website" id="website"
                   value="{{ old('website', $setting->website) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Contoh: www.hotel.com">
            @error('website')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Cutoff Time --}}
        <div class="p-6 border-b">
            <label for="cutoff_time" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-clock text-blue-500 mr-1"></i> Business Date Cutoff Time
            </label>
            <p class="text-xs text-gray-400 mb-3">
                Check-in sebelum jam ini dianggap masuk <strong>business date hari sebelumnya</strong>.
                Default: <code>06:00</code> (standar hotel industry).
            </p>
            <input type="time" name="cutoff_time" id="cutoff_time"
                   value="{{ old('cutoff_time', $setting->cutoff_time ?? '06:00') }}"
                   class="w-40 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            @error('cutoff_time')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tema Aplikasi --}}
        <div class="p-6 border-b">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-palette text-blue-500 mr-1"></i> Tema Aplikasi
            </label>
            <p class="text-xs text-gray-400 mb-4">Pilih tampilan tema untuk seluruh halaman admin.</p>
            <div class="grid grid-cols-3 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="light" class="peer hidden" {{ ($setting->theme ?? 'system') === 'light' ? 'checked' : '' }}>
                    <div class="border-2 border-gray-200 peer-checked:border-blue-500 rounded-xl p-4 text-center hover:border-gray-300 transition">
                        <div class="w-10 h-10 bg-yellow-50 text-yellow-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-sun text-lg"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Terang</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="dark" class="peer hidden" {{ ($setting->theme ?? 'system') === 'dark' ? 'checked' : '' }}>
                    <div class="border-2 border-gray-200 peer-checked:border-blue-500 rounded-xl p-4 text-center hover:border-gray-300 transition">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-moon text-lg"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Gelap</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="system" class="peer hidden" {{ ($setting->theme ?? 'system') === 'system' ? 'checked' : '' }}>
                    <div class="border-2 border-gray-200 peer-checked:border-blue-500 rounded-xl p-4 text-center hover:border-gray-300 transition">
                        <div class="w-10 h-10 bg-gray-100 text-gray-500 rounded-lg flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-desktop text-lg"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Sistem</span>
                    </div>
                </label>
            </div>
            @error('theme')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="p-6 bg-gray-50 rounded-b-lg flex justify-end">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Setting
            </button>
        </div>
    </form>
</div>
@endsection
