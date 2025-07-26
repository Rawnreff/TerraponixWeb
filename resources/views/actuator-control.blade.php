@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 text-primary">
                <i class="bi bi-gear-wide-connected me-2"></i>Actuator Control Center
            </h1>
            <p class="text-muted">Real-time control for greenhouse automation systems</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshStatus()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="showLogs()">
                    <i class="bi bi-clock-history"></i> Logs
                </button>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="auto-mode-switch" checked>
                <label class="form-check-label" for="auto-mode-switch">Auto Mode</label>
            </div>
        </div>
    </div>

    <!-- System Alert -->
    <div id="system-alert" class="alert alert-info d-none" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <span id="alert-message">System is running normally</span>
    </div>

    <!-- Emergency Control -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Emergency Control
                    </h5>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Use this button to immediately stop all actuators in case of emergency</p>
                    <button type="button" class="btn btn-danger btn-lg px-5" onclick="emergencyStop()">
                        <i class="bi bi-exclamation-triangle me-2"></i>EMERGENCY STOP
                    </button>
                    <div class="mt-2">
                        <small class="text-muted">This will turn off all fans, pumps, and close curtains</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Control Panel -->
    <div class="row mb-4">
        <!-- Curtain Control -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-blinds me-2"></i>Curtain Control System
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3">
                            <div class="display-3 text-primary mb-2" id="curtain-position">--%</div>
                            <div class="text-muted">Current Position</div>
                            <div class="mt-2">
                                <span id="curtain-status-badge" class="badge bg-secondary">Unknown</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 0)">
                                    <i class="bi bi-x-circle me-2"></i>Close Completely
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 25)">
                                    <i class="bi bi-dash me-2"></i>25% Open
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 50)">
                                    <i class="bi bi-dash me-2"></i>50% Open
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 75)">
                                    <i class="bi bi-dash me-2"></i>75% Open
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 100)">
                                    <i class="bi bi-check-circle me-2"></i>Fully Open
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mt-3">
                        <label for="curtainRange" class="form-label">
                            Manual Control: <span id="curtainValue" class="badge bg-primary">50</span>%
                        </label>
                        <input type="range" class="form-range" min="0" max="100" id="curtainRange" value="50" 
                               onchange="updateCurtainValue(this.value)" oninput="updateCurtainValue(this.value)">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">0% (Closed)</small>
                            <small class="text-muted">100% (Open)</small>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="applyCurtainPosition()">
                                Apply Position
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fan Control -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-fan me-2"></i>Ventilation Fan Control
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3">
                            <div class="display-3 mb-2">
                                <span id="fan-status-icon" class="text-secondary">
                                    <i class="bi bi-fan"></i>
                                </span>
                            </div>
                            <div class="text-muted">Current Status</div>
                            <div class="mt-2">
                                <span id="fan-status-text" class="badge bg-secondary fs-6">OFF</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Control Options</h6>
                            <div class="d-grid gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="fan-switch" 
                                           onchange="controlActuator('fan', this.checked ? 1 : 0)" style="transform: scale(1.5);">
                                    <label class="form-check-label ms-2" for="fan-switch">Toggle Fan</label>
                                </div>
                                <button type="button" class="btn btn-outline-success" onclick="controlActuator('fan', 1)">
                                    <i class="bi bi-play-circle me-2"></i>Turn ON
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="controlActuator('fan', 0)">
                                    <i class="bi bi-stop-circle me-2"></i>Turn OFF
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mt-3">
                        <h6>Fan Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Status</small>
                                <span id="fan-detail-status">Offline</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Last Updated</small>
                                <span id="fan-last-updated">Never</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Water Pump Control -->
    <div class="row mb-4">
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-droplet-fill me-2"></i>Water Pump Control
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3">
                            <div class="display-3 mb-2">
                                <span id="pump-status-icon" class="text-secondary">
                                    <i class="bi bi-droplet-fill"></i>
                                </span>
                            </div>
                            <div class="text-muted">Current Status</div>
                            <div class="mt-2">
                                <span id="pump-status-text" class="badge bg-secondary fs-6">OFF</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Control Options</h6>
                            <div class="d-grid gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pump-switch" 
                                           onchange="controlActuator('water_pump', this.checked ? 1 : 0)" style="transform: scale(1.5);">
                                    <label class="form-check-label ms-2" for="pump-switch">Toggle Pump</label>
                                </div>
                                <button type="button" class="btn btn-outline-info" onclick="controlActuator('water_pump', 1)">
                                    <i class="bi bi-play-circle me-2"></i>Start Pump
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="controlActuator('water_pump', 0)">
                                    <i class="bi bi-stop-circle me-2"></i>Stop Pump
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mt-3">
                        <h6>Pump Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Status</small>
                                <span id="pump-detail-status">Offline</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Last Updated</small>
                                <span id="pump-last-updated">Never</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Control -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Bulk Control Operations
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Preset Configurations</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-warning" onclick="applyPreset('day')">
                            <i class="bi bi-sun me-2"></i>Day Mode
                            <small class="d-block">Open curtains, fan ON, pump OFF</small>
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="applyPreset('night')">
                            <i class="bi bi-moon me-2"></i>Night Mode
                            <small class="d-block">Close curtains, fan OFF, pump OFF</small>
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="applyPreset('watering')">
                            <i class="bi bi-droplet me-2"></i>Watering Mode
                            <small class="d-block">Curtains 50%, fan ON, pump ON</small>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="applyPreset('eco')">
                            <i class="bi bi-leaf me-2"></i>Eco Mode
                            <small class="d-block">Minimal power consumption</small>
                        </button>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Custom Bulk Control</h6>
                    <div class="row">
                        <div class="col-6">
                            <label for="bulk-curtain" class="form-label">Curtain Position</label>
                            <select class="form-select" id="bulk-curtain">
                                <option value="">No Change</option>
                                <option value="0">0% (Closed)</option>
                                <option value="25">25%</option>
                                <option value="50">50%</option>
                                <option value="75">75%</option>
                                <option value="100">100% (Open)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="bulk-fan" class="form-label">Fan Status</label>
                            <select class="form-select" id="bulk-fan">
                                <option value="">No Change</option>
                                <option value="1">ON</option>
                                <option value="0">OFF</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <label for="bulk-pump" class="form-label">Pump Status</label>
                            <select class="form-select" id="bulk-pump">
                                <option value="">No Change</option>
                                <option value="1">ON</option>
                                <option value="0">OFF</option>
                            </select>
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <button type="button" class="btn btn-warning w-100" onclick="applyBulkControl()">
                                Apply All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>System Status & Logs
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div id="device-status" class="badge bg-success me-2 p-2">
                                    <i class="bi bi-wifi"></i> Online
                                </div>
                                <div>
                                    <small class="text-muted d-block">Device Status</small>
                                    <small>Last seen: <span id="last-seen">Just now</span></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div id="curtain-status" class="badge bg-info me-2 p-2">50% Open</div>
                                <div>
                                    <small class="text-muted d-block">Curtain Status</small>
                                    <small>Position: <span id="curtain-status-value">50%</span></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div id="fan-status" class="badge bg-secondary me-2 p-2">OFF</div>
                                <div>
                                    <small class="text-muted d-block">Fan Status</small>
                                    <small>Speed: <span id="fan-speed">0%</span></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div id="pump-status" class="badge bg-secondary me-2 p-2">OFF</div>
                                <div>
                                    <small class="text-muted d-block">Pump Status</small>
                                    <small>Flow: <span id="pump-flow">0 L/min</span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Recent Activity Log</h6>
                            <div id="activity-log" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-muted text-center">No recent activity</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Control Script -->
