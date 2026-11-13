<?php
/**
 * Module: Homepage Popup
 * Description: Controls for the homepage opening notice modal.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_popup_table' ) ) {
function micada_popup_table() {
	global $wpdb;
	return $wpdb->prefix . 'homepage_popup';
}
}


if ( ! function_exists( 'micada_popup_install_table' ) ) {
function micada_popup_install_table() {
	global $wpdb;
	$table = micada_popup_table();
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	if ( ! $exists || '1' !== get_option( 'micada_homepage_popup_schema' ) ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
			id tinyint(3) unsigned NOT NULL,
			image_attachment_id bigint(20) unsigned NOT NULL DEFAULT 0,
			image_url text NOT NULL,
			default_image_attachment_id bigint(20) unsigned NOT NULL DEFAULT 0,
			is_enabled tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY  (id)
		) {$charset_collate};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'micada_homepage_popup_schema', '1', false );
	}
	if ( ! $wpdb->get_var( "SELECT id FROM `{$table}` WHERE id = 1" ) ) {
		$legacy_enabled = get_option( 'micada_popup_enabled', null );
		$wpdb->insert(
			$table,
			array(
				'id' => 1,
				'image_attachment_id' => absint( get_option( 'micada_popup_media_attachment_id', 0 ) ),
				'image_url' => esc_url_raw( (string) get_option( 'micada_popup_image_url', get_theme_mod( 'micada_notice_image', '' ) ) ),
				'default_image_attachment_id' => absint( get_option( 'micada_popup_default_media_attachment_id', 0 ) ),
				'is_enabled' => null === $legacy_enabled ? ( get_theme_mod( 'micada_notice_enabled', true ) ? 1 : 0 ) : ( '1' === (string) $legacy_enabled ? 1 : 0 ),
			),
			array( '%d', '%d', '%s', '%d', '%d' )
		);
	}
}
}

add_action( 'init', 'micada_popup_install_table', 1 );

if ( ! function_exists( 'micada_popup_settings' ) ) {
function micada_popup_settings() {
	global $wpdb;
	$table = micada_popup_table();
	$row = $wpdb->get_row( "SELECT * FROM `{$table}` WHERE id = 1", ARRAY_A );
	return is_array( $row ) ? $row : array( 'image_attachment_id' => 0, 'image_url' => '', 'default_image_attachment_id' => 0, 'is_enabled' => 1 );
}
}


if ( ! function_exists( 'micada_popup_update_settings' ) ) {
function micada_popup_update_settings( $settings ) {
	global $wpdb;
	return false !== $wpdb->update( micada_popup_table(), $settings, array( 'id' => 1 ) );
}
}


if ( ! function_exists( 'micada_popup_default_image_url' ) ) {
function micada_popup_default_image_url() {
	if ( function_exists( 'micada_home_data' ) && function_exists( 'micada_source_image' ) ) {
		return micada_source_image( micada_home_data( 'notice' ) );
	}
	return '';
}
}


if ( ! function_exists( 'micada_popup_image_url' ) ) {
function micada_popup_image_url() {
	$settings = micada_popup_settings();
	$image = $settings['image_url'] ?? '';
	if ( ! is_string( $image ) || '' === $image ) {
		$image = get_theme_mod( 'micada_notice_image', '' ) ?: micada_popup_default_image_url();
	}
	return function_exists( 'micada_gallery_public_image_url' ) ? micada_gallery_public_image_url( $image ) : $image;
}
}


if ( ! function_exists( 'micada_popup_is_enabled' ) ) {
function micada_popup_is_enabled() {
	$settings = micada_popup_settings();
	return ! empty( $settings['is_enabled'] );
}
}


if ( ! function_exists( 'micada_popup_managed_url_prefix' ) ) {
function micada_popup_managed_url_prefix() {
	$uploads = wp_upload_dir();
	return trailingslashit( $uploads['baseurl'] ) . 'micada/homepage-popup/';
}
}


if ( ! function_exists( 'micada_popup_is_managed_url' ) ) {
function micada_popup_is_managed_url( $url ) {
	return is_string( $url ) && 0 === strpos( $url, micada_popup_managed_url_prefix() );
}
}


/** Copy a validated image file into the popup uploads folder. */
if ( ! function_exists( 'micada_popup_copy_file' ) ) {
function micada_popup_copy_file( $source ) {
	$allowed_mimes = array(
		'image/jpeg' => array( 'jpg', 'jpeg', 'jpe' ),
		'image/png'  => array( 'png' ),
		'image/gif'  => array( 'gif' ),
		'image/webp' => array( 'webp' ),
	);
	if ( ! is_file( $source ) ) {
		return new WP_Error( 'missing_popup_image', __( 'The selected image file could not be found.', 'micada' ) );
	}
	$image_info = @getimagesize( $source );
	$mime      = $image_info['mime'] ?? '';
	$extension = strtolower( pathinfo( $source, PATHINFO_EXTENSION ) );
	if ( false === $image_info || ! isset( $allowed_mimes[ $mime ] ) || ! in_array( $extension, $allowed_mimes[ $mime ], true ) ) {
		return new WP_Error( 'invalid_popup_image', __( 'Choose a valid JPG, PNG, GIF, or WebP image.', 'micada' ) );
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return new WP_Error( 'popup_upload_dir', __( 'The WordPress uploads directory is not available.', 'micada' ) );
	}
	$target_dir = trailingslashit( $uploads['basedir'] ) . 'micada/homepage-popup';
	if ( ! wp_mkdir_p( $target_dir ) ) {
		return new WP_Error( 'popup_folder', __( 'The popup upload folder could not be created. Check folder permissions.', 'micada' ) );
	}
	$filename = wp_unique_filename( $target_dir, wp_basename( $source ) );
	$target   = trailingslashit( $target_dir ) . $filename;
	if ( ! copy( $source, $target ) ) {
		return new WP_Error( 'popup_copy_failed', __( 'The selected image could not be copied to the popup folder.', 'micada' ) );
	}

	return array(
		'path' => $target,
		'url'  => esc_url_raw( trailingslashit( $uploads['baseurl'] ) . 'micada/homepage-popup/' . rawurlencode( $filename ) ),
		'mime' => $mime,
	);
}
}


