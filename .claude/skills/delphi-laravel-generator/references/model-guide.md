# Model Generation Guide

Generate Laravel Eloquent models from existing SQL Server database.

## Table of Contents

- [Overview](#overview)
- [Generation Steps](#generation-steps)
  - [1. Read Schema](#1-read-schema)
  - [2. Detect Model Properties](#2-detect-model-properties)
  - [3. Generate Model](#3-generate-model)
- [Model Template](#model-template)
- [Relationship Detection](#relationship-detection)
  - [Auto-Detection Rules](#auto-detection-rules)
  - [From Foreign Keys](#from-foreign-keys)
- [Cast Mapping](#cast-mapping)
- [Example: Pengajuan Model](#example-pengajuan-model)
- [Naming Convention](#naming-convention)
- [Special Cases](#special-cases)

---

## Overview

Models are generated from **existing database schema**, not created from scratch.

## Generation Steps

### 1. Read Schema

First, run database schema reader:
```bash
cd scripts && read_schema.bat
```

Output: `database_schema.json` with table structures.

### 2. Detect Model Properties

From schema, determine:
- **Table name**: Use actual table name from database
- **Primary key**: Auto-detected from schema
- **Fillable fields**: All columns except auto-increment ID
- **Casts**: Based on column data types
- **Relationships**: From foreign keys

### 3. Generate Model

## Model Template

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * {{ModelName}} Model
 *
 * @property {{PrimaryKeyType}} ${{PrimaryKeySnake}}
 */
class {{ModelName}} extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = '{{TableName}}';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = '{{PrimaryKey}}';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = {{AutoIncrement}};

    /**
     * The data type of the primary key.
     */
    protected $keyType = '{{KeyType}}'; // 'int' or 'string'

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        {{FillableFields}}
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        {{Casts}}
    ];

    /**
     * The attributes that should be mutated to dates.
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
}
```

## Relationship Detection

### Auto-Detection Rules

| Pattern | Relationship | Example |
|---------|-------------|---------|
| Column ends with `_id` | belongsTo | `customer_id` → belongsTo(Customer::class) |
| Column starts with `Kode` | belongsTo | `KodeCust` → belongsTo(Customer::class, 'KodeCust') |
| Table has many child records | hasMany | `Pengajuan` hasMany `Angsuran` |
| Pivot table | belongsToMany | User belongsToMany Role |

### From Foreign Keys

```php
// Single column foreign key
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class, 'KodeCust', 'KodeCust');
}

// Composite foreign key (rare)
public function jaminan(): HasMany
{
    return $this->hasMany(Jaminan::class, 'NoBukti', 'NoBukti');
}
```

## Cast Mapping

| SQL Server Type | Laravel Cast |
|-----------------|--------------|
| varchar, char | string (no cast needed) |
| int, smallint | integer |
| bigint | bigInteger |
| decimal, numeric | decimal:2 |
| float, real | float |
| date | date |
| datetime, datetime2 | datetime |
| bit, boolean | boolean |
| timestamp | timestamp |

## Example: Pengajuan Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajuan extends Model
{
    protected $table = 'dbpengajuan';

    protected $primaryKey = 'NoBukti';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_bukti',
        'tanggal',
        'tanggal_realisasi',
        'kode_cust',
        'kode_prd',
        'pinjaman',
        'tenor',
        'bunga_persen',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_realisasi' => 'date',
        'pinjaman' => 'decimal:2',
        'bunga_persen' => 'decimal:2',
        'status' => 'string',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'KodeCust', 'KodeCust');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'KodePrd', 'KodePrd');
    }

    public function angsuran(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'NoBukti', 'NoBukti');
    }

    public function jaminan(): HasMany
    {
        return $this->hasMany(Jaminan::class, 'NoBukti', 'NoBukti');
    }

    // Business Logic from Delphi: RateOfInterest
    public function calculateAngsuran(): float
    {
        $pokok = $this->pinjaman / $this->tenor;
        $bunga = ($this->pinjaman * $this->bunga_persen / 100) / 12;
        return $pokok + $bunga;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
}
```

## Naming Convention

| Table Name | Model Name | Notes |
|------------|------------|-------|
| dbpengajuan | Pengajuan | Remove 'db' prefix |
| dbcustomer | Customer | Remove 'db' prefix |
| dbangsuran | Angsuran | Remove 'db' prefix, singular |
| dbperkiraan | Perkiraan | Remove 'db' prefix |
| users | User | Laravel default |
| dbuser | User | Remove 'db' prefix |

## Special Cases

### Composite Primary Key

```php
protected $primaryKey = ['id1', 'id2']; // Not well supported, use single key
```

### No Primary Key

```php
public $primaryKey = null; // Use incrementing ID
```

### Custom Timestamps

```php
const CREATED_AT = 'Dibuat';
const UPDATED_AT = 'Diupdate';
public $timestamps = false; // Disable timestamps
```

## See Also

- `types.md` - Type mappings
- `mappings.md` - Function mappings
- `service-guide.md` - Service layer
