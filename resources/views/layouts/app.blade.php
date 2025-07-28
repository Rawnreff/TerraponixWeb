<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terraponix - Greenhouse Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --sidebar-bg: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            --card-shadow: 0 10px 25px rgba(0,0,0,0.1);
            --hover-transform: translateY(-2px);
            --border-radius: 16px;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 198, 255, 0.3) 0%, transparent 50%);
            z-index: -1;
            animation: backgroundFloat 20s ease-in-out infinite;
        }

        @keyframes backgroundFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        /* Modern Sidebar */
        .sidebar {
            background: var(--sidebar-bg);
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .sidebar h4 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            position: relative;
        }

        .sidebar h4::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background: var(--primary-gradient);
            border-radius: 1px;
        }

        /* Enhanced Navigation */
        .list-group-item {
            background: transparent !important;
            border: none !important;
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 1rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: var(--border-radius);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .list-group-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .list-group-item:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            transform: var(--hover-transform);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .list-group-item:hover::before {
            left: 100%;
        }

        .list-group-item.active {
            background: var(--primary-gradient) !important;
            color: white !important;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            transform: var(--hover-transform);
        }

        .list-group-item i {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            transition: transform 0.3s ease;
        }

        .list-group-item:hover i,
        .list-group-item.active i {
            transform: scale(1.1);
        }

        /* Main Content Area */
        main {
            padding: 2rem !important;
            position: relative;
        }

        /* Glass Card Effect */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: var(--hover-transform);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card:hover::before {
            transform: scaleX(1);
        }

        /* Enhanced Buttons */
        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: var(--hover-transform);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }

        .btn-warning {
            background: var(--warning-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                border-radius: 0;
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            main {
                padding: 1rem !important;
                margin-left: 0 !important;
            }

            .mobile-menu-btn {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1060;
                background: var(--primary-gradient);
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                color: white;
                box-shadow: var(--card-shadow);
                transition: all 0.3s ease;
            }

            .mobile-menu-btn:hover {
                transform: scale(1.1);
            }
        }

        /* Status Indicators */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        .status-online {
            background: linear-gradient(45deg, #4ade80, #22c55e);
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
        }

        .status-offline {
            background: linear-gradient(45deg, #f87171, #ef4444);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Floating Action Styles */
        .floating-stats {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .stat-badge {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            margin: 0.25rem 0;
            box-shadow: var(--card-shadow);
            font-size: 0.875rem;
            font-weight: 500;
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Enhanced Form Controls */
        .form-control, .form-select {
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn d-md-none" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="container-fluid p-0 min-vh-100">
        <div class="row g-0">
            <!-- Modern Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar d-flex flex-column" id="sidebar" style="min-height: 100vh;">
                <div>
                    <div class="text-center py-4">
                        <h4>Terraponix</h4>
                        <div class="d-flex justify-content-center align-items-center mt-2">
                            <span class="status-indicator status-online"></span>
                            <small class="text-white-50">System Online</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ Request::is('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('sensor.data') }}" class="list-group-item list-group-item-action {{ Request::is('sensor-data') ? 'active' : '' }}">
                            <i class="bi bi-graph-up"></i> Sensor Data
                        </a>
                        <a href="{{ route('actuator.control') }}" class="list-group-item list-group-item-action {{ Request::is('actuator-control') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i> Actuator Control
                        </a>
                        <a href="{{ route('settings') }}" class="list-group-item list-group-item-action {{ Request::is('settings') ? 'active' : '' }}">
                            <i class="bi bi-sliders"></i> Settings
                        </a>
                    </div>
                    <!-- System Info -->
                    <div class="mt-auto p-3">
                        <div class="card bg-transparent border-0">
                            <div class="card-body p-2 text-center">
                                <small class="text-white-50 d-block">Last Update</small>
                                <small class="text-white" id="lastUpdate">--:--:--</small>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Enhanced Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Update last update time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const lastUpdateEl = document.getElementById('lastUpdate');
            if (lastUpdateEl) {
                lastUpdateEl.textContent = timeString;
            }
        }

        // Update time every second
        setInterval(updateTime, 1000);
        updateTime(); // Initial call

        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add loading animation for cards
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Enhanced interactions
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Create ripple effect
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            .btn {
                position: relative;
                overflow: hidden;
            }
            
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.4);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>