@extends('admin.layout')

@section('title', 'Catégories')
@section('page-title', 'Gestion des catégories')

@section('content')
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="admin-card-title mb-0">
            <i class="fas fa-tags text-primary"></i>
            Catégories
        </h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $categorie)
                    <tr>
                        <td style="width: 80px;">
                            @if($categorie->image)
                                <img src="{{ $categorie->image ? asset($categorie->image) : asset('images/placeholder.png') }}" alt="" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $categorie->nom }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="{{ $categorie->id }}" data-nom="{{ $categorie->nom }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $categorie) }}" onsubmit="return confirm('Supprimer cette catégorie ?')">
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
                        <td colspan="3" class="text-center text-muted py-4">Aucune catégorie</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Ajouter une catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" class="form-control" required>
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

<!-- Modal: Edit -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" id="editCategoryForm" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Modifier la catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" id="editNom" class="form-control" required>
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
document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const nom = button.getAttribute('data-nom');
    const form = document.getElementById('editCategoryForm');
    document.getElementById('editNom').value = nom;
    form.action = `/admin/categories/${id}`;
});
</script>
@endpush
@endsection








