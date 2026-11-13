<?php
/**
 * Module: Galleries
 * Description: Manages photo and video galleries.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_gallery_table' ) ) {
function micada_gallery_table( $type ) {
	global $wpdb;
	return $wpdb->prefix . ( 'video' === $type ? 'video_gallery' : 'photo_gallery' );
}
}


if ( ! function_exists( 'micada_gallery_photo_table' ) ) {
function micada_gallery_photo_table() { return micada_gallery_table( 'photo' ); }
}

if ( ! function_exists( 'micada_gallery_video_table' ) ) {
function micada_gallery_video_table() { return micada_gallery_table( 'video' ); }
}


if ( ! function_exists( 'micada_gallery_install' ) ) {
function micada_gallery_install() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$photo_table = micada_gallery_table( 'photo' );
	$video_table = micada_gallery_table( 'video' );
	$photo_sql = "CREATE TABLE {$photo_table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		image_url text NOT NULL,
		caption_en text NOT NULL,
		caption_hi text NOT NULL,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY list_status (display_status)
	) {$charset_collate};";
	$video_sql = "CREATE TABLE {$video_table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		video_url text NOT NULL,
		caption_en text NOT NULL,
		caption_hi text NOT NULL,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY list_status (display_status)
	) {$charset_collate};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $photo_sql );
	dbDelta( $video_sql );

}
}

add_action( 'init', 'micada_gallery_install', 1 );

if ( ! function_exists( 'micada_gallery_admin_assets' ) ) {
function micada_gallery_admin_assets( $hook ) {
	if ( 'toplevel_page_micada-photo-gallery' === $hook ) {
		wp_enqueue_media();
	}
}
}

add_action( 'admin_enqueue_scripts', 'micada_gallery_admin_assets' );

if ( ! function_exists( 'micada_gallery_admin_menu' ) ) {
function micada_gallery_admin_menu() {
	add_menu_page( __( 'Photo Gallery', 'micada' ), __( 'Photo Gallery', 'micada' ), 'manage_options', 'micada-photo-gallery', 'micada_gallery_admin_page', 'dashicons-format-gallery', 24 );
	add_menu_page( __( 'Video Gallery', 'micada' ), __( 'Video Gallery', 'micada' ), 'manage_options', 'micada-video-gallery', 'micada_gallery_admin_page', 'dashicons-video-alt3', 25 );
}
}

add_action( 'admin_menu', 'micada_gallery_admin_menu' );

if ( ! function_exists( 'micada_gallery_admin_url' ) ) {
function micada_gallery_admin_url( $type, $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'micada-' . $type . '-gallery' ), $args ), admin_url( 'admin.php' ) );
}
}


if ( ! function_exists( 'micada_gallery_valid_video_url' ) ) {
function micada_gallery_valid_video_url( $url ) {
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) ) {
		return false;
	}
	$host = strtolower( $parts['host'] ?? '' );
	$path = $parts['path'] ?? '';
	return ( in_array( $host, array( 'youtube.com', 'www.youtube.com' ), true ) && preg_match( '~^/embed/[a-zA-Z0-9_-]+$~', $path ) ) || ( 'drive.google.com' === $host && preg_match( '~^/file/d/[a-zA-Z0-9_-]+/preview$~', $path ) );
}
}


if ( ! function_exists( 'micada_gallery_valid_image_url' ) ) {
function micada_gallery_valid_image_url( $url ) {
	$parts = wp_parse_url( $url );
	return is_array( $parts ) && in_array( $parts['scheme'] ?? '', array( 'http', 'https' ), true ) && ! empty( $parts['host'] ) && ! isset( $parts['user'] ) && ! isset( $parts['pass'] );
}
}


/** Rebuild local uploads URLs using the current site host for LAN/mobile access. */
if ( ! function_exists( 'micada_gallery_public_image_url' ) ) {
function micada_gallery_public_image_url( $url ) {
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['path'] ) ) {
		return $url;
	}
	$host = strtolower( $parts['host'] );
	$is_local_host = in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true );
	if ( ! $is_local_host && filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		$octets = array_map( 'intval', explode( '.', $host ) );
		$is_local_host = 10 === $octets[0]
			|| 127 === $octets[0]
			|| ( 192 === $octets[0] && 168 === $octets[1] )
			|| ( 172 === $octets[0] && $octets[1] >= 16 && $octets[1] <= 31 );
	}
	if ( ! $is_local_host ) {
		return $url;
	}
	$site = wp_parse_url( home_url( '/' ) );
	if ( ! is_array( $site ) || empty( $site['host'] ) ) {
		return $url;
	}
	$public_url = ( $site['scheme'] ?? 'http' ) . '://' . $site['host'];
	if ( ! empty( $site['port'] ) ) {
		$public_url .= ':' . absint( $site['port'] );
	}
	$public_url .= $parts['path'];
	if ( ! empty( $parts['query'] ) ) {
		$public_url .= '?' . $parts['query'];
	}
	if ( ! empty( $parts['fragment'] ) ) {
		$public_url .= '#' . $parts['fragment'];
	}
	return $public_url;
}
}


