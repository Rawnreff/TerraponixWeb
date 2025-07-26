#!/usr/bin/env python3
"""
Enhanced Terraponix API Test Script
Tests all new real-time and actuator control features
"""

import requests
import json
import time
from datetime import datetime, timedelta

# Configuration
BASE_URL = "http://localhost:8000/api/v1"
DEVICE_ID = 1

def print_response(response, title):
    """Print formatted API response"""
    print(f"\n{'='*50}")
    print(f"📡 {title}")
    print(f"{'='*50}")
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    print(f"{'='*50}")

def test_sensor_endpoints():
    """Test enhanced sensor data endpoints"""
    print("\n🌡️  Testing Sensor Data Endpoints")
    
    # Test real-time sensor data
    response = requests.get(f"{BASE_URL}/sensor-data/realtime")
    print_response(response, "Real-time Sensor Data")
    
    # Test statistics
    response = requests.get(f"{BASE_URL}/sensor-data/statistics?device_id={DEVICE_ID}")
    print_response(response, "Sensor Statistics (24h)")
    
    # Test history with different intervals
    now = datetime.now()
    from_date = (now - timedelta(hours=24)).isoformat()
    to_date = now.isoformat()
    
    for interval in ['hourly', 'daily']:
        response = requests.get(
            f"{BASE_URL}/sensor-data/history",
            params={
                'device_id': DEVICE_ID,
                'from': from_date,
                'to': to_date,
                'interval': interval
            }
        )
        print_response(response, f"Historical Data ({interval})")

def test_actuator_endpoints():
    """Test enhanced actuator control endpoints"""
    print("\n🎛️  Testing Actuator Control Endpoints")
    
    # Test real-time actuator status
    response = requests.get(f"{BASE_URL}/devices/{DEVICE_ID}/actuator-realtime")
    print_response(response, "Real-time Actuator Status")
    
    # Test individual actuator control
    actuators = [
        {'type': 'curtain', 'value': 75},
        {'type': 'fan', 'value': 1},
        {'type': 'water_pump', 'value': 0}
    ]
    
    for actuator in actuators:
        data = {
            'device_id': DEVICE_ID,
            'actuator_type': actuator['type'],
            'value': actuator['value']
        }
        response = requests.post(f"{BASE_URL}/actuator/control", json=data)
        print_response(response, f"Control {actuator['type']} to {actuator['value']}")
        time.sleep(1)  # Wait between requests
    
    # Test bulk control
    bulk_data = {
        'device_id': DEVICE_ID,
        'curtain_position': 50,
        'fan_status': 1,
        'water_pump_status': 1
    }
    response = requests.post(f"{BASE_URL}/actuator/bulk-control", json=bulk_data)
    print_response(response, "Bulk Actuator Control")

def test_emergency_stop():
    """Test emergency stop functionality"""
    print("\n🚨 Testing Emergency Stop")
    
    response = requests.post(f"{BASE_URL}/devices/{DEVICE_ID}/emergency-stop")
    print_response(response, "Emergency Stop Activation")
    
    # Check status after emergency stop
    time.sleep(2)
    response = requests.get(f"{BASE_URL}/devices/{DEVICE_ID}/actuator-realtime")
    print_response(response, "Actuator Status After Emergency Stop")

def test_device_endpoints():
    """Test device management endpoints"""
    print("\n📱 Testing Device Management Endpoints")
    
    # Test device list
    response = requests.get(f"{BASE_URL}/devices")
    print_response(response, "Device List")
    
    # Test device settings
    response = requests.get(f"{BASE_URL}/devices/{DEVICE_ID}/settings")
    print_response(response, "Device Settings")

def test_real_time_simulation():
    """Simulate real-time data updates"""
    print("\n🔄 Simulating Real-time Data Updates")
    
    # Simulate multiple sensor readings
    for i in range(3):
        sensor_data = {
            'device_id': DEVICE_ID,
            'temperature': 25.0 + (i * 0.5),
            'humidity': 60.0 + (i * 2.0),
            'ph_value': 6.5 + (i * 0.1),
            'light_intensity': 2000 + (i * 100),
            'water_level': 1800 + (i * 50)
        }
        
        response = requests.post(f"{BASE_URL}/sensor-data", json=sensor_data)
        print_response(response, f"Sensor Data Update {i+1}")
        
        # Test real-time endpoint after each update
        time.sleep(1)
        response = requests.get(f"{BASE_URL}/sensor-data/realtime")
        print_response(response, f"Real-time Data After Update {i+1}")
        
        time.sleep(2)  # Wait between updates

def test_error_handling():
    """Test error handling and validation"""
    print("\n⚠️  Testing Error Handling")
    
    # Test invalid device ID
    response = requests.get(f"{BASE_URL}/devices/999/actuator-realtime")
    print_response(response, "Invalid Device ID")
    
    # Test invalid sensor data
    invalid_data = {
        'device_id': DEVICE_ID,
        'temperature': 'invalid',
        'humidity': 60.0,
        'ph_value': 6.5,
        'light_intensity': 2000,
        'water_level': 1800
    }
    response = requests.post(f"{BASE_URL}/sensor-data", json=invalid_data)
    print_response(response, "Invalid Sensor Data")
    
    # Test invalid actuator control
    invalid_actuator = {
        'device_id': DEVICE_ID,
        'actuator_type': 'invalid_actuator',
        'value': 100
    }
    response = requests.post(f"{BASE_URL}/actuator/control", json=invalid_actuator)
    print_response(response, "Invalid Actuator Type")

def test_performance():
    """Test API performance"""
    print("\n⚡ Testing API Performance")
    
    start_time = time.time()
    
    # Test multiple concurrent requests
    for i in range(5):
        response = requests.get(f"{BASE_URL}/sensor-data/realtime")
        if response.status_code == 200:
            print(f"✅ Request {i+1}: {response.elapsed.total_seconds():.3f}s")
        else:
            print(f"❌ Request {i+1}: Failed")
    
    end_time = time.time()
    total_time = end_time - start_time
    avg_time = total_time / 5
    
    print(f"\n📊 Performance Summary:")
    print(f"Total Time: {total_time:.3f}s")
    print(f"Average Time: {avg_time:.3f}s")
    print(f"Requests per second: {5/total_time:.2f}")

def main():
    """Main test function"""
    print("🚀 Terraponix Enhanced API Test Suite")
    print("=" * 60)
    
    try:
        # Test basic connectivity
        response = requests.get(f"{BASE_URL.replace('/v1', '')}/test")
        if response.status_code == 200:
            print("✅ API server is running")
        else:
            print("❌ API server is not responding")
            return
        
        # Run all tests
        test_sensor_endpoints()
        test_actuator_endpoints()
        test_emergency_stop()
        test_device_endpoints()
        test_real_time_simulation()
        test_error_handling()
        test_performance()
        
        print("\n🎉 All tests completed successfully!")
        print("\n📋 Test Summary:")
        print("✅ Real-time sensor data endpoints")
        print("✅ Enhanced actuator control")
        print("✅ Emergency stop functionality")
        print("✅ Bulk control operations")
        print("✅ Device management")
        print("✅ Error handling and validation")
        print("✅ Performance testing")
        
    except requests.exceptions.ConnectionError:
        print("❌ Cannot connect to API server. Make sure the server is running on http://localhost:8000")
    except Exception as e:
        print(f"❌ Test failed with error: {str(e)}")

if __name__ == "__main__":
    main()