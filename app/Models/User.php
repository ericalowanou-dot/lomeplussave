<?php

namespace App\Models;
use App\Models\Article;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Services\MediaStorage;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        // Supprimer un compte supprime ses annonces une par une, pour effacer aussi leurs photos
        // (la suppression en cascade de la base ne déclenche pas cet effacement).
        static::deleting(function (User $user) {
            $user->articles()->get()->each->delete();
        });
    }

    public function getProfilPhotoUrl(){
        if ($this->photo_profil) {
            // Les anciens comptes ont parfois un chemin d'un autre dossier : on garde le nom du fichier
            $path = str_starts_with($this->photo_profil, 'users/profil/') || str_starts_with($this->photo_profil, 'http')
                ? $this->photo_profil
                : 'users/profil/' . basename($this->photo_profil);

            return MediaStorage::url($path, self::DEFAULT_AVATAR);
        }
        return asset(self::DEFAULT_AVATAR);
    }

    public const DEFAULT_AVATAR = 'assets/icons/user_default.svg';

    /**
     * Remplace la photo de profil ; l'ancienne n'est effacée qu'une fois la nouvelle enregistrée.
     */
    public function replaceProfilePhoto(UploadedFile $file): void
    {
        $old = $this->photo_profil;

        $this->photo_profil = MediaStorage::storeImage($file, 'users/profil', 'profile');
        $this->save();

        if ($old && $old !== $this->photo_profil) {
            MediaStorage::delete($old);
        }
    }

    /**
     * Avatar par défaut (hors public/images, souvent exclus en déploiement).
     */
    public static function defaultProfilPhotoUrl(): string
    {
        return asset(self::DEFAULT_AVATAR);
    }

    public function estCertifie()
    {
        if ((int) $this->certifie !== 1) {
            return false;
        }

        $now = now();

        if ($this->certifie_from && $this->certifie_from->isFuture()) {
            return false;
        }

        if ($this->certifie_until && $this->certifie_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Retourne l'URL WhatsApp pour contacter l'utilisateur (Togo +228), ou null si pas de numéro.
     */
    public function getWhatsAppUrl(): ?string
    {
        $phone = $this->whatsapp ?? $this->telephone ?? null;
        if (!$phone) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '228' . substr($digits, 1);
        } elseif ($digits && !str_starts_with($digits, '228')) {
            $digits = '228' . $digits;
        }
        return strlen($digits) >= 8 ? 'https://wa.me/' . $digits : null;
    }





    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'telephone',
        'whatsapp',
        'photo_profil',
        'certifie',
        'coins',
        'certifie_from',
        'certifie_until',
        'is_blocked',
        'block_reason',
        'blocked_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_at' => 'datetime',
            'certifie_from' => 'datetime',
            'certifie_until' => 'datetime',
        ];
    }

    public function hasCoins(int $amount): bool
    {
        return ($this->coins ?? 0) >= $amount;
    }

    /**
     * Débite des coins en une seule requête, seulement si le solde en base suffit.
     * Deux clics simultanés ne peuvent donc pas dépenser deux fois les mêmes coins.
     *
     * @return bool Faux si le solde est insuffisant (rien n'est débité).
     */
    public function spendCoins(int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $debited = static::whereKey($this->getKey())
            ->where('coins', '>=', $amount)
            ->decrement('coins', $amount);

        $this->coins = (int) static::whereKey($this->getKey())->value('coins');
        $this->syncOriginalAttribute('coins');

        return $debited === 1;
    }

    public function addCoins(int $amount): void
    {
        if ($amount > 0) {
            static::whereKey($this->getKey())->increment('coins', $amount);
        }

        $this->coins = (int) static::whereKey($this->getKey())->value('coins');
        $this->syncOriginalAttribute('coins');
    }

    public function likedArticles()
{
    return $this->belongsToMany(Article::class, 'article_user_like')->withTimestamps();
    }


    public function articles()
{
    return $this->hasMany(Article::class, 'user_id');
}

public function favoris()
{
    return $this->belongsToMany(Article::class, 'article_user_like')
                ->withTimestamps();
}

public function reportsReceived()
{
    return $this->hasMany(UserReport::class, 'reported_user_id');
}

public function reportsMade()
{
    return $this->hasMany(UserReport::class, 'reporter_id');
}

/**
 * Paramètres de route SEO boutique :
 * /boutique/{slug-nom}-{id}
 */
public function shopRouteParameters(): array
{
    $slug = Str::slug(Str::limit($this->name ?? '', 60, '')) ?: 'boutique';

    return [
        'slugId' => $slug . '-' . $this->id,
    ];
}

/**
 * URL publique SEO de la boutique.
 */
public function shopUrl(): string
{
    return route('boutique.show', $this->shopRouteParameters());
}

public function getShopUrlAttribute(): string
{
    return $this->shopUrl();
}

// Méthodes pour l'administration
public function isAdmin()
{
    return $this->role === 'admin';
}

public function isBlocked()
{
    return $this->is_blocked;
}

public function block($reason = null)
{
    $this->update([
        'is_blocked' => true,
        'block_reason' => $reason,
        'blocked_at' => now(),
    ]);
}

public function unblock()
{
    $this->update([
        'is_blocked' => false,
        'block_reason' => null,
        'blocked_at' => null,
    ]);
}

/**
 * Send the password reset notification.
 *
 * @param  string  $token
 * @return void
 */
public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token));
}
}