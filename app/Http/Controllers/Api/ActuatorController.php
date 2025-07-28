<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorStatus;
use App\Models\ActuatorLog;
use App\Models\Device;
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
            'value' => 'required',
            'triggered_by' => 'string|in:manual,auto,esp32',
            'notes' => 'nullable|string'
        ]);
        
        $actuator = ActuatorStatus::where('device_id', $validated['device_id'])->firstOrFail();
        
        // Store old value for logging
        $oldValue = null;
        $newValue = $validated['value'];
        
        switch($validated['actuator_type']) {
            case 'curtain':
                $oldValue = $actuator->curtain_position;
                $actuator->curtain_position = $validated['value'];
                break;
            case 'fan':
                $oldValue = $actuator->fan_status;
                $actuator->fan_status = (bool)$validated['value'];
                $newValue = (bool)$validated['value'];
                break;
            case 'water_pump':
                $oldValue = $actuator->water_pump_status;
                $actuator->water_pump_status = (bool)$validated['value'];
                $newValue = (bool)$validated['value'];
                break;
        }
        
        $actuator->last_updated = now();
        $actuator->save();
        
        // Log the control action
        ActuatorLog::createLog(
            $validated['device_id'],
            $validated['actuator_type'],
            $oldValue,
            $newValue,
            $validated['triggered_by'] ?? 'manual',
            $validated['notes'] ?? null
        );
        
        // Update device last seen
        Device::where('id', $validated['device_id'])->update([
            'last_seen' => now(),
            'status' => 'online'
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Actuator controlled successfully',
            'data' => $actuator->fresh()
        ]);
    }

    public function history(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'actuator_type' => 'nullable|in:curtain,fan,water_pump',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $query = ActuatorLog::where('device_id', $validated['device_id'])
            ->with('device');

        if (isset($validated['actuator_type'])) {
            $query->where('actuator_type', $validated['actuator_type']);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->limit($validated['limit'] ?? 20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }
}