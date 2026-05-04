<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mt-6">
    <h3 class="font-black text-[#1E3A5F] italic text-[11px] mb-6 flex items-center gap-2">
        <span class="w-1 h-4 bg-orange-500 rounded-full"></span>
        Konfigurasi Shift (Khusus Satpam)
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach([1, 2] as $num)
        @php
            $shift = null;
            if (isset($mitra) && $mitra->shifts) {
                $shift = $mitra->shifts->where('nama_shift', 'Shift ' . $num)->first();
            }
        @endphp
        <div class="space-y-4 p-5 bg-gray-50/50 rounded-[2rem] border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity pointer-events-none">
                <i data-lucide="clock" class="w-20 h-20 text-[#1E3A5F]"></i>
            </div>
            
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-black text-[#1E3A5F] text-xs uppercase tracking-widest">Shift {{ $num }}</h4>
                @if($shift)
                    <input type="hidden" name="shifts[{{ $num }}][id]" value="{{ $shift->id }}">
                @endif
                <input type="hidden" name="shifts[{{ $num }}][nama_shift]" value="Shift {{ $num }}">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Jam Mulai</label>
                    <input type="time" name="shifts[{{ $num }}][jam_mulai]" value="{{ old('shifts.'.$num.'.jam_mulai', $shift?->jam_mulai) }}"
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold outline-none focus:border-orange-400">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Jam Selesai</label>
                    <input type="time" name="shifts[{{ $num }}][jam_selesai]" value="{{ old('shifts.'.$num.'.jam_selesai', $shift?->jam_selesai) }}"
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold outline-none focus:border-orange-400">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 text-emerald-600">Window Mulai</label>
                    <input type="time" name="shifts[{{ $num }}][window_start]" value="{{ old('shifts.'.$num.'.window_start', $shift?->window_start) }}"
                           class="w-full px-3 py-2 rounded-xl border border-emerald-100 bg-emerald-50/30 text-xs font-bold outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 text-emerald-600">Window Akhir</label>
                    <input type="time" name="shifts[{{ $num }}][window_end]" value="{{ old('shifts.'.$num.'.window_end', $shift?->window_end) }}"
                           class="w-full px-3 py-2 rounded-xl border border-emerald-100 bg-emerald-50/30 text-xs font-bold outline-none focus:border-emerald-400">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Toleransi (Menit)</label>
                    <input type="number" name="shifts[{{ $num }}][toleransi_menit]" value="{{ old('shifts.'.$num.'.toleransi_menit', $shift?->toleransi_menit ?? 15) }}"
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold outline-none focus:border-orange-400">
                </div>
                <div class="flex flex-col justify-end">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="shifts[{{ $num }}][is_lintas_hari]" value="1" {{ old('shifts.'.$num.'.is_lintas_hari', $shift?->is_lintas_hari) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                        <span class="text-[9px] font-black text-gray-500 uppercase group-hover:text-orange-500 transition-colors">Lintas Hari?</span>
                    </label>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <p class="mt-4 text-[9px] text-gray-400 italic">
        * Window Mulai/Akhir menentukan kapan tombol "Absen Masuk" muncul/aktif untuk shift tersebut.
    </p>
</div>
