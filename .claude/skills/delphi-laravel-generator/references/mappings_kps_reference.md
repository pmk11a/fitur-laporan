# Function Mappings

Mapping Delphi functions/procedures to Laravel equivalents.

> **Note:** Keu-app uses database `dbwbcp2` with Delphi source in `pwt/` folder.
> KSP-specific examples preserved in `mappings_kps_reference.md`.

## Table of Contents

- [Delphi Function → Laravel Location](#delphi-function--laravel-location)
- [Database Operation Mappings](#database-operation-mappings)
  - [TADOQuery INSERT → Laravel](#tadoquery-insert--laravel)
  - [TADOQuery SELECT → Laravel](#tadoquery-select--laravel)
  - [TADOQuery UPDATE → Laravel](#tadoquery-update--laravel)
  - [TADOQuery DELETE → Laravel](#tadoquery-delete--laravel)
- [Validation Function Mappings](#validation-function-mappings)
  - [CekKosong (Required)](#cekkosong-required)
  - [CekAngka (Numeric)](#cekangka-numeric)
  - [CekPeriode (Date + Exists)](#cekperiode-date--exists)
- [Naming Convention Mappings](#naming-convention-mappings)
- [Event Handler Mappings (Skip - Frontend Only)](#event-handler-mappings-skip---frontend-only)
- [Helper Function Mappings](#helper-function-mappings)
  - [Number Generation](#number-generation)
  - [Logging](#logging)
  - [Format Conversion](#format-conversion)

---

## Delphi Function → Laravel Location

| Delphi Pattern | Laravel Location | Method | Example |
|----------------|------------------|--------|---------|
| `CekPeriode()` | Request + Service | `rules()` + `validatePeriode()` | Validation |
| `Check_Nomor()` | Service | `generateNumber()` | Number generation |
| `Check_NoUrut()` | Service | `generateNumber()` | Sequence generation |
| `LoggingData()` | Service | `logActivity()` | Audit trail |
| `RateOfInterest()` | Model | `calculateAngsuran()` | Business calculation |
| `IsLockPeriode()` | Service | `isLocked()` | State check |
| `IsPeriodeSudahAda()` | Service | `periodExists()` | Existence check |
| `CekDeletePO()` | Service | `canDelete()` | Delete validation |
| `CekKosong()` | Request | `required` rule | Required validation |
| `CekAngka()` | Request | `numeric` rule | Numeric validation |
| `CekTanggal()` | Request | `date` rule | Date validation |
| `CekExist()` | Request | `exists` rule | Existence validation |
| `MyFindField()` | Query Builder | `where()` | Database query |
| `MyCariUserName()` | Auth | `User::where()` | User lookup |
| `MyAktifTgl()` | Service | `getActiveDate()` | Date utility |
| `BulanRomawi()` | Helper | `bulanRomawi()` | Format utility |
| `Romawi()` | Helper | `toRoman()` | Format utility |
| `TextKonversi()` | Helper | `numberToText()` | Number to words |
| `NoUrutKas()` | Service | `generateKasNumber()` | Cash number |
| `NoUrutRLS()` | Service | `generateRlsNumber()` | RLS number |
| `DataBersyarat()` | Query Builder | `where()` | Parameterized query |
| `UrutField()` | Query Builder | `orderBy()` | Sorting |
| `Hidupkan()` | Frontend | - | Enable UI |
| `Matikan()` | Frontend | - | Disable UI |

## Database Operation Mappings

### TADOQuery INSERT → Laravel

**Delphi:**
```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('INSERT INTO dbPengajuan (NoBukti, Tanggal, KodeCust)');
  SQL.Add('VALUES (:0, :1, :2)');
  Parameters[0].Value := NoBukti.Text;
  Parameters[1].Value := Tanggal.Date;
  Parameters[2].Value := KodeCust.Text;
  ExecSQL;
end;
```

**Laravel Service:**
```php
public function create(array $data): Pengajuan
{
    return DB::transaction(function () use ($data) {
        return Pengajuan::create([
            'no_bukti' => $data['no_bukti'],
            'tanggal' => Carbon::parse($data['tanggal']),
            'kode_cust' => $data['kode_cust'],
        ]);
    });
}
```

### TADOQuery SELECT → Laravel

**Delphi:**
```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('SELECT * FROM dbPengajuan WHERE NoBukti = :0');
  Parameters[0].Value := NoBukti;
  Open;
  Result := not IsEmpty;
end;
```

**Laravel Service:**
```php
public function findByNoBukti(string $noBukti): ?Pengajuan
{
    return Pengajuan::where('no_bukti', $noBukti)->first();
}
```

### TADOQuery UPDATE → Laravel

**Delphi:**
```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('UPDATE dbPengajuan SET Status = :0 WHERE NoBukti = :1');
  Parameters[0].Value := 'APPROVED';
  Parameters[1].Value := NoBukti;
  ExecSQL;
end;
```

**Laravel Service:**
```php
public function approve(string $noBukti): void
{
    Pengajuan::where('no_bukti', $noBukti)->update([
        'status' => 'APPROVED'
    ]);
}
```

### TADOQuery DELETE → Laravel

**Delphi:**
```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('DELETE FROM dbPengajuan WHERE NoBukti = :0');
  Parameters[0].Value := NoBukti;
  ExecSQL;
end;
```

**Laravel Service:**
```php
public function delete(string $noBukti): void
{
    Pengajuan::where('no_bukti', $noBukti)->delete();
}
```

## Validation Function Mappings

### CekKosong (Required)

**Delphi:**
```delphi
function CekKosong(Data: String): Boolean;
begin
  Result := Trim(Data) = '';
end;
```

**Laravel Request:**
```php
'kode_cust' => 'required|string'
```

**Laravel Service:**
```php
private function validateNotEmpty(string $value): void
{
    if (empty(trim($value))) {
        throw new \Exception('Data tidak boleh kosong');
    }
}
```

### CekAngka (Numeric)

**Delphi:**
```delphi
function CekAngka(Data: String): Boolean;
var
  nilai: Real;
begin
  Result := not TryStrToFloat(Data, nilai);
end;
```

**Laravel Request:**
```php
'pinjaman' => 'required|numeric|min:0'
```

### CekPeriode (Date + Exists)

**Delphi:**
```delphi
function CekPeriode(Nama:string;tgl:Tdatetime):Boolean;
begin
  with DM.QuCari do
  begin
    Close;
    SQL.Clear;
    SQL.Add('SELECT * FROM dbPeriode WHERE UserID = :0 AND Tgl = :1');
    Parameters[0].Value := Nama;
    Parameters[1].Value := tgl;
    Open;
    Result := RecordCount > 0;
  end;
end;
```

**Laravel Request:**
```php
'tanggal' => 'required|date|after_or_equal:today'
```

**Laravel Service:**
```php
public function validatePeriode(string $nama, Carbon $tanggal): bool
{
    $exists = Periode::where('user_id', $nama)
        ->where('tgl', $tanggal->format('Y-m-d'))
        ->exists();

    if (!$exists) {
        throw new \Exception('Periode tidak valid');
    }

    return true;
}
```

## Naming Convention Mappings

| Delphi | Laravel | Notes |
|--------|---------|-------|
| FrmBarang | BarangController | Remove "Frm" prefix |
| FrmCustomer | CustomerController | Remove "Frm" prefix |
| MyProcedure | UtilityService | "My" prefix = utility |
| MyGlobal | - | Global state (avoid) |
| MyModul | - | Module constants (use config) |
| CekPeriode | validatePeriode | "Cek" → "validate" |
| Check_Nomor | generateNumber / checkNumber | Based on context |
| IsLocked | isLocked | "Is" prefix remains |
| CanDelete | canDelete | "Can" prefix remains |
| GetData | getData / index | Data retrieval |
| SaveData | save / store | Data persistence |
| DeleteData | delete / destroy | Data deletion |
| TampilData | show / detail | Display single item |

## Event Handler Mappings (Skip - Frontend Only)

| Delphi Event | Laravel Equivalent | Action |
|--------------|-------------------|--------|
| FormShow | - | Skip - Frontend (React state) |
| FormClose | - | Skip - Frontend (unmount) |
| FormCreate | - | Skip - Frontend (component mount) |
| FormDestroy | - | Skip - Frontend (component unmount) |
| btTambahClick | store() | Button → Controller method |
| btEditClick | update() | Button → Controller method |
| btHapusClick | destroy() | Button → Controller method |
| btSimpanClick | store() or update() | Save button |
| btBatalClick | - | Skip - Frontend (cancel) |
| Enter | - | Skip - Frontend (form submit) |
| Exit | - | Skip - Frontend (field blur) |
| KeyDown | - | Skip - Frontend (keyboard) |
| Click | - | Skip - Frontend (click handler) |

## Helper Function Mappings

### Number Generation

**Delphi:**
```delphi
function Check_Nomor(Bulan,Tahun:integer;Tipe:String;...):Boolean;
```

**Laravel:**
```php
// In NumberSequenceService.php
public function generate(string $tipe, int $bulan, int $tahun): string
{
    $record = NomorPk::where('tipe', $tipe)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->lockForUpdate()
        ->first();

    // Generate logic...
}
```

### Logging

**Delphi:**
```delphi
Procedure LoggingData(pPemakai, pAktivitas, pSumber, pNoBukti, pKeterangan: String);
```

**Laravel:**
```php
// In Service or use Laravel Log facade
private function logActivity(string $action, string $source, string $noBukti): void
{
    DB::table('dbLogFile')->insert([
        'Tanggal' => Carbon::now(),
        'Pemakai' => auth()->id(),
        'Aktivitas' => $action,
        'Sumber' => $source,
        'NoBukti' => $noBukti,
    ]);
}
```

### Format Conversion

**Delphi:**
```delphi
function BulanRomawi(Tanggal:Tdatetime;Mode:String):String;
```

**Laravel:**
```php
// In Helper class
function bulanRomawi(Carbon $tanggal): string
{
    $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI',
               'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romawi[$tanggal->month - 1];
}
```

## Form Reference Patterns

### Detection Patterns

| Pattern | Form Name | File Location Strategy | Example |
|---------|-----------|------------------------|---------|
| `Application.CreateForm(TFrMenuReport, ...)` | FrMenuReport | Search: `**/FrMenuReport.pas` | Opens modal form |
| `with FrSubMenu do` | FrSubMenu | Search: `**/FrSubMenu.pas` | Direct form access |
| `FrmXxx.ShowModal` | FrmXxx | Search: `**/FrmXxx.pas` | Modal show |
| `uses FrmMenuReport` | FrmMenuReport | Check in `uses` clause | Unit reference |
| `sp_updateMenuReport.ExecProc` | (SP Name) | Check `config/sp_mappings.json` | Stored procedure |

### Cross-Module Scanning Workflow

When form references are detected:

1. **Search entire codebase:**
   ```
   Glob.find('**/ReferencedForm.pas')
   ```

2. **Parse the referenced form:**
   - Extract all `TADOQuery` components
   - Find SQL statements (SELECT, INSERT, UPDATE, DELETE)
   - Identify stored procedure calls
   - List related tables

3. **Merge findings into parent form:**
   - Add related tables to parent's table mapping
   - Generate additional controller methods
   - Inject additional services if needed

### Example: FrmPemakai → FrMenuReport

**In FrmPemakai.pas:**
```delphi
procedure TFrPemakai.ToolButton12Click(Sender: TObject);
begin
  with sp_updateMenuReport do
  begin
     Close;
     Parameters[1].Value:=QuView.fieldbyname('userid').AsString;
     ExecProc;
  end;
  Application.CreateForm(TFrMenuReport, FrMenuReport);
  FrMenuReport.Showmodal;
end;
```

**Detection:**
- Pattern: `Application.CreateForm(TFrmBarang, ...)`
- Find: `pwt/Master/Barang/FrmBarang.pas`

**In FrmBarang.pas (found):**
```delphi
SQL.Add('Select * from BARANG where KODE=:0');
SQL.Add('Select * from SATUAN where KODESAT=:0');
```

**Generate in BarangController:**
```php
public function __construct(
    private BarangService $barangService,
    private SatuanService $satuanService  // ← Added
) {}

public function getSatuan(string $kodesat): JsonResponse
{
    $satuan = $this->satuanService->findByKode($kodesat);
    return response()->json(['success' => true, 'data' => $satuan]);
}

public function updateSatuan(Request $request, string $kodesat): JsonResponse
{
    // Update logic here
    return response()->json(['success' => true]);
}
```

**Add to routes/api.php:**
```php
Route::get('v1/users/{userid}/report-menus', 'UserController@getReportMenus');
Route::post('v1/users/{userid}/report-menus', 'UserController@updateReportMenus');
```
