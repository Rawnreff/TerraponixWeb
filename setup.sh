#!/bin/bash

# Terraponix Smart Greenhouse Setup Script
echo "🌱 Setting up Terraponix Smart Greenhouse System..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if running on supported OS
if [[ "$OSTYPE" != "linux-gnu"* ]] && [[ "$OSTYPE" != "darwin"* ]]; then
    print_error "This script is only supported on Linux and macOS"
    exit 1
fi

# Check for required tools
print_step "Checking system requirements..."

# Check PHP
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2 | cut -d '.' -f 1,2)
if [[ $(echo "$PHP_VERSION < 8.2" | bc -l) -eq 1 ]]; then
    print_error "PHP version $PHP_VERSION is not supported. Please install PHP 8.2 or higher."
    exit 1
fi
print_status "PHP $PHP_VERSION detected ✓"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi
print_status "Composer detected ✓"

# Check Node.js
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18 or higher."
    exit 1
fi
print_status "Node.js $(node -v) detected ✓"

# Check npm
if ! command -v npm &> /dev/null; then
    print_error "npm is not installed. Please install npm."
    exit 1
fi
print_status "npm $(npm -v) detected ✓"

# Install PHP dependencies
print_step "Installing PHP dependencies..."
if composer install --no-dev --optimize-autoloader; then
    print_status "PHP dependencies installed successfully ✓"
else
    print_error "Failed to install PHP dependencies"
    exit 1
fi

# Install Node.js dependencies
print_step "Installing Node.js dependencies..."
if npm install; then
    print_status "Node.js dependencies installed successfully ✓"
else
    print_error "Failed to install Node.js dependencies"
    exit 1
fi

# Setup environment file
print_step "Setting up environment configuration..."
if [ ! -f .env ]; then
    if cp .env.example .env; then
        print_status "Environment file created ✓"
    else
        print_error "Failed to create environment file"
        exit 1
    fi
else
    print_warning "Environment file already exists, skipping..."
fi

# Generate application key
print_step "Generating application key..."
if php artisan key:generate --force; then
    print_status "Application key generated ✓"
else
    print_error "Failed to generate application key"
    exit 1
fi

# Database setup prompt
print_step "Database setup..."
echo "Please configure your database in the .env file before continuing."
echo "Update the following variables:"
echo "  DB_CONNECTION=mysql"
echo "  DB_HOST=127.0.0.1"
echo "  DB_PORT=3306"
echo "  DB_DATABASE=terraponix"
echo "  DB_USERNAME=your_username"
echo "  DB_PASSWORD=your_password"
echo ""
read -p "Have you configured the database settings? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Continuing with database migration..."
else
    print_warning "Please configure the database and run 'php artisan migrate' manually"
    exit 0
fi

# Run database migrations
print_step "Running database migrations..."
if php artisan migrate --force; then
    print_status "Database migrations completed ✓"
else
    print_error "Database migration failed. Please check your database configuration."
    exit 1
fi

# Seed demo data
print_step "Seeding demo data..."
read -p "Do you want to generate demo sensor data? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if php artisan db:seed --class=DemoDataSeeder; then
        print_status "Demo data seeded successfully ✓"
    else
        print_warning "Failed to seed demo data, continuing..."
    fi
fi

# Build frontend assets
print_step "Building frontend assets..."
if npm run build; then
    print_status "Frontend assets built successfully ✓"
else
    print_error "Failed to build frontend assets"
    exit 1
fi

# Set permissions
print_step "Setting file permissions..."
if chmod -R 755 storage bootstrap/cache; then
    print_status "File permissions set ✓"
else
    print_warning "Could not set file permissions, you may need to do this manually"
fi

# Clear and cache configurations
print_step "Optimizing application..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

if php artisan config:cache && php artisan route:cache && php artisan view:cache; then
    print_status "Application optimized ✓"
else
    print_warning "Could not optimize application, continuing..."
fi

# Success message
echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Start the Laravel server: php artisan serve"
echo "2. Visit http://localhost:8000 to access the dashboard"
echo "3. Configure your ESP32 with the API endpoints:"
echo "   - Sensor data: POST http://your-server:8000/api/sensor-data"
echo "   - Actuator commands: GET http://your-server:8000/api/actuator-commands"
echo ""
echo "📡 ESP32 Configuration:"
echo "   const char* serverUrl = \"http://$(hostname -I | awk '{print $1}'):8000/api\";"
echo ""
echo "🔧 For development mode, run: npm run dev (in separate terminal)"
echo ""
print_status "Happy monitoring! 🌱"