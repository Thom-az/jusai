import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Global
                'resources/css/app.css',
                'resources/js/app.js',

                // CSS — client modules
                'resources/css/modules/dashboard.css',
                'resources/css/modules/casos.css',
                'resources/css/modules/documentos.css',
                'resources/css/modules/revisor.css',
                'resources/css/modules/minutas.css',
                'resources/css/modules/configuracoes.css',
                'resources/css/modules/chamados.css',

                // CSS — admin modules
                'resources/css/modules/admin/dashboard.css',
                'resources/css/modules/admin/organizations.css',
                'resources/css/modules/admin/finance.css',
                'resources/css/modules/admin/support.css',
                'resources/css/modules/admin/leads.css',

                // JS — client modules
                'resources/js/modules/casos-show.js',
                'resources/js/modules/documentos-create.js',
                'resources/js/modules/revisor-index.js',
                'resources/js/modules/revisor-show.js',
                'resources/js/modules/chamados-index.js',

                // JS — configuracoes Alpine components (global registration)
                'resources/js/modules/configuracoes-alpine.js',

                // JS — admin modules
                'resources/js/modules/admin/dashboard.js',
                'resources/js/modules/admin/organizations.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
