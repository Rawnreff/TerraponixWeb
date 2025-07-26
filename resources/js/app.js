import './bootstrap';

// Real-time sensor data management
class SensorDataManager {
    constructor() {
        this.data = {};
        this.charts = {};
        this.updateInterval = 5000; // 5 seconds
        this.isConnected = true;
        this.lastUpdate = null;
        
        this.init();
    }

    init() {
        this.setupEventSource();
        this.startPeriodicUpdates();
        this.setupChartDefaults();
    }

    setupEventSource() {
        // Try to use Server-Sent Events if available
        if (typeof EventSource !== 'undefined') {
            this.eventSource = new EventSource('/api/sensor-stream');
            
            this.eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.updateSensorData(data);
            };

            this.eventSource.onerror = () => {
                console.log('EventSource failed, falling back to polling');
                this.eventSource.close();
                this.startPolling();
            };
        } else {
            this.startPolling();
        }
    }

    startPolling() {
        setInterval(() => {
            this.fetchLatestData();
        }, this.updateInterval);
    }

    startPeriodicUpdates() {
        // Initial fetch
        this.fetchLatestData();
        
        // Update connection status indicator
        setInterval(() => {
            this.checkConnection();
        }, 10000);
    }

    async fetchLatestData() {
        try {
            const response = await fetch('/api/latest-sensor-data');
            if (!response.ok) throw new Error('Network error');
            
            const data = await response.json();
            this.updateSensorData(data);
            this.updateConnectionStatus(true);
        } catch (error) {
            console.error('Failed to fetch sensor data:', error);
            this.updateConnectionStatus(false);
        }
    }

    async checkConnection() {
        try {
            const response = await fetch('/api/test');
            if (!response.ok) throw new Error('Connection failed');
            this.updateConnectionStatus(true);
        } catch (error) {
            this.updateConnectionStatus(false);
        }
    }

    updateSensorData(data) {
        this.data = { ...this.data, ...data };
        this.lastUpdate = new Date();
        
        // Update UI elements
        this.updateSensorCards(data);
        this.updateCharts(data);
        this.triggerUpdateAnimation();
        
        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('sensorDataUpdated', { detail: data }));
    }

    updateSensorCards(data) {
        // Temperature
        if (data.temperature !== undefined) {
            this.updateValueElement('temperature-value', `${data.temperature}°C`);
            this.updateStatusColor('temperature-card', this.getTemperatureStatus(data.temperature));
        }

        // Humidity
        if (data.humidity !== undefined) {
            this.updateValueElement('humidity-value', `${data.humidity}%`);
            this.updateStatusColor('humidity-card', this.getHumidityStatus(data.humidity));
        }

        // pH Level
        if (data.ph_value !== undefined) {
            this.updateValueElement('ph-value', data.ph_value.toFixed(1));
            this.updateStatusColor('ph-card', this.getPhStatus(data.ph_value));
        }

        // Light Intensity
        if (data.light_intensity !== undefined) {
            this.updateValueElement('light-value', data.light_intensity);
            this.updateStatusColor('light-card', this.getLightStatus(data.light_intensity));
        }

        // Water Level
        if (data.water_level !== undefined) {
            this.updateValueElement('water-level-value', `${data.water_level}%`);
            this.updateStatusColor('water-level-card', this.getWaterLevelStatus(data.water_level));
        }

        // CO2 Level
        if (data.co2_level !== undefined) {
            this.updateValueElement('co2-value', `${data.co2_level} ppm`);
            this.updateStatusColor('co2-card', this.getCO2Status(data.co2_level));
        }

        // Soil Moisture
        if (data.soil_moisture !== undefined) {
            this.updateValueElement('soil-moisture-value', `${data.soil_moisture}%`);
            this.updateStatusColor('soil-moisture-card', this.getSoilMoistureStatus(data.soil_moisture));
        }

        // Last update time
        if (this.lastUpdate) {
            this.updateValueElement('last-update', this.lastUpdate.toLocaleTimeString());
        }
    }

    updateValueElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
            element.classList.add('animate-pulse-green');
            setTimeout(() => {
                element.classList.remove('animate-pulse-green');
            }, 1000);
        }
    }

    updateStatusColor(cardId, status) {
        const card = document.getElementById(cardId);
        if (card) {
            // Remove existing status classes
            card.classList.remove('border-green-400', 'border-yellow-400', 'border-red-400');
            
            // Add new status class
            switch (status) {
                case 'good':
                    card.classList.add('border-green-400');
                    break;
                case 'warning':
                    card.classList.add('border-yellow-400');
                    break;
                case 'danger':
                    card.classList.add('border-red-400');
                    break;
            }
        }
    }

    triggerUpdateAnimation() {
        const cards = document.querySelectorAll('.sensor-card');
        cards.forEach(card => {
            card.classList.add('animate-pulse');
            setTimeout(() => {
                card.classList.remove('animate-pulse');
            }, 500);
        });
    }

    updateConnectionStatus(connected) {
        const statusEl = document.getElementById('connection-status');
        const textEl = document.getElementById('connection-text');
        
        if (statusEl && textEl) {
            if (connected && !this.isConnected) {
                statusEl.className = 'h-2 w-2 bg-green-400 rounded-full animate-pulse';
                textEl.textContent = 'Connected';
                this.isConnected = true;
            } else if (!connected && this.isConnected) {
                statusEl.className = 'h-2 w-2 bg-red-400 rounded-full';
                textEl.textContent = 'Disconnected';
                this.isConnected = false;
            }
        }
    }

    setupChartDefaults() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.responsive = true;
            Chart.defaults.maintainAspectRatio = false;
            Chart.defaults.plugins.legend.display = true;
            Chart.defaults.elements.line.tension = 0.4;
        }
    }

    updateCharts(data) {
        // Update temperature chart if exists
        if (this.charts.temperature && data.temperature !== undefined) {
            this.addDataToChart(this.charts.temperature, data.temperature);
        }

        // Update humidity chart if exists
        if (this.charts.humidity && data.humidity !== undefined) {
            this.addDataToChart(this.charts.humidity, data.humidity);
        }
    }

    addDataToChart(chart, value) {
        const now = new Date();
        const timeLabel = now.toLocaleTimeString();
        
        chart.data.labels.push(timeLabel);
        chart.data.datasets[0].data.push(value);
        
        // Keep only last 20 data points
        if (chart.data.labels.length > 20) {
            chart.data.labels.shift();
            chart.data.datasets[0].data.shift();
        }
        
        chart.update('none');
    }

    // Status determination methods
    getTemperatureStatus(temp) {
        if (temp < 15 || temp > 35) return 'danger';
        if (temp < 18 || temp > 30) return 'warning';
        return 'good';
    }

    getHumidityStatus(humidity) {
        if (humidity < 30 || humidity > 80) return 'danger';
        if (humidity < 40 || humidity > 70) return 'warning';
        return 'good';
    }

    getPhStatus(ph) {
        if (ph < 5.5 || ph > 8.5) return 'danger';
        if (ph < 6.0 || ph > 8.0) return 'warning';
        return 'good';
    }

    getLightStatus(light) {
        if (light < 200 || light > 1000) return 'warning';
        return 'good';
    }

    getWaterLevelStatus(level) {
        if (level < 20) return 'danger';
        if (level < 30) return 'warning';
        return 'good';
    }

    getCO2Status(co2) {
        if (co2 > 1500) return 'danger';
        if (co2 > 1200) return 'warning';
        return 'good';
    }

    getSoilMoistureStatus(moisture) {
        if (moisture < 30) return 'danger';
        if (moisture < 40) return 'warning';
        return 'good';
    }
}

