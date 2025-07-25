#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <ESP32Servo.h>

// ================== PIN DEFINITIONS ==================
#define DHTPIN 25
#define DHTTYPE DHT11
#define PH_PIN 34
#define LDR_PIN 35
#define SERVO_PIN 13
#define WATER_LEVEL_PIN 32
#define FAN_PIN 14
#define PUMP_PIN 27

// ================== WIFI & SERVER CONFIG ==================
const char* ssid = "HELIOS-16";
const char* password = "jougan9902";

// Ganti IP ini dengan IP lokal dari server Laravel (bukan localhost)
const char* serverUrl = "http://192.168.137.1:8000/api/v1";
const int deviceId = 1; // ID perangkat sesuai database Laravel

// ================ OBJECT & VARIABLE SETUP ==================
DHT dht(DHTPIN, DHTTYPE);
Servo servo;

float pH_offset = 0.0;
unsigned long lastSensorUpdate = 0;
const long sensorUpdateInterval = 10000;
unsigned long lastActuatorCheck = 0;
const long actuatorCheckInterval = 5000;

// ================== SETUP ==================
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

  Serial.println("\nSistem TERRAPONIX Dimulai!");
  Serial.println("==========================");
}

// ================== LOOP ==================
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
  }

  if (millis() - lastSensorUpdate >= sensorUpdateInterval) {
    lastSensorUpdate = millis();

    float temp = dht.readTemperature();
    float hum = dht.readHumidity();
    float pH = read_pH();
    int ldr = analogRead(LDR_PIN);
    int water_level = analogRead(WATER_LEVEL_PIN);

    sendSensorData(temp, hum, pH, ldr, water_level);
    printSensorData(temp, hum, pH, ldr, water_level);
  }

  if (millis() - lastActuatorCheck >= actuatorCheckInterval) {
    lastActuatorCheck = millis();
    checkActuatorStatus();
  }
}

// ================== WIFI FUNCTION ==================
void connectToWiFi() {
  Serial.print("Menghubungkan ke WiFi");
  WiFi.begin(ssid, password);
  int attempt = 0;

  while (WiFi.status() != WL_CONNECTED && attempt < 20) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Terhubung!");
    Serial.print("IP ESP32: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nGagal konek WiFi.");
  }
}

// ================== SEND SENSOR DATA ==================
void sendSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = String(serverUrl) + "/sensor-data";
  http.begin(url);
  http.addHeader("Content-Type", "application/json");

  DynamicJsonDocument doc(512);
  doc["device_id"] = deviceId;
  doc["temperature"] = temp;
  doc["humidity"] = hum;
  doc["ph_value"] = pH;
  doc["light_intensity"] = ldr;
  doc["water_level"] = water_level;

  String payload;
  serializeJson(doc, payload);

  int httpCode = http.POST(payload);
  if (httpCode == HTTP_CODE_OK) {
    Serial.println("Data sensor berhasil dikirim.");
    Serial.println("Respon: " + http.getString());
  } else {
    Serial.printf("Gagal kirim data. Kode: %d\n", httpCode);
  }

  http.end();
}

// ================== GET ACTUATOR STATUS ==================
void checkActuatorStatus() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = String(serverUrl) + "/devices/" + String(deviceId) + "/actuator-status";
  http.begin(url);

  int httpCode = http.GET();
  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    DynamicJsonDocument doc(512);
    deserializeJson(doc, payload);

    int curtainPos = doc["data"]["curtain_position"];
    bool fanStatus = doc["data"]["fan_status"];
    bool pumpStatus = doc["data"]["water_pump_status"];

    servo.write(map(curtainPos, 0, 100, 0, 180));
    digitalWrite(FAN_PIN, fanStatus ? HIGH : LOW);
    digitalWrite(PUMP_PIN, pumpStatus ? HIGH : LOW);

    Serial.println("Status aktuator diperbarui:");
    Serial.println("Tirai: " + String(curtainPos) + "%");
    Serial.println("Kipas: " + String(fanStatus ? "ON" : "OFF"));
    Serial.println("Pompa: " + String(pumpStatus ? "ON" : "OFF"));
  } else {
    Serial.printf("Gagal mengambil status aktuator. Kode: %d\n", httpCode);
  }

  http.end();
}

// ================== BACA SENSOR pH ==================
float read_pH() {
  int raw = analogRead(PH_PIN);
  float voltage = raw * (3.3 / 4095.0);
  return 7.0 - ((voltage - pH_offset) / 0.18);
}

// ================== PRINT SENSOR ==================
void printSensorData(float temp, float hum, float pH, int ldr, int water_level) {
  Serial.println("=== DATA SENSOR ===");
  Serial.print("Suhu: "); Serial.print(temp); Serial.println(" °C");
  Serial.print("Kelembapan: "); Serial.print(hum); Serial.println(" %");
  Serial.print("pH Air: "); Serial.print(pH, 2); Serial.println(" (0-14)");
  Serial.print("Intensitas Cahaya (LDR): "); Serial.println(ldr);
  Serial.print("Tinggi Air: ");
  if (water_level < 1500) {
    Serial.println("RENDAH");
  } else {
    Serial.println("CUKUP");
  }
  Serial.print("Nilai Analog Water Level: "); Serial.println(water_level);
  Serial.println("===================");
}