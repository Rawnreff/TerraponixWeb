<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ActuatorController;

Route::prefix('v1')->group(function() {
    // Device endpoints
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices/register', [DeviceController::class, 'register']);
    
    // Sensor data endpoints
    Route::post('/sensor-data', [SensorController::class, 'store']);
    Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
    Route::get('/sensor-data/history', [SensorController::class, 'history']);
    
    // Actuator control endpoints
    Route::get('/actuator-status', [ActuatorController::class, 'status']);
    Route::post('/actuator/control', [ActuatorController::class, 'control']);
    
    // Settings endpoints
    Route::get('/settings', [DeviceController::class, 'getSettings']);
    Route::post('/settings/update', [DeviceController::class, 'updateSettings']);
});