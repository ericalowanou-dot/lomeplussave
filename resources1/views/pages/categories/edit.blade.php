
@extends('layouts.app2')

@section('title', 'Gestion des catégories')

@section('content')



<form method="POST" action="{{ route('categories.update', $categorie->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modification">
        <h2>modifier une catégorie</h2>
        <input type="text" name="nom" value="{{ $categorie->nom }}" required>
        <input type="file" name="photo">

        <button type="submit">Enregistrer</button>
    </div>
    <style>
    .modification {
        padding-top: 400px;
    }
    </style>
</form>

@endsection