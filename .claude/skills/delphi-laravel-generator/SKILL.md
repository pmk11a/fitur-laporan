---
name: delphi-laravel-generator
description: Generate production-ready Laravel code from Delphi .pas files. This skill extracts business logic, validation rules, and database operations from Delphi source code and generates Laravel Models, Services, Controllers, and Requests that connect to your **existing SQL Server database**.
---

# ⚠️ DEPRECATED FOR EXECUTION

> **For code generation, use: `/delphi-laravel-impl`**
>
> This skill is now **REFERENCE ONLY**.
> Templates and patterns are kept here for documentation purposes.

---

## Reference Content

### Templates (Reference Only)

| Template | Location | Purpose |
|----------|----------|---------|
| Model | `templates/model.template.php` | Eloquent model from existing schema |
| Service | `templates/service.template.php` | Business logic service |
| Controller | `templates/controller.template.php` | RESTful API controller |
| Request | `templates/request.template.php` | FormRequest validation |
| Update Request | `templates/update_request.template.php` | Update validation |

### Config Files (Reference Only)

| Config | Purpose |
|--------|---------|
| `config/table_mappings.json` | Form-to-table mapping |
| `config/validation_patterns.json` | Delphi Cek* → Laravel rules |
| `config/type_mappings.json` | Delphi → PHP types |
| `config/field_patterns.json` | Field patterns |

### Reference Guides

| Guide | Content |
|-------|---------|
| `references/types.md` | Delphi → PHP type mappings |
| `references/mappings.md` | Function mappings, naming conventions |
| `references/table-mapping.md` | Multi-layer validation workflow |
| `references/model-guide.md` | Model generation patterns |
| `references/service-guide.md` | Service patterns |
| `references/controller-guide.md` | Controller patterns |
| `references/validation-guide.md` | FormRequest patterns |

---

## Usage

For actual code generation, invoke:

```
/delphi-laravel-impl <module-name>
```

This will:
1. Scan Delphi file with traceability analyzer
2. Generate complete module (Backend + Frontend)
3. Wire all templates (Model, Service, Controller, Request, Route, Hooks, Pages)

---

## History

- Originally used for direct code generation
- Now consolidated into `delphi-laravel-impl` as single entry point
- Templates remain here for documentation and pattern reference