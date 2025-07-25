<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
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
        $device->update(['last_seen' => now(), 'status' => 'online']);
        
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
            'device_id' => 'required|exists:devices,id',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'interval' => 'in:hourly,daily,weekly'
        ]);
        
        $query = SensorReading::where('device_id', $validated['device_id'])
            ->whereBetween('created_at', [
                Carbon::parse($validated['from']),
                Carbon::parse($validated['to'])
            ]);
            
        // Group by interval if needed
        if (isset($validated['interval'])) {
            switch($validated['interval']) {
                case 'hourly':
                    $data = $query->selectRaw('
                        HOUR(created_at) as hour,
                        AVG(temperature) as avg_temp,
                        AVG(humidity) as avg_humidity,
                        AVG(ph_value) as avg_ph,
                        AVG(light_intensity) as avg_light,
                        AVG(water_level) as avg_water_level,
                        AVG(co2_level) as avg_co2,
                        AVG(soil_moisture) as avg_soil_moisture
                    ')->groupBy('hour')->get();
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
                        AVG(soil_moisture) as avg_soil_moisture
                    ')->groupBy('date')->get();
                    break;
                    
                default:
                    $data = $query->get();
            }
        } else {
            $data = $query->get();
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}