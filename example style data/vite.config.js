import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/override.css',
                'resources/css/shipment-table-shared.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    base: '/',
    server: {
        hmr: {
            port: 5173,
        },
        host: true,
        port: 5173,
        strictPort: false,
    },
});
