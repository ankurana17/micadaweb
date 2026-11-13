<?php
/**
 * Module: Officials
 * Description: Manages leadership cards and official photos.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Custom Post Type: Officials Images
 */
if ( ! function_exists( 'micada_register_officials_post_type' ) ) {
function micada_register_officials_post_type() {
	$labels = array(
		'name'               => _x( "Official's Images", 'Post type general name', 'micada' ),
		'singular_name'      => _x( 'Official Image', 'Post type singular name', 'micada' ),
		'menu_name'          => _x( "Official's Images", 'Admin Menu text', 'micada' ),
		'name_admin_bar'     => _x( 'Official Image', 'Add New on Toolbar', 'micada' ),
		'add_new'            => __( 'Add New Official', 'micada' ),
		'add_new_item'       => __( 'Add New Official Image', 'micada' ),
		'new_item'           => __( 'New Official Image', 'micada' ),
		'edit_item'          => __( 'Edit Official', 'micada' ),
		'view_item'          => __( 'View Official', 'micada' ),
		'all_items'          => __( "All Official's Images", 'micada' ),
		'search_items'       => __( 'Search Officials', 'micada' ),
		'not_found'          => __( 'No officials found.', 'micada' ),
		'not_found_in_trash' => __( 'No officials found in Trash.', 'micada' ),
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
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-groups',
		// Photo & Order are cleanly managed in the top Meta Box.
		'supports'           => array( 'title' ),
		'show_in_rest'       => false,
	);

	register_post_type( 'micada_official', $args );
}
}

add_action( 'init', 'micada_register_officials_post_type', 0 );

/**
 * Enqueue WordPress Media Uploader scripts & Dashicons for micada_official admin edit screen
 */
if ( ! function_exists( 'micada_official_admin_scripts' ) ) {
function micada_official_admin_scripts( $hook ) {
	global $post_type;
	if ( ( 'post.php' === $hook || 'post-new.php' === $hook || 'edit.php' === $hook ) && 'micada_official' === $post_type ) {
		wp_enqueue_media();
		wp_enqueue_style( 'dashicons' );
	}
}
}

add_action( 'admin_enqueue_scripts', 'micada_official_admin_scripts' );

/** Show a persistent label for the WordPress title field on Official edit screens. */
if ( ! function_exists( 'micada_official_title_label' ) ) {
function micada_official_title_label( $post ) {
	if ( ! $post || 'micada_official' !== $post->post_type ) {
		return;
	}
	echo '<p class="micada-official-title-label" style="margin:0 0 6px"><label for="title" style="font-size:16px;font-weight:700;line-height:1.4;color:#1d2327"><strong>' . esc_html__( 'Name of the Official', 'micada' ) . '</strong></label></p>';
}
}

add_action( 'edit_form_top', 'micada_official_title_label' );

if ( ! function_exists( 'micada_official_title_placeholder' ) ) {
function micada_official_title_placeholder( $text, $post ) {
	return $post && 'micada_official' === $post->post_type ? __( 'Name of the Official', 'micada' ) : $text;
}
}

add_filter( 'enter_title_here', 'micada_official_title_placeholder', 10, 2 );

/**
 * Add Main Meta Box for Official Photo & Details
 */
if ( ! function_exists( 'micada_official_meta_boxes' ) ) {
function micada_official_meta_boxes() {
	add_meta_box(
		'micada_official_details',
		__( 'Official Photo & Details', 'micada' ),
		'micada_official_meta_box_render',
		'micada_official',
		'normal',
		'high'
	);
}
}

add_action( 'add_meta_boxes', 'micada_official_meta_boxes' );

