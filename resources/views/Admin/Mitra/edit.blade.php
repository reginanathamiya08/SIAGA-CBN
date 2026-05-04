@extends('admin.sidebar')
@section('title', 'Edit Mitra')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map { height: 360px; border-radius: 16px; z-index: 1; }
</style>
@endpush

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.mitra.show', $mitra->id) }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">
            Edit: {{ $mitra->nama_mitra }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">Klik peta atau edit koordinat manual</p>
    </div>
</header>

<form method="POST" action="{{ route('admin.mitra.update', $mitra->id) }}">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    <div class="lg:col-span-3 space-y-4">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Lokasi di Peta
            </h3>
            <p class="text-[10px] text-gray-400 font-semibold mb-3">
                🖱️ Klik atau drag marker untuk mengubah lokasi.
            </p>
            <div id="map"></div>
            <div class="mt-4 flex items-center gap-3">
                <label class="text-[10px] font-black text-gray-500 tracking-widest whitespace-nowrap">
                    Radius:
                </label>
                <input type="range" id="slider-radius"
                       min="10" max="500" value="{{ $mitra->radius_meter }}"
                       class="flex-1 accent-[#1E3A5F]"
                       oninput="updateRadius(this.value)">
                <span id="label-radius" class="text-[11px] font-black text-[#1E3A5F] w-16">
                    {{ $mitra->radius_meter }} m
                </span>
            </div>
        </div>

        @include('admin.mitra._shift_config')
    </div>

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-teal-500 rounded-full"></span>
                Informasi Mitra
            </h3>

            <div class="space-y-4">

                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                        Nama Mitra <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_mitra"
                           value="{{ old('nama_mitra', $mitra->nama_mitra) }}"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 outline-none transition-all
                                  @error('nama_mitra') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('nama_mitra')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                        Mitra Induk
                    </label>
                    <select name="mitra_induk_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                                   text-sm font-semibold text-gray-700 outline-none
                                   focus:border-[#1E3A5F] focus:bg-white">
                        <option value="">-- Ini adalah mitra induk --</option>
                        @foreach ($daftarInduk as $induk)
                            <option value="{{ $induk->id }}"
                                    {{ old('mitra_induk_id', $mitra->mitra_induk_id) == $induk->id ? 'selected' : '' }}>
                                {{ $induk->nama_mitra }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                        Latitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="latitude" id="input-lat"
                           value="{{ old('latitude', $mitra->latitude) }}" step="any"
                           oninput="updateMarkerFromInput()"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  font-mono text-gray-700 outline-none transition-all
                                  @error('latitude') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('latitude')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                        Longitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="longitude" id="input-lon"
                           value="{{ old('longitude', $mitra->longitude) }}" step="any"
                           oninput="updateMarkerFromInput()"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  font-mono text-gray-700 outline-none transition-all
                                  @error('longitude') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('longitude')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                        Radius Absensi (meter) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="radius_meter" id="input-radius"
                           value="{{ old('radius_meter', $mitra->radius_meter) }}"
                           min="10" max="5000"
                           oninput="syncSlider(this.value)"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 outline-none transition-all
                                  @error('radius_meter') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('radius_meter')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- IP Public --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-500 tracking-widest mb-2">
                        IP PUBLIC KANTOR <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="ip_public"
                           value="{{ old('ip_public', $mitra->ip_public) }}"
                           placeholder="Contoh: 103.12.34.56"
                           required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 outline-none transition-all
                                  @error('ip_public') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('ip_public')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[9px] text-gray-400 font-medium">
                        IP Public jaringan WiFi kantor mitra. Karyawan <strong>hanya bisa absen</strong>
                        saat terhubung ke jaringan ini. Tanyakan ke IT/ISP jika tidak tahu IP-nya.
                    </p>
                </div>

            </div>
        </div>

        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                       tracking-widest py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i>
            Simpan Perubahan
        </button>

        <a href="{{ route('admin.mitra.show', $mitra->id) }}"
           class="block text-center text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">
            Batal
        </a>
    </div>
</div>

</form>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var lat = {{ $mitra->latitude }};
var lon = {{ $mitra->longitude }};
var radius = {{ $mitra->radius_meter }};

var map = L.map('map').setView([lat, lon], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

var markerIcon = L.divIcon({
    className: '',
    html: '<div style="background:#1E3A5F;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
    iconSize: [16, 16], iconAnchor: [8, 8],
});

var marker = L.marker([lat, lon], { draggable: true, icon: markerIcon }).addTo(map);
var circle = L.circle([lat, lon], {
    radius: radius, color: '#1E3A5F',
    fillColor: '#1E3A5F', fillOpacity: 0.08, weight: 2, dashArray: '6,4',
}).addTo(map);

marker.on('dragend', function(e) {
    var p = e.target.getLatLng();
    setKoordinat(p.lat.toFixed(7), p.lng.toFixed(7));
    circle.setLatLng([p.lat, p.lng]);
});

map.on('click', function(e) {
    var lt = e.latlng.lat.toFixed(7), ln = e.latlng.lng.toFixed(7);
    marker.setLatLng([lt, ln]); circle.setLatLng([lt, ln]);
    setKoordinat(lt, ln);
});

function updateMarkerFromInput() {
    var lt = parseFloat(document.getElementById('input-lat').value);
    var ln = parseFloat(document.getElementById('input-lon').value);
    if (!isNaN(lt) && !isNaN(ln)) {
        marker.setLatLng([lt, ln]); circle.setLatLng([lt, ln]);
        map.setView([lt, ln], map.getZoom());
    }
}

function setKoordinat(lt, ln) {
    document.getElementById('input-lat').value = lt;
    document.getElementById('input-lon').value = ln;
}

function updateRadius(val) {
    circle.setRadius(parseInt(val));
    document.getElementById('input-radius').value = val;
    document.getElementById('label-radius').textContent = val + ' m';
}

function syncSlider(val) {
    var v = parseInt(val) || 100;
    circle.setRadius(v);
    document.getElementById('slider-radius').value = Math.min(v, 500);
    document.getElementById('label-radius').textContent = v + ' m';
}
</script>
@endpush