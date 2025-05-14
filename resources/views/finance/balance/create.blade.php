@extends('layouts.app')

@section('title', 'Input Saldo Awal')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-wallet me-2 text-primary"></i> Input Saldo Awal
            </h1>
            <p class="text-muted">Masukkan saldo awal untuk berbagai kategori</p>
        </div>
        <a href="{{ route('reports.finance') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Form Saldo Awal</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('finance.balance.store') }}" method="POST">
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
                        <label for="category_id" class="form-label">Kategori Saldo</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">-- Pilih Kategori Saldo --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ ucfirst($category->type) }})
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="store_id" class="form-label">
                            Toko/Cabang
                            @if($userStoreId)
                                <span class="badge bg-info">Terdeteksi Otomatis</span>
                            @else
                                <span class="badge bg-warning">Pilih Manual</span>
                            @endif
                        </label>
                        <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" {{ $userStoreId ? 'disabled' : '' }}>
                            <option value="">-- Saldo Global --</option>
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

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Jumlah Saldo</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control currency-input @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', 0) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Saldo Awal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Saldo Awal Terbaru</h6>
        </div>
        <div class="card-body">
            @if($latestBalances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th>Tipe</th>
                                <th>Tanggal Input</th>
                                <th class="text-end">Jumlah</th>
                                <th>Cabang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestBalances as $balance)
                                <tr>
                                    <td>{{ $balance->category->name }}</td>
                                    <td>{{ ucfirst($balance->category->type) }}</td>
                                    <td>{{ $balance->date->format('d/m/Y') }}</td>
                                    <td class="text-end">Rp {{ number_format($balance->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($balance->store)
                                            <span class="badge bg-info">{{ $balance->store->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">Global</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Belum ada data saldo awal. Silakan tambahkan data saldo awal baru.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-fill nilai saldo terakhir
        $('#category_id').on('change', function() {
            var categoryId = $(this).val();
            var storeId = $('#store_id').val() || '{{ $userStoreId }}';

            // Reset nilai amount dahulu
            $('#amount').val('0');

            // Ambil saldo terakhir untuk kategori ini
            @foreach($latestBalances as $balance)
                if (categoryId == "{{ $balance->category_id }}" &&
                   (storeId == "{{ $balance->store_id }}" || (!storeId && !{{ $balance->store_id ? 'true' : 'false' }}))) {
                    $('#amount').val("{{ number_format($balance->amount, 0, ',', '.') }}");
                }
            @endforeach

            formatCurrency($('#amount'));
        });

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
    });
</script>
@endsection
