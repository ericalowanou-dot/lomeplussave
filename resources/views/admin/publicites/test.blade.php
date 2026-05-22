@extends('admin.layout')

@section('title', 'Test des publicités')
@section('page-title', 'Test d\'affichage des publicités')

@section('content')
<div class="admin-card">
    <div class="admin-card-body">
        <h5 class="mb-4">🔍 Test du système de publicités</h5>
        
        @php
            use App\Models\Publicite;
            $all = Publicite::all();
            $active = Publicite::where('is_active', true)->get();
            $activeWithScope = Publicite::active()->get();
        @endphp

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $all->count() }}</h3>
                        <p class="mb-0">Total publicités</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $active->count() }}</h3>
                        <p class="mb-0">Actives (is_active=true)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>{{ $activeWithScope->count() }}</h3>
                        <p class="mb-0">Actives (scope active())</p>
                    </div>
                </div>
            </div>
        </div>

        @if($all->count() == 0)
            <div class="alert alert-warning">
                <h6>⚠️ Aucune publicité trouvée</h6>
                <p>Il n'y a aucune publicité dans la base de données. Créez-en une d'abord.</p>
                <a href="{{ route('admin.publicites.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer une publicité
                </a>
            </div>
        @else
            <h6 class="mt-4">📋 Détails des publicités</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Position</th>
                        <th>Active (DB)</th>
                        <th>Visible</th>
                        <th>Image</th>
                        <th>Dates</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($all as $pub)
                        <tr>
                            <td>{{ $pub->id }}</td>
                            <td><span class="badge bg-secondary">{{ $pub->position }}</span></td>
                            <td>
                                @if($pub->is_active)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-danger">Non</span>
                                @endif
                            </td>
                            <td>
                                @if($pub->isCurrentlyActive())
                                    <span class="badge bg-success">✅ Oui</span>
                                @else
                                    <span class="badge bg-secondary">❌ Non</span>
                                    @if(!$pub->is_active)
                                        <br><small>Désactivée</small>
                                    @elseif($pub->date_debut && \Carbon\Carbon::parse($pub->date_debut)->isFuture())
                                        <br><small>Date début future</small>
                                    @elseif($pub->date_fin && \Carbon\Carbon::parse($pub->date_fin)->isPast())
                                        <br><small>Date fin passée</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if(file_exists(public_path($pub->image)))
                                    <span class="badge bg-success">✅ Existe</span>
                                    <br><a href="{{ asset($pub->image) }}" target="_blank" class="btn btn-sm btn-link">Voir</a>
                                @else
                                    <span class="badge bg-danger">❌ Manquante</span>
                                    <br><small>{{ $pub->image }}</small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    Début: {{ $pub->date_debut ? $pub->date_debut->format('Y-m-d') : 'N/A' }}<br>
                                    Fin: {{ $pub->date_fin ? $pub->date_fin->format('Y-m-d') : 'N/A' }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h6 class="mt-4">🧪 Test par position</h6>
            @php
                $positions = ['homepage_top', 'homepage_bottom', 'entre_articles', 'header', 'sidebar', 'footer'];
            @endphp
            @foreach($positions as $pos)
                @php
                    $pubs = Publicite::active()->byPosition($pos)->get();
                @endphp
                <div class="card mb-2">
                    <div class="card-body">
                        <strong>Position "{{ $pos }}":</strong> 
                        @if($pubs->count() > 0)
                            <span class="badge bg-success">{{ $pubs->count() }} publicité(s)</span>
                            <ul class="mb-0 mt-2">
                                @foreach($pubs as $p)
                                    <li>ID {{ $p->id }} - {{ $p->titre ?? 'Sans titre' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="badge bg-secondary">Aucune</span>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="alert alert-info mt-4">
                <h6>💡 Comment tester l'affichage</h6>
                <ol>
                    <li>Créez une publicité avec position "Page d'accueil - Haut"</li>
                    <li>Cochez "Publicité active"</li>
                    <li>Laissez les dates vides</li>
                    <li>Uploadez une image</li>
                    <li>Allez sur la <a href="{{ route('articles.index') }}" target="_blank">page d'accueil</a></li>
                    <li>La publicité devrait apparaître en haut de la page</li>
                </ol>
            </div>
        @endif
    </div>
</div>
@endsection

