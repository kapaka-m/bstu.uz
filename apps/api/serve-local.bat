@echo off
setlocal
cd /d "%~dp0"
cd /d "%~dp0public"
php -c "%~dp0php.ini" -S 127.0.0.1:8000 "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
