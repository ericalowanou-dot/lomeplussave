<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   



    <title>en-tête</title>

    <link rel="stylesheet" href="{{asset('css/header.css')}}">

</head>

<body>



<header>



    <div class="logo" style="background: none; border: none; cursor: pointer;">

        <a href="{{route('articles.index')}}">

            <img src="{{ asset('images/true-logo.png') }}" alt="Logo" class="logo-img">

        </a>

    </div>



    <div class="icons">

        @php

            $unreadCount = 0;

            $userCoins = 0;

            if (auth()->check()) {

                try {

                    $unreadCount = \App\Models\Message::whereHas('recipients', function($q){

                        $q->where('recipient_id', auth()->id())

                          ->whereNull('read_at');

                    })->count();

                    $userCoins = auth()->user()->coins ?? 0;

                } catch (\Throwable $e) {

                    $unreadCount = 0;

                    $userCoins = auth()->user()->coins ?? 0;

                }

            }

        @endphp



        @if (auth()->check())

            <a href="{{ route('messages.inbox') }}" id="headerMessagesBtn" title="Mes messages" style="background: none; border: none; cursor: pointer; margin-right: 18px; display: inline-flex; align-items: center; gap: 4px;">

                <i class="bi bi-chat-left-text-fill" style="font-size: 20px; color: #475569;"></i>

                @if($unreadCount > 0)

                    <span style="background: linear-gradient(135deg, #FF9900, #E68900); color:#fff; font-size:10px; padding:2px 6px; border-radius:10px; min-width: 18px; text-align:center; font-weight: 700; box-shadow: 0 2px 6px rgba(255, 153, 0, 0.4);">{{ $unreadCount }}</span>

                @endif

            </a>



            {{-- <button id="headerCoinsBtn" title="Mes coins" style="background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; margin-right: 8px;">

                <img src="{{ asset('images/coins.png') }}" alt="coins" style="width:20px;height:20px;">

                <span style="font-size:12px; font-weight:700; color:#333;">{{ $userCoins }}</span>

            </button> --}}

        @endif

        <!-- Bouton "Coins" -->

        <!-- <button class="coins" id="coins-icon" style="background: none; border: none; font-size: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); ">

            💰

        </button> -->



    



        <!-- Bouton WhatsApp -->

         <!-- <button style="background: none; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); ">

            <a href="https://whatsapp.com/channel/0029VatlBs06GcG5owxIlF0T" target="_blank" class="icon-button">

                <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp" style="width: 27px; height: 27px;">

            </a>

        </button> -->



        <!-- Bouton Menu si connecté ou non-->

        @if (auth()->check())

            <button id="openAccountModal" class="menu-btn-pro" title="Menu">

                <i class="bi bi-grid-fill"></i>

            </button>

            <style>

                .menu-btn-pro {

                    background: none;

                    border-radius: 10px;

                    padding: 6px 10px;

                    cursor: pointer;

                    display: flex;

                    align-items: center;

                    justify-content: center;

                    transition: all 0.3s ease;

                    

                }

                .menu-btn-pro i {

                    font-size: 20px;

                    color: #475569;

                    transition: color 0.3s ease;

                }

                .menu-btn-pro:hover {

                    background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);

                    border-color: #FF9900;

                    box-shadow: 0 4px 12px rgba(255, 153, 0, 0.3);

                    transform: translateY(-1px);

                }

                .menu-btn-pro:hover i {

                    color: #fff;

                }

                .menu-btn-pro:active {

                    transform: translateY(0);

                }

            </style>

        @else

            <button id="ouvrirModalConnexion" style="background: none; border: none; cursor: pointer; box-shadow: 0 0 4px rgba(0, 0, 0, 0.2); border-radius: 50%;">

                <img src="{{ asset('images/user_default.png') }}" alt="connexion">

            </button>

            <style>

                #ouvrirModalConnexion {

                    width: 35px;

                    height: 35px;

                    display: flex;

                    justify-content: center;

                    align-items: center;

              

                }



            </style>

        @endif



        <!-- Modal Connexion/Inscription -->

        <div id="modal-auth" class="modal" style="display: none;">

            <div class="modal-content" style="max-width: 350px; margin: auto; background: #fff; border-radius: 12px; padding: 24px; text-align: center; position: relative;">

                <span class="close-button" id="close-auth-modal" style="position: absolute; top: 8px; right: 16px; font-size: 28px; cursor: pointer;">&times;</span>

                <h5 style="font-weight: bold;">Vous devez être connecté</h5>

                <div style="display: flex; text-align: center; align-items: center; justify-content: center; margin: 20px 0; gap: 10px;">

                    <a href="{{ route('login') }}" class="btn btn-primary w-100">Connexion</a>

                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Inscription</a>

                </div>

                <div>

                    <a href="{{ route('password.request') }}" class="text-muted" style="font-size: 0.9rem;">Mot de passe oublié ?</a>

                </div>

            </div>

        </div>

    

    <script>

        document.addEventListener('DOMContentLoaded', function() {

            var isAuthenticated = @auth true @else false @endauth;

            var boutonMegaphone = document.getElementById("ouvrirModalConnexion");

            var modalAuth = document.getElementById("modal-auth");

            var closeAuthModal = document.getElementById("close-auth-modal");

        

            // Vérifier que les éléments existent avant de les utiliser

            if (boutonMegaphone) {

            boutonMegaphone.addEventListener("click", function() {

                if (isAuthenticated) {

                    window.location.href = "{{ route('articles.create') }}";

                    } else if (modalAuth) {

                    modalAuth.style.display = "flex";

                }

            });

            }

        

            if (closeAuthModal && modalAuth) {

            closeAuthModal.addEventListener("click", function() {

                modalAuth.style.display = "none";

            });

            }

        

            // Fermer le modal si on clique en dehors du contenu

            if (modalAuth) {

            window.addEventListener("click", function(event) {

                if (event.target === modalAuth) {

                    modalAuth.style.display = "none";

                }

            });

            }

        });

    </script>

    

    <style>

        .modal {

            position: fixed;

            top: 0; left: 0; right: 0; bottom: 0;

            width: 100%;

            height: 100%;

            max-width: 100%;

            background: rgba(0, 0, 0, 0.3);

            display: none;

            justify-content: center;

            align-items: center;

            z-index: 9999;

            overflow-x: hidden;

        }

        /* Empilement modals (menu + sous-modals) — au-dessus header/nav */
        #accountModal.modal,
        #modal-auth.modal {
            position: fixed !important;
            inset: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 10050 !important;
            overflow-x: hidden;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        #editUserModal.edit-user-modal {
            z-index: 10080 !important;
        }

        #coinsRequestModal.secondary-modal,
        #proModal.secondary-modal,
        #certifierModal.secondary-modal,
        #boosterModal.secondary-modal,
        #signalerModal.secondary-modal {
            z-index: 10100 !important;
        }

        #accountModal.modal[style*="display: flex"],
        #accountModal.modal[style*="display:flex"] {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

    </style>

        







        <!-- Modal du compte utilisateur -->

        <div id="accountModal" class="modal" style="display: none;">

            <div class="modal-content account-modal-wrapper">

                <button id="closeAccountModal" class="fermer-modal-menu modal-close-control" type="button" aria-label="Fermer le menu compte">

                    <i class="bi bi-x-lg"></i>

                </button>

                

                <!-- Header du modal avec informations utilisateur -->

                <div class="tete-modal">

                    <div class="user-info-header">

                        @if (auth()->check())

                            @if(auth()->user()->photo_profil ?? false)

                                <img src="{{ asset(auth()->user()->photo_profil) }}" alt="avatar" class="avatar-header" id="avatar">

                            @else

                                <div class="avatar-header avatar-initial" id="avatar">

                                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}

                                </div>

                            @endif



                            <div class="user-details-header">

                                <span class="username-header" id="displayUserName">

                                    {{ auth()->user()->name }}

                                </span>

                                <span class="user-email-header">

                                    {{ auth()->user()->email }}

                                </span>

                            </div>

                        @endif 

                    </div>

                </div>



                <!-- Section actions -->

                <div class="modal-body-content">

                    <!-- Accès page profil complète -->
                    <a href="{{ route('profile.edit') }}" class="open-modal-btn profile-edit-link">
                        <span class="btn-icon">✏️</span>
                        <span class="btn-text">Modifier mon profil</span>
                    </a>



<!-- Modal (ton code inchangé) -->





<!-- Modal de modification des informations utilisateur -->

@if (auth()->check())

<div class="edit-user-modal" id="editUserModal" style="display: none;">

  <div class="edit-user-modal-content">

    <button class="edit-user-modal-close" id="editUserCloseBtn">&times;</button>

    <div class="edit-user-modal-header">

      <div class="header-icon">👤</div>

      <h3>Modifier mes informations</h3>

      <p class="header-subtitle">Mettez à jour votre profil</p>

    </div>

    <div class="edit-user-modal-body">

      <form id="editUserForm" enctype="multipart/form-data">

        <!-- Aperçu de la photo -->

        <div class="photo-preview-section">

          <div class="photo-preview-container">

            <div class="photo-preview-inner">

              @if(auth()->user()->photo_profil ?? false)

                <img src="{{ auth()->user()->getProfilPhotoUrl() }}" alt="Avatar" id="photoPreview" class="photo-preview-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                <div id="photoPreviewPlaceholder" class="photo-preview-placeholder" style="display: none;">

                  {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}

                </div>

              @else

                <div id="photoPreview" class="photo-preview-placeholder">

                  {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}

                </div>

              @endif

              <div class="photo-overlay">

                <span class="photo-text">Cliquez pour changer</span>

              </div>
              
              <!-- Indicateur de chargement -->
              <div class="photo-upload-loader" id="photoUploadLoader" style="display: none;">
                <div class="photo-upload-spinner"></div>
                <span class="photo-upload-text">En cours...</span>
              </div>

            </div>

          </div>

          <p class="photo-hint">Cliquez sur la photo pour la modifier</p>

          <input type="file" id="userPhoto" name="photo" accept="image/*" class="photo-input-hidden">

        </div>



        <!-- Champ nom -->

        <div class="form-group">

          <label for="userName">

            <span class="label-icon">✏️</span>

            Nom <span class="required-badge">*</span>

          </label>

          <input type="text" id="userName" name="name" value="{{ auth()->user()->name }}" class="edit-input" required autocomplete="name">

        </div>



        <!-- Boutons -->

        <div class="edit-modal-actions">

          <button type="button" class="edit-btn-cancel" id="editUserCancelBtn">

            <span>Annuler</span>

          </button>

          <button type="submit" class="edit-btn-save">

            <span>Enregistrer</span>

            <span class="btn-icon">✓</span>

          </button>

        </div>

      </form>

    </div>

  </div>

