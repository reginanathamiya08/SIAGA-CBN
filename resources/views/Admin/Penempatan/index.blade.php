@extends('admin.sidebar')
@section('title', 'Penempatan Karyawan')

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Penempatan Karyawan</h1>
        <p class="text-gray-500 mt-1 text-sm">Kelola penempatan karyawan kontrak ke mitra</p>
    </div>
    <a href="{{ route('admin.penempatan.create') }}"
       class="flex items-center gap-2 bg-[#1E3A5F] hover:bg-red-600 text-white
              font-black text-xs   italic px-5 py-3 rounded-xl transition-all shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Plotting Karyawan
    </a>
</header>

{{-- Statistik --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Aktif</p>
        <p class="text-2xl font-black text-green-600">{{ $stats['aktif'] }}</p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Selesai</p>
        <p class="text-2xl font-black text-gray-400">{{ $stats['selesai'] }}</p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
        <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Pool Tersedia</p>
        <p class="text-2xl font-black text-blue-600">{{ $stats['tersedia'] }}</p>
    </div>
</div>

{{-- Filter --}}
<div id="filter-container" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.penempatan.index') }}"
          id="filter-form" class="flex flex-wrap items-center gap-3">

        <input type="text" name="cari" value="{{ request('cari') }}" id="search-input"
               oninput="liveSearch(this)"
               placeholder="Cari nama karyawan..."
               class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                      text-gray-700 outline-none focus:border-[#1E3A5F] bg-gray-50 w-52">

        <select name="mitra_id" onchange="updateTable(this.form)"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Mitra</option>
            @foreach ($daftarMitra as $m)
                <option value="{{ $m->id }}"
                        {{ request('mitra_id') == $m->id ? 'selected' : '' }}>
                    {{ $m->is_cabang ? '↳ ' : '' }}{{ $m->nama_mitra }}
                </option>
            @endforeach
        </select>

        <select name="status" onchange="updateTable(this.form)"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold
                       text-gray-700 outline-none bg-gray-50 focus:border-[#1E3A5F]">
            <option value="">Semua Status</option>
            <option value="aktif"   {{ request('status')==='aktif'   ?'selected':'' }}>Aktif</option>
            <option value="selesai" {{ request('status')==='selesai' ?'selected':'' }}>Selesai</option>
        </select>

        <button type="submit"
                class="bg-[#1E3A5F] text-white px-5 py-2.5 rounded-xl font-black
                       text-xs   italic hover:bg-red-600 transition-all">
            Cari
        </button>

        @if (request()->hasAny(['cari','mitra_id','status']))
            <a href="{{ route('admin.penempatan.index') }}"
               class="text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel Penempatan --}}
<div id="table-container" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] font-black text-[#1E3A5F]   tracking-wider
                           border-b border-gray-50 bg-gray-50/50">
                    <th class="px-6 py-4" style="width: 25%">Karyawan</th>
                    <th class="px-6 py-4" style="width: 25%">Mitra / Lokasi</th>
                    <th class="px-6 py-4 text-center" style="width: 15%">Mulai</th>
                    <th class="px-6 py-4 text-center" style="width: 15%">Selesai</th>
                    <th class="px-6 py-4 text-center" style="width: 10%">Status</th>
                    <th class="px-6 py-4 text-center" style="width: 10%">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($penempatan as $p)
                    <tr class="hover:bg-gray-50 transition-all" x-data="{ buka: false }">
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-black text-[#1E3A5F]  ">
                                {{ $p->karyawan->nama }}
                            </p>
                            <p class="text-[9px] text-gray-400">
                                {{ $p->karyawan->jabatan }} • {{ $p->karyawan->user->username }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-gray-700">
                                {{ $p->mitra->nama_mitra }}
                            </p>
                            @if ($p->mitra->is_cabang)
                                <p class="text-[9px] text-gray-400">
                                    Cabang: {{ $p->mitra->induk->nama_mitra ?? '-' }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                            {{ $p->tanggal_mulai->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-semibold text-gray-400">
                            {{ $p->tanggal_selesai?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black  
                                         {{ $p->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($p->status === 'aktif')
                                <button @click="buka = !buka" type="button"
                                        class="p-2 rounded-lg bg-red-50 text-red-500
                                               hover:bg-red-100 transition-all"
                                        title="Akhiri Penempatan">
                                    <i data-lucide="user-minus" class="w-3.5 h-3.5"></i>
                                </button>
                                {{-- Form akhiri --}}
                                <div x-show="buka" x-transition @click.away="buka = false"
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                    <div class="bg-white rounded-3xl shadow-xl p-6 w-80" @click.stop>
                                        <h3 class="font-black text-[#1E3A5F]   text-sm mb-1">
                                            Akhiri Penempatan
                                        </h3>
                                        <p class="text-xs text-gray-500 mb-4">
                                            {{ $p->karyawan->nama }} di {{ $p->mitra->nama_mitra }}
                                        </p>
                                        <form method="POST"
                                              action="{{ route('admin.penempatan.selesai', $p->id) }}">
                                            @csrf @method('PATCH')
                                            <div class="mb-4">
                                                <label class="block text-[11px] font-black text-gray-500
                                                                tracking-widest mb-2">
                                                    Tanggal Selesai <span class="text-red-500">*</span>
                                                </label>
                                                <input type="date" name="tanggal_selesai"
                                                       value="{{ date('Y-m-d') }}"
                                                       min="{{ $p->tanggal_mulai->format('Y-m-d') }}"
                                                       class="w-full px-4 py-3 rounded-xl border border-gray-200
                                                              bg-gray-50 text-sm font-semibold text-gray-700
                                                              outline-none focus:border-[#1E3A5F]">
                                            </div>
                                            <div class="flex gap-3">
                                                <button type="submit"
                                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white
                                                               font-black text-xs   italic py-3
                                                               rounded-xl transition-all">
                                                    Akhiri
                                                </button>
                                                <button @click="buka = false" type="button"
                                                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600
                                                               font-black text-xs   italic py-3
                                                               rounded-xl transition-all">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-[9px] text-gray-300 font-semibold">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <i data-lucide="map-pin" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-semibold">
                                Belum ada data penempatan.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($penempatan->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $penempatan->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
let searchTimeout = null;

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

            // Update Filter
            const newFilter = doc.getElementById('filter-container');
            if (newFilter && filterContainer) {
                const currentSearch = document.getElementById('search-input').value;
                filterContainer.innerHTML = newFilter.innerHTML;
                document.getElementById('search-input').value = currentSearch;
                document.getElementById('search-input').focus();
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

    // Intercept pagination clicks
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