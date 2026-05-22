<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'url',
        'related_id',
        'related_type',
        'is_read',
        'read_at',
    ];

    /**
     * Relation avec l'admin
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Marquer la notification comme lue
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Marquer toutes les notifications comme lues pour un admin
     */
    public static function markAllAsRead($adminId = null)
    {
        $query = static::where('is_read', false);
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        $query->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues pour un admin
     */
    public static function unreadCount($adminId = null)
    {
        $query = static::where('is_read', false);
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        return $query->count();
    }

    /**
     * Obtenir les notifications récentes non lues pour un admin
     */
    public static function recentUnread($adminId = null, $limit = 10)
    {
        $query = static::where('is_read', false);
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir toutes les notifications récentes pour un admin
     */
    public static function recent($adminId = null, $limit = 20)
    {
        $query = static::query();
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir tous les administrateurs (même logique que User::isAdmin()).
     * Fallback sur filtre PHP si la requête SQL ne retourne personne.
     */
    private static function getAdmins()
    {
        $admins = User::where(function ($query) {
            $query->where('role', 'admin')
                ->orWhereRaw('LOWER(email) LIKE ?', ['%admin%']);
        })->get();

        if ($admins->isEmpty()) {
            $admins = User::all()->filter(fn (User $u) => $u->isAdmin())->values();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Log::warning('AdminNotification: getAdmins SQL returned 0, fallback isAdmin() found ' . $admins->count());
            }
        }

        return $admins;
    }

    /**
     * Créer une notification pour un nouvel utilisateur (pour tous les admins)
     */
    public static function createForNewUser(User $user)
    {
        $admins = static::getAdmins();
        $notifications = [];

        foreach ($admins as $admin) {
            $notifications[] = static::create([
                'admin_id' => $admin->id,
                'type' => 'user_registered',
                'title' => 'Nouvel utilisateur inscrit',
                'message' => "{$user->name} ({$user->email}) vient de créer un compte.",
                'icon' => 'fa-user-plus',
                'color' => 'success',
                'url' => route('admin.users.show', $user),
                'related_id' => $user->id,
                'related_type' => User::class,
            ]);
        }

        return $notifications;
    }

    /**
     * Créer une notification pour un article en attente (pour tous les admins)
     */
    public static function createForPendingArticle(Article $article)
    {
        $admins = static::getAdmins();
        $notifications = [];

        foreach ($admins as $admin) {
            $notifications[] = static::create([
                'admin_id' => $admin->id,
                'type' => 'article_pending',
                'title' => 'Nouvel article en attente',
                'message' => "L'article \"{$article->titre}\" de {$article->user->name} nécessite une approbation.",
                'icon' => 'fa-clock',
                'color' => 'warning',
                'url' => route('admin.articles.show', $article),
                'related_id' => $article->id,
                'related_type' => Article::class,
            ]);
        }

        return $notifications;
    }

    /**
     * Créer une notification pour un signalement (pour tous les admins)
     */
    public static function createForProblemReport(ProblemReport $report)
    {
        $admins = static::getAdmins();
        $notifications = [];
        $userName = $report->user ? $report->user->name : 'Utilisateur anonyme';

        if ($admins->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning('AdminNotification: Aucun admin trouvé (role=admin ou email contient "admin"). Aucune notification créée pour le signalement #' . $report->id);
            return $notifications;
        }

        \Illuminate\Support\Facades\Log::info('AdminNotification: Création de notifications signalement #' . $report->id . ' pour ' . $admins->count() . ' admin(s).');

        foreach ($admins as $admin) {
            $notifications[] = static::create([
                'admin_id' => $admin->id,
                'type' => 'problem_report',
                'title' => 'Nouveau signalement',
                'message' => "Un signalement a été créé par {$userName}.",
                'icon' => 'fa-flag',
                'color' => 'danger',
                'url' => route('admin.reports.index') . '#report-' . $report->id,
                'related_id' => $report->id,
                'related_type' => ProblemReport::class,
            ]);
        }

        return $notifications;
    }
}
