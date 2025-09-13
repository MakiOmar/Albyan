const fs = require('fs');
const path = require('path');

// Files to update with fallback images
const filesToUpdate = [
    {
        file: 'resources/views/web/default/bundle/index.blade.php',
        fallback: '/assets/default/img/course-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/includes/webinar/list-card.blade.php',
        fallback: '/assets/default/img/course-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/includes/webinar/grid-card.blade.php',
        fallback: '/assets/default/img/course-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/panel/store/products/lists.blade.php',
        fallback: '/assets/default/img/product-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/pages/categories.blade.php',
        fallback: '/assets/default/img/banner-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/pages/classes.blade.php',
        fallback: '/assets/default/img/banner-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/pages/instructors.blade.php',
        fallback: '/assets/default/img/banner-placeholder.jpg'
    },
    {
        file: 'resources/views/web/default/upcoming_courses/lists.blade.php',
        fallback: '/assets/default/img/banner-placeholder.jpg'
    }
];

function updateFile(filePath, fallbackImage) {
    try {
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Replace data-src="{{ $variable->getImage() }}" with fallback
        const dataSrcRegex = /data-src="\{\{\s*\$[^}]+\->getImage\(\)\s*\}\}"/g;
        content = content.replace(dataSrcRegex, (match) => {
            // Extract the variable part
            const variableMatch = match.match(/\{\{\s*(\$[^}]+)\->getImage\(\)\s*\}\}/);
            if (variableMatch) {
                const variable = variableMatch[1];
                return `data-src="{{ ${variable}->getImage() ?: '${fallbackImage}' }}"`;
            }
            return match;
        });
        
        // Also handle other image patterns
        const thumbnailRegex = /data-src="\{\{\s*\$[^}]+\->thumbnail\s*\}\}"/g;
        content = content.replace(thumbnailRegex, (match) => {
            const variableMatch = match.match(/\{\{\s*(\$[^}]+)\->thumbnail\s*\}\}/);
            if (variableMatch) {
                const variable = variableMatch[1];
                return `data-src="{{ ${variable}->thumbnail ?: '${fallbackImage}' }}"`;
            }
            return match;
        });
        
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`✅ Updated: ${filePath}`);
    } catch (error) {
        console.error(`❌ Error updating ${filePath}:`, error.message);
    }
}

console.log('🔧 Adding fallback images to prevent undefined data-src...\n');

filesToUpdate.forEach(({ file, fallback }) => {
    updateFile(file, fallback);
});

console.log('\n✅ All files updated with fallback images!');
