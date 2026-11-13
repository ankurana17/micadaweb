<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wponetap.com
 * @since      1.0.0
 *
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/includes
 * @author     OneTap <support@wponetap.com>
 */
class Accessibility_Onetap_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Store the first activation timestamp for review banner logic.
		if ( ! get_option( 'onetap_install_timestamp', false ) ) {
			update_option( 'onetap_install_timestamp', time() );
		}
	}
}
