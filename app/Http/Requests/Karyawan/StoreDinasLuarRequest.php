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
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:500',
            'file_bukti'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_mulai.required'        => 'Tanggal mulai dinas wajib diisi.',
            'tanggal_mulai.after_or_equal'  => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.required'      => 'Tanggal selesai dinas wajib diisi.',
            'tanggal_selesai.after_or_equal'=> 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'keterangan.required'           => 'Maksud / Keterangan tugas dinas luar kota wajib diisi.',
            'file_bukti.required'           => 'Wajib mengunggah file Surat Tugas.',
            'file_bukti.mimes'              => 'File Surat Tugas harus berformat PDF, JPG, atau PNG.',
            'file_bukti.max'                => 'Ukuran file Surat Tugas maksimal 2MB.',
        ];
    }
}
