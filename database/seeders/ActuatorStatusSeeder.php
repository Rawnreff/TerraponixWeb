<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActuatorStatus;
use App\Models\Device;

class ActuatorStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = Device::all();
        
        foreach ($devices as $device) {
            ActuatorStatus::updateOrCreate(
                ['device_id' => $device->id],
                [
                    'curtain_position' => 90,
                    'fan_status' => false,
                    'water_pump_status' => false,
                    'auto_mode' => true,
                    'last_updated' => now(),
                ]
            );
        }
    }
}