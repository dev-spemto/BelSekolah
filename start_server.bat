@echo off
cd /d "%~dp0"
"%~dp0php\php.exe" artisan serve --host=127.0.0.1 --port=8000