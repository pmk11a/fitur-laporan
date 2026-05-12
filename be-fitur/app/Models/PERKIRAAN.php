<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PERKIRAAN extends Model
{
    protected $table = 'DBPERKIRAAN';
    protected $fillable = ['Perkiraan', 'Keterangan', 'Kelompok', 'Tipe', 'Valas', 'DK', 'Neraca', 'FlagCashFlow', 'Simbol', 'IsPPN', 'GroupPerkiraan', 'MyID', 'Lokasi', 'KodeAK', 'KodeSAK'];
    protected $casts = ['Kelompok' => 'integer', 'Tipe' => 'integer', 'DK' => 'integer', 'IsPPN' => 'boolean', 'Lokasi' => 'integer'];

    public function scopeSearchAkun($query, string $q)
    {
        $q = trim($q);
        if (empty($q)) {
            return $query->where('Tipe', 1)->orderBy('Perkiraan');
        }
        return $query
            ->where('Tipe', 1)
            ->where(function ($inner) use ($q) {
                $inner->where('Perkiraan', 'like', "%{$q}%")
                      ->orWhere('Keterangan', 'like', "%{$q}%");
            })
            ->orderBy('Perkiraan');
    }
}