/** Make the bundled opening notice available in the Media Library on first CMS visit. */
if ( ! function_exists( 'micada_popup_default_media_attachment_id' ) ) {
function micada_popup_default_media_attachment_id() {
	$settings = micada_popup_settings();
	$attachment_id = absint( $settings['default_image_attachment_id'] ?? 0 );
	if ( $attachment_id && 'attachment' === get_post_type( $attachment_id ) && get_attached_file( $attachment_id ) ) {
		return $attachment_id;
	}
	if ( ! function_exists( 'micada_home_data' ) ) {
		return 0;
	}
	$source = get_stylesheet_directory() . '/assets/images/' . ltrim( micada_home_data( 'notice' ), '/' );
	$copy = micada_popup_copy_file( $source );
	if ( is_wp_error( $copy ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $copy['mime'],
			'post_title'     => __( 'Homepage Popup Image', 'micada' ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$copy['path'],
		0,
		true
	);
	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		wp_delete_file( $copy['path'] );
		return 0;
	}
	$metadata = wp_generate_attachment_metadata( $attachment_id, $copy['path'] );
	if ( ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}
	micada_popup_update_settings( array( 'default_image_attachment_id' => $attachment_id ) );
	return $attachment_id;
}
}


if ( ! function_exists( 'micada_popup_current_media_attachment_id' ) ) {
function micada_popup_current_media_attachment_id( $default_attachment_id ) {
	$settings = micada_popup_settings();
	$attachment_id = absint( $settings['image_attachment_id'] ?? 0 );
	if ( $attachment_id && 'attachment' === get_post_type( $attachment_id ) ) {
		return $attachment_id;
	}

	$current_url = $settings['image_url'] ?? '';
	$legacy_url  = get_theme_mod( 'micada_notice_image', '' );
	if ( ! $current_url ) {
		$current_url = $legacy_url ?: micada_popup_default_image_url();
	}
	if ( $current_url && $current_url !== micada_popup_default_image_url() ) {
		$found_id = attachment_url_to_postid( $current_url );
		if ( $found_id ) {
			micada_popup_update_settings( array( 'image_attachment_id' => $found_id ) );
			return $found_id;
		}
	}
	if ( $default_attachment_id ) {
		micada_popup_update_settings( array( 'image_attachment_id' => $default_attachment_id, 'image_url' => wp_get_attachment_url( $default_attachment_id ) ) );
		return $default_attachment_id;
	}
	return 0;
}
}


if ( ! function_exists( 'micada_popup_copy_media_attachment' ) ) {
function micada_popup_copy_media_attachment( $attachment_id ) {
	$mime = get_post_mime_type( $attachment_id );
	if ( ! $mime || ! wp_attachment_is_image( $attachment_id ) ) {
		return new WP_Error( 'unsupported_popup_image', __( 'Choose an image from the Media Library.', 'micada' ) );
	}

	$source = get_attached_file( $attachment_id );
	$copy = micada_popup_copy_file( $source );
	if ( is_wp_error( $copy ) ) {
		return $copy;
	}
	return $copy;
}
}


if ( ! function_exists( 'micada_popup_delete_managed_file' ) ) {
function micada_popup_delete_managed_file( $url ) {
	if ( ! micada_popup_is_managed_url( $url ) ) {
		return;
	}
	$default_id = absint( micada_popup_settings()['default_image_attachment_id'] ?? 0 );
	if ( $default_id && $url === wp_get_attachment_url( $default_id ) ) {
		return;
	}
	$uploads = wp_upload_dir();
	$filename = rawurldecode( wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ) );
	$path = trailingslashit( $uploads['basedir'] ) . 'micada/homepage-popup/' . $filename;
	if ( is_file( $path ) ) {
		wp_delete_file( $path );
	}
}
}


