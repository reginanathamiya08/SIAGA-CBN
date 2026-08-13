@extends('karyawan.sidebar')
@section('title', 'Ajukan Izin')

@section('content')

<header class="flex items-center gap-4 mb-4">
    <a href="{{ route('karyawan.perizinan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">Ajukan Perizinan</h1>
        <p class="text-gray-500 mt-1 text-sm">Cuti, Izin Pribadi, atau Sakit — <span class="text-red-600 font-bold">PT CBN</span></p>
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
                    <label class="block text-[11px] font-black text-gray-500 mb-3">
                        Jenis Izin <span class="text-red-500">*</span>
                    </label>
                    {{-- Radio card --}}
                    <div class="grid grid-cols-2 gap-3" id="jenis-container">
                        @foreach($jenisPerizinan as $jenis)
                            @php
                                $borderColorClass = match($jenis->slug) {
                                    'cuti'           => 'hover:border-indigo-300',
                                    'izin_pribadi'   => 'hover:border-blue-300',
                                    'sakit_surat'    => 'hover:border-purple-300',
                                    'sakit_no_surat' => 'hover:border-orange-300',
                                    'dinas_luar'     => 'hover:border-emerald-300',
                                    default          => 'hover:border-gray-300',
                                };
                                $activeColorClass = match($jenis->slug) {
                                    'cuti'           => 'border-indigo-500 bg-indigo-50',
                                    'izin_pribadi'   => 'border-blue-500 bg-blue-50',
                                    'sakit_surat'    => 'border-purple-500 bg-purple-50',
                                    'sakit_no_surat' => 'border-orange-500 bg-orange-50',
                                    'dinas_luar'     => 'border-emerald-500 bg-emerald-50',
                                    default          => 'border-gray-500 bg-gray-50',
                                };
                                $accentColorClass = match($jenis->slug) {
                                    'cuti'           => 'accent-indigo-600',
                                    'izin_pribadi'   => 'accent-blue-600',
                                    'sakit_surat'    => 'accent-purple-600',
                                    'sakit_no_surat' => 'accent-orange-600',
                                    'dinas_luar'     => 'accent-emerald-600',
                                    default          => 'accent-gray-600',
                                };
                                $textColorClass = match($jenis->slug) {
                                    'cuti'           => 'text-indigo-600',
                                    'izin_pribadi'   => 'text-blue-600',
                                    'sakit_surat'    => 'text-purple-600',
                                    'sakit_no_surat' => 'text-orange-600',
                                    'dinas_luar'     => 'text-emerald-600',
                                    default          => 'text-gray-600',
                                };
                            @endphp
                            <label class="jenis-card relative flex items-start gap-3 p-4 rounded-2xl
                                          border-2 cursor-pointer transition-all {{ $borderColorClass }}
                                          {{ old('jenis_perizinan_id') === $jenis->id ? $activeColorClass : 'border-gray-100' }}">
                                <input type="radio" name="jenis_perizinan_id" value="{{ $jenis->id }}"
                                       class="mt-0.5 {{ $accentColorClass }}"
                                       {{ old('jenis_perizinan_id') === $jenis->id ? 'checked' : '' }}
                                       data-slug="{{ $jenis->slug }}"
                                       data-memotong-kuota="{{ $jenis->memotong_kuota ? '1' : '0' }}"
                                       data-memotong-uang-makan="{{ $jenis->memotong_uang_makan ? '1' : '0' }}"
                                       data-wajib-bukti="{{ $jenis->wajib_upload_bukti ? '1' : '0' }}"
                                       onchange="onJenisChange(this)">
                                <div>
                                    <p class="text-xs font-black text-[#1E3A5F]">{{ $jenis->nama_jenis }}</p>
                                    <p class="text-[9px] text-gray-400 mt-0.5">
                                        @if($jenis->memotong_uang_makan)
                                            Potong uang makan Rp {{ number_format(\App\Models\Configuration::getValue('uang_makan_default'), 0, ',', '.') }}/hari.
                                        @endif
                                        @if($jenis->wajib_upload_bukti)
                                            @if($jenis->slug === 'dinas_luar')
                                                <span class="text-emerald-700 font-bold block mb-0.5">Wajib upload Surat Tugas.</span>
                                            @elseif($jenis->slug === 'sakit_surat')
                                                <span class="text-purple-700 font-bold block mb-0.5">Wajib upload Surat Dokter.</span>
                                            @endif
                                        @endif
                                        @if($jenis->memotong_kuota)
                                            <span class="{{ $textColorClass }} font-bold">Memotong kuota cuti tahunan.</span>
                                        @else
                                            <span class="text-green-600 font-bold">Tidak memotong kuota.</span>
                                        @endif
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_perizinan_id')
                        <p class="mt-2 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 mb-2">
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
                        <label class="block text-[11px] font-black text-gray-500 mb-2">
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

                {{-- Rekan Kerja Pengganti (Hanya untuk Karyawan Tetap + Jenis Cuti) --}}
                @if (Auth::user()->isKaryawanTetap())
                    <div id="wrap-rekan-kerja" class="hidden">
                        <label class="block text-[11px] font-black text-gray-500 mb-2">
                            Rekan Kerja Pengganti (Backup) <span class="text-red-500">*</span>
                        </label>
                        <select name="rekan_kerja_id" id="rekan-kerja-select"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all
                                       @error('rekan_kerja_id') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih Rekan Kerja --</option>
                            @foreach ($rekanKerjaList as $rekan)
                                <option value="{{ $rekan->id }}" {{ old('rekan_kerja_id') == $rekan->id ? 'selected' : '' }}>
                                    {{ $rekan->nama }} ({{ $rekan->jabatan ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('rekan_kerja_id')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Keterangan --}}
                <div id="wrap-keterangan">
                    <label class="block text-[11px] font-black text-gray-500 mb-2">
                        Keterangan <span id="keterangan-req-star" class="text-red-500">*</span>
                    </label>
                    <textarea name="keterangan" id="keterangan-input" rows="3"
                              placeholder="Tuliskan alasan/keterangan pengajuan secara detail..." required
                              class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                     text-gray-700 placeholder-gray-300 outline-none transition-all resize-none
                                     @error('keterangan') border-red-400 bg-red-50
                                     @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                     @enderror">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Upload Surat Dokter (muncul otomatis jika wajib bukti) --}}
        <div id="wrap-dokter" class="hidden bg-purple-50 rounded-3xl border border-purple-200 shadow-sm p-6">
            <h3 id="bukti-title-container" class="font-black text-purple-700 italic text-[11px] mb-3
                       flex items-center gap-2">
                <span id="bukti-dot" class="w-1 h-4 bg-purple-500 rounded-full"></span>
                <span id="bukti-title">Upload Surat Dokter</span> <span class="text-red-500">*</span>
            </h3>
            <p id="bukti-desc" class="text-xs text-purple-600 font-semibold mb-4">
                Sakit dengan surat dokter tidak akan memotong kuota cuti tahunan kamu (Batas {{ \App\Models\Configuration::getValue('kuota_cuti_tahunan') }} hari/tahun).
                Wajib upload surat dokter yang sah.
            </p>
            <div>
                <label class="block text-[11px] font-black text-gray-500 mb-2">
                    <span id="bukti-label">File Surat Dokter</span>
                    <span class="font-normal text-gray-400 normal-case ml-1">(PDF/JPG/PNG, maks 2MB)</span>
                </label>
                <input type="file" name="file_bukti" id="bukti-input"
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

        {{-- Kuota Perizinan --}}
        @if ($kuotaPerizinan)
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                    Jatah Perizinan {{ now()->year }}
                </h3>
                <div class="text-center mb-3">
                    <p class="text-4xl font-black text-[#1E3A5F]">{{ $kuotaPerizinan->sisa }}</p>
                    <p class="text-[9px] text-gray-400 font-black mt-1">Hari Tersisa</p>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                    @php $pct = $kuotaPerizinan->kuota_total > 0 ? ($kuotaPerizinan->sisa / $kuotaPerizinan->kuota_total) * 100 : 0 @endphp
                    <div class="h-2 rounded-full {{ $pct > 50 ? 'bg-green-500' : ($pct > 25 ? 'bg-amber-500' : 'bg-red-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between text-[9px] text-gray-400 font-bold">
                    <span>Terpakai: {{ $kuotaPerizinan->terpakai }}</span>
                    <span>Total: {{ $kuotaPerizinan->kuota_total }}</span>
                </div>
            </div>
        @endif

        {{-- Aturan Ringkas --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4">
            <p class="text-[10px] font-black text-blue-700 mb-2  tracking-tighter">Aturan Perizinan PT CBN</p>
            <ul class="space-y-1.5 text-[9px] text-blue-600 font-semibold leading-relaxed">
                <li class="flex gap-1.5"><span>•</span> <strong>Cuti:</strong> Potong uang makan Rp {{ number_format(\App\Models\Configuration::getValue('uang_makan_default'), 0, ',', '.') }}/hari. Kuota tetap.</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Izin:</strong> Memotong jatah cuti {{ \App\Models\Configuration::getValue('kuota_cuti_tahunan') }} hari.</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Sakit + Surat:</strong> Tidak potong jatah cuti (Limit {{ \App\Models\Configuration::getValue('kuota_cuti_tahunan') }} hari).</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Sakit Tanpa Surat:</strong> Memotong jatah cuti.</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Dinas Luar Kota:</strong> Tidak potong cuti (Wajib upload Surat Tugas).</li>
                <li class="flex gap-1.5"><span>•</span> <strong>Lembur:</strong> Berdasarkan hitungan jam & ACC Pimpinan.</li>
            </ul>
        </div>

        {{-- Tombol --}}
        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                          py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 flex items-center justify-center gap-2">
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
var sisaKuota = {{ $kuotaPerizinan?->sisa ?? 0 }};

function onJenisChange(element) {
    var wajibBukti = element.getAttribute('data-wajib-bukti') === '1';
    var wrapDokter = document.getElementById('wrap-dokter');
    var slug = element.getAttribute('data-slug');
    
    var wrapRekan = document.getElementById('wrap-rekan-kerja');
    if (wrapRekan) {
        if (slug === 'cuti') {
            wrapRekan.classList.remove('hidden');
            document.getElementById('rekan-kerja-select').setAttribute('required', 'required');
        } else {
            wrapRekan.classList.add('hidden');
            document.getElementById('rekan-kerja-select').removeAttribute('required');
        }
    }
    
    // Sembunyikan total kolom keterangan khusus Sakit (Dengan Surat Dokter)
    var wrapKet  = document.getElementById('wrap-keterangan');
    var reqStar  = document.getElementById('keterangan-req-star');
    var ketInput = document.getElementById('keterangan-input');
    if (slug === 'sakit_surat') {
        if (wrapKet) wrapKet.classList.add('hidden');
        if (ketInput) ketInput.removeAttribute('required');
    } else {
        if (wrapKet) wrapKet.classList.remove('hidden');
        if (reqStar) reqStar.classList.remove('hidden');
        if (ketInput) ketInput.setAttribute('required', 'required');
    }
    
    var isKaryawanKontrak = {{ Auth::user()->isKaryawanKontrak() ? 'true' : 'false' }};
    
    if (wajibBukti || slug === 'cuti') {
        wrapDokter.classList.remove('hidden');
        
        var titleElem = document.getElementById('bukti-title');
        var descElem = document.getElementById('bukti-desc');
        var labelElem = document.getElementById('bukti-label');
        var dotElem = document.getElementById('bukti-dot');
        var inputElem = document.getElementById('bukti-input');
        
        if (slug === 'cuti') {
            wrapDokter.className = "bg-indigo-50 rounded-3xl border border-indigo-200 shadow-sm p-6";
            titleElem.textContent = "Upload Form/Dokumen Cuti Tahunan";
            titleElem.className = "font-black text-indigo-700 italic text-[11px] mb-3 flex items-center gap-2";
            dotElem.className = "w-1 h-4 bg-indigo-500 rounded-full";
            descElem.textContent = "Pengajuan Cuti Tahunan wajib mengunggah file formulir/dokumen cuti yang disetujui.";
            descElem.className = "text-xs text-indigo-600 font-semibold mb-4";
            labelElem.textContent = "File Form/Dokumen Cuti";
            inputElem.className = "w-full px-4 py-3 rounded-xl border border-indigo-200 bg-white text-sm font-semibold text-gray-700 outline-none focus:border-indigo-400";
        } else if (slug === 'dinas_luar') {
            wrapDokter.className = "bg-emerald-50 rounded-3xl border border-emerald-200 shadow-sm p-6";
            titleElem.textContent = "Upload Surat Tugas";
            titleElem.className = "font-black text-emerald-700 italic text-[11px] mb-3 flex items-center gap-2";
            dotElem.className = "w-1 h-4 bg-emerald-500 rounded-full";
            descElem.textContent = "Perjalanan dinas luar kota wajib mengunggah Surat Tugas yang sah.";
            descElem.className = "text-xs text-emerald-600 font-semibold mb-4";
            labelElem.textContent = "File Surat Tugas";
            inputElem.className = "w-full px-4 py-3 rounded-xl border border-emerald-200 bg-white text-sm font-semibold text-gray-700 outline-none focus:border-emerald-400";
        } else {
            wrapDokter.className = "bg-purple-50 rounded-3xl border border-purple-200 shadow-sm p-6";
            titleElem.textContent = "Upload Surat Dokter";
            titleElem.className = "font-black text-purple-700 italic text-[11px] mb-3 flex items-center gap-2";
            dotElem.className = "w-1 h-4 bg-purple-500 rounded-full";
            descElem.textContent = "Sakit dengan surat dokter tidak akan memotong kuota cuti tahunan kamu (Batas {{ \App\Models\Configuration::getValue('kuota_cuti_tahunan') }} hari/tahun). Wajib upload surat dokter yang sah.";
            descElem.className = "text-xs text-purple-600 font-semibold mb-4";
            labelElem.textContent = "File Surat Dokter";
            inputElem.className = "w-full px-4 py-3 rounded-xl border border-purple-200 bg-white text-sm font-semibold text-gray-700 outline-none focus:border-purple-400";
        }
    } else {
        wrapDokter.classList.add('hidden');
    }
    
    // Toggle active border styling class
    document.querySelectorAll('.jenis-card').forEach(function(card) {
        card.classList.remove('border-indigo-500', 'bg-indigo-50', 'border-blue-500', 'bg-blue-50', 'border-purple-500', 'bg-purple-50', 'border-orange-500', 'bg-orange-50', 'border-emerald-500', 'bg-emerald-50');
        card.classList.add('border-gray-100');
    });
    
    var card = element.closest('.jenis-card');
    card.classList.remove('border-gray-100');
    if (slug === 'cuti') {
        card.classList.add('border-indigo-500', 'bg-indigo-50');
    } else if (slug === 'izin_pribadi') {
        card.classList.add('border-blue-500', 'bg-blue-50');
    } else if (slug === 'sakit_surat') {
        card.classList.add('border-purple-500', 'bg-purple-50');
    } else if (slug === 'sakit_no_surat') {
        card.classList.add('border-orange-500', 'bg-orange-50');
    } else if (slug === 'dinas_luar') {
        card.classList.add('border-emerald-500', 'bg-emerald-50');
    } else {
        card.classList.add('border-gray-500', 'bg-gray-50');
    }
    
    hitungHari();
}

function hitungHari() {
    var mulai   = document.getElementById('tgl-mulai').value;
    var selesai = document.getElementById('tgl-selesai').value;
    var infoHari  = document.getElementById('info-hari');
    var labelHari = document.getElementById('label-hari');
    var infoKuota = document.getElementById('info-kuota');
    var isKaryawanKontrak = {{ Auth::user()->isKaryawanKontrak() ? 'true' : 'false' }};

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

    var checkedRadio = document.querySelector('input[name="jenis_perizinan_id"]:checked');
    if (!checkedRadio) return;

    var memotongCuti = checkedRadio.getAttribute('data-memotong-kuota') === '1';
    var memotongUangMakan = checkedRadio.getAttribute('data-memotong-uang-makan') === '1';
    var wajibBukti = checkedRadio.getAttribute('data-wajib-bukti') === '1';
    var slug = checkedRadio.getAttribute('data-slug');

    if (memotongCuti) {
        var sisa = sisaKuota - diff;
        if (sisa < 0) {
            infoKuota.textContent = '⚠️ Kuota cuti tidak mencukupi! Sisa: ' + sisaKuota + ' hari.';
            infoKuota.className = 'text-[9px] text-red-600 font-semibold';
        } else {
            if (isKaryawanKontrak && slug === 'cuti') {
                infoKuota.textContent = 'Sisa kuota setelah izin: ' + sisa + ' hari. (Form permohonan akan dicetak untuk ditandatangani offline setelah ini)';
            } else {
                infoKuota.textContent = 'Sisa kuota setelah izin: ' + sisa + ' hari.';
            }
            infoKuota.className = 'text-[9px] text-blue-500 font-semibold';
        }
    } else if (memotongUangMakan) {
        infoKuota.textContent = 'Uang makan akan dipotong Rp ' + (35000 * diff).toLocaleString('id-ID');
        infoKuota.className = 'text-[9px] text-indigo-600 font-semibold';
    } else if (wajibBukti) {
        if (slug === 'dinas_luar') {
            infoKuota.textContent = 'Wajib mengunggah Surat Tugas. Tidak memotong kuota cuti.';
            infoKuota.className = 'text-[9px] text-emerald-600 font-semibold';
        } else {
            infoKuota.textContent = 'Wajib mengunggah surat dokter. Tidak memotong kuota cuti.';
            infoKuota.className = 'text-[9px] text-purple-600 font-semibold';
        }
    } else {
        infoKuota.textContent = 'Tidak memotong kuota cuti.';
        infoKuota.className = 'text-[9px] text-green-600 font-semibold';
    }

    lucide.createIcons();
}

var oldJenisId = '{{ old("jenis_perizinan_id") }}';
if (oldJenisId) {
    var radio = document.querySelector('input[name="jenis_perizinan_id"][value="' + oldJenisId + '"]');
    if (radio) {
        radio.checked = true;
        onJenisChange(radio);
    }
} else {
    // Select first radio by default
    var firstRadio = document.querySelector('input[name="jenis_perizinan_id"]');
    if (firstRadio) {
        firstRadio.checked = true;
        onJenisChange(firstRadio);
    }
}
</script>
@endpush
