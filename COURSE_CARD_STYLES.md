# Course Card Styles System

This system allows you to choose between two different visual styles for course card images in your LMS platform.

## Available Styles

### 1. Dark Overlay Style (Default)
- **Description**: Dark overlay on images that disappears on hover
- **Effect**: Adds a semi-transparent dark layer over course images that fades out when users hover over the card
- **Use Case**: Creates a professional, modern look with good text readability

### 2. Gray Hover Style
- **Description**: Gray images that become colored on hover
- **Effect**: Course images appear in grayscale by default and become full-color when hovered
- **Use Case**: Creates an interactive, engaging experience that draws attention to hovered items

## Configuration

### Admin Panel Settings

1. **Access Settings**: Go to `Admin Panel > Settings > Personalization > Course Card Styles`

2. **Choose Style**: Enable one of the two styles using the toggle switches

3. **Customize Parameters**:

#### Dark Overlay Style Settings:
- **Overlay Color**: Choose the color of the dark overlay
- **Overlay Opacity**: Set the transparency level (0-100%)
- **Transition Duration**: Control how fast the overlay fades (0.1-2 seconds)

#### Gray Hover Style Settings:
- **Gray Filter Intensity**: Set how gray the images appear (0-100%)
- **Brightness**: Control the brightness of non-hovered images (0.1-2x)
- **Transition Duration**: Control how fast the color transition occurs (0.1-2 seconds)

## Technical Implementation

### Files Modified:
- `config/course_card_styles.php` - Configuration file
- `app/Helpers/helper.php` - Helper functions
- `resources/views/web/default/includes/webinar/grid-card.blade.php` - Grid card template
- `resources/views/web/default/includes/webinar/list-card.blade.php` - List card template
- `resources/views/admin/settings/personalization/course_card_styles.blade.php` - Admin settings page
- `lang/en/admin/main.php` - Translation keys

### Helper Functions:
- `getCourseCardStyle()` - Returns the current active style
- `getCourseCardStyleClass()` - Returns the CSS class for the current style
- `getCourseCardStyleSettings()` - Returns the settings for the current style

### CSS Classes:
- `.course-card-dark-overlay` - Applied when dark overlay style is active
- `.course-card-gray-hover` - Applied when gray hover style is active

## Environment Variables

You can set the default style using environment variables:
```env
COURSE_CARD_STYLE=dark_overlay
# or
COURSE_CARD_STYLE=gray_hover
```

## Browser Compatibility

Both styles work with all modern browsers that support:
- CSS `filter` property (for gray hover style)
- CSS `transition` property
- CSS `opacity` property

## Customization

### Adding New Styles:
1. Add the style configuration to `config/course_card_styles.php`
2. Create the CSS rules in the card templates
3. Add admin settings in the personalization section
4. Update helper functions to support the new style

### Modifying Existing Styles:
1. Update the CSS rules in the card templates
2. Modify the admin settings parameters
3. Update the helper functions if needed

## Troubleshooting

### Style Not Changing:
1. Clear browser cache
2. Check if the correct style is enabled in admin settings
3. Verify the CSS classes are being applied correctly

### Performance Issues:
1. Reduce transition duration for faster animations
2. Lower image quality if using many course cards
3. Consider lazy loading for course images

## Examples

### Dark Overlay Style:
```css
.course-card-dark-overlay .image-overlay {
    background: rgba(0, 0, 0, 0.3);
    opacity: 1;
    transition: opacity 0.3s ease;
}
```

### Gray Hover Style:
```css
.course-card-gray-hover .image-box img {
    filter: grayscale(100%) brightness(0.8);
    transition: filter 0.3s ease;
}
```
