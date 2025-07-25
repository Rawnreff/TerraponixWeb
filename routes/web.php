<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\SettingsController;

Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sensor-data', [SensorDataController::class, 'index'])->name('sensor.data');
    Route::get('/actuator-control', [ActuatorController::class, 'index'])->name('actuator.control');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    
    // API untuk frontend
    Route::prefix('api')->group(function() {
        Route::get('/latest-sensor-data', [SensorDataController::class, 'getLatestData']);
        Route::get('/actuator-status', [ActuatorController::class, 'getStatus']);
        Route::post('/control-actuator', [ActuatorController::class, 'control']);
        Route::get('/sensor-history', [SensorDataController::class, 'getHistory']);
        Route::get('/settings', [SettingsController::class, 'getSettings']);
        Route::post('/settings', [SettingsController::class, 'updateSettings']);
    });
});