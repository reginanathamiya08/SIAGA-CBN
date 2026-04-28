@extends('admin.sidebar')
@section('title', 'Tambah Karyawan')

@section('content')

{{-- Data PHP → JavaScript (embed sebagai JSON tersembunyi) --}}
<script id="data-php" type="application/json">
{
    "divisiTetap":    @json($divisiTetap),
    "divisiKontrak":  @json($divisiKontrak),
    "jabatanMap":     @json($jabatanMap),
    "dokumenWajib":   @json($dokumenWajib),
    "jabatanShift":   @json($jabatanShift),
    "jabatanAtasUmr": @json($jabatanAtasUmr),
    "oldJenis":       "{{ old('jenis_karyawan') }}",
    "oldDivisi":      "{{ old('divisi') }}",
    "oldJabatan":     "{{ old('jabatan') }}"
}
</script>

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.karyawan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight  ">
            Tambah Karyawan
        </h1>
        <p class="text-gray-500 mt-1 text-sm">Username di-generate otomatis oleh sistem</p>
    </div>
</header>

<form method="POST" action="{{ route('admin.karyawan.store') }}"
      enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── KIRI ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Dasar --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-5
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                    Informasi Dasar
                </h3>

                <div class="space-y-5">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama" value="{{ old('nama') }}"
                               placeholder="Masukkan nama lengkap karyawan"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 placeholder-gray-300 outline-none transition-all
                                      @error('nama') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('nama')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Email <span class="text-red-500">*</span>
                            <span class="font-normal text-gray-400 normal-case ml-1">
                                (untuk menerima notifikasi approval)
                            </span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="contoh@email.com"
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 placeholder-gray-300 outline-none transition-all
                                      @error('email') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Karyawan --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Jenis Karyawan <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_karyawan" id="sel-jenis"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all cursor-pointer
                                       @error('jenis_karyawan') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih Jenis Karyawan --</option>
                            <option value="tetap"
                                    {{ old('jenis_karyawan')==='tetap' ? 'selected' : '' }}>
                                Karyawan Tetap (Internal CBN)
                            </option>
                            <option value="kontrak"
                                    {{ old('jenis_karyawan')==='kontrak' ? 'selected' : '' }}>
                                Karyawan Kontrak (Alih Daya)
                            </option>
                        </select>
                        @error('jenis_karyawan')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Divisi --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Divisi <span class="text-red-500">*</span>
                        </label>
                        <select name="divisi" id="sel-divisi"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all cursor-pointer
                                       @error('divisi') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih jenis karyawan dulu --</option>
                        </select>
                        @error('divisi')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[9px] text-gray-400 font-medium" id="info-divisi"></p>
                    </div>

                    {{-- Jabatan --}}
                    <div>
                        <label class="block text-[11px] font-black text-gray-500
                                        mb-2">
                            Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select name="jabatan" id="sel-jabatan"
                                class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                       text-gray-700 outline-none transition-all cursor-pointer
                                       @error('jabatan') border-red-400 bg-red-50
                                       @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                       @enderror">
                            <option value="">-- Pilih divisi dulu --</option>
                        </select>
                        @error('jabatan')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Masuk & No HP --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-500
                                            mb-2">
                                Tanggal Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_masuk"
                                   value="{{ old('tanggal_masuk') }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                          text-gray-700 outline-none transition-all
                                          @error('tanggal_masuk') border-red-400 bg-red-50
                                          @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                          @enderror">
                            @error('tanggal_masuk')
                                <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-500
                                            mb-2">
                                No. HP
                            </label>
                            <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                          text-gray-700 placeholder-gray-300 outline-none transition-all
                                          border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Dokumen Wajib --}}
            <div id="wrap-dokumen"
                 class="hidden bg-amber-50 rounded-3xl border border-amber-200 shadow-sm p-6">
                <h3 class="font-black text-amber-700   italic text-[11px] mb-2
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                    Dokumen Wajib
                </h3>
                <p class="text-xs text-amber-600 font-semibold mb-4" id="teks-dokumen"></p>
                <input type="hidden" name="jenis_dokumen" id="input-jenis-dokumen">
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                    mb-2">
                        Upload File <span class="text-red-500">*</span>
                        <span class="font-normal text-gray-400 normal-case ml-1">
                            (PDF/JPG/PNG, maks 2MB)
                        </span>
                    </label>
                    <input type="file" name="file_dokumen"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-3 rounded-xl border border-amber-200
                                  bg-white text-sm font-semibold text-gray-700
                                  outline-none focus:border-amber-400">
                    @error('file_dokumen')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>

        {{-- ── KANAN ────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Preview Username --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                    Username Otomatis
                </h3>
                <div class="bg-gray-50 rounded-2xl p-4 text-center mb-4">
                    <p class="text-[9px] font-black text-gray-400   mb-2">
                        Username yang akan diberikan
                    </p>
                    <p class="text-xl font-black text-[#1E3A5F] font-mono tracking-widest"
                       id="preview-username">—</p>
                    <p class="text-[9px] text-gray-400 mt-1">Di-generate otomatis oleh sistem</p>
                </div>
                <div class="space-y-2 border-t border-gray-100 pt-3">
                    <p class="text-[9px] font-black text-gray-400   mb-1">Format:</p>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] bg-blue-50 text-blue-700
                                     px-2 py-0.5 rounded font-black">KT-CBN-XXXX</span>
                        <span class="text-[9px] text-gray-400">Karyawan Tetap</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] bg-red-50 text-red-600
                                     px-2 py-0.5 rounded font-black">KK-HC-XXXX</span>
                        <span class="text-[9px] text-gray-400">Kontrak Div. HC</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] bg-red-50 text-red-600
                                     px-2 py-0.5 rounded font-black">KK-UM-XXXX</span>
                        <span class="text-[9px] text-gray-400">Kontrak Div. Umum</span>
                    </div>
                </div>
            </div>

            {{-- Info Flags --}}
            <div id="wrap-flags"
                 class="hidden bg-blue-50 rounded-2xl border border-blue-100 p-4">
                <p class="text-[10px] font-black text-blue-700   mb-2">Info Jabatan</p>
                <div id="isi-flags" class="space-y-1.5"></div>
            </div>

            {{-- Password --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                    Password Awal
                </h3>
                <div>
                    <label class="block text-[11px] font-black text-gray-500
                                    mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="password" value="{{ old('password') }}"
                           placeholder="Min. 6 karakter"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 placeholder-gray-300 outline-none transition-all
                                  @error('password') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-[9px] text-gray-400">
                        Berikan password ini ke karyawan. Bisa diubah setelah login.
                    </p>
                </div>
            </div>

            {{-- Tombol --}}
            <button type="submit"
                    class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black
                           text-sm   py-4 rounded-2xl transition-all
                           shadow-sm active:scale-95 italic flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan Karyawan
            </button>

            <a href="{{ route('admin.karyawan.index') }}"
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
// Baca data PHP
var PHP = JSON.parse(document.getElementById('data-php').textContent);
var DIVISI_TETAP    = PHP.divisiTetap;
var DIVISI_KONTRAK  = PHP.divisiKontrak;
var JABATAN_MAP     = PHP.jabatanMap;
var DOKUMEN_WAJIB   = PHP.dokumenWajib;
var JABATAN_SHIFT   = PHP.jabatanShift;
var JABATAN_UMR     = PHP.jabatanAtasUmr;

// Elemen
var selJenis      = document.getElementById('sel-jenis');
var selDivisi     = document.getElementById('sel-divisi');
var selJabatan    = document.getElementById('sel-jabatan');
var prevUsername  = document.getElementById('preview-username');
var wrapDokumen   = document.getElementById('wrap-dokumen');
var teksDokumen   = document.getElementById('teks-dokumen');
var inputJenisDok = document.getElementById('input-jenis-dokumen');
var wrapFlags     = document.getElementById('wrap-flags');
var isiFlags      = document.getElementById('isi-flags');
var infoDivisi    = document.getElementById('info-divisi');

// Isi dropdown
function isiSelect(el, data, placeholder) {
    while (el.firstChild) el.removeChild(el.firstChild);
    var o = document.createElement('option');
    o.value = ''; o.textContent = placeholder;
    el.appendChild(o);
    if (Array.isArray(data)) {
        data.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item; opt.textContent = item;
            el.appendChild(opt);
        });
    } else {
        Object.keys(data).forEach(function(key) {
            var opt = document.createElement('option');
            opt.value = key; opt.textContent = data[key];
            el.appendChild(opt);
        });
    }
}

