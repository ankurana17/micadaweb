<?php
/**
 * Template helper functions for the Onetap plugin.
 *
 * This file contains functions that assist in loading and rendering
 * template files from the plugin's directory structure.
 *
 * @package    Accessibility_Plugin_Onetap_Pro
 * @subpackage Accessibility_Plugin_Onetap_Pro/includes
 */

/**
 * Load a template file from the plugin directory.
 *
 * @param string $relative_path Relative path to the template file,
 *                              starting from the plugin root directory.
 */
function onetap_load_template( $relative_path ) {
	// Build the full path by combining plugin directory path with the relative path.
	$path = ACCESSIBILITY_ONETAP_DIR_PATH . ltrim( $relative_path, '/' );

	// Check if the file exists before including it to avoid errors.
	if ( file_exists( $path ) ) {
		include $path; // Safely include the template file.
	}
}
