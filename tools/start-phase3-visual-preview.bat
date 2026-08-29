@echo off
setlocal EnableExtensions

rem Phase 3 isolated populated storefront preview for Windows/Laragon.
rem This script never changes the project's normal .env database settings.

cd /d "%~dp0.."

if not exist "artisan" (
    echo ERROR: Run this script from the Rythme repository.
    exit /b 1
)

where php >nul 2>nul
if errorlevel 1 (
    echo ERROR: PHP is not available. Start Laragon, then open its Terminal and run this script again.
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo ERROR: Composer dependencies are missing. Run composer install first.
    exit /b 1
)

set "FIXTURE_DB=%CD%\storage\app\phase3-visual-fixture.sqlite"
set "VERIFY_FILE=%TEMP%\rythme-phase3-db-check.txt"

if exist "%FIXTURE_DB%" del /f /q "%FIXTURE_DB%"
type nul > "%FIXTURE_DB%"

rem Process-local overrides: the persistent MySQL rhythm_db is not selected.
set "APP_ENV=local"
set "APP_DEBUG=false"
set "APP_URL=http://127.0.0.1:8001"
set "DB_CONNECTION=sqlite"
set "DB_DATABASE=%FIXTURE_DB%"
set "CACHE_STORE=file"
set "SESSION_DRIVER=file"
set "QUEUE_CONNECTION=sync"

php artisan config:clear >nul
if errorlevel 1 goto :failed

php artisan tinker --execute="echo config('database.default').'|'.DB::connection()->getDatabaseName();" > "%VERIFY_FILE%"
findstr /l /c:"sqlite|%FIXTURE_DB%" "%VERIFY_FILE%" >nul
if errorlevel 1 (
    echo ERROR: Safety check failed. No migration command was run.
    type "%VERIFY_FILE%"
    del /q "%VERIFY_FILE%" >nul 2>nul
    exit /b 1
)
del /q "%VERIFY_FILE%" >nul 2>nul

echo Safety check passed: isolated SQLite fixture selected.
echo Building 33-product visual catalogue...
php artisan migrate:fresh --seed --force
if errorlevel 1 goto :failed

echo.
echo Populated preview is ready:
echo   Homepage: http://127.0.0.1:8001/
echo   Shop:     http://127.0.0.1:8001/shop
echo.
echo Keep this window open while capturing screenshots.
echo Press Ctrl+C or close this window when finished.
echo.
php artisan serve --host=127.0.0.1 --port=8001
exit /b %errorlevel%

:failed
echo ERROR: The isolated preview could not be prepared. rhythm_db was not selected by this script.
exit /b 1
