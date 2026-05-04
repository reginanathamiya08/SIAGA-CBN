@extends('karyawan.sidebar')
@section('title', 'Ajukan Izin')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('karyawan.perizinan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Ajukan Perizinan</h1>
        <p class="text-gray-500 mt-1 text-sm">Cuti, Izin Pribadi, atau Sakit — <span class="text-red-600 font-bold ">Aturan PT CBN</span></p>
    </div>
</header>

<form method="POST" action="{{ route('karyawan.perizinan.store') }}"
      enctype="multipart/form-data" id="form-izin">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kiri: Form ──────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Detail Pengajuan
            </h3>

            <div class="space-y-5">

                {{-- Jenis Izin --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-3">
                        Jenis Izin <span class="text-red-500">*</span>
                    </label>
                    {{-- Radio card --}}
                    <div class="grid grid-cols-2 gap-3" id="jenis-container">

                        <label class="jenis-card relative flex items-start gap-3 p-4 rounded-2xl
                                      border-2 cursor-pointer transition-all hover:border-indigo-300
                                      {{ old('jenis_izin') === 'cuti' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-100' }}">
                            <input type="radio" name="jenis_izin" value="cuti"
                                   class="mt-0.5 accent-indigo-600"
                                   {{ old('jenis_izin') === 'cuti' ? 'checked' : '' }}
                                   onchange="onJenisChange(this.value)">
                            <div>
                                <p class="text-xs font-black text-[#1E3A5F]">Cuti</p>
                                <p class="text-[9px] text-gray-400 mt-0.5">Potong uang makan Rp 35.000/hari. <span class="text-indigo-600 font-bold">Kuota tetap 12 hari.</span></p>
                            </div>
                        </label>

                        <label class="jenis-card relative flex items-start gap-3 p-4 rounded-2xl
                                      border-2 cursor-pointer transition-all hover:border-blue-300
                                      {{ old('jenis_izin') === 'izin_pribadi' ? 'border-blue-500 bg-blue-50' : 'border-gray-100' }}">
                            <input type="radio" name="jenis_izin" value="izin_pribadi"
                                   class="mt-0.5 accent-blue-600"
                                   {{ old('jenis_izin') === 'izin_pribadi' ? 'checked' : '' }}
                                   onchange="onJenisChange(this.value)">
                            <div>
                                <p class="text-xs font-black text-[#1E3A5F]">Izin Pribadi</p>
                                <p class="text-[9px] text-gray-400 mt-0.5"><span class="text-blue-600 font-bold">Memotong kuota cuti tahunan.</span></p>
                            </div>
                        </label>

                        <label class="jenis-card relative flex items-start gap-3 p-4 rounded-2xl
                                      border-2 cursor-pointer transition-all hover:border-purple-300
                                      {{ old('jenis_izin') === 'sakit_surat' ? 'border-purple-500 bg-purple-50' : 'border-gray-100' }}">
                            <input type="radio" name="jenis_izin" value="sakit_surat"
                                   class="mt-0.5 accent-purple-600"
                                   {{ old('jenis_izin') === 'sakit_surat' ? 'checked' : '' }}
                                   onchange="onJenisChange(this.value)">
                            <div>
                                <p class="text-xs font-black text-[#1E3A5F]">Sakit + Surat Dokter</p>
                                <p class="text-[9px] text-gray-400 mt-0.5">Tidak potong kuota (Limit 12 hari). <span class="text-purple-600 font-bold">Wajib upload surat dokter.</span></p>
                            </div>
                        </label>

                        <label class="jenis-card relative flex items-start gap-3 p-4 rounded-2xl
                                      border-2 cursor-pointer transition-all hover:border-orange-300
                                      {{ old('jenis_izin') === 'sakit_no_surat' ? 'border-orange-500 bg-orange-50' : 'border-gray-100' }}">
                            <input type="radio" name="jenis_izin" value="sakit_no_surat"
                                   class="mt-0.5 accent-orange-600"
                                   {{ old('jenis_izin') === 'sakit_no_surat' ? 'checked' : '' }}
                                   onchange="onJenisChange(this.value)">
                            <div>
                                <p class="text-xs font-black text-[#1E3A5F]">Sakit Tanpa Surat</p>
                                <p class="text-[9px] text-gray-400 mt-0.5"><span class="text-orange-600 font-bold">Memotong kuota cuti tahunan.</span></p>
                            </div>
                        </label>

                    </div>
                    @error('jenis_izin')
                        <p class="mt-2 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tgl-mulai"
                               value="{{ old('tanggal_mulai') }}"
                               min="{{ date('Y-m-d') }}"
                               oninput="hitungHari()"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('tanggal_mulai') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('tanggal_mulai')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tgl-selesai"
                               value="{{ old('tanggal_selesai') }}"
                               min="{{ date('Y-m-d') }}"
                               oninput="hitungHari()"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('tanggal_selesai') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('tanggal_selesai')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Info jumlah hari (muncul otomatis) --}}
                <div id="info-hari" class="hidden p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <i data-lucide="calendar-days" class="w-5 h-5 text-blue-500 shrink-0"></i>
                        <div>
                            <p class="text-xs font-black text-blue-700">
                                Durasi: <span id="label-hari" class="text-lg">0</span> hari
                            </p>
                            <p class="text-[9px] text-blue-500 font-semibold" id="info-kuota"></p>
                        </div>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                        Keterangan
                    </label>
                    <textarea name="keterangan" rows="3"
                              placeholder="Tuliskan keterangan tambahan (opsional)..."
                              class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                     text-gray-700 placeholder-gray-300 outline-none transition-all resize-none
                                     border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white">{{ old('keterangan') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Upload Surat Dokter (muncul otomatis jika pilih sakit_surat) --}}
        <div id="wrap-dokter" class="hidden bg-purple-50 rounded-3xl border border-purple-200 shadow-sm p-6">
            <h3 class="font-black text-purple-700 italic text-[11px] mb-3
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                Upload Surat Dokter <span class="text-red-500">*</span>
            </h3>
            <p class="text-xs text-purple-600 font-semibold mb-4">
                Sakit dengan surat dokter tidak akan memotong kuota cuti tahunan kamu (Batas 12 hari/tahun).
                Wajib upload surat dokter yang sah.
            </p>
            <div>
                <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                    File Surat Dokter
                    <span class="font-normal text-gray-400 normal-case ml-1">(PDF/JPG/PNG, maks 2MB)</span>
                </label>
                <input type="file" name="file_bukti"
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-4 py-3 rounded-xl border border-purple-200 bg-white
                              text-sm font-semibold text-gray-700 outline-none focus:border-purple-400">
                @error('file_bukti')
                    <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </div>

    {{-- ── Kanan: Info & Tombol ────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Kuota Cuti --}}
        @if ($kuotaCuti)
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                    Jatah Cuti {{ now()->year }}
                </h3>
                <div class="text-center mb-3">
                    <p class="text-4xl font-black text-[#1E3A5F]">{{ $kuotaCuti->sisa }}</p>
                    <p class="text-[9px] text-gray-400 font-black mt-1">Hari Tersisa</p>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                    @php $pct = $kuotaCuti->kuota_total > 0 ? ($kuotaCuti->sisa / $kuotaCuti->kuota_total) * 100 : 0 @endphp
                    <div class="h-2 rounded-full {{ $pct > 50 ? 'bg-green-500' : ($pct > 25 ? 'bg-amber-500' : 'bg-red-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between text-[9px] text-gray-400 font-bold">
                    <span>Terpakai: {{ $kuotaCuti->terpakai }}</span>
                    <span>Total: {{ $kuotaCuti->kuota_total }}</span>
                </div>
            </div>
        @endif

        {{-- Aturan Ringkas --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4">
            <p class="text-[10px] font-black text-blue-700 mb-2  tracking-tighter">Aturan Perizinan PT CBN</p>
            <ul class="space-y-1.5 text-[9px] text-blue-600 font-semibold leading-relaxed">
                <li class="flex gap-1.5"><span>•</span> <strong>Cuti:</strong> Potong uang makan Rp 35k/hari. Kuota tetap.</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Izin:</strong> Memotong jatah cuti 12 hari.</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Sakit + Surat:</strong> Tidak potong jatah cuti (Limit 12 hari).</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Sakit Tanpa Surat:</strong> Memotong jatah cuti.</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Lembur:</strong> Berdasarkan hitungan jam & ACC Pimpinan.</li>
            </ul>
        </div>

        {{-- Tombol --}}
        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                         tracking-widest py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i>
            Kirim Pengajuan
        </button>

        <a href="{{ route('karyawan.perizinan.index') }}"
           class="block text-center text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">
            Batal
        </a>

    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
var sisaKuota = {{ $kuotaCuti?->sisa ?? 0 }};

function onJenisChange(jenis) {
    var wrapDokter = document.getElementById('wrap-dokter');
    if (jenis === 'sakit_surat') {
        wrapDokter.classList.remove('hidden');
    } else {
        wrapDokter.classList.add('hidden');
    }
    hitungHari();
}

function hitungHari() {
    var mulai   = document.getElementById('tgl-mulai').value;
    var selesai = document.getElementById('tgl-selesai').value;
    var infoHari  = document.getElementById('info-hari');
    var labelHari = document.getElementById('label-hari');
    var infoKuota = document.getElementById('info-kuota');

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

    var jenis = document.querySelector('input[name="jenis_izin"]:checked')?.value;
    var memotongCuti = ['izin_pribadi', 'sakit_no_surat'].includes(jenis);

    if (memotongCuti) {
        var sisa = sisaKuota - diff;
        if (sisa < 0) {
            infoKuota.textContent = '⚠️ Kuota cuti tidak mencukupi! Sisa: ' + sisaKuota + ' hari.';
            infoKuota.className = 'text-[9px] text-red-600 font-semibold';
        } else {
            infoKuota.textContent = 'Sisa kuota setelah izin: ' + sisa + ' hari.';
            infoKuota.className = 'text-[9px] text-blue-500 font-semibold';
        }
    } else if (jenis === 'cuti') {
        infoKuota.textContent = 'Uang makan akan dipotong Rp ' + (35000 * diff).toLocaleString('id-ID');
        infoKuota.className = 'text-[9px] text-indigo-600 font-semibold';
    } else {
        infoKuota.textContent = 'Sakit dengan surat dokter tidak memotong kuota cuti.';
        infoKuota.className = 'text-[9px] text-purple-600 font-semibold';
    }

    lucide.createIcons();
}

var oldJenis = '{{ old("jenis_izin") }}';
if (oldJenis) {
    onJenisChange(oldJenis);
    hitungHari();
}
</script>
@endpush