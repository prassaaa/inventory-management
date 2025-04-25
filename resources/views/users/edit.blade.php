@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-user-edit me-2 text-primary"></i> Edit Pengguna
            </h1>
            <p class="text-muted">Perbarui informasi akun pengguna</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Form Pengguna</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Kata Sandi (kosongkan untuk tetap menggunakan yang sekarang)</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>
                
                <div class="form-group mb-3">
                    <label for="role" class="form-label">Peran</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                        <option value="">Pilih Peran</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $userRole->name ?? '') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label for="store_id" class="form-label">Lokasi</label>
                    <select class="form-select" id="store_id" name="store_id">
                        <option value="">Kantor Pusat (Tidak ada Lokasi)</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id', $user->store_id) == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Tetapkan lokasi untuk peran tingkat toko (misalnya admin_store, kasir)</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Perbarui
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Show/hide store field based on role
        $('#role').change(function() {
            var role = $(this).val();
            if (role === 'admin_store' || role === 'kasir') {
                $('#store_id').prop('required', true);
                $('#store_id').closest('.form-group').show();
            } else {
                $('#store_id').prop('required', false);
                if (role === 'owner' || role === 'admin_back_office' || role === 'admin_gudang') {
                    $('#store_id').val('');
                    $('#store_id').closest('.form-group').hide();
                } else {
                    $('#store_id').closest('.form-group').show();
                }
            }
        });
        
        // Trigger change on page load
        $('#role').trigger('change');
    });
</script>
@endsection