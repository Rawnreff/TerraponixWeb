<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\ActuatorStatus;
use App\Models\Setting;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default ESP32 device
        $device = Device::updateOrCreate(
            ['name' => 'ESP32 Default'],
            [
                'location' => 'Smart Greenhouse',
                'ip_address' => '192.168.1.100',
                'status' => 'online',
                'last_seen' => now()
            ]
        );

        // Create actuator status
        ActuatorStatus::updateOrCreate(
            ['device_id' => $device->id],
            [
                'curtain_position' => 75,
                'fan_status' => false,
                'water_pump_status' => false,
                'last_updated' => now()
            ]
        );

        // Create settings
        Setting::updateOrCreate(
            ['device_id' => $device->id],
            [
                'temp_threshold' => 28.0,
                'light_threshold' => 800,
                'water_level_threshold' => 300,
                'ph_min' => 6.0,
                'ph_max' => 7.5,
                'auto_mode' => true
            ]
        );

        // Generate demo sensor readings for the last 24 hours
        $this->generateDemoSensorData($device->id);

        $this->command->info('Demo data created successfully!');
        $this->command->info("Device ID: {$device->id}");
        $this->command->info("Device Name: {$device->name}");
    }

    private function generateDemoSensorData($deviceId)
    {
        $startTime = Carbon::now()->subDay();
        $endTime = Carbon::now();
        
        // Generate readings every 10 minutes for the last 24 hours
        $current = $startTime->copy();
        
        while ($current <= $endTime) {
            // Simulate realistic greenhouse sensor data
            $time_of_day = $current->hour + ($current->minute / 60);
            
            // Temperature: varies based on time of day (18-32°C)
            $baseTemp = 25 + 5 * sin(($time_of_day - 6) * pi() / 12);
            $temperature = $baseTemp + rand(-20, 20) / 10;
            
            // Humidity: inverse relationship with temperature (40-80%)
            $baseHumidity = 70 - ($temperature - 22) * 2;
            $humidity = max(30, min(90, $baseHumidity + rand(-10, 10)));
            
            // pH: stable around 6.5-7.0
            $ph_value = 6.5 + rand(-30, 50) / 100;
            
            // Light intensity: varies with time of day (0-1000)
            if ($time_of_day < 6 || $time_of_day > 18) {
                $light_intensity = rand(0, 50); // Night
            } else {
                $light_base = 500 + 400 * sin(($time_of_day - 6) * pi() / 12);
                $light_intensity = max(0, $light_base + rand(-200, 200));
            }
            
            // Water level: gradually decreases, replenished randomly
            $water_level = rand(200, 800);
            
            // CO2 level: varies (300-1200 ppm)
            $co2_level = 400 + rand(0, 800);
            
            // Soil moisture: correlated with water level (20-90%)
            $soil_moisture = min(90, max(20, ($water_level / 10) + rand(-20, 20)));
            
            SensorReading::create([
                'device_id' => $deviceId,
                'temperature' => round($temperature, 1),
                'humidity' => round($humidity, 1),
                'ph_value' => round($ph_value, 2),
                'light_intensity' => (int)$light_intensity,
                'water_level' => (int)$water_level,
                'co2_level' => (int)$co2_level,
                'soil_moisture' => (int)$soil_moisture,
                'created_at' => $current,
                'updated_at' => $current
            ]);
            
            $current->addMinutes(10);
        }
        
        $this->command->info('Generated ' . SensorReading::where('device_id', $deviceId)->count() . ' sensor readings');
    }
}