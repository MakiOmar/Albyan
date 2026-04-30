#!/usr/bin/env node

const chokidar = require('chokidar');
const { execSync } = require('child_process');
const path = require('path');

console.log('👀 Starting file watcher for ZSkeleton Theme...\n');

// Watch CSS files
const cssWatcher = chokidar.watch('style.css', {
    ignored: /(^|[\/\\])\../, // ignore dotfiles
    persistent: true
});

cssWatcher.on('change', (filePath) => {
    console.log(`📝 CSS file changed: ${filePath}`);
    try {
        execSync('npx cleancss -o assets/css/style.min.css style.css', { stdio: 'pipe' });
        console.log('✅ CSS minified successfully');
    } catch (error) {
        console.error('❌ CSS minification failed:', error.message);
    }
});

// Watch JavaScript files
const jsWatcher = chokidar.watch('assets/js/main.js', {
    ignored: /(^|[\/\\])\../, // ignore dotfiles
    persistent: true
});

jsWatcher.on('change', (filePath) => {
    console.log(`📝 JS file changed: ${filePath}`);
    try {
        execSync('npx terser assets/js/main.js -o assets/js/main.min.js -c -m', { stdio: 'pipe' });
        console.log('✅ JavaScript minified successfully');
    } catch (error) {
        console.error('❌ JavaScript minification failed:', error.message);
    }
});

console.log('👀 Watching for changes...');
console.log('   - style.css → assets/css/style.min.css');
console.log('   - assets/js/main.js → assets/js/main.min.js');
console.log('\nPress Ctrl+C to stop watching.\n');
