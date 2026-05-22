<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabelGrup extends Model
{
    protected $table = 'dbLabelGrup';

    protected $fillable = [
        'field_name',
        'field_value',
        'label',
        'aktif',
        'sort_order',
    ];

    public $timestamps = false;

    public static function getMapping(string $fieldName): array
    {
        $labels = static::where('field_name', $fieldName)
            ->where('aktif', 1)
            ->orderBy('sort_order')
            ->pluck('label', 'field_value')
            ->toArray();

        return $labels;
    }
}