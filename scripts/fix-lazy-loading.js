const fs = require('fs');
const path = require('path');

// 1x1 transparent pixel (base64 encoded)
const transparentPixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

// Files to update
const filesToUpdate = [
    'resources/views/web/default/bundle/index.blade.php',
    'resources/views/web/default/includes/webinar/list-card.blade.php',
    'resources/views/web/default/includes/webinar/grid-card.blade.php',
    'resources/views/web/default/panel/store/products/lists.blade.php',
    'resources/views/web/default/pages/categories.blade.php',
    'resources/views/web/default/pages/classes.blade.php',
    'resources/views/web/default/pages/instructors.blade.php',
    'resources/views/web/default/upcoming_courses/lists.blade.php',
    'resources/views/web/default/pages/about.blade.php',
    'resources/views/web/default/pages/reviews.blade.php',
    'resources/views/web/default/pages/includes/categories-rounded.blade.php'
];

function updateFile(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Replace SVG placeholders with transparent pixel
        const svgPlaceholderRegex = /src="data:image\/svg\+xml,[^"]*"/g;
        content = content.replace(svgPlaceholderRegex, `src="${transparentPixel}"`);
        
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`✅ Updated: ${filePath}`);
    } catch (error) {
        console.error(`❌ Error updating ${filePath}:`, error.message);
    }
}

console.log('🔧 Fixing lazy loading placeholders...\n');

filesToUpdate.forEach(updateFile);

console.log('\n✅ All files updated with transparent pixel placeholders!');
