# Terraponix - Smart Greenhouse Monitoring System

![Terraponix Dashboard](https://img.shields.io/badge/Laravel-11.x-red) ![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.x-blue) ![ESP32](https://img.shields.io/badge/ESP32-Compatible-green)

## 🌟 Features

### 🎨 Modern UI/UX
- **Dark Mode Support** - Automatic system preference detection with manual toggle
- **Responsive Design** - Mobile-first approach using Tailwind CSS
- **Real-time Updates** - Live sensor data streaming without page refresh
- **Interactive Charts** - Beautiful charts with Chart.js integration
- **Intuitive Controls** - Modern toggle switches and buttons

### 🔄 Real-time Data System
- **Auto-save from ESP32** - Automatic sensor data collection
- **Server-Sent Events** - Real-time streaming to dashboard
- **Connection Monitoring** - Live connection status indicator
- **Data Validation** - Robust input validation and error handling

### 📊 Advanced Analytics
- **Historical Data** - Customizable time ranges and intervals
- **Statistics Dashboard** - Min/max/average calculations
- **Trend Analysis** - Visual representation of sensor trends
- **Export Functionality** - Data export capabilities

### 🤖 Smart Automation
- **Auto-control System** - Intelligent actuator control based on thresholds
- **Threshold Management** - Customizable sensor thresholds
- **Manual Override** - Direct control when needed
- **Scheduling** - Time-based automation rules

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL/PostgreSQL
- ESP32 with sensors

### Installation

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd terraponix
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed --class=DemoDataSeeder
   ```

5. **Build Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

6. **Start Server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the dashboard.

## 📡 ESP32 Integration

### API Endpoints for ESP32

#### Send Sensor Data (Auto-save)
```http
POST /api/sensor-data
Content-Type: application/json

{
    "temperature": 25.5,
    "humidity": 65.2,
    "ph_value": 6.8,
    "light_intensity": 750,
    "water_level": 450,
    "co2_level": 800,
    "soil_moisture": 75
}
```

#### Get Actuator Commands
```http
GET /api/actuator-commands?device_id=1
```

#### Send Control Commands
```http
POST /api/actuator/control
Content-Type: application/json

{
    "actuator": "fan",
    "action": "on"
}
```

### ESP32 Configuration

Update your ESP32 code with these settings:

```cpp
// WiFi Configuration
const char* ssid = "Your_WiFi_Name";
const char* password = "Your_WiFi_Password";

// API Configuration  
const char* serverUrl = "http://your-server-ip:8000/api";

// Device Configuration
const int deviceId = 1; // Use your device ID
```

## 🎛️ Dashboard Features

### Real-time Monitoring
- **Live Sensor Cards** - Color-coded status indicators
- **Connection Status** - Real-time connection monitoring
- **Last Update Time** - Timestamp of latest data
- **Visual Alerts** - Warning colors for out-of-range values

### Interactive Controls
- **Quick Actions** - One-click actuator controls
- **Toggle Switches** - Modern UI controls for fans/pumps
- **Slider Controls** - Position control for curtains
- **Manual Override** - Direct control capabilities

### Data Visualization
- **Real-time Charts** - Live updating temperature/humidity graphs
- **Historical Trends** - Customizable time range views
- **Statistics Panel** - Min/max/average displays
- **Export Options** - CSV/Excel data export

## 🔧 API Documentation

### Real-time Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/sensor-stream` | Server-Sent Events stream |
| GET | `/api/latest-sensor-data` | Latest sensor reading |
| POST | `/api/sensor-data` | Store new sensor data |
| GET | `/api/actuator-status` | Current actuator status |
| POST | `/api/actuator/control` | Control actuators |

### Data Formats

#### Sensor Data Response
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "device_id": 1,
        "temperature": 25.5,
        "humidity": 65.2,
        "ph_value": 6.8,
        "light_intensity": 750,
        "water_level": 450,
        "co2_level": 800,
        "soil_moisture": 75,
        "created_at": "2024-01-15T10:30:00Z"
    },
    "timestamp": "2024-01-15T10:30:00Z"
}
```

#### Actuator Control Request
```json
{
    "actuator": "fan|pump|curtain",
    "action": "on|off|toggle|set",
    "value": 50
}
```

## 🎨 UI Components

### Sensor Cards
- **Gradient Backgrounds** - Beautiful color schemes
- **Status Indicators** - Green/Yellow/Red status
- **Animated Updates** - Smooth value transitions
- **Responsive Layout** - Mobile-friendly design

### Navigation
- **Sidebar Menu** - Collapsible on mobile
- **Dark Mode Toggle** - System preference detection
- **Connection Status** - Live indicator
- **Current Time** - Always visible

### Charts
- **Real-time Updates** - Live data streaming
- **Interactive Legends** - Click to toggle datasets
- **Responsive Design** - Adapts to screen size
- **Smooth Animations** - Beautiful transitions

## ⚙️ Configuration

### Sensor Thresholds
Configure automatic control thresholds in Settings:

```php
// Temperature control
'temp_threshold' => 28.0,

// Light intensity
'light_threshold' => 800,

// Water level warning
'water_level_threshold' => 300,

// pH range
'ph_min' => 6.0,
'ph_max' => 7.5,

// Enable auto mode
'auto_mode' => true
```

### Real-time Settings
Adjust update intervals in `resources/js/app.js`:

```javascript
// Update frequency (milliseconds)
updateInterval: 5000, // 5 seconds

// Chart data points
maxDataPoints: 20,

// Connection check interval
connectionCheckInterval: 10000 // 10 seconds
```

## 🔐 Security Features

- **Input Validation** - Robust data validation
- **CSRF Protection** - Laravel CSRF tokens
- **Rate Limiting** - API rate limiting
- **CORS Support** - Cross-origin resource sharing
- **SQL Injection Protection** - Eloquent ORM protection

## 📱 Mobile Support

- **Responsive Design** - Works on all screen sizes
- **Touch-friendly** - Large buttons and controls
- **Mobile Navigation** - Hamburger menu
- **PWA Ready** - Progressive Web App capabilities

## 🚀 Performance

- **Real-time Streaming** - Efficient Server-Sent Events
- **Database Optimization** - Indexed queries
- **Asset Optimization** - Minified CSS/JS
- **Caching** - Laravel caching system
- **Lazy Loading** - Components load on demand

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage

# Frontend tests
npm run test
```

## 📦 Deployment

### Production Setup

1. **Environment Configuration**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Database Migration**
   ```bash
   php artisan migrate --force
   ```

3. **Asset Optimization**
   ```bash
   npm run build
   php artisan optimize
   ```

4. **Queue Processing**
   ```bash
   php artisan queue:work
   ```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

- **Documentation**: Check this README
- **Issues**: GitHub Issues
- **Email**: support@terraponix.com

## 🎯 Roadmap

- [ ] Mobile App (React Native)
- [ ] Weather API Integration
- [ ] Machine Learning Predictions
- [ ] Multi-user Support
- [ ] Cloud Deployment
- [ ] IoT Device Management
- [ ] Notification System
- [ ] Data Analytics Dashboard

---

**Made with ❤️ for Smart Agriculture**
