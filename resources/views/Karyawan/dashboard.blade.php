@extends('karyawan.sidebar')

@section('title', 'Dashboard')

@section('content')
{{-- GREETING & MINIMALIST REAL-TIME CLOCK --}}
<div class="mb-8 ml-1 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-black text-[#1E3A5F] tracking-tight flex items-center gap-2 leading-none">
            Halo, {{ explode(' ', $karyawan->nama)[0] }}! 👋
        </h1>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 leading-none">{{ $karyawan->jabatan }} — PT CBN</p>
    </div>

    <div class="flex items-center gap-2">
        {{-- TIME PILL --}}
        <div class="bg-white px-3 py-1.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-2.5">
            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
            <span id="realtime-clock" class="text-[10px] font-black text-[#1E3A5F] tracking-widest">00:00:00</span>
        </div>
        
        {{-- DECORATIVE ICON --}}
        <div class="w-8 h-8 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-[#1E3A5F] shadow-sm">
            <i data-lucide="clock" class="w-3.5 h-3.5 opacity-40"></i>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('realtime-clock');
        if (clockEl) clockEl.textContent = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    {{-- LEFT COLUMN --}}
    <div class="lg:col-span-4 space-y-5">
        {{-- PRESENSI CARD --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-5 opacity-5">
                <i data-lucide="clock" class="w-12 h-12 text-[#1E3A5F]"></i>
            </div>
            
            <h3 class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shadow-sm shadow-emerald-500/50"></span> 
                Presensi Hari Ini
            </h3>

            <div class="space-y-3 mb-6">
                <div class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 hover:bg-white hover:border-emerald-100 transition-all">
                    <div class="flex items-center gap-3">
                        <i data-lucide="log-in" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span class="text-[9px] font-black text-gray-600 uppercase tracking-widest">Masuk</span>
                    </div>
                    <span class="text-xs font-black {{ $absensiHariIni?->waktu_masuk ? 'text-[#1E3A5F]' : 'text-gray-400 italic' }}">
                        {{ $absensiHariIni?->waktu_masuk?->format('H:i') ?? '--:--' }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50/80 rounded-2xl border border-gray-100 hover:bg-white hover:border-orange-100 transition-all">
                    <div class="flex items-center gap-3">
                        <i data-lucide="log-out" class="w-3.5 h-3.5 text-orange-600"></i>
                        <span class="text-[9px] font-black text-gray-600 uppercase tracking-widest">Pulang</span>
                    </div>
                    <span class="text-xs font-black {{ $absensiHariIni?->waktu_pulang ? 'text-[#1E3A5F]' : 'text-gray-400 italic' }}">
                        {{ $absensiHariIni?->waktu_pulang?->format('H:i') ?? '--:--' }}
                    </span>
                </div>
            </div>

            @if (!$absensiHariIni?->waktu_masuk || !$absensiHariIni?->waktu_pulang)
                <a href="{{ route('karyawan.absensi.index') }}" 
                   class="w-full flex items-center justify-center gap-2 bg-[#1E3A5F] text-white rounded-xl py-3.5 font-black text-[9px] uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-md active:scale-95">
                    <i data-lucide="scan-face" class="w-4 h-4 text-white"></i>
                    Buka Panel Presensi
                </a>
            @else
                <div class="w-full flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 rounded-xl py-3 font-black text-[9px] uppercase tracking-widest border border-emerald-200">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    Selesai
                </div>
            @endif
        </div>

        {{-- MINI INFO --}}
        <div class="bg-gradient-to-r from-rose-500 to-rose-600 rounded-3xl p-5 text-white shadow-md relative overflow-hidden group">
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center border border-white/10 shrink-0">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-white/80 uppercase tracking-widest">Sisa Kuota Perizinan</p>
                    <p class="text-xl font-black tracking-tight leading-none text-white">{{ $kuotaPerizinan->sisa ?? 0 }} <span class="text-[9px] opacity-80 font-bold">HARI</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="lg:col-span-8 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 h-full">
            {{-- REKAP --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col">
                <h3 class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest mb-6 flex items-center gap-2">
                    <div class="w-1 h-4 bg-blue-600 rounded-full"></div>
                    Rekap Bulan Ini
                </h3>

                <div class="space-y-4 flex-1">
                    @foreach (['hadir' => ['Hadir', 'bg-emerald-500', 'text-emerald-700'], 'telat' => ['Telat', 'bg-amber-500', 'text-amber-700'], 'alfa' => ['Alfa', 'bg-rose-500', 'text-rose-700'], 'izin' => ['Izin', 'bg-blue-500', 'text-blue-700']] as $key => [$label, $color, $text])
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $label }}</span>
                                <span class="text-[10px] font-black {{ $text }}">{{ $rekapBulan[$key] ?? 0 }} Hari</span>
                            </div>
                            <div class="w-full bg-gray-100/50 rounded-full h-1.5 border border-gray-100 overflow-hidden">
                                <div class="{{ $color }} h-full rounded-full transition-all duration-1000 shadow-sm" 
                                     style="width: {{ min(100, (($rekapBulan[$key] ?? 0) / 25) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-5">
                {{-- SLIP --}}
                <div class="flex-1 bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                    <h3 class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest mb-6 flex items-center gap-2">
                        <div class="w-1 h-4 bg-[#1E3A5F] rounded-full"></div>
                        Slip Gaji Terakhir
                    </h3>

                    @if ($slipTerbaru)
                        <div class="mb-5">
                            <p class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">{{ $slipTerbaru->periodeGaji->nama_periode }}</p>
                            <p class="text-3xl font-black text-[#1E3A5F] tracking-tighter">
                                <span class="text-sm font-bold opacity-40">Rp</span>{{ number_format($slipTerbaru->gaji_bersih, 0, ',', '.') }}
                            </p>
                        </div>
                        <a href="{{ route('karyawan.slip-gaji.show', $slipTerbaru->id) }}" 
                           class="inline-flex items-center gap-2 px-5 py-3 bg-[#1E3A5F] text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-white"></i> Detail Slip
                        </a>
                    @else
                        <div class="py-4 text-center border-2 border-dashed border-gray-50 rounded-2xl">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic">Belum Ada Data</p>
                        </div>
                    @endif
                </div>

                {{-- QUICK ACTION --}}
                <div class="bg-[#1E3A5F] rounded-3xl p-6 text-white relative overflow-hidden group shadow-md shadow-blue-900/10">
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-widest mb-0.5 text-white">Butuh Izin?</h4>
                            <p class="text-[9px] font-bold text-white/50 italic uppercase tracking-wider">Ajukan Sekarang</p>
                        </div>
                        <a href="{{ route('karyawan.perizinan.create') }}" 
                           class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center hover:bg-emerald-500 transition-all border border-white/10 shadow-lg">
                            <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endsection
