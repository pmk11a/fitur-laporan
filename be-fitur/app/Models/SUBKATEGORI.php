<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SUBKATEGORI extends Model
{
    protected $table = 'DBSUBKATEGORI';
    protected $fillable = ['KodeSubKategori', 'Keterangan', 'KodeKategori', 'Persediaan'];
}
