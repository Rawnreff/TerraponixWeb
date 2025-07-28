# 🚀 TERRAPONIX QUICK START GUIDE

## 📋 PREREQUISITES
- PHP 8.4+ (sudah terinstall)
- SQLite3 (sudah terinstall)
- Composer (sudah terinstall)
- Python3 dengan requests (untuk testing)

## 🏃‍♂️ QUICK START

### 1. **Start Laravel Server**
```bash
cd /workspace
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. **Access Web Interface**
- **Dashboard**: http://localhost:8000/
- **Actuator Control**: http://localhost:8000/actuator-control
- **Settings**: http://localhost:8000/settings

### 3. **Test API Endpoints**
```bash
# Test semua API endpoints
python3 test_api_improved.py

# Test manual endpoint
curl -X GET http://localhost:8000/api/v1/devices/1/actuator-status
```

## 🎮 ACTUATOR CONTROL

### **Web Interface**
1. Buka http://localhost:8000/actuator-control
2. **Curtain Control**: Gunakan slider untuk mengatur posisi (0-100%)
3. **Fan Control**: Toggle switch ON/OFF
4. **Water Pump**: Toggle switch ON/OFF
5. **Auto Mode**: Switch untuk mode otomatis
6. **History**: Lihat log real-time di bagian bawah (auto-refresh setiap 10s)

### **API Control (untuk ESP32)**
```bash
# Control Curtain
curl -X POST http://localhost:8000/api/v1/actuator/control \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "actuator_type": "curtain",
    "value": 75,
    "triggered_by": "esp32",
    "notes": "Automatic light adjustment"
  }'

# Control Fan
curl -X POST http://localhost:8000/api/v1/actuator/control \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "actuator_type": "fan",
    "value": true,
    "triggered_by": "esp32"
  }'

# Get Status
curl -X GET http://localhost:8000/api/v1/devices/1/actuator-status
```

## 📊 REAL-TIME MONITORING

### **Settings Page**
1. Buka http://localhost:8000/settings
2. **Device Info**: Auto-refresh setiap 30s
3. **Actuator Status**: Auto-refresh setiap 5s
4. **Device Settings**: Real-time threshold values
5. **Manual Control**: Langsung kontrol actuator dari settings

### **Auto-Refresh Features**
- ✅ Actuator status: Update setiap 3 detik
- ✅ History logs: Update setiap 10 detik
- ✅ Device settings: Update setiap 5 detik
- ✅ Device list: Update setiap 30 detik

## 🔍 HISTORY TRACKING

### **View Logs**
```bash
# Get all history
curl "http://localhost:8000/api/v1/actuator/history?device_id=1&limit=10"

