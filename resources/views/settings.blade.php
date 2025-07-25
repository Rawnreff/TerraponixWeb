@extends('layouts.app')

@section('page-title', 'Settings')
@section('page-description', 'Configure greenhouse monitoring thresholds and automation')

@section('content')
<div data-page="settings" class="space-y-6">
    <!-- Threshold Settings -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="bi bi-sliders mr-2"></i>
            Sensor Thresholds
        </h3>
        
        <form id="settings-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Temperature Settings -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900">
                        <i class="bi bi-thermometer-half mr-2 text-blue-500"></i>
                        Temperature Control
                    </h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Temperature Threshold (°C)
                        </label>
                        <input type="number" id="temp-threshold" name="temp_threshold" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="29.0" step="0.1" min="15" max="40">
                        <p class="text-xs text-gray-500 mt-1">Fan will activate above this temperature</p>
                    </div>
                </div>

                <!-- Light Settings -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900">
                        <i class="bi bi-sun mr-2 text-yellow-500"></i>
                        Light Control
                    </h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Light Threshold (Lux)
                        </label>
                        <input type="number" id="light-threshold" name="light_threshold"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="2200" min="1000" max="5000">
                        <p class="text-xs text-gray-500 mt-1">Curtain will close above this light intensity</p>
                    </div>
                </div>

                <!-- Water Level Settings -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900">
                        <i class="bi bi-droplet mr-2 text-cyan-500"></i>
                        Water Management
                    </h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Water Level Threshold (mm)
                        </label>
                        <input type="number" id="water-threshold" name="water_level_threshold"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="1500" min="1000" max="2000">
                        <p class="text-xs text-gray-500 mt-1">Pump will activate below this level</p>
                    </div>
                </div>

                <!-- pH Settings -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900">
                        <i class="bi bi-water mr-2 text-green-500"></i>
                        pH Control
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                pH Min
                            </label>
                            <input type="number" id="ph-min" name="ph_min"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   value="5.5" step="0.1" min="4.0" max="8.0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                pH Max
                            </label>
                            <input type="number" id="ph-max" name="ph_max"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   value="6.5" step="0.1" min="4.0" max="8.0">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Optimal pH range for hydroponic systems</p>
                </div>
            </div>

            <!-- Automation Settings -->
            <div class="border-t pt-6">
                <h4 class="font-medium text-gray-900 mb-4">
                    <i class="bi bi-robot mr-2 text-purple-500"></i>
                    Automation Settings
                </h4>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Automatic Mode</label>
                            <p class="text-xs text-gray-500">Enable automatic responses to sensor readings</p>
                        </div>
                        <button type="button" id="auto-mode-toggle" class="toggle-switch active" data-state="on">
                            <span class="toggle-button active"></span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                            <p class="text-xs text-gray-500">Receive alerts when thresholds are exceeded</p>
                        </div>
                        <button type="button" id="notifications-toggle" class="toggle-switch inactive" data-state="off">
                            <span class="toggle-button inactive"></span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Data Logging</label>
                            <p class="text-xs text-gray-500">Store sensor data for historical analysis</p>
                        </div>
                        <button type="button" id="logging-toggle" class="toggle-switch active" data-state="on">
                            <span class="toggle-button active"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4 pt-6 border-t">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle mr-2"></i>
                    Save Settings
                </button>
                <button type="button" id="reset-defaults" class="btn-secondary">
                    <i class="bi bi-arrow-clockwise mr-2"></i>
                    Reset to Defaults
                </button>
                <button type="button" id="test-settings" class="btn-secondary">
                    <i class="bi bi-play-circle mr-2"></i>
                    Test Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Device Information -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-info-circle mr-2"></i>
            Device Information
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-900">Device Name</div>
                <div class="text-lg text-gray-700" id="device-name">Greenhouse Utama</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-900">IP Address</div>
                <div class="text-lg text-gray-700" id="device-ip">192.168.1.100</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-900">Last Seen</div>
                <div class="text-lg text-gray-700" id="device-last-seen">Just now</div>
            </div>
        </div>
    </div>

    <!-- Calibration -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-tools mr-2"></i>
            Sensor Calibration
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button class="btn-secondary" id="calibrate-ph">
                <i class="bi bi-water mr-2"></i>
                Calibrate pH
            </button>
            <button class="btn-secondary" id="calibrate-temp">
                <i class="bi bi-thermometer mr-2"></i>
                Calibrate Temperature
            </button>
            <button class="btn-secondary" id="calibrate-light">
                <i class="bi bi-sun mr-2"></i>
                Calibrate Light
            </button>
            <button class="btn-secondary" id="calibrate-water">
                <i class="bi bi-droplet mr-2"></i>
                Calibrate Water Level
            </button>
        </div>
    </div>

    <!-- Export/Import -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-file-earmark-arrow-down mr-2"></i>
            Data Management
        </h3>
        
        <div class="flex flex-wrap gap-4">
            <button class="btn-primary" id="export-data">
                <i class="bi bi-download mr-2"></i>
                Export Data
            </button>
            <button class="btn-secondary" id="import-settings">
                <i class="bi bi-upload mr-2"></i>
                Import Settings
            </button>
            <button class="btn-danger" id="clear-data">
                <i class="bi bi-trash mr-2"></i>
                Clear Historical Data
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Settings functionality
    if (typeof window.Dashboard !== 'undefined') {
        window.Dashboard.setupSettingsPage();
    }
});
</script>
@endsection