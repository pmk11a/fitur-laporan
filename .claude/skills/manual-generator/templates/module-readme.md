---
moduleId: "{{moduleId}}"
moduleName:
  id: "{{moduleNameId}}"
  en: "{{moduleNameEn}}"
version: "1.0.0"
lastUpdated: "{{lastUpdated}}"
migratedFrom:
  delphiFile: "{{delphiFile}}"
  dbTables: {{dbTables}}
prerequisites:
  modules: {{prerequisiteModules}}
  permissions: {{permissions}}
  masterData: {{masterData}}
---

# {{moduleNameId}}

## Gambaran Singkat

{{overview}}

:::info Status Modul
Modul ini telah dimigrasikan dari Delphi dan telah diverifikasi sesuai dengan behavior aslinya.
:::

## Prasyarat

### Hak Akses yang Dibutuhkan

| Kode | Nama | Aksi yang Diizinkan |
|------|------|---------------------|
{{#each permissions}}
| {{this.code}} | {{this.name}} | {{this.actions}} |
{{/each}}

### Data Master yang Harus Ada

Sebelum mengakses modul ini, pastikan data berikut sudah ada:

| Tabel | Keterangan | Wajib |
|-------|------------|-------|
{{#each masterData}}
| `{{this.table}}` | {{this.description}} | {{#if this.required}}Ya{{else}}Tidak{{/if}} |
{{/each}}

### Modul Pendukung

{{#if prerequisiteModules}}
Modul lain yang harus diselesaikan terlebih dahulu:
{{#each prerequisiteModules}}
- [{{this}}](../{{this}}/README.md)
{{/each}}
{{else}}
Tidak ada modul pendukung yang harus diselesaikan.
{{/if}}

## Navigasi

Untuk mengakses modul ini, navigasi ke:

```
{{navigationPath}}
```

## Form Input

Lihat halaman [Form Input](./form-input.md) untuk detail lengkap tentang setiap field dalam form.

## Alur Kerja

Lihat halaman [Alur Kerja](./alur-kerja.md) untuk flowchart dan langkah-langkah penggunaan.

## Error Handling

Lihat halaman [Error Handling](./error-handling.md) untuk daftar error dan solusinya.

---

*Versi terakhir diupdate: {{lastUpdated}}*
*Dokumentasi ini di-generate otomatis dari kode aplikasi*