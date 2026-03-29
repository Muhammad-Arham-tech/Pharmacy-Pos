@echo off
TITLE Setup Pharmacy POS Shortcut

:: ==========================================
:: DESKTOP SHORTCUT GENERATOR
:: ==========================================

:: 1. Set Target to the VBS script (this launches the app silently)
set "TARGET_SCRIPT=%~dp0Launch_Pharmacy.vbs"

:: 2. Set Icon (Fall back to system icon if app.ico is missing)
set "ICON_FILE=%~dp0install\app.ico"
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
echo Target: %TARGET_SCRIPT%
echo Icon:   %ICON_PATH%
echo.

:: 3. Use PowerShell to create the shortcut file (.lnk)
:: We set the WorkingDirectory to the script's folder (%~dp0) so relative paths work.
powershell "$s=(New-Object -COM WScript.Shell).CreateShortcut('%DESKTOP_DIR%\%SHORTCUT_NAME%.lnk');$s.TargetPath='%TARGET_SCRIPT%';$s.WorkingDirectory='%~dp0';$s.IconLocation='%ICON_PATH%';$s.Save()"

if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Shortcut created successfully on your Desktop!
    echo.
) else (
    echo [ERROR] Failed to create shortcut.
    echo.
)

pause