<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDET extends Model
{
    protected $table = 'DBInvoiceDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'NoBeli'];
    protected $casts = ['URUT' => 'integer'];
}
