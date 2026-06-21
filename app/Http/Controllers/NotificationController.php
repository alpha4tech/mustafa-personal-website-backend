<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = AdminNotification::latest()
            ->take(20)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'read_at'    => $n->read_at,
                'time'       => $n->created_at->diffForHumans(),
                'reference_id'   => $n->reference_id,
                'reference_type' => $n->reference_type,
            ]);

        return response()->json([
            'data'         => $notifications,
            'unread_count' => AdminNotification::unread()->count(),
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        AdminNotification::whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markOneRead(AdminNotification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
