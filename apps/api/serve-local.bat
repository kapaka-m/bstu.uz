@echo off
setlocal
cd /d "%~dp0"
php -c "%~dp0php.ini" -S 127.0.0.1:8000 -t "%~dp0public" "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
