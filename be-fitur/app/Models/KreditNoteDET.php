<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KreditNoteDET extends Model
{
    protected $table = 'DBKreditNoteDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'NoInv', 'Keterangan', 'Nilai', 'KodeVLS', 'Kurs', 'NilaiRp', 'URUTInvoicepl', 'PPN', 'NDPP', 'NPPN', 'NNET', 'NDPPRp', 'NPPNRp', 'NNETRp'];
    protected $casts = ['URUT' => 'integer', 'Nilai' => 'float', 'Kurs' => 'float', 'NilaiRp' => 'float', 'URUTInvoicepl' => 'integer', 'PPN' => 'integer', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float'];
}
