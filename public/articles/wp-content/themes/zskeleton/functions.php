<?php
/**
 * ZSkeleton Theme Functions
 * 
 * Core theme functionality and feature setup
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Theme version (keep in sync with Version in style.css and theme-update.json).
define('ZSkeleton_VERSION', '1.0.26');

// Theme directory paths
define('ZSkeleton_THEME_DIR', get_template_directory());
define('ZSkeleton_THEME_URL', get_template_directory_uri());

// Design tokens: palette defaults, option sanitization, and front-end CSS variables.
require_once ZSkeleton_THEME_DIR . '/includes/theme-colors.php';
require_once ZSkeleton_THEME_DIR . '/includes/mobile-bottom-nav.php';
require_once ZSkeleton_THEME_DIR . '/includes/blog-hub.php';
require_once ZSkeleton_THEME_DIR . '/includes/blog-hub-featured-meta.php';
require_once ZSkeleton_THEME_DIR . '/includes/taxonomy-term-listing.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/blog-hub-blocks.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/block-type-metadata-min-assets.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/block-heading-shared.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/slider-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/expert-profile-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/about-company-hero-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/feature-promo-card-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/section-title-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/testimonials-image-slider-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/case-studies-split-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/stepper-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/seo-ar-ai-lead-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/blocks/contact-form-block.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-walker-nav-menu-split-logo.php';

// Arabic gettext fallback (when languages/ar.mo is absent); safe alongside Loco Translate.
require_once ZSkeleton_THEME_DIR . '/includes/languages/locale-ar.php';

// Arabic SEO homepage lead form helpers (admin-post handler + fallback markup).
require_once ZSkeleton_THEME_DIR . '/includes/seo-ar-lead-form.php';

/**
 * Featured image markup, or bundled WebP placeholder when the post has no thumbnail.
 *
 * @param int|WP_Post|null $post Post ID or object; null uses the current post in the loop.
 * @param string|int[]     $size Registered image size name or [width, height] for get_the_post_thumbnail().
 * @param array            $attr Attributes for {@see get_the_post_thumbnail()} (also alt, class, loading on placeholder).
 * @return string HTML (img only; not wrapped in a link).
 */
function zskeleton_get_post_thumbnail_or_placeholder_html( $post = null, $size = 'medium_large', array $attr = array() ) {
	if ( has_post_thumbnail( $post ) ) {
		return get_the_post_thumbnail( $post, $size, $attr );
	}

	$post_obj = get_post( $post );
	$alt      = '';
	if ( isset( $attr['alt'] ) && '' !== (string) $attr['alt'] ) {
		$alt = (string) $attr['alt'];
	} elseif ( $post_obj instanceof WP_Post ) {
		$alt = get_the_title( $post_obj );
	}
	if ( '' === $alt ) {
		$alt = __( 'Post', 'zskeleton' );
	}

	$src = ZSkeleton_THEME_URL . '/assets/images/post-placeholder.webp';

	$loading = isset( $attr['loading'] ) ? (string) $attr['loading'] : 'lazy';

	$class = 'zskeleton-post-placeholder-img wp-post-image';
	if ( ! empty( $attr['class'] ) ) {
		$class .= ' ' . trim( (string) $attr['class'] );
	}

	$extra = '';
	if ( isset( $attr['fetchpriority'] ) ) {
		$extra .= sprintf( ' fetchpriority="%s"', esc_attr( (string) $attr['fetchpriority'] ) );
	}
	if ( isset( $attr['decoding'] ) ) {
		$extra .= sprintf( ' decoding="%s"', esc_attr( (string) $attr['decoding'] ) );
	}

	return sprintf(
		'<img src="%s" alt="%s" class="%s" loading="%s"%s />',
		esc_url( $src ),
		esc_attr( $alt ),
		esc_attr( $class ),
		esc_attr( $loading ),
		$extra
	);
}

// External primary site URL
if ( ! defined( 'ZSkeleton_EXTERNAL_SITE_URL' ) ) {
	define( 'ZSkeleton_EXTERNAL_SITE_URL', 'https://zskeleton.org' );
}


/**
 * Theme updates via GitHub (VCS mode) using Plugin Update Checker.
 *
 * Points at the public theme repository; version is read from remote style.css.
 * Override in wp-config.php: define( 'ZSkeleton_UPDATE_METADATA_URL', 'https://...' );
 * (may be a GitHub repo URL or a JSON metadata URL).
 */
function zskeleton_setup_github_update_checker() {
	$update_checker_path = ZSkeleton_THEME_DIR . '/plugin-update-checker/plugin-update-checker.php';

	if ( ! file_exists( $update_checker_path ) ) {
		return;
	}

	require_once $update_checker_path;

	if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		return;
	}

	$theme_slug = wp_get_theme()->get_stylesheet();

	// Default: GitHub repo URL — PUC uses the GitHub API (tags/releases/branches). Override via wp-config: ZSkeleton_UPDATE_METADATA_URL.
	$metadata_url = defined( 'ZSkeleton_UPDATE_METADATA_URL' )
		? ZSkeleton_UPDATE_METADATA_URL
		: 'https://github.com/MakiOmar/ZSkeleton';
	$github_token  = get_option( 'zskeleton_github_access_token', '' );

	$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		$metadata_url,
		__FILE__,
		$theme_slug
	);

	if ( ! empty( $github_token ) ) {
		$update_checker->addHttpRequestArgFilter(
			static function ( $request_args ) use ( $github_token ) {
				// Add Authorization and UA headers for private GitHub resources.
				if ( empty( $request_args['headers'] ) || ! is_array( $request_args['headers'] ) ) {
					$request_args['headers'] = array();
				}

				$request_args['headers']['Authorization'] = 'token ' . $github_token;
				$request_args['headers']['User-Agent']    = 'ZSkeleton-Theme-Updater';
				$request_args['headers']['Accept']        = 'application/vnd.github+json';

				return $request_args;
			}
		);
	}

	if ( class_exists( '\Debug_Bar' ) && class_exists( '\YahnisElsts\PluginUpdateChecker\v5p6\DebugBar\Extension' ) ) {
		new \YahnisElsts\PluginUpdateChecker\v5p6\DebugBar\Extension( $update_checker );
	}

	$GLOBALS['zskeleton_github_update_checker'] = $update_checker;
}
add_action( 'after_setup_theme', 'zskeleton_setup_github_update_checker' );

/**
 * Disable the auto sizes contain CSS injected by core.
 */
function zskeleton_disable_auto_sizes_contain_css() {
	if ( function_exists( 'wp_print_auto_sizes_contain_css_fix' ) ) {
		remove_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );
	}
}
add_action( 'init', 'zskeleton_disable_auto_sizes_contain_css' );

/**
 * One-time migration from legacy institute option keys to site keys.
 */
function zskeleton_migrate_legacy_site_options() {
    if (get_option('zskeleton_site_options_migrated') === '1') {
        return;
    }

    $legacy_name = get_option('zskeleton_institute_name', '');
    $legacy_tagline = get_option('zskeleton_institute_tagline', '');

    if ('' === get_option('zskeleton_site_name', '') && '' !== $legacy_name) {
        update_option('zskeleton_site_name', sanitize_text_field($legacy_name));
    }

    if ('' === get_option('zskeleton_site_tagline', '') && '' !== $legacy_tagline) {
        update_option('zskeleton_site_tagline', sanitize_textarea_field($legacy_tagline));
    }

    update_option('zskeleton_site_options_migrated', '1');
}
add_action('init', 'zskeleton_migrate_legacy_site_options');

/**
 * Add SEO meta data to head
 */
function zskeleton_has_active_seo_plugin() {
    return defined( 'WPSEO_VERSION' )
        || class_exists( 'RankMath' )
        || class_exists( 'SEOPress' )
        || class_exists( 'AIOSEO\\Plugin\\AIOSEO' )
        || function_exists( 'the_seo_framework' );
}

/**
 * Output rel prev/next head links for paginated archives.
 *
 * Keeps pagination hints plugin-agnostic without duplicating canonical/meta tags.
 */
function zskeleton_archive_rel_links() {
    if ( ! ( is_home() || is_archive() || is_search() ) ) {
        return;
    }
    global $wp_query;
    if ( ! ( $wp_query instanceof WP_Query ) ) {
        return;
    }
    $max = (int) $wp_query->max_num_pages;
    if ( $max < 2 ) {
        return;
    }
    $paged = max( 1, (int) get_query_var( 'paged' ) );
    if ( $paged > 1 ) {
        echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '">' . "\n";
    }
    if ( $paged < $max ) {
        echo '<link rel="next" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'zskeleton_archive_rel_links', 4 );

function zskeleton_add_seo_meta() {
    // Skip if Yoast SEO or other SEO plugins are active
    if ( zskeleton_has_active_seo_plugin() ) {
        return;
    }
    
    global $post;
    
    // Default values
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    $site_url = home_url('/');
    
    // Get current page info
    $title = '';
    $description = '';
    $keywords = '';
    $og_image = '';
    $canonical_url = '';
    
    if (is_home() || is_front_page()) {
        // Homepage
        $title = $site_name;
        if ($site_description) {
            $title .= ' - ' . $site_description;
        }
        $description = $site_description ?: 'ZSkeleton - A reusable WordPress base theme for content and membership websites.';
        $keywords = 'WordPress theme, membership, FAQs, website';
        $canonical_url = $site_url;
        
    } elseif (is_singular()) {
        // Single posts/pages
        $title = get_the_title();
        if (!is_front_page()) {
            $title .= ' - ' . $site_name;
        }
        
        // Get description from excerpt or content
        if (has_excerpt()) {
            $description = wp_trim_words(get_the_excerpt(), 25, '...');
        } else {
            $description = wp_trim_words(strip_tags(get_the_content()), 25, '...');
        }
        
        // Get featured image for OG
        if (has_post_thumbnail()) {
            $og_image = get_the_post_thumbnail_url(null, 'large');
        }
        
        $canonical_url = get_permalink();
        
        // Add post-specific keywords
        $post_keywords = array();
        if (is_single()) {
            // Add categories and tags
            $categories = get_the_category();
            foreach ($categories as $category) {
                $post_keywords[] = $category->name;
            }
            $tags = get_the_tags();
            if ($tags) {
                foreach ($tags as $tag) {
                    $post_keywords[] = $tag->name;
                }
            }
        }
        $keywords = implode(', ', $post_keywords);
        
    } elseif (is_category()) {
        // Category archives
        $category = get_queried_object();
        $title = $category->name . ' - ' . $site_name;
        $description = $category->description ?: 'Browse all posts in the ' . $category->name . ' category.';
        $keywords = $category->name . ', ' . $site_name . ', WordPress, website';
        $canonical_url = get_category_link($category->term_id);
        
    } elseif (is_tag()) {
        // Tag archives
        $tag = get_queried_object();
        $title = $tag->name . ' - ' . $site_name;
        $description = $tag->description ?: 'Browse all posts tagged with ' . $tag->name . '.';
        $keywords = $tag->name . ', ' . $site_name . ', WordPress, website';
        $canonical_url = get_tag_link($tag->term_id);
        
    } elseif (is_archive()) {
        // Other archives
        $title = get_the_archive_title() . ' - ' . $site_name;
        $description = get_the_archive_description() ?: 'Browse our collection of ' . strtolower(get_the_archive_title()) . '.';
        $keywords = get_the_archive_title() . ', ' . $site_name . ', WordPress, website';
        $canonical_url = get_pagenum_link();
        
    } elseif (is_search()) {
        // Search results (title and meta description are translatable; hero heading uses a separate catalog string).
        $search_query = get_search_query();
        $title        = sprintf(
            /* translators: 1: Search keywords, 2: Site name (document title for search results). */
            __( 'Search Results for: %1$s - %2$s', 'zskeleton' ),
            $search_query,
            $site_name
        );
        $description = sprintf(
            /* translators: 1: Search keywords, 2: Site name. */
            __( 'Search results for "%1$s" on %2$s.', 'zskeleton' ),
            $search_query,
            $site_name
        );
        $keywords = $search_query . ', search, ' . $site_name;
        $canonical_url = get_search_link();
        
    } elseif (is_404()) {
        // 404 page
        $title = 'Page Not Found - ' . $site_name;
        $description = 'The page you are looking for could not be found.';
        $keywords = '404, page not found, ' . $site_name;
        $canonical_url = home_url('/404/');
    }
    
    // Fallback OG image
    if (empty($og_image)) {
        $og_image = get_theme_mod('zskeleton_default_og_image', '');
        if (empty($og_image)) {
            $og_image = get_site_icon_url(512);
        }
    }
    
    // Output meta tags
    echo "\n<!-- ZSkeleton SEO Meta Tags -->\n";
    
    // Basic meta tags
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    if (!empty($keywords)) {
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    }
    echo '<meta name="author" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta name="robots" content="index, follow">' . "\n";
    
    // Canonical URL
    if (!empty($canonical_url)) {
        echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
    }
    
    // Open Graph tags
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical_url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    if (!empty($og_image)) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    
    // Twitter Card tags
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    if (!empty($og_image)) {
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    }
    
    // Article specific tags
    if (is_singular() && get_post_type() === 'post') {
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
        echo '<meta property="article:author" content="' . esc_attr(get_the_author()) . '">' . "\n";
        
        // Add categories as article:section
        $categories = get_the_category();
        foreach ($categories as $category) {
            echo '<meta property="article:section" content="' . esc_attr($category->name) . '">' . "\n";
        }
        
        // Add tags as article:tag
        $tags = get_the_tags();
        if ($tags) {
            foreach ($tags as $tag) {
                echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
            }
        }
    }
    
    // Schema.org structured data
    echo '<script type="application/ld+json">' . "\n";
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => is_singular() ? 'Article' : 'WebSite',
        'name' => $title,
        'description' => $description,
        'url' => $canonical_url,
        'publisher' => array(
            '@type' => 'Organization',
            'name' => $site_name,
            'url' => $site_url
        )
    );
    
    if (is_singular() && get_post_type() === 'post') {
        $schema['author'] = array(
            '@type' => 'Person',
            'name' => get_the_author()
        );
        $schema['datePublished'] = get_the_date('c');
        $schema['dateModified'] = get_the_modified_date('c');
        if (has_post_thumbnail()) {
            $schema['image'] = get_the_post_thumbnail_url(null, 'large');
        }
    }
    
    if (!empty($og_image)) {
        $schema['image'] = $og_image;
    }
    
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
    
    echo "<!-- End ZSkeleton SEO Meta Tags -->\n\n";
}
add_action('wp_head', 'zskeleton_add_seo_meta', 1);

/**
 * Document title (title-tag theme support); avoids legacy wp_title duplication for SEO plugins.
 */
function zskeleton_document_title_parts($title) {
    if (is_home() || is_front_page()) {
        $title['title'] = get_bloginfo('name');
        $site_description = get_bloginfo('description');
        if ($site_description) {
            $title['tagline'] = $site_description;
        }
    }
    return $title;
}
add_filter('document_title_parts', 'zskeleton_document_title_parts');

/**
 * Customize password reset email to use custom reset page
 */
function zskeleton_customize_password_reset_email($message, $key, $user_login, $user_data) {
    // Create custom reset URL using your custom reset password page
    $reset_url = add_query_arg(array(
        'key' => $key,
        'login' => rawurlencode($user_login)
    ), home_url('/reset-password/'));
    
    // Customize the email message
    $message = sprintf(
        __("Hi %s,

Thank you for registering with ZSkeleton!

Please set your password by clicking the link below:

%s

This link will expire in 24 hours for security reasons.

If you didn't request this password reset, please ignore this email.

Best regards,
ZSkeleton Team", 'zskeleton'),
        $user_data->display_name ?: $user_data->user_email,
        $reset_url
    );
    
    return $message;
}
add_filter('retrieve_password_message', 'zskeleton_customize_password_reset_email', 10, 4);

/**
 * Customize password reset email subject
 */
function zskeleton_customize_password_reset_subject($subject, $user_login, $user_data) {
    return __('Welcome to ZSkeleton - Set Your Password', 'zskeleton');
}
add_filter('retrieve_password_title', 'zskeleton_customize_password_reset_subject', 10, 3);

/**
 * Redirect WordPress default password reset to custom page
 */
function zskeleton_redirect_default_password_reset() {
    global $pagenow;
    
    // Check if we're on wp-login.php with reset password action
    if ( 'wp-login.php' === $pagenow && isset( $_GET['action'] ) && 'rp' === $_GET['action'] ) {
        $key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
        $login = isset( $_GET['login'] ) ? sanitize_text_field( $_GET['login'] ) : '';
        
        if ( $key && $login ) {
            $reset_url = add_query_arg(
                array(
                    'key' => $key,
                    'login' => rawurlencode( $login )
                ),
                home_url( '/reset-password/' )
            );
            wp_redirect( $reset_url );
            exit;
        }
    }
    
    // Redirect resetpass action as well
    if ( 'wp-login.php' === $pagenow && isset( $_GET['action'] ) && 'resetpass' === $_GET['action'] ) {
        $key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
        $login = isset( $_GET['login'] ) ? sanitize_text_field( $_GET['login'] ) : '';
        
        if ( $key && $login ) {
            $reset_url = add_query_arg(
                array(
                    'key' => $key,
                    'login' => rawurlencode( $login )
                ),
                home_url( '/reset-password/' )
            );
            wp_redirect( $reset_url );
            exit;
        }
    }
}
add_action( 'init', 'zskeleton_redirect_default_password_reset' );

/**
 * Override lost password URL to use custom page
 */
function zskeleton_custom_lost_password_url( $url ) {
    return home_url( '/forgot-password/' );
}
add_filter( 'lostpassword_url', 'zskeleton_custom_lost_password_url' );

/**
 * Override network admin password reset URL
 */
function zskeleton_custom_password_reset_url( $url ) {
    if ( strpos( $url, 'action=rp' ) !== false || strpos( $url, 'action=resetpass' ) !== false ) {
        // Extract key and login parameters
        $query_args = array();
        $url_parts = wp_parse_url( $url );
        if ( isset( $url_parts['query'] ) ) {
            parse_str( $url_parts['query'], $query_args );
        }
        
        if ( isset( $query_args['key'] ) && isset( $query_args['login'] ) ) {
            return add_query_arg(
                array(
                    'key' => $query_args['key'],
                    'login' => $query_args['login']
                ),
                home_url( '/reset-password/' )
            );
        }
    }
    return $url;
}
add_filter( 'network_admin_url', 'zskeleton_custom_password_reset_url' );

/**
 * Add custom SEO meta for custom post types
 */
function zskeleton_custom_post_type_seo() {
    if ( zskeleton_has_active_seo_plugin() ) {
        return;
    }
    if (is_singular(array('zskeleton_faqs'))) {
        global $post;
        
        // Get post type labels
        $post_type_obj = get_post_type_object(get_post_type());
        $post_type_name = $post_type_obj->labels->singular_name;
        
        // Custom meta description for custom post types
        $custom_description = '';
        
        if (get_post_type() === 'zskeleton_faqs') {
            $custom_description = 'FAQ: ' . get_the_title() . '. Find quick answers and support information.';
        }
        
        // Override the description
        echo '<meta name="description" content="' . esc_attr($custom_description) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($custom_description) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($custom_description) . '">' . "\n";
        
        // Add custom keywords
        $custom_keywords = $post_type_name . ', ' . get_the_title() . ', ZSkeleton, FAQ, support';
        echo '<meta name="keywords" content="' . esc_attr($custom_keywords) . '">' . "\n";
    }
}
add_action('wp_head', 'zskeleton_custom_post_type_seo', 2);

/**
 * Add breadcrumb structured data
 */
function zskeleton_breadcrumb_schema() {
    if ( zskeleton_has_active_seo_plugin() ) {
        return;
    }
    if (is_front_page()) {
        return;
    }
    
    $breadcrumbs = array();
    $breadcrumbs[] = array(
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => home_url('/')
    );
    
    $position = 2;
    
    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $term->name,
            'item' => get_term_link($term)
        );
    } elseif (is_singular()) {
        // Add parent pages
        $post = get_post();
        $parents = array();
        $parent = $post->post_parent;
        
        while ($parent) {
            $parents[] = $parent;
            $parent = get_post($parent)->post_parent;
        }
        
        $parents = array_reverse($parents);
        foreach ($parents as $parent_id) {
            $parent_post = get_post($parent_id);
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $parent_post->post_title,
                'item' => get_permalink($parent_id)
            );
        }
        
        // Add current page
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title(),
            'item' => get_permalink()
        );
    } elseif (is_archive()) {
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_archive_title(),
            'item' => get_pagenum_link()
        );
    }
    
    if (count($breadcrumbs) > 1) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs
        );
        
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
    }
}
add_action('wp_head', 'zskeleton_breadcrumb_schema', 3);

