#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <ESP32Servo.h>

// Pin Definitions
#define DHTPIN 25       // Pin DHT11
#define DHTTYPE DHT11
#define PH_PIN 34       // Pin pH sensor (Analog)
#define LDR_PIN 35      // Pin LDR (Analog)
#define SERVO_PIN 13    // Pin Servo
#define WATER_LEVEL_PIN 32  // Pin Water Level Sensor (Analog)
#define FAN_PIN 14      // Pin untuk kipas
#define PUMP_PIN 27     // Pin untuk pompa air

// WiFi Credentials - GANTI SESUAI WIFI/HOTSPOT ANDA
const char* ssid = "Xiaomi 14T Pro";
const char* password = "jougen92";

// API Configuration - GANTI IP SESUAI IP LAPTOP ANDA
// Cara mendapatkan IP: Windows=ipconfig, Linux/Mac=ifconfig
const char* serverUrl = "http://172.30.0.2:8000/api/v1";  // IP laptop + port Laravel
const char* apiKey = ""; // Kosongkan jika tidak ada API key

DHT dht(DHTPIN, DHTTYPE);
Servo servo;

// Variables
float pH_offset = 0.0;
unsigned long lastSensorUpdate = 0;
const long sensorUpdateInterval = 10000; // Update setiap 10 detik
unsigned long lastActuatorCheck = 0;
const long actuatorCheckInterval = 5000; // Cek setiap 5 detik

// Device ID - harus sesuai dengan ID di database Laravel
const int deviceId = 1;

void setup() {
  Serial.begin(115200);
  
  // Initialize pins
  pinMode(FAN_PIN, OUTPUT);
  pinMode(PUMP_PIN, OUTPUT);
  digitalWrite(FAN_PIN, LOW);
  digitalWrite(PUMP_PIN, LOW);
  
  // Initialize sensors and servo
  dht.begin();
  servo.attach(SERVO_PIN);
  servo.write(90);  // Posisi awal servo (90%)
  
  // Connect to WiFi
  connectToWiFi();
  
  Serial.println("\n🌱 Sistem TERRAPONIX Started! 🌱");
  Serial.println("==================================");
  Serial.println("Greenhouse IoT Monitoring System");
  Serial.println("==================================");
}

void loop() {
  // Maintain WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️  WiFi disconnected. Reconnecting...");
    connectToWiFi();
  }

  // Handle sensor data
  if (millis() - lastSensorUpdate >= sensorUpdateInterval) {
    lastSensorUpdate = millis();
    
    // Read all sensors
    float temp = dht.readTemperature();
    float hum = dht.readHumidity();
    int ldr_value = analogRead(LDR_PIN);
    float pH_value = read_pH();
    int water_level = analogRead(WATER_LEVEL_PIN);
    
    // Validate sensor readings
    if (isnan(temp) || isnan(hum)) {
      Serial.println("❌ Error: Failed to read from DHT sensor!");
      temp = 0.0;
      hum = 0.0;
    }
    
    // Send data to server
    sendSensorData(temp, hum, pH_value, ldr_value, water_level);
    
    // Print to serial for debugging
    printSensorData(temp, hum, pH_value, ldr_value, water_level);
  }

  // Check actuator status
  if (millis() - lastActuatorCheck >= actuatorCheckInterval) {
    lastActuatorCheck = millis();
    checkActuatorStatus();
  }
}

void connectToWiFi() {
  Serial.println("📶 Connecting to WiFi...");
  Serial.print("SSID: ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("📍 IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("📶 Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
    
    // Test API connection
    testAPIConnection();
  } else {
    Serial.println("\n❌ Failed to connect to WiFi!");
    Serial.println("🔧 Please check:");
    Serial.println("   - WiFi credentials");
    Serial.println("   - WiFi signal strength");
    Serial.println("   - Router settings");
  }
}

void testAPIConnection() {
  Serial.println("🔗 Testing API connection...");
  HTTPClient http;
  String url = String(serverUrl) + "/devices";
  
  http.begin(url);
  http.setTimeout(5000);
  
  int httpCode = http.GET();
  
  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK) {
      String response = http.getString();
      Serial.println("✅ API connection successful!");
      Serial.println("📊 Server response received");
    } else {
      Serial.printf("⚠️  API responded with code: %d\n", httpCode);
    }
  } else {
    Serial.println("❌ Failed to connect to API server");
    Serial.println("🔧 Please check:");
    Serial.println("   - Server IP address in code");
    Serial.println("   - Laravel server is running");
    Serial.println("   - Port 8000 is accessible");
    Serial.println("   - Firewall settings");
  }
  
  http.end();
}

void sendSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️  No WiFi connection - cannot send data");
    return;
  }

  HTTPClient http;
  String url = String(serverUrl) + "/sensor-data";
  
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(10000);
  
  // Create JSON payload
  DynamicJsonDocument doc(1024);
  doc["device_id"] = deviceId;
  doc["temperature"] = temp;
  doc["humidity"] = hum;
  doc["ph_value"] = pH;
  doc["light_intensity"] = ldr;
  doc["water_level"] = water_level;
  
  // Uncomment jika sensor sudah terpasang
  // doc["co2_level"] = readCO2();
  // doc["soil_moisture"] = readSoilMoisture();

  String payload;
  serializeJson(doc, payload);

  Serial.println("📤 Sending sensor data...");
  int httpCode = http.POST(payload);

  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK) {
      String response = http.getString();
      Serial.println("✅ Data sent successfully!");
      
      // Parse response for confirmation
      DynamicJsonDocument responseDoc(1024);
      deserializeJson(responseDoc, response);
      
      if (responseDoc["status"] == "success") {
        Serial.println("📊 Data saved to database");
      }
    } else {
      Serial.printf("⚠️  HTTP Error code: %d\n", httpCode);
      
      if (httpCode == 404) {
        Serial.println("🔧 Check API endpoint URL");
      } else if (httpCode == 422) {
        Serial.println("🔧 Data validation error - check sensor values");
      } else if (httpCode == 500) {
        Serial.println("🔧 Server error - check Laravel logs");
      }
    }
  } else {
    Serial.println("❌ Connection to server failed");
    Serial.println("🔧 Check network connection and server status");
  }

  http.end();
}

void checkActuatorStatus() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }

  HTTPClient http;
  String url = String(serverUrl) + "/devices/" + String(deviceId) + "/actuator-status";
  
  http.begin(url);
  http.setTimeout(5000);
  
  int httpCode = http.GET();

  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, payload);

    if (doc["status"] == "success") {
      // Update actuator status
      int curtainPos = doc["data"]["curtain_position"];
      bool fanStatus = doc["data"]["fan_status"];
      bool pumpStatus = doc["data"]["water_pump_status"];

      // Control actuators
      servo.write(map(curtainPos, 0, 100, 0, 180));
      digitalWrite(FAN_PIN, fanStatus ? HIGH : LOW);
      digitalWrite(PUMP_PIN, pumpStatus ? HIGH : LOW);

      Serial.println("🎛️  Actuator status updated:");
      Serial.println("   🎪 Curtain: " + String(curtainPos) + "%");
      Serial.println("   🌪️  Fan: " + String(fanStatus ? "ON" : "OFF"));
      Serial.println("   💧 Pump: " + String(pumpStatus ? "ON" : "OFF"));
    }
  } else if (httpCode > 0) {
    Serial.printf("⚠️  Actuator status error. Code: %d\n", httpCode);
  }

  http.end();
}

float read_pH() {
  int pH_raw = analogRead(PH_PIN);
  float voltage = pH_raw * (3.3 / 4095.0);
  float pH_value = 7.0 - ((voltage - 1.65 + pH_offset) / 0.18);
  
  // Validasi nilai pH (0-14)
  if (pH_value < 0) pH_value = 0;
  if (pH_value > 14) pH_value = 14;
  
  return pH_value;
}

void printSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  Serial.println("\n📊 === SENSOR DATA ===");
  
  // Temperature status
  Serial.print("🌡️  Temperature: ");
  Serial.print(temp, 1);
  Serial.print(" °C");
  if (temp > 30) Serial.print(" 🔥 HIGH");
  else if (temp < 20) Serial.print(" 🧊 LOW");
  else Serial.print(" ✅ OK");
  Serial.println();
  
  // Humidity status  
  Serial.print("💨 Humidity: ");
  Serial.print(hum, 1);
  Serial.print(" %");
  if (hum > 80) Serial.print(" 💧 HIGH");
  else if (hum < 40) Serial.print(" 🏜️  LOW");
  else Serial.print(" ✅ OK");
  Serial.println();
  
  // pH status
  Serial.print("⚗️  pH Level: ");
  Serial.print(pH, 2);
  if (pH >= 6.0 && pH <= 7.5) Serial.print(" ✅ OPTIMAL");
  else if (pH < 6.0) Serial.print(" 🔴 ACIDIC");
  else Serial.print(" 🔵 ALKALINE");
  Serial.println();
  
  // Light intensity
  Serial.print("☀️  Light: ");
  Serial.print(ldr);
  if (ldr > 3000) Serial.print(" 🌞 BRIGHT");
  else if (ldr < 1000) Serial.print(" 🌙 DARK");
  else Serial.print(" ✅ MODERATE");
  Serial.println();
  
  // Water level status
  Serial.print("💧 Water Level: ");
  Serial.print(water_level);
  if (water_level < 1500) {
    Serial.println(" 🚨 LOW - Pump needed!");
  } else {
    Serial.println(" ✅ SUFFICIENT");
  }
  
  // System status
  Serial.print("🎪 Curtain Position: ");
  int curtainPos = map(servo.read(), 0, 180, 0, 100);
  Serial.print(curtainPos);
  Serial.println("%");
  
  Serial.print("📶 WiFi: ");
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("✅ Connected (");
    Serial.print(WiFi.localIP());
    Serial.print(") - Signal: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("❌ Disconnected");
  }
  
  Serial.print("⏰ Uptime: ");
  unsigned long uptime = millis() / 1000;
  Serial.print(uptime / 3600);
  Serial.print("h ");
  Serial.print((uptime % 3600) / 60);
  Serial.print("m ");
  Serial.print(uptime % 60);
  Serial.println("s");
  
  Serial.println("==================");
}

// Optional: Functions for additional sensors (uncomment when available)
/*
int readCO2() {
  // CO2 sensor implementation
  return 400; // PPM
}

int readSoilMoisture() {
  // Soil moisture sensor implementation  
  return analogRead(SOIL_MOISTURE_PIN);
}
*/