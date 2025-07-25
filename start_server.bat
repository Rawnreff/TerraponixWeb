@echo off
title TERRAPONIX Server

echo 🌱 Starting TERRAPONIX Server...
echo ==================================

:: Check if Laravel is installed
if not exist "artisan" (
    echo ❌ Error: artisan file not found. Make sure you're in Laravel project directory.
    pause
    exit /b 1
)

:: Get local IP address
echo 📍 Getting local IP address...
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| findstr /C:"IPv4 Address"') do (
    set "ip=%%i"
    set "ip=!ip: =!"
    goto :found_ip
)

:found_ip
if "%ip%"=="" (
    echo ❌ Could not determine local IP address
    echo ⚠️  Please run ipconfig to find your IP manually
    set ip=localhost
)

echo ✅ Local IP: %ip%

:: Check if .env file exists
if not exist ".env" (
    echo ⚠️  .env file not found, copying from .env.example
    copy .env.example .env
    echo 🔑 Generating application key...
    php artisan key:generate
)

:: Check database and run migrations
echo 🗄️  Checking database connection...
php artisan migrate:status >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Database not configured or not accessible
    echo 🔧 Running migrations...
    php artisan migrate --force
    if not errorlevel 1 (
        echo ✅ Database migrations completed
        echo 🌱 Seeding initial data...
        php artisan db:seed --force
    ) else (
        echo ❌ Database migration failed. Please check your database configuration.
    )
)

:: Clear cache
echo 🧹 Clearing cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear

:: Set port
set PORT=8000

:: Check if port is in use
netstat -an | find ":%PORT%" | find "LISTENING" >nul
if not errorlevel 1 (
    echo ⚠️  Port %PORT% is already in use
    set PORT=8001
    netstat -an | find ":8001" | find "LISTENING" >nul
    if not errorlevel 1 (
        set PORT=8002
    )
    echo 📡 Using port %PORT% instead
)

echo 🚀 Starting Laravel server...
echo 📍 Server will be available at:
echo    🔗 Local: http://localhost:%PORT%
echo    🔗 Network: http://%ip%:%PORT%
echo.
echo 📋 ESP32 Configuration:
echo    const char* serverUrl = "http://%ip%:%PORT%/api/v1";
echo.
echo ✅ Ready for ESP32 connections!
echo 📊 Access dashboard at: http://%ip%:%PORT%/dashboard
echo ⚙️  Access settings at: http://%ip%:%PORT%/settings
echo.
echo Press Ctrl+C to stop the server
echo ==================================

:: Start the server
php artisan serve --host=0.0.0.0 --port=%PORT%

pause