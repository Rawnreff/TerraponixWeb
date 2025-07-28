@extends('layouts.app')

@section('content')
<!-- Modern Header with Glass Effect -->
<div class="dashboard-header">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-2 p-4">
        <div>
            <h1 class="dashboard-title">
                <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                Smart Greenhouse Dashboard
            </h1>
            <p class="text-muted mb-0">Real-time monitoring and control system</p>
        </div>
        <div class="btn-toolbar">
            <div class="btn-group me-3">
                <button type="button" class="btn btn-outline-primary btn-modern" onclick="exportData()">
                    <i class="bi bi-download me-1"></i> Export Data
                </button>
            </div>
            <div class="dropdown">
                <button type="button" class="btn btn-primary btn-modern dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar-week me-1"></i> 
                    <span id="time-range">This week</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-modern shadow">
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(1, 'Today')">
                        <i class="bi bi-calendar-day me-2"></i>Today
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(7, 'This week')">
                        <i class="bi bi-calendar-week me-2"></i>This week
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="changeTimeRange(30, 'This month')">
                        <i class="bi bi-calendar-month me-2"></i>This month
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loading-indicator" class="d-none">
    <div class="loading-container">
        <div class="loading-spinner">
            <div class="spinner-modern"></div>
            <p class="mt-3 text-muted">Loading dashboard data...</p>
        </div>
    </div>
</div>

<!-- Error Alert -->
<div id="error-alert" class="alert alert-danger alert-modern d-none" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div class="flex-grow-1">
            <strong>Connection Error</strong>
            <div><span id="error-message">Unable to load data. Please check your connection.</span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>

<!-- Main Sensor Cards -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="sensor-card temperature-card">
            <div class="card-icon">
                <i class="bi bi-thermometer-half"></i>
            </div>
            <div class="card-content">
                <div class="sensor-label">Temperature</div>
                <div class="sensor-value" id="temperature-value">
                    <div class="placeholder-shimmer"></div>
                </div>
                <div class="sensor-status" id="temp-status">Normal: 20-30°C</div>
            </div>
            <div class="card-pulse temperature-pulse"></div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="sensor-card humidity-card">
            <div class="card-icon">
                <i class="bi bi-droplet-half"></i>
            </div>
            <div class="card-content">
                <div class="sensor-label">Humidity</div>
                <div class="sensor-value" id="humidity-value">
                    <div class="placeholder-shimmer"></div>
                </div>
                <div class="sensor-status" id="humidity-status">Normal: 60-80%</div>
            </div>
            <div class="card-pulse humidity-pulse"></div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="sensor-card ph-card">
            <div class="card-icon">
                <i class="bi bi-flask"></i>
            </div>
            <div class="card-content">
                <div class="sensor-label">pH Level</div>
                <div class="sensor-value" id="ph-value">
                    <div class="placeholder-shimmer"></div>
                </div>
                <div class="sensor-status" id="ph-status">Normal: 6.0-7.5</div>
            </div>
            <div class="card-pulse ph-pulse"></div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="sensor-card light-card">
            <div class="card-icon">
                <i class="bi bi-brightness-high"></i>
            </div>
            <div class="card-content">
                <div class="sensor-label">Light Intensity</div>
                <div class="sensor-value" id="light-value">
                    <div class="placeholder-shimmer"></div>
                </div>
                <div class="sensor-status" id="light-status">Normal: 400-800 lux</div>
            </div>
            <div class="card-pulse light-pulse"></div>
        </div>
    </div>
</div>

