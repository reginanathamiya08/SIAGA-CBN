<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Karyawan') — Sistem CBN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config={theme:{extend:{colors:{cbn:'#1E3A5F'}}}}
        
        function confirmLogout(event, form) {
            event.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Keluar',
                    text: 'Apakah Anda yakin ingin keluar dari sistem?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Ya, Keluar',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                    form.submit();
                }
            }
            return false;
        }

        // Konfigurasi Global SweetAlert
        window.addEventListener('load', () => {
            @if(session('absen_popup'))
                @php $p = session('absen_popup'); @endphp
                Swal.fire({
                    icon: '{{ $p['is_telat'] ? 'warning' : 'success' }}',
                    title: '{{ $p['title'] }}',
                    html: `
                        <div class="py-2 text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Waktu Absen Dicatat</p>
                            <p class="text-3xl font-black text-[#1E3A5F] tracking-tight mb-3">{{ $p['waktu'] }}</p>
                            @if($p['type'] === 'masuk')
                                <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider {{ $p['is_telat'] ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                                    {{ $p['is_telat'] ? '⚠️ Status: Terlambat (Telat)' : '✓ Status: Tepat Waktu (Hadir)' }}
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-300">
                                    🏠 Status: Selesai Bekerja
                                </div>
                            @endif
                        </div>
                    `,
                    confirmButtonColor: '#1E3A5F',
                    confirmButtonText: 'Siap, Mengerti',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5'
                    }
                });
            @elseif(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#1E3A5F',
                    borderRadius: '20px'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Waduh...',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#1E3A5F'
                });
            @endif
        });
    </script>
    @stack('styles')
    <style>
        /* Force SweetAlert2 container above fixed sidebar */
        .swal2-container {
            z-index: 999999 !important;
        }

        /* Matikan animasi & transisi di seluruh halaman KECUALI fungsionalitas sidebar */
        .page-animate,
        .page-animate *:not(aside):not(aside *):not(#sidebar-toggle-btn):not(#sidebar-toggle-btn *) {
            transition: none !important;
            transition-property: none !important;
            transition-duration: 0s !important;
            animation: none !important;
            animation-duration: 0s !important;
        }

        /* Pertahankan transisi halus khusus untuk sliding sidebar */
        aside,
        aside *,
        .sidebar-slide {
            transition-property: transform, width, margin, opacity !important;
            transition-duration: 300ms !important;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
    </style>
</head>
@php
    $unreadNotifs = \App\Models\Notification::where('user_id', Auth::user()->karyawan?->id)
                    ->where('is_read', false)
                    ->orderBy('created_at', 'desc')
                    ->get();
    $allNotifs = \App\Models\Notification::where('user_id', Auth::user()->karyawan?->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
@endphp
<body class="bg-[#F1F5F9] min-h-screen text-slate-700" x-data="{sidebarOpen: window.innerWidth >= 768, notifOpen:false}">

<aside class="fixed top-0 left-0 h-full bg-white text-slate-700 border-r border-gray-100 z-[2000]
              transition-all duration-300 flex flex-col w-64 md:translate-x-0 shadow-sm"
       :class="sidebarOpen ? 'translate-x-0 md:w-64' : '-translate-x-full md:translate-x-0 md:w-16'">

    <div class="flex items-center gap-3 px-4 py-6 border-b border-gray-100">
        <div class="w-12 h-12 rounded-xl overflow-hidden bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0 shadow-inner">
            <img src="{{ asset('image/logo_cbn.jpg') }}" 
                 alt="Logo PT CBN" 
                 class="w-full h-full object-contain">
        </div>
        <div x-show="sidebarOpen" x-transition.opacity>
            <p class="text-xs font-black text-[#1E3A5F] leading-none">PT Citra Bangun Nagari</p>
            <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-tight">
                {{ Auth::user()->isKaryawanTetap() ? 'Karyawan Tetap' : 'Karyawan Kontrak' }}
            </p>
        </div>
    </div>

    <nav class="flex-1 py-4 overflow-y-auto">
        <div class="px-3 space-y-0.5">

            <div class="pb-2 px-3" x-show="sidebarOpen">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Menu Utama</p>
            </div>

            <a href="{{ route('karyawan.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.dashboard') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity
                      class="text-[11px] font-black uppercase tracking-wider">Dashboard</span>
            </a>

            <a href="{{ route('karyawan.absensi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.absensi.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="fingerprint" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity
                      class="text-[11px] font-black uppercase tracking-wider">Absensi</span>
            </a>

            <a href="{{ route('karyawan.slip-gaji.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.slip-gaji.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity
                      class="text-[11px] font-black uppercase tracking-wider">Slip Gaji</span>
            </a>

            <div class="pt-4 pb-2 px-3" x-show="sidebarOpen">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Layanan Mandiri</p>
            </div>

            <a href="{{ route('karyawan.perizinan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.perizinan.index') || request()->routeIs('karyawan.perizinan.create') || request()->routeIs('karyawan.perizinan.show') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="calendar-x" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity
                      class="text-[11px] font-black uppercase tracking-wider">Pengajuan Izin</span>
            </a>

            <a href="{{ route('karyawan.dinas-luar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.dinas-luar.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity
                      class="text-[11px] font-black uppercase tracking-wider">Dinas Luar Kota</span>
            </a>

            <a href="{{ route('karyawan.perizinan.backup.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.perizinan.backup.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="users-round" class="w-4 h-4 shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition.opacity
                          class="text-[11px] font-black uppercase tracking-wider">Persetujuan Cuti Rekan</span>
                </div>
                @php
                    $pendingBackups = \App\Models\DetailPerizinan::where('rekan_kerja_id', Auth::user()->id)
                                        ->where('status_rekan', 'menunggu')
                                        ->count();
                @endphp
                @if($pendingBackups > 0)
                    <span x-show="sidebarOpen" class="bg-red-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full shrink-0">
                        {{ $pendingBackups }}
                    </span>
                @endif
            </a>

            @if (Auth::user()->isKaryawanTetap())
                <a href="{{ route('karyawan.lembur.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                          {{ request()->routeIs('karyawan.lembur.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                    <i data-lucide="clock" class="w-4 h-4 shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition.opacity
                          class="text-[11px] font-black uppercase tracking-wider">Lembur</span>
                </a>
            @endif

            <div class="pt-4 pb-2 px-3" x-show="sidebarOpen">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Pengaturan</p>
            </div>

            <a href="{{ route('karyawan.password.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('karyawan.password.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="lock" class="w-4 h-4 shrink-0"></i>
                <span x-show="sidebarOpen" x-transition.opacity
                      class="text-[11px] font-black uppercase tracking-wider">Ubah Password</span>
            </a>

        </div>
    </nav>

    <div class="border-t border-gray-100 p-3 bg-gray-50/50">
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 bg-[#1E3A5F] rounded-xl flex items-center justify-center
                        shrink-0 font-black text-xs text-white">
                {{ strtoupper(substr(Auth::user()->karyawan?->nama ?? Auth::user()->nip, 0, 2)) }}
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="flex-1 min-w-0">
                <p class="text-[11px] font-black text-[#1E3A5F] truncate uppercase">
                    {{ Auth::user()->karyawan?->nama ?? Auth::user()->nip }}
                </p>
                <p class="text-[9px] text-gray-400 font-medium">
                    {{ Auth::user()->karyawan?->jabatan ?? '-' }}
                </p>
                <p class="text-[8px] text-gray-300 font-bold uppercase tracking-wider mt-0.5">
                    NIP: {{ Auth::user()->nip ?? '-' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen" onsubmit="return confirmLogout(event, this)">
                @csrf
                <button type="submit"
                        class="text-gray-400 hover:text-red-500 transition-colors"
                        title="Keluar dari Sistem">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Backdrop Overlay -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false" 
     x-transition:opacity
     class="fixed inset-0 bg-black/40 z-[1990] md:hidden">
</div>

<div class="transition-all duration-300 sidebar-slide page-animate min-w-0" :class="sidebarOpen ? 'ml-0 md:ml-64' : 'ml-0 md:ml-16'">
    <div class="sticky top-0 z-[1005] bg-white/80 backdrop-blur-sm border-b border-gray-100
                px-6 py-3 flex items-center gap-4">
        <button @click="sidebarOpen=!sidebarOpen"
                class="text-gray-400 hover:text-[#1E3A5F] transition-colors">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
        <div class="flex-1"></div>
        
        {{-- NOTIFICATION BELL --}}
        <div class="relative" @click.away="notifOpen = false">
            <button @click="notifOpen = !notifOpen"
                    class="w-10 h-10 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center hover:bg-[#1E3A5F] hover:text-white transition-all relative group shadow-sm">
                <i data-lucide="bell" class="w-4 h-4"></i>
                @if($unreadNotifs->count() > 0)
                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                @endif
            </button>

            {{-- DROPDOWN --}}
            <div x-show="notifOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="fixed sm:absolute top-16 sm:top-auto left-4 sm:left-auto right-4 sm:right-0 mt-2 sm:mt-3 sm:w-72 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 overflow-hidden"
                 style="display: none;">
                
                <div class="p-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest">Notifikasi</h3>
                    @if($unreadNotifs->count() > 0)
                        <form method="POST" action="{{ route('karyawan.notifications.mark-all') }}">
                            @csrf
                            <button type="submit" class="text-[8px] font-bold text-blue-600 hover:underline uppercase">Tandai Dibaca</button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto bg-white">
                    @forelse($allNotifs as $n)
                        <a href="{{ route('karyawan.notifications.read', $n->id) }}" 
                           class="block p-4 hover:bg-[#1E3A5F]/5 transition-all border-b border-gray-50 last:border-0 relative">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center {{ $n->is_read ? 'bg-gray-100 text-gray-400' : 'bg-blue-100 text-blue-600' }}">
                                    <i data-lucide="{{ $n->type == 'success' ? 'check-circle' : ($n->type == 'danger' ? 'alert-circle' : 'bell') }}" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black text-[#1E3A5F] leading-tight truncate">{{ $n->title }}</p>
                                    <p class="text-[9px] text-gray-500 mt-1 leading-snug line-clamp-2">{{ $n->message }}</p>
                                    <p class="text-[7px] text-gray-400 mt-1 font-bold uppercase">{{ $n->created_at->diffForHumans() }}</p>
                                </div>
                                @if(!$n->is_read)
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-1.5 shrink-0"></div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <i data-lucide="bell-off" class="w-10 h-10 text-gray-100 mx-auto mb-2"></i>
                            <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest italic">Belum ada notifikasi</p>
                        </div>
                    @endforelse
                </div>

                <div class="p-3 bg-gray-50/50 border-t border-gray-50 text-center">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Sistem Notifikasi PT CBN</p>
                </div>
            </div>
        </div>

        <span class="text-[11px] font-semibold text-gray-400 hidden sm:inline-block">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <main class="px-6 pt-6 pb-8">@yield('content')</main>
</div>

<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
