@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Actuator Control</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="auto-mode-switch">
            <label class="form-check-label" for="auto-mode-switch">Auto Mode</label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-blinds me-2"></i> Curtain Control
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Current Position</h5>
                        <div class="display-4 text-center" id="curtain-position">--%</div>
                    </div>
                    <div class="col-md-6">
                        <h5>Manual Control</h5>
                        <div class="btn-group w-100 mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 0)">Close</button>
                            <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 50)">50%</button>
                            <button type="button" class="btn btn-outline-primary" onclick="controlActuator('curtain', 100)">Open</button>
                        </div>
                        <div class="mt-2">
                            <label for="curtainRange" class="form-label">Set Position: <span id="curtainValue">50</span>%</label>
                            <input type="range" class="form-range" min="0" max="100" id="curtainRange" value="50" onchange="updateCurtainValue(this.value)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-fan me-2"></i> Fan Control
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Current Status</h5>
                        <div class="display-4 text-center">
                            <span id="fan-status-text" class="badge bg-secondary p-3">OFF</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Manual Control</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="fan-switch" onchange="controlActuator('fan', this.checked ? 1 : 0)">
                            <label class="form-check-label" for="fan-switch">Toggle Fan</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-droplet me-2"></i> Water Pump Control
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Current Status</h5>
                        <div class="display-4 text-center">
                            <span id="pump-status-text" class="badge bg-secondary p-3">OFF</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Manual Control</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pump-switch" onchange="controlActuator('water_pump', this.checked ? 1 : 0)">
                            <label class="form-check-label" for="pump-switch">Toggle Pump</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i> Actuator Logs
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Actuator</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="actuator-logs">
                            <!-- Logs will be populated here -->
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
    function updateActuatorStatus() {
        axios.get('/api/actuator-status')
            .then(response => {
                const status = response.data.data;
                
                // Update curtain
                document.getElementById('curtain-position').textContent = status.curtain_position + '%';
                document.getElementById('curtainRange').value = status.curtain_position;
                document.getElementById('curtainValue').textContent = status.curtain_position;
                
                // Update fan
                document.getElementById('fan-status-text').textContent = status.fan_status ? 'ON' : 'OFF';
                document.getElementById('fan-status-text').className = status.fan_status ? 'badge bg-success p-3' : 'badge bg-secondary p-3';
                document.getElementById('fan-switch').checked = status.fan_status;
                
                // Update pump
                document.getElementById('pump-status-text').textContent = status.water_pump_status ? 'ON' : 'OFF';
                document.getElementById('pump-status-text').className = status.water_pump_status ? 'badge bg-success p-3' : 'badge bg-secondary p-3';
                document.getElementById('pump-switch').checked = status.water_pump_status;
            })
            .catch(error => {
                console.error('Error fetching actuator status:', error);
            });
    }
    
    function updateCurtainValue(value) {
        document.getElementById('curtainValue').textContent = value;
        controlActuator('curtain', parseInt(value));
    }
    
    function controlActuator(type, value) {
        axios.post('/api/control-actuator', {
            type: type,
            value: value
        })
        .then(response => {
            updateActuatorStatus();
            addActuatorLog(type, value);
        })
        .catch(error => {
            console.error('Error controlling actuator:', error);
        });
    }
    
    function addActuatorLog(type, value) {
        const logsTable = document.getElementById('actuator-logs');
        const now = new Date();
        const row = document.createElement('tr');
        
        let actionText = '';
        switch(type) {
            case 'curtain':
                actionText = `Set to ${value}%`;
                break;
            case 'fan':
                actionText = value ? 'Turned ON' : 'Turned OFF';
                break;
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
    
    function checkAutoMode() {
        axios.get('/api/settings')
            .then(response => {
                const settings = response.data.data;
                document.getElementById('auto-mode-switch').checked = settings.auto_mode;
            })
            .catch(error => {
                console.error('Error checking auto mode:', error);
            });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        updateActuatorStatus();
        checkAutoMode();
        
        // Update status setiap 3 detik
        setInterval(updateActuatorStatus, 3000);
        
        // Auto mode switch
        document.getElementById('auto-mode-switch').addEventListener('change', function() {
            axios.post('/api/settings', {
                auto_mode: this.checked
            })
            .catch(error => {
                console.error('Error updating auto mode:', error);
            });
        });
    });
</script>
@endsection