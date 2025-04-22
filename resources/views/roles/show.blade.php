@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Role Details</h1>
        <div>
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Role
            </a>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Roles
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Role: {{ $role->name }}</h6>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5 class="font-weight-bold">Permissions</h5>
                
                @foreach($permissionGroups as $group => $permissions)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="font-weight-bold mb-0">{{ ucfirst($group) }} Permissions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($permissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                id="permission_{{ $permission->id }}" 
                                                {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                                disabled>
                                            <label class="custom-control-label" for="permission_{{ $permission->id }}">
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