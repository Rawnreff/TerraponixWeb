<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('actuator_statuses', function (Blueprint $table) {
            $table->boolean('auto_mode')->default(true)->after('water_pump_status');
        });
    }

    public function down()
    {
        Schema::table('actuator_statuses', function (Blueprint $table) {
            $table->dropColumn('auto_mode');
        });
    }
};