if ( ! function_exists( 'micada_official_meta_box_render' ) ) {
function micada_official_meta_box_render( $post ) {
	wp_nonce_field( 'micada_official_save', 'micada_official_nonce' );

	$thumbnail_id = get_post_thumbnail_id( $post->ID );
	$photo_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';
	$name_hi      = get_post_meta( $post->ID, '_official_name_hi', true );
	$designation  = get_post_meta( $post->ID, '_official_designation', true );
	$designation_hi = get_post_meta( $post->ID, '_official_designation_hi', true );
	$department   = get_post_meta( $post->ID, '_official_department', true );
	$department_hi = get_post_meta( $post->ID, '_official_department_hi', true );
	$order        = (int) $post->menu_order;
	?>
	<style>
		.micada-photo-card {
			display: flex;
			align-items: center;
			gap: 25px;
			padding: 20px;
			background: #fbfbfb;
			border: 2px dashed #c3c4c7;
			border-radius: 8px;
			margin-bottom: 22px;
		}
		.micada-photo-avatar {
			width: 120px;
			height: 120px;
			border-radius: 50%;
			overflow: hidden;
			border: 3px solid #f2c9ce;
			background: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
			box-shadow: 0 3px 8px rgba(0,0,0,0.1);
		}
		.micada-photo-avatar img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}
		.micada-photo-avatar .no-photo-icon {
			font-size: 44px;
			color: #aaa;
			width: 44px;
			height: 44px;
		}
		.micada-photo-actions h3 {
			margin: 0 0 10px 0;
			font-size: 15px;
			font-weight: 600;
			color: #1d2327;
		}
	</style>

	<!-- TOP PHOTO SECTION WITH EDIT, SAVE & CANCEL -->
	<div class="micada-photo-card">
		<div class="micada-photo-avatar" id="micada-avatar-preview">
			<?php if ( $photo_url ) : ?>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php esc_attr_e( 'Official Photo', 'micada' ); ?>" id="micada-avatar-img">
			<?php else : ?>
				<span class="dashicons dashicons-businessman no-photo-icon" id="micada-avatar-placeholder"></span>
			<?php endif; ?>
		</div>
		<div class="micada-photo-actions">
			<h3><?php esc_html_e( 'Official Photo (Displayed on Homepage)', 'micada' ); ?></h3>
			<input type="hidden" id="micada_thumbnail_id" name="micada_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
			
			<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
				<!-- Edit Button (Default) -->
				<button type="button" class="button button-primary button-large" id="micada-edit-btn" style="display: inline-flex; align-items: center; gap: 5px;">
					<span class="dashicons dashicons-edit"></span>
					<span id="micada-edit-btn-text"><?php echo $photo_url ? esc_html__( 'Edit Image', 'micada' ) : esc_html__( 'Upload Image', 'micada' ); ?></span>
				</button>

				<!-- Save Button (Shown after picking new image) -->
				<button type="button" class="button button-primary button-large" id="micada-save-btn" style="display: none; background: #007017; border-color: #005a12; align-items: center; gap: 5px;">
					<span class="dashicons dashicons-saved"></span>
					<span><?php esc_html_e( 'Save Image', 'micada' ); ?></span>
				</button>

				<!-- Cancel Button (Shown after picking new image) -->
				<button type="button" class="button button-secondary button-large" id="micada-cancel-btn" style="display: none; align-items: center; gap: 5px;">
					<span class="dashicons dashicons-no"></span>
					<span><?php esc_html_e( 'Cancel', 'micada' ); ?></span>
				</button>

				<!-- Remove Button -->
				<button type="button" class="button button-link-delete" id="micada-remove-btn" style="<?php echo $photo_url ? 'display: inline-flex;' : 'display: none;'; ?> align-items: center; gap: 4px; margin-left: 10px; font-weight: 600;">
					<span class="dashicons dashicons-trash"></span>
					<span><?php esc_html_e( 'Remove Photo', 'micada' ); ?></span>
				</button>
			</div>

			<!-- Status Notification Area -->
			<div id="micada-photo-status" style="margin-top: 10px; display: none;"></div>

			<p style="margin-top: 8px; color: #646970; font-size: 13px;">
				<?php esc_html_e( 'Choose a near-square photo, 250×250 to 4000×4000 px. Images outside this range may be cropped or display incorrectly.', 'micada' ); ?>
			</p>
		</div>
	</div>

	<!-- DETAILS FORM FIELDS -->
	<table class="form-table" style="margin-top: 0;">
		<tr>
			<th scope="row" style="width: 180px;"><label for="official_name_hi"><strong><?php esc_html_e( 'Official Name (Hindi)', 'micada' ); ?></strong></label></th>
			<td>
				<input type="text" id="official_name_hi" name="official_name_hi" value="<?php echo esc_attr( $name_hi ); ?>" class="large-text" lang="hi" placeholder="Enter name in Hindi">
				<p class="description"><?php esc_html_e( 'Shown when visitors select Hindi. Leave blank to use the English name unless a built-in Hindi translation is available.', 'micada' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="official_designation_hi"><strong><?php esc_html_e( 'Designation / Title (Hindi)', 'micada' ); ?></strong></label></th>
			<td>
				<input type="text" id="official_designation_hi" name="official_designation_hi" value="<?php echo esc_attr( $designation_hi ); ?>" class="large-text" lang="hi" placeholder="Enter designation in Hindi">
				<p class="description"><?php esc_html_e( 'Shown below the name when visitors select Hindi.', 'micada' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="official_department_hi"><strong><?php esc_html_e( 'Department / Location (Hindi)', 'micada' ); ?></strong></label></th>
			<td>
				<input type="text" id="official_department_hi" name="official_department_hi" value="<?php echo esc_attr( $department_hi ); ?>" class="large-text" lang="hi" placeholder="Enter department in Hindi">
				<p class="description"><?php esc_html_e( 'Shown as the department or location when visitors select Hindi.', 'micada' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" style="width: 180px;"><label for="official_designation"><strong><?php esc_html_e( 'Designation / Title', 'micada' ); ?></strong></label></th>
			<td>
				<input type="text" id="official_designation" name="official_designation" value="<?php echo esc_attr( $designation ); ?>" class="large-text" placeholder="e.g. Hon'ble Chief Minister">
				<p class="description"><?php esc_html_e( 'The title or designation displayed below the official’s name.', 'micada' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="official_department"><strong><?php esc_html_e( 'Department / Location', 'micada' ); ?></strong></label></th>
			<td>
				<input type="text" id="official_department" name="official_department" value="<?php echo esc_attr( $department ); ?>" class="large-text" placeholder="e.g. HARYANA or I&WRD Haryana">
				<p class="description"><?php esc_html_e( 'The state or department name shown in grey capital text.', 'micada' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="official_menu_order"><strong><?php esc_html_e( 'Display Order', 'micada' ); ?></strong></label></th>
			<td>
				<input type="number" id="official_menu_order" name="official_menu_order" value="<?php echo esc_attr( $order ); ?>" class="small-text" min="1" step="1" style="width: 80px;">
				<p class="description"><?php esc_html_e( 'The display position on the homepage (1 = Top / Chief Minister, 2 = Second, 3 = Third, 4 = Fourth).', 'micada' ); ?></p>
			</td>
		</tr>
	</table>

	<!-- MEDIA UPLOADER & EDIT / SAVE / CANCEL JAVASCRIPT -->
	<script>
	jQuery(document).ready(function($) {
		var mediaUploader;
		var originalId  = $('#micada_thumbnail_id').val();
		var originalUrl = $('#micada-avatar-img').attr('src') || '';
		var pendingId   = originalId;
		var pendingUrl  = originalUrl;

		// 1. Click "Edit Image" -> Open Media Library
		$('#micada-edit-btn').on('click', function(e) {
			e.preventDefault();
			if (mediaUploader) {
				mediaUploader.open();
				return;
			}
			mediaUploader = wp.media({
				title: '<?php echo esc_js( __( 'Select Official Photo', 'micada' ) ); ?>',
				button: {
					text: '<?php echo esc_js( __( 'Choose This Photo', 'micada' ) ); ?>'
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				var width = parseInt(attachment.width, 10) || 0;
				var height = parseInt(attachment.height, 10) || 0;
				var ratio = height ? width / height : 0;
				if (width < 250 || height < 250 || width > 4000 || height > 4000 || ratio < 0.80 || ratio > 1.25) {
					window.micadaShowImageSizeDialog('<?php echo esc_js( __( 'This photo may be cropped or display incorrectly. Choose a near-square official photo between 250×250 and 4000×4000 pixels.', 'micada' ) ); ?>');
					return;
				}
				pendingId  = attachment.id;
				pendingUrl = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;

				// Update preview image
				$('#micada-avatar-preview').html('<img src="' + pendingUrl + '" alt="Official Photo" id="micada-avatar-img">');

				// Toggle buttons: Hide Edit, Show Save & Cancel!
				$('#micada-edit-btn').hide();
				$('#micada-remove-btn').hide();
				$('#micada-save-btn').css('display', 'inline-flex');
				$('#micada-cancel-btn').css('display', 'inline-flex');

				// Show pending status alert
				$('#micada-photo-status').html('<span style="color:#d63638;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-info"></span> <?php echo esc_js( __( 'New picture chosen. Click "Save Image" to apply to database, or "Cancel" to revert.', 'micada' ) ); ?></span>').show();
			});

			mediaUploader.open();
		});

		// 2. Click "Save Image" -> Save immediately via AJAX to database
		$('#micada-save-btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving to Database...', 'micada' ) ); ?>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'micada_quick_save_photo',
					post_id: <?php echo (int) $post->ID; ?>,
					thumbnail_id: pendingId,
					nonce: $('#micada_official_nonce').val()
				},
				success: function(response) {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="margin-top: 4px; margin-right: 4px;"></span> <?php echo esc_js( __( 'Save Image', 'micada' ) ); ?>');
					if (response.success) {
						originalId  = pendingId;
						originalUrl = response.data.url;
						$('#micada_thumbnail_id').val(originalId);

						// Switch back to Edit button
						$('#micada-save-btn').hide();
						$('#micada-cancel-btn').hide();
						$('#micada-edit-btn').css('display', 'inline-flex');
						$('#micada-edit-btn-text').text('<?php echo esc_js( __( 'Edit Image', 'micada' ) ); ?>');
						if (originalId) {
							$('#micada-remove-btn').css('display', 'inline-flex');
						}

						// Success banner
						$('#micada-photo-status').html('<span style="color:#007017;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Image saved successfully to database!', 'micada' ) ); ?></span>').show();
						setTimeout(function() {
							$('#micada-photo-status').fadeOut();
						}, 4000);
					} else {
						window.micadaShowImageSizeDialog('Error saving image: ' + (response.data || 'Unknown error'));
					}
				},
				error: function() {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="margin-top: 4px; margin-right: 4px;"></span> <?php echo esc_js( __( 'Save Image', 'micada' ) ); ?>');
					alert('Connection error. Please try again.');
				}
			});
		});

		// 3. Click "Cancel" -> Revert to previous image
		$('#micada-cancel-btn').on('click', function(e) {
			e.preventDefault();
			pendingId  = originalId;
			pendingUrl = originalUrl;
			$('#micada_thumbnail_id').val(originalId);

			if (originalUrl) {
				$('#micada-avatar-preview').html('<img src="' + originalUrl + '" alt="Official Photo" id="micada-avatar-img">');
				$('#micada-remove-btn').css('display', 'inline-flex');
			} else {
				$('#micada-avatar-preview').html('<span class="dashicons dashicons-businessman no-photo-icon" id="micada-avatar-placeholder"></span>');
				$('#micada-remove-btn').hide();
			}

			// Switch back to Edit button
			$('#micada-save-btn').hide();
			$('#micada-cancel-btn').hide();
			$('#micada-edit-btn').css('display', 'inline-flex');
			$('#micada-photo-status').hide();
		});

		// 4. Click "Remove Photo"
		$('#micada-remove-btn').on('click', function(e) {
			e.preventDefault();
			if (confirm('<?php echo esc_js( __( 'Are you sure you want to remove this photo?', 'micada' ) ); ?>')) {
				pendingId  = 0;
				pendingUrl = '';
				$('#micada-avatar-preview').html('<span class="dashicons dashicons-businessman no-photo-icon" id="micada-avatar-placeholder"></span>');

				// Show Save & Cancel to confirm removal
				$('#micada-edit-btn').hide();
				$('#micada-remove-btn').hide();
				$('#micada-save-btn').css('display', 'inline-flex');
				$('#micada-cancel-btn').css('display', 'inline-flex');
				$('#micada-photo-status').html('<span style="color:#d63638;font-weight:600;"><span class="dashicons dashicons-warning"></span> <?php echo esc_js( __( 'Photo removed. Click "Save Image" to apply.', 'micada' ) ); ?></span>').show();
			}
		});
	});
	</script>
	<?php
}
}


