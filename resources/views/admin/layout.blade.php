<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Administration') - Site Lome Plus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <!-- Page Loader -->
    @include('components.page-loader')
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-crown"></i> Administration</h4>
                <p>Site Lome Plus</p>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.articles.index') }}">
                        <i class="fas fa-newspaper"></i>
                        <span>Articles</span>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('admin.souscategories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.souscategories.index') }}">
                        <i class="fas fa-tag"></i>
                        <span>Sous-catégories</span>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.index') }}">
                        <i class="fas fa-flag"></i>
                        <span>Signalements</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.messages.compose') }}">
                        <i class="fas fa-envelope"></i>
                        <span>Messages (envoi)</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('admin.publicites.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.publicites.index') }}">
                        <i class="fas fa-ad"></i>
                        <span>Publicités</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="{{ route('articles.index') }}" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Voir le site</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <header class="top-navbar">
                <div class="navbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="page-title">@yield('page-title', 'Administration')</h2>
                </div>
                
                <div class="navbar-right">
                    <div class="user-menu">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                {{ auth()->user()->name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user"></i> Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show success-toast" role="alert" style="animation: slideDown 0.5s ease-out;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2" style="font-size: 1.2rem;"></i>
                            <strong>{{ session('success') }}</strong>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show error-toast" role="alert" style="animation: slideDown 0.5s ease-out;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2" style="font-size: 1.2rem;"></i>
                            <strong>{{ session('error') }}</strong>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const wrapper = document.querySelector('.admin-wrapper');
            const sidebar = document.querySelector('.sidebar');
            
            if (window.innerWidth <= 768) {
                // Mobile: toggle sidebar overlay
                sidebar.classList.toggle('show');
            } else {
                // Desktop: toggle collapsed state
                wrapper.classList.toggle('sidebar-collapsed');
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('show') && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-hide alerts avec animation
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 500);
            });
        }, 5000);

        // Animation slideDown pour les toasts
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .success-toast {
                box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
                border-left: 4px solid #28a745;
            }
            .error-toast {
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
                border-left: 4px solid #dc3545;
            }
            .alert {
                margin-bottom: 1.5rem;
                border-radius: 8px;
            }
        `;
        document.head.appendChild(style);

        // Mobile-friendly table actions
        document.addEventListener('DOMContentLoaded', function() {
            // Add touch-friendly classes to action buttons
            const actionButtons = document.querySelectorAll('.btn-action');
            actionButtons.forEach(function(btn) {
                btn.style.minHeight = '44px'; // iOS recommended touch target
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
