# Form Input: {{formName}}

## Tujuan

{{purpose}}

## Komponen Form

| # | Nama Field | Tipe | Wajib | Deskripsi | Validasi |
|---|-----------|------|-------|----------|----------|
{{#each fields}}
{{#if this.required}}*{{/if}}{{add @index 1}} | **{{this.label}}** | `{{this.type}}` | {{#if this.required}}Ya{{else}}Tidak{{/if}} | {{this.description}} | {{#if this.validations}}{{formatValidations this.validations}}{{else}}-{{/if}} |
{{/each}}

## Detail Field

{{#each fields}}
### {{this.label}} ({{this.fieldId}})

{{#if this.foreignKey}}
**Jenis:** Lookup dari tabel `{{this.foreignKey.table}}`
- Display: `{{this.foreignKey.displayField}}`
- Value: `{{this.foreignKey.valueField}}`

**Cara Input:**
- Ketik untuk mencari data
- Klik ikon pencarian untuk melihat daftar
- Tekan Enter untuk memilih
{{/if}}

{{#if this.type}}
**Tipe Input:** {{this.type}}
{{/if}}

{{#if this.validations}}
**Validasi:**
{{#each this.validations}}
- `{{this.rule}}` - {{this.message}}
{{/each}}
{{/if}}

{{#if this.defaultValue}}
**Nilai Default:** `{{this.defaultValue}}`
{{/if}}

{{#if this.options}}
**Pilihan yang Tersedia:**
{{#each this.options}}
- {{this}}
{{/each}}
{{/if}}

{{#if this.helpText}}
**Petunjuk:** {{this.helpText}}
{{/if}}

---

{{/each}}

## Contoh Input

```json
{{exampleInput}}
```

## Error Umum saat Input

| Field | Error | Penyebab | Solusi |
|-------|-------|----------|--------|
{{#each commonErrors}}
| {{this.field}} | {{this.message}} | {{this.cause}} | {{this.solution}} |
{{/each}}

## Tips Input

{{#each tips}}
- {{this}}
{{/each}}