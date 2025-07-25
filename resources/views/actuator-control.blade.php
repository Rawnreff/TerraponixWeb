@extends('layouts.app')

@section('page-title', 'Actuator Control')
@section('page-description', 'Control greenhouse actuators and systems')

@section('content')
<div data-page="actuator-control" class="space-y-6">
    <!-- Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Curtain Status -->
        <div class="control-panel">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="bi bi-arrows-expand mr-2"></i>
                    Curtain Position
                </h3>
                <span id="curtain-status" class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    Manual
                </span>
            </div>
            <div class="space-y-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900" id="curtain-position">50%</div>
                    <div class="text-sm text-gray-500">Current Position</div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Set Position</label>
                    <input type="range" id="curtain-slider" min="0" max="100" value="50" 
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Closed</span>
                        <span>Open</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="btn-secondary flex-1" id="curtain-close">
                        <i class="bi bi-arrow-left mr-1"></i>Close
                    </button>
                    <button class="btn-primary flex-1" id="curtain-open">
                        <i class="bi bi-arrow-right mr-1"></i>Open
                    </button>
                </div>
            </div>
        </div>

        <!-- Fan Control -->
        <div class="control-panel">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="bi bi-fan mr-2"></i>
                    Exhaust Fan
                </h3>
                <span id="fan-status" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    OFF
                </span>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-center">
                    <button id="fan-toggle" class="toggle-switch inactive" data-state="off">
                        <span class="toggle-button inactive"></span>
                    </button>
                </div>
                <div class="text-center">
                    <div class="text-lg font-medium text-gray-900" id="fan-mode">Manual Control</div>
                    <div class="text-sm text-gray-500">Click to toggle</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-900" id="fan-runtime">0h 0m</div>
                        <div class="text-xs text-gray-500">Runtime Today</div>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-900" id="fan-cycles">0</div>
                        <div class="text-xs text-gray-500">Cycles</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Water Pump Control -->
        <div class="control-panel">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="bi bi-droplet mr-2"></i>
                    Water Pump
                </h3>
                <span id="pump-status" class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    OFF
                </span>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-center">
                    <button id="pump-toggle" class="toggle-switch inactive" data-state="off">
                        <span class="toggle-button inactive"></span>
                    </button>
                </div>
                <div class="text-center">
                    <div class="text-lg font-medium text-gray-900" id="pump-mode">Manual Control</div>
                    <div class="text-sm text-gray-500">Click to toggle</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-900" id="pump-flow">0 L/min</div>
                        <div class="text-xs text-gray-500">Flow Rate</div>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg">
                        <div class="text-sm font-medium text-gray-900" id="pump-volume">0 L</div>
                        <div class="text-xs text-gray-500">Total Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-lightning mr-2"></i>
            Quick Actions
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button class="btn-primary" id="emergency-stop">
                <i class="bi bi-stop-circle mr-2"></i>Emergency Stop
            </button>
            <button class="btn-success" id="auto-mode">
                <i class="bi bi-robot mr-2"></i>Auto Mode
            </button>
            <button class="btn-secondary" id="manual-mode">
                <i class="bi bi-hand-index mr-2"></i>Manual Mode
            </button>
            <button class="btn-secondary" id="preset-schedule">
                <i class="bi bi-clock mr-2"></i>Schedule
            </button>
        </div>
    </div>

    <!-- System Log -->
    <div class="control-panel">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi bi-journal-text mr-2"></i>
            Recent Activity
        </h3>
        <div class="space-y-2" id="activity-log">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-900">Water pump activated</span>
                </div>
                <span class="text-xs text-gray-500">2 minutes ago</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-900">Curtain position changed to 75%</span>
                </div>
                <span class="text-xs text-gray-500">5 minutes ago</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-900">Fan turned off automatically</span>
                </div>
                <span class="text-xs text-gray-500">10 minutes ago</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actuator control functionality
    if (typeof window.Dashboard !== 'undefined') {
        window.Dashboard.setupActuatorControls();
    }
});
</script>
@endsection