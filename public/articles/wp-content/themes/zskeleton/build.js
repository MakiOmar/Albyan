#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('🔨 Starting ZSkeleton Theme Build Process...\n');

// Create assets directories if they don't exist
const cssDir = path.join(__dirname, 'assets', 'css');
const jsDir = path.join(__dirname, 'assets', 'js');

if (!fs.existsSync(cssDir)) {
    fs.mkdirSync(cssDir, { recursive: true });
}

if (!fs.existsSync(jsDir)) {
    fs.mkdirSync(jsDir, { recursive: true });
}

try {
    // Minify all CSS files
    console.log('📝 Minifying CSS files...');
    execSync('npm run build:css', { stdio: 'inherit' });
    console.log('✅ All CSS files minified successfully\n');

    // Minify all JavaScript files
    console.log('📝 Minifying JavaScript files...');
    execSync('npm run build:js', { stdio: 'inherit' });
    console.log('✅ All JavaScript files minified successfully\n');

    // Calculate total savings
    const cssFiles = [
        { orig: 'style.css', min: 'assets/css/style.min.css' },
        { orig: 'assets/css/components.css', min: 'assets/css/components.min.css' },
        { orig: 'assets/css/admin.css', min: 'assets/css/admin.min.css' },
        { orig: 'assets/css/admin-settings.css', min: 'assets/css/admin-settings.min.css' },
        { orig: 'assets/css/faq-admin.css', min: 'assets/css/faq-admin.min.css' },
        { orig: 'assets/css/demo-content.css', min: 'assets/css/demo-content.min.css' },
        { orig: 'assets/css/membership-plans.css', min: 'assets/css/membership-plans.min.css' },
        { orig: 'assets/css/form-kit.css', min: 'assets/css/form-kit.min.css' },
        { orig: 'assets/css/page-contact.css', min: 'assets/css/page-contact.min.css' }
    ];

    const jsFiles = [
        { orig: 'assets/js/main.js', min: 'assets/js/main.min.js' },
        { orig: 'assets/js/membership.js', min: 'assets/js/membership.min.js' },
        { orig: 'assets/js/admin.js', min: 'assets/js/admin.min.js' },
        { orig: 'assets/js/admin-settings.js', min: 'assets/js/admin-settings.min.js' },
        { orig: 'assets/js/faq-admin.js', min: 'assets/js/faq-admin.min.js' },
        { orig: 'assets/js/demo-content.js', min: 'assets/js/demo-content.min.js' },
        { orig: 'assets/js/membership-plans.js', min: 'assets/js/membership-plans.min.js' },
        { orig: 'assets/js/membership-admin.js', min: 'assets/js/membership-admin.min.js' },
        { orig: 'assets/js/form-kit.js', min: 'assets/js/form-kit.min.js' },
    ];

    let totalOriginalCSS = 0;
    let totalMinifiedCSS = 0;
    let totalOriginalJS = 0;
    let totalMinifiedJS = 0;

    console.log('📊 Build Summary:');
    console.log('   CSS Files:');
    
    cssFiles.forEach(file => {
        if (fs.existsSync(file.orig) && fs.existsSync(file.min)) {
            const original = fs.statSync(file.orig).size;
            const minified = fs.statSync(file.min).size;
            const savings = ((original - minified) / original * 100).toFixed(1);
            totalOriginalCSS += original;
            totalMinifiedCSS += minified;
            console.log(`     ${file.orig}: ${(original / 1024).toFixed(1)}KB → ${(minified / 1024).toFixed(1)}KB (${savings}% smaller)`);
        }
    });

    console.log('   JavaScript Files:');
    jsFiles.forEach(file => {
        if (fs.existsSync(file.orig) && fs.existsSync(file.min)) {
            const original = fs.statSync(file.orig).size;
            const minified = fs.statSync(file.min).size;
            const savings = ((original - minified) / original * 100).toFixed(1);
            totalOriginalJS += original;
            totalMinifiedJS += minified;
            console.log(`     ${file.orig}: ${(original / 1024).toFixed(1)}KB → ${(minified / 1024).toFixed(1)}KB (${savings}% smaller)`);
        }
    });

    const totalOriginal = totalOriginalCSS + totalOriginalJS;
    const totalMinified = totalMinifiedCSS + totalMinifiedJS;
    const totalSavings = ((totalOriginal - totalMinified) / totalOriginal * 100).toFixed(1);

    console.log('\n📈 Total Savings:');
    console.log(`   CSS: ${(totalOriginalCSS / 1024).toFixed(1)}KB → ${(totalMinifiedCSS / 1024).toFixed(1)}KB (${((totalOriginalCSS - totalMinifiedCSS) / totalOriginalCSS * 100).toFixed(1)}% smaller)`);
    console.log(`   JS:  ${(totalOriginalJS / 1024).toFixed(1)}KB → ${(totalMinifiedJS / 1024).toFixed(1)}KB (${((totalOriginalJS - totalMinifiedJS) / totalOriginalJS * 100).toFixed(1)}% smaller)`);
    console.log(`   Total: ${(totalOriginal / 1024).toFixed(1)}KB → ${(totalMinified / 1024).toFixed(1)}KB (${totalSavings}% smaller)`);
    console.log('\n🎉 Build completed successfully!');

} catch (error) {
    console.error('❌ Build failed:', error.message);
    process.exit(1);
}
