@extends('karyawan.sidebar')

@section('title', 'Absensi')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
#map { height: 260px; border-radius: 16px; z-index: 1; }
.leaflet-container { border-radius: 16px; }
</style>
@endpush

@section('content')

<header class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
    <div>
        <h1 class="text-2xl font-black text-[#1E3A5F]">Absensi</h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>
    <a href="{{ route('karyawan.absensi.riwayat') }}"
       class="flex items-center gap-2 text-[11px] font-black text-[#1E3A5F] bg-blue-50
              px-4 py-2 rounded-xl hover:bg-blue-100 transition-all">
        <i data-lucide="history" class="w-4 h-4"></i>
        Riwayat Absensi
    </a>
</header>

{{-- ALERT MESSAGES --}}
@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700
                text-sm font-semibold flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('warning'))
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl text-yellow-700
                text-sm font-semibold flex items-center gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
        {{ session('warning') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700
                text-sm font-semibold flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i>
        {{ session('error') }}
    </div>
@endif

{{-- NO PENEMPATAN WARNING --}}
@unless($penempatan)
    <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-6 mb-6 text-center">
        <i data-lucide="map-pin-off" class="w-10 h-10 text-yellow-400 mx-auto mb-3"></i>
        <p class="font-black text-yellow-700 text-sm">Belum Ada Penempatan Aktif</p>
        <p class="text-yellow-600 text-xs mt-1">Hubungi admin untuk mendapatkan penempatan.</p>
    </div>
@endunless

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- STATUS ABSENSI HARI INI --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-5 flex items-center gap-2">
            <span class="w-1 h-4 bg-blue-500 rounded-full"></span>
            Status Hari Ini
        </h3>

        <div class="grid grid-cols-2 gap-4 mb-5">
            {{-- Masuk --}}
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-[9px] font-black text-gray-400 tracking-widest mb-2">MASUK</p>
                @if($absensi?->waktu_masuk)
                    <p class="text-2xl font-black text-green-600">
                        {{ $absensi->waktu_masuk->format('H:i') }}
                    </p>
                    @if($absensi->is_telat)
                        <span class="inline-block mt-1 text-[9px] font-black bg-yellow-100
                                     text-yellow-700 px-2 py-0.5 rounded-full">TELAT</span>
                    @else
                        <span class="inline-block mt-1 text-[9px] font-black bg-green-100
                                     text-green-700 px-2 py-0.5 rounded-full">TEPAT WAKTU</span>
                    @endif
                @else
                    <p class="text-2xl font-black text-gray-300">--:--</p>
                    <span class="inline-block mt-1 text-[9px] font-black bg-gray-100
                                 text-gray-400 px-2 py-0.5 rounded-full">BELUM ABSEN</span>
                @endif
            </div>

            {{-- Pulang --}}
            <div class="bg-gray-50 rounded-2xl p-4">
                <p class="text-[9px] font-black text-gray-400 tracking-widest mb-2">PULANG</p>
                @if($absensi?->waktu_pulang)
                    <p class="text-2xl font-black text-blue-600">
                        {{ $absensi->waktu_pulang->format('H:i') }}
                    </p>
                    @php $durasi = $absensi->durasiMenit(); @endphp
                    <span class="inline-block mt-1 text-[9px] font-black bg-blue-100
                                 text-blue-700 px-2 py-0.5 rounded-full">
                        {{ intdiv($durasi,60) }}j {{ $durasi%60 }}m
                    </span>
                @else
                    <p class="text-2xl font-black text-gray-300">--:--</p>
                    <span class="inline-block mt-1 text-[9px] font-black bg-gray-100
                                 text-gray-400 px-2 py-0.5 rounded-full">BELUM PULANG</span>
                @endif
            </div>
        </div>

        {{-- Info Mitra --}}
        @if($penempatan)
            <div class="bg-blue-50 rounded-2xl p-3 flex items-center gap-3">
                <i data-lucide="building-2" class="w-4 h-4 text-blue-500 shrink-0"></i>
                <div>
                    <p class="text-[9px] font-black text-blue-400 tracking-widest">LOKASI ABSENSI</p>
                    <p class="text-xs font-black text-blue-700">{{ $penempatan->mitra->nama_mitra }}</p>
                    <p class="text-[9px] text-blue-500 mt-0.5">
                        Radius: {{ $penempatan->mitra->radius_meter }} m • Validasi IP: Aktif
                    </p>
                </div>
            </div>
        @endif

        {{-- Rekap bulan ini --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-[9px] font-black text-gray-400 tracking-widest mb-3">REKAP BULAN INI</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['hadir'=>['Hadir','green'],'telat'=>['Telat','yellow'],'alfa'=>['Alfa','red']] as $key=>[$label,$color])
                    <div class="text-center bg-{{ $color }}-50 rounded-xl py-2">
                        <p class="text-lg font-black text-{{ $color }}-600">{{ $rekapBulan[$key] ?? 0 }}</p>
                        <p class="text-[8px] font-black text-{{ $color }}-400 tracking-widest">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- FORM ABSENSI --}}
    <div class="space-y-4" x-data="absensiApp()" x-init="init()">

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-black text-[#1E3A5F] italic text-[11px] flex items-center gap-2">
                    <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                    Lokasi GPS Kamu
                </h3>
                <button @click="getLocation()"
                        class="text-[10px] font-black text-[#1E3A5F] bg-blue-50 px-3 py-1
                               rounded-xl hover:bg-blue-100 transition-all flex items-center gap-1">
                    <i data-lucide="refresh-cw" class="w-3 h-3"></i> Perbarui
                </button>
            </div>

            <div id="map" class="mb-3"></div>

            <div x-show="loading" class="text-center py-2">
                <p class="text-xs text-gray-400 font-semibold animate-pulse">Mengambil lokasi GPS...</p>
            </div>

            <div x-show="!loading && lat" class="bg-gray-50 rounded-xl p-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-[8px] font-black text-gray-400 tracking-widest">LATITUDE</p>
                        <p class="text-xs font-black text-gray-700" x-text="lat?.toFixed(7) ?? '-'"></p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-gray-400 tracking-widest">LONGITUDE</p>
                        <p class="text-xs font-black text-gray-700" x-text="lon?.toFixed(7) ?? '-'"></p>
                    </div>
                </div>
                <div class="mt-2" x-show="jarak !== null">
                    <p class="text-[8px] font-black text-gray-400 tracking-widest">JARAK KE MITRA</p>
                    <p class="text-xs font-black" :class="jarakValid ? 'text-green-600' : 'text-red-500'">
                        <span x-text="jarak !== null ? Math.round(jarak) + ' m' : '-'"></span>
                        <span x-show="jarakValid" class="ml-1">✓ Dalam radius</span>
                        <span x-show="!jarakValid && jarak !== null" class="ml-1">✗ Di luar radius</span>
                    </p>
                </div>
            </div>

            <div x-show="errorGps" class="mt-2 bg-red-50 rounded-xl p-3">
                <p class="text-[10px] font-semibold text-red-600" x-text="errorGps"></p>
            </div>
        </div>

        @if($penempatan)
            @if(!$absensi?->waktu_masuk)
                <form method="POST" action="{{ route('karyawan.absensi.masuk') }}"
                      x-ref="formMasuk" @submit.prevent="submitAbsen($refs.formMasuk)">
                    @csrf
                    <input type="hidden" name="latitude"  x-bind:value="lat">
                    <input type="hidden" name="longitude" x-bind:value="lon">

                    <button type="submit"
                            :disabled="!lat || !jarakValid || loading"
                            class="w-full flex items-center justify-center gap-3 py-4
                                   bg-[#1E3A5F] text-white rounded-2xl font-black italic
                                   text-sm shadow-lg hover:bg-green-700 transition-all
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="fingerprint" class="w-5 h-5"></i>
                        <span x-show="!submitting">Absen Masuk Sekarang</span>
                        <span x-show="submitting" class="animate-pulse">Memproses...</span>
                    </button>
                </form>

            @elseif(!$absensi?->waktu_pulang)
                <form method="POST" action="{{ route('karyawan.absensi.pulang') }}"
                      x-ref="formPulang" @submit.prevent="submitAbsen($refs.formPulang)">
                    @csrf
                    <input type="hidden" name="latitude"  x-bind:value="lat">
                    <input type="hidden" name="longitude" x-bind:value="lon">

                    @if(!$bolehPulang)
                        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                            <div class="flex items-center gap-2 text-amber-700 text-xs font-black italic mb-1">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                Belum Waktunya Pulang
                            </div>
                            <p class="text-[10px] font-bold text-amber-600 leading-tight">
                                {{ $pesanBelumPulang }}
                            </p>
                        </div>
                    @endif

                    <button type="submit"
                            :disabled="!lat || !jarakValid || loading || !{{ $bolehPulang ? 'true' : 'false' }}"
                            class="w-full flex items-center justify-center gap-3 py-4
                                   {{ $bolehPulang ? 'bg-gray-700 hover:bg-red-600' : 'bg-gray-300' }} 
                                   text-white rounded-2xl font-black italic
                                   text-sm shadow-lg transition-all
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="{{ $bolehPulang ? 'log-out' : 'lock' }}" class="w-5 h-5"></i>
                        <span x-show="!submitting">{{ $bolehPulang ? 'Absen Pulang' : 'Tombol Terkunci' }}</span>
                        <span x-show="submitting" class="animate-pulse">Memproses...</span>
                    </button>
                </form>

            @else
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5 text-center">
                    <i data-lucide="check-circle-2" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                    <p class="font-black text-green-700 text-sm">Absensi Hari Ini Selesai</p>
                    <p class="text-green-600 text-xs mt-1">
                        {{ $absensi->waktu_masuk->format('H:i') }} —
                        {{ $absensi->waktu_pulang->format('H:i') }}
                    </p>
                </div>
            @endif
        @endif

    </div>
</div>

@if($riwayat->count())
<div class="mt-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
    <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-4 flex items-center gap-2">
        <span class="w-1 h-4 bg-teal-500 rounded-full"></span>
        Riwayat 30 Hari Terakhir
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="text-left text-[9px] font-black text-gray-400 tracking-widest border-b border-gray-100">
                    <th class="pb-3 pr-4">TANGGAL</th>
                    <th class="pb-3 pr-4">STATUS</th>
                    <th class="pb-3 pr-4">MASUK</th>
                    <th class="pb-3 pr-4">PULANG</th>
                    <th class="pb-3">DURASI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($riwayat as $row)
                    @php
                        $statusMap = [
                            'hadir'      => ['Hadir',      'bg-green-100 text-green-700'],
                            'telat'      => ['Telat',      'bg-yellow-100 text-yellow-700'],
                            'alfa'       => ['Alfa',       'bg-red-100 text-red-700'],
                            'izin'       => ['Izin',       'bg-blue-100 text-blue-700'],
                            'sakit'      => ['Sakit',      'bg-purple-100 text-purple-700'],
                            'cuti'       => ['Cuti',       'bg-indigo-100 text-indigo-700'],
                            'dinas_luar' => ['Dinas Luar', 'bg-orange-100 text-orange-700'],
                        ];
                        [$label, $cls] = $statusMap[$row->status] ?? ['?', 'bg-gray-100 text-gray-500'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-2.5 pr-4 font-semibold text-gray-600">{{ $row->tanggal->translatedFormat('d M Y') }}</td>
                        <td class="py-2.5 pr-4"><span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-black {{ $cls }}">{{ $label }}</span></td>
                        <td class="py-2.5 pr-4 text-gray-600">{{ $row->waktu_masuk?->format('H:i') ?? '-' }}</td>
                        <td class="py-2.5 pr-4 text-gray-600">{{ $row->waktu_pulang?->format('H:i') ?? '-' }}</td>
                        <td class="py-2.5 text-gray-600">@if($row->durasiMenit()){{ intdiv($row->durasiMenit(), 60) }}j {{ $row->durasiMenit() % 60 }}m @else - @endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function absensiApp() {
    return {
        lat: null, lon: null, jarak: null, jarakValid: false, loading: false, submitting: false, errorGps: null,
        mitraLat: {{ $penempatan?->mitra->latitude ?? 'null' }},
        mitraLon: {{ $penempatan?->mitra->longitude ?? 'null' }},
        mitraRadius: {{ $penempatan?->mitra->radius_meter ?? 100 }},
        map: null, markerUser: null, markerMitra: null, circle: null,
        init() { this.$nextTick(() => { this.initMap(); this.getLocation(); }); },
        initMap() {
            const defaultLat = this.mitraLat ?? -0.9492;
            const defaultLon = this.mitraLon ?? 100.3543;
            this.map = L.map('map').setView([defaultLat, defaultLon], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
            if (this.mitraLat && this.mitraLon) {
                const icon = L.divIcon({ html: `<div style="background:#1E3A5F;width:14px;height:14px;border-radius:50%;border:3px solid white;shadow:0 2px 6px rgba(0,0,0,.4)"></div>`, className: '', iconAnchor: [7, 7] });
                this.markerMitra = L.marker([this.mitraLat, this.mitraLon], { icon }).addTo(this.map);
                this.circle = L.circle([this.mitraLat, this.mitraLon], { radius: this.mitraRadius, color: '#1E3A5F', fillOpacity: 0.08 }).addTo(this.map);
            }
        },
        getLocation() {
            if (!navigator.geolocation) { this.errorGps = 'Browser tidak mendukung GPS.'; return; }
            this.loading = true; this.errorGps = null;
            navigator.geolocation.getCurrentPosition((pos) => {
                this.lat = pos.coords.latitude; this.lon = pos.coords.longitude; this.loading = false;
                const icon = L.divIcon({ html: `<div style="background:#22c55e;width:12px;height:12px;border-radius:50%;border:3px solid white;shadow:0 2px 6px rgba(0,0,0,.4)"></div>`, className: '', iconAnchor: [6, 6] });
                if (this.markerUser) this.markerUser.setLatLng([this.lat, this.lon]);
                else this.markerUser = L.marker([this.lat, this.lon], { icon }).addTo(this.map);
                if (this.mitraLat && this.mitraLon) { this.jarak = this.hitungJarak(this.lat, this.lon, this.mitraLat, this.mitraLon); this.jarakValid = this.jarak <= this.mitraRadius; }
                const bounds = L.latLngBounds([[this.lat, this.lon]]);
                if (this.mitraLat) bounds.extend([this.mitraLat, this.mitraLon]);
                this.map.fitBounds(bounds, { padding: [30, 30] });
                lucide.createIcons();
            }, (err) => { this.loading = false; this.errorGps = 'Gagal mengambil GPS: ' + err.message; }, { enableHighAccuracy: true, timeout: 10000 });
        },
        submitAbsen(form) { if (!this.lat || !this.jarakValid) return; this.submitting = true; form.submit(); },
        hitungJarak(lat1, lon1, lat2, lon2) {
            const R = 6371000; const dLat = (lat2 - lat1) * Math.PI / 180; const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        },
    };
}
</script>
@endpush