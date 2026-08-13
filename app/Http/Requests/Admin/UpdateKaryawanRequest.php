<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\UsernameGeneratorService;

class UpdateKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $karyawan    = $this->route('karyawan');
        $divisiInput = $this->input('divisi', $karyawan->divisi);

        $divisiValid = $karyawan->isTetap()
            ? ['keuangan', 'koordinator_cs', 'adm_umum']
            : ['HC', 'umum'];

        $jabatanValid = UsernameGeneratorService::daftarJabatan(
            $karyawan->role?->slug ?? 'karyawan_tetap',
            $divisiInput
        );

        return [
            'nama'          => 'required|string|max:100',
            'email'         => 'required|email|max:150|unique:users,email,' . $karyawan->id,
            'divisi'        => 'required|in:' . implode(',', $divisiValid),
            'jabatan'       => 'required|in:' . implode(',', $jabatanValid),
            'pendidikan'    => 'required|in:S3,S2,S1,D3,SMA/SMK',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'no_hp'         => 'nullable|string|max:20',
            'jenis_dokumen' => 'nullable|in:KTA,SIM,ijazah,sertifikat,lainnya',
            'file_dokumen'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'          => 'Nama karyawan wajib diisi.',
            'email.required'         => 'Email karyawan wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'email.unique'           => 'Email ini sudah digunakan oleh karyawan lain.',
            'jabatan.required'       => 'Jabatan wajib dipilih.',
            'jabatan.in'             => 'Jabatan tidak valid.',
            'pendidikan.required'    => 'Tamatan (pendidikan) wajib dipilih.',
            'pendidikan.in'          => 'Pilihan pendidikan tidak valid.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date'     => 'Format tanggal tidak valid.',
            'file_dokumen.mimes'     => 'File harus berformat PDF, JPG, atau PNG.',
            'file_dokumen.max'       => 'Ukuran file maksimal 2MB.',
        ];
    }
}
