# Claude Memory Index

## User
(User info not yet recorded)

## Feedback
- [migration-lessons-learned](feedback/migration-lessons-learned.md) - Pattern OOP/SOLID, DB schema check, label mapping dari database
- [no-modify-menu-report-tables](feedback/no-modify-menu-report-tables.md) - Dilarang ubah data dbmenreport & dbflmenureport saat migrasi/generate SQL
- [verify-menu-access-pattern](feedback/verify-menu-access-pattern.md) - Baca dbinsert_menu_*.sql dulu sebelum tentukan ACCESS, jangan tebak
- [config-driven-summary-fields](feedback/config-driven-summary-fields.md) - Summary left/right columns must read from config_json (summary_fields, right_fields), not hardcoded arrays
- [config-driven-t1-computed](feedback/config-driven-t1-computed.md) - T1 summary computed (Saldo, Tunai) + aggregates must be config-driven via config_json.computed, not hardcoded .fr3 formulas
- [format-type-number-not-currency](feedback/format-type-number-not-currency.md) - format_type='number' not in recognized list; must add to formatCell colType mapping or values show raw
- [userid-placeholder-case-mismatch](feedback/userid-placeholder-case-mismatch.md) - **FIXED** (2025-06-23): Sekarang case-insensitive + word boundaries. Filter `userId` dan SP placeholder `@UserID`/`@IDUser` cocok.
- [filter-placeholder-case-insensitive](feedback/filter-placeholder-case-insensitive.md) - **FIXED** (2025-06-23): Sekarang case-insensitive + word boundaries. `perkiraanA` ↔ `@PerkiraanA` cocok.
- [placeholder-substring-safety](feedback/placeholder-substring-safety.md) - CRITICAL: `str_replace('@kodesupp',...,'...@kodesupp1...')` corrupt SQL. Ganti dengan `preg_replace` + word boundary lookaround. See `ReportPlaceholderTest.php`.
- [report-doc-sync](feedback/report-doc-sync.md) - Setiap perubahan codebase yang memengaruhi seed SQL generation (config_json, footer_bands, T1 computed, CH/GB, filter) harus update docs/report-seed-sql-generation.md
- [query-parsing-regressions](feedback/feedback_query_parsing_regressions.md) - **FIXED** (2026-07-04): BrowseService `search()` dan `validateCode()` sekarang normalize spasi internal (`w h e r e` → `where`) dengan `preg_replace('/(?<=[A-Za-z])\s+(?=[A-Za-z])/', '', $whereExtra)` sebelum strip keyword. Double-AND dari `whereExtra` yang belum ter-strip juga dihandle.
- [debug-config-driven-features-systematically](feedback/debug-config-driven-features-systematically.md) - Jangan iterasi log dulu — trace shape data end-to-end (backend response → frontend consumer → API call → SQL) dengan static analysis sebelum tambah log pertama
- [memory-placement](feedback/memory-placement.md) - Project memory HARUS disimpan di `D:\TestLaB\Fitur\.claude\memory\`, BUKAN `C:\Users\hp-M\.claude\projects\...`. Path user home = orphan, tidak di-load Claude Code.

## Project
- [migration-pattern-multi-dataset-report](project/migration-pattern-multi-dataset-report.md) - Pattern untuk report dengan multiple datasets (Neraca)
- [nota-print-layer-architecture](project/nota-print-layer-architecture.md) - Print Layer B2: Hybrid (1 master Blade + JSON config di dbnotatemplate) untuk 152 Nota .fr3. Library: barryvdh/laravel-dompdf. Endpoint: GET /api/nota/{kode}/print
- [kodemenu-020102-vs-2020102-conflict](project/kodemenu-020102-vs-2020102-conflict.md) - 020102=Bank Harian (Delphi), 2020102=Jurnal Pengeluaran Kas (Laravel). Jangan auto-merge.
- [supplier-browse-10141-view-fix](project/supplier-browse-10141-view-fix.md) - Browse 10141 harus pakai view vwBrowsSupp, bukan tabel DBPERKCUSTSUPP (supplier tanpa Perkiraan tidak muncul)
- [browse-1014-delphi-alamat-fix](project/browse-1014-delphi-alamat-fix.md) - Browse 1014 Alamat harus concat Alamat1+' '+Alamat2 (alias_fields expression), bukan single cs.Alamat
- [filters-tab-dynamic-browse-types](project/filters-tab-dynamic-browse-types.md) - FiltersTab.vue improvement 1: dropdown browse type sekarang dynamic dari GET /api/admin/browse/list (grouped + source badge). Fallback hardcoded array masih ada kalau API gagal.

## Reference
- [generic-grouping-architecture](reference/generic-grouping-architecture.md) - Generic DB-driven grouping (no hardcoded KODEMENU/strategy)
- [browse-autocomplete-loading-spinner-fix](reference/browse-autocomplete-loading-spinner-fix.md) - Vue 3 refs from composables need .value in template
- [browse-autocomplete-kode-browse](reference/browse-autocomplete-kode-browse.md) - Browse filter dengan kode_browse dari database