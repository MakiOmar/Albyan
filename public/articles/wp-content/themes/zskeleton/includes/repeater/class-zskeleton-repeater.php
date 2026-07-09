<?php
/**
 * Repeatable field groups (repeater API) — admin UI, JSON post meta, sanitization.
 *
 * Terminology:
 * - **Repeater** — the generic pattern: N rows of the same sub-fields (hero stats, methodology steps,
 *   tool rows, ratings, etc.). This file is the single implementation; keep the name “repeater”
 *   (same idea as ACF repeater / Form Kit `repeater` type).
 * - **Glossary** — a *use case*, not a second engine: a list of **term + definition** pairs.
 *   Use {@see zskeleton_register_glossary_group()} which wraps {@see zskeleton_register_repeater_group()}
 *   with default `term` + `definition` fields. Other “lists” (why-us bullets, blog links) stay plain repeaters
 *   with their own field shapes.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry + helpers (no UI).
 */
class ZSkeleton_Repeater_Registry {

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private static $groups = array();

	const META_PREFIX = '_zskeleton_rep_';

	/**
	 * Register a repeater group (call on after_setup_theme or init early).
	 *
	 * @param string               $group_id Unique id (alphanumeric + underscore).
	 * @param array<string,mixed> $args {
	 *   @type string   $label       Admin heading.
	 *   @type string[] $post_types  Post types that show the meta box.
	 *   @type array    $fields      Field key => array( 'type' => text|textarea|url|number|image_id, 'label' => string, optional 'editor' => textarea to force plain multiline ).
	 *   @type array    $show_if     Optional. array( 'page_template' => 'page-seo-expert.php' ) to limit visibility.
	 * }
	 */
	public static function register_group( $group_id, array $args ) {
		$group_id = sanitize_key( $group_id );
		if ( '' === $group_id ) {
			return;
		}

		$defaults = array(
			'label'      => $group_id,
			'post_types' => array( 'post' ),
			'fields'     => array(),
			'show_if'    => array(),
		);

		self::$groups[ $group_id ] = array_merge( $defaults, $args, array( 'id' => $group_id ) );
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_groups() {
		return self::$groups;
	}

	/**
	 * @param string $group_id Group id.
	 * @return array<string,mixed>|null
	 */
	public static function get_group( $group_id ) {
		$group_id = sanitize_key( $group_id );
		return isset( self::$groups[ $group_id ] ) ? self::$groups[ $group_id ] : null;
	}

	/**
	 * Meta key used in DB.
	 *
	 * @param string $group_id Group id.
	 * @return string
	 */
	public static function meta_key( $group_id ) {
		return self::META_PREFIX . sanitize_key( $group_id );
	}

	/**
	 * Get decoded rows for a post.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $group_id Group id.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_rows( $post_id, $group_id ) {
		$key  = self::meta_key( $group_id );
		$raw  = get_post_meta( $post_id, $key, true );
		if ( '' === $raw || false === $raw ) {
			return array();
		}
		if ( is_string( $raw ) ) {
			$data = json_decode( $raw, true );
			return is_array( $data ) ? $data : array();
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		return array();
	}

	/**
	 * Sanitize rows from POST structure.
	 *
	 * @param array<string,mixed> $group Group config.
	 * @param mixed               $raw   Raw rows from POST.
	 * @return array<int,array<string,mixed>>
	 */
	public static function sanitize_rows( array $group, $raw ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$fields = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : array();
		$out    = array();

		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$san = array();
			foreach ( $fields as $fname => $fconf ) {
				$type = isset( $fconf['type'] ) ? $fconf['type'] : 'text';
				$val  = isset( $row[ $fname ] ) ? $row[ $fname ] : '';

				switch ( $type ) {
					case 'textarea':
						if ( function_exists( 'zskeleton_field_config_uses_wysiwyg' ) && zskeleton_field_config_uses_wysiwyg( $fconf ) ) {
							$san[ $fname ] = wp_kses_post( wp_unslash( (string) $val ) );
						} else {
							$san[ $fname ] = sanitize_textarea_field( wp_unslash( (string) $val ) );
						}
						break;
					case 'url':
						$san[ $fname ] = esc_url_raw( wp_unslash( (string) $val ) );
						break;
					case 'number':
						$san[ $fname ] = is_numeric( $val ) ? 0 + $val : 0;
						break;
					case 'image_id':
						$san[ $fname ] = absint( $val );
						break;
					case 'text':
					default:
						$san[ $fname ] = sanitize_text_field( wp_unslash( (string) $val ) );
						break;
				}
			}
			$out[] = $san;
		}

		return $out;
	}
}

/**
 * Admin meta boxes and save.
 */
