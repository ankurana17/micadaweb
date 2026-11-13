<?php
/**
 * Module: Organisation
 * Description: Manages Head Office Executives, Field Executives, and Office Orders.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_org_table' ) ) {
function micada_org_table( $section = '' ) {
	global $wpdb;
	$tables = array(
		'head-office-executives' => 'micada_head_office_executives',
		'field-executives' => 'micada_field_executives',
		'office-orders' => 'micada_office_orders',
	);
	if ( isset( $tables[ $section ] ) ) {
		return $wpdb->prefix . $tables[ $section ];
	}
	return $wpdb->prefix . 'micada_organisation_items';
}
}


if ( ! function_exists( 'micada_org_sections' ) ) {
function micada_org_sections() {
	return array(
		'head-office-executives' => array( 'menu' => __( 'Head Office Executives', 'micada' ), 'heading' => __( 'Incumbency of Head Office Officers', 'micada' ), 'slug' => 'head-office-executives' ),
		'field-executives' => array( 'menu' => __( 'Field Executives', 'micada' ), 'heading' => __( 'Incumbency of Field Officers', 'micada' ), 'slug' => 'field-executives' ),
		'office-orders' => array( 'menu' => __( 'Office Orders', 'micada' ), 'heading' => __( 'ACRs Porforma', 'micada' ), 'slug' => 'office-orders' ),
	);
}
}


if ( ! function_exists( 'micada_org_name_column' ) ) {
function micada_org_name_column( $section ) {
	return 'office-orders' === $section ? 'title' : 'officer_name';
}
}


if ( ! function_exists( 'micada_org_install' ) ) {
function micada_org_install() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$tables = array( 'head-office-executives', 'field-executives', 'office-orders' );
	$needs_upgrade = '4' !== get_option( 'micada_org_schema' );
	foreach ( $tables as $section ) {
		$table = micada_org_table( $section );
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		if ( ! $exists ) {
			$needs_upgrade = true;
		} elseif ( 'office-orders' !== $section ) {
			$columns = $wpdb->get_col( "SHOW COLUMNS FROM `{$table}`", 0 );
			if ( in_array( 'title', $columns, true ) && ! in_array( 'officer_name', $columns, true ) ) {
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE `title` `officer_name` varchar(255) NOT NULL" );
			}
		}
	}
	if ( $needs_upgrade ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $tables as $section ) {
			$table = micada_org_table( $section );
			$name_column = micada_org_name_column( $section );
			$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			{$name_column} varchar(255) NOT NULL,
			designation text NOT NULL,
			landline varchar(255) NOT NULL DEFAULT '',
			mobile varchar(100) NOT NULL DEFAULT '',
			email varchar(255) NOT NULL DEFAULT '',
			url text NOT NULL,
			sort_order int(11) NOT NULL DEFAULT 0,
			display_status varchar(10) NOT NULL DEFAULT 'post',
			PRIMARY KEY  (id),
			KEY item_order (sort_order),
			KEY list_status (display_status)
		) {$charset_collate};";
			dbDelta( $sql );
		}
		update_option( 'micada_org_schema', '4', false );
	}
	micada_org_migrate_combined_table();
	micada_org_migrate_page_lists();
}
}

add_action( 'init', 'micada_org_install', 1 );

if ( ! function_exists( 'micada_org_text' ) ) {
function micada_org_text( $node ) {
	return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $node->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
}
}


if ( ! function_exists( 'micada_org_import_page_rows' ) ) {
function micada_org_import_page_rows( $section, $content ) {
	global $wpdb;
	$table = micada_org_table( $section );
	if ( ! class_exists( 'DOMDocument' ) ) {
		return false;
	}
	$previous = libxml_use_internal_errors( true );
	$document = new DOMDocument( '1.0', 'UTF-8' );
	$document->loadHTML( '<?xml encoding="UTF-8">' . $content );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	$index = 0;
	if ( 'office-orders' === $section ) {
		$links = $document->getElementsByTagName( 'a' );
		foreach ( $links as $link ) {
			$url = esc_url_raw( $link->getAttribute( 'href' ) );
			$title = micada_org_text( $link );
			if ( '' === $url || '' === $title ) {
				continue;
			}
			$wpdb->insert( $table, array( 'title' => $title, 'url' => $url, 'sort_order' => ++$index ), array( '%s', '%s', '%d' ) );
		}
		return true;
	}
	$name_column = micada_org_name_column( $section );
	$rows = $document->getElementsByTagName( 'tr' );
	foreach ( $rows as $row ) {
		$cells = array();
		foreach ( $row->childNodes as $cell ) {
			if ( in_array( strtolower( $cell->nodeName ), array( 'td', 'th' ), true ) ) {
				$cells[] = micada_org_text( $cell );
			}
		}
		if ( count( $cells ) < ( 'field-executives' === $section ? 6 : 5 ) ) {
			continue;
		}
		if ( ! ctype_digit( trim( $cells[0] ) ) ) {
			continue;
		}
		$index++;
		$data = array(
			$name_column => sanitize_text_field( $cells[1] ),
			'designation' => sanitize_textarea_field( $cells[2] ),
			'landline' => sanitize_text_field( $cells[3] ),
			'mobile' => 'field-executives' === $section ? sanitize_text_field( $cells[4] ) : '',
			'email' => sanitize_email( $cells[ 'field-executives' === $section ? 5 : 4 ] ),
			'url' => '',
			'sort_order' => (int) $cells[0],
		);
		$wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%d' ) );
	}
	return true;
}
}


if ( ! function_exists( 'micada_org_migrate_page_lists' ) ) {
function micada_org_migrate_page_lists() {
	if ( '1' === get_option( 'micada_org_migrated' ) ) {
		return;
	}
	global $wpdb;
	$ok = true;
	foreach ( micada_org_sections() as $section => $config ) {
		$table = micada_org_table( $section );
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
		if ( $count > 0 ) {
			continue;
		}
		$page = get_page_by_path( $config['slug'] );
		if ( $page && 'publish' === $page->post_status ) {
			$source = apply_filters( 'the_content', $page->post_content );
			$imported = micada_org_import_page_rows( $section, $source );
			if ( false === $imported ) {
				$ok = false;
			}
		}
	}
	if ( $ok ) {
		update_option( 'micada_org_migrated', '1', false );
	}
}
}


if ( ! function_exists( 'micada_org_migrate_combined_table' ) ) {
function micada_org_migrate_combined_table() {
	if ( '1' === get_option( 'micada_org_split_migrated' ) ) {
		return;
	}
	global $wpdb;
	$old_table = micada_org_table();
	$old_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $old_table ) ) === $old_table;
	if ( ! $old_exists ) {
		update_option( 'micada_org_split_migrated', '1', false );
		return;
	}
	$ok = true;
	foreach ( array_keys( micada_org_sections() ) as $section ) {
		$new_table = micada_org_table( $section );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$old_table}` WHERE list_type = %s ORDER BY id ASC", $section ) );
		$name_column = micada_org_name_column( $section );
		foreach ( $rows as $row ) {
			$data = array(
				$name_column => $row->title,
				'designation' => $row->designation,
				'landline' => $row->landline,
				'mobile' => $row->mobile,
				'email' => $row->email,
				'url' => $row->url,
				'sort_order' => $row->sort_order,
			);
			$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$new_table}` WHERE `{$name_column}` = %s AND sort_order = %d LIMIT 1", $row->title, $row->sort_order ) );
			if ( ! $existing && false === $wpdb->insert( $new_table, $data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%d' ) ) ) {
				$ok = false;
			}
		}
	}
	if ( $ok ) {
		update_option( 'micada_org_split_migrated', '1', false );
	}
}
}


if ( ! function_exists( 'micada_org_admin_menu' ) ) {
function micada_org_admin_menu() {
	add_menu_page( __( 'Organisation', 'micada' ), __( 'Organisation', 'micada' ), 'manage_options', 'micada-organisation', 'micada_org_head_admin_page', 'dashicons-networking', 28 );
	add_submenu_page( 'micada-organisation', __( 'Head Office Executives', 'micada' ), __( 'Head Office Executives', 'micada' ), 'manage_options', 'micada-organisation-head', 'micada_org_head_admin_page' );
	add_submenu_page( 'micada-organisation', __( 'Field Executives', 'micada' ), __( 'Field Executives', 'micada' ), 'manage_options', 'micada-organisation-field', 'micada_org_field_admin_page' );
	add_submenu_page( 'micada-organisation', __( 'Office Orders', 'micada' ), __( 'Office Orders', 'micada' ), 'manage_options', 'micada-organisation-orders', 'micada_org_orders_admin_page' );
	remove_submenu_page( 'micada-organisation', 'micada-organisation' );
}
}

add_action( 'admin_menu', 'micada_org_admin_menu' );

if ( ! function_exists( 'micada_org_head_admin_page' ) ) {
function micada_org_head_admin_page() { micada_org_admin_page( 'head-office-executives' ); }
}

if ( ! function_exists( 'micada_org_field_admin_page' ) ) {
function micada_org_field_admin_page() { micada_org_admin_page( 'field-executives' ); }
}

if ( ! function_exists( 'micada_org_orders_admin_page' ) ) {
function micada_org_orders_admin_page() { micada_org_admin_page( 'office-orders' ); }
}


if ( ! function_exists( 'micada_org_admin_url' ) ) {
function micada_org_admin_url( $section, $args = array() ) {
	$pages = array( 'head-office-executives' => 'micada-organisation-head', 'field-executives' => 'micada-organisation-field', 'office-orders' => 'micada-organisation-orders' );
	return add_query_arg( array_merge( array( 'page' => $pages[ $section ] ), $args ), admin_url( 'admin.php' ) );
}
}


if ( ! function_exists( 'micada_org_admin_page_slug' ) ) {
function micada_org_admin_page_slug( $section ) {
	$pages = array( 'head-office-executives' => 'micada-organisation-head', 'field-executives' => 'micada-organisation-field', 'office-orders' => 'micada-organisation-orders' );
	return $pages[ $section ] ?? 'micada-organisation-head';
}
}


if ( ! function_exists( 'micada_org_save' ) ) {
function micada_org_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage Organisation lists.', 'micada' ) );
	}
	$section = sanitize_key( wp_unslash( $_POST['list_type'] ?? '' ) );
	if ( ! isset( micada_org_sections()[ $section ] ) ) {
		wp_die( esc_html__( 'Invalid Organisation list.', 'micada' ) );
	}
	check_admin_referer( 'micada_org_save_' . $section );
	global $wpdb;
	$id = absint( $_POST['id'] ?? 0 );
	$order_raw = trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ?? '' ) ) );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$name_column = micada_org_name_column( $section );
	$data = array(
		$name_column => sanitize_text_field( wp_unslash( $_POST[ $name_column ] ?? '' ) ),
		'designation' => sanitize_textarea_field( wp_unslash( $_POST['designation'] ?? '' ) ),
		'landline' => sanitize_text_field( wp_unslash( $_POST['landline'] ?? '' ) ),
		'mobile' => sanitize_text_field( wp_unslash( $_POST['mobile'] ?? '' ) ),
		'email' => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'url' => $url,
		'sort_order' => 1 === preg_match( '/^\d+$/', $order_raw ) ? (int) $order_raw : -1,
	);
	$invalid = '' === $data[ $name_column ] || $data['sort_order'] < 0;
	if ( 'office-orders' === $section ) {
		$invalid = $invalid || '' === $url;
	} else {
		$invalid = $invalid || '' === $data['designation'];
	}
	if ( $invalid ) {
		wp_safe_redirect( micada_org_admin_url( $section, array( 'message' => 'invalid', 'edit' => $id ) ) );
		exit;
	}
	$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d' );
	$table = micada_org_table( $section );
	if ( $id ) {
		$saved = false !== $wpdb->update( $table, $data, array( 'id' => $id ), $formats, array( '%d' ) );
	} else {
		$saved = false !== $wpdb->insert( $table, $data, $formats );
	}
	wp_safe_redirect( micada_org_admin_url( $section, array( 'message' => $saved ? 'saved' : 'error' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_org_save', 'micada_org_save' );

if ( ! function_exists( 'micada_org_delete' ) ) {
function micada_org_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage Organisation lists.', 'micada' ) );
	}
	$section = sanitize_key( wp_unslash( $_GET['list_type'] ?? '' ) );
	$id = absint( $_GET['id'] ?? 0 );
	if ( ! isset( micada_org_sections()[ $section ] ) ) {
		wp_die( esc_html__( 'Invalid Organisation list.', 'micada' ) );
	}
	check_admin_referer( 'micada_org_delete_' . $section . '_' . $id );
	global $wpdb;
	$deleted = false !== $wpdb->delete( micada_org_table( $section ), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( micada_org_admin_url( $section, array( 'message' => $deleted ? 'deleted' : 'error' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_org_delete', 'micada_org_delete' );

if ( ! function_exists( 'micada_org_rows' ) ) {
function micada_org_rows( $section, $direction = 'ASC' ) {
	global $wpdb;
	$order = 'DESC' === strtoupper( $direction ) ? 'DESC' : 'ASC';
	return $wpdb->get_results( 'SELECT * FROM ' . micada_org_table( $section ) . " ORDER BY sort_order {$order}, id {$order}" );
}
}


if ( ! function_exists( 'micada_org_head_table' ) ) {
function micada_org_head_table() { return micada_org_table( 'head-office-executives' ); }
}

if ( ! function_exists( 'micada_org_field_table' ) ) {
function micada_org_field_table() { return micada_org_table( 'field-executives' ); }
}

if ( ! function_exists( 'micada_org_orders_table' ) ) {
function micada_org_orders_table() { return micada_org_table( 'office-orders' ); }
}


if ( ! function_exists( 'micada_org_admin_page' ) ) {
function micada_org_admin_page( $section ) {
	if ( ! current_user_can( 'manage_options' ) || ! isset( micada_org_sections()[ $section ] ) ) {
		return;
	}
	$config = micada_org_sections()[ $section ];
	// Show the newest database IDs first in the admin CMS.
	global $wpdb;
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . micada_org_table( $section ) . ' WHERE display_status = %s ORDER BY id DESC', $view ) );
	$next_order = 1;
	foreach ( $rows as $row ) {
		$next_order = max( $next_order, (int) $row->sort_order + 1 );
	}
	$message = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	$messages = array(
		'saved' => array( 'success', __( 'Organisation entry saved.', 'micada' ) ),
		'deleted' => array( 'success', __( 'Organisation entry deleted.', 'micada' ) ),
		'invalid' => array( 'error', __( 'Enter the required fields and a non-negative whole-number order.', 'micada' ) ),
		'error' => array( 'error', __( 'The database could not save this Organisation entry.', 'micada' ) ),
	);
	$is_orders = 'office-orders' === $section;
	$name_column = micada_org_name_column( $section );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $config['menu'] ); ?> <button type="button" class="page-title-action" id="micada-org-add" data-next-order="<?php echo esc_attr( $next_order ); ?>"><?php esc_html_e( 'Add Entry', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'Manage this list displayed under Organisation in the homepage navigation. Entries are stored in its dedicated Organisation database table.', 'micada' ); ?></p>
		<?php if ( isset( $messages[ $message ] ) ) : ?><div class="notice notice-<?php echo esc_attr( $messages[ $message ][0] ); ?> is-dismissible"><p><?php echo esc_html( $messages[ $message ][1] ); ?></p></div><?php endif; ?>
		<?php micada_cms_status_tabs( micada_org_admin_page_slug( $section ), micada_org_table( $section ), $view ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php echo $is_orders ? esc_html__( 'Document', 'micada' ) : esc_html__( 'Officer Name', 'micada' ); ?></th><?php if ( ! $is_orders ) : ?><th><?php esc_html_e( 'Designation', 'micada' ); ?></th><th><?php esc_html_e( 'Landline', 'micada' ); ?></th><?php if ( 'field-executives' === $section ) : ?><th><?php esc_html_e( 'Mobile', 'micada' ); ?></th><?php endif; ?><th><?php esc_html_e( 'Email', 'micada' ); ?></th><?php else : ?><th><?php esc_html_e( 'Document URL', 'micada' ); ?></th><?php endif; ?><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php foreach ( $rows as $index => $row ) : ?><tr>
			<td><?php echo esc_html( $row->sort_order ); ?></td><td><?php echo esc_html( $row->{$name_column} ); ?></td>
			<?php if ( ! $is_orders ) : ?><td><?php echo esc_html( $row->designation ); ?></td><td><?php echo esc_html( $row->landline ); ?></td><?php if ( 'field-executives' === $section ) : ?><td><?php echo esc_html( $row->mobile ); ?></td><?php endif; ?><td><?php echo esc_html( $row->email ); ?></td><?php else : ?><td><a href="<?php echo esc_url( $row->url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $row->url ); ?></a></td><?php endif; ?>
			<td><?php echo 'trash' === $row->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><button type="button" class="button-link micada-org-edit" data-id="<?php echo esc_attr( $row->id ); ?>" data-name="<?php echo esc_attr( $row->{$name_column} ); ?>" data-designation="<?php echo esc_attr( $row->designation ); ?>" data-landline="<?php echo esc_attr( $row->landline ); ?>" data-mobile="<?php echo esc_attr( $row->mobile ); ?>" data-email="<?php echo esc_attr( $row->email ); ?>" data-url="<?php echo esc_attr( $row->url ); ?>" data-order="<?php echo esc_attr( $row->sort_order ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <?php micada_cms_trash_action_links( 'office-orders' === $section ? 'org-orders' : ( 'field-executives' === $section ? 'org-field' : 'org-head' ), $row->id, $row->display_status ); ?></td>
		</tr><?php endforeach; ?>
		<?php if ( ! $rows ) : ?><tr><td colspan="8"><?php esc_html_e( 'No entries have been added.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div id="micada-org-modal" class="micada-org-modal" aria-hidden="true"><div class="micada-org-backdrop" data-org-close></div><section class="micada-org-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-org-title">
			<header><h2 id="micada-org-title"><?php esc_html_e( 'Add Entry', 'micada' ); ?></h2><button type="button" class="micada-org-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-org-close>&times;</button></header>
			<form id="micada-org-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="micada_org_save"><input type="hidden" name="list_type" value="<?php echo esc_attr( $section ); ?>"><input type="hidden" name="id" value="0"><?php wp_nonce_field( 'micada_org_save_' . $section ); ?>
				<p><label><strong><?php echo $is_orders ? esc_html__( 'Document title', 'micada' ) : esc_html__( 'Officer name', 'micada' ); ?></strong><input class="widefat" required name="<?php echo esc_attr( $name_column ); ?>"></label></p>
				<?php if ( ! $is_orders ) : ?><p><label><strong><?php esc_html_e( 'Designation', 'micada' ); ?></strong><textarea class="widefat" rows="2" name="designation" required></textarea></label></p><p><label><strong><?php esc_html_e( 'Landline', 'micada' ); ?></strong><input class="widefat" name="landline"></label></p><?php if ( 'field-executives' === $section ) : ?><p><label><strong><?php esc_html_e( 'Mobile', 'micada' ); ?></strong><input class="widefat" name="mobile"></label></p><?php endif; ?><p><label><strong><?php esc_html_e( 'Email', 'micada' ); ?></strong><input class="widefat" type="email" name="email"></label></p><?php else : ?><p><label><strong><?php esc_html_e( 'Document URL', 'micada' ); ?></strong><input class="widefat" type="url" required name="url"></label></p><?php endif; ?>
				<p><label><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong><input class="small-text" type="number" min="0" step="1" required name="sort_order" value="<?php echo esc_attr( $next_order ); ?>"></label></p>
				<div class="micada-org-actions"><button type="submit" class="button button-primary" id="micada-org-submit"><?php esc_html_e( 'Add Entry', 'micada' ); ?></button><button type="button" class="button" data-org-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
			</form>
		</section></div>
	</div>
	<style>
		.micada-org-modal{display:none;position:fixed;inset:32px 0 0 160px;z-index:100100;align-items:center;justify-content:center;padding:24px}.micada-org-modal.is-open{display:flex}.micada-org-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}
		.micada-org-dialog{position:relative;width:min(680px,100%);max-height:calc(100vh - 96px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}.micada-org-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.micada-org-dialog header h2{margin:0;font-size:21px}.micada-org-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}.micada-org-dialog form>p{margin:0 0 16px}.micada-org-dialog form>p label strong{display:block;margin-bottom:6px}.micada-org-actions{display:flex;gap:8px;margin-top:22px}body.micada-org-modal-open{overflow:hidden}
		@media(max-width:782px){.micada-org-modal{inset:46px 0 0;padding:12px}.micada-org-dialog{max-height:calc(100vh - 70px);padding:18px}}
	</style>
	<script>(function(){const modal=document.getElementById('micada-org-modal'),form=document.getElementById('micada-org-form'),title=document.getElementById('micada-org-title'),submit=document.getElementById('micada-org-submit'),nameField='<?php echo esc_js( $name_column ); ?>';const open=function(){modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-org-modal-open');form.elements[nameField].focus();};const close=function(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-org-modal-open');};document.getElementById('micada-org-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.sort_order.value=this.dataset.nextOrder;title.textContent='<?php echo esc_js( __( 'Add Entry', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Add Entry', 'micada' ) ); ?>';open();});document.querySelectorAll('.micada-org-edit').forEach(function(button){button.addEventListener('click',function(){form.elements[nameField].value=button.dataset.name||'';['designation','landline','mobile','email','url'].forEach(function(field){if(form.elements[field])form.elements[field].value=button.dataset[field]||'';});form.elements.sort_order.value=button.dataset.order||'';form.elements.id.value=button.dataset.id;title.textContent='<?php echo esc_js( __( 'Edit Entry', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Save Changes', 'micada' ) ); ?>';open();});});modal.querySelectorAll('[data-org-close]').forEach(function(button){button.addEventListener('click',close);});document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});})();</script>
	<?php
}
}


if ( ! function_exists( 'micada_org_get_rows' ) ) {
function micada_org_get_rows( $section ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . micada_org_table( $section ) . ' WHERE display_status = %s ORDER BY sort_order ASC, id ASC', 'post' ) );
}
}


if ( ! function_exists( 'micada_org_render_table' ) ) {
function micada_org_render_table( $section ) {
	$rows = micada_org_get_rows( $section );
	$field = 'field-executives' === $section;
	$name_column = micada_org_name_column( $section );
	$headers = $field ? array( 'Sr. No.', 'Officer Name', 'Designation', 'Contact No. (Landline)', 'Contact No. (Mobile)', 'Email Address' ) : array( 'Sr. No.', 'Officer Name', 'Designation', 'Contact No. (Landline)', 'Email Address' );
	$html = '<figure class="wp-block-table"><table><thead><tr>';
	foreach ( $headers as $header ) {
		$html .= '<th>' . esc_html( $header ) . '</th>';
	}
	$html .= '</tr></thead><tbody>';
	foreach ( $rows as $index => $row ) {
		$html .= '<tr><td>' . esc_html( $index + 1 ) . '</td><td>' . esc_html( $row->{$name_column} ) . '</td><td>' . esc_html( $row->designation ) . '</td><td>' . esc_html( $row->landline ) . '</td>';
		if ( $field ) {
			$html .= '<td>' . esc_html( $row->mobile ) . '</td>';
		}
		$html .= '<td>' . ( $row->email ? '<a href="mailto:' . esc_attr( $row->email ) . '">' . esc_html( $row->email ) . '</a>' : '' ) . '</td></tr>';
	}
	return $html . '</tbody></table></figure>';
}
}


if ( ! function_exists( 'micada_org_render_orders' ) ) {
function micada_org_render_orders() {
	$html = '';
	foreach ( micada_org_get_rows( 'office-orders' ) as $row ) {
		$html .= '<p><a href="' . esc_url( $row->url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $row->title ) . '</a></p>';
	}
	return $html;
}
}


if ( ! function_exists( 'micada_org_replace_page_content' ) ) {
function micada_org_replace_page_content( $section, $content ) {
	if ( ! isset( micada_org_sections()[ $section ] ) ) {
		return $content;
	}
	if ( 'office-orders' === $section ) {
		$replacement = micada_org_render_orders();
		if ( preg_match( '~<p\b[^>]*>\s*<a\b.*?</a>\s*</p>~is', $content ) ) {
			return preg_replace( '~<p\b[^>]*>\s*<a\b.*?</a>\s*</p>~is', $replacement, $content, 1 );
		}
		return $content . $replacement;
	}
	$replacement = micada_org_render_table( $section );
	if ( preg_match( '~<figure\b[^>]*>\s*<table\b.*?</table>\s*</figure>~is', $content ) ) {
		return preg_replace( '~<figure\b[^>]*>\s*<table\b.*?</table>\s*</figure>~is', $replacement, $content, 1 );
	}
	return $content . $replacement;
}
}

