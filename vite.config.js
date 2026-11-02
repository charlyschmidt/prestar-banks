import { defineConfig } from 'vite';

import laravel from 'laravel-vite-plugin';

import vue from '@vitejs/plugin-vue';

export default defineConfig({

    plugins: [

        laravel({

            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/register-pending.css',
                'resources/css/welcome.css',
                'resources/css/legal.css',
                'resources/css/footer.css',
                'resources/js/welcome.js',
                'resources/css/software-tesoreria.css',
                'resources/css/guia-de-uso.css',
                'resources/css/subscription.css',
                'resources/js/guia-de-uso.js',
            ],

            refresh: true,

        }),

        vue(),

    ],

});
