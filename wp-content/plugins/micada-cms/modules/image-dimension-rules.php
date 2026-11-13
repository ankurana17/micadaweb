<?php
/**
 * Module: MICADA Image Dimension Rules
 * Description: Validates image dimensions used by MICADA home page slots.
 * Version: 1.0.0
 * Author: MICADA
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_image_dimension_rules' ) ) {
function micada_image_dimension_rules( $slot ) {
	$rules = array(
		'official' => array(
			'min_width' => 250, 'min_height' => 250, 'max_width' => 4000, 'max_height' => 4000,
			'min_ratio' => 0.80, 'max_ratio' => 1.25,
			'guide' => __( 'Official photos must be 250–4000 px and near-square (about 1:1).', 'micada' ),
		),
		'banner' => array(
			'min_width' => 1200, 'min_height' => 400, 'max_width' => 8000, 'max_height' => 2700,
			'min_ratio' => 2.85, 'max_ratio' => 3.15,
			'guide' => __( 'Banners must be 1200×400 to 8000×2700 px and close to a 3:1 ratio.', 'micada' ),
		),

	);
	return $rules[ $slot ] ?? array();
}
}


if ( ! function_exists( 'micada_image_dimension_guidance' ) ) {
function micada_image_dimension_guidance( $slot ) {
	$rules = micada_image_dimension_rules( $slot );
	return $rules['guide'] ?? '';
}
}


if ( ! function_exists( 'micada_validate_image_file_dimensions' ) ) {
function micada_validate_image_file_dimensions( $file_path, $slot ) {
	$rules = micada_image_dimension_rules( $slot );
	$image = is_file( $file_path ) ? @getimagesize( $file_path ) : false;
	if ( ! $rules || false === $image || empty( $image[0] ) || empty( $image[1] ) ) {
		return new WP_Error( 'micada_image_dimensions', __( 'The selected file is not a readable image.', 'micada' ) );
	}

	$width  = (int) $image[0];
	$height = (int) $image[1];
	$ratio  = $width / $height;
	$valid  = $width >= $rules['min_width'] && $height >= $rules['min_height']
		&& $width <= $rules['max_width'] && $height <= $rules['max_height']
		&& $ratio >= $rules['min_ratio'] && $ratio <= $rules['max_ratio'];
	if ( ! $valid ) {
		return new WP_Error(
			'micada_image_dimensions',
			sprintf(
				/* translators: 1: width, 2: height, 3: size and shape guidance. */
				__( 'This image is %1$d×%2$d px. %3$s If you use an image outside these limits, it may not display correctly. Choose another image.', 'micada' ),
				$width,
				$height,
				$rules['guide']
			)
		);
	}
	return false;
}
}


if ( ! function_exists( 'micada_validate_image_attachment_dimensions' ) ) {
function micada_validate_image_attachment_dimensions( $attachment_id, $slot ) {
	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		return new WP_Error( 'micada_image_dimensions', __( 'Select an image from the Media Library.', 'micada' ) );
	}
	return micada_validate_image_file_dimensions( get_attached_file( $attachment_id ), $slot );
}
}


if ( ! function_exists( 'micada_validate_micada_image_upload' ) ) {
function micada_validate_micada_image_upload( $file ) {
	if ( empty( $_REQUEST['action'] ) || 'upload-attachment' !== sanitize_key( wp_unslash( $_REQUEST['action'] ) ) || empty( $file['tmp_name'] ) ) {
		return $file;
	}

	$slot    = '';
	$post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0;
	$type    = $post_id ? get_post_type( $post_id ) : '';
	if ( 'micada_official' === $type ) {
		$slot = 'official';
	} elseif ( 'micada_banner' === $type ) {
		$slot = 'banner';
	}

	if ( '' === $slot ) {
		$referer = wp_get_raw_referer();
		$query   = $referer ? wp_parse_url( $referer, PHP_URL_QUERY ) : '';
		$args    = array();
		if ( $query ) {
			parse_str( $query, $args );
		}

	}

	if ( $slot ) {
		$error = micada_validate_image_file_dimensions( $file['tmp_name'], $slot );
		if ( is_wp_error( $error ) ) {
			$file['error'] = $error->get_error_message();
		}
	}
	return $file;
}
}

add_filter( 'wp_handle_upload_prefilter', 'micada_validate_micada_image_upload', 20 );

if ( ! function_exists( 'micada_image_validation_admin_assets' ) ) {
function micada_image_validation_admin_assets( $hook ) {
	global $post_type;
	$image_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true )
		&& in_array( $post_type, array( 'micada_official', 'micada_banner' ), true );
	$popup_editor = 'toplevel_page_micada-homepage-popup' === $hook;
	if ( ! $image_editor && ! $popup_editor ) {
		return;
	}

	wp_register_script( 'micada-image-validation-dialog', false, array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'micada-image-validation-dialog' );
	wp_add_inline_script( 'micada-image-validation-dialog', "
		window.micadaShowImageSizeDialog = function(message) {
			var dialog = document.getElementById('micada-image-size-dialog');
			if (!dialog) {
				dialog = document.createElement('dialog');
				dialog.id = 'micada-image-size-dialog';
				dialog.setAttribute('aria-labelledby', 'micada-image-size-title');
				dialog.style.cssText = 'width:480px;max-width:calc(100% - 32px);border:0;border-radius:8px;padding:0;box-shadow:0 12px 40px rgba(0,0,0,.3);';
				dialog.innerHTML = '<div style=\"padding:24px 26px 20px;font:14px/1.5 system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;color:#1d2327\"><h2 id=\"micada-image-size-title\" style=\"margin:0 0 12px;font-size:20px\">Image size not supported</h2><p data-image-size-message style=\"margin:0 0 22px\"></p><div style=\"display:flex;justify-content:flex-end\"><button type=\"button\" class=\"button button-primary\" data-image-size-close>OK</button></div></div>';
				document.body.appendChild(dialog);
				dialog.querySelector('[data-image-size-close]').addEventListener('click', function() { dialog.close(); });
			}
			dialog.querySelector('[data-image-size-message]').textContent = message;
			if (typeof dialog.showModal === 'function') {
				dialog.showModal();
				dialog.querySelector('[data-image-size-close]').focus();
			} else {
				window.alert(message);
			}
		};
	" );
}
}

add_action( 'admin_enqueue_scripts', 'micada_image_validation_admin_assets' );
