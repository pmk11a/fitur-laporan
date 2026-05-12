#!/usr/bin/env python3
"""
Delphi to Laravel Generator - Database Schema Reader

Connects to SQL Server and reads table schema for Model generation.
Outputs JSON with column information: name, type, nullable, primary_key, etc.
"""

import os
import sys
import json
import argparse
from typing import Dict, List, Any
from datetime import datetime

try:
    import pyodbc
except ImportError:
    print("Installing pyodbc...")
    os.system("pip install pyodbc")
    import pyodbc


class SQLServerSchemaReader:
    """Read schema from SQL Server database"""

    def __init__(self, connection_string: str):
        self.connection_string = connection_string
        self.conn = None

    def connect(self):
        """Connect to SQL Server"""
        try:
            self.conn = pyodbc.connect(self.connection_string)
            print(f"✓ Connected to SQL Server")
            return True
        except Exception as e:
            print(f"✗ Connection failed: {e}")
            return False

    def get_tables(self) -> List[str]:
        """Get all table names"""
        cursor = self.conn.cursor()
        cursor.execute("""
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_TYPE = 'BASE TABLE'
            AND TABLE_NAME LIKE 'db%'
            ORDER BY TABLE_NAME
        """)
        return [row[0] for row in cursor.fetchall()]

    def get_table_schema(self, table_name: str) -> Dict[str, Any]:
        """Get detailed schema for a specific table"""
        cursor = self.conn.cursor()

        # Get columns
        cursor.execute("""
            SELECT
                COLUMN_NAME,
                DATA_TYPE,
                CHARACTER_MAXIMUM_LENGTH,
                IS_NULLABLE,
                COLUMN_DEFAULT,
                ORDINAL_POSITION
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        """, (table_name,))

        columns = []
        for row in cursor.fetchall():
            col = {
                "name": row[0],
                "type": row[1],
                "max_length": row[2],
                "nullable": row[3] == 'YES',
                "default": row[4],
                "position": row[5],
                "laravel_type": self._map_to_laravel_type(row[1], row[2]),
                "php_type": self._map_to_php_type(row[1]),
                "fillable": True,
                "cast": self._map_to_laravel_cast(row[1])
            }
            columns.append(col)

        # Get primary key
        cursor.execute("""
            SELECT c.COLUMN_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE c
                ON t.CONSTRAINT_NAME = c.CONSTRAINT_NAME
            WHERE t.TABLE_NAME = ?
            AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
        """, (table_name,))

        primary_keys = [row[0] for row in cursor.fetchall()]

        # Get foreign keys
        cursor.execute("""
            SELECT
                fk.COLUMN_NAME,
                pk.TABLE_NAME,
                pk.COLUMN_NAME
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE fk
                ON rc.CONSTRAINT_NAME = fk.CONSTRAINT_NAME
            JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS pk
                ON rc.UNIQUE_CONSTRAINT_NAME = pk.CONSTRAINT_NAME
            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE pk
                ON pk.CONSTRAINT_NAME = pk.CONSTRAINT_NAME
            WHERE fk.TABLE_NAME = ?
        """, (table_name,))

        foreign_keys = []
        for row in cursor.fetchall():
            foreign_keys.append({
                "column": row[0],
                "references_table": row[1],
                "references_column": row[2],
                "relationship": self._guess_relationship(row[0], row[1])
            })

        return {
            "table_name": table_name,
            "primary_key": primary_keys[0] if primary_keys else None,
            "primary_keys": primary_keys,
            "columns": columns,
            "foreign_keys": foreign_keys,
            "model_name": self._guess_model_name(table_name),
            "auto_increment": self._check_auto_increment(table_name, cursor)
        }

    def _map_to_laravel_type(self, sql_type: str, max_length: int = None) -> str:
        """Map SQL Server type to Laravel migration type"""
        type_map = {
            'varchar': "string($length)" if max_length else "string",
            'nvarchar': "string($length)" if max_length else "string",
            'char': "string($length)",
            'nchar': "string($length)",
            'int': 'integer',
            'bigint': 'bigInteger',
            'smallint': 'smallint',
            'tinyint': 'tinyint',
            'decimal': 'decimal($precision, $scale)',
            'numeric': 'decimal($precision, $scale)',
            'float': 'float',
            'real': 'float',
            'date': 'date',
            'datetime': 'dateTime',
            'datetime2': 'dateTime',
            'smalldatetime': 'dateTime',
            'timestamp': 'timestamp',
            'bit': 'boolean',
            'text': 'text',
            'ntext': 'text',
            'money': 'decimal(19,4)',
            'xml': 'text',
            'uniqueidentifier': 'uuid',
        }

        base_type = sql_type.lower()
        for key, value in type_map.items():
            if key in base_type:
                return value.replace('$length', str(max_length or 255))
        return 'string'

    def _map_to_php_type(self, sql_type: str) -> str:
        """Map SQL Server type to PHP type"""
        type_map = {
            'varchar': 'string',
            'nvarchar': 'string',
            'char': 'string',
            'nchar': 'string',
            'int': 'int',
            'bigint': 'int',
            'smallint': 'int',
            'tinyint': 'int',
            'decimal': 'float',
            'numeric': 'float',
            'float': 'float',
            'real': 'float',
            'date': 'string',
            'datetime': 'string',
            'datetime2': 'string',
            'bit': 'bool',
            'text': 'string',
            'ntext': 'string',
            'money': 'float',
        }
        return type_map.get(sql_type.lower(), 'string')

    def _map_to_laravel_cast(self, sql_type: str) -> str:
        """Map SQL Server type to Laravel cast"""
        type_map = {
            'varchar': 'string',
            'nvarchar': 'string',
            'int': 'integer',
            'bigint': 'integer',
            'decimal': 'decimal:2',
            'float': 'float',
            'date': 'date',
            'datetime': 'datetime',
            'bit': 'boolean',
            'text': 'string',
        }
        return type_map.get(sql_type.lower(), 'string')

    def _guess_model_name(self, table_name: str) -> str:
        """Guess Laravel model name from table name"""
        # Remove 'db' prefix if present
        name = table_name
        if name.startswith('db'):
            name = name[2:]

        # Convert PascalCase to singular
        # Simple heuristic
        if name.endswith('s'):
            name = name[:-1]

        # Capitalize first letter
        return name.capitalize()

    def _guess_relationship(self, column: str, ref_table: str) -> str:
        """Guess relationship type based on naming"""
        ref_model = self._guess_model_name(ref_table)

        # If column ends with _id, it's belongsTo
        if column.endswith('_id') or column.startswith('Kode') or column.startswith('ID'):
            return f"belongsTo({ref_model})"

        return f"belongsTo({ref_model})"

    def _check_auto_increment(self, table_name: str, cursor) -> bool:
        """Check if table has identity column"""
        try:
            cursor.execute(f"""
                SELECT COLUMNPROPERTY(
                    OBJECT_ID('{table_name}'),
                    COLUMN_NAME,
                    'IsIdentity'
                )
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = '{table_name}'
            """)
            result = cursor.fetchone()
            return result[0] == 1 if result else False
        except:
            return False

    def get_all_schemas(self) -> Dict[str, Any]:
        """Get schema for all tables"""
        tables = self.get_tables()
        print(f"Found {len(tables)} tables")

        schemas = {}
        for table in tables:
            print(f"  Reading {table}...")
            schemas[table] = self.get_table_schema(table)

        return schemas

    def close(self):
        """Close database connection"""
        if self.conn:
            self.conn.close()


