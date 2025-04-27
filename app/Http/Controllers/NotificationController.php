<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function read($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->is_read = true;
        $notification->save();

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    public function getUnreadCount()
    {
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
}
