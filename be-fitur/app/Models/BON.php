<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BON extends Model
{
    protected $table = 'DBBON';
    protected $fillable = ['Devisi', 'NoBukti', 'NOURUT', 'Tanggal', 'Penerima', 'Keterangan', 'Debet', 'Kredit', 'Perkiraan', 'TglInput', 'UserID', 'Urut', 'BuktiKas', 'UrutKas', 'MyID', 'KodeVls', 'Kurs', 'DebetD', 'KreditD'];
    protected $casts = ['Debet' => 'float', 'Kredit' => 'float', 'Urut' => 'integer', 'UrutKas' => 'integer', 'Kurs' => 'float', 'DebetD' => 'float', 'KreditD' => 'float'];
}
