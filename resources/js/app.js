import axios from 'axios';
import './bootstrap';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

// Global axios setup
window.axios = axios;
window.Chart = Chart;

// Dashboard functionality
class Dashboard {
    constructor() {
        this.charts = {};
        this.updateInterval = null;
        this.wsConnection = null;
        this.currentData = {};
        this.init();
    }

    init() {
        this.setupCharts();
        this.setupRealTimeUpdates();
        this.setupEventListeners();
        this.loadInitialData();
    }

    setupCharts() {
        // Temperature & Humidity Chart
        this.setupTemperatureHumidityChart();
        
        // pH & Water Level Chart
        this.setupWaterQualityChart();
        
        // Light Intensity Chart
        this.setupLightChart();
        
        // Historical Trends Chart
        this.setupHistoricalChart();
    }

    setupTemperatureHumidityChart() {
        const ctx = document.getElementById('temp-humidity-chart');
        if (!ctx) return;

        this.charts.tempHumidity = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Temperature (°C)',
                    data: [],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Humidity (%)',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'HH:mm'
                            }
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
    }

    setupWaterQualityChart() {
        const ctx = document.getElementById('water-quality-chart');
        if (!ctx) return;

        this.charts.waterQuality = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'pH Level',
                    data: [],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Water Level (mm)',
                    data: [],
                    borderColor: 'rgb(6, 182, 212)',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
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
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'HH:mm'
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'pH Level'
                        },
                        min: 4,
                        max: 8
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Water Level (mm)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    setupLightChart() {
        const ctx = document.getElementById('light-chart');
        if (!ctx) return;

        this.charts.light = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Light Intensity (Lux)',
                    data: [],
                    borderColor: 'rgb(251, 191, 36)',
                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'HH:mm'
                            }
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Light Intensity (Lux)'
                        }
                    }
                }
            }
        });
    }

    setupHistoricalChart() {
        const ctx = document.getElementById('historical-chart');
        if (!ctx) return;

        this.charts.historical = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Temperature',
                    data: [],
                    borderColor: 'rgb(239, 68, 68)',
                    tension: 0.4
                }, {
                    label: 'Humidity',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.4
                }, {
                    label: 'pH',
                    data: [],
                    borderColor: 'rgb(34, 197, 94)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'MMM dd'
                            }
                        }
                    }
                }
            }
        });
    }

    setupRealTimeUpdates() {
        // Update data every 5 seconds
        this.updateInterval = setInterval(() => {
            this.updateSensorData();
            this.updateActuatorStatus();
        }, 5000);

        // Initial load
        this.updateSensorData();
        this.updateActuatorStatus();
    }

    async updateSensorData() {
        try {
            const response = await axios.get('/api/latest-sensor-data');
            const data = response.data;
            
            if (data.success && data.data) {
                this.currentData = data.data;
                this.updateMetricCards(data.data);
                this.updateCharts(data.data);
                this.updateTrends(data.data);
                this.updateSystemStatus(data.data);
            }
        } catch (error) {
            console.error('Error fetching sensor data:', error);
            this.showConnectionError();
        }
    }

    async updateActuatorStatus() {
        try {
            const response = await axios.get('/api/actuator-status');
            const data = response.data;
            
            if (data.success && data.data) {
                this.updateActuatorDisplay(data.data);
            }
        } catch (error) {
            console.error('Error fetching actuator status:', error);
        }
    }

    updateMetricCards(data) {
        // Update temperature
        this.updateElement('temperature-value', data.temperature + '°C');
        this.updateElement('temperature-trend', this.getTrendIcon(data.temperature_trend));
        
        // Update humidity
        this.updateElement('humidity-value', data.humidity + '%');
        this.updateElement('humidity-trend', this.getTrendIcon(data.humidity_trend));
        
        // Update pH
        this.updateElement('ph-value', data.ph_value.toFixed(1));
        this.updateElement('ph-trend', this.getTrendIcon(data.ph_trend));
        
        // Update light
        this.updateElement('light-value', data.light_intensity + ' Lux');
        this.updateElement('light-trend', this.getTrendIcon(data.light_trend));
        
        // Update water level
        this.updateElement('water-level-value', data.water_level + ' mm');
        this.updateElement('water-level-trend', this.getTrendIcon(data.water_level_trend));
        
        // Update CO2 if available
        if (data.co2_level) {
            this.updateElement('co2-value', data.co2_level + ' ppm');
        }
        
        // Update soil moisture if available
        if (data.soil_moisture) {
            this.updateElement('soil-moisture-value', data.soil_moisture + '%');
        }
        
        // Update last update time
        this.updateElement('last-update', 'Last updated: ' + new Date().toLocaleTimeString());
    }

    updateCharts(data) {
        const now = new Date();
        
        // Update temperature/humidity chart
        if (this.charts.tempHumidity) {
            const chart = this.charts.tempHumidity;
            chart.data.labels.push(now);
            chart.data.datasets[0].data.push(data.temperature);
            chart.data.datasets[1].data.push(data.humidity);
            
            // Keep only last 20 points
            if (chart.data.labels.length > 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
            }
            chart.update('none');
        }
        
        // Update water quality chart
        if (this.charts.waterQuality) {
            const chart = this.charts.waterQuality;
            chart.data.labels.push(now);
            chart.data.datasets[0].data.push(data.ph_value);
            chart.data.datasets[1].data.push(data.water_level);
            
            if (chart.data.labels.length > 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
            }
            chart.update('none');
        }
        
        // Update light chart
        if (this.charts.light) {
            const chart = this.charts.light;
            chart.data.labels.push(now);
            chart.data.datasets[0].data.push(data.light_intensity);
            
            if (chart.data.labels.length > 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
            }
            chart.update('none');
        }
    }

    updateActuatorDisplay(data) {
        // Update curtain position
        this.updateElement('curtain-position', data.curtain_position + '%');
        const curtainSlider = document.getElementById('curtain-slider');
        if (curtainSlider) {
            curtainSlider.value = data.curtain_position;
        }
        
        // Update fan status
        this.updateToggleSwitch('fan-toggle', data.fan_status);
        this.updateElement('fan-status', data.fan_status ? 'ON' : 'OFF');
        
        // Update pump status
        this.updateToggleSwitch('pump-toggle', data.water_pump_status);
        this.updateElement('pump-status', data.water_pump_status ? 'ON' : 'OFF');
    }

    updateToggleSwitch(elementId, isActive) {
        const toggle = document.getElementById(elementId);
        if (!toggle) return;
        
        const button = toggle.querySelector('.toggle-button');
        if (isActive) {
            toggle.classList.remove('inactive');
            toggle.classList.add('active');
            button.classList.remove('inactive');
            button.classList.add('active');
        } else {
            toggle.classList.remove('active');
            toggle.classList.add('inactive');
            button.classList.remove('active');
            button.classList.add('inactive');
        }
        toggle.dataset.state = isActive ? 'on' : 'off';
    }

    getTrendIcon(trend) {
        if (!trend) return '';
        switch (trend) {
            case 'up': return '<i class="bi bi-trend-up text-green-500"></i>';
            case 'down': return '<i class="bi bi-trend-down text-red-500"></i>';
            default: return '<i class="bi bi-dash text-gray-500"></i>';
        }
    }

    updateElement(id, content) {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    }

    setupEventListeners() {
        // Setup actuator controls
        this.setupActuatorControls();
        
        // Setup settings page
        this.setupSettingsPage();
        
        // Setup navigation
        this.setupNavigation();
    }

    setupActuatorControls() {
        // Curtain controls
        const curtainSlider = document.getElementById('curtain-slider');
        if (curtainSlider) {
            curtainSlider.addEventListener('input', (e) => {
                this.updateElement('curtain-position', e.target.value + '%');
            });
            
            curtainSlider.addEventListener('change', (e) => {
                this.controlActuator('curtain', parseInt(e.target.value));
            });
        }
        
        // Curtain buttons
        this.setupButton('curtain-close', () => this.controlActuator('curtain', 0));
        this.setupButton('curtain-open', () => this.controlActuator('curtain', 100));
        
        // Fan toggle
        this.setupToggle('fan-toggle', (state) => this.controlActuator('fan', state));
        
        // Pump toggle
        this.setupToggle('pump-toggle', (state) => this.controlActuator('water_pump', state));
        
        // Quick actions
        this.setupButton('emergency-stop', () => this.emergencyStop());
        this.setupButton('auto-mode', () => this.setAutoMode(true));
        this.setupButton('manual-mode', () => this.setAutoMode(false));
    }

    setupButton(id, callback) {
        const button = document.getElementById(id);
        if (button) {
            button.addEventListener('click', callback);
        }
    }

    setupToggle(id, callback) {
        const toggle = document.getElementById(id);
        if (toggle) {
            toggle.addEventListener('click', () => {
                const currentState = toggle.dataset.state === 'on';
                const newState = !currentState;
                this.updateToggleSwitch(id, newState);
                callback(newState);
            });
        }
    }

    async controlActuator(type, value) {
        try {
            const response = await axios.post('/api/control-actuator', {
                type: type,
                value: value
            });
            
            if (response.data.success) {
                this.showSuccess(`${type} updated successfully`);
                this.updateActuatorStatus(); // Refresh status
            }
        } catch (error) {
            console.error('Error controlling actuator:', error);
            this.showError('Failed to control actuator');
        }
    }

    async emergencyStop() {
        try {
            await Promise.all([
                this.controlActuator('fan', false),
                this.controlActuator('water_pump', false),
                this.controlActuator('curtain', 50)
            ]);
            this.showSuccess('Emergency stop activated');
        } catch (error) {
            this.showError('Emergency stop failed');
        }
    }

    async setAutoMode(enabled) {
        try {
            const response = await axios.post('/api/settings', {
                auto_mode: enabled
            });
            
            if (response.data.success) {
                this.showSuccess(`Auto mode ${enabled ? 'enabled' : 'disabled'}`);
            }
        } catch (error) {
            this.showError('Failed to update auto mode');
        }
    }

    setupSettingsPage() {
        const settingsForm = document.getElementById('settings-form');
        if (settingsForm) {
            settingsForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.saveSettings();
            });
        }
        
        // Load current settings
        this.loadSettings();
        
        // Setup toggle switches in settings
        this.setupToggle('auto-mode-toggle', (state) => this.updateAutoMode(state));
        this.setupToggle('notifications-toggle', (state) => this.updateNotifications(state));
        this.setupToggle('logging-toggle', (state) => this.updateLogging(state));
        
        // Setup other settings buttons
        this.setupButton('reset-defaults', () => this.resetDefaults());
        this.setupButton('test-settings', () => this.testSettings());
    }

    async loadSettings() {
        try {
            const response = await axios.get('/api/settings');
            const settings = response.data.data;
            
            if (settings) {
                this.updateElement('temp-threshold', settings.temp_threshold);
                this.updateElement('light-threshold', settings.light_threshold);
                this.updateElement('water-threshold', settings.water_level_threshold);
                this.updateElement('ph-min', settings.ph_min);
                this.updateElement('ph-max', settings.ph_max);
                
                // Update form inputs
                const inputs = ['temp-threshold', 'light-threshold', 'water-threshold', 'ph-min', 'ph-max'];
                inputs.forEach(id => {
                    const input = document.getElementById(id);
                    if (input && settings[input.name]) {
                        input.value = settings[input.name];
                    }
                });
                
                this.updateToggleSwitch('auto-mode-toggle', settings.auto_mode);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    async saveSettings() {
        try {
            const form = document.getElementById('settings-form');
            const formData = new FormData(form);
            const settings = Object.fromEntries(formData);
            
            // Convert numeric values
            settings.temp_threshold = parseFloat(settings.temp_threshold);
            settings.light_threshold = parseInt(settings.light_threshold);
            settings.water_level_threshold = parseInt(settings.water_level_threshold);
            settings.ph_min = parseFloat(settings.ph_min);
            settings.ph_max = parseFloat(settings.ph_max);
            
            const response = await axios.post('/api/settings', settings);
            
            if (response.data.success) {
                this.showSuccess('Settings saved successfully');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            this.showError('Failed to save settings');
        }
    }

    setupNavigation() {
        // Handle navigation highlighting
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    }

    loadInitialData() {
        // Load historical data for charts
        this.loadHistoricalData();
    }

    async loadHistoricalData() {
        try {
            const response = await axios.get('/api/sensor-data/history?hours=24');
            const data = response.data.data;
            
            if (data && data.length > 0) {
                // Populate charts with historical data
                data.forEach(reading => {
                    const timestamp = new Date(reading.created_at);
                    
                    if (this.charts.tempHumidity) {
                        this.charts.tempHumidity.data.labels.push(timestamp);
                        this.charts.tempHumidity.data.datasets[0].data.push(reading.temperature);
                        this.charts.tempHumidity.data.datasets[1].data.push(reading.humidity);
                    }
                    
                    if (this.charts.waterQuality) {
                        this.charts.waterQuality.data.labels.push(timestamp);
                        this.charts.waterQuality.data.datasets[0].data.push(reading.ph_value);
                        this.charts.waterQuality.data.datasets[1].data.push(reading.water_level);
                    }
                    
                    if (this.charts.light) {
                        this.charts.light.data.labels.push(timestamp);
                        this.charts.light.data.datasets[0].data.push(reading.light_intensity);
                    }
                });
                
                // Update all charts
                Object.values(this.charts).forEach(chart => chart.update());
            }
        } catch (error) {
            console.error('Error loading historical data:', error);
        }
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    showConnectionError() {
        const lastUpdate = document.getElementById('last-update');
        if (lastUpdate) {
            lastUpdate.innerHTML = '<span class="text-red-500">Connection error - ' + new Date().toLocaleTimeString() + '</span>';
        }
    }

    destroy() {
        // Clean up intervals and charts
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        
        Object.values(this.charts).forEach(chart => chart.destroy());
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.Dashboard = new Dashboard();
    
    // Handle page-specific functionality
    const pageElement = document.querySelector('[data-page]');
    if (pageElement) {
        const page = pageElement.dataset.page;
        
        switch (page) {
            case 'dashboard':
                // Dashboard-specific initialization
                break;
            case 'actuator-control':
                // Actuator control page specific
                break;
            case 'settings':
                // Settings page specific
                break;
        }
    }
});

// Handle page unload
window.addEventListener('beforeunload', function() {
    if (window.Dashboard) {
        window.Dashboard.destroy();
    }
});
