@extends('layouts.app')

@section('title', 'Input Pengeluaran')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-money-bill-wave me-2 text-primary"></i> Input Pengeluaran
            </h1>
            <p class="text-muted">Tambahkan data pengeluaran keuangan</p>
        </div>
        <div>
            <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-tags me-1"></i> Kelola Kategori
            </a>
            <a href="{{ route('reports.finance') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Form Pengeluaran</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('finance.expense.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="category_id" class="form-label">Kategori</label>
                        <div class="input-group">
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus"></i>
                            </button>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="amount" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control currency-input @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="store_id" class="form-label">
                            Toko/Cabang
                            @if($userStoreId)
                                <span class="badge bg-info">Terdeteksi Otomatis</span>
                            @else
                                <span class="badge bg-warning">Pilih Manual</span>
                            @endif
                        </label>
                        <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" {{ $userStoreId ? 'disabled' : '' }}>
                            <option value="">-- Pilih Toko --</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id', $userStoreId) == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($userStoreId)
                            <input type="hidden" name="store_id" value="{{ $userStoreId }}">
                            <small class="text-muted">Anda login sebagai pengguna cabang {{ $stores->where('id', $userStoreId)->first()->name ?? '' }}</small>
                        @endif
                        @error('store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Keterangan</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCategoryForm" action="{{ route('expense-categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="category_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="category_is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="category_is_active">
                            Aktif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Format input sebagai mata uang Indonesia saat halaman dimuat
        $('.currency-input').each(function() {
            formatCurrency($(this));
        });

        // Format input sebagai mata uang Indonesia saat pengguna mengetik
        $('.currency-input').on('input', function() {
            formatCurrency($(this));
        });

        // Persiapkan data sebelum submit form
        $('form').on('submit', function() {
            $('.currency-input').each(function() {
                // Hapus separator ribuan dan ganti koma dengan titik untuk nilai numerik
                var value = $(this).val().replace(/\./g, '').replace(/,/g, '.');
                $(this).val(value);
            });
        });

        // Fungsi untuk memformat input sebagai mata uang Indonesia
        function formatCurrency(input) {
            // Hapus karakter selain angka dan koma
            var value = input.val().replace(/[^\d,]/g, '');

            // Hapus semua koma kecuali yang terakhir
            var parts = value.split(',');
            value = parts[0];

            // Format dengan separator ribuan
            if (value.length > 0) {
                value = parseInt(value, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Tambahkan koma dan desimal jika ada
            if (parts.length > 1) {
                value += ',' + parts[1];
            }

            input.val(value);
        }

        // Ajax untuk kategori baru
        $('#addCategoryForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Tutup modal
                        $('#addCategoryModal').modal('hide');

                        // Tambahkan kategori baru ke dropdown
                        var newOption = new Option(response.category.name, response.category.id, true, true);
                        $('#category_id').append(newOption).trigger('change');

                        // Reset form
                        $('#addCategoryForm')[0].reset();

                        // Tampilkan notifikasi
                        alert('Kategori berhasil ditambahkan!');
                    } else {
                        alert('Terjadi kesalahan: ' + response.message);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';

                    for (var key in errors) {
                        errorMessage += errors[key][0] + '\n';
                    }

                    alert('Terjadi kesalahan: ' + errorMessage);
                }
            });
        });
    });
</script>
@endsection
