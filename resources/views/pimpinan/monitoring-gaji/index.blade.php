@extends('pimpinan.sidebar')

@section('title', 'Monitoring Gaji')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F]">Monitoring Gaji</h1>
            <p class="text-gray-500 text-sm">Pantau pengeluaran gaji karyawan <span class="text-red-600 font-bold">PT CBN</span></p>
        </div>
        
        <div class="flex items-center gap-2">
            <a id="btn-export-excel" href="{{ route('pimpinan.monitoring-gaji.export', request()->all()) }}" 
               @if(!$periodeId)
               onclick="event.preventDefault(); Swal.fire({ icon: 'warning', title: 'Belum Bisa Ekspor Excel', text: 'Periode penggajian untuk bulan yang dipilih belum tersedia.', confirmButtonColor: '#1E3A5F', confirmButtonText: 'Mengerti', customClass: { popup: 'rounded-3xl', confirmButton: 'rounded-xl font-bold px-6 py-2.5' } });"
               @endif
               class="flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-emerald-100 hover:-translate-y-0.5 active:scale-95">
                <i data-lucide="download" class="w-4 h-4"></i>
                Ekspor Excel
            </a>
        </div>
    </header>

    <!-- Tab Card Gaji (dengan Filter Terintegrasi) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Header: Tab Buttons + Filter dalam satu baris -->
        <form id="form-filter" action="{{ route('pimpinan.monitoring-gaji.index') }}" method="GET" onsubmit="event.preventDefault(); validateAndSubmitPeriode();">
            
            <!-- Baris 1: Filter Dropdown Bulan & Tahun -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-wider mb-0.5">Filter Periode Penggajian</p>
                    <p class="text-xs text-gray-500 font-semibold">Pilih bulan dan tahun penggajian</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Dropdown Bulan --}}
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-bold text-gray-500">Bulan:</label>
                        <select name="bulan" id="select-bulan" onchange="validateAndSubmitPeriode()"
                                class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($bulan == $m)>
                                    {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Tahun --}}
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-bold text-gray-500">Tahun:</label>
                        <select name="tahun" id="select-tahun" onchange="validateAndSubmitPeriode()"
                                class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500 transition-all shadow-sm">
                            <option value="">Semua Tahun</option>
                            @foreach(range(now()->year - 2, now()->year) as $y)
                                <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="bg-[#1E3A5F] hover:bg-blue-900 text-white text-xs font-black px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-sm active:scale-95">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        Filter
                    </button>

                    @if($bulan || $tahun || $mitraId)
                    <a href="{{ route('pimpinan.monitoring-gaji.index') }}"
                       class="text-xs font-bold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all"
                       title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Baris 2: Tab Buttons + Filter Mitra -->
            <div class="px-6 pt-4 pb-3 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center gap-3">
                <button id="btn-tab-gaji-tetap" type="button" onclick="switchTabGaji('tetap')"
                    class="tab-btn-gaji active-tab-gaji flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all">
                    <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                    Karyawan Tetap
                    <span id="badge-count-tetap" class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-teal-100 text-teal-700">
                        {{ $slipTetap->count() }}
                    </span>
                </button>
                <button id="btn-tab-gaji-kontrak" type="button" onclick="switchTabGaji('kontrak')"
                    class="tab-btn-gaji flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition-all">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Karyawan Kontrak
                    <span id="badge-count-kontrak" class="ml-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-100 text-amber-700">
                        {{ $slipKontrak->count() }}
                    </span>
                </button>

                <!-- Filter di kanan tab buttons -->
                <div class="ml-auto flex flex-wrap items-center gap-2">
                    <!-- Mitra: hanya tampil di tab Kontrak -->
                    <select id="filter-mitra-gaji" name="mitra_id" onchange="validateAndSubmitPeriode()"
                        class="bg-gray-100 border-none rounded-xl px-3 py-2 text-xs font-bold text-[#1E3A5F] focus:ring-2 focus:ring-blue-500 transition-all hidden">
                        <option value="">Semua Mitra</option>
                        @foreach($semuaMitra as $m)
                            <option value="{{ $m->id }}" {{ $mitraId == $m->id ? 'selected' : '' }}>
                                @if($m->is_cabang)
                                    ↳ {{ $m->nama_mitra }} (Cabang)
                                @else
                                    {{ $m->nama_mitra }} (Mitra Utama)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- Table Content Container (Updated via AJAX) -->
        <div id="table-content-container" class="relative">
            @include('pimpinan.monitoring-gaji._table_content')
        </div>

    </div>{{-- /Tab Card Gaji --}}

    <!-- Status Banner Area (Kirim Keputusan Penggajian di bawah tabel) -->
    <div id="status-banner-container" class="pt-2">
        @include('pimpinan.monitoring-gaji._status_banner')
    </div>
</div>

