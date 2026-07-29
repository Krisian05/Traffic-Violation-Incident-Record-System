<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Fetch unread count and latest notifications feed for live topbar polling.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $isGlobalAdmin = in_array($user->role, ['admin', 'super_admin', 'province_admin']) && is_null($user->lgu_id);

        $rawNotifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->latest()
            ->take(50)
            ->get();

        if (!$isGlobalAdmin && $user->lgu_id) {
            $rawNotifications = $rawNotifications->filter(function ($n) use ($user) {
                $data = $n->data ?? [];
                if (!empty($data['violation_id'])) {
                    $lguId = \App\Models\Violation::where('id', $data['violation_id'])->value('lgu_id');
                    if ($lguId && (int)$lguId !== (int)$user->lgu_id) return false;
                }
                if (!empty($data['incident_id'])) {
                    $lguId = \App\Models\Incident::where('id', $data['incident_id'])->value('lgu_id');
                    if ($lguId && (int)$lguId !== (int)$user->lgu_id) return false;
                }
                if (!empty($data['violator_id'])) {
                    $lguId = \App\Models\Violator::where('id', $data['violator_id'])->value('lgu_id');
                    if ($lguId && (int)$lguId !== (int)$user->lgu_id) return false;
                }
                return true;
            });
        }

        $notifications = $rawNotifications->take(15)->values()->map(function ($n) {
            return [
                'id'          => $n->id,
                'type'        => $n->type,
                'data'        => $n->data,
                'read'        => !is_null($n->read_at),
                'created_at'  => $n->created_at->diffForHumans(),
                'timestamp'   => $n->created_at->toIso8601String(),
            ];
        });

        $unreadCount = $rawNotifications->whereNull('read_at')->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            Notification::where('notifiable_type', get_class($user))
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
