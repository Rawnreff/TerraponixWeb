@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-description', 'Real-time greenhouse monitoring and control')

@section('content')
<div data-page="dashboard" class="space-y-6">
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Temperature Card -->
        <div class="sensor-card sensor-card-primary">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <i class="bi bi-thermometer-half text-2xl mb-2"></i>
                        <span id="temperature-trend" class="ml-2"></span>
                    </div>
                    <div class="metric-value" id="temperature-value">--°C</div>
                    <div class="metric-label">Temperature</div>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-75">Target: 25°C</div>
                    <div class="text-xs opacity-75">Range: 20-30°C</div>
                </div>
            </div>
        </div>

        <!-- Humidity Card -->
        <div class="sensor-card sensor-card-success">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <i class="bi bi-droplet-half text-2xl mb-2"></i>
                        <span id="humidity-trend" class="ml-2"></span>
                    </div>
                    <div class="metric-value" id="humidity-value">--%</div>
                    <div class="metric-label">Humidity</div>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-75">Target: 60%</div>
                    <div class="text-xs opacity-75">Range: 50-70%</div>
                </div>
            </div>
        </div>

        <!-- pH Level Card -->
        <div class="sensor-card sensor-card-info">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <i class="bi bi-water text-2xl mb-2"></i>
                        <span id="ph-trend" class="ml-2"></span>
                    </div>
                    <div class="metric-value" id="ph-value">--</div>
                    <div class="metric-label">pH Level</div>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-75">Target: 6.5</div>
                    <div class="text-xs opacity-75">Range: 6.0-7.0</div>
                </div>
            </div>
        </div>

        <!-- Light Intensity Card -->
        <div class="sensor-card sensor-card-warning">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <i class="bi bi-sun text-2xl mb-2"></i>
                        <span id="light-trend" class="ml-2"></span>
                    </div>
                    <div class="metric-value" id="light-value">--</div>
                    <div class="metric-label">Light Intensity</div>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-75">Max: 4095</div>
                    <div class="text-xs opacity-75">Day Mode</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Water Level & System Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Water Level -->
        <div class="sensor-card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Water Level</h3>
            <div class="flex items-center justify-between mb-4">
                <span class="text-3xl font-bold text-blue-600" id="water-level-value">--%</span>
                <i class="bi bi-droplet text-blue-500 text-2xl"></i>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-blue-500 h-4 rounded-full transition-all duration-500" id="water-level-bar" style="width: 0%"></div>
            </div>
            <div class="mt-2 text-sm text-gray-600">
                <span class="text-xs">Empty</span>
                <span class="float-right text-xs">Full</span>
            </div>
        </div>

        <!-- System Status -->
        <div class="sensor-card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Status</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">ESP32 Device</span>
                    <span class="status-indicator status-online">Online</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Last Update</span>
                    <span class="text-sm text-gray-500" id="last-update">--</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Data Points Today</span>
                    <span class="text-sm font-semibold text-gray-900" id="data-points-today">--</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="sensor-card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <button class="control-button control-button-primary w-full control-actuator" 
                        data-actuator="fan" data-action="toggle">
                    <i class="bi bi-fan mr-2"></i>
                    Toggle Fan
                </button>
                <button class="control-button control-button-success w-full control-actuator" 
                        data-actuator="pump" data-action="toggle">
                    <i class="bi bi-droplet mr-2"></i>
                    Toggle Water Pump
                </button>
                <button class="control-button control-button-danger w-full" 
                        onclick="location.href='{{ route('actuator.control') }}'">
                    <i class="bi bi-gear mr-2"></i>
                    Full Control Panel
                </button>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Real-time Temperature & Humidity -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Real-time Trends</h3>
            <div class="h-64">
                <canvas id="tempHumidityChart"></canvas>
            </div>
        </div>

        <!-- Water Quality -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Water Quality</h3>
            <div class="h-64">
                <canvas id="waterQualityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Extended Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Light Intensity -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Light Intensity</h3>
            <div class="h-64">
                <canvas id="lightChart"></canvas>
            </div>
        </div>

        <!-- Historical Data -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">24-Hour History</h3>
            <div class="h-64">
                <canvas id="historicalChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Actuator Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Curtain Position -->
        <div class="actuator-control">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-900">Curtain Position</h4>
                <span class="status-indicator" id="curtain-status">--%</span>
            </div>
            <div class="mb-4">
                <input type="range" min="0" max="100" value="50" 
                       class="control-slider" 
                       data-actuator="curtain" 
                       id="curtain-slider">
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span>Closed</span>
                <span id="curtain-display">50%</span>
                <span>Open</span>
            </div>
        </div>

        <!-- Fan Control -->
        <div class="actuator-control">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-900">Cooling Fan</h4>
                <span class="status-indicator" id="fan-status">OFF</span>
            </div>
            <div class="flex space-x-2">
                <button class="control-button control-button-success flex-1 control-actuator" 
                        data-actuator="fan" data-action="on">
                    <i class="bi bi-power mr-1"></i>ON
                </button>
                <button class="control-button control-button-danger flex-1 control-actuator" 
                        data-actuator="fan" data-action="off">
                    <i class="bi bi-power mr-1"></i>OFF
                </button>
            </div>
            <div class="mt-3 text-xs text-gray-500 text-center">
                Auto mode based on temperature
            </div>
        </div>

        <!-- Water Pump Control -->
        <div class="actuator-control">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-900">Water Pump</h4>
                <span class="status-indicator" id="pump-status">OFF</span>
            </div>
            <div class="flex space-x-2">
                <button class="control-button control-button-success flex-1 control-actuator" 
                        data-actuator="pump" data-action="on">
                    <i class="bi bi-droplet mr-1"></i>ON
                </button>
                <button class="control-button control-button-danger flex-1 control-actuator" 
                        data-actuator="pump" data-action="off">
                    <i class="bi bi-droplet mr-1"></i>OFF
                </button>
            </div>
            <div class="mt-3 text-xs text-gray-500 text-center">
                Manual control only
            </div>
        </div>
    </div>

    <!-- Alerts & Notifications -->
    <div class="sensor-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-bell mr-2"></i>
            System Alerts
        </h3>
        <div id="alerts-container" class="space-y-2">
            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-green-800">All systems operating normally</span>
                </div>
                <span class="text-xs text-green-600">Now</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Dashboard-specific functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Update water level bar when value changes
        const updateWaterLevelBar = () => {
            const valueElement = document.getElementById('water-level-value');
            const barElement = document.getElementById('water-level-bar');
            
            if (valueElement && barElement) {
                const value = valueElement.textContent.replace('%', '');
                if (!isNaN(value)) {
                    barElement.style.width = value + '%';
                    
                    // Change color based on level
                    if (value < 20) {
                        barElement.className = 'bg-red-500 h-4 rounded-full transition-all duration-500';
                    } else if (value < 50) {
                        barElement.className = 'bg-yellow-500 h-4 rounded-full transition-all duration-500';
                    } else {
                        barElement.className = 'bg-blue-500 h-4 rounded-full transition-all duration-500';
                    }
                }
            }
        };

        // Update curtain slider display
        const curtainSlider = document.getElementById('curtain-slider');
        if (curtainSlider) {
            curtainSlider.addEventListener('input', function() {
                const display = document.getElementById('curtain-display');
                if (display) {
                    display.textContent = this.value + '%';
                }
            });
        }

        // Monitor for changes in water level value
        const observer = new MutationObserver(updateWaterLevelBar);
        const waterLevelValue = document.getElementById('water-level-value');
        if (waterLevelValue) {
            observer.observe(waterLevelValue, { childList: true, subtree: true });
        }

        // Initial update
        updateWaterLevelBar();
    });
</script>
@endpush
@endsection