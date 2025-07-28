<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\ActuatorController as ApiActuatorController;

class ActuatorController extends Controller
{
    protected $apiController;
    
    public function __construct()
    {
        $this->apiController = new ApiActuatorController();
    }
    
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
        // Bridge function to handle old web frontend route format
        $validated = $request->validate([
            'type' => 'required|in:curtain,fan,water_pump',
            'value' => 'required'
        ]);

        // Convert old format to new API format
        $apiRequest = new Request([
            'device_id' => 1, // Default device ID for backward compatibility
            'actuator_type' => $validated['type'],
            'value' => $validated['value']
        ]);

        // Call the API controller directly
        return $this->apiController->control($apiRequest);
    }
}