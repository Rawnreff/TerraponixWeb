@extends('layouts.app')

@section('page-title', 'Sensor Data')
@section('page-description', 'Detailed sensor readings and historical data')

@section('content')
<div data-page="sensor-data" class="space-y-6">
    <!-- Data Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="sensor-card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 mr-4">
                    <i class="bi bi-bar-chart text-2xl text-blue-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total Readings</div>
                    <div class="text-2xl font-bold text-gray-900" id="total-readings">-</div>
                </div>
            </div>
        </div>
        
        <div class="sensor-card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 mr-4">
                    <i class="bi bi-clock text-2xl text-green-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Last 24h</div>
                    <div class="text-2xl font-bold text-gray-900" id="readings-24h">-</div>
                </div>
            </div>
        </div>
        
        <div class="sensor-card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 mr-4">
                    <i class="bi bi-thermometer text-2xl text-yellow-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Avg Temperature</div>
                    <div class="text-2xl font-bold text-gray-900" id="avg-temp">-°C</div>
                </div>
            </div>
        </div>
        
        <div class="sensor-card">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 mr-4">
                    <i class="bi bi-exclamation-triangle text-2xl text-purple-600"></i>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Alerts</div>
                    <div class="text-2xl font-bold text-gray-900" id="alert-count">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-funnel mr-2"></i>
            Data Filters
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Time Range</label>
                <select id="time-range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1h">Last Hour</option>
                    <option value="6h">Last 6 Hours</option>
                    <option value="24h" selected>Last 24 Hours</option>
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sensor Type</label>
                <select id="sensor-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Sensors</option>
                    <option value="temperature">Temperature</option>
                    <option value="humidity">Humidity</option>
                    <option value="ph">pH Level</option>
                    <option value="light">Light Intensity</option>
                    <option value="water">Water Level</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Device</label>
                <select id="device-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Devices</option>
                    <option value="1">Greenhouse Utama</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button id="apply-filters" class="btn-primary w-full">
                    <i class="bi bi-search mr-2"></i>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Data Visualization -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Temperature Trends -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Temperature Trends</h3>
            <div class="chart-responsive">
                <canvas id="temperature-trend-chart"></canvas>
            </div>
        </div>

        <!-- Humidity Trends -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Humidity Trends</h3>
            <div class="chart-responsive">
                <canvas id="humidity-trend-chart"></canvas>
            </div>
        </div>

        <!-- pH Trends -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">pH Level Trends</h3>
            <div class="chart-responsive">
                <canvas id="ph-trend-chart"></canvas>
            </div>
        </div>

        <!-- Light Intensity -->
        <div class="chart-container">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Light Intensity</h3>
            <div class="chart-responsive">
                <canvas id="light-trend-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="control-panel">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="bi bi-table mr-2"></i>
                Recent Sensor Readings
            </h3>
            <div class="flex space-x-2">
                <button id="export-csv" class="btn-secondary">
                    <i class="bi bi-download mr-2"></i>
                    Export CSV
                </button>
                <button id="refresh-data" class="btn-primary">
                    <i class="bi bi-arrow-clockwise mr-2"></i>
                    Refresh
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Temperature (°C)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Humidity (%)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            pH Level
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Light (Lux)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Water Level (mm)
                        </th>
                    </tr>
                </thead>
                <tbody id="sensor-data-table" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be populated here -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4">
            <div class="text-sm text-gray-700">
                Showing <span id="showing-from">1</span> to <span id="showing-to">10</span> of <span id="total-count">0</span> results
            </div>
            <div class="flex space-x-2">
                <button id="prev-page" class="btn-secondary" disabled>
                    <i class="bi bi-chevron-left mr-1"></i>Previous
                </button>
                <button id="next-page" class="btn-secondary">
                    Next<i class="bi bi-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Panel -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-graph-up mr-2"></i>
            Statistical Summary
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600" id="temp-avg">-</div>
                <div class="text-sm text-gray-500">Avg Temperature</div>
                <div class="mt-2">
                    <span class="text-xs text-gray-400">Min: </span>
                    <span id="temp-min" class="text-xs font-medium">-</span>
                    <span class="text-xs text-gray-400 ml-2">Max: </span>
                    <span id="temp-max" class="text-xs font-medium">-</span>
                </div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600" id="humidity-avg">-</div>
                <div class="text-sm text-gray-500">Avg Humidity</div>
                <div class="mt-2">
                    <span class="text-xs text-gray-400">Min: </span>
                    <span id="humidity-min" class="text-xs font-medium">-</span>
                    <span class="text-xs text-gray-400 ml-2">Max: </span>
                    <span id="humidity-max" class="text-xs font-medium">-</span>
                </div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600" id="ph-avg">-</div>
                <div class="text-sm text-gray-500">Avg pH Level</div>
                <div class="mt-2">
                    <span class="text-xs text-gray-400">Min: </span>
                    <span id="ph-min" class="text-xs font-medium">-</span>
                    <span class="text-xs text-gray-400 ml-2">Max: </span>
                    <span id="ph-max" class="text-xs font-medium">-</span>
                </div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600" id="light-avg">-</div>
                <div class="text-sm text-gray-500">Avg Light Intensity</div>
                <div class="mt-2">
                    <span class="text-xs text-gray-400">Min: </span>
                    <span id="light-min" class="text-xs font-medium">-</span>
                    <span class="text-xs text-gray-400 ml-2">Max: </span>
                    <span id="light-max" class="text-xs font-medium">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sensor data page functionality
    if (typeof window.Dashboard !== 'undefined') {
        window.Dashboard.setupSensorDataPage();
    }
});
</script>
@endsection