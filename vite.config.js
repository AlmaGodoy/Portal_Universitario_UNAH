import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

const getCssFiles = (dir) => {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach((file) => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(getCssFiles(file));
        } else if (file.endsWith('.css')) {
            results.push(file.replace(/\\/g, '/')); 
        }
    });
    return results;
};

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                ...getCssFiles('resources/css'),
            ],
            refresh: true,
        }),
    ],
});