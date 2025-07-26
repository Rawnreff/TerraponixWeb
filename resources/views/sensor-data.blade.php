@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2 text-primary">
                <i class="bi bi-graph-up me-2"></i>Sensor Data Analytics
            </h1>
            <p class="text-muted">Real-time monitoring and historical analysis of greenhouse sensors</p>
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

    <!-- System Alert -->
    <div id="system-alert" class="alert alert-info d-none" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <span id="alert-message">System is running normally</span>
    </div>

    <!-- Real-time Sensor Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Temperature
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="current-temp">
                                <i class="bi bi-thermometer-half me-2"></i>-- °C
                            </div>
                            <div class="text-xs text-muted mt-1" id="temp-trend">Loading...</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="current-humidity">
                                <i class="bi bi-droplet me-2"></i>-- %
                            </div>
                            <div class="text-xs text-muted mt-1" id="humidity-trend">Loading...</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="current-ph">
                                <i class="bi bi-activity me-2"></i>--
                            </div>
                            <div class="text-xs text-muted mt-1" id="ph-trend">Loading...</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="current-light">
                                <i class="bi bi-sun me-2"></i>--
                            </div>
                            <div class="text-xs text-muted mt-1" id="light-trend">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-sun fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Main Chart -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>Real-time Sensor Trends
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="setChartPeriod('1h')">1H</button>
                            <button type="button" class="btn btn-outline-primary active" onclick="setChartPeriod('6h')">6H</button>
                            <button type="button" class="btn btn-outline-primary" onclick="setChartPeriod('24h')">24H</button>
                            <button type="button" class="btn btn-outline-primary" onclick="setChartPeriod('7d')">7D</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="mainChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Statistics Panel -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Statistics (24h)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Temperature</h6>
                        <div class="row">
                            <div class="col-4">
                                <small class="text-muted">Min</small>
                                <div id="temp-min" class="fw-bold">--°C</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Avg</small>
                                <div id="temp-avg" class="fw-bold">--°C</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Max</small>
                                <div id="temp-max" class="fw-bold">--°C</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Humidity</h6>
                        <div class="row">
                            <div class="col-4">
                                <small class="text-muted">Min</small>
                                <div id="humidity-min" class="fw-bold">--%</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Avg</small>
                                <div id="humidity-avg" class="fw-bold">--%</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Max</small>
                                <div id="humidity-max" class="fw-bold">--%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>pH Level</h6>
                        <div class="row">
                            <div class="col-4">
                                <small class="text-muted">Min</small>
                                <div id="ph-min" class="fw-bold">--</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Avg</small>
                                <div id="ph-avg" class="fw-bold">--</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Max</small>
                                <div id="ph-max" class="fw-bold">--</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Light Intensity</h6>
                        <div class="row">
                            <div class="col-4">
                                <small class="text-muted">Min</small>
                                <div id="light-min" class="fw-bold">--</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Avg</small>
                                <div id="light-avg" class="fw-bold">--</div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Max</small>
                                <div id="light-max" class="fw-bold">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts -->
    <div class="row mb-4">
        <!-- Water Parameters Chart -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-droplet me-2"></i>Water Parameters
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="waterChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- System Health Chart -->
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-heart-pulse me-2"></i>System Health Overview
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="healthChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2"></i>Recent Sensor Readings
                        </h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchData()">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Temperature (°C)</th>
                                    <th>Humidity (%)</th>
                                    <th>pH Level</th>
                                    <th>Light Intensity</th>
                                    <th>Water Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="sensor-table-body">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Sensor data pagination">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination will be generated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Analytics Script -->
<script>
let refreshInterval;
let mainChart;
let waterChart;
let healthChart;
let currentPeriod = '6h';
let currentPage = 1;
const itemsPerPage = 10;

