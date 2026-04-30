# ZSkeleton FAQ System Flow Documentation

## Overview

The ZSkeleton theme includes a comprehensive FAQ (Frequently Asked Questions) system that allows administrators to manage questions and answers with categorization, difficulty levels, and advanced display options. The system consists of custom post types, admin interfaces, frontend templates, and AJAX functionality.

## System Architecture

### 1. Core Components

#### A. Custom Post Type (`class-faqs.php`)
- **Post Type**: `zskeleton_faqs`
- **Purpose**: Stores individual FAQ entries
- **Features**:
  - Question (post title)
  - Answer (post content)
  - Custom meta fields for difficulty, ordering, featured status
  - Category and tag taxonomies
  - Admin column customization
  - Sortable columns

#### B. Admin Interface (`class-faq-admin.php`)
- **Location**: Under FAQs → Settings in WordPress admin
- **Features**:
  - Bulk operations (publish, unpublish, delete, categorize)
  - FAQ reordering with drag-and-drop
  - Import default FAQs
  - Display settings configuration
  - AJAX-powered admin functionality

#### C. Frontend Template (`page-faqs.php`)
- **Purpose**: Displays FAQs to website visitors
- **Features**:
  - Search functionality
  - Category filtering
  - Difficulty level filtering
  - Featured FAQs section
  - Accordion-style display
  - Responsive design

### 2. Data Structure

#### FAQ Post Meta Fields
```php
_zskeleton_faq_difficulty    // beginner|intermediate|advanced
_zskeleton_faq_order        // integer for custom ordering
_zskeleton_faq_featured     // 1 for featured FAQs
```

#### Taxonomies
- **Categories** (`zskeleton_faq_category`): Membership, Technical, Billing, etc.
- **Tags** (`zskeleton_faq_tag`): Flexible tagging system

## System Flow

### 1. Admin Workflow

```
1. Admin creates FAQ
   ↓
2. Sets category, difficulty, and featured status
   ↓
3. FAQ appears in admin list with custom columns
   ↓
4. Admin can bulk manage FAQs via Settings page
   ↓
5. FAQs are displayed on frontend based on settings
```

### 2. Frontend Display Flow

```
1. User visits /faqs/ page
   ↓
2. System loads page-faqs.php template
   ↓
3. Featured FAQs are displayed first (if enabled)
   ↓
4. All FAQs are loaded by category
   ↓
5. JavaScript enables search/filter functionality
   ↓
6. User can interact with accordion-style Q&A
```

### 3. AJAX Operations

#### Admin AJAX Handlers
- `zskeleton_bulk_faq_action`: Bulk operations on FAQs
- `zskeleton_import_default_faqs`: Import sample FAQs
- `zskeleton_reorder_faqs`: Drag-and-drop reordering

#### Frontend AJAX (if implemented)
- Real-time search filtering
- Category-based loading
- Analytics tracking

## File Structure

```
wp-content/themes/gai/
├── includes/
│   ├── post-types/
│   │   └── class-faqs.php           # FAQ post type definition
│   └── admin/
│       └── class-faq-admin.php      # Admin interface
├── page-faqs.php                    # Frontend template
├── assets/
│   ├── js/
│   │   └── faq-admin.js            # Admin JavaScript
│   └── css/
│       └── faq-admin.css           # Admin styles
└── functions.php                    # Initialization
```

## Configuration Options

### Admin Settings (`/wp-admin/edit.php?post_type=zskeleton_faqs&page=zskeleton-faq-settings`)

| Setting | Purpose | Default |
|---------|---------|---------|
| `zskeleton_faq_per_page` | FAQs per page | 10 |
| `zskeleton_faq_show_categories` | Show category filter | Yes |
| `zskeleton_faq_show_search` | Show search box | Yes |
| `zskeleton_faq_accordion_style` | Accordion display style | Default |
| `zskeleton_faq_featured_limit` | Featured FAQs limit | 5 |
| `zskeleton_faq_show_difficulty` | Show difficulty levels | Yes |