function resetSelect(el, placeholder) {
    while (el.firstChild) el.removeChild(el.firstChild);
    var o = document.createElement('option');
    o.value = ''; o.textContent = placeholder;
    el.appendChild(o);
}

// EVENT: Jenis karyawan
selJenis.addEventListener('change', function() {
    var jenis = this.value;
    resetSelect(selDivisi, '-- Pilih Divisi --');
    resetSelect(selJabatan, '-- Pilih Jabatan --');
    sembunyikanDokumen(); sembunyikanFlags();
    prevUsername.textContent = '—'; infoDivisi.textContent = '';
    if (!jenis) return;
    if (jenis === 'tetap') {
        isiSelect(selDivisi, DIVISI_TETAP, '-- Pilih Divisi --');
        infoDivisi.textContent = 'Divisi internal PT Citra Bangun Nagari';
    } else {
        isiSelect(selDivisi, DIVISI_KONTRAK, '-- Pilih Divisi --');
        infoDivisi.textContent = 'HC: Satpam, Sopir, Marketing dll | Umum: CS, CS ATM, Ekspedisi';
    }
});

// EVENT: Divisi
selDivisi.addEventListener('change', function() {
    var divisi = this.value;
    resetSelect(selJabatan, '-- Pilih Jabatan --');
    sembunyikanDokumen(); sembunyikanFlags();
    prevUsername.textContent = '—';
    if (!divisi) return;
    var daftar = JABATAN_MAP[divisi];
    if (!daftar || daftar.length === 0) {
        resetSelect(selJabatan, 'Tidak ada jabatan');
        return;
    }
    isiSelect(selJabatan, daftar, '-- Pilih Jabatan --');
    updatePreview();
});

