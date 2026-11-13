<?php
/**
 * Module: Banners
 * Description: Manages homepage banners and captions in WordPress.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Custom Post Type: Banners
 */
if ( ! function_exists( 'micada_register_banners_post_type' ) ) {
function micada_register_banners_post_type() {
	$labels = array(
		'name'               => _x( 'Banners', 'Post type general name', 'micada' ),
		'singular_name'      => _x( 'Banner', 'Post type singular name', 'micada' ),
		'menu_name'          => _x( 'Banners', 'Admin Menu text', 'micada' ),
		'name_admin_bar'     => _x( 'Banner', 'Add New on Toolbar', 'micada' ),
		'add_new'            => __( 'Add New Banner', 'micada' ),
		'add_new_item'       => __( 'Add New Banner', 'micada' ),
		'new_item'           => __( 'New Banner', 'micada' ),
		'edit_item'          => __( 'Edit Banner', 'micada' ),
		'view_item'          => __( 'View Banner', 'micada' ),
		'all_items'          => __( 'All Banners', 'micada' ),
		'search_items'       => __( 'Search Banners', 'micada' ),
		'not_found'          => __( 'No banners found.', 'micada' ),
		'not_found_in_trash' => __( 'No banners found in Trash.', 'micada' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 21,
		'menu_icon'          => 'dashicons-images-alt2',
		'supports'           => array( 'title' ),
		'show_in_rest'       => false,
	);

	register_post_type( 'micada_banner', $args );
}
}

add_action( 'init', 'micada_register_banners_post_type', 0 );

/** Show a visible label for the banner post title on add and edit screens. */
if ( ! function_exists( 'micada_banner_title_label' ) ) {
function micada_banner_title_label( $post ) {
	if ( ! $post || 'micada_banner' !== $post->post_type ) {
		return;
	}
	echo '<p class="micada-banner-title-label" style="margin:0 0 6px"><label for="title" style="font-size:16px;font-weight:700;line-height:1.4;color:#1d2327"><strong>' . esc_html__( 'Title', 'micada' ) . '</strong></label></p>';
}
}

add_action( 'edit_form_top', 'micada_banner_title_label' );

if ( ! function_exists( 'micada_banner_title_placeholder' ) ) {
function micada_banner_title_placeholder( $text, $post ) {
	return $post && 'micada_banner' === $post->post_type ? __( 'Title', 'micada' ) : $text;
}
}

add_filter( 'enter_title_here', 'micada_banner_title_placeholder', 10, 2 );

/**
 * Seed initial 6 banner images from theme assets if database table is empty
 */
if ( ! function_exists( 'micada_seed_banners_if_empty' ) ) {
function micada_seed_banners_if_empty() {
	// Only seed once if no posts exist
	$existing = get_posts( array(
		'post_type'   => 'micada_banner',
		'post_status' => 'any',
		'numberposts' => 1,
	) );

	if ( ! empty( $existing ) ) {
		return;
	}

	$source_dir = get_stylesheet_directory() . '/assets/images/home/resize';
	if ( ! is_dir( $source_dir ) ) {
		return;
	}

	$banners_data = array(
		1 => array(
			'file'        => 'Drip irrigated wheat in 8 acre in Mangali Suratiya, Hisar, 12.1.2024.jpg',
			'description' => 'Drip irrigated wheat in 8 acre in Mangali Suratiya, Hisar, 12.1.2024',
		),
		2 => array(
			'file'        => 'Wheat with mini sprinklers in Budak, Hisar, 08.03.24.jpg',
			'description' => 'Wheat with mini sprinklers in Budak, Hisar, 08.03.24',
		),
		3 => array(
			'file'        => 'Tomato with drip and poly mulch in the field of Dr. Harish Gupta, Balsamand, Hisar, 8.3.2024.jpg',
			'description' => 'Tomato with drip and poly mulch in the field of Dr. Harish Gupta, Balsamand, Hisar, 8.3.2024',
		),
		4 => array(
			'file'        => 'Harvesting Paired row drip irrigated mustard after cotton in Budak village, Hisar, 20.03.24.jpg',
			'description' => 'Harvesting Paired row drip irrigated mustard after cotton in Budak village, Hisar, 20.03.24',
		),
		5 => array(
			'file'        => 'Citrus intercropped with drip irrigated mustard, Arya Nagar, Hisar, 18.01.2024.jpg',
			'description' => 'Citrus intercropped with drip irrigated mustard, Arya Nagar, Hisar, 18.01.2024',
		),
		6 => array(
			'file'        => 'Mini sprinkler irrigated wheat field, Khairampur, Hisar, 07.03.24.jpg',
			'description' => 'Mini sprinkler irrigated wheat field, Khairampur, Hisar, 07.03.24',
		),
	);

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload_dir = wp_upload_dir();
	$target_dir = $upload_dir['path'];

	foreach ( $banners_data as $order => $data ) {
		$source_path = $source_dir . '/' . $data['file'];
		if ( ! file_exists( $source_path ) ) {
			continue;
		}

		$filename   = wp_unique_filename( $target_dir, $data['file'] );
		$target_file = $target_dir . '/' . $filename;
		copy( $source_path, $target_file );

		$filetype = wp_check_filetype( $filename, null );
		$attachment = array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => sanitize_text_field( $data['description'] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $target_file );
		if ( ! is_wp_error( $attach_id ) && $attach_id > 0 ) {
			$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// Insert micada_banner post
			$post_id = wp_insert_post( array(
				'post_title'  => $data['description'],
				'post_status' => 'publish',
				'post_type'   => 'micada_banner',
				'menu_order'  => $order,
			) );

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				set_post_thumbnail( $post_id, $attach_id );
				update_post_meta( $post_id, '_banner_description', $data['description'] );
			}
		}
	}
}
}

