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
        $roleId   = $this->input('role_id', '');
        $role     = \App\Models\Role::find($roleId);
        $roleSlug = $role?->slug ?? '';
        $divisi   = $this->input('divisi', '');

        $divisiValid = ($roleSlug === 'karyawan_tetap')
            ? ['keuangan', 'koordinator_cs', 'adm_umum']
            : ['HC', 'umum'];

        $jabatanValid = UsernameGeneratorService::daftarJabatan($roleSlug, $divisi);

        return [
            'nama'          => 'required|string|max:100',
            'email'         => 'required|email|max:150|unique:users,email',
            'role_id'       => 'required|exists:roles,id',
            'divisi'        => 'required|in:' . implode(',', $divisiValid),
            'jabatan'       => 'required|in:' . implode(',', $jabatanValid),
            'pendidikan'    => 'required|in:S3,S2,S1,D3,SMA/SMK',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'no_hp'         => 'nullable|string|max:20',
            'password'      => 'required|string|min:6',
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
            'role_id.required'       => 'Role/Jenis karyawan wajib dipilih.',
            'role_id.exists'         => 'Role karyawan tidak valid.',
            'divisi.required'        => 'Divisi wajib dipilih.',
            'divisi.in'              => 'Divisi tidak valid untuk jenis karyawan yang dipilih.',
            'jabatan.required'       => 'Jabatan wajib dipilih.',
            'jabatan.in'             => 'Jabatan tidak valid untuk divisi yang dipilih.',
            'pendidikan.required'    => 'Tamatan (pendidikan) wajib dipilih.',
            'pendidikan.in'          => 'Pilihan pendidikan tidak valid.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date'     => 'Format tanggal tidak valid.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk tidak boleh di masa depan.',
            'password.required'      => 'Password awal wajib diisi.',
            'password.min'           => 'Password minimal 6 karakter.',
            'file_dokumen.mimes'     => 'File harus berformat PDF, JPG, atau PNG.',
            'file_dokumen.max'       => 'Ukuran file maksimal 2MB.',
        ];
    }
}
