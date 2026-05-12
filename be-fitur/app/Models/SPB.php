<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPB extends Model
{
    protected $table = 'dbSPB';
    protected $fillable = ['NoBukti', 'NoUrut', 'Tanggal', 'NoSPP', 'KodeCustSupp', 'NoPolKend', 'Container', 'NoContainer', 'NoSeal', 'Sopir', 'Catatan', 'IsCetak', 'IDUser', 'IsClose', 'IsFlag', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'KodeExp', 'NoResi', 'JumlahTagihan', 'IsBatal', 'UserBatal', 'TglBatal', 'FlagTipe'];
    protected $casts = ['IsCetak' => 'boolean', 'IsClose' => 'boolean', 'IsFlag' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'JumlahTagihan' => 'float', 'IsBatal' => 'boolean', 'FlagTipe' => 'integer'];
}
