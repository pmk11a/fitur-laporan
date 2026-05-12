<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SURATJLNDET extends Model
{
    protected $table = 'DBSURATJLNDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'KODEGDG', 'QNT', 'QNT2', 'QNTBATAL', 'TGLBATAL', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'HPP', 'URUTSPP', 'NOSPP', 'KetDet', 'NetW', 'GrossW'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'QNT2' => 'float', 'QNTBATAL' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'HPP' => 'float', 'URUTSPP' => 'integer', 'NetW' => 'float', 'GrossW' => 'float'];
}
