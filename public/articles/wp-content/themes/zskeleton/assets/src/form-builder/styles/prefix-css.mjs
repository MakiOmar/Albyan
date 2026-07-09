import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const file = path.join( __dirname, 'form-builder.css' );
let c = fs.readFileSync( file, 'utf8' );

if ( c.includes( '#zs-form-kit-app .zs-fb-tabs' ) ) {
	process.exit( 0 );
}

c = c.replace( /^\.zs-form-kit-app \{/m, '#zs-form-kit-app.zs-form-kit-app {' );
c = c.replace( /^\.zs-form-kit-app \[/m, '#zs-form-kit-app [' );
c = c.replace( /\.zs-fb-/g, '#zs-form-kit-app .zs-fb-' );
c = c.replace( /\.zs-form-builder__/g, '#zs-form-kit-app .zs-form-builder__' );
c = c.replace( /^\.root-field-handle/gm, '#zs-form-kit-app .root-field-handle' );
c = c.replace( /^\.row-handle/gm, '#zs-form-kit-app .row-handle' );

const header = [
	'/* Scoped under #zs-form-kit-app to beat wp-admin specificity */',
	'#zs-form-kit-app:not(.is-ready){visibility:hidden;min-height:280px}',
	'#zs-form-kit-app.is-ready{visibility:visible}',
	'#zs-form-kit-app button.zs-fb-tab,#zs-form-kit-app button.zs-fb-btn,#zs-form-kit-app button.zs-fb-palette-btn,#zs-form-kit-app button.zs-fb-icon-btn{appearance:none;-webkit-appearance:none;line-height:inherit;text-shadow:none;box-shadow:none}',
	'',
].join( '\n' );

fs.writeFileSync( file, header + c );
