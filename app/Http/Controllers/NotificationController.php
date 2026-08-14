<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = DatabaseNotification::where('notifiable_id', Auth::id())
            ->where('notifiable_type', get_class(Auth::user()))
            ->latest()
            ->paginate(6);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->notifiable_id !== Auth::id()) abort(403);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        DatabaseNotification::where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = DatabaseNotification::where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->count();
        return response()->json(['count' => $count]);
    }

    public function getLatest()
    {
        $notifications = DatabaseNotification::where('notifiable_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get();
        return response()->json($notifications);
    }
}
