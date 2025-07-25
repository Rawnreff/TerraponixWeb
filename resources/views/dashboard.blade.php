@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <i class="bi bi-calendar"></i> This week
        </button>
    </div>
</div>

<!-- Status Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Temperature</div>
            <div class="card-body">
                <h5 class="card-title" id="temperature-value">-- °C</h5>
                <p class="card-text">Current greenhouse temperature</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Humidity</div>
            <div class="card-body">
                <h5 class="card-title" id="humidity-value">-- %</h5>
                <p class="card-text">Current humidity level</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">pH Level</div>
            <div class="card-body">
                <h5 class="card-title" id="ph-value">--</h5>
                <p class="card-text">Water pH level</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Light Intensity</div>
            <div class="card-body">
                <h5 class="card-title" id="light-value">--</h5>
                <p class="card-text">Current light level</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Temperature & Humidity (7 Days)
            </div>
            <div class="card-body">
                <canvas id="tempHumidityChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Water Parameters
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
                System Status
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h5>Device Status</h5>
                        <div class="d-flex align-items-center">
                            <div id="device-status" class="badge bg-success me-2">Online</div>
                            <small>Last seen: <span id="last-seen">Just now</span></small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h5>Curtain Status</h5>
                        <div id="curtain-status" class="badge bg-info">50% Open</div>
                    </div>
                    <div class="col-md-3">
                        <h5>Fan Status</h5>
                        <div id="fan-status" class="badge bg-secondary">Off</div>
                    </div>
                    <div class="col-md-3">
                        <h5>Water Pump</h5>
                        <div id="pump-status" class="badge bg-secondary">Off</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Inisialisasi chart
    let tempHumidityChart, waterChart;
    
    function initCharts() {
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
                        fill: true
                    },
                    {
                        label: 'Humidity (%)',
                        data: [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Temperature & Humidity History'
                    }
                }
            }
        });

        const waterCtx = document.getElementById('waterChart').getContext('2d');
        waterChart = new Chart(waterCtx, {
            type: 'bar',
            data: {
                labels: ['pH Level', 'Water Level'],
                datasets: [{
                    label: 'Current Values',
                    data: [0, 0],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)'
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
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Water Parameters'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Fungsi untuk update data
    function updateDashboard() {
        // Ambil data sensor terbaru
        axios.get('/api/latest-sensor-data')
            .then(response => {
                const data = response.data.data[0];
                
                // Update nilai sensor
                document.getElementById('temperature-value').textContent = data.temperature + ' °C';
                document.getElementById('humidity-value').textContent = data.humidity + ' %';
                document.getElementById('ph-value').textContent = data.ph_value.toFixed(2);
                document.getElementById('light-value').textContent = data.light_intensity;
                
                // Update chart parameter air
                waterChart.data.datasets[0].data = [data.ph_value, data.water_level];
                waterChart.update();
                
                // Update device status
                document.getElementById('last-seen').textContent = new Date(data.created_at).toLocaleString();
            })
            .catch(error => {
                console.error('Error fetching sensor data:', error);
            });
        
        // Ambil data history untuk chart
        axios.get('/api/sensor-history', { params: { days: 7 } })
            .then(response => {
                const history = response.data.data;
                const labels = history.map(item => item.date || new Date(item.created_at).toLocaleDateString());
                const temps = history.map(item => item.avg_temp || item.temperature);
                const hums = history.map(item => item.avg_humidity || item.humidity);
                
                tempHumidityChart.data.labels = labels;
                tempHumidityChart.data.datasets[0].data = temps;
                tempHumidityChart.data.datasets[1].data = hums;
                tempHumidityChart.update();
            })
            .catch(error => {
                console.error('Error fetching history data:', error);
            });
        
        // Ambil status aktuator
        axios.get('/api/actuator-status')
            .then(response => {
                const status = response.data.data;
                
                document.getElementById('curtain-status').textContent = status.curtain_position + '% ' + 
                    (status.curtain_position > 50 ? 'Open' : 'Closed');
                document.getElementById('fan-status').textContent = status.fan_status ? 'On' : 'Off';
                document.getElementById('fan-status').className = status.fan_status ? 'badge bg-success' : 'badge bg-secondary';
                document.getElementById('pump-status').textContent = status.water_pump_status ? 'On' : 'Off';
                document.getElementById('pump-status').className = status.water_pump_status ? 'badge bg-success' : 'badge bg-secondary';
            })
            .catch(error => {
                console.error('Error fetching actuator status:', error);
            });
    }

    // Inisialisasi
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        updateDashboard();
        
        // Update data setiap 5 detik
        setInterval(updateDashboard, 5000);
    });
</script>
@endsection