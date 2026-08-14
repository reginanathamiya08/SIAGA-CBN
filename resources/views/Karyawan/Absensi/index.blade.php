@extends('karyawan.sidebar')

@section('title', 'Absensi')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #map { height: 200px; border-radius: 16px; z-index: 1; }
    @media (min-width: 768px) {
        #map { height: 280px; border-radius: 20px; }
    }
    .leaflet-container { border-radius: 16px; }
</style>
@endpush

@section('content')

<div class="mb-3">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
        <div>
            <h1 class="text-2xl font-black text-[#1E3A5F] tracking-tight">Absensi Kehadiran</h1>
            <p class="text-gray-500 mt-1 text-sm">Pantau & Catat Kehadiran — <span class="text-red-600 font-bold">PT CBN</span></p>
         </div>
        <div class="flex items-center gap-2">
            <div class="bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm flex items-center gap-2.5">
                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-black text-[#1E3A5F]">{{ now()->translatedFormat('d M Y') }}</span>
            </div>
            <a href="{{ route('karyawan.absensi.riwayat') }}" 
               class="w-10 h-10 bg-white border border-gray-100 text-[#1E3A5F] rounded-xl shadow-sm hover:bg-[#1E3A5F] hover:text-white transition-all flex items-center justify-center">
                <i data-lucide="history" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-4" x-data="absensiApp()" x-init="init()">
    
    {{-- SISI KIRI (MAP & BUTTONS) --}}
    <div class="lg:col-span-8 flex flex-col gap-3.5 order-1">
        {{-- MAP --}}
        <div class="order-1 bg-white rounded-2xl p-1 border border-gray-100 shadow-sm relative group overflow-hidden">
            <div id="map" class="w-full"></div>
            <button @click="getLocation()" class="absolute bottom-3 right-3 z-[1000] w-8 h-8 bg-[#1E3A5F] text-white rounded-lg shadow-xl flex items-center justify-center hover:bg-red-600 transition-all">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="loading ? 'animate-spin' : ''"></i>
            </button>
            @if($penempatan && isset($penempatan->mitra))
                <div class="absolute top-3 left-3 z-[1000] bg-white/90 backdrop-blur-md p-2 rounded-lg border border-gray-100 shadow-xl flex items-center gap-2 max-w-[200px]">
                    <div class="w-7 h-7 bg-[#1E3A5F] text-white rounded flex items-center justify-center flex-shrink-0">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] font-bold text-gray-400 leading-none">Mitra</p>
                        <p class="text-xs font-black text-[#1E3A5F] truncate leading-none mt-1">{{ $penempatan->mitra->nama_mitra }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- BUTTONS (ULTRA COMPACT) --}}
        <div class="order-2">
            @if($penempatan && isset($penempatan->mitra))
                @if($isLiburAtauIzin)
                    <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 flex flex-col items-center justify-center text-center group">
                        <div class="w-10 h-10 bg-white text-blue-600 rounded-xl flex items-center justify-center mb-2 shadow-sm border border-blue-50">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-sm font-black text-blue-800 uppercase tracking-wider">
                            Hari Ini Anda {{ $statusLiburAtauIzin }}
                        </h4>
                        <p class="text-xs font-semibold text-blue-600/70 mt-1 leading-relaxed max-w-md">
                            Anda tidak perlu melakukan absensi karena status kehadiran Anda hari ini tercatat sebagai <strong class="text-blue-800">{{ strtoupper($statusLiburAtauIzin) }}</strong>.
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- TOMBOL MASUK --}}
                        <form method="POST" action="{{ route('karyawan.absensi.masuk') }}" x-ref="formMasuk" class="w-full">
                            @csrf
                            <input type="hidden" name="latitude" x-model="lat">
                            <input type="hidden" name="longitude" x-model="lon">
                            <button type="button" @click="submitWithConfirm($refs.formMasuk, 'Masuk')"
                                    :disabled="!lat || !jarakValid || loading || submitting || {{ $absensi?->waktu_masuk ? 'true' : 'false' }}"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-black transition-all shadow-sm active:scale-95 disabled:opacity-30 disabled:grayscale
                                           {{ $absensi?->waktu_masuk ? 'bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-none' : 'bg-[#1E3A5F] text-white hover:bg-emerald-600 shadow-blue-900/10' }}">
                                <i data-lucide="{{ $absensi?->waktu_masuk ? 'check-circle' : 'fingerprint' }}" class="w-5 h-5"></i>
                                <span class="text-xs sm:text-sm">{{ $absensi?->waktu_masuk ? 'Sudah Masuk' : 'Absen Masuk' }}</span>
                            </button>
                        </form>

                        {{-- TOMBOL PULANG --}}
                        <form method="POST" action="{{ route('karyawan.absensi.pulang') }}" x-ref="formPulang" class="w-full">
                            @csrf
                            <input type="hidden" name="latitude" x-model="lat">
                            <input type="hidden" name="longitude" x-model="lon">
                            <button type="button" @click="submitWithConfirm($refs.formPulang, 'Pulang')"
                                    :disabled="!lat || !jarakValid || loading || submitting || !{{ $absensi?->waktu_masuk ? 'true' : 'false' }} || {{ $absensi?->waktu_pulang ? 'true' : 'false' }} || !{{ $bolehPulang ? 'true' : 'false' }}"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl font-black transition-all shadow-sm active:scale-95 disabled:opacity-30 disabled:grayscale
                                           {{ $absensi?->waktu_pulang ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-none' : 'bg-white border border-[#1E3A5F] text-[#1E3A5F] hover:bg-red-600 hover:text-white shadow-blue-900/5' }}">
                                <i data-lucide="{{ $absensi?->waktu_pulang ? 'check-circle' : 'power' }}" class="w-5 h-5"></i>
                                <span class="text-xs sm:text-sm">{{ $absensi?->waktu_pulang ? 'Sudah Pulang' : 'Absen Pulang' }}</span>
                            </button>
                        </form>
                    </div>

                    <div class="mt-2.5">
                        @if($absensi?->waktu_masuk && !$absensi?->waktu_pulang && !$bolehPulang)
                            <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-100 flex items-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4 text-amber-500"></i>
                                <p class="text-xs font-bold text-amber-700 leading-tight">{{ $pesanBelumPulang }}</p>
                            </div>
                        @endif
                        @if($absensi?->waktu_pulang)
                            <div class="p-2.5 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center gap-2">
                                <i data-lucide="party-popper" class="w-4 h-4 text-emerald-500"></i>
                                <p class="text-xs font-bold text-emerald-700 leading-tight">Presensi hari ini telah selesai. Selamat beristirahat!</p>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <div class="bg-rose-50 rounded-2xl p-5 border border-rose-100 flex flex-col items-center justify-center text-center group">
                    <div class="w-10 h-10 bg-white text-rose-500 rounded-lg flex items-center justify-center mb-2 shadow-sm border border-rose-50 group-hover:shake transition-all">
                        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-xs font-black text-rose-800">Akses Dibatasi</h4>
                    <p class="text-[11px] font-medium text-rose-600/70 mt-1 leading-relaxed">
                        Data **Penempatan Mitra** tidak ditemukan. Hubungi Admin.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- SISI KANAN (CLOCK & VERIFIKASI) --}}
    <div class="lg:col-span-4 space-y-4 order-2">
        {{-- CLOCK --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50/50 rounded-full -mr-8 -mt-8 blur-lg group-hover:bg-blue-100/50 transition-colors duration-700"></div>
            <div class="relative z-10 text-center md:text-left">
                <h3 class="text-xs font-bold text-[#1E3A5F] mb-3 flex items-center justify-center md:justify-start gap-1">
                    <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Waktu Server
                </h3>
                <div class="mb-3">
                    <p class="text-2xl md:text-3xl font-black text-[#1E3A5F] tracking-tighter tabular-nums leading-none" x-text="currentTime">00:00:00</p>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-1 tracking-widest">WIB</p>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-gray-50/80 p-2 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 mb-0.5">Masuk</p>
                        <p class="text-sm font-black text-[#1E3A5F]">{{ $absensi?->waktu_masuk ? $absensi->waktu_masuk->format('H:i') : '--:--' }}</p>
                    </div>
                    <div class="bg-gray-50/80 p-2 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 mb-0.5">Pulang</p>
                        <p class="text-sm font-black text-[#1E3A5F]">{{ $absensi?->waktu_pulang ? $absensi->waktu_pulang->format('H:i') : '--:--' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- VERIFIKASI --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-[#1E3A5F] mb-3 flex items-center justify-center md:justify-start gap-1">
                <span class="w-1.5 h-1.5 bg-blue-600 rounded-full"></span> Verifikasi
            </h3>
            <div class="space-y-2.5">
                <div class="p-2.5 rounded-xl border transition-all duration-700"
                     :class="lat ? (jarakValid ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/50 border-rose-100') : 'bg-gray-50 border-gray-100'">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded flex items-center justify-center shadow-sm"
                             :class="lat ? (jarakValid ? 'bg-white text-emerald-600' : 'bg-white text-rose-600') : 'bg-white text-gray-300'">
                            <i data-lucide="map-pin" class="w-4 h-4" :class="lat && 'animate-bounce'"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black text-gray-400 leading-none">GPS Status</p>
                            <p class="text-xs font-black mt-1" :class="lat ? (jarakValid ? 'text-emerald-700' : 'text-rose-700') : 'text-gray-400'">
                                <template x-if="!lat"><span>Mencari...</span></template>
                                <template x-if="lat && jarakValid"><span x-text="'Aman (' + Math.round(jarak) + 'm)'"></span></template>
                                <template x-if="lat && !jarakValid"><span x-text="'Diluar Jangkauan'"></span></template>
                            </p>
                        </div>
                    </div>
                </div>

                @php
                    $ipKaryawan = request()->ip();
                    $ipValid = false;
                    if ($penempatan && isset($penempatan->mitra) && $penempatan->mitra->ip_public) {
                        $allowedIps = array_map('trim', explode(',', $penempatan->mitra->ip_public));
                        foreach ($allowedIps as $allowed) {
                            if (empty($allowed)) continue;
                            // 1. IPv4 Wildcard (misal: 182.9.200.*)
                            if (str_contains($allowed, '*')) {
                                if (str_starts_with($ipKaryawan, str_replace('*', '', $allowed))) {
                                    $ipValid = true; break;
                                }
                            }
                            // 2. Exact Match
                            if ($ipKaryawan === $allowed) {
                                $ipValid = true; break;
                            }
                            // 3. Fallback tunnel Ngrok / IPv6 vs IPv4
                            if (str_contains($ipKaryawan, ':') && !str_contains($allowed, ':')) {
                                $ipValid = true; break;
                            }
                            // 4. Smart IPv6 Subnet Prefix Match (/48)
                            if (str_contains($allowed, ':') && str_contains($ipKaryawan, ':')) {
                                $pK = explode(':', $ipKaryawan);
                                $pA = explode(':', $allowed);
                                if (count($pK) >= 3 && count($pA) >= 3) {
                                    if (implode(':', array_slice($pK, 0, 3)) === implode(':', array_slice($pA, 0, 3))) {
                                        $ipValid = true; break;
                                    }
                                }
                            }
                        }
                    }
                @endphp

                @if($penempatan && isset($penempatan->mitra) && $penempatan->mitra->ip_public)
                    <div class="p-2.5 rounded-xl border {{ $ipValid ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/50 border-rose-100' }}">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center shadow-sm {{ $ipValid ? 'text-emerald-600' : 'text-rose-600' }}">
                                <i data-lucide="wifi" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-[10px] font-black text-gray-400 leading-none">WiFi Status</p>
                                <p class="text-xs font-black mt-1 {{ $ipValid ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $ipValid ? 'Terhubung' : 'Gunakan WiFi' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- RIWAYAT --}}
<div class="mt-4 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
        <h3 class="text-xs sm:text-sm font-black text-[#1E3A5F] flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Riwayat Terkini
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[#1E3A5F] text-[10px] sm:text-xs font-black tracking-wider border-b border-gray-50 bg-gray-50/20 uppercase">
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-center">In</th>
                    <th class="px-4 py-2 text-center">Out</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($riwayat as $row)
                    @php
                        $statusMap = [
                            'hadir' => ['Hadir', 'bg-emerald-50 text-emerald-700 border-emerald-100'],
                            'telat' => ['Telat', 'bg-amber-50 text-amber-700 border-amber-100'],
                            'alfa' => ['Alfa', 'bg-rose-50 text-rose-700 border-rose-100'],
                            'izin' => ['Izin', 'bg-blue-50 text-blue-700 border-blue-100'],
                            'sakit' => ['Sakit', 'bg-purple-50 text-purple-700 border-purple-100'],
                            'cuti' => ['Cuti', 'bg-indigo-50 text-indigo-700 border-indigo-100'],
                            'dinas_luar' => ['Dinas Luar', 'bg-orange-50 text-orange-700 border-orange-100'],
                        ];
                        [$label, $cls] = $statusMap[$row->status] ?? ['?', 'bg-gray-50 text-gray-500 border-gray-100'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-all text-xs sm:text-sm">
                        <td class="px-4 py-2">
                            <span class="text-[#1E3A5F] font-black block leading-none">{{ $row->tanggal->translatedFormat('d M Y') }}</span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $row->tanggal->translatedFormat('l') }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full border text-[10px] font-black {{ $cls }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center font-bold text-gray-600">{{ $row->waktu_masuk?->format('H:i') ?? '--:--' }}</td>
                        <td class="px-4 py-2 text-center font-bold text-gray-600">{{ $row->waktu_pulang?->format('H:i') ?? '--:--' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function absensiApp() {
    return {
        lat: null, lon: null, jarak: null, jarakValid: false, loading: false, submitting: false, errorGps: null,
        currentTime: '00:00:00',
        mitraLat: {{ $penempatan?->mitra->latitude ?? 'null' }},
        mitraLon: {{ $penempatan?->mitra->longitude ?? 'null' }},
        mitraRadius: {{ $penempatan?->mitra->radius_meter ?? 100 }},
        map: null, markerUser: null, markerMitra: null, circle: null,
        
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            this.$nextTick(() => {
                this.initMap();
                this.getLocation();
            });
        },
        updateTime() {
            const now = new Date();
            this.currentTime = now.getHours().toString().padStart(2, '0') + ':' +
                               now.getMinutes().toString().padStart(2, '0') + ':' +
                               now.getSeconds().toString().padStart(2, '0');
        },
        initMap() {
            const defaultLat = this.mitraLat ?? -0.9492;
            const defaultLon = this.mitraLon ?? 100.3543;
            this.map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLon], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
            if (this.mitraLat && this.mitraLon) {
                const icon = L.divIcon({ 
                    html: `<div class="w-1.5 h-1.5 bg-[#1E3A5F] rounded-full border border-white shadow-lg"></div>`, 
                    className: '', iconSize: [8, 8], iconAnchor: [4, 4] 
                });
                this.markerMitra = L.marker([this.mitraLat, this.mitraLon], { icon }).addTo(this.map);
                this.circle = L.circle([this.mitraLat, this.mitraLon], { radius: this.mitraRadius, color: '#1E3A5F', fillOpacity: 0.1, weight: 1 }).addTo(this.map);
            }
        },
        getLocation() {
            if (!navigator.geolocation) { this.errorGps = 'Browser tidak mendukung GPS.'; return; }
            this.loading = true; this.errorGps = null;
            navigator.geolocation.getCurrentPosition((pos) => {
                this.lat = pos.coords.latitude; this.lon = pos.coords.longitude; this.loading = false;
                const icon = L.divIcon({ 
                    html: `<div class="relative"><div class="absolute -inset-1 bg-green-500/30 rounded-full animate-ping"></div><div class="relative w-1.5 h-1.5 bg-green-500 rounded-full border border-white shadow-md"></div></div>`, 
                    className: '', iconSize: [6, 6], iconAnchor: [3, 3] 
                });
                if (this.markerUser) this.markerUser.setLatLng([this.lat, this.lon]);
                else this.markerUser = L.marker([this.lat, this.lon], { icon }).addTo(this.map);
                if (this.mitraLat && this.mitraLon) { 
                    this.jarak = this.hitungJarak(this.lat, this.lon, this.mitraLat, this.mitraLon); 
                    this.jarakValid = this.jarak <= this.mitraRadius; 
                }
                const bounds = L.latLngBounds([[this.lat, this.lon]]);
                if (this.mitraLat) bounds.extend([this.mitraLat, this.mitraLon]);
                this.map.fitBounds(bounds, { padding: [15, 15] });
            }, (err) => { this.loading = false; this.errorGps = 'Gagal mengambil GPS: ' + err.message; }, { enableHighAccuracy: true, timeout: 10000 });
        },
        submitWithConfirm(form, tipe) {
            if (!this.lat || !this.jarakValid) return;
            Swal.fire({
                title: `Absensi ${tipe}`,
                text: `Lakukan absensi ${tipe.toLowerCase()}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1E3A5F',
                cancelButtonColor: '#64748B',
                confirmButtonText: 'Ya!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitting = true;
                    form.submit();
                }
            });
        },
        hitungJarak(lat1, lon1, lat2, lon2) {
            const R = 6371000; 
            const dLat = (lat2 - lat1) * Math.PI / 180; 
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        },
    };
}
</script>
@endpush
