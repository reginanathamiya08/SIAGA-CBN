<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem CBN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { cbn: '#1E3A5F' }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo & Judul --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#1E3A5F] rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-[#1E3A5F]   tracking-tight">PT Citra Bangun Nagari</h1>
            <p class="text-gray-400 text-sm mt-1 font-medium">Sistem Informasi Absensi & Penggajian</p>
        </div>

        {{-- Card Login --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">

            <h2 class="text-lg font-black text-[#1E3A5F]   italic mb-6">Masuk ke Sistem</h2>

            {{-- Flash Message --}}
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                {{-- Username --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500   tracking-widest mb-2">
                        Username
                    </label>
                    <input
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder="Masukkan username"
                        autofocus
                        class="w-full px-4 py-3 rounded-xl border text-sm font-semibold text-gray-700
                               placeholder-gray-300 outline-none transition-all
                               {{ $errors->has('username') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white' }}"
                    >
                    @error('username')
                        <p class="mt-1.5 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500   tracking-widest mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password"
                            class="w-full px-4 py-3 rounded-xl border text-sm font-semibold text-gray-700
                                   placeholder-gray-300 outline-none transition-all pr-12
                                   {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white' }}"
                        >
                        <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#1E3A5F]">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 rounded border-gray-300 text-[#1E3A5F] cursor-pointer">
                    <label for="remember" class="text-xs font-semibold text-gray-500 cursor-pointer">
                        Ingat saya
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-[#1E3A5F] hover:bg-red-600 text-white font-black text-sm
                                 tracking-widest py-3.5 rounded-xl transition-all shadow-sm
                               active:scale-95 italic">
                    Masuk
                </button>

            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6 font-medium">
            &copy; {{ date('Y') }} PT Citra Bangun Nagari. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>