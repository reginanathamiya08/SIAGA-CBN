<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class StoreDinasLuarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tujuan'             => 'required|string|max:200',
            'tanggal_berangkat'  => 'required|date|after_or_equal:today',
            'tanggal_kembali'    => 'required|date|after_or_equal:tanggal_berangkat',
            'file_surat_tugas'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'tujuan.required'                  => 'Tujuan dinas wajib diisi.',
            'tujuan.max'                        => 'Tujuan maksimal 200 karakter.',
            'tanggal_berangkat.required'        => 'Tanggal berangkat wajib diisi.',
            'tanggal_berangkat.after_or_equal'  => 'Tanggal berangkat tidak boleh di masa lalu.',
            'tanggal_kembali.required'          => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.after_or_equal'    => 'Tanggal kembali tidak boleh sebelum tanggal berangkat.',
            'file_surat_tugas.mimes'            => 'File harus berformat PDF, JPG, atau PNG.',
            'file_surat_tugas.max'              => 'Ukuran file maksimal 2MB.',
        ];
    }
}