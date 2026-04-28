@extends('admin.sidebar')
@section('title', 'Detail Mitra')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map-detail { height: 280px; border-radius: 16px; z-index: 1; }
</style>
@endpush

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.mitra.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="flex-1">
        <h1 class="text-2xl font-black text-[#1E3A5F] ">
            {{ $mitra->nama_mitra }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            @if ($mitra->is_cabang)
                Cabang dari: <strong>{{ $mitra->induk->nama_mitra }}</strong>
            @else
                Mitra Induk
            @endif
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.penempatan.create') }}?mitra_id={{ $mitra->id }}"
           class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white
                  font-black text-xs   italic px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
            Tambah Karyawan
        </a>
        <a href="{{ route('admin.mitra.edit', $mitra->id) }}"
           class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white
                  font-black text-xs   italic px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
            Edit
        </a>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info + Peta --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Peta --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Lokasi Mitra
            </h3>
            <div id="map-detail"></div>
            <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-50">
                <div class="text-center">
                    <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Latitude</p>
                    <p class="text-xs font-black text-gray-700 font-mono">{{ $mitra->latitude }}</p>
                </div>
                <div class="text-center border-x border-gray-100">
                    <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Longitude</p>
                    <p class="text-xs font-black text-gray-700 font-mono">{{ $mitra->longitude }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[9px] font-black text-gray-400   tracking-widest mb-1">Radius</p>
                    <p class="text-xs font-black text-blue-600">{{ $mitra->radius_meter }} m</p>
                </div>
            </div>
        </div>

        {{-- Karyawan Aktif --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] flex items-center gap-2">
                    <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                    Karyawan Aktif
                </h3>
                <span class="text-[9px] font-black text-gray-400  ">
                    {{ $mitra->penempatan->count() }} orang
                </span>
            </div>
            @forelse ($mitra->penempatan as $penempatan)
                <div class="flex items-center justify-between px-6 py-3.5
                            border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-all"
                     x-data="{ buka: false }">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-green-100 text-green-700 rounded-xl
                                    flex items-center justify-center font-black text-xs shrink-0">
                            {{ strtoupper(substr($penempatan->karyawan->nama, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-[11px] font-black text-[#1E3A5F]  ">
                                {{ $penempatan->karyawan->nama }}
                            </p>
                            <p class="text-[9px] text-gray-400 font-medium">
                                {{ $penempatan->karyawan->jabatan }} •
                                Mulai {{ $penempatan->tanggal_mulai->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-[9px] font-black  ">
                            Aktif
                        </span>
                        <button @click="buka = !buka" type="button"
                                class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all"
                                title="Akhiri Penempatan">
                            <i data-lucide="user-minus" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    {{-- Form akhiri penempatan --}}
                    <div x-show="buka" x-transition
                         class="absolute right-6 mt-20 bg-white border border-gray-200
                                rounded-2xl shadow-lg p-4 z-10 w-64">
                        <p class="text-xs font-black text-gray-700 mb-3">
                            Akhiri penempatan {{ $penempatan->karyawan->nama }}?
                        </p>
                        <form method="POST"
                              action="{{ route('admin.penempatan.selesai', $penempatan->id) }}">
                            @csrf @method('PATCH')
                            <div class="mb-3">
                                <label class="block text-[10px] font-black text-gray-400   mb-1">
                                    Tanggal Selesai
                                </label>
                                <input type="date" name="tanggal_selesai"
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200
                                              bg-gray-50 text-sm font-semibold text-gray-700 outline-none">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="flex-1 bg-red-600 text-white text-xs font-black
                                               py-2 rounded-xl   italic hover:bg-red-700">
                                    Akhiri
                                </button>
                                <button @click="buka = false" type="button"
                                        class="flex-1 bg-gray-100 text-gray-600 text-xs font-black
                                               py-2 rounded-xl   italic hover:bg-gray-200">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center">
                    <i data-lucide="users" class="w-8 h-8 text-gray-200 mx-auto mb-2"></i>
                    <p class="text-xs text-gray-400 font-semibold">Belum ada karyawan aktif.</p>
                </div>
            @endforelse
        </div>

    </div>

    {{-- Sidebar Kanan --}}
    <div class="space-y-5">

        {{-- Cabang --}}
        @if (!$mitra->is_cabang)
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] mb-4
                           flex items-center gap-2">
                    <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                    Cabang
                </h3>
                @forelse ($mitra->cabang as $cabang)
                    <a href="{{ route('admin.mitra.show', $cabang->id) }}"
                       class="flex items-center justify-between p-3 rounded-xl
                              bg-gray-50 hover:bg-purple-50 border border-gray-100
                              hover:border-purple-200 transition-all mb-2 last:mb-0">
                        <div>
                            <p class="text-xs font-black text-[#1E3A5F]  ">{{ $cabang->nama_mitra }}</p>
                            <p class="text-[9px] text-gray-400">Radius: {{ $cabang->radius_meter }}m</p>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
                    </a>
                @empty
                    <p class="text-xs text-gray-400 text-center py-3">Belum ada cabang.</p>
                @endforelse
                <a href="{{ route('admin.mitra.create') }}?induk={{ $mitra->id }}"
                   class="mt-3 flex items-center justify-center gap-2 text-xs font-black
                          text-[#1E3A5F]   italic border border-dashed border-gray-300
                          rounded-xl py-2.5 hover:border-[#1E3A5F] hover:bg-blue-50 transition-all">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Tambah Cabang
                </a>
            </div>
        @endif

        {{-- Riwayat Penempatan --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50">
                <h3 class="font-black text-[#1E3A5F]   italic text-[11px] flex items-center gap-2">
                    <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                    Riwayat Penempatan
                </h3>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                @forelse ($riwayat as $r)
                    <div class="px-5 py-3">
                        <p class="text-[11px] font-black text-gray-700  ">
                            {{ $r->karyawan->nama }}
                        </p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[9px] text-gray-400">
                                {{ $r->tanggal_mulai->format('d M Y') }}
                                @if ($r->tanggal_selesai)
                                    — {{ $r->tanggal_selesai->format('d M Y') }}
                                @endif
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-black  
                                         {{ $r->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $r->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-xs text-gray-400 font-semibold">
                        Belum ada riwayat.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map-detail', { zoomControl: true, scrollWheelZoom: false })
           .setView([{{ $mitra->latitude }}, {{ $mitra->longitude }}], 16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

var markerIcon = L.divIcon({
    className: '',
    html: '<div style="background:#1E3A5F;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
    iconSize: [16,16], iconAnchor: [8,8],
});

L.marker([{{ $mitra->latitude }}, {{ $mitra->longitude }}], { icon: markerIcon })
 .addTo(map)
 .bindPopup('<strong>{{ addslashes($mitra->nama_mitra) }}</strong><br>Radius: {{ $mitra->radius_meter }}m');

L.circle([{{ $mitra->latitude }}, {{ $mitra->longitude }}], {
    radius: {{ $mitra->radius_meter }},
    color: '#1E3A5F', fillColor: '#1E3A5F', fillOpacity: 0.08,
    weight: 2, dashArray: '6,4',
}).addTo(map);
</script>
@endpush