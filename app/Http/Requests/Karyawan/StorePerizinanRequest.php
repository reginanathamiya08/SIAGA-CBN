<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\KuotaCuti;

class StorePerizinanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_izin'      => 'required|in:cuti,izin_pribadi,sakit_surat,sakit_no_surat',
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string|max:500',
            // File wajib untuk sakit dengan surat dokter
            'file_bukti'      => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($this->input('jenis_izin') === 'sakit_surat' && !$value) {
                        $fail('Sakit dengan surat dokter wajib upload file surat dokter.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_izin.required'       => 'Jenis izin wajib dipilih.',
            'jenis_izin.in'             => 'Jenis izin tidak valid.',
            'tanggal_mulai.required'    => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.required'  => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'file_bukti.mimes'          => 'File harus berformat PDF, JPG, atau PNG.',
            'file_bukti.max'            => 'Ukuran file maksimal 2MB.',
        ];
    }
}