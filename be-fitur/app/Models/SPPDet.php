<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPPDet extends Model
{
    protected $table = 'dbSPPDet';
    protected $fillable = ['NoBukti', 'Urut', 'NoSO', 'UrutSO', 'KodeBrg', 'NamaBrg', 'QNT', 'QNT2', 'SAT_1', 'SAT_2', 'NOSAT', 'ISI', 'NetW', 'GrossW', 'Mesurement', 'KetDetail', 'ShippingMark', 'MyID', 'HPP', 'Kodegdg', 'isCetakKitir'];
    protected $casts = ['Urut' => 'integer', 'UrutSO' => 'integer', 'QNT' => 'float', 'QNT2' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'NetW' => 'float', 'GrossW' => 'float', 'Mesurement' => 'float', 'HPP' => 'float', 'isCetakKitir' => 'boolean'];
}
