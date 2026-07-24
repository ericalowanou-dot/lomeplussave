@extends('layouts.app2')

@section('title', 'Détails de l\'article')

@section('content')


    <!doctype html>
    <html lang="en" data-bs-theme="light">
    <head>
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
            integrity="sha512-Zzv6WHN8...etc..."
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        />
        <!-- pour le icons boostrap -->
        <link href="{{ asset('css/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
        <!-- font awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <link rel="stylesheet" href="{{asset('css/articles.css')}}">
        <link rel="stylesheet" href="{{asset('css/style.css')}}"> 
        <link rel="stylesheet" href="{{asset('css/detail_article.css')}}"> 

        <link rel="stylesheet" href="{{asset('css/all.min.css')}}" crossorigin="anonymous" referrerpolicy="no-referrer">            
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Hugo 0.122.0">
        <title>lome+</title>
        <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/album/">
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
        <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet">
        @vite(['resources/js/app.js'])
        <!-- @vite(['resources/css/app.css']) -->
        <!-- like.js est déjà inclus dans app.js -->
    </head>
    <body>

        <div class="detail-container mt-4">
            <div class="row">
                <div class="col-md-6">
                    
                    <!-- Conteneur image + infos -->
                    <div class="image-boutons-wrapper text-center mx-auto">

                        <!-- Carrousel d'images -->
                        <div id="carouselArticle" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                            <div class="carousel-inner">
                                @php
                                    $images = [$article->photo, 
                                        $article->photo1 ?? null, 
                                        $article->photo2 ?? null, 
                                        $article->photo3 ?? null,
                                        $article->photo4 ?? null,
                                        $article->photo5 ?? null,
                                        $article->photo6 ?? null,
                                        ];
                                    $images = array_filter($images); // Supprimer les valeurs null
                                @endphp

                                @foreach ($images as $index => $image)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <!-- <img src="{{ asset('storage/' . $image) }}" class="d-block w-100 rounded" height="250" alt="Image de l'article"> -->
                                        <img src="{{ asset('storage/' . $image) }}" class="d-block custom-carousel-img rounded" alt="Image de l'article">
                                    </div>
                                @endforeach
                            </div>

                            @if (count($images) > 1)
                                <!-- Flèches de navigation -->
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselArticle" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselArticle" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                            @endif
                        </div>
                    
                    

                        <div class="prix-like">
                            <!-- Prix et bouton like -->
                            <h3 class="text-danger"><strong>{{ number_format($article->prix_ht, 0, ',', ' ') }} CFA</strong></h3>
                            <div class="actions-container"> 
                                <div class="like-container-detail">
                                    <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}">
                                        @csrf
                                        <input id="post-id-js-{{ $article->id }}" type="hidden" name="article_id" value="{{ $article->id }}">
                                        <button type="button" class="like-button-detail" data-article-id="{{ $article->id }}">
                                            <i class="bi bi-heart{{ $article->isLikeByLoggedInUser() ? '-fill' : '' }} like-icon-detail {{ $article->isLikeByLoggedInUser() ? 'liked' : '' }}"></i>
                                        </button>
                                        <div id="count-js-{{ $article->id }}" class="like-count-detail d-flex align-items-center">
                                            <span class="like-number">{{ $article->usersWhoLiked->count() }}</span>
                                            <span class="like-text ms-1">like(s)</span>
                                        </div>
                                    </form>                                                   
                                </div>
                                <!-- Bouton Partage -->
                                <div class="share-container-detail">
                                    <button onclick="openShareModal()" type="button" class="share-button-detail" >
                                        <img src="{{ asset('images/partager.png') }}" alt="Partager" class="share-icon">
                                    </button>
                                </div>
                                

                                <!-- Modale de partage -->
                                <div id="share-modal" class="share-modal">
                                    <div class="share-modal-content">
                                        <span class="close" onclick="closeShareModal()">&times;</span>
                                        <h3>Partager ce lien</h3>

                                        <input type="text" id="share-url" value="{{ Request::url() }}" readonly>
                                        <button onclick="copyToClipboard()" class="copy-btn">
                                            <i class="fas fa-copy"></i> Copier le lien
                                        </button>

                                        <div class="share-icons">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(Request::url()) }}" target="_blank" class="facebook">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                            <a href="https://wa.me/?text={{ urlencode(Request::url()) }}" target="_blank" class="whatsapp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(Request::url()) }}" target="_blank" class="twitter">
                                                <span style="font-weight: 700;">X</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>



                                <script>
                                    function openShareModal() {
                                        document.getElementById('share-modal').style.display = 'block';
                                    }

                                    function closeShareModal() {
                                        document.getElementById('share-modal').style.display = 'none';
                                    }

                                    function copyToClipboard() {
                                        const urlInput = document.getElementById("share-url");
                                        urlInput.select();
                                        urlInput.setSelectionRange(0, 99999); // Mobile support
                                        navigator.clipboard.writeText(urlInput.value).then(() => {
                                            alert("Lien copié dans le presse-papiers !");
                                        });
                                    }

                                    // Ferme la modale si on clique à l'extérieur du contenu
                                    window.onclick = function(event) {
                                        const modal = document.getElementById("share-modal");
                                        if (event.target === modal) {
                                            modal.style.display = "none";
                                        }
                                    };
                                </script>

                            </div>
                        </div>
                    </div>




                    <div class="d-flex justify-content-between boutons-detail mt-2">
                        <button class="btn btn-warning flex-fill me-2">📞Appeler</button>
                        <button class="btn btn-success flex-fill">
                            <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp" width="20"> WhatsApp
                        </button>
                    </div>
                </div>






                <div class="col-md-6">
                    <!-- Détails du produit -->
                    <h4 class="text-uppercase mt-3"><strong>{{ $article->titre }}</strong></h4>
                    <p>{{ $article->description }}</p>

