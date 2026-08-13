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

        $userRole = strtolower($user->role?->slug ?? '');
        $allowedRoles = array_map('strtolower', $roles);

        // DEBUG LOG
        \Log::info("Role Check for user: " . $user->username, [
            'user_role_slug' => $user->role?->slug,
            'user_role_slug_lower' => $userRole,
            'allowed_roles' => $roles,
            'match' => in_array($userRole, $allowedRoles)
        ]);

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
        }

        return $next($request);
    }
}
