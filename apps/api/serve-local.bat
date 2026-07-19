@echo off
setlocal
cd /d "%~dp0"
call "%~dp0php-local.bat" artisan serve --host=127.0.0.1 --port=8000
