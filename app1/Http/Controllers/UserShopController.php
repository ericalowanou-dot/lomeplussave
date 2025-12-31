<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Article;

use Illuminate\Http\Request;

class UserShopController extends Controller
{
    public function show(User $user){
        $articles = $user->articles()->latest()->paginate(12);
        return view('pages.boutique.show', compact('user', 'articles'));
    }
}
