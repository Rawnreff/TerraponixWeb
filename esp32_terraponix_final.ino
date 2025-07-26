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
const char* ssid = "hotspotkeren";  
const char* password = "87654321";

// API Configuration - GANTI IP SESUAI IP LAPTOP ANDA
const char* serverUrl = "http://192.168.10.40:8000/api/v1";  
const char* apiKey = ""; 
bool hasPosted = false;


DHT dht(DHTPIN, DHTTYPE);
Servo servo;

// Variables
float pH_offset = 0.0;
unsigned long lastSensorUpdate = 0;
const long sensorUpdateInterval = 10000; 
unsigned long lastActuatorCheck = 0;
const long actuatorCheckInterval = 5000; 

const int deviceId = 1;

void setup() {
  Serial.begin(115200);
  
  pinMode(FAN_PIN, OUTPUT);
  pinMode(PUMP_PIN, OUTPUT);
  digitalWrite(FAN_PIN, LOW);
  digitalWrite(PUMP_PIN, LOW);
  
  dht.begin();
  servo.attach(SERVO_PIN);
  servo.write(90);  
  
  connectToWiFi();
  
  Serial.println("\n🌱 Sistem TERRAPONIX Started! 🌱");
  Serial.println("==================================");
  Serial.println("Greenhouse IoT Monitoring System");
  Serial.println("==================================");
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️  WiFi disconnected. Reconnecting...");
    connectToWiFi();
  }

  if (millis() - lastSensorUpdate >= sensorUpdateInterval) {
    lastSensorUpdate = millis();
    
    float temp = dht.readTemperature();
    float hum = dht.readHumidity();
    int ldr_value = analogRead(LDR_PIN);
    float pH_value = read_pH();
    int water_level = analogRead(WATER_LEVEL_PIN);
    
    if (isnan(temp) || isnan(hum)) {
      Serial.println("❌ Error: Failed to read from DHT sensor!");
      temp = 0.0;
      hum = 0.0;
    }
    
    sendSensorData(temp, hum, pH_value, ldr_value, water_level);
    printSensorData(temp, hum, pH_value, ldr_value, water_level);
  }

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
  String url;
  if (!hasPosted) {
    url = String(serverUrl) + "/sensor-data"; // First POST
  } else {
    url = String(serverUrl) + "/sensor-data/" + String(deviceId); // PATCH after
  }

  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(10000);

  DynamicJsonDocument doc(1024);
  doc["device_id"] = deviceId;
  doc["temperature"] = temp;
  doc["humidity"] = hum;
  doc["ph_value"] = pH;
  doc["light_intensity"] = ldr;
  doc["water_level"] = water_level;

  String payload;
  serializeJson(doc, payload);

  Serial.println("📤 Sending sensor data...");

  int httpCode;
  if (!hasPosted) {
    httpCode = http.POST(payload);
  } else {
    httpCode = http.PATCH(payload);
  }

  if (httpCode > 0) {
    String response = http.getString();
    Serial.printf("✅ Server response [%d]: %s\n", httpCode, response.c_str());

    if (httpCode == HTTP_CODE_OK || httpCode == 200) {
      hasPosted = true; // setelah POST sukses, jangan ulangi
      Serial.println("📊 Sensor data updated successfully");
    }
  } else {
    Serial.println("❌ Failed to connect to server");
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
      int curtainPos = doc["data"]["curtain_position"];
      bool fanStatus = doc["data"]["fan_status"];
      bool pumpStatus = doc["data"]["water_pump_status"];

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

// Fungsi konversi pH (2.5V = pH 7.0)
float read_pH() {
  int raw = analogRead(PH_PIN);
  float voltage = raw * (3.3 / 4095.0);

  float pH = 7 + ((2.5 - voltage) * (7.0 / 2.5));
  if (pH < 0) pH = 0;
  if (pH > 14) pH = 14;

  return pH;
}

void printSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  Serial.println("\n📊 === SENSOR DATA ===");
  
  Serial.print("🌡️  Temperature: ");
  Serial.print(temp, 1);
  Serial.print(" °C");
  if (temp > 30) Serial.print(" 🔥 HIGH");
  else if (temp < 20) Serial.print(" 🧊 LOW");
  else Serial.print(" ✅ OK");
  Serial.println();
  
  Serial.print("💨 Humidity: ");
  Serial.print(hum, 1);
  Serial.print(" %");
  if (hum > 80) Serial.print(" 💧 HIGH");
  else if (hum < 40) Serial.print(" 🏜️  LOW");
  else Serial.print(" ✅ OK");
  Serial.println();
  
  Serial.print("⚗️  pH Level: ");
  Serial.print(pH, 2);
  if (pH >= 6.0 && pH <= 7.5) Serial.print(" ✅ OPTIMAL");
  else if (pH < 6.0) Serial.print(" 🔴 ACIDIC");
  else Serial.print(" 🔵 ALKALINE");
  Serial.println();
  
  Serial.print("☀️  Light: ");
  Serial.print(ldr);
  if (ldr > 3000) Serial.print(" 🌞 BRIGHT");
  else if (ldr < 1000) Serial.print(" 🌙 DARK");
  else Serial.print(" ✅ MODERATE");
  Serial.println();
  
  Serial.print("💧 Water Level: ");
  Serial.print(water_level);
  if (water_level < 1500) {
    Serial.println(" 🚨 LOW - Pump needed!");
  } else {
    Serial.println(" ✅ SUFFICIENT");
  }
  
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