</div>

@endif

<script>

document.addEventListener('DOMContentLoaded', function() {
  
  // Fonction pour afficher une notification toast professionnelle
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.innerHTML = `
      <div class="toast-icon">
        ${type === 'success' ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>'}
      </div>
      <div class="toast-message">${message}</div>
    `;
    
    // Styles pour le toast
    if (!document.getElementById('toast-styles')) {
      const style = document.createElement('style');
      style.id = 'toast-styles';
      style.textContent = `
        .toast-notification {
          position: fixed;
          top: 20px;
          right: 20px;
          background: #fff;
          border-radius: 12px;
          box-shadow: 0 8px 24px rgba(0,0,0,0.15);
          padding: 16px 20px;
          display: flex;
          align-items: center;
          gap: 12px;
          z-index: 10000;
          min-width: 300px;
          max-width: 400px;
          animation: slideInRight 0.3s ease-out;
          border-left: 4px solid;
        }
        .toast-success {
          border-left-color: #28a745;
        }
        .toast-success .toast-icon {
          color: #28a745;
          font-size: 1.5rem;
        }
        .toast-error {
          border-left-color: #dc3545;
        }
        .toast-error .toast-icon {
          color: #dc3545;
          font-size: 1.5rem;
        }
        .toast-message {
          flex: 1;
          font-size: 0.9375rem;
          font-weight: 500;
          color: #333;
        }
        @keyframes slideInRight {
          from {
            transform: translateX(100%);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
        @keyframes slideOutRight {
          from {
            transform: translateX(0);
            opacity: 1;
          }
          to {
            transform: translateX(100%);
            opacity: 0;
          }
        }
        @media (max-width: 768px) {
          .toast-notification {
            top: 10px;
            right: 10px;
            left: 10px;
            min-width: auto;
            max-width: none;
          }
        }
      `;
      document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    
    // Auto-hide après 4 secondes
    setTimeout(() => {
      toast.style.animation = 'slideOutRight 0.3s ease-out';
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 4000);
  }

  const modal = document.getElementById('editUserModal');

  // Ancien modal d'édition : seulement un <button>, pas le lien vers /profile
  const openBtn = document.querySelector('button.open-modal-btn');

  const closeBtn = document.getElementById('editUserCloseBtn');

  const cancelBtn = document.getElementById('editUserCancelBtn');

  const photoInput = document.getElementById('userPhoto');

  const photoPreview = document.getElementById('photoPreview');

  const photoContainer = document.querySelector('.photo-preview-container');



  // Ouvrir le modal

  if (openBtn) {

    openBtn.addEventListener('click', () => {

      if (modal) {

        modal.style.display = 'flex';

      }

    });

  }



  // Fermer le modal

  if (closeBtn) {

    closeBtn.addEventListener('click', () => {

      if (modal) {

        modal.style.display = 'none';

      }

    });

  }



  if (cancelBtn) {

    cancelBtn.addEventListener('click', () => {

      if (modal) {

        modal.style.display = 'none';

      }

    });

  }



  // Fermer en cliquant en dehors

  if (modal) {

    window.addEventListener('click', (e) => {

      if (e.target === modal) {

        modal.style.display = 'none';

      }

    });

  }



  // Gérer le clic sur la photo pour ouvrir le file input

  if (photoContainer && photoInput) {

    photoContainer.addEventListener('click', () => {

      photoInput.click();

    });

  }



  // Aperçu de la photo avant upload

  if (photoInput && photoPreview) {

    photoInput.addEventListener('change', function(e) {

      const file = e.target.files[0];

      if (file) {

        // Vérifier le type de fichier

        if (!file.type.startsWith('image/')) {

          alert('Veuillez sélectionner une image valide (JPG, PNG, GIF)');

          return;

        }



        // Vérifier la taille (max 5MB)

        if (file.size > 5 * 1024 * 1024) {

          alert('L\'image est trop volumineuse. Maximum 5MB autorisé.');

          return;

        }



        const reader = new FileReader();

        reader.onload = function(e) {

          // Si c'est un placeholder div, le remplacer par une image

          if (photoPreview.tagName && photoPreview.tagName.toLowerCase() !== 'img') {

            const img = document.createElement('img');

            img.id = 'photoPreview';

            img.className = 'photo-preview-img';

            img.src = e.target.result;

            img.alt = 'Photo de profil';

            photoPreview.replaceWith(img);

          } else {

            photoPreview.src = e.target.result;

          }

        };

        reader.readAsDataURL(file);

      }

    });

  }



  // Formulaire AJAX

  const editForm = document.getElementById('editUserForm');

  if (editForm) {

    editForm.addEventListener('submit', function (e) {

      e.preventDefault();
      
      // Afficher l'indicateur de chargement
      const photoInput = document.getElementById('userPhoto');
      const photoLoader = document.getElementById('photoUploadLoader');
      const submitBtn = editForm.querySelector('button[type="submit"]');
      
      // Vérifier si une photo est en cours d'upload
      if (photoInput && photoInput.files && photoInput.files.length > 0 && photoLoader) {
        photoLoader.style.display = 'flex';
      }
      
      // Désactiver le bouton de soumission
      if (submitBtn) {
        submitBtn.disabled = true;
        const btnText = submitBtn.querySelector('span:first-child');
        if (btnText) {
          btnText.textContent = 'Enregistrement...';
        }
      }



      let formData = new FormData(this);



      fetch("{{ route('user.update.ajax') }}", {

          method: 'POST',

          body: formData,

          headers: {

              'X-CSRF-TOKEN': '{{ csrf_token() }}'

          }

      })

      .then(res => res.json())

      .then(data => {
        
        // Cacher l'indicateur de chargement seulement si une photo était sélectionnée
        const photoInputForHide = document.getElementById('userPhoto');
        if (photoLoader && photoInputForHide && photoInputForHide.files && photoInputForHide.files.length > 0) {
          photoLoader.style.display = 'none';
        }
        
        // Réactiver le bouton
        if (submitBtn) {
          submitBtn.disabled = false;
          const btnText = submitBtn.querySelector('span:first-child');
          if (btnText) {
            btnText.textContent = 'Enregistrer';
          }
        }

          if (data.success) {

              // Mettre à jour l'affichage du nom

              var nameEl = document.getElementById('displayUserName');

              if (nameEl) {

                  nameEl.textContent = data.name;

              }



              // Mettre à jour l'avatar dans le header du modal principal

              if (data.photo) {

                  var avatarEl = document.getElementById('avatar');

                  if (avatarEl) {

                      if (avatarEl.tagName && avatarEl.tagName.toLowerCase() === 'img') {

                          avatarEl.src = data.photo;

                      } else {

                          var img = document.createElement('img');

                          img.id = 'avatar';

                          img.className = 'avatar-header';

                          img.src = data.photo;

                          img.alt = 'avatar';

                          avatarEl.replaceWith(img);

                      }

                  }

              }



              if (modal) {

                  modal.style.display = 'none';

              }

              showToast('Profil mis à jour avec succès !', 'success');

          } else {

              showToast('Une erreur est survenue lors de la mise à jour', 'error');

          }

      })

      .catch(err => {

          console.error(err);
          
          // Cacher l'indicateur de chargement en cas d'erreur seulement si une photo était sélectionnée
          const photoInput = document.getElementById('userPhoto');
          const photoLoader = document.getElementById('photoUploadLoader');
          if (photoLoader && photoInput && photoInput.files && photoInput.files.length > 0) {
            photoLoader.style.display = 'none';
          }
          
          // Réactiver le bouton
          const submitBtn = editForm.querySelector('button[type="submit"]');
          if (submitBtn) {
            submitBtn.disabled = false;
            const btnText = submitBtn.querySelector('span:first-child');
            if (btnText) {
              btnText.textContent = 'Enregistrer';
            }
          }

          showToast('Erreur réseau. Veuillez réessayer.', 'error');

      });

    });

  }

});

</script>



<style>

    /* ========== MODAL DE MODIFICATION UTILISATEUR ========== */

    .edit-user-modal {

        display: none;

        position: fixed;

        top: 0;

        left: 0;

        width: 100%;

        height: 100%;

        background: rgba(255, 255, 255, 0.3);

        backdrop-filter: blur(12px);

        -webkit-backdrop-filter: blur(12px);

        align-items: center;

        justify-content: center;

        z-index: 10080;

        animation: fadeInModal 0.3s ease;

    }



    .edit-user-modal-content {

        background: white;

        border-radius: 20px;

        width: 90%;

        max-width: 500px;

        max-height: 90vh;

        overflow-y: auto;

        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);

        position: relative;

        animation: slideDownModal 0.3s cubic-bezier(0.4, 0, 0.2, 1);

    }



    .edit-user-modal-close {

        position: absolute;

        top: 15px;

        right: 15px;

        background: #dc3545;

        color: white;

        border: none;

        border-radius: 50%;

        width: 38px;

        height: 38px;

        font-size: 24px;

        line-height: 1;

        cursor: pointer;

        display: flex;

        align-items: center;

        justify-content: center;

        z-index: 10;

        transition: all 0.3s ease;

        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);

    }



    .edit-user-modal-close:hover {

        background: #c82333;

        transform: scale(1.1) rotate(90deg);

        box-shadow: 0 6px 16px rgba(220, 53, 69, 0.6);

    }



    .edit-user-modal-header {

        padding: 30px 25px 20px;

        border-radius: 20px 20px 0 0;

        text-align: center;

        background: linear-gradient(135deg, #ff7b00 0%, #f57c00 100%);

        position: relative;

        overflow: hidden;

    }



    .edit-user-modal-header .header-icon {

        font-size: 48px;

        margin-bottom: 10px;

        animation: bounceIn 0.6s ease;

    }



    .edit-user-modal-header h3 {

        margin: 0 0 8px 0;

        color: #fff;

        font-size: 1.6rem;

        font-weight: 700;

        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);

    }



    .edit-user-modal-header .header-subtitle {

        margin: 0;

        color: rgba(255, 255, 255, 0.95);

        font-size: 0.9rem;

        font-weight: 500;

    }



    .edit-user-modal-body {

        padding: 30px 25px;

    }



    /* Section photo preview */

    .photo-preview-section {

        text-align: center;

        margin-bottom: 30px;

    }



    .photo-preview-container {

        position: relative;

        display: inline-block;

        width: 120px;

        height: 120px;

        border-radius: 50%;

        overflow: visible;

        cursor: pointer;

        transition: all 0.3s ease;

    }



    .photo-preview-container::after {

        content: '📷';

        position: absolute;

        bottom: 5px;

        right: 5px;

        width: 32px;

        height: 32px;

        background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 14px;

        box-shadow: 0 2px 8px rgba(255, 153, 0, 0.4);

        border: 2px solid white;

        z-index: 10;

        animation: pulse-camera 2s infinite;

    }



    @keyframes pulse-camera {

        0%, 100% { transform: scale(1); }

        50% { transform: scale(1.1); }

    }



    .photo-preview-container:hover {

        transform: scale(1.05);

    }



    .photo-preview-container:hover::after {

        animation: none;

        transform: scale(1.15);

        box-shadow: 0 4px 12px rgba(255, 153, 0, 0.6);

    }



    .photo-preview-inner {

        width: 100%;

        height: 100%;

        border-radius: 50%;

        overflow: hidden;

        border: 4px solid #e0e0e0;

        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);

        transition: all 0.3s ease;

    }



    .photo-preview-container:hover .photo-preview-inner {

        border-color: #FF9900;

        box-shadow: 0 6px 20px rgba(255, 153, 0, 0.3);

    }



    .photo-preview-img {

        width: 100%;

        height: 100%;

        object-fit: cover;

    }



    .photo-preview-placeholder {

        width: 100%;

        height: 100%;

        background: linear-gradient(135deg, #515ffb 0%, #3b4fd8 100%);

        color: white;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 3rem;

        font-weight: bold;

    }



    .photo-overlay {

        position: absolute;

        top: 0;

        left: 0;

        width: 100%;

        height: 100%;

        background: rgba(0, 0, 0, 0.5);

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        opacity: 0;

        transition: opacity 0.3s ease;

        color: white;

        border-radius: 50%;

    }



    .photo-preview-container:hover .photo-overlay {

        opacity: 1;

    }



    .photo-text {

        font-size: 0.75rem;

        font-weight: 600;

        text-align: center;

        padding: 0 10px;

    }
    
    /* Indicateur de chargement upload */
    .photo-upload-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
        backdrop-filter: blur(4px);
    }
    
    .photo-upload-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #FF9900;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 8px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .photo-upload-text {
        font-size: 0.75rem;
        font-weight: 600;
        color: #FF9900;
        text-align: center;
    }



    .photo-hint {

        margin-top: 10px;

        font-size: 0.75rem;

        color: #FF9900;

        font-weight: 500;

        text-align: center;

        animation: fadeInOut 3s ease-in-out infinite;

    }



    @keyframes fadeInOut {

        0%, 100% { opacity: 0.6; }

        50% { opacity: 1; }

    }



    .photo-input-hidden {

        display: none;

    }



    /* Formulaires */

    .form-group {

        margin-bottom: 22px;

        text-align: left;

    }



    .form-group label {

        display: flex;

        align-items: center;

        gap: 8px;

        margin-bottom: 8px;

        color: #333;

        font-weight: 600;

        font-size: 0.95rem;

    }



    .edit-input {

        width: 100%;

        padding: 14px;

        border: 2px solid #e0e0e0;

        border-radius: 10px;

        font-size: 1rem;

        transition: all 0.3s ease;

        box-sizing: border-box;

        font-family: inherit;

        background: #fff;

    }



    .edit-input:focus {

        outline: none;

        border-color: #515ffb;

        box-shadow: 0 0 0 4px rgba(81, 95, 251, 0.15);

        transform: translateY(-2px);

    }



    /* Boutons */

    .edit-modal-actions {

        display: flex;

        gap: 12px;

        margin-top: 25px;

    }



    .edit-btn-cancel,

    .edit-btn-save {

        flex: 1;

        padding: 14px;

        border: none;

        border-radius: 10px;

        font-size: 1rem;

        font-weight: 600;

        cursor: pointer;

        transition: all 0.3s ease;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

    }



    .edit-btn-cancel {

        background: #f8f9fa;

        color: #666;

        border: 2px solid #e0e0e0;

    }



    .edit-btn-cancel:hover {

        background: #e9ecef;

        border-color: #ccc;

        transform: translateY(-2px);

        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);

    }



    .edit-btn-save {

        background: linear-gradient(135deg, #515ffb 0%, #3b4fd8 100%);

        color: white;

        box-shadow: 0 6px 20px rgba(81, 95, 251, 0.4);

    }



    .edit-btn-save:hover {

        transform: translateY(-3px);

        box-shadow: 0 8px 25px rgba(81, 95, 251, 0.6);

        background: linear-gradient(135deg, #3b4fd8 0%, #2a3bc7 100%);

    }



    .edit-btn-save:active {

        transform: translateY(-1px);

    }



    /* Scrollbar */

    .edit-user-modal-content::-webkit-scrollbar {

        width: 8px;

    }



    .edit-user-modal-content::-webkit-scrollbar-track {

        background: #f1f1f1;

        border-radius: 10px;

    }



    .edit-user-modal-content::-webkit-scrollbar-thumb {

        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

        border-radius: 10px;

    }



    /* Responsive */

    @media (max-width: 768px) {

        .edit-user-modal-content {

            width: 95%;

            max-width: 100%;

            border-radius: 16px;

            max-height: 85vh;

        }



        .edit-user-modal-header {

            padding: 25px 20px 15px;

        }



        .edit-user-modal-header h3 {

            font-size: 1.4rem;

        }



        .edit-user-modal-header .header-icon {

            font-size: 40px;

        }



        .edit-user-modal-body {

            padding: 25px 20px;

        }



        .photo-preview-container {

            width: 100px;

            height: 100px;

        }



        .photo-preview-container::after {

            width: 28px;

            height: 28px;

            font-size: 12px;

            bottom: 2px;

            right: 2px;

        }



        .photo-preview-placeholder {

            font-size: 2.5rem;

        }



        .photo-hint {

            font-size: 0.7rem;

        }

    }



    /* --- BOUTON D'OUVERTURE --- */

    .open-modal-btn {

        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

        color: white;

        padding: 12px 20px;

        border: none;

        border-radius: 10px;

        cursor: pointer;

        font-size: 14px;

        font-weight: 600;

        transition: all 0.3s ease;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);

        width: 100%;

    }



    .open-modal-btn:hover {

        transform: translateY(-2px);

        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);

    }



    .open-modal-btn:active {

        transform: translateY(0);

    }



    .open-modal-btn .btn-icon {

        font-size: 16px;

    }



    .open-modal-btn .btn-text {

        flex: 1;

    }

    a.open-modal-btn.profile-edit-link {
        text-decoration: none;
        color: #fff;
        box-sizing: border-box;
    }

