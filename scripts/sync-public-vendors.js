/**
 * Copies pinned vendor assets from node_modules into public/ for production use.
 * Run after npm install: npm run sync-vendors
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

const pairs = [
    ['node_modules/simplebar/dist/simplebar.min.js', 'public/assets/default/vendors/simplebar/simplebar.min.js'],
    ['node_modules/simplebar/dist/simplebar.min.css', 'public/assets/default/vendors/simplebar/simplebar.min.css'],
    ['node_modules/simplebar/dist/simplebar.css', 'public/assets/default/vendors/simplebar/simplebar.css'],
];

let ok = true;
for (const [relSrc, relDest] of pairs) {
    const from = path.join(root, relSrc);
    const to = path.join(root, relDest);
    if (!fs.existsSync(from)) {
        console.error('Missing source (run npm install):', relSrc);
        ok = false;
        continue;
    }
    fs.mkdirSync(path.dirname(to), { recursive: true });
    fs.copyFileSync(from, to);
    console.log('Synced', relDest);
}

if (!ok) {
    process.exit(1);
}
