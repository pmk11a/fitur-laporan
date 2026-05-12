#!/usr/bin/env python3
"""
Generate Markdown documentation from traceability JSON.
Improved readability with better spacing and formatting.
"""

import json
import argparse
from pathlib import Path
from typing import Dict, Any, List
from datetime import datetime


def truncate_params(params: str, max_len: int = 50) -> str:
    """Truncate long parameter lists for readability."""
    if len(params) > max_len:
        return params[:max_len] + "..."
    return params


def generate_markdown(data: Dict[str, Any], output_path: Path) -> None:
    """Generate Markdown file from traceability JSON data."""

    lines = []
    summary = data.get('summary', {})
    modules = data.get('modules', [])

    # Header
    lines.append(f"# {data.get('project', 'Project')} Traceability Matrix")
    lines.append("")
    lines.append(f"**Scan Date:** {data.get('scan_date', 'N/A')}")
    lines.append("")
    lines.append(f"**Source:** `{data.get('source_dir', 'N/A')}`")
    lines.append("")

    # Summary Section
    lines.append("---")
    lines.append("")
    lines.append("## 📊 Summary")
    lines.append("")

    lines.append("| Metric | Count |")
    lines.append("|:-------|------:|")
    lines.append(f"| **Total Functions** | **{summary.get('total_functions', 0)}** |")
    lines.append(f"| Total Modules | {summary.get('total_modules', 0)} |")
    lines.append(f"| ✅ Migrated | {summary.get('migrated', 0)} |")
    lines.append(f"| ⏳ Pending | {summary.get('pending', 0)} |")
    lines.append("")

    # Category Breakdown
    lines.append("## 📁 Category Breakdown")
    lines.append("")

    lines.append("| Category | Count | Migrated | Pending |")
    lines.append("|:---------|------:|---------:|--------:|")

    categories = ['validation', 'business_logic', 'database', 'utility', 'logging']
    category_names = {
        'validation': '🔍 Validation',
        'business_logic': '💼 Business Logic',
        'database': '🗄️ Database Operations',
        'utility': '🔧 Utility Functions',
        'logging': '📝 Logging Functions'
    }

    for cat in categories:
        count = summary.get(cat, 0)
        lines.append(f"| {category_names.get(cat, cat.title())} | {count} | 0 | {count} |")

    lines.append("")

    # Module Details
    for module in modules:
        module_name = module.get('name', 'Unknown')
        delphi_file = module.get('delphi_file', '')
        functions = module.get('functions', [])

        lines.append("")
        lines.append("---")
        lines.append("")
        lines.append(f"## 📦 Module: {module_name}")
        lines.append("")
        lines.append(f"**File:** ``{delphi_file}``")
        lines.append(f"**Functions:** {len(functions)}")
        lines.append("")

        # Group by category
        by_category: Dict[str, List[Dict]] = {
            'validation': [],
            'business_logic': [],
            'database': [],
            'utility': [],
            'logging': []
        }

        for func in functions:
            category = func.get('category', 'business_logic')
            if category not in by_category:
                category = 'business_logic'
            by_category[category].append(func)

        # Output each category with card-style format
        for cat in categories:
            funcs = by_category.get(cat, [])
            if funcs:
                lines.append(f"### {category_names.get(cat, cat.title())}")
                lines.append("")
                lines.append("| Function | Type | Returns | Laravel Target | Status |")
                lines.append("|:---------|------|:-------|:---------------|:------:|")

                for func in funcs:
                    name = func.get('name', '')
                    func_type = func.get('type', 'function')
                    returns = func.get('returns', '')
                    target = func.get('laravel_target', {})
                    target_file = target.get('file', '')
                    target_method = target.get('method', '')
                    status = target.get('status', 'pending')

                    # Shorten Laravel target for display
                    laravel_display = ""
                    if target_file and target_method:
                        # Extract just class name and method
                        file_parts = target_file.split('/')
                        class_name = file_parts[-1] if file_parts else target_file
                        class_name = class_name.replace('.php', '')
                        laravel_display = f"{class_name}@{target_method}"

                    status_symbol = "⏳" if status == "pending" else "✅"

                    lines.append(f"| **{name}** | {func_type} | `{returns}` | {laravel_display} | {status_symbol} |")

                    # Add full parameters as detail row
                    params = func.get('params', [])
                    if params:
                        params_str = ', '.join(params) if len(params) < 5 else f"{len(params)} parameters"
                        lines.append(f"| ↳ `params: {params_str}` | | | | |")

                lines.append("")

    # Detailed function list with parameters
    lines.append("---")
    lines.append("")
    lines.append("## 📋 Detailed Function List")
    lines.append("")
    lines.append("Complete list with all parameters:")
    lines.append("")

    for module in modules:
        module_name = module.get('name', 'Unknown')
        functions = module.get('functions', [])

        for func in functions:
            name = func.get('name', '')
            params = func.get('params', [])
            returns = func.get('returns', '')
            line = func.get('line', 0)
            category = func.get('category', '')

            lines.append(f"### `{name}`")
            lines.append("")
            lines.append(f"- **Type:** {func.get('type', 'function')}")
            lines.append(f"- **Returns:** `{returns}`")
            lines.append(f"- **Category:** {category}")
            lines.append(f"- **Line:** {line}")

            if params:
                lines.append(f"- **Parameters:**")
                for p in params:
                    lines.append(f"  - `{p}`")

            target = func.get('laravel_target', {})
            if target.get('file') or target.get('method'):
                lines.append(f"- **Laravel Target:**")
                if target.get('file'):
                    lines.append(f"  - File: `{target.get('file')}`")
                if target.get('method'):
                    lines.append(f"  - Method: `{target.get('method')}`")

            lines.append("")

    # Progress tracking section
    lines.append("---")
    lines.append("")
    lines.append("## 📈 Migration Progress")
    lines.append("")
    lines.append("Use this section to track overall migration progress:")
    lines.append("")

    completed = summary.get('migrated', 0)
    total = summary.get('total_functions', 1)
    percentage = int((completed / total) * 100) if total > 0 else 0
    bar_length = 20
    filled = int((completed / total) * bar_length) if total > 0 else 0
    bar = "█" * filled + "░" * (bar_length - filled)
    lines.append(f"``")
    lines.append(f"Progress: [{bar}] {percentage}% ({completed}/{total})")
    lines.append(f"```")
    lines.append("")

    # Quick checklist format
    lines.append("## ✅ Migration Checklist")
    lines.append("")
    lines.append("Quick checklist for tracking:")
    lines.append("")

    for module in modules:
        module_name = module.get('name', 'Unknown')
        functions = module.get('functions', [])

        lines.append(f"### {module_name}")
        lines.append("")

        for func in functions:
            name = func.get('name', '')
            target = func.get('laravel_target', {})
            status = target.get('status', 'pending')

            checkbox = "[ ]" if status == "pending" else "[x]"
            target_info = f"→ {target.get('file', '')}@{target.get('method', '')}" if target.get('file') else ""
            lines.append(f"- {checkbox} `{name}` {target_info}")

        lines.append("")

    # Write to file
    output_path.write_text('\n'.join(lines), encoding='utf-8')
    print(f"Markdown generated: {output_path}")


def main():
    parser = argparse.ArgumentParser(
        description='Generate Markdown from traceability JSON'
    )
    parser.add_argument(
        'input',
        help='Input JSON file'
    )
    parser.add_argument(
        '-o', '--output',
        help='Output Markdown file (default: input file with .md extension)'
    )

    args = parser.parse_args()

    input_path = Path(args.input)
    output_path = Path(args.output) if args.output else input_path.with_suffix('.md')

    with open(input_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    generate_markdown(data, output_path)


if __name__ == '__main__':
    main()
