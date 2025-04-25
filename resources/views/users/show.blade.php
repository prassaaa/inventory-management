@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-user me-2 text-primary"></i> Detail Pengguna
            </h1>
            <p class="text-muted">Informasi lengkap tentang akun pengguna</p>
        </div>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Pengguna
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Informasi Pengguna</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th width="30%" class="border-0">Nama</th>
                                <td class="border-0">{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th class="border-0">Email</th>
                                <td class="border-0">{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th class="border-0">Peran</th>
                                <td class="border-0">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary-light text-primary rounded-pill px-2">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th class="border-0">Lokasi</th>
                                <td class="border-0">
                                    @if($user->store)
                                        {{ $user->store->name }}
                                    @else
                                        <span class="text-muted">Kantor Pusat (Tidak ada Lokasi)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="border-0">Dibuat Pada</th>
                                <td class="border-0">{{ $user->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Hak Akses</h6>
                </div>
                <div class="card-body">
                    @foreach($user->getAllPermissions()->groupBy(function($item, $key) {
                        return explode(' ', $item->name)[0];
                    }) as $group => $permissions)
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark mb-2">{{ ucfirst($group) }}</h6>
                            <div class="d-flex flex-wrap">
                                @foreach($permissions as $permission)
                                    <span class="badge bg-light text-secondary rounded-pill px-3 py-2 me-2 mb-2">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection