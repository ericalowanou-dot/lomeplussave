<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id','subject','body','is_group_message','parent_message_id'
    ];

    protected $casts = [
        'is_group_message' => 'boolean',
    ];

    public function parentMessage()
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'message_recipients', 'message_id', 'recipient_id')
            ->withTimestamps()
            ->withPivot('read_at');
    }
}


