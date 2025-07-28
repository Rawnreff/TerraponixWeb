<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorStatus;
use App\Models\ActuatorLog;
use Illuminate\Http\Request;

class ActuatorController extends Controller
{
    public function status($deviceId)
    {
        $status = ActuatorStatus::where('device_id', $deviceId)->first();
        
        if (!$status) {
            // Create default status if not exists
            $status = ActuatorStatus::create([
                'device_id' => $deviceId,
                'curtain_position' => 90,
                'fan_status' => false,
                'water_pump_status' => false,
                'auto_mode' => true,
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
            'device_id' => 'required|exists:devices,id',
            'actuator_type' => 'required|in:curtain,fan,water_pump',
            'value' => 'required'
        ]);
        
        $actuator = ActuatorStatus::where('device_id', $validated['device_id'])->first();
        
        if (!$actuator) {
            $actuator = ActuatorStatus::create([
                'device_id' => $validated['device_id'],
                'curtain_position' => 90,
                'fan_status' => false,
                'water_pump_status' => false,
                'auto_mode' => true,
                'last_updated' => now()
            ]);
        }
        
        // Prepare log action text
        $actionText = '';
        $valueText = (string)$validated['value'];
        
        switch($validated['actuator_type']) {
            case 'curtain':
                $actuator->curtain_position = $validated['value'];
                $actionText = 'Set to ' . $validated['value'] . '%';
                break;
            case 'fan':
                $actuator->fan_status = (bool)$validated['value'];
                $actionText = $validated['value'] ? 'Turned ON' : 'Turned OFF';
                $valueText = $validated['value'] ? 'true' : 'false';
                break;
            case 'water_pump':
                $actuator->water_pump_status = (bool)$validated['value'];
                $actionText = $validated['value'] ? 'Turned ON' : 'Turned OFF';
                $valueText = $validated['value'] ? 'true' : 'false';
                break;
        }
        
        $actuator->last_updated = now();
        $actuator->save();
        
        // Log the actuator action
        ActuatorLog::create([
            'device_id' => $validated['device_id'],
            'actuator_type' => $validated['actuator_type'],
            'action' => $actionText,
            'value' => $valueText,
            'timestamp' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Actuator controlled successfully',
            'data' => $actuator
        ]);
    }
    
    public function logs($deviceId)
    {
        $logs = ActuatorLog::where('device_id', $deviceId)
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }
    
    public function toggleAutoMode(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'auto_mode' => 'required|boolean'
        ]);
        
        $actuator = ActuatorStatus::where('device_id', $validated['device_id'])->first();
        
        if (!$actuator) {
            $actuator = ActuatorStatus::create([
                'device_id' => $validated['device_id'],
                'curtain_position' => 90,
                'fan_status' => false,
                'water_pump_status' => false,
                'auto_mode' => $validated['auto_mode'],
                'last_updated' => now()
            ]);
        } else {
            $actuator->auto_mode = $validated['auto_mode'];
            $actuator->last_updated = now();
            $actuator->save();
        }
        
        // Log the auto mode change
        ActuatorLog::create([
            'device_id' => $validated['device_id'],
            'actuator_type' => 'system',
            'action' => 'Auto mode ' . ($validated['auto_mode'] ? 'enabled' : 'disabled'),
            'value' => $validated['auto_mode'] ? 'true' : 'false',
            'timestamp' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Auto mode updated successfully',
            'data' => $actuator
        ]);
    }
}