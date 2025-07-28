#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <ESP32Servo.h>
#include <Wire.h>
#include <BH1750.h>

// Pin Definitions
#define DHTPIN 25
#define DHTTYPE DHT22
#define PH_PIN 34
#define MQ135_PIN 33
#define SERVO_PIN 13
#define WATER_LEVEL_PIN 32
#define SOIL_MOISTURE_PIN 35
#define FAN_PIN 14
#define PUMP_PIN 27 

// I2C Configuration
#define I2C_SDA 21  
#define I2C_SCL 22

// WiFi Credentials
const char* ssid = "hotspotkeren";
const char* password = "87654321";

// API Configuration
const char* serverUrl = "http://192.168.137.36:8000/api/v1";
const char* apiKey = "";

// Data Management
const int deviceId = 1;
bool hasPosted = false;
int patchCount = 0;
const int maxPatchesBeforePost = 12;

// Timing
unsigned long lastSensorUpdate = 0;
const long sensorUpdateInterval = 5000;
unsigned long lastActuatorCheck = 0;
const long actuatorCheckInterval = 2000;

DHT dht(DHTPIN, DHTTYPE);
Servo servo;
BH1750 lightMeter;

void setup() {
  Serial.begin(115200);
  
  // Initialize hardware
  pinMode(FAN_PIN, OUTPUT);
  pinMode(PUMP_PIN, OUTPUT);
  digitalWrite(FAN_PIN, LOW);
  digitalWrite(PUMP_PIN, LOW);
  
  dht.begin();
  servo.attach(SERVO_PIN);
  servo.write(90);
  
  // Initialize I2C
  Wire.begin(I2C_SDA, I2C_SCL);
  if (!lightMeter.begin()) {
    Serial.println("Failed to initialize BH1750!");
  }

  connectToWiFi();
  
  Serial.println("\n🌱 Terraponix System Ready");
}

void loop() { 
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
  }

  unsigned long currentMillis = millis();

  // Sensor reading and sending
  if (currentMillis - lastSensorUpdate >= sensorUpdateInterval) {
    lastSensorUpdate = currentMillis;
    
    float temp = dht.readTemperature();
    float hum = dht.readHumidity();
    float lux = lightMeter.readLightLevel();
    float pH = read_pH();
    int waterLevel = analogRead(WATER_LEVEL_PIN);
    int co2 = analogRead(MQ135_PIN);
    int soilMoisture = analogRead(SOIL_MOISTURE_PIN);
    
    if (isnan(temp) || isnan(hum)) {
      temp = 0.0;
      hum = 0.0;
    }
    
    sendSensorData(temp, hum, pH, lux, waterLevel, co2, soilMoisture);
    printSensorData(temp, hum, pH, lux, waterLevel, co2, soilMoisture);
  }

  // Actuator control
  if (currentMillis - lastActuatorCheck >= actuatorCheckInterval) {
    lastActuatorCheck = currentMillis;
    checkActuatorStatus();
  }
}

void sendSensorData(float temp, float hum, float pH, float lux, int waterLevel, int co2, int soilMoisture) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("No WiFi connection");
    return;
  }

  HTTPClient http;
  String url;
  bool shouldPost = (!hasPosted) || (patchCount >= maxPatchesBeforePost);

  if (shouldPost) {
    url = String(serverUrl) + "/sensor-data";
    Serial.println("📤 Sending POST request (New Data)");
  } else {
    url = String(serverUrl) + "/sensor-data/" + String(deviceId);
    Serial.println("🔄 Sending PATCH request (Update)");
  }

  // Create JSON payload
  DynamicJsonDocument doc(512);
  doc["device_id"] = deviceId;
  doc["temperature"] = temp;
  doc["humidity"] = hum;
  doc["ph_value"] = pH;
  doc["light_intensity"] = round(lux);
  doc["water_level"] = waterLevel;
  doc["co2_level"] = co2;
  doc["soil_moisture"] = soilMoisture;

  String payload;
  serializeJson(doc, payload);

  // Configure HTTP
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(10000);

  // Send request
  int httpCode;
  if (shouldPost) {
    httpCode = http.POST(payload);
  } else {
    httpCode = http.sendRequest("PATCH", payload);
  }

  // Handle response
  if (httpCode > 0) {
    String response = http.getString();
    Serial.printf("HTTP Code: %d\n", httpCode);
    Serial.println("Response: " + response);
    
    // Auto-fallback to POST if PATCH fails with 404
    if (!shouldPost && httpCode == HTTP_CODE_NOT_FOUND) {
      Serial.println("⚠ No existing data found. Falling back to POST...");
      sendSensorData(temp, hum, pH, lux, waterLevel, co2, soilMoisture); // Recursively call with POST
      return;
    }
    
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED) {
      if (shouldPost) {
        hasPosted = true;
        patchCount = 0;
      } else {
        patchCount++;
      }
    }
  } else {
    Serial.printf("Error: %s\n", http.errorToString(httpCode).c_str());
  }

  http.end();
}

// [Other functions remain unchanged...]

void connectToWiFi() {
  Serial.println("Connecting to WiFi...");
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n❌ WiFi Failed");
  }
}

void checkActuatorStatus() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = String(serverUrl) + "/devices/" + String(deviceId) + "/actuator-status";
  
  http.begin(url);
  http.setTimeout(5000);

  int httpCode = http.GET();
  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    DynamicJsonDocument doc(128);
    deserializeJson(doc, payload);

    int curtainPos = doc["data"]["curtain_position"];
    bool fanStatus = doc["data"]["fan_status"];
    bool pumpStatus = doc["data"]["water_pump_status"];

    servo.write(map(curtainPos, 0, 100, 0, 180));
    digitalWrite(FAN_PIN, fanStatus ? HIGH : LOW);
    digitalWrite(PUMP_PIN, pumpStatus ? HIGH : LOW);

    Serial.println("🎛 Actuator Update:");
    Serial.printf("  Curtain: %d%%\n", curtainPos);
    Serial.printf("  Fan: %s\n", fanStatus ? "ON" : "OFF");
    Serial.printf("  Pump: %s\n", pumpStatus ? "ON" : "OFF");
  }
  
  http.end();
}

float read_pH() {
  int raw = analogRead(PH_PIN);
  float voltage = raw * (3.3 / 4095.0);
  float pH = 7 + ((2.5 - voltage) * (7.0 / 2.5));
  return constrain(pH, 0, 14);
}

void printSensorData(float temp, float hum, float pH, float lux, int waterLevel, int co2, int soilMoisture) {
  Serial.println("\n📊 Sensor Data:");
  Serial.printf("🌡 Temp: %.1f°C\n", temp);
  Serial.printf("💧 Hum: %.1f%%\n", hum);
  Serial.printf("⚗ pH: %.2f\n", pH);
  Serial.printf("☀ Light: %.1f lux\n", lux);
  Serial.printf("💦 Water: %d\n", waterLevel);
  Serial.printf("🌬 CO2: %d\n", co2);
  Serial.printf("🌱 Soil: %d\n", soilMoisture);
  Serial.println("====================");
}