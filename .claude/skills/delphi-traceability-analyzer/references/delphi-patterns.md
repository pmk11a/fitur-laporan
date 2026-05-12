# Common Delphi Patterns in KSP Application

Reference patterns found in `MyProcedure.pas` and common Delphi VCL patterns.

## Unit Structure

```delphi
unit MyProcedure;

interface

uses
    Controls, SysUtils, ADOdb, Windows, Messages, Forms, Dialogs,
    PBNumEdit, speedbar, stdctrls, ToolEdit, Mask, DB, ppCtrls, CheckLst;

var
    Kode, Nama: String;

function MyFunction(param: Type): ReturnType;
procedure MyProcedure(param: Type);

implementation

uses MyModul, MyGlobal, FrmBrows, FrmReportPreview;

// Function implementations
end.
```

## Function/Procedure Declaration Patterns

### Function with Return Type

```delphi
function FunctionName(Param1: Type1; Param2: Type2): ReturnType;
begin
  Result := value;
end;
```

### Procedure (No Return)

```delphi
procedure ProcedureName(Param1: Type1; var Param2: Type2);
begin
  // Implementation
end;
```

### Array Parameters

```delphi
procedure InputField2(mSelect: String; mParam: Array of Variant; Var mQuery: TAdoQuery);
```

## Database Query Patterns

### Standard SELECT Query

```delphi
with DM.QuCari do
begin
  Close;
  SQL.Clear;
  SQL.Add('SELECT * FROM dbPeriode');
  SQL.Add('WHERE UserID = :0 AND Bulan = :1 AND Tahun = :2');
  Prepared;
  Parameters[0].Value := Nama;
  Parameters[1].Value := Bulan;
  Parameters[2].Value := Tahun;
  Open;
end;

if DM.QuCari.RecordCount > 0 then
begin
  // Process results
end;
```

### INSERT Query

```delphi
with DM.QuLogFile do
begin
  Close;
  SQL.Clear;
  SQL.Add('INSERT INTO dbLogFile (Tahun, Bulan, Tanggal, Pemakai, Aktivitas)');
  SQL.Add('VALUES (:0, :1, GetDate(), :2, :3)');
  try
    ExecSQL;
  except
    on E: Exception do
      // Handle error
  end;
end;
```

### UPDATE Query

```delphi
with Dm.DaftarNO do
begin
  Close;
  SQL.Clear;
  SQL.Add('UPDATE dbflpass SET status = :0');
  SQL.Add('WHERE userid = :1');
  Prepared;
  Parameters[0].Value := Status;
  Parameters[1].Value := Pemakai;
  ExecSQL;
end;
```

### Stored Procedure Execution

```delphi
with DM.QuCari do
begin
  Close;
  sql.Clear;
  sql.Add('Exec SP_UrutNoKAS :0, :1, :2, :3, :4');
  Prepared;
  Parameters[0].Value := Tipe;
  Parameters[1].Value := Tipetrans;
  Parameters[2].Value := Param3;
  Parameters[3].Value := Bulan;
  Parameters[4].Value := Tahun;
  Open;
end;
```

### Complex SQL with Subquery

```delphi
sql.Add('SELECT Isnull(Max(case when isnumeric(left(Nobukti,5))=1');
sql.Add('  then cast(left(Nobukti,5) as int) else 0 end),0) Nomor,');
sql.Add('Isnull(Max(case when isnumeric(left(Nobukti,5))=1');
sql.Add('  then cast(Left(Nobukti,5) as int)+1 else 0 end),1) Nomorbaru');
sql.Add('from dbtrans');
sql.Add('where month(Tanggal) = :0 and year(Tanggal) = :1');
```

## Common Function Categories

### Validation Functions

```delphi
function CekPeriode(Nama: string; tgl: Tdatetime): Boolean;
function MyCek_Lock_Periode(Bukti: string): Boolean;
function IsLockPeriode(Bulan, Tahun: Integer): Boolean;
function CekDeletePO(Bukti, Barang: String; Tipe: integer): Boolean;
```

### Number/String Formatting

```delphi
function NewNo(No: String; Digit: integer): String;
function NoToStr(No, Digit: integer): String;
function Kalimat(Digit: Integer; Kata: String): String;
function NoUrutKas(Tipe, Tipetrans: String; bulan, tahun: integer): String;
```

### Date/Time Functions

