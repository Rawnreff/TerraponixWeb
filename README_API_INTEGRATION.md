# TERRAPONIX API Integration Guide

## Overview
Panduan ini menjelaskan cara mengintegrasikan sistem ESP32 dengan API Laravel untuk sistem greenhouse TERRAPONIX.

## Prerequisites
- ESP32 dengan WiFi
- Laravel aplikasi running di laptop/server
- Koneksi WiFi/Hotspot yang sama untuk ESP32 dan laptop

## Setup Koneksi

### 1. Setup Laravel Server

#### Cara 1: Menggunakan PHP Built-in Server
```bash
# Pastikan berada di direktori proyek Laravel
cd /path/to/your/terraponix-project

# Jalankan server di IP yang dapat diakses ESP32
php artisan serve --host=0.0.0.0 --port=8000
```

#### Cara 2: Menggunakan Laravel Valet (MacOS)
```bash
# Install Valet
composer global require laravel/valet
valet install

# Di direktori proyek
valet link terraponix
```

#### Cara 3: Menggunakan XAMPP/WAMP
1. Copy proyek ke folder htdocs/www
2. Akses melalui `http://localhost/terraponix/public`

### 2. Mendapatkan IP Address Laptop

#### Windows:
```cmd
ipconfig
# Cari IP Address di "Wireless LAN adapter Wi-Fi"
# Contoh: 192.168.1.100
```

#### Linux/MacOS:
```bash
ifconfig
# atau
ip addr show
# Cari IP di interface WiFi (wlan0, en0, dll)
# Contoh: 192.168.1.100
```

### 3. Update ESP32 Code

Ubah konfigurasi di file ESP32:

```cpp
// WiFi Credentials - sesuaikan dengan hotspot/WiFi Anda
const char* ssid = "Xiaomi 14T Pro";        // Nama WiFi/Hotspot
const char* password = "jougen92";          // Password WiFi

// API Configuration - ganti dengan IP laptop Anda
const char* serverUrl = "http://192.168.1.100:8000/api/v1";  // IP laptop + port Laravel
const char* apiKey = "your-api-key-if-any"; 

// Device ID - harus ada di database Laravel
const int deviceId = 1;
```

## API Endpoints

### 1. Sensor Data (POST)
**Endpoint:** `POST /api/v1/sensor-data`

**Request Body:**
```json
{
    "device_id": 1,
    "temperature": 28.5,
    "humidity": 65.2,
    "ph_value": 6.8,
    "light_intensity": 2500,
    "water_level": 1800,
    "co2_level": 400,
    "soil_moisture": 75
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Sensor data saved successfully",
    "data": {
        "id": 123,
        "device_id": 1,
        "temperature": 28.5,
        "humidity": 65.2,
        "ph_value": 6.8,
        "light_intensity": 2500,
        "water_level": 1800,
        "created_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

### 2. Actuator Status (GET)
**Endpoint:** `GET /api/v1/devices/{device_id}/actuator-status`

**Response:**
```json
{
    "status": "success",
    "data": {
        "device_id": 1,
        "curtain_position": 75,
        "fan_status": true,
        "water_pump_status": false,
        "last_updated": "2024-01-15T10:30:00.000000Z"
    }
}
```

### 3. Get Latest Sensor Data (GET)
**Endpoint:** `GET /api/v1/sensor-data/latest`

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 123,
            "device_id": 1,
            "temperature": 28.5,
            "humidity": 65.2,
            "ph_value": 6.8,
            "light_intensity": 2500,
            "water_level": 1800,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "device": {
                "id": 1,
                "name": "Greenhouse A",
                "location": "Sector 1"
            }
        }
    ]
}
```

### 4. Control Actuator (POST)
**Endpoint:** `POST /api/v1/actuator/control`

**Request Body:**
```json
{
    "device_id": 1,
    "actuator_type": "fan",
    "value": true
}
```

**Actuator Types:**
- `curtain`: value 0-100 (percentage)
- `fan`: value true/false
- `water_pump`: value true/false

## Database Setup

### 1. Menjalankan Migration
```bash
php artisan migrate
```

### 2. Seeder untuk Data Awal
```bash
php artisan db:seed
```

Atau insert manual device di database:

```sql
INSERT INTO devices (name, location, ip_address, status, created_at, updated_at) 
VALUES ('Greenhouse A', 'Sector 1', '192.168.1.101', 'offline', NOW(), NOW());

INSERT INTO actuator_statuses (device_id, curtain_position, fan_status, water_pump_status, last_updated) 
VALUES (1, 50, false, false, NOW());

INSERT INTO settings (device_id, temp_threshold, light_threshold, water_level_threshold, ph_min, ph_max, auto_mode, created_at, updated_at) 
VALUES (1, 30.0, 2000, 1500, 6.0, 7.5, false, NOW(), NOW());
```

