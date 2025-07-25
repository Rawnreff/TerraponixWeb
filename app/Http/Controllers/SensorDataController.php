<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SensorReading;
use Carbon\Carbon;

class SensorDataController extends Controller
{
    public function index()
    {
        return view('sensor-data');
    }

    public function getLatestData()
    {
        try {
            $response = Http::get(url('/api/v1/sensor-data/latest'));
            
            if ($response->successful()) {
                return $response->json();
            }
            
            // Fallback to direct database query
            $latestReadings = SensorReading::latest()
                ->with('device')
                ->take(10)
                ->get();
                
            return response()->json([
                'status' => 'success',
                'data' => $latestReadings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch sensor data',
                'data' => []
            ], 500);
        }
    }

    public function getHistory(Request $request)
    {
        $validated = $request->validate([
            'days' => 'sometimes|integer|min:1|max:30',
            'interval' => 'sometimes|in:hourly,daily,weekly'
        ]);

        $days = $request->input('days', 1); // Default 1 day for real-time
        $interval = $request->input('interval', 'hourly');

        try {
            $response = Http::get(url('/api/v1/sensor-data/history'), [
                'device_id' => 1,
                'days' => $days,
                'interval' => $interval
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            // Fallback to direct database query
            $from = now()->subDays($days);
            $to = now();
            
            $query = SensorReading::where('device_id', 1)
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at', 'desc');
                
            if ($interval === 'daily') {
                $data = $query->selectRaw('
                    DATE(created_at) as date,
                    AVG(temperature) as temperature,
                    AVG(humidity) as humidity,
                    AVG(ph_value) as ph_value,
                    AVG(light_intensity) as light_intensity,
                    AVG(water_level) as water_level,
                    MIN(created_at) as created_at
                ')->groupBy('date')->get();
            } else {
                $data = $query->take(100)->get();
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch historical data',
                'data' => []
            ], 500);
        }
    }
    
    public function getStats()
    {
        try {
            $response = Http::get(url('/api/v1/sensor-data/stats'));
            
            if ($response->successful()) {
                return $response->json();
            }
            
            // Fallback to direct calculation
            $today = now()->startOfDay();
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
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch stats',
                'data' => [
                    'data_points_today' => 0,
                    'averages' => null
                ]
            ], 500);
        }
    }
}