<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DeviceSeeder::class,
            SettingSeeder::class,
        ]);
    }
}