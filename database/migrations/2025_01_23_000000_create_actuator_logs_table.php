<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actuator_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->enum('actuator_type', ['curtain', 'fan', 'water_pump']);
            $table->string('action'); // 'manual_control', 'auto_control'
            $table->json('old_value')->nullable(); // Store previous state
            $table->json('new_value'); // Store new state
            $table->string('triggered_by')->default('manual'); // 'manual', 'auto', 'esp32'
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'actuator_type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('actuator_logs');
    }
};