/**
 * AJAX Quick Save Photo Handler
 */
if ( ! function_exists( 'micada_ajax_save_official_photo' ) ) {
function micada_ajax_save_official_photo() {
	check_ajax_referer( 'micada_official_save', 'nonce' );

	$post_id      = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$thumbnail_id = isset( $_POST['thumbnail_id'] ) ? absint( $_POST['thumbnail_id'] ) : 0;

	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( 'Permission denied.' );
	}

	if ( $thumbnail_id > 0 ) {
		$dimension_error = micada_validate_image_attachment_dimensions( $thumbnail_id, 'official' );
		if ( is_wp_error( $dimension_error ) ) {
			wp_send_json_error( $dimension_error->get_error_message() );
		}
		set_post_thumbnail( $post_id, $thumbnail_id );
		$url = wp_get_attachment_image_url( $thumbnail_id, 'medium' );
	} else {
		delete_post_thumbnail( $post_id );
		$url = '';
	}

	wp_send_json_success( array(
		'message' => __( 'Photo saved successfully!', 'micada' ),
		'url'     => $url,
		'id'      => $thumbnail_id,
	) );
}
}

add_action( 'wp_ajax_micada_quick_save_photo', 'micada_ajax_save_official_photo' );

/**
 * Save Meta Box Fields & Photo via Standard Post Update
 */