/**
 * Breadcrumb trail for static pages: Home → parent pages → current (current has no URL).
 *
 * @param int|null $post_id Post ID or null for the main queried object.
 * @return array<int, array{label: string, url: string, current?: bool}>
 */
function zskeleton_get_page_breadcrumb_items($post_id = null) {
    if (null === $post_id) {
        $post_id = get_queried_object_id();
    }
    if (!$post_id) {
        return array();
    }
    $post = get_post($post_id);
    if (!$post || 'page' !== $post->post_type) {
        return array();
    }

    $items = array();
    $items[] = array(
        'label' => __('Home', 'zskeleton'),
        'url'   => home_url('/'),
    );

    foreach (array_reverse(get_post_ancestors($post_id)) as $ancestor_id) {
        $items[] = array(
            'label' => get_the_title($ancestor_id),
            'url'   => get_permalink($ancestor_id),
        );
    }

    $items[] = array(
        'label'   => get_the_title($post),
        'url'     => '',
        'current' => true,
    );

    return $items;
}

/**
 * Blog breadcrumb markup with SEO-plugin-first strategy and safe fallback.
 *
 * @param int $post_id Post ID for single post fallback crumbs.
 * @return string HTML.
 */
function zskeleton_get_blog_breadcrumbs_html( $post_id = 0 ) {
    $post_id = (int) $post_id;
    if ( function_exists( 'yoast_breadcrumb' ) ) {
        return yoast_breadcrumb( '<nav class="zskeleton-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'zskeleton' ) . '">', '</nav>', false );
    }

    if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
        ob_start();
        rank_math_the_breadcrumbs(
            array(
                'wrap_before' => '<nav class="zskeleton-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'zskeleton' ) . '">',
                'wrap_after'  => '</nav>',
            )
        );
        return (string) ob_get_clean();
    }

    $items = array(
        array(
            'label' => __( 'Home', 'zskeleton' ),
            'url'   => home_url( '/' ),
        ),
    );
    if ( $post_id > 0 ) {
        $terms = get_the_terms( $post_id, 'category' );
        if ( is_array( $terms ) && ! empty( $terms ) ) {
            $term_link = get_term_link( $terms[0] );
            $items[] = array(
                'label' => $terms[0]->name,
                'url'   => ! is_wp_error( $term_link ) ? $term_link : '',
            );
        }
        $items[] = array(
            'label'   => get_the_title( $post_id ),
            'url'     => '',
            'current' => true,
        );
    }

    $html = '<nav class="zskeleton-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'zskeleton' ) . '"><ol class="zskeleton-breadcrumbs__list">';
    foreach ( $items as $item ) {
        $label = isset( $item['label'] ) ? (string) $item['label'] : '';
        $url   = isset( $item['url'] ) ? (string) $item['url'] : '';
        if ( '' === trim( $label ) ) {
            continue;
        }
        $html .= '<li class="zskeleton-breadcrumbs__item">';
        if ( '' !== $url ) {
            $html .= '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
        } else {
            $html .= '<span aria-current="page">' . esc_html( $label ) . '</span>';
        }
        $html .= '</li>';
    }
    $html .= '</ol></nav>';

    return $html;
}

/**
 * Centered title bar with breadcrumbs (primary theme color, white text).
 *
 * @param array<string, mixed> $args Optional. Passed to template-parts/page-title-bar.php.
 */
function zskeleton_the_page_title_bar($args = array()) {
    if (is_front_page()) {
        return;
    }
    get_template_part('template-parts/page-title-bar', null, $args);
}

/**
 * Page ID for breadcrumbs and access checks on the current front-end view.
 * Covers singular pages and the static “Posts page” (blog index).
 *
 * @return int Post ID or 0.
 */
function zskeleton_get_context_page_id() {
    if (is_home() && !is_front_page()) {
        return (int) get_option('page_for_posts');
    }
    if (is_singular('page')) {
        return (int) get_queried_object_id();
    }
    return 0;
}

/**
 * Whether to show the members-only badge in the title bar (same rules as default page.php).
 *
 * @param int $post_id Page ID.
 */
function zskeleton_get_page_member_only_badge($post_id) {
    $post_id = (int) $post_id;
    if ($post_id <= 0 || !class_exists('ZSkeleton_Access_Control')) {
        return false;
    }
    if (function_exists('zskeleton_page_is_editor_only_legal_template') && zskeleton_page_is_editor_only_legal_template($post_id)) {
        return false;
    }
    $access_control = new ZSkeleton_Access_Control();
    $has_access = $access_control->user_has_content_access(get_current_user_id(), $post_id);
    return !$has_access && !current_user_can('administrator');
}

/**
 * Page title bar for ZSkeleton companion plugins (SEO Agency Kit, etc.).
 * Wraps zskeleton_the_page_title_bar with defaults suited to plugin-managed layouts.
 *
 * @param array<string, mixed> $args {
 *     @type int    $post_id            Context page ID (use zskeleton_get_context_page_id() when unsure).
 *     @type string $title              H1 text; empty falls back to the page title for $post_id.
 *     @type string $subtitle           Optional plain-text subtitle.
 *     @type bool   $show_meta          Published/updated dates. Default true (matches page.php).
 *     @type bool   $show_breadcrumbs   Default true.
 *     @type bool   $member_only_badge  Default resolved via zskeleton_get_page_member_only_badge( $post_id ).
 * }
 */
function zskeleton_the_plugin_page_title_bar($args = array()) {
    if (!function_exists('zskeleton_the_page_title_bar')) {
        return;
    }
    $post_id = isset($args['post_id']) ? (int) $args['post_id'] : 0;
    $defaults = array(
        'show_meta'         => true,
        'show_breadcrumbs'  => true,
        'member_only_badge' => $post_id ? zskeleton_get_page_member_only_badge($post_id) : false,
    );
    $args = wp_parse_args($args, $defaults);
    if ($post_id > 0 && (!isset($args['title']) || '' === $args['title'])) {
        $args['title'] = get_the_title($post_id);
    }
    zskeleton_the_page_title_bar($args);
}

/**
 * Add robots meta tag based on content
 */
function zskeleton_robots_meta() {
    if ( zskeleton_has_active_seo_plugin() ) {
        return;
    }
    $robots = 'index, follow';
    
    // Don't index search results or 404 pages
    if (is_search() || is_404()) {
        $robots = 'noindex, follow';
    }
    
    // Don't index paginated pages beyond page 2
    if (is_paged() && get_query_var('paged') > 2) {
        $robots = 'noindex, follow';
    }
    
    // Don't index password protected posts
    if (is_singular() && post_password_required()) {
        $robots = 'noindex, nofollow';
    }
    
    echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
}
add_action('wp_head', 'zskeleton_robots_meta', 1);

/**
 * Generate XML sitemap
 */
function zskeleton_generate_sitemap() {
    if ( zskeleton_has_active_seo_plugin() || function_exists( 'wp_sitemaps_get_server' ) ) {
        return;
    }
    if (!isset($_GET['sitemap']) || $_GET['sitemap'] !== 'xml') {
        return;
    }
    
    header('Content-Type: application/xml; charset=utf-8');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Homepage
    echo '<url>' . "\n";
    echo '<loc>' . esc_url(home_url('/')) . '</loc>' . "\n";
    echo '<lastmod>' . date('c') . '</lastmod>' . "\n";
    echo '<changefreq>daily</changefreq>' . "\n";
    echo '<priority>1.0</priority>' . "\n";
    echo '</url>' . "\n";
    
    // Posts
    $posts = get_posts(array(
        'numberposts' => -1,
        'post_status' => 'publish',
        'post_type' => array('post', 'page', 'zskeleton_faqs')
    ));
    
    foreach ($posts as $post) {
        echo '<url>' . "\n";
        echo '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>' . "\n";
        echo '<lastmod>' . date('c', strtotime($post->post_modified)) . '</lastmod>' . "\n";
        echo '<changefreq>weekly</changefreq>' . "\n";
        echo '<priority>0.8</priority>' . "\n";
        echo '</url>' . "\n";
    }
    
    // Pages
    $pages = get_pages(array(
        'number' => 0,
        'post_status' => 'publish'
    ));
    
    foreach ($pages as $page) {
        echo '<url>' . "\n";
        echo '<loc>' . esc_url(get_permalink($page->ID)) . '</loc>' . "\n";
        echo '<lastmod>' . date('c', strtotime($page->post_modified)) . '</lastmod>' . "\n";
        echo '<changefreq>monthly</changefreq>' . "\n";
        echo '<priority>0.6</priority>' . "\n";
        echo '</url>' . "\n";
    }
    
    // Categories
    $categories = get_categories(array('hide_empty' => true));
    foreach ($categories as $category) {
        echo '<url>' . "\n";
        echo '<loc>' . esc_url(get_category_link($category->term_id)) . '</loc>' . "\n";
        echo '<lastmod>' . date('c') . '</lastmod>' . "\n";
        echo '<changefreq>weekly</changefreq>' . "\n";
        echo '<priority>0.5</priority>' . "\n";
        echo '</url>' . "\n";
    }
    
    echo '</urlset>';
    exit;
}
add_action('init', 'zskeleton_generate_sitemap');

/**
 * Theme setup and initialization
 */
function zskeleton_theme_setup() {
    // Add theme support for various features
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    // Add support for responsive embedded content
    add_theme_support('responsive-embeds');
    
    // Add support for editor styles.
    add_theme_support('editor-styles');
    $editor_style = 'assets/css/editor-style.css';
    if ( (bool) get_option( 'zskeleton_use_minified_assets', true )
        && is_readable( ZSkeleton_THEME_DIR . '/assets/css/editor-style.min.css' ) ) {
        $editor_style = 'assets/css/editor-style.min.css';
    }
    add_editor_style( $editor_style );
    
    // Add support for wide and full alignment
    add_theme_support('align-wide');
    
    // Add support for block styles
    add_theme_support('wp-block-styles');
    
    // Add support for custom units
    add_theme_support('custom-units');
    
    // Add support for custom line heights
    add_theme_support('custom-line-height');
    
    // Add support for custom spacing
    add_theme_support('custom-spacing');

    // Default feed links in head; Customizer selective refresh for widgets (plugin-friendly)
    add_theme_support('automatic-feed-links');
    add_theme_support('customize-selective-refresh-widgets');

    // WooCommerce storefront integration when the plugin is active
    if (class_exists('WooCommerce')) {
        add_theme_support('woocommerce');
    }
    
    // Register navigation menus
    $footer_nav_label = __( 'Footer Navigation', 'zskeleton' );
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'zskeleton'),
        'header_nav_right' => __('Header — right of logo (split header only)', 'zskeleton'),
        'secondary_subnav' => __('Secondary Sub-Navigation', 'zskeleton'),
        'footer' => sprintf( '%s %d', $footer_nav_label, 1 ),
        // Footer menu locations (use in widgets via Theme Location)
        'footer-1' => sprintf( '%s %d', $footer_nav_label, 2 ),
        'footer-2' => sprintf( '%s %d', $footer_nav_label, 3 ),
        'footer-3' => sprintf( '%s %d', $footer_nav_label, 4 ),
        'footer-4' => sprintf( '%s %d', $footer_nav_label, 5 ),
        'member' => __('Member Navigation', 'zskeleton'),
    ));
    
    // Content width for embeds and media (must be global)
    global $content_width;
    if (!isset($content_width)) {
        $content_width = 800;
    }
}
add_action('after_setup_theme', 'zskeleton_theme_setup');

/**
 * Header layout options for the theme settings UI and validation.
 *
 * Add or override entries with the {@see 'zskeleton_header_layout_choices'} filter.
 *
 * @return array<string, string> Slug => admin label.
 */
function zskeleton_get_header_layout_choices(): array {
    $choices = array(
        'default'          => __( 'Default (tagline bar + logo row + search toggle)', 'zskeleton' ),
        'split_top_search' => __( 'Split: top search & socials, logo centered between menus', 'zskeleton' ),
    );

    return apply_filters( 'zskeleton_header_layout_choices', $choices );
}

/**
 * Header layout slug from ZSkeleton Settings → Layout.
 *
 * @return string One of the keys from {@see zskeleton_get_header_layout_choices()}.
 */
function zskeleton_get_header_layout(): string {
    $layout  = (string) get_option( 'zskeleton_header_layout', 'default' );
    $allowed = array_keys( zskeleton_get_header_layout_choices() );

    return in_array( $layout, $allowed, true ) ? $layout : 'default';
}

/**
 * Mobile menu toggle icon style from Appearance settings.
 *
 * @return string Either style1 or style2.
 */
function zskeleton_get_mobile_menu_button_style(): string {
    $raw = (string) get_option( 'zskeleton_mobile_menu_button_style', 'style1' );
    if ( function_exists( 'zskeleton_sanitize_option_mobile_menu_button_style' ) ) {
        return zskeleton_sanitize_option_mobile_menu_button_style( $raw );
    }
    return in_array( $raw, array( 'style1', 'style2' ), true ) ? $raw : 'style1';
}

/**
 * Mobile slide-out drawer layout from Appearance settings (tabs vs items-only list).
 *
 * @return string style1|style2
 */
function zskeleton_get_mobile_menu_panel_style(): string {
	$raw = (string) get_option( 'zskeleton_mobile_menu_panel_style', 'style1' );
	if ( function_exists( 'zskeleton_sanitize_option_mobile_menu_panel_style' ) ) {
		$out = zskeleton_sanitize_option_mobile_menu_panel_style( $raw );
	} else {
		$v   = strtolower( trim( $raw ) );
		$out = in_array( $v, array( 'style1', 'style2' ), true ) ? $v : 'style1';
	}
	/**
	 * Filter the mobile slide-out menu panel style slug.
	 *
	 * @param string $out style1|style2
	 */
	return (string) apply_filters( 'zskeleton_mobile_menu_panel_style', $out );
}

/**
 * Mobile slide-out drawer width mode from Appearance settings.
 *
 * @return string default|full
 */
