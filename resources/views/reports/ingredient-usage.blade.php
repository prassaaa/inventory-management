@extends('layouts.app')

@section('title', 'Laporan Penggunaan Bahan Baku')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Laporan Penggunaan Bahan Baku
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportReport()">
                            <i class="fas fa-download me-1"></i>
                            Export Excel
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('reports.ingredient-usage') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label fw-bold">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="{{ $startDate }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label fw-bold">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="{{ $endDate }}" required>
                            </div>
                            
                            @if($canSelectStore)
                            <div class="col-md-2">
                                <label for="store_id" class="form-label fw-bold">Outlet</label>
                                <select class="form-select" id="store_id" name="store_id">
                                    <option value="">Semua Outlet</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <div class="col-md-2">
                                <label for="category_id" class="form-label fw-bold">Kategori Bahan</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100 d-block">
                                    <i class="fas fa-search me-1"></i>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                            <i class="fas fa-cubes text-primary fa-2x"></i>
                                        </div>
                                    </div>
                                    <h2 class="fw-bold text-primary mb-2">{{ number_format($totalIngredients) }}</h2>
                                    <h6 class="text-muted mb-0">Jenis Bahan Baku</h6>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                            <i class="fas fa-weight-hanging text-success fa-2x"></i>
                                        </div>
                                    </div>
                                    <h2 class="fw-bold text-success mb-2">{{ number_format($totalQuantityUsed, 0) }}</h2>
                                    <h6 class="text-muted mb-0">Total Bahan Terpakai</h6>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                            <i class="fas fa-receipt text-warning fa-2x"></i>
                                        </div>
                                    </div>
                                    <h2 class="fw-bold text-warning mb-2">{{ number_format($totalTransactions) }}</h2>
                                    <h6 class="text-muted mb-0">Total Transaksi</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Section -->
                    @if(count($chartData['labels']) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Top 10 Bahan Baku Paling Banyak Digunakan</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="ingredientChart" style="height: 400px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table id="ingredientTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Bahan Baku</th>
                                    <th>Kategori</th>
                                    <th>Total Digunakan</th>
                                    <th>Satuan</th>
                                    <th>Total Transaksi</th>
                                    <th>Detail Produk</th>
                                    @if($canSelectStore && !$storeId)
                                    <th>Outlet</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedUsages as $index => $usage)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $usage->ingredient_code }}</td>
                                    <td>{{ $usage->ingredient_name }}</td>
                                    <td>{{ $usage->category_name }}</td>
                                    <td class="text-end">{{ number_format($usage->total_quantity_used, 2) }}</td>
                                    <td>{{ $usage->unit_name }}</td>
                                    <td class="text-center">{{ $usage->total_transactions }}</td>
                                    <td class="small">
                                        @foreach($usage->products_detail as $product)
                                            <div class="mb-1">
                                                <strong>{{ $product['product_name'] }}:</strong><br>
                                                {{ $product['sold_quantity'] }} × {{ $product['recipe_quantity'] }} = {{ $product['ingredient_used'] }}
                                            </div>
                                        @endforeach
                                    </td>
                                    @if($canSelectStore && !$storeId)
                                    <td>{{ $usage->store_name }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($processedUsages->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data penggunaan bahan baku</h5>
                        <p class="text-muted">Silakan coba ubah filter tanggal atau outlet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#ingredientTable').DataTable({
            responsive: true,
            language: {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            order: [[4, 'desc']], // Sort by total quantity used
            columnDefs: [
                {
                    targets: [0],
                    orderable: false
                }
            ]
        });
    });

    // Chart initialization
    @if(count($chartData['labels']) > 0)
    const ctx = document.getElementById('ingredientChart').getContext('2d');
    const ingredientChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Quantity Used',
                data: @json($chartData['data']),
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ],
                borderColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Penggunaan Bahan Baku (Top 10)'
                }
            }
        }
    });
    @endif

    // Export function
    function exportReport() {
        const params = new URLSearchParams(window.location.search);
        const exportUrl = '{{ route("reports.ingredient-usage.export") }}?' + params.toString();
        window.location.href = exportUrl;
    }

    // Auto-submit form when date changes
    document.getElementById('start_date').addEventListener('change', function() {
        if (this.value && document.getElementById('end_date').value) {
            this.form.submit();
        }
    });

    document.getElementById('end_date').addEventListener('change', function() {
        if (this.value && document.getElementById('start_date').value) {
            this.form.submit();
        }
    });
</script>
@endsection
