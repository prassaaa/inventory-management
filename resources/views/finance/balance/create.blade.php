@extends('layouts.app')

@section('title', 'Input Saldo Awal')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-wallet me-2 text-primary"></i> Input Saldo Awal
            </h1>
            <p class="text-muted">Masukkan saldo awal kas dan bank</p>
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
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="cash_balance" class="form-label">Saldo Kas</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control currency-input @error('cash_balance') is-invalid @enderror" id="cash_balance" name="cash_balance" value="{{ old('cash_balance', $latestBalance ? number_format($latestBalance->cash_balance, 0, ',', '.') : 0) }}" required>
                            @error('cash_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="bank1_balance" class="form-label">Saldo Bank 1</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control currency-input @error('bank1_balance') is-invalid @enderror" id="bank1_balance" name="bank1_balance" value="{{ old('bank1_balance', $latestBalance ? number_format($latestBalance->bank1_balance, 0, ',', '.') : 0) }}" required>
                            @error('bank1_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="bank2_balance" class="form-label">Saldo Bank 2</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control currency-input @error('bank2_balance') is-invalid @enderror" id="bank2_balance" name="bank2_balance" value="{{ old('bank2_balance', $latestBalance ? number_format($latestBalance->bank2_balance, 0, ',', '.') : 0) }}" required>
                            @error('bank2_balance')
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
    });
</script>
@endsection
