const fs = require('fs');

// 1x1 transparent pixel (base64 encoded)
const transparentPixel = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

const testFilePath = 'public/test-lazy-loading.html';

try {
    let content = fs.readFileSync(testFilePath, 'utf8');
    
    // Replace all SVG placeholders with transparent pixel
    const svgPlaceholderRegex = /src="data:image\/svg\+xml,[^"]*"/g;
    content = content.replace(svgPlaceholderRegex, `src="${transparentPixel}"`);
    
    fs.writeFileSync(testFilePath, content, 'utf8');
    console.log('✅ Test page updated with transparent pixel placeholders!');
} catch (error) {
    console.error('❌ Error updating test page:', error.message);
}
