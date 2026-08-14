<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role?->slug ?? '');
        }

        return view('auth.login');
    }

    /**
     * Proses login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email atau NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Cari user berdasarkan email atau NIP secara presisi
        $input = trim($request->email);
        $user = User::where(function($q) use ($input) {
                    $q->where('email', $input)
                      ->orWhere('nip', $input);
                })
                ->where('is_active', true)
                ->first();

        // Cek user ada dan password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email/NIP atau password salah, atau akun tidak aktif.']);
        }

        // Login manual - tidak bergantung pada getAuthIdentifierName()
       Auth::login($user, false);
        $request->session()->regenerate();

        return $this->redirectByRole($user->role?->slug ?? '');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }

    /**
     * Redirect berdasarkan role user.
     */
    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin'             => redirect()->route('admin.dashboard'),
            'pimpinan'          => redirect()->route('pimpinan.dashboard'),
            'karyawan_tetap',
            'karyawan_kontrak'  => redirect()->route('karyawan.dashboard'),
            default             => redirect('/'),
        };
    }
}
