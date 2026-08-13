@extends('admin.sidebar')
@section('title', 'Tambah Mitra')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map { height: 380px; border-radius: 16px; z-index: 1; }
.leaflet-container { border-radius: 16px; }
</style>
@endpush

@section('content')

<header class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
    <a href="{{ route('admin.mitra.index') }}"
       class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all text-gray-500">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F] ">Tambah Mitra</h1>
        <p class="text-gray-500 mt-1 text-sm">Klik peta untuk menentukan lokasi, atau isi koordinat manual</p>
    </div>
</header>

<form method="POST" action="{{ route('admin.mitra.store') }}">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ── KIRI: Peta (3 kolom) ─────────────────────────────────── --}}
    <div class="lg:col-span-3 space-y-4">

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-4
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
                Pilih Lokasi di Peta
            </h3>
            <p class="text-[10px] text-gray-400 font-semibold mb-3">
                🖱️ Klik di peta untuk menentukan titik lokasi mitra. Koordinat akan terisi otomatis.
            </p>
            <div id="map"></div>

            {{-- Radius visual --}}
            <div class="mt-4 flex items-center gap-3">
                <label class="text-[10px] font-black text-gray-500  tracking-widest whitespace-nowrap">
                    Tampilkan Radius:
                </label>
                <input type="range" id="slider-radius" min="10" max="500" value="100"
                       class="flex-1 accent-[#1E3A5F]"
                       oninput="updateRadius(this.value)">
                <span id="label-radius" class="text-[11px] font-black text-[#1E3A5F] w-16">100 m</span>
            </div>
        </div>

        @include('admin.mitra._shift_config')

    </div>

    {{-- ── KANAN: Form (2 kolom) ────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-black text-[#1E3A5F]  italic text-[11px] mb-5
                       flex items-center gap-2">
                <span class="w-1 h-4 bg-teal-500 rounded-full"></span>
                Informasi Mitra
            </h3>

            <div class="space-y-4">

                {{-- Nama Mitra --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500  tracking-widest mb-2">
                        Nama Mitra <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_mitra" value="{{ old('nama_mitra', request('nama')) }}"
                           placeholder="Contoh: Bank Nagari" required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 placeholder-gray-300 outline-none transition-all
                                  @error('nama_mitra') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('nama_mitra')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mitra Induk (opsional, untuk cabang) --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500  tracking-widest mb-2">
                        Mitra Induk
                        <span class="font-normal text-gray-400 normal-case ml-1">(kosongkan jika ini mitra induk)</span>
                    </label>
                    <select name="mitra_induk_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50
                                   text-sm font-semibold text-gray-700 outline-none
                                   focus:border-[#1E3A5F] focus:bg-white">
                        <option value="">-- Ini adalah mitra induk --</option>
                        @foreach ($daftarInduk as $induk)
                            <option value="{{ $induk->id }}"
                                    {{ (old('mitra_induk_id') == $induk->id || request('induk') == $induk->id) ? 'selected' : '' }}>
                                {{ $induk->nama_mitra }}
                            </option>
                        @endforeach
                    </select>
                    @error('mitra_induk_id')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jam Masuk & Jam Pulang --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                            Jam Masuk <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_masuk" value="{{ old('jam_masuk') }}"
                               required
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('jam_masuk') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('jam_masuk')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 tracking-widest mb-2">
                            Jam Pulang <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_pulang" value="{{ old('jam_pulang') }}"
                               required
                               class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                      text-gray-700 outline-none transition-all
                                      @error('jam_pulang') border-red-400 bg-red-50
                                      @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                      @enderror">
                        @error('jam_pulang')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Flag Pusat --}}
                @if(!$hasPusat)
                <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-2xl">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="is_pusat" value="1" {{ old('is_pusat') ? 'checked' : '' }}
                               class="w-5 h-5 rounded-lg border-gray-300 text-[#1E3A5F] focus:ring-[#1E3A5F]">
                        <div>
                            <p class="text-xs font-black text-[#1E3A5F] uppercase tracking-widest group-hover:text-blue-600 transition-colors">
                                Jadikan Kantor Pusat (PT CBN)
                            </p>
                            <p class="text-[9px] text-gray-400 mt-0.5">
                                Jika dicentang, seluruh <strong>karyawan tetap</strong> akan otomatis absen ke lokasi ini.
                            </p>
                        </div>
                    </label>
                </div>
                @endif

                {{-- Latitude --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500  tracking-widest mb-2">
                        Latitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="latitude" id="input-lat"
                           value="{{ old('latitude') }}"
                           placeholder="-0.9492" step="any"
                           oninput="updateMarkerFromInput()" required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 placeholder-gray-300 outline-none transition-all
                                  font-mono
                                  @error('latitude') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('latitude')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Longitude --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500  tracking-widest mb-2">
                        Longitude <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="longitude" id="input-lon"
                           value="{{ old('longitude') }}"
                           placeholder="100.3543" step="any"
                           oninput="updateMarkerFromInput()" required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 placeholder-gray-300 outline-none transition-all
                                  font-mono
                                  @error('longitude') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('longitude')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Radius --}}
                <div>
                    <label class="block text-[11px] font-black text-gray-500  tracking-widest mb-2">
                        Radius Absensi (meter) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="radius_meter" id="input-radius"
                           value="{{ old('radius_meter', 100) }}"
                           min="10" max="5000" placeholder="100"
                           oninput="syncSlider(this.value)" required
                           class="w-full px-4 py-3 rounded-xl border text-sm font-semibold
                                  text-gray-700 outline-none transition-all
                                  @error('radius_meter') border-red-400 bg-red-50
                                  @else border-gray-200 bg-gray-50 focus:border-[#1E3A5F] focus:bg-white
                                  @enderror">
                    @error('radius_meter')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[9px] text-gray-400 font-medium">
                        Karyawan hanya bisa absen dalam radius ini dari titik koordinat.
                    </p>
                </div>

                {{-- IP Public --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-500 tracking-widest mb-2">
                        IP PUBLIC KANTOR <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="ip_public"
                           value="{{ old('ip_public') }}"
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

        {{-- Tombol --}}
        <button type="submit"
                class="w-full bg-[#1E3A5F] hover:bg-green-600 text-white font-black text-sm
                        tracking-widest py-4 rounded-2xl transition-all shadow-sm
                       active:scale-95 italic flex items-center justify-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i>
            Simpan Mitra
        </button>

        <a href="{{ route('admin.mitra.index') }}"
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
// ── Inisialisasi Peta ─────────────────────────────────────────────────
// Default center: Padang, Sumatera Barat (sesuai lokasi PT CBN)
var defaultLat = {{ old('latitude', -0.9492) }};
var defaultLon = {{ old('longitude', 100.3543) }};
var defaultRadius = {{ old('radius_meter', 100) }};

var map = L.map('map').setView([defaultLat, defaultLon], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Ikon marker custom
var markerIcon = L.divIcon({
    className: '',
    html: '<div style="background:#1E3A5F;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
    iconSize: [16, 16],
    iconAnchor: [8, 8],
});

var marker = L.marker([defaultLat, defaultLon], {
    draggable: true,
    icon: markerIcon,
}).addTo(map);

var circle = L.circle([defaultLat, defaultLon], {
    radius: defaultRadius,
    color: '#1E3A5F',
    fillColor: '#1E3A5F',
    fillOpacity: 0.08,
    weight: 2,
    dashArray: '6,4',
}).addTo(map);

// ── Saat marker di-drag ───────────────────────────────────────────────
marker.on('dragend', function(e) {
    var pos = e.target.getLatLng();
    setKoordinat(pos.lat.toFixed(7), pos.lng.toFixed(7));
    circle.setLatLng([pos.lat, pos.lng]);
});

// ── Saat peta diklik ──────────────────────────────────────────────────
map.on('click', function(e) {
    var lat = e.latlng.lat.toFixed(7);
    var lon = e.latlng.lng.toFixed(7);
    marker.setLatLng([lat, lon]);
    circle.setLatLng([lat, lon]);
    setKoordinat(lat, lon);
});

// ── Saat input manual ─────────────────────────────────────────────────
function updateMarkerFromInput() {
    var lat = parseFloat(document.getElementById('input-lat').value);
    var lon = parseFloat(document.getElementById('input-lon').value);
    if (!isNaN(lat) && !isNaN(lon)) {
        marker.setLatLng([lat, lon]);
        circle.setLatLng([lat, lon]);
        map.setView([lat, lon], map.getZoom());
    }
}

// ── Set koordinat ke input ─────────────────────────────────────────────
function setKoordinat(lat, lon) {
    document.getElementById('input-lat').value = lat;
    document.getElementById('input-lon').value = lon;
}

// ── Update radius ──────────────────────────────────────────────────────
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

// Init slider sesuai nilai awal
document.getElementById('slider-radius').value = Math.min(defaultRadius, 500);
document.getElementById('label-radius').textContent = defaultRadius + ' m';
</script>
@endpush
