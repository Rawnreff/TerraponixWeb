<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }

    public function getSettings()
    {
        $response = Http::get(url('/api/v1/devices/1/settings'));
        return $response->json();
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'temp_threshold' => 'required|numeric',
            'light_threshold' => 'required|integer',
            'water_level_threshold' => 'required|integer',
            'ph_min' => 'required|numeric',
            'ph_max' => 'required|numeric',
            'auto_mode' => 'required|boolean'
        ]);

        $response = Http::post(url('/api/v1/devices/1/settings'), $validated);
        return $response->json();
    }
}   