@extends('layouts.app')

@section('content')
<div class="dashboard-header p-4 mb-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
        <h1 class="dashboard-title h2"><i class="bi bi-graph-up me-2"></i> Sensor Data Monitoring</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn-modern btn-primary" onclick="exportData()">
                    <i class="bi bi-download me-1"></i> Export
                </button>
            </div>
            <div class="input-group">
                <select class="form-select form-select-sm" id="days-filter">
                    <option value="1">Last 1 day</option>
                    <option value="3">Last 3 days</option>
                    <option value="7" selected>Last week</option>
                    <option value="30">Last month</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="alert-modern alert alert-danger d-none mb-4" id="error-alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <span id="error-message"></span>
</div>

<div class="loading-container d-none" id="loading-indicator">
    <div class="spinner-modern"></div>
    <div class="mt-3 placeholder-shimmer"></div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="sensor-card temperature-card">
            <div class="sensor-label">Temperature</div>
            <div class="sensor-value" id="temperature-value">-- °C</div>
            <div class="sensor-status" id="temp-status">Loading...</div>
            <i class="bi bi-thermometer-half card-icon"></i>
            <div class="card-pulse temperature-pulse"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sensor-card humidity-card">
            <div class="sensor-label">Humidity</div>
            <div class="sensor-value" id="humidity-value">-- %</div>
            <div class="sensor-status" id="humidity-status">Loading...</div>
            <i class="bi bi-droplet-half card-icon"></i>
            <div class="card-pulse humidity-pulse"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sensor-card ph-card">
            <div class="sensor-label">pH Level</div>
            <div class="sensor-value" id="ph-value">--</div>
            <div class="sensor-status" id="ph-status">Loading...</div>
            <i class="bi bi-speedometer2 card-icon"></i>
            <div class="card-pulse ph-pulse"></div>
        </div>
    </div>
</div>

<div class="chart-card mb-4">
    <div class="chart-header">
        <h5 class="chart-title">
            <i class="bi bi-graph-up me-2"></i> Sensor Data History
            <span class="chart-period ms-2" id="time-range">(Last week)</span>
        </h5>
        <div class="dropdown">
            <button class="btn-modern btn-outline-secondary dropdown-toggle" type="button" id="chartRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Time Range
            </button>
            <ul class="dropdown-menu dropdown-menu-modern" aria-labelledby="chartRangeDropdown">
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(1, 'Last 24 hours')">Last 24 hours</a></li>
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(3, 'Last 3 days')">Last 3 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(7, 'Last week')">Last week</a></li>
                <li><a class="dropdown-item" href="#" onclick="changeTimeRange(30, 'Last month')">Last month</a></li>
            </ul>
        </div>
    </div>
    <div class="chart-body">
        <canvas id="sensorHistoryChart" height="100"></canvas>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-brightness-high me-2"></i> Light Intensity
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="light-value">-- lux</div>
                    <div class="metric-status" id="light-status">Loading...</div>
                </div>
                <div class="metric-label">Current Light Level</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-water me-2"></i> Water Level
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="water-level-value">-- %</div>
                    <div class="progress-modern mt-2">
                        <div class="progress-bar-modern water-progress" id="water-level-bar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="metric-label">Tank Capacity</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-cloud me-2"></i> CO₂ Level
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="co2-value">-- ppm</div>
                    <div class="metric-status">Optimal: 400-800 ppm</div>
                </div>
                <div class="metric-label">Air Quality</div>
            </div>
        </div>
    </div>
</div>

<div class="chart-card">
    <div class="chart-header">
        <h5 class="chart-title">
            <i class="bi bi-table me-2"></i> Latest Sensor Data
        </h5>
        <div class="text-muted small" id="last-seen">Last updated: --</div>
    </div>
    <div class="chart-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Temperature (°C)</th>
                        <th>Humidity (%)</th>
                        <th>pH</th>
                        <th>Light (lux)</th>
                        <th>Water Level</th>
                        <th>Soil Moisture</th>
                    </tr>
                </thead>
                <tbody id="sensor-data-table">
                    <tr>
                        <td colspan="7" class="text-center">Loading data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Connection Status Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="connection-status" class="toast-modern toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <span class="connection-dot bg-success"></span>
            <strong class="me-auto">System Status</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Connected to sensor network successfully
        </div>
    </div>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    
    --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
    --card-hover-shadow: 0 20px 40px rgba(0,0,0,0.15);
    --border-radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.dashboard-header {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 2rem;
    box-shadow: var(--card-shadow);
}