function zskeleton_get_mobile_menu_drawer_width_mode(): string {
	$raw = (string) get_option( 'zskeleton_mobile_menu_drawer_width', 'default' );
	if ( function_exists( 'zskeleton_sanitize_option_mobile_menu_drawer_width' ) ) {
		$out = zskeleton_sanitize_option_mobile_menu_drawer_width( $raw );
	} else {
		$v   = strtolower( trim( $raw ) );
		$out = in_array( $v, array( 'default', 'full' ), true ) ? $v : 'default';
	}
	/**
	 * Filter the mobile slide-out drawer width mode.
	 *
	 * @param string $out default|full
	 */
	return (string) apply_filters( 'zskeleton_mobile_menu_drawer_width_mode', $out );
}

/**
 * Body class for split header layout (scoped CSS).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function zskeleton_body_class_header_layout( array $classes ): array {
    if ( 'split_top_search' === zskeleton_get_header_layout() ) {
        $classes[] = 'zskeleton-header-split-top-search';
    }
    return $classes;
}
add_filter( 'body_class', 'zskeleton_body_class_header_layout' );

/**
 * Renders a compact WPML language switcher for the split header top bar (flags + native name + dropdown).
 * Outputs nothing when WPML is inactive or only one language is available.
 *
 * @return void
 */
function zskeleton_render_split_header_wpml_switcher(): void {
	if ( ! has_filter( 'wpml_active_languages' ) ) {
		return;
	}

	$languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
	if ( ( empty( $languages ) || ! is_array( $languages ) ) && function_exists( 'icl_get_languages' ) ) {
		$languages = icl_get_languages( 'skip_missing=0' );
	}
	if ( empty( $languages ) || ! is_array( $languages ) || count( $languages ) < 2 ) {
		return;
	}

	$active = null;
	$active_code = '';
	foreach ( $languages as $code => $lang ) {
		if ( ! empty( $lang['active'] ) ) {
			$active = $lang;
			$active_code = (string) $code;
			break;
		}
	}
	if ( null === $active ) {
		$active_code = (string) array_key_first( $languages );
		$active = $languages[ $active_code ];
	}

	$flag_url = isset( $active['country_flag_url'] ) ? (string) $active['country_flag_url'] : '';
	$label = isset( $active['native_name'] ) && '' !== $active['native_name']
		? (string) $active['native_name']
		: strtoupper( $active_code );

	echo '<div class="header-topbar-wpml" role="navigation" aria-label="' . esc_attr__( 'Languages', 'zskeleton' ) . '">';
	echo '<details class="header-topbar-wpml__dropdown">';
	echo '<summary class="header-topbar-wpml__summary">';
	if ( '' !== $flag_url ) {
		printf(
			'<img class="header-topbar-wpml__flag" src="%s" width="20" height="14" alt="" decoding="async" loading="lazy" />',
			esc_url( $flag_url )
		);
	}
	printf(
		'<span class="header-topbar-wpml__label">%s</span>',
		esc_html( $label )
	);
	echo '<span class="header-topbar-wpml__caret" aria-hidden="true"></span>';
	echo '</summary>';
	echo '<ul class="header-topbar-wpml__list" role="list">';

	foreach ( $languages as $code => $lang ) {
		$url = isset( $lang['url'] ) ? (string) $lang['url'] : '';
		if ( '' === $url ) {
			continue;
		}
		$is_active = ! empty( $lang['active'] );
		$name = isset( $lang['native_name'] ) && '' !== $lang['native_name']
			? (string) $lang['native_name']
			: strtoupper( (string) $code );
		$f = isset( $lang['country_flag_url'] ) ? (string) $lang['country_flag_url'] : '';

		echo '<li class="header-topbar-wpml__item' . ( $is_active ? ' is-active' : '' ) . '">';
		if ( $is_active ) {
			echo '<span class="header-topbar-wpml__current">';
			if ( '' !== $f ) {
				printf(
					'<img class="header-topbar-wpml__flag" src="%s" width="20" height="14" alt="" decoding="async" loading="lazy" />',
					esc_url( $f )
				);
			}
			printf( '<span>%s</span>', esc_html( $name ) );
			echo '</span>';
		} else {
			echo '<a class="header-topbar-wpml__link" href="' . esc_url( $url ) . '">';
			if ( '' !== $f ) {
				printf(
					'<img class="header-topbar-wpml__flag" src="%s" width="20" height="14" alt="" decoding="async" loading="lazy" />',
					esc_url( $f )
				);
			}
			printf( '<span>%s</span>', esc_html( $name ) );
			echo '</a>';
		}
		echo '</li>';
	}

	echo '</ul></details></div>';
}

/**
 * Template extension points for plugins and child themes:
 * - zskeleton_after_body_open  (header.php, after wp_body_open)
 * - zskeleton_before_main_content (page.php, single.php — inside main, before layout)
 * - zskeleton_after_main_content  (page.php, single.php — inside main, before </main>)
 */

/**
 * True when a menu is assigned to Secondary Sub-Navigation and has at least one item.
 */
function zskeleton_secondary_subnav_has_items() {
    if (!has_nav_menu('secondary_subnav')) {
        return false;
    }
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations['secondary_subnav']) ? (int) $locations['secondary_subnav'] : 0;
    if (!$menu_id) {
        return false;
    }
    $items = wp_get_nav_menu_items($menu_id);
    return !empty($items);
}

/**
 * Keep horizontal subnav styles/JS working for wp_nav_menu output.
 */
function zskeleton_secondary_subnav_menu_item_class($classes, $item, $args, $depth) {
    if (is_object($args) && !empty($args->theme_location) && $args->theme_location === 'secondary_subnav') {
        $classes[] = 'subnav-item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'zskeleton_secondary_subnav_menu_item_class', 10, 4);

// Flush rewrite rules on theme activation
add_action('after_switch_theme', 'zskeleton_flush_rewrite_rules');
function zskeleton_flush_rewrite_rules() {
    flush_rewrite_rules();
}

/**
 * Flush rewrite rules when new post types are added
 * This is a one-time flush to ensure new post types work immediately
 */
function zskeleton_flush_rewrite_rules_once() {
    if (!get_option('zskeleton_rewrite_flushed_v2')) {
        flush_rewrite_rules();
        update_option('zskeleton_rewrite_flushed_v2', true);
    }
}
add_action('init', 'zskeleton_flush_rewrite_rules_once', 999);

/**
 * Add body classes for user state
 */
function zskeleton_body_class($classes) {
    if (is_user_logged_in()) {
        $classes[] = 'logged-in-user';
        
        $user_id = get_current_user_id();
        if (class_exists('ZSkeleton_User_Profile_Fields') && 
            ZSkeleton_User_Profile_Fields::user_has_active_membership($user_id)) {
            $classes[] = 'has-membership';
            
            $membership_type = get_user_meta($user_id, 'zskeleton_membership_type', true);
            if ($membership_type) {
                $classes[] = 'membership-' . $membership_type;
            }
        }
    } else {
        $classes[] = 'logged-out-user';
    }
    
    return $classes;
}
add_filter('body_class', 'zskeleton_body_class');

/**
 * Dequeue jQuery Migrate for better performance
 */
function zskeleton_dequeue_jquery_migrate($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            $script->deps = array_diff($script->deps, array('jquery-migrate'));
        }
    }
}
add_action('wp_default_scripts', 'zskeleton_dequeue_jquery_migrate');

/**
 * WordPress Built-in Sitemaps (WordPress 5.5+)
 * 
 * WordPress automatically generates XML sitemaps for all public post types.
 * All ZSkeleton custom post types are registered as public and are automatically
 * included in the sitemaps.
 * 
 * HOW TO ACCESS SITEMAPS:
 * 
 * Main Sitemap Index:
 * {your-site-url}/wp-sitemap.xml
 * 
 * Individual Post Type Sitemaps:
 * - FAQs: {your-site-url}/wp-sitemap-post_type-zskeleton_faqs-1.xml
 * 
 * Taxonomy Sitemaps:
 * - Taxonomy archives are automatically included at:
 *   {your-site-url}/wp-sitemap-taxonomy-{taxonomy_name}-1.xml
 * 
 * Page Sitemaps:
 * - Pages: {your-site-url}/wp-sitemap-post_type-page-1.xml
 * 
 * All custom post types are automatically included if they have:
 * - 'public' => true
 * - 'publicly_queryable' => true
 * 
 * Custom post types are split across multiple sitemap files if they have
 * more than 2000 items (WordPress default limit).
 */

/**
 * Strip ?ver= only for theme styles (zskeleton-*). Preserves full query strings for plugins and Google Fonts.
 *
 * @param string $src    Source URL.
 * @param string $handle Style handle.
 * @return string
 */
function zskeleton_maybe_strip_style_ver($src, $handle) {
    if (strpos($src, 'fonts.googleapis.com') !== false) {
        return $src;
    }
    if ($handle === 'zskeleton-google-fonts') {
        return $src;
    }
    if (strpos((string) $handle, 'zskeleton-') !== 0) {
        return $src;
    }
    if (strpos($src, '?') === false) {
        return $src;
    }
    return remove_query_arg('ver', $src);
}
add_filter('style_loader_src', 'zskeleton_maybe_strip_style_ver', 10, 2);

/**
 * Strip ?ver= only for theme scripts (zskeleton-*). Preserves query strings for plugins (e.g. API params).
 *
 * @param string $src    Source URL.
 * @param string $handle Script handle.
 * @return string
 */
function zskeleton_maybe_strip_script_ver($src, $handle) {
    if (strpos($src, 'recaptcha') !== false || strpos($src, 'challenges.cloudflare.com/turnstile') !== false) {
        return $src;
    }
    if (strpos((string) $handle, 'zskeleton-') !== 0) {
        return $src;
    }
    if (strpos($src, '?') === false) {
        return $src;
    }
    return remove_query_arg('ver', $src);
}
add_filter('script_loader_src', 'zskeleton_maybe_strip_script_ver', 10, 2);

/**
 * Defer jQuery only when explicitly enabled (default off for plugin compatibility).
 * reCAPTCHA stays synchronous when enabled.
 */
function zskeleton_defer_jquery($tag, $handle, $src) {
    if ('jquery-core' !== $handle) {
        return $tag;
    }
    if (!apply_filters('zskeleton_defer_jquery', false)) {
        return $tag;
    }
    if (class_exists('ZSkeleton_ReCAPTCHA') && function_exists('zskeleton_recaptcha')) {
        $recaptcha = zskeleton_recaptcha();
        if ($recaptcha && $recaptcha->is_enabled() && 'google_recaptcha' === $recaptcha->get_provider()) {
            return $tag;
        }
    }
    return str_replace('<script ', '<script defer ', $tag);
}
add_filter('script_loader_tag', 'zskeleton_defer_jquery', 10, 3);

/**
 * Defer non-critical scripts for better performance
 */
