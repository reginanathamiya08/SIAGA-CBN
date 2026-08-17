@if($selectedPeriode && $selectedPeriode->isProses())
    <div class="p-6 bg-amber-50 border border-amber-200 rounded-3xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-amber-100 text-amber-800 rounded-2xl flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-amber-900">Persetujuan Penggajian Diperlukan</h4>
                <p class="text-xs text-amber-700 font-semibold mt-0.5">
                    Status periode <strong>{{ $selectedPeriode->nama_periode }}</strong> saat ini sedang diproses. Tentukan keputusan persetujuan per karyawan di bawah, kemudian kirimkan keputusan Anda.
                </p>
            </div>
        </div>
        <div class="shrink-0 w-full md:w-auto">
            <button type="submit" form="form-persetujuan" class="w-full px-6 py-3.5 bg-[#1E3A5F] hover:bg-blue-900 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-blue-100 uppercase tracking-wider">
                Kirim Keputusan Penggajian
            </button>
        </div>
    </div>
@endif
