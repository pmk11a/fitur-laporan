#!/usr/bin/env python3
"""
Interactive target assignment for Delphi to Laravel migration.
Allows user to manually assign Laravel targets to functions.
"""

import json
import argparse
import re
from pathlib import Path
from typing import Dict, List, Optional


class InteractiveAssigner:
    """Interactive assignment of Laravel targets."""

    # Common service choices
    COMMON_SERVICES = [
        'App/Http/Controllers/{Controller}',
        'App/Services/{Service}',
        'App/Services/ValidationService',
        'App/Services/QueryService',
        'App/Services/DatabaseService',
        'App/Services/SubmissionService',
        'App/Services/LoanService',
        'App/Services/CustomerService',
        'App/Services/ProductService',
        'App/Services/PeriodLockService',
        'App/Services/NumberSequenceService',
        'App/Services/ExportService',
        'App/Services/ReportService',
        'App/Services/DeleteService',
        'Frontend-only (not_needed)',
    ]

    # Common method choices
    COMMON_METHODS = [
        'create', 'edit', 'destroy', 'save', 'delete',
        'validate', 'check', 'is', 'can',
        'getData', 'loadData', 'saveData', 'deleteData',
        'execute', 'handle', 'process',
        'export', 'print', 'display',
    ]

    def __init__(self, traceability_file: Path):
        self.traceability_file = traceability_file
        self.data = self._load_traceability()
        self.current_index = 0
        self.pending_functions = self._get_pending_functions()

    def _load_traceability(self) -> dict:
        """Load traceability JSON file."""
        with open(self.traceability_file, 'r', encoding='utf-8') as f:
            return json.load(f)

    def _get_pending_functions(self) -> List[Dict]:
        """Get list of functions that need target assignment."""
        pending = []

        for module in self.data.get('modules', []):
            module_name = module.get('name', '')

            for func in module.get('functions', []):
                target = func.get('laravel_target', {})
                status = target.get('status', 'pending')
                target_file = target.get('file', '')

                # Only include functions without target
                if not target_file and status != 'not_needed':
                    pending.append({
                        'module': module_name,
                        'function': func.get('name'),
                        'category': func.get('category'),
                        'line': func.get('line'),
                        'func_ref': func,
                        'module_ref': module
                    })

        return pending

    def _get_module_name(self, function_name: str) -> str:
        """Extract module name from function name."""
        match = re.match(r'T(?:Fr)?(\w+)', function_name.split('.')[-1])
        if match:
            return match.group(1)
        return ''

    def _infer_controller(self, module_name: str) -> str:
        """Infer controller name from module."""
        mappings = {
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
        return mappings.get(module_name, f'{module_name}Controller')

    def _infer_service(self, module_name: str) -> str:
        """Infer service name from module."""
        mappings = {
            'MainPengajuan': 'SubmissionService',
            'Pengajuan': 'SubmissionService',
            'Realisasi': 'RealizationService',
            'AktivaTetap': 'AssetService',
            'SubAktivaTetap': 'AssetService',
            'AksesPerkiraan': 'UserAccountService',
            'LockPeriod': 'PeriodLockService',
            'ProsesTutupBuku': 'YearEndClosingService',
        }
        return mappings.get(module_name, f'{module_name}Service')

    def _save(self):
        """Save changes to traceability file."""
        with open(self.traceability_file, 'w', encoding='utf-8') as f:
            json.dump(self.data, f, indent=2, ensure_ascii=False)

    def _show_current(self):
        """Show current function being assigned."""
        if self.current_index >= len(self.pending_functions):
            return False

        func = self.pending_functions[self.current_index]
        func_name_only = func['function'].split('.')[-1]

        print("\n" + "=" * 60)
        print(f"[{self.current_index + 1}/{len(self.pending_functions)}] {func['function']}")
        print(f"  Module: {func['module']}")
        print(f"  Category: {func['category']}")
        print(f"  Line: {func['line']}")
        print("=" * 60)

        # Show suggestions
        suggestions = self._get_suggestions(func)
        if suggestions:
            print("\nSuggestions:")
            for i, sug in enumerate(suggestions[:5], 1):
                print(f"  {i}. {sug['service']}@{sug['method']} ({sug['reason']})")

        return True

    def _get_suggestions(self, func: Dict) -> List[Dict]:
        """Get suggestions for current function."""
        func_name = func['function']
        func_name_only = func_name.split('.')[-1]
        module_name = func['module'].replace('Frm', '').replace('Fr', '')
        category = func['category']

        suggestions = []

        # Suggest based on function name patterns
        if 'Click' in func_name_only:
            controller = self._infer_controller(module_name)
            action = self._infer_action(func_name_only)

            if action in ['create', 'edit', 'destroy']:
                suggestions.append({
                    'service': f'App/Http/Controllers/{controller}',
                    'method': action,
                    'reason': 'CRUD operation'
                })
            elif action == 'save':
                service = self._infer_service(module_name)
                suggestions.append({
                    'service': f'App/Services/{service}',
                    'method': 'save',
                    'reason': 'Save operation'
                })
            else:
                suggestions.append({
                    'service': '',
                    'method': '',
                    'reason': 'Frontend-only button (Livewire/Alpine.js)',
                    'status': 'not_needed'
                })

        # Suggest based on category
        if category == 'validation':
            suggestions.append({
                'service': 'App/Services/ValidationService',
                'method': func_name_only,
                'reason': 'Validation function'
            })
        elif category == 'database':
            suggestions.append({
                'service': 'App/Services/QueryService',
                'method': 'execute',
                'reason': 'Database operation'
            })
        elif category == 'ui_lifecycle':
            suggestions.append({
                'service': '',
                'method': '',
                'reason': 'Form lifecycle (Livewire/Alpine.js)',
                'status': 'not_needed'
            })

        return suggestions

    def _infer_action(self, func_name: str) -> str:
        """Infer CRUD action from button name."""
        func_lower = func_name.lower()

        if any(x in func_lower for x in ['tambah', 'add', 'new', 'baru']):
            return 'create'
        elif any(x in func_lower for x in ['edit', 'koreksi', 'update']):
            return 'edit'
        elif any(x in func_lower for x in ['hapus', 'delete', 'del']):
            return 'destroy'
        elif any(x in func_lower for x in ['simpan', 'save', 'ok']):
            return 'save'
        elif any(x in func_lower for x in ['batal', 'cancel', 'close']):
            return 'cancel'

        return 'handle'

    def assign(self, service: str, method: str, status: str = 'pending', notes: str = ''):
        """Assign target to current function."""
        if self.current_index >= len(self.pending_functions):
            return False

        func = self.pending_functions[self.current_index]

        # Handle special case for frontend-only
        if service.lower() in ['frontend', 'frontend-only', 'not_needed', '-']:
            func['func_ref']['laravel_target'] = {
                'file': '',
                'method': '',
                'status': 'not_needed',
                'notes': notes or 'Frontend-only - use Livewire/Alpine.js'
            }
        else:
            # Ensure proper path format
            if not service.startswith('App/'):
                if 'Controller' in service or service.endswith('Controller'):
                    service = f'App/Http/Controllers/{service}'
                else:
                    service = f'App/Services/{service}'

            if not service.endswith('.php'):
                service += '.php'

            func['func_ref']['laravel_target'] = {
                'file': service,
                'method': method,
                'status': status,
                'notes': notes
            }

        self._save()
        return True

    def next(self):
        """Move to next function."""
        if self.current_index < len(self.pending_functions) - 1:
            self.current_index += 1
            return True
        return False

    def prev(self):
        """Move to previous function."""
        if self.current_index > 0:
            self.current_index -= 1
            return True
        return False

    def skip(self):
        """Skip current function (mark as pending without target)."""
        return self.next()

    def mark_all_not_needed(self, pattern: str = ''):
        """Mark multiple functions as not_needed based on pattern."""
        count = 0
        for func in self.pending_functions[self.current_index:]:
            func_name = func['function'].split('.')[-1]

            # Check if function matches pattern
            if pattern and pattern.lower() not in func_name.lower():
                continue

            # Auto-mark certain patterns as not_needed
            if any(x in func_name for x in ['Enter', 'KeyDown', 'Change', 'Show', 'Hide']):
                func['func_ref']['laravel_target'] = {
                    'file': '',
                    'method': '',
                    'status': 'not_needed',
                    'notes': 'Frontend-only - field event (Livewire/Alpine.js)'
                }
                count += 1

        self._save()
        return count


def print_help():
    """Print help for interactive mode."""
    print("\nCommands:")
    print("  1-NUMBER    - Choose suggestion from list")
    print("  service@method - Assign custom target (e.g., SubmissionController@create)")
    print("  service     - Assign service only (will prompt for method)")
    print("  - or n      - Next function")
    print("  p or b      - Previous function")
    print("  s or skip   - Skip (leave unassigned)")
    print("  fn          - Mark all Enter/KeyDown/Change as not_needed")
    print("  q or quit   - Save and quit")
    print("  help        - Show this help")


def main():
    parser = argparse.ArgumentParser(
        description='Interactive target assignment for Laravel migration'
    )
    parser.add_argument(
        'traceability',
        help='Traceability JSON file'
    )
    parser.add_argument(
        '--start',
        type=int,
        default=0,
        help='Start from function index'
    )

    args = parser.parse_args()

    assigner = InteractiveAssigner(Path(args.traceability))
    assigner.current_index = args.start

    if not assigner.pending_functions:
        print("All functions have targets assigned!")
        return

    print(f"Found {len(assigner.pending_functions)} functions without targets.")
    print_help()

    while True:
        if not assigner._show_current():
            print("\nAll functions processed!")
            break

        try:
            user_input = input("\nAssign target (or 'help'): ").strip()

            if not user_input:
                continue

            # Commands
            cmd_lower = user_input.lower()

            if cmd_lower in ['q', 'quit', 'exit']:
                print("\nSaving and exiting...")
                break

            elif cmd_lower in ['n', 'next', '-']:
                assigner.next()

            elif cmd_lower in ['p', 'prev', 'b', 'back']:
                assigner.prev()

            elif cmd_lower in ['s', 'skip']:
                assigner.skip()

            elif cmd_lower == 'fn':
                count = assigner.mark_all_not_needed()
                print(f"Marked {count} functions as not_needed")

            elif cmd_lower == 'help':
                print_help()

            # Number selection (choose suggestion)
            elif user_input.isdigit():
                idx = int(user_input) - 1
                suggestions = assigner._get_suggestions(assigner.pending_functions[assigner.current_index])
                if 0 <= idx < len(suggestions):
                    sug = suggestions[idx]
                    assigner.assign(
                        sug.get('service', ''),
                        sug.get('method', ''),
                        sug.get('status', 'pending'),
                        sug.get('reason', '')
                    )
                    print(f"Assigned: {sug.get('service', '')}@{sug.get('method', '')}")
                    assigner.next()
                else:
                    print(f"Invalid selection. Choose 1-{len(suggestions)}")

            # service@method format
            elif '@' in user_input:
                parts = user_input.split('@', 1)
                service = parts[0].strip()
                method = parts[1].strip()

                assigner.assign(service, method)
                print(f"Assigned: {service}@{method}")
                assigner.next()

            # Service name only
            else:
                service = user_input
                method = input(f"Method for {service}: ").strip()

                assigner.assign(service, method)
                print(f"Assigned: {service}@{method}")
                assigner.next()

        except KeyboardInterrupt:
            print("\n\nInterrupted. Saving progress...")
            break
        except Exception as e:
            print(f"\nError: {e}")
            print("Try again or type 'help'")

    print(f"\nDone! {len(assigner.pending_functions) - assigner.current_index - 1} functions remaining.")


if __name__ == '__main__':
    main()
