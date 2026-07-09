import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const distDir = path.resolve( __dirname, '../../dist/form-builder' );

export default defineConfig( {
	plugins: [ tailwindcss(), vue() ],
	root: __dirname,
	build: {
		outDir: distDir,
		emptyOutDir: true,
		rollupOptions: {
			input: path.resolve( __dirname, 'main.js' ),
			output: {
				entryFileNames: 'form-builder-admin.js',
				assetFileNames: ( assetInfo ) => {
					if ( assetInfo.name && assetInfo.name.endsWith( '.css' ) ) {
						return 'form-builder-admin.css';
					}
					return '[name][extname]';
				},
				format: 'iife',
				name: 'ZsFormBuilder',
				inlineDynamicImports: true,
			},
		},
		cssCodeSplit: false,
	},
	define: {
		'process.env.NODE_ENV': JSON.stringify( 'production' ),
	},
} );
