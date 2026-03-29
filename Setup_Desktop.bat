@echo off
TITLE Setup Pharmacy POS Shortcut

:: ==========================================
:: DESKTOP SHORTCUT GENERATOR (ROBUST)
:: ==========================================

:: 1. Calculate Absolute Paths
set "PROJECT_DIR=%~dp0"
:: Remove trailing backslash
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"

set "TARGET_SCRIPT=%PROJECT_DIR%\Launch_App.vbs"
set "ICON_FILE=%PROJECT_DIR%\install\app.ico"

:: 2. Determine Icon Path
if not exist "%ICON_FILE%" (
    set "ICON_PATH=%SystemRoot%\System32\shell32.dll,171"
) else (
    set "ICON_PATH=%ICON_FILE%"
)

set "SHORTCUT_NAME=Pharmacy POS"
set "DESKTOP_DIR=%USERPROFILE%\Desktop"

echo.
echo ========================================================
echo   Creating "Pharmacy POS" Desktop Shortcut...
echo ========================================================
echo.
echo Script:     %TARGET_SCRIPT%
echo WorkingDir: %PROJECT_DIR%
echo.

:: 3. Create Shortcut via PowerShell
:: We explicitly use wscript.exe as the target application and pass the VBS path as an argument.
:: This ensures the correct script host is used.
powershell "$s=(New-Object -COM WScript.Shell).CreateShortcut('%DESKTOP_DIR%\%SHORTCUT_NAME%.lnk');$s.TargetPath='wscript.exe';$s.Arguments='\"%TARGET_SCRIPT%\"';$s.WorkingDirectory='%PROJECT_DIR%';$s.IconLocation='%ICON_PATH%';$s.WindowStyle=7;$s.Save()"

if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Shortcut created successfully on your Desktop!
    echo.
) else (
    echo [ERROR] Failed to create shortcut.
    echo.
)

pause
