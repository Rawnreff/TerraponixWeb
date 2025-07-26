#!/bin/bash

echo "🚀 Setting up Terraponix Database..."
echo "=================================="

# Check if we're in the Laravel project directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root directory."
    exit 1
fi

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "❌ Error: .env file not found. Please create a .env file first."
    exit 1
fi

echo "📋 Step 1: Clearing any existing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "📋 Step 2: Running database migrations..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully!"
else
    echo "❌ Migration failed. Please check your database configuration in .env file."
    exit 1
fi

echo "📋 Step 3: Running database seeders..."
php artisan db:seed --force

if [ $? -eq 0 ]; then
    echo "✅ Seeders completed successfully!"
else
    echo "❌ Seeding failed. Please check your database configuration."
    exit 1
fi

echo "📋 Step 4: Creating storage link..."
php artisan storage:link

echo "📋 Step 5: Setting proper permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  Could not change ownership (may need sudo)"

echo "📋 Step 6: Optimizing Laravel..."
php artisan optimize

echo ""
echo "🎉 Database setup completed successfully!"
echo "=================================="
echo "📊 Your Terraponix system is now ready!"
echo ""
echo "🌐 You can now:"
echo "   - Start the development server: php artisan serve"
echo "   - Access the dashboard at: http://localhost:8000"
echo "   - Test the API endpoints at: http://localhost:8000/api/v1/"
echo ""
echo "📱 ESP32 Configuration:"
echo "   - API Base URL: http://your-server-ip:8000/api/v1"
echo "   - Sensor Data Endpoint: /sensor-data"
echo "   - Actuator Status Endpoint: /devices/{deviceId}/actuator-status"
echo ""
echo "🔧 Next steps:"
echo "   1. Update your ESP32 code with the correct server IP"
echo "   2. Test the API endpoints using the provided test_api.py script"
echo "   3. Configure your sensors and actuators"
echo ""