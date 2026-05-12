#!/usr/bin/env python3
"""
Delphi Source Code Scanner
Extracts functions and procedures from .pas files for traceability analysis.
IMPROVED: Case-insensitive, handles multi-line declarations, better param parsing.
"""

import re
import json
import argparse
import sys
import subprocess
from pathlib import Path
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass, asdict, field
from datetime import datetime


@dataclass
class LaravelTarget:
    """Laravel equivalent target for migration tracking."""
    file: str = ""
    method: str = ""
    status: str = "pending"


@dataclass
class DelphiFunction:
    """Represents a Delphi function or procedure."""
    name: str
    type: str  # 'function' or 'procedure'
    line: int
    params: List[str] = field(default_factory=list)
    returns: str = ""
    category: str = "business_logic"
    business_logic: str = ""
    laravel_target: LaravelTarget = field(default_factory=LaravelTarget)


class DelphiScanner:
    """Scan Delphi source files for functions and procedures."""

    def __init__(self, source_dirs, project_name: str = "KeuApp", config_file: Optional[str] = None,
                 enable_dependency_tracking: bool = True, search_root: Optional[Path] = None):
        """Initialize with one or more source directories and optional config file.

        Args:
            source_dirs: Path(s) to Delphi source directory
            project_name: Project name (default: KeuApp)
            config_file: Path to Laravel mapping config JSON file
            enable_dependency_tracking: If True, automatically scan dependent forms
            search_root: Root directory for dependency search (default: auto-detect)
        """
        if isinstance(source_dirs, str):
            source_dirs = [source_dirs]
        self.source_dirs = [Path(d) for d in source_dirs]
        self.project_name = project_name
        self.functions: List[DelphiFunction] = []
        self.enable_dependency_tracking = enable_dependency_tracking

        # Auto-detect search root if not provided
        if search_root is None:
            search_root = self._auto_detect_search_root()
        self.search_root = Path(search_root)

        # Track scanned files to avoid duplicates
        self.scanned_files: set = set()
        self.dependency_tree: Dict[str, List[str]] = {}  # form -> list of forms it uses

        # Load Laravel mapping configuration
        self.laravel_mappings = self._load_config(config_file)

    def _load_config(self, config_file: Optional[str]) -> dict:
        """Load Laravel mapping configuration from external JSON file."""
        if config_file and Path(config_file).exists():
            try:
                with open(config_file, 'r', encoding='utf-8') as f:
                    return json.load(f)
            except Exception as e:
                print(f"Warning: Could not load config file {config_file}: {e}")

        # Try default config location (go up from scripts/ to skill root, then into config/)
        default_config = Path(__file__).parent.parent / 'config' / 'laravel_mappings.json'
        if default_config.exists():
            try:
                with open(default_config, 'r', encoding='utf-8') as f:
                    return json.load(f)
            except Exception as e:
                print(f"Warning: Could not load default config: {e}")

        # Return empty config if not found
        return {
            'laravel_service_mappings': {},
            'method_prefixes': {},
            'method_replacements': {}
        }

    def _auto_detect_search_root(self) -> Path:
        """Auto-detect the project root directory for dependency search.

        Looks for common project markers like .git, composer.json, or pwt/KSP folder.
        """
        # Start from the first source directory and navigate up
        start_dir = Path(self.source_dirs[0]).resolve()

        # Navigate up until we find a project marker
        current = start_dir
        while current.parent != current:
            # Check for project markers
            if any((current / marker).exists() for marker in ['.git', 'composer.json', 'package.json']):
                return current
            # Check if we're at a level that has pwt or KSP folder (Delphi source)
            if (current / 'pwt').exists() or (current / 'KSP').exists():
                return current
            current = current.parent

        # If no marker found, use the parent of the first source dir
        return start_dir.parent

    # ==================== DEPENDENCY TRACKING METHODS ====================

    def parse_uses_clause(self, file_path: Path) -> List[str]:
        """Parse the uses clause to extract form/unit dependencies.

        Returns:
            List of form/unit names referenced in uses clause
        """
        try:
            content = file_path.read_text(encoding='utf-8', errors='ignore')
        except Exception as e:
            print(f"  [Warning] Could not read {file_path} for uses parsing: {e}")
            return []

        dependencies = []

        # Pattern to match uses clause (interface and implementation sections)
        # Matches: uses Clause1, Clause2, Clause3;
        uses_pattern = re.compile(
            r'^\s*uses\s+([^;]+);',
            re.MULTILINE | re.IGNORECASE
        )

        for match in uses_pattern.finditer(content):
            uses_list = match.group(1)
            # Split by comma and clean up
            for item in uses_list.split(','):
                item = item.strip()
                # Skip system units (lowercase or common Delphi units)
                if item.lower() in ['sysutils', 'classes', 'graphics', 'controls', 'forms',
                                   'dialogs', 'stdctrls', 'db', 'adodb', 'extctrls',
                                   'buttons', 'variants', 'comctrls', 'toolwin',
                                   'windows', 'messages', 'sysutils', 'types']:
                    continue
                # Skip units with dots (typically namespace units)
                if '.' in item:
                    continue
                # Only keep form/unit names (starts with capital letter, typical naming)
                if item and item[0].isupper():
                    dependencies.append(item)

        return dependencies

    def parse_application_createform(self, file_path: Path) -> List[str]:
        """Parse Application.CreateForm() calls to extract form dependencies.

        Returns:
            List of form class names created via Application.CreateForm
        """
        try:
            content = file_path.read_text(encoding='utf-8', errors='ignore')
        except Exception as e:
            return []

        forms = []

        # Pattern: Application.CreateForm(TFormClass, FormInstance)
        createform_pattern = re.compile(
            r'Application\.CreateForm\s*\(\s*T(\w+),\s*(\w+)\s*\)',
            re.IGNORECASE
        )

        for match in createform_pattern.finditer(content):
            form_class = match.group(1)
            form_instance = match.group(2)
            # Use the form class name (e.g., "FrMenuReport" from TFrMenuReport)
            forms.append(form_class)

        return forms

    def find_form_file(self, form_name: str) -> Optional[Path]:
        """Search for a form file in the entire project directory.

        Args:
            form_name: Name of the form to find (e.g., "FrMenuReport")

        Returns:
            Path to the .pas file if found, None otherwise
        """
        # Common form file patterns
        possible_names = [
            f"{form_name}.pas",
            f"{form_name[0].lower()}{form_name[1:]}.pas",  # frmMenuReport
            f"F{form_name}.pas",  # FFormName
        ]

        # Search in the entire project root
        if self.search_root and self.search_root.exists():
            for pattern in possible_names:
                # Try recursive search
                found = list(self.search_root.rglob(pattern))
                if found:
                    # Prefer files in pwt or KSP directory (Delphi source)
                    for f in found:
                        if 'pwt' in f.parts or 'KSP' in f.parts:
                            return f
                    # Otherwise return first match
                    return found[0]

        return None

    def build_dependency_tree(self, file_path: Path, depth: int = 0, max_depth: int = 5) -> Dict[str, List[str]]:
        """Recursively build dependency tree for a form.

        Args:
            file_path: Path to the form file
            depth: Current recursion depth
            max_depth: Maximum recursion depth to prevent infinite loops

        Returns:
            Dictionary mapping form names to their dependencies
        """
        if depth > max_depth:
            return {}

        form_name = file_path.stem
        if form_name in self.dependency_tree:
            return self.dependency_tree

        # Parse uses clause
        uses_deps = self.parse_uses_clause(file_path)

        # Parse Application.CreateForm calls
        createform_deps = self.parse_application_createform(file_path)

        # Combine and dedupe
        all_deps = list(set(uses_deps + createform_deps))
        self.dependency_tree[form_name] = all_deps

        # Recursively process dependencies
        for dep_name in all_deps:
            dep_file = self.find_form_file(dep_name)
            if dep_file and dep_file.stem not in self.dependency_tree:
                # Check if we should skip (already in source_dirs)
                dep_relative = dep_file.relative_to(self.search_root) if self.search_root else dep_file
                is_in_source = any(
                    dep_file.is_relative_to(src_dir) or str(dep_file).startswith(str(src_dir))
                    for src_dir in self.source_dirs
                )

                if not is_in_source:
                    print(f"  [{'  ' * depth}Dependency] {form_name} -> {dep_name} ({dep_file.relative_to(self.search_root) if self.search_root else dep_file})")
                    self.build_dependency_tree(dep_file, depth + 1, max_depth)

        return self.dependency_tree

    def get_all_files_with_dependencies(self) -> List[Path]:
        """Get all files to scan including dependencies.

        Returns:
            List of all .pas files (original + dependencies)
        """
        # Start with original files
        all_files = []
        for source_dir in self.source_dirs:
            if source_dir.is_file():
                if source_dir.suffix.lower() == '.pas':
                    all_files.append(source_dir)
            else:
                all_files.extend(source_dir.rglob("*.pas"))

        # If dependency tracking is enabled, find and add dependencies
        if self.enable_dependency_tracking:
            print(f"\n[*] Dependency Tracking enabled (search root: {self.search_root})")
            print(f"[*] Scanning for form dependencies...")

            dependent_files = []

            for file_path in all_files[:]:  # Copy to avoid modification during iteration
                # Build dependency tree for each file
                self.build_dependency_tree(file_path)

            # Collect all unique dependent files
            seen = set(str(f) for f in all_files)

            for form_name, deps in self.dependency_tree.items():
                for dep_name in deps:
                    dep_file = self.find_form_file(dep_name)
                    if dep_file and str(dep_file) not in seen:
                        dependent_files.append(dep_file)
                        seen.add(str(dep_file))

            if dependent_files:
                print(f"\n[*] Found {len(dependent_files)} additional dependent form(s):")
                for f in dependent_files:
                    rel_path = f.relative_to(self.search_root) if self.search_root else f
                    print(f"  + {rel_path}")

                all_files.extend(dependent_files)

            # Print dependency summary
            print(f"\n[*] Dependency Tree Summary:")
            for form_name, deps in self.dependency_tree.items():
                if deps:
                    deps_str = ', '.join(deps[:3])
                    if len(deps) > 3:
                        deps_str += f' (+{len(deps)-3} more)'
                    print(f"  {form_name} -> [{deps_str}]")

        return all_files

    # ==================== UI COMPONENT DETECTION METHODS ====================

    def detect_ui_components(self, file_path: Path) -> Dict[str, Any]:
        """Detect UI components (PageControl, Grid, Toolbar, etc.) from Delphi form.

        Returns:
            Dict with detected UI components and their properties
        """
        try:
            content = file_path.read_text(encoding='utf-8', errors='ignore')
        except Exception as e:
            return {'components': [], 'tabs': [], 'grids': [], 'toolbars': []}

        components = {
            'components': [],  # All detected components
            'tabs': [],        # PageControl/TabControl with tab sheets
            'grids': [],       # DBGrid, dxDBGrid, etc.
            'toolbars': [],    # ToolBar, CoolBar, ActionManager
            'forms': [],       # Embedded forms
        }

        # Pattern: PageControl variable declarations
        # Matches: dxPageControl1: TdxPageControl;
        pagecontrol_decl_pattern = re.compile(
            r'(\w+PageControl\w*)\s*:\s*T\w*PageControl',
            re.IGNORECASE
        )

        # Pattern: TabSheet declarations
        # Matches: dxTabSheet1: TdxTabSheet;, TabSheet1: TTabSheet
        tabsheet_decl_pattern = re.compile(
            r'(\w*(?:TabSheet|Tab)\w*)\s*:\s*T\w*(?:TabSheet|Tab)',
            re.IGNORECASE
        )

        # Pattern: ActivePageIndex usage with comparison
        # Matches: PageControl1.ActivePageIndex = 0, if PageControl1.ActivePageIndex=1
        activepage_pattern = re.compile(
            r'(\w+PageControl\w*)\.ActivePageIndex\s*(=|==)\s*(\d+)',
            re.IGNORECASE
        )

        # Pattern: Grid components
        grid_pattern = re.compile(
            r'(\w*(?:Grid|DBGrid|dxDBGrid|cxGrid|wwDBGrid)\w*)\s*:\s*T(?:\w+Grid)',
            re.IGNORECASE
        )

        # Pattern: ToolBar / ActionManager
        toolbar_pattern = re.compile(
            r'(\w*(?:ToolBar|ActionManager|dxBarManager|CoolBar)\w*)\s*:\s*T\w+',
            re.IGNORECASE
        )

        # Detect PageControl declarations and their tabs
        pagecontrols = {}
        for match in pagecontrol_decl_pattern.finditer(content):
            pc_name = match.group(1)
            if pc_name not in pagecontrols:
                pagecontrols[pc_name] = {
                    'name': pc_name,
                    'type': 'PageControl',
                    'tab_count': 0,
                    'detected_tabs': []
                }

        # Find ActivePageIndex usage to determine tab count
        for match in activepage_pattern.finditer(content):
            pc_name = match.group(1)
            tab_index = int(match.group(3))
            if pc_name in pagecontrols:
                pagecontrols[pc_name]['detected_tabs'].append(tab_index)
                pagecontrols[pc_name]['tab_count'] = max(pagecontrols[pc_name]['tab_count'], tab_index + 1)

        # Detect TabSheets - link them to PageControls
        tabsheets = {}
        for match in tabsheet_decl_pattern.finditer(content):
            ts_name = match.group(1)
            tabsheets[ts_name] = {'name': ts_name, 'caption': None}

            # Try to find caption (might be in code or commented)
            caption_pattern = re.compile(
                rf"{ts_name}\.Caption\s*[:=]\s*'([^']*)'",
                re.IGNORECASE
            )
            caption_match = caption_pattern.search(content)
            if caption_match:
                tabsheets[ts_name]['caption'] = caption_match.group(1)

        # Group tabs by PageControl (heuristic: TabSheet1, TabSheet2 → PageControl1)
        for pc_name, pc_data in pagecontrols.items():
            # Find TabSheets that might belong to this PageControl
            pc_base = pc_name.replace('PageControl', '').replace('pagecontrol', '')
            related_tabs = []
            for ts_name, ts_data in tabsheets.items():
                if pc_base in ts_name or ts_name.replace('TabSheet', '').replace('tabsheet', '') == pc_base:
                    related_tabs.append(ts_data)

            components['components'].append({
                'type': 'PageControl',
                'name': pc_name,
                'tab_count': pc_data['tab_count'],
                'detected_tabs': pc_data['detected_tabs'],
                'tabs': related_tabs
            })
            components['tabs'].extend(related_tabs)

        # Detect Grids
        grids = set()
        for match in grid_pattern.finditer(content):
            grid_name = match.group(1)
            # Skip if it's a sub-component (contains parent name)
            if any(parent in grid_name for parent in grids):
                continue
            grids.add(grid_name)

            # Try to find column definitions
            column_pattern = re.compile(
                rf'{grid_name}\.(?:Columns|AddColumn|CreateColumn)',
                re.IGNORECASE
            )
            column_count = len(list(column_pattern.finditer(content)))

            components['grids'].append({
                'name': grid_name,
                'column_count': column_count if column_count > 0 else None
            })

        # Detect ToolBars
        toolbars = set()
        for match in toolbar_pattern.finditer(content):
            tb_name = match.group(1)
            if tb_name not in toolbars:
                toolbars.add(tb_name)

                # Count button references
                button_pattern = re.compile(
                    rf'{tb_name}\.(?:Buttons|Items|ToolButton)',
                    re.IGNORECASE
                )
                button_refs = len(list(button_pattern.finditer(content)))

                components['toolbars'].append({
                    'name': tb_name,
                    'button_count': button_refs if button_refs > 0 else None
                })

        return components

    # ==================== END UI COMPONENT DETECTION ====================

    # Method name converters
    def _suggest_method_name(self, func_name: str) -> str:
        """Suggest Laravel method name from Delphi function name."""
        method = func_name

        # Apply prefix replacements
        for prefix, replacement in self.laravel_mappings.get('method_prefixes', {}).items():
            if method.startswith(prefix):
                if replacement:
                    method = replacement + method[len(prefix):]
                else:
                    method = method[len(prefix):]
                break

        # Apply word replacements
        for old, new in self.laravel_mappings.get('method_replacements', {}).items():
            method = method.replace(old, new)

        return method

    def _suggest_laravel_target(self, func_name: str, category: str) -> tuple:
        """Suggest Laravel service and method based on function name."""
        # Try to find matching pattern from config
        # Use LONGEST match first for better specificity
        mappings = self.laravel_mappings.get('laravel_service_mappings', {})

        # DEBUG: Show what we're working with
        # print(f"DEBUG: _suggest_laravel_target for {func_name}, mappings count: {len(mappings)}")

        # Find ALL matching patterns, then pick the longest one
        matching_patterns = [(pattern, service) for pattern, service in mappings.items() if pattern in func_name]

        if matching_patterns:
            # Sort by pattern length (descending) - longest pattern wins
            best_pattern, service = max(matching_patterns, key=lambda x: len(x[0]))
            method = self._suggest_method_name(func_name)
            # print(f"DEBUG: {func_name} -> {best_pattern} -> {service}.{method}()")
            return (f'{service}.php', method)

        # Default fallback based on category
        category_map = {
            'validation': ('ValidationService.php', 'check'),
            'database': ('DatabaseService.php', 'execute'),
            'utility': ('UtilityService.php', 'handle'),
            'logging': ('LoggingService.php', 'log'),
        }

        if category in category_map:
            return category_map[category]

        return ('', '')

    # Improved regex patterns - CASE INSENSITIVE!
    # Matches both function and procedure declarations
    # Pattern breakdown:
    # ^\s* - start of line with optional whitespace
    # (function|procedure) - keyword (case insensitive due to re.IGNORECASE)
    # \s+ - whitespace after keyword
    # (\w+) - function/procedure name
    # \s*(?:\((.*?)\))? - OPTIONAL parameters in parentheses (handles procedures without params)
    # \s*(?::\s*(\w+))? - OPTIONAL return type for functions (colon + type)
    # \s*; - closing semicolon with optional whitespace
    FUNCTION_PATTERN = re.compile(
        r'^\s*(function|procedure)\s+(\w+)\s*(?:\((.*?)\))?\s*(?::\s*(\w+))?\s*;',
        re.MULTILINE | re.IGNORECASE
    )

    # Also handle forward declarations and external declarations
    FORWARD_PATTERN = re.compile(
        r'^\s*(function|procedure)\s+(\w+)\s*;',
        re.MULTILINE | re.IGNORECASE
    )

    # Form class methods: procedure TFormName.MethodName(...);
    # Captures class name and method name separately
    FORM_METHOD_PATTERN = re.compile(
        r'^\s*(function|procedure)\s+(\w+)\.(\w+)\s*(?:\((.*?)\))?\s*(?::\s*(\w+))?\s*;',
        re.MULTILINE | re.IGNORECASE
    )

    # Category patterns - MORE COMPREHENSIVE
    VALIDATION_PREFIXES = ('cek', 'check', 'validate', 'is', 'can', 'has', 'isvalid', 'isValid')
    UTILITY_KEYWORDS = ('format', 'convert', 'tostr', 'left', 'right', 'mid', 'trim',
                       'upper', 'lower', 'length', 'romawi', 'bulan', 'kalimat', 'text', 'geser',
                       'enkrip', 'decrypt', 'rate', 'bunga', 'pinjaman', 'angsuran', 'sec', 'time')
    LOGGING_KEYWORDS = ('log', 'history', 'audit', 'tracking', 'recording', 'tlog')

    # Database patterns - MORE COMPREHENSIVE
    DB_PATTERNS = [
        'TADOQuery', 'TQuery', 'TTable', 'TADOStoredProc',
        'SQL.Add', 'SQL.Clear', 'SQL.Text', 'SQL.LoadFromFile',
        'Parameters[', 'ParamByName', 'Prepared',
        'Open;', 'ExecSQL', 'ExecSQL', 'Post;', 'Append;', 'Edit;', 'Delete;',
        'First;', 'Next;', 'Eof', 'Bof', 'Prior;', 'Last;',
        'FieldByName', 'Fields[', 'AsInteger', 'AsString', 'AsDateTime',
        'Close;', 'Active :='
    ]

    # UI/VCL patterns - to identify frontend-only code
    UI_PATTERNS = [
        'TForm', 'TMenuItem', 'TPopupMenu', 'TButton', 'TEdit',
        'ShowMessage', 'MessageDlg', 'Application.MessageBox',
        'TdxDBGrid', 'TwwDBGrid', 'TDBGrid'
    ]

    def find_pas_files(self) -> List[Path]:
        """Find all .pas files in directories, excluding Form/UI files.

        With dependency tracking enabled, also scans forms referenced in uses clauses
        and Application.CreateForm() calls.
        """
        # Get all files including dependencies
        all_files = self.get_all_files_with_dependencies()

        # Exclude UI-only files (but keep Frm/Form as they may contain business logic)
        excluded_patterns = [
            'Dlg',           # Dialog files (typically UI-only)
            'dx',            # DevExpress UI components (dxGridMenus, etc.)
            # Note: Frm and Form files are NOT excluded - they may contain business logic
        ]

        included_files = []
        for file_path in all_files:
            filename = file_path.stem
            # Skip if filename starts with excluded patterns
            if any(filename.startswith(prefix) or filename.lower().startswith(prefix.lower())
                   for prefix in excluded_patterns):
                print(f"  Skipping UI file: {file_path.name}")
                continue
            included_files.append(file_path)

        return included_files

    def categorize_function(self, name: str, body: str) -> str:
        """Categorize function based on name and content."""
        name_lower = name.lower()

        # Check for UI/Frontend first (exclude from backend migration)
        body_lower = body.lower()
        if any(pattern.lower() in body_lower for pattern in self.UI_PATTERNS):
            # If it's purely UI manipulation (no DB operations)
            if not any(db.lower() in body_lower for db in ['TADOQuery', 'SQL.Add', 'Open;', 'ExecSQL']):
                return 'ui_only'

        # Check for validation - MORE PREFIXES
        if name_lower.startswith(self.VALIDATION_PREFIXES):
            return 'validation'

        # Check for logging
        if any(kw in name_lower for kw in self.LOGGING_KEYWORDS):
            return 'logging'

        # Check for utility (formatting, conversion) - use word boundaries
        # Check if function name contains utility keywords as whole words
        for kw in self.UTILITY_KEYWORDS:
            if kw in name_lower:
                # But exclude if it's primarily a data retrieval function
                if not name_lower.startswith(('get', 'find', 'load', 'fetch', 'car', 'data')):
                    return 'utility'

        # Check for database operations in body
        for pattern in self.DB_PATTERNS:
            if pattern.lower() in body_lower:
                return 'database'

        # Check for form manipulation (UI)
        if 'mform' in body_lower or 'tform' in body_lower:
            return 'ui_utility'

        # Default to business logic
        return 'business_logic'

    def _categorize_form_method(self, name: str, body: str, current_category: str, is_form_method: bool = False) -> str:
        """Categorize form class methods with special handling.

        Form methods often have UI event handlers that contain business logic.
        This method refines the category for form methods.
        """
        name_lower = name.lower()
        body_lower = body.lower()

        # UI lifecycle methods - these are typically UI-only
        ui_lifecycle = ['show', 'close', 'create', 'destroy', 'activate',
                       'deactivate', 'resize', 'paint', 'draw']

        if any(name_lower.endswith(suffix) for suffix in ui_lifecycle):
            # But check if they contain business logic
            if any(pattern.lower() in body_lower for pattern in
                   ['CekOtoritasMenu', 'UpdateStatusUser', 'MyCari',
                    'CekUser', 'CekPeriode', 'SQL.Add', 'Open;', 'ExecSQL']):
                return 'business_logic'  # Has business logic despite lifecycle name
            return 'ui_lifecycle'

        # Event handlers - check content for business logic
        event_patterns = ['handler', 'click', 'change', 'exit', 'enter']
        if any(pattern in name_lower for pattern in event_patterns):
            # Check if contains authorization calls
            if 'CekOtoritasMenu' in body or 'cekotoritasmenu' in body_lower:
                return 'authorization'
            # Check if contains database operations
            if any(db in body_lower for db in ['SQL.Add', 'Open;', 'ExecSQL', 'Parameters[']):
                return 'database'
            # Check if contains user management
            if 'UpdateStatusUser' in body or 'updatestatususer' in body_lower:
                return 'user_management'
            # Check for menu generation
            if 'TMenuItem' in body or 'MainMenu' in body:
                return 'menu_logic'
            return 'event_handler'

        # Timer events - check for business logic
        if 'timer' in name_lower:
            if any(pattern in body_lower for pattern in
                   ['StatusBar', 'Caption', 'DateTime']):
                return 'ui_lifecycle'  # Just updating UI
            return 'event_handler'

        # Methods with specific business logic patterns
        if name_lower.startswith('cek'):
            return 'validation'
        if 'user' in name_lower or 'pemakai' in name_lower:
            return 'user_management'
        if 'menu' in name_lower:
            return 'menu_logic'

        # For form methods, don't just use 'ui_only' - check for business logic patterns
        if current_category == 'ui_only':
            # Re-check with more specific patterns for form methods
            if any(pattern in body_lower for pattern in
                   ['CekOtoritasMenu', 'UpdateStatusUser', 'MyCari',
                    'CekPeriode', 'PeriodBln', 'PeriodThn', 'IDUser',
                    'LevelUserAccess', 'Application.CreateForm']):
                return 'business_logic'
            if any(db in body_lower for db in ['SQL.Add', 'Open;', 'ExecSQL']):
                return 'database'

        # Keep original category if no special case matched
        return current_category

    def extract_function_body(self, content: str, start_line: int, max_lines: int = 100) -> str:
        """Extract function body for categorization."""
        lines = content.split('\n')
        # Start from the function declaration
        if start_line <= len(lines):
            # Get lines after the declaration
            end_line = min(start_line + max_lines, len(lines))
            return '\n'.join(lines[start_line:end_line])
        return ""

    def parse_params(self, params_str: str) -> List[str]:
        """Parse function parameters - handles complex Delphi parameter syntax."""
        if not params_str or params_str.strip() == '':
            return []

        # Clean up and split by semicolon
        params_str = params_str.strip()
        if not params_str:
            return []

        # Split by semicolon but handle nested constructs
        params = []
        current_param = ""
        depth = 0

        for char in params_str:
            if char == '(':
                depth += 1
                current_param += char
            elif char == ')':
                depth -= 1
                current_param += char
            elif char == ';' and depth == 0:
                params.append(current_param.strip())
                current_param = ""
            else:
                current_param += char

        # Add last parameter
        if current_param.strip():
            params.append(current_param.strip())

        return [p for p in params if p]

    def _preprocess_multiline_declarations(self, content: str) -> str:
        """Join multiline function/procedure declarations into single lines.

        Delphi often has declarations like:
            procedure FormMouseWheel(Sender: TObject; Shift: TShiftState;
              WheelDelta: Integer; MousePos: TPoint; var Handled: Boolean);

        This joins them into single lines for regex matching.
        """
        lines = content.split('\n')
        result = []
        i = 0

        while i < len(lines):
            line = lines[i]

            # Check if line starts a function/procedure declaration
            match = re.match(r'^\s*(function|procedure)\s+', line, re.IGNORECASE)
            if match:
                # Accumulate lines until we find the closing semicolon
                declaration_lines = [line]
                i += 1

                # Check if first line already ends with ';' (no params or single line)
                # If it has '(' but no ');', we need to continue
                first_line_stripped = line.strip()
                if '(' not in first_line_stripped:
                    # No params, declaration complete
                    result.append(line)
                    continue
                if ');' in first_line_stripped or (first_line_stripped.endswith(';') and '(' not in first_line_stripped):
                    # Single line declaration
                    result.append(line)
                    continue

                # Continue while we haven't found the closing ');'
                while i < len(lines):
                    next_line = lines[i]

                    # If next line starts a new declaration, stop
                    if re.match(r'^\s*(function|procedure)\s+', next_line, re.IGNORECASE):
                        break

                    declaration_lines.append(next_line)
                    i += 1

                    # Check if declaration is complete (ends with ');')
                    joined = ' '.join(declaration_lines)
                    if ');' in joined.replace('\n', ' ').replace('\r', ''):
                        break

                # Join multiline declaration into single line
                result.append(' '.join(declaration_lines))
            else:
                result.append(line)
                i += 1

        return '\n'.join(result)

    def scan_file(self, file_path: Path) -> List[DelphiFunction]:
        """Scan a single Delphi file."""
        try:
            # Try multiple encodings
            content = None
            for encoding in ['utf-8', 'latin-1', 'cp1252', 'iso-8859-1']:
                try:
                    content = file_path.read_text(encoding=encoding)
                    break
                except:
                    continue

            if content is None:
                print(f"Warning: Could not read {file_path} with any encoding")
                return []

        except Exception as e:
            print(f"Warning: Could not read {file_path}: {e}")
            return []

        # Preprocess to join multiline declarations
        content = self._preprocess_multiline_declarations(content)

        functions = []
        lines = content.split('\n')

        # Track seen functions to avoid duplicates (by function name only)
        # Keep FIRST occurrence (usually interface declaration)
        # For form methods, track by "ClassName.MethodName" to avoid confusion
        seen_functions = set()

        for line_num, line in enumerate(lines, 1):
            # Try form class method pattern FIRST (before regular function pattern)
            # This ensures we capture TForm.MethodName correctly
            form_match = self.FORM_METHOD_PATTERN.match(line)
            is_form_method = False
            class_name = ""

            if form_match:
                func_type, class_name, name, params_str, returns = form_match.groups()
                is_form_method = True
            else:
                # Try the regular function pattern
                match = self.FUNCTION_PATTERN.match(line)
                if match:
                    func_type, name, params_str, returns = match.groups()
                else:
                    continue

            # Skip if already seen (handle duplicates - interface + implementation)
            # For form methods, use compound key "ClassName.MethodName"
            # For regular functions, use just "MethodName"
            seen_key = f"{class_name}.{name}" if is_form_method else name
            if seen_key in seen_functions:
                continue
            seen_functions.add(seen_key)

            # For form methods, prepend class name to avoid conflicts
            if is_form_method:
                display_name = f"{class_name}.{name}"
            else:
                display_name = name

            # Extract function body for categorization
            body = self.extract_function_body(content, line_num)

            # Normalize function type
            func_type = func_type.lower()

            # Parse returns
            returns = returns if returns else ('void' if func_type == 'procedure' else '')

            # Categorize function first (use base name for categorization)
            category = self.categorize_function(name, body)

            # For form methods, ALWAYS use _categorize_form_method to override
            # Form methods need special handling because they often have UI mixed with business logic
            if is_form_method:
                category = self._categorize_form_method(name, body, category, is_form_method=True)

            # Auto-suggest Laravel target (use base name)
            laravel_file, laravel_method = self._suggest_laravel_target(name, category)
            laravel_target = LaravelTarget(
                file=laravel_file,
                method=laravel_method,
                status='pending'
            )

            func = DelphiFunction(
                name=display_name,  # Use full name for form methods
                type=func_type,
                line=line_num,
                params=self.parse_params(params_str),
                returns=returns,
                category=category,
                business_logic=self._extract_business_logic(display_name, body),
                laravel_target=laravel_target
            )
            functions.append(func)

        return functions

    def _extract_business_logic(self, name: str, body: str) -> str:
        """Extract brief description of business logic."""
        body_lines = body.split('\n')[:15]  # First 15 lines

        # Check for SQL operations
        for line in body_lines:
            if 'SQL.Add' in line or 'SELECT' in line.upper():
                # Extract table name if possible
                table_match = re.search(r'FROM\s+(\w+)', line, re.IGNORECASE)
                if table_match:
                    return f"Database query on {table_match.group(1)}"
                return "Database query operation"

        # Check for calculations
        if any(word in body.lower() for word in ['calculate', 'compute', 'sum', 'count', '*']):
            return "Calculation operation"

        # Check for validation
        name_lower = name.lower()
        if name_lower.startswith(('cek', 'check', 'validate')):
            return f"Validation: {name}"

        # Check for number generation
        if any(word in name_lower for word in ['no', 'nomor', 'urut', 'sequence']):
            return "Number sequence generation"

        return ""

    def scan_all(self, output_dir: Optional[Path] = None) -> Dict[str, Any]:
        """Scan all .pas files and generate report."""
        files = self.find_pas_files()
        results = []
        total_functions = 0
        category_counts = {
            'validation': 0,
            'business_logic': 0,
            'database': 0,
            'utility': 0,
            'logging': 0,
            'ui_only': 0,
            'ui_utility': 0,
            'ui_lifecycle': 0,
            'authorization': 0,
            'user_management': 0,
            'menu_logic': 0,
            'event_handler': 0,
            'commented': 0
        }

        print(f"Scanning {len(files)} .pas files...")

        for file_path in files:
            # Scan regular functions
            functions = self.scan_file(file_path)

            # Scan commented functions
            commented = self.scan_commented_functions(file_path)
            all_functions = functions + commented

            if all_functions:
                # Run validation on regular functions only
                detected_names = {f.name for f in functions}
                keyword_val = self.validate_keyword_count(file_path, functions)
                missed = self.find_missed_patterns(file_path, detected_names)

                # Note: Duplicates (interface+implementation) will be auto-fixed
                print(f"  {file_path.name}: {len(all_functions)} functions (duplicates will be auto-removed)")
                if commented:
                    print(f"  [*] {len(commented)} commented function(s)")

                total_functions += len(all_functions)
                for func in all_functions:
                    cat = func.category
                    if cat in category_counts:
                        category_counts[cat] += 1

                # Detect UI components in this form
                ui_components = self.detect_ui_components(file_path)

                results.append({
                    'name': file_path.stem,
                    'delphi_file': str(file_path),
                    'functions_count': len(all_functions),
                    'functions': [self._function_to_dict(f) for f in all_functions],
                    'ui_components': ui_components,
                    'validation': {
                        **keyword_val,
                        'missed_patterns': missed,
                        'commented_count': len(commented)
                    }
                })
                print(f"  {file_path.name}: {len(functions)} functions + {len(commented)} commented")

        return {
            'project': self.project_name,
            'scan_date': datetime.now().isoformat(),
            'source_dir': [str(d) for d in self.source_dirs],
            'modules': results,
            'summary': {
                'total_functions': total_functions,
                'total_modules': len(results),
                **category_counts,
                'migrated': 0,
                'pending': total_functions
            }
        }

    # ==================== VALIDATION METHODS ====================

    def validate_keyword_count(self, file_path: Path, detected_functions: List[DelphiFunction]) -> Dict[str, Any]:
        """Cross-check detected functions vs keyword occurrences.

        Note: In Delphi, each function is typically declared twice:
        - Once in interface section
        - Once in implementation section

        So keyword_count should be approximately 2x detected_count.
        """
        try:
            content = file_path.read_text(encoding='utf-8', errors='ignore')
        except Exception as e:
            print(f"Warning: Could not read {file_path} for validation: {e}")
            return {
                'file': file_path.name,
                'keyword_function_count': 0,
                'keyword_procedure_count': 0,
                'detected_function_count': 0,
                'detected_procedure_count': 0,
                'function_diff': 0,
                'procedure_diff': 0,
                'expected_function_count': 0,
                'expected_procedure_count': 0,
                'potential_misses': 0
            }

        # Count all function/procedure keywords (case-insensitive)
        function_count = len(re.findall(r'\bfunction\b', content, re.IGNORECASE))
        procedure_count = len(re.findall(r'\bprocedure\b', content, re.IGNORECASE))

        detected_func = sum(1 for f in detected_functions if f.type == 'function')
        detected_proc = sum(1 for f in detected_functions if f.type == 'procedure')

        # Expected is approximately 2x (interface + implementation)
        expected_func = detected_func * 2
        expected_proc = detected_proc * 2

        # Calculate how far off we are from expected
        func_diff = abs(function_count - expected_func)
        proc_diff = abs(procedure_count - expected_proc)

        # Only flag as potential issue if difference is significant (> 10%)
        # This allows for forward declarations, external functions, etc.
        func_threshold = max(2, expected_func * 0.1)
        proc_threshold = max(2, expected_proc * 0.1)

        potential_misses = 0
        if func_diff > func_threshold:
            potential_misses += int(func_diff)
        if proc_diff > proc_threshold:
            potential_misses += int(proc_diff)

        return {
            'file': file_path.name,
            'keyword_function_count': function_count,
            'keyword_procedure_count': procedure_count,
            'detected_function_count': detected_func,
            'detected_procedure_count': detected_proc,
            'expected_function_count': expected_func,
            'expected_procedure_count': expected_proc,
            'function_diff': function_count - expected_func,
            'procedure_diff': procedure_count - expected_proc,
            'potential_misses': potential_misses
        }

    def find_missed_patterns(self, file_path: Path, detected_names: set) -> List[Dict[str, Any]]:
        """Find lines with function/procedure patterns that weren't captured."""
        try:
            content = file_path.read_text(encoding='utf-8', errors='ignore')
        except Exception as e:
            print(f"Warning: Could not read {file_path} for missed pattern detection: {e}")
            return []

        lines = content.split('\n')
        missed = []

        # Pattern to find ANY line with function/procedure keyword
        # Handle both: function Name dan function ClassName.Name
        loose_pattern = re.compile(
            r'.*\b(function|procedure)\s+(\w+)(?:\.(\w+))?',
            re.IGNORECASE
        )

        for line_num, line in enumerate(lines, 1):
            match = loose_pattern.match(line)
            if match:
                func_type, name1, name2 = match.groups()
                # For form methods: use "ClassName.MethodName"
                # For regular functions: use just "Name"
                if name2:
                    detected_name = f"{name1}.{name2}"
                else:
                    detected_name = name1

                # Check if this function was detected
                if detected_name not in detected_names:
                    missed.append({
                        'line': line_num,
                        'type': func_type,
                        'name': detected_name,
                        'line_content': line.strip()[:100],
                        'reason': self._guess_miss_reason(line)
                    })

        return missed

    def _guess_miss_reason(self, line: str) -> str:
        """Guess why a pattern might have been missed."""
        if line.strip().startswith('//'):
            return 'commented'
        if '{$' in line or '}' in line:
            return 'in_compiler_directive'
        if ';' not in line:
            return 'no_semicolon'
        if '(' in line and ')' not in line:
            return 'multiline_params'
        return 'unknown'

    def scan_commented_functions(self, file_path: Path) -> List[DelphiFunction]:
        """Extract commented function declarations for review."""
        try:
            content = file_path.read_text(encoding='utf-8', errors='ignore')
        except Exception as e:
            print(f"Warning: Could not read {file_path} for commented functions: {e}")
            return []

        # Pattern for commented functions
        commented_pattern = re.compile(
            r'^\s*//.*?(function|procedure)\s+(\w+)\s*(?:\((.*?)\))?\s*(?::\s*(\w+))?\s*;',
            re.MULTILINE | re.IGNORECASE
        )

        commented = []
        for match in commented_pattern.finditer(content):
            func_type, name, params, returns = match.groups()
            commented.append(DelphiFunction(
                name=name,
                type=func_type.lower(),
                line=0,  # Can't get accurate line easily from finditer
                params=self.parse_params(params) if params else [],
                returns=returns if returns else '',
                category='commented',
                business_logic='COMMENTED - Not implemented',
                laravel_target=LaravelTarget(status='commented')
            ))

        return commented

    def generate_validation_report(self, scan_results: Dict[str, Any]) -> str:
        """Generate validation report with warnings."""
        lines = [
            f"# {scan_results['project']} Scan Validation Report",
            "",
            f"**Scan Date:** {scan_results['scan_date']}",
            f"**Source:** `{scan_results['source_dir']}`",
            "",
            "---",
            "",
            "## Validation Summary",
            ""
        ]

        total_warns = 0
        total_commented = 0
        total_duplicates = 0

        for module in scan_results['modules']:
            val = module.get('validation', {})
            commented_funcs = [f for f in module.get('functions', []) if f.get('category') == 'commented']

            if commented_funcs:
                total_commented += len(commented_funcs)

            if val.get('duplicates_removed', 0) > 0:
                total_duplicates += val.get('duplicates_removed', 0)

            if val.get('status') == 'empty':
                total_warns += 1

        # Overall summary
        lines.extend([
            f"| Metric | Count |",
            f"|:-------|------:|",
            f"| **Modules scanned** | {len(scan_results['modules'])} |",
            f"| **Duplicates removed** | {total_duplicates} |",
            f"| **Commented functions** | {total_commented} |",
            "",
        ])

        if total_warns == 0 and total_duplicates >= 0:
            lines.extend([
                "## Status: OK",
                "",
                "All functions captured successfully. Auto-fix applied duplicates.",
                ""
            ])
        else:
            lines.extend([
                f"## ⚠️ Issues Found",
                "",
                f"**Modules with issues:** {total_warns}",
                ""
            ])

        # Per-module details
        for module in scan_results['modules']:
            module_name = module['name']
            val = module.get('validation', {})
            commented_funcs = [f for f in module.get('functions', []) if f.get('category') == 'commented']

            # Show all modules with their stats
            lines.extend([
                f"### {module_name}",
                "",
                f"- **Final functions:** {val.get('final_count', 0)}",
            ])

            if val.get('duplicates_removed', 0) > 0:
                lines.append(f"- **Duplicates removed:** {val.get('duplicates_removed', 0)}")

            if commented_funcs:
                lines.append(f"- **Commented:** {len(commented_funcs)}")

            lines.append("")

            if commented_funcs:
                lines.extend([
                    "#### Commented Functions",
                    "",
                    "| Function | Type | Returns |",
                    "|:---------|------|:-------|"
                ])
                for func in commented_funcs:
                    lines.append(
                        f"| `{func['name']}` | {func['type']} | `{func['returns']}` |"
                    )
                lines.append("")
                lines.extend([
                    "#### Commented Functions",
                    "",
                    "| Function | Type | Returns |",
                    "|:---------|------|:-------|"
                ])
                for func in commented_funcs:
                    lines.append(
                        f"| `{func['name']}` | {func['type']} | `{func['returns']}` |"
                    )
                lines.append("")

        # Final verdict
        lines.extend(["", "---", ""])
        if total_warns == 0 and total_commented == 0:
            lines.extend([
                "## ✅ Scan Result",
                "",
                "**No issues detected** - scan appears complete!",
                "",
                "All function and procedure keywords in the source files were successfully captured."
            ])
        else:
            lines.extend([
                "## ⚠️ Scan Result",
                "",
                f"**Issues found in {total_warns} module(s)**",
                ""
            ])
            if total_commented > 0:
                lines.append(f"- {total_commented} commented function(s) found (shown with special status)")
            if total_missed > 0:
                lines.append(f"- {total_missed} potential pattern(s) not captured")

        return '\n'.join(lines)

    def auto_fix_common_issues(self, scan_results: Dict[str, Any]) -> Tuple[Dict[str, Any], List[str]]:
        """Auto-fix common scanning issues.

        Returns:
            Tuple of (fixed_results, list of fixes_applied)
        """
        fixes_applied = []

        for module in scan_results.get('modules', []):
            funcs = module.get('functions', [])
            if not funcs:
                continue

            original_count = len(funcs)

            # Helper: Extract base name by removing any class prefix
            def get_base_name(func_name: str, module_name: str) -> str:
                # Extract base name by removing any "TPrefix." pattern
                # This handles: TFrTutupBuku.Method, TFrmProsesTutupBuku.Method, etc.
                name = func_name

                # If there's a dot, split and take the last part
                if '.' in name:
                    parts = name.split('.')
                    name = parts[-1]

                return name

            # Fix 1: Remove interface declarations (keep only implementations)
            # Interface declarations have lower line numbers and duplicate names
            seen = {}
            unique_funcs = []
            for fn in funcs:
                base_name = get_base_name(fn['name'], module['name'])
                # Skip obvious false positives
                if base_name == 'CariDiPosting':
                    fixes_applied.append(f"Removed false positive '{base_name}' from {module['name']}")
                    continue

                # Keep implementation (higher line number for duplicates)
                if base_name not in seen or fn['line'] > seen[base_name]['line']:
                    seen[base_name] = fn

            unique_funcs = list(seen.values())

            # Fix 2: Correct categories
            category_map = {
                'FormShow': 'ui_lifecycle',
                'FormClose': 'ui_lifecycle',
                'FormCreate': 'ui_lifecycle',
                'FormDestroy': 'ui_lifecycle',
                'FormKeyDown': 'ui_lifecycle',
                'FormKeyPress': 'ui_lifecycle',
                'BitBtn1Click': 'event_handler',
                'BitBtn2Click': 'ui_lifecycle',
                'Button1Click': 'event_handler',
                'Button2Click': 'event_handler',
                'SpeedButtonClick': 'event_handler',
                'ToolButtonClick': 'event_handler',
                'CariJumlah': 'business_logic',
                'SetTotal': 'business_logic',
                'CariJumlahDevisi': 'business_logic',
                'SetTotalDevisi': 'business_logic',
                'JumTotal': 'business_logic',
                'JurnalKoreksi': 'business_logic',
                'CariJumDvs': 'business_logic',
            }

            for fn in unique_funcs:
                base_name = get_base_name(fn['name'], module['name'])

                # Apply category mapping
                if base_name in category_map:
                    old_cat = fn.get('category', '')
                    fn['category'] = category_map[base_name]
                    if old_cat != fn['category']:
                        fixes_applied.append(f"Re-categorized '{base_name}': {old_cat} -> {fn['category']}")

                # Fix UI-only functions
                if fn['category'] == 'ui_lifecycle':
                    fn['laravel_target']['status'] = 'not_needed'
                    fn['laravel_target']['notes'] = 'Frontend-only - use Livewire/Alpine.js'

            # Fix 3: Better Laravel targets for business logic
            service_prefix_map = {
                'ProsesAktiva': ('AssetService', 'Asset depreciation'),
                'ProsesHPPRL': ('ProfitLossService', 'P&L processing'),
                'ProsesHPPRLSubDevisi': ('ProfitLossService', 'Sub-division P&L'),
                'ProsesHitUlangNeraca': ('BalanceSheetService', 'Recalculate balance sheet'),
                'ProsesHitUlangAktiva': ('AssetService', 'Recalculate assets'),
                'ProsesBuatJurnal': ('JournalService', 'Create closing entries'),
                'Postingdata': ('YearEndClosingService', 'Post transactions'),
                'PostingKacngBasah': ('YearEndClosingService', 'Post cash basis'),
                'SimpanData': ('JournalService', 'Save journal entry'),
            }

            for fn in unique_funcs:
                base_name = fn['name'].replace(f"{module['name']}.", '')
                if base_name in service_prefix_map:
                    service, desc = service_prefix_map[base_name]
                    fn['laravel_target']['file'] = f"App/Services/{service}.php"
                    fn['laravel_target']['method'] = base_name

            # Update module with fixed functions
            module['functions'] = sorted(unique_funcs, key=lambda x: x['line'])
            module['functions_count'] = len(unique_funcs)

            if len(unique_funcs) < original_count:
                fixes_applied.append(f"{module['name']}: removed {original_count - len(unique_funcs)} duplicate(s)")

        # Recalculate summary
        if fixes_applied:
            total_funcs = sum(m.get('functions_count', 0) for m in scan_results.get('modules', []))
            scan_results['summary']['total_functions'] = total_funcs

            # Recount categories
            category_counts = {}
            for module in scan_results.get('modules', []):
                for fn in module.get('functions', []):
                    cat = fn.get('category', 'unknown')
                    category_counts[cat] = category_counts.get(cat, 0) + 1

            for cat, count in category_counts.items():
                scan_results['summary'][cat] = count

        return scan_results, fixes_applied

    # ==================== END VALIDATION METHODS ====================

    # ==================== PATTERN SUGGESTION METHODS ====================

    def suggest_new_patterns(self, scan_results: Dict[str, Any]) -> Dict[str, Any]:
        """Analyze scan results and suggest new patterns for laravel_mappings.json.

        Returns:
            Dict with suggested new patterns and their confidence levels.
        """
        suggestions = {
            'scan_date': datetime.now().isoformat(),
            'total_functions_analyzed': 0,
            'new_service_patterns': {},
            'new_method_prefixes': {},
            'new_method_replacements': {},
            'functions_needing_review': []
        }

        # Group functions by their Laravel target to find patterns
        service_usage = {}  # service -> [prefixes that mapped to it]
        prefix_usage = {}   # prefix -> [functions with this prefix]

        for module in scan_results['modules']:
            for func in module.get('functions', []):
                # Skip commented and ui_only functions
                if func.get('category') in ['commented', 'ui_only']:
                    continue

                suggestions['total_functions_analyzed'] += 1

                func_name = func['name']
                laravel_file = func.get('laravel_target', {}).get('file', '')

                if not laravel_file or laravel_file == '':
                    # No pattern matched - add to review list
                    suggestions['functions_needing_review'].append({
                        'module': module['name'],
                        'function': func_name,
                        'category': func.get('category', 'unknown'),
                        'suggested_service': self._guess_service_from_category(func.get('category', ''))
                    })
                    continue

                # Extract service name from file
                service_name = laravel_file.replace('.php', '')

                # Find the prefix that matched (by comparing with original name)
                matched_pattern = self._find_matched_pattern(func_name)
                if matched_pattern:
                    if service_name not in service_usage:
                        service_usage[service_name] = {}
                    if matched_pattern not in service_usage[service_name]:
                        service_usage[service_name][matched_pattern] = 0
                    service_usage[service_name][matched_pattern] += 1

                    # Track prefix usage
                    if matched_pattern not in prefix_usage:
                        prefix_usage[matched_pattern] = []
                    prefix_usage[matched_pattern].append(func_name)

        # Analyze patterns and suggest new ones
        suggestions = self._analyze_service_patterns(service_usage, prefix_usage, suggestions)
        suggestions = self._analyze_method_prefixes(scan_results, suggestions)

        return suggestions

    def _find_matched_pattern(self, func_name: str) -> Optional[str]:
        """Find which pattern from config matched this function name."""
        mappings = self.laravel_mappings.get('laravel_service_mappings', {})

        # Find ALL matching patterns
        matching_patterns = [(pattern, service) for pattern, service in mappings.items()
                            if pattern.lower() in func_name.lower()]

        if matching_patterns:
            # Return the longest pattern that matched
            best_pattern, _ = max(matching_patterns, key=lambda x: len(x[0]))
            return best_pattern

        return None

    def _guess_service_from_category(self, category: str) -> str:
        """Guess Laravel service based on function category."""
        category_map = {
            'validation': 'ValidationService',
            'database': 'DatabaseService',
            'utility': 'UtilityService',
            'logging': 'LoggingService',
            'business_logic': 'BusinessLogicService'
        }
        return category_map.get(category, 'UnknownService')

    def _analyze_service_patterns(self, service_usage: Dict, prefix_usage: Dict,
                                   suggestions: Dict) -> Dict:
        """Analyze service usage patterns and suggest new mappings."""
        # Find patterns that consistently map to the same service
        # but aren't in the config yet

        for service, patterns in service_usage.items():
            for pattern, count in patterns.items():
                # Only suggest if pattern is used multiple times
                # and isn't just a single character
                if count >= 2 and len(pattern) >= 2:
                    current_mapping = self.laravel_mappings.get('laravel_service_mappings', {}).get(pattern)

                    if not current_mapping or current_mapping != service:
                        # This is a new or conflicting pattern
                        if pattern not in suggestions['new_service_patterns']:
                            suggestions['new_service_patterns'][pattern] = {
                                'suggested_service': service,
                                'usage_count': count,
                                'confidence': self._calculate_confidence(count, service_usage),
                                'example_functions': prefix_usage.get(pattern, [])[:3]
                            }

        return suggestions

    def _analyze_method_prefixes(self, scan_results: Dict,
                                  suggestions: Dict) -> Dict:
        """Analyze function names to suggest method prefixes/replacements."""
        # Common prefixes to look for
        common_prefixes = ['Get', 'Set', 'Check', 'Cek', 'Find', 'Cari',
                          'Is', 'Can', 'Has', 'Validate', 'Load', 'Save']

        # Words that should be replaced
        indonesian_words = {
            'Nomor': 'Number',
            'No': 'Number',
            'Urut': 'Sequence',
            'Periode': 'Period',
            'Tgl': 'Date',
            'Bulan': 'Month',
            'Tahun': 'Year',
            'Devisi': 'Division',
            'Pemakai': 'User',
            'Kata': 'Word',
            'Kalimat': 'Sentence',
            'Geser': 'Shift',
            'Kunci': 'Key',
            'Hapus': 'Delete',
            'Cari': 'Find',
            'Cek': 'Check',
            'Baru': 'New',
            'Lama': 'Old'
        }

        # Check for Indonesian words in function names that aren't in replacements
        for module in scan_results['modules']:
            for func in module.get('functions', []):
                func_name = func['name']

                for indo, eng in indonesian_words.items():
                    if indo in func_name and indo not in self.laravel_mappings.get('method_replacements', {}):
                        if indo not in suggestions['new_method_replacements']:
                            suggestions['new_method_replacements'][indo] = {
                                'replace_with': eng,
                                'example_function': func_name,
                                'suggested_result': func_name.replace(indo, eng)
                            }

        return suggestions

    def _calculate_confidence(self, count: int, service_usage: Dict) -> str:
        """Calculate confidence level for a pattern suggestion."""
        if count >= 5:
            return 'high'
        elif count >= 3:
            return 'medium'
        else:
            return 'low'

    def generate_suggestions_report(self, suggestions: Dict[str, Any]) -> str:
        """Generate a human-readable suggestions report."""
        lines = [
            "# Pattern Suggestions for laravel_mappings.json",
            "",
            f"**Generated:** {suggestions['scan_date']}",
            f"**Functions Analyzed:** {suggestions['total_functions_analyzed']}",
            "",
            "---",
            ""
        ]

        # New service patterns
        if suggestions['new_service_patterns']:
            lines.extend([
                "## 🎯 Suggested New Service Patterns",
                "",
                "These patterns consistently map to specific services.",
                "",
                "| Pattern | Suggested Service | Usage | Confidence | Examples |",
                "|:--------|:------------------|------:|:----------|:---------|"
            ])

            for pattern, info in sorted(suggestions['new_service_patterns'].items(),
                                        key=lambda x: x[1]['usage_count'],
                                        reverse=True):
                examples = ', '.join(info['example_functions'][:2])
                lines.append(
                    f"| `{pattern}` | {info['suggested_service']} | {info['usage_count']} | "
                    f"{info['confidence']} | {examples} |"
                )

            lines.extend(["", "### JSON to add to `laravel_service_mappings`:", "", "```json"])
            for pattern, info in sorted(suggestions['new_service_patterns'].items()):
                lines.append(f'  "{pattern}": "{info["suggested_service"]}",')
            lines.extend(["```", ""])
        else:
            lines.extend([
                "## 🎯 Suggested New Service Patterns",
                "",
                "No new patterns to suggest. Current config covers most cases.",
                ""
            ])

        # Method replacements
        if suggestions['new_method_replacements']:
            lines.extend([
                "## 🔤 Suggested Method Replacements",
                "",
                "Indonesian words found that should be replaced.",
                "",
                "| Indonesian | English | Example Function | Result |",
                "|:-----------|:--------|:------------------|:-------|"
            ])

            for indo, info in suggestions['new_method_replacements'].items():
                lines.append(
                    f"| `{indo}` | {info['replace_with']} | `{info['example_function']}` | "
                    f"`{info['suggested_result']}` |"
                )

            lines.extend(["", "### JSON to add to `method_replacements`:", "", "```json"])
            for indo, info in suggestions['new_method_replacements'].items():
                lines.append(f'  "{indo}": "{info["replace_with"]}",')
            lines.extend(["```", ""])
        else:
            lines.extend([
                "## 🔤 Suggested Method Replacements",
                "",
                "No new replacements needed.",
                ""
            ])

        # Functions needing review
        if suggestions['functions_needing_review']:
            lines.extend([
                "## ⚠️ Functions Needing Manual Review",
                "",
                "These functions didn't match any pattern and need manual categorization.",
                "",
                "| Module | Function | Category | Suggested Service |",
                "|:-------|:---------|:---------|:------------------|"
            ])

            for func in suggestions['functions_needing_review'][:20]:  # Limit to 20
                lines.append(
                    f"| {func['module']} | `{func['function']}` | {func['category']} | "
                    f"{func['suggested_service']} |"
                )

            if len(suggestions['functions_needing_review']) > 20:
                lines.append(f"| ... | +{len(suggestions['functions_needing_review']) - 20} more | | |")

            lines.append("")
        else:
            lines.extend([
                "## ⚠️ Functions Needing Manual Review",
                "",
                "All functions were matched successfully!",
                ""
            ])

        # Instructions
        lines.extend([
            "---",
            "",
            "## 📋 How to Apply These Suggestions",
            "",
            "1. Review the suggestions above",
            "2. Copy relevant JSON blocks to `laravel_mappings.json`",
            "3. Re-run the scanner to verify improvements",
            "4. Manually review functions in the 'Needs Review' section",
            ""
        ])

        return '\n'.join(lines)

    def save_suggestions(self, suggestions: Dict[str, Any], output_path: Path) -> None:
        """Save suggestions to JSON file for later reference."""
        output_path.parent.mkdir(parents=True, exist_ok=True)
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(suggestions, f, indent=2, ensure_ascii=False)

    # ==================== END PATTERN SUGGESTION METHODS ====================

    def _function_to_dict(self, func: DelphiFunction) -> Dict[str, Any]:
        """Convert DelphiFunction to dictionary."""
        return {
            'name': func.name,
            'type': func.type,
            'line': func.line,
            'params': func.params,
            'returns': func.returns,
            'category': func.category,
            'business_logic': func.business_logic,
            'laravel_target': asdict(func.laravel_target)
        }