## Usage Examples

### 1. Creating FAQs Programmatically

```php
$faq_id = wp_insert_post(array(
    'post_type' => 'zskeleton_faqs',
    'post_title' => 'What is ZSkeleton membership?',
    'post_content' => 'ZSkeleton membership provides access to...',
    'post_status' => 'publish',
    'meta_input' => array(
        '_zskeleton_faq_difficulty' => 'beginner',
        '_zskeleton_faq_featured' => '1',
        '_zskeleton_faq_order' => 1
    )
));

// Assign category
wp_set_post_terms($faq_id, array('membership'), 'zskeleton_faq_category');
```

### 2. Getting FAQs by Category

```php
$faqs = ZSkeleton_FAQs::get_faqs_by_category('membership', 10, false);
foreach ($faqs as $faq) {
    echo '<h3>' . $faq->post_title . '</h3>';
    echo '<p>' . $faq->post_content . '</p>';
}
```

### 3. Frontend Display

The FAQ page automatically handles:
- Category filtering via dropdown
- Search functionality
- Difficulty-based filtering
- Featured FAQs highlighting
- Responsive accordion display

## Integration Points

### 1. Theme Integration
- Initialized in `functions.php` via `zskeleton_init_components()`
- Styles integrated with main theme CSS
- Follows theme design patterns

### 2. Membership System Integration
- Can restrict FAQ access based on membership status
- Featured FAQs for members vs. non-members
- Category-based access control

### 3. Analytics Integration
- Track FAQ interactions
- Popular question analytics
- User engagement metrics

## Customization

### 1. Adding Custom Fields
```php
// In class-faqs.php add_meta_boxes()
add_meta_box(
    'zskeleton_faq_custom',
    __('Custom FAQ Data', 'zskeleton'),
    array($this, 'custom_meta_box_callback'),
    'zskeleton_faqs'
);
```

### 2. Custom Display Templates
- Override `page-faqs.php` in child theme
- Create custom shortcodes for FAQ display
- Add custom CSS for styling

### 3. Additional Taxonomies
```php
// Add new taxonomy in register_taxonomies()
register_taxonomy('zskeleton_faq_priority', 'zskeleton_faqs', array(
    'labels' => array('name' => 'Priorities'),
    'public' => true,
    'hierarchical' => true
));
```

## Best Practices

### 1. Content Management
- Use clear, concise questions as titles
- Organize FAQs into logical categories
- Set appropriate difficulty levels
- Feature most common questions

### 2. Performance
- Limit featured FAQs to 5-10 items
- Use pagination for large FAQ sets
- Implement caching for category queries
- Optimize database queries

### 3. User Experience
- Maintain consistent categorization
- Use search-friendly question titles
- Provide comprehensive answers
- Regular content updates

## Troubleshooting

### Common Issues

1. **FAQs not displaying**
   - Check if post type is registered
   - Verify template file exists
   - Ensure FAQs are published

2. **Admin settings not saving**
   - Verify nonce validation
   - Check user permissions
   - Review AJAX handlers

3. **Search not working**
   - Ensure JavaScript is loaded
   - Check AJAX endpoints
   - Verify search input handling

### Debug Mode
Enable WordPress debug mode to see FAQ-related errors:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Future Enhancements

1. **FAQ Analytics Dashboard**
   - Track most viewed questions
   - User feedback on helpfulness
   - Search query analytics

2. **Advanced Filtering**
   - Multi-select categories
   - Date-based filtering
   - Popularity sorting

3. **Import/Export Features**
   - CSV import/export
   - FAQ backup/restore
   - Migration tools

4. **Multi-language Support**
   - WPML integration
   - Translation management
   - Language-specific FAQs

---

*Last updated: December 2024*
*Version: 1.0.0*
