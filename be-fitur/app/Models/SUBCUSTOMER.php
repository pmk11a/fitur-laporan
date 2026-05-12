<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SUBCUSTOMER extends Model
{
    protected $table = 'DBSUBCUSTOMER';
    protected $fillable = ['KodeSubCustomer', 'NamaSubCustomer', 'kodecust', 'IsUpdate'];
    protected $casts = ['IsUpdate' => 'boolean'];
}
