<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem CBN</title>
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
            <div class="inline-flex p-4 bg-[#1E3A5F] rounded-3xl shadow-2xl shadow-blue-900/20 mb-6">
                <i data-lucide="key-round" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-3xl font-black text-[#1E3A5F] tracking-tight mb-2">Lupa Password?</h1>
            <p class="text-slate-500 text-sm font-medium">Masukkan email Anda untuk menerima link reset password.</p>
        </div>

        <div class="glass-card p-10 rounded-[3rem] shadow-2xl shadow-slate-200/50">
            @if (session('status'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-bold rounded-2xl flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Alamat Email</label>
                        <div class="relative group">
                            <i data-lucide="mail" class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-[#1E3A5F] transition-colors"></i>
                            <input type="email" name="email" required
                                   class="w-full pl-14 pr-6 py-4 bg-slate-50 border-2 border-transparent focus:border-[#1E3A5F]/20 focus:bg-white rounded-2xl outline-none transition-all text-sm font-bold text-[#1E3A5F] placeholder:text-slate-300"
                                   placeholder="nama@email.com">
                        </div>
                        @error('email')
                            <span class="text-red-500 text-[10px] font-bold mt-2 ml-1 block uppercase tracking-tight">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full py-4 bg-[#1E3A5F] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-900/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3">
                        Kirim Link Reset
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-slate-100 text-center">
                <a href="{{ route('login') }}" class="text-xs font-black text-[#1E3A5F]/40 hover:text-[#1E3A5F] uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