// EVENT: Jabatan
selJabatan.addEventListener('change', function() {
    var jabatan = this.value;
    sembunyikanDokumen(); sembunyikanFlags();
    if (!jabatan) return;
    if (DOKUMEN_WAJIB[jabatan]) {
        var info = DOKUMEN_WAJIB[jabatan];
        teksDokumen.textContent = info.keterangan;
        inputJenisDok.value = info.jenis;
        wrapDokumen.classList.remove('hidden');
    }
    tampilkanFlags(jabatan);
});

function updatePreview() {
    var jenis = selJenis.value, divisi = selDivisi.value, prefix = '';
    if (jenis === 'tetap') prefix = 'KT-CBN';
    else if (divisi === 'HC') prefix = 'KK-HC';
    else if (divisi === 'umum') prefix = 'KK-UM';
    prevUsername.textContent = prefix ? prefix + '-XXXX' : '—';
}

function tampilkanFlags(jabatan) {
    var flags = [], divisi = selDivisi.value;
    if (JABATAN_SHIFT.indexOf(jabatan) !== -1)
        flags.push('<p class="text-[9px] text-blue-700 font-semibold">⚡ Bersifat <strong>shift</strong></p>');
    if (JABATAN_UMR.indexOf(jabatan) !== -1)
        flags.push('<p class="text-[9px] text-blue-700 font-semibold">💰 Gaji <strong>di atas UMR</strong></p>');
    if (divisi === 'HC')
        flags.push('<p class="text-[9px] text-blue-700 font-semibold">🍱 Uang makan <strong>dibayar mitra</strong></p>');
    else if (divisi === 'umum')
        flags.push('<p class="text-[9px] text-blue-700 font-semibold">🍱 Uang makan <strong>dibayar CBN</strong> (Rp35.000/hari)</p>');
    if (jabatan === 'CS' || jabatan === 'CS ATM')
        flags.push('<p class="text-[9px] text-blue-700 font-semibold">🕐 Absen <strong>1 jam sebelum</strong> jam operasional mitra</p>');
    if (flags.length > 0) { isiFlags.innerHTML = flags.join(''); wrapFlags.classList.remove('hidden'); }
}

function sembunyikanDokumen() {
    wrapDokumen.classList.add('hidden');
    inputJenisDok.value = ''; teksDokumen.textContent = '';
}
function sembunyikanFlags() {
    wrapFlags.classList.add('hidden'); isiFlags.innerHTML = '';
}

// Restore nilai lama jika form error validasi
(function() {
    var oj = PHP.oldJenis, od = PHP.oldDivisi, ojab = PHP.oldJabatan;
    if (!oj) return;
    selJenis.value = oj;
    isiSelect(selDivisi, oj === 'tetap' ? DIVISI_TETAP : DIVISI_KONTRAK, '-- Pilih Divisi --');
    if (!od) return;
    selDivisi.value = od;
    var daftar = JABATAN_MAP[od];
    if (daftar && daftar.length > 0) isiSelect(selJabatan, daftar, '-- Pilih Jabatan --');
    if (!ojab) return;
    selJabatan.value = ojab;
    updatePreview(); tampilkanFlags(ojab);
    if (DOKUMEN_WAJIB[ojab]) {
        teksDokumen.textContent = DOKUMEN_WAJIB[ojab].keterangan;
        inputJenisDok.value = DOKUMEN_WAJIB[ojab].jenis;
        wrapDokumen.classList.remove('hidden');
    }
}());
</script>
@endpush