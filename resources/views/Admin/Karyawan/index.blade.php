@extends('admin.sidebar')
@section('title', 'Kelola Karyawan')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">
            Kelola Karyawan
        </h1>
        <p class="text-gray-500 mt-1 text-sm">Manajemen data seluruh karyawan <span class="text-red-600 font-bold">PT Citra Bangun Nagari</span></p>
    </div>
    <a href="{{ route('admin.karyawan.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs  italic px-5 py-3 rounded-xl
              transition-all shadow-sm">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        Tambah Karyawan
    </a>
</header>

{{-- Statistik --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['Jumlah Karyawan',    $stats['total'],    'text-[#1E3A5F]'],
        ['Karyawan Tetap',    $stats['tetap'],    'text-blue-600'],
        ['Karyawan Kontrak',  $stats['kontrak'],  'text-red-600'],
        ['Karyawan Nonaktif', $stats['nonaktif'], 'text-gray-400'],
    ] as [$label, $val, $color])
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
            <p class="text-[9px] font-black text-gray-400   mb-1">
                {{ $label }}
            </p>
            <p class="text-2xl font-black {{ $color }}">{{ $val }}</p>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.karyawan.index') }}"
          class="flex flex-wrap items-center gap-3">
        <input type="text" name="cari" value="{{ request('cari') }}"
               placeholder="Cari nama karyawan..."
               class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                      text-gray-700 outline-none focus:border-[#1E3A5F] bg-gray-50 w-52">

        <select name="jenis" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Jenis</option>
            <option value="tetap"   {{ request('jenis')==='tetap'   ?'selected':'' }}>
                Karyawan Tetap
            </option>
            <option value="kontrak" {{ request('jenis')==='kontrak' ?'selected':'' }}>
                Karyawan Kontrak
            </option>
        </select>

        <select name="divisi" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Divisi</option>
            <optgroup label="Karyawan Tetap">
                <option value="keuangan"       {{ request('divisi')==='keuangan'       ?'selected':'' }}>Keuangan</option>
                <option value="koordinator_cs" {{ request('divisi')==='koordinator_cs' ?'selected':'' }}>Koordinator CS</option>
                <option value="adm_umum"       {{ request('divisi')==='adm_umum'       ?'selected':'' }}>Adm & Umum</option>
            </optgroup>
            <optgroup label="Karyawan Kontrak">
                <option value="HC"   {{ request('divisi')==='HC'   ?'selected':'' }}>HC (Human Capital)</option>
                <option value="umum" {{ request('divisi')==='umum' ?'selected':'' }}>Umum</option>
            </optgroup>
        </select>

        <select name="status" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Status</option>
            <option value="aktif"    {{ request('status')==='aktif'    ?'selected':'' }}>Aktif</option>
            <option value="nonaktif" {{ request('status')==='nonaktif' ?'selected':'' }}>Nonaktif</option>
        </select>

        <button type="submit"
                class="bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl font-black
                       text-xs  italic hover:bg-red-600 transition-all">
            Cari
        </button>

        @if (request()->hasAny(['cari','jenis','divisi','status']))
            <a href="{{ route('admin.karyawan.index') }}"
               class="text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">
                Reset
            </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F]  tracking-wider
                           border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Jabatan / Divisi</th>
                    <th class="px-6 py-4 text-center">Jenis</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($karyawan as $kar)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                            font-black text-xs shrink-0
                                            {{ $kar->isTetap()
                                                 ? 'bg-blue-100 text-blue-700'
                                                 : 'bg-red-100 text-red-700' }}">
                                    {{ strtoupper(substr($kar->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-[#1E3A5F] uppercase">
                                        {{ $kar->nama }}
                                    </p>
                                    <p class="text-[9px] text-gray-400 font-medium">
                                        {{ $kar->email ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-black text-gray-600 font-mono tracking-wider">
                                {{ $kar->user->username }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $kar->jabatan }}
                            </p>
                            <p class="text-[9px] text-gray-400">{{ $kar->labelDivisi() }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase
                                         {{ $kar->isTetap()
                                              ? 'bg-blue-100 text-blue-700'
                                              : 'bg-red-100 text-red-700' }}">
                                {{ $kar->isTetap() ? 'Tetap' : 'Kontrak' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase
                                         {{ $kar->is_active
                                              ? 'bg-green-100 text-green-700'
                                              : 'bg-gray-100 text-gray-500' }}">
                                {{ $kar->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.karyawan.show', $kar->id) }}"
                                   class="p-2 rounded-lg bg-blue-50 text-blue-600
                                          hover:bg-blue-100 transition-all" title="Detail">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('admin.karyawan.edit', $kar->id) }}"
                                   class="p-2 rounded-lg bg-amber-50 text-amber-600
                                          hover:bg-amber-100 transition-all" title="Edit">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.karyawan.toggle-status', $kar->id) }}"
                                      onsubmit="return confirm('{{ $kar->is_active ? 'Nonaktifkan' : 'Aktifkan' }} karyawan {{ addslashes($kar->nama) }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="p-2 rounded-lg transition-all
                                                   {{ $kar->is_active
                                                        ? 'bg-red-50 text-red-500 hover:bg-red-100'
                                                        : 'bg-green-50 text-green-600 hover:bg-green-100' }}"
                                            title="{{ $kar->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i data-lucide="{{ $kar->is_active ? 'user-x' : 'user-check' }}"
                                           class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <i data-lucide="users" class="w-12 h-12 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-semibold">
                                Belum ada data karyawan.
                            </p>
                            <a href="{{ route('admin.karyawan.create') }}"
                               class="mt-3 inline-block text-xs font-black text-[#1E3A5F]
                                      hover:text-red-600  italic transition-colors">
                                + Tambah Karyawan Pertama
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($karyawan->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $karyawan->links() }}
        </div>
    @endif
</div>

@endsection