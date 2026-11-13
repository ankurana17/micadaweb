<?php
/**
 * Onetap Pro Config Class.
 *
 * This class manages the configuration settings for Onetap Pro.
 *
 * @link       https://wponetap.com
 * @since      1.0.0
 *
 * @package    Accessibility_Onetap_Config
 */

/**
 * Class Accessibility_Onetap_Config
 *
 * Manages configuration settings for Onetap Pro.
 */
class Accessibility_Onetap_Config {

	/**
	 * Static array property to hold configuration settings.
	 *
	 * @var array
	 */
	public static $settings = array(
		'icons'                            => 'design1',
		'size'                             => 'design-size2',
		'border'                           => 'design-border2',
		'language'                         => 'en',
		'language_toggles'                 => 'en',
		'color'                            => '#0048FE',
		'toggle-widget-button-focus-style' => 'off',
		'position_top_bottom'              => 15,
		'position_left_right'              => 15,
		'widget_position'                  => 'bottom-right',
		'position_top_bottom_tablet'       => 15,
		'position_left_right_tablet'       => 15,
		'widget_position_tablet'           => 'bottom-right',
		'position_top_bottom_mobile'       => 15,
		'position_left_right_mobile'       => 15,
		'widget_position_mobile'           => 'bottom-right',
		'hide_powered_by_onetap'           => 'off',
		'hide_on_desktop'                  => 'off',
		'hide_on_tablet'                   => 'off',
		'hide_on_mobile'                   => 'off',
		'license'                          => '',
	);

	/**
	 * Static array property to hold module configurations.
	 *
	 * @var array
	 */
	public static $modules = array(
		'accessibility_profiles' => 'off',
		'bigger_text'            => 'on',
		'highlight_links'        => 'on',
		'line_height'            => 'on',
		'readable_font'          => 'on',
		'cursor'                 => 'on',
		'text_magnifier'         => 'off',
		'dyslexic_font'          => 'off',
		'text_align'             => 'on',
		'letter_spacing'         => 'on',
		'font_weight'            => 'on',
		'dark_contrast'          => 'off',
		'light_contrast'         => 'on',
		'high_contrast'          => 'on',
		'monochrome'             => 'on',
		'saturation'             => 'off',
		'reading_line'           => 'on',
		'reading_mask'           => 'on',
		'read_page'              => 'off',
		'keyboard_navigation'    => 'off',
		'hide_images'            => 'on',
		'mute_sounds'            => 'off',
		'highlight_titles'       => 'off',
		'highlight_all'          => 'on',
		'stop_animations'        => 'on',
		'skip_to_content'        => 'on',
	);

	/**
	 * Static array property to hold general settings configurations.
	 *
	 * @var array
	 */
	public static $general_settings = array(
		'hide_powered_by_onetap' => 'off',
		'hide_alt_text_feature'  => 'off',
		'hide_on_pages'          => array(),
		'hide_on_post_types'     => array(),
		'hide_on_posts'          => array(),
	);

	/**
	 * Static method to get a setting value.
	 *
	 * @param string $key The setting key to retrieve.
	 * @return mixed The setting value or null if key doesn't exist.
	 */
	public static function get_setting( $key ) {
		return isset( self::$settings[ $key ] ) ? self::$settings[ $key ] : null; // Return setting value or null.
	}

	/**
	 * Static method to get a module configuration value.
	 *
	 * @param string $key The module key to retrieve.
	 * @return mixed The module value or null if key doesn't exist.
	 */
	public static function get_module( $key ) {
		return isset( self::$modules[ $key ] ) ? self::$modules[ $key ] : null; // Return module value or null.
	}

	/**
	 * Static method to get a general setting configuration value.
	 *
	 * @param string $key The general setting key to retrieve.
	 * @return mixed The general setting value or null if key doesn't exist.
	 */
	public static function get_general_setting( $key ) {
		return isset( self::$general_settings[ $key ] ) ? self::$general_settings[ $key ] : null; // Return general setting value or null.
	}

	/**
	 * Whether a language is offered in the toolbar's language picker.
	 *
	 * The saved 'toggle-language' map only ever describes the languages that
	 * existed when the admin last saved, so a language added by a plugin update
	 * has no entry in it. What that absence means depends on whether the admin
	 * has curated the list at all:
	 *
	 * - No map saved yet: a fresh install, so every language is offered.
	 * - A map exists: the admin has chosen. A language they have never been
	 *   shown stays off until they switch it on, because an update must not
	 *   quietly add languages to a site that deliberately runs one or two.
	 *
	 * Read by the toolbar, the dictionaries handed to the browser and the
	 * Display Options screen, so the three cannot disagree.
	 *
	 * @param string $lang_code Language code to test.
	 * @param array  $toggles   The saved 'toggle-language' map.
	 * @return bool True when the language is offered.
	 */
	public static function is_language_enabled( $lang_code, $toggles ) {
		if ( ! is_array( $toggles ) || empty( $toggles ) ) {
			return true;
		}

		return isset( $toggles[ $lang_code ] ) && 'on' === $toggles[ $lang_code ];
	}

	/**
	 * Whether the admin has ever saved the language toggles.
	 *
	 * @param array $toggles The saved 'toggle-language' map.
	 * @return bool True when a choice is on record.
	 */
	public static function has_language_toggles( $toggles ) {
		return is_array( $toggles ) && ! empty( $toggles );
	}

	/**
	 * Determine whether the Alt Text feature should be hidden.
	 *
	 * Reads the saved 'onetap_general_settings' option and falls back to the
	 * default value when the option is not set. When this returns true the
	 * Alt Text submenu, its navigation link, and the "Missing Alt-Text"
	 * dashboard widget are all hidden.
	 *
	 * @return bool True when the Alt Text feature is hidden, false otherwise.
	 */
	public static function is_alt_text_feature_hidden() {
		$general_settings = get_option( 'onetap_general_settings' );
		$value            = is_array( $general_settings ) && isset( $general_settings['hide_alt_text_feature'] )
			? $general_settings['hide_alt_text_feature']
			: self::get_general_setting( 'hide_alt_text_feature' );

		return 'on' === $value;
	}
}

// Example usage:
// Accessing static array values without creating an object.
// echo Accessibility_Onetap_Config::get_setting('language'); // Output: en.
// echo Accessibility_Onetap_Config::get_module('bigger_text'); // Output: on.
// echo Onetap_Pro_Config::get_general_setting('hide_powered_by_onetap'); // Output: off.
