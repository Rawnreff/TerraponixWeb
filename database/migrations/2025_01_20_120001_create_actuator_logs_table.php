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
            $table->string('actuator_type', 20)->default('curtain');
            $table->string('action', 100); // 'Set to 50%', 'Turned ON', 'Turned OFF'
            $table->string('value', 50); // '50', 'true', 'false'
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
            
            $table->index(['device_id', 'timestamp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('actuator_logs');
    }
};