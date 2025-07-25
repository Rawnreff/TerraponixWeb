<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::with(['sensorReadings', 'actuatorStatus', 'settings'])->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $devices
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip'
        ]);

        $device = Device::create($validated);

        // Buat record terkait
        $device->actuatorStatus()->create([
            'curtain_position' => 90,
            'fan_status' => false,
            'water_pump_status' => false
        ]);

        $device->settings()->create([
            'temp_threshold' => 29.0,
            'light_threshold' => 2200,
            'water_level_threshold' => 1500,
            'ph_min' => 5.5,
            'ph_max' => 6.5,
            'auto_mode' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Device registered successfully',
            'data' => $device->load(['actuatorStatus', 'settings'])
        ], 201);
    }

    public function getSettings($deviceId)
    {
        $device = Device::with('settings')->findOrFail($deviceId);

        return response()->json([
            'status' => 'success',
            'data' => $device->settings
        ]);
    }

    public function updateSettings(Request $request, $deviceId)
    {
        $validated = $request->validate([
            'temp_threshold' => 'numeric',
            'light_threshold' => 'integer',
            'water_level_threshold' => 'integer',
            'ph_min' => 'numeric',
            'ph_max' => 'numeric',
            'auto_mode' => 'boolean'
        ]);

        $device = Device::findOrFail($deviceId);
        $device->settings()->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully',
            'data' => $device->settings
        ]);
    }
}