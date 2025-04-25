@extends('layouts.app')

@section('title', 'Tambah Peran')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Peran
            </h1>
            <p class="text-muted">Buat peran baru dengan izin khusus</p>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Form Peran</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nama Peran</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label">Izin</label>
                    
                    @foreach($permissionGroups as $group => $permissions)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input group-checkbox" id="group_{{ $group }}">
                                    <label class="form-check-label fw-bold" for="group_{{ $group }}">
                                        {{ ucfirst($group) }}
                                    </label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input permission-checkbox" 
                                                    name="permissions[]" 
                                                    id="permission_{{ $permission->id }}" 
                                                    value="{{ $permission->id }}"
                                                    data-group="{{ $group }}"
                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Handle group checkbox click
        $('.group-checkbox').change(function() {
            var group = $(this).attr('id').replace('group_', '');
            var isChecked = $(this).prop('checked');
            
            $('input[data-group="' + group + '"]').prop('checked', isChecked);
        });
        
        // Update group checkbox when permissions change
        $('.permission-checkbox').change(function() {
            var group = $(this).data('group');
            var groupSelector = '#group_' + group;
            
            var totalGroupPermissions = $('input[data-group="' + group + '"]').length;
            var checkedGroupPermissions = $('input[data-group="' + group + '"]:checked').length;
            
            $(groupSelector).prop('checked', totalGroupPermissions === checkedGroupPermissions);
        });
        
        // Initialize group checkboxes
        $('.group-checkbox').each(function() {
            var group = $(this).attr('id').replace('group_', '');
            var totalGroupPermissions = $('input[data-group="' + group + '"]').length;
            var checkedGroupPermissions = $('input[data-group="' + group + '"]:checked').length;
            
            $(this).prop('checked', totalGroupPermissions === checkedGroupPermissions && totalGroupPermissions > 0);
        });
    });
</script>
@endsection