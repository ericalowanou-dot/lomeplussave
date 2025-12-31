
@extends('layouts.app2')

@section('title', 'Gestion des catégories')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
    }

    .container {
        max-width: 1200px;
        margin: auto;
        padding-top: 200px;
        width: 100%;
        justify-content: center; /* Centre le contenu horizontalement */
        align-items: center; /* Centre le contenu verticalement */
        text-align: center; /* Centre le texte */
    }

    h2 {
        font-weight: 600;
        text-align: center;
        color: #343a40;
        margin-bottom: 30px;
    }
    .form-ajouter {
        width: 100%;
        justify-content: center; /* Centre le formulaire horizontalement */
        align-items: center; /* Centre le formulaire verticalement */
        margin: 0 auto;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .ajouter-categorie {
        display: flex;
        gap: 20px;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap; /* pour gérer les petits écrans */
    
    }

    .form-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
    }

    .label-form {
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }

    .form-control {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }

    .bouton-ajouter {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .bouton-ajouter:hover {
        background-color: #0056b3;
    }

    button.btn {
        border-radius: 10px;
        font-weight: 500;
    }


    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-warning {
        background-color: #ffc107;
        color: #212529;
        border: 1px solid #ffc107;
        font-size: 14px;
        border-radius: 6px;
        padding: 4px 8px;
        text-decoration: none;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .btn-warning:hover {
        background-color: #e0a800;
        color: #fff;
    }



    img {
        border-radius: 8px;
        object-fit: cover;
    }

 







        /* Style général du tableau */
    .styled-table {
        justify-content: center; /* Centre le tableau horizontalement */
        align-items: center; /* Centre le tableau verticalement */
        text-align: center; /* Centre le texte dans les cellules */ 
        margin: 0 auto; /* Centre le tableau dans son conteneur */
        width: 100%; /* Prend toute la largeur du conteneur */
        border-collapse: collapse; /* Fusionne les bordures entre les cellules */
        font-family: 'Poppins', sans-serif; /* Police moderne et lisible */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Ombre autour du tableau */
        margin-top: 20px; /* Espace au-dessus du tableau */
        border-radius: 12px; /* Coins arrondis */
        overflow: hidden; /* Masque tout ce qui dépasse, surtout les coins arrondis */
    }

    /* Ligne d'en-tête */
    .styled-table thead tr {
        background-color: #343a40; /* Fond sombre pour l'en-tête */
        color: #ffffff; /* Texte blanc */
        text-align: center; /* Centrer les textes de l'en-tête */
        font-weight: bold; /* Texte en gras */
    }

    /* Cellules de l'en-tête et du corps */
    .styled-table th,
    .styled-table td {
        padding: 10px 14px; /* Espace intérieur des cellules */
        text-align: center; /* Centrer le contenu des cellules */
    }

    /* Bordure inférieure pour chaque ligne du tableau */
    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd; /* Ligne grise entre les lignes */
    }

    /* Lignes paires : couleur de fond différente */
    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3; /* Gris clair pour une ligne sur deux */
    }

    /* Changement de couleur au survol */
    .styled-table tbody tr:hover {
        background-color: #f1f9ff; /* Bleu très clair au survol */
        transition: background 0.3s; /* Transition fluide */
    }

    /* Style des boutons */
    .styled-table button {
        padding: 4px 8px; /* Espace intérieur */
        margin: 2px; /* Petit espacement entre les boutons */
        border: none; /* Pas de bordure */
        border-radius: 6px; /* Coins arrondis */
        font-size: 14px; /* Taille du texte */
        cursor: pointer; /* Curseur pointeur au survol */
    }

    /* Premier bouton (Modifier) */
    .styled-table button:first-child {
        background-color: #ffc107; /* Jaune (Bootstrap warning) */
        color: #212529; /* Texte noir/gris foncé */
    }

    /* Au survol du bouton Modifier */
    .styled-table button:first-child:hover {
        background-color: #e0a800; /* Jaune plus foncé */
    }

    /* Deuxième bouton (Supprimer) */
    .styled-table button:last-child {
        background-color: #dc3545; /* Rouge (Bootstrap danger) */
        color: white; /* Texte blanc */
    }

    /* Au survol du bouton Supprimer */
    .styled-table button:last-child:hover {
        background-color: #c82333; /* Rouge plus foncé */
    }

</style>

<div class="container">

    {{-- Message de succès --}}

    <h2>Catégories</h2>

    {{-- Formulaire d'ajout --}}
    <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" class="form-ajouter">
        @csrf
        <div class="ajouter-categorie">
            <div class="form-group">
                <label for="nom" class="label-form">Nom</label>
                <input type="text" name="nom" id="nom" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="photo" class="label-form">Photo</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button class="bouton-ajouter" type="submit">Ajouter</button>
            </div>
        </div>
    </form>

    {{-- Liste des catégories --}}
 
    <table class="styled-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($categories as $categorie)
            <tr>
                <td>
                    @if($categorie->image)
                        <img src="{{ $categorie->image ? asset($categorie->image) : asset('images/placeholder.png') }}" alt="photo" style="height:40px;">
                    @endif
                </td>
                <td>{{ $categorie->nom }}</td>
                <td style="display: flex; justify-content: center; gap: 5px;">
                    <form action="{{ route('categories.destroy', $categorie) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette catégorie ?')">Supprimer</button>
                    </form>
                    <!-- Pour la modification, tu peux faire un modal ou une autre page -->
                    <a href="{{ route('categories.edit', $categorie) }}" class="btn btn-warning btn-sm">Modifier</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection