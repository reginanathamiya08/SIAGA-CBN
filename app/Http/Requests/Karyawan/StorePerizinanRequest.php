<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\KuotaPerizinan;

class StorePerizinanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jenisId = $this->input('jenis_perizinan_id');
        $jenis = \App\Models\JenisPerizinan::find($jenisId);

        $rules = [
            'jenis_perizinan_id' => 'required|exists:jenis_perizinan,id',
            'tanggal_mulai'      => 'required|date|after_or_equal:today',
            'tanggal_selesai'    => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'         => ($jenis && $jenis->slug === 'sakit_surat') ? 'nullable|string|max:500' : 'required|string|max:500',
            'rekan_kerja_id'     => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = Auth::user();
                    $jenisId = $this->input('jenis_perizinan_id');
                    $jenis = \App\Models\JenisPerizinan::find($jenisId);
                    if ($user && $user->isKaryawanTetap() && $jenis && $jenis->slug === 'cuti') {
                        if (!$value) {
                            $fail('Rekan kerja pengganti wajib dipilih.');
                        } else {
                            $rekan = \App\Models\User::find($value);
                            if (!$rekan || !$rekan->isKaryawanTetap()) {
                                $fail('Rekan kerja pengganti harus sesama Karyawan Tetap.');
                            }
                        }
                    }
                },
            ],
            'file_bukti'         => [
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],
        ];

        $user = Auth::user();
        $jenisId = $this->input('jenis_perizinan_id');
        $jenis = \App\Models\JenisPerizinan::find($jenisId);
        
        $isRequired = false;
        if ($jenis) {
            if ($jenis->wajib_upload_bukti || $jenis->slug === 'cuti') {
                $isRequired = true;
            }
        }

        if ($isRequired) {
            $rules['file_bukti'][] = 'required';
        } else {
            $rules['file_bukti'][] = 'nullable';
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'jenis_perizinan_id.required'   => 'Jenis izin wajib dipilih.',
            'jenis_perizinan_id.exists'     => 'Jenis izin tidak valid.',
            'tanggal_mulai.required'        => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal'  => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.required'      => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'=> 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'file_bukti.mimes'              => 'File harus berformat PDF, JPG, atau PNG.',
            'file_bukti.max'                => 'Ukuran file maksimal 2MB.',
            'keterangan.required'           => 'Keterangan/alasan pengajuan perizinan wajib diisi.',
        ];

        $user = Auth::user();
        $jenisId = $this->input('jenis_perizinan_id');
        $jenis = \App\Models\JenisPerizinan::find($jenisId);
        if ($jenis) {
            if ($jenis->slug === 'cuti') {
                $messages['file_bukti.required'] = 'Wajib mengunggah file bukti/dokumen pengajuan Cuti Tahunan.';
            } elseif ($jenis->slug === 'dinas_luar') {
                $messages['file_bukti.required'] = 'Wajib upload file Surat Tugas.';
            } else {
                $messages['file_bukti.required'] = 'Wajib upload file bukti/surat keterangan.';
            }
        } else {
            $messages['file_bukti.required'] = 'File bukti wajib diunggah.';
        }

        return $messages;
    }
}
