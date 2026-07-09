import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const themeDir = path.resolve( __dirname, '../../..' );
const distDir = path.resolve( themeDir, 'assets/dist/form-builder' );

const copies = [
	{
		from: path.join( distDir, 'form-builder-admin.js' ),
		to: path.join( themeDir, 'assets/js/form-builder-admin.js' ),
	},
	{
		from: path.join( distDir, 'form-builder-admin.css' ),
		to: path.join( themeDir, 'assets/css/form-builder-admin.css' ),
	},
];

for ( const { from, to } of copies ) {
	if ( ! fs.existsSync( from ) ) {
		console.error( 'Build output missing:', from );
		process.exit( 1 );
	}
	fs.mkdirSync( path.dirname( to ), { recursive: true } );
	fs.copyFileSync( from, to );
	console.log( 'Copied', path.relative( themeDir, from ), '->', path.relative( themeDir, to ) );
}
