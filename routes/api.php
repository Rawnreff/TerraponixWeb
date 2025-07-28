<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\ActuatorController;
use App\Http\Controllers\DashboardController;

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
    Route::get('/sensor-data/history', [SensorController::class, 'history']);
    Route::patch('/sensor-data/{device}', [SensorController::class, 'update']);

    
    // Actuator control endpoints
    Route::get('/devices/{device}/actuator-status', [ActuatorController::class, 'status']);
    Route::post('/actuator/control', [ActuatorController::class, 'control']);
    Route::get('/devices/{device}/actuator-logs', [ActuatorController::class, 'logs']);
    Route::post('/actuator/auto-mode', [ActuatorController::class, 'toggleAutoMode']);
});

// Web frontend compatibility routes
Route::get('/devices', [DeviceController::class, 'index']);
Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
Route::get('/actuator-status', [DashboardController::class, 'getActuatorStatus']);
Route::post('/control-actuator', [ActuatorController::class, 'control']); // Bridge route for web frontend

Route::prefix('dashboard')->group(function () {
    Route::get('/latest-sensor-data', [DashboardController::class, 'getLatestSensorData']);
    Route::get('/sensor-history', [DashboardController::class, 'getSensorHistory']);
    Route::get('/actuator-status', [DashboardController::class, 'getActuatorStatus']);
    Route::get('/device-status', [DashboardController::class, 'getDeviceStatus']);
    Route::get('/summary', [DashboardController::class, 'getDashboardSummary']);
    Route::get('/export', [DashboardController::class, 'exportData']);
});

// Alternative routes (untuk kompatibilitas dengan frontend yang sudah ada)
Route::get('/latest-sensor-data', [DashboardController::class, 'getLatestSensorData']);
Route::get('/sensor-history', [DashboardController::class, 'getSensorHistory']);