// Actuator control functionality
class ActuatorController {
    constructor() {
        this.status = {};
        this.init();
    }

    init() {
        this.fetchActuatorStatus();
        setInterval(() => {
            this.fetchActuatorStatus();
        }, 10000);
    }

    async fetchActuatorStatus() {
        try {
            const response = await fetch('/api/actuator-status');
            if (!response.ok) throw new Error('Network error');
            
            const data = await response.json();
            this.updateActuatorStatus(data);
        } catch (error) {
            console.error('Failed to fetch actuator status:', error);
        }
    }

    async controlActuator(actuator, action, value = null) {
        try {
            const payload = {
                actuator: actuator,
                action: action
            };

            if (value !== null) {
                payload.value = value;
            }

            const response = await fetch('/api/control-actuator', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) throw new Error('Control failed');
            
            const result = await response.json();
            this.showNotification(`${actuator} ${action} successful`, 'success');
            
            // Refresh status after a short delay
            setTimeout(() => {
                this.fetchActuatorStatus();
            }, 1000);
            
            return result;
        } catch (error) {
            console.error('Actuator control failed:', error);
            this.showNotification(`Failed to control ${actuator}`, 'error');
            throw error;
        }
    }

    updateActuatorStatus(status) {
        this.status = { ...this.status, ...status };
        
        // Update UI toggles and indicators
        Object.keys(status).forEach(actuator => {
            this.updateActuatorUI(actuator, status[actuator]);
        });

        // Trigger custom event
        window.dispatchEvent(new CustomEvent('actuatorStatusUpdated', { detail: status }));
    }

    updateActuatorUI(actuator, status) {
        const toggle = document.getElementById(`${actuator}-toggle`);
        const statusIndicator = document.getElementById(`${actuator}-status`);
        
        if (toggle) {
            const isEnabled = status === 'on' || status === true;
            toggle.checked = isEnabled;
            
            // Update visual state
            const switchEl = toggle.closest('.toggle-switch');
            if (switchEl) {
                switchEl.classList.toggle('enabled', isEnabled);
                switchEl.classList.toggle('disabled', !isEnabled);
            }
        }

        if (statusIndicator) {
            statusIndicator.textContent = status === 'on' || status === true ? 'ON' : 'OFF';
            statusIndicator.className = `status-indicator ${status === 'on' || status === true ? 'status-online' : 'status-offline'}`;
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        switch (type) {
            case 'success':
                notification.classList.add('bg-green-500', 'text-white');
                break;
            case 'error':
                notification.classList.add('bg-red-500', 'text-white');
                break;
            case 'warning':
                notification.classList.add('bg-yellow-500', 'text-black');
                break;
            default:
                notification.classList.add('bg-blue-500', 'text-white');
        }
        
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">&times;</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }
}

// Global instances
let sensorManager;
let actuatorController;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize managers
    sensorManager = new SensorDataManager();
    actuatorController = new ActuatorController();
    
    // Setup actuator controls
    document.querySelectorAll('[data-actuator-control]').forEach(element => {
        element.addEventListener('click', async function() {
            const actuator = this.dataset.actuator;
            const action = this.dataset.action;
            const value = this.dataset.value;
            
            try {
                await actuatorController.controlActuator(actuator, action, value);
            } catch (error) {
                console.error('Control failed:', error);
            }
        });
    });
    
    // Setup toggle switches
    document.querySelectorAll('input[type="checkbox"][data-actuator]').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const actuator = this.dataset.actuator;
            const action = this.checked ? 'on' : 'off';
            
            try {
                await actuatorController.controlActuator(actuator, action);
            } catch (error) {
                // Revert toggle state on error
                this.checked = !this.checked;
            }
        });
    });
    
    console.log('Terraponix application initialized');
});

// Export for global access
window.sensorManager = sensorManager;
window.actuatorController = actuatorController;
