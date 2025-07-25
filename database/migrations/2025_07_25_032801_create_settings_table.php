<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->float('temp_threshold')->default(29.0);
            $table->integer('light_threshold')->default(2200);
            $table->integer('water_level_threshold')->default(1500);
            $table->float('ph_min')->default(5.5);
            $table->float('ph_max')->default(6.5);
            $table->boolean('auto_mode')->default(true);
            $table->timestamps(); // Tambahkan ini
            
            $table->index('device_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};