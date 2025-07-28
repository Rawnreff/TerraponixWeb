@echo off
echo 🚀 Setting up Terraponix Database...

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ artisan file not found. Make sure you're in the Laravel project root.
    pause
    exit /b 1
)

echo 📦 Resetting database migrations...
php artisan migrate:reset --force

echo 📦 Running fresh database migrations...
php artisan migrate --force

echo 🌱 Running database seeders...
php artisan db:seed --force

echo ✅ Database setup completed successfully!
echo.
echo 🌐 You can now start the server using:
echo    php artisan serve
echo.
echo 🔗 API Endpoints available:
echo    - GET  /api/v1/devices
echo    - GET  /api/v1/devices/{id}/actuator-status
echo    - POST /api/v1/actuator/control
echo    - GET  /api/v1/devices/{id}/actuator-logs
echo    - POST /api/v1/actuator/auto-mode
echo.
pause