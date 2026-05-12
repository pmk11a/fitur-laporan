#!/usr/bin/env python3
"""
Migration Progress Dashboard

Track Delphi to Laravel migration progress by analyzing:
1. Memory folder (scan_*.md, generation_*.md)
2. Delphi source files
3. Traceability files

Usage:
    python migration_progress.py [--memory=PATH] [--source=PATH]
"""

import os
import re
import json
from pathlib import Path
from collections import defaultdict
from datetime import datetime

# Default paths
DEFAULT_MEMORY = r"D:\Koperasi\memory"
DEFAULT_SOURCE = r"D:\Koperasi\KSP"
DEFAULT_TRACEABILITY = r"D:\Koperasi\docs\traceability"

# Module categories mapping
CATEGORIES = {
    "Master": ["Master", "Pemakai", "Produk", "Customer", "Supplier", "Perkiraan",
               "Aktiva", "Neraca", "LR", "Posting", "Budget", "SaldoAwal"],
    "Transaksi": ["Transaksi", "Pengajuan", "Pembayaran", "Penjualan", "Pembelian",
                  "KasBank", "Memorial", "Realisasi"],
    "Report": ["Report", "Preview", "Cetak", "Laporan"],
    "Setup": ["Setup", "Setting", "Lock", "Period", "Tutup"],
    "System": ["Main", "Menu", "Utama", "Login", "Password"]
}


