<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SensorDataController extends Controller
{
    public function index()
    {
        return view('sensor-data');
    }

    public function getLatestData()
    {
        $response = Http::get(url('/api/v1/sensor-data/latest'));
        return $response->json();
    }

    public function getHistory(Request $request)
    {
        $validated = $request->validate([
            'days' => 'sometimes|integer|min:1|max:30'
        ]);

        $days = $request->input('days', 7); // Default 7 hari

        $response = Http::get(url('/api/v1/sensor-data/history'), [
            'device_id' => 1,
            'from' => now()->subDays($days)->toDateString(),
            'to' => now()->toDateString(),
            'interval' => 'daily'
        ]);

        return $response->json();
    }
}