add_action( 'init', 'micada_seed_banners_if_empty', 5 );

/**
 * Enqueue Media scripts & Dashicons for micada_banner admin screens
 */
if ( ! function_exists( 'micada_banner_admin_scripts' ) ) {
function micada_banner_admin_scripts( $hook ) {
	global $post_type;
	if ( ( 'post.php' === $hook || 'post-new.php' === $hook || 'edit.php' === $hook ) && 'micada_banner' === $post_type ) {
		wp_enqueue_media();
		wp_enqueue_style( 'dashicons' );
	}
}
}

add_action( 'admin_enqueue_scripts', 'micada_banner_admin_scripts' );

/**
 * Add Meta Box for Banner Image & Details
 */
if ( ! function_exists( 'micada_banner_meta_boxes' ) ) {
function micada_banner_meta_boxes() {
	add_meta_box(
		'micada_banner_details',
		__( 'Banner Image & Caption', 'micada' ),
		'micada_banner_meta_box_render',
		'micada_banner',
		'normal',
		'high'
	);
}
}

add_action( 'add_meta_boxes', 'micada_banner_meta_boxes' );

if ( ! function_exists( 'micada_banner_meta_box_render' ) ) {
function micada_banner_meta_box_render( $post ) {
	wp_nonce_field( 'micada_banner_save', 'micada_banner_nonce' );

	$thumbnail_id = get_post_thumbnail_id( $post->ID );
	$photo_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'full' ) : '';
	$description  = get_post_meta( $post->ID, '_banner_description', true ) ?: $post->post_title;
	$order        = (int) $post->menu_order;
	?>
	<style>
		.micada-banner-card {
			padding: 20px;
			background: #fbfbfb;
			border: 2px dashed #c3c4c7;
			border-radius: 8px;
			margin-bottom: 22px;
		}
		.micada-banner-preview-box {
			width: 100%;
			max-width: 650px;
			min-height: 180px;
			border-radius: 8px;
			overflow: hidden;
			background: #eee;
			border: 2px solid #ddd;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 15px;
			box-shadow: 0 3px 10px rgba(0,0,0,0.08);
		}
		.micada-banner-preview-box img {
			width: 100%;
			height: auto;
			aspect-ratio: 3/1;
			object-fit: cover;
			display: block;
		}
		.micada-banner-preview-box .no-banner-icon {
			font-size: 50px;
			color: #aaa;
			width: 50px;
			height: 50px;
		}
	</style>

	<div class="micada-banner-card">
		<div class="micada-banner-preview-box" id="micada-banner-preview">
			<?php if ( $photo_url ) : ?>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php esc_attr_e( 'Banner', 'micada' ); ?>" id="micada-banner-img">
			<?php else : ?>
				<span class="dashicons dashicons-format-image no-banner-icon" id="micada-banner-placeholder"></span>
			<?php endif; ?>
		</div>

		<div class="micada-banner-actions">
			<input type="hidden" id="micada_banner_thumbnail_id" name="micada_banner_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">

			<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
				<!-- Edit Button (Default) -->
				<button type="button" class="button button-primary button-large" id="micada-banner-edit-btn" style="display: inline-flex; align-items: center; gap: 5px;">
					<span class="dashicons dashicons-edit"></span>
					<span id="micada-banner-edit-btn-text"><?php echo $photo_url ? esc_html__( 'Edit Image', 'micada' ) : esc_html__( 'Upload Image', 'micada' ); ?></span>
				</button>

				<!-- Save Button (Shown after picking new image) -->
				<button type="button" class="button button-primary button-large" id="micada-banner-save-btn" style="display: none; background: #007017; border-color: #005a12; align-items: center; gap: 5px;">
					<span class="dashicons dashicons-saved"></span>
					<span><?php esc_html_e( 'Save Image', 'micada' ); ?></span>
				</button>

				<!-- Cancel Button (Shown after picking new image) -->
				<button type="button" class="button button-secondary button-large" id="micada-banner-cancel-btn" style="display: none; align-items: center; gap: 5px;">
					<span class="dashicons dashicons-no"></span>
					<span><?php esc_html_e( 'Cancel', 'micada' ); ?></span>
				</button>

				<!-- Remove Button -->
				<button type="button" class="button button-link-delete" id="micada-banner-remove-btn" style="<?php echo $photo_url ? 'display: inline-flex;' : 'display: none;'; ?> align-items: center; gap: 4px; margin-left: 10px; font-weight: 600;">
					<span class="dashicons dashicons-trash"></span>
					<span><?php esc_html_e( 'Remove Photo', 'micada' ); ?></span>
				</button>
			</div>

			<!-- Status Notification Area -->
			<div id="micada-banner-status" style="margin-top: 10px; display: none;"></div>

			<p style="margin-top: 8px; color: #646970; font-size: 13px;">
				<?php esc_html_e( 'Choose a 3:1 banner, 1200×400 to 8000×2700 px. Images outside this range may not display correctly.', 'micada' ); ?>
			</p>
		</div>
	</div>

	<!-- FORM FIELDS -->
	<table class="form-table" style="margin-top: 0;">
		<tr>
			<th scope="row" style="width: 180px;"><label for="banner_description"><strong><?php esc_html_e( 'Caption / Description', 'micada' ); ?></strong></label></th>
			<td>
				<input type="text" id="banner_description" name="banner_description" value="<?php echo esc_attr( $description ); ?>" class="large-text" placeholder="e.g. Citrus intercropped with drip irrigated mustard, Arya Nagar, Hisar, 18.01.2024">
				<p class="description"><?php esc_html_e( 'The description displayed below this banner slide on the homepage.', 'micada' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="banner_menu_order"><strong><?php esc_html_e( 'Display Order', 'micada' ); ?></strong></label></th>
			<td>
				<input type="number" id="banner_menu_order" name="banner_menu_order" value="<?php echo esc_attr( $order ); ?>" class="small-text" min="1" step="1" style="width: 80px;">
				<p class="description"><?php esc_html_e( 'The sequence order of this slide (1 = First slide, 2 = Second slide, etc.).', 'micada' ); ?></p>
			</td>
		</tr>
	</table>

	<!-- JAVASCRIPT FOR EDIT, SAVE & CANCEL -->
	<script>
	jQuery(document).ready(function($) {
		var bannerUploader;
		var originalId  = $('#micada_banner_thumbnail_id').val();
		var originalUrl = $('#micada-banner-img').attr('src') || '';
		var pendingId   = originalId;
		var pendingUrl  = originalUrl;

		// 1. Click "Edit Image" -> Open Media Library
		$('#micada-banner-edit-btn').on('click', function(e) {
			e.preventDefault();
			if (bannerUploader) {
				bannerUploader.open();
				return;
			}
			bannerUploader = wp.media({
				title: '<?php echo esc_js( __( 'Select Banner', 'micada' ) ); ?>',
				button: {
					text: '<?php echo esc_js( __( 'Choose This Image', 'micada' ) ); ?>'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			bannerUploader.on('select', function() {
				var attachment = bannerUploader.state().get('selection').first().toJSON();
				var width = parseInt(attachment.width, 10) || 0;
				var height = parseInt(attachment.height, 10) || 0;
				var ratio = height ? width / height : 0;
				if (width < 1200 || height < 400 || width > 8000 || height > 2700 || ratio < 2.85 || ratio > 3.15) {
					window.micadaShowImageSizeDialog('<?php echo esc_js( __( 'This banner may not display correctly. Choose a 3:1 image between 1200×400 and 8000×2700 pixels.', 'micada' ) ); ?>');
					return;
				}
				pendingId  = attachment.id;
				pendingUrl = attachment.url;

				// Update preview image
			$('#micada-banner-preview').html('<img src="' + pendingUrl + '" alt="Banner" id="micada-banner-img">');

				// Toggle buttons: Hide Edit, Show Save & Cancel!
				$('#micada-banner-edit-btn').hide();
				$('#micada-banner-remove-btn').hide();
				$('#micada-banner-save-btn').css('display', 'inline-flex');
				$('#micada-banner-cancel-btn').css('display', 'inline-flex');

				// Show pending status alert
				$('#micada-banner-status').html('<span style="color:#d63638;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-info"></span> <?php echo esc_js( __( 'New picture chosen. Click "Save Image" to apply to database, or "Cancel" to revert.', 'micada' ) ); ?></span>').show();
			});

			bannerUploader.open();
		});

		// 2. Click "Save Image" -> Save immediately via AJAX to database
		$('#micada-banner-save-btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving to Database...', 'micada' ) ); ?>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'micada_quick_save_banner',
					post_id: <?php echo (int) $post->ID; ?>,
					thumbnail_id: pendingId,
					description: $('#banner_description').val(),
					nonce: $('#micada_banner_nonce').val()
				},
				success: function(response) {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> <span><?php echo esc_js( __( 'Save Image', 'micada' ) ); ?></span>');
					if (response.success) {
						originalId  = pendingId;
						originalUrl = response.data.url;
						$('#micada_banner_thumbnail_id').val(originalId);

						// Switch back to Edit button
						$('#micada-banner-save-btn').hide();
						$('#micada-banner-cancel-btn').hide();
						$('#micada-banner-edit-btn').css('display', 'inline-flex');
						$('#micada-banner-edit-btn-text').text('<?php echo esc_js( __( 'Edit Image', 'micada' ) ); ?>');
						if (originalId) {
							$('#micada-banner-remove-btn').css('display', 'inline-flex');
						}

						// Success banner
						$('#micada-banner-status').html('<span style="color:#007017;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Slider image saved successfully to database!', 'micada' ) ); ?></span>').show();
						setTimeout(function() {
							$('#micada-banner-status').fadeOut();
						}, 4000);
					} else {
						window.micadaShowImageSizeDialog('Error saving image: ' + (response.data || 'Unknown error'));
					}
				},
				error: function() {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> <span><?php echo esc_js( __( 'Save Image', 'micada' ) ); ?></span>');
					alert('Connection error. Please try again.');
				}
			});
		});

		// 3. Click "Cancel" -> Revert to previous image
		$('#micada-banner-cancel-btn').on('click', function(e) {
			e.preventDefault();
			pendingId  = originalId;
			pendingUrl = originalUrl;
			$('#micada_banner_thumbnail_id').val(originalId);

			if (originalUrl) {
			$('#micada-banner-preview').html('<img src="' + originalUrl + '" alt="Banner" id="micada-banner-img">');
				$('#micada-banner-remove-btn').css('display', 'inline-flex');
			} else {
				$('#micada-banner-preview').html('<span class="dashicons dashicons-format-image no-banner-icon" id="micada-banner-placeholder"></span>');
				$('#micada-banner-remove-btn').hide();
			}

			// Switch back to Edit button
			$('#micada-banner-save-btn').hide();
			$('#micada-banner-cancel-btn').hide();
			$('#micada-banner-edit-btn').css('display', 'inline-flex');
			$('#micada-banner-status').hide();
		});

		// 4. Click "Remove Photo"
		$('#micada-banner-remove-btn').on('click', function(e) {
			e.preventDefault();
			if (confirm('<?php echo esc_js( __( 'Are you sure you want to remove this slider photo?', 'micada' ) ); ?>')) {
				pendingId  = 0;
				pendingUrl = '';
				$('#micada-banner-preview').html('<span class="dashicons dashicons-format-image no-banner-icon" id="micada-banner-placeholder"></span>');

				// Show Save & Cancel to confirm removal
				$('#micada-banner-edit-btn').hide();
				$('#micada-banner-remove-btn').hide();
				$('#micada-banner-save-btn').css('display', 'inline-flex');
				$('#micada-banner-cancel-btn').css('display', 'inline-flex');
				$('#micada-banner-status').html('<span style="color:#d63638;font-weight:600;"><span class="dashicons dashicons-warning"></span> <?php echo esc_js( __( 'Photo removed. Click "Save Image" to apply.', 'micada' ) ); ?></span>').show();
			}
		});
	});
	</script>
	<?php
}
}


