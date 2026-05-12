<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBFLMENUREPORT extends Model
{
    protected $table = 'DBFLMENUREPORT';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'USERID',
        'L1',
        'Access',
        'IsDesign',
        'IsExport'
    ];

    protected $casts = [
        'Access' => 'integer',
        'IsDesign' => 'integer',
        'IsExport' => 'integer'
    ];

    /**
     * Get parent menu
     */
    public function menu()
    {
        return $this->belongsTo(DBMENUREPORT::class, 'L1', 'KODEMENU');
    }
}