</style>

                    <!-- Ajout de l'élément pour afficher un message si l'image est incorrecte -->

                    <p id="photoErrorMessage" style="color: red; display: none;">Format d'image invalide. Veuillez télécharger une image au format JPG, PNG ou GIF.</p>



                    

                    <!-- Grille des actions -->

                <div class="actions-grid">

                    <div class="action-container">

                        <a href="{{ route('mes_annonces') }}" class="action-link">

                            <button class="account-action" id="btnMesAnnonces">

                                <img src="{{asset('images/marketplace.png')}}" alt="Boutique">

                            </button>

                            <span class="action-text">Ma boutique</span>

                        </a>

                    </div>



                    <div class="action-container">

                        <a href="{{ route('mes_favoris') }}" class="action-link">

                            <button class="account-action favoris" id="btnMesFavoris">

                                <img src="{{asset('images/love.png')}}" alt="Favoris">

                            </button>

                            <span class="action-text">Mes favoris</span>

                        </a>

                    </div>



                    <div class="action-container">

                        <button class="account-action booster" id="btnBoosterProduit">

                            🚀

                        </button>

                        <span class="action-text">Booster mon produit</span>

                    </div>



                    <div class="action-container">

                        <button class="account-action certifier" id="btnCertifierBoutique">

                            <img src="{{asset('images/certife.png')}}" alt="">

                        </button>

                        <span class="action-text">Certifier ma boutique</span>

                    </div>





                    <div class="action-container">

                        <button class="account-action signaler" id="btnSignalerProbleme">

                            <img src="{{asset('images/danger.png')}}" alt="">

                        </button>

                        <span class="action-text">Signaler un problème</span>

                    </div>



                    <div class="action-container">

                        <a href="{{ route('about') }}">

                            <button class="account-action favoris" id="btnMesFavoris">

                                <img src="{{asset('images/true-logo.png')}}" alt="">

                            </button>

                        </a>

                        <span class="action-text">A propos de nous</span>

                    </div>

                </div>



                <form id="logoutForm" action="{{ route('logout') }}" method="POST">

                    @csrf

                    <button type="button" class="Deconnexion" id="btnLogout">

                        <i class="bi bi-box-arrow-right"></i>

                        Déconnexion

                    </button>

                </form>

                    <style>

                        .Deconnexion {

                            position: relative;

                            left: 50%;

                            transform: translateX(-50%);

                            width: 125px;

                            padding: 1px;

                            text-align: center;

                            background: #fff;

                            border: 2px solid #dc3545;

                            color: #dc3545;

                            font-weight: 600;

                            font-size: 14px;

                            cursor: pointer;

                            margin-top: 2px;

                            border-radius: 10px;

                            transition: all 0.3s ease;

                            display: flex;

                            align-items: center;

                            justify-content: center;

                            gap: 3px;

                        }



                        .Deconnexion:hover {

                            background: #dc3545;

                            color: #fff;

                            transform: translateY(-2px);

                            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);

                        }

                    </style>





                    <script>

                        document.addEventListener('DOMContentLoaded', function() {

                            const btnLogout = document.getElementById('btnLogout');

                            if (btnLogout) {

                                btnLogout.addEventListener('click', function(e) {

                                    if (confirm('Voulez-vous vraiment vous déconnecter ?')) {

                                        const logoutForm = document.getElementById('logoutForm');

                                        if (logoutForm) {

                                            logoutForm.submit();

                                        }

                                    }

                                });

                            }

                        });

                    </script>

            </div>

        </div>



        



        <style>

            /* Styles du modal */

            .modal {

                display: none; /* Cache le modal par défaut */

                position: fixed; /* Fixe le modal à l'écran pour qu'il ne défile pas avec la page */

                top: 0; /* Positionne le modal en haut de la page */

                left: 0; /* Positionne le modal à gauche de la page */

                width: 100%; /* S'étend sur toute la largeur de l'écran */

                height: 100%; /* S'étend sur toute la hauteur de l'écran */

                background: rgba(255, 255, 255, 0.3); /* Fond flou au lieu de noir */

                backdrop-filter: blur(12px);  /* Floute le fond (supporté sur Chrome/Edge/Safari) */

                -webkit-backdrop-filter: blur(12px); /* Support Safari */

                align-items: center; /* Centre le contenu du modal verticalement */

                justify-content: center; /* Centre le contenu du modal horizontalement */

                z-index: 10050; /* Aligné sur #accountModal (évite conflit avec sous-modals) */

            }



            /* Modals secondaires (ouverts depuis accountModal) - au-dessus du menu */

            #certifierModal,

            #boosterModal,

            #signalerModal,

            #coinsRequestModal,

            #proModal {

                z-index: 10100 !important;

            }



                @keyframes modalFadeIn {

                from {

                    opacity: 0; /* Rend le modal invisible au début */

                    transform: translateY(-60px) scale(0.50); /* Déplace le modal vers le haut et réduit sa taille */

                }

                to {

                    opacity: 1; /* Rend le modal visible à la fin */

                    transform: translateY(0) scale(1); /* Ramène le modal à sa position d'origine et à sa taille normale */

                }

            }

            .modal-content {

                animation: modalFadeIn 0.3s cubic-bezier(.4,0,.2,1); /* Applique l'animation de fondu et de montée */

                background: white; /* Définit un fond blanc pour le contenu du modal */

                padding: 0; /* Pas de padding général, on le met sur les sections */

                border-radius: 20px; /* Arrondit les coins du modal */

                width: 90%; /* Définit la largeur à 90% de l'écran */

                max-width: 420px; /* Largeur maximale augmentée pour 3 colonnes */

                max-height: 90vh; /* Limite la hauteur à 90% de la hauteur de l'écran */

                position: relative; /* Permet le positionnement absolu d'éléments enfants comme le bouton de fermeture */

                text-align: center; /* Centre le texte à l'intérieur */

                overflow: hidden; /* Cache ce qui dépasse */

                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); /* Ombre plus prononcée */

                display: flex;

                flex-direction: column;

            }



            .account-modal-wrapper {

                display: flex;

                flex-direction: column;

                max-height: 90vh;

                overflow-y: auto; /* Permet le scroll si le contenu dépasse */

                overflow-x: hidden;

                /* Style de la scrollbar pour un rendu plus propre */

                scrollbar-width: thin;

                scrollbar-color: rgba(244, 117, 26, 0.5) transparent;

            }



            /* Style de la scrollbar pour Webkit (Chrome, Safari, Edge) */

            .account-modal-wrapper::-webkit-scrollbar {

                width: 6px;

            }



            .account-modal-wrapper::-webkit-scrollbar-track {

                background: transparent;

            }



            .account-modal-wrapper::-webkit-scrollbar-thumb {

                background: rgba(244, 117, 26, 0.5);

                border-radius: 3px;

            }



            .account-modal-wrapper::-webkit-scrollbar-thumb:hover {

                background: rgba(244, 117, 26, 0.8);

            }



            @media (max-width: 480px) {

                .modal-content {

                    width: 90%;

                    max-width: 350px;

                    border-radius: 15px;

                    max-height: 85vh;

                }

                

                .account-modal-wrapper {

                    max-height: 85vh;

                }

            }



 









            /* === Boutons de fermeture === */

            .modal-close-control {

                position: absolute;

                top: 16px;

                right: 16px;

                width: 40px;

                height: 40px;

                border-radius: 50%;

                border: none;

                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);

                color: #f97316;

                display: inline-flex;

                align-items: center;

                justify-content: center;

                font-size: 1.1rem;

                box-shadow: 0 10px 24px rgba(249, 115, 22, 0.3);

                cursor: pointer;

                transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;

                z-index: 20;

            }



            .modal-close-control i {

                pointer-events: none;

            }



            .modal-close-control:hover {

                transform: translateY(-2px) scale(1.04);

                background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);

                color: #ffffff;

                box-shadow: 0 14px 30px rgba(239, 68, 68, 0.35);

            }



            .modal-close-control:active {

                transform: translateY(0) scale(0.96);

            }



            @media (max-width: 768px) {

                .modal-close-control {

                    top: 12px;

                    right: 12px;

                    width: 38px;

                    height: 38px;

                    font-size: 1rem;

                }

            }







            .modal-body-content {

                padding: 15px 20px;

                flex: 1;

                display: flex;

                flex-direction: column;

                gap: 12px;

                overflow-y: auto; /* Permet le scroll si nécessaire */

                overflow-x: hidden;

            }



            .actions-grid{

                padding-top: 5px;

                margin-top: 5px;

                padding-bottom: 5px;

            }



          

            /* Titre */

            .modal-title {

                color:rgb(255, 255, 255); /* Définit la couleur du texte en orange */

                margin-bottom: 15px; /* Ajoute un espacement en bas du titre */

            }



            /* Styles hérités - gardés pour compatibilité */

            .user-info {

                display: flex;

                align-items: center;

                gap: 5px;

                justify-content: center;

            }



            .avatar {

                width: 30px;

                height: 30px;

                border-radius: 50%;

                border: 1px solid;

            }



            .username {

                font-weight: bold;

            }



            .edit-btn, .link-btn {

                background: none; /* Pas de fond */

                border: none; /* Pas de bordure */

                cursor: pointer; /* Indique que ces boutons sont cliquables */

            }



            /* Grille des actions */

            .actions-grid {

                display: grid; /* Utilisation de CSS Grid pour organiser les éléments */

                grid-template-columns: repeat(2, 1fr); /* Deux colonnes par défaut (mobile) */

                gap: 6px; /* Espacement réduit */

                text-align: center; /* Centre le contenu */

            }



            /* Sur desktop (PC), utiliser 3 colonnes */

            @media (min-width: 992px) {

                .actions-grid {

                    grid-template-columns: repeat(3, 1fr); /* Trois colonnes sur PC */

                    gap: 8px; /* Espacement réduit */

                }

            }



            /* Conteneur de chaque bouton avec le texte */

            .action-container {

                display: flex; /* Utilise Flexbox */

                flex-direction: column; /* Dispose les éléments en colonne (image au-dessus du texte) */

                align-items: center; /* Centre horizontalement les éléments */

                text-align: center; /* Centre le texte */

                justify-content: flex-start; /* Aligne en haut */

                width: 100%; /* Prend toute la largeur disponible */

            }



            .action-link {

                display: flex;

                flex-direction: column;

                align-items: center;

                text-decoration: none;

                color: inherit;

            }



            /* Style général des boutons */

            .account-action {

                padding: 0; /* Supprime le padding */

                border-radius: 12px; /* Arrondit les coins */

                border: none; /* Supprime les bordures */

                color: white; /* Définit la couleur du texte en blanc */

                height: 70px; /* Hauteur réduite pour économiser l'espace */

                width: 70px; /* Largeur réduite pour économiser l'espace */

                display: flex; /* Utilise Flexbox pour centrer le contenu */

                justify-content: center; /* Centre horizontalement */

                align-items: center; /* Centre verticalement */

                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);

                transition: all 0.3s ease;

                cursor: pointer;

                margin: 0 auto; /* Centre le bouton dans son conteneur */

            }



            /* Sur desktop, réduire encore la taille des boutons */

            @media (min-width: 992px) {

                .account-action {

                    height: 65px;

                    width: 65px;

                }

            }



            .account-action:hover {

                transform: translateY(-3px);

                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);

            }



            .account-action:active {

                transform: translateY(-1px);

            }



            /* Style des images à l'intérieur des boutons */

            .account-action img {

                width: 100%; /* S'adapte à la largeur du bouton */

                height: 100%; /* S'adapte à la hauteur du bouton */

                object-fit: contain; /* Ajuste l'image sans la déformer */

                max-width: 60px; /* Limite la taille max */

                max-height: 60px; /* Limite la taille max */

            }



            @media (min-width: 992px) {

                .account-action img {

                    max-width: 55px;

                    max-height: 55px;

                }

            }



                /* Texte sous le bouton */

                .action-text {

                    margin-top: 6px; /* Réduit l'espacement au-dessus */

                    margin-bottom: 10px; /* Réduit encore l'espacement en bas */

                    font-size: 10px; /* Taille de texte plus petite */

                    font-weight: 600; /* Met le texte en demi-gras */

                    color: #333; /* Couleur de texte plus douce */

                    width: 100%; /* Prend toute la largeur disponible */

                    max-width: 80px; /* Limite la largeur max */

                    text-align: center; /* Centre le texte */

                    word-wrap: break-word; /* Permet de couper les mots longs si nécessaire */

                    line-height: 1.2; /* Réduit l'espacement entre les lignes */

                    transition: color 0.3s ease;

                }



                @media (min-width: 992px) {

                    .action-text {

                        margin-bottom: 8px;

                        font-size: 9px;

                        max-width: 70px;

                    }

                }



                .action-link:hover .action-text {

                    color: rgb(244, 117, 26);

                }



            /* Styles spécifiques pour chaque bouton */



            /* Bouton "Mes annonces" */

            #btnMesAnnonces {

                background: #FFA500; /* Couleur orange */

            }



            /* Cibler les icônes des boutons spécifiques */

            #btnBoosterProduit, #btnRechargerCompte{

            font-size: 40px;  /* augmente la taille des emojis */

            }





            /* Bouton "Mes favoris" */

            .favoris {

                border: 1px solid blue; /* Ajoute une bordure bleue */

                background: rgb(255, 255, 255); /* Fond gris */

            }



            /* Bouton "Booster un produit" */

            .booster {

                border: 1px solid blue; /* Ajoute une bordure bleue */

                background: #1E90FF; /* Couleur bleue vive */

            }



            /* Bouton "Certifier ma boutique" */

            .certifier {

                border: 1px solid blue; /* Ajoute une bordure bleue */

                background: rgb(255, 255, 255); /* Fond gris clair */

                

            }



            /* Bouton "Recharger mon compte" */

            .recharge {

                background:rgb(65, 238, 13); /* Couleur jaune dorée */

            }



            /* Bouton "Signaler un problème" */

            .signaler {

                border: 1px solid blue; /* Ajoute une bordure bleue */

                background: rgb(255, 255, 255); /* Fond gris clair */

            }



        </style>

        <style>

            .tete-modal{

                width: 100%;

                min-height: 100px; /* Réduit la hauteur minimale */

                background: linear-gradient(135deg, rgb(244, 117, 26) 0%, rgba(255, 153, 0, 1) 100%);

                border-radius: 15px 15px 0 0;

                padding: 15px 20px; /* Réduit le padding vertical */

                display: flex;

                align-items: center;

                justify-content: center;

                position: relative;

                box-shadow: 0 4px 10px rgba(244, 117, 26, 0.3);

                flex-shrink: 0; /* Empêche le header de rétrécir */

            }



            @media (min-width: 992px) {

                .tete-modal {

                    min-height: 90px; /* Encore plus compact sur PC */

                    padding: 12px 20px;

                }

            }



            .user-info-header {

                display: flex;

                align-items: center;

                gap: 12px;

                width: 100%;

            }



            .avatar-header {

                width: 60px;

                height: 60px;

                border-radius: 50%;

                border: 3px solid rgba(255, 255, 255, 0.9);

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 1.6em;

                background: rgba(255, 255, 255, 0.2);

                color: #fff;

                font-weight: bold;

                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);

                object-fit: cover;

                flex-shrink: 0; /* Empêche l'avatar de rétrécir */

            }



            @media (min-width: 992px) {

                .avatar-header {

                    width: 55px;

                    height: 55px;

                    font-size: 1.5em;

                }

            }



            .user-details-header {

                display: flex;

                flex-direction: column;

                align-items: flex-start;

                flex: 1;

            }



            .username-header {

                font-weight: 700;

                font-size: 1.1em;

                color: #fff;

                margin-bottom: 3px;

                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);

                line-height: 1.2;

            }



            @media (min-width: 992px) {

                .username-header {

                    font-size: 1em;

                    margin-bottom: 2px;

                }

            }



            .user-email-header {

                font-size: 0.8em;

                color: rgba(255, 255, 255, 0.9);

                word-break: break-word;

                line-height: 1.2;

            }



            @media (min-width: 992px) {

                .user-email-header {

                    font-size: 0.75em;

                }

            }



            

            .avatar {

                width: 60px;

                height: 60px;

                border-radius: 50%;

                border: 1px solid #ffffffff;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 1.5em;

                background: #ffe0b2;

                color: #f57c00;

                font-weight: bold;

                object-fit: cover;

            }

            .avatar-initial {

                background: #ffffffff;

                color: #f57c00;

                font-size: 1.5em;

                font-weight: bold;

            }

        

            .user-details {

                display: flex;

                flex-direction: column;

                align-items: flex-start;

                margin-left: 5px;

            }

            .username {

                font-weight: bold;

                color: rgb(255, 255, 255);

                font-size: 1.1em;

            }

            .user-email {

                font-size: 0.95em;

                color: rgb(255, 255, 255);

            }

            .modifier-user-info {  

                position: absolute;

                bottom: 5px;

                right:  5px;

                background: rgba(253, 253, 253, 1);

                border: 1px solid rgb(255, 255, 255);

                border-radius: 5px;

                cursor: pointer;

                color: rgba(42, 41, 41, 1);

                font-size: 0.9em;

                margin-top: 5px;

            }



        </style>





        <!-- JavaScript pour gérer l'ouverture et la fermeture du modal -->

        <script>



                // Récupérer les éléments

                // const avatarImg = document.getElementById('avatar');

                // const uploadPhoto = document.getElementById('uploadPhoto');

                // const changeAvatarBtn = document.getElementById('changeAvatarBtn');

                // const photoErrorMessage = document.getElementById('photoErrorMessage');

                



                // Lorsque l'utilisateur clique sur le bouton de changement de photo

                // changeAvatarBtn.addEventListener('click', function () {

                //     uploadPhoto.click(); // Simule un clic sur le champ de téléchargement d'image

                // });



                // Lorsque l'utilisateur choisit une image

                // uploadPhoto.addEventListener('change', function (event) {

                //     const file = event.target.files[0]; // Récupère le fichier sélectionné



                // Vérification si un fichier est sélectionné et s'il est d'un format d'image valide

            //     if (file) {

            //         const reader = new FileReader();

            //         const validFormats = ['image/jpeg', 'image/png', 'image/gif'];



            //         // Vérifie le format de l'image

            //         if (validFormats.includes(file.type)) {

            //             // Masquer le message d'erreur si l'image est valide

            //             photoErrorMessage.style.display = 'none';



            //             reader.onload = function () {

            //                 avatarImg.src = reader.result; // Affiche l'image téléchargée dans l'avatar

            //             };



            //             reader.readAsDataURL(file); // Lire le fichier en tant qu'URL de données

            //         } else {

            //             // Affiche un message d'erreur si le format de l'image est incorrect

            //             photoErrorMessage.style.display = 'block';

            //         }

            //     }

            // }); 









            document.addEventListener('DOMContentLoaded', function() {

                // Déplace les modals hors du <header> (transform / hauteur 45px
                // sinon cassent position:fixed — visible surtout sans barre nav, ex. boutique)
                function ensureModalOnBody(el) {
                    if (el && el.parentElement !== document.body) {
                        document.body.appendChild(el);
                    }
                    return el;
                }

                [
                    'accountModal',
                    'modal-auth',
                    'editUserModal',
                    'coinsRequestModal',
                    'proModal',
                    'certifierModal',
                    'boosterModal',
                    'signalerModal',
                ].forEach(function (id) {
                    ensureModalOnBody(document.getElementById(id));
                });

                const openAccountBtn = document.getElementById('openAccountModal');

                if (openAccountBtn) {

                    openAccountBtn.addEventListener('click', function () {

                        const accountModal = ensureModalOnBody(document.getElementById('accountModal'));

                        if (accountModal) {

                            accountModal.style.display = 'flex';

                        }

                    });

                }



                const closeAccountBtn = document.getElementById('closeAccountModal');

                if (closeAccountBtn) {

                    closeAccountBtn.addEventListener('click', function () {

                        const accountModal = document.getElementById('accountModal');

                        if (accountModal) {

                            accountModal.style.display = 'none';

                        }

                    });

                }



                // permet de fermer le modal lorsqu'on clique sur une autre partie vide du modal

                const accountModal = document.getElementById("accountModal");

                if (accountModal) {

                    accountModal.addEventListener("click", function(event) {

                        if (event.target === this) {

                            this.style.display = "none";

                        }

                    });

                }

            });



        </script>













          

    </div>

