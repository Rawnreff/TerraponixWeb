<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use App\Models\Device;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'ph_value' => 'required|numeric',
            'light_intensity' => 'required|integer',
            'water_level' => 'required|integer',
            'co2_level' => 'nullable|integer',
            'soil_moisture' => 'nullable|integer',
        ]);
        
        $reading = SensorReading::create($validated);
        
        // Update device last seen
        $device = Device::find($validated['device_id']);
        if ($device) {
            $device->update(['last_seen' => now(), 'status' => 'online']);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Sensor data saved successfully',
            'data' => $reading
        ]);
    }
    
    public function latest()
    {
        $latestReadings = SensorReading::latest()
            ->with('device')
            ->take(10)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $latestReadings
        ]);
    }
    
    public function history(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'sometimes|exists:devices,id',
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after_or_equal:from',
            'interval' => 'sometimes|in:hourly,daily,weekly',
            'days' => 'sometimes|integer|min:1|max:30'
        ]);
        
        // Default values
        $deviceId = $validated['device_id'] ?? 1;
        $days = $validated['days'] ?? 1;
        $from = $validated['from'] ?? now()->subDays($days);
        $to = $validated['to'] ?? now();
        
        $query = SensorReading::where('device_id', $deviceId)
            ->whereBetween('created_at', [
                Carbon::parse($from),
                Carbon::parse($to)
            ])
            ->orderBy('created_at', 'desc');
            
        // Group by interval if needed
        if (isset($validated['interval'])) {
            switch($validated['interval']) {
                case 'hourly':
                    $data = $query->selectRaw('
                        HOUR(created_at) as hour,
                        DATE(created_at) as date,
                        AVG(temperature) as avg_temp,
                        AVG(humidity) as avg_humidity,
                        AVG(ph_value) as avg_ph,
                        AVG(light_intensity) as avg_light,
                        AVG(water_level) as avg_water_level,
                        AVG(co2_level) as avg_co2,
                        AVG(soil_moisture) as avg_soil_moisture,
                        MIN(created_at) as created_at
                    ')->groupBy('date', 'hour')->get();
                    break;
                    
                case 'daily':
                    $data = $query->selectRaw('
                        DATE(created_at) as date,
                        AVG(temperature) as avg_temp,
                        AVG(humidity) as avg_humidity,
                        AVG(ph_value) as avg_ph,
                        AVG(light_intensity) as avg_light,
                        AVG(water_level) as avg_water_level,
                        AVG(co2_level) as avg_co2,
                        AVG(soil_moisture) as avg_soil_moisture,
                        MIN(created_at) as created_at
                    ')->groupBy('date')->get();
                    break;
                    
                default:
                    $data = $query->take(100)->get();
            }
        } else {
            $data = $query->take(100)->get();
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function stats()
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        
        $todayCount = SensorReading::whereDate('created_at', $today)->count();
        $averages = SensorReading::whereDate('created_at', $today)
            ->selectRaw('
                AVG(temperature) as avg_temp,
                AVG(humidity) as avg_humidity,
                AVG(ph_value) as avg_ph,
                AVG(light_intensity) as avg_light,
                MAX(temperature) as max_temp,
                MIN(temperature) as min_temp
            ')
            ->first();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'data_points_today' => $todayCount,
                'averages' => $averages
            ]
        ]);
    }
}