if ( ! function_exists( 'micada_popup_admin_menu' ) ) {
function micada_popup_admin_menu() {
	add_menu_page(
		__( 'Homepage Popup', 'micada' ),
		__( 'Homepage Popup', 'micada' ),
		'manage_options',
		'micada-homepage-popup',
		'micada_popup_admin_page',
		'dashicons-format-image',
		4
	);
}
}

add_action( 'admin_menu', 'micada_popup_admin_menu', PHP_INT_MAX );

if ( ! function_exists( 'micada_popup_admin_scripts' ) ) {
function micada_popup_admin_scripts( $hook ) {
	if ( 'toplevel_page_micada-homepage-popup' === $hook ) {
		wp_enqueue_media();
	}
}
}

add_action( 'admin_enqueue_scripts', 'micada_popup_admin_scripts' );

if ( ! function_exists( 'micada_popup_redirect' ) ) {
function micada_popup_redirect( $notice ) {
	wp_safe_redirect( add_query_arg(
		array( 'page' => 'micada-homepage-popup', 'notice' => $notice ),
		admin_url( 'admin.php' )
	) );
	exit;
}
}


if ( ! function_exists( 'micada_popup_save' ) ) {
function micada_popup_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the homepage popup.', 'micada' ) );
	}
	check_admin_referer( 'micada_popup_save' );

	$settings = micada_popup_settings();
	$settings['is_enabled'] = isset( $_POST['popup_enabled'] ) ? 1 : 0;
	micada_popup_update_settings( array( 'is_enabled' => $settings['is_enabled'] ) );

	if ( isset( $_POST['remove_image'] ) ) {
		$old_image = $settings['image_url'] ?? '';
		micada_popup_delete_managed_file( $old_image );
		$default_id = micada_popup_default_media_attachment_id();
		if ( $default_id ) {
			micada_popup_update_settings( array( 'image_attachment_id' => $default_id, 'image_url' => wp_get_attachment_url( $default_id ) ) );
		} else {
			micada_popup_update_settings( array( 'image_attachment_id' => 0, 'image_url' => '' ) );
		}
		micada_popup_redirect( 'removed' );
	}

	$attachment_id = isset( $_POST['popup_attachment_id'] ) ? absint( $_POST['popup_attachment_id'] ) : 0;
	if ( $attachment_id ) {
		$new_image = micada_popup_copy_media_attachment( $attachment_id );
		if ( is_wp_error( $new_image ) ) {
			$notice = 'micada_image_dimensions' === $new_image->get_error_code() ? 'size-error' : 'media-error';
			micada_popup_redirect( $notice );
		}
		$old_image = $settings['image_url'] ?? '';
		micada_popup_update_settings( array( 'image_url' => $new_image['url'], 'image_attachment_id' => $attachment_id ) );
		micada_popup_delete_managed_file( $old_image );
	}

	micada_popup_redirect( 'saved' );
}
}

