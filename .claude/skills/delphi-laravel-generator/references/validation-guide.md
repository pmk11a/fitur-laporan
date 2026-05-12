# Request Validation Guide

Generate Laravel FormRequest validation classes from Delphi Cek* functions.

## Table of Contents

- [Overview](#overview)
- [FormRequest Template](#formrequest-template)
- [Delphi Cek* to Laravel Rules](#delphi-cek-to-laravel-rules)
  - [CekKosong → required](#cekkosong--required)
  - [CekAngka → numeric](#cekangka--numeric)
  - [CekTanggal → date](#cektanggal--date)
  - [CekPeriode → exists + date](#cekperiode--exists--date)
  - [CekExist → exists](#cekexist--exists)
- [Common Validation Patterns](#common-validation-patterns)
- [Error Messages (Bahasa Indonesia)](#error-messages-bahasa-indonesia)
- [Attribute Names (Bahasa Indonesia)](#attribute-names-bahasa-indonesia)
- [Example: StorePengajuanRequest](#example-storepengajuanrequest)
- [Custom Validation Rules](#custom-validation-rules)

---

## Overview

Validation rules are extracted from Delphi validation functions:
- `CekKosong` → `required`
- `CekAngka` → `numeric`
- `CekTanggal` → `date`
- `CekPeriode` → `exists`
- `CekExist` → `exists`

## FormRequest Template

```php
<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Store{{ModelName}} Request
 *
 * Generated from: {{DelphiForm}}
 * Validation rules extracted from Cek* functions
 */
class Store{{ModelName}}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * From Delphi: CekKosong, CekAngka, CekTanggal, CekPeriode, etc.
     */
    public function rules(): array
    {
        return [
            {{ValidationRules}}
        ];
    }

    /**
     * Get custom error messages for validator
     */
    public function messages(): array
    {
        return [
            {{ErrorMessages}}
        ];
    }

    /**
     * Get custom attributes for validator
     */
    public function attributes(): array
    {
        return [
            {{AttributeNames}}
        ];
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    // =========================================================================
    // CUSTOM VALIDATION (From Delphi business logic)
    // =========================================================================

    {{CustomValidationMethods}}
}
```

## Delphi Cek* to Laravel Rules

### CekKosong → required

**Delphi:**
```delphi
function CekKosong(Data: String): Boolean;
begin
  Result := Trim(Data) = '';
end;

if CekKosong(KodeCust.Text) then
  ShowMessage('Kode Customer harus diisi');
```

**Laravel:**
```php
'kode_cust' => 'required|string'
```

### CekAngka → numeric

**Delphi:**
```delphi
function CekAngka(Data: String): Boolean;
var
  nilai: Real;
begin
  Result := TryStrToFloat(Data, nilai);
end;
```

**Laravel:**
```php
'pinjaman' => 'required|numeric'
```

### CekTanggal → date

**Delphi:**
```delphi
function CekTanggal(Data: String): Boolean;
begin
  try
    StrToDate(Data);
    Result := True;
  except
    Result := False;
  end;
end;
```

**Laravel:**
```php
'tanggal' => 'required|date'
```

### CekPeriode → exists + date

**Delphi:**
```delphi
function CekPeriode(Nama:string;tgl:Tdatetime):Boolean;
begin
  with DM.QuCari do
  begin
    SQL.Add('SELECT * FROM dbPeriode WHERE UserID = :0 AND Tgl = :1');
    Open;
    Result := RecordCount > 0;
  end;
end;
```

**Laravel:**
```php
'tanggal' => 'required|date|exists:dbperiode,Tgl'
```

### CekExist → exists

**Delphi:**
```delphi
function CekCustomer(Kode: String): Boolean;
begin
  with DM.QuCari do
  begin
    SQL.Add('SELECT * FROM dbCustomer WHERE KodeCust = :0');
    Open;
    Result := not IsEmpty;
  end;
end;
```

**Laravel:**
```php
'kode_cust' => 'required|exists:dbcustomer,KodeCust'
```

## Common Validation Patterns

| Delphi Pattern | Laravel Rule | Notes |
|----------------|--------------|-------|
| CekKosong | required | Field must be present |
| CekAngka | numeric | Must be numeric |
| CekTanggal | date | Valid date format |
| CekPeriode | exists:table,column | Must exist in database |
| CekMin | min:value | Minimum value |
| CekMax | max:value | Maximum value |
| CekEmail | email | Valid email format |
| CekUnique | unique:table,column | Must be unique |

## Error Messages (Bahasa Indonesia)

```php
public function messages(): array
{
    return [
        'required' => ':attribute harus diisi',
        'numeric' => ':attribute harus berupa angka',
        'date' => ':attribute harus berupa tanggal yang valid',
        'after_or_equal' => ':attribute tidak boleh mundur',
        'min' => ':attribute minimum :min',
        'max' => ':attribute maksimum :max',
        'exists' => ':attribute tidak ditemukan',
        'unique' => ':attribute sudah ada',
        'email' => ':attribute harus berupa email yang valid',
        'in' => ':attribute harus salah satu dari: :values',
    ];
}
```

## Attribute Names (Bahasa Indonesia)

```php
public function attributes(): array
{
    return [
        'kode_cust' => 'Kode Customer',
        'kode_prd' => 'Kode Produk',
        'kode_area' => 'Kode Area',
        'tanggal' => 'Tanggal',
        'no_bukti' => 'Nomor Bukti',
        'pinjaman' => 'Jumlah Pinjaman',
        'tenor' => 'Tenor',
        'bunga_persen' => 'Bunga (%)',
        'biaya_admin' => 'Biaya Admin',
        'nama' => 'Nama',
        'alamat' => 'Alamat',
        'telepon' => 'Telepon',
        'hp' => 'No. HP',
    ];
}
```

## Example: StorePengajuanRequest

```php
<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // From CekKosong validation
            'kode_cust' => 'required|string|exists:dbcustomer,KodeCust',
            'kode_prd' => 'required|string|exists:dbproduk,KodePrd',

            // From CekPeriode validation
            'tanggal' => 'required|date|after_or_equal:today',

            // From CekAngka validation
            'pinjaman' => 'required|numeric|min:500000|max:1000000000',
            'tenor' => 'required|integer|min:1|max:60',
            'bunga_persen' => 'sometimes|numeric|min:0|max:100',

            // From jaminan validations
            'no_pol' => 'required|string',
            'no_rangka' => 'required|string',
            'no_mesin' => 'required|string',
            'bpkb' => 'required|string',

            // Optional fields
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_cust.required' => 'Kode Customer harus diisi',
            'kode_cust.exists' => 'Kode Customer tidak ditemukan',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh mundur',
            'pinjaman.min' => 'Pinjaman minimum Rp 500.000',
            'pinjaman.max' => 'Pinjaman maksimum Rp 1.000.000.000',
            'tenor.min' => 'Tenor minimum 1 bulan',
            'tenor.max' => 'Tenor maksimum 60 bulan',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_cust' => 'Kode Customer',
            'kode_prd' => 'Kode Produk',
            'tanggal' => 'Tanggal',
            'pinjaman' => 'Jumlah Pinjaman',
            'tenor' => 'Tenor',
            'bunga_persen' => 'Bunga (%)',
            'no_pol' => 'Nomor Polisi',
            'no_rangka' => 'Nomor Rangka',
            'no_mesin' => 'Nomor Mesin',
            'bpkb' => 'Nomor BPKB',
        ];
    }
}
```

## Custom Validation Rules

For complex business logic from Delphi:

```php
// In FormRequest
protected function prepareForValidation()
{
    // Auto-populate fields from Delphi logic
    if ($this->has('pinjaman') && $this->has('tenor')) {
        $this->merge([
            'angsuran' => $this->calculateAngsuran(
                $this->pinjaman,
                $this->tenor
            )
        ]);
    }
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Custom validation from Delphi CekPeriode
        if ($this->has('tanggal')) {
            $periodService = app(PeriodService::class);
            if ($periodService->isLocked($this->tanggal)) {
                $validator->errors()->add('tanggal', 'Periode terkunci');
            }
        }
    });
}
```

## See Also

- `mappings.md` - Function mappings
- `service-guide.md` - Service layer
