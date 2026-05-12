<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KATEGORI extends Model
{
    protected $table = 'DBKATEGORI';
    protected $fillable = ['KodeKategori', 'Keterangan', 'Kodegdg'];
}
