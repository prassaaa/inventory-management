<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Manajemen Inventaris') }} - @yield('title')</title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #60a5fa;
            --light-color: #f3f4f6;
            --dark-color: #1e3a8a;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }
        
        #wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
        }
        
        #sidebar-wrapper {
            min-width: 250px;
            max-width: 250px;
            transition: all 0.3s;
        }
        
        #page-content-wrapper {
            min-width: 100vw;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        #wrapper.toggled #sidebar-wrapper {
            margin-left: -250px;
        }
        
        .list-group-item-action:hover {
            background-color: var(--light-color);
            color: var(--primary-color) !important;
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .card-header {
            background-color: white;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-color);
            border-color: var(--dark-color);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .border-left-primary {
            border-left: 4px solid var(--primary-color);
        }
        
        .border-left-success {
            border-left: 4px solid #10b981;
        }
        
        .border-left-info {
            border-left: 4px solid #3b82f6;
        }
        
        .border-left-warning {
            border-left: 4px solid #f59e0b;
        }
        
        .active {
            border-left: 4px solid var(--primary-color);
        }
        
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }
            
            #page-content-wrapper {
                min-width: 0;
                width: 100%;
            }
            
            #wrapper.toggled #sidebar-wrapper {
                margin-left: -250px;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            @include('layouts.header')
            
            <div class="container-fluid px-4 py-4">
                @yield('content')
            </div>
            
            @include('layouts.footer')
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#wrapper').toggleClass('toggled');
            });
            
            // Initialize Select2
            $('.select2').select2({
                theme: "bootstrap-5"
            });
            
            // Initialize DataTables with custom styling
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true
            });
            
            // Tooltip initialization
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>