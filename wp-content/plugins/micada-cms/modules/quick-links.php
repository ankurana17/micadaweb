<?php
/**
 * Module: Quick Links
 * Description: Stores and manages homepage quick links in custom DB table.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_quick_links_table' ) ) {
function micada_quick_links_table() {
	global $wpdb;
	return $wpdb->prefix . 'quick_links';
}
}


if ( ! function_exists( 'micada_quick_links_install' ) ) {
function micada_quick_links_install() {
	global $wpdb;
	$table = micada_quick_links_table();
	$legacy_table = $wpdb->prefix . 'micada_quick_links';
	$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	$legacy_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ) === $legacy_table;
	if ( $legacy_exists && ! $table_exists ) {
		$wpdb->query( "RENAME TABLE `{$legacy_table}` TO `{$table}`" );
		$table_exists = true;
		$legacy_exists = false;
	}
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		label_en text NOT NULL,
		label_hi text NOT NULL,
		url text NOT NULL,
		new_tab tinyint(1) NOT NULL DEFAULT 0,
		show_new tinyint(1) NOT NULL DEFAULT 0,
		is_notice tinyint(1) NOT NULL DEFAULT 0,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY list_status (display_status)
	) {$charset_collate};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	if ( $legacy_exists ) {
		micada_quick_links_migrate_legacy_table( $legacy_table, $table );
	}
	micada_quick_links_upgrade_existing_rows( $table );
}
}

add_action( 'init', 'micada_quick_links_install', 1 );

if ( ! function_exists( 'micada_quick_links_migrate_legacy_table' ) ) {
function micada_quick_links_migrate_legacy_table( $legacy_table, $table ) {
	global $wpdb;
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ) !== $legacy_table ) {
		return;
	}
	$legacy_rows = $wpdb->get_results( "SELECT * FROM `{$legacy_table}`", ARRAY_A );
	if ( empty( $legacy_rows ) ) {
		return;
	}
	foreach ( $legacy_rows as $row ) {
		$label_en = $row['label_en'] ?? $row['title_en'] ?? '';
		$label_hi = $row['label_hi'] ?? $row['title_hd'] ?? '';
		$url = $row['url'] ?? '';
		if ( '' === $label_en || '' === $url ) {
			continue;
		}
		$new_tab = isset( $row['new_tab'] ) ? (int) $row['new_tab'] : ( ( '_blank' === ( $row['target'] ?? '' ) ) ? 1 : 0 );
		$show_new = isset( $row['show_new'] ) ? (int) $row['show_new'] : (int) ( $row['is_new'] ?? 0 );
		$is_notice = (int) ( $row['is_notice'] ?? 0 );
		$sort_order = isset( $row['sort_order'] ) ? (int) $row['sort_order'] : (int) ( $row['display_order'] ?? 0 );
		$display_status = isset( $row['display_status'] ) && in_array( $row['display_status'], array( 'post', 'trash' ), true )
			? $row['display_status']
			: ( ( 'trash' === ( $row['status'] ?? '' ) ) ? 'trash' : 'post' );
		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE url = %s AND (label_en = %s OR label_en = '') LIMIT 1", $url, $label_en ) );
		if ( ! $existing_id ) {
			$wpdb->insert( $table, array(
				'label_en' => $label_en,
				'label_hi' => $label_hi,
				'url' => $url,
				'new_tab' => $new_tab,
				'show_new' => $show_new,
				'is_notice' => $is_notice,
				'sort_order' => $sort_order,
				'display_status' => $display_status,
			) );
		}
	}
}
}


if ( ! function_exists( 'micada_quick_links_upgrade_existing_rows' ) ) {
function micada_quick_links_upgrade_existing_rows( $table = '' ) {
	global $wpdb;
	if ( empty( $table ) ) {
		$table = micada_quick_links_table();
	}
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return;
	}
	$columns = $wpdb->get_col( "DESCRIBE `{$table}`", 0 );
	if ( empty( $columns ) ) {
		return;
	}
	$col_set = array_flip( $columns );

	if ( isset( $col_set['title_en'] ) && isset( $col_set['label_en'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET label_en = title_en WHERE (label_en IS NULL OR label_en = '') AND title_en IS NOT NULL AND title_en != ''" );
	}
	if ( isset( $col_set['title_hd'] ) && isset( $col_set['label_hi'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET label_hi = title_hd WHERE (label_hi IS NULL OR label_hi = '') AND title_hd IS NOT NULL AND title_hd != ''" );
	}
	if ( isset( $col_set['target'] ) && isset( $col_set['new_tab'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET new_tab = 1 WHERE target = '_blank'" );
	}
	if ( isset( $col_set['is_new'] ) && isset( $col_set['show_new'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET show_new = is_new WHERE is_new IS NOT NULL" );
	}
	if ( isset( $col_set['display_order'] ) && isset( $col_set['sort_order'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET sort_order = display_order WHERE display_order IS NOT NULL AND (sort_order = 0 OR sort_order IS NULL)" );
	}
	if ( isset( $col_set['status'] ) && isset( $col_set['display_status'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET display_status = CASE WHEN status = 'trash' THEN 'trash' ELSE 'post' END WHERE display_status IS NULL OR display_status = '' OR display_status = 'active'" );
	} elseif ( isset( $col_set['display_status'] ) ) {
		$wpdb->query( "UPDATE `{$table}` SET display_status = 'post' WHERE display_status IS NULL OR display_status = '' OR display_status = 'active'" );
	}
}
}


if ( ! function_exists( 'micada_quick_links_admin_menu' ) ) {
function micada_quick_links_admin_menu() {
	add_menu_page( __( 'Quick Links', 'micada' ), __( 'Quick Links', 'micada' ), 'manage_options', 'micada-quick-links', 'micada_quick_links_admin_page', 'dashicons-admin-links', 22 );
}
}

add_action( 'admin_menu', 'micada_quick_links_admin_menu' );

if ( ! function_exists( 'micada_quick_links_save' ) ) {
function micada_quick_links_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage Quick Links.', 'micada' ) );
	}
	check_admin_referer( 'micada_quick_links_save' );
	global $wpdb;
	$table = micada_quick_links_table();
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$order_raw = isset( $_POST['sort_order'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ) ) ) : '0';
	$order_is_valid = 1 === preg_match( '/^\d+$/', $order_raw );
	$data = array(
		'label_en' => sanitize_text_field( wp_unslash( $_POST['label_en'] ?? '' ) ),
		'label_hi' => sanitize_text_field( wp_unslash( $_POST['label_hi'] ?? '' ) ),
		'url' => esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ),
		'new_tab' => isset( $_POST['new_tab'] ) ? 1 : 0,
		'show_new' => isset( $_POST['show_new'] ) ? 1 : 0,
		'is_notice' => 0,
		'sort_order' => $order_is_valid ? (int) $order_raw : 0,
	);
	if ( '' === $data['label_en'] || '' === $data['url'] || ! $order_is_valid ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'micada-quick-links', 'message' => 'invalid' ), admin_url( 'admin.php' ) ) );
		exit;
	}
	if ( $id ) {
		$wpdb->update( $table, $data, array( 'id' => $id ), array( '%s', '%s', '%s', '%d', '%d', '%d', '%d' ), array( '%d' ) );
	} else {
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$table} (label_en, label_hi, url, new_tab, show_new, is_notice, sort_order) VALUES (%s, %s, %s, %d, %d, %d, %d)",
			$data['label_en'], $data['label_hi'], $data['url'], $data['new_tab'], $data['show_new'], $data['is_notice'], $data['sort_order']
		) );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-quick-links', 'message' => 'saved' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_quick_links_save', 'micada_quick_links_save' );

if ( ! function_exists( 'micada_quick_links_delete' ) ) {
function micada_quick_links_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage Quick Links.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	check_admin_referer( 'micada_quick_links_delete_' . $id );
	global $wpdb;
	$wpdb->delete( micada_quick_links_table(), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-quick-links', 'message' => 'deleted' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_quick_links_delete', 'micada_quick_links_delete' );

if ( ! function_exists( 'micada_quick_links_admin_page' ) ) {
function micada_quick_links_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = micada_quick_links_table();
	$next_order = 1 + (int) $wpdb->get_var( "SELECT COALESCE(MAX(sort_order), 0) FROM {$table}" );
	$edit_id = absint( $_GET['edit'] ?? 0 );
	$item = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ), ARRAY_A ) : null;
	$defaults = array( 'id' => 0, 'label_en' => '', 'label_hi' => '', 'url' => '', 'new_tab' => 0, 'show_new' => 0, 'is_notice' => 0, 'sort_order' => $next_order );
	$item = $item ? $item : $defaults;
	if ( isset( $_GET['message'] ) ) {
		$messages = array( 'saved' => __( 'Quick Link saved.', 'micada' ), 'deleted' => __( 'Quick Link deleted.', 'micada' ), 'invalid' => __( 'Enter an English label and a URL, and use a non-negative whole number for display order.', 'micada' ) );
		$key = sanitize_key( wp_unslash( $_GET['message'] ) );
		if ( isset( $messages[ $key ] ) ) {
			echo '<div class="notice ' . ( 'invalid' === $key ? 'notice-error' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( $messages[ $key ] ) . '</p></div>';
		}
	}
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$links = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE display_status = %s ORDER BY sort_order DESC, id DESC", $view ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick Links', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-quick-link-add"><?php esc_html_e( 'Add New Link', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'These entries are stored in the WordPress database and displayed in this order on the homepage.', 'micada' ); ?></p>
		<hr><h2><?php esc_html_e( 'Homepage Quick Links', 'micada' ); ?></h2>
		<?php micada_cms_status_tabs( 'micada-quick-links', $table, $view ); ?>
		<table id="micada-quick-links-table" class="widefat striped"><thead><tr><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php esc_html_e( 'English label', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi label', 'micada' ); ?></th><th><?php esc_html_e( 'URL', 'micada' ); ?></th><th><?php esc_html_e( 'Options', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php if ( $links ) : foreach ( $links as $link ) : ?>
			<tr><td><?php echo esc_html( $link->sort_order ); ?></td><td><?php echo esc_html( $link->label_en ); ?></td><td><?php echo esc_html( $link->label_hi ); ?></td><td><a href="<?php echo esc_url( $link->url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link->url ); ?></a></td><td><?php echo esc_html( implode( ', ', array_filter( array( $link->new_tab ? __( 'New tab', 'micada' ) : '', $link->show_new ? __( 'New badge', 'micada' ) : '' ) ) ) ); ?></td><td><?php echo 'trash' === $link->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><button type="button" class="button-link micada-quick-link-edit" data-id="<?php echo esc_attr( $link->id ); ?>" data-label-en="<?php echo esc_attr( $link->label_en ); ?>" data-label-hi="<?php echo esc_attr( $link->label_hi ); ?>" data-url="<?php echo esc_attr( $link->url ); ?>" data-sort-order="<?php echo esc_attr( $link->sort_order ); ?>" data-new-tab="<?php echo esc_attr( $link->new_tab ); ?>" data-show-new="<?php echo esc_attr( $link->show_new ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <?php micada_cms_trash_action_links( 'quick-links', $link->id, $link->display_status ); ?></td></tr>
		<?php endforeach; else : ?><tr><td colspan="6"><?php esc_html_e( 'No Quick Links found.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div id="micada-quick-link-modal" class="micada-quick-link-modal<?php echo $edit_id ? ' is-open' : ''; ?>" aria-hidden="<?php echo $edit_id ? 'false' : 'true'; ?>">
			<div class="micada-quick-link-backdrop" data-quick-link-close></div>
			<section class="micada-quick-link-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-quick-link-modal-title">
				<header><h2 id="micada-quick-link-modal-title"><?php echo $edit_id ? esc_html__( 'Edit Quick Link', 'micada' ) : esc_html__( 'Add New Link', 'micada' ); ?></h2><button type="button" class="micada-quick-link-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-quick-link-close>&times;</button></header>
				<form id="micada-quick-link-form" method="post" autocomplete="off" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="micada_quick_links_save"><input type="hidden" id="micada-quick-link-id" name="id" value="<?php echo esc_attr( $item['id'] ); ?>">
					<?php wp_nonce_field( 'micada_quick_links_save' ); ?>
					<p><label for="micada-quick-link-label-en"><strong><?php esc_html_e( 'English label', 'micada' ); ?></strong></label><input class="widefat" required id="micada-quick-link-label-en" name="label_en" value="<?php echo esc_attr( $item['label_en'] ); ?>"></p>
					<p><label for="micada-quick-link-label-hi"><strong><?php esc_html_e( 'Hindi label', 'micada' ); ?></strong></label><input class="widefat" id="micada-quick-link-label-hi" name="label_hi" value="<?php echo esc_attr( $item['label_hi'] ); ?>"></p>
					<p><label for="micada-quick-link-url"><strong><?php esc_html_e( 'URL', 'micada' ); ?></strong></label><input class="widefat" type="url" required id="micada-quick-link-url" name="url" value="<?php echo esc_attr( $item['url'] ); ?>"></p>
					<p><label for="micada-quick-link-order"><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong></label><input type="number" min="0" step="1" required id="micada-quick-link-order" name="sort_order" value="<?php echo esc_attr( $item['sort_order'] ); ?>" autocomplete="off"></p>
					<p><label><input type="checkbox" id="micada-quick-link-new-tab" name="new_tab" value="1" <?php checked( $item['new_tab'], 1 ); ?>> <?php esc_html_e( 'Open in a new tab', 'micada' ); ?></label><br><label><input type="checkbox" id="micada-quick-link-show-new" name="show_new" value="1" <?php checked( $item['show_new'], 1 ); ?>> <?php esc_html_e( 'Show “New” badge', 'micada' ); ?></label></p>
					<div class="micada-quick-link-modal-actions"><button type="submit" class="button button-primary" id="micada-quick-link-submit"><?php echo $edit_id ? esc_html__( 'Update Quick Link', 'micada' ) : esc_html__( 'Add Quick Link', 'micada' ); ?></button><button type="button" class="button" data-quick-link-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
	</div>
	<style>
		#micada-quick-links-table th:last-child,#micada-quick-links-table td:last-child{width:130px;min-width:130px;white-space:nowrap}
		.micada-quick-link-modal{display:none;position:fixed;inset:0;z-index:100100;align-items:center;justify-content:center;padding:24px}
		.micada-quick-link-modal.is-open{display:flex}
		.micada-quick-link-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}
		.micada-quick-link-dialog{position:relative;width:min(620px,100%);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}
		.micada-quick-link-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
		.micada-quick-link-dialog header h2{margin:0;font-size:21px}
		.micada-quick-link-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}
		.micada-quick-link-dialog form>p{margin:0 0 16px}
		.micada-quick-link-dialog form>p>label{display:block;margin-bottom:6px}
		.micada-quick-link-modal-actions{display:flex;gap:8px;margin-top:22px}
		body.micada-quick-link-modal-open{overflow:hidden}
	</style>
	<script>
	(function(){
		const modal=document.getElementById('micada-quick-link-modal');
		const form=document.getElementById('micada-quick-link-form');
		const title=document.getElementById('micada-quick-link-modal-title');
		const submit=document.getElementById('micada-quick-link-submit');
		const open=()=>{modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-quick-link-modal-open');document.getElementById('micada-quick-link-label-en').focus();};
		const close=()=>{modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-quick-link-modal-open');};
		document.getElementById('micada-quick-link-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.label_en.value='';form.elements.label_hi.value='';form.elements.url.value='';form.elements.sort_order.value='<?php echo esc_js( (string) $next_order ); ?>';form.elements.new_tab.checked=false;form.elements.show_new.checked=false;title.textContent='<?php echo esc_js( __( 'Add New Link', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Add Quick Link', 'micada' ) ); ?>';open();});
		document.querySelectorAll('.micada-quick-link-edit').forEach(function(button){button.addEventListener('click',function(){form.elements.id.value=button.dataset.id;form.elements.label_en.value=button.dataset.labelEn;form.elements.label_hi.value=button.dataset.labelHi;form.elements.url.value=button.dataset.url;form.elements.sort_order.value=button.dataset.sortOrder;form.elements.new_tab.checked=button.dataset.newTab==='1';form.elements.show_new.checked=button.dataset.showNew==='1';title.textContent='<?php echo esc_js( __( 'Edit Quick Link', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Update Quick Link', 'micada' ) ); ?>';open();});});
		modal.querySelectorAll('[data-quick-link-close]').forEach(function(button){button.addEventListener('click',close);});
		document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});
	})();
	</script>
	<?php
}
}


if ( ! function_exists( 'micada_render_managed_quick_links' ) ) {
function micada_render_managed_quick_links() {
	global $wpdb;
	$table = micada_quick_links_table();
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return false;
	}
	$links = $wpdb->get_results( "SELECT * FROM {$table} WHERE display_status = 'post' ORDER BY sort_order DESC, id DESC" );
	if ( ! $links ) {
		return false;
	}
	$language = function_exists( 'micada_language' ) ? micada_language() : 'en';
	echo '<marquee direction="up" scrollamount="3" data-pause-marquee style="max-height:300px"><ul class="w3-ul" style="font-size:12px">';
	foreach ( $links as $link ) {
		$label = 'hd' === $language && '' !== $link->label_hi ? $link->label_hi : $link->label_en;
		if ( '' === $link->url ) {
			continue;
		}
		echo '<li class="w3-padding-8' . ( $link->show_new ? ' cursor-pointer-new' : ' cursor-pointer' ) . ' w3-left-align"><a href="' . esc_url( micada_resolve_app_link( $link->url ) ) . '"' . ( $link->new_tab ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $label );
		if ( $link->show_new ) {
			if ( function_exists( 'micada_asset' ) ) {
				echo ' <img src="' . esc_url( micada_asset( 'activitiesImages/newitem.gif' ) ) . '" alt="' . esc_attr__( 'New', 'micada' ) . '">';
			} else {
				echo ' <span>' . esc_html__( 'New', 'micada' ) . '</span>';
			}
		}
		echo '</a></li>';
	}
	echo '<li class="w3-padding-24"></li><li class="w3-padding-16"></li>';
	echo '</ul></marquee>';
	return true;
}
}

