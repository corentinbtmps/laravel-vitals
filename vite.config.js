import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    // Emit relative asset URLs in CSS (url(./foo.woff2)) so they resolve
    // alongside dashboard.css regardless of where it is served from
    // (the package serves it via /vitals/_assets/{file}).
    base: './',
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        cssCodeSplit: false,
        manifest: false,
        rollupOptions: {
            input: {
                dashboard: resolve(__dirname, 'resources/js/dashboard.js'),
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name === 'style.css' || (assetInfo.name && assetInfo.name.endsWith('.css'))) {
                        return 'dashboard.css';
                    }
                    return '[name].[ext]';
                },
            },
        },
    },
});
