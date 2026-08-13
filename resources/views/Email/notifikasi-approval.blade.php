<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Pengajuan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f6f9;
            padding: 32px 16px;
            color: #333;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1E3A5F;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header p {
            color: rgba(255,255,255,0.6);
            font-size: 12px;
            margin-top: 4px;
        }
        .body {
            padding: 32px;
        }
        .greeting {
            font-size: 15px;
            font-weight: 600;
            color: #1E3A5F;
            margin-bottom: 16px;
        }
        .status-box {
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
            border-left: 4px solid;
        }
        .status-box.disetujui {
            background: #f0fdf4;
            border-color: #16a34a;
        }
        .status-box.ditolak {
            background: #fef2f2;
            border-color: #dc2626;
        }
        .status-label {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .status-box.disetujui .status-label { color: #16a34a; }
        .status-box.ditolak  .status-label { color: #dc2626; }
        .status-desc {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .detail-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .detail-table td:first-child {
            color: #9ca3af;
            font-weight: 600;
            width: 40%;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        .detail-table td:last-child {
            color: #1E3A5F;
            font-weight: 700;
        }
        .alasan-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .alasan-box p:first-child {
            font-size: 11px;
            font-weight: 800;
            color: #ea580c;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .alasan-box p:last-child {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }
        .note {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 32px;
            text-align: center;
            border-top: 1px solid #f3f4f6;
        }
        .footer p {
            font-size: 11px;
            color: #9ca3af;
        }
        .footer strong {
            color: #1E3A5F;
        }
    </style>
</head>
<body>
<div class="container">

    {{-- Header --}}
    <div class="header">
        <h1>PT Citra Bangun Nagari</h1>
        <p>Sistem Informasi Absensi & Penggajian</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p class="greeting">Kepada Yth.<br>{{ $namaKaryawan }}</p>

        {{-- Status box --}}
        <div class="status-box {{ $statusApproval }}">
            <p class="status-label">
                {{ $statusApproval === 'disetujui' ? '✅ Pengajuan Disetujui' : '❌ Pengajuan Ditolak' }}
            </p>
            <p class="status-desc">
                Pengajuan <strong>{{ $jenisAjuan }}</strong> Anda telah
                @if ($statusApproval === 'disetujui')
                    <span style="color: #16a34a">disetujui</span> oleh pimpinan.
                @else
                    <span style="color: #dc2626">ditolak</span> oleh pimpinan.
                @endif
            </p>
        </div>

        {{-- Detail --}}
        <table class="detail-table">
            <tr>
                <td>Jenis Pengajuan</td>
                <td>{{ $jenisAjuan }}</td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td>{{ $keterangan }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ ucfirst($statusApproval) }}</td>
            </tr>
            <tr>
                <td>Tanggal Notifikasi</td>
                <td>{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>

        {{-- Alasan tolak (jika ditolak) --}}
        @if ($statusApproval === 'ditolak' && $alasanTolak)
            <div class="alasan-box">
                <p>Alasan Penolakan</p>
                <p>{{ $alasanTolak }}</p>
            </div>
        @endif

        <p class="note">
            Email ini dikirim otomatis oleh sistem. Jika ada pertanyaan, silakan hubungi admin
            atau pimpinan langsung. Harap tidak membalas email ini.
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} <strong>PT Citra Bangun Nagari</strong>. All rights reserved.</p>
        <p style="margin-top: 4px;">Sistem Informasi Absensi & Penggajian</p>
    </div>

</div>
</body>
</html>
