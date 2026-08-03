@echo off
setlocal

REM ==========================================
REM R&R COLLECTION - DATABASE BACKUP
REM ==========================================

set "MYSQLDUMP=C:\xampp\mysql\bin\mysqldump.exe"
set "DB_NAME=rnr_collection"
set "BACKUP_DIR=C:\xampp\htdocs\rnr_collection\backups\database"

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=1-3 delims=/" %%a in ("%date%") do set "DATE=%%c-%%b-%%a"
for /f "tokens=1-3 delims=:." %%a in ("%time%") do set "TIME=%%a-%%b-%%c"

set "BACKUP_FILE=%BACKUP_DIR%\rnr_collection_%DATE%_%TIME%.sql"

echo.
echo ==========================================
echo       R&R COLLECTION DATABASE BACKUP
echo ==========================================
echo.
echo Database: %DB_NAME%
echo.
echo Creating backup...
echo.

"%MYSQLDUMP%" -u root "%DB_NAME%" > "%BACKUP_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ==========================================
    echo       BACKUP SUCCESSFUL
    echo ==========================================
    echo.
    echo File created:
    echo %BACKUP_FILE%
) else (
    echo.
    echo ==========================================
    echo       BACKUP FAILED
    echo ==========================================
    echo.
    echo Check that MySQL is running in XAMPP.
)

echo.
pause
endlocal