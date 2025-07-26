@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 text-primary">
                <i class="bi bi-speedometer2 me-2"></i>Terraponix Dashboard
            </h1>
            <p class="text-muted">Real-time greenhouse monitoring & control system</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="exportData()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                <label class="form-check-label" for="auto-refresh">Auto Refresh</label>
            </div>
        </div>
    </div>

    <!-- System Status Alert -->
    <div id="system-alert" class="alert alert-info d-none" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <span id="alert-message">System is running normally</span>
    </div>

    <!-- Real-time Sensor Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Temperature
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="temperature-value">
                                <i class="bi bi-thermometer-half me-2"></i>-- °C
                            </div>
                            <div class="text-xs text-muted mt-1" id="temp-status">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-thermometer-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Humidity
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="humidity-value">
                                <i class="bi bi-droplet me-2"></i>-- %
                            </div>
                            <div class="text-xs text-muted mt-1" id="humidity-status">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-droplet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                pH Level
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="ph-value">
                                <i class="bi bi-activity me-2"></i>--
                            </div>
                            <div class="text-xs text-muted mt-1" id="ph-status">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-activity fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Light Intensity
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="light-value">
                                <i class="bi bi-sun me-2"></i>--
                            </div>
                            <div class="text-xs text-muted mt-1" id="light-status">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-sun fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actuator Control Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear-wide-connected me-2"></i>Actuator Control
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Curtain Control -->
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-blinds me-2"></i>Curtain Control
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-4 text-primary mb-3" id="curtain-position">--%</div>
                                    <div class="btn-group w-100 mb-3" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 0)">
                                            <i class="bi bi-x-circle"></i> Close
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 50)">
                                            <i class="bi bi-dash"></i> 50%
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 100)">
                                            <i class="bi bi-check-circle"></i> Open
                                        </button>
                                    </div>
                                    <div class="mb-3">
                                        <label for="curtainRange" class="form-label">
                                            Position: <span id="curtainValue" class="badge bg-primary">50</span>%
                                        </label>
                                        <input type="range" class="form-range" min="0" max="100" id="curtainRange" value="50" 
                                               onchange="updateCurtainValue(this.value)" oninput="updateCurtainValue(this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fan Control -->
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-fan me-2"></i>Fan Control
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-4 mb-3">
                                        <span id="fan-status-icon" class="text-secondary">
                                            <i class="bi bi-fan"></i>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <span id="fan-status-text" class="badge bg-secondary fs-6">OFF</span>
                                    </div>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="fan-switch" 
                                               onchange="controlActuator('fan', this.checked ? 1 : 0)" style="transform: scale(1.5);">
                                        <label class="form-check-label ms-2" for="fan-switch">Toggle Fan</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Water Pump Control -->
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-droplet-fill me-2"></i>Water Pump
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-4 mb-3">
                                        <span id="pump-status-icon" class="text-secondary">
                                            <i class="bi bi-droplet-fill"></i>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <span id="pump-status-text" class="badge bg-secondary fs-6">OFF</span>
                                    </div>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="pump-switch" 
                                               onchange="controlActuator('water_pump', this.checked ? 1 : 0)" style="transform: scale(1.5);">
                                        <label class="form-check-label ms-2" for="pump-switch">Toggle Pump</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Control -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-danger btn-lg" onclick="emergencyStop()">
                                    <i class="bi bi-exclamation-triangle me-2"></i>EMERGENCY STOP
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Temperature & Humidity Trends
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="tempHumidityChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart me-2"></i>System Overview
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="systemOverviewChart" height="200"></canvas>
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
                        <i class="bi bi-info-circle me-2"></i>System Status
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Update Script -->
<script>
let refreshInterval;
let tempHumidityChart;
let systemOverviewChart;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadInitialData();
    startAutoRefresh();
    
    // Auto refresh toggle
    document.getElementById('auto-refresh').addEventListener('change', function() {
        if (this.checked) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    });
});