if ( ! function_exists( 'micada_save_official_meta' ) ) {
function micada_save_official_meta( $post_id ) {
	if ( ! isset( $_POST['micada_official_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['micada_official_nonce'] ) ), 'micada_official_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['official_name_hi'] ) ) {
		update_post_meta( $post_id, '_official_name_hi', sanitize_text_field( wp_unslash( $_POST['official_name_hi'] ) ) );
	}
	if ( isset( $_POST['official_designation_hi'] ) ) {
		update_post_meta( $post_id, '_official_designation_hi', sanitize_text_field( wp_unslash( $_POST['official_designation_hi'] ) ) );
	}
	if ( isset( $_POST['official_department_hi'] ) ) {
		update_post_meta( $post_id, '_official_department_hi', sanitize_text_field( wp_unslash( $_POST['official_department_hi'] ) ) );
	}

	// Save Designation
	if ( isset( $_POST['official_designation'] ) ) {
		update_post_meta( $post_id, '_official_designation', sanitize_text_field( wp_unslash( $_POST['official_designation'] ) ) );
	}

	// Save Department
	if ( isset( $_POST['official_department'] ) ) {
		update_post_meta( $post_id, '_official_department', sanitize_text_field( wp_unslash( $_POST['official_department'] ) ) );
	}

	// Save Photo (Featured Image / Thumbnail)
	if ( isset( $_POST['micada_thumbnail_id'] ) ) {
		$thumb_id = absint( $_POST['micada_thumbnail_id'] );
		if ( $thumb_id > 0 ) {
			if ( ! is_wp_error( micada_validate_image_attachment_dimensions( $thumb_id, 'official' ) ) ) {
				set_post_thumbnail( $post_id, $thumb_id );
			}
		} else {
			delete_post_thumbnail( $post_id );
		}
	}

	// Save Display Order
	if ( isset( $_POST['official_menu_order'] ) ) {
		$new_order = absint( $_POST['official_menu_order'] );
		remove_action( 'save_post_micada_official', 'micada_save_official_meta' );
		wp_update_post( array(
			'ID'         => $post_id,
			'menu_order' => $new_order,
		) );
		add_action( 'save_post_micada_official', 'micada_save_official_meta' );
	}
}
}

add_action( 'save_post_micada_official', 'micada_save_official_meta' );

/**
 * Admin Columns in Officials Images list table
 */
if ( ! function_exists( 'micada_official_columns' ) ) {
function micada_official_columns( $columns ) {
	$new = array();
	$new['cb']          = $columns['cb'];
	$new['photo']       = __( 'Photo', 'micada' );
	$new['title']       = __( 'Official Name', 'micada' );
	$new['title_hi']    = __( 'Official Name (Hindi)', 'micada' );
	$new['designation'] = __( 'Designation', 'micada' );
	$new['designation_hi'] = __( 'Designation (Hindi)', 'micada' );
	$new['department']  = __( 'Department', 'micada' );
	$new['department_hi'] = __( 'Department (Hindi)', 'micada' );
	$new['order']       = __( 'Order', 'micada' );
	$new['date']        = $columns['date'];
	return $new;
}
}

add_filter( 'manage_micada_official_posts_columns', 'micada_official_columns' );

if ( ! function_exists( 'micada_official_custom_column' ) ) {
function micada_official_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'photo':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 50, 50 ), array( 'style' => 'width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;' ) );
			} else {
				echo '<span style="color:#999;font-size:12px;">' . esc_html__( 'No photo', 'micada' ) . '</span>';
			}
			break;
		case 'designation':
			echo esc_html( get_post_meta( $post_id, '_official_designation', true ) ?: '—' );
			break;
		case 'title_hi':
			echo esc_html( get_post_meta( $post_id, '_official_name_hi', true ) ?: '—' );
			break;
		case 'designation_hi':
			echo esc_html( get_post_meta( $post_id, '_official_designation_hi', true ) ?: '—' );
			break;
		case 'department':
			echo esc_html( get_post_meta( $post_id, '_official_department', true ) ?: '—' );
			break;
		case 'department_hi':
			echo esc_html( get_post_meta( $post_id, '_official_department_hi', true ) ?: '—' );
			break;
		case 'order':
			$post = get_post( $post_id );
			echo '<strong>' . esc_html( (string) $post->menu_order ) . '</strong>';
			break;
	}
}
}

