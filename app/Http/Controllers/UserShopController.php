<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Article;

use Illuminate\Http\Request;

class UserShopController extends Controller
{
    public function show(User $user){
        $articles = $user->articles()
            ->where('status', 'approved')
            ->select('id', 'user_id', 'titre', 'prix_ht', 'lieu', 'photo', 'sous_categorie_id', 'status', 'boosted_until', 'created_at', 'neuf', 'livraison')
            ->withLikeCounts(auth()->id())
            ->with(['user:id,name,photo_profil,certifie,ville', 'sousCategorie:id,nom,categorie_id'])
            ->latest()
            ->paginate(24);
        return view('pages.boutique.show', compact('user', 'articles'));
    }
}
