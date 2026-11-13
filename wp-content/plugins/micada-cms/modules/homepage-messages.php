<?php
/**
 * Module: Homepage Messages
 * Description: Manages scrolling ticker, notices, and important messages.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_home_ticker_table' ) ) {
function micada_home_ticker_table() {
	global $wpdb;
	return $wpdb->prefix . 'homepage_messages';
}
}


if ( ! function_exists( 'micada_home_notice_table' ) ) {
function micada_home_notice_table() {
	global $wpdb;
	return $wpdb->prefix . 'homepage_notices';
}
}


if ( ! function_exists( 'micada_important_message_table' ) ) {
function micada_important_message_table() {
	global $wpdb;
	return $wpdb->prefix . 'important_messages';
}
}


if ( ! function_exists( 'micada_home_ticker_install' ) ) {
function micada_home_ticker_install() {
	global $wpdb;
	$table = micada_home_ticker_table();
	$legacy_table = $wpdb->prefix . 'homepage_ticker';
	$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	$legacy_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ) === $legacy_table;
	$had_legacy_table = $legacy_exists;
	if ( ! $table_exists && $legacy_exists ) {
		$renamed = $wpdb->query( "RENAME TABLE `{$legacy_table}` TO `{$table}`" );
		if ( false !== $renamed ) {
			$table_exists = true;
			$legacy_exists = false;
		}
	}
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		title_en text NOT NULL,
		title_hi text NOT NULL,
		url text NOT NULL,
		new_tab tinyint(1) NOT NULL DEFAULT 0,
		show_new tinyint(1) NOT NULL DEFAULT 1,
		is_active tinyint(1) NOT NULL DEFAULT 1,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY is_active (is_active),
		KEY list_status (display_status)
	) {$charset_collate};";
	if ( '2' !== get_option( 'micada_home_messages_schema' ) || ! $table_exists ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'micada_home_messages_schema', '2', false );
	}
	$notices_table = micada_home_notice_table();
	$notices_sql = "CREATE TABLE {$notices_table} (
		id varchar(64) NOT NULL,
		notice_en text NOT NULL,
		notice_hi text NOT NULL,
		is_enabled tinyint(1) NOT NULL DEFAULT 1,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY is_enabled (is_enabled),
		KEY list_status (display_status)
	) {$charset_collate};";
	$important_table = micada_important_message_table();
	$important_sql = "CREATE TABLE {$important_table} (
		id varchar(64) NOT NULL,
		text_en text NOT NULL,
		text_hi text NOT NULL,
		is_enabled tinyint(1) NOT NULL DEFAULT 1,
		show_new tinyint(1) NOT NULL DEFAULT 1,
		created_at bigint(20) unsigned NOT NULL DEFAULT 0,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY is_enabled (is_enabled),
		KEY list_status (display_status)
	) {$charset_collate};";
	$notices_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $notices_table ) ) === $notices_table;
	$important_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $important_table ) ) === $important_table;
	if ( '2' !== get_option( 'micada_homepage_content_tables_schema' ) || ! $notices_exists || ! $important_exists ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $notices_sql );
		dbDelta( $important_sql );
		update_option( 'micada_homepage_content_tables_schema', '2', false );
	}
	micada_migrate_homepage_json_content();

	// If both table names exist, copy old records once without overwriting current records.
	if ( $legacy_exists && '1' !== get_option( 'micada_home_messages_migrated' ) ) {
		$migrated = $wpdb->query( "INSERT IGNORE INTO `{$table}` (id, title_en, title_hi, url, new_tab, show_new, is_active, sort_order) SELECT id, title_en, title_hi, url, new_tab, show_new, is_active, sort_order FROM `{$legacy_table}`" );
		if ( false !== $migrated ) {
			update_option( 'micada_home_messages_migrated', '1', false );
		}
	}

	if ( '1' !== get_option( 'micada_home_messages_seeded' ) && ! $had_legacy_table && 0 === (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) ) {
		$path = get_stylesheet_directory() . '/assets/data/translations.json';
		$all = is_readable( $path ) ? json_decode( file_get_contents( $path ), true ) : array();
		$en = $all['en'] ?? array();
		$hi = $all['hd'] ?? array();
		$wpdb->insert(
			$table,
			array(
				'title_en' => $en['PORTAL_OPEN'] ?? 'Farmer Registration for MI Application is now open',
				'title_hi' => $hi['PORTAL_OPEN'] ?? '',
				'url' => '',
				'new_tab' => 0,
				'show_new' => 1,
				'is_active' => 1,
				'sort_order' => 1,
			),
			array( '%s', '%s', '%s', '%d', '%d', '%d', '%d' )
		);
	}
	update_option( 'micada_home_messages_seeded', '1', false );
}
}

add_action( 'init', 'micada_home_ticker_install', 1 );

if ( ! function_exists( 'micada_home_ticker_menu' ) ) {
function micada_home_ticker_menu() {
	add_menu_page( __( 'Homepage Messages', 'micada' ), __( 'Homepage Messages', 'micada' ), 'manage_options', 'micada-homepage-ticker', 'micada_home_ticker_admin_page', 'dashicons-editor-kitchensink', 24 );
}
}

add_action( 'admin_menu', 'micada_home_ticker_menu' );

if ( ! function_exists( 'micada_home_notice_menu' ) ) {
function micada_home_notice_menu() {
	add_submenu_page( 'micada-homepage-ticker', __( 'Homepage Notice', 'micada' ), __( 'Homepage Notice', 'micada' ), 'manage_options', 'micada-homepage-notice', 'micada_home_notice_admin_page' );
}
}

add_action( 'admin_menu', 'micada_home_notice_menu' );

if ( ! function_exists( 'micada_vendor_banner_menu' ) ) {
function micada_vendor_banner_menu() {
	add_submenu_page( 'micada-homepage-ticker', __( 'Important Message', 'micada' ), __( 'Important Message', 'micada' ), 'manage_options', 'micada-important', 'micada_vendor_banner_admin_page' );
}
}

add_action( 'admin_menu', 'micada_vendor_banner_menu' );

if ( ! function_exists( 'micada_vendor_banner_defaults' ) ) {
function micada_vendor_banner_defaults() {
	return array(
		'id' => 'default',
		'text_en' => 'New Vendor Registration Open Now',
		'text_hi' => 'नया विक्रेता पंजीकरण अब खुला है',
		'is_enabled' => true,
		'show_new' => true,
	);
}
}


if ( ! function_exists( 'micada_vendor_banner_file' ) ) {
function micada_vendor_banner_file() {
	$uploads = wp_upload_dir();
	return trailingslashit( $uploads['basedir'] ) . 'micada/vendor-registration-banner.json';
}
}


if ( ! function_exists( 'micada_migrate_homepage_json_content' ) ) {
function micada_migrate_homepage_json_content() {
	global $wpdb;
	$vendor_table = micada_important_message_table();
	if ( '1' !== get_option( 'micada_important_messages_migrated' ) ) {
		$file = micada_vendor_banner_file();
		if ( is_readable( $file ) ) {
			$saved = json_decode( file_get_contents( $file ), true );
			if ( isset( $saved['items'] ) && is_array( $saved['items'] ) ) {
				$items = array_values( $saved['items'] );
			} elseif ( isset( $saved['text_en'] ) || isset( $saved['text_hi'] ) ) {
				$items = array( array_merge( micada_vendor_banner_defaults(), $saved ) );
			} else {
				$items = array();
			}
		} else {
			$items = array( micada_vendor_banner_defaults() );
		}
		$ok = true;
		foreach ( $items as $order => $item ) {
			$id = sanitize_text_field( (string) ( $item['id'] ?? wp_generate_uuid4() ) );
			if ( '' === $id ) {
				$id = wp_generate_uuid4();
			}
			$result = $wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO `{$vendor_table}` (id, text_en, text_hi, is_enabled, show_new, created_at, sort_order) VALUES (%s, %s, %s, %d, %d, %d, %d)",
				$id,
				sanitize_text_field( $item['text_en'] ?? '' ),
				sanitize_text_field( $item['text_hi'] ?? '' ),
				! empty( $item['is_enabled'] ) ? 1 : 0,
				! empty( $item['show_new'] ) ? 1 : 0,
				absint( $item['created_at'] ?? current_time( 'timestamp' ) ),
				(int) $order
			) );
			if ( false === $result ) {
				$ok = false;
				break;
			}
		}
		if ( $ok ) {
			update_option( 'micada_important_messages_migrated', '1', false );
		}
	}

	$notices_table = micada_home_notice_table();
	if ( '1' !== get_option( 'micada_home_notices_migrated' ) ) {
		$file = micada_home_notice_file();
		if ( is_readable( $file ) ) {
			$saved = json_decode( file_get_contents( $file ), true );
			if ( isset( $saved['notices'] ) && is_array( $saved['notices'] ) ) {
				$notices = array_values( $saved['notices'] );
			} elseif ( isset( $saved['notice_en'] ) || isset( $saved['notice_hi'] ) ) {
				$notices = array( $saved );
			} else {
				$notices = array();
			}
		} else {
			$notices = array( micada_home_notice_defaults() );
		}
		$ok = true;
		foreach ( $notices as $order => $notice ) {
			$id = sanitize_text_field( (string) ( $notice['id'] ?? wp_generate_uuid4() ) );
			if ( '' === $id ) {
				$id = wp_generate_uuid4();
			}
			$result = $wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO `{$notices_table}` (id, notice_en, notice_hi, is_enabled, sort_order) VALUES (%s, %s, %s, %d, %d)",
				$id,
				sanitize_textarea_field( $notice['notice_en'] ?? '' ),
				sanitize_textarea_field( $notice['notice_hi'] ?? '' ),
				! empty( $notice['is_enabled'] ) ? 1 : 0,
				(int) $order
			) );
			if ( false === $result ) {
				$ok = false;
				break;
			}
		}
		if ( $ok ) {
			update_option( 'micada_home_notices_migrated', '1', false );
		}
	}
}
}


if ( ! function_exists( 'micada_get_vendor_banner' ) ) {
function micada_get_vendor_banner() {
	global $wpdb;
	$rows = $wpdb->get_results( 'SELECT * FROM ' . micada_important_message_table() . ' ORDER BY sort_order ASC, created_at DESC', ARRAY_A );
	return is_array( $rows ) ? $rows : array();
}
}


if ( ! function_exists( 'micada_save_vendor_banners' ) ) {
function micada_save_vendor_banners( $items ) {
	global $wpdb;
	$table = micada_important_message_table();
	$keep_ids = array();
	foreach ( array_values( $items ) as $order => $item ) {
		$id = sanitize_text_field( (string) ( $item['id'] ?? '' ) );
		if ( '' === $id ) {
			$id = wp_generate_uuid4();
		}
		$keep_ids[] = $id;
		$data = array(
			'id' => $id,
			'text_en' => sanitize_text_field( $item['text_en'] ?? '' ),
			'text_hi' => sanitize_text_field( $item['text_hi'] ?? '' ),
			'is_enabled' => ! empty( $item['is_enabled'] ) ? 1 : 0,
			'show_new' => ! empty( $item['show_new'] ) ? 1 : 0,
			'created_at' => absint( $item['created_at'] ?? current_time( 'timestamp' ) ),
			'sort_order' => (int) $order,
			'display_status' => in_array( $item['display_status'] ?? $item['list_status'] ?? 'post', array( 'post', 'trash' ), true ) ? ( $item['display_status'] ?? $item['list_status'] ?? 'post' ) : 'post',
		);
		$result = $wpdb->replace( $table, $data, array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s' ) );
		if ( false === $result ) {
			return false;
		}
	}
	if ( $keep_ids ) {
		$placeholders = implode( ',', array_fill( 0, count( $keep_ids ), '%s' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE id NOT IN ({$placeholders})", $keep_ids ) );
	} else {
		$wpdb->query( "DELETE FROM `{$table}`" );
	}
	return empty( $wpdb->last_error );
}
}


if ( ! function_exists( 'micada_vendor_banner_save' ) ) {
function micada_vendor_banner_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage this homepage banner.', 'micada' ) );
	}
	check_admin_referer( 'micada_vendor_banner_save' );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$item = array(
		'id' => '' !== $id ? $id : wp_generate_uuid4(),
		'text_en' => sanitize_text_field( wp_unslash( $_POST['text_en'] ?? '' ) ),
		'text_hi' => sanitize_text_field( wp_unslash( $_POST['text_hi'] ?? '' ) ),
		'is_enabled' => isset( $_POST['is_enabled'] ),
		'show_new' => isset( $_POST['show_new'] ),
	);
	if ( '' === $item['text_en'] && '' === $item['text_hi'] ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'micada-important', 'message' => 'invalid' ), admin_url( 'admin.php' ) ) );
		exit;
	}
	$items = micada_get_vendor_banner();
	$found = false;
	foreach ( $items as $index => $existing ) {
		if ( (string) ( $existing['id'] ?? '' ) === $id && '' !== $id ) {
			$item['created_at'] = absint( $existing['created_at'] ?? 0 );
			$items[ $index ] = $item;
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		$item['created_at'] = current_time( 'timestamp' );
		array_unshift( $items, $item );
	}
	$saved = micada_save_vendor_banners( $items );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-important', 'message' => $saved ? 'saved' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_vendor_banner_save', 'micada_vendor_banner_save' );

if ( ! function_exists( 'micada_vendor_banner_delete' ) ) {
function micada_vendor_banner_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage homepage important messages.', 'micada' ) );
	}
	$id = sanitize_text_field( wp_unslash( $_GET['id'] ?? '' ) );
	check_admin_referer( 'micada_vendor_banner_delete_' . $id );
	global $wpdb;
	$saved = false !== $wpdb->update( micada_important_message_table(), array( 'display_status' => 'trash' ), array( 'id' => $id ), array( '%s' ), array( '%s' ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-important', 'message' => $saved ? 'deleted' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_vendor_banner_delete', 'micada_vendor_banner_delete' );

if ( ! function_exists( 'micada_vendor_banner_admin_page' ) ) {
function micada_vendor_banner_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$items = micada_get_vendor_banner();
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$edit_id = sanitize_text_field( wp_unslash( $_GET['edit'] ?? '' ) );
	$is_add = isset( $_GET['add'] );
	$current = null;
	foreach ( $items as $item ) {
		if ( '' !== $edit_id && (string) ( $item['id'] ?? '' ) === $edit_id ) {
			$current = $item;
			break;
		}
	}
	if ( ! $current ) {
		$current = array( 'id' => '', 'text_en' => '', 'text_hi' => '', 'is_enabled' => true, 'show_new' => true );
	}
	$message = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Important Message', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-important-add"><?php esc_html_e( 'Add Important Message', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'Manage messages shown in the highlighted homepage area. Messages are stored in the Important Messages database table.', 'micada' ); ?></p>
		<?php if ( 'saved' === $message ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Important message saved.', 'micada' ); ?></p></div><?php elseif ( 'deleted' === $message ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Important message deleted.', 'micada' ); ?></p></div><?php elseif ( 'invalid' === $message ) : ?><div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Enter English or Hindi text before saving.', 'micada' ); ?></p></div><?php elseif ( 'error' === $message ) : ?><div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'The database could not save this important message. Please try again.', 'micada' ); ?></p></div><?php endif; ?>
		<div id="micada-important-modal" class="micada-message-modal<?php echo ( $is_add || '' !== $edit_id ) ? ' is-open' : ''; ?>" aria-hidden="<?php echo ( $is_add || '' !== $edit_id ) ? 'false' : 'true'; ?>">
			<div class="micada-message-backdrop" data-important-close></div>
			<section class="micada-message-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-important-title">
				<header><h2 id="micada-important-title"><?php echo '' !== $edit_id ? esc_html__( 'Edit Important Message', 'micada' ) : esc_html__( 'Add Important Message', 'micada' ); ?></h2><button type="button" class="micada-message-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-important-close>&times;</button></header>
				<form id="micada-important-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="micada_vendor_banner_save"><input type="hidden" name="id" value="<?php echo esc_attr( $current['id'] ); ?>">
					<?php wp_nonce_field( 'micada_vendor_banner_save' ); ?>
					<p><label for="micada-important-en"><strong><?php esc_html_e( 'English message', 'micada' ); ?></strong></label><input class="widefat" id="micada-important-en" name="text_en" value="<?php echo esc_attr( $current['text_en'] ); ?>"></p>
					<p><label for="micada-important-hi"><strong><?php esc_html_e( 'Hindi message (optional)', 'micada' ); ?></strong></label><input class="widefat" id="micada-important-hi" name="text_hi" value="<?php echo esc_attr( $current['text_hi'] ); ?>"></p>
					<p><label><input type="checkbox" name="show_new" value="1" <?php checked( ! empty( $current['show_new'] ) ); ?>> <?php esc_html_e( 'Show New badge', 'micada' ); ?></label><br><label><input type="checkbox" name="is_enabled" value="1" <?php checked( ! empty( $current['is_enabled'] ) ); ?>> <?php esc_html_e( 'Display this message on the homepage', 'micada' ); ?></label></p>
					<div class="micada-message-actions"><button type="submit" class="button button-primary" id="micada-important-submit"><?php echo '' !== $edit_id ? esc_html__( 'Update Message', 'micada' ) : esc_html__( 'Add Message', 'micada' ); ?></button><button type="button" class="button" data-important-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
		<h2><?php esc_html_e( 'Important Messages', 'micada' ); ?></h2>
		<?php micada_cms_status_tabs( 'micada-important', micada_important_message_table(), $view ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'English', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi', 'micada' ); ?></th><th><?php esc_html_e( 'Visibility', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php $shown_items = array_filter( $items, static function ( $item ) use ( $view ) { return ( $item['display_status'] ?? $item['list_status'] ?? 'post' ) === $view; } ); if ( $shown_items ) : foreach ( $shown_items as $item ) : $item_id = (string) ( $item['id'] ?? '' ); ?>
			<tr><td><?php echo esc_html( $item['text_en'] ?? '' ); ?></td><td><?php echo esc_html( $item['text_hi'] ?? '' ); ?></td><td><?php echo ! empty( $item['is_enabled'] ) ? esc_html__( 'Visible', 'micada' ) : esc_html__( 'Hidden', 'micada' ); ?></td><td><?php echo 'trash' === ( $item['display_status'] ?? $item['list_status'] ?? 'post' ) ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'micada-important', 'edit' => $item_id ), admin_url( 'admin.php' ) ) ); ?>#important-form"><?php esc_html_e( 'Edit', 'micada' ); ?></a> | <?php micada_cms_trash_action_links( 'important-message', $item_id, $item['display_status'] ?? $item['list_status'] ?? 'post' ); ?></td></tr>
		<?php endforeach; else : ?><tr><td colspan="5"><?php esc_html_e( 'No important messages have been added.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
	</div>
	<style>
		.micada-message-modal{display:none;position:fixed;inset:0;z-index:100100;align-items:center;justify-content:center;padding:24px}.micada-message-modal.is-open{display:flex}.micada-message-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}.micada-message-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}.micada-message-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.micada-message-dialog header h2{margin:0;font-size:21px}.micada-message-dialog form>p{margin:0 0 16px}.micada-message-dialog form>p>label{display:block;margin-bottom:6px}.micada-message-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}.micada-message-actions{display:flex;gap:8px;margin-top:22px}body.micada-message-modal-open{overflow:hidden}
	</style>
	<script>
	(function(){const modal=document.getElementById('micada-important-modal'),form=document.getElementById('micada-important-form');if(!modal||!form)return;const open=function(){modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-message-modal-open');document.getElementById('micada-important-en').focus();},close=function(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-message-modal-open');};document.getElementById('micada-important-add').addEventListener('click',function(){form.reset();form.elements.id.value='';form.elements.text_en.value='';form.elements.text_hi.value='';form.elements.show_new.checked=true;form.elements.is_enabled.checked=true;document.getElementById('micada-important-title').textContent='<?php echo esc_js( __( 'Add Important Message', 'micada' ) ); ?>';document.getElementById('micada-important-submit').textContent='<?php echo esc_js( __( 'Add Message', 'micada' ) ); ?>';open();});modal.querySelectorAll('[data-important-close]').forEach(function(button){button.addEventListener('click',close);});document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});if(modal.classList.contains('is-open'))document.body.classList.add('micada-message-modal-open');})();
	</script>
	<?php
}
}


if ( ! function_exists( 'micada_render_vendor_banner' ) ) {
function micada_render_vendor_banner() {
	$items = micada_get_vendor_banner();
	$language = function_exists( 'micada_language' ) ? micada_language() : 'en';
	$visible = array();
	foreach ( $items as $item ) {
		if ( empty( $item['is_enabled'] ) || 'trash' === ( $item['display_status'] ?? $item['list_status'] ?? 'post' ) ) {
			continue;
		}
		$text = 'hd' === $language && ! empty( $item['text_hi'] ) ? $item['text_hi'] : ( $item['text_en'] ?? '' );
		if ( '' !== trim( $text ) ) {
			$visible[] = array( 'text' => $text, 'show_new' => ! empty( $item['show_new'] ) );
		}
	}
	if ( ! $visible ) {
		return;
	}
	echo '<div class="new-registration mt-2">';
	foreach ( $visible as $item ) {
		echo '<p>';
		if ( $item['show_new'] ) {
			echo '<img src="' . esc_url( micada_asset( 'activitiesImages/newitem.gif' ) ) . '" alt="' . esc_attr__( 'New', 'astra-child' ) . '"> ';
		}
		echo '<span>' . esc_html( $item['text'] ) . '</span></p>';
	}
	echo '</div>';
}
}


if ( ! function_exists( 'micada_home_notice_defaults' ) ) {
function micada_home_notice_defaults() {
	return array(
		'id' => 'default',
		'notice_en' => 'Notice: Pre-Estimate Survey and Physical Verification (PV) services are temporarily under maintenance. These services will resume shortly. We regret the inconvenience.',
		'notice_hi' => 'सूचना: प्री-एस्टीमेट सर्वे और भौतिक सत्यापन (PV) सेवाएं अस्थायी रूप से रखरखाव के अधीन हैं। ये सेवाएं शीघ्र ही फिर से शुरू होंगी। असुविधा के लिए खेद है।',
		'is_enabled' => true,
	);
}
}


if ( ! function_exists( 'micada_home_notice_file' ) ) {
function micada_home_notice_file() {
	$uploads = wp_upload_dir();
	return trailingslashit( $uploads['basedir'] ) . 'micada/home-notice.json';
}
}


if ( ! function_exists( 'micada_get_home_notices' ) ) {
function micada_get_home_notices() {
	global $wpdb;
	$rows = $wpdb->get_results( 'SELECT * FROM ' . micada_home_notice_table() . ' ORDER BY sort_order ASC', ARRAY_A );
	return is_array( $rows ) ? $rows : array();
}
}


if ( ! function_exists( 'micada_save_home_notices' ) ) {
function micada_save_home_notices( $notices ) {
	global $wpdb;
	$table = micada_home_notice_table();
	$keep_ids = array();
	foreach ( array_values( $notices ) as $order => $notice ) {
		$id = sanitize_text_field( (string) ( $notice['id'] ?? '' ) );
		if ( '' === $id ) {
			$id = wp_generate_uuid4();
		}
		$keep_ids[] = $id;
		$result = $wpdb->replace(
			$table,
			array(
				'id' => $id,
				'notice_en' => sanitize_textarea_field( $notice['notice_en'] ?? '' ),
				'notice_hi' => sanitize_textarea_field( $notice['notice_hi'] ?? '' ),
				'is_enabled' => ! empty( $notice['is_enabled'] ) ? 1 : 0,
				'sort_order' => (int) $order,
				'display_status' => in_array( $notice['display_status'] ?? $notice['list_status'] ?? 'post', array( 'post', 'trash' ), true ) ? ( $notice['display_status'] ?? $notice['list_status'] ?? 'post' ) : 'post',
			),
			array( '%s', '%s', '%s', '%d', '%d', '%s' )
		);
		if ( false === $result ) {
			return false;
		}
	}
	if ( $keep_ids ) {
		$placeholders = implode( ',', array_fill( 0, count( $keep_ids ), '%s' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE id NOT IN ({$placeholders})", $keep_ids ) );
	} else {
		$wpdb->query( "DELETE FROM `{$table}`" );
	}
	return empty( $wpdb->last_error );
}
}


if ( ! function_exists( 'micada_home_notice_save' ) ) {
function micada_home_notice_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage homepage notices.', 'micada' ) );
	}
	check_admin_referer( 'micada_home_notice_save' );
	$id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
	$notice = array(
		'id' => '' !== $id ? $id : wp_generate_uuid4(),
		'notice_en' => sanitize_textarea_field( wp_unslash( $_POST['notice_en'] ?? '' ) ),
		'notice_hi' => sanitize_textarea_field( wp_unslash( $_POST['notice_hi'] ?? '' ) ),
		'is_enabled' => isset( $_POST['is_enabled'] ),
	);
	if ( '' === $notice['notice_en'] && '' === $notice['notice_hi'] ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'micada-homepage-notice', 'message' => 'invalid' ), admin_url( 'admin.php' ) ) );
		exit;
	}
	$notices = micada_get_home_notices();
	$found = false;
	foreach ( $notices as $index => $existing ) {
		if ( (string) ( $existing['id'] ?? '' ) === $id && '' !== $id ) {
			$notice['display_status'] = $existing['display_status'] ?? 'post';
			$notices[ $index ] = $notice;
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		$notices[] = $notice;
	}
	$saved = micada_save_home_notices( $notices );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-homepage-notice', 'message' => $saved ? 'saved' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_home_notice_save', 'micada_home_notice_save' );

if ( ! function_exists( 'micada_home_notice_delete' ) ) {
function micada_home_notice_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage homepage notices.', 'micada' ) );
	}
	$id = sanitize_text_field( wp_unslash( $_GET['id'] ?? '' ) );
	check_admin_referer( 'micada_home_notice_delete_' . $id );
	global $wpdb;
	$saved = false !== $wpdb->update( micada_home_notice_table(), array( 'display_status' => 'trash' ), array( 'id' => $id ), array( '%s' ), array( '%s' ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-homepage-notice', 'message' => $saved ? 'deleted' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_home_notice_delete', 'micada_home_notice_delete' );

if ( ! function_exists( 'micada_home_notice_hide_all' ) ) {
function micada_home_notice_hide_all() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage homepage notices.', 'micada' ) );
	}
	check_admin_referer( 'micada_home_notice_hide_all' );
	$notices = micada_get_home_notices();
	foreach ( $notices as &$notice ) {
		$notice['is_enabled'] = false;
	}
	unset( $notice );
	$saved = micada_save_home_notices( $notices );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-homepage-notice', 'message' => $saved ? 'hidden' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_home_notice_hide_all', 'micada_home_notice_hide_all' );

if ( ! function_exists( 'micada_home_notice_admin_page' ) ) {
function micada_home_notice_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$notices = micada_get_home_notices();
	$edit_id = sanitize_text_field( wp_unslash( $_GET['edit'] ?? '' ) );
	$is_add = isset( $_GET['add'] );
	$current = null;
	foreach ( $notices as $notice ) {
		if ( '' !== $edit_id && (string) ( $notice['id'] ?? '' ) === $edit_id ) {
			$current = $notice;
			break;
		}
	}
	if ( $is_add || ! $current ) {
		$current = array( 'id' => '', 'notice_en' => '', 'notice_hi' => '', 'is_enabled' => true );
	}
	$message = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	$base_url = admin_url( 'admin.php?page=micada-homepage-notice' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage Notice', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-home-notice-add"><?php esc_html_e( 'Add New Notice', 'micada' ); ?></button> <a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=micada_home_notice_hide_all' ), 'micada_home_notice_hide_all' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Remove all notices from the homepage? You can show them again by editing a notice.', 'micada' ) ); ?>');"><?php esc_html_e( 'Remove from Homepage', 'micada' ); ?></a></h1>
		<p><?php esc_html_e( 'Manage the notices shown below Farmer Corner. Notices are stored in the Homepage Notices database table.', 'micada' ); ?></p>
		<?php if ( 'saved' === $message ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Notice saved.', 'micada' ); ?></p></div><?php elseif ( 'deleted' === $message ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Notice deleted.', 'micada' ); ?></p></div><?php elseif ( 'hidden' === $message ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'All notices have been removed from the homepage. Edit a notice and enable display to show it again.', 'micada' ); ?></p></div><?php elseif ( 'invalid' === $message ) : ?><div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Enter English or Hindi text before saving.', 'micada' ); ?></p></div><?php elseif ( 'error' === $message ) : ?><div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'The database could not save the notice. Please try again.', 'micada' ); ?></p></div><?php endif; ?>
		<div id="micada-home-notice-modal" class="micada-home-notice-modal<?php echo ( $is_add || '' !== $edit_id ) ? ' is-open' : ''; ?>" aria-hidden="<?php echo ( $is_add || '' !== $edit_id ) ? 'false' : 'true'; ?>">
			<div class="micada-home-notice-backdrop" data-home-notice-close></div>
			<section class="micada-home-notice-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-home-notice-title">
				<header><h2 id="micada-home-notice-title"><?php echo '' !== $edit_id ? esc_html__( 'Edit Notice', 'micada' ) : esc_html__( 'Add Notice', 'micada' ); ?></h2><button type="button" class="micada-home-notice-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-home-notice-close>&times;</button></header>
				<form id="micada-home-notice-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="micada_home_notice_save"><input type="hidden" id="micada-home-notice-id" name="id" value="<?php echo esc_attr( $current['id'] ); ?>">
					<?php wp_nonce_field( 'micada_home_notice_save' ); ?>
					<p><label for="micada-notice-en"><strong><?php esc_html_e( 'English notice', 'micada' ); ?></strong></label><textarea class="widefat" rows="3" id="micada-notice-en" name="notice_en"><?php echo esc_textarea( $current['notice_en'] ); ?></textarea></p>
					<p><label for="micada-notice-hi"><strong><?php esc_html_e( 'Hindi notice', 'micada' ); ?></strong></label><textarea class="widefat" rows="3" id="micada-notice-hi" name="notice_hi"><?php echo esc_textarea( $current['notice_hi'] ); ?></textarea></p>
					<p><label><input type="checkbox" id="micada-home-notice-active" name="is_enabled" value="1" <?php checked( ! empty( $current['is_enabled'] ) ); ?>> <?php esc_html_e( 'Display this notice on the homepage', 'micada' ); ?></label></p>
					<div class="micada-home-notice-actions"><button type="submit" class="button button-primary" id="micada-home-notice-submit"><?php echo '' !== $edit_id ? esc_html__( 'Update Notice', 'micada' ) : esc_html__( 'Add Notice', 'micada' ); ?></button><button type="button" class="button" data-home-notice-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
		<h2><?php esc_html_e( 'Notices', 'micada' ); ?></h2>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'English', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php if ( $notices ) : foreach ( $notices as $notice ) : $notice_id = (string) ( $notice['id'] ?? '' ); ?>
			<tr><td><?php echo esc_html( $notice['notice_en'] ?? '' ); ?></td><td><?php echo esc_html( $notice['notice_hi'] ?? '' ); ?></td><td><?php echo ! empty( $notice['is_enabled'] ) ? esc_html__( 'Visible', 'micada' ) : esc_html__( 'Hidden', 'micada' ); ?></td><td><button type="button" class="button-link micada-home-notice-edit" data-id="<?php echo esc_attr( $notice_id ); ?>" data-notice-en="<?php echo esc_attr( $notice['notice_en'] ?? '' ); ?>" data-notice-hi="<?php echo esc_attr( $notice['notice_hi'] ?? '' ); ?>" data-is-enabled="<?php echo ! empty( $notice['is_enabled'] ) ? '1' : '0'; ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'micada_home_notice_delete', 'id' => $notice_id ), admin_url( 'admin-post.php' ) ), 'micada_home_notice_delete_' . $notice_id ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this notice?', 'micada' ) ); ?>');"><?php esc_html_e( 'Delete', 'micada' ); ?></a></td></tr>
		<?php endforeach; else : ?><tr><td colspan="4"><?php esc_html_e( 'No notices. The homepage notice bar will remain in place.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
	</div>
	<style>
		.micada-home-notice-modal{display:none;position:fixed;inset:0;z-index:100100;align-items:center;justify-content:center;padding:24px}
		.micada-home-notice-modal.is-open{display:flex}
		.micada-home-notice-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}
		.micada-home-notice-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}
		.micada-home-notice-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
		.micada-home-notice-dialog header h2{margin:0;font-size:21px}
		.micada-home-notice-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}
		.micada-home-notice-dialog form>p{margin:0 0 16px}
		.micada-home-notice-dialog form>p>label{display:block;margin-bottom:6px}
		.micada-home-notice-actions{display:flex;gap:8px;margin-top:22px}
		body.micada-home-notice-modal-open{overflow:hidden}
	</style>
	<script>
	(function(){
		const modal=document.getElementById('micada-home-notice-modal');
		const form=document.getElementById('micada-home-notice-form');
		const title=document.getElementById('micada-home-notice-title');
		const submit=document.getElementById('micada-home-notice-submit');
		const open=function(){modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-home-notice-modal-open');document.getElementById('micada-notice-en').focus();};
		const close=function(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-home-notice-modal-open');};
		document.getElementById('micada-home-notice-add').addEventListener('click',function(){form.reset();form.elements.id.value='';form.elements.notice_en.value='';form.elements.notice_hi.value='';document.getElementById('micada-home-notice-active').checked=true;title.textContent='<?php echo esc_js( __( 'Add Notice', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Add Notice', 'micada' ) ); ?>';open();});
		document.querySelectorAll('.micada-home-notice-edit').forEach(function(button){button.addEventListener('click',function(){form.elements.id.value=button.dataset.id;form.elements.notice_en.value=button.dataset.noticeEn;form.elements.notice_hi.value=button.dataset.noticeHi;document.getElementById('micada-home-notice-active').checked=button.dataset.isEnabled==='1';title.textContent='<?php echo esc_js( __( 'Edit Notice', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Update Notice', 'micada' ) ); ?>';open();});});
		modal.querySelectorAll('[data-home-notice-close]').forEach(function(button){button.addEventListener('click',close);});
		document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});
		if(modal.classList.contains('is-open'))document.body.classList.add('micada-home-notice-modal-open');
	})();
	</script>
	<?php
}
}


if ( ! function_exists( 'micada_render_home_notice' ) ) {
function micada_render_home_notice() {
	$notices = micada_get_home_notices();
	$language = function_exists( 'micada_language' ) ? micada_language() : 'en';
	$visible_text = array();
	foreach ( $notices as $notice ) {
		if ( empty( $notice['is_enabled'] ) || 'trash' === ( $notice['display_status'] ?? $notice['list_status'] ?? 'post' ) ) {
			continue;
		}
		$text = 'hd' === $language && ! empty( $notice['notice_hi'] ) ? $notice['notice_hi'] : ( $notice['notice_en'] ?? '' );
		if ( '' === trim( $text ) ) {
			continue;
		}
		$visible_text[] = esc_html( $text );
	}
	if ( ! $visible_text ) {
		return;
	}
	echo '<div class="maintenance-notice" role="status">' . micada_icon_svg( 'exclamation-triangle', 'maintenance-icon' ) . ' ' . implode( '<br>', $visible_text ) . '</div>';
}
}


if ( ! function_exists( 'micada_home_ticker_admin_url' ) ) {
function micada_home_ticker_admin_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'micada-homepage-ticker' ), $args ), admin_url( 'admin.php' ) );
}
}


if ( ! function_exists( 'micada_home_ticker_save' ) ) {
function micada_home_ticker_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage homepage messages.', 'micada' ) );
	}
	check_admin_referer( 'micada_home_ticker_save' );
	global $wpdb;
	$id = absint( $_POST['id'] ?? 0 );
	$order_raw = trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ?? '' ) ) );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$data = array(
		'title_en' => sanitize_text_field( wp_unslash( $_POST['title_en'] ?? '' ) ),
		'title_hi' => sanitize_text_field( wp_unslash( $_POST['title_hi'] ?? '' ) ),
		'url' => $url,
		'new_tab' => isset( $_POST['new_tab'] ) ? 1 : 0,
		'show_new' => isset( $_POST['show_new'] ) ? 1 : 0,
		'is_active' => isset( $_POST['is_active'] ) ? 1 : 0,
		'sort_order' => ctype_digit( $order_raw ) ? (int) $order_raw : -1,
	);
	if ( '' === $data['title_en'] || $data['sort_order'] < 0 || ( '' !== $url && ! wp_http_validate_url( $url ) ) ) {
		wp_safe_redirect( micada_home_ticker_admin_url( array( 'message' => 'invalid' ) ) );
		exit;
	}
	$formats = array( '%s', '%s', '%s', '%d', '%d', '%d', '%d' );
	if ( $id ) {
		$saved = false !== $wpdb->update( micada_home_ticker_table(), $data, array( 'id' => $id ), $formats, array( '%d' ) );
	} else {
		$saved = false !== $wpdb->insert( micada_home_ticker_table(), $data, $formats );
	}
	wp_safe_redirect( micada_home_ticker_admin_url( array( 'message' => $saved ? 'saved' : 'error' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_home_ticker_save', 'micada_home_ticker_save' );

if ( ! function_exists( 'micada_home_ticker_delete' ) ) {
function micada_home_ticker_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage homepage messages.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	check_admin_referer( 'micada_home_ticker_delete_' . $id );
	global $wpdb;
	$wpdb->delete( micada_home_ticker_table(), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( micada_home_ticker_admin_url( array( 'message' => 'deleted' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_home_ticker_delete', 'micada_home_ticker_delete' );

if ( ! function_exists( 'micada_home_ticker_admin_page' ) ) {
function micada_home_ticker_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = micada_home_ticker_table();
	$edit_id = absint( $_GET['edit'] ?? 0 );
	$is_add = isset( $_GET['add'] );
	$item = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ), ARRAY_A ) : null;
	$next_order = 1 + (int) $wpdb->get_var( "SELECT COALESCE(MAX(sort_order), 0) FROM {$table}" );
	$item = $item ?: array( 'id' => 0, 'title_en' => '', 'title_hi' => '', 'url' => '', 'new_tab' => 0, 'show_new' => 1, 'is_active' => 1, 'sort_order' => $next_order );
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE display_status = %s ORDER BY sort_order DESC, id DESC", $view ) );
	$messages = array(
		'saved' => __( 'Ticker message saved.', 'micada' ),
		'deleted' => __( 'Ticker message deleted.', 'micada' ),
		'invalid' => __( 'Enter an English message, a whole-number display order, and a valid URL when provided.', 'micada' ),
		'error' => __( 'The database could not save the message. Please try again.', 'micada' ),
	);
	$key = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage Messages', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-ticker-add"><?php esc_html_e( 'Add Message', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'Manage the messages that scroll from right to left beside Farmer Corner. Clear “Display this message” to hide an item without deleting it.', 'micada' ); ?></p>
		<?php if ( isset( $messages[ $key ] ) ) : ?><div class="notice <?php echo 'invalid' === $key ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $messages[ $key ] ); ?></p></div><?php endif; ?>
		<div id="micada-ticker-modal" class="micada-message-modal<?php echo ( $is_add || $edit_id ) ? ' is-open' : ''; ?>" aria-hidden="<?php echo ( $is_add || $edit_id ) ? 'false' : 'true'; ?>">
			<div class="micada-message-backdrop" data-ticker-close></div>
			<section class="micada-message-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-ticker-title">
				<header><h2 id="micada-ticker-title"><?php echo $edit_id ? esc_html__( 'Edit Message', 'micada' ) : esc_html__( 'Add Message', 'micada' ); ?></h2><button type="button" class="micada-message-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-ticker-close>&times;</button></header>
				<form id="micada-ticker-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="micada_home_ticker_save"><input type="hidden" name="id" value="<?php echo esc_attr( $item['id'] ); ?>">
					<?php wp_nonce_field( 'micada_home_ticker_save' ); ?>
					<p><label for="micada-ticker-en"><strong><?php esc_html_e( 'English message', 'micada' ); ?></strong></label><input class="widefat" required id="micada-ticker-en" name="title_en" value="<?php echo esc_attr( $item['title_en'] ); ?>"></p>
					<p><label for="micada-ticker-hi"><strong><?php esc_html_e( 'Hindi message (optional)', 'micada' ); ?></strong></label><input class="widefat" id="micada-ticker-hi" name="title_hi" value="<?php echo esc_attr( $item['title_hi'] ); ?>"></p>
					<p><label for="micada-ticker-url"><strong><?php esc_html_e( 'Link URL (optional)', 'micada' ); ?></strong></label><input class="widefat" type="url" id="micada-ticker-url" name="url" value="<?php echo esc_attr( $item['url'] ); ?>"></p>
					<p><label for="micada-ticker-order"><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong></label><input type="number" min="0" step="1" required id="micada-ticker-order" name="sort_order" value="<?php echo esc_attr( $item['sort_order'] ); ?>"></p>
					<p><label><input type="checkbox" name="show_new" value="1" <?php checked( $item['show_new'], 1 ); ?>> <?php esc_html_e( 'Show New badge', 'micada' ); ?></label><br><label><input type="checkbox" name="new_tab" value="1" <?php checked( $item['new_tab'], 1 ); ?>> <?php esc_html_e( 'Open link in a new tab', 'micada' ); ?></label><br><label><input type="checkbox" name="is_active" value="1" <?php checked( $item['is_active'], 1 ); ?>> <?php esc_html_e( 'Display this message', 'micada' ); ?></label></p>
					<div class="micada-message-actions"><button type="submit" class="button button-primary" id="micada-ticker-submit"><?php echo $edit_id ? esc_html__( 'Update Message', 'micada' ) : esc_html__( 'Add Message', 'micada' ); ?></button><button type="button" class="button" data-ticker-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
		<h2><?php esc_html_e( 'Messages', 'micada' ); ?></h2>
		<?php micada_cms_status_tabs( 'micada-homepage-ticker', $table, $view ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php esc_html_e( 'English', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi', 'micada' ); ?></th><th><?php esc_html_e( 'Visibility', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php if ( $items ) : foreach ( $items as $message ) : ?>
			<tr><td><?php echo esc_html( $message->sort_order ); ?></td><td><?php echo esc_html( $message->title_en ); ?></td><td><?php echo esc_html( $message->title_hi ); ?></td><td><?php echo $message->is_active ? esc_html__( 'Visible', 'micada' ) : esc_html__( 'Hidden', 'micada' ); ?></td><td><?php echo 'trash' === $message->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><a href="<?php echo esc_url( micada_home_ticker_admin_url( array( 'edit' => $message->id ) ) ); ?>#ticker-form"><?php esc_html_e( 'Edit', 'micada' ); ?></a> | <?php micada_cms_trash_action_links( 'home-ticker', $message->id, $message->display_status ); ?></td></tr>
		<?php endforeach; else : ?><tr><td colspan="5"><?php esc_html_e( 'No ticker messages yet.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
	</div>
	<style>
		.micada-message-modal{display:none;position:fixed;inset:0;z-index:100100;align-items:center;justify-content:center;padding:24px}.micada-message-modal.is-open{display:flex}.micada-message-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}.micada-message-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}.micada-message-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.micada-message-dialog header h2{margin:0;font-size:21px}.micada-message-dialog form>p{margin:0 0 16px}.micada-message-dialog form>p>label{display:block;margin-bottom:6px}.micada-message-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}.micada-message-actions{display:flex;gap:8px;margin-top:22px}body.micada-message-modal-open{overflow:hidden}
	</style>
	<script>
	(function(){const modal=document.getElementById('micada-ticker-modal'),form=document.getElementById('micada-ticker-form');if(!modal||!form)return;const open=function(){modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-message-modal-open');document.getElementById('micada-ticker-en').focus();},close=function(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-message-modal-open');};document.getElementById('micada-ticker-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.title_en.value='';form.elements.title_hi.value='';form.elements.url.value='';form.elements.sort_order.value='<?php echo esc_js( $next_order ); ?>';form.elements.show_new.checked=true;form.elements.new_tab.checked=false;form.elements.is_active.checked=true;document.getElementById('micada-ticker-title').textContent='<?php echo esc_js( __( 'Add Message', 'micada' ) ); ?>';document.getElementById('micada-ticker-submit').textContent='<?php echo esc_js( __( 'Add Message', 'micada' ) ); ?>';open();});modal.querySelectorAll('[data-ticker-close]').forEach(function(button){button.addEventListener('click',close);});document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});if(modal.classList.contains('is-open'))document.body.classList.add('micada-message-modal-open');})();
	</script>
	<?php
}
}


if ( ! function_exists( 'micada_render_home_ticker' ) ) {
function micada_render_home_ticker() {
	global $wpdb;
	$table = micada_home_ticker_table();
	$items = $wpdb->get_results( "SELECT * FROM {$table} WHERE is_active = 1 AND display_status = 'post' ORDER BY sort_order DESC, id DESC" );
	if ( ! $items ) {
		return;
	}
	$language = function_exists( 'micada_language' ) ? micada_language() : 'en';
	echo '<marquee direction="left" scrollamount="5" data-pause-marquee>';
	foreach ( $items as $item ) {
		$text = 'hd' === $language && '' !== $item->title_hi ? $item->title_hi : $item->title_en;
		echo '<span class="s1">';
		if ( $item->show_new ) {
			echo '<img src="' . esc_url( micada_asset( 'activitiesImages/newitem.gif' ) ) . '" alt="' . esc_attr__( 'New', 'micada' ) . '"> ';
		}
		if ( '' !== $item->url ) {
			echo '<a href="' . esc_url( micada_resolve_app_link( $item->url ) ) . '"' . ( $item->new_tab ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $text ) . '</a>';
		} else {
			echo esc_html( $text );
		}
		echo '</span><span class="micada-ticker-separator" aria-hidden="true">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
	}
	echo '</marquee>';
}
}