add_action( 'admin_post_micada_popup_save', 'micada_popup_save' );

if ( ! function_exists( 'micada_popup_admin_page' ) ) {
function micada_popup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the homepage popup.', 'micada' ) );
	}
	$default_attachment_id = micada_popup_default_media_attachment_id();
	$current_attachment_id = micada_popup_current_media_attachment_id( $default_attachment_id );
	$notice = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : '';
	$image  = micada_popup_image_url();
	$popup_settings = micada_popup_settings();
	$uploaded_image = $popup_settings['image_url'] ?? '';
	$has_uploaded_image = micada_popup_is_managed_url( $uploaded_image ) && $uploaded_image !== ( $default_attachment_id ? wp_get_attachment_url( $default_attachment_id ) : '' );
	$messages = array(
		'saved'       => __( 'Popup settings saved.', 'micada' ),
		'removed'     => __( 'Popup image restored to the original homepage notice.', 'micada' ),
		'media-error' => __( 'The selected media could not be used. Choose a valid image from the Media Library.', 'micada' ),

	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage Popup', 'micada' ); ?></h1>
		<p><?php esc_html_e( 'This image appears in the opening popup when visitors first open the homepage.', 'micada' ); ?></p>
		<?php if ( isset( $messages[ $notice ] ) ) : ?>
			<div class="notice <?php echo 'media-error' === $notice ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $messages[ $notice ] ); ?></p></div>
		<?php endif; ?>
		<div style="max-width:760px;background:#fff;border:1px solid #c3c4c7;padding:24px;margin-top:18px">
			<h2><?php esc_html_e( 'Current popup image', 'micada' ); ?></h2>
			<img id="micada-popup-preview" src="<?php echo esc_url( $image ); ?>" alt="<?php esc_attr_e( 'Current homepage popup', 'micada' ); ?>" style="display:block;width:auto;max-width:100%;max-height:620px;margin:12px 0 20px;border:1px solid #dcdcde">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="micada_popup_save">
				<input type="hidden" id="micada-popup-attachment-id" name="popup_attachment_id" value="">
				<input type="hidden" id="micada-popup-current-attachment-id" value="<?php echo esc_attr( (string) $current_attachment_id ); ?>">
				<?php wp_nonce_field( 'micada_popup_save' ); ?>
				<p><label><input type="checkbox" name="popup_enabled" value="1" <?php checked( micada_popup_is_enabled() ); ?>> <?php esc_html_e( 'Show the popup on the homepage', 'micada' ); ?></label></p>
				<p><button type="button" class="button button-secondary" id="micada-popup-select-image"><?php esc_html_e( 'Select or Upload Image', 'micada' ); ?></button>
					<span id="micada-popup-selected-name" class="description"></span>
				</p>
				<p class="description"><?php esc_html_e( 'Popup settings are stored in the Homepage Popup database table. Image files remain in wp-content/uploads/micada/homepage-popup.', 'micada' ); ?></p>
				<?php submit_button( __( 'Save Popup', 'micada' ) ); ?>
				<?php if ( $has_uploaded_image ) : ?>
					<button type="submit" class="button" name="remove_image" value="1" onclick="return confirm('<?php echo esc_js( __( 'Restore the original homepage popup image?', 'micada' ) ); ?>');"><?php esc_html_e( 'Restore original image', 'micada' ); ?></button>
				<?php endif; ?>
			</form>
		</div>
	</div>
	<script>
	jQuery(function($) {
		var frame;
		var currentAttachmentId = parseInt($('#micada-popup-current-attachment-id').val(), 10) || 0;
		$('#micada-popup-select-image').on('click', function(event) {
			event.preventDefault();
			if (!frame) {
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Select Popup Image', 'micada' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use this image', 'micada' ) ); ?>' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('open', function() {
					if (currentAttachmentId) {
						var selection = frame.state().get('selection');
						selection.reset();
						selection.add(wp.media.attachment(currentAttachmentId));
					}
				});
				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();

					$('#micada-popup-attachment-id').val(attachment.id);
					$('#micada-popup-preview').attr('src', attachment.url);
					$('#micada-popup-selected-name').text(attachment.filename || attachment.title || '');
				});
			}
			frame.open();
		});
	});
	</script>
	<?php
}
}

