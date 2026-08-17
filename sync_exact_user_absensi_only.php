<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$defMakan = (float) App\Models\Configuration::getValue('uang_makan_default', 35000);
$defTransport = (float) App\Models\Configuration::getValue('uang_transport_default', 45000);
$gajiPanganDefault = (float) App\Models\Configuration::getValue('tunjangan_pangan_kontrak_umum', 805000);
$tarifPanganHarian = $gajiPanganDefault / 23;
$jabatanUmumList = ['CS', 'CS ATM', 'Ekspedisi'];

$periodes = App\Models\PeriodeGaji::all();
$karyawanList = App\Models\User::whereHas('role', fn($r) => $r->whereIn('slug', ['karyawan_tetap', 'karyawan_kontrak']))->get();

foreach ($periodes as $p) {
    $tglMulai = $p->tanggal_mulai->toDateString();
    $tglSelesai = $p->tanggal_selesai->toDateString();

    foreach ($karyawanList as $k) {
        // AMBIL DATA ABSENSI MURNI DARI TABEL ABSENSI DATABASE
        $absensi = App\Models\Absensi::where('user_id', $k->id)
            ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
            ->get();

        $hadirCount = $absensi->whereIn('status', ['hadir', 'telat'])->count();
        $telatCount = $absensi->where('is_telat', true)->count();
        $izinCount  = $absensi->whereIn('status', ['izin', 'sakit'])->count();
        $cutiCount  = $absensi->where('status', 'cuti')->count();
        $alfaCount  = $absensi->where('status', 'alfa')->count();

        $isTetap = $k->isKaryawanTetap();
        $uangMakan = $isTetap ? $defMakan * $hadirCount : 0.0;
        $uangTransport = $isTetap ? $defTransport * $hadirCount : 0.0;

        $isKontrakUmum = $k->isKaryawanKontrak() && (in_array($k->jabatan, $jabatanUmumList) || $k->divisi === 'umum');
        $gajiPangan = $isKontrakUmum ? round($tarifPanganHarian * $hadirCount) : 0.0;

        $slip = App\Models\SlipGajiPeriode::where('user_id', $k->id)->where('periode_id', $p->id)->first();
        if ($slip) {
            if ($isTetap) {
                App\Models\DetailGajiKomponen::where('slip_gaji_periode_id', $slip->id)->where('komponen_gaji_id', 'MKG-00002')->delete();
            }

            if ($uangMakan > 0) {
                App\Models\DetailGajiKomponen::updateOrCreate([
                    'slip_gaji_periode_id' => $slip->id,
                    'komponen_gaji_id'     => 'MKG-00003',
                ], [
                    'user_id'       => $k->id,
                    'nama_komponen' => 'Uang Makan',
                    'tipe'          => 'pendapatan',
                    'nominal'       => $uangMakan,
                ]);
            } else {
                App\Models\DetailGajiKomponen::where('slip_gaji_periode_id', $slip->id)->where('komponen_gaji_id', 'MKG-00003')->delete();
            }

            if ($uangTransport > 0) {
                App\Models\DetailGajiKomponen::updateOrCreate([
                    'slip_gaji_periode_id' => $slip->id,
                    'komponen_gaji_id'     => 'MKG-00004',
                ], [
                    'user_id'       => $k->id,
                    'nama_komponen' => 'Uang Transport',
                    'tipe'          => 'pendapatan',
                    'nominal'       => $uangTransport,
                ]);
            } else {
                App\Models\DetailGajiKomponen::where('slip_gaji_periode_id', $slip->id)->where('komponen_gaji_id', 'MKG-00004')->delete();
            }

            if ($gajiPangan > 0) {
                App\Models\DetailGajiKomponen::updateOrCreate([
                    'slip_gaji_periode_id' => $slip->id,
                    'komponen_gaji_id'     => 'MKG-00002',
                ], [
                    'user_id'       => $k->id,
                    'nama_komponen' => 'Tunjangan Pangan',
                    'tipe'          => 'pendapatan',
                    'nominal'       => $gajiPangan,
                ]);
            } else if ($isKontrakUmum && $gajiPangan == 0) {
                App\Models\DetailGajiKomponen::where('slip_gaji_periode_id', $slip->id)->where('komponen_gaji_id', 'MKG-00002')->delete();
            }

            $sReload = $slip->fresh(['details']);
            $potongan = (float) $sReload->details->where('tipe', 'potongan')->sum('nominal');
            $pendapatan = (float) $sReload->details->where('tipe', 'pendapatan')->sum('nominal');

            $sReload->update([
                'total_hadir'    => $hadirCount,
                'total_telat'    => $telatCount,
                'total_izin'     => $izinCount,
                'total_cuti'     => $cutiCount,
                'total_alfa'     => $alfaCount,
                'total_potongan' => $potongan,
                'gaji_bersih'    => max(0.0, $pendapatan - $potongan),
            ]);
        }
    }
}
echo "SYNCED ALL SLIPS STRICTLY FROM RAW ABSENSI DB ROWS\n";
