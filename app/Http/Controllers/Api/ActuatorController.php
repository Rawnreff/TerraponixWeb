<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorStatus;
use App\Models\Device;
use Illuminate\Http\Request;

class ActuatorController extends Controller
{
    public function status($deviceId = null)
    {
        $deviceId = $deviceId ?? 1; // Default to device ID 1
        
        $status = ActuatorStatus::where('device_id', $deviceId)->first();
        
        // Create default status if not exists
        if (!$status) {
            $status = ActuatorStatus::create([
                'device_id' => $deviceId,
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
    }
    
    public function control(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'sometimes|exists:devices,id',
            'actuator' => 'required|in:curtain,fan,pump,water_pump',
            'action' => 'required|in:on,off,toggle,set',
            'value' => 'sometimes|numeric|min:0|max:100'
        ]);
        
        $deviceId = $validated['device_id'] ?? 1;
        
        $actuator = ActuatorStatus::where('device_id', $deviceId)->first();
        
        // Create if not exists
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
    }
}