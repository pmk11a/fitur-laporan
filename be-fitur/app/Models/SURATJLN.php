<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SURATJLN extends Model
{
    protected $table = 'DBSURATJLN';
    protected $fillable = ['NOBUKTI', 'NOURUT', 'TANGGAL', 'KODECUST', 'NOSO', 'NOPNJ', 'NoAlamatKirim', 'AlamatKirim', 'KODEGDG', 'SOPIR', 'KETERANGAN', 'CATATAN', 'ISCETAK', 'ISBATAL', 'USERBATAL', 'KETBATAL', 'KodeExp', 'INSGdg', 'INSBrg', 'NewNo', 'TGLShipment', 'KotaAsal', 'TGLTiba', 'KotaTujuan', 'Vessel', 'Cont', 'NoCont', 'NoSeal', 'FlagTipe'];
    protected $casts = ['NoAlamatKirim' => 'integer', 'ISCETAK' => 'integer', 'ISBATAL' => 'boolean'];
}