add_action( 'manage_micada_official_posts_custom_column', 'micada_official_custom_column', 10, 2 );

/**
 * Custom column widths and alignment for Officials Images list table
 */
if ( ! function_exists( 'micada_official_admin_table_styles' ) ) {
function micada_official_admin_table_styles() {
	global $post_type;
	if ( 'micada_official' !== $post_type ) {
		return;
	}
	?>
	<style>
		.post-type-micada_official .wp-list-table th#photo,
		.post-type-micada_official .wp-list-table td.column-photo {
			width: 80px !important;
			text-align: center;
			vertical-align: middle;
			padding-left: 8px;
			padding-right: 8px;
		}
		.post-type-micada_official .wp-list-table td.column-photo img {
			display: inline-block;
			vertical-align: middle;
		}
		.post-type-micada_official .wp-list-table th#title,
		.post-type-micada_official .wp-list-table td.column-title {
			width: 24%;
			vertical-align: middle;
		}
		.post-type-micada_official .wp-list-table th#designation,
		.post-type-micada_official .wp-list-table td.column-designation {
			width: 16%;
			vertical-align: middle;
		}
		.post-type-micada_official .wp-list-table th#title_hi,
		.post-type-micada_official .wp-list-table td.column-title_hi,
		.post-type-micada_official .wp-list-table th#designation_hi,
		.post-type-micada_official .wp-list-table td.column-designation_hi,
		.post-type-micada_official .wp-list-table th#department_hi,
		.post-type-micada_official .wp-list-table td.column-department_hi {
			width: 16%;
			vertical-align: middle;
		}
		.post-type-micada_official .wp-list-table th#department,
		.post-type-micada_official .wp-list-table td.column-department {
			width: 16%;
			vertical-align: middle;
		}
		.post-type-micada_official .wp-list-table th#order,
		.post-type-micada_official .wp-list-table td.column-order {
			width: 80px;
			text-align: center;
			vertical-align: middle;
		}
		.post-type-micada_official .wp-list-table th#date,
		.post-type-micada_official .wp-list-table td.column-date {
			width: 15%;
			vertical-align: middle;
		}
	</style>
	<?php
}
}

