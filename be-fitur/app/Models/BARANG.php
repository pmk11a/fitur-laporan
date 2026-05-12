<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BARANG extends Model
{
    protected $table = 'DBBARANG';
    protected $fillable = ['KODEBRG', 'NAMABRG', 'KODEGRP', 'KODESUBGRP', 'KODESUPP', 'SAT1', 'ISI1', 'SAT2', 'ISI2', 'SAT3', 'ISI3', 'Hrg1_1', 'Hrg2_1', 'Hrg3_1', 'Hrg1_2', 'Hrg2_2', 'Hrg3_2', 'Hrg1_3', 'Hrg2_3', 'Hrg3_3', 'QntMin', 'QntMax', 'ISAKTIF', 'Keterangan', 'NFix', 'NamaBrg2', 'Tolerate', 'Proses', 'IsTakeIn', 'IsBarang', 'KodeBag', 'KODEBhn', 'KODEBRGL', 'IsUpdate', 'Qnt1', 'HrgKhusus1'];
    protected $casts = ['ISI1' => 'float', 'ISI2' => 'float', 'ISI3' => 'float', 'Hrg1_1' => 'float', 'Hrg2_1' => 'float', 'Hrg3_1' => 'float', 'Hrg1_2' => 'float', 'Hrg2_2' => 'float', 'Hrg3_2' => 'float', 'Hrg1_3' => 'float', 'Hrg2_3' => 'float', 'Hrg3_3' => 'float', 'QntMin' => 'float', 'QntMax' => 'float', 'ISAKTIF' => 'integer', 'NFix' => 'boolean', 'Tolerate' => 'float', 'Proses' => 'integer', 'IsTakeIn' => 'boolean', 'IsBarang' => 'integer', 'IsUpdate' => 'boolean', 'Qnt1' => 'float', 'HrgKhusus1' => 'float'];
}
