@extends('admin.layout')

@section('title', 'Gestion des publicités')
@section('page-title', 'Gestion des publicités')

@section('content')
<div class="search-filters">
    <form method="GET" action="{{ route('admin.publicites.index') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="Titre ou URL...">
            </div>
            
            <div class="filter-group">
                <label for="position" class="form-label">Position</label>
                <select name="position" id="position" class="form-control">
                    <option value="">Toutes</option>
                    <option value="header" {{ request('position') === 'header' ? 'selected' : '' }}>Header</option>
                    <option value="sidebar" {{ request('position') === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                    <option value="footer" {{ request('position') === 'footer' ? 'selected' : '' }}>Footer</option>
                    <option value="entre_articles" {{ request('position') === 'entre_articles' ? 'selected' : '' }}>Section annonces</option>
                    <option value="homepage_top" {{ request('position') === 'homepage_top' ? 'selected' : '' }}>Page d'accueil - Haut</option>
                    <option value="homepage_bottom" {{ request('position') === 'homepage_bottom' ? 'selected' : '' }}>Page d'accueil - Bas</option>
                    <option value="popup" {{ request('position') === 'popup' ? 'selected' : '' }}>Popup</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                <a href="{{ route('admin.publicites.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            </div>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="admin-card-title">
                <i class="fas fa-ad"></i>
                Liste des publicités ({{ $publicites->total() }})
            </h5>
            <a href="{{ route('admin.publicites.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter une publicité
            </a>
        </div>
    </div>
    <div class="admin-card-body">
        @if($publicites->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="80">Image</th>
                            <th>Titre / Position</th>
                            <th>URL</th>
                            <th>Dates</th>
                            <th>Statistiques</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publicites as $publicite)
                        <tr>
                            <td>
                                <img src="{{ asset($publicite->image) }}" 
                                     alt="{{ $publicite->titre ?? 'Publicité' }}" 
                                     class="img-thumbnail" 
                                     style="width:70px;height:70px;object-fit:cover;"
                                     onerror="this.src='{{ asset('images/placeholder.png') }}';">
                            </td>
                            <td>
                                <strong>{{ $publicite->titre ?? 'Sans titre' }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    {{ ucfirst(str_replace('_', ' ', $publicite->position)) }}
                                    @if($publicite->ordre)
                                        | Ordre: {{ $publicite->ordre }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($publicite->lien_url)
                                    <a href="{{ $publicite->lien_url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                        {{ $publicite->lien_url }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($publicite->date_debut || $publicite->date_fin)
                                    <small>
                                        <strong>Début:</strong> {{ $publicite->date_debut ? $publicite->date_debut->format('d/m/Y') : '—' }}<br>
                                        <strong>Fin:</strong> {{ $publicite->date_fin ? $publicite->date_fin->format('d/m/Y') : '—' }}
                                    </small>
                                @else
                                    <span class="text-muted">Permanent</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-eye"></i> {{ number_format($publicite->affichages, 0, ',', ' ') }} vues<br>
                                    <i class="fas fa-mouse-pointer"></i> {{ number_format($publicite->clics, 0, ',', ' ') }} clics
                                    @if($publicite->affichages > 0)
                                        <br>
                                        <span class="text-success">
                                            {{ number_format(($publicite->clics / $publicite->affichages) * 100, 2) }}% CTR
                                        </span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($publicite->isCurrentlyActive())
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                    <br>
                                    <small class="text-success">
                                        <i class="fas fa-eye"></i> Visible sur le site
                                    </small>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-pause-circle"></i> Inactive
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        @if(!$publicite->is_active)
                                            <i class="fas fa-ban"></i> Désactivée manuellement
                                        @elseif($publicite->date_debut && \Carbon\Carbon::parse($publicite->date_debut)->isFuture())
                                            <i class="fas fa-calendar"></i> Date de début non atteinte
                                        @elseif($publicite->date_fin && \Carbon\Carbon::parse($publicite->date_fin)->isPast())
                                            <i class="fas fa-calendar-times"></i> Date de fin dépassée
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.publicites.edit', $publicite) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.publicites.toggle', $publicite) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $publicite->is_active ? 'secondary' : 'success' }}" title="{{ $publicite->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas fa-{{ $publicite->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.publicites.delete', $publicite) }}" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publicité ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $publicites->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-ad fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune publicité trouvée.</p>
                <a href="{{ route('admin.publicites.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer une publicité
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Information sur l'affichage -->
<div class="admin-card mt-3">
    <div class="admin-card-body">
        <h6 class="mb-3"><i class="fas fa-info-circle text-info"></i> Guide d'utilisation</h6>
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">✅ Pour qu'une publicité s'affiche :</h6>
                <ul class="mb-3">
                    <li>Cocher <strong>"Publicité active"</strong> lors de la création</li>
                    <li>Si dates définies : être dans la période (début ≤ aujourd'hui ≤ fin)</li>
                    <li>Vérifier la <strong>position</strong> : elle détermine où la publicité apparaît</li>
                </ul>
                
                <h6 class="text-primary">📍 Positions disponibles :</h6>
                <ul class="mb-0">
                    <li><strong>Page d'accueil - Haut</strong> : En haut de la page principale</li>
                    <li><strong>Page d'accueil - Bas</strong> : En bas de la page principale</li>
                    <li><strong>Entre articles</strong> : Entre les articles dans les listes</li>
                    <li><strong>Header/Sidebar/Footer</strong> : À intégrer manuellement si besoin</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-warning">⚠️ Problèmes courants :</h6>
                <ul class="mb-3">
                    <li>Publicité <strong>désactivée</strong> → Ne s'affichera pas</li>
                    <li>Date de <strong>début future</strong> → Attendre la date</li>
                    <li>Date de <strong>fin passée</strong> → Mettre à jour la date</li>
                    <li>Image <strong>non trouvée</strong> → Vérifier le fichier</li>
                </ul>
                
                <h6 class="text-success">💡 Astuce :</h6>
                <p class="mb-0">
                    Pour tester rapidement, créez une publicité avec position 
                    <strong>"Page d'accueil - Haut"</strong>, cochez <strong>"Active"</strong>, 
                    et laissez les dates vides. Elle devrait apparaître immédiatement sur la page d'accueil.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Section de test -->
@php
    $testPub = \App\Models\Publicite::where('is_active', true)->first();
@endphp
@if($testPub)
<div class="admin-card mt-3 bg-light">
    <div class="admin-card-body">
        <h6 class="mb-3"><i class="fas fa-flask text-warning"></i> Test d'affichage</h6>
        <p>Publicité de test active trouvée :</p>
        <ul>
            <li><strong>ID :</strong> {{ $testPub->id }}</li>
            <li><strong>Position :</strong> {{ $testPub->position }}</li>
            <li><strong>Active :</strong> {{ $testPub->is_active ? 'Oui' : 'Non' }}</li>
            <li><strong>Visible :</strong> {{ $testPub->isCurrentlyActive() ? '✅ Oui' : '❌ Non' }}</li>
            <li><strong>Image :</strong> 
                @if(file_exists(public_path($testPub->image)))
                    <span class="text-success">✅ Existe</span> - 
                    <a href="{{ asset($testPub->image) }}" target="_blank">Voir</a>
                @else
                    <span class="text-danger">❌ Non trouvée</span> ({{ $testPub->image }})
                @endif
            </li>
        </ul>
        <div class="mt-2">
            <small class="text-muted">
                <strong>Requête SQL simulée :</strong><br>
                <code>Publicite::active()->byPosition('{{ $testPub->position }}')->get()</code>
            </small>
        </div>
    </div>
</div>
@endif
@endsection

