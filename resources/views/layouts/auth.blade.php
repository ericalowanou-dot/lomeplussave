<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo_icon.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_icon.jpg') }}">
    
    <!-- pour les icons bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Fonts - Police professionnelle uniforme -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- css pour publier une annonce -->
    <link href="{{ asset('css/annonce-create.css') }}" rel="stylesheet">
    
    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-left: 3px solid rgb(255, 119, 0);
            border-right: 3px solid rgb(255, 119, 0);
        
        }

        .auth-container {
            width: 100%;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            
        }

        .retour-accueil{
            position: absolute;
            top: 5px;
            left: 5px;
            background-color:rgb(238, 238, 238); 
            border: 1px dotted rgb(183, 183, 183);
            border-radius: 5px;
            text-decoration: none;
            margin: 3px 3px;
            padding: 3px 3px;
            font-size: 12px;
            box-shadow: 0 2px 4px rgb(207, 207, 207);
        }

        .retour-accueil:hover{
            background-color: rgb(255, 111, 0);
            color: rgb(0, 0, 0);
            text-decoration: none;
        }

        .auth-card {
            position: relative;
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            z-index: 0;
        }

        /* Ajoute un calque avec l'image floutée en arrière-plan */
        .auth-card::before {
            content: "";
            position: absolute;
            inset: 0;
            /* background-image: url('{{ asset('images/background.png') }}'); */
            /* background-image: url('https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1350&q=80'); */
            background-size: cover;
            background-position: center;
            filter: blur(15px);
            z-index: 0;
        }

        /* Le contenu reste lisible par-dessus */
        .auth-card > * {
            position: relative;
            z-index: 1;
        }
        .auth-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
        }

        .grouped-form{
            background-color:rgb(233, 230, 230);
            padding: 20px;
            margin: 60px 0;
            border-radius: 10px;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
        }


        .form-control:focus {
            border-color: #f57c00;
            outline: none;
            box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #f57c00;
            color: white;
            border: 1px solid #d7d7d7ff;
            box-shadow:4px 4px 6px rgba(0,0,0,0.5) !important;
        }

        .btn-primary:hover {
            background: #e65100;
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid #eee;
        }

        .auth-footer a {
            color: #f57c00;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        @media (max-width: 480px) {
            .auth-card {
                border-radius: 0;
            }
            
            .auth-container {
                padding: 0;
            }
        }
    </style>

    @stack('styles')

    <!-- IntlTelInput CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" rel="stylesheet" />

    <!-- Styles personnalisés pour les champs de téléphone -->
    <style>
        .iti {
            width: 100%;
            margin-bottom: 20px;
        }
        .iti__flag {
            background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/img/flags.png");
        }
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {
                background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/img/flags@2x.png");
            }
        }
        .phone-field-container {
            position: relative;
            margin-bottom: 20px;
        }
        .phone-input {
            padding-left: 120px !important; /* espace entre le drapeau et le numéro */
            height: 48px !important;
            font-size: 16px !important;
            border: 2px solid #eee !important;
            border-radius: 8px !important;
        }
        .phone-validation-message {
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>



</head>
<body>
    <!-- Page Loader -->
    @include('components.page-loader')
    <div class="auth-container">
        <div class="auth-card">
            

            <!-- Bouton retour à l'accueil -->
            <a href="{{ route('articles.index') }}" class="retour-accueil">
                <i class="bi bi-house-door"></i> Accueil
            </a>
            
            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <div class="auth-footer">
                @yield('footer')
            </div>
        </div>
    </div>
    <!-- Scripts IntlTelInput -->
     @yield('scripts')
     @stack('scripts')

</body>
</html>