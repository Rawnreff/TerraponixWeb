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
            <div class="dropdown">
                <button type="button" class="btn-modern btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar-week me-1"></i> 
                    <span id="time-range-label">Last week</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-modern shadow">
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(1, 'Last 24 hours')">
                        <i class="bi bi-calendar-day me-2"></i>Last 24 hours
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(3, 'Last 3 days')">
                        <i class="bi bi-calendar-week me-2"></i>Last 3 days
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(7, 'Last week')">
                        <i class="bi bi-calendar-week me-2"></i>Last week
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(30, 'Last month')">
                        <i class="bi bi-calendar-month me-2"></i>Last month
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="alert-modern alert alert-danger d-none mb-4" id="error-alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div class="flex-grow-1">
            <strong>Connection Error</strong>
            <div><span id="error-message">Unable to load data. Please check your connection.</span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>

<div class="loading-container d-none" id="loading-indicator">
    <div class="spinner-modern"></div>
    <p class="mt-3 text-muted">Loading sensor data...</p>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="sensor-card temperature-card">
            <div class="sensor-label">Temperature</div>
            <div class="sensor-value" id="temperature-value">-- °C</div>
            <div class="sensor-status" id="temp-status">Loading...</div>
            <div class="sensor-visualization">
                <div class="thermometer">
                    <div class="thermometer-bulb"></div>
                    <div class="thermometer-stem">
                        <div class="thermometer-fluid" id="temperature-visual"></div>
                    </div>
                </div>
            </div>
            <i class="bi bi-thermometer-half card-icon"></i>
            <div class="card-pulse temperature-pulse"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sensor-card humidity-card">
            <div class="sensor-label">Humidity</div>
            <div class="sensor-value" id="humidity-value">-- %</div>
            <div class="sensor-status" id="humidity-status">Loading...</div>
            <div class="sensor-visualization">
                <div class="humidity-droplet">
                    <div class="droplet" id="humidity-visual"></div>
                    <div class="ripples"></div>
                </div>
            </div>
            <i class="bi bi-droplet-half card-icon"></i>
            <div class="card-pulse humidity-pulse"></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sensor-card ph-card">
            <div class="sensor-label">pH Level</div>
            <div class="sensor-value" id="ph-value">--</div>
            <div class="sensor-status" id="ph-status">Loading...</div>
            <div class="sensor-visualization">
                <div class="ph-scale">
                    <div class="ph-indicator" id="ph-visual"></div>
                </div>
            </div>
            <i class="bi bi-speedometer2 card-icon"></i>
            <div class="card-pulse ph-pulse"></div>
        </div>
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
                <div class="sensor-visualization">
                    <div class="light-visual-container">
                        <div class="sun" id="light-visual"></div>
                        <div class="sun-rays"></div>
                    </div>
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
                    <div class="metric-status" id="water-status">Loading...</div>
                </div>
                <div class="sensor-visualization">
                    <div class="water-tank">
                        <div class="water-level" id="water-visual"></div>
                        <div class="water-waves"></div>
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
                    <div class="metric-status" id="co2-status">Optimal: 400-800 ppm</div>
                </div>
                <div class="sensor-visualization">
                    <div class="co2-cloud">
                        <div class="cloud" id="co2-visual"></div>
                    </div>
                </div>
                <div class="metric-label">Air Quality</div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="modern-card soil-moisture-card">
            <div class="card-header-modern">
                <i class="bi bi-moisture me-2"></i> Soil Moisture Visualization
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="soil-moisture-value">-- %</div>
                    <div class="metric-status" id="soil-moisture-status">Loading...</div>
                </div>
                <div class="soil-visualization-container">
                    <div class="soil-visualization">
                        <div class="soil-layer">
                            <div class="soil-moisture-level" id="soil-moisture-visual"></div>
                        </div>
                        <div class="grass-layer" id="grass-visual">
                            <div class="grass-blade"></div>
                            <div class="grass-blade"></div>
                            <div class="grass-blade"></div>
                            <div class="grass-blade"></div>
                            <div class="grass-blade"></div>
                        </div>
                    </div>
                </div>
                <div class="metric-label">Moisture Content</div>
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
                        <th>Water Level (%)</th>
                        <th>CO₂ (ppm)</th>
                        <th>Soil Moisture (%)</th>
                    </tr>
                </thead>
                <tbody id="sensor-data-table">
                    <tr>
                        <td colspan="8" class="text-center">Loading data...</td>
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
    color: white;
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
    box-shadow: var(--card-shadow);
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
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    height: 300px;
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

