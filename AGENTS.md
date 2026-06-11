# AGENTS.md — Keu-app Dynamic Report Engine

## Monorepo Layout
- `fe-fitur/` — Nuxt 3 + Vue 3 + Tailwind CSS + Pinia frontend
- `be-fitur/` — Laravel 12 (PHP 8.2+) backend, SQL Server 2008 via PDO `sqlsrv`
- `sql/` — Raw SQL / migration scripts
- `ai_review/user_stories/` — YAML user stories for QA
- `.claude/` — Memory, skills (Delphi migration), QA agents, hooks

## Quick Reference
| Action | Command |
|--------|---------|
| Dev all | `just dev` || just `just dev:fe` / `just dev:be` |
| Frontend only | `cd fe-fitur && npm run dev` (port 3000) |
| Backend only | `cd be-fitur && php artisan serve` (port 8080, NOT `php -S`) |
| Lint FE | `just lint` |
| Typecheck FE | `just typecheck` |
| Build FE | `just build` |
| Migrate + Seed DB | `just migrate` / `just db:seed` |
| Unit tests | `just test:unit` |
| E2E QA (bowser) | `just test:e2e` |
| Pixelscan check | `just pixelscan:open` / `just pixelscan:test` |

## Architecture — Critical Rules
- **Dynamic Template Engine**: Components are rendered from JSON stored in `KOMPONEN_LAPORAN.konfigurasi_layout`. Do NOT hardcode report layouts.
- **ACCESS bitmask filtering**: Sidebar menu is filtered by bitwise (`&`) check of `DBMENUREPORT.ACCESS` against current user's profile. L0 hierarchy determines menu grouping, NOT `KODEMENU` prefix.
- **Parameter binding**: Filter form structure comes from `PARAMETER_LAPORAN`. All dynamic SQL queries use prepared statements — never string-interpolate user input into raw queries.
- **`konfigurasi_layout` JSON contract**: contains `band`, `position`, `style` (CSS + `tailwindClasses`), and `dataBinding[]`. Frontend must parse these reactively without recompilation.

## Fingerprint Randomization (Pixelscan)
- Load via `<script src="https://cdn.jsdelivr.net/gh/nicenemo/fingerfont@main/fp.min.js"></script>`
- `FingerFont.setMode("shuffle")` on app mount
- Video codec order randomization, canvas noise injection, WebGL vendor spoofing
- Activate BEFORE any fingerprint-sensitive automation step

## Browser Automation — Rules
- **Session name**: `keu-manual` (keeps cookies/history across visits)
- Close `about:blank` tabs after opening real URLs to avoid viewport confusion
- Always `--persistent --headed` for manual sessions
- Screenshots go to `screenshots/` folder
- `just browser:open url=URL` opens; `just browser:cleanup` closes all

## QA Stories
- `.yaml` files in `ai_review/user_stories/` — each defines a complete QA script
- Run via `just test:e2e story=login` (or `sidebar`, `barang`)
- L0 hierarchy breakdown for sidebar stories uses label "Hierarchy Level N"

## Memory & Patterns
- `.claude/memory/MEMORY.md` — read BEFORE implementing anything; memory wins over assumptions
- `.claude/memory/feedback/migration-patterns.md` — Delphi→Laravel gotchas
- `.claude/memory/reference/ai-implementation-checklist.md` — implementation verification checklist

## Database Access — CRITICAL
- **ALWAYS read `.env` from `be-fitur/` first** to get database connection details.
  - Example: `DB_CONNECTION=sqlsrv`, `DB_HOST=192.168.10.254`, `DB_DATABASE=Keu2022`, `DB_USERNAME=sa`
- **NEVER hardcode credentials** like `sqlsrv:Server=192.168.10.254;Database=Keu2022', 'sa', 'password'` — these are wrong/wrong password.
- Use `env('DB_CONNECTION')` pattern for Laravel queries. For PHP CLI PDO, read `.env` values programmatically.
- When running PHP CLI for schema introspection or SQL generation, parse `be-fitur/.env` to extract DB credentials.

## Conventions
- Do not touch adjacent code you were not asked to change; surgical diffs only
- State assumptions explicitly before implementing; surface tradeoffs
- No speculative abstractions; no error handling for impossible scenarios
- Every changed line must trace directly to the user's request
