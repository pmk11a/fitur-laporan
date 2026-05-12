#!/usr/bin/env python3
"""
Batch scan multiple Delphi forms for traceability analysis.
Scans all .pas files in a directory or pattern, generating individual traceability files.
"""

import json
import argparse
import sys
import subprocess
from pathlib import Path
from typing import List, Dict
from collections import defaultdict


class BatchScanner:
    """Scan multiple Delphi forms in batch."""

    def __init__(self, source_pattern: str, output_dir: str = 'docs/traceability', project_name: str = 'KSP'):
        self.source_pattern = Path(source_pattern)
        self.output_dir = Path(output_dir)
        self.project_name = project_name
        self.results = []

    def find_scan_files(self) -> List[Path]:
        """Find all .pas and .dfm files to scan."""
        files = []

        if self.source_pattern.is_file():
            # Single file
            if self.source_pattern.suffix.lower() in ['.pas', '.dfm']:
                files.append(self.source_pattern)
        elif self.source_pattern.is_dir():
            # Directory - scan for all pas files
            files.extend(self.source_pattern.rglob('*.pas'))
            # Also get corresponding dfm files
            dfm_files = self.source_pattern.rglob('*.dfm')
            files.extend(dfm_files)
        else:
            # Pattern - use glob
            files.extend(Path('.').glob(str(self.source_pattern)))

        # Group pas/dfm pairs
        grouped = defaultdict(list)
        for f in files:
            # Get base name without extension
            base = f.stem.lower()
            grouped[base].append(f)

        # Return unique pairs (pas + dfm)
        result = []
        for base, file_list in grouped.items():
            result.extend(file_list)

        return sorted(set(result))

    def scan_file(self, pas_file: Path, dfm_file: Path = None) -> Dict:
        """Scan a single Delphi form."""
        scan_script = Path(__file__).parent / 'scan_delphi.py'
        output_name = pas_file.stem.lower()

        # Build command
        cmd = [sys.executable, str(scan_script)]
        if dfm_file and dfm_file.exists():
            cmd.extend([str(pas_file), str(dfm_file)])
        else:
            cmd.append(str(pas_file))

        cmd.extend(['-p', self.project_name, '-d', str(self.output_dir)])

        # Run scan
        print(f"  Scanning {pas_file.name}...")
        try:
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=60
            )

            if result.returncode == 0:
                # Read the generated JSON to get summary
                json_file = self.output_dir / f'{output_name}.json'
                if json_file.exists():
                    with open(json_file, 'r', encoding='utf-8') as f:
                        data = json.load(f)
                        return {
                            'file': pas_file.name,
                            'module': data.get('modules', [{}])[0].get('name', output_name),
                            'functions': data.get('summary', {}).get('total_functions', 0),
                            'status': 'success'
                        }
                return {'file': pas_file.name, 'status': 'success', 'functions': 0}
            else:
                return {'file': pas_file.name, 'status': 'error', 'error': result.stderr}

        except subprocess.TimeoutExpired:
            return {'file': pas_file.name, 'status': 'timeout'}
        except Exception as e:
            return {'file': pas_file.name, 'status': 'error', 'error': str(e)}

    def scan_all(self) -> List[Dict]:
        """Scan all found files."""
        files_to_scan = self.find_scan_files()

        if not files_to_scan:
            print(f"No .pas files found in {self.source_pattern}")
            return []

        # Group files by base name (pas + dfm pairs)
        grouped = defaultdict(dict)
        for f in files_to_scan:
            base = f.stem.lower()
            ext = f.suffix.lower()
            grouped[base][ext] = f

        print(f"Found {len(grouped)} form(s) to scan:")
        for base in sorted(grouped.keys())[:10]:
            print(f"  - {base}")
        if len(grouped) > 10:
            print(f"  ... and {len(grouped) - 10} more")
        print()

        # Scan each form
        results = []
        for base, files in sorted(grouped.items()):
            pas_file = files.get('.pas')
            dfm_file = files.get('.dfm')

            if pas_file:
                result = self.scan_file(pas_file, dfm_file)
                results.append(result)

        return results

    def generate_summary(self, results: List[Dict]) -> str:
        """Generate batch scan summary."""
        lines = [
            "=" * 60,
            "BATCH SCAN SUMMARY",
            "=" * 60,
            "",
            f"Total Forms Scanned: {len(results)}",
            ""
        ]

        # Count by status
        status_counts = defaultdict(int)
        total_functions = 0
        modules = []

        for r in results:
            status_counts[r['status']] += 1
            total_functions += r.get('functions', 0)
            if 'module' in r:
                modules.append(r['module'])

        lines.append("Results by Status:")
        lines.append("-" * 40)
        for status, count in sorted(status_counts.items()):
            lines.append(f"  {status}: {count}")
        lines.append("")

        lines.append(f"Total Functions Extracted: {total_functions}")
        lines.append("")
        lines.append("Modules:")
        lines.append("-" * 40)
        for m in sorted(set(modules)):
            lines.append(f"  - {m}")
        lines.append("")

        # List output files
        lines.append("Output Files:")
        lines.append("-" * 40)
        for r in results:
            if r['status'] == 'success':
                base = Path(r['file']).stem.lower()
                lines.append(f"  - {base}.json")
                lines.append(f"    {base}.csv")
                lines.append(f"    {base}.md")
                lines.append(f"    {base}-migration.md")

        lines.append("")
        lines.append("=" * 60)

        return "\n".join(lines)


