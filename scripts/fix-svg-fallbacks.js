const fs = require('fs');
const path = require('path');

// Files to update with SVG fallback
const filesToUpdate = [
    'resources/views/web/default/bundle/index.blade.php',
    'resources/views/web/default/includes/webinar/list-card.blade.php',
    'resources/views/web/default/includes/webinar/grid-card.blade.php',
    'resources/views/web/default/panel/store/products/lists.blade.php',
    'resources/views/web/default/pages/categories.blade.php',
    'resources/views/web/default/pages/classes.blade.php',
    'resources/views/web/default/pages/instructors.blade.php',
    'resources/views/web/default/upcoming_courses/lists.blade.php'
];

const svgPlaceholder = '/assets/default/img/placeholder.svg';

function updateFile(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Replace JPG placeholders with SVG
        content = content.replace(/\/assets\/default\/img\/[^"]*\.jpg/g, svgPlaceholder);
        
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`✅ Updated: ${filePath}`);
    } catch (error) {
        console.error(`❌ Error updating ${filePath}:`, error.message);
    }
}

console.log('🔧 Updating fallback images to use SVG placeholder...\n');

filesToUpdate.forEach(updateFile);

console.log('\n✅ All files updated with SVG placeholder!');
