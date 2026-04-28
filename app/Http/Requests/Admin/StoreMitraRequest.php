<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMitraRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_mitra'     => 'required|string|max:150',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'radius_meter'   => 'required|integer|min:10|max:5000',
            'mitra_induk_id' => 'nullable|exists:mitra,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_mitra.required'   => 'Nama mitra wajib diisi.',
            'latitude.required'     => 'Latitude wajib diisi.',
            'latitude.between'      => 'Latitude harus antara -90 dan 90.',
            'longitude.required'    => 'Longitude wajib diisi.',
            'longitude.between'     => 'Longitude harus antara -180 dan 180.',
            'radius_meter.required' => 'Radius wajib diisi.',
            'radius_meter.min'      => 'Radius minimal 10 meter.',
            'radius_meter.max'      => 'Radius maksimal 5000 meter.',
        ];
    }
}