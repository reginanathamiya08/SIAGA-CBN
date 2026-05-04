@extends('karyawan.sidebar')
@section('title', 'Ajukan Dinas Luar')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.dinas-luar.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight ">
            Ajukan Dinas Luar Kota
        </h1>
        <p class="text-gray-500 mt-1 text-sm italic font-bold  tracking-tighter">Wajib Melampirkan Dokumen SPPD Resmi</p>
    </div>
</header>

<form method="POST" action="{{ route('karyawan.dinas-luar.store') }}"
      enctype="multipart/form-data">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kiri: Form ──────────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Detail Perjalanan Dinas
            </h3>

            <div class="space-y-5">

                {{-- Tujuan --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                   mb-2">
                        Tujuan Kota / Tempat <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="tujuan" value="{{ old('tujuan') }}"
                           placeholder="Contoh: Jakarta, Kantor Pusat Bank Nagari"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 placeholder-gray-300 outline-none transition-all
                                  @error('tujuan') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('tujuan')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Berangkat & Kembali --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                       mb-2">
                            Tanggal Berangkat <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_berangkat"
                               value="{{ old('tanggal_berangkat') }}"
                               min="{{ date('Y-m-d') }}"
                               id="tgl-berangkat"
                               oninput="hitungDurasi()"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('tanggal_berangkat') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('tanggal_berangkat')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                       mb-2">
                            Tanggal Kembali <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_kembali"
                               value="{{ old('tanggal_kembali') }}"
                               min="{{ date('Y-m-d') }}"
                               id="tgl-kembali"
                               oninput="hitungDurasi()"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('tanggal_kembali') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('tanggal_kembali')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Preview durasi (muncul otomatis) --}}
                <div id="info-durasi"
                     class="hidden p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <i data-lucide="calendar-days" class="w-5 h-5 text-blue-500 shrink-0"></i>
                        <p class="text-xs font-black text-blue-700">
                            Durasi Dinas:
                            <span id="label-durasi" class="text-lg">0</span> hari
                        </p>
                    </div>
                </div>

                {{-- Upload Surat Tugas / SPPD --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                   mb-2">
                        Upload Dokumen SPPD (Sudah Diisi) <span class="text-red-500">*</span>
                        <span class="font-normal text-gray-400 normal-case ml-1">
                            (PDF/JPG/PNG/DOCX, maks 2MB)
                        </span>
                    </label>
                    <div class="relative group">
                        <input type="file" name="file_surat_tugas" required
                               accept=".pdf,.jpg,.jpeg,.png,.docx"
                               class="w-full px-4 py-8 rounded-2xl border-2 border-dashed border-gray-200
                                      bg-gray-50 text-xs font-black text-center text-gray-400
                                      outline-none group-hover:border-blue-300 group-hover:bg-blue-50/30 transition-all
                                file:hidden cursor-pointer">
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none group-hover:scale-105 transition-transform">
                            <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-300 mb-2 group-hover:text-blue-400"></i>
                            <p class="text-[10px]  font-black tracking-widest">Klik atau seret dokumen ke sini</p>
                        </div>
                    </div>
                    @error('file_surat_tugas')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- ── Kanan: Info & Tombol ────────────────────────────────── --}}
    <div class="space-y-5">

        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5">
            <p class="text-[10px] font-black text-blue-700  mb-3">Ketentuan</p>
            <ul class="space-y-2 text-[9px] text-blue-600 font-semibold">
                <li>• Pengajuan harus disetujui pimpinan sebelum berangkat.</li>
                <li>• Upload surat tugas jika sudah tersedia.</li>
                <li>• Perjalanan dinas resmi tercatat sebagai izin di absensi.</li>
            </ul>
        </div>

        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                        py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i>
            Kirim Pengajuan
        </button>

        <a href="{{ route('karyawan.dinas-luar.index') }}"
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
    var berangkat = document.getElementById('tgl-berangkat').value;
    var kembali   = document.getElementById('tgl-kembali').value;
    var info      = document.getElementById('info-durasi');
    var label     = document.getElementById('label-durasi');

    if (!berangkat || !kembali) { info.classList.add('hidden'); return; }

    var b = new Date(berangkat);
    var k = new Date(kembali);

    if (k < b) { info.classList.add('hidden'); return; }

    var durasi = Math.round((k - b) / (1000 * 60 * 60 * 24)) + 1;
    label.textContent = durasi;
    info.classList.remove('hidden');
    lucide.createIcons();
}
</script>
@endpush