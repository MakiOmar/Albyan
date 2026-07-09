<?php
/**
 * Frontend shortcode for managing Form Kit submissions.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * [zskeleton_form_submissions form_id="my-form" roles="editor" users="12"]
 */
class ZSkeleton_Form_Submissions_Shortcode {

	const SHORTCODE = 'zskeleton_form_submissions';

	/**
	 * @var bool
	 */
	private static $assets_enqueued = false;

	/**
	 * Register shortcode and action handlers.
	 */
	public static function init() {
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'render' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_actions' ) );
	}

	/**
	 * @param array<string,string> $atts Attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'form_id'  => '',
				'id'       => '',
				'role'     => '',
				'roles'    => '',
				'user'     => '',
				'user_id'  => '',
				'users'    => '',
				'user_ids' => '',
				'per_page' => '20',
			),
			$atts,
			self::SHORTCODE
		);

		$form_id = sanitize_key( (string) $atts['form_id'] );
		if ( '' === $form_id ) {
			$form_id = sanitize_key( (string) $atts['id'] );
		}

		if ( '' === $form_id ) {
			return self::wrap_message( __( 'Form id is required. Example: [zskeleton_form_submissions form_id="my-form"].', 'zskeleton' ), 'error' );
		}

		if ( ! class_exists( 'ZSkeleton_Form_Definition' ) || ! ZSkeleton_Form_Definition::get( $form_id ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				return self::wrap_message(
					sprintf(
						/* translators: %s: form id */
						__( 'Form “%s” was not found. Check the form id in the shortcode.', 'zskeleton' ),
						$form_id
					),
					'error'
				);
			}
			return '';
		}

		$access = ZSkeleton_Form_Submissions_Access::resolve_access( $form_id, $atts );
		if ( ! ZSkeleton_Form_Submissions_Access::can_manage( $form_id, $access ) ) {
			if ( ! is_user_logged_in() ) {
				return self::render_login_prompt();
			}
			return self::wrap_message( __( 'You do not have permission to manage submissions for this form.', 'zskeleton' ), 'error' );
		}

		self::enqueue_assets();

		$submission_id = isset( $_GET['zs_fs_submission'] ) ? absint( $_GET['zs_fs_submission'] ) : 0;
		if ( $submission_id > 0 ) {
			return self::render_single( $form_id, $submission_id, $access, $atts );
		}

		return self::render_list( $form_id, $access, $atts );
	}

	/**
	 * Handle mark read, trash, etc. via signed GET requests.
	 */
	public static function handle_actions() {
		if ( ! isset( $_GET['zs_fs_do'], $_GET['zs_fs_id'], $_GET['zs_fs_form'], $_GET['_wpnonce'] ) ) {
			return;
		}

		$form_id = sanitize_key( wp_unslash( $_GET['zs_fs_form'] ) );
		$id      = absint( $_GET['zs_fs_id'] );
		$do      = sanitize_key( wp_unslash( $_GET['zs_fs_do'] ) );

		if ( '' === $form_id || $id < 1 ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), self::action_nonce_name( $id ) ) ) {
			return;
		}

		$access = self::get_access_for_form_from_page( $form_id );
		if ( ! ZSkeleton_Form_Submissions_Access::can_manage( $form_id, $access ) ) {
			return;
		}

		if ( ! ZSkeleton_Form_Submissions_Repository::belongs_to_form( $id, $form_id ) ) {
			return;
		}

		self::run_action( $do, array( $id ) );

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = home_url( '/' );
		}

		$redirect = add_query_arg(
			array(
				'zs_fs_updated' => '1',
			),
			remove_query_arg( array( 'zs_fs_do', 'zs_fs_id', 'zs_fs_form', '_wpnonce' ), $redirect )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * @param string              $form_id Form id.
	 * @param array{roles: string[], users: int[]} $access Access config.
	 * @param array<string,string> $atts Shortcode atts.
	 * @return string
	 */
	private static function render_list( $form_id, array $access, array $atts ) {
		unset( $access );

		$status = isset( $_GET['zs_fs_status'] ) ? sanitize_key( wp_unslash( $_GET['zs_fs_status'] ) ) : '';
		$search = isset( $_GET['zs_fs_search'] ) ? sanitize_text_field( wp_unslash( $_GET['zs_fs_search'] ) ) : '';
		$page   = isset( $_GET['zs_fs_page'] ) ? max( 1, absint( $_GET['zs_fs_page'] ) ) : 1;
		$per_page = max( 1, min( 50, absint( $atts['per_page'] ) ) );

		$result = ZSkeleton_Form_Submissions_Repository::query(
			array(
				'form_id'  => $form_id,
				'status'   => $status,
				'search'   => $search,
				'per_page' => $per_page,
				'page'     => $page,
			)
		);

		$counts   = ZSkeleton_Form_Submissions_Repository::get_status_counts( $form_id );
		$base_url = self::get_base_url( $form_id );
		$tabs     = array(
			'' => __( 'All', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_NEW => __( 'New', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_READ => __( 'Read', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_SPAM => __( 'Spam', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_TRASH => __( 'Trash', 'zskeleton' ),
		);

		ob_start();
		?>
		<div class="<?php echo esc_attr( self::wrapper_classes() ); ?>" data-zs-form-id="<?php echo esc_attr( $form_id ); ?>"<?php echo self::wrapper_dir_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( isset( $_GET['zs_fs_updated'] ) ) : ?>
				<div class="zs-fb-submissions-front__notice zs-fb-submissions-front__notice--success"><?php esc_html_e( 'Submissions updated.', 'zskeleton' ); ?></div>
			<?php endif; ?>

			<div class="zs-fb-submissions-front__header">
				<div>
					<h2 class="zs-fb-submissions-front__title"><?php echo esc_html( ZSkeleton_Form_Submissions_Repository::get_form_label( $form_id ) ); ?></h2>
					<p class="zs-fb-submissions-front__form-id"><?php echo esc_html( $form_id ); ?></p>
				</div>
			</div>

			<div class="zs-fb-submissions-stats">
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) (int) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_NEW ] ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'New', 'zskeleton' ); ?></span>
				</div>
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) (int) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_READ ] ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'Read', 'zskeleton' ); ?></span>
				</div>
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) (int) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_SPAM ] ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'Spam', 'zskeleton' ); ?></span>
				</div>
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) array_sum( $counts ) ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'Total', 'zskeleton' ); ?></span>
				</div>
			</div>

			<ul class="zs-fb-submissions-front__tabs">
				<?php foreach ( $tabs as $tab_status => $label ) : ?>
					<?php
					$tab_url = '' === $tab_status
						? remove_query_arg( array( 'zs_fs_status', 'zs_fs_page' ), $base_url )
						: add_query_arg( array( 'zs_fs_status' => $tab_status, 'zs_fs_page' => false ), $base_url );
					$count   = '' === $tab_status
						? array_sum( $counts )
						: (int) $counts[ $tab_status ];
					?>
					<li class="zs-fb-submissions-front__tab<?php echo $status === $tab_status ? ' is-active' : ''; ?>">
						<a href="<?php echo esc_url( $tab_url ); ?>"><?php echo esc_html( $label . ' (' . $count . ')' ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>

			<form class="zs-fb-submissions-front__search" method="get" action="<?php echo esc_url( $base_url ); ?>">
				<label class="screen-reader-text" for="zs-fs-search-<?php echo esc_attr( $form_id ); ?>"><?php esc_html_e( 'Search submissions', 'zskeleton' ); ?></label>
				<input type="search" id="zs-fs-search-<?php echo esc_attr( $form_id ); ?>" name="zs_fs_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search submissions…', 'zskeleton' ); ?>" />
				<?php if ( '' !== $status ) : ?>
					<input type="hidden" name="zs_fs_status" value="<?php echo esc_attr( $status ); ?>" />
				<?php endif; ?>
				<button type="submit" class="zs-fb-submissions-front__btn zs-fb-submissions-front__btn--primary"><?php esc_html_e( 'Search', 'zskeleton' ); ?></button>
				<?php if ( '' !== $search ) : ?>
					<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( remove_query_arg( array( 'zs_fs_search', 'zs_fs_page' ), $base_url ) ); ?>"><?php esc_html_e( 'Clear', 'zskeleton' ); ?></a>
				<?php endif; ?>
			</form>

			<div class="zs-fb-submissions-table-card">
				<?php if ( empty( $result['items'] ) ) : ?>
					<p class="zs-fb-submissions-front__empty"><?php esc_html_e( 'No submissions found.', 'zskeleton' ); ?></p>
				<?php else : ?>
					<div class="zs-fb-submissions-front__table-wrap">
						<table class="zs-fb-submissions-front__table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'ID', 'zskeleton' ); ?></th>
									<th><?php esc_html_e( 'Status', 'zskeleton' ); ?></th>
									<th><?php esc_html_e( 'Submitted', 'zskeleton' ); ?></th>
									<th><?php esc_html_e( 'Preview', 'zskeleton' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'zskeleton' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $result['items'] as $row ) : ?>
									<?php
									$row_id    = (int) $row['id'];
									$view_url  = add_query_arg( 'zs_fs_submission', $row_id, $base_url );
									$preview   = self::get_payload_preview( isset( $row['payload'] ) ? $row['payload'] : array() );
									$row_nonce = wp_create_nonce( self::action_nonce_name( $row_id ) );
									?>
									<tr>
										<td><a href="<?php echo esc_url( $view_url ); ?>">#<?php echo esc_html( (string) $row_id ); ?></a></td>
										<td><?php echo wp_kses_post( self::render_status_badge( (string) $row['status'] ) ); ?></td>
										<td><?php echo esc_html( self::format_datetime( (string) $row['created_at'] ) ); ?></td>
										<td><?php echo esc_html( $preview ); ?></td>
										<td>
											<div class="zs-fb-submissions-front__actions">
												<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e( 'View', 'zskeleton' ); ?></a>
												<?php if ( ZSkeleton_Form_Submissions_Repository::STATUS_TRASH !== $row['status'] ) : ?>
													<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( self::action_url( 'mark_read', $row_id, $form_id, $row_nonce ) ); ?>"><?php esc_html_e( 'Read', 'zskeleton' ); ?></a>
													<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( self::action_url( 'trash', $row_id, $form_id, $row_nonce ) ); ?>"><?php esc_html_e( 'Trash', 'zskeleton' ); ?></a>
												<?php else : ?>
													<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( self::action_url( 'restore', $row_id, $form_id, $row_nonce ) ); ?>"><?php esc_html_e( 'Restore', 'zskeleton' ); ?></a>
													<a class="zs-fb-submissions-front__btn zs-fb-submissions-front__btn--danger" href="<?php echo esc_url( self::action_url( 'delete', $row_id, $form_id, $row_nonce ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this submission permanently?', 'zskeleton' ) ); ?>');"><?php esc_html_e( 'Delete', 'zskeleton' ); ?></a>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>

			<?php echo wp_kses_post( self::render_pagination( $base_url, $page, $per_page, (int) $result['total'], $status, $search ) ); ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param string              $form_id Form id.
	 * @param int                 $id Submission id.
	 * @param array{roles: string[], users: int[]} $access Access config.
	 * @param array<string,string> $atts Shortcode atts.
	 * @return string
	 */
	private static function render_single( $form_id, $id, array $access, array $atts ) {
		unset( $access, $atts );

		$row = ZSkeleton_Form_Submissions_Repository::get( $id );
		if ( ! $row || sanitize_key( (string) $row['form_id'] ) !== $form_id ) {
			return self::wrap_message( __( 'Submission not found.', 'zskeleton' ), 'error' );
		}

		if ( ZSkeleton_Form_Submissions_Repository::STATUS_NEW === $row['status'] ) {
			ZSkeleton_Form_Submissions_Repository::mark_read( $id );
			$row['status'] = ZSkeleton_Form_Submissions_Repository::STATUS_READ;
		}

		$field_labels = self::get_field_labels( $form_id );
		$list_url     = self::get_base_url( $form_id );
		$nonce        = wp_create_nonce( self::action_nonce_name( $id ) );

		ob_start();
		?>
		<div class="<?php echo esc_attr( self::wrapper_classes( 'zs-fb-submissions-wrap--single' ) ); ?>"<?php echo self::wrapper_dir_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( isset( $_GET['zs_fs_updated'] ) ) : ?>
				<div class="zs-fb-submissions-front__notice zs-fb-submissions-front__notice--success"><?php esc_html_e( 'Submissions updated.', 'zskeleton' ); ?></div>
			<?php endif; ?>

			<p class="zs-fb-submissions-front__back">
				<a class="zs-fb-submissions-front__back-link" href="<?php echo esc_url( $list_url ); ?>">
					<?php esc_html_e( 'Back to submissions', 'zskeleton' ); ?>
				</a>
			</p>

			<h2 class="zs-fb-submissions-front__title"><?php printf( esc_html__( 'Submission #%d', 'zskeleton' ), (int) $row['id'] ); ?></h2>

			<div class="zs-fb-submissions-detail-grid">
				<div class="zs-fb-submissions-front__panel">
					<h3 class="zs-fb-submissions-front__panel-title"><?php esc_html_e( 'Details', 'zskeleton' ); ?></h3>
					<div class="zs-fb-submissions-front__panel-body">
						<table class="zs-fb-submissions-front__meta-table">
							<tbody>
								<tr>
									<th><?php esc_html_e( 'Form', 'zskeleton' ); ?></th>
									<td>
										<strong><?php echo esc_html( ZSkeleton_Form_Submissions_Repository::get_form_label( $form_id ) ); ?></strong><br>
										<code><?php echo esc_html( $form_id ); ?></code>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Status', 'zskeleton' ); ?></th>
									<td><?php echo wp_kses_post( self::render_status_badge( (string) $row['status'] ) ); ?></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Submitted', 'zskeleton' ); ?></th>
									<td><?php echo esc_html( self::format_datetime( (string) $row['created_at'] ) ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="zs-fb-submissions-front__panel">
					<h3 class="zs-fb-submissions-front__panel-title"><?php esc_html_e( 'Actions', 'zskeleton' ); ?></h3>
					<div class="zs-fb-submissions-front__panel-body zs-fb-submissions-actions">
						<?php if ( ZSkeleton_Form_Submissions_Repository::STATUS_TRASH !== $row['status'] ) : ?>
							<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( self::action_url( 'mark_read', $id, $form_id, $nonce ) ); ?>"><?php esc_html_e( 'Mark as read', 'zskeleton' ); ?></a>
							<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( self::action_url( 'mark_spam', $id, $form_id, $nonce ) ); ?>"><?php esc_html_e( 'Mark as spam', 'zskeleton' ); ?></a>
							<a class="zs-fb-submissions-front__btn" href="<?php echo esc_url( self::action_url( 'trash', $id, $form_id, $nonce ) ); ?>"><?php esc_html_e( 'Move to trash', 'zskeleton' ); ?></a>
						<?php else : ?>
							<a class="zs-fb-submissions-front__btn zs-fb-submissions-front__btn--primary" href="<?php echo esc_url( self::action_url( 'restore', $id, $form_id, $nonce ) ); ?>"><?php esc_html_e( 'Restore', 'zskeleton' ); ?></a>
							<a class="zs-fb-submissions-front__btn zs-fb-submissions-front__btn--danger" href="<?php echo esc_url( self::action_url( 'delete', $id, $form_id, $nonce ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this submission permanently?', 'zskeleton' ) ); ?>');"><?php esc_html_e( 'Delete permanently', 'zskeleton' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="zs-fb-submissions-front__panel">
				<h3 class="zs-fb-submissions-front__panel-title"><?php esc_html_e( 'Submitted fields', 'zskeleton' ); ?></h3>
				<div class="zs-fb-submissions-front__panel-body">
					<?php if ( empty( $row['payload'] ) ) : ?>
						<p class="zs-fb-submissions-muted"><?php esc_html_e( 'No field data was stored for this submission.', 'zskeleton' ); ?></p>
					<?php else : ?>
						<div class="zs-fb-submissions-front__table-wrap">
							<table class="zs-fb-submissions-front__table zs-fb-submissions-fields-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Field', 'zskeleton' ); ?></th>
										<th><?php esc_html_e( 'Value', 'zskeleton' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $row['payload'] as $key => $value ) : ?>
										<tr>
											<td>
												<strong><?php echo esc_html( isset( $field_labels[ $key ] ) ? $field_labels[ $key ] : (string) $key ); ?></strong><br>
												<code><?php echo esc_html( (string) $key ); ?></code>
											</td>
											<td><?php echo esc_html( is_array( $value ) ? wp_json_encode( $value, JSON_UNESCAPED_UNICODE ) : (string) $value ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Resolve shortcode access rules from the current page content.
	 *
	 * @param string $form_id Form id.
	 * @return array{roles: string[], users: int[]}
	 */
	public static function get_access_for_form_from_page( $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id || ! is_singular() ) {
			return array(
				'roles' => array(),
				'users' => array(),
			);
		}

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return array(
				'roles' => array(),
				'users' => array(),
			);
		}

		$atts = self::get_shortcode_atts_from_content( (string) $post->post_content, $form_id );
		return ZSkeleton_Form_Submissions_Access::resolve_access( $form_id, $atts );
	}

	/**
	 * Shortcode attributes for a form on a page (empty when not found).
	 *
	 * @param string $content Post content.
	 * @param string $form_id Form id.
	 * @return array<string,string>
	 */
	public static function get_shortcode_atts_from_content( $content, $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id || '' === $content || ! has_shortcode( $content, self::SHORTCODE ) ) {
			return array();
		}

		$pattern = get_shortcode_regex( array( self::SHORTCODE ) );
		if ( ! preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			return array();
		}

		foreach ( $matches as $match ) {
			$atts = shortcode_parse_atts( isset( $match[3] ) ? $match[3] : '' );
			if ( ! is_array( $atts ) ) {
				continue;
			}

			$atts    = array_map( 'strval', $atts );
			$sc_form = isset( $atts['form_id'] ) ? sanitize_key( $atts['form_id'] ) : '';
			if ( '' === $sc_form && isset( $atts['id'] ) ) {
				$sc_form = sanitize_key( $atts['id'] );
			}

			if ( $sc_form === $form_id ) {
				return $atts;
			}
		}

		return array();
	}

	/**
	 * @param string $content Post content.
	 * @param string $form_id Form id.
	 * @return array{roles: string[], users: int[]}
	 */
	public static function parse_access_from_content( $content, $form_id ) {
		return ZSkeleton_Form_Submissions_Access::resolve_access(
			$form_id,
			self::get_shortcode_atts_from_content( $content, $form_id )
		);
	}

	/**
	 * For GET actions, admins always pass; others must have been allowed when viewing the page.
	 * Non-admins are validated by nonce + logged-in state + submission ownership.
	 *
	 * @param string $action Action key.
	 * @param int[]  $ids Submission ids.
	 */
	private static function run_action( $action, array $ids ) {
		switch ( $action ) {
			case 'mark_read':
				ZSkeleton_Form_Submissions_Repository::update_status( $ids, ZSkeleton_Form_Submissions_Repository::STATUS_READ );
				break;
			case 'mark_spam':
				ZSkeleton_Form_Submissions_Repository::update_status( $ids, ZSkeleton_Form_Submissions_Repository::STATUS_SPAM );
				break;
			case 'trash':
				ZSkeleton_Form_Submissions_Repository::update_status( $ids, ZSkeleton_Form_Submissions_Repository::STATUS_TRASH );
				break;
			case 'restore':
				ZSkeleton_Form_Submissions_Repository::update_status( $ids, ZSkeleton_Form_Submissions_Repository::STATUS_READ );
				break;
			case 'delete':
				ZSkeleton_Form_Submissions_Repository::delete( $ids );
				break;
		}
	}

	/**
	 * @param string $form_id Form id.
	 * @return string
	 */
	private static function get_base_url( $form_id ) {
		unset( $form_id );
		$url = get_permalink();
		if ( ! $url ) {
			$url = home_url( add_query_arg( array() ) );
		}
		return remove_query_arg( array( 'zs_fs_do', 'zs_fs_id', 'zs_fs_form', '_wpnonce', 'zs_fs_updated' ), $url );
	}

	/**
	 * @param string $do Action.
	 * @param int    $id Submission id.
	 * @param string $form_id Form id.
	 * @param string $nonce Nonce.
	 * @return string
	 */
	private static function action_url( $do, $id, $form_id, $nonce ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'zs_fs_do'   => sanitize_key( $do ),
					'zs_fs_id'   => absint( $id ),
					'zs_fs_form' => sanitize_key( $form_id ),
				),
				self::get_base_url( $form_id )
			),
			self::action_nonce_name( $id ),
			'_wpnonce'
		);
	}

	/**
	 * @param int $id Submission id.
	 * @return string
	 */
	private static function action_nonce_name( $id ) {
		return 'zs_fs_submission_' . absint( $id );
	}

	/**
	 * @param string $form_id Form id.
	 * @return array<string,string>
	 */
	private static function get_field_labels( $form_id ) {
		$labels = array();
		if ( ! class_exists( 'ZSkeleton_Form_Definition' ) ) {
			return $labels;
		}

		$def = ZSkeleton_Form_Definition::get( $form_id );
		if ( ! $def ) {
			return $labels;
		}

		foreach ( $def->get_fields_by_name() as $name => $field ) {
			$labels[ $name ] = isset( $field['label'] ) ? (string) $field['label'] : $name;
		}

		return $labels;
	}

	/**
	 * @param array<string,mixed> $payload Payload.
	 * @return string
	 */
	private static function get_payload_preview( array $payload ) {
		if ( empty( $payload ) ) {
			return '—';
		}

		$parts = array();
		foreach ( $payload as $value ) {
			if ( is_array( $value ) ) {
				$parts[] = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
			} else {
				$parts[] = (string) $value;
			}
			if ( count( $parts ) >= 2 ) {
				break;
			}
		}

		$text = implode( ' · ', $parts );
		if ( strlen( $text ) > 80 ) {
			$text = substr( $text, 0, 77 ) . '…';
		}

		return $text;
	}

	/**
	 * @param string $status Status slug.
	 * @return string
	 */
	private static function render_status_badge( $status ) {
		$status = sanitize_key( (string) $status );
		$labels = array(
			ZSkeleton_Form_Submissions_Repository::STATUS_NEW   => __( 'New', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_READ  => __( 'Read', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_SPAM  => __( 'Spam', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_TRASH => __( 'Trash', 'zskeleton' ),
		);
		$label  = isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
		return '<span class="zs-fb-submission-status zs-fb-submission-status--' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * @param string $datetime UTC mysql datetime.
	 * @return string
	 */
	private static function format_datetime( $datetime ) {
		$ts = '' !== $datetime ? strtotime( $datetime . ' UTC' ) : false;
		return $ts ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts ) : $datetime;
	}

	/**
	 * @param string $base_url Base list URL.
	 * @param int    $page Current page.
	 * @param int    $per_page Per page.
	 * @param int    $total Total items.
	 * @param string $status Status filter.
	 * @param string $search Search query.
	 * @return string
	 */
	private static function render_pagination( $base_url, $page, $per_page, $total, $status, $search ) {
		$total_pages = (int) ceil( $total / max( 1, $per_page ) );
		if ( $total_pages < 2 ) {
			return '';
		}

		$html = '<nav class="zs-fb-submissions-front__pagination" aria-label="' . esc_attr__( 'Submissions pages', 'zskeleton' ) . '">';
		for ( $i = 1; $i <= $total_pages; $i++ ) {
			$args = array( 'zs_fs_page' => $i );
			if ( '' !== $status ) {
				$args['zs_fs_status'] = $status;
			}
			if ( '' !== $search ) {
				$args['zs_fs_search'] = $search;
			}
			$url = add_query_arg( $args, $base_url );
			if ( $i === $page ) {
				$html .= '<span class="current">' . esc_html( (string) $i ) . '</span>';
			} else {
				$html .= '<a href="' . esc_url( $url ) . '">' . esc_html( (string) $i ) . '</a>';
			}
		}
		$html .= '</nav>';
		return $html;
	}

	/**
	 * @return string
	 */
	private static function render_login_prompt() {
		$login_url = wp_login_url( get_permalink() ? get_permalink() : home_url( '/' ) );
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::wrapper_classes() ); ?>"<?php echo self::wrapper_dir_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<p class="zs-fb-submissions-front__login">
				<?php
				printf(
					wp_kses(
						/* translators: %s: login URL */
						__( 'Please <a href="%s">log in</a> to manage form submissions.', 'zskeleton' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( $login_url )
				);
				?>
			</p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param string $message Message.
	 * @param string $type success|error.
	 * @return string
	 */
	private static function wrap_message( $message, $type = 'error' ) {
		$class = 'error' === $type ? 'zs-fb-submissions-front__notice--error' : 'zs-fb-submissions-front__notice--success';
		return '<div class="' . esc_attr( self::wrapper_classes() ) . '"' . self::wrapper_dir_attr() . '><div class="zs-fb-submissions-front__notice ' . esc_attr( $class ) . '">' . esc_html( $message ) . '</div></div>';
	}

	/**
	 * Wrapper CSS classes (includes RTL hook class).
	 *
	 * @param string $extra Optional extra class.
	 * @return string
	 */
	private static function wrapper_classes( $extra = '' ) {
		$classes = array( 'zs-fb-submissions-front', 'zs-fb-submissions-wrap' );
		if ( '' !== $extra ) {
			$classes[] = $extra;
		}
		if ( is_rtl() ) {
			$classes[] = 'is-rtl';
		}
		return implode( ' ', $classes );
	}

	/**
	 * Explicit text direction for the submissions UI (matches site locale).
	 *
	 * @return string HTML dir attribute.
	 */
	private static function wrapper_dir_attr() {
		return is_rtl() ? ' dir="rtl"' : ' dir="ltr"';
	}

	/**
	 * Enqueue shared + frontend styles once per request.
	 */
	private static function enqueue_assets() {
		if ( self::$assets_enqueued ) {
			return;
		}
		self::$assets_enqueued = true;

		$admin_css = ZSkeleton_THEME_DIR . '/assets/css/form-submissions-admin.css';
		$front_css = ZSkeleton_THEME_DIR . '/assets/css/form-submissions-frontend.css';

		wp_enqueue_style(
			'zskeleton-form-submissions-shared',
			ZSkeleton_THEME_URL . '/assets/css/form-submissions-admin.css',
			array(),
			file_exists( $admin_css ) ? (string) filemtime( $admin_css ) : ZSkeleton_VERSION
		);

		wp_enqueue_style(
			'zskeleton-form-submissions-frontend',
			ZSkeleton_THEME_URL . '/assets/css/form-submissions-frontend.css',
			array( 'zskeleton-form-submissions-shared' ),
			file_exists( $front_css ) ? (string) filemtime( $front_css ) : ZSkeleton_VERSION
		);
	}
}
