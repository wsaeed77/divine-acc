import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const appUrl = env.APP_URL;

    // Laravel Vite plugin defaults allow localhost / *.test / APP_URL — not *.local (common with MAMP).
    // Without this, http://something.local cannot load scripts from the dev server → blank Inertia pages.
    const corsOrigins = [
        /^https?:\/\/(?:(?:[^:]+\.)?localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
        ...(appUrl ? [appUrl] : []),
        /^https?:\/\/.*\.test(?::\d+)?$/,
        /^https?:\/\/.*\.local(?::\d+)?$/,
    ];

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),
            react(),
        ],
        server: {
            cors: {
                origin: corsOrigins,
            },
        },
    };
});
