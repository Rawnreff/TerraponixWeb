<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ActuatorController extends Controller
{
    public function index()
    {
        return view('actuator-control');
    }

    public function getStatus()
    {
        $response = Http::get(url('/api/v1/devices/1/actuator-status'));
        return $response->json();
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
}