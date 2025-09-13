/**
 * CSS Minification Utility
 * Creates minified versions of CSS files for better performance
 */

class CSSMinifier {
    constructor() {
        this.minifiedFiles = new Set();
    }

    /**
     * Minify CSS content
     * @param {string} css - CSS content to minify
     * @returns {string} - Minified CSS
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
     * Create minified version of a CSS file
     * @param {string} filePath - Path to the CSS file
     * @returns {Promise<string>} - Minified CSS content
     */
    async createMinifiedVersion(filePath) {
        try {
            const response = await fetch(filePath);
            if (!response.ok) {
                throw new Error(`Failed to fetch CSS file: ${filePath}`);
            }
            
            const css = await response.text();
            const minified = this.minifyCSS(css);
            
            // Store the minified content
            this.minifiedFiles.add(filePath);
            
            return minified;
        } catch (error) {
            console.error(`Error minifying CSS file ${filePath}:`, error);
            throw error;
        }
    }

    /**
     * Create theme-specific minified CSS files
     * @param {string} theme - Theme name
     * @param {Array<string>} cssFiles - Array of CSS file names
     */
    async createThemeMinifiedFiles(theme, cssFiles) {
        const results = [];
        
        for (const cssFile of cssFiles) {
            try {
                const originalPath = `/assets/${theme}/vendors/${cssFile}/${cssFile}.css`;
                const minifiedPath = `/assets/${theme}/vendors/${cssFile}/${cssFile}.min.css`;
                
                const minified = await this.createMinifiedVersion(originalPath);
                
                // In a real implementation, you would save this to the server
                // For now, we'll just log the result
                console.log(`Minified ${cssFile} for theme ${theme}:`, {
                    original: originalPath,
                    minified: minifiedPath,
                    size: minified.length
                });
                
                results.push({
                    file: cssFile,
                    theme: theme,
                    originalPath,
                    minifiedPath,
                    size: minified.length
                });
            } catch (error) {
                console.error(`Failed to minify ${cssFile} for theme ${theme}:`, error);
            }
        }
        
        return results;
    }

    /**
     * Get minification statistics
     * @returns {Object} - Statistics about minified files
     */
    getStats() {
        return {
            minifiedFiles: Array.from(this.minifiedFiles),
            count: this.minifiedFiles.size
        };
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CSSMinifier;
} else {
    window.CSSMinifier = CSSMinifier;
}
