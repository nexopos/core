import { defineConfig, loadEnv } from 'vite';

import { fileURLToPath } from 'node:url';
import laravel from 'laravel-vite-plugin';
import path, { resolve } from 'node:path';
import vuePlugin from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const Vue = fileURLToPath(
	new URL(
		'vue',
		import.meta.url
	)
);

export default ({ mode }) => {
    process.env = {...process.env, ...loadEnv(mode, process.cwd())};

    return defineConfig({
        base: './',
        server: {
            port: 3333,
            host: '127.0.0.1',
            cors: true,
            hmr: {
                protocol: 'wss',
                host: 'localhost',
            },
            https: true,
        },
        plugins: [
            tailwindcss(),
            vuePlugin(),
            laravel({
                input: [
                    'resources/ts/bootstrap.ts',
                    'resources/ts/app.ts',
                    'resources/ts/auth.ts',
                    'resources/ts/setup.ts',
                    'resources/ts/update.ts',
                    'resources/ts/dev.ts',
                    'resources/ts/lang-loader.ts',
                    'resources/ts/popups.ts',
                    'resources/ts/widgets.ts',
                    'resources/ts/wizard.ts',
                    // 'resources/css/app.css',
                    'resources/css/grid.css',
                    'resources/css/animations.css',
                    'resources/css/fonts.css',
                    'resources/scss/line-awesome/1.3.0/scss/line-awesome.scss',
                    'resources/css/light.css',
                    'resources/css/dark.css',
                    'resources/css/phosphor.css',
                ],
                refresh: [ 
                    'resources/**', 
                ]
            })
        ],
        resolve: {
            alias: [
                {
                    find: '&',
                    replacement: resolve(__dirname, 'resources'),
                }, {
                    find: '~',
                    replacement: resolve(__dirname, 'resources/ts'),
                },
            ]
        },
        build: {
            outDir: 'public/build',
            manifest: true,
            // rollupOptions: {
            //     input: [
            //         'resources/ts/bootstrap.ts',
            //         'resources/ts/app.ts',
            //         'resources/ts/auth.ts',
            //         'resources/ts/setup.ts',
            //         'resources/ts/update.ts',
            //         'resources/ts/dev.ts',
            //         'resources/ts/lang-loader.ts',
            //         'resources/ts/popups.ts',
            //         'resources/ts/widgets.ts',
            //         'resources/ts/wizard.ts',
            //         'resources/css/app.css',
            //         'resources/css/grid.css',
            //         'resources/css/animations.css',
            //         'resources/css/fonts.css',
            //         'resources/scss/line-awesome/1.3.0/scss/line-awesome.scss',
            //         'resources/css/light.css',
            //         'resources/css/dark.css',
            //         'resources/css/phosphor.css',
            //     ],
            // }
        }        
    });
}