class MigrationProgress:
    def __init__(self, memory_path=None, source_path=None, traceability_path=None):
        self.memory_path = Path(memory_path or DEFAULT_MEMORY)
        self.source_path = Path(source_path or DEFAULT_SOURCE)
        self.traceability_path = Path(traceability_path or DEFAULT_TRACEABILITY)

        self.scanned = {}
        self.generated = {}
        self.all_modules = {}
        self.traceability = {}

        self._load_data()

    def _load_data(self):
        """Load all data from memory and source"""
        self._load_memory()
        self._load_source_files()
        self._load_traceability()

    def _load_memory(self):
        """Load scan and generation files from memory folder"""
        # Load scan_*.md files
        for f in self.memory_path.glob("scan_*.md"):
            name = f.stem.replace("scan_", "")
            self.scanned[name.lower()] = {
                "file": f,
                "date": datetime.fromtimestamp(f.stat().st_mtime),
                "name": name
            }

        # Load generation_*.md files
        for f in self.memory_path.glob("generation_*.md"):
            name = f.stem.replace("generation_", "")
            self.generated[name.lower()] = {
                "file": f,
                "date": datetime.fromtimestamp(f.stat().st_mtime),
                "name": name
            }

    def _load_source_files(self):
        """Scan Delphi source directory for all .pas files"""
        if not self.source_path.exists():
            return

        for pas_file in self.source_path.rglob("*.pas"):
            # Extract module name from path
            rel_path = pas_file.relative_to(self.source_path)
            parts = rel_path.parts

            # Get parent folder as module name
            if len(parts) >= 2:
                folder = parts[-2]  # Parent folder
                file_stem = pas_file.stem.lower()

                if folder not in self.all_modules:
                    self.all_modules[folder] = []

                self.all_modules[folder].append({
                    "file": pas_file,
                    "name": pas_file.stem,
                    "path": str(rel_path)
                })

    def _load_traceability(self):
        """Load traceability JSON files"""
        if not self.traceability_path.exists():
            return

        for f in self.traceability_path.glob("*.json"):
            try:
                with open(f, 'r', encoding='utf-8') as fp:
                    data = json.load(fp)
                    name = f.stem.lower()
                    self.traceability[name] = data
            except:
                pass

    def _categorize(self, module_name):
        """Categorize module based on name patterns"""
        name_upper = module_name.upper()

        for category, keywords in CATEGORIES.items():
            for keyword in keywords:
                if keyword.upper() in name_upper:
                    return category

        return "Other"

    def get_summary(self):
        """Get overall migration summary"""
        # Count unique modules
        scanned_modules = set(self.scanned.keys())
        generated_modules = set(self.generated.keys())

        # Get all potential modules from source
        all_potential = set()
        for folder in self.all_modules.keys():
            all_potential.add(folder.lower())

        # Count statuses
        total = len(all_potential) + len(scanned_modules)  # Approximation
        migrated = len(scanned_modules & generated_modules)
        in_progress = len(scanned_modules) - migrated
        pending = total - len(scanned_modules)

        return {
            "total": total,
            "migrated": migrated,
            "in_progress": in_progress,
            "pending": pending,
            "percentage": (migrated / total * 100) if total > 0 else 0
        }

    def get_by_category(self):
        """Get progress breakdown by category"""
        category_stats = defaultdict(lambda: {
            "scanned": 0,
            "generated": 0,
            "modules": []
        })

        # Process all modules
        all_names = set(self.scanned.keys()) | set(self.generated.keys())

        for name in all_names:
            category = self._categorize(name)
            is_scanned = name in self.scanned
            is_generated = name in self.generated

            if is_scanned:
                category_stats[category]["scanned"] += 1
            if is_generated:
                category_stats[category]["generated"] += 1

            category_stats[category]["modules"].append({
                "name": name,
                "scanned": is_scanned,
                "generated": is_generated
            })

        return dict(category_stats)

    def get_pending_priority(self):
        """Get pending modules sorted by priority"""
        pending = []

        # Find modules in source that aren't scanned yet
        for folder, files in self.all_modules.items():
            if folder.lower() not in self.scanned:
                # Count files to estimate complexity
                pending.append({
                    "name": folder,
                    "files": len(files),
                    "category": self._categorize(folder),
                    "path": f"KSP/{files[0]['path'].split('/')[0]}/{folder}"
                })

        # Sort by file count (more files = higher priority typically)
        pending.sort(key=lambda x: x["files"], reverse=True)

        return pending[:10]  # Top 10

    def get_recent_activity(self, limit=10):
        """Get recent scan/generation activity"""
        activity = []

        for name, info in self.scanned.items():
            activity.append({
                "type": "scan",
                "name": name,
                "date": info["date"]
            })

        for name, info in self.generated.items():
            activity.append({
                "type": "generation",
                "name": name,
                "date": info["date"]
            })

        activity.sort(key=lambda x: x["date"], reverse=True)
        return activity[:limit]

    def print_report(self):
        """Print formatted progress report"""
        print("\n" + "="*60)
        print(" KSP DELPHI TO LARAVEL MIGRATION PROGRESS")
        print("="*60)
        print(f"  Generated: {datetime.now().strftime('%Y-%m-%d %H:%M')}")
        print()

        # Summary
        summary = self.get_summary()
        print(" OVERALL PROGRESS")
        print("-" * 40)
        print(f"  Total Modules:  {summary['total']}")
        print(f"  Migrated:       {summary['migrated']} ({summary['percentage']:.0f}%)")
        print(f"  In Progress:    {summary['in_progress']}")
        print(f"  Pending:        {summary['pending']}")
        print()

        # Progress bar (ASCII safe for Windows)
        bar_width = 30
        filled = int(bar_width * summary['percentage'] / 100)
        bar = "#" * filled + "-" * (bar_width - filled)
        print(f"  [{bar}] {summary['percentage']:.0f}%")
        print()

        # By Category
        print(" BY CATEGORY")
        print("-" * 40)
        category_stats = self.get_by_category()

        # Sort by total scanned
        sorted_cats = sorted(category_stats.items(),
                            key=lambda x: x[1]["scanned"],
                            reverse=True)

        for category, stats in sorted_cats:
            if stats["scanned"] > 0:
                pct = (stats["generated"] / stats["scanned"] * 100) if stats["scanned"] > 0 else 0
                print(f"  {category:12} {stats['generated']:2}/{stats['scanned']:2} ({pct:.0f}%)")
        print()

        # Pending Priority
        pending = self.get_pending_priority()
        if pending:
            print(" PENDING HIGH-PRIORITY MODULES")
            print("-" * 40)
            for i, mod in enumerate(pending[:5], 1):
                print(f"  {i}. {mod['name']:20} ({mod['category']:10}) - {mod['files']} files")
            print()

        # Recent Activity
        recent = self.get_recent_activity(5)
        if recent:
            print(" RECENT ACTIVITY")
            print("-" * 40)
            for act in recent:
                date_str = act['date'].strftime('%m/%d %H:%M')
                type_symbol = "[SCAN]" if act['type'] == 'scan' else "[GEN]"
                print(f"  {date_str} {type_symbol} {act['name']}")
            print()

        print("="*60)
        print()


def main():
    import argparse

    parser = argparse.ArgumentParser(description="Show Delphi to Laravel migration progress")
    parser.add_argument("--memory", help="Path to memory folder", default=DEFAULT_MEMORY)
    parser.add_argument("--source", help="Path to KSP source folder", default=DEFAULT_SOURCE)
    parser.add_argument("--traceability", help="Path to traceability folder", default=DEFAULT_TRACEABILITY)
    parser.add_argument("--json", action="store_true", help="Output as JSON")

    args = parser.parse_args()

    progress = MigrationProgress(
        memory_path=args.memory,
        source_path=args.source,
        traceability_path=args.traceability
    )

    if args.json:
        import json
        data = {
            "summary": progress.get_summary(),
            "by_category": progress.get_by_category(),
            "pending": progress.get_pending_priority(),
            "recent": progress.get_recent_activity()
        }
        print(json.dumps(data, indent=2, default=str))
    else:
        progress.print_report()


if __name__ == "__main__":
    main()
