<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Project')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">


    @stack('styles')

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 230px;
            height: 100%;
            background: #343a40;
            color: #fff;
            padding: 15px;
            overflow-y: auto;
        }

        .sidebar h4 {
            color: #fff;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #ddd;
            padding: 10px;
            text-decoration: none;
            margin-bottom: 8px;
            border-radius: 5px;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: #0d6efd;
            color: #fff;
        }

        .content {
            margin-left: 230px;
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        footer {
            text-align: center;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #ddd;
            margin-top: auto;
        }
        a{
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4>My App</h4>
        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a>

        @auth('admin')
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('designation_index') }}"
                class="{{ request()->routeIs('designation_index') ? 'active' : '' }}">Designation</a>
            <a href="{{ route('employee_index') }}"
                class="{{ request()->routeIs('employee_index') ? 'active' : '' }}">Employee</a>
            <a href="{{ route('project_index') }}"
                class="{{ request()->routeIs('project_index') ? 'active' : '' }}">Projects</a>
            <a href="{{ route('employee_project_index') }}"
                class="{{ request()->routeIs('employee_project_index') ? 'active' : '' }}">Project Detail</a>
            <a href="{{ route('logout') }}" class="text-danger{{ request()->routeIs('logout') ? 'active' : '' }}">Logout</a>

        @endauth
    </div>

    <div class="content d-flex flex-column">
        <main class="flex-grow-1">
            @yield('content')
        </main>
        <footer>
            <p class="mb-0">&copy; {{ date('Y') }} My Project. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('scripts')
</body>

</html>