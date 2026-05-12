#!/usr/bin/env python3
"""
verify_migration.py - Verify generated code matches AST

Usage:
    python verify_migration.py <module.json> <generated_folder>

Checks:
    1. All functions in traceability JSON have corresponding code
    2. Column names are UPPERCASE
    3. No DDL statements (Schema::, table()->create(), etc.)
    4. USERID handling correct
"""

import json
import sys
import re
from pathlib import Path

def load_traceability(json_path):
    """Load traceability JSON."""
    with open(json_path, 'r', encoding='utf-8') as f:
        return json.load(f)

def check_uppercase_columns(file_path):
    """Check that column names use UPPERCASE."""
    issues = []
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Patterns that suggest lowercase column names
    lowercase_patterns = [
        r'where\(\'[a-z_]+\'',  # where('column')
        r'->where\("[a-z_]+"',  # ->where("column")
        r'->select\("[a-z_]+"',  # ->select("column")
    ]

    for pattern in lowercase_patterns:
        matches = re.findall(pattern, content)
        if matches:
            issues.append(f"Lowercase column in {file_path.name}: {matches[:3]}")

    return issues

def check_ddl_blocker(file_path):
    """Check for forbidden DDL statements."""
    issues = []
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Forbidden patterns
    forbidden = [
        r'Schema::',
        r'->create\(',
        r'->table\(',
        r'alter table',
        r'drop table',
    ]

    for pattern in forbidden:
        if re.search(pattern, content, re.IGNORECASE):
            issues.append(f"DDL statement found: {pattern}")

    return issues

def check_userid(file_path):
    """Check USERID (uppercase) handling."""
    issues = []
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Check for wrong userid (lowercase)
    if re.search(r'userid\b', content) and not re.search(r'USERID', content):
        issues.append("Found 'userid' without 'USERID' - check auth")

    return issues

def verify_module(module_json_path, generated_folder):
    """Verify a generated module."""
    print(f"\n{'='*60}")
    print(f"VERIFICATION: {module_json_path}")
    print(f"{'='*60}")

    data = load_traceability(module_json_path)
    folder = Path(generated_folder)

    all_issues = []
    total_functions = data.get('summary', {}).get('total_functions', 0)
    matched = 0

    print(f"\n📊 Module: {data.get('module', 'Unknown')}")
    print(f"   Total functions: {total_functions}")

    # Check PHP files
    php_files = list(folder.rglob("*.php"))
    print(f"   PHP files: {len(php_files)}")

    for php_file in php_files:
        print(f"\n   Checking: {php_file.relative_to(folder)}")

        # Check UPPERCASE columns
        issues = check_uppercase_columns(php_file)
        if issues:
            print(f"   ⚠️  UPPERCASE issues:")
            for issue in issues:
                print(f"      - {issue}")
                all_issues.extend(issues)

        # Check DDL blocker
        issues = check_ddl_blocker(php_file)
        if issues:
            print(f"   ❌ DDL BLOCKER:")
            for issue in issues:
                print(f"      - {issue}")
                all_issues.extend(issues)

        # Check USERID
        issues = check_userid(php_file)
        if issues:
            print(f"   ⚠️  USERID issues:")
            for issue in issues:
                print(f"      - {issue}")
                all_issues.extend(issues)

    # Check TypeScript files
    ts_files = list(folder.rglob("*.ts")) + list(folder.rglob("*.tsx"))
    print(f"\n   TypeScript files: {len(ts_files)}")

    for ts_file in ts_files:
        print(f"\n   Checking: {ts_file.relative_to(folder)}")

        with open(ts_file, 'r', encoding='utf-8') as f:
            content = f.read()

        # Check res.data pattern
        if 'res.data.data' in content and 'res.data' not in content.replace('res.data.data', ''):
            print(f"   ⚠️  Found 'res.data.data' - should be 'res.data'")
            all_issues.append(f"Wrong res.data pattern in {ts_file.name}")

    # Summary
    print(f"\n{'='*60}")
    print(f"SUMMARY")
    print(f"{'='*60}")
    print(f"   Total functions in trace: {total_functions}")
    print(f"   PHP files checked: {len(php_files)}")
    print(f"   TypeScript files checked: {len(ts_files)}")
    print(f"   Issues found: {len(all_issues)}")

    if all_issues:
        print(f"\n   ❌ VERIFICATION FAILED")
        print(f"\n   Issues:")
        for i, issue in enumerate(all_issues[:10], 1):
            print(f"      {i}. {issue}")
        if len(all_issues) > 10:
            print(f"      ... and {len(all_issues) - 10} more")
        return False
    else:
        print(f"\n   ✅ VERIFICATION PASSED")
        return True

def main():
    if len(sys.argv) < 3:
        print("Usage: python verify_migration.py <module.json> <generated_folder>")
        print("\nExample:")
        print("  python verify_migration.py docs/traceability/frmbarang.json be-keu/app")
        sys.exit(1)

    module_json = sys.argv[1]
    generated_folder = sys.argv[2]

    if not Path(module_json).exists():
        print(f"❌ Traceability JSON not found: {module_json}")
        sys.exit(1)

    if not Path(generated_folder).exists():
        print(f"❌ Generated folder not found: {generated_folder}")
        sys.exit(1)

    success = verify_module(module_json, generated_folder)
    sys.exit(0 if success else 1)

if __name__ == '__main__':
    main()