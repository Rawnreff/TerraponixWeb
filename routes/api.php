<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\ActuatorController;

Route::prefix('v1')->group(function() {
    // Device endpoints
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices/{device}/settings', [DeviceController::class, 'getSettings']);
    Route::post('/devices/{device}/settings', [DeviceController::class, 'updateSettings']);
    
    // Sensor data endpoints
    Route::post('/sensor-data', [SensorController::class, 'store']);
    Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
    Route::get('/sensor-data/history', [SensorController::class, 'history']);
    Route::get('/sensor-data/stats', [SensorController::class, 'stats']);
    
    // Actuator control endpoints
    Route::get('/devices/{device}/actuator-status', [ActuatorController::class, 'status']);
    Route::get('/actuator-status', function() {
        return app(ActuatorController::class)->status(1);
    });
    Route::post('/actuator/control', [ActuatorController::class, 'control']);
});