add_action( 'admin_head', 'micada_official_admin_table_styles' );

/**
 * Copy the existing homepage Hindi official text into Officials CMS metadata once.
 * The resulting values live in wp_postmeta, so removing this migration later
 * will not remove the saved translations.
 */
if ( ! function_exists( 'micada_migrate_official_hindi_fields' ) ) {
function micada_migrate_official_hindi_fields() {
	if ( get_option( 'micada_official_hindi_fields_migrated' ) ) {
		return;
	}

	$translation_file = get_stylesheet_directory() . '/assets/data/translations.json';
	if ( ! is_readable( $translation_file ) ) {
		return;
	}
	$translations = json_decode( file_get_contents( $translation_file ), true );
	if ( empty( $translations['hd'] ) || ! is_array( $translations['hd'] ) ) {
		return;
	}

	$official_translation_keys = array(
		'Sh. Nayab Singh Saini'   => array( 'CHIEF_MIN_NAME', 'CHIEF_MIN', 'HARYANA' ),
		'Smt. Shruti Choudhry'    => array( 'IRRIGATION_MIN_NAME', 'IRRIGATION_MIN', 'HARYANA' ),
		'Sh. Anurag Agarwal, IAS' => array( 'ADD_CHIEF_SEC_NAME', 'ADD_CHIEF_SEC', 'I&WRD_HARYANA' ),
		'Sh. Birender Singh'      => array( 'ADMINISTRATOR_NAME', 'ADMINISTRATOR', 'MICADA_HARYANA' ),
	);
	$query = new WP_Query( array(
		'post_type'      => 'micada_official',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	));
	if ( empty( $query->posts ) ) {
		return;
	}

	$meta_fields = array( '_official_name_hi', '_official_designation_hi', '_official_department_hi' );
	$matched = false;
	foreach ( $query->posts as $post_id ) {
		$keys = $official_translation_keys[ get_the_title( $post_id ) ] ?? array();
		if ( 3 !== count( $keys ) ) {
			continue;
		}
		$matched = true;
		foreach ( $meta_fields as $index => $meta_key ) {
			$value = $translations['hd'][ $keys[ $index ] ] ?? '';
			if ( '' !== $value && '' === get_post_meta( $post_id, $meta_key, true ) ) {
				update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
			}
		}
	}

	if ( $matched ) {
		update_option( 'micada_official_hindi_fields_migrated', 1, false );
	}
}
}

