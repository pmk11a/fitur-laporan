# Memory Index

## User
- [Developer Profile](user/developer.md) — Senior Delphi/Laravel developer

## Feedback
- [Migration Patterns](feedback/migration-patterns.md) — Critical gotchas for Delphi→Laravel
- [No Hardcode Rule](feedback/no-hardcode-rule.md) — Database-first, prevent hardcoded values

## Project
- [Keu-app Migration](project/keu-app-migration.md) — Project context & constraints
- [Login Auth Patterns](project/login-auth-patterns.md) — Pattern login base64 + Repository
- [Fluffy Bee](project/fluffy-bee.md) — Dynamic Report Engine frontend (Nuxt.js)
- [Fluffy Bee Report Engine](project/fluffy-bee-report-engine.md) — Full migration status & DB tables

## Reference
- [External Systems](reference/external-systems.md) — External resources & skills
- [Artisan Serve Fix](reference/artisan-serve-fix.md) — Fix untuk php artisan serve crash
- [DBFLPASS Password Field](reference/dbflpass-password-field.md) — Use UID2, not UID, for password
- [Sidebar Menu Hierarchy](reference/sidebar-menu-hierarchy.md) — Debug menu sidebar: L0-based hierarchy, files to check
- [AI Implementation Checklist](reference/ai-implementation-checklist.md) — Checklist sebelum implement: avoid hallucination & overlap
- [Report Setup Pattern](reference/report-setup-pattern.md) — Pattern setup report: Delphi → .fr3 → SQL → Frontend
- [Fluffy Bee Fixes](reference/fluffy-bee-fixes.md) — All fixes: footer_bands, dbperiode, SP params, export

## Verification Scripts
Located in `.claude/skills/delphi-laravel-impl/scripts/`:
- `verify_migration.py` — Check generated code vs AST
- `check_ddl_blocker.py` — Prevent schema modifications