<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Terraponix - Greenhouse Monitoring')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans antialiased">
    <div class="min-h-full">
        <!-- Sidebar -->
        <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
            <div class="flex min-h-0 flex-1 flex-col sidebar">
                <!-- Logo -->
                <div class="flex h-16 flex-shrink-0 items-center px-6">
                    <h1 class="text-2xl font-bold text-white">
                        <i class="bi bi-cpu mr-2"></i>Terraponix
                    </h1>
                </div>
                
                <!-- Navigation -->
                <div class="flex flex-1 flex-col overflow-y-auto">
                    <nav class="flex-1 space-y-1 px-2 py-4">
                        <a href="{{ route('dashboard') }}" 
                           class="sidebar-item {{ Request::is('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                            Dashboard
                        </a>
                        
                        <a href="{{ route('sensor.data') }}" 
                           class="sidebar-item {{ Request::is('sensor-data') ? 'active' : '' }}">
                            <i class="bi bi-graph-up mr-3 text-lg"></i>
                            Sensor Data
                        </a>
                        
                        <a href="{{ route('actuator.control') }}" 
                           class="sidebar-item {{ Request::is('actuator-control') ? 'active' : '' }}">
                            <i class="bi bi-gear mr-3 text-lg"></i>
                            Actuator Control
                        </a>
                        
                        <a href="{{ route('settings') }}" 
                           class="sidebar-item {{ Request::is('settings') ? 'active' : '' }}">
                            <i class="bi bi-sliders mr-3 text-lg"></i>
                            Settings
                        </a>
                    </nav>
                    
                    <!-- System Status -->
                    <div class="flex-shrink-0 p-4">
                        <div class="bg-gray-800 rounded-lg p-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 bg-green-400 rounded-full pulse-glow"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-white">System Online</p>
                                    <p class="text-xs text-gray-400" id="system-uptime">Uptime: --</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div class="md:hidden">
            <div class="fixed inset-0 z-50 flex" id="mobile-sidebar" style="display: none;">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="toggleMobileSidebar()"></div>
                <div class="relative flex w-full max-w-xs flex-1 flex-col sidebar">
                    <div class="absolute top-0 right-0 -mr-12 pt-2">
                        <button type="button" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" onclick="toggleMobileSidebar()">
                            <i class="bi bi-x-lg text-white"></i>
                        </button>
                    </div>
                    <!-- Same navigation as desktop -->
                    <div class="flex h-16 flex-shrink-0 items-center px-6">
                        <h1 class="text-2xl font-bold text-white">
                            <i class="bi bi-cpu mr-2"></i>Terraponix
                        </h1>
                    </div>
                    <div class="flex flex-1 flex-col overflow-y-auto">
                        <nav class="flex-1 space-y-1 px-2 py-4">
                            <a href="{{ route('dashboard') }}" class="sidebar-item {{ Request::is('dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2 mr-3 text-lg"></i>Dashboard
                            </a>
                            <a href="{{ route('sensor.data') }}" class="sidebar-item {{ Request::is('sensor-data') ? 'active' : '' }}">
                                <i class="bi bi-graph-up mr-3 text-lg"></i>Sensor Data
                            </a>
                            <a href="{{ route('actuator.control') }}" class="sidebar-item {{ Request::is('actuator-control') ? 'active' : '' }}">
                                <i class="bi bi-gear mr-3 text-lg"></i>Actuator Control
                            </a>
                            <a href="{{ route('settings') }}" class="sidebar-item {{ Request::is('settings') ? 'active' : '' }}">
                                <i class="bi bi-sliders mr-3 text-lg"></i>Settings
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="md:pl-64 flex flex-col flex-1">
            <!-- Top bar for mobile -->
            <div class="sticky top-0 z-10 md:hidden pl-1 pt-1 sm:pl-3 sm:pt-3 bg-gray-50">
                <button type="button" class="-ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" onclick="toggleMobileSidebar()">
                    <i class="bi bi-list text-xl"></i>
                </button>
            </div>
            
            <!-- Page content -->
            <main class="flex-1">
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
                        <!-- Page header -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                                    <p class="mt-1 text-sm text-gray-600">@yield('page-description', 'Monitor and control your greenhouse environment')</p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="bi bi-clock mr-1"></i>
                                        <span id="current-time">--:--</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="status-indicator status-online">
                                            <i class="bi bi-wifi mr-1"></i>
                                            Connected
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Page content -->
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'flex' : 'none';
        }

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit'
            });
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Update time every second
        setInterval(updateTime, 1000);
        updateTime(); // Initial call

        // Set up axios CSRF token
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    </script>
    
    @stack('scripts')
</body>
</html>