@extends('layouts.auth')

@section('title', 'Transférer l\'annonce')

@section('content')
<div>
    <h2 class="h5 mb-3">Transférer l'annonce</h2>
    <p class="text-muted small mb-3">{{ $article->titre }}</p>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form method="POST" action="{{ route('articles.doTransfer', $article) }}">
        @csrf
        <div class="mb-3">
            <label for="user_id" class="form-label">Transférer à l'utilisateur</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">— Choisir un utilisateur —</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Transférer</button>
            <a href="{{ route('mes_annonces') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
