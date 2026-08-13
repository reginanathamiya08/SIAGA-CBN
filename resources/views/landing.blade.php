<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Citra Bangun Nagari — Solusi Terpercaya Pengelolaan SDM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .text-gradient {
            background: linear-gradient(to right, #1E3A5F, #3B82F6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .bg-gradient-cbn {
            background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
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
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

    {{-- Navbar --}}
    <nav id="navbar" x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 w-full z-[99999] transition-all duration-300 border-b border-transparent bg-white/75 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center transition-all duration-300">
            <div class="flex items-center gap-3">
                <img src="{{ asset('image/logo_cbn.jpg') }}" alt="Logo" class="h-10 w-auto rounded-lg">
                <span class="font-black text-[#1E3A5F] tracking-tighter text-xl">PT. Citra Bangun Nagari</span>
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#beranda" class="relative group hover:text-[#1E3A5F] transition-colors py-1">
                    Beranda
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#1E3A5F] transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#tentang" class="relative group hover:text-[#1E3A5F] transition-colors py-1">
                    Tentang Kami
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#1E3A5F] transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#layanan" class="relative group hover:text-[#1E3A5F] transition-colors py-1">
                    Layanan
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#1E3A5F] transition-all duration-300 group-hover:w-full"></span>
                </a>
                @guest
                    <a href="{{ route('login') }}" class="bg-[#1E3A5F] text-white px-6 py-2.5 rounded-xl hover:bg-blue-600 hover:scale-105 active:scale-90 active:bg-slate-800 transition-all duration-200 shadow-md shadow-blue-900/10 font-bold">
                        Masuk Sistem
                    </a>
                @else
                    <a href="{{ url('/admin/dashboard') }}" class="bg-[#1E3A5F] text-white px-6 py-2.5 rounded-xl hover:bg-blue-600 hover:scale-105 active:scale-90 active:bg-slate-800 transition-all duration-200 shadow-md shadow-blue-900/10 font-bold">
                        Dashboard
                    </a>
                @endguest
            </div>

            {{-- Hamburger Button (Mobile Only) --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 hover:text-[#1E3A5F] transition-colors focus:outline-none" aria-label="Toggle Menu">
                <i data-lucide="menu" x-show="!mobileMenuOpen" class="w-6 h-6"></i>
                <i data-lucide="x" x-show="mobileMenuOpen" class="w-6 h-6" x-cloak></i>
            </button>
        </div>

        {{-- Mobile Navigation Menu --}}
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200 transform" 
             x-transition:enter-start="opacity-0 -translate-y-4" 
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden bg-white border-t border-slate-100 px-6 py-6 space-y-4 shadow-lg" x-cloak>
            <a href="#beranda" @click="mobileMenuOpen = false" class="block font-bold text-slate-600 hover:text-[#1E3A5F] transition-colors py-2">
                Beranda
            </a>
            <a href="#tentang" @click="mobileMenuOpen = false" class="block font-bold text-slate-600 hover:text-[#1E3A5F] transition-colors py-2">
                Tentang Kami
            </a>
            <a href="#layanan" @click="mobileMenuOpen = false" class="block font-bold text-slate-600 hover:text-[#1E3A5F] transition-colors py-2">
                Layanan
            </a>
            <div class="pt-4 border-t border-slate-100">
                @guest
                    <a href="{{ route('login') }}" class="block text-center bg-[#1E3A5F] text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-600 transition-colors">
                        Masuk Sistem
                    </a>
                @else
                    <a href="{{ url('/admin/dashboard') }}" class="block text-center bg-[#1E3A5F] text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-600 transition-colors">
                        Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </nav>

    <div class="page-animate">
        {{-- Hero Section --}}
    <section id="beranda" class="min-h-screen pt-32 pb-20 px-6 flex items-center relative overflow-hidden">
        <div class="absolute top-0 right-0 -z-10 w-1/2 h-full bg-blue-50 rounded-l-[100px] hidden lg:block"></div>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-xs font-bold tracking-widest uppercase">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    Official Landing Page
                </div>
                <h1 class="text-5xl lg:text-7xl font-black leading-[1.1] text-slate-900">
                    Membangun Masa Depan <br> 
                    <span class="text-gradient">SDM Berkualitas.</span>
                </h1>
                <p class="text-lg text-slate-500 max-w-lg leading-relaxed font-medium">
                    PT Citra Bangun Nagari hadir sebagai mitra strategis dalam pengelolaan tenaga kerja, outsourcing, dan layanan profesional terbaik di Sumatera Barat.
                </p>
                <div class="flex flex-wrap gap-4">
                    @guest
                        <a href="{{ route('login') }}" class="group px-8 py-4 bg-[#1E3A5F] text-white rounded-2xl font-black shadow-2xl shadow-blue-900/40 hover:translate-y-[-4px] hover:scale-105 active:scale-90 active:bg-slate-800 transition-all duration-200 flex items-center gap-2">
                            Masuk Sistem 
                            <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @else
                        @php
                            $dashboardRoute = 'login'; // fallback
                            if(auth()->user()->role == 'admin') $dashboardRoute = 'admin.dashboard';
                            elseif(auth()->user()->role == 'pimpinan') $dashboardRoute = 'pimpinan.dashboard';
                            else $dashboardRoute = 'karyawan.dashboard';
                        @endphp
                        <a href="{{ route($dashboardRoute) }}" class="group px-8 py-4 bg-[#1E3A5F] text-white rounded-2xl font-black shadow-2xl shadow-blue-900/40 hover:translate-y-[-4px] hover:scale-105 active:scale-90 active:bg-slate-800 transition-all duration-200 flex items-center gap-2">
                            Kembali ke Dashboard 
                            <i data-lucide="layout-dashboard" class="w-5 h-5 group-hover:rotate-12 transition-transform"></i>
                        </a>
                    @endguest
                    <a href="#tentang" class="px-8 py-4 bg-white text-slate-600 border border-slate-200 rounded-2xl font-black hover:bg-slate-50 hover:scale-105 active:scale-90 active:bg-slate-100 transition-all duration-200">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
            <div class="relative animate-float max-w-lg mx-auto lg:mr-0">
                <div class="relative z-10 rounded-[40px] overflow-hidden shadow-2xl border-[8px] border-white">
                    <img src="{{ asset('image/pt_cbn.jpeg') }}" alt="Office PT CBN" class="w-full h-auto object-cover">
                </div>
                <div class="absolute -bottom-10 -left-10 glass p-6 rounded-3xl shadow-xl z-20 max-w-[200px]">
                    <p class="text-3xl font-black text-[#1E3A5F]">15+</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Tahun Pengalaman</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Tentang Kami --}}
    <section id="tentang" class="py-24 bg-white px-6">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-5xl font-black text-slate-900 mb-4">Profil Perusahaan</h2>
                <div class="w-20 h-2 bg-[#1E3A5F] mx-auto rounded-full"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-12 items-center">                <div class="space-y-6">
                    <p class="text-lg text-slate-600 leading-relaxed">
                        <b>PT Citra Bangun Nagari</b> merupakan perusahaan yang bergerak di bidang penyediaan jasa tenaga kerja (outsourcing), pembersihan gedung (cleaning service), dan berbagai layanan pendukung bisnis lainnya.
                    </p>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        Kami berkomitmen untuk memberikan solusi SDM yang terintegrasi, profesional, dan berorientasi pada kepuasan mitra kerja kami di seluruh wilayah Sumatera Barat dan sekitarnya.
                    </p>
                    
                    {{-- Motto Blockquote --}}
                    <div class="p-6 bg-slate-50 border-l-4 border-[#1E3A5F] rounded-r-2xl">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Motto Perusahaan</p>
                        <p class="text-lg font-bold text-[#1E3A5F] italic leading-relaxed">
                            "Tumbuh Bersama Mitra, Melayani dengan Integritas"
                        </p>
                    </div>

                    {{-- Tujuan Perusahaan --}}
                    <div class="p-6 bg-[#1E3A5F]/5 rounded-3xl border border-[#1E3A5F]/10 space-y-3">
                        <h4 class="text-sm font-black text-[#1E3A5F] flex items-center gap-2">
                            <i data-lucide="compass" class="w-4 h-4 text-blue-600"></i>
                            Tujuan Perusahaan
                        </h4>
                        <div class="space-y-2 text-xs text-slate-600 font-bold leading-relaxed">
                            <div class="flex gap-2.5 items-start">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-1.5 shrink-0"></span>
                                <p>Menjalankan usaha dibidang perdagangan, pembangunan konstruksi, persewaan gedung, jasa tenaga kerja, jasa pengamanan, pertanian dan perikanan, pergudangan, dan percetakan yang dibutuhkan oleh instansi kepemerintahan, BUMN, BUMD, dan perusahaan swasta lainnya.</p>
                            </div>
                            <div class="flex gap-2.5 items-start">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-1.5 shrink-0"></span>
                                <p>Mencarikan, memberikan peluang kerja bagi para pencari kerja dan memberikan kontribusi pada stakeholder, karyawan, masyarakat, dan pemerintah.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 pt-2">
                        <div class="p-4 rounded-2xl bg-slate-50">
                            <i data-lucide="check-circle" class="w-8 h-8 text-blue-600 mb-3"></i>
                            <p class="font-bold text-sm">Terakreditasi</p>
                            <p class="text-xs text-slate-400">Memiliki izin resmi operasional</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50">
                            <i data-lucide="users" class="w-8 h-8 text-blue-600 mb-3"></i>
                            <p class="font-bold text-sm">Profesional</p>
                            <p class="text-xs text-slate-400">Tenaga kerja terlatih dan kompeten</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-cbn p-10 rounded-[50px] text-white shadow-xl relative overflow-hidden space-y-8">
                    {{-- Decorative Background Gradients --}}
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-600/10 rounded-full blur-3xl"></div>

                    {{-- Visi Perusahaan --}}
                    <div class="space-y-4 relative z-10">
                        <h4 class="text-lg font-black flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-500/20 flex items-center justify-center">
                                <i data-lucide="eye" class="w-4 h-4 text-blue-400"></i>
                            </div>
                            Visi Perusahaan
                        </h4>
                        <p class="text-blue-100 text-sm leading-relaxed italic">
                            "Menjadi perusahaan swasta nasional yang dapat menjalin mitra kerja dengan instansi pemerintah, BUMN, dan Perusahaan Swasta lainnya secara profesional dan berkelanjutan."
                        </p>
                    </div>

                    <div class="border-t border-white/10 my-6"></div>

                    {{-- Misi Perusahaan --}}
                    <div class="space-y-4 relative z-10">
                        <h4 class="text-lg font-black flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-500/20 flex items-center justify-center">
                                <i data-lucide="target" class="w-4 h-4 text-blue-400"></i>
                            </div>
                            Misi Perusahaan
                        </h4>
                        <div class="space-y-3 text-sm text-blue-100 font-medium">
                            <div class="flex gap-3 items-start">
                                <span class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center shrink-0 text-[10px] font-bold text-blue-300">1</span>
                                <p>Meningkatkan nilai kompetensi dalam bidang bisnis.</p>
                            </div>
                            <div class="flex gap-3 items-start">
                                <span class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center shrink-0 text-[10px] font-bold text-blue-300">2</span>
                                <p>Menciptakan nilai yang berkesinambungan kepada pelanggan/owner, management, karyawan, investor, pemegang saham dan berbagai pihak lain yan berkepentingan.</p>
                            </div>
                            <div class="flex gap-3 items-start">
                                <span class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center shrink-0 text-[10px] font-bold text-blue-300">3</span>
                                <p>Meningkatkan daya saing perusahaan di industri jasa, konstruksi, perdagangan dengan mengembangkan pelayanan dan teknologi terbaik kepada konsumen dalam memenuhi harapan pemangku kepentingan.</p>
                            </div>
                            <div class="flex gap-3 items-start">
                                <span class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center shrink-0 text-[10px] font-bold text-blue-300">4</span>
                                <p>Mengurangi tingkat pengangguran dengan memberikan peluang kerja bagi para pencari kerja untuk ditempatkan sebagai tenaga kerja outsourcing.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Layanan --}}
    <section id="layanan" class="py-24 bg-slate-50 px-6">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-3xl lg:text-5xl font-black text-slate-900 mb-4">Layanan Kami</h2>
            <p class="text-slate-500 max-w-2xl mx-auto mb-16 text-base font-medium">
                Penyediaan Tenaga Kerja Alih Daya (Outsourcing) PT Citra Bangun Nagari terbagi dalam 2 Divisi Utama:
            </p>

            <div class="grid md:grid-cols-2 gap-8 text-left">
                {{-- Divisi HC --}}
                <div class="bg-white p-8 lg:p-10 rounded-[36px] shadow-lg border border-slate-100 hover:border-blue-200 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-bl-full"></div>
                    <div class="w-14 h-14 bg-blue-100 text-[#1E3A5F] rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#1E3A5F] group-hover:text-white transition-colors">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-black uppercase tracking-wider">Divisi HC</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-3 mb-2">Human Capital (HC)</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Penyediaan dan pengelolaan Tenaga Ahli Daya (TAD) profesional untuk fungsi pengamanan, teknis, pemasaran, dan operasional spesialis.
                    </p>
                    <div class="space-y-3">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Penyediaan Tenaga Kerja:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                ['shield', 'Satpam'],
                                ['car', 'Sopir'],
                                ['trending-up', 'Marketing'],
                                ['utensils', 'Pramusaji'],
                                ['user-check', 'Pramubakti'],
                                ['phone-call', 'Call Centre'],
                                ['credit-card', 'Card Centre'],
                                ['cpu', 'E-Channel'],
                                ['square-p', 'Juru Parkir'],
                                ['wrench', 'Teknisi'],
                                ['monitor', 'Monitoring ATM & Jaringan'],
                                ['file-text', 'PPID'],
                            ] as [$ic, $nama])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 transition-all">
                                    <i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-blue-600"></i>
                                    {{ $nama }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Divisi Umum --}}
                <div class="bg-white p-8 lg:p-10 rounded-[36px] shadow-lg border border-slate-100 hover:border-emerald-200 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-bl-full"></div>
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i data-lucide="sparkles" class="w-7 h-7"></i>
                    </div>
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-black uppercase tracking-wider">Divisi Umum</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-3 mb-2">Divisi Umum</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                        Penyediaan layanan kebersihan gedung/kantor komersial, kebersihan area ATM, serta operasional ekspedisi & logistik terpadu.
                    </p>
                    <div class="space-y-3">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-wider">Penyediaan Tenaga Kerja:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                ['sparkles', 'Cleaning Service'],
                                ['vault', 'CS ATM'],
                                ['truck', 'Ekspedisi'],
                            ] as [$ic, $nama])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 transition-all">
                                    <i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    {{ $nama }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-white pt-20 pb-10 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-3 gap-16 pb-16 border-b border-white/10">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('image/logo_cbn.jpg') }}" alt="Logo" class="h-12 w-auto rounded-xl">
                        <span class="font-black tracking-tighter text-2xl">PT CBN</span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Solusi terpercaya untuk pengelolaan SDM dan layanan pendukung bisnis di Sumatera Barat. Berkomitmen pada kualitas dan profesionalisme.
                    </p>
                </div>
                <div class="space-y-6">
                    <h5 class="text-lg font-bold">Kontak Kami</h5>
                    <ul class="space-y-4 text-sm text-slate-400">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 text-blue-500"></i>
                            Jl. Pemuda No. 23 F Padang, Sumatera Barat
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="w-5 h-5 text-blue-500"></i>
                            (0751) 37319
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="w-5 h-5 text-blue-500"></i>
                            citra_bangunnagari@yahoo.com
                        </li>
                    </ul>
                </div>
                
                <div class="space-y-6">
                    <h5 class="text-lg font-bold">Akses Cepat</h5>
                    <div class="flex flex-col gap-4 text-sm text-slate-400 font-semibold">
                        <a href="{{ route('login') }}" class="text-white hover:text-blue-400">Masuk ke Sistem</a>
                        <a href="#beranda" class="hover:text-white">Beranda</a>
                        <a href="#tentang" class="hover:text-white">Tentang Perusahaan</a>
                    </div>
                </div>
            </div>
            <div class="pt-10 text-center text-slate-500 text-xs font-semibold tracking-widest">
                &copy; {{ date('Y') }} PT CITRA BANGUN NAGARI. ALL RIGHTS RESERVED.
            </div>
        </div>
    </footer>

    </div> {{-- End Page Animate Wrapper --}}

    <script>
        lucide.createIcons();

        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            const navContainer = nav.querySelector('div');
            
            if (window.scrollY > 10) {
                nav.classList.add('bg-white', 'shadow-md', 'border-slate-100');
                nav.classList.remove('bg-white/75', 'border-transparent');
                navContainer.classList.remove('py-4');
                navContainer.classList.add('py-3');
            } else {
                nav.classList.add('bg-white/75', 'border-transparent');
                nav.classList.remove('bg-white', 'shadow-md', 'border-slate-100');
                navContainer.classList.remove('py-3');
                navContainer.classList.add('py-4');
            }
        });
    </script>
</body>
</html>
