<?php
/**
 * Module: RTI
 * Description: Manages State Public Information Officers list.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_rti_table' ) ) {
function micada_rti_table() {
	global $wpdb;
	return $wpdb->prefix . 'state_public_information_officers';
}
}


if ( ! function_exists( 'micada_rti_install' ) ) {
function micada_rti_install() {
	global $wpdb;
	$table = micada_rti_table();
	$legacy_table = $wpdb->prefix . 'rti_officers';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	$legacy_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ) === $legacy_table;
	if ( ! $exists && $legacy_exists ) {
		$renamed = $wpdb->query( "RENAME TABLE `{$legacy_table}` TO `{$table}`" );
		if ( false !== $renamed ) {
			$exists = true;
			$legacy_exists = false;
		}
	}
	if ( ! $exists || '2' !== get_option( 'micada_rti_schema' ) ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			office_name varchar(255) NOT NULL,
			designation varchar(255) NOT NULL,
			telephone varchar(100) NOT NULL,
			sort_order int(11) NOT NULL DEFAULT 0,
			display_status varchar(10) NOT NULL DEFAULT 'post',
			PRIMARY KEY  (id),
			KEY sort_order (sort_order),
			KEY list_status (display_status)
		) {$charset_collate};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'micada_rti_schema', '2', false );
	}
	if ( $legacy_exists && '1' !== get_option( 'micada_rti_table_migrated' ) ) {
		$migrated = $wpdb->query( "INSERT IGNORE INTO `{$table}` (id, office_name, designation, telephone, sort_order) SELECT id, office_name, designation, telephone, sort_order FROM `{$legacy_table}`" );
		if ( false !== $migrated ) {
			update_option( 'micada_rti_table_migrated', '1', false );
		}
	}
	if ( get_option( 'micada_rti_seeded' ) ) {
		return;
	}
	$existing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
	if ( 0 === $existing ) {
		$defaults = array(
			array( 'Head Office, MICADA, Haryana', 'Officer on Special Duty', '0172 2567034' ),
			array( 'MICAD Circle Rohtak', 'Superintending Engineer (SPIO)', '01262-256006' ),
			array( 'MICAD Circle Karnal', 'Superintending Engineer (SPIO)', '0184-2271054' ),
			array( 'MICAD Circle Hisar', 'Superintending Engineer (SPIO)', '01662-227369' ),
			array( 'MICAD Division Rohtak', 'Executive Engineer(SPIO)', '01262-254552' ),
			array( 'MICAD Division Jhajjar', 'Executive Engineer(SPIO)', '01251-257076' ),
			array( 'MICAD Division Jind', 'Executive Engineer(SPIO)', '01681-227072' ),
			array( 'MICAD Division Rewari', 'Executive Engineer(SPIO)', '01274-260404' ),
			array( 'MICAD Division Kaithal', 'Executive Engineer(SPIO)', '01746-224337' ),
			array( 'MICAD Division Karnal', 'Executive Engineer(SPIO)', '0184-2271054' ),
			array( 'MICAD Division Kurukshetra', 'Executive Engineer(SPIO)', '01744-238740' ),
			array( 'MICAD Division Panipat', 'Executive Engineer(SPIO)', '0180-2650805' ),
			array( 'MICAD Division Hisar', 'Executive Engineer(SPIO)', '01662-235425' ),
			array( 'MICAD Division Bhiwani', 'Executive Engineer(SPIO)', '01664-244273' ),
			array( 'MICAD Division Fatehabad', 'Executive Engineer(SPIO)', '01667-227125' ),
			array( 'MICAD Division Sirsa', 'Executive Engineer(SPIO)', '01666-244129' ),
		);
		foreach ( $defaults as $index => $row ) {
			$wpdb->insert(
				$table,
				array( 'office_name' => $row[0], 'designation' => $row[1], 'telephone' => $row[2], 'sort_order' => $index + 1, 'display_status' => 'post' ),
				array( '%s', '%s', '%s', '%d', '%s' )
			);
		}
	}
	update_option( 'micada_rti_seeded', 1, false );
}
}

add_action( 'init', 'micada_rti_install', 1 );

if ( ! function_exists( 'micada_rti_admin_menu' ) ) {
function micada_rti_admin_menu() {
	add_menu_page( __( 'RTI', 'micada' ), __( 'RTI', 'micada' ), 'manage_options', 'micada-rti', 'micada_rti_admin_page', 'dashicons-media-text', 27 );
	add_submenu_page( 'micada-rti', __( 'State Public Information Officers', 'micada' ), __( 'State Public Information Officers', 'micada' ), 'manage_options', 'micada-rti-list', 'micada_rti_admin_page' );
	remove_submenu_page( 'micada-rti', 'micada-rti' );
}
}

add_action( 'admin_menu', 'micada_rti_admin_menu' );

if ( ! function_exists( 'micada_rti_save' ) ) {
function micada_rti_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the RTI list.', 'micada' ) );
	}
	check_admin_referer( 'micada_rti_save' );
	global $wpdb;
	$id = absint( $_POST['id'] ?? 0 );
	$order_raw = trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ?? '' ) ) );
	$data = array(
		'office_name' => sanitize_text_field( wp_unslash( $_POST['office_name'] ?? '' ) ),
		'designation' => sanitize_text_field( wp_unslash( $_POST['designation'] ?? '' ) ),
		'telephone' => sanitize_text_field( wp_unslash( $_POST['telephone'] ?? '' ) ),
		'sort_order' => 1 === preg_match( '/^\d+$/', $order_raw ) ? (int) $order_raw : -1,
	);
	if ( '' === $data['office_name'] || '' === $data['designation'] || '' === $data['telephone'] || $data['sort_order'] < 0 ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'micada-rti-list', 'message' => 'invalid', 'edit' => $id ), admin_url( 'admin.php' ) ) );
		exit;
	}
	if ( $id ) {
		$saved = false !== $wpdb->update( micada_rti_table(), $data, array( 'id' => $id ), array( '%s', '%s', '%s', '%d' ), array( '%d' ) );
	} else {
		$data['display_status'] = 'post';
		$saved = false !== $wpdb->insert( micada_rti_table(), $data, array( '%s', '%s', '%s', '%d', '%s' ) );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-rti-list', 'message' => $saved ? 'saved' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_rti_save', 'micada_rti_save' );

if ( ! function_exists( 'micada_rti_set_status' ) ) {
function micada_rti_set_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the RTI list.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	$status = sanitize_key( wp_unslash( $_GET['status'] ?? '' ) );
	if ( ! in_array( $status, array( 'post', 'trash' ), true ) ) {
		wp_die( esc_html__( 'Invalid RTI list status.', 'micada' ) );
	}
	check_admin_referer( 'micada_rti_status_' . $id . '_' . $status );
	global $wpdb;
	$updated = false !== $wpdb->update( micada_rti_table(), array( 'display_status' => $status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-rti-list', 'message' => $updated ? ( 'trash' === $status ? 'trashed' : 'restored' ) : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_rti_set_status', 'micada_rti_set_status' );

if ( ! function_exists( 'micada_rti_delete' ) ) {
function micada_rti_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the RTI list.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	check_admin_referer( 'micada_rti_delete_' . $id );
	global $wpdb;
	$deleted = false !== $wpdb->delete( micada_rti_table(), array( 'id' => $id ), array( '%d' ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-rti-list', 'message' => $deleted ? 'deleted' : 'error' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_rti_delete', 'micada_rti_delete' );

if ( ! function_exists( 'micada_rti_rows' ) ) {
function micada_rti_rows( $direction = 'DESC', $status = '' ) {
	global $wpdb;
	$order = 'ASC' === strtoupper( $direction ) ? 'ASC' : 'DESC';
	$where = in_array( $status, array( 'post', 'trash' ), true ) ? $wpdb->prepare( ' WHERE display_status = %s', $status ) : '';
	return $wpdb->get_results( 'SELECT * FROM ' . micada_rti_table() . $where . " ORDER BY sort_order {$order}, id {$order}" );
}
}


if ( ! function_exists( 'micada_rti_admin_page' ) ) {
function micada_rti_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$rows = micada_rti_rows( 'DESC', $view );
	$next_order = 1;
	foreach ( $rows as $row ) {
		$next_order = max( $next_order, (int) $row->sort_order + 1 );
	}
	$message = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	$messages = array(
		'saved' => array( 'success', __( 'RTI list entry saved.', 'micada' ) ),
		'deleted' => array( 'success', __( 'RTI list entry deleted.', 'micada' ) ),
		'trashed' => array( 'success', __( 'RTI list entry moved to Trash.', 'micada' ) ),
		'restored' => array( 'success', __( 'RTI list entry restored.', 'micada' ) ),
		'invalid' => array( 'error', __( 'Complete all fields and enter a non-negative whole-number order.', 'micada' ) ),
		'error' => array( 'error', __( 'The database could not save the RTI list entry.', 'micada' ) ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'State Public Information Officers', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-rti-add" data-next-order="<?php echo esc_attr( $next_order ); ?>"><?php esc_html_e( 'Add Entry', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'Manage the State Public Information Officers list displayed under the RTI menu. Entries are stored in the dedicated state_public_information_officers database table and shown in descending order. Trashed entries remain stored but are hidden from the public list.', 'micada' ); ?></p>
		<?php if ( isset( $messages[ $message ] ) ) : ?><div class="notice notice-<?php echo esc_attr( $messages[ $message ][0] ); ?> is-dismissible"><p><?php echo esc_html( $messages[ $message ][1] ); ?></p></div><?php endif; ?>
		<?php micada_cms_status_tabs( 'micada-rti-list', micada_rti_table(), $view ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php esc_html_e( 'Name of Office', 'micada' ); ?></th><th><?php esc_html_e( 'Designation of SPIO', 'micada' ); ?></th><th><?php esc_html_e( 'Telephone No.', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php foreach ( $rows as $index => $row ) : ?><tr>
			<td><?php echo esc_html( $row->sort_order ); ?></td><td><?php echo esc_html( $row->office_name ); ?></td><td><?php echo esc_html( $row->designation ); ?></td><td><?php echo esc_html( $row->telephone ); ?></td><td><?php echo 'trash' === $row->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td>
			<td><button type="button" class="button-link micada-rti-edit" data-id="<?php echo esc_attr( $row->id ); ?>" data-office="<?php echo esc_attr( $row->office_name ); ?>" data-designation="<?php echo esc_attr( $row->designation ); ?>" data-telephone="<?php echo esc_attr( $row->telephone ); ?>" data-order="<?php echo esc_attr( $row->sort_order ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <?php micada_cms_trash_action_links( 'rti', $row->id, $row->display_status ); ?></td>
		</tr><?php endforeach; ?>
		<?php if ( ! $rows ) : ?><tr><td colspan="7"><?php esc_html_e( 'No RTI entries have been added.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div id="micada-rti-modal" class="micada-rti-modal" aria-hidden="true">
			<div class="micada-rti-backdrop" data-rti-close></div>
			<section class="micada-rti-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-rti-modal-title">
				<header><h2 id="micada-rti-modal-title"><?php esc_html_e( 'Add RTI Entry', 'micada' ); ?></h2><button type="button" class="micada-rti-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-rti-close>&times;</button></header>
				<form id="micada-rti-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="micada_rti_save"><input type="hidden" name="id" value="0"><?php wp_nonce_field( 'micada_rti_save' ); ?>
					<p><label><strong><?php esc_html_e( 'Name of Office', 'micada' ); ?></strong><input class="widefat" required name="office_name"></label></p>
					<p><label><strong><?php esc_html_e( 'Designation of SPIO', 'micada' ); ?></strong><input class="widefat" required name="designation"></label></p>
					<p><label><strong><?php esc_html_e( 'Telephone No.', 'micada' ); ?></strong><input class="widefat" required name="telephone"></label></p>
					<p><label><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong><input type="number" min="0" step="1" required name="sort_order" value="<?php echo esc_attr( $next_order ); ?>"></label></p>
					<div class="micada-rti-actions"><button type="submit" class="button button-primary" id="micada-rti-submit"><?php esc_html_e( 'Add Entry', 'micada' ); ?></button><button type="button" class="button" data-rti-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
	</div>
	<style>
		.micada-rti-modal{display:none;position:fixed;inset:32px 0 0 160px;z-index:100100;align-items:center;justify-content:center;padding:24px}
		.micada-rti-modal.is-open{display:flex}.micada-rti-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}
		.micada-rti-dialog{position:relative;width:min(640px,100%);max-height:calc(100vh - 96px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}
		.micada-rti-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.micada-rti-dialog header h2{margin:0;font-size:21px}
		.micada-rti-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}.micada-rti-dialog form>p{margin:0 0 16px}.micada-rti-dialog form>p label strong{display:block;margin-bottom:6px}
		.micada-rti-actions{display:flex;gap:8px;margin-top:22px}body.micada-rti-modal-open{overflow:hidden}
		@media(max-width:782px){.micada-rti-modal{inset:46px 0 0;padding:12px}.micada-rti-dialog{max-height:calc(100vh - 70px);padding:18px}}
	</style>
	<script>(function(){const modal=document.getElementById('micada-rti-modal'),form=document.getElementById('micada-rti-form'),title=document.getElementById('micada-rti-modal-title'),submit=document.getElementById('micada-rti-submit');const open=function(){modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-rti-modal-open');form.elements.office_name.focus();};const close=function(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-rti-modal-open');};document.getElementById('micada-rti-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.sort_order.value=this.dataset.nextOrder;title.textContent='<?php echo esc_js( __( 'Add RTI Entry', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Add Entry', 'micada' ) ); ?>';open();});document.querySelectorAll('.micada-rti-edit').forEach(function(button){button.addEventListener('click',function(){form.elements.id.value=button.dataset.id;form.elements.office_name.value=button.dataset.office;form.elements.designation.value=button.dataset.designation;form.elements.telephone.value=button.dataset.telephone;form.elements.sort_order.value=button.dataset.order;title.textContent='<?php echo esc_js( __( 'Edit RTI Entry', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Save Changes', 'micada' ) ); ?>';open();});});modal.querySelectorAll('[data-rti-close]').forEach(function(button){button.addEventListener('click',close);});document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});})();</script>
	<?php
}
}


if ( ! function_exists( 'micada_rti_render_table' ) ) {
function micada_rti_render_table() {
	$rows = micada_rti_rows( 'ASC', 'post' );
	$output = '<figure class="wp-block-table micada-rti-table-wrap"><table class="micada-rti-public-table"><thead><tr><th>' . esc_html__( 'Sr.No.', 'micada' ) . '</th><th>' . esc_html__( 'Name of Office', 'micada' ) . '</th><th>' . esc_html__( 'Designation of SPIO', 'micada' ) . '</th><th>' . esc_html__( 'Telephone No.', 'micada' ) . '</th></tr></thead><tbody>';
	foreach ( $rows as $index => $row ) {
		$output .= '<tr><td>' . esc_html( $index + 1 ) . '</td><td>' . esc_html( $row->office_name ) . '</td><td>' . esc_html( $row->designation ) . '</td><td>' . esc_html( $row->telephone ) . '</td></tr>';
	}
	return $output . '</tbody></table></figure>';
}
}


if ( ! function_exists( 'micada_rti_replace_page_list' ) ) {
function micada_rti_replace_page_list( $content ) {
	$content = preg_replace( '~<h2\b[^>]*>\s*1\.\s*State Public Information\s*Officers:?\s*</h2>~is', '<h2 class="micada-rti-heading"><strong>1.</strong> State Public Information Officers</h2>', $content, 1 );
	return preg_replace( '~<figure\b[^>]*>\s*<table\b.*?</table>\s*</figure>~is', micada_rti_render_table(), $content, 1 );
}
}

