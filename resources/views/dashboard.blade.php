@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData()">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
        <div class="dropdown">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-calendar"></i> <span id="time-range">This week</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(1, 'Today')">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(7, 'This week')">This week</a></li>
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(30, 'This month')">This month</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loading-indicator" class="d-none">
    <div class="d-flex justify-content-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Error Alert -->
<div id="error-alert" class="alert alert-danger d-none" role="alert">
    <i class="bi bi-exclamation-triangle"></i>
    <span id="error-message">Unable to load data. Please check your connection.</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Status Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Temperature</span>
                <i class="bi bi-thermometer-half"></i>
            </div>
            <div class="card-body">
                <h5 class="card-title" id="temperature-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </span>
                </h5>
                <p class="card-text">Current greenhouse temperature</p>
                <small class="text-light" id="temp-status">Normal range: 20-30°C</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Humidity</span>
                <i class="bi bi-droplet-half"></i>
            </div>
            <div class="card-body">
                <h5 class="card-title" id="humidity-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </span>
                </h5>
                <p class="card-text">Current humidity level</p>
                <small class="text-light" id="humidity-status">Normal range: 60-80%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>pH Level</span>
                <i class="bi bi-flask"></i>
            </div>
            <div class="card-body">
                <h5 class="card-title" id="ph-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-4"></span>
                    </span>
                </h5>
                <p class="card-text">Water pH level</p>
                <small class="text-light" id="ph-status">Normal range: 6.0-7.5</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Light Intensity</span>
                <i class="bi bi-brightness-high"></i>
            </div>
            <div class="card-body">
                <h5 class="card-title" id="light-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </span>
                </h5>
                <p class="card-text">Current light level</p>
                <small class="text-dark" id="light-status">Normal range: 400-800 lux</small>
            </div>
        </div>
    </div>
</div>

<!-- Additional Sensor Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-water"></i> Water Level
            </div>
            <div class="card-body">
                <h5 class="card-title text-primary" id="water-level-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </span>
                </h5>
                <div class="progress mt-2">
                    <div class="progress-bar bg-primary" role="progressbar" id="water-level-bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-secondary">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-cloud"></i> CO2 Level
            </div>
            <div class="card-body">
                <h5 class="card-title text-secondary" id="co2-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </span>
                </h5>
                <small class="text-muted" id="co2-status">Normal range: 300-500 ppm</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <i class="bi bi-moisture"></i> Soil Moisture
            </div>
            <div class="card-body">
                <h5 class="card-title text-success" id="soil-moisture-value">
                    <span class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </span>
                </h5>
                <div class="progress mt-2">
                    <div class="progress-bar bg-success" role="progressbar" id="soil-moisture-bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up"></i> Temperature & Humidity</span>
                <small class="text-muted" id="chart-period">(7 Days)</small>
            </div>
            <div class="card-body">
                <canvas id="tempHumidityChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Water Parameters
            </div>
            <div class="card-body">
                <canvas id="waterChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear"></i> System Status
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6><i class="bi bi-cpu"></i> Device Status</h6>
                        <div class="d-flex align-items-center">
                            <div id="device-status" class="badge bg-secondary me-2">
                                <span class="placeholder-glow">
                                    <span class="placeholder col-8"></span>
                                </span>
                            </div>
                            <small>Last seen: <span id="last-seen">Loading...</span></small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="bi bi-window"></i> Curtain Status</h6>
                        <div id="curtain-status" class="badge bg-secondary">
                            <span class="placeholder-glow">
                                <span class="placeholder col-6"></span>
                            </span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-info" role="progressbar" id="curtain-progress" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="bi bi-fan"></i> Fan Status</h6>
                        <div id="fan-status" class="badge bg-secondary">
                            <span class="placeholder-glow">
                                <span class="placeholder col-4"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h6><i class="bi bi-droplet"></i> Water Pump</h6>
                        <div id="pump-status" class="badge bg-secondary">
                            <span class="placeholder-glow">
                                <span class="placeholder col-4"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connection Status -->