if ( ! function_exists( 'micada_gallery_save' ) ) {
function micada_gallery_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage gallery items.', 'micada' ) );
	}
	$type = isset( $_POST['gallery_type'] ) ? sanitize_key( wp_unslash( $_POST['gallery_type'] ) ) : '';
	if ( ! in_array( $type, array( 'photo', 'video' ), true ) ) {
		wp_die( esc_html__( 'Invalid gallery type.', 'micada' ) );
	}
	check_admin_referer( 'micada_gallery_save_' . $type );
	$id = absint( $_POST['id'] ?? 0 );
	$order_raw = isset( $_POST['sort_order'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ) ) ) : '0';
	$order_valid = 1 === preg_match( '/^\d+$/', $order_raw );
	$url = esc_url_raw( wp_unslash( $_POST['gallery_url'] ?? '' ) );
	$caption_en = sanitize_text_field( wp_unslash( $_POST['caption_en'] ?? '' ) );
	$caption_hi = sanitize_text_field( wp_unslash( $_POST['caption_hi'] ?? '' ) );
	$url_valid = 'photo' === $type ? micada_gallery_valid_image_url( $url ) : micada_gallery_valid_video_url( $url );
	if ( '' === $caption_en || ! $order_valid || ! $url_valid ) {
		wp_safe_redirect( micada_gallery_admin_url( $type, array( 'message' => 'invalid' ) ) );
		exit;
	}
	$table = micada_gallery_table( $type );
	$url_column = 'photo' === $type ? 'image_url' : 'video_url';
	$data = array( $url_column => $url, 'caption_en' => $caption_en, 'caption_hi' => $caption_hi, 'sort_order' => (int) $order_raw );
	$formats = array( '%s', '%s', '%s', '%d' );
	global $wpdb;
	if ( $id ) {
		$wpdb->update( $table, $data, array( 'id' => $id ), $formats, array( '%d' ) );
	} else {
		$wpdb->insert( $table, $data, $formats );
	}
	wp_safe_redirect( micada_gallery_admin_url( $type, array( 'message' => 'saved' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_gallery_save', 'micada_gallery_save' );

if ( ! function_exists( 'micada_gallery_delete' ) ) {
function micada_gallery_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage gallery items.', 'micada' ) );
	}
	$type = isset( $_GET['gallery_type'] ) ? sanitize_key( wp_unslash( $_GET['gallery_type'] ) ) : '';
	$id = absint( $_GET['id'] ?? 0 );
	if ( ! in_array( $type, array( 'photo', 'video' ), true ) ) {
		wp_die( esc_html__( 'Invalid gallery type.', 'micada' ) );
	}
	check_admin_referer( 'micada_gallery_delete_' . $type . '_' . $id );
	global $wpdb;
	$wpdb->delete( micada_gallery_table( $type ), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( micada_gallery_admin_url( $type, array( 'message' => 'deleted' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_gallery_delete', 'micada_gallery_delete' );

if ( ! function_exists( 'micada_gallery_admin_page' ) ) {
function micada_gallery_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$type = isset( $_GET['page'] ) && 'micada-video-gallery' === sanitize_key( wp_unslash( $_GET['page'] ) ) ? 'video' : 'photo';
	$table = micada_gallery_table( $type );
	$url_column = 'photo' === $type ? 'image_url' : 'video_url';
	$label = 'photo' === $type ? __( 'Photo', 'micada' ) : __( 'Video', 'micada' );
	$next_order = 1 + (int) $GLOBALS['wpdb']->get_var( "SELECT COALESCE(MAX(sort_order), 0) FROM {$table}" );
	$edit_id = absint( $_GET['edit'] ?? 0 );
	$item = $edit_id ? $GLOBALS['wpdb']->get_row( $GLOBALS['wpdb']->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ), ARRAY_A ) : null;
	$item = $item ?: array( 'id' => 0, $url_column => '', 'caption_en' => '', 'caption_hi' => '', 'sort_order' => $next_order );
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$items = $GLOBALS['wpdb']->get_results( $GLOBALS['wpdb']->prepare( "SELECT * FROM {$table} WHERE display_status = %s ORDER BY sort_order DESC, id DESC", $view ) );
	$page_name = 'photo' === $type ? __( 'Photo Gallery', 'micada' ) : __( 'Video Gallery', 'micada' );
	$add_label = 'photo' === $type ? __( 'Add New Photo', 'micada' ) : __( 'Add New Video', 'micada' );
	$items_label = 'photo' === $type ? __( 'Photo Gallery Items', 'micada' ) : __( 'Video Gallery Items', 'micada' );
	if ( isset( $_GET['message'] ) ) {
		$messages = array( 'saved' => __( 'Gallery item saved.', 'micada' ), 'deleted' => __( 'Gallery item deleted.', 'micada' ), 'invalid' => __( 'Enter an English caption, a valid media URL, and a non-negative whole number for display order.', 'micada' ) );
		$key = sanitize_key( wp_unslash( $_GET['message'] ) );
		if ( isset( $messages[ $key ] ) ) {
			echo '<div class="notice ' . ( 'invalid' === $key ? 'notice-error' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( $messages[ $key ] ) . '</p></div>';
		}
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $page_name ); ?> <button type="button" class="page-title-action" id="micada-gallery-add"><?php echo esc_html( $add_label ); ?></button></h1>
		<p><?php esc_html_e( 'These items are displayed in the homepage gallery and the full gallery page. Higher display order values appear first.', 'micada' ); ?></p>
		<hr><h2><?php echo esc_html( $items_label ); ?></h2>
		<?php micada_cms_status_tabs( 'photo' === $type ? 'micada-photo-gallery' : 'micada-video-gallery', $table, $view ); ?>
		<table class="widefat striped micada-gallery-admin-table"><thead><tr><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php echo esc_html( $label ); ?></th><th><?php esc_html_e( 'English caption', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi caption', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php if ( $items ) : foreach ( $items as $entry ) : ?>
			<tr><td><?php echo esc_html( $entry->sort_order ); ?></td><td><?php if ( 'photo' === $type ) : ?><img src="<?php echo esc_url( micada_gallery_public_image_url( $entry->image_url ) ); ?>" alt="" style="width:100px;height:60px;object-fit:cover"><?php else : ?><a href="<?php echo esc_url( $entry->video_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $entry->video_url ); ?></a><?php endif; ?></td><td><?php echo esc_html( $entry->caption_en ); ?></td><td><?php echo esc_html( $entry->caption_hi ); ?></td><td><?php echo 'trash' === $entry->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><button type="button" class="button-link micada-gallery-edit" data-id="<?php echo esc_attr( $entry->id ); ?>" data-url="<?php echo esc_attr( $entry->$url_column ); ?>" data-caption-en="<?php echo esc_attr( $entry->caption_en ); ?>" data-caption-hi="<?php echo esc_attr( $entry->caption_hi ); ?>" data-sort-order="<?php echo esc_attr( $entry->sort_order ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <?php micada_cms_trash_action_links( 'photo' === $type ? 'gallery-photo' : 'gallery-video', $entry->id, $entry->display_status ); ?></td></tr>
		<?php endforeach; else : ?><tr><td colspan="5"><?php esc_html_e( 'No gallery items found.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div id="micada-gallery-modal" class="micada-gallery-modal<?php echo $edit_id ? ' is-open' : ''; ?>" aria-hidden="<?php echo $edit_id ? 'false' : 'true'; ?>">
			<div class="micada-gallery-backdrop" data-gallery-close></div>
			<section class="micada-gallery-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-gallery-title">
				<header><h2 id="micada-gallery-title"><?php echo $edit_id ? esc_html__( 'Edit Gallery Item', 'micada' ) : esc_html( $add_label ); ?></h2><button type="button" class="micada-gallery-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-gallery-close>&times;</button></header>
				<form id="micada-gallery-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
					<input type="hidden" name="action" value="micada_gallery_save"><input type="hidden" name="gallery_type" value="<?php echo esc_attr( $type ); ?>"><input type="hidden" name="id" value="<?php echo esc_attr( $item['id'] ); ?>">
					<?php wp_nonce_field( 'micada_gallery_save_' . $type ); ?>
					<p><label for="micada-gallery-url"><strong><?php echo esc_html( 'photo' === $type ? __( 'Image URL', 'micada' ) : __( 'YouTube or Drive embed URL', 'micada' ) ); ?></strong></label><input class="widefat" type="url" required id="micada-gallery-url" name="gallery_url" value="<?php echo esc_attr( $item[ $url_column ] ); ?>" placeholder="<?php echo esc_attr( 'video' === $type ? 'https://www.youtube.com/embed/VIDEO_ID' : '' ); ?>"><?php if ( 'photo' === $type ) : ?><button type="button" class="button" id="micada-gallery-select-image" style="margin-top:8px"><?php esc_html_e( 'Choose Image', 'micada' ); ?></button><?php endif; ?></p>
					<p><label for="micada-gallery-caption-en"><strong><?php esc_html_e( 'English caption', 'micada' ); ?></strong></label><input class="widefat" required id="micada-gallery-caption-en" name="caption_en" value="<?php echo esc_attr( $item['caption_en'] ); ?>"></p>
					<p><label for="micada-gallery-caption-hi"><strong><?php esc_html_e( 'Hindi caption', 'micada' ); ?></strong></label><input class="widefat" id="micada-gallery-caption-hi" name="caption_hi" value="<?php echo esc_attr( $item['caption_hi'] ); ?>"></p>
					<p><label for="micada-gallery-order"><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong></label><input type="number" min="0" step="1" required class="small-text" id="micada-gallery-order" name="sort_order" value="<?php echo esc_attr( $item['sort_order'] ); ?>"></p>
					<div class="micada-gallery-actions"><button type="submit" class="button button-primary" id="micada-gallery-submit"><?php echo $edit_id ? esc_html__( 'Update Item', 'micada' ) : esc_html( 'photo' === $type ? __( 'Add Photo', 'micada' ) : __( 'Add Video', 'micada' ) ); ?></button><button type="button" class="button" data-gallery-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
	</div>
	<style>
		.micada-gallery-admin-table th:last-child,.micada-gallery-admin-table td:last-child{width:130px;white-space:nowrap}
		.micada-gallery-modal{display:none;position:fixed;inset:0;z-index:100100;align-items:center;justify-content:center;padding:24px}.micada-gallery-modal.is-open{display:flex}
		.micada-gallery-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}.micada-gallery-dialog{position:relative;width:min(680px,100%);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}
		.micada-gallery-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.micada-gallery-dialog header h2{margin:0;font-size:21px}.micada-gallery-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}
		.micada-gallery-dialog form>p{margin:0 0 16px}.micada-gallery-dialog form>p>label{display:block;margin-bottom:6px}.micada-gallery-actions{display:flex;gap:8px;margin-top:22px}body.micada-gallery-modal-open{overflow:hidden}
	</style>
	<script>
	(function(){
		const modal=document.getElementById('micada-gallery-modal'),form=document.getElementById('micada-gallery-form'),heading=document.getElementById('micada-gallery-title'),submit=document.getElementById('micada-gallery-submit');
		const open=()=>{modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-gallery-modal-open');document.getElementById('micada-gallery-caption-en').focus();};
		const close=()=>{modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-gallery-modal-open');};
		document.getElementById('micada-gallery-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.gallery_url.value='';form.elements.caption_en.value='';form.elements.caption_hi.value='';form.elements.sort_order.value='<?php echo esc_js( (string) $next_order ); ?>';heading.textContent='<?php echo esc_js( $add_label ); ?>';submit.textContent='<?php echo esc_js( 'photo' === $type ? __( 'Add Photo', 'micada' ) : __( 'Add Video', 'micada' ) ); ?>';open();});
		document.querySelectorAll('.micada-gallery-edit').forEach(function(button){button.addEventListener('click',function(){form.elements.id.value=button.dataset.id;form.elements.gallery_url.value=button.dataset.url;form.elements.caption_en.value=button.dataset.captionEn;form.elements.caption_hi.value=button.dataset.captionHi;form.elements.sort_order.value=button.dataset.sortOrder;heading.textContent='<?php echo esc_js( __( 'Edit Gallery Item', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Update Item', 'micada' ) ); ?>';open();});});
		modal.querySelectorAll('[data-gallery-close]').forEach(button=>button.addEventListener('click',close));document.addEventListener('keydown',event=>{if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});
		<?php if ( 'photo' === $type ) : ?>
		let picker;document.getElementById('micada-gallery-select-image').addEventListener('click',function(){if(picker){picker.open();return;}picker=wp.media({title:'<?php echo esc_js( __( 'Choose Photo', 'micada' ) ); ?>',button:{text:'<?php echo esc_js( __( 'Use this photo', 'micada' ) ); ?>'},multiple:false,library:{type:'image'}});picker.on('select',function(){form.elements.gallery_url.value=picker.state().get('selection').first().toJSON().url;});picker.open();});
		<?php endif; ?>
	})();
	</script>
	<?php
}
}


if ( ! function_exists( 'micada_gallery_items' ) ) {
function micada_gallery_items( $type ) {
	global $wpdb;
	$table = micada_gallery_table( $type );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( "SELECT * FROM {$table} WHERE display_status = 'post' ORDER BY sort_order DESC, id DESC" );
		if ( $rows ) {
			$items = array();
			foreach ( $rows as $row ) {
				$caption = 'hd' === micada_language() && '' !== $row->caption_hi ? $row->caption_hi : $row->caption_en;
				$items[] = 'photo' === $type ? array( 'previewImageSrc' => micada_gallery_public_image_url( $row->image_url ), 'description' => $caption ) : array( 'url' => $row->video_url, 'description' => $caption );
			}
			return $items;
		}
	}
	return array();
}
}

