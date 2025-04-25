@extends('layouts.app')

@section('title', 'Detail Peran')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-user-shield me-2 text-primary"></i> Detail Peran
            </h1>
            <p class="text-muted">Informasi lengkap tentang peran dan izin</p>
        </div>
        <div>
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Peran
            </a>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Peran: {{ $role->name }}</h6>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5 class="fw-bold">Daftar Izin</h5>
                
                @foreach($permissionGroups as $group => $permissions)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0">{{ ucfirst($group) }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                id="permission_{{ $permission->id }}" 
                                                {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                                disabled>
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
        </div>
    </div>
</div>
@endsection