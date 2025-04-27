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
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px;">
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
