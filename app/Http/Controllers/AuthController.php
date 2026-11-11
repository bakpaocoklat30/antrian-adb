<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login() { 
        return view('auth.login'); 
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'], 
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            
            // CEK APAKAH AKUN AKTIF
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun dinonaktifkan! Silakan hubungi Koordinator/Super Admin.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            
            // 🔥 MENGHAPUS MEMORI INTENDED URL 🔥
            // Mencegah petugas ter-redirect ke halaman admin lain akibat cache URL di browser
            $request->session()->forget('url.intended');

            // Pengalihan mutlak berdasarkan Role
            if (Auth::user()->role === 'scanner') {
                return redirect('/admin/scanner');
            } else {
                return redirect('/admin/dashboard');
            }
        }
        
        return back()->withErrors(['email' => 'Email atau Password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout(); 
        $request->session()->invalidate(); 
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}