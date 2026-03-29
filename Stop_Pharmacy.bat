@echo off
TITLE Stop Pharmacy POS
CD /D "%~dp0"

echo ==================================================
echo      STOPPING PHARMACY POS SYSTEM
echo ==================================================
echo.

:: 1. Kill PHP Server
taskkill /F /IM php.exe /T > NUL 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Web Server Stopped.
) else (
    echo [INFO] Web Server was not running.
)

:: 2. Kill MariaDB/MySQL
:: Note: This kills ALL mysqld.exe processes. 
:: If you have other local SQL servers running, this might affect them.
taskkill /F /IM mysqld.exe /T > NUL 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [OK] Database Service Stopped.
) else (
    echo [INFO] Database Service was not running.
)

echo.
echo All systems shut down safely.
timeout /t 3 > NUL
