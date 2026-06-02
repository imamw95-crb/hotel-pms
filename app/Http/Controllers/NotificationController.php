<?php

namespace App\Http\Controllers;

use App\Services\BookingNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly BookingNotificationService $notificationService
    ) {}

    /**
     * Get unread notification count.
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(),
        ]);
    }

    /**
     * Get recent notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        $notifications = $this->notificationService->getRecent($limit);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $this->notificationService->markAsRead($id);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->notificationService->markAllAsRead();

        return response()->json(['success' => true]);
    }
}
