@extends('admin.sidebar')
@section('title', 'Detail Mitra — ' . $mitra->nama_mitra)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map-detail { height: 300px; border-radius: 1.25rem; z-index: 1; }
</style>
@endpush

@section('content')

{{-- Top Header / Navigation --}}
<header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.mitra.index') }}"
           class="p-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 transition-all text-gray-600 shadow-sm shrink-0"
           title="Kembali ke Daftar Mitra">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl font-black text-[#1E3A5F]">
                    {{ $mitra->nama_mitra }}
                </h1>
                @if ($mitra->is_pusat)
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-full uppercase tracking-wide">
                        Kantor Pusat
                    </span>
                @elseif ($mitra->is_cabang)
                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-black rounded-full uppercase tracking-wide">
                        Cabang
                    </span>
                @else
                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-black rounded-full uppercase tracking-wide">
                        Mitra Induk
                    </span>
                @endif
            </div>
            <p class="text-gray-500 mt-1 text-xs font-medium">
                @if ($mitra->is_cabang && $mitra->induk)
                    Cabang dari: <a href="{{ route('admin.mitra.show', $mitra->induk->id) }}" class="font-bold text-blue-600 hover:underline">{{ $mitra->induk->nama_mitra }}</a>
                @elseif($mitra->is_pusat)
                    Lokasi Utama PT Citra Bangun Nagari
                @else
                    Mitra Utama (Memiliki {{ $mitra->cabang->count() }} Cabang)
                @endif
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('admin.penempatan.create') }}?mitra_id={{ $mitra->id }}"
           class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Tambah Karyawan
        </a>
        <a href="{{ route('admin.mitra.edit', $mitra->id) }}"
           class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm">
            <i data-lucide="pencil" class="w-4 h-4"></i>
            Edit Mitra
        </a>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content Column --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Map & Coordinates Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-[#1E3A5F] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                    Lokasi Mitra & Geofence
                </h3>
                <span class="text-[10px] font-bold text-gray-400 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">
                    OpenStreetMap
                </span>
            </div>

            <div id="map-detail" class="shadow-inner border border-gray-100 mb-4"></div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-slate-50/70 border border-slate-100 p-3 rounded-2xl text-center">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">Latitude</p>
                    <p class="text-xs font-bold text-slate-800 font-mono truncate">{{ $mitra->latitude }}</p>
                </div>
                <div class="bg-slate-50/70 border border-slate-100 p-3 rounded-2xl text-center">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mb-1">Longitude</p>
                    <p class="text-xs font-bold text-slate-800 font-mono truncate">{{ $mitra->longitude }}</p>
                </div>
                <div class="bg-blue-50/50 border border-blue-100 p-3 rounded-2xl text-center">
                    <p class="text-[9px] font-black text-blue-500 uppercase tracking-wider mb-1">Radius Presensi</p>
                    <p class="text-xs font-extrabold text-blue-700">{{ $mitra->radius_meter }} Meter</p>
                </div>
                <div class="bg-emerald-50/50 border border-emerald-100 p-3 rounded-2xl text-center">
                    <p class="text-[9px] font-black text-emerald-600 uppercase tracking-wider mb-1">IP Public</p>
                    <p class="text-xs font-bold text-emerald-700 font-mono truncate">{{ $mitra->ip_public ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Active Employees Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/40">
                <h3 class="font-black text-[#1E3A5F] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                    Karyawan Aktif Saat Ini
                </h3>
                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full">
                    {{ $mitra->penempatan->count() + $karyawanTetap->count() }} orang
                </span>
            </div>

            <div class="divide-y divide-gray-50">
                {{-- Loop Karyawan Kontrak (Penempatan) --}}
                @forelse ($mitra->penempatan as $penempatan)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-all"
                         x-data="{ buka: false }">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 bg-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center font-black text-xs shrink-0 shadow-sm">
                                {{ strtoupper(substr($penempatan->karyawan->nama, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold text-[#1E3A5F] truncate">
                                    {{ $penempatan->karyawan->nama }}
                                </p>
                                <p class="text-[10px] text-gray-500 font-medium mt-0.5">
                                    {{ $penempatan->karyawan->jabatan }} • Mulai {{ $penempatan->tanggal_mulai->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0">
                            <span class="px-2.5 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg text-[10px] font-extrabold uppercase">
                                Aktif
                            </span>
                            <button @click="buka = true" type="button"
                                    class="p-2 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition-all"
                                    title="Akhiri Penempatan">
                                <i data-lucide="user-minus" class="w-4 h-4"></i>
                            </button>

                            {{-- Modal Akhiri Penempatan --}}
                            <template x-teleport="body">
                                <div x-show="buka" x-transition.opacity class="fixed inset-0 z-[3000] flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
                                    <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm border border-gray-100" @click.away="buka = false">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="w-10 h-10 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center font-bold shrink-0">
                                                <i data-lucide="user-minus" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-black text-[#1E3A5F] text-sm">Akhiri Penempatan</h3>
                                                <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $penempatan->karyawan->nama }}</p>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.penempatan.selesai', $penempatan->id) }}">
                                            @csrf @method('PATCH')
                                            <div class="mb-4">
                                                <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase">TANGGAL SELESAI</label>
                                                <input type="date" name="tanggal_selesai" value="{{ date('Y-m-d') }}" required
                                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-xs font-bold text-[#1E3A5F] outline-none focus:ring-2 focus:ring-red-500">
                                            </div>
                                            <div class="flex gap-2.5">
                                                <button type="submit" class="flex-1 bg-red-600 text-white font-bold text-xs py-2.5 rounded-xl hover:bg-red-700 transition-all shadow-md shadow-red-600/20">
                                                    AKHIRI
                                                </button>
                                                <button @click="buka = false" type="button" class="flex-1 bg-gray-100 text-gray-600 font-bold text-xs py-2.5 rounded-xl hover:bg-gray-200 transition-all">
                                                    BATAL
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                @empty
                @endforelse

                {{-- Loop Karyawan Tetap (Khusus Kantor Pusat) --}}
                @foreach ($karyawanTetap as $tetap)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-all">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-2xl flex items-center justify-center font-black text-xs shrink-0 shadow-sm">
                                {{ strtoupper(substr($tetap->nama, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold text-[#1E3A5F] truncate">
                                    {{ $tetap->nama }}
                                </p>
                                <p class="text-[10px] text-gray-500 font-medium mt-0.5">
                                    {{ $tetap->jabatan }} • Karyawan Tetap CBN
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-700 rounded-lg text-[10px] font-extrabold uppercase">
                                Pusat
                            </span>
                        </div>
                    </div>
                @endforeach

                @if($mitra->penempatan->isEmpty() && $karyawanTetap->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <p class="text-xs font-bold text-gray-500">Belum ada karyawan aktif yang di-plotting di mitra ini.</p>
                        <a href="{{ route('admin.penempatan.create') }}?mitra_id={{ $mitra->id }}" class="mt-2 inline-block text-xs font-black text-blue-600 hover:underline">
                            + Plotting Karyawan Sekarang
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Right Sidebar Column --}}
    <div class="space-y-6">

        {{-- Working Hours Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F] text-xs uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                Jam Kerja (Wajib)
            </h3>
            <div class="flex items-center justify-between p-4 rounded-2xl bg-blue-50/50 border border-blue-100">
                <div class="text-center flex-1">
                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">JAM MASUK</p>
                    <p class="text-base font-black text-[#1E3A5F]">{{ substr($mitra->jam_masuk, 0, 5) }}</p>
                </div>
                <div class="w-px h-8 bg-blue-200/60 mx-3"></div>
                <div class="text-center flex-1">
                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">JAM PULANG</p>
                    <p class="text-base font-black text-[#1E3A5F]">{{ substr($mitra->jam_pulang, 0, 5) }}</p>
                </div>
            </div>
            <p class="mt-3 text-[10px] text-gray-400 font-medium italic">
                * Jam kerja ini berlaku untuk presensi karyawan non-satpam di lokasi ini.
            </p>
        </div>

        {{-- Branches Section (If Mitra Induk) --}}
        @if (!$mitra->is_cabang)
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-black text-[#1E3A5F] text-xs uppercase tracking-wider flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-purple-500 rounded-full"></span>
                        Daftar Cabang
                    </h3>
                    <span class="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md">
                        {{ $mitra->cabang->count() }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @forelse ($mitra->cabang as $cabang)
                        <a href="{{ route('admin.mitra.show', $cabang->id) }}"
                           class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 hover:bg-purple-50/60 border border-gray-100 hover:border-purple-200 transition-all group">
                            <div class="min-w-0 pr-2">
                                <p class="text-xs font-black text-[#1E3A5F] truncate group-hover:text-purple-700 transition-colors">
                                    {{ $cabang->nama_mitra }}
                                </p>
                                <p class="text-[10px] text-gray-400 font-medium mt-0.5">
                                    Radius: {{ $cabang->radius_meter }}m • {{ $cabang->penempatan_count ?? $cabang->penempatan()->where('status','aktif')->count() }} Karyawan
                                </p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-purple-600 transition-colors shrink-0"></i>
                        </a>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-4 italic">Belum ada cabang terdaftar.</p>
                    @endforelse
                </div>

                <a href="{{ route('admin.mitra.create') }}?induk={{ $mitra->id }}"
                   class="flex items-center justify-center gap-2 text-xs font-black text-[#1E3A5F] bg-gray-50 border border-dashed border-gray-300 rounded-2xl py-3 hover:border-[#1E3A5F] hover:bg-blue-50/50 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Cabang Baru
                </a>
            </div>
        @endif

        {{-- Placement History Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/40">
                <h3 class="font-black text-[#1E3A5F] text-xs uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-amber-500 rounded-full"></span>
                    Riwayat Penempatan
                </h3>
            </div>
            <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                @forelse ($riwayat as $r)
                    <div class="px-6 py-3.5 hover:bg-gray-50/60 transition-all">
                        <p class="text-xs font-extrabold text-[#1E3A5F]">
                            {{ $r->karyawan->nama }}
                        </p>
                        <div class="flex items-center justify-between gap-2 mt-1">
                            <span class="text-[10px] text-gray-500 font-medium">
                                {{ $r->tanggal_mulai->format('d M Y') }}
                                @if ($r->tanggal_selesai)
                                    — {{ $r->tanggal_selesai->format('d M Y') }}
                                @else
                                    — Sekarang
                                @endif
                            </span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase
                                         {{ $r->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $r->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-xs text-gray-400 font-medium">
                        Belum ada data riwayat penempatan.
                    </div>
                @endforelse
            </div>
            @if ($riwayat->hasPages())
                <div class="p-3 border-t border-gray-100 bg-gray-50/30">
                    {{ $riwayat->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map-detail', { zoomControl: true, scrollWheelZoom: false })
               .setView([{{ $mitra->latitude }}, {{ $mitra->longitude }}], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var markerIcon = L.divIcon({
        className: '',
        html: '<div style="background:#1E3A5F;width:18px;height:18px;border-radius:50%;border:3px solid white;box-shadow:0 3px 10px rgba(0,0,0,0.3)"></div>',
        iconSize: [18,18], iconAnchor: [9,9],
    });

    L.marker([{{ $mitra->latitude }}, {{ $mitra->longitude }}], { icon: markerIcon })
     .addTo(map)
     .bindPopup('<strong style="color:#1E3A5F">{{ addslashes($mitra->nama_mitra) }}</strong><br>Radius Geofence: {{ $mitra->radius_meter }}m');

    L.circle([{{ $mitra->latitude }}, {{ $mitra->longitude }}], {
        radius: {{ $mitra->radius_meter }},
        color: '#1E3A5F', fillColor: '#1E3A5F', fillOpacity: 0.1,
        weight: 2, dashArray: '6,4',
    }).addTo(map);

    setTimeout(function() {
        map.invalidateSize();
    }, 200);
});
</script>
@endpush

