<form id="form-persetujuan" action="{{ route('pimpinan.monitoring-gaji.submit', $periodeId ?: 'none') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan dan mengirimkan keputusan persetujuan penggajian ini?')">
    @csrf

    <!-- Panel: Karyawan Tetap -->
    <div id="panel-gaji-tetap" class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/30 border-b border-gray-100 uppercase">
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Karyawan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Jabatan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Gaji Pokok</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Uang Makan</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Transportasi</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">BPJS TK</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">BPJS Kes</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Total Tunjangan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Potongan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] text-right">Gaji Bersih</th>
                    <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center w-[200px]">Persetujuan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($slipTetap as $slip)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-black text-xs shadow-sm shrink-0">
                                {{ strtoupper(substr($slip->karyawan?->nama ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors whitespace-nowrap">
                                        {{ $slip->karyawan?->nama ?? 'Karyawan Tidak Ditemukan' }}
                                    </p>
                                    @if($slip->status === 'direvisi')
                                        <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[8px] font-black rounded-md border border-emerald-200 uppercase tracking-wider shrink-0">Sudah Direvisi</span>
                                    @elseif($slip->status === 'ditolak')
                                        <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-[8px] font-black rounded-md border border-red-200 uppercase tracking-wider shrink-0">Ditolak</span>
                                    @endif
                                </div>
                                <p class="text-[10px] font-bold text-gray-400 tracking-tighter mt-0.5">{{ $slip->karyawan?->nip ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-xs font-bold text-[#1E3A5F] whitespace-nowrap">{{ $slip->karyawan?->jabatan ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-xs font-bold text-gray-700">Rp {{ number_format($slip->getNominal('Gaji Pokok'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($slip->getNominal('Uang Makan'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($slip->getNominal('Uang Transport'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($slip->getNominal('Tunjangan Jamsostek'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($slip->getNominal('Tunjangan Askes'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-5 py-4 text-center whitespace-nowrap">
                        @php $totalTunjangan = $slip->totalPendapatan() - $slip->getNominal('Gaji Pokok'); @endphp
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black">
                            + Rp {{ number_format($totalTunjangan, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center whitespace-nowrap">
                        <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-[10px] font-black">
                            - Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <p class="text-xs font-black text-[#1E3A5F]">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($selectedPeriode && $selectedPeriode->isProses())
                            <div class="flex flex-col gap-2" x-data="{ keputusan: 'setuju', alasan: '', isSaved: false }">
                                <div class="flex items-center justify-center gap-3">
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="slips[{{ $slip->id }}][status]" value="setuju" checked 
                                               @change="keputusan = 'setuju'; isSaved = false; alasan = ''"
                                               class="text-emerald-600 focus:ring-emerald-500 border-gray-300 w-4 h-4">
                                        <span class="text-xs font-black text-emerald-600">Setuju</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="slips[{{ $slip->id }}][status]" value="tolak" 
                                               @change="keputusan = 'tolak'"
                                               class="text-red-600 focus:ring-red-500 border-gray-300 w-4 h-4">
                                        <span class="text-xs font-black text-red-600">Tolak</span>
                                    </label>
                                </div>
                                
                                <input type="hidden" name="slips[{{ $slip->id }}][alasan]" :value="alasan">

                                <div x-show="keputusan === 'tolak' && !isSaved" x-transition class="mt-1 flex flex-col gap-1.5">
                                    <textarea x-model="alasan" placeholder="Tulis alasan penolakan..." rows="2"
                                              class="w-full p-2.5 border border-red-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none"></textarea>
                                    <button type="button" @click="if(alasan.trim() !== '') isSaved = true"
                                            class="self-end px-3 py-1 bg-[#1E3A5F] hover:bg-blue-900 text-white rounded-lg text-[10px] font-black transition-all">
                                        OK
                                    </button>
                                </div>

                                <div x-show="keputusan === 'tolak' && isSaved" x-transition class="mt-1 text-left p-2.5 bg-red-50 border border-red-100 rounded-xl max-w-[200px]">
                                    <p class="text-[10px] text-red-700 font-bold italic leading-relaxed">Catatan: "<span x-text="alasan"></span>"</p>
                                    <button type="button" @click="isSaved = false" class="text-[9px] text-blue-600 underline font-bold mt-1 block hover:text-blue-800">
                                        Ubah Catatan
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center whitespace-nowrap">
                                @if($slip->isDiterbitkan() || $slip->status === 'disetujui')
                                    <span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-black uppercase">Disetujui</span>
                                @elseif($slip->status === 'ditolak')
                                    <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase">Ditolak</span>
                                    @if($slip->alasan_tolak)
                                        <p class="text-[9px] text-red-500 mt-1 italic font-semibold">{{ $slip->alasan_tolak }}</p>
                                    @endif
                                @else
                                    <span class="px-2.5 py-1 bg-gray-50 text-gray-500 rounded-lg text-[10px] font-black uppercase">Menunggu</span>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="px-6 py-14 text-center text-gray-400 font-bold text-xs tracking-wider">Tidak ada data gaji karyawan tetap.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Panel: Karyawan Kontrak -->
    <div id="panel-gaji-kontrak" class="overflow-x-auto hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/30 border-b border-gray-100 uppercase">
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Karyawan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Jabatan &amp; Mitra</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] tracking-tight">Gaji Pokok</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Tunjangan Pangan</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">BPJS TK</th>
                    <th class="px-4 py-4 text-[10px] font-black text-[#1E3A5F] text-center">BPJS Kes</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Total Tunjangan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] text-center">Potongan</th>
                    <th class="px-5 py-4 text-[10px] font-black text-[#1E3A5F] text-right">Gaji Bersih</th>
                    <th class="px-6 py-4 text-[10px] font-black text-[#1E3A5F] text-center w-[200px]">Persetujuan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($slipKontrak as $slip)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-black text-xs shadow-sm shrink-0">
                                {{ strtoupper(substr($slip->karyawan?->nama ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-black text-[#1E3A5F] group-hover:text-blue-600 transition-colors whitespace-nowrap">
                                        {{ $slip->karyawan?->nama ?? 'Karyawan Tidak Ditemukan' }}
                                    </p>
                                    @if($slip->status === 'direvisi')
                                        <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 text-[8px] font-black rounded-md border border-emerald-200 uppercase tracking-wider shrink-0">Sudah Direvisi</span>
                                    @elseif($slip->status === 'ditolak')
                                        <span class="px-1.5 py-0.5 bg-red-100 text-red-800 text-[8px] font-black rounded-md border border-red-200 uppercase tracking-wider shrink-0">Ditolak</span>
                                    @endif
                                </div>
                                <p class="text-[10px] font-bold text-gray-400 tracking-tighter mt-0.5">{{ $slip->karyawan?->nip ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-xs font-bold text-[#1E3A5F]">{{ $slip->karyawan?->jabatan ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">
                            {{ $slip->karyawan?->isTetap() ? 'PT. Citra Bangun Nagari (Pusat)' : ($slip->karyawan?->penempatanAktif?->mitra?->nama_mitra ?? '-') }}
                        </p>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-xs font-bold text-gray-700">Rp {{ number_format($slip->getNominal('Gaji Pokok'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        @php
                            $isKontrakUmum = in_array($slip->karyawan?->jabatan, ['CS', 'CS ATM', 'Ekspedisi']) || $slip->karyawan?->divisi === 'umum';
                            $pangan = $slip->getNominal('Tunjangan Pangan');
                        @endphp
                        @if($isKontrakUmum)
                            <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($pangan, 0, ',', '.') }}</p>
                        @else
                            <p class="text-xs font-bold text-gray-400">-</p>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($slip->getNominal('Tunjangan Jamsostek'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-4 text-center whitespace-nowrap">
                        <p class="text-xs font-bold text-emerald-700">Rp {{ number_format($slip->getNominal('Tunjangan Askes'), 0, ',', '.') }}</p>
                    </td>
                    <td class="px-5 py-4 text-center whitespace-nowrap">
                        @php $totalTunjangan = $slip->totalPendapatan() - $slip->getNominal('Gaji Pokok'); @endphp
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black">
                            + Rp {{ number_format($totalTunjangan, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center whitespace-nowrap">
                        <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-[10px] font-black">
                            - Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right whitespace-nowrap">
                        <p class="text-xs font-black text-[#1E3A5F]">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($selectedPeriode && $selectedPeriode->isProses())
                            <div class="flex flex-col gap-2" x-data="{ keputusan: 'setuju', alasan: '', isSaved: false }">
                                <div class="flex items-center justify-center gap-3">
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="slips[{{ $slip->id }}][status]" value="setuju" checked 
                                               @change="keputusan = 'setuju'; isSaved = false; alasan = ''"
                                               class="text-emerald-600 focus:ring-emerald-500 border-gray-300 w-4 h-4">
                                        <span class="text-xs font-black text-emerald-600">Setuju</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="slips[{{ $slip->id }}][status]" value="tolak" 
                                               @change="keputusan = 'tolak'"
                                               class="text-red-600 focus:ring-red-500 border-gray-300 w-4 h-4">
                                        <span class="text-xs font-black text-red-600">Tolak</span>
                                    </label>
                                </div>
                                
                                <input type="hidden" name="slips[{{ $slip->id }}][alasan]" :value="alasan">

                                <div x-show="keputusan === 'tolak' && !isSaved" x-transition class="mt-1 flex flex-col gap-1.5">
                                    <textarea x-model="alasan" placeholder="Tulis alasan penolakan..." rows="2"
                                              class="w-full p-2.5 border border-red-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none"></textarea>
                                    <button type="button" @click="if(alasan.trim() !== '') isSaved = true"
                                            class="self-end px-3 py-1 bg-[#1E3A5F] hover:bg-blue-900 text-white rounded-lg text-[10px] font-black transition-all">
                                        OK
                                    </button>
                                </div>

                                <div x-show="keputusan === 'tolak' && isSaved" x-transition class="mt-1 text-left p-2.5 bg-red-50 border border-red-100 rounded-xl max-w-[200px]">
                                    <p class="text-[10px] text-red-700 font-bold italic leading-relaxed">Catatan: "<span x-text="alasan"></span>"</p>
                                    <button type="button" @click="isSaved = false" class="text-[9px] text-blue-600 underline font-bold mt-1 block hover:text-blue-800">
                                        Ubah Catatan
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center whitespace-nowrap">
                                @if($slip->isDiterbitkan() || $slip->status === 'disetujui')
                                    <span class="px-2.5 py-1 bg-green-50 text-green-700 rounded-lg text-[10px] font-black uppercase">Disetujui</span>
                                @elseif($slip->status === 'ditolak')
                                    <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase">Ditolak</span>
                                    @if($slip->alasan_tolak)
                                        <p class="text-[9px] text-red-500 mt-1 italic font-semibold">{{ $slip->alasan_tolak }}</p>
                                    @endif
                                @else
                                    <span class="px-2.5 py-1 bg-gray-50 text-gray-500 rounded-lg text-[10px] font-black uppercase">Menunggu</span>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-6 py-14 text-center text-gray-400 font-bold text-xs tracking-wider">Tidak ada data gaji karyawan kontrak.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
