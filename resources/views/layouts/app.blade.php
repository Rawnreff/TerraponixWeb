<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terraponix - Greenhouse Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar min-vh-100">
                <div class="text-center py-4">
                    <h4 class="text-white">Terraponix</h4>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ Request::is('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('sensor.data') }}" class="list-group-item list-group-item-action {{ Request::is('sensor-data') ? 'active' : '' }}">
                        <i class="bi bi-graph-up me-2"></i> Sensor Data
                    </a>
                    <a href="{{ route('actuator.control') }}" class="list-group-item list-group-item-action {{ Request::is('actuator-control') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i> Actuator Control
                    </a>
                    <a href="{{ route('settings') }}" class="list-group-item list-group-item-action {{ Request::is('settings') ? 'active' : '' }}">
                        <i class="bi bi-sliders me-2"></i> Settings
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>