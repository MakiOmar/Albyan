# ZSkeleton Theme Build System

This theme includes a comprehensive build system for minifying CSS and JavaScript assets to improve performance.

## Features

- **Automatic Minification**: CSS and JavaScript files are automatically minified
- **Theme Option**: Choose between minified and original assets via WordPress Customizer
- **Development Mode**: Watch files for changes and auto-rebuild
- **Performance Monitoring**: Build process shows file size savings

## Quick Start

### 1. Install Dependencies

```bash
npm install
```

### 2. Build Minified Assets

```bash
npm run build
```

This will create:
- `assets/css/style.min.css` (minified version of `style.css`)
- `assets/js/main.min.js` (minified version of `assets/js/main.js`)

### 3. Development Mode

For development with auto-rebuild on file changes:

```bash
npm run dev
```

This will:
- Build the initial minified files
- Watch for changes in `style.css` and `assets/js/main.js`
- Automatically rebuild minified versions when files change

## Available Commands

| Command | Description |
|---------|-------------|
| `npm run build` | Build all minified assets |
| `npm run build:css` | Build only CSS minified version |
| `npm run build:js` | Build only JavaScript minified version |
| `npm run watch` | Watch all files for changes |
| `npm run watch:css` | Watch only CSS files |
| `npm run watch:js` | Watch only JavaScript files |
| `npm run dev` | Build and watch (development mode) |
| `npm run clean` | Remove all minified files |

## Theme Configuration

### WordPress Customizer

1. Go to **Appearance > Customize**
2. Navigate to **Performance Settings**
3. Toggle **"Use Minified Assets"** option

**Default**: Minified assets are enabled by default for better performance.

**Development**: Uncheck this option to load original files for debugging.

### Contact page & Form Kit

- **Page template**: Assign **Contact (modern)** (`page-contact.php`) to your contact page, or use a page with slug `contact` so layout styles load. Page body content is shown as the intro above the detail cards.
- **Customizer**: Section **Contact page** — subtitle (under title bar), phone, address, business hours, map URL (opens in a new tab).
- **Form**: Public submissions use the Form Kit form `zskeleton_contact` (`includes/contact-form-kit.php`) — AJAX, honeypot, validation/sanitization. Inbound mail goes to **ZSkeleton Settings → Contact email** (`zskeleton_contact_email`).
- **SEO Agency Kit**: The kit **Contact** template reuses `zskeleton_render_contact_page_layout()` when this theme is active; run `npm run build` so `page-contact.min.css` and `form-kit.min.css` exist if minification is on.

`npm run build:css` and `npm run build:js` also minify **form-kit** and **page-contact** assets (see `package.json`).

## File Structure

```
wp-content/themes/gai/
├── style.css                 # Original CSS (source)
├── assets/
│   ├── css/
│   │   └── style.min.css     # Minified CSS (generated)
│   └── js/
│       ├── main.js           # Original JavaScript (source)
│       └── main.min.js       # Minified JavaScript (generated)
├── package.json              # NPM dependencies and scripts
├── build.js                  # Build script
├── watch.js                  # Watch script
└── BUILD.md                  # This file
```

## Dependencies

- **clean-css-cli**: CSS minification
- **terser**: JavaScript minification and compression
- **chokidar-cli**: File watching for development
- **rimraf**: Cross-platform file deletion

## Performance Benefits

The build system typically achieves:
- **CSS**: 20-40% size reduction
- **JavaScript**: 30-50% size reduction
- **Faster Loading**: Reduced file sizes improve page load times
- **Better Caching**: Minified files are more cache-friendly

## Development Workflow

### For Theme Development

1. Make changes to `style.css` or `assets/js/main.js`
2. Run `npm run dev` to start watching for changes
3. Minified files are automatically updated
4. Test with minified assets enabled in WordPress Customizer

### For Production Deployment

1. Run `npm run build` to ensure minified files are up to date
2. Commit both original and minified files to version control
3. Deploy with minified assets enabled by default

## Troubleshooting

### Build Fails

If the build process fails:

1. Ensure Node.js and npm are installed
2. Run `npm install` to install dependencies
3. Check file permissions in the theme directory

### Minified Files Not Loading

1. Verify minified files exist in `assets/css/` and `assets/js/`
2. Check WordPress Customizer setting for "Use Minified Assets"
3. Clear any caching plugins
4. Check browser developer tools for 404 errors

### File Watching Not Working

1. Ensure `chokidar-cli` is installed: `npm install chokidar-cli`
2. Check file permissions
3. Try running `npm run build` manually first

## Integration with WordPress

The theme automatically:
- Detects the minification setting from WordPress Customizer
- Loads appropriate asset versions (minified or original)
- Maintains all existing functionality
- Provides seamless switching between modes

## Best Practices

1. **Always build before deployment**: Run `npm run build` before pushing changes
2. **Test both modes**: Verify functionality with both minified and original assets
3. **Version control**: Commit both original and minified files
4. **Development**: Use `npm run dev` for active development
5. **Production**: Keep minified assets enabled for better performance
