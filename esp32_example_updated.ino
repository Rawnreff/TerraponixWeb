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
#define CO2_PIN 36      // Pin CO2 sensor (Analog)
#define SOIL_PIN 39     // Pin Soil moisture sensor (Analog)

// WiFi Credentials - GANTI SESUAI WIFI/HOTSPOT ANDA
const char* ssid = "Your_WiFi_SSID";
const char* password = "Your_WiFi_Password";

// API Configuration - GANTI IP SESUAI IP SERVER ANDA
// Untuk mendapatkan IP server: jalankan 'hostname -I' di server Linux
const char* serverUrl = "http://192.168.1.100:8000/api";  // IP server + port Laravel
const char* apiKey = ""; // Kosongkan jika tidak ada API key

DHT dht(DHTPIN, DHTTYPE);
Servo servo;

// Variables for sensor readings
float temperature, humidity, ph_value;
int light_intensity, water_level, co2_level, soil_moisture;

// Timing variables
unsigned long lastSensorUpdate = 0;
unsigned long lastActuatorCheck = 0;
const long sensorUpdateInterval = 10000; // Kirim data setiap 10 detik
const long actuatorCheckInterval = 5000; // Cek kontrol setiap 5 detik

// Actuator states
bool fanStatus = false;
bool pumpStatus = false;
int curtainPosition = 50; // Posisi tirai 0-100%

// Connection status
bool wifiConnected = false;
bool serverConnected = false;

void setup() {
  Serial.begin(115200);
  Serial.println("🌱 Terraponix Smart Greenhouse Starting...");
  
  // Initialize pins
  pinMode(FAN_PIN, OUTPUT);
  pinMode(PUMP_PIN, OUTPUT);
  pinMode(LED_BUILTIN, OUTPUT);
  
  // Turn off actuators initially
  digitalWrite(FAN_PIN, LOW);
  digitalWrite(PUMP_PIN, LOW);
  digitalWrite(LED_BUILTIN, LOW);
  
  // Initialize sensors and servo
  dht.begin();
  servo.attach(SERVO_PIN);
  servo.write(curtainPosition); // Set initial position
  
  // Initialize WiFi
  setupWiFi();
  
  // Test server connection
  testServerConnection();
  
  Serial.println("✅ Setup completed successfully!");
  Serial.println("📡 Ready to monitor greenhouse...");
}

void loop() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi disconnected, reconnecting...");
    setupWiFi();
  }
  
  unsigned long currentTime = millis();
  
  // Read and send sensor data
  if (currentTime - lastSensorUpdate >= sensorUpdateInterval) {
    readSensors();
    sendSensorData();
    lastSensorUpdate = currentTime;
  }
  
  // Check for actuator commands
  if (currentTime - lastActuatorCheck >= actuatorCheckInterval) {
    checkActuatorCommands();
    lastActuatorCheck = currentTime;
  }
  
  // Status LED
  digitalWrite(LED_BUILTIN, serverConnected ? HIGH : LOW);
  
  delay(100); // Small delay to prevent watchdog timer issues
}

void setupWiFi() {
  WiFi.begin(ssid, password);
  Serial.print("🔌 Connecting to WiFi");
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifiConnected = true;
    Serial.println("\n✅ WiFi connected successfully!");
    Serial.print("📍 IP address: ");
    Serial.println(WiFi.localIP());
    Serial.print("📶 Signal strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    wifiConnected = false;
    Serial.println("\n❌ WiFi connection failed!");
    Serial.println("🔄 Will retry in next cycle...");
  }
}

void readSensors() {
  Serial.println("\n📊 Reading sensors...");
  
  // Read DHT11 (Temperature & Humidity)
  temperature = dht.readTemperature();
  humidity = dht.readHumidity();
  
  // Check if DHT reading failed
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("⚠️ Failed to read from DHT sensor!");
    temperature = 25.0; // Default value
    humidity = 60.0;    // Default value
  }
  
  // Read pH sensor (analog)
  int phRaw = analogRead(PH_PIN);
  ph_value = map(phRaw, 0, 4095, 0, 1400) / 100.0; // Convert to pH scale
  
  // Read light sensor (LDR)
  int ldrRaw = analogRead(LDR_PIN);
  light_intensity = map(ldrRaw, 0, 4095, 0, 1000);
  
  // Read water level sensor
  int waterRaw = analogRead(WATER_LEVEL_PIN);
  water_level = map(waterRaw, 0, 4095, 0, 800);
  
  // Read CO2 sensor (if available)
  int co2Raw = analogRead(CO2_PIN);
  co2_level = map(co2Raw, 0, 4095, 300, 1500); // PPM range
  
  // Read soil moisture sensor
  int soilRaw = analogRead(SOIL_PIN);
  soil_moisture = map(soilRaw, 0, 4095, 0, 100); // Percentage
  
  // Print sensor values
  Serial.printf("🌡️ Temperature: %.1f°C\n", temperature);
  Serial.printf("💧 Humidity: %.1f%%\n", humidity);
  Serial.printf("⚗️ pH: %.2f\n", ph_value);
  Serial.printf("☀️ Light: %d lux\n", light_intensity);
  Serial.printf("🚰 Water Level: %d\n", water_level);
  Serial.printf("🌬️ CO2: %d ppm\n", co2_level);
  Serial.printf("🌱 Soil Moisture: %d%%\n", soil_moisture);
}

