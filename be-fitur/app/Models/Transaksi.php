<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'dbTransaksi';
    protected $fillable = ['NoBukti', 'Tanggal', 'Devisi', 'Note', 'Lampiran', 'Perkiraan', 'Lawan', 'Keterangan', 'Keterangan2', 'Debet', 'Kredit', 'Valas', 'Kurs', 'DebetRp', 'KreditRp', 'TipeTrans', 'TPHC', 'CustSuppP', 'CustSuppL', 'Urut', 'KodeP', 'KodeL', 'NoAktivaP', 'NoAktivaL', 'StatusAktivaP', 'StatusAktivaL', 'Nobon', 'KodeBag', 'StatusGiro', 'MyID', 'FlagSimbol'];
    protected $casts = ['Lampiran' => 'float', 'Debet' => 'float', 'Kredit' => 'float', 'Kurs' => 'float', 'DebetRp' => 'float', 'KreditRp' => 'float', 'Urut' => 'integer'];
}
