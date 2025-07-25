#!/bin/bash

# TERRAPONIX Server Startup Script
# Script ini memudahkan startup server Laravel untuk greenhouse monitoring

echo "🌱 Starting TERRAPONIX Server..."
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if Laravel is installed
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Make sure you're in Laravel project directory.${NC}"
    exit 1
fi

# Get local IP address
echo -e "${BLUE}📍 Getting local IP address...${NC}"

if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    LOCAL_IP=$(hostname -I | awk '{print $1}')
    INTERFACE=$(ip route | grep default | awk '{print $5}' | head -1)
elif [[ "$OSTYPE" == "darwin"* ]]; then
    # Mac OSX
    LOCAL_IP=$(ifconfig | grep "inet " | grep -Fv 127.0.0.1 | awk '{print $2}' | head -1)
    INTERFACE=$(route get default | grep interface | awk '{print $2}')
else
    # Windows (Git Bash/WSL)
    LOCAL_IP=$(ipconfig | grep -A 1 "Wireless LAN adapter Wi-Fi" | grep "IPv4" | awk '{print $NF}' | tr -d '\r')
    if [ -z "$LOCAL_IP" ]; then
        LOCAL_IP=$(ipconfig | grep -A 1 "Ethernet adapter" | grep "IPv4" | awk '{print $NF}' | tr -d '\r' | head -1)
    fi
fi

if [ -z "$LOCAL_IP" ]; then
    echo -e "${RED}❌ Could not determine local IP address${NC}"
    echo -e "${YELLOW}⚠️  Please run: ipconfig (Windows) or ifconfig (Linux/Mac) to find your IP${NC}"
    LOCAL_IP="localhost"
fi

echo -e "${GREEN}✅ Local IP: $LOCAL_IP${NC}"

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️  .env file not found, copying from .env.example${NC}"
    cp .env.example .env
    echo -e "${BLUE}🔑 Generating application key...${NC}"
    php artisan key:generate
fi

# Check database connection
echo -e "${BLUE}🗄️  Checking database connection...${NC}"
php artisan migrate:status > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Database not configured or not accessible${NC}"
    echo -e "${BLUE}🔧 Running migrations...${NC}"
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Database migrations completed${NC}"
        echo -e "${BLUE}🌱 Seeding initial data...${NC}"
        php artisan db:seed --force
    else
        echo -e "${RED}❌ Database migration failed. Please check your database configuration.${NC}"
    fi
fi

# Clear cache
echo -e "${BLUE}🧹 Clearing cache...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear

PORT=8000

# Check if port is in use
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Port $PORT is already in use${NC}"
    PORT=8001
    if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
        PORT=8002
    fi
    echo -e "${BLUE}📡 Using port $PORT instead${NC}"
fi

echo -e "${GREEN}🚀 Starting Laravel server...${NC}"
echo -e "${BLUE}📍 Server will be available at:${NC}"
echo -e "   🔗 Local: http://localhost:$PORT"
echo -e "   🔗 Network: http://$LOCAL_IP:$PORT"
echo ""
echo -e "${YELLOW}📋 ESP32 Configuration:${NC}"
echo -e "   const char* serverUrl = \"http://$LOCAL_IP:$PORT/api/v1\";"
echo ""
echo -e "${GREEN}✅ Ready for ESP32 connections!${NC}"
echo -e "${BLUE}📊 Access dashboard at: http://$LOCAL_IP:$PORT/dashboard${NC}"
echo -e "${BLUE}⚙️  Access settings at: http://$LOCAL_IP:$PORT/settings${NC}"
echo ""
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
echo "=================================="

# Start the server
php artisan serve --host=0.0.0.0 --port=$PORT