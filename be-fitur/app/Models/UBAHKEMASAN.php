<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UBAHKEMASAN extends Model
{
    protected $table = 'DBUBAHKEMASAN';
    protected $fillable = ['NOBUKTI', 'NOURUT', 'TANGGAL', 'NOTE', 'IsCetak', 'NilaiCetak', 'FlagTipe', 'IndexMargin', 'Revisi', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL'];
    protected $casts = ['IsCetak' => 'boolean', 'NilaiCetak' => 'integer', 'FlagTipe' => 'integer', 'IndexMargin' => 'float', 'Revisi' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer'];
}
