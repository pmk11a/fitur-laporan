<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPP extends Model
{
    protected $table = 'dbSPP';
    protected $fillable = ['NoBukti', 'NoUrut', 'Tanggal', 'NoSHIP', 'NoPesan', 'KodeCustSupp', 'TglKirim', 'NoLC', 'NamaKirim', 'AlamatKirim', 'Packing', 'Catatan', 'IsCetak', 'IDUser', 'IsClose', 'IsFlag', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoAlamatKirim', 'isCetakKitir', 'cetakke', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'IsBatal', 'UserBatal', 'TglBatal', 'FlagTipe'];
    protected $casts = ['IsCetak' => 'boolean', 'IsClose' => 'boolean', 'IsFlag' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'NoAlamatKirim' => 'integer', 'isCetakKitir' => 'boolean', 'cetakke' => 'integer', 'MaxOL' => 'integer', 'IsBatal' => 'boolean', 'FlagTipe' => 'integer'];
}
