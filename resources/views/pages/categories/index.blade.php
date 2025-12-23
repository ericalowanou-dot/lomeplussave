<!-- resources/views/pages/categories/index.blade.php -->
@extends('layouts.app2')

@section('content')
<style>
    .container {
        max-width: 1000px;
        margin: auto;
        padding: 30px;
    }
    .card {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    .card img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 20px;
    }
    .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .actions button {
        margin-left: 5px;
    }
    .modal input {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
    }
    .form-inline {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
    }
</style>

<div class="container" style="padding-top: 180px;">
    <h2 class="mb-4">Gestion des Catégories</h2>

    <!-- Formulaire d'ajout -->
    <form id="addForm" enctype="multipart/form-data" class="form-inline">
        @csrf
        <input type="text" name="nom" placeholder="Nom de la catégorie" required>
        <input type="file" name="image">
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>

    <!-- Liste des catégories -->
    <div id="categoryList">
        @foreach($categories as $categorie)
            <div class="card" id="cat-{{ $categorie->id }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <img src="{{ $categorie->image ? asset($categorie->image) : asset('images/placeholder.png') }}" alt="">
                        <strong>{{ $categorie->nom }}</strong>
                    </div>
                    <div class="actions">
                        <button class="btn btn-sm btn-warning" onclick="openEditModal({{ $categorie->id }}, '{{ $categorie->nom }}')">Modifier</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory({{ $categorie->id }})">Supprimer</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal de modification -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editForm" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Modifier la catégorie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <input type="text" name="nom" id="edit-nom" required>
          <input type="file" name="image">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
    const token = document.querySelector('input[name="_token"]').value;

    // Ajouter
    document.getElementById('addForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        const res = await fetch("{{ route('categories.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            body: formData
        });

        const data = await res.json();
        if (data.id) location.reload();
    });

    // Supprimer
    function deleteCategory(id) {
        if (!confirm('Confirmer la suppression ?')) return;

        fetch(`/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token
            }
        }).then(() => {
            document.getElementById('cat-' + id).remove();
        });
    }

    // Modifier
    function openEditModal(id, nom) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-nom').value = nom;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    // Envoyer la modification
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        let id = document.getElementById('edit-id').value;
        let formData = new FormData(this);

        const res = await fetch(`/categories/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        });

        const data = await res.json();
        if (data.id) location.reload();
    });
</script>
@endsection