<script>
let refreshInterval;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadInitialStatus();
    startAutoRefresh();
    
    // Auto mode toggle
    document.getElementById('auto-mode-switch').addEventListener('change', function() {
        if (this.checked) {
            startAutoRefresh();
            showAlert('Auto mode enabled', 'success');
        } else {
            stopAutoRefresh();
            showAlert('Auto mode disabled', 'warning');
        }
    });
});

function loadInitialStatus() {
    fetchActuatorStatus();
}

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        fetchActuatorStatus();
    }, 3000); // Refresh every 3 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function refreshStatus() {
    fetchActuatorStatus();
    showAlert('Status refreshed', 'success');
}

function fetchActuatorStatus() {
    fetch('/api/v1/devices/1/actuator-realtime')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                updateActuatorDisplay(data.data);
                addActivityLog('Status updated', 'info');
            }
        })
        .catch(error => {
            console.error('Error fetching actuator status:', error);
            addActivityLog('Failed to fetch status', 'danger');
        });
}

function updateActuatorDisplay(data) {
    // Update curtain
    document.getElementById('curtain-position').textContent = data.curtain_position + '%';
    document.getElementById('curtain-status-value').textContent = data.curtain_position + '%';
    document.getElementById('curtain-status').textContent = data.curtain_position + '% Open';
    document.getElementById('curtain-status-badge').textContent = data.curtain_position + '% Open';
    document.getElementById('curtainRange').value = data.curtain_position;
    document.getElementById('curtainValue').textContent = data.curtain_position;

    // Update fan
    const fanIcon = document.getElementById('fan-status-icon');
    const fanText = document.getElementById('fan-status-text');
    const fanSwitch = document.getElementById('fan-switch');
    const fanDetailStatus = document.getElementById('fan-detail-status');
    
    if (data.fan_status) {
        fanIcon.innerHTML = '<i class="bi bi-fan text-success"></i>';
        fanText.textContent = 'ON';
        fanText.className = 'badge bg-success fs-6';
        fanSwitch.checked = true;
        document.getElementById('fan-status').textContent = 'ON';
        document.getElementById('fan-status').className = 'badge bg-success me-2 p-2';
        fanDetailStatus.textContent = 'Running';
        fanDetailStatus.className = 'text-success';
    } else {
        fanIcon.innerHTML = '<i class="bi bi-fan text-secondary"></i>';
        fanText.textContent = 'OFF';
        fanText.className = 'badge bg-secondary fs-6';
        fanSwitch.checked = false;
        document.getElementById('fan-status').textContent = 'OFF';
        document.getElementById('fan-status').className = 'badge bg-secondary me-2 p-2';
        fanDetailStatus.textContent = 'Stopped';
        fanDetailStatus.className = 'text-secondary';
    }

    // Update pump
    const pumpIcon = document.getElementById('pump-status-icon');
    const pumpText = document.getElementById('pump-status-text');
    const pumpSwitch = document.getElementById('pump-switch');
    const pumpDetailStatus = document.getElementById('pump-detail-status');
    
    if (data.water_pump_status) {
        pumpIcon.innerHTML = '<i class="bi bi-droplet-fill text-info"></i>';
        pumpText.textContent = 'ON';
        pumpText.className = 'badge bg-info fs-6';
        pumpSwitch.checked = true;
        document.getElementById('pump-status').textContent = 'ON';
        document.getElementById('pump-status').className = 'badge bg-info me-2 p-2';
        pumpDetailStatus.textContent = 'Running';
        pumpDetailStatus.className = 'text-info';
    } else {
        pumpIcon.innerHTML = '<i class="bi bi-droplet-fill text-secondary"></i>';
        pumpText.textContent = 'OFF';
        pumpText.className = 'badge bg-secondary fs-6';
        pumpSwitch.checked = false;
        document.getElementById('pump-status').textContent = 'OFF';
        document.getElementById('pump-status').className = 'badge bg-secondary me-2 p-2';
        pumpDetailStatus.textContent = 'Stopped';
        pumpDetailStatus.className = 'text-secondary';
    }

    // Update timestamps
    const now = new Date().toLocaleTimeString();
    document.getElementById('fan-last-updated').textContent = now;
    document.getElementById('pump-last-updated').textContent = now;
    document.getElementById('last-seen').textContent = 'Just now';
}

