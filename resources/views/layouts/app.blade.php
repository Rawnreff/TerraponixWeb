<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terraponix - Greenhouse IoT System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            font-size: .875rem;
        }
        
        .feather {
            width: 16px;
            height: 16px;
            vertical-align: text-bottom;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                top: 5rem;
            }
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
        }
        
        .sidebar .nav-link.active {
            color: #007bff;
        }
        
        .sidebar .nav-link:hover {
            color: #007bff;
        }
        
        .sidebar .nav-link .feather {
            margin-right: 4px;
            color: #727272;
        }
        
        .sidebar .nav-link.active .feather {
            color: inherit;
        }
        
        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
        }
        
        /* Navbar */
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
        
        .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }
        
        .navbar .form-control {
            padding: .75rem 1rem;
            border-width: 0;
            border-radius: 0;
        }
        
        .form-control-dark {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .1);
        }
        
        .form-control-dark:focus {
            border-color: transparent;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
        }
        
        /* Main content */
        .main {
            padding-top: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .main {
                padding-left: 40px;
            }
        }
        
        /* Cards */
        .card {
            margin-bottom: 1.5rem;
        }
        
        /* Utilities */
        .border-top { border-top: 1px solid #e5e5e5; }
        .border-bottom { border-bottom: 1px solid #e5e5e5; }
        
        .box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
        
        .lh-condensed { line-height: 1.25; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
            <i class="bi bi-leaf me-2"></i>Terraponix
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="#">
                    <i class="bi bi-box-arrow-right me-2"></i>Sign out
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('sensor-data*') ? 'active' : '' }}" href="{{ url('/sensor-data') }}">
                                <i class="bi bi-graph-up me-2"></i>
                                Sensor Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('actuator-control*') ? 'active' : '' }}" href="{{ url('/actuator-control') }}">
                                <i class="bi bi-gear-wide-connected me-2"></i>
                                Actuator Control
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" href="{{ url('/settings') }}">
                                <i class="bi bi-gear me-2"></i>
                                Settings
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>System Status</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-wifi me-2"></i>
                                <span id="system-status">Online</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-clock me-2"></i>
                                <span id="last-update">Just now</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Update system status
        function updateSystemStatus() {
            fetch('/api/v1/sensor-data/realtime')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('system-status').textContent = 'Online';
                        document.getElementById('system-status').className = 'text-success';
                        document.getElementById('last-update').textContent = 'Just now';
                    } else {
                        document.getElementById('system-status').textContent = 'Offline';
                        document.getElementById('system-status').className = 'text-danger';
                    }
                })
                .catch(error => {
                    document.getElementById('system-status').textContent = 'Error';
                    document.getElementById('system-status').className = 'text-warning';
                });
        }
        
        // Update status every 30 seconds
        setInterval(updateSystemStatus, 30000);
        updateSystemStatus();
    </script>
</body>
</html>