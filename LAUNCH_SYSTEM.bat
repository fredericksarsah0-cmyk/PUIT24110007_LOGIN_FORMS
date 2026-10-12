@echo off
title Launch Secure Login System
echo ====================================================
echo Starting Secure Login System (Apache & MySQL 3307)
echo ====================================================

:: 1. Check & start Apache
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Apache is already running.
) else (
    echo Starting Apache...
    start "" "C:\xampp\apache\bin\httpd.exe"
)

:: 2. Check & start MySQL on port 3307
netstat -ano | findstr ":3307 " >NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL (Port 3307) is already running.
) else (
    echo Starting MySQL on port 3307...
    start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
)

:: 3. Wait briefly for services
timeout /t 2 /nobreak >nul

:: 4. Launch browser directly to the project
echo Opening project in your default browser...
start http://localhost/simple_auth/login.php

echo.
echo ====================================================
echo Project successfully launched at:
echo http://localhost/simple_auth/login.php
echo ====================================================
echo.
pause
