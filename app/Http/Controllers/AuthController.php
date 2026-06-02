<?php

namespace App\Http\Controllers;

use App\Models\HotelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        $setting = HotelSetting::get();
        return view('auth.login', compact('setting'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $login, 'password' => $request->input('password')])) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isOwner()) {
                return redirect()->to('/owner/dashboard');
            } elseif ($user->isAdmin()) {
                return redirect()->to('/admin/dashboard');
            } elseif ($user->isHousekeeping()) {
                return redirect()->to('/housekeeping');
            } elseif ($user->isUserManager()) {
                return redirect()->to('/dashboard');
            } else {
                return redirect()->to('/frontoffice/dashboard');
            }
        }

        return back()->withErrors(['login' => 'Username/Email atau password salah']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