.sensor-card .sensor-visualization {
    height: 80px;
    margin-top: 0.5rem;
    position: relative;
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

/* Modern Card Styles */
.modern-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    overflow: hidden;
    height: 400px;
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
    margin-bottom: 0.5rem;
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
    margin-bottom: 0.5rem;
}

.metric-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
}

.sensor-visualization {
    margin: 1rem 0;
    height: 100px;
    position: relative;
}

/* Temperature Visualization */
.thermometer {
    width: 40px;
    height: 100px;
    margin: 0 auto;
    position: relative;
    bottom: 10px;
}

.thermometer-bulb {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e74c3c;
    position: absolute;
    bottom: 0;
    left: 0;
    z-index: 2;
}

.thermometer-stem {
    width: 20px;
    height: 80px;
    background: #f5f5f5;
    border-radius: 10px;
    position: absolute;
    top: 0;
    left: 10px;
    z-index: 1;
    overflow: hidden;
}

.thermometer-fluid {
    width: 100%;
    height: 0%;
    background: linear-gradient(to top, #e74c3c, #c0392b);
    position: absolute;
    bottom: 0;
    left: 0;
    transition: height 0.5s ease;
}

/* Humidity Visualization */
.humidity-droplet {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    position: relative;
}

.droplet {
    width: 60px;
    height: 60px;
    background: #3498db;
    border-radius: 50% 50% 50% 50%/60% 60% 40% 40%;
    position: absolute;
    top: 10px;
    left: 10px;
    transform: scale(0.5);
    transition: transform 0.5s ease;
}

.ripples {
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 2px solid rgba(52, 152, 219, 0.5);
    top: 0;
    left: 0;
    animation: ripple 2s infinite;
}

@keyframes ripple {
    0% { transform: scale(0.5); opacity: 1; }
    100% { transform: scale(1); opacity: 0; }
}

/* pH Visualization */
.ph-scale {
    width: 100%;
    height: 20px;
    background: linear-gradient(to right, #ff0000, #ff9900, #ccff00, #33cc33, #0099ff, #6633cc, #9900cc);
    border-radius: 10px;
    margin: 30px auto 0;
    position: relative;
}

.ph-indicator {
    width: 20px;
    height: 30px;
    background: #fff;
    border-radius: 3px;
    position: absolute;
    top: -5px;
    left: 50%;
    transform: translateX(-50%);
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
    transition: left 0.5s ease;
}

/* Light Visualization */
.light-visual-container {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    position: relative;
}

.sun {
    width: 50px;
    height: 50px;
    background: #f39c12;
    border-radius: 50%;
    position: absolute;
    top: 25px;
    left: 25px;
    box-shadow: 0 0 20px #f39c12;
    transition: all 0.5s ease;
}

.sun-rays {
    position: absolute;
    width: 100px;
    height: 100px;
    top: 0;
    left: 0;
}

.sun-rays:before, .sun-rays:after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    background: #f39c12;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
}

.sun-rays:after {
    transform: translateY(-50%) rotate(90deg);
}

/* Water Level Visualization */
.water-tank {
    width: 80px;
    height: 100px;
    margin: 0 auto;
    background: #f5f5f5;
    border-radius: 5px;
    position: relative;
    overflow: hidden;
}

.water-level {
    width: 100%;
    height: 0%;
    background: linear-gradient(to top, #1abc9c, #16a085);
    position: absolute;
    bottom: 0;
    left: 0;
    transition: height 0.5s ease;
}

.water-waves {
    position: absolute;
    width: 200%;
    height: 10px;
    background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 120 10" xmlns="http://www.w3.org/2000/svg"><path d="M0,5 Q30,10 60,5 T120,5" fill="none" stroke="%2316a085" stroke-width="2"/></svg>') repeat-x;
    background-size: 60px 10px;
    bottom: 0;
    left: 0;
    animation: water-wave 2s linear infinite;
}

@keyframes water-wave {
    0% { transform: translateX(0); }
    100% { transform: translateX(-60px); }
}

/* CO2 Visualization */
.co2-cloud {
    width: 100px;
    height: 60px;
    margin: 20px auto 0;
    position: relative;
}

.cloud {
    width: 60px;
    height: 40px;
    background: #ecf0f1;
    border-radius: 40px;
    position: absolute;
    top: 10px;
    left: 20px;
    transition: all 0.5s ease;
}

.cloud:before, .cloud:after {
    content: '';
    position: absolute;
    background: #ecf0f1;
    border-radius: 50%;
}

.cloud:before {
    width: 30px;
    height: 30px;
    top: -15px;
    left: -10px;
}

.cloud:after {
    width: 40px;
    height: 40px;
    top: -20px;
    right: -10px;
}

/* Soil Moisture Visualization */
.soil-moisture-card {
    height: 364px;
}

.soil-visualization-container {
    height: 150px;
    margin: 1rem 0;
    position: relative;
}

.soil-visualization {
    width: 100%;
    height: 120px;
    position: relative;
    overflow: hidden;
}

.soil-layer {
    width: 100%;
    height: 100%;
    background: #8B4513;
    position: absolute;
    bottom: 0;
    left: 0;
    border-radius: 5px;
    overflow: hidden;
}

.soil-moisture-level {
    width: 100%;
    height: 0%;
    background: linear-gradient(to top, #5D4037, #8D6E63);
    position: absolute;
    bottom: 0;
    left: 0;
    transition: height 0.5s ease;
}

.grass-layer {
    width: 100%;
    height: 40px;
    position: absolute;
    bottom: 0;
    left: 0;
    display: flex;
    justify-content: space-around;
    padding: 0 20px;
}

.grass-blade {
    width: 4px;
    height: 0;
    background: linear-gradient(to top, #4CAF50, #8BC34A);
    border-radius: 2px;
    position: relative;
    transform-origin: bottom center;
    animation: grass-grow 1s ease-out forwards;
}

.grass-blade:nth-child(1) { animation-delay: 0.1s; }
.grass-blade:nth-child(2) { animation-delay: 0.3s; }
.grass-blade:nth-child(3) { animation-delay: 0.2s; }
.grass-blade:nth-child(4) { animation-delay: 0.4s; }
.grass-blade:nth-child(5) { animation-delay: 0.25s; }

@keyframes grass-grow {
    0% { height: 0; transform: scaleY(0); }
    80% { transform: scaleY(1.1); }
    100% { height: 30px; transform: scaleY(1); }
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
    
    .sensor-card, .modern-card {
        height: auto;
        min-height: 200px;
    }
    
    .sensor-card .sensor-value, .metric-value {
        font-size: 1.8rem;
    }
    
    .soil-moisture-card {
        height: auto;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Global variables
    let currentDays = 7;
    let updateInterval;
    let isConnected = true;
    
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

        // Water Level status
        const waterLevel = data.water_level ? (data.water_level / 2000 * 100) : 0;
        const waterStatus = document.getElementById('water-status');
        if (waterLevel < 20) {
            waterStatus.textContent = 'Very Low - Below 20%';
            waterStatus.style.color = '#e74c3c';
        } else if (waterLevel < 40) {
            waterStatus.textContent = 'Low - Below 40%';
            waterStatus.style.color = '#e67e22';
        } else if (waterLevel > 80) {
            waterStatus.textContent = 'High - Above 80%';
            waterStatus.style.color = '#27ae60';
        } else {
            waterStatus.textContent = 'Normal: 40-80%';
            waterStatus.style.color = '#27ae60';
        }

        // Soil Moisture status
        const soilMoisture = data.soil_moisture ? (data.soil_moisture / 4095 * 100) : 0;
        const soilMoistureStatus = document.getElementById('soil-moisture-status');
        if (soilMoisture < 30) {
            soilMoistureStatus.textContent = 'Too Dry - Below 30%';
            soilMoistureStatus.style.color = '#e67e22';
        } else if (soilMoisture > 70) {
            soilMoistureStatus.textContent = 'Too Wet - Above 70%';
            soilMoistureStatus.style.color = '#e67e22';
        } else {
            soilMoistureStatus.textContent = 'Optimal: 30-70%';
            soilMoistureStatus.style.color = '#27ae60';
        }
    }

    // Update visualizations
    function updateVisualizations(data) {
        // Temperature visualization (0-50°C range)
        const temp = data.temperature;
        const tempPercentage = Math.min(Math.max((temp / 50) * 100, 0), 100);
        document.getElementById('temperature-visual').style.height = tempPercentage + '%';

        // Humidity visualization
        const humidity = data.humidity;
        document.getElementById('humidity-visual').style.transform = `scale(${humidity / 100})`;

        // pH visualization (0-14 scale)
        const ph = data.ph_value;
        const phPosition = Math.min(Math.max((ph / 14) * 100, 0), 100);
        document.getElementById('ph-visual').style.left = phPosition + '%';

        // Light visualization
        const light = data.light_intensity;
        const lightScale = Math.min(Math.max(light / 1000, 0.3), 1);
        document.getElementById('light-visual').style.transform = `scale(${lightScale})`;
        document.getElementById('light-visual').style.boxShadow = `0 0 ${20 * lightScale}px #f39c12`;

        // Water Level visualization
        const waterLevel = data.water_level ? (data.water_level / 2000 * 100) : 0;
        document.getElementById('water-visual').style.height = waterLevel + '%';

        // CO2 visualization (400-1200 ppm range)
        const co2 = data.co2_level;
        const co2Scale = Math.min(Math.max((co2 - 400) / 800, 0.5), 1.5);
        document.getElementById('co2-visual').style.transform = `scale(${co2Scale})`;

        // Soil Moisture visualization
        const soilMoisture = data.soil_moisture ? (data.soil_moisture / 4095 * 100) : 0;
        document.getElementById('soil-moisture-visual').style.height = soilMoisture + '%';
        
        // Grass visualization
        const grassBlades = document.querySelectorAll('.grass-blade');
        grassBlades.forEach(blade => {
            if (soilMoisture > 30) {
                blade.style.height = '30px';
                blade.style.animation = 'grass-grow 1s ease-out forwards';
            } else {
                blade.style.height = '10px';
                blade.style.animation = 'none';
            }
        });
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
                        const waterLevelPercentage = data.water_level ? ((data.water_level / 2000) * 100).toFixed(1) : '--';
                        const soilMoisturePercentage = data.soil_moisture ? (data.soil_moisture / 4095 * 100).toFixed(1) : '--';
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${new Date(data.created_at).toLocaleString()}</td>
                            <td>${data.temperature?.toFixed(1) || '--'}</td>
                            <td>${data.humidity?.toFixed(1) || '--'}</td>
                            <td>${data.ph_value?.toFixed(2) || '--'}</td>
                            <td>${data.light_intensity || '--'}</td>
                            <td>${waterLevelPercentage}%</td>
                            <td>${data.co2_level || '--'}</td>
                            <td>${soilMoisturePercentage}%</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    // Get the latest reading (first item in array)
                    const latest = sensorData[0];
                    const waterLevelPercentage = latest.water_level ? ((latest.water_level / 2000) * 100).toFixed(1) : 0;
                    const soilMoisturePercentage = latest.soil_moisture ? (latest.soil_moisture / 4095 * 100).toFixed(1) : 0;
                    
                    // Update summary cards
                    animateValue('temperature-value', latest.temperature?.toFixed(1) + ' °C');
                    animateValue('humidity-value', latest.humidity?.toFixed(1) + ' %');
                    animateValue('ph-value', latest.ph_value?.toFixed(2) || '--');
                    animateValue('light-value', latest.light_intensity?.toFixed(0) + ' lux' || '--');
                    animateValue('water-level-value', waterLevelPercentage + '%' || '--');
                    animateValue('co2-value', latest.co2_level?.toFixed(0) + ' ppm' || '--');
                    animateValue('soil-moisture-value', soilMoisturePercentage + '%' || '--');

                    // Update sensor status indicators
                    updateSensorStatus(latest);
                    
                    // Update visualizations
                    updateVisualizations(latest);
                    
                    // Update last seen
                    document.getElementById('last-seen').textContent = 'Last updated: ' + new Date(latest.created_at).toLocaleString();
                    
                } else {
                    document.getElementById('sensor-data-table').innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">No data available</td>
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
                        <td colspan="8" class="text-center text-danger">Error loading data</td>
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

    // Change time range for data
    function changeTimeRange(days, label) {
        currentDays = days;
        document.getElementById('time-range-label').textContent = label;
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