<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $supplier->name ?? '') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $supplier->email ?? '') }}">
            <small class="form-text text-muted">Opsional: untuk komunikasi elektronik</small>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="phone" class="form-label">Telepon</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}">
            </div>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="payment_term" class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
            <select class="form-select @error('payment_term') is-invalid @enderror" id="payment_term" name="payment_term" required>
                <option value="tunai" {{ old('payment_term', $supplier->payment_term ?? '') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                <option value="tempo" {{ old('payment_term', $supplier->payment_term ?? '') === 'tempo' ? 'selected' : '' }}>Tempo (Kredit)</option>
            </select>
            @error('payment_term')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row credit-limit-row {{ old('payment_term', $supplier->payment_term ?? '') === 'tempo' ? '' : 'd-none' }}">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="credit_limit" class="form-label">Limit Kredit</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror" id="credit_limit" name="credit_limit" value="{{ old('credit_limit', $supplier->credit_limit ?? 0) }}">
            </div>
            <small class="form-text text-muted">Batas maksimum kredit yang diberikan ke pemasok ini</small>
            @error('credit_limit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mb-3">
    <label for="address" class="form-label">Alamat</label>
    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap pemasok">{{ old('address', $supplier->address ?? '') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
               {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Aktif
        </label>
    </div>
    <small class="form-text text-muted">Pemasok yang tidak aktif tidak akan muncul di daftar saat transaksi</small>
    @error('is_active')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> {{ $submitButtonText }}
            </button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Batal
            </a>
        </div>
    </div>
</div>

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        $('#payment_term').change(function() {
            if ($(this).val() === 'tempo') {
                $('.credit-limit-row').removeClass('d-none');
            } else {
                $('.credit-limit-row').addClass('d-none');
                $('#credit_limit').val(0);
            }
        });
        
        // Initialize Select2 if available
        if ($.fn.select2) {
            $('#payment_term').select2({
                theme: "bootstrap-5",
                width: '100%'
            });
        }
        
        // Format nomor telepon
        $('#phone').on('input', function() {
            // Hapus semua karakter non-angka
            var phoneNumber = $(this).val().replace(/\D/g, '');
            
            // Format ulang nomor telepon
            if (phoneNumber.length > 0) {
                if (phoneNumber.startsWith('0')) {
                    // Jika dimulai dengan 0, asumsikan format Indonesia
                    if (phoneNumber.length > 3) {
                        phoneNumber = phoneNumber.substring(0, 4) + '-' + phoneNumber.substring(4);
                    }
                    if (phoneNumber.length > 8) {
                        phoneNumber = phoneNumber.substring(0, 9) + '-' + phoneNumber.substring(9);
                    }
                } else if (phoneNumber.startsWith('62')) {
                    // Jika dimulai dengan 62, asumsikan kode negara Indonesia
                    if (phoneNumber.length > 2) {
                        phoneNumber = '+' + phoneNumber.substring(0, 2) + '-' + phoneNumber.substring(2);
                    }
                    if (phoneNumber.length > 6) {
                        phoneNumber = phoneNumber.substring(0, 7) + '-' + phoneNumber.substring(7);
                    }
                }
            }
            
            $(this).val(phoneNumber);
        });
    });
</script>
@endsection