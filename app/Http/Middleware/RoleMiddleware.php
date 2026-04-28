<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Cek apakah user yang login memiliki role yang diizinkan.
     *
     * Cara pakai di routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,pimpinan')
     *   ->middleware('role:karyawan_tetap,karyawan_kontrak')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi admin.');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}