<style>
.tab-btn-gaji { color: #6b7280; background: transparent; }
.tab-btn-gaji.active-tab-gaji { background: #1E3A5F; color: #fff; }
</style>

@push('scripts')
<script>
    const availablePeriods = @json($periodeAvailable ?? []);
    const namaBulanMap = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April', 5: 'Mei', 6: 'Juni',
        7: 'Juli', 8: 'Agustus', 9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    let currentBulanVal = "{{ $bulan }}";
    let currentTahunVal = "{{ $tahun }}";
    let activeTabState  = 'tetap';

    function validateAndSubmitPeriode() {
        const selBulan = document.getElementById('select-bulan').value;
        const selTahun = document.getElementById('select-tahun').value;
        const selMitra = document.getElementById('filter-mitra-gaji').value;

        if (!selBulan || !selTahun) {
            fetchTableData(selBulan, selTahun, selMitra);
            return;
        }

        const exists = availablePeriods.some(p => {
            const bMatch = p.month == selBulan;
            const yMatch = p.year == selTahun;
            const nMatch = p.name && p.name.toLowerCase().includes(namaBulanMap[selBulan].toLowerCase()) && p.name.includes(selTahun);
            return (bMatch && yMatch) || nMatch;
        });

        if (!exists) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Periode Penggajian Belum Ada',
                    text: `Periode penggajian untuk bulan ${namaBulanMap[selBulan]} ${selTahun} belum diterbitkan atau belum dibuat oleh Admin.`,
                    confirmButtonColor: '#1E3A5F',
                    confirmButtonText: 'Saya Mengerti',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5'
                    }
                });
            } else {
                alert(`Periode penggajian untuk bulan ${namaBulanMap[selBulan]} ${selTahun} belum diterbitkan.`);
            }

            // Kembalikan pilihan dropdown ke bulan sebelumnya tanpa berpindah halaman
            document.getElementById('select-bulan').value = currentBulanVal;
            document.getElementById('select-tahun').value = currentTahunVal;
            return false;
        }

        // Lakukan pembaruan data tabel secara AJAX (In-Place / Tanpa Full Reload)
        fetchTableData(selBulan, selTahun, selMitra);
    }

    function fetchTableData(bulan, tahun, mitraId) {
        const url = new URL("{{ route('pimpinan.monitoring-gaji.index') }}");
        if (bulan) url.searchParams.set('bulan', bulan);
        if (tahun) url.searchParams.set('tahun', tahun);
        if (mitraId) url.searchParams.set('mitra_id', mitraId);

        const tableContainer = document.getElementById('table-content-container');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {
                document.getElementById('status-banner-container').innerHTML = data.htmlBanner;
                document.getElementById('table-content-container').innerHTML = data.htmlTable;
                document.getElementById('badge-count-tetap').innerText = data.countTetap;
                document.getElementById('badge-count-kontrak').innerText = data.countKontrak;
                
                const exportBtn = document.getElementById('btn-export-excel');
                if (exportBtn) {
                    exportBtn.href = data.exportUrl;
                    exportBtn.removeAttribute('onclick');
                }

                currentBulanVal = bulan;
                currentTahunVal = tahun;

                // Pertahankan tab yang sedang aktif
                switchTabGaji(activeTabState);

                // Re-initialize Lucide icons & Alpine if any
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Update URL secara halus tanpa me-reload layar
                window.history.pushState({}, '', url);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Periode Penggajian Belum Ada',
                        text: data.message,
                        confirmButtonColor: '#1E3A5F',
                        confirmButtonText: 'Saya Mengerti',
                        customClass: {
                            popup: 'rounded-3xl',
                            confirmButton: 'rounded-xl font-bold px-6 py-2.5'
                        }
                    });
                }
                document.getElementById('select-bulan').value = currentBulanVal;
                document.getElementById('select-tahun').value = currentTahunVal;
            }
        })
        .catch(err => {
            console.error('Error fetching table data:', err);
        });
    }

    function switchTabGaji(tab) {
        activeTabState = tab;
        const panels = { tetap: 'panel-gaji-tetap', kontrak: 'panel-gaji-kontrak' };
        const btns   = { tetap: 'btn-tab-gaji-tetap', kontrak: 'btn-tab-gaji-kontrak' };
        Object.keys(panels).forEach(key => {
            const p = document.getElementById(panels[key]);
            const b = document.getElementById(btns[key]);
            if (p) p.classList.toggle('hidden', key !== tab);
            if (b) b.classList.toggle('active-tab-gaji', key === tab);
        });
        const mitraFilter = document.getElementById('filter-mitra-gaji');
        if (mitraFilter) mitraFilter.classList.toggle('hidden', tab !== 'kontrak');
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if($mitraId)
        switchTabGaji('kontrak');
        @endif
    });
</script>
@endpush
@endsection
