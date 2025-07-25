# 🌱 TERRAPONIX - Setup Guide
**Sistem Monitoring Greenhouse IoT dengan Laravel & ESP32**

## 📋 Overview

TERRAPONIX adalah sistem monitoring greenhouse yang terdiri dari:
- **ESP32** dengan sensor (suhu, kelembapan, pH, cahaya, level air)
- **Website Laravel** untuk monitoring dan kontrol
- **API REST** untuk komunikasi ESP32 dan website
- **Database MySQL** untuk penyimpanan data sensor

## 🛠️ Prerequisites

### Software yang dibutuhkan:
- **PHP 8.1+** dengan extensions: mysql, curl, json, mbstring
- **Composer** (Laravel package manager)
- **MySQL** atau **SQLite** 
- **Arduino IDE** (untuk ESP32)
- **Python 3.x** (untuk testing - optional)

### Hardware yang dibutuhkan:
- **ESP32** development board
- **DHT11** sensor (suhu & kelembapan)
- **pH sensor** analog
- **LDR** sensor (cahaya)
- **Water level sensor** analog
- **Servo motor** (untuk tirai)
- **Relay modules** (untuk kipas & pompa)
- **Breadboard & jumper wires**

## 🚀 Quick Start

### 1. Setup Laravel Project

```bash
# Clone atau extract project ke folder
cd terraponix-project

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database (edit .env file untuk database config)
php artisan migrate
php artisan db:seed
```

### 2. Start Server

#### Option A: Menggunakan Script (Recommended)

**Linux/Mac:**
```bash
./start_server.sh
```

**Windows:**
```batch
start_server.bat
```

#### Option B: Manual
```bash
# Mendapatkan IP address
# Windows: ipconfig
# Linux/Mac: ifconfig

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

### 3. Setup ESP32

1. **Install Library di Arduino IDE:**
   - WiFi
   - HTTPClient  
   - ArduinoJson
   - DHT sensor library
   - ESP32Servo

2. **Wiring Diagram:**
   ```
   ESP32 Pins:
   - DHT11    → Pin 25
   - pH       → Pin 34 (analog)
   - LDR      → Pin 35 (analog)  
   - Servo    → Pin 13
   - Water    → Pin 32 (analog)
   - Fan      → Pin 14
   - Pump     → Pin 27
   ```

3. **Upload Code:**
   - Buka file `esp32_terraponix_final.ino`
   - Ganti WiFi credentials dan IP server
   - Upload ke ESP32

### 4. Test Koneksi

#### Option A: Test dengan Python Script
```bash
# Edit IP di file test_api.py
python3 test_api.py
```

#### Option B: Test dengan Browser
```
http://YOUR_IP:8000/api/v1/devices
```

## 📱 Akses Website

Setelah server running, akses:

- **Dashboard:** `http://YOUR_IP:8000/dashboard`
- **Sensor Data:** `http://YOUR_IP:8000/sensor-data`  
- **Actuator Control:** `http://YOUR_IP:8000/actuator-control`
- **Settings:** `http://YOUR_IP:8000/settings`

## 🔧 Konfigurasi Detail

### Database Configuration (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=terraponix
DB_USERNAME=root
DB_PASSWORD=your_password
```

### ESP32 Configuration

```cpp
// WiFi Settings
const char* ssid = "Your_WiFi_Name";
const char* password = "Your_WiFi_Password";

// Server Settings  
const char* serverUrl = "http://192.168.1.100:8000/api/v1";
const int deviceId = 1;
```

### Sensor Thresholds (dapat diatur di Settings)

```
Temperature: 30°C (fan turns on)
Light: 2000 (curtain closes)
Water Level: 1500 (pump turns on)
pH Range: 6.0 - 7.5 (optimal)
```

## 📊 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/devices` | List all devices |
| POST | `/api/v1/sensor-data` | Send sensor data |
| GET | `/api/v1/sensor-data/latest` | Get latest readings |
| GET | `/api/v1/devices/{id}/actuator-status` | Get actuator status |
| POST | `/api/v1/actuator/control` | Control actuators |
| GET | `/api/v1/devices/{id}/settings` | Get device settings |
| POST | `/api/v1/devices/{id}/settings` | Update settings |

## 🐛 Troubleshooting

### ESP32 tidak connect ke WiFi
```cpp
// Check serial monitor untuk error messages
// Pastikan SSID dan password benar
// Check signal strength
```

### API tidak accessible
```bash
# Check firewall
sudo ufw allow 8000  # Linux
# Windows: Allow port 8000 di Windows Firewall

# Check server running
netstat -tulpn | grep :8000
```

### Database errors
```bash
# Check database connection
php artisan migrate:status

# Reset database
php artisan migrate:fresh --seed
```

### Sensor readings tidak akurat
```cpp
// Calibrate pH sensor
float pH_offset = 0.0;  // Adjust this value

// Check wiring dan power supply
// DHT sensor butuh delay antara readings
```

## 📈 Monitoring

### Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### ESP32 Serial Monitor
- Baud rate: 115200
- Shows sensor readings, WiFi status, API responses

### Network Traffic
```bash
# Monitor HTTP requests
sudo tcpdump -i any port 8000
```

## 🔒 Security (Production)

### 1. Enable HTTPS
```bash
# Using Certbot for Let's Encrypt
sudo certbot --nginx -d yourdomain.com
```

### 2. Database Security
```sql
CREATE USER 'terraponix'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT,INSERT,UPDATE,DELETE ON terraponix.* TO 'terraponix'@'localhost';
```

### 3. API Authentication (Future)
```php
// Add API token authentication
Route::middleware('auth:api')->group(function() {
    // Protected routes
});
```

## 📁 File Structure

```
terraponix/
├── app/
│   ├── Http/Controllers/Api/     # API Controllers
│   ├── Models/                   # Database Models
├── database/
│   ├── migrations/              # Database structure
│   ├── seeders/                 # Sample data
├── resources/views/             # Frontend pages
├── routes/
│   ├── api.php                  # API routes
│   ├── web.php                  # Web routes
├── esp32_terraponix_final.ino   # ESP32 code
├── start_server.sh              # Server startup script
├── start_server.bat             # Windows startup script
├── test_api.py                  # API testing tool
└── README_API_INTEGRATION.md    # Detailed API docs
```

## 🚀 Production Deployment

### Using Nginx + PHP-FPM

```nginx
server {
    listen 80;
    server_name terraponix.yourdomain.com;
    root /var/www/terraponix/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### Process Management
```bash
# Install Supervisor
sudo apt install supervisor

# Create config for Laravel queue worker
sudo nano /etc/supervisor/conf.d/terraponix-worker.conf
```

## 📞 Support

### Debug Checklist
1. ✅ Laravel server running?
2. ✅ Database connected? 
3. ✅ ESP32 connected to WiFi?
4. ✅ ESP32 can reach server IP?
5. ✅ Firewall allows port 8000?
6. ✅ Sensor wiring correct?

### Logs to Check
- Laravel: `storage/logs/laravel.log`
- ESP32: Serial Monitor output
- Web server: access logs & error logs
- Database: MySQL error logs

### Common Issues
- **CORS errors:** Add CORS middleware
- **422 validation errors:** Check sensor data format
- **500 server errors:** Check Laravel logs
- **Network timeouts:** Check firewall & routing

---

**Happy Monitoring! 🌱🏠📊**