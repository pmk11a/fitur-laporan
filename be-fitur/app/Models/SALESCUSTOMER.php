<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SALESCUSTOMER extends Model
{
    protected $table = 'DBSALESCUSTOMER';
    protected $fillable = ['KeyNik', 'KodeCustSupp', 'NIK', 'MingguKe'];
    protected $casts = ['KeyNik' => 'integer', 'MingguKe' => 'integer'];
}