function controlActuator(type, value) {
    fetch('/api/v1/actuator/control', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            device_id: 1,
            actuator_type: type,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(`${type} controlled successfully`, 'success');
            addActivityLog(`${type} set to ${value}`, 'success');
            fetchActuatorStatus();
        } else {
            showAlert(`Failed to control ${type}`, 'danger');
            addActivityLog(`Failed to control ${type}`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error controlling actuator:', error);
        showAlert(`Error controlling ${type}`, 'danger');
        addActivityLog(`Error controlling ${type}`, 'danger');
    });
}

function updateCurtainValue(value) {
    document.getElementById('curtainValue').textContent = value;
}

function applyCurtainPosition() {
    const value = document.getElementById('curtainRange').value;
    controlActuator('curtain', parseInt(value));
}

function emergencyStop() {
    if (confirm('Are you sure you want to activate emergency stop? This will turn off all actuators immediately.')) {
        fetch('/api/v1/devices/1/emergency-stop', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('Emergency stop activated - all actuators turned off', 'warning');
                addActivityLog('Emergency stop activated', 'danger');
                fetchActuatorStatus();
            } else {
                showAlert('Failed to activate emergency stop', 'danger');
                addActivityLog('Failed to activate emergency stop', 'danger');
            }
        })
        .catch(error => {
            console.error('Error activating emergency stop:', error);
            showAlert('Error activating emergency stop', 'danger');
            addActivityLog('Error activating emergency stop', 'danger');
        });
    }
}

