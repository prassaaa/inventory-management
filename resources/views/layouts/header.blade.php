<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <button class="btn text-white" id="sidebarCollapse" style="background-color: #2563eb;">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                <!-- Notifikasi -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        @php
                            $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                                ->where('is_read', false)
                                ->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="notificationDropdown" style="width: 300px;">
                        <li class="dropdown-header">Notifikasi</li>
                        @php
                            $notifications = \App\Models\Notification::where('user_id', auth()->id())
                                ->where('is_read', false)
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        @forelse($notifications as $notification)
                            <li>
                                <a class="dropdown-item" href="{{ route('notifications.read', $notification->id) }}">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <strong class="d-block">{{ $notification->title }}</strong>
                                            <small class="text-truncate d-block">{{ \Illuminate\Support\Str::limit($notification->message, 50) }}</small>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            @if(!$loop->last)
                                <li><hr class="dropdown-divider"></li>
                            @endif
                        @empty
                            <li><a class="dropdown-item" href="#">Tidak ada notifikasi baru</a></li>
                        @endforelse
                        @if($unreadCount > 0)
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="{{ route('notifications.index') }}">Lihat semua notifikasi</a></li>
                        @endif
                    </ul>
                </li>

                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-dark" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2 text-primary"></i> Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2 text-danger"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
