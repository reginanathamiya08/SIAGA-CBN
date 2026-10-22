<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMitraRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $timeRegex = 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';

        return [
            'nama_mitra'     => 'required|string|min:3|max:150',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'radius_meter'   => 'required|integer|min:10|max:5000',
            'ip_public'      => 'required|string',
            'mitra_induk_id' => 'nullable|exists:mitra,id',
            'jam_masuk'      => ['required', $timeRegex],
            'jam_pulang'     => ['required', $timeRegex],
            'is_pusat'       => 'nullable|boolean',
            'shifts'         => 'nullable|array',
            'shifts.*.id'              => 'nullable|exists:shift,id',
            'shifts.*.nama_shift'      => 'nullable',
            'shifts.*.jam_mulai'       => ['nullable', $timeRegex],
            'shifts.*.jam_selesai'     => ['nullable', $timeRegex],
            'shifts.*.window_start'    => ['nullable', $timeRegex],
            'shifts.*.window_end'      => ['nullable', $timeRegex],
            'shifts.*.toleransi_menit' => 'nullable|integer',
            'shifts.*.is_lintas_hari'  => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_mitra.required'   => 'Nama mitra wajib diisi.',
            'nama_mitra.min'        => 'Nama mitra minimal 3 karakter.',
            'latitude.required'     => 'Latitude wajib diisi.',
            'latitude.numeric'      => 'Latitude harus berupa angka.',
            'latitude.between'      => 'Latitude harus antara -90 dan 90.',
            'longitude.required'    => 'Longitude wajib diisi.',
            'longitude.numeric'     => 'Longitude harus berupa angka.',
            'longitude.between'     => 'Longitude harus antara -180 dan 180.',
            'radius_meter.required' => 'Radius wajib diisi.',
            'radius_meter.integer'  => 'Radius harus berupa angka bulat.',
            'radius_meter.min'      => 'Radius minimal 10 meter.',
            'radius_meter.max'      => 'Radius maksimal 5000 meter.',
            'ip_public.required'    => 'IP Public kantor wajib diisi.',
            'ip_public.string'      => 'Format IP Public tidak valid.',
            'jam_masuk.required'    => 'Jam masuk wajib diisi.',
            'jam_masuk.regex'       => 'Format jam masuk tidak valid (gunakan HH:MM).',
            'jam_pulang.required'   => 'Jam pulang wajib diisi.',
            'jam_pulang.regex'      => 'Format jam pulang tidak valid (gunakan HH:MM).',
        ];
    }
}