.dashboard-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.btn-modern {
    border-radius: 12px;
    padding: 0.6rem 1.2rem;
    font-weight: 500;
    transition: var(--transition);
    border: none;
    position: relative;
    overflow: hidden;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.dropdown-menu-modern {
    border: none;
    border-radius: 12px;
    padding: 0.5rem;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.dropdown-menu-modern .dropdown-item {
    border-radius: 8px;
    padding: 0.6rem 1rem;
    transition: var(--transition);
}

.dropdown-menu-modern .dropdown-item:hover {
    background: var(--primary-gradient);
    color: white;
    transform: translateX(5px);
}

/* Loading Styles */
.loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
    flex-direction: column;
}

.spinner-modern {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(102, 126, 234, 0.1);
    border-left: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.placeholder-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    height: 1.5rem;
    border-radius: 4px;
    width: 80px;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* Alert Styles */
.alert-modern {
    border: none;
    border-radius: var(--border-radius);
    backdrop-filter: blur(10px);
    background: rgba(248, 215, 218, 0.9);
    box-shadow: var(--card-shadow);
}

/* Sensor Card Styles */
.sensor-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    height: 160px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.sensor-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--card-hover-shadow);
}

.sensor-card .card-icon {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    font-size: 2rem;
    opacity: 0.6;
}

.sensor-card .sensor-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.sensor-card .sensor-value {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.sensor-card .sensor-status {
    font-size: 0.8rem;
    font-weight: 500;
    opacity: 0.8;
}

.card-pulse {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 4px;
    width: 100%;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
    animation: pulse 2s infinite;
}

.temperature-card .card-icon { color: #e74c3c; }
.temperature-pulse { background: var(--danger-gradient); }

.humidity-card .card-icon { color: #3498db; }
.humidity-pulse { background: var(--info-gradient); }

.ph-card .card-icon { color: #9b59b6; }
.ph-pulse { background: var(--primary-gradient); }

.light-card .card-icon { color: #f39c12; }
.light-pulse { background: var(--warning-gradient); }

@keyframes pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

/* Modern Card Styles */
.modern-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    overflow: hidden;
    height: 200px;
}

.modern-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow);
}

.card-header-modern {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.card-body-modern {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: calc(100% - 70px);
}

.metric-display {
    text-align: center;
    margin-bottom: auto;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.metric-status {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.metric-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
}

/* Progress Bar Styles */
.progress-modern {
    height: 8px;
    background: rgba(0,0,0,0.05);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}

.progress-bar-modern {
    height: 100%;
    border-radius: 20px;
    transition: width 0.6s ease;
    position: relative;
}

.water-progress {
    background: var(--info-gradient);
}

.soil-progress {
    background: var(--success-gradient);
}

.curtain-progress {
    background: var(--primary-gradient);
}

/* Chart Styles */
.chart-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    overflow: hidden;
    margin-bottom: 2rem;
}

.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow);
}

.chart-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(248, 249, 250, 0.5);
}

.chart-title {
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-period {
    font-size: 0.85rem;
    color: #6c757d;
}

.chart-body {
    padding: 1.5rem;
}

/* Toast Styles */
.toast-modern {
    border: none;
    border-radius: var(--border-radius);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
    box-shadow: var(--card-shadow);
}

.connection-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 0.5rem;
}

