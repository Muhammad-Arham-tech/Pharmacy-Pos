@echo off
SETLOCAL EnableExtensions
CD /D "%~dp0"
TITLE Pharmacy POS - Engine Controller

echo ==================================================

echo      PHARMACY POS - PORTABLE LAUNCHER

echo ==================================================

:: --------------------------------------------------------
:: 1. DATABASE INITIALIZATION CHECK
:: --------------------------------------------------------
:: We check if the 'mysql' system folder exists inside 'data'.
:: If not, we assume this is a fresh install and run initialization.
if not exist "data\mysql" (
    echo [SETUP] First run detected. Initializing database...
    
    :: Create data directory if it doesn't exist
    if not exist "data" mkdir "data"
    
    :: Run the MariaDB initialization tool
    :: We use --datadir to point to our local portable folder
    if exist "bin\mariadb\bin\mysql_install_db.exe" (
        "bin\mariadb\bin\mysql_install_db.exe" --datadir=.\data > NUL
        echo [SETUP] Database initialized successfully.
    ) else (
        echo [ERROR] Could not find 'mysql_install_db.exe' in bin\mariadb\bin\
        echo Please ensure MariaDB binaries are placed correctly.
        pause
        exit /b 1
    )
)

:: --------------------------------------------------------
:: 2. START MARIADB ENGINE (Port 3307)
:: --------------------------------------------------------
echo [BOOT] Starting Database Engine (Port 3307)...
if exist "bin\mariadb\bin\mysqld.exe" (
    :: Starts silently in background (/B). 
    :: Redirects output to NUL to keep window clean.
    start "" /B "bin\mariadb\bin\mysqld.exe" --no-defaults --port=3307 --datadir=".\data" --console > NUL 2>&1
) else (
    echo [ERROR] mysqld.exe not found!
    pause
    exit /b 1
)

:: --------------------------------------------------------
:: 3. START PHP WEB ENGINE (Port 8080)
:: --------------------------------------------------------
echo [BOOT] Starting Web Server (Port 8080)...
if exist "bin\php\php.exe" (
    start "" /B "bin\php\php.exe" -S localhost:8080 -t . > NUL 2>&1
) else (
    echo [ERROR] php.exe not found!
    pause
    exit /b 1
)

:: --------------------------------------------------------
:: 4. LAUNCH BROWSER
:: --------------------------------------------------------
echo [SUCCESS] System is running. Launching interface...
timeout /t 5 > NUL
start http://localhost:8080

:: Keep the window open just for a moment to show status, then close or stay?
:: Usually for a portable app, we might want this window to close 
:: or stay open as a "Console". 
:: If you use the VBS launcher, this window is hidden anyway.
exit /b 0