<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Password - Sistem CBN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        {{-- Logo/Brand --}}
        <div class="text-center mb-10">
            <div class="inline-flex p-4 bg-emerald-600 rounded-3xl shadow-2xl shadow-emerald-900/20 mb-6">
                <i data-lucide="shield-check" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-3xl font-black text-[#1E3A5F] tracking-tight mb-2">Password Baru</h1>
            <p class="text-slate-500 text-sm font-medium">Silakan tentukan password baru yang aman.</p>
        </div>

        <div class="glass-card p-10 rounded-[3rem] shadow-2xl shadow-slate-200/50">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div class="space-y-6">
                    {{-- Email (Read-only/Fixed) --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Konfirmasi Email</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                            <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                                   class="w-full pl-14 pr-6 py-4 bg-slate-100 border-2 border-transparent rounded-2xl outline-none text-sm font-bold text-slate-400">
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Password Baru</label>
                        <div class="relative group">
                            <i data-lucide="lock" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-[#1E3A5F] transition-colors"></i>
                            <input type="password" name="password" required autofocus
                                   class="w-full pl-14 pr-6 py-4 bg-slate-50 border-2 border-transparent focus:border-[#1E3A5F]/20 focus:bg-white rounded-2xl outline-none transition-all text-sm font-bold text-[#1E3A5F]"
                                   placeholder="••••••••">
                        </div>
                        @error('password')
                            <span class="text-red-500 text-[10px] font-bold mt-2 ml-1 block uppercase tracking-tight">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Ulangi Password</label>
                        <div class="relative group">
                            <i data-lucide="refresh-ccw" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-[#1E3A5F] transition-colors"></i>
                            <input type="password" name="password_confirmation" required
                                   class="w-full pl-14 pr-6 py-4 bg-slate-50 border-2 border-transparent focus:border-[#1E3A5F]/20 focus:bg-white rounded-2xl outline-none transition-all text-sm font-bold text-[#1E3A5F]"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-4 bg-[#1E3A5F] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-900/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3">
                        Simpan & Login
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
