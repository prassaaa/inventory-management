@extends('layouts.app')

@section('title', 'Laporan Inventaris')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-clipboard-list me-2 text-primary"></i> Laporan Inventaris
            </h1>
            <p class="text-muted">Analisis stok produk di gudang dan toko</p>
        </div>
        <div>
            <a href="{{ route('reports.inventory.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.inventory') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="store_id" class="form-label">Lokasi</label>
                            <select class="form-select" id="store_id" name="store_id">
                                <option value="">Gudang (Default)</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="stock_status" class="form-label">Status Stok</label>
                            <select class="form-select" id="stock_status" name="stock_status">
                                <option value="">Semua Status</option>
                                <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>Tersedia</option>
                                <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Hampir Habis</option>
                                <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Habis</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Produk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_products }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Nilai Stok</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($total_stock_value, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Stok Rendah</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $low_stock_count }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Stok Habis</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $out_of_stock_count }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(count($category_data) > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Nilai Stok per Kategori</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="categoryStockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Inventaris</h6>
            <div class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari produk...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="inventoryTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                            <th>Harga Beli</th>
                            <th>Nilai Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($products) > 0)
                            @foreach($products as $product)
                                <tr>
                                    <td><span class="fw-medium">{{ $product->code }}</span></td>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                            {{ $product->category->name ?? 'Tanpa Kategori' }}
                                        </span>
                                    </td>
                                    <td>{{ $product->baseUnit->name ?? '-' }}</td>
                                    <td>{{ $product->stock_quantity }}</td>
                                    <td>{{ $product->min_stock }}</td>
                                    <td>
                                        @if($product->stock_quantity <= 0)
                                            <span class="badge bg-danger-light text-danger rounded-pill px-2">Habis</span>
                                        @elseif($product->stock_quantity < $product->min_stock)
                                            <span class="badge bg-warning-light text-warning rounded-pill px-2">Hampir Habis</span>
                                        @else
                                            <span class="badge bg-success-light text-success rounded-pill px-2">Tersedia</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($product->purchase_price ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format(($product->stock_quantity * ($product->purchase_price ?? 0)), 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data inventaris yang tersedia</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hanya inisialisasi tabel jika ada data
        if ($('#inventoryTable tbody tr').length > 1 || 
            ($('#inventoryTable tbody tr').length === 1 && $('#inventoryTable tbody tr td').length > 1)) {
            
            // Cek jika tabel sudah diinisialisasi sebelumnya
            if ($.fn.dataTable.isDataTable('#inventoryTable')) {
                $('#inventoryTable').DataTable().destroy();
            }
            
            // Inisialisasi DataTable baru
            var table = $('#inventoryTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 10,
                order: [[1, 'asc']],
                destroy: true // Pastikan opsi destroy diaktifkan
            });
            
            // Custom search
            $('#customSearch').keyup(function() {
                table.search($(this).val()).draw();
            });
        }
        
        @if(count($category_data) > 0)
        // Category Stock Chart
        var ctx = document.getElementById("categoryStockChart");
        var categoryStockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($category_data, 'name')) !!},
                datasets: [{
                    label: "Nilai Stok",
                    backgroundColor: "#4e73df",
                    hoverBackgroundColor: "#2e59d9",
                    borderColor: "#4e73df",
                    data: {!! json_encode(array_column($category_data, 'value')) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Nilai Stok: Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                }
            }
        });
        @endif
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Format numbers with commas
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>
@endsection