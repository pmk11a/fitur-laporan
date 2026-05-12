<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PORevDET extends Model
{
    protected $table = 'DBPORevDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'PPN', 'DISC', 'QNT', 'QntBatal', 'TglBatal', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'DISCP', 'DISCTOT', 'BYANGKUT', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'NNET', 'NoPPL', 'IsClose', 'Catatan', 'revisike', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'DISC' => 'float', 'QNT' => 'float', 'QntBatal' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'DISCP' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'IsClose' => 'boolean', 'revisike' => 'integer', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float'];
}
