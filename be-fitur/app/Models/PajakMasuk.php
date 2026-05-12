<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PajakMasuk extends Model
{
    protected $table = 'DBPajakMasuk';
    protected $fillable = ['NoBukti', 'Urut', 'NOFPJ', 'TGLFPJ', 'NPPn', 'TglLaporFPJ', 'TipePPh', 'NoPPh', 'TglPPh', 'nPPh', 'TglLaporPPh', 'NPWP', 'NamaPKP', 'AlamatPKP1', 'AlamatPKP2', 'KotaPKP', 'MyID', 'UrutTrans'];
    protected $casts = ['Urut' => 'integer', 'NPPn' => 'float', 'TipePPh' => 'integer', 'nPPh' => 'float', 'UrutTrans' => 'integer'];
}
