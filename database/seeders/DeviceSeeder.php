<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        // Buat device utama
        $device = Device::create([
            'name' => 'Greenhouse Utama',
            'location' => 'Kebun Percobaan',
            'ip_address' => '192.168.1.100',
            'status' => 'online',
            'last_seen' => now()
        ]);

        // Buat status aktuator default
        $device->actuatorStatus()->create([
            'curtain_position' => 90,
            'fan_status' => false,
            'water_pump_status' => false,
            'last_updated' => now()
        ]);

        // Buat beberapa data sensor contoh
        for ($i = 0; $i < 24; $i++) {
            $device->sensorReadings()->create([
                'temperature' => 25 + rand(-5, 5) + (rand(0, 10) / 10),
                'humidity' => 60 + rand(-20, 20),
                'ph_value' => 6.0 + (rand(-5, 5) / 10),
                'light_intensity' => 2000 + rand(-500, 1000),
                'water_level' => 1600 + rand(-200, 200),
                'co2_level' => 400 + rand(-50, 100),
                'soil_moisture' => 50 + rand(-20, 30),
                'created_at' => now()->subHours($i)
            ]);
        }
    }
}