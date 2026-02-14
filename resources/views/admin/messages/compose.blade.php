@extends('admin.layout')

@section('title', 'Envoyer un message')
@section('page-title', 'Envoyer un message aux utilisateurs')

@section('content')
<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="admin-card-title">
            <i class="fas fa-envelope"></i>
            {{ $message ? 'Répondre à un message' : 'Envoyer un message' }}
        </h5>
        @if($message)
            <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour au message
            </a>
        @endif
    </div>
    <div class="admin-card-body">
        @if($message)
            <div class="alert alert-info mb-4">
                <h6><i class="fas fa-info-circle"></i> Réponse à :</h6>
                <p class="mb-1"><strong>De :</strong> {{ $message->sender->name }} ({{ $message->sender->email }})</p>
                <p class="mb-1"><strong>Sujet :</strong> {{ $message->subject ?: '(Sans sujet)' }}</p>
                <p class="mb-0"><strong>Message :</strong> {{ Str::limit(strip_tags($message->body), 200) }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.messages.send') }}" id="messageForm">
            @csrf
            @if($message)
                <input type="hidden" name="parent_message_id" value="{{ $message->id }}">
            @endif
            
            <div class="mb-3">
                <label class="form-label">Cible</label>
                <select name="recipient_scope" id="recipientScope" class="form-control" {{ $message ? 'disabled' : '' }}>
                    @if($message)
                        <option value="selected" selected>Répondre à l'expéditeur uniquement</option>
                    @else
                        <option value="all">Tous les utilisateurs</option>
                        <option value="active">Utilisateurs actifs uniquement</option>
                        <option value="blocked">Utilisateurs bloqués uniquement</option>
                        <option value="certified">Utilisateurs certifiés uniquement</option>
                        <option value="selected">Sélection manuelle</option>
                    @endif
                </select>
                @if($message)
                    <input type="hidden" name="recipient_scope" value="selected">
                    <input type="hidden" name="recipients[]" value="{{ $message->sender->id }}">
                    <small class="text-muted">Vous répondez à {{ $message->sender->name }}</small>
                @endif
            </div>

            <!-- Section de sélection manuelle -->
            <div id="manualSelection" class="mb-3" style="display: none;">
                <label class="form-label">Filtres de recherche</label>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-control">
                            <option value="all">Tous les statuts</option>
                            <option value="active">Actifs</option>
                            <option value="blocked">Bloqués</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="certifiedFilter" class="form-control">
                            <option value="all">Tous</option>
                            <option value="true">Certifiés</option>
                            <option value="false">Non certifiés</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="searchFilter" class="form-control" placeholder="Rechercher par nom/email">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Sélectionner les destinataires</label>
                    <div id="usersList" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center text-muted">Utilisez les filtres ci-dessus pour rechercher des utilisateurs</div>
                    </div>
                    <small class="text-muted">Destinataires sélectionnés: <span id="selectedCount">0</span></small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Sujet (optionnel)</label>
                <input type="text" name="subject" class="form-control" maxlength="200" 
                       value="{{ $message && $message->subject ? 'Re: ' . $message->subject : '' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="body" class="form-control" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Utiliser un Set avec conversion en nombre pour les IDs
let selectedUsers = new Set();

// Fonction debounce pour la recherche
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function loadUsers() {
    const status = document.getElementById('statusFilter').value;
    const certified = document.getElementById('certifiedFilter').value;
    const search = document.getElementById('searchFilter').value;
    
    const params = new URLSearchParams({
        status: status,
        certified: certified,
        search: search
    });
    
    fetch(`{{ route('admin.messages.users') }}?${params}`)
        .then(response => response.json())
        .then(users => {
            displayUsers(users);
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('usersList').innerHTML = '<div class="text-center text-danger">Erreur lors du chargement des utilisateurs</div>';
        });
}

function displayUsers(users) {
    const usersList = document.getElementById('usersList');
    if (!usersList) return;
    
    usersList.innerHTML = '';
    
    if (users.length === 0) {
        usersList.innerHTML = '<div class="text-center text-muted">Aucun utilisateur trouvé</div>';
        return;
    }
    
    users.forEach(user => {
        const userDiv = document.createElement('div');
        userDiv.className = 'form-check mb-2';
        const userId = parseInt(user.id);
        const isChecked = selectedUsers.has(userId);
        userDiv.innerHTML = `
            <input type="checkbox" class="form-check-input user-checkbox" 
                   value="${user.id}" id="user_${user.id}"
                   ${isChecked ? 'checked' : ''}>
            <label class="form-check-label" for="user_${user.id}">
                <strong>${escapeHtml(user.name)}</strong> (${escapeHtml(user.email)})
                <small class="text-muted d-block">
                    ${user.is_blocked ? '<span class="badge bg-danger">Bloqué</span>' : '<span class="badge bg-success">Actif</span>'}
                    ${user.certifie ? '<span class="badge bg-primary">Certifié</span>' : ''}
                </small>
            </label>
        `;
        usersList.appendChild(userDiv);
    });
    
    // Ajouter les event listeners
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const userId = parseInt(this.value);
            if (this.checked) {
                selectedUsers.add(userId);
            } else {
                selectedUsers.delete(userId);
            }
            updateSelectedCount();
        });
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateSelectedCount() {
    const countEl = document.getElementById('selectedCount');
    if (countEl) {
        countEl.textContent = selectedUsers.size;
    }
}

// S'assurer que le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    const recipientScope = document.getElementById('recipientScope');
    if (!recipientScope) return;
    
    recipientScope.addEventListener('change', function() {
        const manualSelection = document.getElementById('manualSelection');
        if (this.value === 'selected') {
            if (manualSelection) {
                manualSelection.style.display = 'block';
                loadUsers();
            }
        } else {
            if (manualSelection) {
                manualSelection.style.display = 'none';
            }
            selectedUsers.clear();
            updateSelectedCount();
        }
    });

    // Filtres - s'assurer qu'ils existent avant d'ajouter les listeners
    const statusFilter = document.getElementById('statusFilter');
    const certifiedFilter = document.getElementById('certifiedFilter');
    const searchFilter = document.getElementById('searchFilter');
    
    if (statusFilter) statusFilter.addEventListener('change', loadUsers);
    if (certifiedFilter) certifiedFilter.addEventListener('change', loadUsers);
    if (searchFilter) searchFilter.addEventListener('input', debounce(loadUsers, 300));

    // Gestion du formulaire
    const form = document.getElementById('messageForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const scope = document.getElementById('recipientScope').value;
            
            if (scope === 'selected') {
                // Nettoyer les inputs existants
                document.querySelectorAll('input[name="recipients[]"]').forEach(el => el.remove());
                
                // Vérifier qu'au moins un utilisateur est sélectionné
                if (selectedUsers.size === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins un destinataire.');
                    return false;
                }
                
                // Convertir le Set en tableau et ajouter comme inputs hidden
                Array.from(selectedUsers).forEach(userId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recipients[]';
                    input.value = String(userId);
                    form.appendChild(input);
                });
            }
        });
    }
});
</script>
@endpush
@endsection
