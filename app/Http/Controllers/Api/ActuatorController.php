<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorStatus;
use App\Models\Device;
use Illuminate\Http\Request;

class ActuatorController extends Controller
{
    /**
     * Get actuator status for specific device
     */
    public function status($deviceId = null)
    {
        $deviceId = $deviceId ?? 1; // Default to device 1
        
        $status = ActuatorStatus::where('device_id', $deviceId)->first();
        
        if (!$status) {
            // Create default status if not exists
            $device = Device::findOrFail($deviceId);
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
            'data' => [
                'curtain_position' => $status->curtain_position,
                'fan_status' => $status->fan_status,
                'water_pump_status' => $status->water_pump_status,
                'last_updated' => $status->last_updated,
                // Additional status for frontend
                'fan' => $status->fan_status ? 'on' : 'off',
                'pump' => $status->water_pump_status ? 'on' : 'off',
                'curtain' => $status->curtain_position
            ],
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Control actuators from frontend or ESP32
     */
    public function control(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'nullable|exists:devices,id',
            'actuator' => 'required|in:fan,pump,water_pump,curtain',
            'action' => 'required|in:on,off,toggle,set',
            'value' => 'nullable|numeric|between:0,100'
        ]);
        
        $deviceId = $validated['device_id'] ?? 1;
        $actuator = $validated['actuator'];
        $action = $validated['action'];
        $value = $validated['value'];
        
        // Get or create actuator status
        $actuatorStatus = ActuatorStatus::firstOrCreate(
            ['device_id' => $deviceId],
            [
                'curtain_position' => 50,
                'fan_status' => false,
                'water_pump_status' => false,
                'last_updated' => now()
            ]
        );
        
        // Process control command
        $result = $this->processActuatorControl($actuatorStatus, $actuator, $action, $value);
        
        // Update timestamp
        $actuatorStatus->last_updated = now();
        $actuatorStatus->save();
        
        // Log the action
        \Log::info('Actuator control command', [
            'device_id' => $deviceId,
            'actuator' => $actuator,
            'action' => $action,
            'value' => $value,
            'result' => $result,
            'ip' => $request->ip()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => "Successfully {$action} {$actuator}",
            'data' => $actuatorStatus->fresh(),
            'command' => [
                'actuator' => $actuator,
                'action' => $action,
                'value' => $value,
                'result' => $result
            ],
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Process the actuator control logic
     */
    private function processActuatorControl($actuatorStatus, $actuator, $action, $value)
    {
        switch ($actuator) {
            case 'fan':
                return $this->controlFan($actuatorStatus, $action);
                
            case 'pump':
            case 'water_pump':
                return $this->controlPump($actuatorStatus, $action);
                
            case 'curtain':
                return $this->controlCurtain($actuatorStatus, $action, $value);
                
            default:
                throw new \InvalidArgumentException("Unknown actuator: {$actuator}");
        }
    }
    
    /**
     * Control fan (on/off/toggle)
     */
    private function controlFan($actuatorStatus, $action)
    {
        switch ($action) {
            case 'on':
                $actuatorStatus->fan_status = true;
                return 'Fan turned ON';
                
            case 'off':
                $actuatorStatus->fan_status = false;
                return 'Fan turned OFF';
                
            case 'toggle':
                $actuatorStatus->fan_status = !$actuatorStatus->fan_status;
                return $actuatorStatus->fan_status ? 'Fan turned ON' : 'Fan turned OFF';
                
            default:
                throw new \InvalidArgumentException("Invalid fan action: {$action}");
        }
    }
    
    /**
     * Control water pump (on/off/toggle)
     */
    private function controlPump($actuatorStatus, $action)
    {
        switch ($action) {
            case 'on':
                $actuatorStatus->water_pump_status = true;
                return 'Water pump turned ON';
                
            case 'off':
                $actuatorStatus->water_pump_status = false;
                return 'Water pump turned OFF';
                
            case 'toggle':
                $actuatorStatus->water_pump_status = !$actuatorStatus->water_pump_status;
                return $actuatorStatus->water_pump_status ? 'Water pump turned ON' : 'Water pump turned OFF';
                
            default:
                throw new \InvalidArgumentException("Invalid pump action: {$action}");
        }
    }
    
    /**
     * Control curtain position (set position 0-100)
     */
    private function controlCurtain($actuatorStatus, $action, $value)
    {
        switch ($action) {
            case 'set':
                if ($value === null) {
                    throw new \InvalidArgumentException("Value required for curtain set action");
                }
                $position = max(0, min(100, $value)); // Clamp between 0-100
                $actuatorStatus->curtain_position = $position;
                return "Curtain set to {$position}%";
                
            case 'on':
                $actuatorStatus->curtain_position = 100; // Fully open
                return 'Curtain fully opened';
                
            case 'off':
                $actuatorStatus->curtain_position = 0; // Fully closed
                return 'Curtain fully closed';
                
            case 'toggle':
                $actuatorStatus->curtain_position = $actuatorStatus->curtain_position > 50 ? 0 : 100;
                return $actuatorStatus->curtain_position > 50 ? 'Curtain opened' : 'Curtain closed';
                
            default:
                throw new \InvalidArgumentException("Invalid curtain action: {$action}");
        }
    }
    
    /**
     * Get control commands for ESP32 to poll
     */
    public function getCommands($deviceId = null)
    {
        $deviceId = $deviceId ?? 1;
        
        $status = ActuatorStatus::where('device_id', $deviceId)->first();
        
        if (!$status) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'fan' => false,
                    'pump' => false,
                    'curtain' => 50
                ],
                'timestamp' => now()->toISOString()
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'fan' => $status->fan_status,
                'pump' => $status->water_pump_status,
                'curtain' => $status->curtain_position
            ],
            'timestamp' => now()->toISOString(),
            'last_updated' => $status->last_updated
        ]);
    }
    
    /**
     * Auto control based on sensor readings (can be called by cron job)
     */
    public function autoControl(Request $request)
    {
        $deviceId = $request->get('device_id', 1);
        
        // Get latest sensor data
        $latestReading = \App\Models\SensorReading::where('device_id', $deviceId)
            ->latest()
            ->first();
            
        if (!$latestReading) {
            return response()->json([
                'status' => 'error',
                'message' => 'No sensor data available for auto control'
            ]);
        }
        
        // Get device settings
        $settings = \App\Models\Setting::where('device_id', $deviceId)->first();
        
        if (!$settings || !$settings->auto_mode) {
            return response()->json([
                'status' => 'success',
                'message' => 'Auto mode disabled'
            ]);
        }
        
        $actions = [];
        
        // Temperature control (fan)
        if ($latestReading->temperature > $settings->temp_threshold) {
            $this->control(new Request([
                'device_id' => $deviceId,
                'actuator' => 'fan',
                'action' => 'on'
            ]));
            $actions[] = 'Fan turned ON (temp: ' . $latestReading->temperature . '°C)';
        } elseif ($latestReading->temperature < ($settings->temp_threshold - 2)) {
            $this->control(new Request([
                'device_id' => $deviceId,
                'actuator' => 'fan',
                'action' => 'off'
            ]));
            $actions[] = 'Fan turned OFF (temp: ' . $latestReading->temperature . '°C)';
        }
        
        // Water level control (pump)
        if ($latestReading->water_level < $settings->water_level_threshold) {
            $this->control(new Request([
                'device_id' => $deviceId,
                'actuator' => 'pump',
                'action' => 'on'
            ]));
            $actions[] = 'Water pump turned ON (level: ' . $latestReading->water_level . ')';
        }
        
        // Light control (curtain)
        if ($latestReading->light_intensity > $settings->light_threshold) {
            $curtainPosition = max(20, 100 - (($latestReading->light_intensity - $settings->light_threshold) / 10));
            $this->control(new Request([
                'device_id' => $deviceId,
                'actuator' => 'curtain',
                'action' => 'set',
                'value' => $curtainPosition
            ]));
            $actions[] = "Curtain adjusted to {$curtainPosition}% (light: {$latestReading->light_intensity})";
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Auto control executed',
            'actions' => $actions,
            'sensor_data' => $latestReading,
            'timestamp' => now()->toISOString()
        ]);
    }
}