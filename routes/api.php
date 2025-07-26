<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\ActuatorController;

// Simple test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!', 'timestamp' => now()]);
});

Route::prefix('v1')->group(function() {
    // Device endpoints
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices/{device}/settings', [DeviceController::class, 'getSettings']);
    Route::post('/devices/{device}/settings', [DeviceController::class, 'updateSettings']);
    
    // Sensor data endpoints
    Route::post('/sensor-data', [SensorController::class, 'store']);
    Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
    Route::get('/sensor-data/realtime', [SensorController::class, 'realtime']);
    Route::get('/sensor-data/history', [SensorController::class, 'history']);
    Route::get('/sensor-data/statistics', [SensorController::class, 'statistics']);
    
    // Actuator control endpoints
    Route::get('/devices/{device}/actuator-status', [ActuatorController::class, 'status']);
    Route::get('/devices/{device}/actuator-realtime', [ActuatorController::class, 'getRealtimeStatus']);
    Route::post('/actuator/control', [ActuatorController::class, 'control']);
    Route::post('/actuator/bulk-control', [ActuatorController::class, 'bulkControl']);
    Route::post('/devices/{device}/emergency-stop', [ActuatorController::class, 'emergencyStop']);
});

// Additional simple endpoints without v1 prefix for testing
Route::get('/devices', [DeviceController::class, 'index']);
Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
Route::get('/sensor-data/realtime', [SensorController::class, 'realtime']);