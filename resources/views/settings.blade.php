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
    <h1 class="h2">Settings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" onclick="saveSettings()">
            <i class="bi bi-save"></i> Save Settings
        </button>
    </div>
</div>

<!-- Alert Messages -->
<div id="alert-container"></div>

<!-- Device Selection -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Device Selection</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="device-select" class="form-label">Select Device</label>
                        <select class="form-select" id="device-select" onchange="loadDeviceSettings()">
                            <option value="">Loading devices...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Device Status</label>
                        <div class="d-flex align-items-center">
                            <span id="device-status" class="badge bg-secondary">Unknown</span>
                            <span id="device-last-seen" class="text-muted ms-2">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Settings Form -->
<div class="row">
    <!-- Temperature Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-thermometer-half text-danger me-2"></i>
                    Temperature Control
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="temp-threshold" class="form-label">Temperature Threshold (°C)</label>
                    <input type="number" class="form-control" id="temp-threshold" 
                           placeholder="30" step="0.1" min="0" max="50">
                    <div class="form-text">Fan will turn on when temperature exceeds this value</div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto-fan">
                        <label class="form-check-label" for="auto-fan">
                            Auto Fan Control
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Light Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-brightness-high text-warning me-2"></i>
                    Light Control
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="light-threshold" class="form-label">Light Threshold</label>
                    <input type="number" class="form-control" id="light-threshold" 
                           placeholder="2000" min="0" max="4095">
                    <div class="form-text">Curtain will close when light exceeds this value</div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto-curtain">
                        <label class="form-check-label" for="auto-curtain">
                            Auto Curtain Control
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Water Level Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-droplet text-info me-2"></i>
                    Water Level Control
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="water-threshold" class="form-label">Water Level Threshold</label>
                    <input type="number" class="form-control" id="water-threshold" 
                           placeholder="1500" min="0" max="4095">
                    <div class="form-text">Pump will turn on when water level below this value</div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto-pump">
                        <label class="form-check-label" for="auto-pump">
                            Auto Water Pump Control
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- pH Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-droplet-half text-success me-2"></i>
                    pH Level Control
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ph-min" class="form-label">pH Minimum</label>
                            <input type="number" class="form-control" id="ph-min" 
                                   placeholder="6.0" step="0.1" min="0" max="14">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ph-max" class="form-label">pH Maximum</label>
                            <input type="number" class="form-control" id="ph-max" 
                                   placeholder="7.5" step="0.1" min="0" max="14">
                        </div>
                    </div>
                </div>
                <div class="form-text">Optimal pH range for hydroponic plants</div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Control Override -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear text-primary me-2"></i>
                    Manual Control Override
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label for="manual-curtain" class="form-label">Curtain Position (%)</label>
                        <div class="input-group">
                            <input type="range" class="form-range" id="manual-curtain" 
                                   min="0" max="100" value="50" oninput="updateCurtainValue()">
                        </div>
                        <div class="text-center">
                            <small class="text-muted">Position: <span id="curtain-value">50</span>%</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="controlCurtain()">
                            Set Curtain
                        </button>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fan Control</label>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success" id="fan-on-btn" onclick="controlFan(true)">
                                <i class="bi bi-fan"></i> Turn On Fan
                            </button>
                            <button class="btn btn-outline-danger" id="fan-off-btn" onclick="controlFan(false)">
                                <i class="bi bi-fan"></i> Turn Off Fan
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Water Pump Control</label>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success" id="pump-on-btn" onclick="controlPump(true)">
                                <i class="bi bi-droplet"></i> Turn On Pump
                            </button>
                            <button class="btn btn-outline-danger" id="pump-off-btn" onclick="controlPump(false)">
                                <i class="bi bi-droplet"></i> Turn Off Pump
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle text-info me-2"></i>
                    System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Device IP:</strong>
                        <span id="device-ip" class="text-muted">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Location:</strong>
                        <span id="device-location" class="text-muted">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Auto Mode:</strong>
                        <span id="auto-mode-status" class="badge bg-secondary">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Last Update:</strong>
                        <span id="settings-last-update" class="text-muted">-</span>
                    </div>
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
@push('scripts')
<script>
let currentDeviceId = null;

// Load devices on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDevices();
    setInterval(loadDevices, 30000); // Refresh devices every 30 seconds
});

async function loadDevices() {
    try {
        const response = await axios.get('/api/v1/devices');
        const select = document.getElementById('device-select');
        
        if (response.data.status === 'success') {
            select.innerHTML = '<option value="">Select a device...</option>';
            
            response.data.data.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.textContent = `${device.name} (${device.location})`;
                select.appendChild(option);
            });
            
            // Auto-select first device if available
            if (response.data.data.length > 0 && !currentDeviceId) {
                select.value = response.data.data[0].id;
                loadDeviceSettings();
            }
        }
    } catch (error) {
        console.error('Error loading devices:', error);
        showAlert('Error loading devices', 'danger');
    }
}

