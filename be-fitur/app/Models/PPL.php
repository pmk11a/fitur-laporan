<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PPL extends Model
{
    protected $table = 'DBPPL';
    protected $fillable = ['Nobukti', 'Nourut', 'Tanggal', 'IsClose', 'KDDep', 'cetakke', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'IsBatal', 'UserBatal', 'TglBatal'];
    protected $casts = ['IsClose' => 'boolean', 'cetakke' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'IsBatal' => 'boolean'];
}
