@echo off
REM PHPCS Runner Script
REM Runs PHPCS with custom config to avoid escapeshellarg length limits

if "%~1"=="" (
    echo Usage: %0 ^<file^|directory^>
    echo Example: %0 includes\Admin\Dashboard.php
    echo Example: %0 .
    exit /b 1
)

cd /d "%~dp0"
vendor\bin\phpcs --standard=./phpcs.xml %*
exit /b %errorlevel%