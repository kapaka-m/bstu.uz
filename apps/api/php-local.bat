@echo off
for %%I in (php.exe) do set "PHP_BIN=%%~$PATH:I"
for %%I in ("%PHP_BIN%") do set "PHP_EXT_DIR=%%~dpIext"
if not exist "%~dp0storage\temp" mkdir "%~dp0storage\temp"
set "PHP_RUNTIME_INI=%~dp0storage\temp\php.ini"
copy /Y "%~dp0php.ini" "%PHP_RUNTIME_INI%" >nul
>>"%PHP_RUNTIME_INI%" echo extension_dir="%PHP_EXT_DIR%"
set "PHPRC=%~dp0storage\temp"
php -c "%PHP_RUNTIME_INI%" %*