def get_connection_from_env() -> str:
    """Build connection string from environment or .env file"""
    # Try to read from Laravel .env file
    env_path = os.path.join(os.getcwd(), 'be-keu', '.env')

    default_values = {
        'DB_HOST': 'localhost',
        'DB_PORT': '1433',
        'DB_DATABASE': 'dbwbcp2',
        'DB_USERNAME': 'sa',
        'DB_PASSWORD': ''
    }

    if os.path.exists(env_path):
        with open(env_path, 'r') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    key, value = line.split('=', 1)
                    if key.startswith('DB_'):
                        default_values[key] = value.strip('"\'')

    # Map Laravel to pyodbc format
    driver = '{ODBC Driver 17 for SQL Server}'
    server = default_values.get('DB_HOST', 'localhost')
    port = default_values.get('DB_PORT', '1433')
    database = default_values.get('DB_DATABASE', 'ksppare')
    username = default_values.get('DB_USERNAME', 'sa')
    password = default_values.get('DB_PASSWORD', '')

    # IMPORTANT: Escape backslashes in server name (e.g., (local)\SQL2019)
    server = server.replace('\\', '\\\\')

    conn_str = f"DRIVER={driver};SERVER={server},{port};DATABASE={database};UID={username};PWD={password}"
    return conn_str


def main():
    parser = argparse.ArgumentParser(description='Read SQL Server schema for Laravel generation')
    parser.add_argument('--table', help='Specific table to read (default: all)')
    parser.add_argument('--output', help='Output JSON file', default='database_schema.json')
    parser.add_argument('--connection', help='ODBC connection string')
    parser.add_argument('--pretty', action='store_true', help='Pretty print JSON')

    args = parser.parse_args()

    # Get connection string
    if args.connection:
        conn_str = args.connection
    else:
        conn_str = get_connection_from_env()
        print("Using connection from .env file")
        print(f"Database: {conn_str.split('DATABASE=')[1].split(';')[0]}")

    # Connect and read
    reader = SQLServerSchemaReader(conn_str)

    if not reader.connect():
        sys.exit(1)

    try:
        if args.table:
            schema = reader.get_table_schema(args.table)
            result = {args.table: schema}
        else:
            result = reader.get_all_schemas()

        # Add metadata
        output = {
            "scan_date": datetime.now().isoformat(),
            "database": conn_str.split('DATABASE=')[1].split(';')[0],
            "tables": result
        }

        # Write output
        with open(args.output, 'w', encoding='utf-8') as f:
            if args.pretty:
                json.dump(output, f, indent=2, ensure_ascii=False)
            else:
                json.dump(output, f, ensure_ascii=False)

        print(f"\n✓ Schema saved to {args.output}")
        print(f"  Total tables: {len(result)}")

    finally:
        reader.close()


if __name__ == '__main__':
    main()