void sendSensorData() {
  if (!wifiConnected) {
    Serial.println("⚠️ WiFi not connected, skipping data send");
    return;
  }
  
  Serial.println("📤 Sending sensor data to server...");
  
  HTTPClient http;
  String endpoint = String(serverUrl) + "/sensor-data";
  http.begin(endpoint);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<512> doc;
  doc["temperature"] = temperature;
  doc["humidity"] = humidity;
  doc["ph_value"] = ph_value;
  doc["light_intensity"] = light_intensity;
  doc["water_level"] = water_level;
  doc["co2_level"] = co2_level;
  doc["soil_moisture"] = soil_moisture;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("📦 Payload: " + jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.printf("✅ Server response (%d): %s\n", httpResponseCode, response.c_str());
    serverConnected = true;
    
    // Parse response to check for errors
    StaticJsonDocument<256> responseDoc;
    deserializeJson(responseDoc, response);
    
    if (responseDoc["status"] == "success") {
      Serial.println("✅ Data saved successfully!");
    } else {
      Serial.println("⚠️ Server reported error: " + String(responseDoc["message"].as<const char*>()));
    }
  } else {
    Serial.printf("❌ HTTP error: %d\n", httpResponseCode);
    serverConnected = false;
  }
  
  http.end();
}

void checkActuatorCommands() {
  if (!wifiConnected) {
    return;
  }
  
  HTTPClient http;
  String endpoint = String(serverUrl) + "/actuator-commands";
  http.begin(endpoint);
  
  int httpResponseCode = http.GET();
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    
    // Parse JSON response
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error && doc["status"] == "success") {
      JsonObject data = doc["data"];
      
      // Update fan status
      bool newFanStatus = data["fan"];
      if (newFanStatus != fanStatus) {
        fanStatus = newFanStatus;
        digitalWrite(FAN_PIN, fanStatus ? HIGH : LOW);
        Serial.printf("🌀 Fan %s\n", fanStatus ? "ON" : "OFF");
      }
      
      // Update pump status
      bool newPumpStatus = data["pump"];
      if (newPumpStatus != pumpStatus) {
        pumpStatus = newPumpStatus;
        digitalWrite(PUMP_PIN, pumpStatus ? HIGH : LOW);
        Serial.printf("💧 Pump %s\n", pumpStatus ? "ON" : "OFF");
      }
      
      // Update curtain position
      int newCurtainPosition = data["curtain"];
      if (newCurtainPosition != curtainPosition) {
        curtainPosition = newCurtainPosition;
        servo.write(map(curtainPosition, 0, 100, 0, 180));
        Serial.printf("🎭 Curtain position: %d%%\n", curtainPosition);
      }
      
      serverConnected = true;
    }
  } else {
    if (httpResponseCode != -1) { // Don't log timeout errors
      Serial.printf("❌ Command check error: %d\n", httpResponseCode);
    }
    serverConnected = false;
  }
  
  http.end();
}

void testServerConnection() {
  Serial.println("🔍 Testing server connection...");
  
  HTTPClient http;
  String endpoint = String(serverUrl) + "/test";
  http.begin(endpoint);
  
  int httpResponseCode = http.GET();
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.printf("✅ Server test successful (%d): %s\n", httpResponseCode, response.c_str());
    serverConnected = true;
  } else {
    Serial.printf("❌ Server test failed: %d\n", httpResponseCode);
    Serial.println("⚠️ Check server URL and network connection");
    serverConnected = false;
  }
  
  http.end();
}

// Function to handle manual control (for testing)
void manualControl(String actuator, String action) {
  HTTPClient http;
  String endpoint = String(serverUrl) + "/actuator/control";
  http.begin(endpoint);
  http.addHeader("Content-Type", "application/json");
  
  StaticJsonDocument<128> doc;
  doc["actuator"] = actuator;
  doc["action"] = action;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.printf("Manual control response: %s\n", response.c_str());
  }
  
  http.end();
}

// Function to print status (for debugging)
void printStatus() {
  Serial.println("\n📊 === SYSTEM STATUS ===");
  Serial.printf("🔌 WiFi: %s\n", wifiConnected ? "Connected" : "Disconnected");
  Serial.printf("📡 Server: %s\n", serverConnected ? "Connected" : "Disconnected");
  Serial.printf("🌀 Fan: %s\n", fanStatus ? "ON" : "OFF");
  Serial.printf("💧 Pump: %s\n", pumpStatus ? "ON" : "OFF");
  Serial.printf("🎭 Curtain: %d%%\n", curtainPosition);
  Serial.printf("⏰ Uptime: %lu seconds\n", millis() / 1000);
  Serial.println("========================\n");
}