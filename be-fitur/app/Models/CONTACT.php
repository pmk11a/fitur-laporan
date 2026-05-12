<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CONTACT extends Model
{
    protected $table = 'DBCONTACT';
    protected $fillable = ['CONTACTID', 'KODECUSTSUPP', 'TITLE', 'FIRSTNAME', 'MIDDLENAME', 'LASTNAME', 'JOBTITLE', 'COMPANY', 'PHONETYPE1', 'PHONE1', 'PHONETYPE2', 'PHONE2', 'PHONETYPE3', 'PHONE3', 'PHONETYPE4', 'PHONE4', 'ALAMAT', 'EMAIL', 'DEPARTEMEN', 'BIRTHDAY', 'ANNIVERSARY', 'PHOTO', 'MyID'];
    protected $casts = ['CONTACTID' => 'integer'];
}
