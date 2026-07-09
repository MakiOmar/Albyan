<?php
/**
 * Form submissions admin list and detail screens.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Submissions list table.
 */
class ZSkeleton_Form_Submissions_List_Table extends WP_List_Table {

	/**
	 * @var string
	 */
	private $form_filter = '';

	/**
	 * @var string
	 */
	private $status_filter = '';

	/**
	 * @var string
	 */
	private $search = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'submission',
				'plural'   => 'submissions',
				'ajax'     => false,
			)
		);

		$this->form_filter   = isset( $_REQUEST['form_id'] ) ? sanitize_key( wp_unslash( $_REQUEST['form_id'] ) ) : '';
		$this->status_filter = isset( $_REQUEST['status'] ) ? sanitize_key( wp_unslash( $_REQUEST['status'] ) ) : '';
		$this->search        = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
	}

	/**
	 * @return array<string,string>
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'id'         => __( 'ID', 'zskeleton' ),
			'form_id'    => __( 'Form', 'zskeleton' ),
			'summary'    => __( 'Summary', 'zskeleton' ),
			'status'     => __( 'Status', 'zskeleton' ),
			'created_at' => __( 'Date', 'zskeleton' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_sortable_columns() {
		return array(
			'id'         => array( 'id', false ),
			'form_id'    => array( 'form_id', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_bulk_actions() {
		if ( ZSkeleton_Form_Submissions_Repository::STATUS_TRASH === $this->status_filter ) {
			return array(
				'restore' => __( 'Restore', 'zskeleton' ),
				'delete'  => __( 'Delete permanently', 'zskeleton' ),
			);
		}

		return array(
			'mark_read' => __( 'Mark as read', 'zskeleton' ),
			'mark_spam' => __( 'Mark as spam', 'zskeleton' ),
			'trash'     => __( 'Move to trash', 'zskeleton' ),
		);
	}

	/**
	 * Status filter links.
	 */
	protected function get_views() {
		$base_url = admin_url( 'admin.php?page=zskeleton-form-submissions' );
		if ( '' !== $this->form_filter ) {
			$base_url = add_query_arg( 'form_id', $this->form_filter, $base_url );
		}

		$counts  = ZSkeleton_Form_Submissions_Repository::get_status_counts( $this->form_filter );
		$current = '' !== $this->status_filter ? $this->status_filter : 'all';
		$all     = array_sum( $counts ) - (int) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_TRASH ];

		$views = array(
			'all' => sprintf(
				'<a href="%s"%s>%s</a> <span class="count">(%d)</span>',
				esc_url( $base_url ),
				'all' === $current ? ' class="current"' : '',
				esc_html__( 'All', 'zskeleton' ),
				(int) $all
			),
		);

		$labels = array(
			ZSkeleton_Form_Submissions_Repository::STATUS_NEW  => __( 'New', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_READ => __( 'Read', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_SPAM => __( 'Spam', 'zskeleton' ),
			ZSkeleton_Form_Submissions_Repository::STATUS_TRASH => __( 'Trash', 'zskeleton' ),
		);

		foreach ( $labels as $status => $label ) {
			$url = add_query_arg( 'status', $status, $base_url );
			$views[ $status ] = sprintf(
				'<a href="%s"%s>%s</a> <span class="count">(%d)</span>',
				esc_url( $url ),
				$status === $current ? ' class="current"' : '',
				esc_html( $label ),
				(int) $counts[ $status ]
			);
		}

		return $views;
	}

	/**
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$form_labels = ZSkeleton_Form_Submissions_Repository::get_form_labels();
		?>
		<div class="alignleft actions zs-fb-submissions-filters">
			<label class="screen-reader-text" for="filter-by-form"><?php esc_html_e( 'Filter by form', 'zskeleton' ); ?></label>
			<select name="form_id" id="filter-by-form">
				<option value=""><?php esc_html_e( 'All forms', 'zskeleton' ); ?></option>
				<?php foreach ( $form_labels as $form_id => $label ) : ?>
					<option value="<?php echo esc_attr( $form_id ); ?>" <?php selected( $this->form_filter, $form_id ); ?>>
						<?php echo esc_html( $label . ' (' . $form_id . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( __( 'Filter', 'zskeleton' ), '', 'filter_action', false ); ?>
		</div>
		<?php
	}

	/**
	 * @param array<string,mixed> $item Row.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="submission[]" value="%d" />', (int) $item['id'] );
	}

	/**
	 * @param array<string,mixed> $item Row.
	 * @return string
	 */
	protected function column_id( $item ) {
		$url = add_query_arg(
			array(
				'page'   => 'zskeleton-form-submissions',
				'action' => 'view',
				'id'     => (int) $item['id'],
			),
			admin_url( 'admin.php' )
		);
		return '<a class="row-title" href="' . esc_url( $url ) . '">#' . esc_html( (string) $item['id'] ) . '</a>';
	}

	/**
	 * @param array<string,mixed> $item Row.
	 * @return string
	 */
	protected function column_form_id( $item ) {
		$form_id = isset( $item['form_id'] ) ? (string) $item['form_id'] : '';
		$label   = ZSkeleton_Form_Submissions_Repository::get_form_label( $form_id );
		$url     = add_query_arg(
			array(
				'page'    => 'zskeleton-form-submissions',
				'form_id' => $form_id,
			),
			admin_url( 'admin.php' )
		);

		return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a><br><code class="zs-fb-submissions-form-id">' . esc_html( $form_id ) . '</code>';
	}

	/**
	 * @param array<string,mixed> $item Row.
	 * @return string
	 */
	protected function column_status( $item ) {
		$status = isset( $item['status'] ) ? (string) $item['status'] : '';
		return ZSkeleton_Form_Submissions_Admin::render_status_badge( $status );
	}

	/**
	 * @param array<string,mixed> $item Row.
	 * @return string
	 */
	protected function column_summary( $item ) {
		$payload = isset( $item['payload'] ) && is_array( $item['payload'] ) ? $item['payload'] : array();
		foreach ( array( 'email', 'name', 'first_name', 'demo_email', 'demo_name' ) as $key ) {
			if ( ! empty( $payload[ $key ] ) ) {
				return esc_html( (string) $payload[ $key ] );
			}
		}

		$first = reset( $payload );
		if ( is_scalar( $first ) && '' !== (string) $first ) {
			return esc_html( (string) $first );
		}

		return '<span class="zs-fb-submissions-muted">—</span>';
	}

	/**
	 * @param array<string,mixed> $item Row.
	 * @param string              $column_name Column.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		if ( 'created_at' === $column_name && ! empty( $item['created_at'] ) ) {
			$ts = strtotime( (string) $item['created_at'] . ' UTC' );
			if ( $ts ) {
				return esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts ) );
			}
		}
		return isset( $item[ $column_name ] ) ? esc_html( (string) $item[ $column_name ] ) : '';
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		$per_page = 20;
		$paged    = max( 1, $this->get_pagenum() );
		$orderby  = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
		$order    = isset( $_REQUEST['order'] ) ? sanitize_key( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$result = ZSkeleton_Form_Submissions_Repository::query(
			array(
				'form_id'  => $this->form_filter,
				'status'   => $this->status_filter,
				'search'   => $this->search,
				'per_page' => $per_page,
				'page'     => $paged,
				'orderby'  => $orderby,
				'order'    => $order,
			)
		);

		$this->items = $result['items'];
		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $result['total'] / $per_page ),
			)
		);
	}
}

/**
 * Submissions admin screen.
 */
class ZSkeleton_Form_Submissions_Admin {

	const PAGE_SLUG = 'zskeleton-form-submissions';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_badge' ), 999 );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register submenu under Forms CPT.
	 */
	public function register_menu() {
		add_submenu_page(
			'edit.php?post_type=' . ZSkeleton_Forms_CPT::POST_TYPE,
			__( 'Form Submissions', 'zskeleton' ),
			__( 'Submissions', 'zskeleton' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Show count of new submissions in the submenu label.
	 */
	public function add_menu_badge() {
		global $submenu;

		if ( ! current_user_can( 'manage_options' ) || empty( $submenu ) ) {
			return;
		}

		$counts = ZSkeleton_Form_Submissions_Repository::get_status_counts();
		$new    = (int) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_NEW ];
		if ( $new < 1 ) {
			return;
		}

		$parent = 'edit.php?post_type=' . ZSkeleton_Forms_CPT::POST_TYPE;
		if ( empty( $submenu[ $parent ] ) ) {
			return;
		}

		foreach ( $submenu[ $parent ] as $index => $item ) {
			if ( isset( $item[2] ) && self::PAGE_SLUG === $item[2] ) {
				$submenu[ $parent ][ $index ][0] .= ' <span class="awaiting-mod count-' . esc_attr( (string) $new ) . '"><span class="pending-count">' . esc_html( (string) $new ) . '</span></span>';
				break;
			}
		}
	}

	/**
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, self::PAGE_SLUG ) ) {
			return;
		}

		wp_enqueue_style(
			'zskeleton-form-submissions-admin',
			ZSkeleton_THEME_URL . '/assets/css/form-submissions-admin.css',
			array(),
			file_exists( ZSkeleton_THEME_DIR . '/assets/css/form-submissions-admin.css' )
				? (string) filemtime( ZSkeleton_THEME_DIR . '/assets/css/form-submissions-admin.css' )
				: ZSkeleton_VERSION
		);
	}

	/**
	 * Bulk and single row actions.
	 */
	public function handle_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( self::PAGE_SLUG !== $page ) {
			return;
		}

		if ( isset( $_GET['action'], $_GET['id'] ) && 'view' === $_GET['action'] ) {
			return;
		}

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		}

		if ( isset( $_GET['zs_do'], $_GET['id'], $_GET['_wpnonce'] ) ) {
			$do = sanitize_key( wp_unslash( $_GET['zs_do'] ) );
			$id = absint( $_GET['id'] );
			if ( $id > 0 && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'zs_submission_' . $id ) ) {
				$this->run_submission_action( $do, array( $id ) );
				$redirect = add_query_arg( 'zs_updated', '1', remove_query_arg( array( 'zs_do', 'id', '_wpnonce' ), $redirect ) );
				wp_safe_redirect( $redirect );
				exit;
			}
		}

		$action = '';
		if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
		} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action2'] ) );
		}

		if ( '' === $action || empty( $_POST['submission'] ) || ! is_array( $_POST['submission'] ) ) {
			return;
		}

		check_admin_referer( 'bulk-submissions' );

		$ids = array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['submission'] ) ) ) );
		if ( empty( $ids ) ) {
			return;
		}

		$this->run_submission_action( $action, $ids );
		wp_safe_redirect( add_query_arg( 'zs_updated', count( $ids ), remove_query_arg( array( 'action', 'action2', 'id', 'zs_do', '_wpnonce' ), $redirect ) ) );
		exit;
	}

	/**
	 * @param string $action Action key.
	 * @param int[]  $ids    Submission ids.
	 */
	private function run_submission_action( $action, array $ids ) {
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
	 * @param string $status Submission status.
	 * @return string
	 */
	public static function render_status_badge( $status ) {
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
	 * Render list or single view.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'zskeleton' ) );
		}

		if ( isset( $_GET['action'], $_GET['id'] ) && 'view' === $_GET['action'] ) {
			$this->render_single( absint( $_GET['id'] ) );
			return;
		}

		$this->render_list();
	}

	/**
	 * List screen.
	 */
	private function render_list() {
		$table   = new ZSkeleton_Form_Submissions_List_Table();
		$counts  = ZSkeleton_Form_Submissions_Repository::get_status_counts();
		$new     = (int) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_NEW ];
		$form_id = isset( $_REQUEST['form_id'] ) ? sanitize_key( wp_unslash( $_REQUEST['form_id'] ) ) : '';
		?>
		<div class="wrap zs-fb-submissions-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Form Submissions', 'zskeleton' ); ?></h1>
			<?php if ( isset( $_GET['zs_updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Submissions updated.', 'zskeleton' ); ?></p></div>
			<?php endif; ?>

			<div class="zs-fb-submissions-stats">
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) $new ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'New', 'zskeleton' ); ?></span>
				</div>
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_READ ] ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'Read', 'zskeleton' ); ?></span>
				</div>
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) $counts[ ZSkeleton_Form_Submissions_Repository::STATUS_SPAM ] ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'Spam', 'zskeleton' ); ?></span>
				</div>
				<div class="zs-fb-submissions-stat">
					<span class="zs-fb-submissions-stat__value"><?php echo esc_html( (string) array_sum( $counts ) ); ?></span>
					<span class="zs-fb-submissions-stat__label"><?php esc_html_e( 'Total', 'zskeleton' ); ?></span>
				</div>
			</div>

			<?php if ( '' !== $form_id ) : ?>
				<p class="zs-fb-submissions-filter-note">
					<?php
					printf(
						/* translators: 1: form title, 2: form id */
						esc_html__( 'Showing submissions for %1$s (%2$s).', 'zskeleton' ),
						esc_html( ZSkeleton_Form_Submissions_Repository::get_form_label( $form_id ) ),
						esc_html( $form_id )
					);
					?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>"><?php esc_html_e( 'Clear filter', 'zskeleton' ); ?></a>
				</p>
			<?php endif; ?>

			<form method="post">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>" />
				<?php wp_nonce_field( 'bulk-submissions' ); ?>
				<?php if ( '' !== $form_id ) : ?>
					<input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>" />
				<?php endif; ?>
				<?php if ( isset( $_REQUEST['status'] ) ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( sanitize_key( wp_unslash( $_REQUEST['status'] ) ) ); ?>" />
				<?php endif; ?>
				<?php
				$table->search_box( __( 'Search submissions', 'zskeleton' ), 'submission' );
				$table->prepare_items();
				?>
				<div class="zs-fb-submissions-table-card">
					<?php $table->display(); ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * @param int $id Submission id.
	 */
	private function render_single( $id ) {
		$row = ZSkeleton_Form_Submissions_Repository::get( $id );
		if ( ! $row ) {
			wp_die( esc_html__( 'Submission not found.', 'zskeleton' ) );
		}

		if ( ZSkeleton_Form_Submissions_Repository::STATUS_NEW === $row['status'] ) {
			ZSkeleton_Form_Submissions_Repository::mark_read( $id );
			$row['status'] = ZSkeleton_Form_Submissions_Repository::STATUS_READ;
		}

		$field_labels = array();
		if ( class_exists( 'ZSkeleton_Form_Definition' ) ) {
			$def = ZSkeleton_Form_Definition::get( (string) $row['form_id'] );
			if ( $def ) {
				foreach ( $def->get_fields_by_name() as $name => $field ) {
					$field_labels[ $name ] = isset( $field['label'] ) ? (string) $field['label'] : $name;
				}
			}
		}

		$list_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		$nonce    = wp_create_nonce( 'zs_submission_' . $id );
		?>
		<div class="wrap zs-fb-submissions-wrap zs-fb-submissions-wrap--single">
			<h1><?php printf( esc_html__( 'Submission #%d', 'zskeleton' ), (int) $row['id'] ); ?></h1>
			<p class="zs-fb-submissions-back">
				<a href="<?php echo esc_url( $list_url ); ?>">&larr; <?php esc_html_e( 'Back to submissions', 'zskeleton' ); ?></a>
			</p>

			<div class="zs-fb-submissions-detail-grid">
				<div class="postbox zs-fb-submissions-meta-box">
					<h2 class="hndle"><span><?php esc_html_e( 'Details', 'zskeleton' ); ?></span></h2>
					<div class="inside">
						<table class="form-table" role="presentation">
							<tbody>
								<tr>
									<th scope="row"><?php esc_html_e( 'Form', 'zskeleton' ); ?></th>
									<td>
										<strong><?php echo esc_html( ZSkeleton_Form_Submissions_Repository::get_form_label( (string) $row['form_id'] ) ); ?></strong><br>
										<code><?php echo esc_html( (string) $row['form_id'] ); ?></code>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Status', 'zskeleton' ); ?></th>
									<td><?php echo wp_kses_post( self::render_status_badge( (string) $row['status'] ) ); ?></td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Submitted', 'zskeleton' ); ?></th>
									<td>
										<?php
										$ts = ! empty( $row['created_at'] ) ? strtotime( (string) $row['created_at'] . ' UTC' ) : false;
										echo $ts ? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts ) ) : esc_html( (string) $row['created_at'] );
										?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="postbox zs-fb-submissions-meta-box">
					<h2 class="hndle"><span><?php esc_html_e( 'Actions', 'zskeleton' ); ?></span></h2>
					<div class="inside zs-fb-submissions-actions">
						<?php if ( ZSkeleton_Form_Submissions_Repository::STATUS_TRASH !== $row['status'] ) : ?>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'zs_do' => 'mark_read', 'id' => $id ), $list_url ), 'zs_submission_' . $id ) ); ?>"><?php esc_html_e( 'Mark as read', 'zskeleton' ); ?></a>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'zs_do' => 'mark_spam', 'id' => $id ), $list_url ), 'zs_submission_' . $id ) ); ?>"><?php esc_html_e( 'Mark as spam', 'zskeleton' ); ?></a>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'zs_do' => 'trash', 'id' => $id ), $list_url ), 'zs_submission_' . $id ) ); ?>"><?php esc_html_e( 'Move to trash', 'zskeleton' ); ?></a>
						<?php else : ?>
							<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'zs_do' => 'restore', 'id' => $id ), $list_url ), 'zs_submission_' . $id ) ); ?>"><?php esc_html_e( 'Restore', 'zskeleton' ); ?></a>
							<a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'zs_do' => 'delete', 'id' => $id ), $list_url ), 'zs_submission_' . $id ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this submission permanently?', 'zskeleton' ) ); ?>');"><?php esc_html_e( 'Delete permanently', 'zskeleton' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="postbox zs-fb-submissions-meta-box zs-fb-submissions-meta-box--wide">
				<h2 class="hndle"><span><?php esc_html_e( 'Submitted fields', 'zskeleton' ); ?></span></h2>
				<div class="inside">
					<?php if ( empty( $row['payload'] ) ) : ?>
						<p class="zs-fb-submissions-muted"><?php esc_html_e( 'No field data was stored for this submission.', 'zskeleton' ); ?></p>
					<?php else : ?>
						<table class="widefat striped zs-fb-submissions-fields-table">
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
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
