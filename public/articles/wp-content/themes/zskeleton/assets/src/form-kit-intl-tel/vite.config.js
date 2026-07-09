import { defineConfig } from 'vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const distDir = path.resolve( __dirname, '../../dist/form-kit-intl-tel' );

export default defineConfig( {
	root: __dirname,
	build: {
		outDir: distDir,
		emptyOutDir: true,
		lib: {
			entry: path.resolve( __dirname, 'main.js' ),
			name: 'ZskeletonIntlTel',
			formats: [ 'iife' ],
			fileName: () => 'form-kit-intl-tel.js',
		},
		rollupOptions: {
			output: {
				inlineDynamicImports: true,
			},
		},
	},
} );
