<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.exists'   => 'Alamat email tidak terdaftar dalam sistem.',
        ]);

        try {
            $status = Password::broker()->sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', 'Kami telah mengirimkan link reset password ke email Anda!');
            }

            if ($status === Password::RESET_THROTTLED) {
                return back()->withErrors(['email' => 'Permintaan terlalu cepat. Silakan tunggu 1 menit sebelum mencoba lagi.']);
            }

            return back()->withErrors(['email' => __($status)]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error sending reset link email: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Gagal mengirim email: ' . $e->getMessage()]);
        }
    }
}
