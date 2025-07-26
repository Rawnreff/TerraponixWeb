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

    }
}