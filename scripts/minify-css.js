#!/usr/bin/env node

/**
 * CSS Minification Build Script
 * Creates minified versions of CSS files for all themes
 */

const fs = require('fs');
const path = require('path');

class CSSBuildTool {
    constructor() {
        this.themes = ['default']; // Add more themes as needed
        this.cssFiles = [
            'sweetalert2/dist/sweetalert2.css',
            'toast/jquery.toast.css',
            'swiper/swiper-bundle.css',
            'simplebar/simplebar.css',
            'owl-carousel2/owl.carousel.css'
        ];
        
        this.appCSSFiles = [
            'css/app.css',
            'css/rtl-app.css',
            'css/panel.css'
        ];
        
        // Note: app.css, rtl-app.css, and panel.css are loaded immediately in HTML head
        // and are NOT part of the lazy loading system as they contain critical styles
    }

    /**
     * Minify CSS content
     */
    minifyCSS(css) {
        return css
            // Remove comments
            .replace(/\/\*[\s\S]*?\*\//g, '')
            // Remove unnecessary whitespace
            .replace(/\s+/g, ' ')
            // Remove space before and after specific characters
            .replace(/\s*{\s*/g, '{')
            .replace(/;\s*}/g, '}')
            .replace(/;\s*/g, ';')
            .replace(/,\s*/g, ',')
            .replace(/:\s*/g, ':')
            .replace(/\s*>\s*/g, '>')
            .replace(/\s*\+\s*/g, '+')
            .replace(/\s*~\s*/g, '~')
            // Remove trailing semicolons before closing braces
            .replace(/;}/g, '}')
            // Remove leading and trailing whitespace
            .trim();
    }

    /**
     * Process a single CSS file
     */
    processCSSFile(theme, cssFile) {
        // Determine the correct path based on file type
        let publicPath;
        if (cssFile.startsWith('css/')) {
            // App CSS files are in the main assets directory
            publicPath = path.join(__dirname, '..', 'public', 'assets', theme);
        } else {
            // Vendor CSS files are in the vendors subdirectory
            publicPath = path.join(__dirname, '..', 'public', 'assets', theme, 'vendors');
        }
        
        const originalPath = path.join(publicPath, cssFile);
        const minifiedPath = originalPath.replace('.css', '.min.css');

        try {
            // Check if original file exists
            if (!fs.existsSync(originalPath)) {
                console.log(`⚠️  Original file not found: ${originalPath}`);
                return null;
            }

            // Read original CSS
            const originalCSS = fs.readFileSync(originalPath, 'utf8');
            
            // Minify CSS
            const minifiedCSS = this.minifyCSS(originalCSS);
            
            // Write minified CSS
            fs.writeFileSync(minifiedPath, minifiedCSS, 'utf8');
            
            // Calculate compression ratio
            const originalSize = originalCSS.length;
            const minifiedSize = minifiedCSS.length;
            const compressionRatio = ((originalSize - minifiedSize) / originalSize * 100).toFixed(2);
            
            console.log(`✅ Minified: ${cssFile}`);
            console.log(`   Original: ${originalSize} bytes`);
            console.log(`   Minified: ${minifiedSize} bytes`);
            console.log(`   Compression: ${compressionRatio}%`);
            console.log('');
            
            return {
                file: cssFile,
                originalSize,
                minifiedSize,
                compressionRatio: parseFloat(compressionRatio)
            };
            
        } catch (error) {
            console.error(`❌ Error processing ${cssFile}:`, error.message);
            return null;
        }
    }

    /**
     * Process all CSS files for a theme
     */
    processTheme(theme) {
        console.log(`🎨 Processing theme: ${theme}`);
        console.log('='.repeat(50));
        
        const results = [];
        
        // Process vendor CSS files
        for (const cssFile of this.cssFiles) {
            const result = this.processCSSFile(theme, cssFile);
            if (result) {
                results.push(result);
            }
        }
        
        // Process application CSS files
        for (const cssFile of this.appCSSFiles) {
            const result = this.processCSSFile(theme, cssFile);
            if (result) {
                results.push(result);
            }
        }
        
        return results;
    }

    /**
     * Process all themes
     */
    async build() {
        console.log('🚀 Starting CSS minification build...');
        console.log('');
        
        const allResults = [];
        
        for (const theme of this.themes) {
            const themeResults = this.processTheme(theme);
            allResults.push({
                theme,
                results: themeResults
            });
        }
        
        // Print summary
        this.printSummary(allResults);
        
        console.log('✨ CSS minification build completed!');
    }

    /**
     * Print build summary
     */
    printSummary(allResults) {
        console.log('');
        console.log('📊 BUILD SUMMARY');
        console.log('='.repeat(50));
        
        let totalOriginalSize = 0;
        let totalMinifiedSize = 0;
        let totalFiles = 0;
        
        for (const themeData of allResults) {
            console.log(`\n🎨 Theme: ${themeData.theme}`);
            
            for (const result of themeData.results) {
                totalOriginalSize += result.originalSize;
                totalMinifiedSize += result.minifiedSize;
                totalFiles++;
                
                console.log(`   ${result.file}: ${result.compressionRatio}% compression`);
            }
        }
        
        const totalCompression = ((totalOriginalSize - totalMinifiedSize) / totalOriginalSize * 100).toFixed(2);
        
        console.log('\n📈 OVERALL STATISTICS');
        console.log(`   Total files processed: ${totalFiles}`);
        console.log(`   Total original size: ${totalOriginalSize} bytes`);
        console.log(`   Total minified size: ${totalMinifiedSize} bytes`);
        console.log(`   Total compression: ${totalCompression}%`);
        console.log(`   Space saved: ${(totalOriginalSize - totalMinifiedSize)} bytes`);
    }
}

// Run the build if this script is executed directly
if (require.main === module) {
    const buildTool = new CSSBuildTool();
    buildTool.build().catch(console.error);
}

module.exports = CSSBuildTool;