function initializeCharts() {
    // Temperature & Humidity Chart
    const tempCtx = document.getElementById('tempHumidityChart').getContext('2d');
    tempHumidityChart = new Chart(tempCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Temperature (°C)',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4
            }, {
                label: 'Humidity (%)',
                data: [],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // System Overview Chart
    const overviewCtx = document.getElementById('systemOverviewChart').getContext('2d');
    systemOverviewChart = new Chart(overviewCtx, {
        type: 'doughnut',
        data: {
            labels: ['Optimal', 'Warning', 'Critical'],
            datasets: [{
                data: [70, 20, 10],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function loadInitialData() {
    fetchSensorData();
    fetchActuatorStatus();
}

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        fetchSensorData();
        fetchActuatorStatus();
    }, 5000); // Refresh every 5 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function refreshData() {
    loadInitialData();
    showAlert('Data refreshed successfully', 'success');
}

function fetchSensorData() {
    fetch('/api/v1/sensor-data/realtime')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                updateSensorDisplay(data.data);
                updateCharts(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching sensor data:', error);
            showAlert('Failed to fetch sensor data', 'danger');
        });
}

function fetchActuatorStatus() {
    fetch('/api/v1/devices/1/actuator-realtime')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                updateActuatorDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching actuator status:', error);
        });
}

function updateSensorDisplay(data) {
    // Update temperature
    document.getElementById('temperature-value').innerHTML = 
        `<i class="bi bi-thermometer-half me-2"></i>${data.temperature.toFixed(1)} °C`;
    
    // Update temperature status
    const tempStatus = document.getElementById('temp-status');
    if (data.temperature > 30) {
        tempStatus.innerHTML = '<span class="text-danger">High Temperature</span>';
    } else if (data.temperature < 20) {
        tempStatus.innerHTML = '<span class="text-warning">Low Temperature</span>';
    } else {
        tempStatus.innerHTML = '<span class="text-success">Optimal</span>';
    }

    // Update humidity
    document.getElementById('humidity-value').innerHTML = 
        `<i class="bi bi-droplet me-2"></i>${data.humidity.toFixed(1)} %`;
    
    // Update humidity status
    const humidityStatus = document.getElementById('humidity-status');
    if (data.humidity > 80) {
        humidityStatus.innerHTML = '<span class="text-warning">High Humidity</span>';
    } else if (data.humidity < 40) {
        humidityStatus.innerHTML = '<span class="text-danger">Low Humidity</span>';
    } else {
        humidityStatus.innerHTML = '<span class="text-success">Optimal</span>';
    }

    // Update pH
    document.getElementById('ph-value').innerHTML = 
        `<i class="bi bi-activity me-2"></i>${data.ph_value.toFixed(2)}`;
    
    // Update pH status
    const phStatus = document.getElementById('ph-status');
    if (data.ph_value >= 6.0 && data.ph_value <= 7.5) {
        phStatus.innerHTML = '<span class="text-success">Optimal</span>';
    } else if (data.ph_value < 6.0) {
        phStatus.innerHTML = '<span class="text-danger">Acidic</span>';
    } else {
        phStatus.innerHTML = '<span class="text-warning">Alkaline</span>';
    }

    // Update light
    document.getElementById('light-value').innerHTML = 
        `<i class="bi bi-sun me-2"></i>${data.light_intensity}`;
    
    // Update light status
    const lightStatus = document.getElementById('light-status');
    if (data.light_intensity > 3000) {
        lightStatus.innerHTML = '<span class="text-warning">Bright</span>';
    } else if (data.light_intensity < 1000) {
        lightStatus.innerHTML = '<span class="text-info">Dark</span>';
    } else {
        lightStatus.innerHTML = '<span class="text-success">Moderate</span>';
    }

    // Update last seen
    document.getElementById('last-seen').textContent = 'Just now';
}

function updateActuatorDisplay(data) {
    // Update curtain
    document.getElementById('curtain-position').textContent = data.curtain_position + '%';
    document.getElementById('curtain-status-value').textContent = data.curtain_position + '%';
    document.getElementById('curtain-status').textContent = data.curtain_position + '% Open';
    document.getElementById('curtainRange').value = data.curtain_position;
    document.getElementById('curtainValue').textContent = data.curtain_position;

    // Update fan
    const fanIcon = document.getElementById('fan-status-icon');
    const fanText = document.getElementById('fan-status-text');
    const fanSwitch = document.getElementById('fan-switch');
    
    if (data.fan_status) {
        fanIcon.innerHTML = '<i class="bi bi-fan text-success"></i>';
        fanText.textContent = 'ON';
        fanText.className = 'badge bg-success fs-6';
        fanSwitch.checked = true;
        document.getElementById('fan-status').textContent = 'ON';
        document.getElementById('fan-status').className = 'badge bg-success me-2 p-2';
    } else {
        fanIcon.innerHTML = '<i class="bi bi-fan text-secondary"></i>';
        fanText.textContent = 'OFF';
        fanText.className = 'badge bg-secondary fs-6';
        fanSwitch.checked = false;
        document.getElementById('fan-status').textContent = 'OFF';
        document.getElementById('fan-status').className = 'badge bg-secondary me-2 p-2';
    }

    // Update pump
    const pumpIcon = document.getElementById('pump-status-icon');
    const pumpText = document.getElementById('pump-status-text');
    const pumpSwitch = document.getElementById('pump-switch');
    
    if (data.water_pump_status) {
        pumpIcon.innerHTML = '<i class="bi bi-droplet-fill text-info"></i>';
        pumpText.textContent = 'ON';
        pumpText.className = 'badge bg-info fs-6';
        pumpSwitch.checked = true;
        document.getElementById('pump-status').textContent = 'ON';
        document.getElementById('pump-status').className = 'badge bg-info me-2 p-2';
    } else {
        pumpIcon.innerHTML = '<i class="bi bi-droplet-fill text-secondary"></i>';
        pumpText.textContent = 'OFF';
        pumpText.className = 'badge bg-secondary fs-6';
        pumpSwitch.checked = false;
        document.getElementById('pump-status').textContent = 'OFF';
        document.getElementById('pump-status').className = 'badge bg-secondary me-2 p-2';
    }
}

function updateCharts(data) {
    const now = new Date().toLocaleTimeString();
    
    // Update temperature & humidity chart
    tempHumidityChart.data.labels.push(now);
    tempHumidityChart.data.datasets[0].data.push(data.temperature);
    tempHumidityChart.data.datasets[1].data.push(data.humidity);
    
    // Keep only last 10 data points
    if (tempHumidityChart.data.labels.length > 10) {
        tempHumidityChart.data.labels.shift();
        tempHumidityChart.data.datasets[0].data.shift();
        tempHumidityChart.data.datasets[1].data.shift();
    }
    
    tempHumidityChart.update();
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
            fetchActuatorStatus();
        } else {
            showAlert(`Failed to control ${type}`, 'danger');
        }
    })
    .catch(error => {
        console.error('Error controlling actuator:', error);
        showAlert(`Error controlling ${type}`, 'danger');
    });
}

function updateCurtainValue(value) {
    document.getElementById('curtainValue').textContent = value;
}

function emergencyStop() {
    if (confirm('Are you sure you want to activate emergency stop? This will turn off all actuators.')) {
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
                showAlert('Emergency stop activated', 'warning');
                fetchActuatorStatus();
            } else {
                showAlert('Failed to activate emergency stop', 'danger');
            }
        })
        .catch(error => {
            console.error('Error activating emergency stop:', error);
            showAlert('Error activating emergency stop', 'danger');
        });
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

function exportData() {
    // Implementation for data export
    showAlert('Export feature coming soon', 'info');
}
</script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-xs {
    font-size: 0.7rem !important;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-group .btn {
    transition: all 0.2s;
}

.btn-group .btn:hover {
    transform: scale(1.05);
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

.display-4 {
    font-weight: 300;
    line-height: 1.2;
}
</style>
@endsection