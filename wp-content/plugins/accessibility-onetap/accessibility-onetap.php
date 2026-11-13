<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wponetap.com
 * @since             1.0.0
 * @package           Accessibility_Onetap
 *
 * @wordpress-plugin
 * Plugin Name:       Accessibility Widget by OneTap – Easy One-Click Accessibility Toolbar
 * Plugin URI:        https://wponetap.com
 * Description:       OneTap is a multilingual WordPress plugin designed for seamless website accessibility. With a simple one-click installation, it ensures your site meets accessibility standards without any hassle. Built for performance, providing an inclusive, user-friendly web environment for all visitors.
 * Version:           2.14.0
 * Author:            OneTap
 * Author URI:        https://wponetap.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       accessibility-onetap
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ACCESSIBILITY_ONETAP_VERSION', '2.14.0' );
define( 'ACCESSIBILITY_ONETAP_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACCESSIBILITY_ONETAP_PLUGINS_URL', plugins_url( 'accessibility-onetap/' ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-accessibility-onetap-activator.php
 */
function accessibility_onetap_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-accessibility-onetap-activator.php';
	Accessibility_Onetap_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-accessibility-onetap-deactivator.php
 */
function accessibility_onetap_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-accessibility-onetap-deactivator.php';
	Accessibility_Onetap_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'accessibility_onetap_activate' );
register_deactivation_hook( __FILE__, 'accessibility_onetap_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-accessibility-onetap.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function accessibility_onetap_run() {

	$plugin = new Accessibility_Onetap();
	$plugin->run();
}
accessibility_onetap_run();
