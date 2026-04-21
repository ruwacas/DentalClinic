<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Aquino Dental Clinic Appointment Management System' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/fallback.css') }}?v={{ filemtime(public_path('css/fallback.css')) }}">
    @endif
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <div>
                <h1 class="brand">Aquino Dental Clinic Appointment Management System</h1>
                <p class="subbrand">Where Every Smile Shines Like a Pearl</p>
            </div>
            @auth
                <div class="topbar-actions {{ auth()->user()->role === 'dentist' ? 'dentist-actions' : '' }} {{ auth()->user()->role === 'admin' ? 'admin-actions' : '' }}">
                    @if (auth()->user()->role === 'dentist')
                        <p class="dentist-greeting dentist-greeting-right">Welcome! Dr. {{ strtoupper(auth()->user()->name) }}</p>
                    @endif
                    @if (auth()->user()->role === 'patient')
                        <p class="patient-greeting patient-greeting-right">{{ auth()->user()->name }}</p>
                    @endif
                    @if (auth()->user()->role === 'admin')
                        <a class="role-link" href="{{ route('admin.dashboard') }}">Home</a>
                        <div class="admin-menu js-dropdown-menu">
                            <button type="button" class="admin-menu-toggle dropdown-toggle" aria-expanded="false">
                                <span>Manage Services</span>
                                <span class="admin-menu-arrow" aria-hidden="true">&#9662;</span>
                            </button>
                            <div class="admin-menu-panel" role="menu">
                                <a class="admin-menu-item" href="{{ route('admin.services.create') }}">
                                    <span class="admin-menu-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <span>Add Service</span>
                                </a>
                                <a class="admin-menu-item" href="{{ route('admin.services.edit') }}">
                                    <span class="admin-menu-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M4 20h4l10-10-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            <path d="m12 6 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <span>Update Service</span>
                                </a>
                                <a class="admin-menu-item" href="{{ route('admin.services.delete') }}">
                                    <span class="admin-menu-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M5 7h14M9 7V5h6v2m-8 0 1 12h8l1-12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span>Delete Service</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <a class="role-link" href="{{ route('services') }}">Our Services</a>
                    @endif
                    @if (auth()->user()->role === 'admin')
                        <a class="role-link" href="{{ route('admin.dentists.index') }}">Manage Dentist Account</a>
                    @else
                        <a class="role-link" href="{{ route('home') }}#location">Location</a>
                    @endif
                    @if (auth()->user()->role !== 'dentist')
                        <nav class="role-nav">
                            @if (auth()->user()->role === 'patient')
                                <a class="role-link" href="{{ route('patient.dashboard') }}">Dashboard</a>
                                <a class="role-link" href="{{ route('patient.profile') }}">Profile</a>
                            @endif
                        </nav>
                    @endif
                    @if (auth()->user()->role === 'admin')
                        <div class="topbar-admin-actions">
                            <div class="admin-menu js-dropdown-menu">
                                <button type="button" class="admin-menu-toggle dropdown-toggle" aria-expanded="false">
                                    <span class="admin-greeting">Welcome, {{ auth()->user()->name }}!</span>
                                    <span class="admin-menu-arrow" aria-hidden="true">&#9662;</span>
                                </button>
                                <div class="admin-menu-panel" role="menu">
                                    <a class="admin-menu-item" href="{{ route('admin.accounts.create') }}">
                                        <span class="admin-menu-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <span>Add Admin Account</span>
                                    </a>
                                    <a class="admin-menu-item" href="{{ route('admin.accounts.edit') }}">
                                        <span class="admin-menu-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M4 20h4l10-10-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                <path d="m12 6 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <span>Edit My Account</span>
                                    </a>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="role-link logout-link" type="submit">Logout</button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-ghost" type="submit">Logout</button>
                        </form>
                    @endif
                </div>
            @else
                <div class="topbar-actions">
                    <a class="role-link" href="{{ route('home') }}">Home</a>
                    <a class="role-link" href="{{ route('services') }}">Our Services</a>
                    <a class="role-link" href="{{ route('home') }}#location">Location</a>
                    <a class="role-link" href="{{ route('login.form') }}">Login</a>
                    <a class="btn" href="{{ route('register.form') }}">Book Appointment</a>
                </div>
            @endauth
        </header>

        @if (session('success'))
            <div class="flash flash-success {{ request()->routeIs('admin.accounts.*') || request()->routeIs('admin.dentists.*') ? 'account-toast' : '' }}">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash flash-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="content-flow">
            @yield('content')
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdowns = Array.from(document.querySelectorAll('.js-dropdown-menu'));

        if (!dropdowns.length) {
            return;
        }

        const closeAll = function () {
            dropdowns.forEach((menu) => {
                menu.classList.remove('is-open');
                const menuToggle = menu.querySelector('.dropdown-toggle');
                if (menuToggle) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        };

        dropdowns.forEach((menu) => {
            const toggle = menu.querySelector('.dropdown-toggle');
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', function (event) {
                event.stopPropagation();
                const isOpen = menu.classList.contains('is-open');
                closeAll();
                if (!isOpen) {
                    menu.classList.add('is-open');
                    toggle.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (!dropdowns.some((menu) => menu.contains(event.target))) {
                closeAll();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAll();
            }
        });
    });
    </script>
</body>
</html>
