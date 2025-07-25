<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorStatus;
use Illuminate\Http\Request;

class ActuatorController extends Controller
{
    public function status($deviceId)
    {
        $status = ActuatorStatus::where('device_id', $deviceId)->firstOrFail();
        
        return response()->json([
            'status' => 'success',
            'data' => $status
        ]);
    }
    
    public function control(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'actuator_type' => 'required|in:curtain,fan,water_pump',
            'value' => 'required'
        ]);
        
        $actuator = ActuatorStatus::where('device_id', $validated['device_id'])->firstOrFail();
        
        switch($validated['actuator_type']) {
            case 'curtain':
                $actuator->curtain_position = $validated['value'];
                break;
            case 'fan':
                $actuator->fan_status = (bool)$validated['value'];
                break;
            case 'water_pump':
                $actuator->water_pump_status = (bool)$validated['value'];
                break;
        }
        
        $actuator->last_updated = now();
        $actuator->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Actuator controlled successfully',
            'data' => $actuator
        ]);
    }
}