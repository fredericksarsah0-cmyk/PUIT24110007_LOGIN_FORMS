@echo off
title Stop Secure Login System
echo Stopping Apache and XAMPP MySQL (Port 3307)...

:: Shutdown XAMPP MySQL on port 3307 cleanly
"C:\xampp\mysql\bin\mysqladmin.exe" --port=3307 -u root shutdown >nul 2>&1

:: Stop Apache cleanly
"C:\xampp\apache\bin\httpd.exe" -k stop >nul 2>&1
taskkill /F /IM httpd.exe >nul 2>&1

echo Services stopped successfully.
pause
