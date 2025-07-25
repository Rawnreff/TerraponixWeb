@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Sensor Data</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
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

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-graph-up me-2"></i> Sensor Data History
    </div>
    <div class="card-body">
        <canvas id="sensorHistoryChart" height="100"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-2"></i> Latest Sensor Readings
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Temperature (°C)</th>
                        <th>Humidity (%)</th>
                        <th>pH</th>
                        <th>Light</th>
                        <th>Water Level</th>
                    </tr>
                </thead>
                <tbody id="sensor-data-table">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let sensorHistoryChart;
    
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
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Humidity (%)',
                        data: [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'pH Level',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1,
                        yAxisID: 'y2'
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
                    title: {
                        display: true,
                        text: 'Sensor Data History'
                    }
                },
                scales: {
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
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Humidity (%)'
                        }
                    },
                    y2: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'pH Level'
                        },
                        min: 0,
                        max: 14
                    }
                }
            }
        });
    }

    function updateSensorData(days = 7) {
        // Ambil data history
        axios.get('/api/sensor-history', { params: { days } })
            .then(response => {
                const history = response.data.data;
                
                // Update chart
                const labels = history.map(item => item.date || new Date(item.created_at).toLocaleDateString());
                const temps = history.map(item => item.avg_temp || item.temperature);
                const hums = history.map(item => item.avg_humidity || item.humidity);
                const phs = history.map(item => item.avg_ph || item.ph_value);
                
                sensorHistoryChart.data.labels = labels;
                sensorHistoryChart.data.datasets[0].data = temps;
                sensorHistoryChart.data.datasets[1].data = hums;
                sensorHistoryChart.data.datasets[2].data = phs;
                sensorHistoryChart.update();
                
                // Update table (ambil 10 data terbaru)
                const tableBody = document.getElementById('sensor-data-table');
                tableBody.innerHTML = '';
                
                history.slice(0, 10).forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${new Date(item.created_at).toLocaleString()}</td>
                        <td>${item.temperature?.toFixed(1) || item.avg_temp?.toFixed(1) || '--'}</td>
                        <td>${item.humidity?.toFixed(1) || item.avg_humidity?.toFixed(1) || '--'}</td>
                        <td>${item.ph_value?.toFixed(2) || item.avg_ph?.toFixed(2) || '--'}</td>
                        <td>${item.light_intensity || item.avg_light || '--'}</td>
                        <td>${item.water_level || item.avg_water_level || '--'}</td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching sensor history:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        updateSensorData();
        
        // Filter berdasarkan hari
        document.getElementById('days-filter').addEventListener('change', function() {
            updateSensorData(parseInt(this.value));
        });
    });
</script>
@endsection