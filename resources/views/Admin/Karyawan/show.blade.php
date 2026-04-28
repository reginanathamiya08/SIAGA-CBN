@extends('admin.sidebar')
@section('title', 'Detail Karyawan')

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.karyawan.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight uppercase">
            {{ $karyawan->nama }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ $karyawan->jabatan }} • {{ $karyawan->labelDivisi() }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.karyawan.edit', $karyawan->id) }}"
           class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white
                  font-black text-xs  italic px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
        </a>
        <form method="POST"
              action="{{ route('admin.karyawan.toggle-status', $karyawan->id) }}"
              onsubmit="return confirm('{{ $karyawan->is_active ? 'Nonaktifkan' : 'Aktifkan' }} karyawan ini?')">
            @csrf @method('PATCH')
            <button type="submit"
                    class="flex items-center gap-2 font-black text-xs  italic
                           px-4 py-2.5 rounded-xl transition-all
                           {{ $karyawan->is_active
                                ? 'bg-red-100 text-red-600 hover:bg-red-200'
                                : 'bg-green-100 text-green-600 hover:bg-green-200' }}">
                <i data-lucide="{{ $karyawan->is_active ? 'user-x' : 'user-check' }}"
                   class="w-3.5 h-3.5"></i>
                {{ $karyawan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kiri ─────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Data Utama --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Data Karyawan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                @foreach ([
                    ['Username / Login', $karyawan->user->username],
                    ['Nama Lengkap',     $karyawan->nama],
                    ['Email',            $karyawan->email ?? '-'],
                    ['Jenis Karyawan',   $karyawan->isTetap() ? 'Karyawan Tetap' : 'Karyawan Kontrak'],
                    ['Divisi',           $karyawan->labelDivisi()],
                    ['Jabatan',          $karyawan->jabatan],
                    ['Tanggal Masuk',    $karyawan->tanggal_masuk->translatedFormat('d F Y')],
                    ['No. HP',           $karyawan->no_hp ?? '-'],
                    ['Status Akun',      $karyawan->is_active ? 'Aktif' : 'Nonaktif'],
                ] as [$label, $value])
                    <div>
                        <p class="text-[9px] font-black text-gray-400   mb-1">
                            {{ $label }}
                        </p>
                        <p class="text-sm font-semibold text-gray-700">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2 mt-5 pt-4 border-t border-gray-50">
                @if ($karyawan->is_shift)
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-[9px] font-black uppercase">Bersifat Shift</span>
                @endif
                @if ($karyawan->gaji_atas_umr)
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[9px] font-black uppercase">Gaji di Atas UMR</span>
                @endif
                @if ($karyawan->uang_makan_by_mitra)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[9px] font-black uppercase">Uang Makan by Mitra</span>
                @else
                    <span class="px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-[9px] font-black uppercase">Uang Makan by CBN</span>
                @endif
            </div>
        </div>

        {{-- Dokumen --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                Dokumen Karyawan
            </h3>
            @forelse ($karyawan->dokumen as $dok)
                <div class="flex items-center justify-between p-3 bg-gray-50
                            rounded-xl border border-gray-100 mb-2 last:mb-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-amber-100 text-amber-600 rounded-xl
                                    flex items-center justify-center">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-black text-gray-700 uppercase">{{ $dok->jenis_dokumen }}</p>
                            <p class="text-[9px] text-gray-400">{{ $dok->uploaded_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                           class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        </a>
                        <form method="POST"
                              action="{{ route('admin.karyawan.hapus-dokumen', [$karyawan->id, $dok->id]) }}"
                              onsubmit="return confirm('Hapus dokumen ini?')">
                            @csrf @method('DELETE')
                            <button class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-6">
                    <i data-lucide="file-x" class="w-8 h-8 text-gray-200 mx-auto mb-2"></i>
                    <p class="text-xs text-gray-400 font-semibold">Belum ada dokumen.</p>
                </div>
            @endforelse
        </div>

    </div>

    {{-- ── Kanan ────────────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Komponen Gaji --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                Komponen Gaji
            </h3>
            @if ($karyawan->komponenGaji)
                @php $kg = $karyawan->komponenGaji @endphp
                <div class="space-y-2.5">
                    @foreach ([
                        ['Gaji Pokok',         'Rp ' . number_format($kg->gaji_pokok, 0, ',', '.')],
                        ['Uang Makan',         $kg->uang_makan !== null ? 'Rp ' . number_format($kg->uang_makan,0,',','.').'/hari' : 'Dibayar Mitra'],
                        ['Uang Transport',     $kg->uang_transport !== null ? 'Rp ' . number_format($kg->uang_transport,0,',','.').'/hari' : 'Dibayar Mitra'],
                        ['BPJS Kesehatan',     $kg->persen_bpjs_kes . '%'],
                        ['BPJS Ketenagakerjaan', $kg->persen_bpjs_tk . '%'],
                    ] as [$lbl, $val])
                        <div class="flex justify-between">
                            <span class="text-[10px] text-gray-400 font-semibold">{{ $lbl }}</span>
                            <span class="text-[11px] font-black text-gray-700">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>
                @if ($kg->gaji_pokok == 0)
                    <div class="mt-3 p-2.5 bg-amber-50 rounded-xl border border-amber-100">
                        <p class="text-[9px] text-amber-600 font-semibold">Gaji pokok belum diisi. Atur di Komponen Gaji.</p>
                    </div>
                @endif
            @else
                <p class="text-xs text-gray-400 text-center py-4">Belum ada data gaji.</p>
            @endif
        </div>

        {{-- Penempatan --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-teal-500 rounded-full"></span>
                Penempatan Aktif
            </h3>
            @if ($karyawan->penempatanAktif)
                <div class="bg-teal-50 rounded-2xl p-4 border border-teal-100">
                    <p class="text-sm font-black text-teal-700 uppercase">
                        {{ $karyawan->penempatanAktif->mitra->nama_mitra }}
                    </p>
                    <p class="text-[9px] text-teal-500 mt-1">
                        Sejak {{ $karyawan->penempatanAktif->tanggal_mulai->translatedFormat('d F Y') }}
                    </p>
                </div>
            @else
                <div class="bg-gray-50 rounded-2xl p-4 text-center border border-gray-100">
                    <p class="text-xs text-gray-400 font-semibold">Belum ditempatkan.</p>
                </div>
            @endif
        </div>

        {{-- Kuota Cuti --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                Kuota Cuti {{ now()->year }}
            </h3>
            @if ($kuota = $karyawan->kuotaCuti->first())
                <div class="text-center mb-3">
                    <p class="text-4xl font-black text-[#1E3A5F]">{{ $kuota->sisa }}</p>
                    <p class="text-[9px] text-gray-400 font-black  mt-1">Hari Tersisa</p>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                    @php $pct = $kuota->kuota_total > 0 ? ($kuota->sisa / $kuota->kuota_total) * 100 : 0 @endphp
                    <div class="bg-[#1E3A5F] h-2 rounded-full" style="width:{{ $pct }}%"></div>
                </div>
                <div class="flex justify-between text-[9px] text-gray-400 font-bold">
                    <span>Terpakai: {{ $kuota->terpakai }}</span>
                    <span>Total: {{ $kuota->kuota_total }}</span>
                </div>
            @else
                <p class="text-xs text-gray-400 text-center py-4">Belum ada data kuota cuti.</p>
            @endif
        </div>

        {{-- Reset Password --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6"
             x-data="{ buka: false }">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-red-500 rounded-full"></span>
                Reset Password
            </h3>
            <button @click="buka = !buka" type="button"
                    class="w-full text-xs font-black text-gray-500  italic
                           border border-gray-200 rounded-xl py-2.5 hover:border-red-300
                           hover:text-red-500 transition-all">
                Klik untuk Reset Password
            </button>
            <form x-show="buka" x-transition
                  method="POST"
                  action="{{ route('admin.karyawan.reset-password', $karyawan->id) }}"
                  class="mt-4 space-y-3">
                @csrf @method('PATCH')
                <input type="password" name="password_baru"
                       placeholder="Password baru (min. 6 karakter)"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50
                              text-sm font-semibold text-gray-700 outline-none
                              focus:border-[#1E3A5F] focus:bg-white">
                <input type="password" name="password_baru_confirmation"
                       placeholder="Ulangi password baru"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50
                              text-sm font-semibold text-gray-700 outline-none
                              focus:border-[#1E3A5F] focus:bg-white">
                <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-black
                               text-xs  italic py-2.5 rounded-xl transition-all">
                    Simpan Password Baru
                </button>
            </form>
        </div>

    </div>
</div>

@endsection