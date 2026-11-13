<?php
/**
 * Module: Important Links
 * Description: Manages footer important partner links and logos.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_important_links_table' ) ) {
function micada_important_links_table() {
	global $wpdb;
	return $wpdb->prefix . 'micada_important_links';
}
}


if ( ! function_exists( 'micada_important_links_install' ) ) {
function micada_important_links_install() {
	global $wpdb;
	$table = micada_important_links_table();
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		label_en varchar(255) NOT NULL DEFAULT '',
		label_hi varchar(255) NOT NULL DEFAULT '',
		url text NOT NULL,
		image_id bigint(20) unsigned NOT NULL DEFAULT 0,
		alt_text varchar(255) NOT NULL DEFAULT '',
		sort_order int(11) NOT NULL DEFAULT 0,
		is_visible tinyint(1) NOT NULL DEFAULT 1,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY is_visible (is_visible),
		KEY list_status (display_status)
	) {$charset_collate};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	if ( get_option( 'micada_important_links_seeded' ) ) {
		return;
	}
	if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) > 0 ) {
		update_option( 'micada_important_links_seeded', 1 );
		return;
	}

	$defaults = array(
		array( 'PDMC', 'https://pdmc.da.gov.in/', 'pdmc-logo.png', 'Pre Drop More Crop (PDMC)', 1 ),
		array( 'Irrigation & Water Resources Department', 'https://hid.gov.in/', 'IWRD-logo.png', 'Irrigation & Water Resources Department', 2 ),
		array( 'PMKSY', 'https://www.pmksy.gov.in/', 'pmksy-logo.png', 'Pradhan Mantri Krishi Sinchayee Yojana (PMKSY)', 3 ),
		array( 'Agriculture and Farmers Welfare Department', 'https://agriharyana.gov.in/', 'agri-logo.png', 'Department of Agriculture and Farmers Welfare, Haryana', 4 ),
	);
	foreach ( $defaults as $item ) {
		$image_id = micada_important_links_import_default_image( $item[2] );
		$wpdb->insert(
			$table,
			array( 'label_en' => $item[0], 'url' => $item[1], 'image_id' => $image_id, 'alt_text' => $item[3], 'sort_order' => $item[4], 'is_visible' => 1 ),
			array( '%s', '%s', '%d', '%s', '%d', '%d' )
		);
	}
	update_option( 'micada_important_links_seeded', 1 );
}
}

add_action( 'init', 'micada_important_links_install', 1 );

if ( ! function_exists( 'micada_important_links_import_default_image' ) ) {
function micada_important_links_import_default_image( $filename ) {
	$source = get_stylesheet_directory() . '/assets/images/importantLinks/' . $filename;
	if ( ! is_readable( $source ) ) {
		return 0;
	}
	$uploads = wp_upload_dir();
	$directory = trailingslashit( $uploads['basedir'] ) . 'micada/important-links';
	if ( ! wp_mkdir_p( $directory ) ) {
		return 0;
	}
	$target = trailingslashit( $directory ) . sanitize_file_name( $filename );
	if ( ! file_exists( $target ) && ! copy( $source, $target ) ) {
		return 0;
	}
	$relative = 'micada/important-links/' . sanitize_file_name( $filename );
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_wp_attached_file', 'meta_value' => $relative, 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$filetype = wp_check_filetype( $filename );
	$attachment_id = wp_insert_attachment(
		array( 'post_mime_type' => $filetype['type'], 'post_title' => sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ), 'post_status' => 'inherit' ),
		$target
	);
	if ( ! is_wp_error( $attachment_id ) && $attachment_id ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target ) );
		return (int) $attachment_id;
	}
	return 0;
}
}


if ( ! function_exists( 'micada_important_links_admin_menu' ) ) {
function micada_important_links_admin_menu() {
	add_menu_page( __( 'Important Links', 'micada' ), __( 'Important Links', 'micada' ), 'manage_options', 'micada-important-links', 'micada_important_links_admin_page', 'dashicons-admin-links', 26 );
}
}

add_action( 'admin_menu', 'micada_important_links_admin_menu' );

if ( ! function_exists( 'micada_important_links_admin_assets' ) ) {
function micada_important_links_admin_assets( $hook ) {
	if ( 'toplevel_page_micada-important-links' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_register_script( 'micada-important-links-admin', false, array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'micada-important-links-admin' );
	wp_add_inline_script( 'micada-important-links-admin', "jQuery(function($){var frame;$('#micada-important-link-pick').on('click',function(e){e.preventDefault();if(frame){frame.open();return;}frame=wp.media({title:'Choose Important Link image',button:{text:'Use this image'},multiple:false,library:{type:'image'}});frame.on('select',function(){var image=frame.state().get('selection').first().toJSON();$('#micada-important-image-id').val(image.id);$('#micada-important-image-preview').attr('src',image.url).show();$('#micada-important-image-name').text(image.filename||image.title);});frame.open();});$('#micada-important-image-clear').on('click',function(e){e.preventDefault();$('#micada-important-image-id').val('0');$('#micada-important-image-preview').attr('src','').hide();$('#micada-important-image-name').text('No image selected');});});" );
}
}

add_action( 'admin_enqueue_scripts', 'micada_important_links_admin_assets' );

if ( ! function_exists( 'micada_important_links_upload_dimension_check' ) ) {
function micada_important_links_upload_dimension_check( $file ) {
	if ( empty( $_REQUEST['action'] ) || 'upload-attachment' !== sanitize_key( wp_unslash( $_REQUEST['action'] ) ) || empty( $file['tmp_name'] ) ) {
		return $file;
	}
	$referer = $_REQUEST['_wp_http_referer'] ?? '';
	if ( ! $referer ) {
		$referer = wp_get_raw_referer();
	}
	$referer = is_string( $referer ) ? sanitize_text_field( wp_unslash( $referer ) ) : '';
	if ( false === strpos( $referer, 'micada-important-links' ) ) {
		return $file;
	}
	$size = @getimagesize( $file['tmp_name'] );
	if ( ! $size || $size[0] < 600 || $size[1] < 400 ) {
		$file['error'] = __( 'This image is too small for Important Links. Upload an image at least 600 × 400 pixels; larger images scale without stretching.', 'micada' );
	}
	return $file;
}
}

add_filter( 'wp_handle_upload_prefilter', 'micada_important_links_upload_dimension_check', 30 );

if ( ! function_exists( 'micada_important_links_save' ) ) {
function micada_important_links_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage Important Links.', 'micada' ) );
	}
	check_admin_referer( 'micada_important_links_save' );
	global $wpdb;
	$table = micada_important_links_table();
	$id = absint( $_POST['id'] ?? 0 );
	$image_id = absint( $_POST['image_id'] ?? 0 );
	$label_en = sanitize_text_field( wp_unslash( $_POST['label_en'] ?? '' ) );
	$label_hi = sanitize_text_field( wp_unslash( $_POST['label_hi'] ?? '' ) );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$alt_text = sanitize_text_field( wp_unslash( $_POST['alt_text'] ?? '' ) );
	$order_raw = trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ?? '' ) ) );
	$sort_order = 1 === preg_match( '/^\d+$/', $order_raw ) ? (int) $order_raw : -1;
	$error = '';
	if ( '' === $label_en || ! $url || $sort_order < 0 ) {
		$error = 'invalid';
	} else {
		$dimensions = micada_important_links_validate_image( $image_id );
		if ( is_wp_error( $dimensions ) ) {
			$error = 'image';
		}
	}
	if ( $error ) {
		$redirect_args = array( 'page' => 'micada-important-links', 'message' => $error );
		$redirect_args[ $id ? 'edit' : 'add' ] = $id ? $id : 1;
		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
		exit;
	}
	$data = array( 'label_en' => $label_en, 'label_hi' => $label_hi, 'url' => $url, 'image_id' => $image_id, 'alt_text' => $alt_text, 'sort_order' => $sort_order, 'is_visible' => isset( $_POST['is_visible'] ) ? 1 : 0 );
	$formats = array( '%s', '%s', '%s', '%d', '%s', '%d', '%d' );
	if ( $id ) {
		$wpdb->update( $table, $data, array( 'id' => $id ), $formats, array( '%d' ) );
	} else {
		$wpdb->insert( $table, $data, $formats );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-important-links', 'message' => 'saved' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_important_links_save', 'micada_important_links_save' );

if ( ! function_exists( 'micada_important_links_delete' ) ) {
function micada_important_links_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage Important Links.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	check_admin_referer( 'micada_important_links_delete_' . $id );
	global $wpdb;
	$wpdb->delete( micada_important_links_table(), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-important-links', 'message' => 'deleted' ), admin_url( 'admin.php' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_important_links_delete', 'micada_important_links_delete' );

if ( ! function_exists( 'micada_important_links_validate_image' ) ) {
function micada_important_links_validate_image( $attachment_id ) {
	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		return new WP_Error( 'micada_image_required', __( 'Choose an image from the Media Library.', 'micada' ) );
	}
	$size = @getimagesize( get_attached_file( $attachment_id ) );
	if ( ! $size || $size[0] < 600 || $size[1] < 400 ) {
		return new WP_Error( 'micada_image_too_small', __( 'Image is too small. Use at least 600 × 400 pixels. Larger images are scaled without stretching.', 'micada' ) );
	}
	return true;
}
}


if ( ! function_exists( 'micada_important_links_admin_page' ) ) {
function micada_important_links_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = micada_important_links_table();
	$edit_id = absint( $_GET['edit'] ?? 0 );
	$item = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ), ARRAY_A ) : null;
	$next_order = 1 + (int) $wpdb->get_var( "SELECT COALESCE(MAX(sort_order), 0) FROM {$table}" );
	$item = $item ?: array( 'id' => 0, 'label_en' => '', 'label_hi' => '', 'url' => '', 'image_id' => 0, 'alt_text' => '', 'sort_order' => $next_order, 'is_visible' => 1 );
	$messages = array(
		'saved' => array( 'success', __( 'Important Link saved.', 'micada' ) ),
		'deleted' => array( 'success', __( 'Important Link deleted.', 'micada' ) ),
		'invalid' => array( 'error', __( 'Enter an English label, valid URL, and non-negative display order.', 'micada' ) ),
		'image' => array( 'error', __( 'Choose an image at least 600 × 400 pixels. The image will scale without stretching.', 'micada' ) ),
	);
	$key = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	if ( isset( $messages[ $key ] ) ) {
		echo '<div class="notice notice-' . esc_attr( $messages[ $key ][0] ) . ' is-dismissible"><p>' . esc_html( $messages[ $key ][1] ) . '</p></div>';
	}
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$links = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE display_status = %s ORDER BY sort_order ASC, id ASC", $view ) );
	$image_url = $item['image_id'] ? wp_get_attachment_image_url( $item['image_id'], 'medium' ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Important Links', 'micada' ); ?> <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=micada-important-links&add=1' ) ); ?>"><?php esc_html_e( 'Add Important Link', 'micada' ); ?></a></h1>
		<p><?php esc_html_e( 'Manage the logo cards shown in the homepage Important Links section. Link details are stored in a dedicated WordPress table; image files are Media Library uploads under wp-content/uploads.', 'micada' ); ?></p>
		<p><strong><?php esc_html_e( 'Image size:', 'micada' ); ?></strong> <?php esc_html_e( 'Minimum 600 × 400 pixels. Use a larger source when available; the card preserves the image proportions and contains it without stretching or cropping.', 'micada' ); ?></p>
		<?php micada_cms_status_tabs( 'micada-important-links', $table, $view ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Image', 'micada' ); ?></th><th><?php esc_html_e( 'English title', 'micada' ); ?></th><th><?php esc_html_e( 'URL', 'micada' ); ?></th><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php esc_html_e( 'Visibility', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php if ( $links ) : foreach ( $links as $link ) : ?><tr>
			<td><?php echo $link->image_id ? wp_get_attachment_image( $link->image_id, array( 90, 60 ), false, array( 'style' => 'width:90px;height:60px;object-fit:contain;background:#fff' ) ) : esc_html__( 'No image', 'micada' ); ?></td>
			<td><?php echo esc_html( $link->label_en ); ?></td><td><a href="<?php echo esc_url( $link->url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link->url ); ?></a></td><td><?php echo esc_html( $link->sort_order ); ?></td><td><?php echo $link->is_visible ? esc_html__( 'Visible', 'micada' ) : esc_html__( 'Hidden', 'micada' ); ?></td><td><?php echo 'trash' === $link->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td>
			<td><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'micada-important-links', 'edit' => $link->id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></a> | <?php micada_cms_trash_action_links( 'important-links', $link->id, $link->display_status ); ?></td>
		</tr><?php endforeach; else : ?><tr><td colspan="6"><?php esc_html_e( 'No Important Links yet.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div class="micada-important-modal<?php echo ( isset( $_GET['add'] ) || $edit_id ) ? ' is-open' : ''; ?>" aria-hidden="<?php echo ( isset( $_GET['add'] ) || $edit_id ) ? 'false' : 'true'; ?>">
			<div class="micada-important-modal-backdrop" data-important-close></div>
			<section class="micada-important-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-important-dialog-title">
			<header><h2 id="micada-important-dialog-title"><?php echo $edit_id ? esc_html__( 'Edit Important Link', 'micada' ) : esc_html__( 'Add Important Link', 'micada' ); ?></h2><a class="micada-important-close" href="<?php echo esc_url( admin_url( 'admin.php?page=micada-important-links' ) ); ?>" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>">&times;</a></header>
			<form id="micada-important-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="micada_important_links_save"><input type="hidden" name="id" value="<?php echo esc_attr( $item['id'] ); ?>"><input type="hidden" id="micada-important-image-id" name="image_id" value="<?php echo esc_attr( $item['image_id'] ); ?>"><?php wp_nonce_field( 'micada_important_links_save' ); ?>
			<p><label><strong><?php esc_html_e( 'English title', 'micada' ); ?></strong><input class="widefat" required name="label_en" value="<?php echo esc_attr( $item['label_en'] ); ?>"></label></p>
			<p><label><strong><?php esc_html_e( 'Hindi title (optional)', 'micada' ); ?></strong><input class="widefat" name="label_hi" value="<?php echo esc_attr( $item['label_hi'] ); ?>"></label></p>
			<p><label><strong><?php esc_html_e( 'Destination URL', 'micada' ); ?></strong><input class="widefat" type="url" required name="url" value="<?php echo esc_attr( $item['url'] ); ?>"></label></p>
			<p><label><strong><?php esc_html_e( 'Image alt text', 'micada' ); ?></strong><input class="widefat" name="alt_text" value="<?php echo esc_attr( $item['alt_text'] ); ?>"></label></p>
			<p><img id="micada-important-image-preview" src="<?php echo esc_url( $image_url ?: '' ); ?>" alt="" style="<?php echo $image_url ? 'display:block;' : 'display:none;'; ?>max-width:240px;max-height:140px;object-fit:contain;background:#f0f0f1;padding:8px"><span id="micada-important-image-name"><?php echo $image_url ? esc_html( basename( get_attached_file( $item['image_id'] ) ) ) : esc_html__( 'No image selected', 'micada' ); ?></span><br><button type="button" class="button" id="micada-important-link-pick"><?php esc_html_e( 'Select or Upload Image', 'micada' ); ?></button> <button type="button" class="button" id="micada-important-image-clear"><?php esc_html_e( 'Remove Image', 'micada' ); ?></button><br><span class="description"><?php esc_html_e( 'Minimum 600 × 400 pixels. Image data stays in uploads/Media Library, not in the custom table.', 'micada' ); ?></span></p>
			<p><label><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong><input type="number" min="0" step="1" required name="sort_order" value="<?php echo esc_attr( $item['sort_order'] ); ?>"></label></p>
			<p><label><input type="checkbox" name="is_visible" value="1" <?php checked( $item['is_visible'], 1 ); ?>> <?php esc_html_e( 'Display this link on the homepage', 'micada' ); ?></label></p>
			<div class="micada-important-modal-actions"><button type="submit" class="button button-primary" id="micada-important-submit"><?php echo $edit_id ? esc_html__( 'Save Changes', 'micada' ) : esc_html__( 'Add Important Link', 'micada' ); ?></button><button type="button" class="button" data-important-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
			</form>
			</section>
		</div>
	</div>
	<style>
		.micada-important-modal{display:none;position:fixed;inset:32px 0 0 160px;z-index:100100;align-items:center;justify-content:center;padding:24px}
		.micada-important-modal.is-open{display:flex}
		.micada-important-modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}
		.micada-important-dialog{position:relative;width:min(760px,100%);max-height:calc(100vh - 96px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}
		.micada-important-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
		.micada-important-dialog header h2{margin:0;font-size:21px}
		.micada-important-close{color:#50575e;text-decoration:none;font-size:28px;line-height:1}
		.micada-important-dialog form>p{margin:0 0 16px}
		.micada-important-dialog form>p label strong{display:block;margin-bottom:6px}
		.micada-important-modal-actions{display:flex;align-items:center;gap:8px;margin-top:20px}
		.micada-important-modal-actions p.submit{margin:0;padding:0}
		body.micada-important-modal-open{overflow:hidden}
		@media(max-width:782px){.micada-important-modal{inset:46px 0 0;padding:12px}.micada-important-dialog{max-height:calc(100vh - 70px);padding:18px}}
	</style>
	<?php
}
}


if ( ! function_exists( 'micada_get_important_links' ) ) {
function micada_get_important_links() {
	global $wpdb;
	$table = micada_important_links_table();
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return array();
	}
	return $wpdb->get_results( "SELECT * FROM {$table} WHERE is_visible = 1 AND display_status = 'post' ORDER BY sort_order ASC, id ASC" );
}
}

