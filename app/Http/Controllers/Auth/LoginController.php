<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    /**
     * Proses login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = [
            'username'  => $request->username,
            'password'  => $request->password,
            'is_active' => true,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user()->role);
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['username' => 'Username atau password salah, atau akun tidak aktif.']);
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