<div class="position-fixed bottom-0 end-0 p-3">
    <div id="connection-status" class="toast hide" role="alert">
        <div class="toast-header">
            <div class="rounded me-2 bg-success" style="width: 20px; height: 20px;"></div>
            <strong class="me-auto">Connection Status</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Dashboard is connected and updating.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Global variables
    let tempHumidityChart, waterChart;
    let currentDays = 7;
    let updateInterval;
    let isConnected = true;
    
    // Initialize charts
    function initCharts() {
        // Temperature & Humidity Chart
        const tempHumidityCtx = document.getElementById('tempHumidityChart').getContext('2d');
        tempHumidityChart = new Chart(tempHumidityCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Temperature (°C)',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Humidity (%)',
                        data: [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Temperature & Humidity History'
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Humidity (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Water Parameters Chart
        const waterCtx = document.getElementById('waterChart').getContext('2d');
        waterChart = new Chart(waterCtx, {
            type: 'bar',
            data: {
                labels: ['pH Level', 'Water Level (%)', 'CO2 (ppm/10)', 'Soil Moisture (%)'],
                datasets: [{
                    label: 'Current Values',
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    borderColor: [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 206, 86)',
                        'rgb(153, 102, 255)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Current Water & Environmental Parameters'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Values'
                        }
                    }
                }
            }
        });
    }

    // Show loading state
    function showLoading() {
        document.getElementById('loading-indicator').classList.remove('d-none');
    }

    // Hide loading state
    function hideLoading() {
        document.getElementById('loading-indicator').classList.add('d-none');
    }

    // Show error
    function showError(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-alert').classList.remove('d-none');
        isConnected = false;
    }

    // Hide error
    function hideError() {
        document.getElementById('error-alert').classList.add('d-none');
        isConnected = true;
    }

    // Update sensor status indicators
    function updateSensorStatus(data) {
        // Temperature status
        const temp = data.temperature;
        const tempStatus = document.getElementById('temp-status');
        if (temp < 20) {
            tempStatus.textContent = 'Too Cold - Below 20°C';
            tempStatus.className = 'text-warning';
        } else if (temp > 30) {
            tempStatus.textContent = 'Too Hot - Above 30°C';
            tempStatus.className = 'text-danger';
        } else {
            tempStatus.textContent = 'Normal range: 20-30°C';
            tempStatus.className = 'text-light';
        }

        // Humidity status
        const humidity = data.humidity;
        const humidityStatus = document.getElementById('humidity-status');
        if (humidity < 60) {
            humidityStatus.textContent = 'Too Dry - Below 60%';
            humidityStatus.className = 'text-warning';
        } else if (humidity > 80) {
            humidityStatus.textContent = 'Too Humid - Above 80%';
            humidityStatus.className = 'text-danger';
        } else {
            humidityStatus.textContent = 'Normal range: 60-80%';
            humidityStatus.className = 'text-light';
        }

        // pH status
        const ph = data.ph_value;
        const phStatus = document.getElementById('ph-status');
        if (ph < 6.0) {
            phStatus.textContent = 'Too Acidic - Below 6.0';
            phStatus.className = 'text-warning';
        } else if (ph > 7.5) {
            phStatus.textContent = 'Too Basic - Above 7.5';
            phStatus.className = 'text-warning';
        } else {
            phStatus.textContent = 'Normal range: 6.0-7.5';
            phStatus.className = 'text-light';
        }
    }

    // Update dashboard data
    function updateDashboard() {
        console.log('Updating dashboard...');
        
        // Show loading only on first load
        if (!document.getElementById('temperature-value').textContent.includes('°C')) {
            showLoading();
        }

        // Test API connection first
        axios.get('/api/test')
            .then(response => {
                console.log('API test successful:', response.data);
                hideError();
            })
            .catch(error => {
                console.error('API test failed:', error);
                showError('API connection failed');
                hideLoading();
                return;
            });

        // Fetch latest sensor data
        axios.get('/api/dashboard/latest-sensor-data')
            .then(response => {
                console.log('Sensor data response:', response.data);
                hideError();
                hideLoading();
                
                if (response.data.success && response.data.data.length > 0) {
                    const data = response.data.data[0];
                    console.log('Processing sensor data:', data);
                    
                    // Update main sensor values
                    document.getElementById('temperature-value').textContent = data.temperature.toFixed(1) + ' °C';
                    document.getElementById('humidity-value').textContent = data.humidity.toFixed(1) + ' %';
                    document.getElementById('ph-value').textContent = data.ph_value.toFixed(2);
                    document.getElementById('light-value').textContent = data.light_intensity + ' lux';
                    
                    // Update additional sensors (handle null values)
                    const waterLevel = data.water_level || 0;
                    const co2Level = data.co2_level || 0;
                    const soilMoisture = data.soil_moisture || 0;
                    
                    document.getElementById('water-level-value').textContent = waterLevel.toFixed(1) + '%';
                    document.getElementById('water-level-bar').style.width = waterLevel + '%';
                    
                    document.getElementById('co2-value').textContent = co2Level + ' ppm';
                    document.getElementById('soil-moisture-value').textContent = soilMoisture.toFixed(1) + '%';
                    document.getElementById('soil-moisture-bar').style.width = soilMoisture + '%';
                    
                    // Update water chart
                    waterChart.data.datasets[0].data = [
                        data.ph_value, 
                        waterLevel, 
                        co2Level / 10, // Scale down for better visualization
                        soilMoisture
                    ];
                    waterChart.update();
                    
                    // Update sensor status indicators
                    updateSensorStatus(data);
                    
                    // Update last seen
                    document.getElementById('last-seen').textContent = new Date(data.created_at).toLocaleString();
                    
                    console.log('Sensor data updated successfully');
                } else {
                    console.warn('No sensor data received or empty data array');
                }
            })
            .catch(error => {
                console.error('Error fetching sensor data:', error);
                console.error('Error details:', error.response);
                showError('Failed to load sensor data: ' + (error.message || 'Unknown error'));
                hideLoading();
            });
        
        // Fetch sensor history
        axios.get('/api/dashboard/sensor-history', { params: { days: currentDays } })
            .then(response => {
                console.log('History data response:', response.data);
                if (response.data.success && response.data.data.length > 0) {
                    const history = response.data.data;
                    const labels = history.map(item => item.date);
                    const temps = history.map(item => item.avg_temp);
                    const hums = history.map(item => item.avg_humidity);
                    
                    console.log('Updating temperature chart with:', { labels, temps, hums });
                    
                    tempHumidityChart.data.labels = labels;
                    tempHumidityChart.data.datasets[0].data = temps;
                    tempHumidityChart.data.datasets[1].data = hums;
                    tempHumidityChart.update();
                } else {
                    console.warn('No history data available');
                }
            })
            .catch(error => {
                console.error('Error fetching history data:', error);
            });
        
        // Fetch actuator status
        axios.get('/api/dashboard/actuator-status')
            .then(response => {
                console.log('Actuator status response:', response.data);
                if (response.data.success) {
                    const status = response.data.data;
                    
                    // Update curtain status
                    const curtainPos = status.curtain_position || 0;
                    document.getElementById('curtain-status').textContent = curtainPos + '% ' + 
                        (curtainPos > 50 ? 'Open' : 'Closed');
                    document.getElementById('curtain-progress').style.width = curtainPos + '%';
                    
                    // Update fan status
                    const fanElement = document.getElementById('fan-status');
                    fanElement.textContent = status.fan_status ? 'On' : 'Off';
                    fanElement.className = status.fan_status ? 'badge bg-success' : 'badge bg-secondary';
                    
                    // Update pump status
                    const pumpElement = document.getElementById('pump-status');
                    pumpElement.textContent = status.water_pump_status ? 'On' : 'Off';
                    pumpElement.className = status.water_pump_status ? 'badge bg-success' : 'badge bg-secondary';
                    
                    console.log('Actuator status updated successfully');
                } else {
                    console.warn('Failed to get actuator status');
                }
            })
            .catch(error => {
                console.error('Error fetching actuator status:', error);
                console.error('Error details:', error.response);
            });

        // Update device status
        document.getElementById('device-status').textContent = isConnected ? 'Online' : 'Offline';
        document.getElementById('device-status').className = isConnected ? 'badge bg-success me-2' : 'badge bg-danger me-2';
    }

    // Change time range for charts
    function changeTimeRange(days, label) {
        currentDays = days;
        document.getElementById('time-range').textContent = label;
        document.getElementById('chart-period').textContent = `(${label})`;
        updateDashboard();
    }

    // Export data function
    function exportData() {
        window.open('/api/dashboard/export?days=' + currentDays, '_blank');
    }

    // Initialize everything
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        updateDashboard();
        
        // Update data every 5 seconds
        updateInterval = setInterval(updateDashboard, 5000);
        
        // Show connection status
        setTimeout(() => {
            if (isConnected) {
                const toast = new bootstrap.Toast(document.getElementById('connection-status'));
                toast.show();
            }
        }, 2000);
    });

    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(updateInterval);
        } else {
            updateDashboard();
            updateInterval = setInterval(updateDashboard, 5000);
        }
    });
</script>
@endsection