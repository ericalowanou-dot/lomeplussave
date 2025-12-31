@extends('admin.layout')

@section('title', 'Sous-catégories')
@section('page-title', 'Gestion des sous-catégories')

@section('content')
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="admin-card-title mb-0">
            <i class="fas fa-tag text-primary"></i>
            Sous-catégories
        </h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus"></i>
            Ajouter
        </button>
            </div>
    <div class="admin-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
        <thead>
            <tr>
                        <th>Photo</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
                    @forelse($sousCategories as $sousCategorie)
                    <tr>
                        <td style="width: 80px;">
                            @if($sousCategorie->image)
                                <img src="{{ $sousCategorie->image ? asset($sousCategorie->image) : asset('images/placeholder.png') }}" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
                            @else
                                <span class="text-muted">—</span>
                        @endif
                    </td>
                        <td>{{ $sousCategorie->nom }}</td>
                        <td>{{ $sousCategorie->categorie->nom ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="{{ $sousCategorie->id }}"
                                        data-nom="{{ $sousCategorie->nom }}"
                                        data-categorie="{{ $sousCategorie->categorie_id }}">
                                    <i class="fas fa-edit"></i>
                        </button>
                                <form method="POST" action="{{ route('admin.souscategories.destroy', $sousCategorie) }}" onsubmit="return confirm('Supprimer cette sous-catégorie ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                        </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Aucune sous-catégorie</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
                            <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('admin.souscategories.store') }}" enctype="multipart/form-data">
                                    @csrf
                                        <div class="modal-header">
        <h5 class="modal-title">Ajouter une sous-catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Catégorie</label>
          <select name="categorie_id" class="form-select" required>
            <option value="" disabled selected>Choisir...</option>
            @foreach($categories as $categorie)
                <option value="{{ $categorie->id }}">{{ $categorie->nom }}</option>
            @endforeach
          </select>
                                            </div>
                                            <div class="mb-3">
          <label class="form-label">Photo (optionnel)</label>
          <input type="file" name="image" class="form-control" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" id="editForm" enctype="multipart/form-data">
                            @csrf 
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Modifier la sous-catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" id="editNom" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Catégorie</label>
          <select name="categorie_id" id="editCategorie" class="form-select" required>
            @foreach($categories as $categorie)
                <option value="{{ $categorie->id }}">{{ $categorie->nom }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Photo (optionnel)</label>
          <input type="file" name="image" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
      </div>
                        </form>
  </div>
</div>

@push('scripts')
<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const nom = button.getAttribute('data-nom');
    const categorieId = button.getAttribute('data-categorie');
    const form = document.getElementById('editForm');
    document.getElementById('editNom').value = nom;
    document.getElementById('editCategorie').value = categorieId;
    form.action = `/admin/souscategories/${id}`;
});
</script>
@endpush
@endsection
