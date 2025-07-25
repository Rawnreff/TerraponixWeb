@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">System Settings</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-sliders me-2"></i> Threshold Settings
            </div>
            <div class="card-body">
                <form id="settings-form">
                    <div class="mb-3">
                        <label for="temp-threshold" class="form-label">Temperature Threshold (°C)</label>
                        <input type="number" step="0.1" class="form-control" id="temp-threshold">
                    </div>
                    
                    <div class="mb-3">
                        <label for="light-threshold" class="form-label">Light Intensity Threshold</label>
                        <input type="number" class="form-control" id="light-threshold">
                    </div>
                    
                    <div class="mb-3">
                        <label for="water-threshold" class="form-label">Water Level Threshold</label>
                        <input type="number" class="form-control" id="water-threshold">
                    </div>
                    
                    <div class="mb-3">
                        <label for="ph-min" class="form-label">Minimum pH Level</label>
                        <input type="number" step="0.1" class="form-control" id="ph-min">
                    </div>
                    
                    <div class="mb-3">
                        <label for="ph-max" class="form-label">Maximum pH Level</label>
                        <input type="number" step="0.1" class="form-control" id="ph-max">
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="auto-mode">
                        <label class="form-check-label" for="auto-mode">Automatic Control Mode</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> System Information
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>Device Information</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Device Name
                            <span id="device-name" class="badge bg-primary rounded-pill">--</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Location
                            <span id="device-location" class="badge bg-primary rounded-pill">--</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            IP Address
                            <span id="device-ip" class="badge bg-primary rounded-pill">--</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Status
                            <span id="device-status" class="badge bg-success rounded-pill">Online</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Last Seen
                            <span id="device-last-seen" class="badge bg-primary rounded-pill">--</span>
                        </li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <h5><i class="bi bi-exclamation-triangle-fill me-2"></i> About Thresholds</h5>
                    <p>These thresholds determine when the system will take automatic actions:</p>
                    <ul>
                        <li>When temperature exceeds the threshold, the fan will turn on</li>
                        <li>When light intensity exceeds the threshold, curtains may close</li>
                        <li>When water level falls below threshold, pump will turn on</li>
                        <li>pH levels outside the min/max range will trigger alerts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function loadSettings() {
        axios.get('/api/settings')
            .then(response => {
                const settings = response.data.data;
                
                document.getElementById('temp-threshold').value = settings.temp_threshold;
                document.getElementById('light-threshold').value = settings.light_threshold;
                document.getElementById('water-threshold').value = settings.water_level_threshold;
                document.getElementById('ph-min').value = settings.ph_min;
                document.getElementById('ph-max').value = settings.ph_max;
                document.getElementById('auto-mode').checked = settings.auto_mode;
            })
            .catch(error => {
                console.error('Error loading settings:', error);
            });
            
        axios.get('/api/v1/devices')
            .then(response => {
                const device = response.data.data[0];
                
                document.getElementById('device-name').textContent = device.name;
                document.getElementById('device-location').textContent = device.location || 'N/A';
                document.getElementById('device-ip').textContent = device.ip_address || 'N/A';
                document.getElementById('device-status').textContent = device.status;
                document.getElementById('device-status').className = device.status === 'online' ? 
                    'badge bg-success rounded-pill' : 'badge bg-danger rounded-pill';
                document.getElementById('device-last-seen').textContent = 
                    new Date(device.last_seen).toLocaleString();
            })
            .catch(error => {
                console.error('Error loading device info:', error);
            });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        loadSettings();
        
        // Form submission
        document.getElementById('settings-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const settings = {
                temp_threshold: parseFloat(document.getElementById('temp-threshold').value),
                light_threshold: parseInt(document.getElementById('light-threshold').value),
                water_level_threshold: parseInt(document.getElementById('water-threshold').value),
                ph_min: parseFloat(document.getElementById('ph-min').value),
                ph_max: parseFloat(document.getElementById('ph-max').value),
                auto_mode: document.getElementById('auto-mode').checked
            };
            
            axios.post('/api/settings', settings)
                .then(response => {
                    alert('Settings saved successfully!');
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    alert('Failed to save settings');
                });
        });
    });
</script>
@endsection