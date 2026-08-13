<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Admin') — Sistem CBN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config={theme:{extend:{colors:{cbn:'#1E3A5F'}}}}
        
        // Konfigurasi Global SweetAlert
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

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

        window.addEventListener('load', () => {
            @if(session('success'))
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
        /* Custom Scrollbar for Sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Page Transition Effect */
        @keyframes pageIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-animate {
            animation: pageIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
    </style>
    @php
        $unreadNotifs = \App\Models\Notification::where('user_id', Auth::user()->id)
                        ->where('is_read', false)
                        ->orderBy('created_at', 'desc')
                        ->get();
        $allNotifs = \App\Models\Notification::where('user_id', Auth::user()->id)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
    @endphp
</head>
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
            <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase tracking-tight">Admin Control Panel</p>
        </div>
    </div>

    <nav class="flex-1 py-4 overflow-y-auto sidebar-scroll">

        <div class="px-3 mb-2">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.dashboard') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Dashboard</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-5 pb-2">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Data Master</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.karyawan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.karyawan.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="users" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Data Karyawan</span>
            </a>

            <a href="{{ route('admin.mitra.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.mitra.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="building-2" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Kelola Mitra</span>
            </a>

            <a href="{{ route('admin.penempatan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.penempatan.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="map-pin" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Penempatan</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-5 pb-2">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Payroll</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.komponen-gaji-karyawan.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.komponen-gaji-karyawan.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="wallet" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Komponen Gaji</span>
            </a>

            <a href="{{ route('admin.penggajian.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.penggajian.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="calculator" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Proses Gaji</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-5 pb-2">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Laporan</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.laporan.absensi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.laporan.absensi.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="file-text" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Laporan Absensi</span>
            </a>
            <a href="{{ route('admin.laporan.gaji.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.laporan.gaji.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="bar-chart-2" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Laporan Gaji</span>
            </a>
            <a href="{{ route('admin.laporan.lembur.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.laporan.lembur.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="clock" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Rekap Lembur</span>
            </a>
        </div>

        <div x-show="sidebarOpen" class="px-6 pt-5 pb-2">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Sistem</p>
        </div>
        <div class="px-3 space-y-0.5">
            <a href="{{ route('admin.konfigurasi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('admin.konfigurasi.*') ? 'bg-[#1E3A5F] text-white shadow-md shadow-blue-900/10' : 'text-gray-500 hover:bg-gray-50 hover:text-[#1E3A5F]' }}">
                <i data-lucide="settings" class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="text-xs font-bold">Konfigurasi</span>
            </a>
        </div>

    </nav>

    <div class="border-t border-gray-100 p-3 bg-gray-50/50">
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 bg-[#1E3A5F] rounded-xl flex items-center justify-center shrink-0 font-black text-xs text-white">
                {{ strtoupper(substr(Auth::user()->nip,0,2)) }}
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="flex-1 min-w-0">
                <p class="text-[11px] font-black text-[#1E3A5F] truncate">{{ Auth::user()->nip }}</p>
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Administrator</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen" onsubmit="return confirmLogout(event, this)">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Keluar dari Sistem">
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

<div class="transition-all duration-300 page-animate min-w-0" :class="sidebarOpen ? 'ml-0 md:ml-64' : 'ml-0 md:ml-16'">
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
                    class="w-10 h-10 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center hover:bg-[#1E3A5F] hover:text-white transition-all relative group shadow-sm text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
                @if($unreadNotifs->count() > 0)
                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                @endif
            </button>

            {{-- DROPDOWN --}}
            <div x-show="notifOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 mt-3 w-72 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 overflow-hidden"
                 style="display: none;">
                
                <div class="p-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-[10px] font-black text-[#1E3A5F] uppercase tracking-widest">Notifikasi</h3>
                    @if($unreadNotifs->count() > 0)
                        <form method="POST" action="{{ route('admin.notifications.mark-all') }}">
                            @csrf
                            <button type="submit" class="text-[8px] font-bold text-blue-600 hover:underline uppercase">Tandai Dibaca</button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto bg-white">
                    @forelse($allNotifs as $n)
                        <a href="{{ route('admin.notifications.read', $n->id) }}" 
                           class="block p-4 hover:bg-[#1E3A5F]/5 transition-all border-b border-gray-50 last:border-0 relative">
                            <div class="flex gap-3 text-left">
                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center {{ $n->is_read ? 'bg-gray-100 text-gray-400' : 'bg-blue-100 text-blue-600' }}">
                                    @if($n->type == 'success')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    @elseif($n->type == 'danger')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black text-[#1E3A5F] leading-tight truncate">{{ $n->title }}</p>
                                    <p class="text-[9px] text-gray-500 mt-1 leading-snug line-clamp-2 font-semibold">{{ $n->message }}</p>
                                    <p class="text-[7px] text-gray-400 mt-1 font-black uppercase">{{ $n->created_at->diffForHumans() }}</p>
                                </div>
                                @if(!$n->is_read)
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-1.5 shrink-0"></div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10 text-gray-200 mx-auto mb-2"><path d="M13.73 21a2 2 0 0 1-3.46 0"></path><path d="M18.63 13A17.89 17.89 0 0 1 18 8"></path><path d="M6.26 6.26A5.86 5.86 0 0 0 6 8c0 7-3 9-3 9h14"></path><path d="M18 8a6 6 0 0 0-9.33-5"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest italic">Belum ada notifikasi</p>
                        </div>
                    @endforelse
                </div>

                <div class="p-3 bg-gray-50/50 border-t border-gray-50 text-center">
                    <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Sistem Notifikasi PT CBN</p>
                </div>
            </div>
        </div>

        <span class="text-[11px] font-semibold text-gray-400">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <div class="px-6 pt-5">
    </div>

    <main class="px-6 pb-8">@yield('content')</main>
</div>

<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