/* Table Styles */
.table-responsive {
    border-radius: var(--border-radius);
    overflow: hidden;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    border-bottom-width: 1px;
    font-weight: 600;
    color: #2c3e50;
    background: rgba(248, 249, 250, 0.7);
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        margin: 1rem;
        padding: 1rem;
    }
    
    .sensor-card, .modern-card, .control-card {
        height: auto;
        min-height: 120px;
    }
    
    .sensor-card .sensor-value, .metric-value {
        font-size: 1.8rem;
    }
    
    .chart-card {
        margin-bottom: 1.5rem;
    }
    
    .chart-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Global variables
    let sensorHistoryChart;
    let currentDays = 7;
    let updateInterval;
    let isConnected = true;
    
    // Chart color schemes
    const chartColors = {
        temperature: {
            border: '#e74c3c',
            background: 'rgba(231, 76, 60, 0.1)',
            gradient: ['#e74c3c', '#c0392b']
        },
        humidity: {
            border: '#3498db',
            background: 'rgba(52, 152, 219, 0.1)',
            gradient: ['#3498db', '#2980b9']
        },
        ph: {
            border: '#9b59b6',
            background: 'rgba(155, 89, 182, 0.1)',
            gradient: ['#9b59b6', '#8e44ad']
        },
        waterLevel: {
            border: '#1abc9c',
            background: 'rgba(26, 188, 156, 0.1)',
            gradient: ['#1abc9c', '#16a085']
        },
        light: {
            border: '#f39c12',
            background: 'rgba(243, 156, 18, 0.1)',
            gradient: ['#f39c12', '#e67e22']
        }
    };
    
    // Initialize charts
    function initCharts() {
        const ctx = document.getElementById('sensorHistoryChart').getContext('2d');
        sensorHistoryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Temperature (°C)',
                        data: [],
                        borderColor: chartColors.temperature.border,
                        backgroundColor: chartColors.temperature.background,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: chartColors.temperature.border,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Humidity (%)',
                        data: [],
                        borderColor: chartColors.humidity.border,
                        backgroundColor: chartColors.humidity.background,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: chartColors.humidity.border,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'pH Level',
                        data: [],
                        borderColor: chartColors.ph.border,
                        backgroundColor: chartColors.ph.background,
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: chartColors.ph.border,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#6c757d',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            title: function(context) {
                                return 'Date: ' + context[0].label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'Temperature (°C)',
                            color: '#6c757d'
                        }
                    },
                    y1: {
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'Humidity (%)',
                            color: '#6c757d'
                        }
                    },
                    y2: {
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'pH Level',
                            color: '#6c757d'
                        },
                        min: 0,
                        max: 14
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Show loading state with modern animation
    function showLoading() {
        document.getElementById('loading-indicator').classList.remove('d-none');
    }

    // Hide loading state
    function hideLoading() {
        document.getElementById('loading-indicator').classList.add('d-none');
    }

    // Show error with modern styling
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

    // Update sensor status indicators with modern styling
    function updateSensorStatus(data) {
        // Temperature status
        const temp = data.temperature;
        const tempStatus = document.getElementById('temp-status');
        if (temp < 20) {
            tempStatus.textContent = 'Too Cold - Below 20°C';
            tempStatus.style.color = '#e67e22';
        } else if (temp > 30) {
            tempStatus.textContent = 'Too Hot - Above 30°C';
            tempStatus.style.color = '#e74c3c';
        } else {
            tempStatus.textContent = 'Normal: 20-30°C';
            tempStatus.style.color = '#27ae60';
        }

        // Humidity status
        const humidity = data.humidity;
        const humidityStatus = document.getElementById('humidity-status');
        if (humidity < 60) {
            humidityStatus.textContent = 'Too Dry - Below 60%';
            humidityStatus.style.color = '#e67e22';
        } else if (humidity > 80) {
            humidityStatus.textContent = 'Too Humid - Above 80%';
            humidityStatus.style.color = '#e74c3c';
        } else {
            humidityStatus.textContent = 'Normal: 60-80%';
            humidityStatus.style.color = '#27ae60';
        }

        // pH status
        const ph = data.ph_value;
        const phStatus = document.getElementById('ph-status');
        if (ph < 6.0) {
            phStatus.textContent = 'Too Acidic - Below 6.0';
            phStatus.style.color = '#e67e22';
        } else if (ph > 7.5) {
            phStatus.textContent = 'Too Basic - Above 7.5';
            phStatus.style.color = '#e67e22';
        } else {
            phStatus.textContent = 'Normal: 6.0-7.5';
            phStatus.style.color = '#27ae60';
        }

        // Light status
        const light = data.light_intensity;
        const lightStatus = document.getElementById('light-status');
        if (light < 400) {
            lightStatus.textContent = 'Too Dark - Below 400 lux';
            lightStatus.style.color = '#e67e22';
        } else if (light > 800) {
            lightStatus.textContent = 'Too Bright - Above 800 lux';
            lightStatus.style.color = '#e67e22';
        } else {
            lightStatus.textContent = 'Normal: 400-800 lux';
            lightStatus.style.color = '#27ae60';
        }
    }

    // Update dashboard data
        function updateDashboard() {
            console.log('Updating dashboard...');
            showLoading();

            // Fetch latest sensor data
            axios.get('/api/sensor-data/latest')        
                .then(response => {
                    console.log('Sensor data response:', response.data);
                    hideError();
                    hideLoading();
                    
                    if (response.data.status === "success" && response.data.data && response.data.data.length > 0) {
                        const sensorData = response.data.data;
                        const tableBody = document.getElementById('sensor-data-table');
                        
                        // Clear table
                        tableBody.innerHTML = '';
                        
                        // Populate table with all returned data
                        sensorData.forEach(data => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${new Date(data.created_at).toLocaleString()}</td>
                                <td>${data.temperature?.toFixed(1) || '--'}</td>
                                <td>${data.humidity?.toFixed(1) || '--'}</td>
                                <td>${data.ph_value?.toFixed(2) || '--'}</td>
                                <td>${data.light_intensity || '--'}</td>
                                <td>${data.water_level || '--'}</td>
                                <td>${data.soil_moisture || '--'}</td>
                            `;
                            tableBody.appendChild(row);
                        });

                        // Get the latest reading (first item in array)
                        const latest = sensorData[0];
                        
                        // Update summary cards
                        animateValue('temperature-value', latest.temperature?.toFixed(1) + ' °C');
                        animateValue('humidity-value', latest.humidity?.toFixed(1) + ' %');
                        animateValue('ph-value', latest.ph_value?.toFixed(2) || '--');
                        animateValue('light-value', latest.light_intensity?.toFixed(0) + ' lux' || '--');
                        animateValue('water-level-value', latest.water_level?.toFixed(0) + '%' || '--');
                        animateProgressBar('water-level-bar', latest.water_level || 0);
                        animateValue('soil-moisture-value', latest.soil_moisture?.toFixed(0) + '%' || '--');

                        // Update sensor status indicators
                        updateSensorStatus(latest);
                        
                        // Update last seen
                        document.getElementById('last-seen').textContent = 'Last updated: ' + new Date(latest.created_at).toLocaleString();
                        
                    } else {
                        document.getElementById('sensor-data-table').innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center">No data available</td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching sensor data:', error);
                    showError('Failed to load sensor data: ' + (error.message || 'Unknown error'));
                    hideLoading();
                    document.getElementById('sensor-data-table').innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-danger">Error loading data</td>
                        </tr>
                    `;
                });
        }

    // Animate value changes
    function animateValue(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.transform = 'scale(1.05)';
            setTimeout(() => {
                element.textContent = newValue;
                element.style.transform = 'scale(1)';
            }, 150);
        }
    }

    // Animate progress bars
    function animateProgressBar(elementId, percentage) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.width = percentage + '%';
        }
    }

    // Change time range for charts
    function changeTimeRange(days, label) {
        currentDays = days;
        document.getElementById('time-range').textContent = `(${label})`;
        updateDashboard();
    }

    // Export data function
    function exportData() {
        // Add loading state to export button
        const exportBtn = document.querySelector('[onclick="exportData()"]');
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Exporting...';
        exportBtn.disabled = true;
        
        // Simulate export process
        setTimeout(() => {
            window.open('/api/dashboard/export?days=' + currentDays, '_blank');
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
        }, 1000);
    }

    // Initialize everything
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        updateDashboard();
        
        // Update data every 5 seconds
        updateInterval = setInterval(updateDashboard, 5000);
        
        // Show connection status with delay
        setTimeout(() => {
            if (isConnected) {
                const toast = new bootstrap.Toast(document.getElementById('connection-status'));
                toast.show();
            }
        }, 3000);
        
        // Handle time filter change
        document.getElementById('days-filter').addEventListener('change', function() {
            const days = parseInt(this.value);
            let label = 'Last week';
            if (days === 1) label = 'Last 24 hours';
            else if (days === 3) label = 'Last 3 days';
            else if (days === 30) label = 'Last month';
            
            changeTimeRange(days, label);
        });
    });

    // Handle page visibility change for performance
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