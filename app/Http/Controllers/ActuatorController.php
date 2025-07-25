<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ActuatorStatus;

class ActuatorController extends Controller
{
    public function index()
    {
        return view('actuator-control');
    }

    public function getStatus()
    {
        try {
            $response = Http::get(url('/api/v1/devices/1/actuator-status'));
            
            if ($response->successful()) {
                return $response->json();
            }
            
            // Fallback to direct database query
            $status = ActuatorStatus::where('device_id', 1)->first();
            
            if (!$status) {
                $status = ActuatorStatus::create([
                    'device_id' => 1,
                    'curtain_position' => 50,
                    'fan_status' => false,
                    'water_pump_status' => false,
                    'last_updated' => now()
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch actuator status',
                'data' => [
                    'curtain_position' => 50,
                    'fan_status' => false,
                    'water_pump_status' => false
                ]
            ], 500);
        }
    }

    public function control(Request $request)
    {
        $validated = $request->validate([
            'actuator' => 'required|in:curtain,fan,pump,water_pump',
            'action' => 'required|in:on,off,toggle,set',
            'value' => 'sometimes|numeric|min:0|max:100'
        ]);

        try {
            $response = Http::post(url('/api/v1/actuator/control'), [
                'device_id' => 1,
                'actuator' => $validated['actuator'],
                'action' => $validated['action'],
                'value' => $validated['value'] ?? null
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            // Fallback to direct database update
            $deviceId = 1;
            $actuator = ActuatorStatus::where('device_id', $deviceId)->first();
            
            if (!$actuator) {
                $actuator = ActuatorStatus::create([
                    'device_id' => $deviceId,
                    'curtain_position' => 50,
                    'fan_status' => false,
                    'water_pump_status' => false,
                    'last_updated' => now()
                ]);
            }
            
            $actuatorType = $validated['actuator'];
            $action = $validated['action'];
            
            // Handle pump alias
            if ($actuatorType === 'pump') {
                $actuatorType = 'water_pump';
            }
            
            switch($actuatorType) {
                case 'curtain':
                    if ($action === 'set' && isset($validated['value'])) {
                        $actuator->curtain_position = $validated['value'];
                    } elseif ($action === 'on') {
                        $actuator->curtain_position = 100;
                    } elseif ($action === 'off') {
                        $actuator->curtain_position = 0;
                    } elseif ($action === 'toggle') {
                        $actuator->curtain_position = $actuator->curtain_position > 50 ? 0 : 100;
                    }
                    break;
                    
                case 'fan':
                    if ($action === 'on') {
                        $actuator->fan_status = true;
                    } elseif ($action === 'off') {
                        $actuator->fan_status = false;
                    } elseif ($action === 'toggle') {
                        $actuator->fan_status = !$actuator->fan_status;
                    }
                    break;
                    
                case 'water_pump':
                    if ($action === 'on') {
                        $actuator->water_pump_status = true;
                    } elseif ($action === 'off') {
                        $actuator->water_pump_status = false;
                    } elseif ($action === 'toggle') {
                        $actuator->water_pump_status = !$actuator->water_pump_status;
                    }
                    break;
            }
            
            $actuator->last_updated = now();
            $actuator->save();
            
            return response()->json([
                'status' => 'success',
                'message' => ucfirst($actuatorType) . ' controlled successfully',
                'data' => $actuator
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to control actuator'
            ], 500);
        }
    }
}