/**
 * AJAX Quick Save Banner Handler
 */
if ( ! function_exists( 'micada_ajax_save_banner_photo' ) ) {
function micada_ajax_save_banner_photo() {
	check_ajax_referer( 'micada_banner_save', 'nonce' );

	$post_id      = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$thumbnail_id = isset( $_POST['thumbnail_id'] ) ? absint( $_POST['thumbnail_id'] ) : 0;
	$description  = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';

	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( 'Permission denied.' );
	}

	if ( $thumbnail_id > 0 ) {
		$dimension_error = micada_validate_image_attachment_dimensions( $thumbnail_id, 'banner' );
		if ( is_wp_error( $dimension_error ) ) {
			wp_send_json_error( $dimension_error->get_error_message() );
		}
		set_post_thumbnail( $post_id, $thumbnail_id );
		$url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
	} else {
		delete_post_thumbnail( $post_id );
		$url = '';
	}

	if ( $description ) {
		update_post_meta( $post_id, '_banner_description', $description );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => $description,
		) );
	}

	wp_send_json_success( array(
		'message' => __( 'Slider image saved successfully!', 'micada' ),
		'url'     => $url,
		'id'      => $thumbnail_id,
	) );
}
}

add_action( 'wp_ajax_micada_quick_save_banner', 'micada_ajax_save_banner_photo' );

