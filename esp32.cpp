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

// WiFi Credentials
const char* ssid = "Xiaomi 14T Pro";
const char* password = "jougen92";

// API Configuration
const char* serverUrl = "http://your-laravel-app.com/api/v1";
const char* apiKey = "your-api-key-if-any"; 

DHT dht(DHTPIN, DHTTYPE);
Servo servo;

// Variables
float pH_offset = 0.0;
unsigned long lastSensorUpdate = 0;
const long sensorUpdateInterval = 10000; // Update setiap 10 detik
unsigned long lastActuatorCheck = 0;
const long actuatorCheckInterval = 5000; // Cek setiap 5 detik

// Device ID - harus sesuai dengan ID di database
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
  servo.write(90);  // Posisi awal servo
  
  // Connect to WiFi
  connectToWiFi();
  
  Serial.println("\nSistem TERRAPONIX Started!");
  Serial.println("==========================");
}

void loop() {
  // Maintain WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
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
  Serial.println("\nMenghubungkan ke WiFi...");
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Terhubung!");
    Serial.print("Alamat IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nGagal terhubung ke WiFi!");
  }
}

void sendSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Tidak terhubung ke WiFi, tidak bisa mengirim data");
    return;
  }

  HTTPClient http;
  String url = String(serverUrl) + "/sensor-data";
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  DynamicJsonDocument doc(1024);
  doc["device_id"] = deviceId;
  doc["temperature"] = temp;
  doc["humidity"] = hum;
  doc["ph_value"] = pH;
  doc["light_intensity"] = ldr;
  doc["water_level"] = water_level;
  // doc["co2_level"] = 0;  // Uncomment jika sensor CO2 sudah terpasang
  // doc["soil_moisture"] = 0;  // Uncomment jika sensor kelembaban tanah sudah terpasang

  String payload;
  serializeJson(doc, payload);

  int httpCode = http.POST(payload);

  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK) {
      String response = http.getString();
      Serial.println("Response: " + response);
    } else {
      Serial.printf("HTTP Error code: %d\n", httpCode);
    }
  } else {
    Serial.println("Error on HTTP request");
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
  
  int httpCode = http.GET();

  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, payload);

    // Update actuator status
    int curtainPos = doc["data"]["curtain_position"];
    bool fanStatus = doc["data"]["fan_status"];
    bool pumpStatus = doc["data"]["water_pump_status"];

    servo.write(map(curtainPos, 0, 100, 0, 180));
    digitalWrite(FAN_PIN, fanStatus ? HIGH : LOW);
    digitalWrite(PUMP_PIN, pumpStatus ? HIGH : LOW);

    Serial.println("Updated actuator status:");
    Serial.println("Curtain: " + String(curtainPos) + "%");
    Serial.println("Fan: " + String(fanStatus ? "ON" : "OFF"));
    Serial.println("Pump: " + String(pumpStatus ? "ON" : "OFF"));
  } else {
    Serial.printf("Error getting actuator status. Code: %d\n", httpCode);
  }

  http.end();
}

float read_pH() {
  int pH_raw = analogRead(PH_PIN);
  float voltage = pH_raw * (3.3 / 4095.0);
  return 7.0 - ((voltage - pH_offset) / 0.18);
}

void printSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  Serial.println("=== DATA SENSOR ===");
  Serial.print("Suhu: "); Serial.print(temp); Serial.println(" °C");
  Serial.print("Kelembapan: "); Serial.print(hum); Serial.println(" %");
  Serial.print("pH Air: "); Serial.print(pH, 2); Serial.println(" (0-14)");
  Serial.print("Cahaya (LDR): "); Serial.println(ldr);
  
  // Water Level Information
  Serial.print("Level Air: ");
  if (water_level < 1500) { // Sesuaikan threshold jika perlu
    Serial.println("RENDAH");
  } else {
    Serial.println("CUKUP");
  }
  Serial.print("Nilai Tinggi Air Sensor: "); Serial.println(water_level);
  
  Serial.print("Status Tirai: ");
  Serial.println(servo.read() == 0 ? "Tertutup" : "Terbuka");
  Serial.print("Status WiFi: ");
  Serial.println(WiFi.status() == WL_CONNECTED ? "Terhubung" : "Terputus");
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("Alamat IP: ");
    Serial.println(WiFi.localIP());
  }
  Serial.println("===================");
}