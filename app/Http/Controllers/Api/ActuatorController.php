<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
        
        $oldValue = null;
        $newValue = $validated['value'];
        
        switch($validated['actuator_type']) {
            case 'curtain':
                $oldValue = $actuator->curtain_position;
                $actuator->curtain_position = $newValue;
                break;
            case 'fan':
                $oldValue = $actuator->fan_status;
                $actuator->fan_status = (bool)$newValue;
                break;
            case 'water_pump':
                $oldValue = $actuator->water_pump_status;
                $actuator->water_pump_status = (bool)$newValue;
                break;
        }
        
        $actuator->last_updated = now();
        $actuator->save();
        
        // Log the actuator change
        Log::info('Actuator controlled', [
            'device_id' => $validated['device_id'],
            'actuator_type' => $validated['actuator_type'],
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'timestamp' => now()
        ]);
        
        // Cache the updated status for real-time access
        $this->cacheActuatorStatus($actuator);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Actuator controlled successfully',
            'data' => $actuator
        ]);
    }
    
    public function bulkControl(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'curtain_position' => 'nullable|integer|min:0|max:100',
            'fan_status' => 'nullable|boolean',
            'water_pump_status' => 'nullable|boolean'
        ]);
        
        $actuator = ActuatorStatus::where('device_id', $validated['device_id'])->firstOrFail();
        
        $changes = [];
        
        if (isset($validated['curtain_position'])) {
            $changes['curtain'] = [
                'old' => $actuator->curtain_position,
                'new' => $validated['curtain_position']
            ];
            $actuator->curtain_position = $validated['curtain_position'];
        }
        
        if (isset($validated['fan_status'])) {
            $changes['fan'] = [
                'old' => $actuator->fan_status,
                'new' => $validated['fan_status']
            ];
            $actuator->fan_status = $validated['fan_status'];
        }
        
        if (isset($validated['water_pump_status'])) {
            $changes['water_pump'] = [
                'old' => $actuator->water_pump_status,
                'new' => $validated['water_pump_status']
            ];
            $actuator->water_pump_status = $validated['water_pump_status'];
        }
        
        $actuator->last_updated = now();
        $actuator->save();
        
        // Log all changes
        if (!empty($changes)) {
            Log::info('Bulk actuator control', [
                'device_id' => $validated['device_id'],
                'changes' => $changes,
                'timestamp' => now()
            ]);
        }
        
        // Cache the updated status
        $this->cacheActuatorStatus($actuator);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Actuators controlled successfully',
            'data' => $actuator,
            'changes' => $changes
        ]);
    }
    
    public function getRealtimeStatus($deviceId)
    {
        // Try to get from cache first
        $cachedStatus = Cache::get("actuator_status_{$deviceId}");
        
        if (!$cachedStatus) {
            $cachedStatus = ActuatorStatus::where('device_id', $deviceId)->first();
            if ($cachedStatus) {
                $this->cacheActuatorStatus($cachedStatus);
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $cachedStatus,
            'timestamp' => now()->toISOString()
        ]);
    }
    
    public function emergencyStop($deviceId)
    {
        $actuator = ActuatorStatus::where('device_id', $deviceId)->firstOrFail();
        
        // Emergency stop - turn off all actuators
        $actuator->curtain_position = 0; // Close curtain
        $actuator->fan_status = false; // Turn off fan
        $actuator->water_pump_status = false; // Turn off pump
        $actuator->last_updated = now();
        $actuator->save();
        
        Log::warning('Emergency stop activated', [
            'device_id' => $deviceId,
            'timestamp' => now()
        ]);
        
        $this->cacheActuatorStatus($actuator);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Emergency stop activated - all actuators turned off',
            'data' => $actuator
        ]);
    }
    
    private function cacheActuatorStatus($actuator)
    {
        Cache::put("actuator_status_{$actuator->device_id}", $actuator, now()->addMinutes(5));
    }
}