/**
 * Save Meta Box Fields & Photo via Standard Post Update
 */
if ( ! function_exists( 'micada_save_banner_meta' ) ) {
function micada_save_banner_meta( $post_id ) {
	if ( ! isset( $_POST['micada_banner_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['micada_banner_nonce'] ) ), 'micada_banner_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save Description
	if ( isset( $_POST['banner_description'] ) ) {
		$desc = sanitize_text_field( wp_unslash( $_POST['banner_description'] ) );
		update_post_meta( $post_id, '_banner_description', $desc );
		remove_action( 'save_post_micada_banner', 'micada_save_banner_meta' );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => $desc,
		) );
		add_action( 'save_post_micada_banner', 'micada_save_banner_meta' );
	}

	// Save Photo
	if ( isset( $_POST['micada_banner_thumbnail_id'] ) ) {
		$thumb_id = absint( $_POST['micada_banner_thumbnail_id'] );
		if ( $thumb_id > 0 ) {
			if ( ! is_wp_error( micada_validate_image_attachment_dimensions( $thumb_id, 'banner' ) ) ) {
				set_post_thumbnail( $post_id, $thumb_id );
			}
		} else {
			delete_post_thumbnail( $post_id );
		}
	}

	// Save Display Order
	if ( isset( $_POST['banner_menu_order'] ) ) {
		$new_order = absint( $_POST['banner_menu_order'] );
		remove_action( 'save_post_micada_banner', 'micada_save_banner_meta' );
		wp_update_post( array(
			'ID'         => $post_id,
			'menu_order' => $new_order,
		) );
		add_action( 'save_post_micada_banner', 'micada_save_banner_meta' );
	}
}
}

add_action( 'save_post_micada_banner', 'micada_save_banner_meta' );

/**
 * Admin Columns in Banners list table
 */
if ( ! function_exists( 'micada_banner_columns' ) ) {
function micada_banner_columns( $columns ) {
	$new = array();
	$new['cb']          = $columns['cb'];
	$new['photo']       = __( 'Preview', 'micada' );
	$new['title']       = __( 'Caption / Description', 'micada' );
	$new['order']       = __( 'Order', 'micada' );
	$new['date']        = $columns['date'];
	return $new;
}
}

add_filter( 'manage_micada_banner_posts_columns', 'micada_banner_columns' );

if ( ! function_exists( 'micada_banner_custom_column' ) ) {
function micada_banner_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'photo':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 100, 35 ), array( 'style' => 'width: 100px; height: 35px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' ) );
			} else {
				echo '<span style="color:#999;font-size:12px;">' . esc_html__( 'No image', 'micada' ) . '</span>';
			}
			break;
		case 'order':
			$post = get_post( $post_id );
			echo '<strong>' . esc_html( (string) $post->menu_order ) . '</strong>';
			break;
	}
}
}

