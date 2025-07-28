<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\ActuatorStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Get latest sensor data for dashboard
     */
    public function getLatestSensorData()
    {
        try {
            $latestReading = SensorReading::with('device')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestReading) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sensor data found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [$latestReading]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sensor data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get sensor history for charts
     */
    public function getSensorHistory(Request $request)
    {
        try {
            $days = $request->get('days', 7);
            $startDate = Carbon::now()->subDays($days);

            // Get daily averages for the chart
            $history = SensorReading::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('AVG(temperature) as avg_temp'),
                    DB::raw('AVG(humidity) as avg_humidity'),
                    DB::raw('AVG(ph_value) as avg_ph'),
                    DB::raw('AVG(light_intensity) as avg_light'),
                    DB::raw('AVG(water_level) as avg_water_level')
                )
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // Format data for frontend
            $formattedHistory = $history->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M j'),
                    'avg_temp' => round($item->avg_temp, 1),
                    'avg_humidity' => round($item->avg_humidity, 1),
                    'avg_ph' => round($item->avg_ph, 2),
                    'avg_light' => round($item->avg_light, 0),
                    'avg_water_level' => round($item->avg_water_level, 1)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedHistory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching history data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get actuator status
     */
    public function getActuatorStatus()
    {
        try {
            // Default to device_id = 1 for backward compatibility
            $deviceId = request('device_id', 1);
            
            $status = ActuatorStatus::with('device')
                ->where('device_id', $deviceId)
                ->first();

            if (!$status) {
                // Create default status if no data found
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
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching actuator status: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get device status for system monitoring
     */
    public function getDeviceStatus()
    {
        try {
            $devices = Device::select('id', 'name', 'status', 'last_seen')
                ->get()
                ->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'name' => $device->name,
                        'status' => $device->status,
                        'last_seen' => $device->last_seen ? $device->last_seen->diffForHumans() : 'Never',
                        'is_online' => $device->last_seen && $device->last_seen->diffInMinutes(now()) < 5
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $devices
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching device status: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get dashboard summary stats
     */
    public function getDashboardSummary()
    {
        try {
            $summary = [
                'total_devices' => Device::count(),
                'online_devices' => Device::where('last_seen', '>=', Carbon::now()->subMinutes(5))->count(),
                'total_readings_today' => SensorReading::whereDate('created_at', today())->count(),
                'average_temp_today' => SensorReading::whereDate('created_at', today())->avg('temperature'),
                'average_humidity_today' => SensorReading::whereDate('created_at', today())->avg('humidity')
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard summary: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Export dashboard data to CSV
     */
    public function exportData(Request $request)
    {
        try {
            $days = $request->get('days', 7);
            $startDate = Carbon::now()->subDays($days);

            $readings = SensorReading::with('device')
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->get();

            $filename = 'greenhouse_data_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = array(
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );

            $callback = function() use($readings) {
                $file = fopen('php://output', 'w');
                
                // Header
                fputcsv($file, [
                    'Date/Time', 
                    'Device', 
                    'Temperature (°C)', 
                    'Humidity (%)', 
                    'pH Level', 
                    'Light Intensity (lux)', 
                    'Water Level (%)', 
                    'CO2 Level (ppm)', 
                    'Soil Moisture (%)'
                ]);

                // Data
                foreach ($readings as $reading) {
                    fputcsv($file, [
                        $reading->created_at->format('Y-m-d H:i:s'),
                        $reading->device->name ?? 'Unknown',
                        $reading->temperature,
                        $reading->humidity,
                        $reading->ph_value,
                        $reading->light_intensity,
                        $reading->water_level,
                        $reading->co2_level,
                        $reading->soil_moisture,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting data: ' . $e->getMessage()
            ], 500);
        }
    }
}