add_action( 'init', 'micada_migrate_official_hindi_fields', 20 );

/**
 * Retrieve all published officials from the database for theme display.
 * Theme-independent: Works with any active theme.
 */
if ( ! function_exists( 'micada_get_officials' ) ) {
function micada_get_officials() {
	$query = new WP_Query( array(
		'post_type'      => 'micada_official',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
		'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
	) );

	if ( ! $query->have_posts() ) {
		return array();
	}

	$officials = array();
	foreach ( $query->posts as $post ) {
		$image = get_the_post_thumbnail_url( $post->ID, 'full' );
		$name  = $post->post_title;
		$title = get_post_meta( $post->ID, '_official_designation', true );
		$location = get_post_meta( $post->ID, '_official_department', true );
		if ( function_exists( 'micada_language' ) && 'hd' === micada_language() ) {
			$known_translations = array(
				'Sh. Nayab Singh Saini' => array( 'name' => 'CHIEF_MIN_NAME', 'title' => 'CHIEF_MIN', 'location' => 'HARYANA' ),
				'Smt. Shruti Choudhry' => array( 'name' => 'IRRIGATION_MIN_NAME', 'title' => 'IRRIGATION_MIN', 'location' => 'HARYANA' ),
				'Sh. Anurag Agarwal, IAS' => array( 'name' => 'ADD_CHIEF_SEC_NAME', 'title' => 'ADD_CHIEF_SEC', 'location' => 'I&WRD_HARYANA' ),
				'Sh. Birender Singh' => array( 'name' => 'ADMINISTRATOR_NAME', 'title' => 'ADMINISTRATOR', 'location' => 'MICADA_HARYANA' ),
			);
			$translation = $known_translations[ $post->post_title ] ?? array();
			$localized_fields = array(
				'name'     => get_post_meta( $post->ID, '_official_name_hi', true ),
				'title'    => get_post_meta( $post->ID, '_official_designation_hi', true ),
				'location' => get_post_meta( $post->ID, '_official_department_hi', true ),
			);
			foreach ( $localized_fields as $field => $localized_value ) {
				if ( '' !== $localized_value ) {
					$$field = $localized_value;
				} elseif ( isset( $translation[ $field ] ) && function_exists( 'micada_text' ) ) {
					$$field = micada_text( $translation[ $field ] );
				}
			}
		}
		$officials[] = array(
			'id'       => $post->ID,
			'name'     => $name,
			'title'    => $title,
			'location' => $location,
			'image'    => $image ?: '',
			'order'    => $post->menu_order,
		);
	}
	return $officials;
}
}

