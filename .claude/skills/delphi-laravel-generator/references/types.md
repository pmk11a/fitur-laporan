# Type Mappings

Delphi to Laravel type mappings for code generation.

## Table of Contents

- [Delphi to PHP Types](#delphi-to-php-types)
- [ADO Field Types to Laravel](#ado-field-types-to-laravel)
- [Field Pattern to Laravel Type](#field-pattern-to-laravel-type)
- [PHP to Laravel Casts](#php-to-laravel-casts)
- [Usage in Model Generation](#usage-in-model-generation)
- [Usage in Migration](#usage-in-migration)

---

## Delphi to PHP Types

| Delphi Type | PHP Type | Laravel Cast | Notes |
|-------------|----------|--------------|-------|
| String | string | - | Variable length text |
| AnsiString | string | - | ANSI text |
| Integer | int | - | 32-bit integer |
| Smallint | int | - | 16-bit integer |
| LongWord | int | - | 32-bit unsigned |
| Int64 | int | - | 64-bit integer |
| Real | float | - | Floating point |
| Double | float | - | Double precision |
| Extended | float | - | Extended precision |
| Single | float | - | Single precision |
| Currency | decimal | decimal:2 | Financial (4 decimal) |
| TDateTime | string | datetime | Date + time |
| Date | string | date | Date only |
| Time | string | - | Time only |
| Boolean | bool | boolean | True/False |
| Word | int | - | 16-bit unsigned |
| Byte | int | - | 8-bit unsigned |
| Variant | mixed | - | Can be any type |

## ADO Field Types to Laravel

| ADO Field Type | PHP Type | Laravel Cast | Notes |
|----------------|----------|--------------|-------|
| TBCDField | decimal | decimal:2 | Binary Coded Decimal |
| TStringField | string | - | String field |
| TIntegerField | int | integer | Integer field |
| TFloatField | float | float | Float field |
| TDateTimeField | string | datetime | DateTime field |
| TBooleanField | bool | boolean | Boolean field |
| TMemoField | string | - | Memo/text field |
| TBlobField | binary | - | Blob/binary data |
| TAutoIncField | int | integer | Auto-increment |

## Field Pattern to Laravel Type

| Pattern (Field Name) | Laravel Migration Type | Example |
|----------------------|------------------------|---------|
| Kode.* | string(20) | kode_cust |
| Kode_.* | string(20) | kode_area |
| Nama.* | string(255) | nama_cust |
| Alamat.* | text | alamat |
| Telpon | string(50) | telpon |
| HP | string(30) | hp |
| Fax | string(30) | fax |
| Handphone | string(30) | handphone |
| Tanggal | date | tanggal |
| Tgl.* | date | tgl_realisasi |
| TglRealisasi | datetime | tgl_realisasi |
| Waktu | time | waktu |
| Jam | time | jam |
| NoBukti | string(50) | no_bukti |
| No_.* | string(50) | no_polisi |
| Nomor | int | nomor |
| NoUrut | int | no_urut |
| Nilai | decimal(15,2) | nilai |
| Pinjaman | decimal(15,2) | pinjaman |
| Bunga | decimal(15,2) | bunga |
| Biaya | decimal(15,2) | biaya |
| Rp_.* | decimal(15,2) | rp_admin |
| Denda | decimal(15,2) | denda |
| Status | tinyint | status |
| Keterangan | text | keterangan |
| Catatan | text | catatan |
| .*_id | unsignedBigInteger | customer_id |
| ID | unsignedBigInteger | id |
| Tahun | int | tahun |
| Bulan | tinyint | bulan |
| Divisi | string(10) | divisi |
| Devisi | string(10) | devisi |
| User | string(50) | user |
| UserID | string(50) | user_id |
| Pemakai | string(50) | pemakai |

## PHP to Laravel Casts

| PHP Type | Laravel Cast | Example |
|----------|--------------|---------|
| decimal | decimal:2 | Financial values |
| boolean | boolean | True/False |
| datetime | datetime | Carbon instance |
| date | date | Carbon date only |
| timestamp | timestamp | Carbon timestamp |
| array | array | Serialized array |
| object | object | Serialized object |
| collection | collection | Laravel Collection |
| encrypted | encrypted | Encrypted storage |
| hashed | hashed | Hashed value (one-way) |

## Usage in Model Generation

```php
protected $casts = [
    'tanggal' => 'date',
    'pinjaman' => 'decimal:2',
    'bunga_persen' => 'decimal:2',
    'is_active' => 'boolean',
    'created_at' => 'datetime',
];
```

## Usage in Migration

```php
$table->string('kode_cust', 20);
$table->string('nama_cust', 255);
$table->text('alamat');
$table->date('tanggal');
$table->decimal('pinjaman', 15, 2);
$table->tinyint('status');
```