<!-- Additional Environmental Cards -->
<div class="row g-4 mb-5">
    <div class="col-lg-4 col-md-6">
        <div class="modern-card water-level-card">
            <div class="card-header-modern">
                <i class="bi bi-water text-primary"></i>
                <span>Water Level</span>
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="water-level-value">
                        <div class="placeholder-shimmer"></div>
                    </div>
                </div>
                <div class="progress-modern mt-3">
                    <div class="progress-bar-modern water-progress" id="water-level-bar"></div>
                </div>
                <div class="metric-label">Reservoir Status</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="modern-card co2-card">
            <div class="card-header-modern">
                <i class="bi bi-cloud text-warning"></i>
                <span>CO₂ Level</span>
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="co2-value">
                        <div class="placeholder-shimmer"></div>
                    </div>
                </div>  
                <div class="metric-label">Air Quality</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6">
        <div class="modern-card soil-card">
            <div class="card-header-modern">
                <i class="bi bi-moisture text-success"></i>
                <span>Soil Moisture</span>
            </div>
            <div class="card-body-modern">
                <div class="metric-display">
                    <div class="metric-value" id="soil-moisture-value">
                        <div class="placeholder-shimmer"></div>
                    </div>
                </div>
                <div class="progress-modern mt-3">
                    <div class="progress-bar-modern soil-progress" id="soil-moisture-bar"></div>
                </div>
                <div class="metric-label">Moisture Content</div>
            </div>
        </div>
    </div>
</div>

<!-- Individual Charts Section -->
<div class="charts-section mb-5">
    <div class="section-header mb-4">
        <h3 class="section-title">
            <i class="bi bi-graph-up text-primary me-2"></i>
            Historical Data Analysis
        </h3>
        <p class="text-muted">Track environmental parameters over time</p>
    </div>
    
    <div class="row g-4">
        <!-- Temperature Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="bi bi-thermometer-half text-danger"></i>
                        Temperature History
                    </div>
                    <div class="chart-period" id="temp-chart-period">(7 Days)</div>
                </div>
                <div class="chart-body">
                    <canvas id="temperatureChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Humidity Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="bi bi-droplet-half text-info"></i>
                        Humidity History
                    </div>
                    <div class="chart-period" id="humidity-chart-period">(7 Days)</div>
                </div>
                <div class="chart-body">
                    <canvas id="humidityChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- pH Level Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="bi bi-flask text-primary"></i>
                        pH Level History
                    </div>
                    <div class="chart-period" id="ph-chart-period">(7 Days)</div>
                </div>
                <div class="chart-body">
                    <canvas id="phChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Water Level Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="bi bi-water text-primary"></i>
                        Water Level History
                    </div>
                    <div class="chart-period" id="water-chart-period">(7 Days)</div>
                </div>
                <div class="chart-body">
                    <canvas id="waterLevelChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- CO2 Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="bi bi-cloud text-warning"></i>
                        CO₂ Level History
                    </div>
                    <div class="chart-period" id="co2-chart-period">(7 Days)</div>
                </div>
                <div class="chart-body">
                    <canvas id="co2Chart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Soil Moisture Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <i class="bi bi-moisture text-success"></i>
                        Soil Moisture History
                    </div>
                    <div class="chart-period" id="soil-chart-period">(7 Days)</div>
                </div>
                <div class="chart-body">
                    <canvas id="soilMoistureChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Control Panel -->
<div class="control-panel mb-5">
    <div class="section-header mb-4">
        <h3 class="section-title">
            <i class="bi bi-gear text-primary me-2"></i>
            System Status & Control
        </h3>
        <p class="text-muted">Monitor and control greenhouse automation systems</p>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="control-card">
                <div class="control-header">
                    <i class="bi bi-cpu"></i>
                    <span>Device Status</span>
                </div>
                <div class="control-body">
                    <div class="status-indicator">
                        <div id="device-status" class="status-badge">
                            <div class="placeholder-shimmer"></div>
                        </div>
                    </div>
                    <div class="status-info">
                        <small>Last seen: <span id="last-seen">Loading...</span></small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="control-card">
                <div class="control-header">
                    <i class="bi bi-window"></i>
                    <span>Curtain Control</span>
                </div>
                <div class="control-body">
                    <div class="status-indicator">
                        <div id="curtain-status" class="status-badge">
                            <div class="placeholder-shimmer"></div>
                        </div>
                    </div>
                    <div class="progress-modern mt-2">
                        <div class="progress-bar-modern curtain-progress" id="curtain-progress"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="control-card">
                <div class="control-header">
                    <i class="bi bi-fan"></i>
                    <span>Ventilation</span>
                </div>
                <div class="control-body">
                    <div class="status-indicator">
                        <div id="fan-status" class="status-badge">
                            <div class="placeholder-shimmer"></div>
                        </div>
                    </div>
                    <div class="status-info">
                        <small>Air circulation system</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="control-card">
                <div class="control-header">
                    <i class="bi bi-droplet"></i>
                    <span>Water Pump</span>
                </div>
                <div class="control-body">
                    <div class="status-indicator">
                        <div id="pump-status" class="status-badge">
                            <div class="placeholder-shimmer"></div>
                        </div>
                    </div>
                    <div class="status-info">
                        <small>Irrigation system</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connection Status Toast -->
