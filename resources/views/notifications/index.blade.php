@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Notifications') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Message') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $notification)
                                <tr class="{{ $notification->is_read ? '' : 'font-weight-bold' }}">
                                    <td>{{ $notification->title }}</td>
                                    <td>{{ $notification->message }}</td>
                                    <td>{{ $notification->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('notifications.read', $notification->id) }}" class="btn btn-sm btn-primary">
                                            {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('No notifications found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
