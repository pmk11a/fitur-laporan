@echo off
REM Database Schema Reader - Scan SQL Server for table structure

echo ========================================
echo Database Schema Reader
echo ========================================
echo.

REM Check if Python is available
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python not found. Please install Python 3.8+
    pause
    exit /b 1
)

REM Install pyodbc if not present
echo Checking dependencies...
python -c "import pyodbc" 2>nul
if errorlevel 1 (
    echo Installing pyodbc...
    pip install pyodbc
)

REM Run the schema reader
echo.
echo Scanning database schema...
echo.

python "%~dp0db_schema_reader.py" %*

echo.
echo ========================================
echo Done! Check database_schema.json
echo ========================================
pause
