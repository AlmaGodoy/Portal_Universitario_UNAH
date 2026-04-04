import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                // Esto buscará cualquier archivo .css en la carpeta y subcarpetas
                ...import.meta.glob('resources/css/**/*.css', { eager: true, import: 'default' }),
            ],
            refresh: true,
        }),
    ],
});