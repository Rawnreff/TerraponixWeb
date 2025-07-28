<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ActuatorStatus;
use App\Models\ActuatorLog;

class ActuatorController extends Controller
{
    public function index()
    {
        return view('actuator-control');
    }

    public function getStatus()
    {
        try {
            // Get status directly from database for real-time data
            $status = ActuatorStatus::where('device_id', 1)->with('device')->first();
            
            if (!$status) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Actuator status not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get actuator status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function control(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:curtain,fan,water_pump',
            'value' => 'required'
        ]);

        $response = Http::post(url('/api/v1/actuator/control'), [
            'device_id' => 1,
            'actuator_type' => $request->type,
            'value' => $request->value
        ]);

        return $response->json();
    }

    public function getHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'nullable|exists:devices,id',
                'actuator_type' => 'nullable|in:curtain,fan,water_pump',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $deviceId = $validated['device_id'] ?? 1;
            $limit = $validated['limit'] ?? 20;

            $query = ActuatorLog::where('device_id', $deviceId)
                ->with('device');

            if (isset($validated['actuator_type'])) {
                $query->where('actuator_type', $validated['actuator_type']);
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get actuator history: ' . $e->getMessage()
            ], 500);
        }
    }
}