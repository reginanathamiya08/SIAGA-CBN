<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\UsernameGeneratorService;

class StoreKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jenis  = $this->input('jenis_karyawan', '');
        $divisi = $this->input('divisi', '');

        $divisiValid = $jenis === 'tetap'
            ? ['keuangan', 'koordinator_cs', 'adm_umum']
            : ['HC', 'umum'];

        $jabatanValid = UsernameGeneratorService::daftarJabatan($jenis, $divisi);

        return [
            'nama'           => 'required|string|max:100',
            'email'          => 'required|email|max:150|unique:karyawan,email',
            'jenis_karyawan' => 'required|in:tetap,kontrak',
            'divisi'         => 'required|in:' . implode(',', $divisiValid),
            'jabatan'        => 'required|in:' . implode(',', $jabatanValid),
            'tanggal_masuk'  => 'required|date|before_or_equal:today',
            'no_hp'          => 'nullable|string|max:20',
            'password'       => 'required|string|min:6',
            'jenis_dokumen'  => 'nullable|in:KTA,SIM,ijazah,sertifikat,lainnya',
            'file_dokumen'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'           => 'Nama karyawan wajib diisi.',
            'email.required'          => 'Email karyawan wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'email.unique'            => 'Email ini sudah digunakan oleh karyawan lain.',
            'jenis_karyawan.required' => 'Jenis karyawan wajib dipilih.',
            'jenis_karyawan.in'       => 'Jenis karyawan tidak valid.',
            'divisi.required'         => 'Divisi wajib dipilih.',
            'divisi.in'               => 'Divisi tidak valid untuk jenis karyawan yang dipilih.',
            'jabatan.required'        => 'Jabatan wajib dipilih.',
            'jabatan.in'              => 'Jabatan tidak valid untuk divisi yang dipilih.',
            'tanggal_masuk.required'  => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date'      => 'Format tanggal tidak valid.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk tidak boleh di masa depan.',
            'password.required'       => 'Password awal wajib diisi.',
            'password.min'            => 'Password minimal 6 karakter.',
            'file_dokumen.mimes'      => 'File harus berformat PDF, JPG, atau PNG.',
            'file_dokumen.max'        => 'Ukuran file maksimal 2MB.',
        ];
    }
}