// Initialize page
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
    // Main Chart
    const mainCtx = document.getElementById('mainChart').getContext('2d');
    mainChart = new Chart(mainCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Temperature (°C)',
                data: [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Humidity (%)',
                data: [],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Time'
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

    // Water Chart
    const waterCtx = document.getElementById('waterChart').getContext('2d');
    waterChart = new Chart(waterCtx, {
        type: 'bar',
        data: {
            labels: ['pH Level', 'Water Level'],
            datasets: [{
                label: 'Current Values',
                data: [0, 0],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(75, 192, 192)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Health Chart
    const healthCtx = document.getElementById('healthChart').getContext('2d');
    healthChart = new Chart(healthCtx, {
        type: 'doughnut',
        data: {
            labels: ['Optimal', 'Warning', 'Critical'],
            datasets: [{
                data: [70, 20, 10],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function loadInitialData() {
    fetchRealtimeData();
    fetchStatistics();
    fetchHistoricalData();
}

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        fetchRealtimeData();
        fetchStatistics();
    }, 10000); // Refresh every 10 seconds
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

function fetchRealtimeData() {
    fetch('/api/v1/sensor-data/realtime')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                updateRealtimeDisplay(data.data);
                updateMainChart(data.data);
                updateWaterChart(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching realtime data:', error);
            showAlert('Failed to fetch realtime data', 'danger');
        });
}

function fetchStatistics() {
    fetch('/api/v1/sensor-data/statistics?device_id=1')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                updateStatistics(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching statistics:', error);
        });
}

function fetchHistoricalData() {
    const now = new Date();
    const from = new Date(now.getTime() - (24 * 60 * 60 * 1000)); // 24 hours ago
    
    fetch(`/api/v1/sensor-data/history?device_id=1&from=${from.toISOString()}&to=${now.toISOString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data) {
                updateDataTable(data.data);
            }
        })
        .catch(error => {
            console.error('Error fetching historical data:', error);
        });
}

function updateRealtimeDisplay(data) {
    // Update current values
    document.getElementById('current-temp').innerHTML = 
        `<i class="bi bi-thermometer-half me-2"></i>${data.temperature.toFixed(1)} °C`;
    document.getElementById('current-humidity').innerHTML = 
        `<i class="bi bi-droplet me-2"></i>${data.humidity.toFixed(1)} %`;
    document.getElementById('current-ph').innerHTML = 
        `<i class="bi bi-activity me-2"></i>${data.ph_value.toFixed(2)}`;
    document.getElementById('current-light').innerHTML = 
        `<i class="bi bi-sun me-2"></i>${data.light_intensity}`;

    // Update trends
    updateTrend('temp-trend', data.temperature, 20, 30);
    updateTrend('humidity-trend', data.humidity, 40, 80);
    updateTrend('ph-trend', data.ph_value, 6.0, 7.5);
    updateTrend('light-trend', data.light_intensity, 1000, 3000);
}

function updateTrend(elementId, value, minOptimal, maxOptimal) {
    const element = document.getElementById(elementId);
    if (value < minOptimal) {
        element.innerHTML = '<span class="text-warning">↘ Low</span>';
    } else if (value > maxOptimal) {
        element.innerHTML = '<span class="text-danger">↗ High</span>';
    } else {
        element.innerHTML = '<span class="text-success">→ Optimal</span>';
    }
}

function updateMainChart(data) {
    const now = new Date().toLocaleTimeString();
    
    mainChart.data.labels.push(now);
    mainChart.data.datasets[0].data.push(data.temperature);
    mainChart.data.datasets[1].data.push(data.humidity);
    
    // Keep only last 20 data points
    if (mainChart.data.labels.length > 20) {
        mainChart.data.labels.shift();
        mainChart.data.datasets[0].data.shift();
        mainChart.data.datasets[1].data.shift();
    }
    
    mainChart.update();
}

function updateWaterChart(data) {
    waterChart.data.datasets[0].data = [data.ph_value, data.water_level];
    waterChart.update();
}

function updateStatistics(data) {
    // Update temperature statistics
    document.getElementById('temp-min').textContent = data.min_temperature ? data.min_temperature.toFixed(1) + '°C' : '--°C';
    document.getElementById('temp-avg').textContent = data.avg_temperature ? data.avg_temperature.toFixed(1) + '°C' : '--°C';
    document.getElementById('temp-max').textContent = data.max_temperature ? data.max_temperature.toFixed(1) + '°C' : '--°C';
    
    // Update humidity statistics
    document.getElementById('humidity-min').textContent = data.min_humidity ? data.min_humidity.toFixed(1) + '%' : '--%';
    document.getElementById('humidity-avg').textContent = data.avg_humidity ? data.avg_humidity.toFixed(1) + '%' : '--%';
    document.getElementById('humidity-max').textContent = data.max_humidity ? data.max_humidity.toFixed(1) + '%' : '--%';
    
    // Update pH statistics
    document.getElementById('ph-min').textContent = data.avg_ph ? data.avg_ph.toFixed(2) : '--';
    document.getElementById('ph-avg').textContent = data.avg_ph ? data.avg_ph.toFixed(2) : '--';
    document.getElementById('ph-max').textContent = data.avg_ph ? data.avg_ph.toFixed(2) : '--';
    
    // Update light statistics
    document.getElementById('light-min').textContent = data.avg_light ? Math.round(data.avg_light) : '--';
    document.getElementById('light-avg').textContent = data.avg_light ? Math.round(data.avg_light) : '--';
    document.getElementById('light-max').textContent = data.avg_light ? Math.round(data.avg_light) : '--';
}

function updateDataTable(data) {
    const tbody = document.getElementById('sensor-table-body');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = data.slice(startIndex, endIndex);
    
    tbody.innerHTML = '';
    
    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No data available</td></tr>';
        return;
    }
    
    pageData.forEach(reading => {
        const row = document.createElement('tr');
        const timestamp = new Date(reading.created_at).toLocaleString();
        const status = getStatusBadge(reading);
        
        row.innerHTML = `
            <td>${timestamp}</td>
            <td>${reading.temperature.toFixed(1)}</td>
            <td>${reading.humidity.toFixed(1)}</td>
            <td>${reading.ph_value.toFixed(2)}</td>
            <td>${reading.light_intensity}</td>
            <td>${reading.water_level}</td>
            <td>${status}</td>
        `;
        
        tbody.appendChild(row);
    });
    
    updatePagination(data.length);
}

function getStatusBadge(reading) {
    let issues = [];
    
    if (reading.temperature > 30) issues.push('High Temp');
    if (reading.temperature < 20) issues.push('Low Temp');
    if (reading.humidity > 80) issues.push('High Humidity');
    if (reading.humidity < 40) issues.push('Low Humidity');
    if (reading.ph_value < 6.0 || reading.ph_value > 7.5) issues.push('pH Issue');
    
    if (issues.length === 0) {
        return '<span class="badge bg-success">Normal</span>';
    } else {
        return `<span class="badge bg-warning">${issues.join(', ')}</span>`;
    }
}

function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const pagination = document.getElementById('pagination');
    
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>`;
    pagination.appendChild(prevLi);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
        pagination.appendChild(li);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>`;
    pagination.appendChild(nextLi);
}

function changePage(page) {
    currentPage = page;
    fetchHistoricalData();
}

function setChartPeriod(period) {
    currentPeriod = period;
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Reload chart data for the new period
    loadChartDataForPeriod(period);
}

function loadChartDataForPeriod(period) {
    // Implementation for loading chart data based on period
    showAlert(`Loading ${period} data...`, 'info');
}

function searchData() {
    const searchTerm = document.getElementById('searchInput').value;
    // Implementation for searching data
    showAlert(`Searching for: ${searchTerm}`, 'info');
}

function exportData() {
    // Implementation for data export
    showAlert('Export feature coming soon', 'info');
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

.btn-group .btn.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.pagination .page-link {
    color: #007bff;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.fw-bold {
    font-weight: 600 !important;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endsection