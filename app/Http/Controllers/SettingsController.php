<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Device;
use App\Models\Setting;
use App\Models\ActuatorStatus;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }

    public function getSettings()
    {
        try {
            // Get settings directly from database for real-time data
            $setting = Setting::where('device_id', 1)->with('device')->first();
            
            if (!$setting) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Settings not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $setting
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDevices()
    {
        try {
            $devices = Device::with(['actuatorStatus', 'settings'])->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $devices
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get devices: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeviceInfo($deviceId = null)
    {
        try {
            $deviceId = $deviceId ?? 1;
            
            $device = Device::with(['actuatorStatus', 'settings', 'sensorReadings' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])->find($deviceId);
            
            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $device
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get device info: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'temp_threshold' => 'required|numeric',
            'light_threshold' => 'required|integer',
            'water_level_threshold' => 'required|integer',
            'ph_min' => 'required|numeric',
            'ph_max' => 'required|numeric',
            'auto_mode' => 'required|boolean'
        ]);

        $response = Http::post(url('/api/v1/devices/1/settings'), $validated);
        return $response->json();
    }
}   