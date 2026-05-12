<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TRANSFERDET extends Model
{
    protected $table = 'DBTRANSFERDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'GDGASAL', 'GDGTUJUAN', 'SAT_1', 'SAT_2', 'NOSAT', 'ISI', 'QNT', 'QNT2', 'HARGA', 'HPP', 'MyID'];
    protected $casts = ['URUT' => 'integer', 'NOSAT' => 'integer', 'ISI' => 'float', 'QNT' => 'float', 'QNT2' => 'float', 'HARGA' => 'float', 'HPP' => 'float'];
}
