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
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-blue-900 text-white
              font-black text-xs px-5 py-3 rounded-xl
              transition-all shadow-lg shadow-blue-900/10 active:scale-95">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        TAMBAH KARYAWAN
    </a>
</header>

{{-- Statistik --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['Jumlah Karyawan',    $stats['total'],    'text-[#1E3A5F]', 'users'],
        ['Karyawan Tetap',    $stats['tetap'],    'text-blue-600', 'user-check'],
        ['Karyawan Kontrak',  $stats['kontrak'],  'text-red-600', 'user-plus'],
        ['Karyawan Nonaktif', $stats['nonaktif'], 'text-gray-400', 'user-x'],
    ] as [$label, $val, $color, $icon])
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm text-center hover:-translate-y-1 transition-all duration-300 group">
            <p class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-tight">
                {{ $label }}
            </p>
            <p class="text-2xl font-black {{ $color }}">{{ $val }}</p>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<div id="filter-container" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.karyawan.index') }}"
          id="filter-form" class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="cari" value="{{ request('cari') }}" id="search-input"
                   placeholder="Cari nama karyawan..."
                   class="border-none rounded-xl pl-11 pr-4 py-3 text-sm font-bold
                          text-[#1E3A5F] outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 w-64 transition-all">
        </div>

        <select name="jenis" id="select-jenis" onchange="filterDivisiOptions(true)"
                class="border-none rounded-xl px-4 py-3 text-sm font-bold
                       text-[#1E3A5F] outline-none bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="">Semua Jenis</option>
            <option value="tetap"   {{ request('jenis')==='tetap' || request('jenis')==='JNS-00001' ?'selected':'' }}>Tetap</option>
            <option value="kontrak" {{ request('jenis')==='kontrak' || request('jenis')==='JNS-00002' ?'selected':'' }}>Kontrak</option>
        </select>

        <select name="divisi" id="select-divisi"
                class="border-none rounded-xl px-4 py-3 text-sm font-bold
                       text-[#1E3A5F] outline-none bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="">Semua Divisi</option>
            <optgroup label="Karyawan Tetap" id="optgroup-tetap">
                <option value="keuangan"       {{ request('divisi')==='keuangan'       ?'selected':'' }}>Keuangan</option>
                <option value="koordinator_cs" {{ request('divisi')==='koordinator_cs' ?'selected':'' }}>Koordinator CS</option>
                <option value="adm_umum"       {{ request('divisi')==='adm_umum'       ?'selected':'' }}>Adm & Umum</option>
            </optgroup>
            <optgroup label="Karyawan Kontrak" id="optgroup-kontrak">
                <option value="HC"   {{ request('divisi')==='HC'   ?'selected':'' }}>HC (Human Capital)</option>
                <option value="umum" {{ request('divisi')==='umum' ?'selected':'' }}>Umum</option>
            </optgroup>
        </select>

        <select name="status"
                class="border-none rounded-xl px-4 py-3 text-sm font-bold
                       text-[#1E3A5F] outline-none bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="">Semua Status</option>
            <option value="aktif"    {{ request('status')==='aktif'    ?'selected':'' }}>Aktif</option>
            <option value="nonaktif" {{ request('status')==='nonaktif' ?'selected':'' }}>Nonaktif</option>
        </select>

        <button type="submit"
                class="bg-[#1E3A5F] hover:bg-blue-900 text-white px-6 py-3 rounded-xl font-black
                       text-xs transition-all active:scale-95 shadow-lg shadow-blue-900/10">
            CARI
        </button>

        @if (request()->hasAny(['cari','jenis','divisi','status']))
            <a href="{{ route('admin.karyawan.index') }}"
               class="text-[10px] font-black text-gray-400 hover:text-red-500 transition-colors uppercase">
                Reset
            </a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div id="table-container" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F] border-b border-gray-50 bg-gray-50/50 uppercase">
                    <th class="px-6 py-4" style="width: 30%">Nama</th>
                    <th class="px-6 py-4" style="width: 15%">NIP</th>
                    <th class="px-6 py-4" style="width: 20%">Jabatan / Divisi</th>
                    <th class="px-6 py-4 text-center" style="width: 10%">Jenis</th>
                    <th class="px-6 py-4 text-center" style="width: 10%">Status</th>
                    <th class="px-6 py-4 text-center" style="width: 15%">Aksi</th>
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
                            <span class="text-xs font-bold text-[#1E3A5F] font-mono">
                                {{ $kar->nip }}
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
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase
                                         {{ $kar->is_active
                                              ? 'bg-emerald-50 text-emerald-600'
                                              : 'bg-gray-50 text-gray-400' }}">
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
                               class="mt-3 inline-block text-[10px] font-black text-[#1E3A5F]
                                      hover:text-blue-900 transition-colors uppercase tracking-tight">
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

@push('scripts')
<script>
let searchTimeout = null;

function filterDivisiOptions(resetSelected = false) {
    const jenisSelect = document.getElementById('select-jenis');
    const divisiSelect = document.getElementById('select-divisi');
    const optTetap = document.getElementById('optgroup-tetap');
    const optKontrak = document.getElementById('optgroup-kontrak');
    if (!jenisSelect || !divisiSelect) return;

    const val = jenisSelect.value;
    if (resetSelected) {
        divisiSelect.value = '';
    }

    if (val === 'tetap' || val === 'JNS-00001') {
        if (optTetap) { optTetap.hidden = false; optTetap.style.display = ''; }
        if (optKontrak) { optKontrak.hidden = true; optKontrak.style.display = 'none'; }
    } else if (val === 'kontrak' || val === 'JNS-00002') {
        if (optTetap) { optTetap.hidden = true; optTetap.style.display = 'none'; }
        if (optKontrak) { optKontrak.hidden = false; optKontrak.style.display = ''; }
    } else {
        if (optTetap) { optTetap.hidden = false; optTetap.style.display = ''; }
        if (optKontrak) { optKontrak.hidden = false; optKontrak.style.display = ''; }
    }
}

document.addEventListener('DOMContentLoaded', () => filterDivisiOptions(false));

function updateTable(form) {
    const url = new URL(form.action || window.location.href);
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    url.search = params.toString();

    const container = document.getElementById('table-container');
    const filterContainer = document.getElementById('filter-container');
    
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update Tabel
            const newContent = doc.getElementById('table-container');
            if (newContent && container) {
                container.innerHTML = newContent.innerHTML;
            }

            // Update Filter (untuk sinkronisasi dropdown)
            const newFilter = doc.getElementById('filter-container');
            if (newFilter && filterContainer) {
                // Simpan nilai input cari agar tidak hilang saat mengetik
                const currentSearch = document.getElementById('search-input').value;
                filterContainer.innerHTML = newFilter.innerHTML;
                document.getElementById('search-input').value = currentSearch;
                document.getElementById('search-input').focus();
                // Pindahkan kursor ke akhir
                const val = document.getElementById('search-input').value;
                document.getElementById('search-input').value = '';
                document.getElementById('search-input').value = val;
            }

            if (window.lucide) window.lucide.createIcons();
            window.history.pushState({}, '', url);
        });
}

function liveSearch(input) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateTable(input.form);
    }, 400);
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    if (searchInput && searchInput.value !== '') {
        searchInput.focus();
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }

    // Intercept pagination clicks (only links inside nav)
    document.addEventListener('click', e => {
        const link = e.target.closest('#table-container nav a');
        if (link && link.href) {
            e.preventDefault();
            const container = document.getElementById('table-container');
            
            fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('table-container');
                    if (newContent && container) {
                        container.innerHTML = newContent.innerHTML;
                        if (window.lucide) window.lucide.createIcons();
                        window.history.pushState({}, '', link.href);
                    }
                });
        }
    });
});
</script>
@endpush

@endsection
