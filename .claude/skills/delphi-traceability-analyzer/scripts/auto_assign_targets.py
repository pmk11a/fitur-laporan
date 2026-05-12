#!/usr/bin/env python3
"""
Auto-assign Laravel targets to functions without targets.
Uses pattern matching to suggest appropriate services and methods.
"""

import json
import re
import argparse
from pathlib import Path
from typing import Dict, List, Tuple


class TargetAutoAssigner:
    """Auto-assign Laravel targets based on function patterns."""

    def __init__(self, traceability_file: Path):
        self.traceability_file = traceability_file
        self.data = self._load_traceability()
        self.assignments = 0

        # Pattern mappings for auto-assignment
        self.patterns = self._load_patterns()

    def _load_traceability(self) -> dict:
        """Load traceability JSON file."""
        with open(self.traceability_file, 'r', encoding='utf-8') as f:
            return json.load(f)

    def _load_patterns(self) -> Dict[str, Dict]:
        """Load pattern-to-service mappings."""
        return {
            # Validation patterns
            'Cek': {'service': 'ValidationService', 'method_prefix': 'validate'},
            'Check': {'service': 'ValidationService', 'method_prefix': 'validate'},
            'Validate': {'service': 'ValidationService', 'method_prefix': 'validate'},
            'Is': {'service': 'ValidationService', 'method_prefix': 'is'},
            'Can': {'service': 'ValidationService', 'method_prefix': 'can'},
            'isValid': {'service': 'ValidationService', 'method_prefix': 'isValid'},

            # CRUD operations
            'Tambah': {'service': '{Controller}', 'action': 'create'},
            'Simpan': {'service': '{Service}', 'method': 'save'},
            'Save': {'service': '{Service}', 'method': 'save'},
            'Edit': {'service': '{Controller}', 'action': 'edit'},
            'Koreksi': {'service': '{Controller}', 'action': 'edit'},
            'Hapus': {'service': '{Controller}', 'action': 'destroy'},
            'Delete': {'service': 'DeleteService', 'method': 'delete'},
            'Refresh': {'service': '{Controller}', 'action': 'refresh'},

            # Data operations
            'GetData': {'service': 'QueryService', 'method': 'getData'},
            'AmbilData': {'service': 'QueryService', 'method': 'getData'},
            'LoadData': {'service': 'QueryService', 'method': 'loadData'},
            'Tampil': {'service': 'QueryService', 'method': 'display'},
            'Show': {'service': 'QueryService', 'method': 'show'},

            # Export
            'Export': {'service': 'ExportService', 'method': 'export'},
            'Cetak': {'service': 'ReportService', 'method': 'print'},
            'Print': {'service': 'ReportService', 'method': 'print'},

            # Calculation operations
            'Hitung': {'service': 'CalculationService', 'method': 'calculate'},
            'Calculate': {'service': 'CalculationService', 'method': 'calculate'},
            'GetNilai': {'service': 'CalculationService', 'method': 'getValue'},

            # Button/Click events (usually CRUD)
            'btTambahClick': {'service': '{Controller}', 'action': 'create'},
            'btSimpanClick': {'service': '{Service}', 'method': 'save'},
            'btEditClick': {'service': '{Controller}', 'action': 'edit'},
            'btHapusClick': {'service': 'DeleteService', 'method': 'delete'},
            'btBatalClick': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Cancel button - frontend-only'},
            'BitBtn1Click': {'service': '{Service}', 'method': 'save', 'default_save': True},
            'BitBtn2Click': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Cancel button - frontend-only'},

            # Form lifecycle
            'FormShow': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - use Livewire/Alpine.js'},
            'FormClose': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - use Livewire/Alpine.js'},
            'FormCreate': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - use Livewire/Alpine.js'},
            'FormDestroy': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - use Livewire/Alpine.js'},
            'FormKeyDown': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - use Livewire/Alpine.js'},

            # Grid events
            'GridDblClick': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - grid double-click (Livewire)'},
            'GridClick': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - grid click (Livewire)'},
            'ColumnClick': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - grid column click (Livewire)'},
            'TitleButtonClick': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - grid sort (Livewire)'},

            # Field events
            'Enter': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - field enter (Livewire/Alpine.js)'},
            'Exit': {'service': 'ValidationService', 'method': 'validate'},
            'Change': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - field change (Livewire/Alpine.js)'},
            'KeyDown': {'service': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - key down (Livewire/Alpine.js)'},

            # Date operations
            'Tanggal': {'service': 'DateService', 'method_prefix': 'handle'},
            'Date': {'service': 'DateService', 'method_prefix': 'handle'},

            # Number/sequence operations
            'NoBukti': {'service': 'NumberSequenceService', 'method_prefix': 'generate'},
            'Nomor': {'service': 'NumberSequenceService', 'method_prefix': 'generate'},
            'Urut': {'service': 'NumberSequenceService', 'method_prefix': 'get'},
        }

    def _get_module_name(self, function_name: str) -> str:
        """Extract module name from function name (e.g., TFrMainPengajuan -> MainPengajuan)."""
        # Remove class prefix (TFr, TFr, T, etc.)
        match = re.match(r'T(?:Fr)?(\w+)', function_name)
        if match:
            return match.group(1)
        return ''

    def _get_form_name(self, function_name: str) -> str:
        """Extract form name from function name (e.g., TFrMainPengajuan -> FrmMainPengajuan)."""
        match = re.match(r'(T\w+)\.', function_name)
        if match:
            return match.group(1)
        return ''

    def _infer_controller_name(self, module_name: str, function_name: str) -> str:
        """Infer controller name from module or function name."""
        # Common mappings
        module_to_controller = {
            'MainPengajuan': 'SubmissionController',
            'Pengajuan': 'SubmissionController',
            'Realisasi': 'RealizationController',
            'AktivaTetap': 'AssetController',
            'SubAktivaTetap': 'SubAssetController',
            'SetAksesPerkiraan': 'UserAccountController',
            'LockPeriod': 'PeriodLockController',
            'ProsesTutupBuku': 'YearEndClosingController',
            'TutupTahun': 'YearEndClosingController',
            'Brows': 'BrowseController',
        }

        # Try direct module name mapping
        if module_name in module_to_controller:
            return module_to_controller[module_name]

        # Try without "Frm" prefix
        clean_name = module_name.replace('Frm', '').replace('Fr', '')
        if clean_name in module_to_controller:
            return module_to_controller[clean_name]

        # Default: convert to PascalCase + "Controller"
        return f'{module_name}Controller'

    def _infer_service_name(self, module_name: str, function_name: str) -> str:
        """Infer service name from module or function name."""
        # Common mappings
        module_to_service = {
            'MainPengajuan': 'SubmissionService',
            'Pengajuan': 'SubmissionService',
            'Realisasi': 'RealizationService',
            'AktivaTetap': 'AssetService',
            'SubAktivaTetap': 'AssetService',
            'AksesPerkiraan': 'UserAccountService',
            'LockPeriod': 'PeriodLockService',
            'ProsesTutupBuku': 'YearEndClosingService',
        }

        if module_name in module_to_service:
            return module_to_service[module_name]

        # Default: convert to PascalCase + "Service"
        clean_name = module_name.replace('Frm', '').replace('Fr', '')
        return f'{clean_name}Service'

    def _suggest_target(self, func_name: str, category: str, module_name: str) -> Dict:
        """Suggest Laravel target for a function."""
        func_name_only = func_name.split('.')[-1]  # Get only the function name part

        # Check patterns - longest match first
        matches = []
        for pattern, config in self.patterns.items():
            if pattern in func_name_only:
                matches.append((pattern, config, len(pattern)))

        if matches:
            # Sort by pattern length (descending) - longest pattern wins
            _, config, _ = max(matches, key=lambda x: x[2])

            # Handle special cases
            if config.get('status') == 'not_needed':
                return {
                    'file': '',
                    'method': '',
                    'status': 'not_needed',
                    'notes': config.get('notes', 'Frontend-only')
                }

            service = config.get('service', '')
            method = config.get('method', '')
            action = config.get('action', '')

            # Handle {Controller} placeholder
            if '{Controller}' in service:
                service = self._infer_controller_name(module_name, func_name)
                if action:
                    method = action
                else:
                    method = self._toCamelCase(func_name_only.replace('Click', '').replace('bt', '').replace('BitBtn', ''))

            # Handle {Service} placeholder
            elif '{Service}' in service:
                service = self._infer_service_name(module_name, func_name)
                if not method:
                    method = config.get('method_prefix', '') + self._toCamelCase(func_name_only.replace('Click', ''))
                    if method and method[0].islower():
                        method = method[0].upper() + method[1:]

            # Handle method prefix
            elif config.get('method_prefix'):
                prefix = config['method_prefix']
                rest = func_name_only.replace(pattern, '')
                method = prefix + self._toCamelCase(rest)

            return {
                'file': f'App/Http/Controllers/{service}' if 'Controller' in service else f'App/Services/{service}',
                'method': method or func_name_only,
                'status': 'pending'
            }

        # Fallback based on category
        if category == 'validation':
            return {'file': 'App/Services/ValidationService.php', 'method': func_name_only, 'status': 'pending'}
        elif category == 'database':
            return {'file': 'App/Services/QueryService.php', 'method': 'execute', 'status': 'pending'}
        elif category == 'ui_lifecycle':
            return {'file': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - use Livewire/Alpine.js'}
        elif category == 'ui_only':
            return {'file': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - UI only'}
        elif category == 'event_handler':
            # Check if it's a button click
            if 'Click' in func_name_only:
                # Might be CRUD - assign to controller
                controller = self._infer_controller_name(module_name, func_name)
                action = self._infer_action_from_button(func_name_only)
                return {
                    'file': f'App/Http/Controllers/{controller}',
                    'method': action,
                    'status': 'pending',
                    'notes': f'{action} operation'
                }
            return {'file': '', 'method': '', 'status': 'not_needed', 'notes': 'Frontend-only - event handler (Livewire/Alpine.js)'}

        # Default fallback
        return {'file': '', 'method': '', 'status': 'pending'}

    def _infer_action_from_button(self, func_name: str) -> str:
        """Infer CRUD action from button name."""
        func_lower = func_name.lower()

        if any(x in func_lower for x in ['tambah', 'add', 'new', 'baru', 'create', 'bt1', 'button1']):
            return 'create'
        elif any(x in func_lower for x in ['edit', 'koreksi', 'update', 'ubah', 'bt2', 'button2']):
            return 'edit'
        elif any(x in func_lower for x in ['hapus', 'delete', 'del', 'remove', 'bt3', 'button3']):
            return 'destroy'
        elif any(x in func_lower for x in ['refresh', 'reload', 'load', 'bt4', 'button4']):
            return 'refresh'
        elif any(x in func_lower for x in ['simpan', 'save', 'ok']):
            return 'save'
        elif any(x in func_lower for x in ['batal', 'cancel', 'close']):
            return 'cancel'

        return 'handle'

    def _toCamelCase(self, text: str) -> str:
        """Convert text to camelCase."""
        if not text:
            return ''
        # Remove common prefixes
        for prefix in ['bt', 'Button', 'BitBtn', 'ToolButton', 'SpeedButton']:
            if text.startswith(prefix):
                text = text[len(prefix):]
                break

        if not text:
            return ''

        # First letter lowercase, rest as-is
        return text[0].lower() + text[1:] if text else ''

    def assign_targets(self, dry_run: bool = False) -> int:
        """Auto-assign targets to functions without them."""
        assigned = 0

        for module in self.data.get('modules', []):
            module_name = module.get('name', '').replace('Frm', '').replace('Fr', '')

            for func in module.get('functions', []):
                target = func.get('laravel_target', {})
                status = target.get('status', 'pending')
                target_file = target.get('file', '')

                # Skip if already has target or is not_needed
                if target_file or status == 'not_needed':
                    continue

                # Suggest target
                func_name = func.get('name', '')
                category = func.get('category', 'business_logic')

                suggestion = self._suggest_target(func_name, category, module_name)

                if dry_run:
                    print(f"  {func_name} -> {suggestion.get('file', '')}@{suggestion.get('method', '')} ({suggestion.get('status', '')})")
                else:
                    func['laravel_target'] = suggestion
                    assigned += 1

        if not dry_run and assigned > 0:
            self._save_traceability()
            print(f"Auto-assigned {assigned} targets.")

        return assigned

    def _save_traceability(self):
        """Save updated traceability data."""
        with open(self.traceability_file, 'w', encoding='utf-8') as f:
            json.dump(self.data, f, indent=2, ensure_ascii=False)


def main():
    parser = argparse.ArgumentParser(
        description='Auto-assign Laravel targets to functions'
    )
    parser.add_argument(
        'traceability',
        help='Traceability JSON file'
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help='Show suggestions without modifying file'
    )

    args = parser.parse_args()

    assigner = TargetAutoAssigner(Path(args.traceability))

    if args.dry_run:
        print("Dry run - showing suggestions:")
        assigner.assign_targets(dry_run=True)
    else:
        print("Auto-assigning targets...")
        count = assigner.assign_targets()
        print(f"Done! Assigned {count} targets.")


if __name__ == '__main__':
    main()
