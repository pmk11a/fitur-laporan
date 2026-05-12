<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebetNoteDET extends Model
{
    protected $table = 'DBDebetNoteDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'NoInv', 'Keterangan', 'Nilai', 'KodeVLS', 'Kurs', 'NilaiRp', 'PPN', 'NDPP', 'NPPN', 'NNET', 'NDPPRp', 'NPPNRp', 'NNETRp'];
    protected $casts = ['URUT' => 'integer', 'Nilai' => 'float', 'Kurs' => 'float', 'NilaiRp' => 'float', 'PPN' => 'integer', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float'];
}