<!--                  
                    <div class="comments-preview" id="commentsPreview" onclick="openModal()">

                    <div class="comments-section" id="commentsContainer">
                        <h3>Commentaires</h3>
                        @foreach ($article->comments->take(3) as $comment)
                            <div class="comment">
                                <img src="{{ $comment->user->getProfilPhotoUrl() }}" alt="Avatar de {{ $comment->user->name }}" class="comment-avatar">
                                <p><strong>{{ $comment->user->name }}</strong>:</p>
                                <p>{{ $comment->content }}</p>
                                <small>Publié {{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                        @endforeach
                        <p class="see-more">Voir tous les commentaires...</p>

                    </div>

                    </div> -->

<!-- 
                    <form id="commentForm" action="{{ route('comments.store', $article->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <textarea name="content" id="content" class="form-control" placeholder="Ajouter un commentaire..." rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Publier le commentaire</button>
                    </form> -->



                    <!-- script pour ajouter des commentaires sans rechargement de la page -->
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        $(document).ready(function() {
                            $('#commentForm').on('submit', function(e) {
                                e.preventDefault();  // Empêche le rechargement de la page

                                var content = $('#content').val();  // Récupère le contenu du commentaire
                                var articleId = {{ $article->id }}; // ID de l'article (si nécessaire, adapte-le)

                                // Envoie la requête AJAX
                                $.ajax({
                                    url: '/articles/' + articleId + '/comments',
                                    type: 'POST',
                                    data: {
                                        _token: $('meta[name="csrf-token"]').attr('content'),
                                        content: content,
                                    },
                                    success: function(response) {
                                        // Si le commentaire est ajouté avec succès, mettez à jour l'interface
                                        
                                        $('#commentsContainer').prepend('<p>' + response.comment.content + ' - ' + response.comment.user.name + '</p>');
                                        $('#content').val('');  // Réinitialiser le champ du commentaire
                                    },
                                    error: function(xhr, status, error) {
                                        // Gérer les erreurs ici (ex: afficher un message d'erreur)
                                        alert('Erreur lors de l\'ajout du commentaire.');
                                    }
                                });
                            });
                        });
                    </script>

                    <!-- style css pour l'affichage de tous les commentaires de manière brute -->
                    <style>
                            /* Style général de la section des commentaires */
                        .comments-section {
                            margin-top: 20px;
                            background: #f8f9fa;
                            padding: 15px;
                            border-radius: 10px;
                            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                        }

                        /* Style d'un commentaire */
                        .comment {
                            display: flex;
                            align-items: flex-start;
                            gap: 10px;
                            background: white;
                            padding: 10px;
                            border-radius: 8px;
                            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
                            margin-bottom: 10px;
                            opacity: 0;
                            transform: translateY(10px);
                            animation: fadeIn 0.3s ease-in-out forwards;
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                        }

                        /* Effet d'apparition pour les nouveaux commentaires */
                        @keyframes fadeIn {
                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }

                        /* Avatar utilisateur (optionnel, si tu as des images de profil) */
                        .comment img {
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            object-fit: cover;
                            border: 2px solid #007bff; /* Bordure bleue */
                        }

                        .comment-content {
                            flex: 1; /* Prend tout l'espace restant */
                        }

                        /* Style du nom de l'utilisateur */
                        .comment p strong {
                            color: #333;
                            font-size: 14px;
                            margin-bottom: 5px;
                        }

                        /* Style du texte du commentaire */
                        .comment p {
                            margin: 5px 0;
                            font-size: 14px;
                            color: #555;
                        }

                        /* Date du commentaire */
                        .comment small {
                            font-size: 12px;
                            color: #777;
                        }

                        /* Formulaire d'ajout de commentaire */
                        #commentForm {
                            margin-top: 15px;
                        }

                        #commentForm textarea {
                            width: 100%;
                            border: 1px solid #ddd;
                            padding: 8px;
                            border-radius: 5px;
                            font-size: 14px;
                            resize: none;
                        }

                        #commentForm button {
                            margin-top: 10px;
                            background: #007bff;
                            color: white;
                            border: none;
                            padding: 8px 15px;
                            border-radius: 5px;
                            cursor: pointer;
                            transition: 0.3s;
                        }

                        #commentForm button:hover {
                            background: #0056b3;
                        }

                    </style>



                    <!-- Modal des commentaires -->
                    <div id="commentsModal" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal()">&times;</span>
                            <h3>Tous les commentaires</h3>
                            <div class="comments-list">
                                @foreach ($article->comments as $comment)
                                    <div class="comment">
                                        <p><strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}</p>
                                        <small>{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- CSS pour le deuxième affichage des commentaires-->
                    <style>
                        #commentsPreview {
                            cursor: pointer;
                            padding: 10px;
                            background: #f1f1f1;
                            border-radius: 5px;
                        }
                        .see-more {
                            color: blue;
                            text-decoration: underline;
                        }
                        #commentsModal {
                            display: none;
                            position: fixed;
                            z-index: 1000;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0,0,0,0.5);
                        }
                        .modal-content {
                            background: white;
                            margin: 10% auto;
                            padding: 20px;
                            width: 50%;
                            border-radius: 5px;
                        }
                        .close {
                            float: right;
                            font-size: 28px;
                            cursor: pointer;
                        }
                        .comments-list {
                            max-height: 300px;
                            overflow-y: auto;
                        }
                    </style>

                    <!-- JavaScript pour l'ouverture du modal des commentaires-->
                    <script>
                        function openModal() {
                            document.getElementById('commentsModal').style.display = 'block';
                        }
                        function closeModal() {
                            document.getElementById('commentsModal').style.display = 'none';
                        }
                    </script>



                </div>
            </div>
        </div>



        <h3 style="text-align: center; margin-top: 50px;">Articles similaires</h3>

        <div class="album py-5 bg-body-tertiary">
            <div class="container">
                <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach($articlesParCategorie as $article)
                   
                        @if($articles->isEmpty())
                            <p class="text-center">Aucun résultat trouvé.</p>
                        @else

  
                            <div class="col">
                                <div class="card shadow-sm rounded-4 article-hover">
                                        <!-- Heure en haut à droite -->
                                        <div class="position-absolute top-0 end-0 p-1 rounded-bottom-start small d-flex heure">
                                            <i class="bi bi-clock" style="padding-right: 5px;"></i>
                                            <p class="mb-0">
                                                {!! $article->created_at->diffInYears() > 1
                                                    ? '<strong>' . floor($article->created_at->diffInYears()) . '</strong> an' . ($article->created_at->diffInYears() > 1 ? 's' : '') 
                                                    : ($article->created_at->diffInMonths() > 1
                                                        ? '<strong>' . floor($article->created_at->diffInMonths()) . '</strong> mois' 
                                                        : ($article->created_at->diffInDays() > 1 
                                                            ? '<strong>' . floor($article->created_at->diffInDays()) . '</strong> jour' . ($article->created_at->diffInDays() > 1 ? 's' : '') 
                                                            : ($article->created_at->diffInHours() > 1 
                                                                ? '<strong>' . floor($article->created_at->diffInHours()) . '</strong> heure' . ($article->created_at->diffInHours() > 1 ? 's' : '') 
                                                                : ($article->created_at->diffInMinutes() > 1 
                                                                    ? '<strong>' . floor($article->created_at->diffInMinutes()) . '</strong> minute' . ($article->created_at->diffInMinutes() > 1 ? 's' : '') 
                                                                    : ($article->created_at->diffInSeconds() > 1 
                                                                        ? '<strong>' . floor($article->created_at->diffInSeconds()) . '</strong> seconde' . ($article->created_at->diffInSeconds() > 1 ? 's' : '') 
                                                                        : ''))))) !!}
                                            </p>

                                        </div>

                                        <a href="{{ $article->url }}" style="text-decoration: none; color: inherit; border-bottom: 1px solid;">
                                            <!-- partie image de l'article -->
                                            <img class="card-img-top rounded-top-4 article-img-fixed" src="{{ asset('storage/'.$article->photo) }}" width="100%" height="150" alt="Card image cap" style="object-fit: cover;" > 
                                            <!--supprimer style="object-fit: cover;" si l'affichage de l'image n'est pas correct-->                                   
                                        </a>

                                        <div class="card-body">

                                            <!-- partie texte de l'article -->
                                            <div class="card-text">

                                            <div class="article-price">
                                                <span>{{ intval($article->prix_ht) }} FCFA</span>    
                                            </div>
                                            <div class="article-title">
                                                {{ $article->titre }}
                                            </div>

                                                <!-- Ajout de la description -->
                                                <!-- <div class="text-bas-article">
                                                    <p class="text-muted mt-2 description-article">{{ $article->description }}</p>
                                                </div> -->
                                                     <!-- Localisation et la ville -->
                                                     <!-- Localisation et la ville -->
                                                     <div class="article-localisation">
                                                        <img src="{{ asset('images/localisation.png') }}" alt="Localisation" class="localisation-icon">
                                                        <p class="localisation-text">{{ $article->lieu ?? 'Ville non spécifiée' }}</p>
                                                    </div>
                                               
                                               


                                                <!-- <div class="d-flex align-items">
                                                    <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}">
                                                        <div id="count-js-{{ $article->id }}" class="like-count">
                                                            {{ $article->usersWhoLiked->count() }} like(s)
                                                        </div>
                                                        @csrf
                                                        <input id="post-id-js-{{ $article->id }}" type="hidden" name="article_id" value="{{ $article->id }}">
                                                        <button type="submit" class="btn btn-link btn-sm">
                                                            {{ $article->isLikeByLoggedInUser() ? 'Retirer le like' : 'J\'aime' }}
                                                        </button>
                                                    </form>
                                                </div> -->


                                                <!-- système de like -->
                                                <div class="like-container d-flex align-items">
                                                    
                                                        <form action="{{ route('articles.like', ['article' => $article->id]) }}" method="POST" id="form-js-{{ $article->id }}">
                                                            @csrf
                                                            <input id="post-id-js-{{ $article->id }}" type="hidden" name="article_id" value="{{ $article->id }}">
                                                            <button type="button" class="like-button" data-article-id="{{ $article->id }}">
                                                                <i class="bi bi-heart{{ $article->isLikeByLoggedInUser() ? '-fill' : '' }} like-icon {{ $article->isLikeByLoggedInUser() ? 'liked' : '' }}"></i>
                                                            </button>
                                                            <div id="count-js-{{ $article->id }}" class="like-count d-flex align-items-center">
    <span class="like-number">{{ $article->usersWhoLiked->count() }}</span>
    <span class="like-text ms-1">like(s)</span>
