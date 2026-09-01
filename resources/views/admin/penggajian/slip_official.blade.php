<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $slipGaji->karyawan->nama }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 13px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 790px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            position: relative;
        }
        header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 200px;
        }
        .logo-section img {
            width: 80px;
            height: auto;
        }
        .logo-section .company-name {
            font-weight: bold;
            font-size: 10px;
            margin-top: 5px;
            text-align: center;
        }
        .address-section {
            text-align: right;
            font-size: 10px;
            line-height: 1.2;
        }
        .address-section b {
            font-size: 14px;
        }
        .title-section {
            text-align: left;
            margin-bottom: 20px;
        }
        .title-section h2 {
            margin: 0;
            text-decoration: underline;
            font-size: 16px;
        }
        .title-section p {
            margin: 5px 0;
            font-weight: bold;
        }
        .rahasia {
            position: absolute;
            top: 150px;
            right: 50px;
            border: 2px solid #000;
            padding: 5px 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .employee-info {
            border: 2px solid #000;
            width: fit-content;
            padding: 5px 20px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }
        .main-table td {
            vertical-align: top;
            padding: 0 10px;
        }
        .section-box {
            border: 2px solid #000;
            margin-bottom: 10px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            font-weight: bold;
            padding: 5px;
        }
        .row-item {
            display: flex;
            align-items: center;
            padding: 2px 5px;
        }
        .row-item .label {
            text-transform: uppercase;
            width: 175px;
            display: inline-block;
        }
        .row-item .colon {
            width: 20px;
            text-align: center;
        }
        .row-item .value {
            flex-grow: 1;
            text-align: right;
        }
        .total-row {
            border-top: 2px solid #000;
            font-weight: bold;
            padding: 5px;
            display: flex;
            justify-content: space-between;
        }
        .footer-signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 300px;
        }
        .signature-space {
            height: 80px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .signature-title {
            font-weight: bold;
            font-size: 10px;
        }
        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1E3A5F;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 15mm;
            }
            .no-print-btn { display: none !important; }
            body { 
                padding: 0 !important; 
                margin: 0 !important; 
            }
            .container { 
                border: none !important; 
                padding: 10px !important; 
                width: 100% !important; 
                max-width: 100% !important;
                margin: 0 auto !important;
            }
            .rahasia {
                right: 20px !important;
                top: 130px !important;
            }
        }
    </style>
</head>
<body>

<button class="no-print-btn" onclick="window.print()">CETAK SLIP</button>

