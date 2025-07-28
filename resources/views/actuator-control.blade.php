@extends('layouts.app')

@section('content')
<!-- Modern Header with Glass Effect -->
<div class="dashboard-header">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-2 p-4">
        <div>
            <h1 class="dashboard-title">
                <i class="bi bi-gear text-primary me-2"></i>
                Actuator Control
            </h1>
            <p class="text-muted mb-0">Manual control for greenhouse actuators</p>
        </div>
        <div class="btn-toolbar">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="auto-mode-switch">
                <label class="form-check-label" for="auto-mode-switch">Auto Mode</label>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<div id="alert-container" class="mb-4"></div>

<div class="row">
    <!-- Curtain Control -->
    <div class="col-md-6 mb-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-blinds text-primary me-2"></i>
                <span>Curtain Control</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Current Position</h5>
                        <div class="metric-value text-center" id="curtain-position">--%</div>
                    </div>
                    <div class="col-md-6">
                        <h5>Manual Control</h5>
                        <div class="btn-group w-100 mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary btn-modern" onclick="controlActuator('curtain', 0)">Close</button>
                            <button type="button" class="btn btn-outline-primary btn-modern" onclick="controlActuator('curtain', 50)">50%</button>
                            <button type="button" class="btn btn-outline-primary btn-modern" onclick="controlActuator('curtain', 100)">Open</button>
                        </div>
                        <div class="mt-2">
                            <label for="curtainRange" class="form-label">Set Position: <span id="curtainValue">50</span>%</label>
                            <input type="range" class="form-range-modern" min="0" max="100" id="curtainRange" value="50" onchange="updateCurtainValue(this.value)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fan Control -->
    <div class="col-md-6 mb-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-fan text-warning me-2"></i>
                <span>Fan Control</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Current Status</h5>
                        <div class="metric-value text-center">
                            <span id="fan-status-text" class="status-badge">OFF</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Manual Control</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="fan-switch" onchange="controlActuator('fan', this.checked ? 1 : 0)">
                            <label class="form-check-label" for="fan-switch">Toggle Fan</label>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-success btn-modern" onclick="controlActuator('fan', 1)">
                                <i class="bi bi-power"></i> Turn On
                            </button>
                            <button class="btn btn-danger btn-modern" onclick="controlActuator('fan', 0)">
                                <i class="bi bi-power"></i> Turn Off
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Water Pump Control -->
    <div class="col-md-6 mb-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-droplet text-info me-2"></i>
                <span>Water Pump Control</span>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Current Status</h5>
                        <div class="metric-value text-center">
                            <span id="pump-status-text" class="status-badge">OFF</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Manual Control</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pump-switch" onchange="controlActuator('water_pump', this.checked ? 1 : 0)">
                            <label class="form-check-label" for="pump-switch">Toggle Pump</label>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-success btn-modern" onclick="controlActuator('water_pump', 1)">
                                <i class="bi bi-power"></i> Turn On
                            </button>
                            <button class="btn btn-danger btn-modern" onclick="controlActuator('water_pump', 0)">
                                <i class="bi bi-power"></i> Turn Off
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actuator Logs -->
    <div class="col-md-6 mb-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-clock-history text-secondary me-2"></i>
                <span>Actuator Logs</span>
            </div>
            <div class="card-body-modern">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Actuator</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="actuator-logs">
                            <tr>
                                <td colspan="3" class="text-center">No logs available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Global variables
    let currentDeviceId = null;
    let autoMode = false;

    // Update actuator status
    function updateActuatorStatus() {
        axios.get('/api/actuator-status')
            .then(response => {
                const status = response.data.data;
                
                // Update curtain
                document.getElementById('curtain-position').textContent = status.curtain_position + '%';
                document.getElementById('curtainRange').value = status.curtain_position;
                document.getElementById('curtainValue').textContent = status.curtain_position;
                
                // Update fan
                const fanStatus = status.fan_status;
                document.getElementById('fan-status-text').textContent = fanStatus ? 'ON' : 'OFF';
                document.getElementById('fan-status-text').className = fanStatus ? 'status-badge online' : 'status-badge offline';
                document.getElementById('fan-switch').checked = fanStatus;
                
                // Update pump
                const pumpStatus = status.water_pump_status;
                document.getElementById('pump-status-text').textContent = pumpStatus ? 'ON' : 'OFF';
                document.getElementById('pump-status-text').className = pumpStatus ? 'status-badge online' : 'status-badge offline';
                document.getElementById('pump-switch').checked = pumpStatus;
                
                // Update auto mode
                autoMode = status.auto_mode;
                document.getElementById('auto-mode-switch').checked = autoMode;
            })
            .catch(error => {
                console.error('Error fetching actuator status:', error);
                showAlert('Failed to load actuator status', 'danger');
            });
    }
    
    function updateCurtainValue(value) {
        document.getElementById('curtainValue').textContent = value;
        controlActuator('curtain', parseInt(value));
    }
    
    // Control actuator function
    function controlActuator(type, value) {
        if (autoMode) {
            showAlert('Cannot control manually in Auto Mode', 'warning');
            updateActuatorStatus(); // Refresh status
            return;
        }

        axios.post('/api/control-actuator', {
            type: type,
            value: value
        })
        .then(response => {
            updateActuatorStatus();
            addActuatorLog(type, value);
            showAlert(`${type.replace('_', ' ')} controlled successfully`, 'success');
        })
        .catch(error => {
            console.error('Error controlling actuator:', error);
            showAlert(`Failed to control ${type.replace('_', ' ')}`, 'danger');
        });
    }
    
    // Add actuator log
    function addActuatorLog(type, value) {
        const logsTable = document.getElementById('actuator-logs');
        
        // Remove "no logs" message if present
        if (logsTable.children.length === 1 && logsTable.children[0].children.length === 1) {
            logsTable.innerHTML = '';
        }
        
        const now = new Date();
        const row = document.createElement('tr');
        
        let actionText = '';
        switch(type) {
            case 'curtain':
                actionText = `Set to ${value}%`;
                break;
            case 'fan':
            case 'water_pump':
                actionText = value ? 'Turned ON' : 'Turned OFF';
                break;
        }
        
        row.innerHTML = `
            <td>${now.toLocaleTimeString()}</td>
            <td>${type.replace('_', ' ').toUpperCase()}</td>
            <td>${actionText}</td>
        `;
        
        logsTable.insertBefore(row, logsTable.firstChild);
        
        // Keep only the last 10 logs
        if (logsTable.children.length > 10) {
            logsTable.removeChild(logsTable.lastChild);
        }
    }
    
    // Toggle auto mode
    function toggleAutoMode() {
        const autoMode = document.getElementById('auto-mode-switch').checked;
        
        axios.post('/api/settings', {
            auto_mode: autoMode
        })
        .then(response => {
            showAlert(`Auto mode ${autoMode ? 'enabled' : 'disabled'}`, 'success');
            updateActuatorStatus();
        })
        .catch(error => {
            console.error('Error updating auto mode:', error);
            showAlert('Failed to update auto mode', 'danger');
            document.getElementById('auto-mode-switch').checked = !autoMode;
        });
    }

    // Show alert
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

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateActuatorStatus();
        
        // Update status every 3 seconds
        setInterval(updateActuatorStatus, 3000);
        
        // Auto mode switch event
        document.getElementById('auto-mode-switch').addEventListener('change', toggleAutoMode);
    });
</script>
@endsection

<style>
/* Modern Card Styles */
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

/* Metric Value */
.metric-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 1rem 0;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 1rem;
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

/* Table Modern */
.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.table-modern thead th {
    background: rgba(248, 249, 250, 0.7);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    font-weight: 600;
    color: #2c3e50;
}

.table-modern tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Range Input */
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

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card-body-modern {
        padding: 1rem;
    }
    
    .card-header-modern {
        padding: 1rem;
    }
    
    .metric-value {
        font-size: 1.5rem;
    }
}
</style>