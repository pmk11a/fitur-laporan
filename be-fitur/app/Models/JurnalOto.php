<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalOto extends Model
{
    protected $table = 'DBJurnalOto';
    protected $fillable = ['NoBukti', 'Tanggal', 'Devisi', 'Note', 'Lampiran', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'Urut', 'Perkiraan', 'Lawan', 'Keterangan', 'Keterangan2', 'Debet', 'Kredit', 'Valas', 'Kurs', 'DebetRp', 'KreditRp', 'TipeTrans', 'TPHC', 'CustSuppP', 'CustSuppL', 'KodeP', 'KodeL', 'NoAktivaP', 'NoAktivaL', 'StatusAktivaP', 'StatusAktivaL', 'Nobon', 'KodeBag', 'StatusGiro', 'MyID', 'Jenis', 'NOURUT', 'NobuktiTrans'];
    protected $casts = ['Lampiran' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'Urut' => 'integer', 'Debet' => 'float', 'Kredit' => 'float', 'Kurs' => 'float', 'DebetRp' => 'float', 'KreditRp' => 'float'];
}
