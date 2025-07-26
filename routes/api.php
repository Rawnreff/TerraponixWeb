<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\ActuatorController;

// Health check endpoint
Route::get('/test', [SensorController::class, 'healthCheck']);

// ESP32 endpoints (simple routes for ESP32 compatibility)
Route::post('/sensor-data', [SensorController::class, 'store']);
Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
Route::get('/actuator-commands', [ActuatorController::class, 'getCommands']);
Route::post('/actuator/control', [ActuatorController::class, 'control']);

// Real-time streaming endpoint
Route::get('/sensor-stream', [SensorController::class, 'stream']);

Route::prefix('v1')->group(function() {
    // Device management endpoints
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::get('/devices/{device}/settings', [DeviceController::class, 'getSettings']);
    Route::post('/devices/{device}/settings', [DeviceController::class, 'updateSettings']);
    
    // Sensor data endpoints
    Route::post('/sensor-data', [SensorController::class, 'store']);
    Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
    Route::get('/sensor-data/history', [SensorController::class, 'history']);
    Route::get('/sensor-data/statistics', [SensorController::class, 'statistics']);
    Route::get('/sensor-stream', [SensorController::class, 'stream']);
    
    // Actuator control endpoints
    Route::get('/devices/{device}/actuator-status', [ActuatorController::class, 'status']);
    Route::get('/actuator-status', function(Request $request) {
        return app(ActuatorController::class)->status($request->get('device_id'));
    });
    Route::post('/actuator/control', [ActuatorController::class, 'control']);
    Route::get('/actuator/commands', [ActuatorController::class, 'getCommands']);
    Route::post('/actuator/auto-control', [ActuatorController::class, 'autoControl']);
});

// Additional endpoints for backward compatibility
Route::get('/devices', [DeviceController::class, 'index']);
Route::get('/latest-sensor-data', [SensorController::class, 'latest']);
Route::get('/actuator-status', function(Request $request) {
    return app(ActuatorController::class)->status($request->get('device_id'));
});