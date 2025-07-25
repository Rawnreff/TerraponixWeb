#!/usr/bin/env python3
"""
TERRAPONIX API Testing Script
Script untuk testing semua endpoint API greenhouse monitoring system
"""

import requests
import json
import time
import random
from datetime import datetime

# Konfigurasi API
API_BASE_URL = "http://192.168.1.100:8000/api/v1"  # Ganti dengan IP laptop Anda
DEVICE_ID = 1

class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    END = '\033[0m'
    BOLD = '\033[1m'

def log_success(message):
    print(f"{Colors.GREEN}✅ {message}{Colors.END}")

def log_error(message):
    print(f"{Colors.RED}❌ {message}{Colors.END}")

def log_info(message):
    print(f"{Colors.BLUE}ℹ️  {message}{Colors.END}")

def log_warning(message):
    print(f"{Colors.YELLOW}⚠️  {message}{Colors.END}")

def test_connection():
    """Test basic connection to API"""
    print(f"\n{Colors.BOLD}🔗 Testing API Connection{Colors.END}")
    print("=" * 50)
    
    try:
        response = requests.get(f"{API_BASE_URL}/devices", timeout=5)
        if response.status_code == 200:
            log_success("API connection successful!")
            data = response.json()
            if data.get('status') == 'success':
                devices = data.get('data', [])
                log_info(f"Found {len(devices)} devices in database")
                for device in devices:
                    print(f"   📱 Device {device['id']}: {device['name']} ({device['location']})")
            return True
        else:
            log_error(f"API returned status code: {response.status_code}")
            return False
    except requests.exceptions.ConnectionError:
        log_error("Cannot connect to API server")
        log_warning("Make sure Laravel server is running")
        return False
    except Exception as e:
        log_error(f"Connection error: {str(e)}")
        return False

def generate_sensor_data():
    """Generate realistic sensor data"""
    return {
        "device_id": DEVICE_ID,
        "temperature": round(random.uniform(20.0, 35.0), 1),
        "humidity": round(random.uniform(40.0, 80.0), 1),
        "ph_value": round(random.uniform(5.5, 8.0), 2),
        "light_intensity": random.randint(500, 3500),
        "water_level": random.randint(1000, 2500),
        "co2_level": random.randint(300, 800),
        "soil_moisture": random.randint(30, 90)
    }

def test_send_sensor_data():
    """Test sending sensor data"""
    print(f"\n{Colors.BOLD}📤 Testing Sensor Data Submission{Colors.END}")
    print("=" * 50)
    
    sensor_data = generate_sensor_data()
    
    log_info("Sending sensor data:")
    for key, value in sensor_data.items():
        if key != "device_id":
            print(f"   {key}: {value}")
    
    try:
        response = requests.post(
            f"{API_BASE_URL}/sensor-data",
            json=sensor_data,
            headers={'Content-Type': 'application/json'},
            timeout=10
        )
        
        if response.status_code == 200:
            log_success("Sensor data sent successfully!")
            result = response.json()
            if result.get('status') == 'success':
                log_info("Data saved to database")
                return True
        else:
            log_error(f"Failed to send data. Status: {response.status_code}")
            try:
                error_info = response.json()
                print(f"   Error details: {error_info}")
            except:
                print(f"   Response: {response.text}")
            return False
            
    except Exception as e:
        log_error(f"Error sending sensor data: {str(e)}")
        return False

def test_get_latest_data():
    """Test getting latest sensor data"""
    print(f"\n{Colors.BOLD}📊 Testing Latest Sensor Data Retrieval{Colors.END}")
    print("=" * 50)
    
    try:
        response = requests.get(f"{API_BASE_URL}/sensor-data/latest", timeout=5)
        
        if response.status_code == 200:
            log_success("Latest data retrieved successfully!")
            data = response.json()
            
            if data.get('status') == 'success':
                readings = data.get('data', [])
                log_info(f"Retrieved {len(readings)} latest readings")
                
                for reading in readings[:3]:  # Show first 3
                    device_name = reading.get('device', {}).get('name', 'Unknown')
                    timestamp = reading.get('created_at', '')
                    print(f"   📱 {device_name}: {reading['temperature']}°C, {reading['humidity']}% - {timestamp}")
                
                return True
        else:
            log_error(f"Failed to get data. Status: {response.status_code}")
            return False
            
    except Exception as e:
        log_error(f"Error getting latest data: {str(e)}")
        return False

def test_actuator_status():
    """Test getting actuator status"""
    print(f"\n{Colors.BOLD}🎛️  Testing Actuator Status{Colors.END}")
    print("=" * 50)
    
    try:
        response = requests.get(f"{API_BASE_URL}/devices/{DEVICE_ID}/actuator-status", timeout=5)
        
        if response.status_code == 200:
            log_success("Actuator status retrieved successfully!")
            data = response.json()
            
            if data.get('status') == 'success':
                status = data.get('data', {})
                print(f"   🎪 Curtain Position: {status.get('curtain_position', 0)}%")
                print(f"   🌪️  Fan Status: {'ON' if status.get('fan_status') else 'OFF'}")
                print(f"   💧 Water Pump: {'ON' if status.get('water_pump_status') else 'OFF'}")
                print(f"   ⏰ Last Updated: {status.get('last_updated', 'N/A')}")
                return True
        else:
            log_error(f"Failed to get actuator status. Status: {response.status_code}")
            return False
            
    except Exception as e:
        log_error(f"Error getting actuator status: {str(e)}")
        return False

