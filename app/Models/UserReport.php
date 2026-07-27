<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    use HasFactory;

    public const REASONS = [
        'fraude' => 'Fraude / arnaque',
        'spam' => 'Spam ou publicité abusive',
        'contenu_inapproprie' => 'Contenu inapproprié',
        'harcelement' => 'Harcèlement',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason',
        'message',
        'status',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
