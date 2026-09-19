@echo off
cd /d "%~dp0"

:: 1. Hentikan proses PHP lama jika ada
taskkill /F /IM php.exe >nul 2>&1

:: 2. Jalankan Laravel Server di Port 8085
"%~dp0php\php.exe" artisan serve --host=127.0.0.1 --port=8085