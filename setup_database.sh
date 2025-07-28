#!/bin/bash

echo "🚀 Setting up Terraponix Database..."

# Check if php is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    exit 1
fi

# Check if artisan file exists
if [ ! -f "artisan" ]; then
    echo "❌ artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

echo "📦 Running database migrations..."
php artisan migrate --force

echo "🌱 Running database seeders..."
php artisan db:seed --force

echo "✅ Database setup completed successfully!"
echo ""
echo "🌐 You can now start the server using:"
echo "   php artisan serve"
echo ""
echo "🔗 API Endpoints available:"
echo "   - GET  /api/v1/devices"
echo "   - GET  /api/v1/devices/{id}/actuator-status"
echo "   - POST /api/v1/actuator/control"
echo "   - GET  /api/v1/devices/{id}/actuator-logs"
echo "   - POST /api/v1/actuator/auto-mode"
echo ""