function applyPreset(preset) {
    let config = {};
    
    switch(preset) {
        case 'day':
            config = { curtain_position: 100, fan_status: 1, water_pump_status: 0 };
            break;
        case 'night':
            config = { curtain_position: 0, fan_status: 0, water_pump_status: 0 };
            break;
        case 'watering':
            config = { curtain_position: 50, fan_status: 1, water_pump_status: 1 };
            break;
        case 'eco':
            config = { curtain_position: 25, fan_status: 0, water_pump_status: 0 };
            break;
    }
    
    applyBulkControlConfig(config, preset);
}

function applyBulkControl() {
    const curtain = document.getElementById('bulk-curtain').value;
    const fan = document.getElementById('bulk-fan').value;
    const pump = document.getElementById('bulk-pump').value;
    
    const config = {};
    if (curtain !== '') config.curtain_position = parseInt(curtain);
    if (fan !== '') config.fan_status = parseInt(fan);
    if (pump !== '') config.water_pump_status = parseInt(pump);
    
    if (Object.keys(config).length === 0) {
        showAlert('Please select at least one setting to change', 'warning');
        return;
    }
    
    applyBulkControlConfig(config, 'custom');
}

function applyBulkControlConfig(config, presetName) {
    fetch('/api/v1/actuator/bulk-control', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            device_id: 1,
            ...config
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(`${presetName} configuration applied successfully`, 'success');
            addActivityLog(`Applied ${presetName} preset`, 'success');
            fetchActuatorStatus();
        } else {
            showAlert(`Failed to apply ${presetName} configuration`, 'danger');
            addActivityLog(`Failed to apply ${presetName} preset`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error applying bulk control:', error);
        showAlert(`Error applying ${presetName} configuration`, 'danger');
        addActivityLog(`Error applying ${presetName} preset`, 'danger');
    });
}

function addActivityLog(message, type) {
    const logContainer = document.getElementById('activity-log');
    const timestamp = new Date().toLocaleTimeString();
    
    const logEntry = document.createElement('div');
    logEntry.className = `d-flex justify-content-between align-items-center mb-1`;
    logEntry.innerHTML = `
        <span class="text-${type}">${message}</span>
        <small class="text-muted">${timestamp}</small>
    `;
    
    logContainer.insertBefore(logEntry, logContainer.firstChild);
    
    // Keep only last 10 entries
    const entries = logContainer.children;
    if (entries.length > 10) {
        logContainer.removeChild(entries[entries.length - 1]);
    }
}

function showAlert(message, type) {
    const alert = document.getElementById('system-alert');
    const alertMessage = document.getElementById('alert-message');
    
    alert.className = `alert alert-${type}`;
    alertMessage.textContent = message;
    alert.classList.remove('d-none');
    
    setTimeout(() => {
        alert.classList.add('d-none');
    }, 3000);
}

function showLogs() {
    // Implementation for detailed logs view
    showAlert('Detailed logs feature coming soon', 'info');
}
</script>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

#curtainRange {
    cursor: pointer;
}

.badge {
    transition: all 0.3s;
}

.display-3 {
    font-weight: 300;
    line-height: 1.2;
}

.form-range::-webkit-slider-thumb {
    background: #007bff;
}

.form-range::-moz-range-thumb {
    background: #007bff;
}

#activity-log {
    background-color: #f8f9fa;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.card-header {
    font-weight: 600;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-secondary {
    color: #6c757d !important;
}
</style>
@endsection