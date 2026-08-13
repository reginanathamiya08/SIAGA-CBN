<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PT Citra Bangun Nagari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        cbn: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#1E3A5F',
                            600: '#172f4e',
                            700: '#11233c',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Ambient Pastel Glow Background */
        .glow-blob-1 {
            position: absolute;
            top: -10%;
            left: -5%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(219, 234, 254, 0.8) 0%, rgba(255, 255, 255, 0) 70%);
            filter: blur(50px);
            pointer-events: none;
            animation: floatGlow 14s ease-in-out infinite alternate;
        }
        .glow-blob-2 {
            position: absolute;
            bottom: -10%;
            right: -5%;
            width: 45vw;
            height: 45vw;
            background: radial-gradient(circle, rgba(224, 231, 255, 0.7) 0%, rgba(255, 255, 255, 0) 70%);
            filter: blur(50px);
            pointer-events: none;
            animation: floatGlow 12s ease-in-out infinite alternate-reverse;
        }
        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -20px) scale(1.05); }
            100% { transform: translate(-20px, 30px) scale(0.95); }
        }

        /* Clean White Card with Soft Shadow */
        .light-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 25px 50px -15px rgba(30, 58, 95, 0.12), 0 0 0 1px rgba(241, 245, 249, 0.8);
        }

        /* Custom Input Focus */
        .custom-input:focus-within {
            box-shadow: 0 0 0 3.5px rgba(30, 58, 95, 0.12);
            border-color: #1E3A5F;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/40 to-indigo-50/30 text-slate-800 antialiased flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-x-hidden">

    <!-- Ambient Glowing Orbs -->
    <div class="glow-blob-1"></div>
    <div class="glow-blob-2"></div>

    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 rounded-3xl overflow-hidden shadow-2xl shadow-slate-200/90 border border-slate-200/70 relative z-10 my-auto light-card">

        <!-- LEFT SIDE: Hero Banner with PT CBN Office Photo (pt_cbn.jpeg) & Logo (logo_cbn.jpg) -->
        <div class="lg:col-span-5 relative p-8 sm:p-12 text-white flex flex-col justify-between overflow-hidden group">
            
            <!-- Background Image PT CBN with Gradient Overlay -->
            <img src="{{ asset('image/pt_cbn.jpeg') }}" 
                 alt="Kantor PT Citra Bangun Nagari" 
                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
            
            <!-- Blue Dark Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#0f243f] via-[#1E3A5F]/90 to-[#1E3A5F]/80"></div>

            <!-- Top Logo & Company Info -->
            <div class="relative z-10">
                <div class="inline-flex items-center gap-3 bg-white/15 backdrop-blur-md border border-white/20 px-3.5 py-2 rounded-2xl mb-8 shadow-lg">
                    <!-- Logo CBN Image -->
                    <img src="{{ asset('image/logo_cbn.jpg') }}" 
                         alt="Logo PT CBN" 
                         class="w-9 h-9 rounded-xl object-contain bg-white p-1 shadow-md shrink-0">
                    <div>
                        <span class="block text-[10px] font-extrabold text-blue-200 uppercase tracking-widest">PT CITRA BANGUN NAGARI</span>
                        <span class="text-xs font-black text-white tracking-tight">Sistem Informasi HRD</span>
                    </div>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight tracking-tight text-white mb-3 drop-shadow-sm">
                    Manajemen <span class="text-blue-200">Absensi & Penggajian</span>
                </h1>
                <p class="text-blue-100/90 text-xs sm:text-sm leading-relaxed font-medium">
                    Platform layanan kepegawaian terpadu untuk kemudahan presensi harian, pengajuan perizinan, dan akses informasi slip gaji.
                </p>
            </div>

            <!-- Bottom Copyright -->
            <div class="relative z-10 pt-4 border-t border-white/15 flex items-center justify-between text-[11px] text-blue-100/80">
                <span>&copy; {{ date('Y') }} PT Citra Bangun Nagari</span>
                <span class="inline-flex items-center gap-1.5 font-bold text-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> System Online
                </span>
            </div>
        </div>

        <!-- RIGHT SIDE: Login Form Panel with CBN Logo Header -->
        <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-center relative bg-white">

            <div class="max-w-md mx-auto w-full">
                <!-- Header Title with CBN Logo -->
                <div class="mb-8 text-center sm:text-left">
                    <div class="inline-flex items-center gap-3 mb-4">
                        <img src="{{ asset('image/logo_cbn.jpg') }}" 
                             alt="Logo PT CBN" 
                             class="h-12 w-auto object-contain rounded-xl p-1 border border-slate-200 shadow-sm bg-white">
                        <div>
                            <h3 class="text-sm font-black text-[#1E3A5F] tracking-wider uppercase">PT Citra Bangun Nagari</h3>
                            <span class="text-xs font-bold text-slate-400">Padang, Sumatera Barat</span>
                        </div>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Selamat Datang 👋</h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium">Silakan masuk dengan akun terdaftar kamu.</p>
                </div>

                <!-- Alert Messages -->
                @if (session('status'))
                    <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-2xl text-blue-800 text-xs font-semibold flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-semibold flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Alamat Email
                        </label>
                        <div class="relative custom-input rounded-2xl transition-all">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@cbn.test"
                                required
                                autofocus
                                class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 placeholder-slate-400 bg-slate-50/60 outline-none transition-all focus:bg-white"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-500 font-semibold flex items-center gap-1">
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                                Kata Sandi
                            </label>
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#1E3A5F] hover:text-blue-600 transition-colors">
                                Lupa Kata Sandi?
                            </a>
                        </div>
                        <div class="relative custom-input rounded-2xl transition-all">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="••••••••"
                                required
                                class="w-full pl-11 pr-12 py-3.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 placeholder-slate-400 bg-slate-50/60 outline-none transition-all focus:bg-white"
                            >
                            <button type="button" onclick="togglePassword()"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#1E3A5F] p-1.5 rounded-lg transition-colors"
                                    title="Tampilkan / Sembunyikan Password">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" name="remember" id="remember"
                               class="w-4 h-4 rounded-md border-slate-300 text-[#1E3A5F] focus:ring-[#1E3A5F] cursor-pointer">
                        <label for="remember" class="text-xs font-semibold text-slate-600 cursor-pointer select-none">
                            Biarkan saya tetap masuk
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-[#1E3A5F] hover:bg-[#162c48] text-white font-extrabold text-sm tracking-wider py-4 rounded-2xl transition-all shadow-lg shadow-blue-900/15 hover:shadow-blue-900/30 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] flex items-center justify-center gap-2 group">
                        <span>MASUK KE SISTEM</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

            </div>

        </div>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.022 10.022 0 013.682-.763c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m-1.127 1.127L3 3l18 18"/>`;
            } else {
                input.type = 'password';
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
            }
        }
    </script>

</body>
</html>
