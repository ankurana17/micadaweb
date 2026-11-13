<?php
/**
 * Alt Text Management Functionality for OneTap Accessibility Plugin
 *
 * This file contains the admin-specific functionality for managing image alt text
 * within the OneTap accessibility plugin. It provides AJAX handlers for
 * updating alt text for images to improve accessibility.
 *
 * @link       https://wponetap.com
 * @since      1.0.0
 *
 * @package    Accessibility_Plugin_Onetap
 * @subpackage Accessibility_Plugin_Onetap/admin
 */

/**
 * Alt Text Management Class
 *
 * Handles the administrative functionality for managing image alt text.
 * This class provides methods to update alt text for images through AJAX requests,
 * ensuring proper security validation and user permission checks.
 *
 * @package    Accessibility_Plugin_Onetap
 * @subpackage Accessibility_Plugin_Onetap/admin
 * @author     OneTap <support@wponetap.com>
 */
class Onetap_Free_Alt_Text {

	/**
	 * Plugin identifier
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The unique identifier for this plugin.
	 */
	private $plugin_name;

	/**
	 * Plugin version number
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version number of this plugin.
	 */
	private $version;

	/**
	 * Constructor - Initialize the class with plugin details
	 *
	 * Sets up the plugin name and version for internal use within the class.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The unique identifier for this plugin.
	 * @param      string $version    The current version number of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Handle AJAX request to save alt text for an image.
	 *
	 * @return void
	 */
	public function handle_ajax_save_alt_text() {
		// Verify the security nonce.
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'onetap-ajax-verification' )
		) {
			wp_send_json( array( 'error' => 'Invalid nonce!' ), 400 );
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( array( 'error' => 'Unauthorized' ), 403 );
		}

		// Get and sanitize data from request.
		$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;
		$alt_text = isset( $_POST['alt_text'] ) ? sanitize_text_field( wp_unslash( $_POST['alt_text'] ) ) : '';

		// Validate image ID.
		if ( $image_id <= 0 ) {
			wp_send_json( array( 'error' => 'Invalid image ID' ), 400 );
		}

		// Check if the attachment exists and is an image.
		$attachment = get_post( $image_id );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			wp_send_json( array( 'error' => 'Image not found' ), 404 );
		}

		// Check if it's actually an image.
		$mime_type = get_post_mime_type( $image_id );
		if ( ! $mime_type || 0 !== strpos( $mime_type, 'image/' ) ) {
			wp_send_json( array( 'error' => 'File is not an image' ), 400 );
		}

		// Update the alt text.
		$result = update_post_meta( $image_id, '_wp_attachment_image_alt', $alt_text );

		if ( $result ) {
			wp_send_json(
				array(
					'success'  => true,
					'message'  => 'Alt text saved successfully',
					'alt_text' => $alt_text,
				),
				200
			);
		} else {
			wp_send_json(
				array(
					'error' => 'Failed to save alt text',
				),
				500
			);
		}
	}
}