add_action( 'manage_micada_banner_posts_custom_column', 'micada_banner_custom_column', 10, 2 );

/**
 * Custom column widths and alignment for Banners list table
 */
if ( ! function_exists( 'micada_banner_admin_table_styles' ) ) {
function micada_banner_admin_table_styles() {
	global $post_type;
	if ( 'micada_banner' !== $post_type ) {
		return;
	}
	?>
	<style>
		.post-type-micada_banner .wp-list-table th#photo,
		.post-type-micada_banner .wp-list-table td.column-photo {
			width: 125px !important;
			text-align: center;
			vertical-align: middle;
			padding-left: 8px;
			padding-right: 8px;
		}
		.post-type-micada_banner .wp-list-table td.column-photo img {
			display: inline-block;
			vertical-align: middle;
		}
		.post-type-micada_banner .wp-list-table th#title,
		.post-type-micada_banner .wp-list-table td.column-title {
			width: 55%;
			vertical-align: middle;
		}
		.post-type-micada_banner .wp-list-table th#order,
		.post-type-micada_banner .wp-list-table td.column-order {
			width: 90px;
			text-align: center;
			vertical-align: middle;
		}
		.post-type-micada_banner .wp-list-table th#date,
		.post-type-micada_banner .wp-list-table td.column-date {
			width: 20%;
			vertical-align: middle;
		}
	</style>
	<?php
}
}

add_action( 'admin_head', 'micada_banner_admin_table_styles' );

/**
 * Retrieve all published slider banners from database.
 * Theme-independent: Works with any active theme.
 */
if ( ! function_exists( 'micada_get_banners' ) ) {
function micada_get_banners() {
	$query = new WP_Query( array(
		'post_type'      => 'micada_banner',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
		'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
	) );

	if ( ! $query->have_posts() ) {
		return array();
	}

	$banners = array();
	foreach ( $query->posts as $post ) {
		$image = get_the_post_thumbnail_url( $post->ID, 'full' );
		if ( ! $image ) {
			continue;
		}
		$desc = get_post_meta( $post->ID, '_banner_description', true ) ?: $post->post_title;
		$banners[] = array(
			'id'              => $post->ID,
			'previewImageSrc' => $image,
			'description'     => $desc,
			'order'           => $post->menu_order,
		);
	}
	return $banners;
}
}