<div class="position-fixed bottom-0 end-0 p-3">
    <div id="connection-status" class="toast toast-modern hide" role="alert">
        <div class="toast-header">
            <div class="connection-dot bg-success"></div>
            <strong class="me-auto">System Status</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Dashboard is connected and updating in real-time.
        </div>
    </div>
</div>

<!-- Custom Styles -->
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

.dropdown {
    position: relative;
    z-index: 1000;
}

    .dropdown-menu-modern {
        position: absolute;
        z-index: 1050; /* Lebih tinggi dari parent dropdown */
        left: 0;
        top: 100%;
        margin-top: 0.5rem;
        min-width: 100%;
        border: none;
        border-radius: 12px;
        padding: 0.5rem;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        will-change: transform;
        transform: translateZ(0);
        overflow: visible !important;   
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
    z-index: auto;
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
    position: relative;
    z-index: auto;
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
.charts-section .section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.chart-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    overflow: hidden;
    margin-bottom: 2rem;
    position: relative;
    z-index: auto;
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

/* Control Panel Styles */
.control-panel .section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.control-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    overflow: hidden;
    height: 100%;
    position: relative;
    z-index: auto;    
}

.control-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow);
}

.control-header {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.control-body {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: calc(100% - 70px);
}

.status-indicator {
    text-align: center;
    margin-bottom: auto;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--transition);
}

.status-badge.online {
    background: var(--success-gradient);
    color: white;
}

.status-badge.offline {
    background: var(--danger-gradient);
    color: white;
}

