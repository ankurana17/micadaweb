<?php
/** Shared trash and restore actions for MICADA custom-table CMS entries. */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_cms_trash_targets' ) ) {
function micada_cms_trash_targets() {
	return array(
		'rti' => array( 'table' => 'micada_rti_table', 'page' => 'micada-rti-list' ),
		'org-head' => array( 'table' => 'micada_org_head_table', 'page' => 'micada-organisation-head' ),
		'org-field' => array( 'table' => 'micada_org_field_table', 'page' => 'micada-organisation-field' ),
		'org-orders' => array( 'table' => 'micada_org_orders_table', 'page' => 'micada-organisation-orders' ),
		'faq' => array( 'table' => 'micada_faq_table', 'page' => 'micada-faqs' ),
		'quick-links' => array( 'table' => 'micada_quick_links_table', 'page' => 'micada-quick-links' ),
		'news-events' => array( 'table' => 'micada_news_events_table', 'page' => 'micada-news-events' ),
		'important-links' => array( 'table' => 'micada_important_links_table', 'page' => 'micada-important-links' ),
		'gallery-photo' => array( 'table' => 'micada_gallery_photo_table', 'page' => 'micada-photo-gallery' ),
		'gallery-video' => array( 'table' => 'micada_gallery_video_table', 'page' => 'micada-video-gallery' ),
		'home-ticker' => array( 'table' => 'micada_home_ticker_table', 'page' => 'micada-home-ticker' ),
		'home-notice' => array( 'table' => 'micada_home_notice_table', 'page' => 'micada-homepage-notice', 'string_id' => true ),
		'important-message' => array( 'table' => 'micada_important_message_table', 'page' => 'micada-important', 'string_id' => true ),
	);
}
}


if ( ! function_exists( 'micada_cms_trash_action_url' ) ) {
function micada_cms_trash_action_url( $type, $id, $status ) {
	$targets = micada_cms_trash_targets();
	if ( ! isset( $targets[ $type ] ) ) {
		return '';
	}
	$id = isset( $targets[ $type ]['string_id'] ) ? sanitize_text_field( (string) $id ) : absint( $id );
	return wp_nonce_url(
		add_query_arg( array( 'action' => 'micada_cms_trash_action', 'type' => $type, 'id' => $id, 'status' => $status ), admin_url( 'admin-post.php' ) ),
		'micada_cms_trash_' . $type . '_' . $id . '_' . $status
	);
}
}


if ( ! function_exists( 'micada_cms_trash_action_links' ) ) {
function micada_cms_trash_action_links( $type, $id, $status ) {
	if ( 'trash' === $status ) {
		$restore = micada_cms_trash_action_url( $type, $id, 'post' );
		$delete = micada_cms_trash_action_url( $type, $id, 'delete' );
		echo '<a href="' . esc_url( $restore ) . '">' . esc_html__( 'Restore', 'micada' ) . '</a> | <a href="' . esc_url( $delete ) . '" onclick="return confirm(\'' . esc_js( __( 'Permanently delete this item? This cannot be undone.', 'micada' ) ) . '\');">' . esc_html__( 'Delete Permanently', 'micada' ) . '</a>';
		return;
	}
	$trash = micada_cms_trash_action_url( $type, $id, 'trash' );
	echo '<a href="' . esc_url( $trash ) . '">' . esc_html__( 'Move to Trash', 'micada' ) . '</a>';
}
}


if ( ! function_exists( 'micada_cms_trash_action' ) ) {
function micada_cms_trash_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage this item.', 'micada' ) );
	}
	$type = sanitize_key( wp_unslash( $_GET['type'] ?? '' ) );
	$targets = micada_cms_trash_targets();
	$id = isset( $targets[ $type ]['string_id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ?? '' ) ) : absint( $_GET['id'] ?? 0 );
	$status = sanitize_key( wp_unslash( $_GET['status'] ?? '' ) );
	if ( ! isset( $targets[ $type ] ) || ! in_array( $status, array( 'post', 'trash', 'delete' ), true ) || '' === (string) $id || 0 === $id ) {
		wp_die( esc_html__( 'Invalid CMS action.', 'micada' ) );
	}
	check_admin_referer( 'micada_cms_trash_' . $type . '_' . $id . '_' . $status );
	$target = $targets[ $type ];
	if ( ! is_callable( $target['table'] ) ) {
		wp_die( esc_html__( 'CMS table is unavailable.', 'micada' ) );
	}
	$table = call_user_func( $target['table'] );
	global $wpdb;
	if ( 'delete' === $status ) {
		$deleted = false !== $wpdb->delete( $table, array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
		$notice = $deleted ? 'deleted' : 'error';
	} else {
		$updated = false !== $wpdb->update( $table, array( 'display_status' => $status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
		$notice = $updated ? ( 'trash' === $status ? 'trashed' : 'restored' ) : 'error';
	}
	wp_safe_redirect( add_query_arg( array( 'page' => $target['page'], 'view' => 'trash' === $status ? 'post' : ( 'delete' === $status ? 'trash' : 'post' ), 'message' => $notice ), admin_url( 'admin.php' ) ) );
	exit;
}
}

if ( ! function_exists( 'micada_cms_migrate_display_status_columns' ) ) {
function micada_cms_migrate_display_status_columns() {
	global $wpdb;
	foreach ( micada_cms_trash_targets() as $target ) {
		if ( ! is_callable( $target['table'] ) ) {
			continue;
		}
		$table = call_user_func( $target['table'] );
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			continue;
		}
		$has_old = (bool) $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `$table` LIKE %s", 'list_status' ) );
		$has_new = (bool) $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `$table` LIKE %s", 'display_status' ) );
		if ( $has_old && $has_new ) {
			$wpdb->query( "UPDATE `$table` SET display_status = list_status" );
			$wpdb->query( "ALTER TABLE `$table` DROP COLUMN list_status" );
		} elseif ( $has_old ) {
			$wpdb->query( "ALTER TABLE `$table` CHANGE COLUMN list_status display_status varchar(10) NOT NULL DEFAULT 'post'" );
		}
		$has_status_index = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM `$table` WHERE Key_name = %s", 'list_status' ) );
		if ( ! $has_status_index ) {
			$wpdb->query( "ALTER TABLE `$table` ADD KEY list_status (display_status)" );
		}
	}
}
}

add_action( 'admin_init', 'micada_cms_migrate_display_status_columns', 999 );
add_action( 'admin_post_micada_cms_trash_action', 'micada_cms_trash_action' );

if ( ! function_exists( 'micada_cms_status_tabs' ) ) {
function micada_cms_status_tabs( $page, $table, $current = 'post' ) {
	global $wpdb;
	$published = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE display_status = 'post'" );
	$trashed = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE display_status = 'trash'" );
	$view = 'trash' === $current ? 'trash' : 'post';
	$base = admin_url( 'admin.php?page=' . rawurlencode( $page ) );
	echo '<ul class="subsubsub"><li><a class="' . ( 'post' === $view ? 'current' : '' ) . '" href="' . esc_url( add_query_arg( 'view', 'post', $base ) ) . '">' . esc_html__( 'Post', 'micada' ) . ' <span class="count">(' . esc_html( $published ) . ')</span></a> | </li><li><a class="' . ( 'trash' === $view ? 'current' : '' ) . '" href="' . esc_url( add_query_arg( 'view', 'trash', $base ) ) . '">' . esc_html__( 'Trash', 'micada' ) . ' <span class="count">(' . esc_html( $trashed ) . ')</span></a></li></ul><br class="clear">';
}
}