function zskeleton_defer_non_critical_scripts($tag, $handle, $src) {
    // Scripts to defer (non-critical)
    $defer_scripts = array(
        'zskeleton-main',
        'zskeleton-membership',
        'zskeleton-slider',
        'comment-reply'
    );
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace('<script ', '<script defer ', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'zskeleton_defer_non_critical_scripts', 10, 3);

/**
 * Enqueue scripts and styles
 */
function zskeleton_enqueue_assets() {
    // Locale-aware Google Fonts (Arabic vs non-Arabic).
    $google_fonts_url = zskeleton_get_google_fonts_url_for_locale();
    if (!empty($google_fonts_url)) {
        wp_enqueue_style(
            'zskeleton-google-fonts',
            $google_fonts_url,
            array(),
            null
        );
    }
    
    // Check if minified assets should be used
    $use_minified = get_option('zskeleton_use_minified_assets', true);
    
    // Main stylesheet - use minified or original based on setting
    $main_css_file = $use_minified ? 'style.min.css' : 'style.css';
    $main_css_url = $use_minified ? ZSkeleton_THEME_URL . '/assets/css/' . $main_css_file : get_stylesheet_uri();
    $main_css_path = $use_minified ? ZSkeleton_THEME_DIR . '/assets/css/' . $main_css_file : get_stylesheet_directory() . '/style.css';
    $main_css_ver = is_readable( $main_css_path ) ? (string) filemtime( $main_css_path ) : ZSkeleton_VERSION;
    wp_enqueue_style(
        'zskeleton-style',
        $main_css_url,
        array('zskeleton-google-fonts'),
        $main_css_ver
    );
    
    // Component styles - use minified or original based on setting
    $components_css_file = $use_minified ? 'components.min.css' : 'components.css';
    $components_path     = ZSkeleton_THEME_DIR . '/assets/css/' . $components_css_file;
    $components_ver      = is_readable($components_path) ? (string) filemtime($components_path) : ZSkeleton_VERSION;
    wp_enqueue_style(
        'zskeleton-components',
        ZSkeleton_THEME_URL . '/assets/css/' . $components_css_file,
        array('zskeleton-style'),
        $components_ver
    );

    $widgets_basename = ( $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/widgets-zskeleton.min.css' ) )
        ? 'widgets-zskeleton.min.css'
        : 'widgets-zskeleton.css';
    $widgets_path      = ZSkeleton_THEME_DIR . '/assets/css/' . $widgets_basename;
    if ( is_readable( $widgets_path ) ) {
        wp_enqueue_style(
            'zskeleton-widgets',
            ZSkeleton_THEME_URL . '/assets/css/' . $widgets_basename,
            array( 'zskeleton-components' ),
            (string) filemtime( $widgets_path )
        );
    }

    // Page title bar (own file + filemtime) so RTL breadcrumbs/title bar are not dropped by stale components cache
    $page_title_bar_file = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/page-title-bar.min.css' )
        ? 'page-title-bar.min.css'
        : 'page-title-bar.css';
    $page_title_bar_path = ZSkeleton_THEME_DIR . '/assets/css/' . $page_title_bar_file;
    if (is_readable($page_title_bar_path)) {
        wp_enqueue_style(
            'zskeleton-page-title-bar',
            ZSkeleton_THEME_URL . '/assets/css/' . $page_title_bar_file,
            array('zskeleton-style'),
            (string) filemtime($page_title_bar_path)
        );
    }

    // Apply selected locale font families globally (inputs, footer, SEO template, etc.).
    $font_family        = zskeleton_get_font_family_for_current_locale();
    $safe_font_family   = !empty($font_family) ? wp_strip_all_tags($font_family) : '';
    $system_font_stack  = 'Arial,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif';
    $base_var           = $safe_font_family !== '' ? '"' . esc_attr($safe_font_family) . '",sans-serif' : $system_font_stack;
    $heading_var        = $safe_font_family !== '' ? '"' . esc_attr($safe_font_family) . '",sans-serif' : $system_font_stack;

    $font_css  = ':root{--zskeleton-font-family-base:' . $base_var . ';--zskeleton-font-family-headings:' . $heading_var . ';}';
    $font_css .= 'html,body,.site,#page,#content,button,input,select,textarea,optgroup,.btn,.subnav,.site-footer,.newsletter-section,.seo-ar-homepage,.header-top,.header-main,.site-header,.mobile-bottom-nav,.mobile-bottom-nav--style2,.zskeleton-mbn2{font-family:var(--zskeleton-font-family-base);}';
    $font_css .= 'h1,h2,h3,h4,h5,h6,.site-footer h1,.site-footer h2,.site-footer h3,.newsletter-section h2,.site-footer .footer-widget-title{font-family:var(--zskeleton-font-family-headings);}';
    $font_css .= '.academic-content,.academic-content h1,.academic-content h2,.academic-content h3{font-family:var(--zskeleton-font-family-base);}';

    // RTL layout fixes: ensure sidebar stays on the expected side for flex/grid wrappers.
    // WordPress sets `dir="rtl"` on <html> via `language_attributes()`. Some setups also add `rtl` to <body>.
    // Scope to desktop so we don't fight the templates' mobile breakpoint (flex-direction: column).
    $font_css .= '@media (min-width:769px){html[dir="rtl"] .page-layout,body.rtl .page-layout{display:flex;flex-direction:row-reverse;}}';
    $font_css .= '@media (min-width:769px){html[dir="rtl"] .content-sidebar-layout,body.rtl .content-sidebar-layout{grid-template-columns:1fr 350px;}}';
    $font_css .= '@media (max-width:1200px) and (min-width:769px){html[dir="rtl"] .content-sidebar-layout,body.rtl .content-sidebar-layout{grid-template-columns:1fr 320px;}}';
    $font_css .= '@media (max-width:1024px) and (min-width:769px){html[dir="rtl"] .content-sidebar-layout,body.rtl .content-sidebar-layout{grid-template-columns:1fr 300px;}}';

    // Split header logo controls (Appearance -> Layout).
    $split_logo_height = max( 24, min( 120, (int) get_option( 'zskeleton_split_logo_height', 56 ) ) );
    $split_logo_side_padding = max( 0, min( 180, (int) get_option( 'zskeleton_split_logo_side_padding', 72 ) ) );
    $font_css .= ':root{--zskeleton-split-logo-max-height:' . $split_logo_height . 'px;--zskeleton-split-logo-side-padding:' . $split_logo_side_padding . 'px;}';
    $font_css .= '@media (min-width:769px){.site-header--split-top-search .menu-item-logo-split .custom-logo,.site-header--split-top-search .menu-item-logo-split .custom-logo.desktop-logo,.site-header--split-top-search .menu-item-logo-split .custom-logo.mobile-logo,.site-header--split-top-search .header-main-split--no-right-nav .menu-item-logo-split .custom-logo{max-height:var(--zskeleton-split-logo-max-height);min-height:0;height:auto;}.site-header--split-top-search .header-nav--left .nav-menu{padding-inline-end:var(--zskeleton-split-logo-side-padding);}.site-header--split-top-search .header-nav--right .nav-menu{padding-inline-start:var(--zskeleton-split-logo-side-padding);}}';

    $color_css = function_exists( 'zskeleton_get_theme_color_css_variables' ) ? zskeleton_get_theme_color_css_variables() : '';

    wp_add_inline_style( 'zskeleton-style', $font_css . $color_css );
    
    // Main JavaScript - use minified or original based on setting
    $main_js_file = $use_minified ? 'main.min.js' : 'main.js';
    $main_js_path = ZSkeleton_THEME_DIR . '/assets/js/' . $main_js_file;
    wp_enqueue_script(
        'zskeleton-main',
        ZSkeleton_THEME_URL . '/assets/js/' . $main_js_file,
        array('jquery'),
        is_readable( $main_js_path ) ? (string) filemtime( $main_js_path ) : ZSkeleton_VERSION,
        true
    );
    
    // Membership JavaScript - use minified or original based on setting
    $membership_js_file = $use_minified ? 'membership.min.js' : 'membership.js';
    $membership_js_path = ZSkeleton_THEME_DIR . '/assets/js/' . $membership_js_file;
    wp_enqueue_script(
        'zskeleton-membership',
        ZSkeleton_THEME_URL . '/assets/js/' . $membership_js_file,
        array('jquery'),
        is_readable( $membership_js_path ) ? (string) filemtime( $membership_js_path ) : ZSkeleton_VERSION,
        true
    );
    
    // Localize scripts
    wp_localize_script('zskeleton-main', 'zskeletonAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('zskeleton_nonce'),
        'strings' => array(
            'loading' => __('Loading...', 'zskeleton'),
            'error' => __('An error occurred. Please try again.', 'zskeleton'),
            'member_required' => __('Membership required to access this content.', 'zskeleton'),
        )
    ));
    
    // Comments script for threaded comments
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'zskeleton_enqueue_assets');

/**
 * WooCommerce table/layout fixes (cart totals, checkout review order, RTL).
 */
function zskeleton_enqueue_woocommerce_compat_css(): void {
	if ( is_admin() || ! class_exists( 'WooCommerce', false ) ) {
		return;
	}

	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/woocommerce-compat.min.css' )
		? 'woocommerce-compat.min.css'
		: 'woocommerce-compat.css';
	$path         = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		return;
	}

	$deps = array( 'zskeleton-style' );
	if ( wp_style_is( 'woocommerce-general', 'registered' ) ) {
		$deps[] = 'woocommerce-general';
	}

	wp_enqueue_style(
		'zskeleton-woocommerce-compat',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		$deps,
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_woocommerce_compat_css', 25 );

/**
 * Styles for membership pricing cards (memberships page + homepage templates).
 */
function zskeleton_enqueue_membership_plans_pricing_css() {
    if (is_admin()) {
        return;
    }

    if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && ! zskeleton_is_memberships_feature_enabled() ) {
        return;
    }

    $load = is_page_template('page-memberships.php') || is_page_template('page-home-seo-ar.php') || is_page_template('page-seo-expert.php');

    if (!$load && is_front_page() && is_page()) {
        $tpl = get_page_template_slug(get_queried_object_id());
        $load = ('' === $tpl || false === $tpl || 'default' === $tpl);
    }

    if (!$load) {
        return;
    }

    $use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
    $pricing_file = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/membership-plans-pricing.min.css' )
        ? 'membership-plans-pricing.min.css'
        : 'membership-plans-pricing.css';
    $pricing_path = ZSkeleton_THEME_DIR . '/assets/css/' . $pricing_file;

    wp_enqueue_style(
        'zskeleton-membership-plans-pricing',
        ZSkeleton_THEME_URL . '/assets/css/' . $pricing_file,
        array('zskeleton-style'),
        is_readable( $pricing_path ) ? (string) filemtime( $pricing_path ) : ZSkeleton_VERSION
    );
}
add_action('wp_enqueue_scripts', 'zskeleton_enqueue_membership_plans_pricing_css', 11);

/**
 * Register the shared navy blog hero + search stylesheet (used by index, archives, and blog listing templates).
 *
 * @return void
 */
function zskeleton_register_blog_listing_hero_style(): void {
	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/blog-listing-hero.min.css' )
		? 'blog-listing-hero.min.css'
		: 'blog-listing-hero.css';
	$path = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		$file = 'blog-listing-hero.css';
		$path = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	}
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_register_style(
		'zskeleton-blog-listing-hero',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		array(),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_register_blog_listing_hero_style', 9 );

/**
 * Enqueue blog listing hero CSS on index / search / archives and blog listing views (not only hub template).
 *
 * @return void
 */
function zskeleton_enqueue_blog_listing_hero_css(): void {
	if ( is_admin() ) {
		return;
	}
	if ( ! is_home() && ! is_search() && ! is_archive() && ! is_singular( 'post' ) && ! zskeleton_is_blog_listing_public_view() ) {
		return;
	}
	if ( ! wp_style_is( 'zskeleton-blog-listing-hero', 'registered' ) ) {
		return;
	}
	wp_enqueue_style( 'zskeleton-blog-listing-hero' );
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_blog_listing_hero_css', 11 );

/**
 * Styles for the Blog listing page template (hub sections).
 */
function zskeleton_enqueue_blog_page_css() {
	if ( is_admin()
		|| ! function_exists( 'zskeleton_should_enqueue_blog_hub_page_styles' )
		|| ! zskeleton_should_enqueue_blog_hub_page_styles() ) {
		return;
	}

	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/blog-page.min.css' )
		? 'blog-page.min.css'
		: 'blog-page.css';
	$path         = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		$file = 'blog-page.css';
		$path  = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	$deps = array( 'zskeleton-components' );
	if ( wp_style_is( 'zskeleton-blog-listing-hero', 'registered' ) ) {
		$deps[] = 'zskeleton-blog-listing-hero';
	}

	wp_enqueue_style(
		'zskeleton-blog-page',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		$deps,
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_blog_page_css', 12 );

/**
 * Shared layout and content styles for default pages (page.php) and single posts (single.php).
 *
 * @return void
 */
function zskeleton_enqueue_page_single_shared_css(): void {
	if ( is_admin() ) {
		return;
	}
	if ( ! is_page() && ! is_singular( 'post' ) ) {
		return;
	}

	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/page-single-shared.min.css' )
		? 'page-single-shared.min.css'
		: 'page-single-shared.css';
	$path         = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		$file = 'page-single-shared.css';
		$path = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'zskeleton-page-single-shared',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		array( 'zskeleton-components' ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_page_single_shared_css', 12 );

/**
 * Single post template styles (hero, featured image, tags, post navigation).
 *
 * @return void
 */
function zskeleton_enqueue_single_post_css(): void {
	if ( is_admin() || ! is_singular( 'post' ) ) {
		return;
	}

	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/single-post.min.css' )
		? 'single-post.min.css'
		: 'single-post.css';
	$path         = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		$file = 'single-post.css';
		$path = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'zskeleton-single-post',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		array( 'zskeleton-page-single-shared' ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_single_post_css', 13 );

/**
 * Styles for the Arabic SEO homepage template (page-home-seo-ar.php).
 *
 * @return void
 */
function zskeleton_enqueue_seo_home_ar_css() {
	if ( is_admin() ) {
		return;
	}
	$load_template = is_page_template( 'page-home-seo-ar.php' );
	$load_block    = false;
	if ( ! $load_template && is_singular() && function_exists( 'zskeleton_seo_ar_page_has_ai_lead_block' ) ) {
		$load_block = zskeleton_seo_ar_page_has_ai_lead_block( (int) get_queried_object_id() );
	}
	if ( ! $load_template && ! $load_block ) {
		return;
	}

	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/seo-home-ar.min.css' )
		? 'seo-home-ar.min.css'
		: 'seo-home-ar.css';
	$path = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		$file = 'seo-home-ar.css';
		$path = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'zskeleton-seo-home-ar',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		array( 'zskeleton-style' ),
		(string) filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_seo_home_ar_css', 12 );

/**
 * Enqueue membership pricing CSS when the SEO Agency Kit memberships template is used (not page-memberships.php).
 *
 * @return void
 */
function zskeleton_enqueue_membership_plans_pricing_css_forced() {
    if (is_admin()) {
        return;
    }
    if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && ! zskeleton_is_memberships_feature_enabled() ) {
        return;
    }

    $use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
    $pricing_file = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/membership-plans-pricing.min.css' )
        ? 'membership-plans-pricing.min.css'
        : 'membership-plans-pricing.css';
    $pricing_path = ZSkeleton_THEME_DIR . '/assets/css/' . $pricing_file;

    wp_enqueue_style(
        'zskeleton-membership-plans-pricing',
        ZSkeleton_THEME_URL . '/assets/css/' . $pricing_file,
        array('zskeleton-style'),
        is_readable( $pricing_path ) ? (string) filemtime( $pricing_path ) : ZSkeleton_VERSION
    );
}

/**
 * Build locale-aware Google Fonts URL based on theme settings.
 *
 * @return string
 */
function zskeleton_get_google_fonts_url_for_locale() {
    $font_query = zskeleton_get_font_query_for_current_locale();
    if (empty($font_query)) {
        return '';
    }

    // Support both "family=..." and raw family descriptors.
    if (strpos($font_query, 'family=') === false) {
        $families = array_map('trim', explode(',', $font_query));
        $families = array_filter($families);
        if (empty($families)) {
            return '';
        }
        $font_query = 'family=' . implode('&family=', $families);
    }

    return 'https://fonts.googleapis.com/css2?' . $font_query . '&display=swap';
}

/**
 * Return configured font query by locale.
 *
 * @return string
 */
function zskeleton_get_font_query_for_current_locale() {
    $is_arabic = zskeleton_is_arabic_locale();
    $font_query = $is_arabic
        ? get_option('zskeleton_google_font_arabic', 'Cairo:wght@400;500;600;700')
        : get_option('zskeleton_google_font_default', 'Inter:wght@400;500;600;700');

    return trim((string) $font_query);
}

/**
 * Return font family name for CSS usage from current locale setting.
 *
 * @return string
 */
function zskeleton_get_font_family_for_current_locale() {
    $font_query = zskeleton_get_font_query_for_current_locale();
    if (empty($font_query)) {
        return '';
    }

    // Remove optional "family=" and take the first family descriptor.
    $font_query = str_replace('family=', '', $font_query);
    $first_family = trim(explode(',', $font_query)[0]);
    if ($first_family === '') {
        return '';
    }

    // "Noto+Sans+Arabic:wght@400;700" -> "Noto Sans Arabic"
    $family_name = explode(':', $first_family)[0];
    return str_replace('+', ' ', $family_name);
}

/**
 * Check if current locale is Arabic.
 *
 * @return bool
 */
function zskeleton_is_arabic_locale() {
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    return strpos(strtolower((string) $locale), 'ar') === 0;
}

/**
 * Curated Google Fonts for Arabic locales (CSS2 family value => admin label).
 *
 * @return array<string, string>
 */
function zskeleton_get_google_font_choices_arabic() {
    return array(
        'Cairo:wght@400;500;600;700' => 'Cairo',
        'Noto+Sans+Arabic:wght@400;500;600;700' => 'Noto Sans Arabic',
        'Tajawal:wght@400;500;700;800' => 'Tajawal',
        'Almarai:wght@300;400;700;800' => 'Almarai',
        'Changa:wght@400;500;600;700' => 'Changa',
        'El+Messiri:wght@400;500;600;700' => 'El Messiri',
        'IBM+Plex+Sans+Arabic:wght@400;500;600;700' => 'IBM Plex Sans Arabic',
        'Amiri:wght@400;700' => 'Amiri',
        'Lateef:wght@400;700' => 'Lateef',
        'Markazi+Text:wght@400;500;600;700' => 'Markazi Text',
        'Mada:wght@400;500;600;700' => 'Mada',
        'Reem+Kufi:wght@400;500;600;700' => 'Reem Kufi',
        'Rubik:wght@400;500;600;700' => 'Rubik',
        'Vazirmatn:wght@400;500;600;700' => 'Vazirmatn',
        'Lexend:wght@400;500;600;700' => 'Lexend',
    );
}

/**
 * Curated Google Fonts for non-Arabic locales (CSS2 family value => admin label).
 *
 * @return array<string, string>
 */
function zskeleton_get_google_font_choices_latin() {
    return array(
        'Inter:wght@400;500;600;700' => 'Inter',
        'Roboto:wght@400;500;700' => 'Roboto',
        'Open+Sans:wght@400;500;600;700' => 'Open Sans',
        'Lato:wght@400;700' => 'Lato',
        'Montserrat:wght@400;500;600;700' => 'Montserrat',
        'Poppins:wght@400;500;600;700' => 'Poppins',
        'Source+Sans+3:wght@400;500;600;700' => 'Source Sans 3',
        'Nunito:wght@400;500;600;700' => 'Nunito',
        'Raleway:wght@400;500;600;700' => 'Raleway',
        'Ubuntu:wght@400;500;700' => 'Ubuntu',
        'Work+Sans:wght@400;500;600;700' => 'Work Sans',
        'DM+Sans:wght@400;500;600;700' => 'DM Sans',
        'Manrope:wght@400;500;600;700' => 'Manrope',
        'Plus+Jakarta+Sans:wght@400;500;600;700' => 'Plus Jakarta Sans',
        'Figtree:wght@400;500;600;700' => 'Figtree',
    );
}

/**
 * Admin scripts and styles
 */
function zskeleton_admin_enqueue_assets($hook) {
    // Check if minified assets should be used
    $use_minified = get_option('zskeleton_use_minified_assets', true);
    
    // Enqueue admin styles - use minified or original based on setting
    $admin_css_file = $use_minified ? 'admin.min.css' : 'admin.css';
    wp_enqueue_style(
        'zskeleton-admin',
        ZSkeleton_THEME_URL . '/assets/css/' . $admin_css_file,
        array(),
        null // Remove version for better caching
    );
    
    // Enqueue admin JavaScript - use minified or original based on setting
    $admin_js_file = $use_minified ? 'admin.min.js' : 'admin.js';
    wp_enqueue_script(
        'zskeleton-admin',
        ZSkeleton_THEME_URL . '/assets/js/' . $admin_js_file,
        array('jquery'),
        null, // Remove version for better caching
        true
    );

    $admin_meta_css_file = $use_minified ? 'admin-meta-fields.min.css' : 'admin-meta-fields.css';
    wp_enqueue_style(
        'zskeleton-admin-meta-fields',
        ZSkeleton_THEME_URL . '/assets/css/' . $admin_meta_css_file,
        array( 'zskeleton-admin' ),
        null
    );
}
add_action('admin_enqueue_scripts', 'zskeleton_admin_enqueue_assets');

/**
 * Include required files
 */
// Membership, payments, and renewals are provided by the ZSkeleton Membership & Payments plugin.

require_once ZSkeleton_THEME_DIR . '/includes/post-types/class-faqs.php';
require_once ZSkeleton_THEME_DIR . '/includes/post-types/class-glossary-terms.php';
require_once ZSkeleton_THEME_DIR . '/includes/post-types/class-sliders.php';
require_once ZSkeleton_THEME_DIR . '/includes/post-types/class-services.php';
require_once ZSkeleton_THEME_DIR . '/includes/admin/class-theme-features-admin.php';
require_once ZSkeleton_THEME_DIR . '/includes/slider/class-zskeleton-slider-frontend.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-taxonomy-landing.php';
require_once ZSkeleton_THEME_DIR . '/includes/admin/meta-wysiwyg.php';
require_once ZSkeleton_THEME_DIR . '/includes/repeater/class-zskeleton-repeater.php';
require_once ZSkeleton_THEME_DIR . '/includes/kits/seo-expert/seo-expert-kit.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-zskeleton-contact-customizer.php';





require_once ZSkeleton_THEME_DIR . '/includes/admin/class-theme-settings.php';
require_once ZSkeleton_THEME_DIR . '/includes/admin/class-sitemap-status.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-recaptcha.php';
require_once ZSkeleton_THEME_DIR . '/includes/extensions/form-kit/form-kit.php';
require_once ZSkeleton_THEME_DIR . '/includes/contact-form-kit.php';
require_once ZSkeleton_THEME_DIR . '/includes/contact-page-layout.php';
require_once ZSkeleton_THEME_DIR . '/includes/common-pages.php';
require_once ZSkeleton_THEME_DIR . '/includes/upload-mime-types.php';
require_once ZSkeleton_THEME_DIR . '/includes/google-map.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-widget-google-map.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-widget-social-icons.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-widget-heading.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-widget-contact-lines.php';
require_once ZSkeleton_THEME_DIR . '/includes/class-widget-nav-menus.php';

/**
 * Exclude users from WordPress sitemaps
 * 
 * WordPress automatically includes author/user pages in sitemaps.
 * This function disables the users sitemap to exclude author pages.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */
function zskeleton_exclude_users_from_sitemap() {
	// Return 0 to disable users sitemap - no pages will be generated
	// This effectively excludes users from sitemaps by making max pages = 0
	add_filter('wp_sitemaps_users_pre_max_num_pages', '__return_zero');
	
	// Prevent users provider from being added to the registry by returning false
	add_filter('wp_sitemaps_add_provider', function($provider, $name) {
		if ('users' === $name) {
			// Return a non-WP_Sitemaps_Provider object to prevent adding the provider
			return false;
		}
		return $provider;
	}, 10, 2);
}
add_action('init', 'zskeleton_exclude_users_from_sitemap', 5);
require_once ZSkeleton_THEME_DIR . '/includes/admin/class-faq-admin.php';

/**
 * Handle member activity tracking
 */
function zskeleton_handle_track_activity() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_nonce')) {
        wp_send_json_error(__('Invalid nonce', 'zskeleton'));
        return;
    }
    
    // Only track for logged-in users
    if (!is_user_logged_in()) {
        wp_send_json_error(__('User not logged in', 'zskeleton'));
        return;
    }
    
    $user_id = get_current_user_id();
    $page = sanitize_text_field($_POST['page']);
    $timestamp = intval($_POST['timestamp']);
    
    // Validate data
    if (empty($page) || empty($timestamp)) {
        wp_send_json_error(__('Missing required data', 'zskeleton'));
        return;
    }
    
    // Update user's last access time
    update_user_meta($user_id, 'zskeleton_last_access', current_time('mysql'));
    
    // Track page view (you can expand this to store more detailed analytics)
    $activity_log = get_user_meta($user_id, 'zskeleton_activity_log', true);
    if (!is_array($activity_log)) {
        $activity_log = array();
    }
    
    // Add new activity entry
    $activity_log[] = array(
        'page' => $page,
        'timestamp' => $timestamp,
        'date' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR']
    );
    
    // Keep only last 100 entries to prevent bloating
    if (count($activity_log) > 100) {
        $activity_log = array_slice($activity_log, -100);
    }
    
    update_user_meta($user_id, 'zskeleton_activity_log', $activity_log);
    
    // Update membership statistics if user has active membership
    if (class_exists('ZSkeleton_User_Profile_Fields') && 
        ZSkeleton_User_Profile_Fields::user_has_active_membership($user_id)) {
        
        $content_views = get_user_meta($user_id, 'zskeleton_content_views', true);
        $content_views = intval($content_views) + 1;
        update_user_meta($user_id, 'zskeleton_content_views', $content_views);
    }
    
    wp_send_json_success('Activity tracked successfully');
}
add_action('wp_ajax_zskeleton_track_activity', 'zskeleton_handle_track_activity');

/**
 * Resolve a theme logo option (attachment ID or URL string) to a URL.
 *
 * @param mixed $raw Raw option value.
 * @return string|false Absolute URL or false if unset/invalid.
 */
function zskeleton_resolve_logo_option_value($raw) {
    if (!$raw) {
        return false;
    }
    if (is_numeric($raw)) {
        $url = wp_get_attachment_url((int) $raw);
        return $url ? $url : false;
    }
    return $raw;
}

/**
 * Get the appropriate logo based on device type and current locale (Arabic vs non-Arabic).
 *
 * Non-Arabic locales use zskeleton_site_logo_ltr / zskeleton_mobile_logo_ltr when set,
 * then fall back to the main theme logos (same as Arabic).
 */
function zskeleton_get_logo($device = 'desktop') {
    $is_arabic = function_exists('zskeleton_is_arabic_locale') && zskeleton_is_arabic_locale();

    if ($device === 'mobile') {
        if ($is_arabic) {
            $url = zskeleton_resolve_logo_option_value(get_option('zskeleton_mobile_logo'));
        } else {
            $url = zskeleton_resolve_logo_option_value(get_option('zskeleton_mobile_logo_ltr'));
            if (!$url) {
                $url = zskeleton_resolve_logo_option_value(get_option('zskeleton_mobile_logo'));
            }
        }
        if ($url) {
            return $url;
        }
    }

    if ($is_arabic) {
        $url = zskeleton_resolve_logo_option_value(get_option('zskeleton_site_logo'));
    } else {
        $url = zskeleton_resolve_logo_option_value(get_option('zskeleton_site_logo_ltr'));
        if (!$url) {
            $url = zskeleton_resolve_logo_option_value(get_option('zskeleton_site_logo'));
        }
    }
    if ($url) {
        return $url;
    }

    if (has_custom_logo()) {
        $custom_logo_id = get_theme_mod('custom_logo');
        return wp_get_attachment_url($custom_logo_id);
    }

    return false;
}

/**
 * Number of footer widget columns to render (1–4). Sidebars stay registered; output is limited.
 *
 * @return int
 */
function zskeleton_get_footer_widget_areas_count() {
	$n = (int) get_option( 'zskeleton_footer_widget_areas_count', 4 );
	if ( $n < 1 ) {
		$n = 1;
	}
	if ( $n > 4 ) {
		$n = 4;
	}
	return $n;
}

/**
 * Sanitized destination URL for the optional floating WhatsApp button (Contact & social + Customizer).
 *
 * @return string Non-empty URL or empty string when unset.
 */
function zskeleton_get_whatsapp_float_button_url() {
	$url = trim( (string) get_option( 'zskeleton_whatsapp_float_url', '' ) );
	if ( '' === $url ) {
		return '';
	}
	/**
	 * Filter the floating WhatsApp button destination URL (already stored with sanitization).
	 *
	 * @param string $url URL from option.
	 */
	return (string) apply_filters( 'zskeleton_whatsapp_float_button_url', $url );
}

/**
 * Whether the floating WhatsApp control should render (enabled and URL present).
 *
 * @return bool
 */
function zskeleton_should_show_whatsapp_float_button() {
	if ( '1' !== (string) get_option( 'zskeleton_whatsapp_float_enabled', '0' ) ) {
		return false;
	}
	return '' !== zskeleton_get_whatsapp_float_button_url();
}

/**
 * Whether the back-to-top floating control should render (Appearance → ZSkeleton Settings → Layout).
 *
 * @return bool
 */
function zskeleton_should_show_back_to_top_button() {
	$on = ( '1' === (string) get_option( 'zskeleton_back_to_top_enabled', '1' ) );
	/**
	 * Whether to output the back-to-top floating control.
	 *
	 * @param bool $on Value from `zskeleton_back_to_top_enabled` (Appearance → ZSkeleton → Layout / Customizer).
	 */
	return (bool) apply_filters( 'zskeleton_should_show_back_to_top_button', $on );
}

/**
 * Whether membership join CTAs, header button, pricing blocks, and related nav links are shown.
 *
 * Controlled only when **ZSkeleton Membership & Payments** is active: reads
 * `zskeleton_memberships_feature_enabled` from **Memberships → Settings** in the plugin.
 * When the plugin is inactive, this is always false (no stale theme defaults).
 *
 * Extensions may override via {@see 'zskeleton_show_membership_public_ui'}.
 *
 * @return bool
 */
function zskeleton_is_memberships_feature_enabled() {
	if ( ! defined( 'ZSKELETON_MEMBERSHIP_VERSION' ) ) {
		/**
		 * @param bool $enabled False when membership plugin is not loaded.
		 */
		return (bool) apply_filters( 'zskeleton_show_membership_public_ui', false );
	}

	$enabled = '1' === (string) get_option( 'zskeleton_memberships_feature_enabled', '1' );

	/**
	 * Whether the theme should show public membership UI (join links, pricing blocks, etc.).
	 *
	 * @param bool $enabled Value from plugin option `zskeleton_memberships_feature_enabled`.
	 */
	return (bool) apply_filters( 'zskeleton_show_membership_public_ui', $enabled );
}

/**
 * Resolved membership landing page ID (plugin option, SEO Agency Kit, or slug fallback).
 *
 * Landing page is configured in **Memberships → Settings** in the ZSkeleton Membership plugin.
 * Override resolution with {@see 'zskeleton_membership_landing_page_id'}.
 *
 * @return int Published page ID or 0.
 */
function zskeleton_get_membership_page_id() {
	$page_id = (int) get_option( 'zskeleton_membership_page_id', 0 );
	$id      = 0;

	if ( $page_id > 0 && 'publish' === get_post_status( $page_id ) ) {
		$id = $page_id;
	} elseif ( class_exists( 'ZSAK_Content_Store' ) ) {
		$pid = (int) ZSAK_Content_Store::get_page_id( 'agency-memberships' );
		if ( $pid > 0 && 'publish' === get_post_status( $pid ) ) {
			$id = $pid;
		}
	}

	if ( ! $id ) {
		$p = get_page_by_path( 'memberships' );
		if ( $p && 'publish' === get_post_status( $p ) ) {
			$id = (int) $p->ID;
		}
	}
	if ( ! $id ) {
		$p = get_page_by_path( 'agency-memberships' );
		if ( $p && 'publish' === get_post_status( $p ) ) {
			$id = (int) $p->ID;
		}
	}

	/**
	 * Membership landing page ID used by the theme for permalinks and “is membership page” checks.
	 *
	 * @param int $id Resolved page ID (0 if none).
	 */
	return (int) apply_filters( 'zskeleton_membership_landing_page_id', $id );
}

/**
 * Whether the current singular page is the configured membership landing page.
 *
 * @return bool
 */
function zskeleton_is_membership_page() {
    if ( ! is_page() ) {
        return false;
    }
    $mid = zskeleton_get_membership_page_id();
    return $mid > 0 && (int) get_queried_object_id() === $mid;
}

/**
 * Add-to-cart + checkout URL for a membership plan (WooCommerce). Empty if checkout cannot be started.
 *
 * Implemented by the ZSkeleton Membership plugin via {@see 'zskeleton_membership_checkout_start_url'}.
 *
 * @param string $plan_id Plan key from Membership Plans.
 * @return string
 */
function zskeleton_get_membership_checkout_url( $plan_id ) {
	$plan_id = (string) $plan_id;

	/**
	 * Start checkout URL for a membership plan (theme templates).
	 *
	 * @param string $url     Default empty; ZSkeleton Membership fills via WooCommerce integration.
	 * @param string $plan_id Plan key.
	 */
	return (string) apply_filters( 'zskeleton_membership_checkout_start_url', '', $plan_id );
}

/**
 * Auth page URL from ZSkeleton Theme Settings (Content), or WordPress defaults.
 *
 * @param string $context     One of: login, register, lost_password, reset_password.
 * @param string $redirect_to Optional absolute URL (used for login/register when the page supports it).
 * @return string
 */
function zskeleton_get_auth_page_url( $context, $redirect_to = '' ) {
    $context = sanitize_key( (string) $context );
    $map     = array(
        'login'          => 'zskeleton_auth_login_page_id',
        'register'       => 'zskeleton_auth_register_page_id',
        'lost_password'  => 'zskeleton_auth_lost_password_page_id',
        'reset_password' => 'zskeleton_auth_reset_password_page_id',
    );
    if ( ! isset( $map[ $context ] ) ) {
        return '';
    }
    $page_id = (int) get_option( $map[ $context ], 0 );
    if ( $page_id > 0 && 'publish' === get_post_status( $page_id ) ) {
        $url = get_permalink( $page_id );
        if ( $redirect_to !== '' && in_array( $context, array( 'login', 'register' ), true ) ) {
            $url = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $url );
        }
        return $url;
    }
    switch ( $context ) {
        case 'login':
            return wp_login_url( $redirect_to );
        case 'register':
            if ( function_exists( 'wp_registration_url' ) ) {
                $reg = wp_registration_url();
            } else {
                $reg = add_query_arg( 'action', 'register', wp_login_url( '', false ) );
            }
            return ( $redirect_to !== '' ) ? add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $reg ) : $reg;
        case 'lost_password':
            return wp_lostpassword_url( $redirect_to );
        case 'reset_password':
            return site_url( 'wp-login.php?action=rp', 'login' );
    }
    return '';
}

/**
 * Helper function to get page URLs by slug with fallback
 *
 * @param string $slug    Logical slug (e.g. memberships, contact).
 * @param string $fallback URL if no page is found.
 */
function zskeleton_get_page_url($slug, $fallback = '#') {
    if ( 'memberships' === $slug ) {
        $mid = zskeleton_get_membership_page_id();
        if ( $mid > 0 ) {
            return get_permalink( $mid );
        }
    }

    if ( in_array( $slug, array( 'login', 'register', 'forgot-password', 'lost-password', 'reset-password' ), true ) ) {
        $ctx = 'login';
        if ( 'register' === $slug ) {
            $ctx = 'register';
        } elseif ( in_array( $slug, array( 'forgot-password', 'lost-password' ), true ) ) {
            $ctx = 'lost_password';
        } elseif ( 'reset-password' === $slug ) {
            $ctx = 'reset_password';
        }
        return zskeleton_get_auth_page_url( $ctx );
    }

    $page = get_page_by_path($slug);
    if ($page) {
        return get_permalink($page->ID);
    }
    
    // Try to find page by slug in different ways
    $pages = get_posts(array(
        'post_type' => 'page',
        'name' => $slug,
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    if (!empty($pages)) {
        return get_permalink($pages[0]->ID);
    }
    
    // Check for common page mappings
    $page_mappings = array(
        'about' => home_url('/about/'),
        'contact' => home_url('/contact/'),
        'memberships' => home_url('/memberships/'),
        'register' => home_url('/register/'),
        'blog' => get_permalink(get_option('page_for_posts')) ?: home_url('/blog/'),
        'terms-conditions' => home_url('/terms-conditions/'),
        'privacy-policy' => get_privacy_policy_url() ?: home_url('/privacy-policy/'),
        'faqs' => home_url('/faqs/'),
        'complete-payment' => home_url('/complete-payment/'),
        'payment-success' => home_url('/payment-success/'),
        'profile' => home_url('/profile/')
    );
    
    if (isset($page_mappings[$slug])) {
        return $page_mappings[$slug];
    }
    
    return $fallback;
}

/**
 * Permalink for the theme “contact” page chosen in ZSkeleton Settings → Content, or the mapped Contact URL.
 *
 * Use anywhere a contact link should respect that setting (CTAs, blocks, footers, etc.).
 *
 * @return string
 */
function zskeleton_get_theme_contact_page_url() {
    $page_id = (int) get_option( 'zskeleton_theme_contact_page_id', 0 );
    if ( $page_id < 1 ) {
        $legacy = (int) get_option( 'zskeleton_faq_cta_contact_page_id', 0 );
        if ( $legacy > 0 ) {
            update_option( 'zskeleton_theme_contact_page_id', $legacy );
            delete_option( 'zskeleton_faq_cta_contact_page_id' );
            $page_id = $legacy;
        }
    }
    if ( $page_id > 0 && 'publish' === get_post_status( $page_id ) ) {
        $url = get_permalink( $page_id );
        if ( is_string( $url ) && '' !== $url ) {
            return $url;
        }
    }
    return zskeleton_get_page_url( 'contact' );
}

/**
 * Back-compat alias for {@see zskeleton_get_theme_contact_page_url()}.
 *
 * @return string
 */
function zskeleton_get_faq_cta_contact_url() {
    return zskeleton_get_theme_contact_page_url();
}

// Common pages (login, register, blog, etc.) are created via includes/common-pages.php: Appearance → ZSkeleton Settings → Content → “Create & sync common pages”, or once when switching to this theme (see zskeleton_common_pages_auto_installed).

/**
 * Debug function to check registered post types
 */
function zskeleton_debug_post_types() {
    if (current_user_can('administrator') && isset($_GET['debug_post_types'])) {
        $post_types = get_post_types(array(), 'objects');
        $zskeleton_types = array();
        
        foreach ($post_types as $post_type) {
            if (strpos($post_type->name, 'zskeleton_') === 0) {
                $zskeleton_types[] = $post_type->name . ' (' . $post_type->label . ')';
            }
        }
        
        if (!empty($zskeleton_types)) {
            add_action('admin_notices', function() use ($zskeleton_types) {
                echo '<div class="notice notice-success"><p><strong>' . esc_html__('ZSkeleton Post Types Registered:', 'zskeleton') . '</strong><br>' . implode('<br>', $zskeleton_types) . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p><strong>' . esc_html__('No ZSkeleton Post Types Found!', 'zskeleton') . '</strong> ' . esc_html__('Check class initialization in functions.php', 'zskeleton') . '</p></div>';
            });
        }
    }
}
add_action('admin_init', 'zskeleton_debug_post_types');

/**
 * Initialize theme components
 */
function zskeleton_init_components() {
    // Prevent double initialization
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;
    
    // Membership system: ZSkeleton Membership & Payments plugin (ZSKELETON_MEMBERSHIP_PATH).

    // Initialize content post types kept in the base theme
    try {
        new ZSkeleton_FAQs();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    try {
        new ZSkeleton_Glossary_Terms();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    if (is_admin()) {
        try {
            new ZSkeleton_Theme_Features_Admin();
        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }
    }

    try {
        new ZSkeleton_Sliders();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    ZSkeleton_Slider_Frontend::init();

    try {
        new ZSkeleton_Services();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    try {
        new ZSkeleton_Landing_Taxonomy();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
    
    // ZSkeleton_Theme_Settings loads on after_setup_theme (see below) so admin_post_* hooks exist before admin_init.

    
    try {
        new ZSkeleton_ReCAPTCHA();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
    
    try {
        new ZSkeleton_FAQ_Admin();
    } catch (Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}

// Register CPTs and admin menus on init (single hook; avoids duplicate static init).
add_action('init', 'zskeleton_init_components', 0);

// Theme settings admin (admin_post save, register_setting) must register before admin_init on admin-post.php.
add_action(
    'after_setup_theme',
    static function () {
        if (!class_exists('ZSkeleton_Theme_Settings')) {
            return;
        }
        try {
            new ZSkeleton_Theme_Settings();
        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('[ZSkeleton] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }
    },
    0
);











/**
 * Register widget areas
 */
function zskeleton_register_sidebars() {
    register_sidebar(array(
        'name' => __('Primary Sidebar', 'zskeleton'),
        'id' => 'sidebar-1',
        'description' => __('Main sidebar widget area', 'zskeleton'),
        'before_widget' => '<section id="%1$s" class="widget %2$s formal-card">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widget Area 1', 'zskeleton'),
        'id' => 'footer-1',
        'description' => __('First footer widget area', 'zskeleton'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widget Area 2', 'zskeleton'),
        'id' => 'footer-2',
        'description' => __('Second footer widget area', 'zskeleton'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widget Area 3', 'zskeleton'),
        'id' => 'footer-3',
        'description' => __('Third footer widget area', 'zskeleton'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widget Area 4', 'zskeleton'),
        'id' => 'footer-4',
        'description' => __('Fourth footer widget area', 'zskeleton'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));

    register_sidebar(array(
        'name' => __('Footer Bottom (left column)', 'zskeleton'),
        'id' => 'footer-bottom-1',
        'description' => __('Bottom bar — left column (e.g. copyright or horizontal menu).', 'zskeleton'),
        'before_widget' => '<div id="%1$s" class="footer-bottom-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-bottom-widget-title">',
        'after_title' => '</h3>',
    ));

    register_sidebar(array(
        'name' => __('Footer Bottom (right column)', 'zskeleton'),
        'id' => 'footer-bottom-2',
        'description' => __('Bottom bar — right column (e.g. vertical menu or legal links).', 'zskeleton'),
        'before_widget' => '<div id="%1$s" class="footer-bottom-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-bottom-widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'zskeleton_register_sidebars');

/**
 * Custom image sizes
 */
function zskeleton_add_image_sizes() {
    add_image_size('zskeleton-featured', 800, 450, true);
    add_image_size('zskeleton-card', 400, 300, true);
    add_image_size('zskeleton-thumbnail', 200, 150, true);
    add_image_size('zskeleton-hero', 1200, 600, true);
}
add_action('after_setup_theme', 'zskeleton_add_image_sizes');

/**
 * Customize excerpt length
 */
function zskeleton_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'zskeleton_excerpt_length');

/**
 * Customize excerpt more text
 */
function zskeleton_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'zskeleton_excerpt_more');

/**
 * Add body classes
 */
function zskeleton_body_classes($classes) {
    // Add class for logged-in users
    if (is_user_logged_in()) {
        $classes[] = 'logged-in-user';
        
        // Add membership class (plugin provides ZSkeleton_User_Profile_Fields).
        $user_id = get_current_user_id();
        if ( class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id ) ) {
            $membership_type = ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id );
            $classes[] = 'has-membership';
            $classes[] = 'membership-' . $membership_type;
        } else {
            $classes[] = 'no-membership';
        }
    }
    
    // Add page template class
    if (is_page_template()) {
        $template = str_replace('.php', '', basename(get_page_template()));
        $classes[] = 'template-' . $template;
    }
    
    return $classes;
}
add_filter('body_class', 'zskeleton_body_classes');

/**
 * Custom logo setup
 */
function zskeleton_custom_logo_setup() {
    add_theme_support('custom-logo', array(
        'height' => 50,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
        'header-text' => array('site-title', 'site-description'),
    ));
}
add_action('after_setup_theme', 'zskeleton_custom_logo_setup');

/**
 * Customizer settings
 */
function zskeleton_customize_register($wp_customize) {
    // Site Identity Section
    $wp_customize->add_setting('zskeleton_tagline', array(
        'default' => 'A flexible WordPress base theme for membership-driven websites.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('zskeleton_tagline', array(
        'label' => __('Site Tagline', 'zskeleton'),
        'section' => 'title_tagline',
        'type' => 'textarea',
    ));
    
    // Colors Section (options mirror ZSkeleton Settings → General for one source of truth).
    $wp_customize->add_section('zskeleton_colors', array(
        'title' => __('ZSkeleton Colors', 'zskeleton'),
        'priority' => 40,
    ));

    $palette_defaults = function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults() : array(
        'primary'             => '#647FBC',
        'secondary'           => '#91ADC8',
        'accent'              => '#AED6CF',
        'background'          => '#FAFDD6',
        'button_background'   => '#647FBC',
        'button_text'         => '#000000',
        'counter_text'        => '#647FBC',
    );

    $wp_customize->add_setting(
        'zskeleton_primary_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['primary'],
            'sanitize_callback' => 'zskeleton_sanitize_option_primary_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_primary_color',
            array(
                'label'   => __('Primary brand color', 'zskeleton'),
                'section' => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_secondary_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['secondary'],
            'sanitize_callback' => 'zskeleton_sanitize_option_secondary_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_secondary_color',
            array(
                'label'       => __('Secondary color', 'zskeleton'),
                'description' => __('Borders, soft UI, and supporting accents.', 'zskeleton'),
                'section'     => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_accent_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['accent'],
            'sanitize_callback' => 'zskeleton_sanitize_option_accent_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_accent_color',
            array(
                'label'       => __('Accent color', 'zskeleton'),
                'description' => __('Highlights, badges, and subtle section tints.', 'zskeleton'),
                'section'     => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_background_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['background'],
            'sanitize_callback' => 'zskeleton_sanitize_option_background_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_background_color',
            array(
                'label'       => __('Page background', 'zskeleton'),
                'description' => __('Main canvas color (warm cream works well on mobile).', 'zskeleton'),
                'section'     => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_button_background_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['button_background'],
            'sanitize_callback' => 'zskeleton_sanitize_option_button_background_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_button_background_color',
            array(
                'label'       => __('Buttons background', 'zskeleton'),
                'description' => __('Primary-style buttons and default form buttons.', 'zskeleton'),
                'section'     => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_button_text_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['button_text'],
            'sanitize_callback' => 'zskeleton_sanitize_option_button_text_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_button_text_color',
            array(
                'label'       => __('Buttons text color', 'zskeleton'),
                'description' => __('Label color on primary-style buttons (default black).', 'zskeleton'),
                'section'     => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_counter_text_color',
        array(
            'type'              => 'option',
            'default'           => $palette_defaults['counter_text'],
            'sanitize_callback' => 'zskeleton_sanitize_option_counter_text_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'zskeleton_counter_text_color',
            array(
                'label'       => __('Counters text color', 'zskeleton'),
                'description' => __('Large numbers in hero and stat blocks.', 'zskeleton'),
                'section'     => 'zskeleton_colors',
            )
        )
    );

    $wp_customize->add_setting(
        'zskeleton_nav_item_hover_bg',
        array(
            'type'              => 'option',
            'default'           => '',
            'sanitize_callback' => 'zskeleton_sanitize_option_nav_item_background',
        )
    );
    $wp_customize->add_control(
        'zskeleton_nav_item_hover_bg',
        array(
            'label'       => __('Primary menu — hover / focus link background', 'zskeleton'),
            'description' => __('Optional. Hex or rgba, e.g. rgba(30, 58, 138, 0.1). Leave empty for no background.', 'zskeleton'),
            'section'     => 'zskeleton_colors',
            'type'          => 'text',
        )
    );
    $wp_customize->add_setting(
        'zskeleton_nav_item_active_bg',
        array(
            'type'              => 'option',
            'default'           => '',
            'sanitize_callback' => 'zskeleton_sanitize_option_nav_item_background',
        )
    );
    $wp_customize->add_control(
        'zskeleton_nav_item_active_bg',
        array(
            'label'       => __('Primary menu — current item link background', 'zskeleton'),
            'description' => __('Optional. Empty = same as hover. Set a color to distinguish the current page.', 'zskeleton'),
            'section'     => 'zskeleton_colors',
            'type'          => 'text',
        )
    );

    // Homepage Section
    $wp_customize->add_section('zskeleton_homepage', array(
        'title' => __('Homepage Settings', 'zskeleton'),
        'priority' => 50,
    ));
    
    $wp_customize->add_setting('zskeleton_hero_title', array(
        'default' => 'ZSkeleton',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('zskeleton_hero_title', array(
        'label' => __('Hero Title', 'zskeleton'),
        'section' => 'zskeleton_homepage',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('zskeleton_hero_subtitle', array(
        'default' => 'Launch your next WordPress project faster with reusable templates and core features.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('zskeleton_hero_subtitle', array(
        'label' => __('Hero Subtitle', 'zskeleton'),
        'section' => 'zskeleton_homepage',
        'type' => 'textarea',
    ));

    // Layout: floating back to top (same wp option as ZSkeleton Settings → Layout).
    $wp_customize->add_section(
        'zskeleton_layout',
        array(
            'title'       => __( 'ZSkeleton layout', 'zskeleton' ),
            'description' => __( 'Floating controls at the bottom of the screen. Positions use logical left/right so they mirror correctly in RTL.', 'zskeleton' ),
            'priority'    => 45,
        )
    );
    $wp_customize->add_setting(
        'zskeleton_back_to_top_enabled',
        array(
            'type'              => 'option',
            'default'           => '1',
            'sanitize_callback' => 'zskeleton_sanitize_option_whatsapp_float_enabled',
        )
    );
    $wp_customize->add_control(
        'zskeleton_back_to_top_enabled',
        array(
            'label'       => __( 'Show back to top button', 'zskeleton' ),
            'description' => __( 'Appears after scrolling. In LTR it sits on the bottom-right; in RTL it moves to the bottom-left (opposite the floating WhatsApp control when both are enabled).', 'zskeleton' ),
            'section'     => 'zskeleton_layout',
            'type'        => 'checkbox',
            'settings'    => 'zskeleton_back_to_top_enabled',
        )
    );
}
add_action('customize_register', 'zskeleton_customize_register');

/**
 * Add search functionality to header
 */
function zskeleton_header_search() {
    ?>
    <div id="header-search" class="header-search">
        <button class="search-close" type="button" aria-label="<?php _e('Close Search', 'zskeleton'); ?>">
            ×
        </button>
        <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="search" class="search-field" placeholder="<?php echo esc_attr__('Search...', 'zskeleton'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
            <button type="submit" class="search-submit">
                <span class="screen-reader-text"><?php echo _x('Search', 'submit button', 'zskeleton'); ?></span>
                🔍
            </button>
        </form>
    </div>
    <?php
}

/**
 * AJAX handler for search
 */
function zskeleton_ajax_search() {
    check_ajax_referer('zskeleton_nonce', 'nonce');
    
    $search_query = sanitize_text_field($_POST['query']);
    
    if (empty($search_query)) {
        wp_die();
    }
    
    $posts = get_posts(array(
        's' => $search_query,
        'post_type' => array('post', 'page', 'zskeleton_faqs'),
        'numberposts' => 5,
        'post_status' => 'publish'
    ));
    
    $results = array();
    foreach ($posts as $post) {
        $results[] = array(
            'title' => get_the_title($post),
            'url' => get_permalink($post),
            'excerpt' => wp_trim_words(get_the_excerpt($post), 15),
            'type' => get_post_type($post)
        );
    }
    
    wp_send_json_success($results);
}
add_action('wp_ajax_zskeleton_search', 'zskeleton_ajax_search');
add_action('wp_ajax_nopriv_zskeleton_search', 'zskeleton_ajax_search');

/**
 * Newsletter subscription handler
 */
function zskeleton_newsletter_subscription() {
    // Check nonce
    if (!check_ajax_referer('zskeleton_nonce', 'nonce', false)) {
        wp_send_json_error(__('Security check failed. Please refresh the page and try again.', 'zskeleton'));
        return;
    }
    
    // Get and sanitize email
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    
    // Validate email
    if (!is_email($email)) {
        wp_send_json_error(__('Please enter a valid email address.', 'zskeleton'));
        return;
    }
    
    // Check if already subscribed in WordPress
    $subscribers = get_option('zskeleton_newsletter_subscribers', array());
    $is_new_subscriber = !in_array($email, $subscribers);
    
    if (!$is_new_subscriber) {
        wp_send_json_error(__('This email is already subscribed.', 'zskeleton'));
        return;
    }
    
    // MailerLite only when integration is enabled and the plugin is configured (otherwise store in WordPress).
    $mailerlite_integration_on = function_exists( 'zskeleton_mailerlite_subscribe' )
        && function_exists( 'zskeleton_is_mailerlite_active' )
        && zskeleton_is_mailerlite_active()
        && (bool) get_option( 'zskeleton_enable_mailerlite', false );

    $mailerlite_success = false;
    if ( $mailerlite_integration_on ) {
        $ml_fields = array();
        $group_id = function_exists( 'zskeleton_get_mailerlite_group_id' ) ? zskeleton_get_mailerlite_group_id( 'general' ) : null;

        try {
            $mailerlite_success = zskeleton_mailerlite_subscribe( $email, $ml_fields, $group_id );
        } catch ( Exception $e ) {
            // Silent fail; error path below if integration was required.
        }
    }

    // Store locally when MailerLite is off, or when MailerLite signup succeeded.
    if ( $mailerlite_success || ! $mailerlite_integration_on ) {
        $subscribers[] = $email;
        update_option('zskeleton_newsletter_subscribers', $subscribers);
        
        $message = __('Thank you for subscribing to our newsletter!', 'zskeleton');
        if ($mailerlite_success) {
            $message .= ' ' . __('You have been added to our mailing list.', 'zskeleton');
        }
        
        wp_send_json_success($message);
    } else {
        wp_send_json_error(__('Failed to subscribe. Please try again later.', 'zskeleton'));
    }
}
add_action('wp_ajax_zskeleton_newsletter', 'zskeleton_newsletter_subscription');
add_action('wp_ajax_nopriv_zskeleton_newsletter', 'zskeleton_newsletter_subscription');

/**
 * AJAX handler for updating last access time
 */
function zskeleton_ajax_update_last_access() {
    // Check nonce
    if (!check_ajax_referer('zskeleton_nonce', 'nonce', false)) {
        wp_send_json_error(__('Security check failed.', 'zskeleton'));
        return;
    }
    
    // Must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(__('User not logged in.', 'zskeleton'));
        return;
    }
    
    $user_id = get_current_user_id();
    
    // Update last access time
    if (class_exists('ZSkeleton_User_Profile_Fields')) {
        ZSkeleton_User_Profile_Fields::update_last_access($user_id);
        wp_send_json_success(__('Last access updated.', 'zskeleton'));
    } else {
        // Fallback if class not available
        update_user_meta($user_id, 'zskeleton_last_access', current_time('mysql'));
        wp_send_json_success(__('Last access updated.', 'zskeleton'));
    }
}
add_action('wp_ajax_zskeleton_update_last_access', 'zskeleton_ajax_update_last_access');

/**
 * Custom pagination
 */
function zskeleton_pagination($args = array()) {
    $defaults = array(
        'mid_size' => 2,
        'prev_text' => __('&laquo; Previous', 'zskeleton'),
        'next_text' => __('Next &raquo;', 'zskeleton'),
        'screen_reader_text' => __('Posts navigation', 'zskeleton'),
    );
    
    $args = wp_parse_args($args, $defaults);
    
    echo '<nav class="pagination-wrapper" aria-label="' . esc_attr($args['screen_reader_text']) . '">';
    echo paginate_links($args);
    echo '</nav>';
}

/**
 * Build bot-protection fields for the comment form.
 *
 * @return string
 */
function zskeleton_get_comment_security_fields_markup() {
    $output = '';

    $output .= wp_nonce_field('zskeleton_comment_submission', 'zskeleton_comment_nonce', true, false);
    $output .= '<p class="zskeleton-comment-hp-field" aria-hidden="true">';
    $output .= '<label for="zskeleton_comment_hp">' . esc_html__('Leave this field empty', 'zskeleton') . '</label>';
    $output .= '<input type="text" id="zskeleton_comment_hp" name="zskeleton_comment_hp" value="" tabindex="-1" autocomplete="off">';
    $output .= '</p>';

    if (class_exists('ZSkeleton_ReCAPTCHA') && function_exists('zskeleton_recaptcha')) {
        $captcha = zskeleton_recaptcha();
        if ($captcha && $captcha->is_enabled()) {
            ob_start();
            $captcha->render_field('comment_submit');
            $captcha_markup = trim((string) ob_get_clean());
            if ('' !== $captcha_markup) {
                $output .= '<div class="zskeleton-comment-captcha-wrap">' . $captcha_markup . '</div>';
            }
        }
    }

    return $output;
}

/**
 * Style and structure the native WordPress comment form.
 *
 * @param array<string, mixed> $defaults Comment form defaults.
 * @return array<string, mixed>
 */
function zskeleton_customize_comment_form_defaults($defaults) {
    if (!is_singular()) {
        return $defaults;
    }

    $security_fields = zskeleton_get_comment_security_fields_markup();

    $defaults['class_form']           = 'comment-form zskeleton-comment-form formal-card';
    $defaults['class_submit']         = 'submit zskeleton-comment-submit';
    $defaults['title_reply_before']   = '<h3 id="reply-title" class="comment-reply-title">';
    $defaults['title_reply_after']    = '</h3>';
    $defaults['title_reply']          = __('Leave a Comment', 'zskeleton');
    $defaults['label_submit']         = __('Post Comment', 'zskeleton');
    $defaults['comment_notes_before'] = '<p class="comment-notes">' . esc_html__('Your email address will not be published. Required fields are marked *', 'zskeleton') . '</p>';
    $defaults['comment_field']        = '<p class="comment-form-comment"><label for="comment">' . _x('Comment', 'noun', 'zskeleton') . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="7" maxlength="65525" required="required" placeholder="' . esc_attr__('Share your thoughts...', 'zskeleton') . '"></textarea></p>' . $security_fields;

    return $defaults;
}
add_filter('comment_form_defaults', 'zskeleton_customize_comment_form_defaults');

/**
 * Improve default comment input fields appearance.
 *
 * @param array<string, string> $fields Default comment fields.
 * @return array<string, string>
 */
function zskeleton_customize_comment_form_fields($fields) {
    $commenter = wp_get_current_commenter();
    $req       = get_option('require_name_email');

    $fields['author'] =
        '<p class="comment-form-author">' .
        '<label for="author">' . esc_html__('Name', 'zskeleton') . ($req ? ' <span class="required">*</span>' : '') . '</label>' .
        '<input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30" maxlength="245" ' . ($req ? 'required="required"' : '') . ' placeholder="' . esc_attr__('Your name', 'zskeleton') . '">' .
        '</p>';

    $fields['email'] =
        '<p class="comment-form-email">' .
        '<label for="email">' . esc_html__('Email', 'zskeleton') . ($req ? ' <span class="required">*</span>' : '') . '</label>' .
        '<input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" maxlength="100" ' . ($req ? 'required="required"' : '') . ' placeholder="' . esc_attr__('your@email.com', 'zskeleton') . '">' .
        '</p>';

    $fields['url'] =
        '<p class="comment-form-url">' .
        '<label for="url">' . esc_html__('Website', 'zskeleton') . '</label>' .
        '<input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" maxlength="200" placeholder="' . esc_attr__('https://example.com', 'zskeleton') . '">' .
        '</p>';

    return $fields;
}
add_filter('comment_form_default_fields', 'zskeleton_customize_comment_form_fields');

/**
 * Validate theme-specific security checks for comment submissions.
 *
 * @param array<string, mixed> $commentdata Comment payload.
 * @return array<string, mixed>
 */
function zskeleton_validate_comment_security($commentdata) {
    if (is_admin() || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
        return $commentdata;
    }

    $nonce = isset($_POST['zskeleton_comment_nonce']) ? sanitize_text_field(wp_unslash($_POST['zskeleton_comment_nonce'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    if ('' === $nonce || !wp_verify_nonce($nonce, 'zskeleton_comment_submission')) {
        wp_die(
            esc_html__('Security check failed. Please refresh the page and try again.', 'zskeleton'),
            esc_html__('Comment Submission Blocked', 'zskeleton'),
            array('response' => 403)
        );
    }

    $honeypot = isset($_POST['zskeleton_comment_hp']) ? trim((string) wp_unslash($_POST['zskeleton_comment_hp'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    if ('' !== $honeypot) {
        wp_die(
            esc_html__('Your submission was blocked by anti-spam protection.', 'zskeleton'),
            esc_html__('Comment Submission Blocked', 'zskeleton'),
            array('response' => 403)
        );
    }

    if (class_exists('ZSkeleton_ReCAPTCHA') && function_exists('zskeleton_recaptcha')) {
        $captcha = zskeleton_recaptcha();
        if ($captcha && $captcha->is_enabled()) {
            $verification = $captcha->verify_form_submission();
            if (is_wp_error($verification)) {
                wp_die(
                    esc_html($verification->get_error_message()),
                    esc_html__('Comment Submission Blocked', 'zskeleton'),
                    array('response' => 403)
                );
            }
        }
    }

    return $commentdata;
}
add_filter('preprocess_comment', 'zskeleton_validate_comment_security');

/**
 * Comments callback
 */
function zskeleton_comment_callback($comment, $args, $depth) {
    ?>
    <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
        <article class="comment-body">
            <header class="comment-meta">
                <div class="comment-author vcard">
                    <?php echo get_avatar($comment, 60); ?>
                    <span class="fn"><?php echo get_comment_author_link(); ?></span>
                    <?php if ( class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( get_comment( get_comment_ID() )->user_id ) ) : ?>
                        <span class="member-badge"><?php esc_html_e('ZSkeleton Member', 'zskeleton'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="comment-metadata">
                    <time datetime="<?php comment_time('c'); ?>">
                        <?php printf(__('%1$s at %2$s', 'zskeleton'), get_comment_date(), get_comment_time()); ?>
                    </time>
                    <?php edit_comment_link(__('Edit', 'zskeleton'), '<span class="edit-link">', '</span>'); ?>
                </div>
            </header>
            
            <div class="comment-content">
                <?php comment_text(); ?>
            </div>
            
            <div class="comment-reply">
                <?php 
                comment_reply_link(array_merge($args, array(
                    'depth' => $depth,
                    'max_depth' => $args['max_depth'],
                    'reply_text' => __('Reply', 'zskeleton')
                )));
                ?>
            </div>
        </article>
    <?php
}

/**
 * Block editor settings
 */
function zskeleton_block_editor_settings() {
    // Block editor assets would be enqueued here if block-editor.js existed
    // Currently no block editor customizations are implemented
}
add_action('enqueue_block_editor_assets', 'zskeleton_block_editor_settings');

/**
 * Security enhancements
 */
function zskeleton_security_headers() {
    // Remove WordPress version from head
    remove_action('wp_head', 'wp_generator');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
    
    // Remove wlwmanifest link
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head');
}
add_action('init', 'zskeleton_security_headers');

/**
 * Disable comments on pages by default
 */
function zskeleton_disable_comments_on_pages() {
    if (is_admin() && get_post_type() === 'page') {
        remove_post_type_support('page', 'comments');
        remove_post_type_support('page', 'trackbacks');
    }
}
add_action('init', 'zskeleton_disable_comments_on_pages');

// Ensure proper text domain loading
function zskeleton_load_textdomain() {
    load_theme_textdomain('zskeleton', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'zskeleton_load_textdomain');

/**
 * Custom Authentication System
 */

/**
 * Handle custom login redirects
 */
function zskeleton_custom_login_redirect($redirect_to, $request, $user) {
    // Check if user is logged in and is not an admin
    if (isset($user->roles) && is_array($user->roles)) {
        // If user is not admin, redirect to home page
        if (!in_array('administrator', $user->roles)) {
            return home_url();
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'zskeleton_custom_login_redirect', 10, 3);

/**
 * Handle custom registration redirects
 */
function zskeleton_custom_registration_redirect($redirect_to, $request, $user) {
    if (isset($user->ID)) {
        // Redirect new users to home page with welcome message
        return add_query_arg('registered', 'success', home_url());
    }
    return $redirect_to;
}
add_filter('registration_redirect', 'zskeleton_custom_registration_redirect', 10, 3);

/**
 * Show success messages for registration and password reset
 */
function zskeleton_show_auth_messages() {
    if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
        echo '<div class="auth-success-notice">';
        echo '<p><strong>' . esc_html__('Welcome to ZSkeleton!', 'zskeleton') . '</strong> ' . esc_html__('Your account has been created successfully. You are now logged in and can access all member benefits.', 'zskeleton') . '</p>';
        echo '</div>';
    }
    
    if (isset($_GET['password_reset']) && $_GET['password_reset'] === 'success') {
        echo '<div class="auth-success-notice">';
        echo '<p><strong>' . esc_html__('Password Updated!', 'zskeleton') . '</strong> ' . esc_html__('Your password has been successfully reset. You are now logged in.', 'zskeleton') . '</p>';
        echo '</div>';
    }
}
add_action('wp_footer', 'zskeleton_show_auth_messages');

/**
 * Handle password reset key validation
 */
function zskeleton_validate_password_reset_key($key, $login) {
    $user = get_user_by('login', $login);
    if (!$user) {
        return new WP_Error('invalid_key', __('Invalid reset key.', 'zskeleton'));
    }
    
    $key_data = get_password_reset_key($user);
    if (is_wp_error($key_data)) {
        return $key_data;
    }
    
    if ($key_data !== $key) {
        return new WP_Error('invalid_key', __('Invalid reset key.', 'zskeleton'));
    }
    
    return true;
}

/**
 * Enhanced user registration with additional fields
 */
function zskeleton_enhanced_user_registration($user_id) {
    // Update user meta with additional fields if they exist
    if (isset($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    
    if (isset($_POST['last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
    
    if (isset($_POST['organization'])) {
        update_user_meta($user_id, 'organization', sanitize_text_field($_POST['organization']));
    }
    
    // Set registration date
    update_user_meta($user_id, 'zskeleton_registration_date', current_time('mysql'));
    
    // Set default membership status (can be upgraded later)
    update_user_meta($user_id, 'zskeleton_membership_status', 'pending');
    update_user_meta($user_id, 'zskeleton_membership_type', 'individual');
}
add_action('user_register', 'zskeleton_enhanced_user_registration');

/**
 * Custom logout redirect
 */
function zskeleton_custom_logout_redirect() {
    return home_url();
}
add_filter('logout_redirect', 'zskeleton_custom_logout_redirect');

/**
 * Add authentication CSS styles
 */
function zskeleton_auth_styles() {
    if (is_page('login') || is_page('register') || is_page('forgot-password') || is_page('reset-password')) {
        ?>
        <style>
        .auth-success-notice {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
        }
        
        .auth-success-notice p {
            margin: 0;
            font-weight: 500;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Auto-hide success notice after 5 seconds */
        .auth-success-notice {
            animation: slideInRight 0.3s ease-out, slideOutRight 0.3s ease-in 4.7s forwards;
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'zskeleton_auth_styles');

/**
 * Note: Default WordPress login pages are kept accessible
 * Users can still use wp-login.php if needed
 * Custom pages are available as alternatives with enhanced UI
 */

/**
 * Replace WordPress with ZSkeleton in email templates
 */

/**
 * Customize email sender name
 */
function zskeleton_mail_from_name($original_email_from) {
    return 'ZSkeleton';
}
add_filter('wp_mail_from_name', 'zskeleton_mail_from_name');

/**
 * Customize email sender address to use admin email
 */
function zskeleton_mail_from($original_email_address) {
    return get_option('admin_email');
}
add_filter('wp_mail_from', 'zskeleton_mail_from');

/**
 * Customize password reset email content
 */
function zskeleton_custom_retrieve_password_message($message, $key, $user_login, $user_data) {
    $site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    
    $message = sprintf(__('Someone has requested a password reset for the following ZSkeleton account:', 'zskeleton')) . "\r\n\r\n";
    $message .= sprintf(__('ZSkeleton Site: %s', 'zskeleton'), $site_name) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s', 'zskeleton'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, ignore this email and nothing will happen.', 'zskeleton') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following address:', 'zskeleton') . "\r\n\r\n";
    $message .= home_url('/reset-password/?key=' . $key . '&login=' . rawurlencode($user_login)) . "\r\n\r\n";
    
    if (!is_user_logged_in()) {
        $requester_ip = $_SERVER['REMOTE_ADDR'];
        if ($requester_ip) {
            $message .= sprintf(__('This password reset request originated from the IP address %s.', 'zskeleton'), $requester_ip) . "\r\n";
        }
    }
    
    return $message;
}
add_filter('retrieve_password_message', 'zskeleton_custom_retrieve_password_message', 10, 4);

/**
 * Customize password reset email subject
 */
function zskeleton_custom_retrieve_password_title($title, $user_login, $user_data) {
    return __('[ZSkeleton] Password Reset', 'zskeleton');
}
add_filter('retrieve_password_title', 'zskeleton_custom_retrieve_password_title', 10, 3);

/**
 * Customize new user notification email content for users
 */
function zskeleton_custom_new_user_notification_email($wp_new_user_notification_email, $user, $blogname) {
    $key = get_password_reset_key($user);
    if (is_wp_error($key)) {
        return $wp_new_user_notification_email;
    }
    
    $message = sprintf(__('Welcome to ZSkeleton!', 'zskeleton')) . "\r\n\r\n";
    $message .= sprintf(__('Your ZSkeleton account has been created successfully.', 'zskeleton')) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s', 'zskeleton'), $user->user_login) . "\r\n\r\n";
    $message .= __('To set your password, visit the following address:', 'zskeleton') . "\r\n\r\n";
    $message .= home_url('/reset-password/?key=' . $key . '&login=' . rawurlencode($user->user_login)) . "\r\n\r\n";
    $message .= home_url('/login/') . "\r\n\r\n";
    $message .= __('Thank you for joining ZSkeleton!', 'zskeleton');
    
    $wp_new_user_notification_email['message'] = $message;
    $wp_new_user_notification_email['subject'] = __('[ZSkeleton] Welcome - Login Details', 'zskeleton');
    
    return $wp_new_user_notification_email;
}
add_filter('wp_new_user_notification_email', 'zskeleton_custom_new_user_notification_email', 10, 3);

/**
 * Customize new user notification email content for admin
 */
function zskeleton_custom_new_user_notification_email_admin($wp_new_user_notification_email_admin, $user, $blogname) {
    $message = sprintf(__('New user registration on your ZSkeleton site %s:', 'zskeleton'), $blogname) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s', 'zskeleton'), $user->user_login) . "\r\n\r\n";
    $message .= sprintf(__('Email: %s', 'zskeleton'), $user->user_email) . "\r\n\r\n";
    $message .= __('This user has successfully registered for ZSkeleton membership.', 'zskeleton');
    
    $wp_new_user_notification_email_admin['message'] = $message;
    $wp_new_user_notification_email_admin['subject'] = __('[ZSkeleton] New User Registration', 'zskeleton');
    
    return $wp_new_user_notification_email_admin;
}
add_filter('wp_new_user_notification_email_admin', 'zskeleton_custom_new_user_notification_email_admin', 10, 3);

/**
 * Force all WordPress emails to use ZSkeleton in the subject line
 */
function zskeleton_force_email_subject($args) {
    // Check if this is an array (wp_mail format) or string
    if (is_array($args) && isset($args['subject'])) {
        $subject = $args['subject'];
        // Only modify if it's a WordPress system email (contains site name or common patterns)
        if (strpos($subject, '[') !== false && strpos($subject, ']') !== false) {
            // Extract the content after the brackets
            preg_match('/\[([^\]]+)\]\s*(.+)/', $subject, $matches);
            if (isset($matches[2])) {
                // Replace with ZSkeleton and keep the rest of the subject
                $args['subject'] = '[ZSkeleton] ' . trim($matches[2]);
            }
        }
        return $args;
    } elseif (is_string($args)) {
        // Handle direct subject string
        $subject = $args;
        if (strpos($subject, '[') !== false && strpos($subject, ']') !== false) {
            preg_match('/\[([^\]]+)\]\s*(.+)/', $subject, $matches);
            if (isset($matches[2])) {
                $subject = '[ZSkeleton] ' . trim($matches[2]);
            }
        }
        return $subject;
    }
    
    return $args;
}
add_filter('wp_mail', 'zskeleton_force_email_subject');

/**
 * Check if current user is an ZSkeleton member
 */
function zskeleton_is_member() {
    if (!is_user_logged_in()) {
        return false;
    }
    
    $user_id = get_current_user_id();
    $membership_status = get_user_meta($user_id, 'zskeleton_membership_status', true);
    
    // Consider user a member only if they have active membership status
    return $membership_status === 'active';
}

/**
 * Payment gateway classes are loaded by the ZSkeleton Membership & Payments plugin.
 */

/**
 * Check if MailerLite plugin is active and configured
 *
 * @return bool True if plugin is active and API key is set
 * @since 1.0.0
 */
function zskeleton_is_mailerlite_active() {
    // Check if the plugin class exists (plugin is active)
    if (!class_exists('MailerLiteForms\Api\PlatformAPI')) {
        return false;
    }
    
    // Check if API key is configured
    $api_key = get_option('mailerlite_api_key');
    if (empty($api_key)) {
        return false;
    }
    
    return true;
}

/**
 * Subscribe user to MailerLite newsletter
 *
 * @param string $email      Email address
 * @param array  $fields     Additional fields (name, phone, country, etc.)
 * @param string $group_id   Optional group ID to add subscriber to
 * @return bool              Success status
 * @since 1.0.0
 */
function zskeleton_mailerlite_subscribe($email, $fields = array(), $group_id = null) {
    // Validate email
    if (empty($email) || !is_email($email)) {
        return false;
    }
    
    // Check if MailerLite integration is enabled in theme settings
    $is_enabled = get_option('zskeleton_enable_mailerlite', false);
    if (!$is_enabled) {
        return false;
    }
    
    // Check if MailerLite is active and configured
    if (!zskeleton_is_mailerlite_active()) {
        return false;
    }
    
    try {
        $api_key = get_option('mailerlite_api_key');
        $API = new \MailerLiteForms\Api\PlatformAPI($api_key);
        
        // Prepare subscriber data
        $subscriber = array(
            'email'  => sanitize_email($email),
            'fields' => array(),
        );
        
        // Sanitize and add fields
        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                $subscriber['fields'][$key] = sanitize_text_field($value);
            }
        }
        
        // Add subscriber to group or general list
        // No email confirmation needed - subscribers are added directly
        if (!empty($group_id)) {
            // MailerLite API expects group ID as an array
            $groups = is_array($group_id) ? $group_id : array($group_id);
            $result = $API->addSubscriberToGroup($subscriber, $groups);
        } else {
            $result = $API->addSubscriber($subscriber);
        }
        
        // Check if result is valid (not false, not empty, or is an array/object)
        if ($result !== false && !empty($result)) {
            return true;
        } else {
            return false;
        }
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get MailerLite group ID from theme settings
 *
 * @param string $type Group type: 'individual', 'organization', 'corporate', 'general', 'tools'
 * @return string|null Group ID or null if not set
 * @since 1.0.0
 */
function zskeleton_get_mailerlite_group_id($type) {
    $setting_map = array(
        'individual'   => 'zskeleton_mailerlite_individual_group',
        'organization' => 'zskeleton_mailerlite_organization_group',
        'corporate'    => 'zskeleton_mailerlite_corporate_group',
        'general'      => 'zskeleton_mailerlite_general_group',
        'tools'        => 'zskeleton_mailerlite_tools_group',
    );
    
    if (!isset($setting_map[$type])) {
        return null;
    }
    
    $group_id = get_option($setting_map[$type], '');
    return !empty($group_id) ? $group_id : null;
}

/**
 * Whether to output the footer newsletter section.
 *
 * Shown only when MailerLite integration is enabled in theme settings and the
 * MailerLite plugin is active with an API key (footer form uses MailerLite only).
 *
 * @return bool
 */
function zskeleton_show_newsletter_section(): bool {
    $enabled = (bool) get_option( 'zskeleton_enable_mailerlite', false );
    $active  = function_exists( 'zskeleton_is_mailerlite_active' ) && zskeleton_is_mailerlite_active();
    $show    = $enabled && $active;

    /**
     * Override footer newsletter visibility after theme checks.
     *
     * @param bool $show Whether to show the section.
     */
    return (bool) apply_filters( 'zskeleton_display_newsletter_section', $show );
}

/**
 * Get MailerLite groups
 *
 * @param int $limit  Number of groups to retrieve (default: 100)
 * @param int $offset Offset for pagination (default: 0)
 * @return array|false Array of groups or false on failure
 * @since 1.0.0
 */
function zskeleton_mailerlite_get_groups($limit = 100, $offset = 0) {
    // Check if MailerLite is active and configured
    if (!zskeleton_is_mailerlite_active()) {
        return false;
    }
    
    try {
        $api_key = get_option('mailerlite_api_key');
        $API = new \MailerLiteForms\Api\PlatformAPI($api_key);
        
        $params = array(
            'limit'  => $limit,
            'offset' => $offset,
        );
        
        $groups = $API->getGroups($params);
        return $groups ?: array();
        
    } catch (Exception $e) {
        return false;
    }
}

// Tool submission email flow removed from the generalized base theme.

/**
 * Create payment order record
 *
 * @param array $order_data Order data
 * @return int|false Order ID or false on failure
 * @since 1.0.0
 */
function zskeleton_create_payment_order( $order_data ) {
    if ( ! class_exists( 'ZSkeleton_Payment_Orders' ) ) {
        return false;
    }
    
    // Idempotency check: Check if user already has a pending order for this type
    if ( isset( $order_data['user_id'] ) && isset( $order_data['order_type'] ) ) {
        $user_id    = intval( $order_data['user_id'] );
        $order_type = $order_data['order_type'];
        $item_type  = isset( $order_data['item_type'] ) ? $order_data['item_type'] : '';
        
        $existing_order = ZSkeleton_Payment_Orders::get_user_pending_order( $user_id, $order_type, $item_type );
        
        // If pending order exists, return its ID instead of creating duplicate
        if ( $existing_order ) {
            error_log( "ZSkeleton Payment Orders: Reusing existing pending order #{$existing_order->id} for user #{$user_id}" );
            return $existing_order->id;
        }
    }
    
    // Set gateway mode
    $gateway = isset( $order_data['payment_gateway'] ) ? $order_data['payment_gateway'] : ( class_exists( 'ZSkeleton_Payment_Gateway' ) ? ZSkeleton_Payment_Gateway::get_active_gateway() : 'stripe' );
    
    if ( 'stripe' === $gateway ) {
        $mode = get_option( 'zskeleton_stripe_mode', 'sandbox' );
    } else {
        $mode = get_option( 'zskeleton_paypal_mode', 'sandbox' );
    }
    
    $order_data['gateway_mode'] = $mode;
    
    // Set defaults
    if ( ! isset( $order_data['currency'] ) ) {
        $order_data['currency'] = class_exists( 'ZSkeleton_Payment_Gateway' ) ? ZSkeleton_Payment_Gateway::get_currency() : 'USD';
    }
    
    if ( ! isset( $order_data['payment_status'] ) ) {
        $order_data['payment_status'] = 'pending';
    }
    
    return ZSkeleton_Payment_Orders::create_order( $order_data );
}

/**
 * Add thumbnail column to posts admin
 */
function zskeleton_add_post_thumbnail_column( $columns ) {
    $new_columns = array();
    
    foreach ( $columns as $key => $value ) {
        if ( 'title' === $key ) {
            $new_columns['thumbnail'] = __( 'Thumbnail', 'zskeleton' );
        }
        $new_columns[ $key ] = $value;
    }
    
    return $new_columns;
}
add_filter( 'manage_posts_columns', 'zskeleton_add_post_thumbnail_column' );

/**
 * Display thumbnail in posts admin column
 */
function zskeleton_display_post_thumbnail_column( $column_name, $post_id ) {
    if ( 'thumbnail' === $column_name ) {
        $thumbnail = get_the_post_thumbnail( $post_id, array( 60, 60 ) );
        
        if ( $thumbnail ) {
            echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . $thumbnail . '</a>';
        } else {
            echo '<span style="color: #999; font-size: 12px;">' . __( 'No image', 'zskeleton' ) . '</span>';
        }
    }
}
add_action( 'manage_posts_custom_column', 'zskeleton_display_post_thumbnail_column', 10, 2 );

/**
 * Set thumbnail column width
 */
function zskeleton_post_thumbnail_column_width() {
    echo '<style>
        .column-thumbnail {
            width: 80px;
            text-align: center;
        }
        .column-thumbnail img {
            max-width: 60px;
            height: auto;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }
    </style>';
}
add_action( 'admin_head', 'zskeleton_post_thumbnail_column_width' );

/**
 * Page templates that omit Sidebar Layout + Content Restrictions meta boxes and use editor-only body copy.
 *
 * @return string[] Template slugs (e.g. page-refund-general.php).
 */
function zskeleton_page_templates_editor_only_legal(): array {
	$templates = array(
		'page-refund-general.php',
		'page-terms-general.php',
	);

	/**
	 * Filter templates treated as editor-only legal pages (no extra page meta boxes).
	 *
	 * @param string[] $templates Page template filenames.
	 */
	return apply_filters( 'zskeleton_page_templates_editor_only_legal', $templates );
}

/**
 * Whether a page uses an editor-only legal template (refund / terms general).
 *
 * @param int|null $post_id Post ID; 0 uses queried object on front, or global $post in admin when applicable.
 */
function zskeleton_page_is_editor_only_legal_template( $post_id = null ): bool {
	$post_id = null !== $post_id ? (int) $post_id : 0;
	if ( $post_id < 1 ) {
		$post_id = (int) get_queried_object_id();
	}
	if ( $post_id < 1 ) {
		global $post;
		if ( $post instanceof WP_Post && 'page' === $post->post_type ) {
			$post_id = (int) $post->ID;
		}
	}
	if ( $post_id < 1 ) {
		return false;
	}

	$tpl = (string) get_page_template_slug( $post_id );

	return in_array( $tpl, zskeleton_page_templates_editor_only_legal(), true );
}

/**
 * Whether a page is assigned as a WooCommerce system page (shop, cart, checkout, account).
 *
 * @param int $post_id Page ID.
 * @return bool
 */
function zskeleton_is_woocommerce_page_id( int $post_id ): bool {
	if ( $post_id < 1 || ! class_exists( 'WooCommerce', false ) ) {
		return false;
	}
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return false;
	}
	foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $wc_key ) {
		$wc_pid = (int) wc_get_page_id( $wc_key );
		if ( $wc_pid > 0 && $wc_pid === $post_id ) {
			return true;
		}
	}
	return false;
}

/**
 * True when the main blog index is the front page (Reading: "Your latest posts").
 *
 * @return bool
 */
function zskeleton_is_latest_posts_homepage(): bool {
	return is_front_page() && is_home();
}

/**
 * Blog posts index (Reading → a static “Posts page”), not the front page.
 *
 * @return bool
 */
function zskeleton_is_posts_page_archive(): bool {
	return is_home() && ! is_front_page();
}

/**
 * Reading → Posts page ID (0 if not set).
 *
 * @return int
 */
function zskeleton_get_page_for_posts_id(): int {
	return (int) get_option( 'page_for_posts', 0 );
}

/**
 * Whether the given page is assigned as the Posts page under Settings → Reading.
 *
 * @param int $pid Page ID.
 * @return bool
 */
function zskeleton_is_page_for_posts( int $pid ): bool {
	return $pid > 0 && zskeleton_get_page_for_posts_id() === $pid;
}

/**
 * Whether sidebar / layout helpers should read that page’s meta (singular or its post archive).
 *
 * @param int $pid Page ID.
 * @return bool
 */
function zskeleton_page_context_uses_page_sidebar_meta( int $pid ): bool {
	if ( $pid < 1 ) {
		return false;
	}
	if ( is_page( $pid ) ) {
		return true;
	}
	return zskeleton_is_posts_page_archive() && zskeleton_is_page_for_posts( $pid );
}

/**
 * Front-end views that use the Blog listing hub (static template or Posts page with that template).
 *
 * @return bool
 */
function zskeleton_is_blog_listing_public_view(): bool {
	if ( is_page_template( 'page-blog.php' ) || is_page_template( 'page-blog-blocks.php' ) ) {
		return true;
	}
	if ( ! zskeleton_is_posts_page_archive() ) {
		return false;
	}
	$pfp = zskeleton_get_page_for_posts_id();
	if ( $pfp < 1 ) {
		return false;
	}
	$tpl = (string) get_post_meta( $pfp, '_wp_page_template', true );
	$tpl = str_replace( '\\', '/', $tpl );
	$base = '' !== $tpl ? basename( $tpl ) : '';
	return 'page-blog.php' === $base || 'page-blog-blocks.php' === $base;
}

/**
 * Whether the main sidebar should show on the latest-posts homepage.
 * Disabled by default (full-width layout).
 *
 * @return bool
 */
function zskeleton_posts_home_show_sidebar(): bool {
	return '1' === (string) get_option( 'zskeleton_posts_home_show_sidebar', '0' );
}

/**
 * Whether index.php should output the main sidebar column for the current view.
 *
 * @return bool
 */
function zskeleton_index_should_show_sidebar(): bool {
	if ( zskeleton_is_latest_posts_homepage() ) {
		return zskeleton_posts_home_show_sidebar();
	}
	if ( zskeleton_is_posts_page_archive() ) {
		$pfp = zskeleton_get_page_for_posts_id();
		if ( $pfp > 0 ) {
			return zskeleton_page_sidebar_enabled( $pfp );
		}
	}
	return true;
}

/**
 * Get whether sidebar should be displayed for a given post.
 *
 * Stored on the page edit screen as `_zskeleton_show_sidebar`:
 * - 1 => show sidebar (must be enabled explicitly)
 * - 0 or unset => hide sidebar (default for static pages)
 *
 * Editor-only legal templates (refund/terms general) always use full width on the front.
 * WooCommerce system pages never use this sidebar.
 *
 * @param int|null $post_id Optional post ID. If empty, uses queried object.
 * @return bool True when sidebar should be shown.
 */
function zskeleton_page_sidebar_enabled( $post_id = null ): bool {
	$post_id = null !== $post_id ? (int) $post_id : 0;

	if ( 0 === $post_id ) {
		$post_id = (int) get_queried_object_id();
	}

	if ( 0 === $post_id ) {
		return false;
	}

	if ( zskeleton_is_woocommerce_page_id( $post_id ) ) {
		return false;
	}

	if ( zskeleton_page_is_editor_only_legal_template( $post_id ) ) {
		return false;
	}

	$raw = get_post_meta( $post_id, '_zskeleton_show_sidebar', true );
	if ( '' === $raw || null === $raw ) {
		return false;
	}

	return 1 === (int) $raw;
}

/**
 * Class string for a layout container when the page sidebar may be hidden.
 *
 * When the sidebar is off for the current page or for the latest-posts homepage (theme option), appends `{base}--no-sidebar` (e.g. `wide-container--no-sidebar`).
 *
 * @param string   $base_class    Primary class (e.g. `wide-container`, `container`, `seo-ar-container`).
 * @param string   $extra_classes Optional extra classes (space-separated).
 * @param int|null $post_id       Page ID; defaults to queried object when omitted.
 * @return string Escaped `class` attribute value.
 */
function zskeleton_page_main_container_class( string $base_class = 'wide-container', string $extra_classes = '', $post_id = null ): string {
	$base_class = '' !== trim( $base_class ) ? sanitize_html_class( $base_class ) : 'wide-container';
	if ( '' === $base_class ) {
		$base_class = 'wide-container';
	}

	$classes = array( $base_class );

	if ( zskeleton_is_latest_posts_homepage() && ! zskeleton_posts_home_show_sidebar() ) {
		$classes[] = $base_class . '--no-sidebar';
	}

	$pid = null !== $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( $pid > 0 && zskeleton_page_context_uses_page_sidebar_meta( $pid ) && ! zskeleton_page_sidebar_enabled( $pid ) ) {
		$classes[] = $base_class . '--no-sidebar';
	}

	if ( is_string( $extra_classes ) && '' !== trim( $extra_classes ) ) {
		$classes = array_merge( $classes, preg_split( '/\s+/', trim( $extra_classes ), -1, PREG_SPLIT_NO_EMPTY ) );
	}

	return esc_attr( implode( ' ', array_unique( array_filter( $classes ) ) ) );
}

/**
 * Class string for `.page-layout` when the page sidebar may be hidden or the latest-posts homepage sidebar is off.
 *
 * @param string   $extra_classes Optional extra classes (space-separated).
 * @param int|null $post_id       Page ID; defaults to queried object when omitted.
 * @return string Escaped `class` attribute value.
 */
function zskeleton_page_layout_class( string $extra_classes = '', $post_id = null ): string {
	$classes = array( 'page-layout' );

	if ( zskeleton_is_latest_posts_homepage() && ! zskeleton_posts_home_show_sidebar() ) {
		$classes[] = 'page-layout--no-sidebar';
	}

	$pid = null !== $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( $pid > 0 && zskeleton_page_context_uses_page_sidebar_meta( $pid ) && ! zskeleton_page_sidebar_enabled( $pid ) ) {
		$classes[] = 'page-layout--no-sidebar';
	}

	if ( is_string( $extra_classes ) && '' !== trim( $extra_classes ) ) {
		$classes = array_merge( $classes, preg_split( '/\s+/', trim( $extra_classes ), -1, PREG_SPLIT_NO_EMPTY ) );
	}

	return esc_attr( implode( ' ', array_unique( array_filter( $classes ) ) ) );
}

/**
 * Body class for shared blog listing layout / CSS (template or Posts page using that template).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function zskeleton_blog_listing_body_class( array $classes ): array {
	if ( zskeleton_is_blog_listing_public_view() ) {
		$classes[] = 'zskeleton-blog-listing';
	}
	return $classes;
}
add_filter( 'body_class', 'zskeleton_blog_listing_body_class' );

/**
 * Body class when hub blocks are embedded outside the official blog listing templates (scoped `blog-page.css` rules).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function zskeleton_blog_hub_embedded_blocks_body_class( array $classes ): array {
	if ( zskeleton_is_blog_listing_public_view() ) {
		return $classes;
	}
	if ( is_singular() && function_exists( 'zskeleton_post_content_has_blog_hub_blocks' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && zskeleton_post_content_has_blog_hub_blocks( $post ) ) {
			$classes[] = 'zskeleton-blog-hub-blocks';
		}
	}
	return $classes;
}
add_filter( 'body_class', 'zskeleton_blog_hub_embedded_blocks_body_class', 11 );

/**
 * Register sidebar toggle metabox for pages (skipped for editor-only legal templates).
 *
 * @param WP_Post $post Current page in the editor.
 */
function zskeleton_register_sidebar_metabox( $post ): void {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( zskeleton_page_is_editor_only_legal_template( $post->ID ) ) {
		return;
	}
	if ( zskeleton_is_woocommerce_page_id( (int) $post->ID ) ) {
		return;
	}

	add_meta_box(
		'zskeleton-sidebar-metabox',
		__( 'Sidebar Layout', 'zskeleton' ),
		function ( WP_Post $post ) {
			wp_nonce_field( 'zskeleton_save_sidebar_setting', 'zskeleton_sidebar_nonce' );

			$enabled = zskeleton_page_sidebar_enabled( $post->ID );
			?>
			<div class="zs-meta-fields zs-meta-fields--compact">
				<div class="zs-meta-field">
					<label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_show_sidebar">
						<input type="checkbox" id="zskeleton_show_sidebar" name="zskeleton_show_sidebar" value="1" <?php checked( $enabled ); ?> />
						<span><?php esc_html_e( 'Show sidebar', 'zskeleton' ); ?></span>
					</label>
					<p class="zs-meta-field__hint"><?php esc_html_e( 'Leave unchecked for full-width content. Check to show the main sidebar on this page.', 'zskeleton' ); ?></p>
				</div>
			</div>
			<?php
		},
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'zskeleton_register_sidebar_metabox', 10, 1 );

/**
 * Save sidebar toggle metabox.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post Post object.
 */
function zskeleton_save_sidebar_metabox( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['zskeleton_sidebar_nonce'] ) ) {
		return;
	}

	$nonce = (string) wp_unslash( $_POST['zskeleton_sidebar_nonce'] );
	if ( ! wp_verify_nonce( $nonce, 'zskeleton_save_sidebar_setting' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( zskeleton_page_is_editor_only_legal_template( $post_id ) ) {
		return;
	}
	if ( zskeleton_is_woocommerce_page_id( $post_id ) ) {
		return;
	}

	$enabled = isset( $_POST['zskeleton_show_sidebar'] ) ? 1 : 0;
	update_post_meta( $post_id, '_zskeleton_show_sidebar', (int) $enabled );
}
add_action( 'save_post_page', 'zskeleton_save_sidebar_metabox', 10, 2 );
