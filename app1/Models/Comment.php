<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use App\Models\User; 

class Comment extends Model
{

    protected $fillable = [
        'content',
        'article_id', 
        'user_id']; // Les champs remplissables

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
