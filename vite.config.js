import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { glob } from 'glob';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                ...glob.sync('resources/css/*.css'),
            ],
            refresh: true,
        }),
    ],
});