## Testing Koneksi

### 1. Test dari Browser
Buka browser dan akses:
```
http://192.168.1.100:8000/api/v1/devices
```

Jika berhasil, akan muncul response JSON dengan daftar devices.

### 2. Test dengan cURL
```bash
# Test GET devices
curl -X GET "http://192.168.1.100:8000/api/v1/devices"

# Test POST sensor data
curl -X POST "http://192.168.1.100:8000/api/v1/sensor-data" \
     -H "Content-Type: application/json" \
     -d '{
         "device_id": 1,
         "temperature": 25.5,
         "humidity": 60.0,
         "ph_value": 6.5,
         "light_intensity": 1800,
         "water_level": 2000
     }'
```

### 3. Test ESP32 Connection
Upload kode ke ESP32 dan buka Serial Monitor (115200 baud). Anda akan melihat:

```
Sistem TERRAPONIX Started!
==========================
Menghubungkan ke WiFi...
WiFi Terhubung!
Alamat IP: 192.168.1.101

=== DATA SENSOR ===
Suhu: 25.50 °C
Kelembapan: 60.00 %
pH Air: 6.50 (0-14)
Cahaya (LDR): 1800
Level Air: CUKUP
Nilai Tinggi Air Sensor: 2000
Status Tirai: Terbuka
Status WiFi: Terhubung
Alamat IP: 192.168.1.101
===================
Response: {"status":"success","message":"Sensor data saved successfully","data":{"device_id":1,"temperature":25.5,"humidity":60,"ph_value":6.5,"light_intensity":1800,"water_level":2000,"updated_at":"2024-01-15T10:30:00.000000Z","created_at":"2024-01-15T10:30:00.000000Z","id":123}}
```

## Troubleshooting

### ESP32 tidak bisa connect ke API

1. **Cek WiFi Connection:**
   ```cpp
   if (WiFi.status() == WL_CONNECTED) {
     Serial.print("IP Address: ");
     Serial.println(WiFi.localIP());
   }
   ```

2. **Ping test dari ESP32 ke laptop:**
   - ESP32 IP: 192.168.1.101
   - Laptop IP: 192.168.1.100
   - Test: `ping 192.168.1.100` dari command prompt

3. **Cek Firewall:**
   - Windows: Allow port 8000 di Windows Firewall
   - Linux: `sudo ufw allow 8000`
   - MacOS: System Preferences > Security & Privacy > Firewall

4. **Cek Laravel server:**
   ```bash
   # Pastikan server running dan listening di 0.0.0.0
   php artisan serve --host=0.0.0.0 --port=8000
   ```

### Error 404 Not Found

1. Pastikan URL benar: `http://IP:PORT/api/v1/endpoint`
2. Cek file `routes/api.php`
3. Clear route cache: `php artisan route:clear`

### Error 500 Internal Server Error

1. Cek Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode di `.env`: `APP_DEBUG=true`
3. Cek database connection di `.env`

### Error CORS (jika akses dari browser)

Tambahkan CORS middleware jika perlu:
```bash
php artisan make:middleware Cors
```

## Monitoring dan Debugging

### 1. Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### 2. ESP32 Serial Monitor
Gunakan Arduino IDE Serial Monitor atau:
```bash
# Linux/MacOS
screen /dev/ttyUSB0 115200

# Windows (gunakan PuTTY atau Arduino IDE)
```

### 3. Network Monitoring
```bash
# Monitor traffic di port 8000
sudo netstat -tulpn | grep :8000

# Monitor HTTP requests
sudo tcpdump -i any port 8000
```

## Production Deployment

Untuk production, disarankan menggunakan:

1. **Web Server:** Nginx + PHP-FPM
2. **Database:** MySQL/PostgreSQL
3. **Process Manager:** Supervisor
4. **SSL/TLS:** Let's Encrypt
5. **Load Balancer:** jika multiple devices

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/terraponix/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Security Considerations

1. **API Authentication:** Implementasi API key atau JWT token
2. **Rate Limiting:** Batasi request per device
3. **Input Validation:** Validasi semua input sensor
4. **HTTPS:** Gunakan SSL untuk production
5. **Database Security:** Proper user permissions dan encryption

---

**Support:** Jika ada masalah, cek dokumentasi Laravel dan ESP32, atau hubungi tim development.