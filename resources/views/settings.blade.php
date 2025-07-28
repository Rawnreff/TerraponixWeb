@extends('layouts.app')

@section('content')
<!-- Modern Header with Glass Effect -->
<div class="dashboard-header">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-2 p-4">
        <div>
            <h1 class="dashboard-title">
                <i class="bi bi-gear text-primary me-2"></i>
                System Settings
            </h1>
            <p class="text-muted mb-0">Configure your greenhouse parameters and controls</p>
        </div>
        <div class="btn-toolbar">
            <button type="button" class="btn btn-primary btn-modern" onclick="saveSettings()">
                <i class="bi bi-save me-1"></i> Save Settings
            </button>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<div id="alert-container" class="mb-4"></div>

<!-- Device Selection -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-device-hdd text-primary"></i>
                <span>Device Selection</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-6">
                        <label for="device-select" class="form-label">Select Device</label>
                        <select class="form-select-modern" id="device-select" onchange="loadDeviceSettings()">
                            <option value="">Loading devices...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Device Status</label>
                        <div class="d-flex align-items-center">
                            <div id="device-status" class="status-badge">
                                <div class="placeholder-shimmer"></div>
                            </div>
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
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-thermometer-half text-danger me-2"></i>
                <span>Temperature Control</span>
            </div>
            <div class="card-body-modern">
                <div class="mb-3">
                    <label for="temp-threshold" class="form-label">Temperature Threshold (°C)</label>
                    <input type="number" class="form-control-modern" id="temp-threshold" 
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
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-brightness-high text-warning me-2"></i>
                <span>Light Control</span>
            </div>
            <div class="card-body-modern">
                <div class="mb-3">
                    <label for="light-threshold" class="form-label">Light Threshold</label>
                    <input type="number" class="form-control-modern" id="light-threshold" 
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
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-droplet text-info me-2"></i>
                <span>Water Level Control</span>
            </div>
            <div class="card-body-modern">
                <div class="mb-3">
                    <label for="water-threshold" class="form-label">Water Level Threshold</label>
                    <input type="number" class="form-control-modern" id="water-threshold" 
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
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-droplet-half text-success me-2"></i>
                <span>pH Level Control</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ph-min" class="form-label">pH Minimum</label>
                            <input type="number" class="form-control-modern" id="ph-min" 
                                   placeholder="6.0" step="0.1" min="0" max="14">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ph-max" class="form-label">pH Maximum</label>
                            <input type="number" class="form-control-modern" id="ph-max" 
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
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-gear text-primary me-2"></i>
                <span>Manual Control Override</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-4">
                        <label for="manual-curtain" class="form-label">Curtain Position (%)</label>
                        <div class="input-group">
                            <input type="range" class="form-range-modern" id="manual-curtain" 
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
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-info-circle text-info me-2"></i>
                <span>System Information</span>
            </div>
            <div class="card-body-modern">
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
                        <span id="auto-mode-status" class="status-badge">-</span>
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
            document.getElementById('device-status').className = `status-badge ${device.status === 'online' ? 'online' : 'offline'}`;
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
            document.getElementById('auto-mode-status').className = `status-badge ${status.auto_mode ? 'online' : ''}`;
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
        <div id="${alertId}" class="alert alert-modern alert-${type} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'} me-2"></i>
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
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

<style>
/* Modern Card Styles for Settings */
.modern-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    overflow: hidden;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: auto;
}

.modern-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow);
}

.card-header-modern {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
    background: rgba(248, 249, 250, 0.5);
}

.card-body-modern {
    padding: 1.5rem;
}

/* Form Controls */
.form-control-modern {
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: var(--transition);
}

.form-control-modern:focus {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(102, 126, 234, 0.5);
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.2);
}

.form-select-modern {
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: var(--transition);
}

.form-select-modern:focus {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(102, 126, 234, 0.5);
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.2);
}

.form-range-modern {
    width: 100%;
    height: 8px;
    -webkit-appearance: none;
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
    outline: none;
}

.form-range-modern::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    background: #667eea;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition);
}

.form-range-modern::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}

/* Alert Modern */
.alert-modern {
    border: none;
    border-radius: var(--border-radius);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
    box-shadow: var(--card-shadow);
}

/* Status Badge */
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

/* Form Text */
.form-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card-body-modern {
        padding: 1rem;
    }
    
    .card-header-modern {
        padding: 1rem;
    }
}
</style>