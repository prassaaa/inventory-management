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
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body>
    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Mobile Header (Visible only on mobile) -->
            <div class="mobile-header d-md-none">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link text-dark" id="sidebarToggleMobile">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="text-center">
                        <h5 class="mb-0">{{ config('app.name', 'Manajemen Inventaris') }}</h5>
                    </div>
                    <div style="width: 30px"></div> <!-- Spacer to center the title -->
                </div>
            </div>
            
            <!-- Regular Header (Hidden on mobile) -->
            <div class="d-none d-md-block">
                @include('layouts.header')
            </div>
            
            <div class="container-fluid px-4 py-4">
                <!-- Mobile title for current page (optional) -->
                <div class="d-md-none mb-3">
                    <h4 class="text-primary">@yield('title')</h4>
                    <hr>
                </div>
                
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
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Fungsi untuk mengecek apakah tampilan mobile
            function isMobile() {
                return window.innerWidth < 768;
            }
            
            // Default sidebar toggle untuk desktop
            $('#sidebarCollapse').on('click', function() {
                $('#wrapper').toggleClass('toggled');
            });
            
            // Handler untuk tombol toggle sidebar di header mobile
            $('#sidebarToggleMobile').on('click', function(e) {
                e.preventDefault();
                $('#wrapper').toggleClass('toggled');
                $('body').toggleClass('sidebar-open');
            });
            
            // Handler untuk overlay
            $('#sidebar-overlay').on('click', function() {
                $('#wrapper').addClass('toggled');
                $('body').removeClass('sidebar-open');
            });
            
            // Tutup sidebar ketika item menu diklik di mobile
            if (isMobile()) {
                $('.list-group-item').not('.submenu-toggle').on('click', function() {
                    $('#wrapper').addClass('toggled');
                    $('body').removeClass('sidebar-open');
                });
            }
            
            // Handler untuk submenu toggle
            $('.submenu-toggle').on('click', function() {
                // Toggle icon
                const icon = $(this).find('.fas');
                if (icon.hasClass('fa-angle-right')) {
                    icon.removeClass('fa-angle-right').addClass('fa-angle-down');
                } else if (!$(this).hasClass('collapsed')) {
                    icon.removeClass('fa-angle-down').addClass('fa-angle-right');
                }
            });
            
            // Pastikan sidebar disembunyikan saat halaman dimuat di mobile
            if (isMobile()) {
                $('#wrapper').addClass('toggled');
            }
            
            // Handler untuk resize window
            $(window).on('resize', function() {
                if (isMobile()) {
                    $('#wrapper').addClass('toggled');
                    $('body').removeClass('sidebar-open');
                } else {
                    $('#wrapper').removeClass('toggled');
                }
            });
            
            // Perbaikan untuk scroll pada submenu yang terbuka
            $('.submenu-toggle').on('shown.bs.collapse', function() {
                if (isMobile()) {
                    const $activeSubmenu = $($(this).attr('href'));
                    if ($activeSubmenu.length) {
                        const container = $('.sidebar-menu-container');
                        const topPos = $activeSubmenu.position().top;
                        
                        // Hanya scroll jika submenu tidak terlihat sepenuhnya
                        if (topPos + $activeSubmenu.height() > container.height()) {
                            container.animate({
                                scrollTop: container.scrollTop() + topPos - 100
                            }, 300);
                        }
                    }
                }
            });
            
            // Initialize Select2 with mobile-friendly options
            $('.select2').select2({
                theme: "bootstrap-5",
                width: '100%',
                dropdownCssClass: 'select2-dropdown-responsive'
            });
            
            // Initialize DataTables with responsive feature
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true,
                autoWidth: false
            });
            
            // Adjust chart dimensions on window resize
            const resizeCharts = function() {
                if (typeof chartInstances !== 'undefined') {
                    for (let chart of chartInstances) {
                        chart.resize();
                    }
                }
            };
            
            // Handle window resize for charts and select2
            $(window).on('resize', function() {
                // Adjust Select2 width
                $('.select2').css('width', '100%');
                
                // Resize charts if any
                resizeCharts();
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