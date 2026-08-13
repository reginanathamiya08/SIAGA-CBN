@extends('karyawan.sidebar')
@section('title', 'Ajukan Dinas Luar Kota')

@section('content')

<header class="flex items-center gap-4 mb-4">
    <a href="{{ route('karyawan.dinas-luar.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">Ajukan Dinas Luar Kota</h1>
        <p class="text-gray-500 mt-1 text-sm">Form Penugasan Perjalanan Dinas Resmi — <span class="text-emerald-600 font-bold">PT CBN</span></p>
    </div>
</header>

<form method="POST" action="{{ route('karyawan.dinas-luar.store') }}"
      enctype="multipart/form-data">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Form Utama --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-2 flex items-center gap-2">
                <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                Rincian Perjalanan Dinas
            </h3>

            {{-- Tanggal --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-black text-gray-500 mb-2">
                        Tanggal Berangkat <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" id="tgl-mulai"
                           value="{{ old('tanggal_mulai') }}"
                           min="{{ date('Y-m-d') }}"
                           oninput="hitungHari()" required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold text-gray-700 outline-none transition-all
                                  @error('tanggal_mulai') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white @enderror">
                    @error('tanggal_mulai')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-500 mb-2">
                        Tanggal Kembali <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" id="tgl-selesai"
                           value="{{ old('tanggal_selesai') }}"
                           min="{{ date('Y-m-d') }}"
                           oninput="hitungHari()" required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold text-gray-700 outline-none transition-all
                                  @error('tanggal_selesai') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white @enderror">
                    @error('tanggal_selesai')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Info Hari --}}
            <div id="info-hari" class="hidden p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                <div class="flex items-center gap-3">
                    <i data-lucide="map-pin" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <div>
                        <p class="text-xs font-black text-emerald-800">
                            Durasi Dinas: <span id="label-hari" class="text-lg">0</span> hari
                        </p>
                        <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                            Penugasan resmi kantor — Tidak memotong kuota cuti tahunan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Keterangan / Maksud Tugas --}}
            <div>
                <label class="block text-[11px] font-black text-gray-500 mb-2">
                     Penugasan Dinas <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" rows="4"
                          placeholder="Tuliskan tujuan kota, lokasi/klien, serta agenda pekerjaan dinas..." required
                          class="w-full px-4 py-3 rounded-xl border text-sm font-semibold text-gray-700 placeholder-gray-300 outline-none transition-all resize-none
                                 @error('keterangan') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white @enderror">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Upload Surat Tugas --}}
        <div class="bg-emerald-50 rounded-3xl border border-emerald-200 shadow-sm p-6">
            <h3 class="font-black text-emerald-800 italic text-[11px] mb-2 flex items-center gap-2">
                <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                Upload Surat Tugas Resmi <span class="text-red-500">*</span>
            </h3>
            <p class="text-xs text-emerald-700 font-semibold mb-4">
                Wajib melampirkan berkas Surat Tugas yang sudah ditandatangani Pimpinan / Penanggung Jawab.
            </p>
            <div>
                <label class="block text-[11px] font-black text-gray-500 mb-2">
                    File Surat Tugas <span class="font-normal text-gray-400 normal-case ml-1">(PDF/JPG/PNG, maks 2MB)</span>
                </label>
                <input type="file" name="file_bukti" required accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-4 py-3 rounded-xl border border-emerald-200 bg-white text-sm font-semibold text-gray-700 outline-none focus:border-emerald-400">
                @error('file_bukti')
                    <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </div>

    {{-- Sidebar Info & Action --}}
    <div class="space-y-5">

        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-5">
            <p class="text-[11px] font-black text-emerald-800 mb-3 tracking-tighter uppercase">Ketentuan Dinas Luar Kota</p>
            <ul class="space-y-2 text-[10px] text-emerald-700 font-semibold leading-relaxed">
                <li class="flex gap-2"><span>•</span> <strong>Status Tugas:</strong> Penugasan resmi perjalanan kantor.</li>
                <li class="flex gap-2"><span>•</span> <strong>Kuota Cuti:</strong> Tidak memotong kuota cuti tahunan.</li>
                <li class="flex gap-2"><span>•</span> <strong>Persetujuan:</strong> Memerlukan konfirmasi persetujuan dari Pimpinan.</li>
                <li class="flex gap-2"><span>•</span> <strong>Dokumen:</strong> Wajib mengunggah berkas Surat Tugas yang valid.</li>
            </ul>
        </div>

        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-emerald-600 text-white font-black text-sm
                       py-4 rounded-2xl transition-all shadow-sm active:scale-95 flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i>
            Kirim Pengajuan Dinas
        </button>

        <a href="{{ route('karyawan.dinas-luar.index') }}"
           class="block text-center text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">
            Batal
        </a>

    </div>

</div>

</form>

@endsection

@push('scripts')
<script>
function hitungHari() {
    var mulai   = document.getElementById('tgl-mulai').value;
    var selesai = document.getElementById('tgl-selesai').value;
    var infoHari  = document.getElementById('info-hari');
    var labelHari = document.getElementById('label-hari');

    if (!mulai || !selesai) {
        infoHari.classList.add('hidden');
        return;
    }

    var m = new Date(mulai);
    var s = new Date(selesai);
    if (s < m) {
        infoHari.classList.add('hidden');
        return;
    }

    var diff = Math.round((s - m) / (1000 * 60 * 60 * 24)) + 1;
    labelHari.textContent = diff;
    infoHari.classList.remove('hidden');

    lucide.createIcons();
}
</script>
@endpush
