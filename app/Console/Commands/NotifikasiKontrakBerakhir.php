<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class NotifikasiKontrakBerakhir extends Command
{
    /**
     * Signature command ini.
     */
    protected $signature = 'kontrak:notifikasi-berakhir
                            {--dry-run : Jalankan tanpa menyimpan notifikasi (preview saja)}';

    /**
     * Deskripsi command.
     */
    protected $description = 'Kirim notifikasi ke admin untuk karyawan kontrak yang kontraknya akan segera berakhir atau sudah berakhir.';

    /**
     * Threshold hari sebelum berakhir yang dikirim notifikasi.
     */
    private array $thresholds = [30, 14, 7, 1];

    public function handle(): int
    {
        $today   = Carbon::today();
        $isDry   = $this->option('dry-run');

        // Ambil semua admin
        $admins = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))
            ->where('is_active', true)
            ->get();

        if ($admins->isEmpty()) {
            $this->warn('Tidak ada akun admin aktif untuk menerima notifikasi.');
            return 0;
        }

        // Ambil semua karyawan kontrak aktif yang punya tanggal berakhir
        $karyawanKontrak = User::whereHas('role', fn($q) => $q->where('slug', 'karyawan_kontrak'))
            ->whereNotNull('tanggal_berakhir_kontrak')
            ->get();

        $totalDikirim = 0;

        foreach ($karyawanKontrak as $karyawan) {
            $berakhir  = Carbon::parse($karyawan->tanggal_berakhir_kontrak);
            $sisaHari  = $today->diffInDays($berakhir, false); // negatif jika sudah lewat

            // 1. Sudah berakhir — notif sekali saja (dan karyawan masih aktif)
            if ($sisaHari < 0 && $karyawan->is_active) {
                $judul   = "⚠️ Kontrak {$karyawan->nama} Telah Berakhir";
                $pesan   = "Kontrak karyawan **{$karyawan->nama}** (NIP: {$karyawan->nip}) "
                    . "telah berakhir pada " . $berakhir->translatedFormat('d F Y') . ". "
                    . "Segera tindak lanjuti perpanjangan atau penonaktifan akun.";
                $type    = 'warning';
                $link    = '/admin/karyawan/' . $karyawan->id;

                $this->sendToAdmins($admins, $karyawan->id, $judul, $pesan, $type, $link, $isDry);
                $totalDikirim++;
                continue;
            }

            // 2. Akan berakhir — sesuai threshold
            if (in_array((int) $sisaHari, $this->thresholds)) {
                $sisaLabel = $sisaHari === 1 ? 'besok' : "dalam {$sisaHari} hari";
                $judul     = "🔔 Kontrak {$karyawan->nama} Berakhir " . ucfirst($sisaLabel);
                $pesan     = "Kontrak karyawan **{$karyawan->nama}** (NIP: {$karyawan->nip}) "
                    . "akan berakhir pada " . $berakhir->translatedFormat('d F Y')
                    . " ({$sisaLabel}). Segera evaluasi perpanjangan kontrak.";
                $type      = $sisaHari <= 7 ? 'warning' : 'info';
                $link      = '/admin/karyawan/' . $karyawan->id;

                $this->sendToAdmins($admins, $karyawan->id, $judul, $pesan, $type, $link, $isDry);
                $totalDikirim++;
            }
        }

        $mode = $isDry ? '[DRY-RUN] ' : '';
        $this->info("{$mode}Selesai. Total {$totalDikirim} notifikasi dikirim ke " . $admins->count() . " admin.");

        return 0;
    }

    /**
     * Kirim notifikasi ke semua admin — cegah duplikat dalam hari yang sama.
     */
    private function sendToAdmins($admins, string $karyawanId, string $title, string $message, string $type, string $link, bool $isDry): void
    {
        foreach ($admins as $admin) {
            // Cegah duplikat: cek apakah notif dengan judul + karyawan ini sudah ada hari ini
            $alreadySent = Notification::where('user_id', $admin->id)
                ->where('title', $title)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadySent) {
                $this->line("  [SKIP] Admin {$admin->nama} sudah menerima: {$title}");
                continue;
            }

            if ($isDry) {
                $this->line("  [DRY] Akan kirim ke [{$admin->nama}]: {$title}");
                continue;
            }

            Notification::create([
                'user_id' => $admin->id,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
                'is_read' => false,
                'link'    => $link,
            ]);

            $this->line("  ✓ Notifikasi dikirim ke [{$admin->nama}]: {$title}");
        }
    }
}
