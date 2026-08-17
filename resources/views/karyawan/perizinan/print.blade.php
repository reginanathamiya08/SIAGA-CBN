<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Permohonan Cuti — {{ $perizinan->karyawan->nama }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.0cm 2.0cm 1.0cm 2.0cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }
        .meta-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            vertical-align: top;
            padding: 0;
        }
        .meta-left {
            width: 50%;
            text-align: left;
        }
        .meta-right {
            width: 50%;
            text-align: right;
        }
        .content-body {
            text-align: justify;
            margin-bottom: 15px;
        }
        .indent-paragraph {
            text-indent: 40px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .signatures-container {
            width: 100%;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0;
        }
        .digital-sig {
            border: 1px dashed #4b5563;
            background-color: #f9fafb;
            border-radius: 4px;
            padding: 4px;
            text-align: center;
            font-size: 7.5pt;
            color: #1f2937;
            font-family: monospace;
            width: 190px;
            margin: 5px auto;
            line-height: 1.2;
        }
        .digital-sig-success {
            border: 1px dashed #22c55e;
            background-color: #f0fdf4;
            color: #15803d;
        }
        .digital-sig-info {
            border: 1px dashed #3b82f6;
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .digital-sig-danger {
            border: 1px dashed #ef4444;
            background-color: #fef2f2;
            color: #b91c1c;
        }
        .double-divider {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin: 10px 0;
        }
        .sdm-section-title {
            font-weight: bold;
            text-decoration: underline;
            font-size: 11pt;
            margin-top: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .section-subtitle {
            font-size: 10pt;
            font-weight: normal;
            margin-top: -8px;
            margin-bottom: 10px;
        }
        .subsection-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .pertimbangan-table {
            width: 100%;
            margin-left: 15px;
            border-collapse: collapse;
        }
        .pertimbangan-table td {
            padding: 1px 0;
            font-size: 11pt;
        }
        .usul-saran-body {
            margin-left: 15px;
            font-size: 11pt;
            text-align: justify;
        }
        .usul-saran-list {
            margin-left: 15px;
            margin-top: 5px;
        }
        .usul-saran-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .usul-saran-list td {
            vertical-align: top;
            padding: 2px 0;
        }
        /* Cetak Otomatis */
        @media print {
            .no-print {
                display: none;
            }
        }
        .print-btn-container {
            max-width: 800px;
            margin: 10px auto;
            text-align: right;
            padding-right: 10px;
        }
        .print-btn {
            background-color: #1E3A5F;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-btn:hover {
            background-color: #112238;
        }
        .page-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 5px;
        }
    </style>
</head>
<body>

    <div class="print-btn-container no-print">
        <button onclick="window.print()" class="print-btn">🖨️ Cetak Dokumen</button>
    </div>

    @php
        function terbilang($angka) {
            $words = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", 
                      "sebelas", "dua belas", "tiga belas", "empat belas", "lima belas", "enam belas", "tujuh belas", 
                      "delapan belas", "sembilan belas", "dua puluh", "dua puluh satu", "dua puluh dua", "dua puluh tiga", 
                      "dua puluh empat", "dua puluh lima", "dua puluh enam", "dua puluh tujuh", "dua puluh delapan", 
                      "dua puluh sembilan", "tiga puluh"];
            return $words[$angka] ?? '';
        }
    @endphp

    <div class="page-container">
        <!-- Meta Section (Header) -->
        <div class="meta-section">
            <table class="meta-table">
                <tr>
                    <td class="meta-left">
                        <strong>Perihal : Permohonan cuti</strong>
                    </td>
                    <td class="meta-right">
                        Padang, {{ $perizinan->created_at->translatedFormat('d F Y') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Recipient -->
        <div class="content-body" style="margin-top: 15px; margin-bottom: 15px;">
            Kepada Yth,<br>
            <strong>Bapak Direksi PT. Citra Bangun Nagari</strong><br>
            Di –<br>
            <span style="margin-left: 30px;">Tempat</span>
        </div>

        <!-- Body Content -->
        <div class="content-body">
            Dengan hormat,<br>
            <div class="indent-paragraph">
                Dengan ini saya mengajukan permohonan kepada Bapak kiranya saya diperkenankan untuk menjalani cuti selama <strong>{{ $perizinan->jumlah_hari }} ({{ terbilang($perizinan->jumlah_hari) }})</strong> hari kerja mulai tanggal <strong>{{ $perizinan->tanggal_mulai->translatedFormat('d F Y') }}</strong> s/d <strong>{{ $perizinan->tanggal_selesai->translatedFormat('d F Y') }}</strong>. Adapun cuti tersebut akan saya pergunakan untuk <u><strong>{{ $perizinan->keterangan ?? '..................................................' }}</strong></u>.
            </div>
            <div class="indent-paragraph" style="margin-top: 5px;">
                Demikian permohonan ini saya ajukan dengan harapan kiranya Bapak berkenan mengabulkannya dan untuk itu saya ucapkan terimakasih.
            </div>
        </div>

        <!-- Signatures Section -->
        <div class="signatures-container">
            <table class="signature-table">
                <tr>
                    <td>
                        @if ($perizinan->karyawan->isKaryawanKontrak())
                            <div>Diketahui Oleh,</div>
                            @php
                                $namaMitra = $perizinan->karyawan->penempatanAktif?->mitra?->nama_mitra;
                            @endphp
                            <div style="font-weight: bold; margin-bottom: 5px;">Pimpinan {{ $namaMitra ? $namaMitra : 'Mitra Penempatan' }}</div>
                            <div style="height: 55px;"></div>
                            <div style="margin-top: 5px; font-weight: bold;">
                                ( ............................................... )
                            </div>
                            <div style="font-size: 8pt; font-style: italic;">Tanda Tangan & Cap Resmi</div>
                        @else
                            <div>Diketahui Oleh</div>
                            
                            @if ($perizinan->rekan_kerja_id && $perizinan->status_rekan === 'disetujui')
                                <div class="digital-sig digital-sig-success">
                                    <strong>APPROVED VIA SYSTEM</strong><br>
                                    Rekan Kerja: {{ $perizinan->rekanKerja->nama }}<br>
                                    Waktu: {{ $perizinan->rekan_approved_at->format('d/m/Y H:i') }}
                                </div>
                                <div style="margin-top: 5px; font-weight: bold; text-decoration: underline;">
                                    ( {{ $perizinan->rekanKerja->nama }} )
                                </div>
                            @else
                                <div style="height: 55px;"></div>
                                <div style="margin-top: 5px; font-weight: bold;">
                                    ( {{ $perizinan->rekanKerja?->nama ?? '.......................................' }} )
                                </div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <div>Hormat Saya,</div>
                        <div style="font-weight: bold; margin-bottom: 5px;">Pemohon</div>
                        
                        @if ($perizinan->karyawan->isKaryawanKontrak())
                            <div style="height: 55px;"></div>
                            <div style="margin-top: 5px; font-weight: bold; text-decoration: underline;">
                                ( {{ $perizinan->karyawan->nama }} )
                            </div>
                            <div style="font-size: 8pt; font-style: italic;">Karyawan Kontrak</div>
                        @else
                            <div class="digital-sig digital-sig-info">
                                <strong>SUBMITTED VIA SYSTEM</strong><br>
                                Pemohon: {{ $perizinan->karyawan->nama }}<br>
                                Waktu: {{ $perizinan->created_at->format('d/m/Y H:i') }}
                            </div>
                            
                            <div style="margin-top: 5px; font-weight: bold; text-decoration: underline;">
                                ( {{ $perizinan->karyawan->nama }} )
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Divider with note -->
        <div style="font-size: 9pt; font-weight: bold; margin-bottom: 2px;">*) Note :</div>
        <div class="double-divider"></div>

        <!-- SDM & UMUM Section -->
        <div class="sdm-section-title">REKOMENDASI BAGIAN SDM DAN UMUM</div>
        <div class="section-subtitle">(Diisi Oleh Pegawai PT. Citra Bangun Nagari)</div>

        <!-- A. Pertimbangan -->
        <div class="subsection-title">A. PERTIMBANGAN</div>
        <table class="pertimbangan-table">
            <tr>
                <td style="width: 45%;">Hak cuti tahunan Ybs. periode {{ $perizinan->tanggal_mulai->year }}</td>
                <td style="width: 3%; text-align: center;">:</td>
                <td style="width: 52%; font-weight: bold;">{{ $kuotaPerizinan?->kuota_total ?? 12 }} hari kerja</td>
            </tr>
            <tr>
                <td>Telah dilaksanakan</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: bold;">{{ $kuotaPerizinan?->terpakai ?? 0 }} hari kerja</td>
            </tr>
            <tr>
                <td><strong>Sisa cuti periode</strong></td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: bold;">{{ $kuotaPerizinan?->sisa ?? 12 }} hari kerja</td>
            </tr>
        </table>

        <!-- B. Usul dan Saran -->
        <div class="subsection-title">B. USUL DAN SARAN</div>
        <div class="usul-saran-body">
            Sehubungan dengan permohonan cuti tersebut diatas, dengan ini diusulkan sebagai berikut :
            <div class="usul-saran-list">
                <table>
                    <tr>
                        <td style="width: 3%;">3.</td>
                        <td>
                            Cuti Ybs. disetujui untuk dilaksanakan selama <strong>{{ $perizinan->jumlah_hari }}</strong> hari kerja terhitung tgl <strong>{{ $perizinan->tanggal_mulai->translatedFormat('d F Y') }}</strong> s/d <strong>{{ $perizinan->tanggal_selesai->translatedFormat('d F Y') }}</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td>4.</td>
                        <td>
                            Pekerjaan Ybs. dirangkap/digantikan oleh <strong>{{ $perizinan->rekanKerja?->nama ?? '-' }}</strong>.
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- SDM Signature Block (Right aligned) -->
        <div style="width: 100%; margin-top: 10px;">
            <div style="float: right; text-align: center; width: 45%;">
                <div style="font-weight: bold; margin-bottom: 40px;">SDM & Umum</div>
                <div style="font-weight: bold;">( ................................................ )</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- C. Keputusan Direksi -->
        <div class="subsection-title">C. KEPUTUSAN DIREKSI</div>
        <div class="usul-saran-body">
            Berdasarkan hal tersebut diatas, maka permohonan cuti Ybs. pada prinsipnya : 
            @if ($perizinan->status_approval === 'disetujui')
                <strong><u>DISETUJUI / DIKABULKAN</u></strong>
            @elseif ($perizinan->status_approval === 'ditolak')
                <strong><u>DITOLAK / DITANGGUHKAN</u></strong>
            @else
                <strong><u>..................................................</u></strong>
            @endif
        </div>

        <!-- Direksi Signature Block (Right aligned) -->
        <div style="width: 100%; margin-top: 10px;">
            <div style="float: right; text-align: center; width: 45%;">
                <div style="font-weight: bold; margin-bottom: 5px;">PT. CITRA BANGUN NAGARI</div>
                
                @if ($perizinan->status_approval === 'disetujui')
                    <div class="digital-sig digital-sig-success" style="width: 180px; font-size: 7pt; margin: 2px auto;">
                        <strong>APPROVED BY DIREKSI</strong><br>
                        Approver: {{ $perizinan->approver?->nama ?? 'Direksi' }}<br>
                        Waktu: {{ $perizinan->approved_at->format('d/m/Y H:i') }}
                    </div>
                    <div style="font-weight: bold; text-decoration: underline; margin-top: 2px;">
                        ( {{ $perizinan->approver?->nama }} )
                    </div>
                @elseif ($perizinan->status_approval === 'ditolak')
                    <div class="digital-sig digital-sig-danger" style="width: 180px; font-size: 7pt; margin: 2px auto;">
                        <strong>DITOLAK BY DIREKSI</strong><br>
                        Alasan: {{ $perizinan->alasan_tolak ?? '-' }}
                    </div>
                    <div style="font-weight: bold; text-decoration: underline; margin-top: 2px;">
                        ( {{ $perizinan->approver?->nama ?? '................................' }} )
                    </div>
                @else
                    <div style="height: 45px;"></div>
                    <div style="font-weight: bold;">( ................................................ )</div>
                @endif
            </div>
            <div style="clear: both;"></div>
        </div>

    </div>

    <script>
        // Jalankan dialog print saat halaman dimuat
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
