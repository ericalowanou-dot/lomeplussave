import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
              'resources/css/app.css', 
              'resources/js/app.js',
              'resources/js/like.js',
            ],
            refresh: [
                'resources/views/**',
                'app/**',
                'routes/**',
            ],
        }),
    ],
    // Configuration serveur de développement optimisée pour Windows
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            port: 5173,
            protocol: 'ws',
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
        cors: true,
    },
    // Optimisations pour le développement
    optimizeDeps: {
        include: ['alpinejs', 'axios'],
    },
});
