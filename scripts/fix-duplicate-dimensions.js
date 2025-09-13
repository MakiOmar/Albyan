const fs = require('fs');
const path = require('path');

// Files that might have duplicate width/height attributes
const filesToCheck = [
    'resources/views/web/default/includes/webinar/grid-card.blade.php',
    'resources/views/web/default/includes/webinar/list-card.blade.php',
    'resources/views/web/default/gift/bundle_card.blade.php',
    'resources/views/web/default/gift/course_card.blade.php',
    'resources/views/web/default/includes/webinar/upcoming_course_grid_card.blade.php',
    'resources/views/web/default/includes/webinar/upcoming_course_list_card.blade.php'
];

function fixDuplicateDimensions(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        let updated = false;
        
        // Pattern to find duplicate width/height attributes
        // This regex looks for img tags with multiple width or height attributes
        const duplicatePattern = /<img([^>]*?)width="[^"]*"([^>]*?)width="[^"]*"([^>]*?)>/g;
        content = content.replace(duplicatePattern, (match, before, middle, after) => {
            updated = true;
            // Remove the first width/height and keep the last one
            const cleanedBefore = before.replace(/width="[^"]*"\s*/, '').replace(/height="[^"]*"\s*/, '');
            const cleanedMiddle = middle.replace(/width="[^"]*"\s*/, '').replace(/height="[^"]*"\s*/, '');
            return `<img${cleanedBefore}${cleanedMiddle}${after}>`;
        });
        
        // Also fix height duplicates
        const heightDuplicatePattern = /<img([^>]*?)height="[^"]*"([^>]*?)height="[^"]*"([^>]*?)>/g;
        content = content.replace(heightDuplicatePattern, (match, before, middle, after) => {
            updated = true;
            const cleanedBefore = before.replace(/height="[^"]*"\s*/, '');
            const cleanedMiddle = middle.replace(/height="[^"]*"\s*/, '');
            return `<img${cleanedBefore}${cleanedMiddle}${after}>`;
        });
        
        if (updated) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`✅ Fixed duplicate dimensions: ${filePath}`);
        } else {
            console.log(`⏭️  No duplicate dimensions found: ${filePath}`);
        }
    } catch (error) {
        console.error(`❌ Error updating ${filePath}:`, error.message);
    }
}

console.log('🔧 Fixing duplicate width/height attributes in image templates...\n');

filesToCheck.forEach(fixDuplicateDimensions);

console.log('\n✅ Duplicate dimensions fixed!');
