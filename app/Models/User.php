<?php

namespace App\Models;
use App\Models\Article;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public function getProfilPhotoUrl(){
        if ($this->photo_profil) {
            // Si le chemin commence déjà par users/profil, on l'utilise tel quel
            if (str_starts_with($this->photo_profil, 'users/profil/')) {
                return asset($this->photo_profil);
            }
            // Sinon, on adapte les anciens chemins
            return asset('users/profil/' . basename($this->photo_profil));
        }
        return asset('images/user_default.png');
    }

    public function estCertifie()
    {
        return $this->certifie == 1;
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
            'certifie_until' => 'datetime',
        ];
    }

    public function hasCoins(int $amount): bool
    {
        return ($this->coins ?? 0) >= $amount;
    }

    public function spendCoins(int $amount): void
    {
        $this->coins = max(0, ($this->coins ?? 0) - $amount);
        $this->save();
    }

    public function addCoins(int $amount): void
    {
        $this->coins = ($this->coins ?? 0) + max(0, $amount);
        $this->save();
    }

    public function likedArticles()
{
    return $this->belongsToMany(Article::class, 'article_user_like');
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

// Méthodes pour l'administration
public function isAdmin()
{
    // Pour l'instant, on considère qu'un utilisateur est admin si son email contient 'admin'
    // Vous pouvez modifier cette logique selon vos besoins
    return str_contains(strtolower($this->email), 'admin') || $this->role === 'admin';
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