@echo off
setlocal
cd /d "%~dp0"
if "%PHP_SERVER_HOST%"=="" set "PHP_SERVER_HOST=0.0.0.0"
if "%PHP_SERVER_PORT%"=="" set "PHP_SERVER_PORT=8000"
for %%I in (php.exe) do set "PHP_BIN=%%~$PATH:I"
for %%I in ("%PHP_BIN%") do set "PHP_EXT_DIR=%%~dpIext"
if not exist "%~dp0storage\temp" mkdir "%~dp0storage\temp"
set "PHP_RUNTIME_INI=%~dp0storage\temp\php.ini"
copy /Y "%~dp0php.ini" "%PHP_RUNTIME_INI%" >nul
>>"%PHP_RUNTIME_INI%" echo extension_dir="%PHP_EXT_DIR%"
set "PHPRC=%~dp0storage\temp"
php -c "%PHP_RUNTIME_INI%" -S %PHP_SERVER_HOST%:%PHP_SERVER_PORT% -t "%~dp0public" "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
