<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiApproval extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $namaKaryawan   Nama karyawan yang mengajukan
     * @param string $jenisAjuan     'Perizinan' | 'Lembur' | 'Dinas Luar Kota'
     * @param string $statusApproval 'disetujui' | 'ditolak'
     * @param string $keterangan     Detail izin (misal: "Cuti Tahunan, 2 hari")
     * @param string|null $alasanTolak  Alasan penolakan jika ditolak
     */
    public function __construct(
        public string  $namaKaryawan,
        public string  $jenisAjuan,
        public string  $statusApproval,
        public string  $keterangan,
        public ?string $alasanTolak = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjek = $this->statusApproval === 'disetujui'
            ? "✅ Pengajuan {$this->jenisAjuan} Anda Disetujui"
            : "❌ Pengajuan {$this->jenisAjuan} Anda Ditolak";

        return new Envelope(subject: $subjek);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi-approval',
        );
    }
}
