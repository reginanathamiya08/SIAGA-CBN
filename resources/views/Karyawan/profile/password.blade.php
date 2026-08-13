@extends('karyawan.sidebar')

@section('title', 'Ubah Password')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-black text-[#1E3A5F]">Pengaturan Keamanan</h1>
        <p class="text-sm text-gray-500 mt-1">Perbarui password Anda secara berkala untuk menjaga keamanan akun.</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 md:p-10 relative overflow-hidden">
        {{-- Dekorasi Latar Belakang --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>

        <form action="{{ route('karyawan.password.update') }}" method="POST" class="relative z-10 space-y-6">
            @csrf
            @method('PUT')

            <!-- Password Sekarang -->
            <div class="space-y-2">
                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">
                    Password Saat Ini
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#1E3A5F] transition-colors">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                    </div>
                    <input type="password" name="current_password" required
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold text-[#1E3A5F] focus:bg-white focus:border-[#1E3A5F] focus:ring-4 focus:ring-blue-100 outline-none transition-all placeholder:text-gray-300"
                           placeholder="••••••••">
                </div>
                @error('current_password')
                    <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password Baru -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">
                        Password Baru
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" name="password" required
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold text-[#1E3A5F] focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all placeholder:text-gray-300"
                               placeholder="Minimal 6 karakter">
                    </div>
                    @error('password')
                        <p class="text-[10px] font-bold text-red-500 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">
                        Ulangi Password Baru
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <input type="password" name="password_confirmation" required
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold text-[#1E3A5F] focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition-all placeholder:text-gray-300"
                               placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>

            <!-- Warning Note -->
            <div class="bg-amber-50 rounded-2xl p-4 flex gap-3 border border-amber-100">
                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-500 shrink-0"></i>
                <p class="text-[10px] font-bold text-amber-700 leading-relaxed uppercase tracking-tight">
                    Penting: Jangan bagikan password Anda kepada siapapun. Pastikan password baru Anda sulit ditebak namun mudah diingat.
                </p>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex justify-end">
                <button type="submit"
                        class="px-8 py-4 bg-[#1E3A5F] text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-900 hover:shadow-xl hover:shadow-blue-900/20 active:scale-95 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Footer Note -->
    <p class="text-center text-[10px] font-bold text-gray-400 mt-8 uppercase tracking-widest">
        &copy; {{ date('Y') }} PT Citra Bangun Nagari • Security System
    </p>
</div>
@endsection
