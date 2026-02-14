<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    <a href="{{ route('admin.messages.inbox') }}">
                        <i class="fas fa-inbox"></i>
                        <span>Messages reçus</span>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.messages.compose') ? 'active' : '' }}">
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
                    <!-- Notifications -->
                    <div class="notification-menu me-3" style="overflow: visible;">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary position-relative overflow-visible" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge badge bg-danger" id="notificationBadge" style="display: none;" aria-hidden="true">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 350px; max-height: 500px; overflow-y: auto;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-bell me-2"></i>Notifications</span>
                                    <button class="btn btn-sm btn-link text-decoration-none p-0" id="markAllReadBtn" style="display: none;">
                                        <small>Tout marquer comme lu</small>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div id="notificationsList" class="px-3 py-2 text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
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
    
    <!-- Script global pour cacher les loaders au chargement de la page -->
    <script>
        // Cacher tous les loaders au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Cacher tous les overlays de chargement
            const loaders = document.querySelectorAll('#loadingOverlay, .loading-overlay, [id*="loading"]');
            loaders.forEach(function(loader) {
                if (loader) {
                    loader.style.display = 'none';
                }
            });
            
            // Réactiver tous les boutons désactivés
            const disabledButtons = document.querySelectorAll('button:disabled, .btn:disabled');
            disabledButtons.forEach(function(btn) {
                // Ne réactiver que si ce n'est pas intentionnellement désactivé
                if (btn.hasAttribute('data-keep-disabled')) {
                    return;
                }
            });
        });
        
        // Cacher les loaders lors de la navigation
        window.addEventListener('pageshow', function(event) {
            const loaders = document.querySelectorAll('#loadingOverlay, .loading-overlay, [id*="loading"]');
            loaders.forEach(function(loader) {
                if (loader) {
                    loader.style.display = 'none';
                }
            });
        });
    </script>
    
    <!-- Audio pour les notifications (généré via Web Audio API) -->

    <!-- Scripts pour les notifications et rafraîchissement automatique -->
    <script>
        (function() {
            const NOTIFICATION_CHECK_INTERVAL = 5000; // 5 secondes
            const DATA_REFRESH_INTERVAL = 30000; // 30 secondes
            let lastNotificationCount = 0;
            let notificationCheckInterval;
            let dataRefreshInterval;
            let soundEnabled = true;

            // Fonction pour jouer le son de notification (douce mélodie)
            function playNotificationSound() {
                if (soundEnabled) {
                    try {
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        
                        // Créer un son doux et agréable (fréquence de 800Hz avec une enveloppe douce)
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();
                        
                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);
                        
                        // Configuration du son doux
                        oscillator.frequency.value = 800; // Fréquence douce
                        oscillator.type = 'sine'; // Onde sinusoïdale (la plus douce)
                        
                        // Enveloppe ADSR pour un son doux
                        const now = audioContext.currentTime;
                        gainNode.gain.setValueAtTime(0, now);
                        gainNode.gain.linearRampToValueAtTime(0.3, now + 0.01); // Attaque rapide mais douce
                        gainNode.gain.exponentialRampToValueAtTime(0.1, now + 0.1); // Décroissance
                        gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3); // Fin douce
                        
                        oscillator.start(now);
                        oscillator.stop(now + 0.3);
                    } catch (e) {
                        console.log('Impossible de jouer le son:', e);
                    }
                }
            }

            // Fonction pour formater la date
            function formatDate(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diff = now - date;
                const seconds = Math.floor(diff / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);
                const days = Math.floor(hours / 24);

                if (seconds < 60) return 'Il y a quelques secondes';
                if (minutes < 60) return `Il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
                if (hours < 24) return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
                if (days < 7) return `Il y a ${days} jour${days > 1 ? 's' : ''}`;
                return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            }

            // Fonction pour obtenir l'icône selon le type
            function getIcon(type) {
                const icons = {
                    'user_registered': 'fa-user-plus',
                    'article_pending': 'fa-clock',
                    'problem_report': 'fa-flag',
                };
                return icons[type] || 'fa-bell';
            }

            // Fonction pour obtenir la couleur selon le type
            function getColor(type) {
                const colors = {
                    'user_registered': 'success',
                    'article_pending': 'warning',
                    'problem_report': 'danger',
                };
                return colors[type] || 'primary';
            }

            // Fonction pour charger les notifications
            async function loadNotifications() {
                try {
                    const response = await fetch('{{ route("admin.api.notifications.unread") }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) throw new Error('Erreur réseau');

                    const data = await response.json();
                    const unreadCount = data.unread_count || 0;
                    const notifications = data.notifications || [];

                    // Mettre à jour le badge
                    const badge = document.getElementById('notificationBadge');
                    const markAllBtn = document.getElementById('markAllReadBtn');
                    
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                        badge.style.display = 'inline-flex';
                        markAllBtn.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                        markAllBtn.style.display = 'none';
                    }

                    // Jouer le son si nouvelle notification
                    if (unreadCount > lastNotificationCount && lastNotificationCount > 0) {
                        playNotificationSound();
                    }
                    lastNotificationCount = unreadCount;

                    // Mettre à jour la liste
                    const listContainer = document.getElementById('notificationsList');
                    if (notifications.length === 0) {
                        listContainer.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x mb-2"></i><p class="mb-0">Aucune notification</p></div>';
                    } else {
                        listContainer.innerHTML = notifications.map(notif => `
                            <div class="notification-item ${!notif.is_read ? 'unread' : ''} p-3 border-bottom" data-id="${notif.id}">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas ${getIcon(notif.type)} text-${getColor(notif.type)}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold mb-1">${notif.title}</div>
                                        <div class="small text-muted mb-2">${notif.message}</div>
                                        <div class="small text-muted">${formatDate(notif.created_at)}</div>
                                    </div>
                                </div>
                            </div>
                        `).join('');

                        // Ajouter les événements de clic
                        listContainer.querySelectorAll('.notification-item').forEach(item => {
                            item.style.cursor = 'pointer';
                            item.addEventListener('click', function() {
                                const notifId = this.dataset.id;
                                markAsRead(notifId);
                                if (notifications.find(n => n.id == notifId)?.url) {
                                    window.location.href = notifications.find(n => n.id == notifId).url;
                                }
                            });
                        });
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement des notifications:', error);
                }
            }

            // Fonction pour marquer une notification comme lue
            async function markAsRead(notificationId) {
                try {
                    const response = await fetch(`{{ route("admin.api.notifications.read", ":id") }}`.replace(':id', notificationId), {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        credentials: 'same-origin'
                    });

                    if (response.ok) {
                        const data = await response.json();
                        const badge = document.getElementById('notificationBadge');
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        if (data.unread_count === 0) {
                            badge.style.display = 'none';
                            var markAllBtn = document.getElementById('markAllReadBtn');
                            if (markAllBtn) markAllBtn.style.display = 'none';
                        }
                        // Marquer visuellement comme lu
                        const item = document.querySelector(`[data-id="${notificationId}"]`);
                        if (item) {
                            item.classList.remove('unread');
                        }
                    }
                } catch (error) {
                    console.error('Erreur lors du marquage comme lu:', error);
                }
            }

            // Fonction pour marquer toutes comme lues
            async function markAllAsRead() {
                try {
                    const response = await fetch('{{ route("admin.api.notifications.read-all") }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        credentials: 'same-origin'
                    });

                    if (response.ok) {
                        loadNotifications();
                    }
                } catch (error) {
                    console.error('Erreur lors du marquage de toutes comme lues:', error);
                }
            }

            // Fonction pour rafraîchir les données du dashboard
            async function refreshDashboardData() {
                // Seulement si on est sur le dashboard
                if (window.location.pathname.includes('/admin') && (window.location.pathname === '/admin' || window.location.pathname === '/admin/')) {
                    try {
                        // Rafraîchir les statistiques via AJAX si nécessaire
                        // Pour l'instant, on recharge juste les notifications
                        // Vous pouvez étendre cela pour rafraîchir d'autres données
                    } catch (error) {
                        console.error('Erreur lors du rafraîchissement:', error);
                    }
                }
            }

            // Initialisation
            document.addEventListener('DOMContentLoaded', function() {
                // Charger les notifications au démarrage
                loadNotifications();

                // Vérifier les nouvelles notifications toutes les 5 secondes
                notificationCheckInterval = setInterval(loadNotifications, NOTIFICATION_CHECK_INTERVAL);

                // Rafraîchir les données toutes les 30 secondes
                dataRefreshInterval = setInterval(refreshDashboardData, DATA_REFRESH_INTERVAL);

                // Bouton "Tout marquer comme lu"
                const markAllBtn = document.getElementById('markAllReadBtn');
                if (markAllBtn) {
                    markAllBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        markAllAsRead();
                    });
                }

                // Toggle son (clic droit sur le badge pour activer/désactiver)
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    badge.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                        soundEnabled = !soundEnabled;
                        badge.title = soundEnabled ? 'Son activé (clic droit pour désactiver)' : 'Son désactivé (clic droit pour activer)';
                    });
                }
            });

            // Nettoyer les intervalles quand on quitte la page
            window.addEventListener('beforeunload', function() {
                if (notificationCheckInterval) clearInterval(notificationCheckInterval);
                if (dataRefreshInterval) clearInterval(dataRefreshInterval);
            });
        })();
    </script>

    <style>
        .notification-item {
            transition: background-color 0.2s;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-item.unread {
            background-color: #e7f3ff;
            border-left: 3px solid #0d6efd !important;
        }
        .notification-dropdown {
            max-width: 400px;
        }
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.35em;
            font-size: 0.7rem;
            font-weight: 600;
            line-height: 1.25rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            white-space: nowrap;
        }
        .notification-menu .dropdown .btn {
            overflow: visible;
        }
    </style>
    
    @stack('scripts')
</body>
</html>
