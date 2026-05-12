<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JUAL extends Model
{
    protected $table = 'DBJUAL';
    protected $fillable = ['NOBUKTI', 'NOURUT', 'TANGGAL', 'TGLJATUHTEMPO', 'KODECUST', 'KODESLS', 'KODEGDG', 'HANDLING', 'KETERANGAN', 'KODEVLS', 'KURS', 'PPN', 'TIPEBAYAR', 'HARI', 'CATATAN', 'TIPEDISC', 'DISC', 'DISCRP', 'NILAIPOT', 'NILAIDPP', 'NILAIPPN', 'NILAINET', 'ISCETAK', 'ISBATAL', 'USERBATAL', 'NOPAJAK', 'KodeExp', 'INSGdg', 'INSBrg', 'TGLFPJ', 'NobuktiUM', 'NewNo', 'Term', 'FlagTipe'];
    protected $casts = ['KODESLS' => 'integer', 'HANDLING' => 'float', 'KURS' => 'float', 'PPN' => 'integer', 'TIPEBAYAR' => 'integer', 'HARI' => 'integer', 'TIPEDISC' => 'integer', 'DISC' => 'float', 'DISCRP' => 'float', 'NILAIPOT' => 'float', 'NILAIDPP' => 'float', 'NILAIPPN' => 'float', 'NILAINET' => 'float', 'ISCETAK' => 'integer', 'ISBATAL' => 'boolean'];
}