</header>



<div id="coinsRequestModal" class="secondary-modal" style="display: none;">

    <div class="secondary-modal-content coins-modal">

        <button id="closeCoinsModal" class="secondary-modal-close modal-close-control" type="button" aria-label="Fermer la demande de coins">

            <i class="bi bi-x-lg"></i>

        </button>

        <div class="secondary-modal-header coins-header">

            <div class="header-icon">💳</div>

            <h3>Recharger mes coins</h3>

            <p class="header-subtitle">Transmettez votre demande à l'équipe Lome+.</p>

        </div>

        <div class="secondary-modal-body">

            <div class="coins-balance-card">

                <p class="coins-balance-label">Solde actuel</p>

                <p class="coins-balance-value">{{ number_format($userCoins ?? 0, 0, ',', ' ') }} coins</p>

            </div>



            <form id="coinsRequestForm" class="secondary-form" onsubmit="return false;">

                <div class="form-group">

                    <label for="coinsAmount">

                        <span class="label-icon">🔢</span>

                        Nombre de coins souhaité <span class="required-badge">*</span>

                    </label>

                    <input type="number" id="coinsAmount" name="coins_amount" class="secondary-input" min="1" step="1" placeholder="Ex : 50" required>

                </div>

            </form>



            <div class="coins-helper" id="coinsWhatsappHelper">

                Saisissez le nombre de coins souhaité pour générer votre message WhatsApp.

            </div>



            <a href="#" id="coinsWhatsappBtn" class="secondary-btn-whatsapp coins-whatsapp-btn is-disabled" target="_blank" rel="noopener" aria-disabled="true">

                <span>Contacter l'équipe sur WhatsApp</span>

            </a>



            <p class="coins-note">Un administrateur confirmera votre paiement et créditera votre boutique dans l'espace Pro.</p>

        </div>

    </div>

