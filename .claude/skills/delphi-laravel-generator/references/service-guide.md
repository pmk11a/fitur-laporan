# Service Generation Guide

Generate Laravel service classes from Delphi business logic.

## Table of Contents

- [Overview](#overview)
- [Service Template](#service-template)
- [Delphi to Laravel Service Mapping](#delphi-to-laravel-service-mapping)
  - [CekPeriode → validatePeriode](#cekperiode--validateperiode)
  - [Check_Nomor → generateNumber](#check_nomor--generatenumber)
  - [LoggingData → logActivity](#loggingdata--logactivity)
  - [RateOfInterest → Model Method](#rateofinterest--model-method)
- [Dependency Injection](#dependency-injection)
- [Transaction Management](#transaction-management)
- [Error Handling](#error-handling)

---

## Overview

Services contain business logic extracted from Delphi procedures. Services:
- Handle business rules
- Manage database operations
- Coordinate between models
- Provide validation beyond FormRequest

## Service Template

```php
<?php

namespace App\Services;

use App\Models\{{ModelName}};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * {{ModelName}} Service
 *
 * Generated from: {{DelphiForm}}
 */
class {{ModelName}}Service
{
    {{ConstructorDependencies}}

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    public function create(array $data): {{ModelName}}
    {
        {{Validations}}

        return DB::transaction(function () use ($data) {
            {{NumberGeneration}}

            ${{ModelNameLower}} = {{ModelName}}::create($data);

            {{RelatedDataCreation}}

            {{LoggingActivity}}

            return ${{ModelNameLower}};
        });
    }

    public function update({{ModelName}} ${{ModelNameLower}}, array $data): {{ModelName}}
    {
        {{UpdateValidations}}

        return DB::transaction(function () use (${{ModelNameLower}}, $data) {
            ${{ModelNameLower}}->update($data);

            {{RelatedDataUpdate}}

            {{LoggingActivityUpdate}}

            return ${{ModelNameLower}};
        });
    }

    public function delete({{ModelName}} ${{ModelNameLower}}): bool
    {
        {{DeleteValidation}}

        return DB::transaction(function () use (${{ModelNameLower}}) {
            {{RelatedDataDeletion}}

            {{LoggingActivityDelete}}

            return ${{ModelNameLower}}->delete();
        });
    }

    // =========================================================================
    // VALIDATION METHODS (From Cek* functions)
    // =========================================================================

    {{ValidationMethods}}

    // =========================================================================
    // BUSINESS LOGIC METHODS (From Delphi procedures)
    // =========================================================================

    {{BusinessLogicMethods}}

    // =========================================================================
    // NUMBER GENERATION
    // =========================================================================

    {{NumberGenerationMethods}}

    // =========================================================================
    // LOGGING
    // =========================================================================

    private function logActivity(
        string $action,
        string $source,
        string $noBukti,
        ?string $keterangan = null
    ): void {
        DB::table('dbLogFile')->insert([
            'Tahun' => Carbon::now()->year,
            'Bulan' => Carbon::now()->month,
            'Tanggal' => Carbon::now(),
            'Pemakai' => auth()->id() ?? 'system',
            'Aktivitas' => $action,
            'Sumber' => $source,
            'NoBukti' => $noBukti,
            'Keterangan' => $keterangan,
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    {{HelperMethods}}
}
```

## Delphi to Laravel Service Mapping

### CekPeriode → validatePeriode

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

**Laravel Service:**
```php
public function validatePeriode(string $nama, Carbon $tanggal): bool
{
    $exists = Periode::where('UserID', $nama)
        ->where('Tgl', $tanggal->format('Y-m-d'))
        ->exists();

    if (!$exists) {
        throw new Exception('Periode tidak ditemukan');
    }

    return true;
}
```

### Check_Nomor → generateNumber

**Delphi:**
```delphi
function Check_Nomor(Bulan,Tahun:integer;Tipe:String;...):Boolean;
begin
  with DM.QuCari do
  begin
    SQL.Add('SELECT Nomor FROM dbNomorPK WHERE Tipe = :0');
    Open;
    if IsEmpty then
      Nomor := '0001'
    else
      Nomor := IntToStr(StrToInt(Nomor) + 1);
  end;
end;
```

**Laravel Service:**
```php
public function generateNumber(string $tipe, int $bulan, int $tahun): string
{
    return DB::transaction(function () use ($tipe, $bulan, $tahun) {
        $record = NomorPk::where('Tipe', $tipe)
            ->where('Bulan', $bulan)
            ->where('Tahun', $tahun)
            ->lockForUpdate()
            ->first();

        if (!$record) {
            $nomor = '0001';
            NomorPk::create([
                'Tipe' => $tipe,
                'Bulan' => $bulan,
                'Tahun' => $tahun,
                'Nomor' => $nomor,
            ]);
        } else {
            $nomor = str_pad((int)$record->Nomor + 1, 4, '0', STR_PAD_LEFT);
            $record->update(['Nomor' => $nomor]);
        }

        return $tipe . $bulan . $tahun . $nomor;
    });
}
```

### LoggingData → logActivity

**Delphi:**
```delphi
Procedure LoggingData(pPemakai, pAktivitas, pSumber, pNoBukti, pKeterangan: String);
begin
  With DM.QuLogFile do
  begin
    SQL.Add('Insert into dbLogFile (Tahun, Bulan, Tanggal, Pemakai, Aktivitas, Sumber, NoBukti, Keterangan)');
    SQL.Add('values (:tahun, :bulan, Getdate(), :pemakai, :aktivitas, :sumber, :nobukti, :ket)');
    ExecSQL;
  end;
end;
```

**Laravel Service:**
```php
private function logActivity(
    string $action,
    string $source,
    string $noBukti,
    ?string $keterangan = null
): void {
    DB::table('dbLogFile')->insert([
        'Tahun' => Carbon::now()->year,
        'Bulan' => Carbon::now()->month,
        'Tanggal' => Carbon::now(),
        'Pemakai' => auth()->id() ?? 'system',
        'Aktivitas' => $action,
        'Sumber' => $source,
        'NoBukti' => $noBukti,
        'Keterangan' => $keterangan,
    ]);
}
```

### RateOfInterest → Model Method

**Delphi:**
```delphi
function RateOfInterest(Pinjaman,Angsuran:Real;Tenor:Integer):Real;
var
  pokok, bunga: Real;
begin
  pokok := Pinjaman / Tenor;
  bunga := (Pinjaman * 0.12) / 12;
  Result := pokok + bunga;
end;
```

**Laravel (in Model):**
```php
public function calculateAngsuran(): float
{
    $pokok = $this->pinjaman / $this->tenor;
    $bunga = ($this->pinjaman * $this->bunga_persen / 100) / 12;
    return $pokok + $bunga;
}
```

## Dependency Injection

Services should inject dependencies via constructor:

```php
public function __construct(
    private PeriodLockService $periodLock,
    private NumberSequenceService $numberSequence,
    private ActivityLogService $activityLog
) {}
```

## Transaction Management

Always wrap related operations in transactions:

```php
return DB::transaction(function () use ($data) {
    // All operations here are atomic
    $pengajuan = Pengajuan::create($data);
    $jaminan = Jaminan::create([...]);
    Angsuran::create([...]);

    return $pengajuan;
});
```

## Error Handling

```php
try {
    $result = $this->create($data);
} catch (ValidationException $e) {
    throw $e;
} catch (Exception $e) {
    Log::error('Service error', [
        'service' => get_class($this),
        'error' => $e->getMessage()
    ]);
    throw $e;
}
```

## See Also

- `mappings.md` - Function mappings
- `model-guide.md` - Model generation
- `validation-guide.md` - Request validation
