import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/css/admin.css',
                'resources/css/public.css',
                'resources/js/app.js',
                'resources/js/public.js',
                'resources/js/pages/home.js',
                'resources/js/modules/events-page.js',
                'resources/js/pages/admin-dashboard.js',
                'resources/js/admin/analytics-charts.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
