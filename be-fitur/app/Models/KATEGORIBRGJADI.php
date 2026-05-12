<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KATEGORIBRGJADI extends Model
{
    protected $table = 'DBKATEGORIBRGJADI';
    protected $fillable = ['KodeKategori', 'Keterangan', 'Kodegdg', 'Perkiraan'];
}
