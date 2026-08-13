<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class StoreLemburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|different:jam_mulai',
            'keterangan'  => 'required|string|min:5|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required'       => 'Tanggal lembur wajib diisi.',
            'tanggal.date'           => 'Format tanggal tidak valid.',
            'jam_mulai.required'     => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format'  => 'Format jam tidak valid (HH:MM).',
            'jam_selesai.required'   => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format'=> 'Format jam tidak valid (HH:MM).',
            'jam_selesai.different'  => 'Jam selesai harus berbeda dengan jam mulai.',
            'keterangan.required'    => 'Keterangan/keperluan lembur wajib diisi.',
            'keterangan.min'         => 'Keterangan minimal 5 karakter.',
        ];
    }
}
