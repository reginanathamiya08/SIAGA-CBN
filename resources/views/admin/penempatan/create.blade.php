@extends('admin.sidebar')
@section('title', 'Plotting Karyawan')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.penempatan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight  ">
            Plotting Karyawan ke Mitra
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            Tempatkan karyawan kontrak yang tersedia ke perusahaan mitra
        </p>
    </div>
</header>

<form method="POST" action="{{ route('admin.penempatan.store') }}">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kiri: Pilih Karyawan ─────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Pilih Karyawan --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Pilih Karyawan
                <span class="font-normal text-gray-400 normal-case text-[9px] ml-1">
                    (hanya karyawan kontrak yang belum ditempatkan)
                </span>
            </h3>

            {{-- Search karyawan --}}
            <div class="mb-4">
                <input type="text" id="search-karyawan"
                       placeholder="Cari nama karyawan..."
                       oninput="filterKaryawan(this.value)"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                              text-sm font-semibold text-gray-700 placeholder-gray-300
                              outline-none focus:border-[#1E3A5F] focus:bg-white">
            </div>

            {{-- Daftar karyawan (radio buttons) --}}
            <div class="space-y-2 max-h-80 overflow-y-auto pr-1" id="list-karyawan">
                @forelse ($karyawanTersedia as $kar)
                    <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-100
                                  hover:border-blue-200 hover:bg-blue-50/30 cursor-pointer
                                  transition-all karyawan-item"
                           data-nama="{{ strtolower($kar->nama) }}"
                           data-jabatan="{{ strtolower($kar->jabatan) }}">
                        <input type="radio" name="user_id" value="{{ $kar->id }}"
                               class="accent-[#1E3A5F] w-4 h-4 shrink-0"
                               {{ (old('user_id') == $kar->id || optional($karyawanDipilih)->id == $kar->id) ? 'checked' : '' }}>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    font-black text-xs shrink-0 bg-red-100 text-red-700">
                            {{ strtoupper(substr($kar->nama, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-black text-[#1E3A5F]  ">
                                {{ $kar->nama }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-[9px] font-mono text-gray-400">
                                    {{ $kar->nip }}
                                </span>
                                <span class="text-[9px] text-gray-300">•</span>
                                <span class="text-[9px] text-gray-500 font-semibold">
                                    {{ $kar->jabatan }}
                                </span>
                                <span class="text-[9px] text-gray-300">•</span>
                                <span class="text-[9px] {{ strtolower($kar->divisi) === 'hc' ? 'text-red-500' : 'text-blue-500' }} font-semibold  ">
                                    {{ $kar->labelDivisi() }}
                                </span>
                                @if ($kar->is_shift)
                                    <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700
                                                 rounded text-[8px] font-black  ">Shift</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-[9px] font-black text-green-600   shrink-0">
                            Tersedia
                        </span>
                    </label>
                @empty
                    <div class="text-center py-10">
                        <i data-lucide="users" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-400 font-semibold">
                            Semua karyawan kontrak sudah memiliki penempatan aktif.
                        </p>
                    </div>
                @endforelse
            </div>

            @error('user_id')
                <p class="mt-2 text-xs text-red-500 font-semibold">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- ── Kanan: Pilih Mitra & Tanggal ───────────────────────────── --}}
    <div class="space-y-5">

        {{-- Pilih Mitra --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-teal-500 rounded-full"></span>
                Pilih Mitra
            </h3>

            <select name="mitra_id"
                    class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                           text-gray-700 outline-none transition-all cursor-pointer
                           @error('mitra_id') border-red-400 bg-red-50
                           @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                           @enderror">
                <option value="">-- Pilih Mitra --</option>
                @foreach ($daftarMitra as $m)
                    @if (!$m->is_cabang)
                        <optgroup label="{{ $m->nama_mitra }}{{ $m->is_pusat ? ' (Kantor Pusat / Utama)' : '' }}">
                            <option value="{{ $m->id }}"
                                    {{ old('mitra_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nama_mitra }} {{ $m->is_pusat ? '(Kantor Pusat / Utama)' : '(Induk)' }}
                            </option>
                            @foreach ($m->cabang as $cab)
                                <option value="{{ $cab->id }}"
                                        {{ old('mitra_id') == $cab->id ? 'selected' : '' }}>
                                    ↳ {{ $cab->nama_mitra }}{{ $cab->is_pusat ? ' (Kantor Pusat / Utama)' : '' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
            @error('mitra_id')
                <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tanggal Mulai --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                Tanggal Mulai
            </h3>
            <input type="date" name="tanggal_mulai"
                   value="{{ old('tanggal_mulai', date('Y-m-d')) }}"
                   class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                          text-gray-700 outline-none transition-all
                          @error('tanggal_mulai') border-red-400 bg-red-50
                          @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                          @enderror">
            @error('tanggal_mulai')
                <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        {{-- Info --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4">
            <p class="text-[10px] font-black text-blue-700   mb-2">Catatan</p>
            <ul class="space-y-1.5 text-[9px] text-blue-600 font-semibold">
                <li>• Hanya karyawan kontrak yang bisa ditempatkan.</li>
                <li>• Satu karyawan hanya bisa punya satu penempatan aktif.</li>
                <li>• Koordinat absensi mengikuti lokasi mitra yang dipilih.</li>
            </ul>
        </div>

        {{-- Tombol --}}
        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                         tracking-widest py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="map-pin" class="w-5 h-5"></i>
            Tempatkan Karyawan
        </button>

        <a href="{{ route('admin.penempatan.index') }}"
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
function filterKaryawan(keyword) {
    var lower = keyword.toLowerCase().trim();
    document.querySelectorAll('.karyawan-item').forEach(function(el) {
        var nama    = el.getAttribute('data-nama') || '';
        var jabatan = el.getAttribute('data-jabatan') || '';
        var match   = nama.includes(lower) || jabatan.includes(lower);
        el.style.display = match ? 'flex' : 'none';
    });
}
</script>
@endpush
