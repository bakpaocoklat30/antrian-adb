<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pastikan user terautentikasi, aktif, dan memiliki salah satu role yang diizinkan.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akun Anda telah dinonaktifkan.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun dinonaktifkan! Silakan hubungi Koordinator/Super Admin.',
            ]);
        }

        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak untuk role Anda.'], 403);
            }

            abort(403, 'Akses Terlarang!');
        }

        return $next($request);
    }
}
