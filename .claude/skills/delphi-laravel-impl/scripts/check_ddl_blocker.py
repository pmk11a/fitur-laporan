#!/usr/bin/env python3
"""
check_ddl_blocker.py - Prevent schema modifications

Usage:
    python check_ddl_blocker.py <file.php>
    python check_ddl_blocker.py <folder>

Checks for forbidden DDL operations:
    - Schema::create()
    - Schema::table()
    - Schema::drop()
    - Schema::rename()
    - ALTER TABLE
    - DROP TABLE
    - Blueprint operations
"""

import sys
import re
from pathlib import Path

FORBIDDEN_PATTERNS = [
    (r'Schema::', 'Schema:: usage'),
    (r'->create\(', 'create() on table'),
    (r'->drop\(' , 'drop() on table'),
    (r'->table\(', 'table() in Blueprint'),
    (r'Blueprint', 'Blueprint usage'),
    (r'alter table', 'ALTER TABLE statement'),
    (r'drop table', 'DROP TABLE statement'),
    (r'migration', 'Migration class'),
]

def check_file(file_path):
    """Check a single file for DDL operations."""
    issues = []

    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    for i, line in enumerate(lines, 1):
        for pattern, description in FORBIDDEN_PATTERNS:
            if re.search(pattern, line, re.IGNORECASE):
                # Skip comments and strings
                stripped = line.strip()
                if stripped.startswith('//') or stripped.startswith('#') or stripped.startswith('*'):
                    continue
                if '"' in line and '//' not in line.split('"')[0]:
                    continue

                issues.append({
                    'line': i,
                    'content': line.rstrip(),
                    'description': description
                })

    return issues

def check_folder(folder_path):
    """Check all PHP files in a folder."""
    folder = Path(folder_path)
    all_issues = []

    for php_file in folder.rglob("*.php"):
        file_issues = check_file(php_file)
        if file_issues:
            all_issues.append({
                'file': str(php_file.relative_to(folder)),
                'issues': file_issues
            })

    return all_issues

def main():
    if len(sys.argv) < 2:
        print("Usage: python check_ddl_blocker.py <file.php|folder>")
        print("\nExamples:")
        print("  python check_ddl_blocker.py app/Models/User.php")
        print("  python check_ddl_blocker.py be-keu/app")
        sys.exit(1)

    target = Path(sys.argv[1])

    if not target.exists():
        print(f"❌ File/folder not found: {target}")
        sys.exit(1)

    print(f"\n🔍 Checking: {target}")
    print("="*60)

    if target.is_file():
        issues = check_file(target)
        if issues:
            print(f"\n❌ DDL BLOCKER TRIGGERED in {target}")
            for issue in issues:
                print(f"   Line {issue['line']}: {issue['description']}")
                print(f"   {issue['content']}")
            sys.exit(1)
        else:
            print(f"\n✅ No DDL operations found")

    else:
        issues = check_folder(target)
        if issues:
            print(f"\n❌ DDL BLOCKER TRIGGERED in {len(issues)} file(s)")
            for item in issues:
                print(f"\n   📁 {item['file']}")
                for issue in item['issues'][:3]:
                    print(f"      Line {issue['line']}: {issue['description']}")
            sys.exit(1)
        else:
            print(f"\n✅ No DDL operations found in any files")

if __name__ == '__main__':
    main()