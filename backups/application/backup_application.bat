@echo off
setlocal

REM =========================================================
REM RNR COLLECTION - APPLICATION BACKUP
REM =========================================================

set "SOURCE=C:\xampp\htdocs\rnr_collection"
set "BACKUP_DIR=C:\xampp\htdocs\rnr_collection\backups\application"

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HH-mm-ss"') do set "TIMESTAMP=%%i"

set "BACKUP_FILE=%BACKUP_DIR%\rnr_application_%TIMESTAMP%.zip"

echo.
echo ==========================================
echo       RNR COLLECTION APPLICATION BACKUP
echo ==========================================
echo.
echo Creating ZIP backup...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command "$source='%SOURCE%'; $destination='%BACKUP_FILE%'; $files=Get-ChildItem -Path $source -Force | Where-Object { $_.Name -ne 'backups' }; Compress-Archive -Path ($files.FullName) -DestinationPath $destination -Force"

if exist "%BACKUP_FILE%" (
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
)

echo.
pause
endlocal