def test_actuator_control():
    """Test controlling actuators"""
    print(f"\n{Colors.BOLD}🎮 Testing Actuator Control{Colors.END}")
    print("=" * 50)
    
    tests = [
        {"actuator_type": "fan", "value": True, "description": "Turn ON fan"},
        {"actuator_type": "water_pump", "value": False, "description": "Turn OFF water pump"},
        {"actuator_type": "curtain", "value": 75, "description": "Set curtain to 75%"}
    ]
    
    for test in tests:
        log_info(f"Testing: {test['description']}")
        
        control_data = {
            "device_id": DEVICE_ID,
            "actuator_type": test["actuator_type"],
            "value": test["value"]
        }
        
        try:
            response = requests.post(
                f"{API_BASE_URL}/actuator/control",
                json=control_data,
                headers={'Content-Type': 'application/json'},
                timeout=10
            )
            
            if response.status_code == 200:
                log_success(f"✅ {test['description']} - SUCCESS")
            else:
                log_error(f"❌ {test['description']} - FAILED (Status: {response.status_code})")
                
        except Exception as e:
            log_error(f"❌ {test['description']} - ERROR: {str(e)}")
        
        time.sleep(1)  # Delay between tests

def test_device_settings():
    """Test getting and updating device settings"""
    print(f"\n{Colors.BOLD}⚙️  Testing Device Settings{Colors.END}")
    print("=" * 50)
    
    # Get current settings
    try:
        response = requests.get(f"{API_BASE_URL}/devices/{DEVICE_ID}/settings", timeout=5)
        
        if response.status_code == 200:
            log_success("Device settings retrieved successfully!")
            data = response.json()
            
            if data.get('status') == 'success':
                settings = data.get('data', {})
                print(f"   🌡️  Temperature Threshold: {settings.get('temp_threshold')}°C")
                print(f"   ☀️  Light Threshold: {settings.get('light_threshold')}")
                print(f"   💧 Water Level Threshold: {settings.get('water_level_threshold')}")
                print(f"   ⚗️  pH Range: {settings.get('ph_min')} - {settings.get('ph_max')}")
                print(f"   🤖 Auto Mode: {'ON' if settings.get('auto_mode') else 'OFF'}")
                
        # Test updating settings
        log_info("Testing settings update...")
        
        new_settings = {
            "temp_threshold": 29.5,
            "light_threshold": 2100,
            "water_level_threshold": 1600,
            "ph_min": 6.0,
            "ph_max": 7.0,
            "auto_mode": True
        }
        
        response = requests.post(
            f"{API_BASE_URL}/devices/{DEVICE_ID}/settings",
            json=new_settings,
            headers={'Content-Type': 'application/json'},
            timeout=10
        )
        
        if response.status_code == 200:
            log_success("Settings updated successfully!")
            return True
        else:
            log_error(f"Failed to update settings. Status: {response.status_code}")
            return False
            
    except Exception as e:
        log_error(f"Error with device settings: {str(e)}")
        return False

def run_continuous_test():
    """Run continuous sensor data simulation"""
    print(f"\n{Colors.BOLD}🔄 Running Continuous Test (Press Ctrl+C to stop){Colors.END}")
    print("=" * 50)
    
    count = 0
    try:
        while True:
            count += 1
            print(f"\n📊 Test #{count} - {datetime.now().strftime('%H:%M:%S')}")
            
            # Send sensor data
            if test_send_sensor_data():
                log_success(f"Data #{count} sent successfully")
            else:
                log_error(f"Data #{count} failed")
            
            # Wait before next test
            time.sleep(10)
            
    except KeyboardInterrupt:
        print(f"\n{Colors.YELLOW}⏹️  Continuous test stopped by user{Colors.END}")
        log_info(f"Total tests completed: {count}")

def main():
    print(f"""
{Colors.BOLD}🌱 TERRAPONIX API Testing Tool 🌱{Colors.END}
{'=' * 50}
This tool tests all API endpoints for the greenhouse monitoring system.

Configuration:
📍 API Base URL: {API_BASE_URL}
📱 Device ID: {DEVICE_ID}

""")
    
    # Test connection first
    if not test_connection():
        log_error("Cannot proceed with tests. Please fix connection issues first.")
        return
    
    # Menu
    while True:
        print(f"\n{Colors.BOLD}🧪 Available Tests:{Colors.END}")
        print("1. 📤 Test Sensor Data Submission")
        print("2. 📊 Test Latest Data Retrieval") 
        print("3. 🎛️  Test Actuator Status")
        print("4. 🎮 Test Actuator Control")
        print("5. ⚙️  Test Device Settings")
        print("6. 🔄 Run Continuous Test")
        print("7. 🧪 Run All Tests")
        print("0. ❌ Exit")
        
        choice = input(f"\n{Colors.BLUE}Select test (0-7): {Colors.END}").strip()
        
        if choice == "1":
            test_send_sensor_data()
        elif choice == "2":
            test_get_latest_data()
        elif choice == "3":
            test_actuator_status()
        elif choice == "4":
            test_actuator_control()
        elif choice == "5":
            test_device_settings()
        elif choice == "6":
            run_continuous_test()
        elif choice == "7":
            # Run all tests
            print(f"\n{Colors.BOLD}🧪 Running All Tests{Colors.END}")
            test_send_sensor_data()
            test_get_latest_data()
            test_actuator_status()
            test_actuator_control()
            test_device_settings()
            log_success("All tests completed!")
        elif choice == "0":
            log_info("Goodbye! 👋")
            break
        else:
            log_warning("Invalid choice. Please select 0-7.")

if __name__ == "__main__":
    main()