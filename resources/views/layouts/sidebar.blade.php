<div class="bg-white" id="sidebar-wrapper">
    <!-- Header Sidebar dengan Logo -->
    <div class="sidebar-heading border-bottom bg-primary text-white p-3 d-flex align-items-center">
        <i class="fas fa-box-open me-2"></i>
        <span class="fs-4 fw-bold">{{ config('app.name', 'Inventaris') }}</span>
    </div>

    <!-- Profile Section -->
    <div class="px-3 py-4 d-flex align-items-center border-bottom">
        <div class="avatar-circle me-3 bg-primary">
            <span class="text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-dark">{{ Auth::user()->name }}</h6>
            <small class="text-muted">
                @foreach(Auth::user()->roles as $role)
                    {{ ucfirst($role->name) }}
                @endforeach
            </small>
        </div>
    </div>

    <!-- Menu Section -->
    <div class="list-group list-group-flush pt-2">
        <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-tachometer-alt text-primary"></i>
                </span>
                <span>Dashboard</span>
            </div>
        </a>
        
        @can('view products')
        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('products.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-boxes text-primary"></i>
                </span>
                <span>Produk</span>
            </div>
        </a>
        @endcan
        
        @can('view categories')
        <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('categories.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-tags text-primary"></i>
                </span>
                <span>Kategori</span>
            </div>
        </a>
        @endcan

        @can('view units')
        <a href="{{ route('units.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('units.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-ruler text-primary"></i>
                </span>
                <span>Satuan</span>
            </div>
        </a>
        @endcan
        
        @can('view suppliers')
        <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('suppliers.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-truck text-primary"></i>
                </span>
                <span>Pemasok</span>
            </div>
        </a>
        @endcan

        @can('view stores')
        <a href="{{ route('stores.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('stores.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-store text-primary"></i>
                </span>
                <span>Toko</span>
            </div>
        </a>
        @endcan
        
        <!-- Divider -->
        <div class="sidebar-divider my-2"></div>
        <h6 class="sidebar-heading px-3 text-muted text-uppercase small">Transaksi</h6>
        
        @can('view purchases')
        <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('purchases.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-shopping-cart text-primary"></i>
                </span>
                <span>Pembelian</span>
            </div>
        </a>
        @endcan
        
        @can('view sales')
        <a href="{{ route('sales.index') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('sales.*') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-cash-register text-primary"></i>
                </span>
                <span>Penjualan</span>
            </div>
        </a>
        @endcan

        @can('create sales')
        <a href="{{ route('pos') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('pos') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-shopping-bag text-primary"></i>
                </span>
                <span>Kasir (POS)</span>
            </div>
        </a>
        @endcan
        
        <!-- Divider -->
        <div class="sidebar-divider my-2"></div>
        <h6 class="sidebar-heading px-3 text-muted text-uppercase small">Inventaris</h6>
        
        <!-- Stock Section -->
        <a href="#stockSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('stock.*') ? 'true' : 'false' }}" class="list-group-item list-group-item-action border-0 submenu-toggle {{ request()->routeIs('stock.*') ? 'active-parent' : '' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="icon-wrapper me-3">
                        <i class="fas fa-warehouse text-primary"></i>
                    </span>
                    <span>Stok</span>
                </div>
                <i class="fas {{ request()->routeIs('stock.*') ? 'fa-angle-down' : 'fa-angle-right' }}"></i>
            </div>
        </a>
        <div class="collapse {{ request()->routeIs('stock.*') ? 'show' : '' }}" id="stockSubmenu">
            <div class="bg-light">
                @can('view stock warehouses')
                <a href="{{ route('stock.warehouse') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('stock.warehouse') ? 'active-submenu' : '' }}">
                    <i class="fas fa-boxes me-2 text-primary small"></i> Stok Gudang
                </a>
                @endcan
                
                @can('view stock stores')
                <a href="{{ route('stock.store') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('stock.store') ? 'active-submenu' : '' }}">
                    <i class="fas fa-store me-2 text-primary small"></i> Stok Toko
                </a>
                @endcan
            </div>
        </div>
        
        <!-- Reports Section -->
        <a href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}" class="list-group-item list-group-item-action border-0 submenu-toggle {{ request()->routeIs('reports.*') ? 'active-parent' : '' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="icon-wrapper me-3">
                        <i class="fas fa-chart-bar text-primary"></i>
                    </span>
                    <span>Laporan</span>
                </div>
                <i class="fas {{ request()->routeIs('reports.*') ? 'fa-angle-down' : 'fa-angle-right' }}"></i>
            </div>
        </a>
        <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsSubmenu">
            <div class="bg-light">
                @can('view sales')
                <a href="{{ route('reports.sales') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('reports.sales') ? 'active-submenu' : '' }}">
                    <i class="fas fa-chart-line me-2 text-primary small"></i> Laporan Penjualan
                </a>
                @endcan
                
                @can('view purchases')
                <a href="{{ route('reports.purchases') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('reports.purchases') ? 'active-submenu' : '' }}">
                    <i class="fas fa-chart-pie me-2 text-primary small"></i> Laporan Pembelian
                </a>
                @endcan
                
                @can('view stock warehouses')
                <a href="{{ route('reports.inventory') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('reports.inventory') ? 'active-submenu' : '' }}">
                    <i class="fas fa-dolly me-2 text-primary small"></i> Laporan Inventaris
                </a>
                @endcan
                
                @can('view financial reports')
                <a href="{{ route('reports.finance') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('reports.finance') ? 'active-submenu' : '' }}">
                    <i class="fas fa-coins me-2 text-primary small"></i> Laporan Keuangan
                </a>
                
                <a href="{{ route('reports.profit-loss') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('reports.profit-loss') ? 'active-submenu' : '' }}">
                    <i class="fas fa-dollar-sign me-2 text-primary small"></i> Laporan Laba/Rugi
                </a>
                @endcan
            </div>
        </div>
        
        <!-- TAMBAHAN: Sistem Manajemen Menu -->
        @canany(['manage users', 'manage roles'])
        <!-- Divider -->
        <div class="sidebar-divider my-2"></div>
        <h6 class="sidebar-heading px-3 text-muted text-uppercase small">Sistem</h6>
        
        <!-- System Management Section -->
        <a href="#systemSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('backups.*') ? 'true' : 'false' }}" class="list-group-item list-group-item-action border-0 submenu-toggle {{ (request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('backups.*')) ? 'active-parent' : '' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="icon-wrapper me-3">
                        <i class="fas fa-cogs text-primary"></i>
                    </span>
                    <span>Manajemen Sistem</span>
                </div>
                <i class="fas {{ (request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('backups.*')) ? 'fa-angle-down' : 'fa-angle-right' }}"></i>
            </div>
        </a>
        <div class="collapse {{ (request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('backups.*')) ? 'show' : '' }}" id="systemSubmenu">
            <div class="bg-light">
                @can('manage users')
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('users.*') ? 'active-submenu' : '' }}">
                    <i class="fas fa-users me-2 text-primary small"></i> Pengguna
                </a>
                @endcan
                
                @can('manage roles')
                <a href="{{ route('roles.index') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('roles.*') ? 'active-submenu' : '' }}">
                    <i class="fas fa-user-tag me-2 text-primary small"></i> Peran
                </a>
                
                <a href="{{ route('permissions.index') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('permissions.*') ? 'active-submenu' : '' }}">
                    <i class="fas fa-lock me-2 text-primary small"></i> Izin
                </a>
                @endcan
                
                @can('backup database')
                <a href="{{ route('backups.index') }}" class="list-group-item list-group-item-action border-0 ps-5 py-2 {{ request()->routeIs('backups.*') ? 'active-submenu' : '' }}">
                    <i class="fas fa-database me-2 text-primary small"></i> Database
                </a>
                @endcan
            </div>
        </div>
        @endcanany
        
        <!-- Profile Link -->
        <a href="{{ route('profile') }}" class="list-group-item list-group-item-action border-0 {{ request()->routeIs('profile') ? 'active-menu' : '' }}">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-user-circle text-primary"></i>
                </span>
                <span>Profil Saya</span>
            </div>
        </a>
        
        <!-- Logout Link -->
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="list-group-item list-group-item-action border-0">
            <div class="d-flex align-items-center">
                <span class="icon-wrapper me-3">
                    <i class="fas fa-sign-out-alt text-primary"></i>
                </span>
                <span>Logout</span>
            </div>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

    <!-- Footer Section -->
    <div class="mt-auto text-center border-top p-3">
        <small class="text-muted">© {{ date('Y') }} {{ config('app.name', 'Inventaris') }}</small>
    </div>
</div>