def main():
    parser = argparse.ArgumentParser(
        description='Batch scan Delphi forms for traceability analysis'
    )
    parser.add_argument(
        'source',
        help='Source pattern (file, directory, or glob pattern like "KSP/**/*.pas")'
    )
    parser.add_argument(
        '-o', '--output-dir',
        default='docs/traceability',
        help='Output directory (default: docs/traceability)'
    )
    parser.add_argument(
        '-p', '--project',
        default='KSP',
        help='Project name (default: KSP)'
    )
    parser.add_argument(
        '--auto-assign',
        action='store_true',
        help='Auto-assign Laravel targets after scan'
    )
    parser.add_argument(
        '--summary-only',
        action='store_true',
        help='Only show summary, don\'t scan'
    )

    args = parser.parse_args()

    scanner = BatchScanner(args.source, args.output_dir, args.project)

    if args.summary_only:
        files = scanner.find_scan_files()
        grouped = defaultdict(dict)
        for f in files:
            base = f.stem.lower()
            ext = f.suffix.lower()
            grouped[base][ext] = f

        print(f"Found {len(grouped)} form(s) in {args.source}:")
        for base in sorted(grouped.keys()):
            print(f"  - {base}")
        return

    results = scanner.scan_all()

    # Print summary
    print("\n")
    print(scanner.generate_summary(results))

    # Auto-assign targets if requested
    if args.auto_assign:
        print("\nAuto-assigning Laravel targets...")
        auto_assign_script = Path(__file__).parent / 'auto_assign_targets.py'

        for r in results:
            if r['status'] == 'success':
                base = Path(r['file']).stem.lower()
                json_file = Path(args.output_dir) / f'{base}.json'

                if json_file.exists():
                    print(f"  Assigning targets for {base}...")
                    subprocess.run(
                        [sys.executable, str(auto_assign_script), str(json_file)],
                        capture_output=True
                    )

        print("\nAuto-assign complete!")

    # Run validation on all generated files
    print("\nRunning migration validation...")
    validate_script = Path(__file__).parent / 'validate_migration.py'

    for r in results:
        if r['status'] == 'success':
            base = Path(r['file']).stem.lower()
            json_file = Path(args.output_dir) / f'{base}.json'

            if json_file.exists():
                subprocess.run(
                    [sys.executable, str(validate_script), str(json_file)],
                    capture_output=True
                )


if __name__ == '__main__':
    main()
