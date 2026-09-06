{{-- filepath: resources/views/layouts/boutique.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Boutique')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/true-logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/true-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/true-logo.png') }}">

    <!-- PWA : manifest, theme, icônes app -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ff7b00">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Lome+">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/icons/pwa/apple-touch-icon-180.png') }}">
    @vite(['resources/js/pwa.js'])

    {{-- Bootstrap ou ton CSS principal --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background: #f6f7fb;
        }
        .boutique-header {
            background: #fff;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
        }
        
        .boutique-header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .boutique-header .profile-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #FF9900;
            flex-shrink: 0;
        }
        
        .boutique-header .profile-info {
            flex: 1;
            min-width: 0;
        }
        
        .boutique-header .profile-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 0.25rem 0;
            line-height: 1.3;
        }
        
        .boutique-header .profile-email {
            font-size: 0.875rem;
            color: #666;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .boutique-header .profile-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }
        
        .boutique-header .meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8125rem;
            color: #888;
        }
        
        .boutique-header .meta-item i {
            font-size: 0.875rem;
            color: #FF9900;
        }
        
        .boutique-header .certification-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .boutique-header {
                padding: 1rem;
                margin-bottom: 1rem;
                border-radius: 8px;
            }
            
            .boutique-header-content {
                gap: 0.75rem;
            }
            
            .boutique-header .profile-avatar {
                width: 56px;
                height: 56px;
                border-width: 2px;
            }
            
            .boutique-header .profile-name {
                font-size: 1.125rem;
            }
            
            .boutique-header .profile-email {
                font-size: 0.8125rem;
            }
            
            .boutique-header .profile-meta {
                gap: 0.75rem;
                margin-top: 0.375rem;
            }
            
            .boutique-header .meta-item {
                font-size: 0.75rem;
            }
        }
        .boutique-sidebar {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }

        /* Modal menu compte : plein écran au-dessus du contenu boutique */
        #accountModal.modal {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 10050 !important;
        }
    </style>

     <!-- pour le icons boostrap -->
        <link href="{{ asset('css/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
        
        <!-- font awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <link rel="stylesheet" href="{{asset('css/articles.css')}}">
        <link rel="stylesheet" href="{{asset('css/style.css')}}"> 

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


        
            @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/articles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/article-horizontale.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('css/background-articles.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('css/detail_article.css') }}">
    <link rel="stylesheet" href="{{ asset('css/content-width.css') }}">
    <!-- Police professionnelle uniforme -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    @yield('head')
</head>
<body>
    <!-- Page Loader -->
    @include('components.page-loader')
    @include('includes.header')
    <div class="container">
        <div class="boutique-header" style="margin-top: 4.5rem;">
            @yield('boutique-header')
        </div>
    </div>
    <div class="container">
        <div class="row">
            {{-- Sidebar à gauche --}}
            <div class="col-12 col-md-3 mb-3 mb-md-0">
                @yield('boutique-sidebar')
            </div>
            {{-- Contenu principal --}}
            <div class="col-12 col-md-9">
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Inclusion du pied de page -->
    @include('includes.footer')
    
    @yield('scripts')
        <script>
    function shareShop() {
        const url = window.location.href;
        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: url
            });
        } else {
            navigator.clipboard.writeText(url);
            alert('Lien de la boutique copié !');
        }
    }
    </script>
</body>
</html>