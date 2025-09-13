const fs = require('fs');
const path = require('path');

// Files that need to be updated with lazy loading for avatars and other images
const filesToUpdate = [
    'resources/views/web/default/includes/webinar/upcoming_course_list_card.blade.php',
    'resources/views/web/default/includes/webinar/upcoming_course_grid_card.blade.php',
    'resources/views/web/default/gift/bundle_card.blade.php',
    'resources/views/web/default/gift/course_card.blade.php',
    'resources/views/web/default/includes/webinar/list-card.blade.php',
    'resources/views/web/default/includes/webinar/grid-card.blade.php',
    'resources/views/web/default/pages/home.blade.php',
    'resources/views/web/default/gift/product_card.blade.php',
    'resources/views/web/default/course/tabs/reviews.blade.php',
    'resources/views/web/default/forum/topics_search.blade.php',
    'resources/views/web/default/user/profile_tabs/forum.blade.php',
    'resources/views/web/default/forum/topics.blade.php',
    'resources/views/web/default/products/includes/tabs/reviews.blade.php',
    'resources/views/web/default/products/includes/card.blade.php',
    'resources/views/web/default/bundle/tabs/reviews.blade.php',
    'resources/views/web/default/includes/comments.blade.php',
    'resources/views/web/default/panel/webinar/course_statistics/index.blade.php',
    'resources/views/web/default/panel/webinar/comments.blade.php',
    'resources/views/web/default/panel/manage/students.blade.php',
    'resources/views/web/default/panel/manage/instructors.blade.php',
    'resources/views/web/default/panel/assignments/students.blade.php',
    'resources/views/web/default/panel/financial/sales.blade.php',
    'resources/views/web/default/panel/support/ticket_conversations.blade.php',
    'resources/views/web/default/panel/support/conversations.blade.php'
];

const transparentPixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
const placeholderSvg = '/assets/default/img/placeholder.svg';

function updateFile(filePath) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        let updated = false;
        
        // Pattern 1: Direct src with getAvatar() - these are usually small avatars
        const avatarPattern = /src="\{\{\s*\$([^}]+)\->getAvatar\(\)\s*\}\}"/g;
        content = content.replace(avatarPattern, (match, variable) => {
            updated = true;
            return `src="${transparentPixel}" data-src="{{ $${variable}->getAvatar() ?: '${placeholderSvg}' }}"`;
        });
        
        // Pattern 2: Direct src with getImage() that we might have missed
        const imagePattern = /src="\{\{\s*\$([^}]+)\->getImage\(\)\s*\}\}"/g;
        content = content.replace(imagePattern, (match, variable) => {
            updated = true;
            return `src="${transparentPixel}" data-src="{{ $${variable}->getImage() ?: '${placeholderSvg}' }}"`;
        });
        
        // Pattern 3: Direct src with thumbnail
        const thumbnailPattern = /src="\{\{\s*\$([^}]+)\->thumbnail\s*\}\}"/g;
        content = content.replace(thumbnailPattern, (match, variable) => {
            updated = true;
            return `src="${transparentPixel}" data-src="{{ $${variable}->thumbnail ?: '${placeholderSvg}' }}"`;
        });
        
        // Add width and height attributes to images that don't have them
        // For avatars, use smaller dimensions
        const imgWithoutDimensions = /<img([^>]*?)(?<!width="[^"]*")(?<!height="[^"]*")([^>]*?)>/g;
        content = content.replace(imgWithoutDimensions, (match, before, after) => {
            if (match.includes('data-src') && !match.includes('width=')) {
                // Check if it's an avatar (smaller image)
                if (match.includes('getAvatar') || match.includes('avatar')) {
                    return `<img${before} width="50" height="50"${after}>`;
                } else {
                    return `<img${before} width="200" height="150"${after}>`;
                }
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

console.log('🔧 Updating all remaining course and avatar images with lazy loading...\n');

filesToUpdate.forEach(updateFile);

console.log('\n✅ All remaining images updated with lazy loading!');
