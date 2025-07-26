# 🌱 Terraponix - Advanced Greenhouse IoT System

**Real-time monitoring and intelligent control system for modern greenhouse automation**

## 🚀 Features

### ✨ Real-time Data Management
- **Automatic sensor data storage** from ESP32 devices
- **Real-time data broadcasting** with caching system
- **Live dashboard updates** every 5-10 seconds
- **Historical data analysis** with advanced filtering

### 🎛️ Advanced Actuator Control
- **Real-time actuator status monitoring**
- **Bulk control operations** for multiple actuators
- **Preset configurations** (Day Mode, Night Mode, Watering Mode, Eco Mode)
- **Emergency stop functionality**
- **Manual and automatic control modes**

### 📊 Modern Analytics Dashboard
- **Interactive real-time charts** using Chart.js
- **Multi-sensor visualization** with trend analysis
- **Statistical overview** (min, max, average values)
- **System health monitoring** with status indicators
- **Responsive design** for all devices

### 🔧 Enhanced API System
- **RESTful API endpoints** for all operations
- **Real-time data endpoints** for live updates
- **Bulk control endpoints** for efficient operations
- **Statistics and analytics endpoints**
- **Comprehensive error handling and logging**

## 🏗️ System Architecture

```
ESP32 Sensors → Laravel API → Real-time Frontend
     ↓              ↓              ↓
  Data Collection → Processing → Visualization
     ↓              ↓              ↓
  Actuator Control ← API ← User Interface
```

## 📋 Prerequisites

- PHP 8.0 or higher
- Laravel 9.x
- MySQL/PostgreSQL database
- ESP32 development board
- Required sensors (DHT11, pH sensor, LDR, water level sensor)
- Required actuators (servo motor, fan, water pump)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd terraponix
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Configuration
```bash
# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=terraponix
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations
```bash
php artisan migrate
php artisan db:seed
```

### 6. Start the Server
```bash
php artisan serve
```

## 🔌 ESP32 Setup

### Required Libraries
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <ESP32Servo.h>
```

### Configuration
Update the ESP32 code with your network and server details:

```cpp
// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// Server Configuration
const char* serverUrl = "http://YOUR_SERVER_IP:8000/api/v1";
const int deviceId = 1; // Must match database device ID
```

### Pin Configuration
```cpp
#define DHTPIN 25           // DHT11 sensor
#define PH_PIN 34          // pH sensor (Analog)
#define LDR_PIN 35         // Light sensor (Analog)
#define SERVO_PIN 13       // Curtain servo
#define WATER_LEVEL_PIN 32 // Water level sensor
#define FAN_PIN 14         // Fan control
#define PUMP_PIN 27        // Water pump control
```

## 📡 API Endpoints

### Sensor Data
- `POST /api/v1/sensor-data` - Store sensor readings
- `GET /api/v1/sensor-data/realtime` - Get latest sensor data
- `GET /api/v1/sensor-data/latest` - Get recent readings
- `GET /api/v1/sensor-data/history` - Get historical data
- `GET /api/v1/sensor-data/statistics` - Get statistical data

### Actuator Control
- `GET /api/v1/devices/{id}/actuator-status` - Get actuator status
- `GET /api/v1/devices/{id}/actuator-realtime` - Get real-time status
- `POST /api/v1/actuator/control` - Control individual actuator
- `POST /api/v1/actuator/bulk-control` - Control multiple actuators
- `POST /api/v1/devices/{id}/emergency-stop` - Emergency stop

### Device Management
- `GET /api/v1/devices` - List all devices
- `POST /api/v1/devices/register` - Register new device
- `GET /api/v1/devices/{id}/settings` - Get device settings
- `POST /api/v1/devices/{id}/settings` - Update device settings

## 🎨 Frontend Features

### Dashboard
- **Real-time sensor cards** with status indicators
- **Interactive actuator control** with visual feedback
- **Live charts** showing temperature and humidity trends
- **System overview** with health status
- **Emergency control** with confirmation dialogs

### Actuator Control Center
- **Individual actuator controls** with status display
- **Bulk control operations** for efficiency
- **Preset configurations** for common scenarios
- **Manual control** with range sliders and buttons
- **Activity logging** with timestamps

### Sensor Data Analytics
- **Real-time data visualization** with multiple chart types
- **Statistical analysis** with min/max/average values
- **Historical data browsing** with pagination
- **Search and filter** capabilities
- **Export functionality** (coming soon)

## 🔧 Configuration

### Auto-refresh Settings
- Dashboard: 5 seconds
- Actuator control: 3 seconds
- Sensor data: 10 seconds
- System status: 30 seconds

### Cache Settings
- Sensor data cache: 5 minutes
- Actuator status cache: 5 minutes
- Real-time data: Immediate updates

## 🚨 Emergency Features

### Emergency Stop
- **One-click emergency stop** for all actuators
- **Confirmation dialog** to prevent accidental activation
- **Immediate shutdown** of fans, pumps, and curtains
- **System logging** of emergency events

### System Monitoring
- **Real-time status monitoring** of all components
- **Automatic error detection** and alerting
- **Connection status** indicators
- **Performance metrics** tracking

## 📊 Data Management

### Real-time Storage
- **Automatic data persistence** from ESP32
- **Caching system** for performance optimization
- **Data validation** and error handling
- **Logging system** for debugging

### Historical Analysis
- **Time-series data** storage and retrieval
- **Statistical calculations** (min, max, average)
- **Data aggregation** by time intervals
- **Export capabilities** for external analysis

## 🔒 Security Features

- **CSRF protection** for all forms
- **Input validation** and sanitization
- **API rate limiting** (configurable)
- **Error logging** without sensitive data exposure

## 🚀 Performance Optimizations

- **Caching system** for frequently accessed data
- **Optimized database queries** with proper indexing
- **Minimal API calls** with efficient data structures
- **Responsive design** for optimal user experience

## 🐛 Troubleshooting

### Common Issues

1. **ESP32 Connection Issues**
   - Check WiFi credentials
   - Verify server IP address
   - Ensure Laravel server is running

2. **Data Not Updating**
   - Check auto-refresh settings
   - Verify API endpoints
   - Check browser console for errors

3. **Actuator Control Issues**
   - Verify device ID in database
   - Check actuator status endpoint
   - Ensure proper API authentication

### Debug Mode
Enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## 📈 Future Enhancements

- [ ] **WebSocket support** for real-time updates
- [ ] **Mobile app** development
- [ ] **Machine learning** for predictive control
- [ ] **Weather integration** for smart automation
- [ ] **Multi-device support** for larger greenhouses
- [ ] **Advanced analytics** and reporting
- [ ] **User authentication** and role management

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Check the documentation
- Review the troubleshooting guide

---

**🌱 Built with ❤️ for modern greenhouse automation**
