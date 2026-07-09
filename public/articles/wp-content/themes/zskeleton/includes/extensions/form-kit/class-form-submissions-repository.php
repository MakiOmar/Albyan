<?php
/**
 * CRUD for Form Kit submissions table.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Submissions repository.
 */
class ZSkeleton_Form_Submissions_Repository {

	const STATUS_NEW   = 'new';
	const STATUS_READ  = 'read';
	const STATUS_SPAM  = 'spam';
	const STATUS_TRASH = 'trash';

	/**
	 * @param string              $form_id Form id.
	 * @param array<string,mixed> $payload Sanitized field values.
	 * @param array<string,mixed> $args Optional meta, status, user_id.
	 * @return int|false Insert id or false.
	 */
	public static function insert( $form_id, array $payload, array $args = array() ) {
		global $wpdb;

		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id ) {
			return false;
		}

		$status = isset( $args['status'] ) ? sanitize_key( (string) $args['status'] ) : self::STATUS_NEW;
		if ( ! in_array( $status, array( self::STATUS_NEW, self::STATUS_READ, self::STATUS_SPAM, self::STATUS_TRASH ), true ) ) {
			$status = self::STATUS_NEW;
		}

		$user_id = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : get_current_user_id();
		$meta    = isset( $args['meta'] ) && is_array( $args['meta'] ) ? $args['meta'] : array();
		$ip_hash = isset( $args['ip_hash'] ) ? sanitize_text_field( (string) $args['ip_hash'] ) : self::hash_ip( self::get_client_ip() );

		$table = ZSkeleton_Form_Submissions_DB::get_table_name();
		$ok    = $wpdb->insert(
			$table,
			array(
				'form_id'    => $form_id,
				'status'     => $status,
				'payload'    => wp_json_encode( $payload ),
				'ip_hash'    => $ip_hash,
				'user_id'    => $user_id,
				'created_at' => current_time( 'mysql', true ),
				'meta'       => wp_json_encode( $meta ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * @param int    $id      Submission id.
	 * @param string $form_id Expected form id.
	 * @return bool
	 */
	public static function belongs_to_form( $id, $form_id ) {
		$row = self::get( $id );
		if ( ! $row ) {
			return false;
		}

		return sanitize_key( (string) $row['form_id'] ) === sanitize_key( (string) $form_id );
	}

	/**
	 * @param int $id Submission id.
	 * @return array<string,mixed>|null
	 */
	public static function get( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( $id < 1 ) {
			return null;
		}

		$table = ZSkeleton_Form_Submissions_DB::get_table_name();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return null;
		}

		return self::normalize_row( $row );
	}

	/**
	 * @param string $form_id Form id.
	 * @return int
	 */
	public static function count_for_form( $form_id ) {
		global $wpdb;

		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id ) {
			return 0;
		}

		$table = ZSkeleton_Form_Submissions_DB::get_table_name();
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE form_id = %s AND status != %s",
				$form_id,
				self::STATUS_TRASH
			)
		);