.status-info {
    text-align: center;
    font-size: 0.8rem;
    color: #6c757d;
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
    let temperatureChart, humidityChart, phChart, waterLevelChart, co2Chart, soilMoistureChart;
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
        co2: {
            border: '#f39c12',
            background: 'rgba(243, 156, 18, 0.1)',
            gradient: ['#f39c12', '#e67e22']
        },
        soilMoisture: {
            border: '#27ae60',
            background: 'rgba(39, 174, 96, 0.1)',
            gradient: ['#27ae60', '#229954']
        }
    };
    
    // Initialize individual charts
    function initCharts() {
        // Temperature Chart
        const tempCtx = document.getElementById('temperatureChart').getContext('2d');
        temperatureChart = createChart(tempCtx, 'Temperature (°C)', chartColors.temperature, 'line');
        
        // Humidity Chart
        const humidityCtx = document.getElementById('humidityChart').getContext('2d');
        humidityChart = createChart(humidityCtx, 'Humidity (%)', chartColors.humidity, 'line');
        
        // pH Chart
        const phCtx = document.getElementById('phChart').getContext('2d');
        phChart = createChart(phCtx, 'pH Level', chartColors.ph, 'line');
        
        // Water Level Chart
        const waterCtx = document.getElementById('waterLevelChart').getContext('2d');
        waterLevelChart = createChart(waterCtx, 'Water Level (%)', chartColors.waterLevel, 'area');
        
        // CO2 Chart
        const co2Ctx = document.getElementById('co2Chart').getContext('2d');
        co2Chart = createChart(co2Ctx, 'CO₂ Level (ppm)', chartColors.co2, 'line');
        
        // Soil Moisture Chart
        const soilCtx = document.getElementById('soilMoistureChart').getContext('2d');
        soilMoistureChart = createChart(soilCtx, 'Soil Moisture (%)', chartColors.soilMoisture, 'area');
    }
    
    // Create individual chart with modern styling
    function createChart(ctx, label, colors, type = 'line') {
        return new Chart(ctx, {
            type: type === 'area' ? 'line' : type,
            data: {
                labels: [],
                datasets: [{
                    label: label,
                    data: [],
                    borderColor: colors.border,
                    backgroundColor: type === 'bar' ? colors.border : colors.background,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: type === 'area',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: colors.border,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: colors.border,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return 'Date: ' + context[0].label;
                            },
                            label: function(context) {
                                return label + ': ' + context.parsed.y.toFixed(2);
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return value.toFixed(1);
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
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
                    
                    // Update main sensor values with animation
                    animateValue('temperature-value', data.temperature.toFixed(1) + ' °C');
                    animateValue('humidity-value', data.humidity.toFixed(1) + ' %');
                    animateValue('ph-value', data.ph_value.toFixed(2));
                    animateValue('light-value', data.light_intensity + ' lux');
                    
                    // Update additional sensors (handle null values)
                    const waterLevel = data.water_level || 0;
                    const co2Level = data.co2_level || 0;
                    const soilMoisture = data.soil_moisture || 0;
                    
                    animateValue('water-level-value', waterLevel.toFixed(1));
                    animateProgressBar('water-level-bar', waterLevel);
                    
                    animateValue('co2-value', co2Level + ' ppm');
                    animateValue('soil-moisture-value', soilMoisture.toFixed(1) + '%');
                    animateProgressBar('soil-moisture-bar', soilMoisture);
                    
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
        
        // Fetch sensor history for individual charts
        axios.get('/api/dashboard/sensor-history', { params: { days: currentDays } })
            .then(response => {
                console.log('History data response:', response.data);
                if (response.data.success && response.data.data.length > 0) {
                    const history = response.data.data;
                    const labels = history.map(item => item.date);
                    
                    // Update individual charts
                    updateChart(temperatureChart, labels, history.map(item => item.avg_temp));
                    updateChart(humidityChart, labels, history.map(item => item.avg_humidity));
                    updateChart(phChart, labels, history.map(item => item.avg_ph || 7.0));
                    updateChart(waterLevelChart, labels, history.map(item => item.avg_water_level || 0));
                    updateChart(co2Chart, labels, history.map(item => item.avg_co2 || 0));
                    updateChart(soilMoistureChart, labels, history.map(item => item.avg_soil_moisture || 0));
                    
                    console.log('Charts updated successfully');
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
                    const curtainElement = document.getElementById('curtain-status');
                    curtainElement.textContent = curtainPos + '% ' + (curtainPos > 50 ? 'Open' : 'Closed');
                    curtainElement.className = 'status-badge ' + (curtainPos > 50 ? 'online' : 'offline');
                    animateProgressBar('curtain-progress', curtainPos);
                    
                    // Update fan status
                    const fanElement = document.getElementById('fan-status');
                    fanElement.textContent = status.fan_status ? 'Online' : 'Offline';
                    fanElement.className = status.fan_status ? 'status-badge online' : 'status-badge offline';
                    
                    // Update pump status
                    const pumpElement = document.getElementById('pump-status');
                    pumpElement.textContent = status.water_pump_status ? 'Active' : 'Standby';
                    pumpElement.className = status.water_pump_status ? 'status-badge online' : 'status-badge offline';
                    
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
        const deviceElement = document.getElementById('device-status');
        deviceElement.textContent = isConnected ? 'Online' : 'Offline';
        deviceElement.className = isConnected ? 'status-badge online' : 'status-badge offline';
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

    // Update individual chart
    function updateChart(chart, labels, data) {
        if (chart) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.update('active');
        }
    }

    // Change time range for charts
    function changeTimeRange(days, label) {
        currentDays = days;
        document.getElementById('time-range').textContent = label;
        
        // Update all chart period labels
        const periodElements = [
            'temp-chart-period', 'humidity-chart-period', 'ph-chart-period',
            'water-chart-period', 'co2-chart-period', 'soil-chart-period'
        ];
        
        periodElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = `(${label})`;
            }
        });
        
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

    // Add smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endsection