class ZSkeleton_Repeater_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Register one meta box per group per matching post type.
	 *
	 * @param string $post_type Post type.
	 * @param WP_Post $post    Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		foreach ( ZSkeleton_Repeater_Registry::get_groups() as $gid => $group ) {
			if ( ! in_array( $post_type, (array) $group['post_types'], true ) ) {
				continue;
			}

			add_meta_box(
				'zskeleton-repeater-' . $gid,
				isset( $group['label'] ) ? $group['label'] : $gid,
				function ( $p ) use ( $group, $gid ) {
					$this->render_box( $p, $group, $gid );
				},
				$post_type,
				'normal',
				'default',
				array( 'group_id' => $gid )
			);
		}
	}

	/**
	 * @param WP_Post              $post  Post.
	 * @param array<string,mixed> $group Group.
	 * @param string               $gid   Group id.
	 */
	private function render_box( $post, array $group, $gid ) {
		if ( ! $this->should_show_group( $post, $group ) ) {
			echo '<p class="description">' . esc_html__( 'Select the matching page template to edit these fields.', 'zskeleton' ) . '</p>';
			return;
		}

		wp_nonce_field( 'zskeleton_repeater_save_' . $gid, 'zskeleton_repeater_nonce_' . $gid );

		$rows   = ZSkeleton_Repeater_Registry::get_rows( $post->ID, $gid );
		$fields = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : array();

		?>
		<div class="zskeleton-repeater" data-group-id="<?php echo esc_attr( $gid ); ?>">
			<table class="widefat striped zskeleton-repeater__table">
				<thead>
					<tr>
						<?php foreach ( $fields as $fname => $fconf ) : ?>
							<th><?php echo esc_html( isset( $fconf['label'] ) ? $fconf['label'] : $fname ); ?></th>
						<?php endforeach; ?>
						<th class="zskeleton-repeater__actions"><?php esc_html_e( 'Actions', 'zskeleton' ); ?></th>
					</tr>
				</thead>
				<tbody class="zskeleton-repeater__rows">
					<?php
					if ( empty( $rows ) ) {
						$rows = array( array() );
					}
					foreach ( $rows as $ridx => $row ) {
						$this->render_row( $gid, $fields, $ridx, is_array( $row ) ? $row : array() );
					}
					?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button zskeleton-repeater__add" data-group-id="<?php echo esc_attr( $gid ); ?>">
					<?php esc_html_e( 'Add row', 'zskeleton' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * @param string               $gid    Group id.
	 * @param array<string,mixed> $fields Fields config.
	 * @param int|string           $ridx   Row index or placeholder.
	 * @param array<string,mixed> $row    Row values.
	 */
	private function render_row( $gid, array $fields, $ridx, array $row ) {
		$name_base = 'zskeleton_repeater[' . esc_attr( $gid ) . '][' . $ridx . ']';
		?>
		<tr class="zskeleton-repeater__row">
			<?php foreach ( $fields as $fname => $fconf ) : ?>
				<?php
				$type = isset( $fconf['type'] ) ? $fconf['type'] : 'text';
				$val  = isset( $row[ $fname ] ) ? $row[ $fname ] : '';
				$rid  = (int) $ridx;
				$id   = 'zskeleton_rep_' . sanitize_key( $gid ) . '_' . $rid . '_' . sanitize_key( $fname );
				$wys  = ( 'textarea' === $type && function_exists( 'zskeleton_field_config_uses_wysiwyg' ) && zskeleton_field_config_uses_wysiwyg( $fconf ) );
				$tclass = 'widefat' . ( $wys ? ' zskeleton-repeater-wysiwyg-field' : '' );
				?>
				<td>
					<?php if ( 'textarea' === $type ) : ?>
						<textarea class="<?php echo esc_attr( $tclass ); ?>" name="<?php echo esc_attr( $name_base . '[' . $fname . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" rows="3"><?php echo esc_textarea( (string) $val ); ?></textarea>
					<?php elseif ( 'image_id' === $type ) : ?>
						<input type="number" class="small-text" name="<?php echo esc_attr( $name_base . '[' . $fname . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) (int) $val ); ?>" min="0" step="1" />
					<?php elseif ( 'number' === $type ) : ?>
						<input type="number" class="small-text" name="<?php echo esc_attr( $name_base . '[' . $fname . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) $val ); ?>" step="any" />
					<?php elseif ( 'url' === $type ) : ?>
						<input type="url" class="widefat" name="<?php echo esc_attr( $name_base . '[' . $fname . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) $val ); ?>" />
					<?php else : ?>
						<input type="text" class="widefat" name="<?php echo esc_attr( $name_base . '[' . $fname . ']' ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) $val ); ?>" />
					<?php endif; ?>
				</td>
			<?php endforeach; ?>
			<td>
				<button type="button" class="button-link-delete zskeleton-repeater__remove" aria-label="<?php esc_attr_e( 'Remove row', 'zskeleton' ); ?>">&times;</button>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param WP_Post              $post  Post.
	 * @param array<string,mixed> $group Config.
	 * @return bool
	 */
	private function should_show_group( $post, array $group ) {
		$show_if = isset( $group['show_if'] ) && is_array( $group['show_if'] ) ? $group['show_if'] : array();
		if ( empty( $show_if ) ) {
			return true;
		}

		if ( isset( $show_if['page_template'] ) ) {
			$want = (string) $show_if['page_template'];
			$cur  = '';
			if ( isset( $_POST['_wp_page_template'] ) && is_string( $_POST['_wp_page_template'] ) ) {
				$cur = sanitize_text_field( wp_unslash( $_POST['_wp_page_template'] ) );
			} elseif ( $post && isset( $post->ID ) ) {
				$cur = (string) get_page_template_slug( $post );
			}
			if ( $cur !== $want ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $post || wp_is_post_revision( $post_id ) ) {
			return;
		}

		foreach ( ZSkeleton_Repeater_Registry::get_groups() as $gid => $group ) {
			if ( ! in_array( $post->post_type, (array) $group['post_types'], true ) ) {
				continue;
			}

			if ( ! isset( $_POST[ 'zskeleton_repeater_nonce_' . $gid ] ) ) {
				continue;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'zskeleton_repeater_nonce_' . $gid ] ) ), 'zskeleton_repeater_save_' . $gid ) ) {
				continue;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			if ( ! $this->should_show_group( $post, $group ) ) {
				continue;
			}

			$raw_rows = array();
			if ( isset( $_POST['zskeleton_repeater'][ $gid ] ) && is_array( $_POST['zskeleton_repeater'][ $gid ] ) ) {
				$raw_rows = wp_unslash( $_POST['zskeleton_repeater'][ $gid ] );
			}

			$clean = ZSkeleton_Repeater_Registry::sanitize_rows( $group, $raw_rows );
			$key   = ZSkeleton_Repeater_Registry::meta_key( $gid );
			update_post_meta( $post_id, $key, wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
		}
	}

	/**
	 * @param string $hook_suffix Hook.
	 */
	public function enqueue( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_editor();

		$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/js/repeater-admin.min.js' )
			? 'repeater-admin.min.js'
			: 'repeater-admin.js';
		$path         = ZSkeleton_THEME_DIR . '/assets/js/' . $file;

		wp_enqueue_script(
			'zskeleton-repeater-admin',
			ZSkeleton_THEME_URL . '/assets/js/' . $file,
			array( 'jquery', 'wp-editor' ),
			is_readable( $path ) ? (string) filemtime( $path ) : ZSkeleton_VERSION,
			true
		);

		wp_localize_script(
			'zskeleton-repeater-admin',
			'zskeletonRepeaterWysiwyg',
			array(
				'editor' => array(
					'tinymce'   => true,
					'quicktags' => true,
				),
			)
		);
	}
}

/**
 * @param int    $post_id  Post ID.
 * @param string $group_id Group id.
 * @return array<int,array<string,mixed>>
 */
function zskeleton_get_repeater( $post_id, $group_id ) {
	return ZSkeleton_Repeater_Registry::get_rows( (int) $post_id, $group_id );
}

/**
 * @param string               $group_id Group id.
 * @param array<string,mixed> $args     See ZSkeleton_Repeater_Registry::register_group.
 */
function zskeleton_register_repeater_group( $group_id, array $args ) {
	ZSkeleton_Repeater_Registry::register_group( $group_id, $args );
}

/**
 * Register a glossary-style group: each row is a **term** + **definition** (wraps the repeater API).
 *
 * Pass the same keys as {@see zskeleton_register_repeater_group()} (`post_types`, `show_if`, `label`, …).
 * Override `fields` in `$args` only if you need non-default column names.
 *
 * @param string               $group_id Unique group id (e.g. `seo_glossary`).
 * @param array<string,mixed> $args     Merged with glossary defaults.
 */
function zskeleton_register_glossary_group( $group_id, array $args ) {
	$default_fields = array(
		'term'       => array(
			'type'  => 'text',
			'label' => __( 'Term', 'zskeleton' ),
		),
		'definition' => array(
			'type'  => 'textarea',
			'label' => __( 'Definition', 'zskeleton' ),
		),
	);

	$default_fields = apply_filters( 'zskeleton_glossary_default_fields', $default_fields, $group_id );

	$defaults = array(
		'label'  => __( 'Glossary', 'zskeleton' ),
		'fields' => $default_fields,
	);

	$merged = array_merge( $defaults, $args );
	if ( empty( $merged['fields'] ) || ! is_array( $merged['fields'] ) ) {
		$merged['fields'] = $default_fields;
	}

	zskeleton_register_repeater_group( $group_id, $merged );
}

/**
 * Get glossary rows (alias of {@see zskeleton_get_repeater()} for readable call sites).
 *
 * @param int    $post_id  Post ID.
 * @param string $group_id Group id registered with {@see zskeleton_register_glossary_group()}.
 * @return array<int,array<string,mixed>>
 */
function zskeleton_get_glossary( $post_id, $group_id ) {
	return zskeleton_get_repeater( $post_id, $group_id );
}

/**
 * Bootstrap repeater admin.
 */
function zskeleton_repeater_bootstrap() {
	new ZSkeleton_Repeater_Admin();
}
add_action( 'after_setup_theme', 'zskeleton_repeater_bootstrap', 20 );