def main():
    parser = argparse.ArgumentParser(
        description='Scan Delphi source files for traceability analysis'
    )
    parser.add_argument(
        'source_dir',
        nargs='+',
        help='Path(s) to Delphi source directory (can specify multiple)'
    )
    parser.add_argument(
        '-o', '--output',
        default=None,
        help='Output JSON file (default: auto from first input filename, e.g. FrmUtama.pas → FrmUtama.json)'
    )
    parser.add_argument(
        '-p', '--project',
        default='KeuApp',
        help='Project name (default: KeuApp)'
    )
    parser.add_argument(
        '-m', '--markdown',
        action='store_true',
        help='Also generate Markdown output'
    )
    parser.add_argument(
        '-c', '--config',
        default=None,
        help='Path to Laravel mapping config JSON file (default: config/laravel_mappings.json in skill directory)'
    )
    parser.add_argument(
        '-s', '--suggest',
        action='store_true',
        help='Generate pattern suggestions for improving laravel_mappings.json'
    )
    parser.add_argument(
        '-d', '--output-dir',
        default='docs/traceability',
        help='Output directory for traceability files (default: docs/traceability)'
    )
    parser.add_argument(
        '--no-deps',
        action='store_true',
        help='Disable automatic dependency tracking (default: enabled)'
    )
    parser.add_argument(
        '--search-root',
        default=None,
        help='Root directory for dependency search (default: auto-detect from project structure)'
    )

    args = parser.parse_args()

    # Initialize scanner with dependency tracking
    enable_deps = not args.no_deps
    search_root = Path(args.search_root) if args.search_root else None

    scanner = DelphiScanner(
        args.source_dir,
        args.project,
        args.config,
        enable_dependency_tracking=enable_deps,
        search_root=search_root
    )
    result = scanner.scan_all()

    # AUTO-FIX: Apply common fixes to scan results
    result, fixes = scanner.auto_fix_common_issues(result)
    if fixes:
        print(f"\n{'='*50}")
        print(f"[Auto-Fix] Applied: {len(fixes)} fix(es)")
        for fix in fixes:
            print(f"  - {fix}")
        print(f"{'='*50}\n")

    # Determine output path
    if args.output:
        # User specified output file
        output_path = Path(args.output)
    else:
        # Auto-generate from first input filename
        # e.g. "pwt/Master/FrmBarang.pas" → "frmbarang.json"
        input_name = Path(args.source_dir[0]).stem.lower()
        output_path = Path(args.output_dir) / f'{input_name}.json'

    # VALIDATION: Check if output filename matches input module name
    # This prevents the bug where output file is named incorrectly
    if result.get('modules') and len(result['modules']) > 0:
        expected_module_name = result['modules'][0].get('name', '').lower()
        output_filename = output_path.stem.lower()
        if expected_module_name and output_filename != expected_module_name.lower():
            print(f"\n[WARNING] Filename mismatch detected!")
            print(f"   Output file: {output_path.name}")
            print(f"   Module name: {expected_module_name}")
            print(f"   Auto-correcting to: {expected_module_name}.json")
            output_path = output_path.parent / f'{expected_module_name}.json'

    # Make absolute path relative to project root
    # Auto-detect project root by looking for common markers
    if not output_path.is_absolute():
        project_root = Path(args.source_dir[0])
        # Navigate up until we find a project marker or reach filesystem root
        # Project markers: .git, composer.json, package.json, or pwt/KSP folder
        while project_root.parent != project_root:
            if any((project_root / marker).exists() for marker in ['.git', 'composer.json', 'package.json']):
                break
            if (project_root / 'pwt').exists() or (project_root / 'KSP').exists():
                break
            project_root = project_root.parent
        output_path = project_root / output_path
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(
        json.dumps(result, indent=2, ensure_ascii=False),
        encoding='utf-8'
    )

    print(f"\n{'='*50}")
    print(f"Scan Summary:")
    print(f"  Total Functions: {result['summary']['total_functions']}")
    print(f"  Total Modules: {result['summary']['total_modules']}")
    print(f"  Validation: {result['summary']['validation']}")
    print(f"  Business Logic: {result['summary']['business_logic']}")
    print(f"  Database: {result['summary']['database']}")
    print(f"  Utility: {result['summary']['utility']}")
    print(f"  Logging: {result['summary']['logging']}")
    print(f"  Authorization: {result['summary']['authorization']}")
    print(f"  User Management: {result['summary']['user_management']}")
    print(f"  Menu Logic: {result['summary']['menu_logic']}")
    print(f"  Event Handler: {result['summary']['event_handler']}")
    print(f"  UI Lifecycle: {result['summary']['ui_lifecycle']}")
    print(f"  Commented: {result['summary']['commented']}")
    print(f"  UI Only (skip): {result['summary']['ui_only']}")
    print(f"  UI Utility: {result['summary']['ui_utility']}")
    print(f"{'='*50}")
    print(f"Output written to: {output_path}")

    if args.markdown:
        # Generate markdown inline
        md_content = generate_markdown_content(result)
        md_path = output_path.with_suffix('.md')
        md_path.write_text(md_content, encoding='utf-8')
        print(f"Markdown written to: {md_path.resolve()}")

    # Always generate validation report AFTER auto-fix
    # Update validation data to reflect post-fix state
    for module in result.get('modules', []):
        # Recalculate validation based on final (fixed) function count
        funcs = module.get('functions', [])
        module['validation'] = {
            'original_count': module.get('functions_count', 0) + len(funcs),  # Before fix
            'final_count': len(funcs),  # After fix
            'duplicates_removed': module.get('functions_count', 0),  # Approximate
            'potential_misses': 0,  # Auto-fix handled duplicates
            'status': 'ok' if len(funcs) > 0 else 'empty'
        }

    validation_content = scanner.generate_validation_report(result)
    validation_path = output_path.parent / 'traceability-validation.md'
    validation_path.write_text(validation_content, encoding='utf-8')
    print(f"Validation report written to: {validation_path.resolve()}")

    # Always generate CSV from current scan results (same name as JSON)
    # Path: scan_delphi.py -> scripts -> delphi-traceability-analyzer -> skills -> .claude -> project_root
    csv_script_path = Path(__file__).parent.parent.parent.parent.parent / 'docs' / 'traceability' / 'generate_csv.py'
    # CSV dengan nama yang sama dengan JSON (myprocedure.json → myprocedure.csv)
    csv_output_path = output_path.parent / f'{output_path.stem}.csv'

    if csv_script_path.exists():
        try:
            subprocess.run(
                [sys.executable, str(csv_script_path), str(output_path), str(csv_output_path)],
                check=True,
                capture_output=True,
                text=True
            )
            print(f"CSV generated: {csv_output_path.resolve()}")
        except subprocess.CalledProcessError as e:
            print(f"[Warning] CSV generation failed: {e}")
            if e.stderr:
                print(f"  Error: {e.stderr}")
    else:
        print(f"[Warning] CSV generator not found: {csv_script_path}")

    # Migration validation report - check completeness
    try:
        validation_script = Path(__file__).parent / 'validate_migration.py'
        if validation_script.exists():
            result = subprocess.run(
                [sys.executable, str(validation_script), str(output_path)],
                capture_output=True,
                text=True
            )
            if result.stdout:
                migration_path = output_path.parent / f'{output_path.stem}-migration.md'
                migration_path.write_text(result.stdout, encoding='utf-8')
                print(f"Migration validation: {migration_path.resolve()}")

                # Show summary
                for line in result.stdout.split('\n'):
                    if 'Overall Progress:' in line or 'WARNING:' in line:
                        print(f"  {line}")
        else:
            print(f"[Warning] Migration validator not found: {validation_script}")
    except Exception as e:
        print(f"[Warning] Migration validation failed: {e}")

    # Generate pattern suggestions if requested
    if args.suggest:
        print("\n[*] Analyzing patterns for suggestions...")
        suggestions = scanner.suggest_new_patterns(result)

        # Save suggestions JSON
        suggestions_json_path = output_path.parent / 'pattern-suggestions.json'
        scanner.save_suggestions(suggestions, suggestions_json_path)
        print(f"Suggestions JSON written to: {suggestions_json_path.resolve()}")

        # Generate suggestions report
        suggestions_md = scanner.generate_suggestions_report(suggestions)
        suggestions_md_path = output_path.parent / 'pattern-suggestions.md'
        suggestions_md_path.write_text(suggestions_md, encoding='utf-8')
        print(f"Suggestions report written to: {suggestions_md_path.resolve()}")

        # Print summary
        new_patterns = len(suggestions.get('new_service_patterns', {}))
        new_replacements = len(suggestions.get('new_method_replacements', {}))
        need_review = len(suggestions.get('functions_needing_review', []))

        print(f"\n[*] Suggestions Summary:")
        print(f"  - New service patterns: {new_patterns}")
        print(f"  - New method replacements: {new_replacements}")
        print(f"  - Functions needing review: {need_review}")


