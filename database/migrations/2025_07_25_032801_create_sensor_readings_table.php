<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->float('ph_value')->nullable();
            $table->integer('light_intensity')->nullable();
            $table->integer('water_level')->nullable();
            $table->integer('co2_level')->nullable();
            $table->integer('soil_moisture')->nullable();
            $table->timestamps();
            
            $table->index('device_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_readings');
    }
};