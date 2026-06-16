<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type'] ?? 'info',
                'title'      => $n->data['title'] ?? '',
                'message'    => $n->data['message'] ?? '',
                'url'        => $n->data['url'] ?? null,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count'         => $request->user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
