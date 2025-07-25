<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        // Asumsi device pertama sudah dibuat oleh DeviceSeeder
        Setting::create([
            'device_id' => 1,
            'temp_threshold' => 29.0,
            'light_threshold' => 2200,
            'water_level_threshold' => 1500,
            'ph_min' => 5.5,
            'ph_max' => 6.5,
            'auto_mode' => true
        ]);
    }
}