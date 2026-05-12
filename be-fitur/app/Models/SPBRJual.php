<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPBRJual extends Model
{
    protected $table = 'dbSPBRJual';
    protected $fillable = ['NoBukti', 'NoUrut', 'Tanggal', 'NoRPJ', 'KodeCustSupp', 'NoPolKend', 'Container', 'NoContainer', 'NoSeal', 'Sopir', 'Catatan', 'IsCetak', 'IDUser', 'IsClose', 'IsFlag', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'IsBatal', 'UserBatal', 'TglBatal', 'Flagtipe'];
    protected $casts = ['IsCetak' => 'boolean', 'IsClose' => 'boolean', 'IsFlag' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'IsBatal' => 'boolean', 'Flagtipe' => 'integer'];
}
