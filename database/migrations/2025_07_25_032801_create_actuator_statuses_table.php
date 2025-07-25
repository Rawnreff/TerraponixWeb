<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actuator_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->integer('curtain_position')->default(90)->comment('0-100%');
            $table->boolean('fan_status')->default(false)->comment('0=off, 1=on');
            $table->boolean('water_pump_status')->default(false);
            $table->datetime('last_updated')->useCurrent();
            $table->timestamps(); // untuk mencatat waktu pembuatan dan pembaruan
            
            $table->index('device_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('actuator_statuses');
    }
};