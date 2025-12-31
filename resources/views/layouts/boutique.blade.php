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
    {{-- Bootstrap ou ton CSS principal --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background: #f6f7fb;
        }
        .boutique-header {
            background: linear-gradient(90deg, #ff9800 0%, #ffb74d 100%);
            color: #fff;
            padding: 40px 0 20px 0;
            text-align: center;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            margin-bottom: 30px;
        }
        .boutique-header img {
            border-radius: 50%;
            border: 4px solid #fff;
            width: 90px;
            height: 90px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .boutique-sidebar {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px #eee;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>

     <!-- pour le icons boostrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        
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
    <!-- Police professionnelle uniforme -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    @yield('head')
</head>
<body>
    <!-- Page Loader -->
    @include('components.page-loader')
    <div>
        @include('includes.header')
    </div>
    <div class="boutique-header" style="margin-top: 45px;">
        @yield('boutique-header')
    </div>
    <div class="container">
        <div class="row">
            {{-- Sidebar à gauche --}}
            <div class="col-md-3">
                @yield('boutique-sidebar')
            </div>
            {{-- Contenu principal --}}
            <div class="col-md-9">
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