</div>



<!-- Modal Pro - Bientôt disponible -->

<div id="proModal" class="secondary-modal" style="display: none;">

    <div class="secondary-modal-content pro-modal">

        <button id="closeProModal" class="secondary-modal-close modal-close-control" type="button" aria-label="Fermer">

            <i class="bi bi-x-lg"></i>

        </button>

        <div class="pro-body">

            <div class="pro-icon-container">

                <span class="pro-icon">⏳</span>

            </div>

            <h4 class="pro-title">Bientôt disponible</h4>

            <button type="button" class="pro-btn-ok" onclick="closeModal('proModal')">

                OK

            </button>

        </div>

    </div>

</div>



<style>

    /* ========== MODAL PRO ========== */

    .pro-modal {

        max-width: 320px;

        border-radius: 16px;

        overflow: hidden;

    }



    .pro-body {

        text-align: center;

        padding: 40px 30px;

    }



    .pro-icon-container {

        width: 70px;

        height: 70px;

        margin: 0 auto 20px;

        background: linear-gradient(135deg, #f4751a 0%, #ff9a44 100%);

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        box-shadow: 0 6px 20px rgba(244, 117, 26, 0.3);

    }



    .pro-icon {

        font-size: 32px;

    }



    .pro-title {

        color: #333;

        font-size: 1.3rem;

        font-weight: 600;

        margin: 0 0 25px 0;

    }



    .pro-btn-ok {

        background: linear-gradient(135deg, #f4751a 0%, #ff9a44 100%);

        color: white;

        border: none;

        border-radius: 8px;

        padding: 12px 50px;

        font-size: 1rem;

        font-weight: 600;

        cursor: pointer;

        transition: all 0.3s ease;

        box-shadow: 0 4px 15px rgba(244, 117, 26, 0.3);

    }



    .pro-btn-ok:hover {

        transform: translateY(-2px);

        box-shadow: 0 6px 20px rgba(244, 117, 26, 0.4);

    }

</style>



{{-- Modal Certifier commenté - à réactiver plus tard

<div id="certifierModal" class="secondary-modal" style="display: none;">

    <div class="secondary-modal-content">

        <button id="closeCertifierModal" class="secondary-modal-close modal-close-control" type="button" aria-label="Fermer la certification">

            <i class="bi bi-x-lg"></i>

        </button>

        <div class="secondary-modal-header certifier-header">

            <div class="header-icon">🏆</div>

            <h3>Certifier ma boutique</h3>

            <p class="header-subtitle">Améliorez la visibilité de votre boutique</p>

        </div>

        <div class="secondary-modal-body">

            <div class="info-card">

                <div class="info-row">

                    <span class="info-label">💰 Solde disponible</span>

                    <span class="info-value" id="userCoinsCert">--</span>

                    <span class="info-unit">coins</span>

                </div>

                <div class="info-row">

                    <span class="info-label">💵 Coins restants après</span>

                    <span class="info-value" id="remainingCoins">--</span>

                    <span class="info-unit">coins</span>

                </div>

            </div>

            

            <div class="status-card">

                <div class="status-row">

                    <span class="status-label">Certification actuelle</span>

                    <span class="status-badge" id="currentCertification">--</span>

                </div>

                <div class="status-row">

                    <span class="status-label">Date d'expiration</span>

                    <span class="status-value" id="certificationExpiry">--</span>

                </div>

            </div>



            <form id="certifyForm" class="secondary-form">

                <div class="form-group">

                    <label for="certDays">

                        <span class="label-icon">📅</span>

                        Nombre de jours

                    </label>

                    <p class="form-hint">1 coin = 1 jour de certification</p>

                    <input type="number" id="certDays" name="days" min="1" value="7" class="secondary-input" required />

                </div>

                <button type="submit" class="secondary-btn-primary">

                    <span>Certifier ma boutique</span>

                    <span class="btn-icon">✓</span>

                </button>

            </form>

            

            <div class="contact-section">

                <p class="contact-text">Besoin d'aide ?</p>

                <a href="https://wa.me/" target="_blank" class="secondary-btn-whatsapp">

                    <span>💬 Contacter via WhatsApp</span>

                </a>

            </div>

        </div>

    </div>

</div>

--}}



{{-- Modal Booster commenté - à réactiver plus tard

<div id="boosterModal" class="secondary-modal" style="display: none;">

    <div class="secondary-modal-content">

        <button id="closeBoosterModal" class="secondary-modal-close modal-close-control" type="button" aria-label="Fermer le boost">

            <i class="bi bi-x-lg"></i>

        </button>

        <div class="secondary-modal-header booster-header">

            <div class="header-icon">🚀</div>

            <h3>Booster un produit</h3>

            <p class="header-subtitle">Augmentez la visibilité de votre article</p>

        </div>

        <div class="secondary-modal-body">

            <div class="info-card">

                <div class="info-row">

                    <span class="info-label">💰 Solde disponible</span>

                    <span class="info-value" id="userCoinsBoost">--</span>

                    <span class="info-unit">coins</span>

                </div>

            </div>



            <form id="boostForm" class="secondary-form">

                <div class="form-group">

                    <label for="boostArticle">

                        <span class="label-icon">📦</span>

                        Choisir un article

                    </label>

                    <select id="boostArticle" class="secondary-select" required>

                        <option value="">Sélectionnez un article...</option>

                    </select>

                </div>

                <div class="form-group">

                    <label for="boostDays">

                        <span class="label-icon">📅</span>

                        Nombre de jours

                    </label>

                    <p class="form-hint">1 coin = 1 jour de boost</p>

                    <input type="number" id="boostDays" name="days" min="1" value="7" class="secondary-input" required />

                </div>

                <button type="submit" class="secondary-btn-primary">

                    <span>Booster mon article</span>

                    <span class="btn-icon">🚀</span>

                </button>

            </form>

        </div>

    </div>

</div>

--}}



<!-- Modal pour Signaler un problème -->

<div id="signalerModal" class="secondary-modal" style="display: none;">

    <div class="secondary-modal-content">

        <button id="closeSignalerModal" class="secondary-modal-close modal-close-control" type="button" aria-label="Fermer le signalement">

            <i class="bi bi-x-lg"></i>

        </button>

        <div class="secondary-modal-header reporter-header">

            <div class="header-icon">⚠️</div>

            <h3>Signaler un problème</h3>

            <p class="header-subtitle">Aidez-nous à améliorer notre service</p>

        </div>

        <div class="secondary-modal-body">

            <form id="reportForm" class="secondary-form">

                <div class="form-group">

                    <label for="reportSubject">

                        <span class="label-icon">📝</span>

                        Sujet <span class="optional-badge">(optionnel)</span>

                    </label>

                    <input type="text" id="reportSubject" name="subject" class="secondary-input" maxlength="150" placeholder="Ex: Problème de paiement, bug, etc." />

                </div>

                <div class="form-group">

                    <label for="reportMessage">

                        <span class="label-icon">✉️</span>

                        Message <span class="required-badge">*</span>

                    </label>

                    <textarea id="reportMessage" name="message" class="secondary-textarea" rows="5" placeholder="Décrivez le problème en détail..." required minlength="10" maxlength="2000"></textarea>

                    <p class="form-hint"><span id="reportMessageCounter">0</span> / 10 caractères minimum</p>

                    <p id="reportMessageError" class="form-error text-danger small mt-1" style="display:none;" role="alert"></p>

                </div>

                <button type="submit" class="secondary-btn-primary">

                    <span>Envoyer le signalement</span>

                    <span class="btn-icon">📤</span>

                </button>

            </form>

            

            <div class="contact-section">

                <p class="contact-text">Besoin d'une réponse rapide ?</p>

                <a href="https://wa.me/" target="_blank" class="secondary-btn-whatsapp">

                    <span>💬 Contacter via WhatsApp</span>

                </a>

            </div>

        </div>

    </div>

</div>



<style>

    /* ========== MODALS SECONDAIRES PROFESSIONNELS ========== */

    .secondary-modal {

        display: none;

        position: fixed;

        top: 0;

        left: 0;

        width: 100%;

        height: 100%;

        background: rgba(255, 255, 255, 0.3);

        backdrop-filter: blur(12px);

        -webkit-backdrop-filter: blur(12px);

        align-items: center;

        justify-content: center;

        z-index: 10100;

        animation: fadeInModal 0.3s ease;

    }



    .secondary-modal-content {

        background: white;

        border-radius: 20px;

        width: 90%;

        max-width: 480px;

        max-height: 90vh;

        overflow-y: auto;

        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);

        position: relative;

        animation: slideDownModal 0.3s cubic-bezier(0.4, 0, 0.2, 1);

    }



    .coins-modal {

        max-width: 420px;

    }



    .coins-header {

        background: linear-gradient(135deg, #ff8a3c 0%, #ff3d6b 100%);

    }



    .coins-balance-card {

        background: rgba(248, 250, 252, 0.95);

        border-radius: 16px;

        padding: 18px;

        border: 1px solid rgba(226, 232, 240, 0.8);

        margin-bottom: 18px;

        text-align: center;

    }



    .coins-balance-label {

        margin: 0;

        color: #6b7280;

        font-size: 0.85rem;

        letter-spacing: 0.4px;

        text-transform: uppercase;

        font-weight: 600;

    }



    .coins-balance-value {

        margin: 6px 0 0;

        font-size: 1.6rem;

        font-weight: 700;

        color: #ff5d62;

    }



    .coins-helper {

        font-size: 0.9rem;

        color: #475569;

        background: rgba(248, 250, 252, 0.9);

        border-radius: 12px;

        padding: 12px 14px;

        border: 1px solid rgba(226, 232, 240, 0.7);

        margin-bottom: 16px;

        text-align: center;

    }



    .coins-whatsapp-btn {

        margin-top: 8px;

    }



    .coins-whatsapp-btn.is-disabled {

        opacity: 0.6;

        pointer-events: none;

        cursor: not-allowed;

    }



    .coins-note {

        margin-top: 16px;

        font-size: 0.85rem;

        color: #64748b;

        text-align: center;

    }



    @keyframes fadeInModal {

        from { opacity: 0; }

        to { opacity: 1; }

    }



    @keyframes slideDownModal {

        from {

            transform: translateY(-50px);

            opacity: 0;

        }

        to {

            transform: translateY(0);

            opacity: 1;

        }

    }



    /* Bouton de fermeture */



    /* Header avec gradient orange */

    .secondary-modal-header {

        padding: 30px 25px 20px;

        border-radius: 20px 20px 0 0;

        text-align: center;

        position: relative;

        overflow: hidden;

        background: linear-gradient(135deg, #ff7b00 0%, #f57c00 100%);

    }



    .certifier-header {

        background: linear-gradient(135deg, #ff7b00 0%, #f57c00 100%);

    }



    .booster-header {

        background: linear-gradient(135deg, #ff7b00 0%, #f57c00 100%);

    }



    .reporter-header {

        background: linear-gradient(135deg, #ff7b00 0%, #f57c00 100%);

    }

    

    /* Header par défaut pour tous les modals secondaires */

    .secondary-modal-header {

        background: linear-gradient(135deg, #ff7b00 0%, #f57c00 100%);

    }



    .header-icon {

        font-size: 48px;

        margin-bottom: 10px;

        animation: bounceIn 0.6s ease;

    }



    @keyframes bounceIn {

        0% { transform: scale(0); }

        50% { transform: scale(1.2); }

        100% { transform: scale(1); }

    }



    .secondary-modal-header h3 {

        margin: 0 0 8px 0;

        color: #fff;

        font-size: 1.6rem;

        font-weight: 700;

        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);

    }



    .header-subtitle {

        margin: 0;

        color: rgba(255, 255, 255, 0.95);

        font-size: 0.9rem;

        font-weight: 500;

    }



    /* Body */

    .secondary-modal-body {

        padding: 25px;

    }



    /* Cartes d'information */

    .info-card,

    .status-card {

        background: #f8f9fa;

        border-radius: 12px;

        padding: 18px;

        margin-bottom: 20px;

        border-left: 4px solid #667eea;

    }



    .status-card {

        background: #e3f2fd;

        border-left-color: #2196f3;

    }



    .info-row,

    .status-row {

        display: flex;

        justify-content: space-between;

        align-items: center;

        padding: 10px 0;

        border-bottom: 1px solid rgba(0, 0, 0, 0.05);

    }



    .info-row:last-child,

    .status-row:last-child {

        border-bottom: none;

        padding-bottom: 0;

    }



    .info-label,

    .status-label {

        color: #666;

        font-size: 0.9rem;

        font-weight: 600;

        display: flex;

        align-items: center;

        gap: 6px;

    }



    .info-value {

        color: #f4751a;

        font-weight: 700;

        font-size: 1.3rem;

        display: flex;

        align-items: baseline;

        gap: 4px;

    }



    .info-unit {

        color: #999;

        font-size: 0.85rem;

        font-weight: 400;

    }



    .status-badge {

        padding: 6px 12px;

        border-radius: 20px;

        font-size: 0.85rem;

        font-weight: 600;

        background: #4caf50;

        color: white;

    }



    .status-badge:empty::before {

        content: "--";

        color: #999;

        background: transparent;

    }



    .status-value {

        color: #333;

        font-weight: 600;

        font-size: 0.95rem;

    }



    /* Formulaires */

    .secondary-form {

        margin-top: 20px;

    }



    .form-group {

        margin-bottom: 22px;

        text-align: left;

    }



    .form-group label {

        display: flex;

        align-items: center;

        gap: 8px;

        margin-bottom: 8px;

        color: #333;

        font-weight: 600;

        font-size: 0.95rem;

    }



    .label-icon {

        font-size: 1.1rem;

    }



    .form-hint {

        font-size: 0.8rem;

        color: #666;

        margin-top: 4px;

        margin-bottom: 8px;

        font-style: italic;

    }



    .optional-badge {

        color: #999;

        font-weight: 400;

        font-size: 0.85rem;

    }



    .required-badge {

        color: #dc3545;

        font-weight: 700;

    }



    .secondary-input,

    .secondary-select,

    .secondary-textarea {

        width: 100%;

        padding: 14px;

        border: 2px solid #e0e0e0;

        border-radius: 10px;

        font-size: 1rem;

        transition: all 0.3s ease;

        box-sizing: border-box;

        font-family: inherit;

        background: #fff;

    }



    .secondary-input:focus,

    .secondary-select:focus,

    .secondary-textarea:focus {

        outline: none;

        border-color: #515ffb;

        box-shadow: 0 0 0 4px rgba(81, 95, 251, 0.15);

        transform: translateY(-2px);

    }



    .secondary-textarea {

        resize: vertical;

        min-height: 120px;

    }



    /* Boutons avec bleu du site */

    .secondary-btn-primary {

        width: 100%;

        padding: 16px;

        background: linear-gradient(135deg, #515ffb 0%, #3b4fd8 100%);

        color: white;

        border: none;

        border-radius: 12px;

        font-size: 1rem;

        font-weight: 700;

        cursor: pointer;

        transition: all 0.3s ease;

        margin-top: 10px;

        box-shadow: 0 6px 20px rgba(81, 95, 251, 0.4);

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

    }



    .secondary-btn-primary:hover {

        transform: translateY(-3px);

        box-shadow: 0 8px 25px rgba(81, 95, 251, 0.6);

        background: linear-gradient(135deg, #3b4fd8 0%, #2a3bc7 100%);

    }



    .secondary-btn-primary:active {

        transform: translateY(-1px);

    }



    .btn-icon {

        font-size: 1.2rem;

    }



    /* Section contact */

    .contact-section {

        margin-top: 25px;

        padding-top: 20px;

        border-top: 2px solid #f0f0f0;

        text-align: center;

    }



    .contact-text {

        color: #666;

        font-size: 0.9rem;

        margin-bottom: 12px;

        font-weight: 500;

    }



    .secondary-btn-whatsapp {

        display: inline-block;

        padding: 14px 24px;

        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);

        color: white;

        text-decoration: none;

        border-radius: 12px;

        font-weight: 600;

        transition: all 0.3s ease;

        box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);

        width: 100%;

        text-align: center;

    }



    .secondary-btn-whatsapp:hover {

        transform: translateY(-3px);

        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);

        color: white;

        text-decoration: none;

    }



    /* Scrollbar personnalisée */

    .secondary-modal-content::-webkit-scrollbar {

        width: 8px;

    }



    .secondary-modal-content::-webkit-scrollbar-track {

        background: #f1f1f1;

        border-radius: 10px;

    }



    .secondary-modal-content::-webkit-scrollbar-thumb {

        background: linear-gradient(135deg, #515ffb 0%, #3b4fd8 100%);

        border-radius: 10px;

    }



    .secondary-modal-content::-webkit-scrollbar-thumb:hover {

        background: linear-gradient(135deg, #3b4fd8 0%, #2a3bc7 100%);

    }



    /* Responsive */

    @media (max-width: 768px) {

        .secondary-modal-content {

            width: 95%;

            max-width: 100%;

            border-radius: 16px;

            max-height: 85vh;

        }



        .secondary-modal-header {

            padding: 25px 20px 15px;

        }



        .secondary-modal-header h3 {

            font-size: 1.4rem;

        }



        .header-icon {

            font-size: 40px;

        }



        .secondary-modal-body {

            padding: 20px;

        }





        .info-card,

        .status-card {

            padding: 15px;

        }



        .info-value {

            font-size: 1.1rem;

        }

    }

</style>



<script>

    // Fonction globale pour ouvrir un modal (utilisée par les modals secondaires)

    function openModal(modalId) {

        const modal = document.getElementById(modalId);

        if (modal) {

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            modal.style.display = 'flex';

            // Sous-modals au-dessus du menu compte (10050)
            if (['boosterModal', 'certifierModal', 'signalerModal', 'coinsRequestModal', 'proModal', 'editUserModal'].includes(modalId)) {

                modal.style.zIndex = '10100';

            }

        }

    }



    // Fonction globale pour fermer un modal

    function closeModal(modalId) {

        const modal = document.getElementById(modalId);

        if (modal) {

            modal.style.display = 'none';

        }

    }



    // Attendre que le DOM soit chargé avant d'attacher les événements

    document.addEventListener('DOMContentLoaded', function() {

        

        // Bouton Booster - Ouvre le modal Pro (bientôt disponible)

        const btnBooster = document.getElementById('btnBoosterProduit');

        if (btnBooster) {

            btnBooster.addEventListener('click', function () {

                openModal('proModal');

            });

        }

        

        // Bouton Certifier - Ouvre le modal Pro (bientôt disponible)

        const btnCertifier = document.getElementById('btnCertifierBoutique');

        if (btnCertifier) {

            btnCertifier.addEventListener('click', function () {

                openModal('proModal');

            });

        }



        // Bouton fermer modal Pro

        const closeProBtn = document.getElementById('closeProModal');

        if (closeProBtn) {

            closeProBtn.addEventListener('click', function () {

                closeModal('proModal');

            });

        }



        // Fermer le modal Pro si on clique en dehors

        const proModal = document.getElementById('proModal');

        if (proModal) {

            proModal.addEventListener('click', function (event) {

                if (event.target === proModal) {

                    closeModal('proModal');

                }

            });

        }



        {{-- Code original des boutons Booster et Certifier - à réactiver plus tard

        // Bouton Booster

        const btnBooster = document.getElementById('btnBoosterProduit');

        if (btnBooster) {

            btnBooster.addEventListener('click', function () {

                openModal('boosterModal');

                // Charger articles de l'utilisateur

                fetch("{{ route('user.my-articles') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' }})

                    .then(r=>r.json()).then(list=>{

                        const sel = document.getElementById('boostArticle');

                        if (sel) {

                            sel.innerHTML = '';

                            (list||[]).forEach(a=>{

                                const opt = document.createElement('option');

                                opt.value = a.id; opt.textContent = a.titre;

                                sel.appendChild(opt);

                            });

                        }

                    }).catch(err => console.error('Erreur chargement articles:', err));

                

                // Charger le solde de coins

                fetch("/user/info", { headers: { 'X-Requested-With': 'XMLHttpRequest' }})

                    .then(r=>r.json()).then(userInfo=>{

                        const coinsBoost = document.getElementById('userCoinsBoost');

                        if (coinsBoost) {

                            coinsBoost.textContent = userInfo.coins || 0;

                        }

                    }).catch(err => console.error('Erreur chargement coins:', err));

            });

        }

        

        // Bouton Certifier

        const btnCertifier = document.getElementById('btnCertifierBoutique');

        if (btnCertifier) {

            btnCertifier.addEventListener('click', function () {

                openModal('certifierModal');

                // Charger les informations de l'utilisateur

                fetch("/user/info", { headers: { 'X-Requested-With': 'XMLHttpRequest' }})

                    .then(r=>r.json()).then(userInfo=>{

                        const coinsCert = document.getElementById('userCoinsCert');

                        const remainingCoins = document.getElementById('remainingCoins');

                        const currentCert = document.getElementById('currentCertification');

                        const certExpiry = document.getElementById('certificationExpiry');

                        

                        if (coinsCert) coinsCert.textContent = userInfo.coins || 0;

                        if (remainingCoins) remainingCoins.textContent = userInfo.coins || 0;

                        

                        if (userInfo.certifie_until) {

                            const expiryDate = new Date(userInfo.certifie_until);

                            const now = new Date();

                            if (expiryDate > now) {

                                if (currentCert) {

                                    currentCert.textContent = 'Certifiée';

                                    currentCert.style.background = '#4caf50';

                                }

                                if (certExpiry) certExpiry.textContent = expiryDate.toLocaleDateString('fr-FR');

                            } else {

                                if (currentCert) {

                                    currentCert.textContent = 'Expirée';

                                    currentCert.style.background = '#dc3545';

                                }

                                if (certExpiry) certExpiry.textContent = 'Expirée le ' + expiryDate.toLocaleDateString('fr-FR');

                            }

                        } else {

                            if (currentCert) {

                                currentCert.textContent = 'Non certifiée';

                                currentCert.style.background = '#999';

                            }

                            if (certExpiry) certExpiry.textContent = 'N/A';

                        }

                    }).catch(err => console.error('Erreur chargement infos user:', err));

            });

        }

        --}}



        // Bouton Signaler

        const btnSignaler = document.getElementById('btnSignalerProbleme');

        if (btnSignaler) {

            btnSignaler.addEventListener('click', function () {

                openModal('signalerModal');
                const f = document.getElementById('reportForm');
                const err = document.getElementById('reportMessageError');
                const cnt = document.getElementById('reportMessageCounter');
                if (f) f.reset();
                if (err) { err.style.display = 'none'; err.textContent = ''; }
                if (cnt) cnt.textContent = '0';

            });

        }



        {{-- Boutons de fermeture des anciens modals - commentés

        const closeBooster = document.getElementById('closeBoosterModal');

        if (closeBooster) {

            closeBooster.addEventListener('click', function () {

                closeModal('boosterModal');

            });

        }



        const closeCertifier = document.getElementById('closeCertifierModal');

        if (closeCertifier) {

            closeCertifier.addEventListener('click', function () {

                closeModal('certifierModal');

            });

        }

        --}}



        const closeSignaler = document.getElementById('closeSignalerModal');

        if (closeSignaler) {

            closeSignaler.addEventListener('click', function () {

                closeModal('signalerModal');

            });

        }



        // Fermer le modal si on clique en dehors du contenu (modals secondaires)

        document.querySelectorAll('.secondary-modal').forEach(modal => {

            modal.addEventListener('click', function (event) {

                if (event.target === modal) {

                    modal.style.display = 'none';

                }

            });

        });



        // Soumission report + compteur de caractères

        const reportForm = document.getElementById('reportForm');
        const reportMessage = document.getElementById('reportMessage');
        const reportMessageCounter = document.getElementById('reportMessageCounter');
        const reportMessageError = document.getElementById('reportMessageError');

        if (reportForm && reportMessage) {

            function updateReportCounter() {
                const n = (reportMessage.value || '').trim().length;
                if (reportMessageCounter) reportMessageCounter.textContent = n;
                if (reportMessageError) { reportMessageError.style.display = 'none'; reportMessageError.textContent = ''; }
            }

            reportMessage.addEventListener('input', updateReportCounter);
            reportMessage.addEventListener('paste', function(){ setTimeout(updateReportCounter, 0); });
            updateReportCounter();

            reportForm.addEventListener('submit', function(e){

                e.preventDefault();

                const msg = (reportMessage.value || '').trim();
                const minLen = 10;

                if (reportMessageError) { reportMessageError.style.display = 'none'; reportMessageError.textContent = ''; }

                if (msg.length < minLen) {
                    if (reportMessageError) {
                        reportMessageError.textContent = 'Veuillez saisir au moins ' + minLen + ' caractères (actuellement ' + msg.length + ').';
                        reportMessageError.style.display = 'block';
                    }
                    reportMessage.focus();
                    return;
                }

                const fd = new FormData(reportForm);
                const submitBtn = reportForm.querySelector('button[type="submit"]');
                if (submitBtn) { submitBtn.disabled = true; }

                fetch(`{{ route('report.store') }}`, {

                    method: 'POST',

                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },

                    body: fd

                }).then(function(r){

                    return r.json().then(function(data){ return { ok: r.ok, status: r.status, data: data }; });

                }).then(function(res){

                    if (res.data.success){

                        alert('Merci pour votre signalement.');
                        closeModal('signalerModal');
                        reportForm.reset();
                        updateReportCounter();

                    } else {

                        let err = 'Impossible d\'envoyer le signalement.';
                        if (res.status === 422 && res.data.errors && res.data.errors.message && res.data.errors.message[0]) {
                            err = res.data.errors.message[0];
                        } else if (res.data.message) {
                            err = res.data.message;
                        }
                        if (reportMessageError) {
                            reportMessageError.textContent = err;
                            reportMessageError.style.display = 'block';
                        } else {
                            alert(err);
                        }

                    }

                }).catch(function(){

                    if (reportMessageError) {
                        reportMessageError.textContent = 'Erreur réseau. Vérifiez votre connexion et réessayez.';
                        reportMessageError.style.display = 'block';
                    } else {
                        alert('Erreur réseau.');
                    }

                }).finally(function(){

                    if (submitBtn) submitBtn.disabled = false;

                });

            });

        }



        {{-- Soumission Boost - commenté

        const boostForm = document.getElementById('boostForm');

        if (boostForm) {

            boostForm.addEventListener('submit', function(e){

                e.preventDefault();

                const articleIdEl = document.getElementById('boostArticle');

                const daysEl = document.getElementById('boostDays');

                if (articleIdEl && daysEl && articleIdEl.value) {

                    const fd = new FormData();

                    fd.append('days', daysEl.value);

                    fetch(`/user/boost/${articleIdEl.value}`, {

                        method: 'POST',

                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },

                        body: fd

                    }).then(r=>r.json()).then(data=>{

                        if (data.success){

                            alert('Article boosté jusqu\'au ' + data.boosted_until);

                            closeModal('boosterModal');

                            window.location.reload();

                        } else {

                            alert(data.message || 'Erreur');

                        }

                    }).catch(()=>alert('Erreur réseau'));

                } else {

                    alert('Veuillez sélectionner un article.');

                }

            });

        }



        // Soumission Certification

        const certifyForm = document.getElementById('certifyForm');

        if (certifyForm) {

            certifyForm.addEventListener('submit', function(e){

                e.preventDefault();

                const daysEl = document.getElementById('certDays');

                if (daysEl) {

                    const fd = new FormData();

                    fd.append('days', daysEl.value);

                    fetch(`{{ route('user.certify') }}`, {

                        method: 'POST',

                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },

                        body: fd

                    }).then(r=>r.json()).then(data=>{

                        if (data.success){

                            alert('Boutique certifiée jusqu\'au ' + data.certifie_until);

                            closeModal('certifierModal');

                            window.location.reload();

                        } else {

                            alert(data.message || 'Erreur');

                        }

                    }).catch(()=>alert('Erreur réseau'));

                }

            });

        }



        // Calculer les coins restants pour la certification en temps réel

        const certDaysInput = document.getElementById('certDays');

        if (certDaysInput) {

            certDaysInput.addEventListener('input', function() {

                const days = parseInt(this.value) || 0;

                const userCoinsEl = document.getElementById('userCoinsCert');

                const remainingCoinsEl = document.getElementById('remainingCoins');

                if (userCoinsEl && remainingCoinsEl) {

                    const userCoins = parseInt(userCoinsEl.textContent) || 0;

                    const remaining = Math.max(0, userCoins - days);

                    remainingCoinsEl.textContent = remaining;

                    

                    // Changer la couleur si insuffisant

                    if (remaining < 0 || days > userCoins) {

                        remainingCoinsEl.style.color = '#dc3545';

                    } else {

                        remainingCoinsEl.style.color = '#f4751a';

                    }

                }

            });

        }

        --}}



        @php

            $coinsUserMeta = auth()->check()

                ? [

                    'id' => auth()->id(),

                    'name' => auth()->user()->name,

                    'email' => auth()->user()->email,

                ]

                : ['id' => null, 'name' => '', 'email' => ''];

        @endphp



        // Code du bouton de coins commenté

        // const coinsBtnTrigger = document.getElementById('headerCoinsBtn');

        const coinsModal = document.getElementById('coinsRequestModal');

        const closeCoinsBtn = document.getElementById('closeCoinsModal');

        const coinsAmountInput = document.getElementById('coinsAmount');

        const coinsWhatsappBtn = document.getElementById('coinsWhatsappBtn');

        const coinsHelper = document.getElementById('coinsWhatsappHelper');

        const userMeta = @json($coinsUserMeta);



        const formatAmount = (amount) => {

            return Number(amount).toLocaleString('fr-FR');

        };



        const buildWhatsappText = (amount) => {

            const lines = [

                'Bonjour équipe Lome+,',

                `Je souhaite recharger ${formatAmount(amount)} coin${amount > 1 ? 's' : ''}.`,

                'Mes informations :'

            ];



            if (userMeta.id !== null) {

                lines.push(`- ID utilisateur : ${userMeta.id}`);

            }

            if (userMeta.name) {

                lines.push(`- Nom : ${userMeta.name}`);

            }

            if (userMeta.email) {

                lines.push(`- Email : ${userMeta.email}`);

            }



            lines.push('Merci de me contacter pour finaliser le paiement.');



            return encodeURIComponent(lines.join('\n'));

        };



        const updateCoinsModalState = () => {

            if (!coinsAmountInput || !coinsWhatsappBtn || !coinsHelper) return;

            const amount = parseInt(coinsAmountInput.value, 10);



            if (Number.isFinite(amount) && amount > 0) {

                coinsWhatsappBtn.href = `https://wa.me/?text=${buildWhatsappText(amount)}`;

                coinsWhatsappBtn.classList.remove('is-disabled');

                coinsWhatsappBtn.setAttribute('aria-disabled', 'false');

                coinsHelper.textContent = `Un message vers WhatsApp est prêt pour ${formatAmount(amount)} coin${amount > 1 ? 's' : ''}.`;

            } else {

                coinsWhatsappBtn.href = '#';

                coinsWhatsappBtn.classList.add('is-disabled');

                coinsWhatsappBtn.setAttribute('aria-disabled', 'true');

                coinsHelper.textContent = 'Saisissez le nombre de coins souhaité pour générer votre message WhatsApp.';

            }

        };



        // if (coinsBtnTrigger && coinsModal) {

        //     coinsBtnTrigger.addEventListener('click', function () {

        //         if (userMeta.id === null) {

        //             window.location.href = "{{ route('login') }}";

        //             return;

        //         }

        //         openModal('coinsRequestModal');

        //         updateCoinsModalState();

        //         if (coinsAmountInput) {

        //             coinsAmountInput.focus();

        //         }

        //     });

        // }



        if (closeCoinsBtn) {

            closeCoinsBtn.addEventListener('click', function () {

                closeModal('coinsRequestModal');

            });

        }



        if (coinsModal) {

            coinsModal.addEventListener('click', function (event) {

                if (event.target === coinsModal) {

                    closeModal('coinsRequestModal');

                }

            });

        }



        if (coinsAmountInput) {

            coinsAmountInput.addEventListener('input', updateCoinsModalState);

        }

    });

</script>



</body>

</html>

