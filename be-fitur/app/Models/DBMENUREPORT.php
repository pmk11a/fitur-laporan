<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBMENUREPORT extends Model
{
    protected $table = 'DBMENUREPORT';
    protected $primaryKey = 'KODEMENU';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'KODEMENU',
        'Keterangan',
        'L0',
        'ACCESS',
        'OL',
        'TipeTrans',
        'ROUTENAME',
        'icon',
        'PlatformMask'
    ];

    protected $casts = [
        'L0' => 'integer',
        'ACCESS' => 'integer',
        'OL' => 'integer',
        'PlatformMask' => 'integer'
    ];

    /**
     * Get child menu items
     */
    public function children()
    {
        return $this->hasMany(DBMENUREPORT::class, 'KODEMENU', 'KODEMENU')
            ->whereRaw('KODEMENU LIKE ?', [$this->KODEMENU . '%'])
            ->orderBy('OL');
    }

    /**
     * Get report master configuration
     */
    public function masterLaporan()
    {
        return $this->hasOne(MasterLaporan::class, 'KODEMENU', 'KODEMENU');
    }

    /**
     * Get user access for specific user
     */
    public function userAccess(string $userId)
    {
        return $this->hasOne(DBFLMENUREPORT::class, 'KODEMENU', 'KODEMENU')
            ->where('USERID', $userId);
    }
}