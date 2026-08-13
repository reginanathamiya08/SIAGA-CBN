<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Configuration;

class DetailGajiKomponenCollection extends Collection
{
    /**
     * Get a property dynamically from the collection.
     * Maps virtual columns to specific components in the collection.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key === 'gaji_pokok') {
            $row = $this->firstWhere('komponen_gaji_id', 'MKG-00001');
            return $row ? (float) $row->nominal : 0.0;
        }

        if ($key === 'uang_makan') {
            $row = $this->firstWhere('komponen_gaji_id', 'MKG-00003');
            return $row ? (float) $row->nominal : null;
        }

        if ($key === 'uang_transport') {
            $row = $this->firstWhere('komponen_gaji_id', 'MKG-00004');
            return $row ? (float) $row->nominal : null;
        }

        if ($key === 'persen_bpjs_kes') {
            $row = $this->firstWhere('komponen_gaji_id', 'MKG-00009');
            return $row ? (float) $row->nominal : (float) Configuration::getValue('persen_bpjs_kes', 9.24);
        }

        if ($key === 'persen_bpjs_tk') {
            $row = $this->firstWhere('komponen_gaji_id', 'MKG-00010');
            return $row ? (float) $row->nominal : (float) Configuration::getValue('persen_bpjs_tk', 5.00);
        }

        return parent::__get($key);
    }
}
