const fs = require('fs');
const path = require('path');

// Files that still need to be updated with lazy loading
const filesToUpdate = [
    'resources/views/web/default/upcoming_courses/show.blade.php',
    'resources/views/web/default/gift/course_card.blade.php',
    'resources/views/web/default/gift/bundle_card.blade.php',
    'resources/views/web/default/includes/purchase_notifications.blade.php',
    'resources/views/web/default/includes/webinar/upcoming_course_list_card.blade.php',
    'resources/views/web/default/includes/webinar/upcoming_course_grid_card.blade.php',
    'resources/views/web/default/panel/webinar/purchases.blade.php',
    'resources/views/web/default/panel/webinar/organization_classes.blade.php',
    'resources/views/web/default/panel/webinar/favorites.blade.php',
    'resources/views/web/default/panel/financial/installments/lists.blade.php',
    'resources/views/web/default/panel/bundle/index.blade.php',
    'resources/views/web/default/panel/upcoming_courses/lists.blade.php',
    'resources/views/web/default/panel/upcoming_courses/followings.blade.php'
];

const transparentPixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
const placeholderSvg = '/assets/default/img/placeholder.svg';

function updateFile(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        let updated = false;
        
        // Pattern 1: Direct src with getImage()
        const directSrcPattern = /src="\{\{\s*\$([^}]+)\->getImage\(\)\s*\}\}"/g;
        content = content.replace(directSrcPattern, (match, variable) => {
            updated = true;
            return `src="${transparentPixel}" data-src="{{ $${variable}->getImage() ?: '${placeholderSvg}' }}"`;
        });
        
        // Pattern 2: Direct src with thumbnail
        const thumbnailPattern = /src="\{\{\s*\$([^}]+)\->thumbnail\s*\}\}"/g;
        content = content.replace(thumbnailPattern, (match, variable) => {
            updated = true;
            return `src="${transparentPixel}" data-src="{{ $${variable}->thumbnail ?: '${placeholderSvg}' }}"`;
        });
        
        // Add width and height attributes to images that don't have them
        const imgWithoutDimensions = /<img([^>]*?)(?<!width="[^"]*")(?<!height="[^"]*")([^>]*?)>/g;
        content = content.replace(imgWithoutDimensions, (match, before, after) => {
            if (match.includes('data-src') && !match.includes('width=')) {
                return `<img${before} width="200" height="150"${after}>`;
            }
            return match;
        });
        
        if (updated) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`✅ Updated: ${filePath}`);
        } else {
            console.log(`⏭️  No changes needed: ${filePath}`);
        }
    } catch (error) {
        console.error(`❌ Error updating ${filePath}:`, error.message);
    }
}

console.log('🔧 Updating all remaining course image templates with lazy loading...\n');

filesToUpdate.forEach(updateFile);

console.log('\n✅ All course image templates updated!');
