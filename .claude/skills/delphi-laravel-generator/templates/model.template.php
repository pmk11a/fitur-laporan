<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * {{ModelName}} Model
 *
 * Generated from: {{DelphiForm}}
 * Table: {{TableName}}
 * Primary Key: {{PrimaryKey}}
 *
 * ⚠️ GOTCHA: SQL Server columns are UPPERCASE!
 * When querying, use ->where('ColumnName', ...) NOT ->where('columnname', ...)
 *
 * @property {{PrimaryKeyType}} ${{PrimaryKeySnake}}
 */
class {{ModelName}} extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '{{TableName}}';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '{{PrimaryKey}}';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = {{AutoIncrement}};

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = '{{KeyType}}';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        {{FillableFields}}
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        {{Casts}}
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        {{Dates}}
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    {{Relationships}}

    // =========================================================================
    // BUSINESS LOGIC (From Delphi)
    // =========================================================================

    {{BusinessLogicMethods}}

    // =========================================================================
    // SCOPES
    // =========================================================================

    {{Scopes}}

    // =========================================================================
    // ACCESSORS & MUTATORS
    // =========================================================================

    {{AccessorsMutators}}
}
