<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class FLPASS extends Authenticatable implements JWTSubject
{
    protected $table = 'DBFLPASS';
    protected $primaryKey = 'USERID';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['USERID', 'UID', 'PASSWORD', 'UID2', 'FullName', 'TINGKAT', 'STATUS', 'HOSTID', 'IPAddres', 'kodeBag', 'KodeJab', 'KodeKasir', 'Kodegdg', 'keynik', 'UID2', 'role', 'department_code'];
    protected $casts = ['TINGKAT' => 'integer', 'STATUS' => 'integer', 'keynik' => 'integer'];
    protected $hidden = ['UID2'];

    public function getAuthPassword()
    {
        return $this->UID2;
    }

    public function getAuthIdentifierName()
    {
        return 'USERID';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'USERID' => $this->USERID,
            'FullName' => $this->FullName,
            'TINGKAT' => $this->TINGKAT,
            'STATUS' => $this->STATUS,
            'csrf_token' => Str::random(40)
        ];
    }
}
