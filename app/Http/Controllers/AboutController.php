<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Afficher la page "À propos de nous"
     */
    public function index(): View
    {
        return view('pages.about');
    }
}

