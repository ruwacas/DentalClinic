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
                <div class="topbar-actions {{ auth()->user()->role === 'dentist' ? 'dentist-actions' : '' }}">
                    @if (auth()->user()->role === 'dentist')
                        <p class="dentist-greeting dentist-greeting-right">Welcome! Dr. {{ strtoupper(auth()->user()->name) }}</p>
                    @endif
                    @if (auth()->user()->role === 'patient')
                        <p class="patient-greeting patient-greeting-right">{{ auth()->user()->name }}</p>
                    @endif
                    <a class="role-link" href="{{ route('home') }}#location">Location</a>
                    @if (auth()->user()->role !== 'dentist')
                        <nav class="role-nav">
                            @if (auth()->user()->role === 'patient')
                                <a class="role-link" href="{{ route('patient.dashboard') }}">Dashboard</a>
                                <a class="role-link" href="{{ route('patient.profile') }}">Profile</a>
                            @elseif (auth()->user()->role === 'admin')
                                <a class="role-link" href="{{ route('admin.dashboard') }}">Admin Panel</a>
                            @endif
                        </nav>
                    @endif
                    @if (auth()->user()->role === 'admin')
                        <span class="chip">{{ ucfirst(auth()->user()->role) }}</span>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-ghost" type="submit">Logout</button>
                    </form>
                </div>
            @else
                <div class="topbar-actions">
                    <a class="role-link" href="{{ route('home') }}">Home</a>
                    <a class="role-link" href="{{ route('home') }}#location">Location</a>
                    <a class="role-link" href="{{ route('login.form') }}">Login</a>
                    <a class="btn" href="{{ route('register.form') }}">Book Appointment</a>
                </div>
            @endauth
        </header>

        @if (session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
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
</body>
</html>
