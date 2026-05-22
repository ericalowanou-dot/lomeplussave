<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Obtenir les notifications non lues pour l'admin connecté
     */
    public function unread()
    {
        $adminId = auth()->id();
        $notifications = AdminNotification::recentUnread($adminId, 10);
        $unreadCount = AdminNotification::unreadCount($adminId);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Obtenir toutes les notifications récentes pour l'admin connecté
     */
    public function index()
    {
        $adminId = auth()->id();
        $notifications = AdminNotification::recent($adminId, 20);
        $unreadCount = AdminNotification::unreadCount($adminId);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marquer une notification comme lue (vérifie que c'est bien la notification de l'admin connecté)
     */
    public function markAsRead(AdminNotification $notification)
    {
        // Vérifier que la notification appartient à l'admin connecté
        if ($notification->admin_id !== auth()->id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => AdminNotification::unreadCount(auth()->id()),
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues pour l'admin connecté
     */
    public function markAllAsRead()
    {
        AdminNotification::where('admin_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues pour l'admin connecté
     */
    public function count()
    {
        return response()->json([
            'unread_count' => AdminNotification::unreadCount(auth()->id()),
        ]);
    }
}
