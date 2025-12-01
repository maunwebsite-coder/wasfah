import path from 'node:path';
import { createRequire } from 'node:module';
import { constants as zlibConstants } from 'node:zlib';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const require = createRequire(path.join(process.cwd(), 'package.json'));
let viteCompression;

try {
    ({ default: viteCompression } = require('vite-plugin-compression'));
} catch (error) {
    console.warn('vite-plugin-compression not available, skipping asset compression.', error.message);
}

const buildCompressionPlugins = () => {
    if (!viteCompression) {
        return [];
    }

    return [
        viteCompression({
            algorithm: 'brotliCompress',
            ext: '.br',
            include: [/\.(js|css|mjs|json|html|svg)$/i],
            compressionOptions: {
                params: {
                    [zlibConstants.BROTLI_PARAM_MODE]: zlibConstants.BROTLI_MODE_TEXT,
                    [zlibConstants.BROTLI_PARAM_QUALITY]: 11,
                },
            },
        }),
        viteCompression({
            algorithm: 'gzip',
            ext: '.gz',
            include: [/\.(js|css|mjs|json|html|svg)$/i],
            compressionOptions: { level: zlibConstants.Z_BEST_COMPRESSION },
        }),
    ];
};

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/non-critical.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        ...buildCompressionPlugins(),
    ],
    build: {
        // Ensure Laravel can resolve assets without relying on the dev server
        manifest: 'manifest.json',
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
                manualChunks: {
                    'vendor-realtime': ['laravel-echo', 'pusher-js'],
                },
            },
        },
    },
});