```delphi
function BulanRomawi(Tanggal: Tdatetime; Mode: String): String;
function Romawi(Tanggal: Tdatetime; Mode: String): String;
function SecToTime(Sec: Integer): string;
function MyAktifTgl(T: TdateTime; Nama: String): TdateTime;
```

### Business Logic Functions

```delphi
function Check_Nomor(Bulan, Tahun: integer; Tipe: String; PPn: String;
    var TipeTrans: String; var PlusPPN: String; var Nomor: String; Devisi: String): Boolean;
function RateOfInterst(Pinjaman, Angsuran: Real; Tenor: Integer): Real;
function getperkiraan(KodePrd: String; tipe: integer): String;
```

### Utility Functions

```delphi
procedure Delay(Lama: LongWord);
function SLeft(mString: String; mPos: Integer): String;
function SRight(mString: String; mPos: Integer): STring;
function CariKoma(Nilai: string): Integer;
procedure GeserKalimat(Kalimat: String; var Hasil: String);
```

## Global Variables and Constants

```delphi
var
  Kode, Nama: String;
  iduser: String;
  PeriodThn: String;
  PeriodBln: String;
```

## Common Data Access Components

| Component | Usage |
|-----------|-------|
| `TADOQuery` | General query execution |
| `TADOTable` | Direct table access |
| `TDataSource` | Data binding |
| `TADOConnection` | Database connection |

## Common String Manipulation

```delphi
// Substring (1-indexed in Delphi)
Copy(String, StartIndex, Length)

// String length
Length(String)

// Find position (0 if not found)
Pos(Substring, String)

// Concatenation
Result := String1 + String2;

// String to number
StrToInt(StringValue)
StrToFloat(StringValue)

// Number to string
IntToStr(IntegerValue)
FloatToStr(FloatValue)
```

## Common Validation Patterns

### Empty/Null Check

```delphi
if Trim(Edit1.Text) = '' then
  ShowMessage('Field cannot be empty!');

if VarIsNull(Query.FieldByName('Field').Value) then
  // Handle null
```

### Record Existence Check

```delphi
if Query.RecordCount > 0 then
  // Records exist
else
  // No records

if Query.IsEmpty then
  // No records
```

## Common Message Display

```delphi
// Simple message
ShowMessage('Your message here');

// Confirmation dialog
if MessageDlg('Are you sure?', mtConfirmation, [mbYes, mbNo], 0) = mrYes then
  // User clicked Yes

// Error message
MessageDlg('Error occurred!', mtError, [mbOK], 0);
```

## Type Conversions

```delphi
// String to Integer
IntValue := StrToInt(StringValue);

// Integer to String
StringValue := IntToStr(IntValue);

// String to Float
FloatValue := StrToFloat(StringValue);

// Float to String
StringValue := FloatToStr(FloatValue);

// Date to String
StringValue := DateToStr(DateValue);

// String to Date
DateValue := StrToDate(StringValue);

// Variant conversions
VarToInt(Value)
VarToStr(Value)
```

## Common Control Patterns

### ComboBox Selection

```delphi
if ComboBox1.ItemIndex > -1 then
  SelectedValue := ComboBox1.Items.Objects[ComboBox1.ItemIndex];
```

### CheckBox State

```delphi
if CheckBox1.Checked then
  // Checkbox is checked
```

### Edit Field Validation

```delphi
if Trim(Edit1.Text) = '' then
begin
  ShowMessage('Field cannot be empty!');
  Edit1.SetFocus;
  Abort;
end;
```

## File Operations

```delphi
// Check if file exists
if FileExists(FilePath) then
  // File exists

// Delete file
DeleteFile(FilePath);

// File information
FileSize := FileGetSize(FilePath);
```

## DataModule Patterns

```delphi
// Access DataModule queries
with DM.QuCari do
begin
  // Query operations
end;

// Access DataModule tables
with DM.TableMaster do
begin
  // Table operations
end;

// Access DataModule connections
DM.Connection.Connected := True;
```

## Transaction Patterns

```delphi
// Start transaction
DM.Connection.BeginTrans;

try
  // Execute queries
  Query1.ExecSQL;
  Query2.ExecSQL;

  // Commit if successful
  DM.Connection.CommitTrans;
except
  // Rollback on error
  DM.Connection.RollbackTrans;
  raise;
end;
```

## Common Constants in KSP

```delphi
// Period format
PeriodThn = '2026'
PeriodBln = '04'

// User info
iduser = 'ADMIN'

// Number formatting
DecimalSeparator = '.'
ThousandSeparator = ','
```
