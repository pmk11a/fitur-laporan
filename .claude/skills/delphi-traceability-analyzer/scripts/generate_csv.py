#!/usr/bin/env python3
"""
Generate CSV from traceability JSON for Excel compatibility.
User-friendly format for tracking migration progress.
"""

import json
import argparse
import csv
from pathlib import Path
from typing import Dict, Any, List


def generate_csv(data: Dict[str, Any], output_path: Path) -> None:
    """Generate CSV file from traceability JSON data."""

    summary = data.get('summary', {})
    modules = data.get('modules', [])

    # Prepare CSV data
    rows = []

    # Header row
    rows.append([
        'Module',
        'Function',
        'Type',
        'Category',
        'Parameters',
        'Returns',
        'Line',
        'Laravel File',
        'Laravel Method',
        'Status',
        'Notes'
    ])

    # Data rows
    for module in modules:
        module_name = module.get('name', 'Unknown')
        delphi_file = module.get('delphi_file', '')
        functions = module.get('functions', [])

        for func in functions:
            name = func.get('name', '')
            func_type = func.get('type', 'function')
            category = func.get('category', '')
            params = func.get('params', [])
            returns = func.get('returns', '')
            line = func.get('line', 0)

            target = func.get('laravel_target', {})
            target_file = target.get('file', '')
            target_method = target.get('method', '')
            status = target.get('status', 'pending')

            # Format parameters
            if params:
                if len(params) > 5:
                    params_str = f"{len(params)} parameters"
                else:
                    params_str = '; '.join(params)
            else:
                params_str = ''

            rows.append([
                module_name,
                name,
                func_type,
                category,
                params_str,
                returns,
                line,
                target_file,
                target_method,
                status,
                ''  # Notes column - empty for user to fill
            ])

    # Write CSV with UTF-8 BOM for Excel compatibility
    with open(output_path, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.writer(f)
        writer.writerows(rows)

    print(f"CSV generated: {output_path}")
    print(f"Total rows: {len(rows) - 1} functions")


def generate_summary_csv(data: Dict[str, Any], output_path: Path) -> None:
    """Generate summary CSV by category."""

    summary = data.get('summary', {})
    modules = data.get('modules', [])

    rows = []

    # Header
    rows.append([
        'Category',
        'Count',
        'Migrated',
        'Pending',
        'Priority',
        'Notes'
    ])

    # Category data
    categories = {
        'validation': {'name': 'Validation', 'priority': 'P1 - Critical'},
        'business_logic': {'name': 'Business Logic', 'priority': 'P2 - High'},
        'database': {'name': 'Database Operations', 'priority': 'P3 - Medium'},
        'utility': {'name': 'Utility Functions', 'priority': 'P4 - Low'},
        'logging': {'name': 'Logging Functions', 'priority': 'P4 - Low'}
    }

    for cat_key, cat_info in categories.items():
        count = summary.get(cat_key, 0)
        rows.append([
            cat_info['name'],
            count,
            0,
            count,
            cat_info['priority'],
            ''
        ])

    with open(output_path, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.writer(f)
        writer.writerows(rows)

    print(f"Summary CSV generated: {output_path}")


def main():
    parser = argparse.ArgumentParser(
        description='Generate CSV from traceability JSON'
    )
    parser.add_argument(
        'input',
        help='Input JSON file'
    )
    parser.add_argument(
        '-o', '--output',
        help='Output CSV file (default: input file with .csv extension)'
    )
    parser.add_argument(
        '-s', '--summary',
        action='store_true',
        help='Also generate summary CSV'
    )

    args = parser.parse_args()

    input_path = Path(args.input)
    output_path = Path(args.output) if args.output else input_path.with_suffix('.csv')

    # Find project root for relative paths
    # Auto-detect project root by looking for common markers
    project_root = input_path
    while project_root.parent != project_root:
        if any((project_root / marker).exists() for marker in ['.git', 'composer.json', 'package.json']):
            break
        if (project_root / 'KSP').exists():
            break
        project_root = project_root.parent

    # Make output path absolute to project root
    if not output_path.is_absolute():
        output_path = project_root / output_path

    output_path.parent.mkdir(parents=True, exist_ok=True)

    with open(input_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    generate_csv(data, output_path)

    if args.summary:
        summary_path = output_path.parent / 'summary.csv'
        generate_summary_csv(data, summary_path)


if __name__ == '__main__':
    main()
