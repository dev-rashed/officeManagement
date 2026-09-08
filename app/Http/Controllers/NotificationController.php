<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Full list, with unread first. */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(25);

        $unreadCount = $request->user()->unreadNotifications()->count();

        return view('pages.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Feeds the bell. Kept small on purpose -- it runs on every page load.
     */
    public function recent(Request $request)
    {
        $user = $request->user();

        $recent = $user->notifications()->latest()->limit(8)->get()->map(fn ($n) => [
            'id' => $n->id,
            'title' => $n->data['title'] ?? 'Notification',
            'message' => $n->data['message'] ?? '',
            'url' => $n->data['url'] ?? null,
            'type' => $n->data['entry_type'] ?? null,
            'amount' => $n->data['amount'] ?? null,
            'read' => $n->read_at !== null,
            'ago' => $n->created_at->diffForHumans(short: true),
        ]);

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items' => $recent,
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'All notifications marked as read.']);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->json(['message' => 'Notification removed.']);
    }
}
