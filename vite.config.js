import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { readdirSync } from 'fs';
import { resolve } from 'path';

// Detecta automáticamente todos los archivos CSS en resources/css/
const cssFiles = readdirSync(resolve(__dirname, 'resources/css'))
    .filter(file => file.endsWith('.css'))
    .map(file => `resources/css/${file}`);

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                ...cssFiles,
            ],
            refresh: true,
        }),
    ],
});