async function loadDeviceSettings() {
    const deviceId = document.getElementById('device-select').value;
    if (!deviceId) return;
    
    currentDeviceId = deviceId;
    
    try {
        // Load device settings
        const settingsResponse = await axios.get(`/api/v1/devices/${deviceId}/settings`);
        const deviceResponse = await axios.get(`/api/v1/devices`);
        
        if (settingsResponse.data.status === 'success') {
            const settings = settingsResponse.data.data;
            
            // Populate form fields
            document.getElementById('temp-threshold').value = settings.temp_threshold || '';
            document.getElementById('light-threshold').value = settings.light_threshold || '';
            document.getElementById('water-threshold').value = settings.water_level_threshold || '';
            document.getElementById('ph-min').value = settings.ph_min || '';
            document.getElementById('ph-max').value = settings.ph_max || '';
            document.getElementById('auto-fan').checked = settings.auto_mode || false;
            document.getElementById('auto-curtain').checked = settings.auto_mode || false;
            document.getElementById('auto-pump').checked = settings.auto_mode || false;
        }
        
        // Load device info
        const device = deviceResponse.data.data.find(d => d.id == deviceId);
        if (device) {
            document.getElementById('device-status').textContent = device.status || 'Unknown';
            document.getElementById('device-status').className = `badge ${device.status === 'online' ? 'bg-success' : 'bg-danger'}`;
            document.getElementById('device-last-seen').textContent = device.last_seen ? new Date(device.last_seen).toLocaleString() : '-';
            document.getElementById('device-ip').textContent = device.ip_address || '-';
            document.getElementById('device-location').textContent = device.location || '-';
        }
        
        // Load current actuator status
        loadActuatorStatus();
        
    } catch (error) {
        console.error('Error loading device settings:', error);
        showAlert('Error loading device settings', 'danger');
    }
}

async function loadActuatorStatus() {
    if (!currentDeviceId) return;
    
    try {
        const response = await axios.get(`/api/v1/devices/${currentDeviceId}/actuator-status`);
        
        if (response.data.status === 'success') {
            const status = response.data.data;
            
            // Update curtain position
            document.getElementById('manual-curtain').value = status.curtain_position || 50;
            updateCurtainValue();
            
            // Update button states
            updateFanButtons(status.fan_status);
            updatePumpButtons(status.water_pump_status);
            
            // Update auto mode status
            document.getElementById('auto-mode-status').textContent = status.auto_mode ? 'Enabled' : 'Disabled';
            document.getElementById('auto-mode-status').className = `badge ${status.auto_mode ? 'bg-success' : 'bg-secondary'}`;
        }
    } catch (error) {
        console.error('Error loading actuator status:', error);
    }
}

async function saveSettings() {
    if (!currentDeviceId) {
        showAlert('Please select a device first', 'warning');
        return;
    }
    
    const settings = {
        temp_threshold: parseFloat(document.getElementById('temp-threshold').value) || null,
        light_threshold: parseInt(document.getElementById('light-threshold').value) || null,
        water_level_threshold: parseInt(document.getElementById('water-threshold').value) || null,
        ph_min: parseFloat(document.getElementById('ph-min').value) || null,
        ph_max: parseFloat(document.getElementById('ph-max').value) || null,
        auto_mode: document.getElementById('auto-fan').checked
    };
    
    try {
        const response = await axios.post(`/api/v1/devices/${currentDeviceId}/settings`, settings);
        
        if (response.data.status === 'success') {
            showAlert('Settings saved successfully!', 'success');
            document.getElementById('settings-last-update').textContent = new Date().toLocaleString();
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        showAlert('Error saving settings', 'danger');
    }
}

async function controlCurtain() {
    const position = document.getElementById('manual-curtain').value;
    await controlActuator('curtain', parseInt(position));
}

async function controlFan(status) {
    await controlActuator('fan', status);
}

async function controlPump(status) {
    await controlActuator('water_pump', status);
}

async function controlActuator(type, value) {
    if (!currentDeviceId) {
        showAlert('Please select a device first', 'warning');
        return;
    }
    
    try {
        const response = await axios.post('/api/v1/actuator/control', {
            device_id: currentDeviceId,
            actuator_type: type,
            value: value
        });
        
        if (response.data.status === 'success') {
            showAlert(`${type} controlled successfully!`, 'success');
            loadActuatorStatus(); // Refresh status
        }
    } catch (error) {
        console.error(`Error controlling ${type}:`, error);
        showAlert(`Error controlling ${type}`, 'danger');
    }
}

function updateCurtainValue() {
    const value = document.getElementById('manual-curtain').value;
    document.getElementById('curtain-value').textContent = value;
}

function updateFanButtons(status) {
    const onBtn = document.getElementById('fan-on-btn');
    const offBtn = document.getElementById('fan-off-btn');
    
    if (status) {
        onBtn.classList.remove('btn-outline-success');
        onBtn.classList.add('btn-success');
        offBtn.classList.remove('btn-danger');
        offBtn.classList.add('btn-outline-danger');
    } else {
        onBtn.classList.remove('btn-success');
        onBtn.classList.add('btn-outline-success');
        offBtn.classList.remove('btn-outline-danger');
        offBtn.classList.add('btn-danger');
    }
}

function updatePumpButtons(status) {
    const onBtn = document.getElementById('pump-on-btn');
    const offBtn = document.getElementById('pump-off-btn');
    
    if (status) {
        onBtn.classList.remove('btn-outline-success');
        onBtn.classList.add('btn-success');
        offBtn.classList.remove('btn-danger');
        offBtn.classList.add('btn-outline-danger');
    } else {
        onBtn.classList.remove('btn-success');
        onBtn.classList.add('btn-outline-success');
        offBtn.classList.remove('btn-outline-danger');
        offBtn.classList.add('btn-danger');
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Auto-refresh actuator status every 10 seconds
setInterval(() => {
    if (currentDeviceId) {
        loadActuatorStatus();
    }
}, 10000);
</script>
@endpush
