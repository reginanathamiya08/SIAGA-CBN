@extends('karyawan.sidebar')
@section('title', 'Ajukan Lembur')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.lembur.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight ">
            Ajukan Lembur
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            Lembur harus disetujui pimpinan sebelum dilaksanakan
        </p>
    </div>
</header>

<form method="POST" action="{{ route('karyawan.lembur.store') }}">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kiri: Form ──────────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Detail Lembur
            </h3>

            <div class="space-y-5">

                {{-- Tanggal --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                   mb-2">
                        Tanggal Lembur <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal"
                           value="{{ old('tanggal') }}"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 outline-none transition-all
                                  @error('tanggal') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('tanggal')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jam Mulai & Selesai --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                       mb-2">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_mulai"
                               value="{{ old('jam_mulai') }}"
                               id="jam-mulai"
                               oninput="hitungDurasi()"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('jam_mulai') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('jam_mulai')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                       mb-2">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_selesai"
                               value="{{ old('jam_selesai') }}"
                               id="jam-selesai"
                               oninput="hitungDurasi()"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('jam_selesai') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('jam_selesai')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Preview durasi --}}
                <div id="info-durasi"
                     class="hidden p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <i data-lucide="clock" class="w-5 h-5 text-blue-500 shrink-0"></i>
                        <p class="text-xs font-black text-blue-700">
                            Durasi Lembur:
                            <span id="label-durasi" class="text-lg">0j 0m</span>
                        </p>
                    </div>
                </div>

                {{-- Keperluan Lembur --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                   mb-2">
                        Keperluan Lembur <span class="text-red-500">*</span>
                    </label>
                    <textarea name="keterangan" rows="4"
                              placeholder="Jelaskan keperluan lembur secara singkat..."
                              class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                     text-gray-700 placeholder-gray-300 outline-none
                                     transition-all resize-none
                                     @error('keterangan') border-red-400 bg-red-50
                                     @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                     @enderror">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- ── Kanan: Info & Tombol ────────────────────────────────── --}}
    <div class="space-y-5">

        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
            <p class="text-[10px] font-black text-blue-700  mb-3">Ketentuan Lembur</p>
            <ul class="space-y-2 text-[9px] text-blue-600 font-semibold">
                <li>• Pengajuan harus disetujui pimpinan <strong>sebelum</strong> melaksanakan lembur.</li>
                <li>• Lembur yang belum disetujui tidak akan tercatat sebagai lembur resmi.</li>
                <li>• Hanya satu pengajuan per hari yang diperbolehkan.</li>
            </ul>
        </div>

        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                        py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i>
            Kirim Pengajuan
        </button>

        <a href="{{ route('karyawan.lembur.index') }}"
           class="block text-center text-xs font-bold text-gray-400
                  hover:text-red-500 transition-colors">
            Batal
        </a>

    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
function hitungDurasi() {
    var mulai   = document.getElementById('jam-mulai').value;
    var selesai = document.getElementById('jam-selesai').value;
    var info    = document.getElementById('info-durasi');
    var label   = document.getElementById('label-durasi');

    if (!mulai || !selesai) { info.classList.add('hidden'); return; }

    var jm = parseInt(mulai.split(':')[0])   * 60 + parseInt(mulai.split(':')[1]);
    var js = parseInt(selesai.split(':')[0]) * 60 + parseInt(selesai.split(':')[1]);

    // Jika selesai sebelum mulai, berarti melewati tengah malam
    if (js <= jm) js += 24 * 60;

    var total = js - jm;
    var jam   = Math.floor(total / 60);
    var menit = total % 60;

    label.textContent = jam + 'j ' + menit + 'm';
    info.classList.remove('hidden');
    lucide.createIcons();
}
</script>
@endpush