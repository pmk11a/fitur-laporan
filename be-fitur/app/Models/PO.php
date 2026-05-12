<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PO extends Model
{
    protected $table = 'DBPO';
    protected $fillable = ['NOBUKTI', 'NOURUT', 'TANGGAL', 'TglJatuhTempo', 'KODESUPP', 'HANDLING', 'KODEEXP', 'KETERANGAN', 'FAKTURSUPP', 'KODEVLS', 'KURS', 'PPN', 'TIPEBAYAR', 'HARI', 'TipeDisc', 'DISC', 'DISCRP', 'ISCETAK', 'NilaiCetak', 'IsBatal', 'UserBatal', 'IsClose', 'IsExp', 'isAut', 'KodeGDG', 'cetakke', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'TglBatal', 'flagtipe', 'IsPPh'];
    protected $casts = ['HANDLING' => 'float', 'KURS' => 'float', 'PPN' => 'integer', 'TIPEBAYAR' => 'integer', 'HARI' => 'integer', 'TipeDisc' => 'integer', 'DISC' => 'float', 'DISCRP' => 'float', 'ISCETAK' => 'boolean', 'NilaiCetak' => 'integer', 'IsBatal' => 'boolean', 'IsClose' => 'boolean', 'IsExp' => 'boolean', 'isAut' => 'boolean', 'cetakke' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'flagtipe' => 'integer', 'IsPPh' => 'boolean'];
}
