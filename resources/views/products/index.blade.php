@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-boxes me-2 text-primary"></i> Produk
            </h1>
            <p class="text-muted">Kelola semua produk inventaris Anda</p>
        </div>
        <div class="d-flex gap-2">
            @can('create products')
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </a>
            @endcan
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Import
            </button>
            <a href="{{ route('products.export') }}" class="btn btn-info text-white">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Produk</h6>
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover datatable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td><span class="fw-medium">{{ $product->code }}</span></td>
                            <td>{{ $product->name }}</td>
                            <td>
                                <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $wareStock = $product->stockWarehouses->first();
                                    $stockQty = $wareStock ? floatval($wareStock->quantity) : 0;
                                    // Format angka untuk menampilkan nilai bulat jika angka desimal adalah 0
                                    $formattedStock = (floor($stockQty) == $stockQty) ? number_format($stockQty, 0, ',', '.') : number_format($stockQty, 2, ',', '.');
                                @endphp
                                <div class="d-flex align-items-center">
                                    {{ $formattedStock }} {{ $product->baseUnit->name }}
                                    @if (!$wareStock || ($wareStock && $stockQty < floatval($product->min_stock)))
                                        <span class="badge bg-danger-light text-danger ms-2">Stok Rendah</span>
                                    @else
                                        <span class="badge bg-success-light text-success ms-2">Tersedia</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }} rounded-pill px-2">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit products')
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('delete products')
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?')) { document.getElementById('delete-form-{{ $product->id }}').submit(); }">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import_file" class="form-label">File Excel</label>
                        <input type="file" class="form-control" id="import_file" name="import_file" required accept=".xlsx, .xls, .csv">
                        <div class="form-text">Unggah file Excel (.xlsx, .xls) atau CSV.</div>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('products.import.template') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download me-1"></i> Unduh Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-circle text-danger fa-5x mb-3"></i>
                    <h5>Apakah Anda yakin ingin menghapus produk ini?</h5>
                    <p class="text-muted">Produk: <span id="product-name" class="fw-bold"></span></p>
                    <p class="small text-danger">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <i class="fas fa-trash me-1"></i> Hapus Produk
                </button>
                <!-- Form yang akan di-submit oleh JavaScript -->
                <form id="active-delete-form" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
    var dataTable;
    
    // Cek apakah tabel sudah ada dan inisialisasi
    try {
        // Inisialisasi DataTable baru dengan cara yang lebih aman
        dataTable = $('.datatable').DataTable({
            // Definisi bahasa Indonesia langsung dalam kode
            language: {
                "emptyTable": "Tidak ada data yang tersedia pada tabel ini",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "loadingRecords": "Sedang memuat...",
                "processing": "Sedang memproses...",
                "search": "Cari:",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 10,
            // Hindari menghancurkan dan membuat ulang tabel
            destroy: true,
            retrieve: true
        });
    } catch (error) {
        console.error("Error initializing DataTable:", error);
        // Jika ada error, initialize dengan opsi minimal
        dataTable = $('.datatable').DataTable({
            responsive: true,
            pageLength: 10
        });
    }
    
    // Custom search dengan penanganan error
    $('#customSearch').keyup(function() {
        try {
            dataTable.search($(this).val()).draw();
        } catch (error) {
            console.error("Error during search:", error);
        }
    });
    
    // Delete confirmation
    $('.delete-btn').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        // Konfirmasi langsung dengan browser
        if (confirm('Apakah Anda yakin ingin menghapus produk "' + name + '"?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    });

    // Initialize tooltips
    try {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });
    } catch(error) {
        console.error("Error initializing tooltips:", error);
    }
    
    // Format currency input (Rupiah)
    $('.money-format').each(function() {
        var value = $(this).val();
        if (value) {
            // Hapus semua karakter non-numerik
            value = value.replace(/[^\d]/g, '');
            
            // Format dengan pemisah ribuan
            if (value !== '') {
                value = parseInt(value).toLocaleString('id-ID');
                $(this).val(value);
            }
        }
    });
    
    // Handle input untuk field dengan format uang
    $(document).on('input', '.money-format', function() {
        var value = $(this).val();
        
        // Hapus semua karakter non-numerik
        value = value.replace(/[^\d]/g, '');
        
        // Format dengan pemisah ribuan
        if (value !== '') {
            value = parseInt(value).toLocaleString('id-ID');
        }
        
        // Update tampilan
        $(this).val(value);
        
        // Simpan nilai asli ke hidden input jika ada
        var hiddenInput = $(this).attr('id') + '_real';
        if ($('#' + hiddenInput).length) {
            $('#' + hiddenInput).val(value.replace(/\./g, ''));
        }
    });
    
    // Auto-calculate selling price with markup (for example: 20% markup)
    $('#purchase_price').on('input', function() {
        if (!$('#selling_price').val() || $('#selling_price').val() == '0') {
            var purchasePrice = $(this).val().replace(/\./g, '');
            purchasePrice = parseFloat(purchasePrice) || 0;
            var markup = 0.2; // 20% markup
            var sellingPrice = purchasePrice * (1 + markup);
            
            // Format sellingPrice as currency
            sellingPrice = Math.round(sellingPrice).toLocaleString('id-ID');
            $('#selling_price').val(sellingPrice);
            
            // Update hidden field
            $('#selling_price_real').val(sellingPrice.replace(/\./g, ''));
        }
    });
    
    // Form submission - ensure hidden fields are updated
    $('form').on('submit', function() {
        $('.money-format').each(function() {
            var value = $(this).val().replace(/\./g, '');
            var hiddenInput = $(this).attr('id') + '_real';
            if ($('#' + hiddenInput).length) {
                $('#' + hiddenInput).val(value);
            }
        });
    });
    
    // Add unit row if that function exists in your page
    if ($('#add-unit').length) {
        $('#add-unit').click(function() {
            var index = $('.unit-row').length;
            var unitOptions = '';
            
            // Dapatkan opsi unit dari select unit pertama jika ada
            if ($('.unit-row:first select').length) {
                unitOptions = $('.unit-row:first select').html();
            }
            
            var newRow = `
                <div class="row mb-3 unit-row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label d-block d-md-none">Satuan</label>
                        <select class="form-select select2-new" name="additional_units[${index}][unit_id]">
                            ${unitOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block d-md-none">Nilai Konversi</label>
                        <div class="input-group">
                            <input type="number" step="0.0001" class="form-control" name="additional_units[${index}][conversion_value]" 
                                placeholder="Konversi">
                            <span class="input-group-text">per satuan dasar</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">Harga Beli</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money-format" name="additional_units[${index}][purchase_price]" 
                                placeholder="Harga Beli" value="0">
                            <input type="hidden" name="additional_units[${index}][purchase_price_real]" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">Harga Jual</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money-format" name="additional_units[${index}][selling_price]" 
                                placeholder="Harga Jual" value="0">
                            <input type="hidden" name="additional_units[${index}][selling_price_real]" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger remove-unit">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            
            $('#additional-units').append(newRow);
            
            // Inisialisasi Select2 untuk elemen baru
            try {
                $('.select2-new').select2({
                    theme: "bootstrap-5",
                    width: '100%'
                });
                // Hapus class select2-new untuk menghindari inisialisasi ganda
                $('.select2-new').removeClass('select2-new');
            } catch (error) {
                console.error("Error initializing Select2:", error);
            }
            
            // Inisialisasi format uang untuk input baru
            $('.money-format').each(function() {
                var value = $(this).val();
                if (value && value !== '0') {
                    value = parseInt(value).toLocaleString('id-ID');
                    $(this).val(value);
                }
            });
        });
        
        // Remove unit row
        $(document).on('click', '.remove-unit', function() {
            $(this).closest('.unit-row').remove();
        });
    }
});
</script>
@endsection