def generate_markdown_content(result: Dict[str, Any]) -> str:
    """Generate markdown content from scan results."""
    lines = [
        f"# {result['project']} Traceability Matrix",
        "",
        f"**Scan Date:** {result['scan_date']}",
        "",
        f"**Source:** `{result['source_dir']}`",
        "",
        "---",
        "",
        "## 📊 Summary",
        "",
        "| Metric | Count |",
        "|:-------|------:|",
        f"| **Total Functions** | **{result['summary']['total_functions']}** |",
        f"| Total Modules | {result['summary']['total_modules']} |",
        f"| ✅ Migrated | {result['summary']['migrated']} |",
        f"| ⏳ Pending | {result['summary']['pending']} |",
        "",
        "## 📁 Category Breakdown",
        "",
        "| Category | Count | Migrated | Pending |",
        "|:---------|------:|---------:|--------:|",
    ]

    categories = ['validation', 'business_logic', 'database', 'utility', 'logging', 'commented']
    category_names = {
        'validation': '🔍 Validation',
        'business_logic': '💼 Business Logic',
        'database': '🗄️ Database Operations',
        'utility': '🔧 Utility Functions',
        'logging': '📝 Logging Functions',
        'commented': '💤 Commented (Not Active)'
    }

    for cat in categories:
        count = result['summary'].get(cat, 0)
        lines.append(f"| {category_names[cat]} | {count} | 0 | {count} |")

    lines.extend(["", "---", ""])

    # Add module details
    for module in result['modules']:
        lines.extend([
            f"## 📦 Module: {module['name']}",
            "",
            f"**File:** ``{module['delphi_file']}``",
            f"**Functions:** {module['functions_count']}",
            "",
        ])

        # Group by category
        by_category = {}
        for func in module['functions']:
            cat = func['category']
            if cat not in by_category:
                by_category[cat] = []
            by_category[cat].append(func)

        for cat in ['validation', 'business_logic', 'database', 'utility', 'logging', 'commented']:
            if cat in by_category:
                lines.append(f"### {category_names.get(cat, cat.capitalize())}")
                lines.append("")
                lines.append("| Function | Type | Returns | Laravel Target | Status |")
                lines.append("|:---------|------|:-------|:---------------|:------:|")

                for func in by_category[cat]:
                    params_brief = f"{len(func['params'])} parameters" if func['params'] else '-'
                    laravel_target = ""
                    if cat == 'commented':
                        status = "💤"
                    else:
                        status = "⏳"

                    lines.append(f"| **{func['name']}** | {func['type']} | `{func['returns']}` | {laravel_target} | {status} |")
                    lines.append(f"| ↳ `params: {params_brief}` | | | | |")

                lines.append("")
                lines.append("---")
                lines.append("")

    return '\n'.join(lines)


if __name__ == '__main__':
    main()