# Get curtain history only
curl "http://localhost:8000/api/v1/actuator/history?device_id=1&actuator_type=curtain&limit=5"
```

### **Log Format**
```json
{
  "id": 1,
  "device_id": 1,
  "actuator_type": "curtain",
  "action": "manual_control",
  "old_value": {"curtain": 90},
  "new_value": {"curtain": 75},
  "triggered_by": "manual",
  "notes": "User adjustment",
  "created_at": "2025-07-28T08:02:35.000000Z"
}
```

## 🤖 ESP32 INTEGRATION

### **Update ESP32 Code**
ESP32 code yang ada sudah kompatibel! Hanya perlu update URL endpoint:

```cpp
// Di ESP32 code, function checkActuatorControl()
void checkActuatorControl() {
    HTTPClient http;
    http.begin(String(serverUrl) + "/api/v1/devices/" + String(deviceId) + "/actuator-status");
    
    int httpResponseCode = http.GET();
    if (httpResponseCode == 200) {
        String response = http.getString();
        
        // Parse JSON response
        DynamicJsonDocument doc(1024);
        deserializeJson(doc, response);
        
        if (doc["status"] == "success") {
            JsonObject data = doc["data"];
            
            // Control curtain
            int curtainPos = data["curtain_position"];
            servo.write(map(curtainPos, 0, 100, 0, 180));
            
            // Control fan
            bool fanStatus = data["fan_status"];
            digitalWrite(FAN_PIN, fanStatus ? HIGH : LOW);
            
            // Control pump
            bool pumpStatus = data["water_pump_status"];
            digitalWrite(PUMP_PIN, pumpStatus ? HIGH : LOW);
        }
    }
}
```

### **ESP32 Send Control**
```cpp
// Function untuk send control dari ESP32
void sendActuatorControl(String actuatorType, float value, String notes = "") {
    HTTPClient http;
    http.begin(String(serverUrl) + "/api/v1/actuator/control");
    http.addHeader("Content-Type", "application/json");
    
    String payload = "{";
    payload += "\"device_id\":" + String(deviceId) + ",";
    payload += "\"actuator_type\":\"" + actuatorType + "\",";
    payload += "\"value\":" + String(value) + ",";
    payload += "\"triggered_by\":\"esp32\"";
    if (notes != "") {
        payload += ",\"notes\":\"" + notes + "\"";
    }
    payload += "}";
    
    int httpResponseCode = http.POST(payload);
    
    if (httpResponseCode == 200) {
        Serial.println("✅ Actuator control sent successfully");
    } else {
        Serial.println("❌ Failed to send actuator control");
    }
    
    http.end();
}
```

## 🧪 TESTING

### **Comprehensive API Test**
```bash
# Run full test suite
python3 test_api_improved.py
```

### **Manual Testing**
```bash
# Test curtain control
curl -X POST http://localhost:8000/api/v1/actuator/control \
  -H "Content-Type: application/json" \
  -d '{"device_id":1,"actuator_type":"curtain","value":50,"triggered_by":"manual"}'

# Check result
curl http://localhost:8000/api/v1/devices/1/actuator-status

# View history
curl "http://localhost:8000/api/v1/actuator/history?device_id=1&limit=3"
```

## 📱 WEB INTERFACE FEATURES

### **Actuator Control Page**
- 🎛️ **Curtain Slider**: Real-time position control (0-100%)
- 🌪️ **Fan Toggle**: ON/OFF with visual feedback
- 💧 **Pump Toggle**: ON/OFF with visual feedback
- 🤖 **Auto Mode**: Enable/disable automatic control
- 📋 **History Table**: Real-time logs with auto-refresh
- 🔔 **Notifications**: Success/error alerts

### **Settings Page**
- ⚙️ **Threshold Settings**: Temperature, light, water, pH
- 📊 **Device Status**: Real-time online/offline status
- 🎮 **Quick Controls**: Direct actuator control
- 📈 **Live Data**: Auto-refreshing device information

## 🚨 TROUBLESHOOTING

### **Server Issues**
```bash
# Restart Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Check database
php artisan migrate:status

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### **API Issues**
```bash
# Test basic connectivity
curl http://localhost:8000/api/v1/devices/1/actuator-status

# Check logs
tail -f storage/logs/laravel.log
```

### **Database Issues**
```bash
# Reset database (WARNING: deletes all data)
php artisan migrate:fresh --seed
```

## 📈 MONITORING

### **Real-time Dashboard**
- Access http://localhost:8000/ untuk overview
- Monitor device status, sensor readings, dan actuator status
- Auto-refresh untuk data terbaru

### **Log Monitoring**
- Web interface menampilkan 10 log terakhir
- Filter berdasarkan actuator type
- Export capability untuk analisis

## 🎯 NEXT STEPS

1. **Deploy to Production Server**
2. **Configure ESP32** dengan endpoint yang baru
3. **Setup Monitoring** untuk production
4. **Add More Sensors** sesuai kebutuhan
5. **Implement Alerts** untuk threshold violations

---

## ✅ SYSTEM STATUS

Sistem Terraponix sekarang fully functional dengan:
- ✅ Real-time actuator control
- ✅ Complete history tracking
- ✅ ESP32 integration ready
- ✅ Auto-refresh web interface
- ✅ Comprehensive API endpoints
- ✅ Professional error handling

**Happy farming! 🌱🚀**