		return (int) $count;
	}

	/**
	 * @return array<string,int>
	 */
	public static function get_status_counts( $form_id = '' ) {
		global $wpdb;

		$table   = ZSkeleton_Form_Submissions_DB::get_table_name();
		$form_id = sanitize_key( (string) $form_id );
		$where   = '1=1';
		$args    = array();

		if ( '' !== $form_id ) {
			$where .= ' AND form_id = %s';
			$args[]  = $form_id;
		}

		$sql = "SELECT status, COUNT(*) AS total FROM {$table} WHERE {$where} GROUP BY status";
		$rows = $args ? $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

		$counts = array(
			self::STATUS_NEW   => 0,
			self::STATUS_READ  => 0,
			self::STATUS_SPAM  => 0,
			self::STATUS_TRASH => 0,
		);

		foreach ( (array) $rows as $row ) {
			$status = isset( $row['status'] ) ? (string) $row['status'] : '';
			if ( isset( $counts[ $status ] ) ) {
				$counts[ $status ] = (int) $row['total'];
			}
		}

		return $counts;
	}

	/**
	 * @return string[]
	 */
	public static function get_distinct_form_ids() {
		global $wpdb;

		$table = ZSkeleton_Form_Submissions_DB::get_table_name();
		$ids   = $wpdb->get_col( "SELECT DISTINCT form_id FROM {$table} ORDER BY form_id ASC" );

		return array_values( array_filter( array_map( 'strval', (array) $ids ) ) );
	}

	/**
	 * Human-readable labels for form ids (UI-built forms + registry keys).
	 *
	 * @return array<string,string>
	 */
	public static function get_form_labels() {
		$labels = array();

		$posts = get_posts(
			array(
				'post_type'      => ZSkeleton_Forms_CPT::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$form_id = ZSkeleton_Forms_CPT::get_form_id_for_post( $post->ID );
			if ( '' !== $form_id ) {
				$labels[ $form_id ] = $post->post_title;
			}
		}

		foreach ( self::get_distinct_form_ids() as $form_id ) {
			if ( ! isset( $labels[ $form_id ] ) ) {
				$labels[ $form_id ] = $form_id;
			}
		}

		asort( $labels, SORT_NATURAL | SORT_FLAG_CASE );
		return $labels;
	}

	/**
	 * @param string $form_id Form id.
	 * @return string
	 */
	public static function get_form_label( $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		$labels  = self::get_form_labels();
		return isset( $labels[ $form_id ] ) ? $labels[ $form_id ] : $form_id;
	}

	/**
	 * Query submissions for the admin list table.
	 *
	 * @param array<string,mixed> $args Query args.
	 * @return array{items: array<int,array<string,mixed>>, total: int}
	 */
	public static function query( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'form_id'  => '',
			'status'   => '',
			'search'   => '',
			'per_page' => 20,
			'page'     => 1,
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		);

		$args     = wp_parse_args( $args, $defaults );
		$table    = ZSkeleton_Form_Submissions_DB::get_table_name();
		$where    = array( '1=1' );
		$sql_args = array();

		$status = sanitize_key( (string) $args['status'] );
		if ( in_array( $status, array( self::STATUS_NEW, self::STATUS_READ, self::STATUS_SPAM, self::STATUS_TRASH ), true ) ) {
			$where[]    = 'status = %s';
			$sql_args[] = $status;
		} else {
			$where[]    = 'status != %s';
			$sql_args[] = self::STATUS_TRASH;
		}

		$form_id = sanitize_key( (string) $args['form_id'] );
		if ( '' !== $form_id ) {
			$where[]    = 'form_id = %s';
			$sql_args[] = $form_id;
		}

		$search = sanitize_text_field( (string) $args['search'] );
		if ( '' !== $search ) {
			$where[]    = 'payload LIKE %s';
			$sql_args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = $sql_args
			? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $sql_args ) )
			: (int) $wpdb->get_var( $count_sql );

		$per_page = max( 1, min( 100, (int) $args['per_page'] ) );
		$page     = max( 1, (int) $args['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		$allowed_orderby = array( 'id', 'form_id', 'status', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

		$list_sql  = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$list_args = array_merge( $sql_args, array( $per_page, $offset ) );
		$rows      = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ), ARRAY_A );

		$items = array();
		foreach ( (array) $rows as $row ) {
			if ( is_array( $row ) ) {
				$items[] = self::normalize_row( $row );
			}
		}

		return array(
			'items' => $items,
			'total' => $total,
		);
	}

	/**
	 * @param int[]  $ids    Submission ids.
	 * @param string $status New status.
	 * @return int Rows updated.
	 */
	public static function update_status( array $ids, $status ) {
		global $wpdb;

		$status = sanitize_key( (string) $status );
		if ( ! in_array( $status, array( self::STATUS_NEW, self::STATUS_READ, self::STATUS_SPAM, self::STATUS_TRASH ), true ) ) {
			return 0;
		}

		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$table        = ZSkeleton_Form_Submissions_DB::get_table_name();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = "UPDATE {$table} SET status = %s WHERE id IN ({$placeholders})";
		$args         = array_merge( array( $status ), $ids );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders built from absint ids.
		return (int) $wpdb->query( $wpdb->prepare( $sql, $args ) );
	}

	/**
	 * @param int[] $ids Submission ids.
	 * @return int Rows deleted.
	 */
	public static function delete( array $ids ) {
		global $wpdb;

		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$table        = ZSkeleton_Form_Submissions_DB::get_table_name();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders built from absint ids.
		return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) );
	}

	/**
	 * @param int $id Submission id.
	 * @return bool
	 */
	public static function mark_read( $id ) {
		$id = absint( $id );
		if ( $id < 1 ) {
			return false;
		}
		return self::update_status( array( $id ), self::STATUS_READ ) > 0;
	}

	/**
	 * @param string $ip IP address.
	 * @return string
	 */
	public static function hash_ip( $ip ) {
		$ip = is_string( $ip ) ? trim( $ip ) : '';
		if ( '' === $ip ) {
			return '';
		}
		return hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
	}

	/**
	 * @return string
	 */
	public static function get_client_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return $ip;
	}

	/**
	 * @param array<string,mixed> $row DB row.
	 * @return array<string,mixed>
	 */
	public static function normalize_row( array $row ) {
		$payload = array();
		if ( ! empty( $row['payload'] ) ) {
			$decoded = json_decode( (string) $row['payload'], true );
			if ( is_array( $decoded ) ) {
				$payload = $decoded;
			}
		}

		$meta = array();
		if ( ! empty( $row['meta'] ) ) {
			$decoded = json_decode( (string) $row['meta'], true );
			if ( is_array( $decoded ) ) {
				$meta = $decoded;
			}
		}

		$row['payload'] = $payload;
		$row['meta']    = $meta;
		return $row;
	}
}
