import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { readdirSync } from 'fs';
import { resolve } from 'path';

const cssFiles = readdirSync(resolve(__dirname, 'resources/css'))
    .filter(file => file.endsWith('.css'))
    .map(file => `resources/css/${file}`);

const jsFiles = readdirSync(resolve(__dirname, 'resources/js'))
    .filter(file => file.endsWith('.js'))
    .map(file => `resources/js/${file}`);

export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...jsFiles,
                ...cssFiles,
            ],
            refresh: true,
        }),
    ],
});