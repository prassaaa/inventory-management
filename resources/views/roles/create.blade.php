@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Role</h1>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Role Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Permissions</label>
                    
                    @foreach($permissionGroups as $group => $permissions)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input group-checkbox" id="group_{{ $group }}">
                                    <label class="custom-control-label font-weight-bold" for="group_{{ $group }}">
                                        {{ ucfirst($group) }} Permissions
                                    </label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input permission-checkbox" 
                                                    name="permissions[]" 
                                                    id="permission_{{ $permission->id }}" 
                                                    value="{{ $permission->id }}"
                                                    data-group="{{ $group }}"
                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Role
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