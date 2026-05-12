<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UBAHKEMASANDET extends Model
{
    protected $table = 'DBUBAHKEMASANDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'KodeGdg', 'SATUAN', 'NOSAT', 'ISI', 'QNTDB', 'QNTCR', 'HARGA', 'HPP', 'HPP2', 'QntPRSI', 'QntPRSO', 'HrgPRSI', 'HrgPRSO', 'HargaIn', 'tglInput', 'UserID'];
    protected $casts = ['URUT' => 'integer', 'NOSAT' => 'integer', 'ISI' => 'float', 'QNTDB' => 'float', 'QNTCR' => 'float', 'HARGA' => 'float', 'HPP' => 'float', 'HPP2' => 'float', 'QntPRSI' => 'float', 'QntPRSO' => 'float', 'HrgPRSI' => 'float', 'HrgPRSO' => 'float', 'HargaIn' => 'float'];
}
