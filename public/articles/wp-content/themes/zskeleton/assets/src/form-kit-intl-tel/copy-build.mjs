import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const themeDir = path.resolve( __dirname, '../../..' );
const distDir = path.resolve( themeDir, 'assets/dist/form-kit-intl-tel' );
const vendorDir = path.resolve( themeDir, 'assets/vendor/intl-tel-input' );
const nodePkg = path.resolve( themeDir, 'node_modules/intl-tel-input/dist' );

function copyFile( from, to ) {
	fs.mkdirSync( path.dirname( to ), { recursive: true } );
	fs.copyFileSync( from, to );
	console.log( 'Copied', path.relative( themeDir, from ), '->', path.relative( themeDir, to ) );
}

function copyDir( from, to ) {
	fs.mkdirSync( to, { recursive: true } );
	for ( const entry of fs.readdirSync( from, { withFileTypes: true } ) ) {
		const src = path.join( from, entry.name );
		const dest = path.join( to, entry.name );
		if ( entry.isDirectory() ) {
			copyDir( src, dest );
		} else {
			copyFile( src, dest );
		}
	}
}

const jsFrom = path.join( distDir, 'form-kit-intl-tel.js' );
const jsTo = path.join( themeDir, 'assets/js/form-kit-intl-tel.js' );
if ( ! fs.existsSync( jsFrom ) ) {
	console.error( 'Build output missing:', jsFrom );
	process.exit( 1 );
}
copyFile( jsFrom, jsTo );

copyFile( path.join( nodePkg, 'css/intlTelInput.css' ), path.join( vendorDir, 'css/intlTelInput.css' ) );
copyDir( path.join( nodePkg, 'img' ), path.join( vendorDir, 'img' ) );
