@php
    $gajiPokok = $user->komponenGaji ? (float) $user->komponenGaji->gaji_pokok : 0.0;
    $upahPerJam = floor($gajiPokok / 173);
    $jamLembur = (float) $lembur->total_jam;
    $isHoliday = \App\Helpers\AttendanceHelper::isHolidayOrWeekend($lembur->tanggal);

    $kerjaJam1 = 0;
    $kerjaJamSeterusnya = 0;
    $libur8 = 0;
    $libur9 = 0;
    $libur10 = 0;

    if ($isHoliday) {
        if ($jamLembur <= 8) {
            $libur8 = round($jamLembur * 2.0 * $upahPerJam);
        } elseif ($jamLembur == 9) {
            $libur9 = round($jamLembur * 5.0 * $upahPerJam);
        } else {
            $libur10 = round($jamLembur * 9.0 * $upahPerJam);
        }
        $uangMakan = 40000;
        $hariLembur = 1;
        $jumlahUangMakan = 40000;
    } else {
        $jamPertama = min(1.0, $jamLembur);
        $jamSisa = max(0.0, $jamLembur - 1.0);
        $kerjaJam1 = round($jamPertama * 1.5 * $upahPerJam);
        $kerjaJamSeterusnya = round($jamSisa * 2.0 * $upahPerJam);
        $uangMakan = 0;
        $hariLembur = 1;
        $jumlahUangMakan = 0;
    }

    $jumlahLembur = $kerjaJam1 + $kerjaJamSeterusnya + $libur8 + $libur9 + $libur10;
    $total = $jumlahLembur + $jumlahUangMakan;

    // Helper untuk terbilang
    if (!function_exists('penyebut')) {
        function penyebut($nilai) {
            $nilai = abs($nilai);
            $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " " . $huruf[$nilai];
            } else if ($nilai < 20) {
                $temp = penyebut($nilai - 10). " belas";
            } else if ($nilai < 100) {
                $temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
            } else if ($nilai < 200) {
                $temp = " seratus" . penyebut($nilai - 100);
            } else if ($nilai < 1000) {
                $temp = penyebut($nilai/100)." ratus". penyebut($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = " seribu" . penyebut($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = penyebut($nilai/1000)." ribu". penyebut($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = penyebut($nilai/1000000)." juta". penyebut($nilai % 1000000);
            }
            return $temp;
        }
    }

    if (!function_exists('terbilang')) {
        function terbilang($nilai) {
            if($nilai<0) {
                $hasil = "minus ". trim(penyebut($nilai));
            } else {
                $hasil = trim(penyebut($nilai));
            }     
            return ucwords($hasil) . " Rupiah";
        }
    }

    $admUmum = \App\Models\User::where('jabatan', 'Staff Administrasi & Umum')
                                ->where('nama', '!=', 'Administrator Utama')
                                ->first();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Perhitungan Uang Lembur - {{ $lembur->id }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 30px 20px;
            background-color: #f3f4f6;
            color: #000;
        }
        .print-container {
            max-width: 1100px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .title-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .title-container h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title-container h3 {
            margin: 5px 0 0 0;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title-container h4 {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: normal;
        }
        table.excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
        }
        table.excel-table th, table.excel-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
        }
        table.excel-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.excel-table td {
            height: 28px;
        }
        span.formula {
            font-size: 9px;
            font-style: italic;
            font-weight: normal;
            display: block;
            margin-top: 2px;
            text-transform: none;
        }
        .terbilang-section {
            margin-top: 15px;
            font-size: 12px;
            margin-bottom: 30px;
        }
        .signatures-container {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding: 0 50px;
        }
        .signature-block {
            text-align: center;
            width: 250px;
            font-size: 12px;
        }
        .signature-block p {
            margin: 0;
            line-height: 1.4;
        }
        .signature-space {
            height: 80px;
        }
        .signature-block .name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-block .title {
            font-weight: bold;
        }
        .no-print-btn {
            display: block;
            width: 150px;
            margin: 0 auto 20px auto;
            background-color: #1e3a8a;
            color: #ffffff;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .no-print-btn:hover {
            background-color: #172554;
        }
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            body {
                background-color: #fff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print-btn {
                display: none !important;
            }
            table.excel-table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">CETAK SLIP</button>

    <div class="print-container">
        <div class="title-container">
            <h2>PERHITUNGAN PEMBAYARAN UANG LEMBUR</h2>
            <h3>PT. CITRA BANGUN NAGARI</h3>
            <h4>Tanggal {{ \Carbon\Carbon::parse($lembur->tanggal)->translatedFormat('d F Y') }}</h4>
        </div>

        <table class="excel-table">
            <thead>
                <tr>
                    <th rowspan="3" style="width: 4%;">NO</th>
                    <th rowspan="3" style="width: 18%;">NAMA</th>
                    <th rowspan="3" style="width: 10%;">HONOR</th>
                    <th rowspan="3" style="width: 10%;">UPAH/JAM<br>173 JAM<br><span class="formula">b = (a / 173jam)</span></th>
                    <th colspan="2" style="width: 16%;">UPAH LEMBUR HARI KERJA</th>
                    <th colspan="3" style="width: 24%;">UPAH LEMBUR HARI LIBUR</th>
                    <th rowspan="3" style="width: 5%;">HARI<br>LEMBUR<br><span class="formula">i</span></th>
                    <th rowspan="3" style="width: 8%;">Jumlah<br><br><span class="formula">j = (kolom aktif x i)</span></th>
                    <th colspan="3" style="width: 15%;">Uang Makan</th>
                    <th rowspan="3" style="width: 10%;">Total<br><br><span class="formula">m = (j + l)</span></th>
                </tr>
                <tr>
                    <th>Jam Ke - 1</th>
                    <th>Jam ke - 2 &<br>Seterusnya</th>
                    <th>1 s/d 8<br>Jam</th>
                    <th>1 s/d 9<br>Jam</th>
                    <th>Jam ke - 10 dan<br>Seterusnya</th>
                    <th rowspan="2">Uang Makan<br><span class="formula">h</span></th>
                    <th rowspan="2">Hari<br><span class="formula">k</span></th>
                    <th rowspan="2">Jumlah<br><span class="formula">l = (h x k)</span></th>
                </tr>
                <tr>
                    <th><span class="formula">c = (1,5 x b)</span></th>
                    <th><span class="formula">d = (2 x b) x Jam</span></th>
                    <th><span class="formula">e = (2 x b) x Jam</span></th>
                    <th><span class="formula">f = (5 x b)</span></th>
                    <th><span class="formula">g = (9 x b)</span></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td style="font-weight: bold;">{{ $user->nama }}</td>
                    <td style="text-align: right;">{{ number_format($gajiPokok, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($upahPerJam, 0, ',', '.') }}</td>
                    
                    <!-- Hari Kerja -->
                    <td style="text-align: right;">{{ $kerjaJam1 > 0 ? number_format($kerjaJam1, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right;">{{ $kerjaJamSeterusnya > 0 ? number_format($kerjaJamSeterusnya, 0, ',', '.') : '-' }}</td>
                    
                    <!-- Hari Libur -->
                    <td style="text-align: right;">{{ $libur8 > 0 ? number_format($libur8, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right;">{{ $libur9 > 0 ? number_format($libur9, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right;">{{ $libur10 > 0 ? number_format($libur10, 0, ',', '.') : '-' }}</td>
                    
                    <!-- Hari Lembur -->
                    <td style="text-align: center;">{{ $hariLembur }}</td>
                    
                    <!-- Jumlah -->
                    <td style="text-align: right; font-weight: bold;">{{ number_format($jumlahLembur, 0, ',', '.') }}</td>
                    
                    <!-- Uang Makan -->
                    <td style="text-align: right;">{{ $uangMakan > 0 ? number_format($uangMakan, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: center;">{{ $uangMakan > 0 ? $hariLembur : '-' }}</td>
                    <td style="text-align: right;">{{ $jumlahUangMakan > 0 ? number_format($jumlahUangMakan, 0, ',', '.') : '-' }}</td>
                    
                    <!-- Total -->
                    <td style="text-align: right; font-weight: bold; background-color: #fafafa;">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                <!-- Total Row -->
                <tr style="background-color: #f9fafb; font-weight: bold;">
                    <td colspan="10" style="text-align: center; text-transform: uppercase;">TOTAL BIAYA LEMBUR</td>
                    <td style="text-align: right;">{{ number_format($jumlahLembur, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                    <td style="text-align: right;">{{ number_format($jumlahUangMakan, 0, ',', '.') }}</td>
                    <td style="text-align: right; background-color: #f3f4f6;">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="terbilang-section">
            <strong>Terbilang : </strong> <span style="font-style: italic;">{{ terbilang($total) }}</span>
        </div>

        <div class="signatures-container">
            <div class="signature-block">
                <p>Disetujui Oleh,</p>
                <div class="signature-space"></div>
                <p class="name">{{ $lembur->approver?->nama ?? 'H. IRMED, SE., MM' }}</p>
                <p class="title">{{ $lembur->approver?->jabatan ?? 'Direktur' }}</p>
            </div>
            <div class="signature-block">
                <p>Padang, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                <p>Yang Membayarkan,</p>
                <div class="signature-space"></div>
                <p class="name">{{ $admUmum->nama ?? 'AMI PUTRI ANSYAH' }}</p>
                <p class="title">Adm & Umum</p>
            </div>
        </div>
    </div>

</body>
</html>
