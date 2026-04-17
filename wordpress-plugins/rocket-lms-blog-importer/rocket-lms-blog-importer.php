<?php
/**
 * Plugin Name: Rocket LMS Blog Importer
 * Description: Import blog posts from a Rocket LMS JSON export (title, description, content, featured image).
 * Version: 1.0.0
 * Author: Rocket LMS
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: rocket-lms-blog-importer
 *
 * @package Rocket_LMS_Blog_Importer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin UI and JSON import for Rocket LMS blog export format.
 */
final class Rocket_LMS_Blog_Importer
{
    private const META_SOURCE_ID = '_rocket_lms_source_id';

    private const META_LOCALE = '_rocket_lms_locale';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_tools_page']);
    }

    public function register_tools_page(): void
    {
        add_management_page(
            __('Rocket LMS Blog Import', 'rocket-lms-blog-importer'),
            __('Rocket LMS Blog Import', 'rocket-lms-blog-importer'),
            'manage_options',
            'rocket-lms-blog-importer',
            [$this, 'render_tools_page']
        );
    }

    public function render_tools_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $messages = [];
        if (isset($_POST['rocket_lms_bi_nonce']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('rocket_lms_bi_import', 'rocket_lms_bi_nonce');
            $messages = $this->handle_import();
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(__('Rocket LMS Blog Import', 'rocket-lms-blog-importer')); ?></h1>
            <p><?php echo esc_html(__('Upload the JSON file downloaded from your Rocket LMS admin: Blog → Export for WordPress (JSON).', 'rocket-lms-blog-importer')); ?></p>

            <?php foreach ($messages as $m) : ?>
                <div class="<?php echo esc_attr($m['type']); ?>"><p><?php echo esc_html($m['text']); ?></p></div>
            <?php endforeach; ?>

            <form method="post" enctype="multipart/form-data" action="">
                <?php wp_nonce_field('rocket_lms_bi_import', 'rocket_lms_bi_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="rocket_lms_bi_file"><?php echo esc_html(__('JSON file', 'rocket-lms-blog-importer')); ?></label></th>
                        <td><input type="file" name="import_file" id="rocket_lms_bi_file" accept=".json,application/json" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html(__('If the same post is imported again', 'rocket-lms-blog-importer')); ?></th>
                        <td>
                            <label><input type="radio" name="duplicate_mode" value="skip" checked /> <?php echo esc_html(__('Skip (keep existing)', 'rocket-lms-blog-importer')); ?></label><br />
                            <label><input type="radio" name="duplicate_mode" value="update" /> <?php echo esc_html(__('Update existing WordPress post', 'rocket-lms-blog-importer')); ?></label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Import posts', 'rocket-lms-blog-importer')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    private function handle_import(): array
    {
        $out = [];

        if (empty($_FILES['import_file']['tmp_name'])) {
            $out[] = ['type' => 'notice notice-error', 'text' => __('No file uploaded.', 'rocket-lms-blog-importer')];

            return $out;
        }

        if (!empty($_FILES['import_file']['error'])) {
            $out[] = ['type' => 'notice notice-error', 'text' => __('Upload error.', 'rocket-lms-blog-importer')];

            return $out;
        }

        $raw = file_get_contents($_FILES['import_file']['tmp_name']);
        if ($raw === false) {
            $out[] = ['type' => 'notice notice-error', 'text' => __('Could not read the file.', 'rocket-lms-blog-importer')];

            return $out;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || ($data['format'] ?? '') !== 'rocket_lms_blog_export') {
            $out[] = ['type' => 'notice notice-error', 'text' => __('Invalid Rocket LMS export file (missing format).', 'rocket-lms-blog-importer')];

            return $out;
        }

        $posts = $data['posts'] ?? null;
        if (!is_array($posts)) {
            $out[] = ['type' => 'notice notice-error', 'text' => __('Export contains no posts array.', 'rocket-lms-blog-importer')];

            return $out;
        }

        $duplicateMode = isset($_POST['duplicate_mode']) && $_POST['duplicate_mode'] === 'update' ? 'update' : 'skip';
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($posts as $row) {
            if (!is_array($row)) {
                continue;
            }

            $title = isset($row['title']) ? (string) $row['title'] : '';
            if ($title === '') {
                $errors++;

                continue;
            }

            $sourceId = isset($row['source_id']) ? (int) $row['source_id'] : 0;
            $locale = isset($row['locale']) ? sanitize_key((string) $row['locale']) : 'default';
            $slug = isset($row['slug']) ? sanitize_title((string) $row['slug']) : '';
            $description = isset($row['description']) ? (string) $row['description'] : '';
            $content = isset($row['content']) ? (string) $row['content'] : '';
            $imageUrl = isset($row['image']) ? esc_url_raw((string) $row['image']) : '';
            $status = (isset($row['status']) && $row['status'] === 'publish') ? 'publish' : 'draft';

            $postContent = $content !== '' ? $content : $description;
            $excerpt = wp_strip_all_tags($description);

            $existingId = $this->find_existing_post_id($sourceId, $locale);

            if ($existingId && $duplicateMode === 'skip') {
                $skipped++;

                continue;
            }

            $postarr = [
                'post_title' => wp_slash($title),
                'post_content' => wp_slash($postContent),
                'post_excerpt' => wp_slash($excerpt),
                'post_status' => $status,
                'post_type' => 'post',
            ];

            if ($slug !== '') {
                $postarr['post_name'] = $slug;
            }

            if ($existingId && $duplicateMode === 'update') {
                $postarr['ID'] = $existingId;
                $postId = wp_update_post($postarr, true);
            } else {
                $postId = wp_insert_post($postarr, true);
            }

            if (is_wp_error($postId)) {
                $errors++;

                continue;
            }

            if ($sourceId > 0) {
                update_post_meta($postId, self::META_SOURCE_ID, $sourceId);
            }
            update_post_meta($postId, self::META_LOCALE, $locale);

            if ($imageUrl !== '') {
                $this->attach_featured_image($postId, $imageUrl, $title);
            }

            if ($existingId && $duplicateMode === 'update') {
                $updated++;
            } else {
                $created++;
            }
        }

        $out[] = [
            'type' => 'notice notice-success',
            'text' => sprintf(
                /* translators: 1: created count, 2: updated, 3: skipped, 4: errors */
                __('Finished: %1$d created, %2$d updated, %3$d skipped, %4$d errors.', 'rocket-lms-blog-importer'),
                $created,
                $updated,
                $skipped,
                $errors
            ),
        ];

        return $out;
    }

    private function find_existing_post_id(int $sourceId, string $locale): int
    {
        if ($sourceId <= 0) {
            return 0;
        }

        $q = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => self::META_SOURCE_ID,
                    'value' => $sourceId,
                ],
                [
                    'key' => self::META_LOCALE,
                    'value' => $locale,
                ],
            ],
        ]);

        if ($q->have_posts()) {
            return (int) $q->posts[0];
        }

        return 0;
    }

    private function attach_featured_image(int $postId, string $imageUrl, string $title): void
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($imageUrl);
        if (is_wp_error($tmp)) {
            return;
        }

        $path = (string) parse_url($imageUrl, PHP_URL_PATH);
        $basename = $path !== '' ? basename($path) : 'featured.jpg';
        if ($basename === '' || $basename === '/') {
            $basename = 'featured.jpg';
        }

        $file_array = [
            'name' => sanitize_file_name($basename),
            'tmp_name' => $tmp,
        ];

        $attId = media_handle_sideload($file_array, $postId, $title);
        if (is_wp_error($attId)) {
            @unlink($file_array['tmp_name']);

            return;
        }

        set_post_thumbnail($postId, (int) $attId);
    }
}

new Rocket_LMS_Blog_Importer();
