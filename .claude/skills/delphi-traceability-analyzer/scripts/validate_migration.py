#!/usr/bin/env python3
"""
Validate Delphi to Laravel migration completeness.
Auto-checks for missing functions and provides gap analysis.
"""

import json
import argparse
from pathlib import Path
from typing import Dict, Any, List, Set
from collections import defaultdict


class MigrationValidator:
    """Validate migration completeness."""

    def __init__(self, traceability_file: Path, laravel_root: Path = None):
        self.traceability_file = traceability_file
        self.laravel_root = laravel_root
        self.data = self._load_traceability()

    def _load_traceability(self) -> Dict[str, Any]:
        """Load traceability JSON file."""
        with open(self.traceability_file, 'r', encoding='utf-8') as f:
            return json.load(f)

    def validate_status(self) -> Dict[str, Any]:
        """Check migration status of all functions."""
        results = {
            'total': 0,
            'migrated': 0,
            'pending': 0,
            'not_needed': 0,
            'no_target': 0,
            'targets_assigned': 0,
            'by_category': defaultdict(lambda: {'total': 0, 'migrated': 0, 'pending': 0, 'not_needed': 0}),
            'pending_functions': []
        }

        for module in self.data.get('modules', []):
            for func in module.get('functions', []):
                results['total'] += 1
                category = func.get('category', 'business_logic')
                results['by_category'][category]['total'] += 1

                target = func.get('laravel_target', {})
                status = target.get('status', 'pending')
                target_file = target.get('file', '')
                target_method = target.get('method', '')

                if status == 'migrated':
                    results['migrated'] += 1
                    results['by_category'][category]['migrated'] += 1
                    results['targets_assigned'] += 1
                elif status == 'not_needed':
                    # Frontend-only functions - correctly assigned
                    results['not_needed'] += 1
                    results['by_category'][category]['not_needed'] += 1
                    results['targets_assigned'] += 1
                elif not target_file or not target_method:
                    results['no_target'] += 1
                    results['pending'] += 1
                    results['by_category'][category]['pending'] += 1
                    results['pending_functions'].append({
                        'module': module.get('name'),
                        'function': func.get('name'),
                        'category': category,
                        'line': func.get('line')
                    })
                else:
                    results['pending'] += 1
                    results['by_category'][category]['pending'] += 1
                    results['targets_assigned'] += 1
                    results['pending_functions'].append({
                        'module': module.get('name'),
                        'function': func.get('name'),
                        'category': category,
                        'target': f"{target_file}@{target_method}",
                        'line': func.get('line')
                    })

        return results

    def check_laravel_files(self) -> Dict[str, Any]:
        """Check if Laravel target files exist (if laravel_root provided)."""
        if not self.laravel_root:
            return {'checked': False, 'reason': 'No Laravel root provided'}

        laravel_path = Path(self.laravel_root)
        if not laravel_path.exists():
            return {'checked': False, 'reason': 'Laravel root not found'}

        found_files = set()
        missing_files = set()

        for module in self.data.get('modules', []):
            for func in module.get('functions', []):
                target = func.get('laravel_target', {})
                target_file = target.get('file', '')

                if target_file:
                    # Convert Laravel path to filesystem
                    file_path = laravel_path / target_file
                    if file_path.exists():
                        found_files.add(target_file)
                    else:
                        missing_files.add(target_file)

        return {
            'checked': True,
            'found': len(found_files),
            'missing': len(missing_files),
            'missing_files': list(missing_files)
        }

    def generate_report(self) -> str:
        """Generate validation report."""
        status = self.validate_status()
        laravel_check = self.check_laravel_files()

        lines = []
        lines.append("=" * 60)
        lines.append("MIGRATION VALIDATION REPORT")
        lines.append("=" * 60)
        lines.append("")

        # Overall status
        total = status['total']
        assigned = status['targets_assigned']
        migrated = status['migrated']
        not_needed = status['not_needed']
        no_target = status['no_target']
        pending_with_target = assigned - migrated - not_needed
        percentage = int((assigned / total) * 100) if total > 0 else 0

        lines.append(f"Overall Progress: {assigned}/{total} ({percentage}%)")
        lines.append(f"  Migrated: {migrated} | Pending (with target): {pending_with_target} | Frontend-only: {not_needed} | No target: {no_target}")
        lines.append("")

        # By category
        lines.append("By Category:")
        lines.append("-" * 40)
        for category, counts in status['by_category'].items():
            cat_total = counts['total']
            cat_assigned = cat_total - counts['pending']
            cat_pct = int((cat_assigned / cat_total) * 100) if cat_total > 0 else 0
            lines.append(f"  {category}: {cat_assigned}/{cat_total} ({cat_pct}%)")
        lines.append("")

        # Functions without targets
        if status['no_target'] > 0:
            lines.append(f"WARNING: {status['no_target']} functions have NO Laravel target assigned!")
            lines.append("")
        else:
            lines.append("All targets assigned!")
            lines.append("")

        # Laravel files check
        if laravel_check['checked']:
            lines.append("Laravel Files Check:")
            lines.append(f"  Found: {laravel_check['found']}")
            lines.append(f"  Missing: {laravel_check['missing']}")
            if laravel_check['missing'] > 0:
                lines.append("  Missing files:")
                for f in laravel_check['missing_files'][:10]:
                    lines.append(f"    - {f}")
                if len(laravel_check['missing_files']) > 10:
                    lines.append(f"    ... and {len(laravel_check['missing_files']) - 10} more")
            lines.append("")

        # Pending functions
        pending_with_target = [f for f in status['pending_functions'] if 'target' in f]
        pending_no_target = [f for f in status['pending_functions'] if 'target' not in f]

        if pending_no_target:
            lines.append(f"Functions WITHOUT Target ({len(pending_no_target)}):")
            lines.append("-" * 40)
            for func in pending_no_target[:20]:
                info = f"{func['module']}::{func['function']}"
                lines.append(f"  [ ] {info}")
            if len(pending_no_target) > 20:
                lines.append(f"  ... and {len(pending_no_target) - 20} more")
            lines.append("")

        if pending_with_target:
            lines.append(f"Pending Functions with Target ({len(pending_with_target)}):")
            lines.append("-" * 40)
            for func in pending_with_target[:20]:
                info = f"{func['module']}::{func['function']} -> {func['target']}"
                lines.append(f"  [ ] {info}")
            if len(pending_with_target) > 20:
                lines.append(f"  ... and {len(pending_with_target) - 20} more")
            lines.append("")

        lines.append("=" * 60)

        return '\n'.join(lines)


def main():
    parser = argparse.ArgumentParser(
        description='Validate Delphi to Laravel migration'
    )
    parser.add_argument(
        'traceability',
        help='Traceability JSON file'
    )
    parser.add_argument(
        '-l', '--laravel',
        help='Laravel project root path (for file existence check)'
    )
    parser.add_argument(
        '-o', '--output',
        help='Save report to file'
    )

    args = parser.parse_args()

    validator = MigrationValidator(
        Path(args.traceability),
        Path(args.laravel) if args.laravel else None
    )

    report = validator.generate_report()

    if args.output:
        Path(args.output).write_text(report, encoding='utf-8')
        print(f"Report saved to: {args.output}")
    else:
        print(report)


if __name__ == '__main__':
    main()