</div>
                                                        </form>
                                                    
                                                </div>








                                                <!-- ligne horizontale qui sépare la photo et les autres elements  -->
                                                <hr style="border-top: 3px solid #000000;  width: 100%; margin-bottom: 5px; margin-top: 5px;">
                                                
                                                
                                                <!-- photo de profil et le nom de l'utilisateur -->
                                                @if($article->user)
                                                    <div class="d-flex align-items-center user-info">
                                                        <img src="{{ $article->user->getProfilPhotoUrl() }}" alt="Profil de {{ $article->user->name }}" class="profile-picture me-2">
                                                        <p class="text-muted user-name mb-0">{{ $article->user->name ?? 'nom non spécifiée' }}</p>
                                                    </div>
                                                @endif

                                                <!-- @if($article->user)
                                                    <div class="profile-container">
                                                        <img src="{{ $article->user->getProfilPhotoUrl() }}" alt="Profil de {{ $article->user->name }}" class="profile-picture">
                                                        <p class="profile-text mb-0">{{ $article->user->name ?? 'Nom non spécifié' }}</p>
                                                    </div>
                                                @endif -->

                                           
                                    
                            
                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <!-- certification de l'utilisateur -->
                                                    @if($article->user->estCertifie())
                                                        <div class="certification">
                                                            <img src="{{ asset('images/certifier.png') }}" alt="Certifié" class="certification-logo">
                                                            <span class="certification-text">Vérifié</span>
                                                        </div>
                                                    @endif
                                                    <!-- <small class="text-body-secondary">9 mins</small> -->
                                                </div>

                                            </div>
                                            </div>
                                    
                                    </div>
                                </div>
                                @endif
                        @endforeach
                       

       
                </div>
            </div>
        </div>

    </body>
    </html>

@endsection