<div class="container">
    <header>
        <div class="logo-section">
            <img src="{{ asset('image/logo_cbn.jpg') }}" alt="Logo">
            <div class="company-name">PT. CITRA BANGUN NAGARI</div>
        </div>
        <div class="address-section">
            <b>PT. CITRA BANGUN NAGARI</b><br>
            Jl. Pemuda No. 23 F Padang<br>
            Sumatera Barat<br>
            Telp. (0751) 37319<br>
            Fax (0751) 840835<br>
            E-mail : citra_bangunnagari@yahoo.com
        </div>
    </header>

    <div class="rahasia">RAHASIA</div>

    <div class="title-section">
        <h2>DAFTAR GAJI PEGAWAI</h2>
        <p>PT. CITRA BANGUN NAGARI</p>
        <p>TANGGAL : {{ strtoupper($slipGaji->periodeGaji->tanggal_selesai->translatedFormat('d F Y')) }}</p>
    </div>

    <div class="employee-info">
        {{ strtoupper($slipGaji->karyawan->nama) }}<br>
        {{ strtoupper($slipGaji->karyawan->jabatan ?? 'STAFF') }}
    </div>

    <div style="display: flex; gap: 20px; align-items: flex-start;">
        <!-- KOLOM KIRI -->
        <div style="flex: 1;">
            @php
                $karyawan = $slipGaji->karyawan;
                $jabatanUmumList = ['CS', 'CS ATM', 'Ekspedisi'];
                $isKontrakUmum = $karyawan->isKaryawanKontrak() && (
                    in_array($karyawan->jabatan, $jabatanUmumList) || $karyawan->divisi === 'umum'
                );
                $nominalPangan = $isKontrakUmum ? $slipGaji->getNominal('Tunjangan Pangan') : 0;
            @endphp
            {{-- GAJI --}}
            <div class="section-box">
                <div class="section-header">
                    <span>KOMPONEN</span>
                    <span>JUMLAH (Rp)</span>
                </div>
                <div style="padding: 5px;">
                    <b>GAJI :</b>
                    <div class="row-item">
                        <span class="label">GAJI POKOK</span>
                        <span class="colon">:</span>
                        <span class="value">{{ number_format($slipGaji->getNominal('Gaji Pokok'), 0, ',', '.') }}</span>
                    </div>
                    <div class="row-item">
                        <span class="label">PANGAN</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $nominalPangan > 0 ? number_format($nominalPangan, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div class="total-row">
                        <span>JUMLAH</span>
                        <span>{{ number_format($slipGaji->getNominal('Gaji Pokok') + $nominalPangan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
 
            {{-- TUNJANGAN --}}
            <div class="section-box">
                <div class="section-header">
                    <span>KOMPONEN</span>
                    <span>JUMLAH (Rp)</span>
                </div>
                <div style="padding: 5px;">
                    <b>TUNJANGAN LAINNYA :</b>
                    @php
                        $isTetapKaryawan = $slipGaji->karyawan->isKaryawanTetap();
                        $uangMakanVal = $isTetapKaryawan ? $slipGaji->getNominal('Uang Makan') : 0;
                        $uangTransportVal = $isTetapKaryawan ? $slipGaji->getNominal('Uang Transport') : 0;
                    @endphp
                    @php
                        $gajiPokokVal     = $slipGaji->getNominal('Gaji Pokok');

                        $rawTunjJamsostek = str_replace(',', '.', (string) \App\Models\Configuration::getValue('persen_tunjangan_jamsostek', '6.24'));
                        $rawTunjAskes     = str_replace(',', '.', (string) \App\Models\Configuration::getValue('persen_tunjangan_askes', '4.00'));
                        $rawPotKes        = str_replace(',', '.', (string) \App\Models\Configuration::getValue('persen_potongan_bpjs_kes', '5.00'));
                        $rawPotTk         = str_replace(',', '.', (string) \App\Models\Configuration::getValue('persen_potongan_bpjs_tk', '9.24'));

                        $pctTunjJamsostek = (float) $rawTunjJamsostek;
                        $pctTunjAskes     = (float) $rawTunjAskes;
                        $pctPotKes        = (float) $rawPotKes;
                        $pctPotTk         = (float) $rawPotTk;

                        $tunjJamsostekVal = ($gajiPokokVal > 0) ? round($gajiPokokVal * ($pctTunjJamsostek / 100)) : $slipGaji->getNominal('Tunjangan Jamsostek');
                        $tunjAskesVal     = ($gajiPokokVal > 0) ? round($gajiPokokVal * ($pctTunjAskes / 100))     : $slipGaji->getNominal('Tunjangan Askes');
                        $potKesVal        = ($gajiPokokVal > 0) ? round($gajiPokokVal * ($pctPotKes / 100))        : $slipGaji->getNominal('Potongan BPJS Kesehatan');
                        $potTkVal         = ($gajiPokokVal > 0) ? round($gajiPokokVal * ($pctPotTk / 100))         : $slipGaji->getNominal('Potongan BPJS Ketenagakerjaan');

                        $lblPctTunjJamsostek = str_replace('.', ',', (string) $pctTunjJamsostek);
                        $lblPctTunjAskes     = str_replace('.', ',', (string) $pctTunjAskes);
                        $lblPctPotKes        = str_replace('.', ',', (string) $pctPotKes);
                        $lblPctPotTk         = str_replace('.', ',', (string) $pctPotTk);
                    @endphp
                    <div class="row-item">
                        <span class="label">UANG MAKAN</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $uangMakanVal > 0 ? number_format($uangMakanVal, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div class="row-item">
                        <span class="label">TRANSPORT</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $uangTransportVal > 0 ? number_format($uangTransportVal, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div class="row-item">
                        <span class="label">JAMSOSTEK ({{ $lblPctTunjJamsostek }}%)</span>
                        <span class="colon">:</span>
                        <span class="value">{{ number_format($tunjJamsostekVal, 0, ',', '.') }}</span>
                    </div>
                    <div class="row-item">
                        <span class="label">ASKES ({{ $lblPctTunjAskes }}%)</span>
                        <span class="colon">:</span>
                        <span class="value">{{ number_format($tunjAskesVal, 0, ',', '.') }}</span>
                    </div>
                    <div class="row-item">
                        <span class="label">PPh 21</span>
                        <span class="colon">:</span>
                        <span class="value">-</span>
                    </div>
                    @php
                        $pendapatanLainnya = $slipGaji->getPendapatanLainnya();
                        $isSatpam = str_contains(strtolower($slipGaji->karyawan->jabatan ?? ''), 'satpam');
                        if ($isSatpam && $pendapatanLainnya == 0) {
                            $pendapatanLainnya = 100000;
                        }
                        $labelPendapatanLainnya = $isSatpam ? 'PENDAPATAN LAINNYA' : 'PENDAPATAN LAINNYA';
                        $extraSatpamAdd = ($isSatpam && $slipGaji->getPendapatanLainnya() == 0) ? 100000 : 0;
                        $totalTunjangan = ($slipGaji->totalPendapatan() - ($slipGaji->getNominal('Gaji Pokok') + $slipGaji->getNominal('Tunjangan Pangan'))) + $extraSatpamAdd;
                        $gajiKotor = $slipGaji->totalPendapatan() + $extraSatpamAdd;
                    @endphp
                    <div class="row-item">
                        <span class="label">{{ $labelPendapatanLainnya }}</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $pendapatanLainnya > 0 ? number_format($pendapatanLainnya, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div class="total-row">
                        <span>JUMLAH TUNJANGAN</span>
                        <span>{{ number_format($totalTunjangan, 0, ',', '.') }}</span>
                    </div>
                    <div class="total-row" style="background: #eee;">
                        <span>GAJI KOTOR</span>
                        <span>{{ number_format($gajiKotor, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- KOLOM KANAN -->
        <div style="flex: 1;">
            {{-- POTONGAN --}}
            <div class="section-box">
                <div class="section-header">
                    <span>KOMPONEN</span>
                    <span>JUMLAH (Rp)</span>
                </div>
                <div style="padding: 5px;">
                    <b>POTONGAN - POTONGAN :</b>
                    <div class="row-item">
                        <span class="label">PPh 21</span>
                        <span class="colon">:</span>
                        <span class="value">-</span>
                    </div>
                    <div class="row-item">
                        <span class="label">JAMSOSTEK ({{ $lblPctPotTk }}%)</span>
                        <span class="colon">:</span>
                        <span class="value">{{ number_format($potTkVal, 0, ',', '.') }}</span>
                    </div>
                    <div class="row-item">
                        <span class="label">ASKES ({{ $lblPctPotKes }}%)</span>
                        <span class="colon">:</span>
                        <span class="value">{{ number_format($potKesVal, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $nomPinjaman = $slipGaji->getNominal('Potongan Pinjaman');
                    @endphp
                    <div class="row-item">
                        <span class="label">PINJAMAN LAINNYA</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $nomPinjaman > 0 ? number_format($nomPinjaman, 0, ',', '.') : '-' }}</span>
                    </div>
                    <div class="total-row">
                        <span>JUMLAH POTONGAN</span>
                        <span>{{ number_format($slipGaji->total_potongan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- GAJI BERSIH --}}
            <div style="margin-top: 20px; border: 4px double #000; padding: 15px;">
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 18px;">
                    <span>GAJI BERSIH</span>
                    <span>{{ number_format($gajiKotor - $slipGaji->total_potongan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-signatures">
        <div class="signature-box">
            Diketahui Oleh<br>
            PT. CITRA BANGUN NAGARI
            <div class="signature-space"></div>
            <div class="signature-name">{{ $admUmum->nama ?? 'AMI PUTRI ANSYAH' }}</div>
            <div class="signature-title">ADM & UMUM</div>
        </div>
        <div class="signature-box">
            Diterima Oleh<br>
            PEGAWAI PT. CITRA BANGUN NAGARI
            <div class="signature-space"></div>
            <div class="signature-name">{{ $slipGaji->karyawan->nama }}</div>
        </div>
    </div>
</div>

</body>
</html>
