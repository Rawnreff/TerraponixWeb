<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class SensorController extends Controller
{
    /**
     * Store sensor data from ESP32 - Auto-save functionality
     */
    public function store(Request $request)
    {
        // Validate incoming sensor data
        $validated = $request->validate([
            'device_id' => 'nullable|exists:devices,id',
            'temperature' => 'required|numeric|between:-50,100',
            'humidity' => 'required|numeric|between:0,100',
            'ph_value' => 'required|numeric|between:0,14',
            'light_intensity' => 'required|integer|min:0',
            'water_level' => 'required|integer|min:0',
            'co2_level' => 'nullable|integer|min:0',
            'soil_moisture' => 'nullable|integer|between:0,100',
        ]);

        // If no device_id provided, use default device (for ESP32 without registration)
        if (!isset($validated['device_id'])) {
            $device = Device::firstOrCreate(
                ['name' => 'ESP32 Default'],
                [
                    'location' => 'Greenhouse',
                    'ip_address' => $request->ip(),
                    'status' => 'online',
                    'last_seen' => now()
                ]
            );
            $validated['device_id'] = $device->id;
        }
        
        // Create sensor reading
        $reading = SensorReading::create($validated);
        
        // Update device status and last seen
        $device = Device::find($validated['device_id']);
        $device->update([
            'last_seen' => now(), 
            'status' => 'online',
            'ip_address' => $request->ip()
        ]);
        
        // Log for debugging
        \Log::info('Sensor data received from ESP32', [
            'device_id' => $validated['device_id'],
            'ip' => $request->ip(),
            'data' => $validated
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Sensor data saved successfully',
            'data' => $reading,
            'device_id' => $validated['device_id'],
            'timestamp' => now()->toISOString()
        ], 201);
    }
    
    /**
     * Get latest sensor readings for dashboard
     */
    public function latest(Request $request)
    {
        $deviceId = $request->get('device_id', 1); // Default to device 1
        
        $latestReading = SensorReading::where('device_id', $deviceId)
            ->with('device')
            ->latest()
            ->first();
            
        if (!$latestReading) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'No sensor data available'
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $latestReading,
            'timestamp' => now()->toISOString()
        ]);
    }
    
    /**
     * Server-Sent Events stream for real-time data
     */
    public function stream(Request $request)
    {
        $deviceId = $request->get('device_id', 1);
        
        return response()->stream(function () use ($deviceId) {
            $lastUpdate = null;
            
            while (true) {
                // Get latest reading
                $reading = SensorReading::where('device_id', $deviceId)
                    ->latest()
                    ->first();
                
                if ($reading && (!$lastUpdate || $reading->updated_at > $lastUpdate)) {
                    $data = json_encode([
                        'type' => 'sensor_update',
                        'data' => $reading,
                        'timestamp' => now()->toISOString()
                    ]);
                    
                    echo "data: {$data}\n\n";
                    $lastUpdate = $reading->updated_at;
                }
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
                
                // Wait before next check
                sleep(2);
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }
    
    /**
     * Get sensor data history with flexible parameters
     */
    public function history(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'nullable|exists:devices,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'interval' => 'nullable|in:raw,hourly,daily,weekly',
            'limit' => 'nullable|integer|min:1|max:1000'
        ]);
        
        $deviceId = $validated['device_id'] ?? 1;
        $from = $validated['from'] ? Carbon::parse($validated['from']) : Carbon::now()->subDay();
        $to = $validated['to'] ? Carbon::parse($validated['to']) : Carbon::now();
        $interval = $validated['interval'] ?? 'raw';
        $limit = $validated['limit'] ?? 100;
        
        $query = SensorReading::where('device_id', $deviceId)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc');
            
        // Group by interval if needed
        switch($interval) {
            case 'hourly':
                $data = $query->selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as time_group,
                    AVG(temperature) as temperature,
                    AVG(humidity) as humidity,
                    AVG(ph_value) as ph_value,
                    AVG(light_intensity) as light_intensity,
                    AVG(water_level) as water_level,
                    AVG(co2_level) as co2_level,
                    AVG(soil_moisture) as soil_moisture,
                    COUNT(*) as reading_count
                ')->groupBy('time_group')
                ->orderBy('time_group', 'desc')
                ->limit($limit)
                ->get();
                break;
                
            case 'daily':
                $data = $query->selectRaw('
                    DATE(created_at) as date,
                    AVG(temperature) as temperature,
                    AVG(humidity) as humidity,
                    AVG(ph_value) as ph_value,
                    AVG(light_intensity) as light_intensity,
                    AVG(water_level) as water_level,
                    AVG(co2_level) as co2_level,
                    AVG(soil_moisture) as soil_moisture,
                    COUNT(*) as reading_count
                ')->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit($limit)
                ->get();
                break;
                
            case 'weekly':
                $data = $query->selectRaw('
                    YEARWEEK(created_at) as week,
                    AVG(temperature) as temperature,
                    AVG(humidity) as humidity,
                    AVG(ph_value) as ph_value,
                    AVG(light_intensity) as light_intensity,
                    AVG(water_level) as water_level,
                    AVG(co2_level) as co2_level,
                    AVG(soil_moisture) as soil_moisture,
                    COUNT(*) as reading_count
                ')->groupBy('week')
                ->orderBy('week', 'desc')
                ->limit($limit)
                ->get();
                break;
                
            default: // raw
                $data = $query->limit($limit)->get();
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'device_id' => $deviceId,
                'from' => $from->toISOString(),
                'to' => $to->toISOString(),
                'interval' => $interval,
                'count' => $data->count()
            ]
        ]);
    }
    
    /**
     * Get sensor statistics
     */
    public function statistics(Request $request)
    {
        $deviceId = $request->get('device_id', 1);
        $days = $request->get('days', 7);
        
        $from = Carbon::now()->subDays($days);
        
        $stats = SensorReading::where('device_id', $deviceId)
            ->where('created_at', '>=', $from)
            ->selectRaw('
                COUNT(*) as total_readings,
                AVG(temperature) as avg_temperature,
                MIN(temperature) as min_temperature,
                MAX(temperature) as max_temperature,
                AVG(humidity) as avg_humidity,
                MIN(humidity) as min_humidity,
                MAX(humidity) as max_humidity,
                AVG(ph_value) as avg_ph,
                MIN(ph_value) as min_ph,
                MAX(ph_value) as max_ph,
                AVG(light_intensity) as avg_light,
                MIN(light_intensity) as min_light,
                MAX(light_intensity) as max_light,
                AVG(water_level) as avg_water_level,
                MIN(water_level) as min_water_level,
                MAX(water_level) as max_water_level
            ')
            ->first();
            
        return response()->json([
            'status' => 'success',
            'data' => $stats,
            'period' => [
                'from' => $from->toISOString(),
                'to' => now()->toISOString(),
                'days' => $days
            ]
        ]);
    }
    
    /**
     * Health check for ESP32
     */
    public function healthCheck()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API is working',
            'timestamp' => now()->toISOString(),
            'server_time' => now()->format('Y-m-d H:i:s')
        ]);
    }
}