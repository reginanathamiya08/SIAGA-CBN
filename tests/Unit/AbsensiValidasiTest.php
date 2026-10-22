<?php

namespace Tests\Unit;

use App\Http\Controllers\Karyawan\AbsensiController;
use App\Models\Shift;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use ReflectionClass;
use PHPUnit\Framework\Attributes\Test;

class AbsensiValidasiTest extends TestCase
{
    private function callPrivateMethod(object $object, string $method, array $args = [])
    {
        $ref = new ReflectionClass($object);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($object, $args);
    }

    #[Test]
    public function ip_dalam_whitelist_diloloskan()
    {
        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'isIpAllowed', ['182.9.200.55', '182.9.200.*']);
        $this->assertTrue($hasil, 'IP yang cocok prefix whitelist seharusnya diloloskan');
    }

    #[Test]
    public function ip_di_luar_whitelist_ditolak()
    {
        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'isIpAllowed', ['103.10.50.20', '182.9.200.*']);
        $this->assertFalse($hasil, 'IP yang tidak cocok whitelist seharusnya ditolak');
    }

    #[Test]
    public function ip_ipv6_tidak_lagi_lolos_otomatis()
    {
        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'isIpAllowed', ['2001:db8:abcd:1234::1', '182.9.200.*']);
        $this->assertFalse($hasil, 'IP IPv6 yang tidak cocok prefix mitra seharusnya tetap ditolak, bukan diloloskan otomatis');
    }

    #[Test]
    public function ip_kosong_tidak_lagi_bypass_validasi()
    {
        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'isIpAllowed', ['182.9.200.55', '']);
        $this->assertFalse($hasil, 'Konfigurasi IP mitra yang kosong seharusnya ditolak (fail-closed), bukan meloloskan validasi IP');
    }

    #[Test]
    public function shift_overlap_memilih_shift_dengan_jam_mulai_terdekat()
    {
        $shiftPagi = new Shift([
            'nama_shift' => 'Pagi', 'jam_mulai' => '06:00:00',
            'window_start' => '05:00:00', 'window_end' => '18:00:00',
        ]);
        $shiftMalam = new Shift([
            'nama_shift' => 'Malam', 'jam_mulai' => '18:00:00',
            'window_start' => '17:00:00', 'window_end' => '06:00:00',
        ]);
        $shifts = collect([$shiftPagi, $shiftMalam]);

        $kandidat = $shifts->filter(fn($s) => $s->isInWindow('17:30:00'));
        $this->assertCount(2, $kandidat, 'Pada jam 17:30 kedua shift seharusnya sama-sama masuk window (kasus overlap)');

        $nowMinutes = 17 * 60 + 30;
        $terpilih = $kandidat->sortBy(function ($s) use ($nowMinutes) {
            [$h, $m] = explode(':', $s->jam_mulai);
            $selisih = abs($nowMinutes - ((int) $h * 60 + (int) $m));
            return min($selisih, 1440 - $selisih);
        })->first();

        $this->assertEquals('Malam', $terpilih->nama_shift, 'Jam 17:30 seharusnya terdeteksi Shift Malam (lebih dekat ke jam_mulai 18:00), bukan Shift Pagi');
    }

    #[Test]
    public function geolocation_menolak_jika_ip_ditandai_proxy()
    {
        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'success', 'lat' => -0.9492, 'lon' => 100.3543,
                'isp' => 'Suspicious VPN Provider', 'proxy' => true,
            ], 200),
        ]);

        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'cekIpGeolocation', ['103.10.50.20', -0.9492, 100.3543]);

        $this->assertNotNull($hasil, 'IP yang ditandai proxy=true oleh API seharusnya ditolak');
        $this->assertStringContainsString('VPN/proxy', $hasil);
    }

    #[Test]
    public function geolocation_menolak_jika_lokasi_ip_jauh_dari_mitra()
    {
        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'success', 'lat' => -6.2088, 'lon' => 106.8456,
                'isp' => 'ISP Jakarta', 'proxy' => false,
            ], 200),
        ]);

        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'cekIpGeolocation', ['180.250.1.1', -0.9492, 100.3543]);

        $this->assertNotNull($hasil, 'IP yang lokasinya jauh dari mitra (>100km) seharusnya ditolak');
        $this->assertStringContainsString('km dari kantor', $hasil);
    }

    #[Test]
    public function geolocation_meloloskan_jika_ip_dekat_dengan_mitra()
    {
        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'success', 'lat' => -0.9500, 'lon' => 100.3550,
                'isp' => 'ISP Lokal Padang', 'proxy' => false,
            ], 200),
        ]);

        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'cekIpGeolocation', ['182.9.200.55', -0.9492, 100.3543]);

        $this->assertNull($hasil, 'IP yang lokasinya dekat dengan mitra dan bukan proxy seharusnya diloloskan (null = tidak ada error)');
    }

    #[Test]
    public function geolocation_fail_open_ketika_api_gagal_atau_timeout()
    {
        Http::fake([
            'ip-api.com/*' => Http::response([], 500),
        ]);

        $controller = new AbsensiController();
        $hasil = $this->callPrivateMethod($controller, 'cekIpGeolocation', ['182.9.200.55', -0.9492, 100.3543]);

        $this->assertNull($hasil, 'Ketika API geolocation gagal/down, sistem harus fail-open (tidak memblokir absensi) agar tidak mengganggu operasional');
    }
}