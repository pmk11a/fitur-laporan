<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RPenyerahanBhn extends Model
{
    protected $table = 'DBRPenyerahanBhn';
    protected $fillable = ['Nobukti', 'Nourut', 'Tanggal', 'Kodegdg', 'NoPenyerahanBhn', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'IsSampel', 'FlagTipe'];
    protected $casts = ['IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'IsSampel' => 'boolean', 'FlagTipe' => 'integer'];
}
