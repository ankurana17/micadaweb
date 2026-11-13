<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wponetap.com
 * @since      1.0.0
 *
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/public
 * @author     OneTap <support@wponetap.com>
 */
class Accessibility_Onetap_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Get setting value with fallback to default config.
	 *
	 * @param array  $settings The settings array from database.
	 * @param string $key The setting key to retrieve.
	 * @param string $sanitize_function The sanitization function to apply (optional).
	 * @return mixed The setting value or default config value.
	 */
	private function get_setting_with_fallback( $settings, $key, $sanitize_function = null ) {
		$value = isset( $settings[ $key ] ) ? $settings[ $key ] : Accessibility_Onetap_Config::get_setting( $key );

		if ( $sanitize_function && function_exists( $sanitize_function ) ) {
			return $sanitize_function( $value );
		}

		return $value;
	}

	/**
	 * Helper method to get module settings efficiently.
	 *
	 * This method retrieves all module settings from the database and returns them
	 * as an associative array to avoid code repetition across multiple methods.
	 *
	 * @since    1.0.0
	 * @return   array    Array of module settings with standardized keys.
	 */
	private function get_module_settings() {
		// Get the 'modules' option from the database.
		$onetap_modules = get_option( 'onetap_modules' );

		// Define module mapping for consistent key naming.
		$module_mapping = array(
			'accessibility-profiles' => 'accessibility_profiles',
			'bigger-text'            => 'bigger_text',
			'highlight-links'        => 'highlight_links',
			'line-height'            => 'line_height',
			'readable-font'          => 'readable_font',
			'cursor'                 => 'cursor',
			'text-magnifier'         => 'text_magnifier',
			'dyslexic-font'          => 'dyslexic_font',
			'text-align'             => 'text_align',
			'align-left'             => 'align_left',
			'align-center'           => 'align_center',
			'align-right'            => 'align_right',
			'letter-spacing'         => 'letter_spacing',
			'font-weight'            => 'font_weight',
			'dark-contrast'          => 'dark_contrast',
			'light-contrast'         => 'light_contrast',
			'high-contrast'          => 'high_contrast',
			'monochrome'             => 'monochrome',
			'saturation'             => 'saturation',
			'reading-line'           => 'reading_line',
			'reading-mask'           => 'reading_mask',
			'read-page'              => 'read_page',
			'keyboard-navigation'    => 'keyboard_navigation',
			'hide-images'            => 'hide_images',
			'mute-sounds'            => 'mute_sounds',
			'highlight-titles'       => 'highlight_titles',
			'highlight-all'          => 'highlight_all',
			'stop-animations'        => 'stop_animations',
			'skip-to-content'        => 'skip_to_content',
		);

		$module_settings = array();

		// Loop through module mapping to get settings.
		foreach ( $module_mapping as $db_key => $config_key ) {
			$module_settings[ $config_key ] = isset( $onetap_modules[ $db_key ] )
				? esc_html( $onetap_modules[ $db_key ] )
				: Accessibility_Onetap_Config::get_module( $config_key );
		}

		return $module_settings;
	}

	/**
	 * Keep only the toolbar dictionaries the visitor can actually reach.
	 *
	 * Every dictionary used to be inlined into each page, and together they
	 * outweigh the rest of the toolbar payload. A language the admin switched
	 * off in Display Options never appears in the language list, so its
	 * translations are dead weight.
	 *
	 * The rule matches the one the language list itself is rendered with, so
	 * the two cannot disagree about what is reachable. English and the
	 * configured default language are always kept, so
	 * the toolbar still has something to render for a visitor whose stored
	 * language has since been switched off.
	 *
	 * @since    1.0.0
	 * @param    array  $dictionaries    Toolbar translations keyed by language code.
	 * @param    array  $toggles         The saved 'toggle-language' map.
	 * @param    string $active_language The language code configured as the default.
	 * @return   array The dictionaries that are still reachable.
	 */
	private function filter_enabled_language_dictionaries( $dictionaries, $toggles, $active_language ) {
		$always_keep = array( 'en', $active_language );
		$enabled     = array();

		foreach ( $dictionaries as $lang_code => $dictionary ) {
			if ( Accessibility_Onetap_Config::is_language_enabled( $lang_code, $toggles ) || in_array( $lang_code, $always_keep, true ) ) {
				$enabled[ $lang_code ] = $dictionary;
			}
		}

		return $enabled;
	}

	/**
	 * Determine whether the "Powered by OneTap" entry is shown in the toolbar footer.
	 *
	 * The entry can be switched off from the current general settings or from the
	 * legacy widget setting, so both are read here to keep every caller in sync.
	 *
	 * @since    1.0.0
	 * @return   bool True when the entry is rendered.
	 */
	private function is_powered_by_visible() {
		$general_settings = get_option( 'onetap_general_settings' );
		$settings         = get_option( 'onetap_settings' );

		$hidden_by_general = is_array( $general_settings ) && isset( $general_settings['hide_powered_by_onetap'] ) && 'on' === $general_settings['hide_powered_by_onetap'];
		$hidden_by_legacy  = is_array( $settings ) && isset( $settings['hide-powered-by-onetap'] ) && 'on' === $settings['hide-powered-by-onetap'];

		return ! $hidden_by_general && ! $hidden_by_legacy;
	}

	/**
	 * Determine whether the "Statement" entry is shown in the toolbar footer.
	 *
	 * The entry needs the feature enabled *and* a statement text: turning the
	 * toggle on while leaving the editor empty renders nothing.
	 *
	 * @since    1.0.0
	 * @return   bool True when the entry is rendered.
	 */
	private function is_statement_visible() {
		return (bool) get_option( 'onetap_show_accessibility' ) && ! empty( get_option( 'onetap_editor_generator' ) );
	}

	/**
	 * Print the dot that separates two toolbar footer entries.
	 *
	 * Nothing is printed for the first entry, which is what keeps a dot from ever
	 * showing up on the left edge of the footer. The flag is passed by reference
	 * so callers only have to declare it once and then render their entries in
	 * order.
	 *
	 * @since    1.0.0
	 * @param    bool $entry_printed Whether an entry has already been printed.
	 * @return   void
	 */
	private function render_footer_separator( &$entry_printed ) {
		if ( $entry_printed ) {
			?>
			<svg class="onetap-separator" width="4" height="4" viewBox="0 0 4 4" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="2" fill="currentColor"/></svg>
			<?php
		}

		$entry_printed = true;
	}

	/**
	 * Generate widget position CSS styles.
	 *
	 * @since    1.0.0
	 * @param    string $position              Widget position (middle-right, middle-left, bottom-right, bottom-left, top-right, top-left).
	 * @param    int    $position_left_right   Left/right margin value.
	 * @param    int    $position_top_bottom   Top/bottom margin value.
	 * @param    string $media_query           Media query string.
	 * @return   string Generated CSS styles.
	 */
	private function generate_widget_position_styles( $position, $position_left_right, $position_top_bottom, $media_query ) {
		// Default to bottom-right if position is empty or invalid.
		if ( empty( $position ) || ! in_array( $position, array( 'middle-right', 'middle-left', 'bottom-right', 'bottom-left', 'top-right', 'top-left' ), true ) ) {
			$position = 'bottom-right';
			// Use default values for fallback.
			$position_left_right = $position_left_right ? $position_left_right : 20;
			$position_top_bottom = $position_top_bottom ? $position_top_bottom : 20;
		}

		// Check if this is mobile device (max-width: 576px).
		$is_mobile = strpos( $media_query, 'max-width: 576px' ) !== false;

		// Parse position to get vertical and horizontal parts.
		$parts      = explode( '-', $position );
		$vertical   = $parts[0]; // top, middle, bottom.
		$horizontal = $parts[1]; // left, right.

		// Initialize CSS properties.
		$toggle_styles         = array();
		$nav_closed_side       = '';
		$nav_open_side         = '';
		$close_button_position = '';

		// Set horizontal position (left or right).
		if ( 'left' === $horizontal ) {
			$toggle_styles[] = 'left: 0 !important;';
			$toggle_styles[] = "margin-left: {$position_left_right}px !important;";
			$toggle_styles[] = 'right: unset !important;';
			$toggle_styles[] = 'margin-right: unset !important;';
			$nav_closed_side = 'left: -580px !important;';
			$nav_open_side   = 'left: 0 !important;';
			// Mobile uses right positioning for close button, tablet/desktop use left.
			$close_button_position = $is_mobile ? 'right: 20px !important;' : 'left: calc(530px - 20px) !important;';
		} else { // right.
			$toggle_styles[]       = 'right: 0 !important;';
			$toggle_styles[]       = "margin-right: {$position_left_right}px !important;";
			$toggle_styles[]       = 'left: unset !important;';
			$toggle_styles[]       = 'margin-left: unset !important;';
			$nav_closed_side       = 'right: -580px !important;';
			$nav_open_side         = 'right: 0 !important;';
			$close_button_position = 'right: 20px !important;';
		}

		// Set vertical position (top, middle, bottom).
		if ( 'top' === $vertical ) {
			$toggle_styles[] = 'top: 0 !important;';
			$toggle_styles[] = "margin-top: {$position_top_bottom}px !important;";
			$toggle_styles[] = 'bottom: unset !important;';
			$toggle_styles[] = 'margin-bottom: unset !important;';
		} elseif ( 'middle' === $vertical ) {
			$toggle_styles[] = 'bottom: 50% !important;';
			$toggle_styles[] = "margin-bottom: {$position_top_bottom}px !important;";
			$toggle_styles[] = 'top: unset !important;';
			$toggle_styles[] = 'margin-top: unset !important;';
		} else { // bottom.
			$toggle_styles[] = 'bottom: 0 !important;';
			$toggle_styles[] = "margin-bottom: {$position_top_bottom}px !important;";
			$toggle_styles[] = 'top: unset !important;';
			$toggle_styles[] = 'margin-top: unset !important;';
		}

		// Build CSS string.
		$toggle_css = implode( "\n\t\t\t\t\t", $toggle_styles );

		// Generate RTL-specific CSS for left positions.
		$rtl_css = '';
		if ( 'left' === $horizontal ) {
			// For RTL + left position, nav should appear from the left side.
			$rtl_css = '
			nav.onetap-accessibility.onetap-plugin-onetap.onetap-rtl {
				right: unset !important;
				left: -580px !important;
			}
			nav.onetap-accessibility.onetap-plugin-onetap.onetap-rtl.onetap-toggle-open {
				right: unset !important;
				left: 0 !important;
			}
			nav.onetap-accessibility.onetap-plugin-onetap.onetap-rtl .onetap-container .onetap-accessibility-settings header.onetap-header-top .onetap-close {
				right: 20px !important;
				left: unset !important;
			}
			';
		}

		return "
		{$media_query} {
			.onetap-container-toggle .onetap-toggle {
				{$toggle_css}
			}
			nav.onetap-accessibility.onetap-plugin-onetap {
				{$nav_closed_side}
			}
			nav.onetap-accessibility.onetap-plugin-onetap.onetap-toggle-open {
				{$nav_open_side}
			}
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings header.onetap-header-top .onetap-close {
				{$close_button_position}
			}
			{$rtl_css}
		}
		";
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// Enqueue the main plugin stylesheet for the front-end.
		wp_enqueue_style( $this->plugin_name, plugins_url( $this->plugin_name ) . '/assets/css/accessibility-onetap-front-end.min.css', array(), $this->version, 'all' );

		// Get the plugin settings, specifically the color option.
		$onetap_settings         = get_option( 'onetap_settings' );
		$onetap_general_settings = get_option( 'onetap_general_settings' );

		// Use the user-defined color setting, or fall back to the default if not set.
		$onetap_setting_widget_button_focus_style      = $this->get_setting_with_fallback( $onetap_general_settings, 'toggle-widget-button-focus-style', 'esc_html' );
		$onetap_setting_color                          = $this->get_setting_with_fallback( $onetap_settings, 'color', 'esc_html' );
		$onetap_setting_position_top_bottom            = $this->get_setting_with_fallback( $onetap_settings, 'position-top-bottom', 'absint' );
		$onetap_setting_position_left_right            = $this->get_setting_with_fallback( $onetap_settings, 'position-left-right', 'absint' );
		$onetap_setting_widget_position                = $this->get_setting_with_fallback( $onetap_settings, 'widge-position', 'esc_html' );
		$onetap_setting_position_top_bottom_tablet     = $this->get_setting_with_fallback( $onetap_settings, 'position-top-bottom-tablet', 'absint' );
		$onetap_setting_position_left_right_tablet     = $this->get_setting_with_fallback( $onetap_settings, 'position-left-right-tablet', 'absint' );
		$onetap_setting_widget_position_tablet         = $this->get_setting_with_fallback( $onetap_settings, 'widge-position-tablet', 'esc_html' );
		$onetap_setting_position_top_bottom_mobile     = $this->get_setting_with_fallback( $onetap_settings, 'position-top-bottom-mobile', 'absint' );
		$onetap_setting_position_left_right_mobile     = $this->get_setting_with_fallback( $onetap_settings, 'position-left-right-mobile', 'absint' );
		$onetap_setting_widget_position_mobile         = $this->get_setting_with_fallback( $onetap_settings, 'widge-position-mobile', 'esc_html' );
		$onetap_setting_toggle_device_position_desktop = $this->get_setting_with_fallback( $onetap_settings, 'toggle-device-position-desktop', 'esc_html' );
		$onetap_setting_toggle_device_position_tablet  = $this->get_setting_with_fallback( $onetap_settings, 'toggle-device-position-tablet', 'esc_html' );
		$onetap_setting_toggle_device_position_mobile  = $this->get_setting_with_fallback( $onetap_settings, 'toggle-device-position-mobile', 'esc_html' );

		// Get module settings using helper method.
		$module_settings = $this->get_module_settings();

		// Load font stylesheets only when the corresponding module is enabled (more efficient).
		if ( ! empty( $module_settings['readable_font'] ) && 'off' !== $module_settings['readable_font'] ) {
			wp_enqueue_style(
				$this->plugin_name . '-fonts-readable',
				plugins_url( $this->plugin_name ) . '/assets/css/onetap-fonts-readable.min.css',
				array( $this->plugin_name ),
				$this->version,
				'all'
			);
		}

		if ( ! empty( $module_settings['dyslexic_font'] ) && 'off' !== $module_settings['dyslexic_font'] ) {
			wp_enqueue_style(
				$this->plugin_name . '-fonts-dyslexic',
				plugins_url( $this->plugin_name ) . '/assets/css/onetap-fonts-dyslexic.min.css',
				array( $this->plugin_name ),
				$this->version,
				'all'
			);
		}

		// Define custom CSS to apply the color setting to specific elements.
		$style = "
		.onetap-container-toggle .onetap-toggle svg,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings header.onetap-header-top .onetap-site-container .onetap-site-info .onetap-image svg {
			fill: {$onetap_setting_color} !important;
		}		
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature.onetap-lv2 .onetap-title p.onetap-option-levels span.onetap-level.onetap-level1, nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature.onetap-lv2 .onetap-title p.onetap-option-levels span.onetap-level.onetap-level2,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature.onetap-lv3 .onetap-title p.onetap-option-levels span.onetap-level.onetap-level1, nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature.onetap-lv3 .onetap-title p.onetap-option-levels span.onetap-level.onetap-level2, nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature.onetap-lv3 .onetap-title p.onetap-option-levels span.onetap-level.onetap-level3,
		.onetap-container-toggle .onetap-toggle img,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration .box-btn-action button.hide-toolbar,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-step-controls .onetap-new-level .onetap-btn,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-reset-settings button,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings header.onetap-header-top .onetap-site-container,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature .onetap-box-functions .onetap-functional-feature.onetap-active .onetap-right .box-swich label.switch input+.slider,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature .onetap-box-functions .onetap-functional-feature .onetap-right .box-swich label.switch input:checked+.slider,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature .onetap-box-functions .onetap-functional-feature.onetap-active div.onetap-right div.box-swich label.switch span.slider.round:hover,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-step-controls .onetap-new-level .onetap-title .box-btn .onetap-btn,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature .onetap-box-functions .onetap-functional-feature.onetap-active .onetap-left svg,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature.onetap-active .onetap-icon .onetap-icon-animation svg {
			background: {$onetap_setting_color} !important;
		}
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-step-controls .onetap-new-level .onetap-btn,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-step-controls .onetap-new-level .onetap-title .box-btn .onetap-btn {
			color: {$onetap_setting_color} !important;
		}			
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration form label input[type='radio']:checked {
			accent-color: {$onetap_setting_color} !important;
			box-shadow: 0 0 0 1px {$onetap_setting_color} !important;
			background: {$onetap_setting_color} !important;
		}
		.onetap-container-toggle .onetap-toggle img.design-border1 {
			box-shadow: 0 0 0 4px {$onetap_setting_color};
		}

		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration form label.active,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration form label:focus-visible,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration form label:hover {
			outline: none !important;
			border: 1px solid {$onetap_setting_color} !important;
			box-shadow: 0 0 0 1px {$onetap_setting_color} !important;
		}		
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .accessibility-status-wrapper .accessibility-status-text button:focus,			
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration .box-btn-action button:focus {
			outline: 2px solid {$onetap_setting_color} !important;
			outline-offset: 2px !important;
		}			
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature .onetap-box-functions .onetap-functional-feature .onetap-right .box-swich label.switch:focus .slider,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature .onetap-box-functions .onetap-functional-feature .onetap-right .box-swich label.switch input:focus-visible + .slider,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-reset-settings button:focus,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings header.onetap-header-top .onetap-list-of-languages ul li button:focus {
			outline: 2px solid {$onetap_setting_color} !important;
		}
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature:hover,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature:focus-visible,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features button.onetap-box-feature.onetap-inactive:hover,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature.onetap-active,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature.onetap-active.onetap-text-align-mode-left,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature.onetap-active.onetap-text-align-mode-center,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature.onetap-active.onetap-text-align-mode-right,		
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-feature.onetap-inactive:focus-visible,
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-skip-content-control select:focus-visible {
			border-color: {$onetap_setting_color} !important;
			box-shadow: 0 0 0 1px {$onetap_setting_color} !important;
		}

		nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration .box-btn-action button.hide-toolbar {
			border-color: {$onetap_setting_color} !important;
		}";

		if ( 'on' === $onetap_setting_widget_button_focus_style ) {
			$style .= '
		.onetap-container-toggle .onetap-toggle:focus img {
			border: dotted 2px #fff !important;
		}';
		}

		if ( '#0048FE' === $onetap_setting_color ) {
			$style .= 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-reset-settings button:hover {
				background: #003cd5 !important;
			}';
		}

		// Mobile.
		if ( 'on' === $onetap_setting_toggle_device_position_mobile ) {
			$style .= '
			@media only screen and (max-width: 576px) {
				.onetap-container-toggle .onetap-toggle.hide-on-mobile {
					display: none !important;
				}
			}			
			';
		}

		$style .= $this->generate_widget_position_styles(
			$onetap_setting_widget_position_mobile,
			$onetap_setting_position_left_right_mobile,
			$onetap_setting_position_top_bottom_mobile,
			'@media only screen and (max-width: 576px)'
		);

		// Tablet.
		if ( 'on' === $onetap_setting_toggle_device_position_tablet ) {
			$style .= '
			@media only screen and (min-width: 576px) and (max-width: 991.98px) {
				.onetap-container-toggle .onetap-toggle.hide-on-tablet {
					display: none !important;
				}
			}			
			';
		}

		$style .= $this->generate_widget_position_styles(
			$onetap_setting_widget_position_tablet,
			$onetap_setting_position_left_right_tablet,
			$onetap_setting_position_top_bottom_tablet,
			'@media only screen and (min-width: 576px) and (max-width: 991.98px)'
		);

		// Desktop.
		if ( 'on' === $onetap_setting_toggle_device_position_desktop ) {
			$style .= '
			@media only screen and (min-width: 992px) {
				.onetap-container-toggle .onetap-toggle.hide-on-desktop {
					display: none !important;
				}
			}			
			';
		}

		$style .= $this->generate_widget_position_styles(
			$onetap_setting_widget_position,
			$onetap_setting_position_left_right,
			$onetap_setting_position_top_bottom,
			'@media only screen and (min-width: 992px)'
		);

		// If accessibility_profiles OFF.
		if ( 'on' !== $module_settings['accessibility_profiles'] ) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings div.onetap-multi-functional-feature {
				display: none !important;
			}

			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-content-modules {
				padding: 0 14px 0 14px !important;
				margin-top: -85px !important;
				margin-bottom: 24px !important;
			}
				
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-content-secondary {
				margin-top: 24px !important;
			}';
		}

		if ( 'on' !== $module_settings['bigger_text'] ) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-step-controls.onetap-font-size {
				display: none !important;
			}';
		}

		if ( 'on' !== $module_settings['skip_to_content'] ) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-skip-content {
				display: none !important;
			}';
		}

		if ( 'on' !== $module_settings['line_height'] ) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-features .onetap-box-step-controls.onetap-line-height {
				display: none !important;
			}';
		}

		if ( 'on' !== $module_settings['bigger_text'] &&
			'on' !== $module_settings['readable_font'] &&
			'on' !== $module_settings['line_height'] &&
			'on' !== $module_settings['cursor'] &&
			'on' !== $module_settings['letter_spacing'] &&
			'on' !== $module_settings['text_align'] &&
			'on' !== $module_settings['font_weight']
		) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-content-modules {
				display: none !important;
			}
				
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-content-modules.onetap-feature-content-secondary {
				display: block !important;
			}';
		}

		if ( 'on' !== $module_settings['text_align'] &&
			'on' !== $module_settings['letter_spacing'] &&
			'on' !== $module_settings['font_weight']
		) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-content-modules.onetap-feature-content-secondary {
				display: none !important;
			}';
		}

		if ( 'on' !== $module_settings['dark_contrast'] &&
			'on' !== $module_settings['light_contrast'] &&
			'on' !== $module_settings['high_contrast'] &&
			'on' !== $module_settings['monochrome'] &&
			'on' !== $module_settings['saturation']
		) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-color-modules {
				display: none !important;
			}';
		}

		if ( 'on' !== $module_settings['reading_line'] &&
			'on' !== $module_settings['reading_mask'] &&
			'on' !== $module_settings['hide_images'] &&
			'on' !== $module_settings['highlight_all'] &&
			'on' !== $module_settings['stop_animations']
		) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-orientation-modules {
				display: none !important;
			}

			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-orientation-modules.onetap-feature-content-secondary {
				display: block !important;
			}';
		}

		if ( 'on' !== $module_settings['highlight_titles'] &&
			'on' !== $module_settings['highlight_all'] &&
			'on' !== $module_settings['stop_animations']
		) {
			$style .= '
			nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container.onetap-feature-orientation-modules.onetap-feature-content-secondary {
				display: none !important;
			}';
		}

		// Add the custom inline CSS to the previously enqueued plugin stylesheet.
		// Visually hide the switch label text: it names the control for screen
		// readers while the slider stays the only visible affordance.
		$style .= '
		nav.onetap-accessibility.onetap-plugin-onetap .onetap-visually-hidden {
			position: absolute !important;
			width: 1px !important;
			height: 1px !important;
			padding: 0 !important;
			margin: -1px !important;
			overflow: hidden !important;
			clip: rect(0, 0, 0, 0) !important;
			white-space: nowrap !important;
			border: 0 !important;
		}';

		wp_add_inline_style( $this->plugin_name, $style );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Construct the file path of the plugin.
		$plugin_file = WP_PLUGIN_DIR . '/accessibility-onetap/accessibility-onetap.php';

		// Check if the plugin file exists.
		$plugin_version = '1.0.0';
		if ( file_exists( $plugin_file ) ) {
			// Include the necessary WordPress file for plugin data retrieval.
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			// Retrieve the plugin data.
			$plugin_info = get_plugin_data( $plugin_file );

			// Extract relevant plugin information.
			$plugin_version = $plugin_info['Version'];
		}

		// Register hotkeys.js library.
		wp_enqueue_script(
			'onetap-hotkeys-library',
			plugins_url( $this->plugin_name ) . '/assets/js/hotkeys.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localize script for configurable options.
		wp_localize_script(
			'onetap-hotkeys-library',
			'accessibilityHotkeys',
			array(
				'hotKeyMenu'     => 'm',
				'hotKeyHeadings' => 'h',
				'hotKeyForms'    => 'f',
				'hotKeyButtons'  => 'b',
				'hotKeyGraphics' => 'g',
			)
		);

		// Register the script but do not enqueue it yet.
		wp_register_script(
			$this->plugin_name, // Handle for the script.
			plugins_url( $this->plugin_name ) . '/assets/js/script.min.js', // URL to the script file.
			array( 'jquery' ), // Dependencies, in this case, jQuery.
			$this->version, // Script version for cache-busting.
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			) // An array of additional script loading strategies.
		);

		// Enqueue the script after it has been registered.
		wp_enqueue_script( $this->plugin_name );

		// Get the 'settings' option from the database.
		$onetap_settings                               = get_option( 'onetap_settings' );
		$onetap_setting_language                       = $this->get_setting_with_fallback( $onetap_settings, 'language', 'esc_html' );
		$onetap_setting_color                          = $this->get_setting_with_fallback( $onetap_settings, 'color', 'esc_html' );
		$onetap_setting_position_top_bottom            = $this->get_setting_with_fallback( $onetap_settings, 'position-top-bottom', 'absint' );
		$onetap_setting_position_left_right            = $this->get_setting_with_fallback( $onetap_settings, 'position-left-right', 'absint' );
		$onetap_setting_widget_position                = $this->get_setting_with_fallback( $onetap_settings, 'widge-position', 'esc_html' );
		$onetap_setting_position_top_bottom_tablet     = $this->get_setting_with_fallback( $onetap_settings, 'position-top-bottom-tablet', 'absint' );
		$onetap_setting_position_left_right_tablet     = $this->get_setting_with_fallback( $onetap_settings, 'position-left-right-tablet', 'absint' );
		$onetap_setting_widget_position_tablet         = $this->get_setting_with_fallback( $onetap_settings, 'widge-position-tablet', 'esc_html' );
		$onetap_setting_position_top_bottom_mobile     = $this->get_setting_with_fallback( $onetap_settings, 'position-top-bottom-mobile', 'absint' );
		$onetap_setting_position_left_right_mobile     = $this->get_setting_with_fallback( $onetap_settings, 'position-left-right-mobile', 'absint' );
		$onetap_setting_widget_position_mobile         = $this->get_setting_with_fallback( $onetap_settings, 'widge-position-mobile', 'esc_html' );
		$onetap_setting_toggle_device_position_desktop = $this->get_setting_with_fallback( $onetap_settings, 'toggle-device-position-desktop', 'esc_html' );
		$onetap_setting_toggle_device_position_tablet  = $this->get_setting_with_fallback( $onetap_settings, 'toggle-device-position-tablet', 'esc_html' );
		$onetap_setting_toggle_device_position_mobile  = $this->get_setting_with_fallback( $onetap_settings, 'toggle-device-position-mobile', 'esc_html' );
		$onetap_setting_hide_powered_by_onetap         = $this->get_setting_with_fallback( $onetap_settings, 'hide-powered-by-onetap', 'esc_html' );

		// Get general settings for compatibility check (new plugin version).
		$onetap_general_settings                 = get_option( 'onetap_modules' );
		$general_settings_hide_powered_by_onetap = isset( $onetap_general_settings['hide_powered_by_onetap'] ) ? $onetap_general_settings['hide_powered_by_onetap'] : 'off';

		// Compatibility check: try new plugin setting first, then fallback to legacy plugin setting.
		$onetap_setting_hide_powered_by_onetap = ( 'on' === $general_settings_hide_powered_by_onetap || 'on' === $onetap_setting_hide_powered_by_onetap ) ? 'on' : 'off';

		// Get module settings using helper method.
		$module_settings = $this->get_module_settings();

		$list_languages = array(
			'en'    => 'English',
			'de'    => 'Deutsch',
			'es'    => 'Español',
			'fr'    => 'Français',
			'it'    => 'Italiano',
			'pl'    => 'Polski',
			'se'    => 'Svenska',
			'fi'    => 'Suomi',
			'pt'    => 'Português',
			'ro'    => 'Română',
			'si'    => 'Slovenščina',
			'sk'    => 'Slovenčina',
			'nl'    => 'Nederlands',
			'dk'    => 'Dansk',
			'gr'    => 'Ελληνικά',
			'cz'    => 'Čeština',
			'hu'    => 'Magyar',
			'lt'    => 'Lietuvių',
			'lv'    => 'Latviešu',
			'ee'    => 'Eesti',
			'hr'    => 'Hrvatski',
			'ie'    => 'Gaeilge',
			'bg'    => 'Български',
			'no'    => 'Norsk',
			'tr'    => 'Türkçe',
			'id'    => 'Bahasa Indonesia',
			'pt-br' => 'Português (Brasil)',
			'ja'    => '日本語',
			'ko'    => '한국어',
			'zh'    => '简体中文',
			'ar'    => 'العربية',
			'ru'    => 'Русский',
			'hi'    => 'हिन्दी',
			'uk'    => 'Українська',
			'sr'    => 'Srpski',
			'gb'    => 'English (UK)',
			'ir'    => 'فارسی',
			'il'    => 'עברית',
			'mk'    => 'Македонски',
			'th'    => 'ไทย',
			'vn'    => 'Tiếng Việt',
			'bn'    => 'বাংলা',
			'tl'    => 'Filipino',
			'ur'    => 'اردو',
		);

		// Get plugin settings.
		$plugin_settings = get_option( 'onetap_settings' );

		// Get active language.
		$language = isset( $plugin_settings['language'] ) ? $plugin_settings['language'] : 'en';

		// Language list with English names and image filenames.
		$language_list = array(
			'en'    => array(
				'name'  => 'English',
				'image' => 'english.png',
			),
			'de'    => array(
				'name'  => 'German',
				'image' => 'german.png',
			),
			'es'    => array(
				'name'  => 'Spanish',
				'image' => 'spanish.png',
			),
			'fr'    => array(
				'name'  => 'French',
				'image' => 'french.png',
			),
			'it'    => array(
				'name'  => 'Italian',
				'image' => 'italia.png',
			),
			'pl'    => array(
				'name'  => 'Polish',
				'image' => 'poland.png',
			),
			'se'    => array(
				'name'  => 'Swedish',
				'image' => 'swedish.png',
			),
			'fi'    => array(
				'name'  => 'Finnish',
				'image' => 'finnland.png',
			),
			'pt'    => array(
				'name'  => 'Portuguese',
				'image' => 'portugal.png',
			),
			'ro'    => array(
				'name'  => 'Romanian',
				'image' => 'rumania.png',
			),
			'si'    => array(
				'name'  => 'Slovenian',
				'image' => 'slowenien.png',
			),
			'sk'    => array(
				'name'  => 'Slovak',
				'image' => 'slowakia.png',
			),
			'nl'    => array(
				'name'  => 'Dutch',
				'image' => 'netherland.png',
			),
			'dk'    => array(
				'name'  => 'Danish',
				'image' => 'danish.png',
			),
			'gr'    => array(
				'name'  => 'Greek',
				'image' => 'greece.png',
			),
			'cz'    => array(
				'name'  => 'Czech',
				'image' => 'czech.png',
			),
			'hu'    => array(
				'name'  => 'Hungarian',
				'image' => 'hungarian.png',
			),
			'lt'    => array(
				'name'  => 'Lithuanian',
				'image' => 'lithuanian.png',
			),
			'lv'    => array(
				'name'  => 'Latvian',
				'image' => 'latvian.png',
			),
			'ee'    => array(
				'name'  => 'Estonian',
				'image' => 'estonian.png',
			),
			'hr'    => array(
				'name'  => 'Croatian',
				'image' => 'croatia.png',
			),
			'ie'    => array(
				'name'  => 'Irish',
				'image' => 'ireland.png',
			),
			'bg'    => array(
				'name'  => 'Bulgarian',
				'image' => 'bulgarian.png',
			),
			'no'    => array(
				'name'  => 'Norwegian',
				'image' => 'norwegan.png',
			),
			'tr'    => array(
				'name'  => 'Turkish',
				'image' => 'turkish.png',
			),
			'id'    => array(
				'name'  => 'Indonesian',
				'image' => 'indonesian.png',
			),
			'pt-br' => array(
				'name'  => 'Portuguese (Brazil)',
				'image' => 'brasilian.png',
			),
			'ja'    => array(
				'name'  => 'Japanese',
				'image' => 'japanese.png',
			),
			'ko'    => array(
				'name'  => 'Korean',
				'image' => 'korean.png',
			),
			'zh'    => array(
				'name'  => 'Chinese (Simplified)',
				'image' => 'chinese-simplified.png',
			),
			'ar'    => array(
				'name'  => 'Arabic',
				'image' => 'arabic.png',
			),
			'ru'    => array(
				'name'  => 'Russian',
				'image' => 'russian.png',
			),
			'hi'    => array(
				'name'  => 'Hindi',
				'image' => 'hindi.png',
			),
			'uk'    => array(
				'name'  => 'Ukrainian',
				'image' => 'ukrainian.png',
			),
			'sr'    => array(
				'name'  => 'Serbian',
				'image' => 'serbian.png',
			),
			'gb'    => array(
				'name'  => 'English (UK)',
				'image' => 'england.png',
			),
			'ir'    => array(
				'name'  => 'Persian',
				'image' => 'iran.png',
			),
			'il'    => array(
				'name'  => 'Hebrew',
				'image' => 'israel.png',
			),
			'mk'    => array(
				'name'  => 'Macedonian',
				'image' => 'macedonia.png',
			),
			'th'    => array(
				'name'  => 'Thai',
				'image' => 'thailand.png',
			),
			'vn'    => array(
				'name'  => 'Vietnamese',
				'image' => 'vietnam.png',
			),
			'bn'    => array(
				'name'  => 'Bengali',
				'image' => 'bangladesh.png',
			),
			'tl'    => array(
				'name'  => 'Filipino',
				'image' => 'philippines.png',
			),
			'ur'    => array(
				'name'  => 'Urdu',
				'image' => 'pakistan.png',
			),
		);
		// Toolbar translations, one dictionary per language.
		$onetap_language_dictionaries = array(
			'en'    => array(
				'global'                 => array(
					'back'    => 'Back',
					'default' => 'Default',
				),
				'hideToolbar'            => array(
					'title'   => 'How long do you want to hide the toolbar?',
					'radio1'  => 'Only for this session',
					'radio2'  => '24 hours',
					'radio3'  => 'A Week',
					'button1' => 'Not Now',
					'button2' => 'Hide Toolbar',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browser needs to be updated',
					'desc'  => 'Your browser doesn’t support speech output. Please update your browser or use one with speech synthesis enabled (e.g. Chrome, Edge, Safari).',
					'link'  => 'How to Update?',
				),
				'header'                 => array(
					'language'      => 'English',
					'listLanguages' => $list_languages,
					'title'         => 'Accessibility Adjustments',
					'desc'          => 'Powered by',
					'anchor'        => 'OneTap',
					'statement'     => 'Statement',
					'hideToolbar'   => 'Hide Toolbar',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Select your accessibility profile',
					'visionImpairedMode' => array(
						'title' => 'Vision Impaired Mode',
						'desc'  => "Enhances website's visuals",
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'seizureSafeProfile' => array(
						'title' => 'Seizure Safe Profile',
						'desc'  => 'Clear flashes & reduces color',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD Friendly Mode',
						'desc'  => 'Focused browsing, distraction-free',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'blindnessMode'      => array(
						'title' => 'Blindness Mode',
						'desc'  => 'Reduces distractions, improves focus',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsy Safe Mode',
						'desc'  => 'Dims colors and stops blinking',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Content Modules',
					'colorModules'       => 'Color Modules',
					'orientationModules' => 'Orientation Modules',
				),
				'features'               => array(
					'skipToContent'      => 'Skip To Content',
					'skipChoose'         => 'Choose...',
					'skipMain'           => 'Main Content',
					'skipNavigation'     => 'Navigation',
					'skipFooter'         => 'Footer',
					'biggerText'         => 'Font Size',
					'highlightLinks'     => 'Highlight Links',
					'lineHeight'         => 'Line Height',
					'readableFont'       => 'Readable Font',
					'cursor'             => 'Big Cursor',
					'textMagnifier'      => 'Text Magnifier',
					'dyslexicFont'       => 'Dyslexic Font',
					'alignDefault'       => 'Align Text',
					'alignLeft'          => 'Left',
					'alignCenter'        => 'Centered',
					'alignRight'         => 'Right',
					'letterSpacing'      => 'Letter Spacing',
					'fontWeight'         => 'Font Weight',
					'darkContrast'       => 'Dark Contrast',
					'lightContrast'      => 'Light Contrast',
					'highContrast'       => 'High Contrast',
					'monochrome'         => 'Monochrome',
					'saturation'         => 'Saturation',
					'readingLine'        => 'Reading Line',
					'readingMask'        => 'Reading Mask',
					'readPage'           => 'Read Page',
					'keyboardNavigation' => 'Keyboard Navigation',
					'hideImages'         => 'Hide Images',
					'muteSounds'         => 'Mute Sounds',
					'highlightTitles'    => 'Highlight Titles',
					'highlightAll'       => 'Highlight Content',
					'stopAnimations'     => 'Stop Animations',
					'resetSettings'      => 'Reset Settings',
				),
			),
			'de'    => array(
				'global'                 => array(
					'back'    => 'Zurück',
					'default' => 'Standard',
				),
				'hideToolbar'            => array(
					'title'   => 'Wie lange möchten Sie die Barrierefreiheits-Symbolleiste ausblenden?',
					'radio1'  => 'Nur für diese Sitzung',
					'radio2'  => '24 Stunden',
					'radio3'  => 'Eine Woche',
					'button1' => 'Nicht jetzt',
					'button2' => 'Symbolleiste ausblenden',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browser muss aktualisiert werden',
					'desc'  => 'Ihr Browser unterstützt keine Sprachausgabe. Bitte aktualisieren Sie Ihren Browser oder verwenden Sie einen mit aktivierter Sprachsynthese (z. B. Chrome, Edge, Safari).',
					'link'  => 'Wie aktualisieren?',
				),
				'header'                 => array(
					'language'      => 'Deutsch',
					'listLanguages' => $list_languages,
					'title'         => 'Barrierefreie Anpassungen',
					'desc'          => 'Unterstützt durch',
					'anchor'        => 'OneTap',
					'statement'     => 'Erklärung',
					'hideToolbar'   => 'Symbolleiste ausblenden',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Wählen Sie Ihr Barrierefreiheitsprofil',
					'visionImpairedMode' => array(
						'title' => 'Sehbehinderungsmodus',
						'desc'  => 'Verbessert die visuellen Elemente der Website',
						'on'    => 'AN',
						'off'   => 'AUS',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil für Anfallsicherheit',
						'desc'  => 'Reduziert Farbblitze und klarere Farben',
						'on'    => 'AN',
						'off'   => 'AUS',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-freundlicher Modus',
						'desc'  => 'Fokussiertes Surfen ohne Ablenkungen',
						'on'    => 'AN',
						'off'   => 'AUS',
					),
					'blindnessMode'      => array(
						'title' => 'Blindheitsmodus',
						'desc'  => 'Reduziert Ablenkungen, verbessert die Konzentration',
						'on'    => 'AN',
						'off'   => 'AUS',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsie-sicherer Modus',
						'desc'  => 'Dimmt Farben und stoppt das Blinken',
						'on'    => 'AN',
						'off'   => 'AUS',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Inhaltsmodule',
					'colorModules'       => 'Farbmodule',
					'orientationModules' => 'Orientierungsmodule',
				),
				'features'               => array(
					'skipToContent'      => 'Zum Inhalt springen',
					'skipChoose'         => 'Auswählen...',
					'skipMain'           => 'Hauptinhalt',
					'skipNavigation'     => 'Navigation',
					'skipFooter'         => 'Fußzeile',
					'biggerText'         => 'Schriftgröße',
					'highlightLinks'     => 'Links hervorheben',
					'lineHeight'         => 'Zeilenhöhe',
					'readableFont'       => 'Lesbare Schriftart',
					'cursor'             => 'Großer Cursor',
					'textMagnifier'      => 'Textvergrößerung',
					'dyslexicFont'       => 'Dyslexische Schriftart',
					'alignDefault'       => 'Ausrichten',
					'alignLeft'          => 'Links',
					'alignCenter'        => 'Zentriert',
					'alignRight'         => 'Rechts',
					'letterSpacing'      => 'Buchstabenabstand',
					'fontWeight'         => 'Schriftstärke',
					'darkContrast'       => 'Dunkler Kontrast',
					'lightContrast'      => 'Heller Kontrast',
					'highContrast'       => 'Hoher Kontrast',
					'monochrome'         => 'Monochrom',
					'saturation'         => 'Sättigung',
					'readingLine'        => 'Leselinie',
					'readingMask'        => 'Lese-Maske',
					'readPage'           => 'Seite lesen',
					'keyboardNavigation' => 'Tastaturnavigation',
					'hideImages'         => 'Bilder ausblenden',
					'muteSounds'         => 'Geräusche stummschalten',
					'highlightTitles'    => 'Titel hervorheben',
					'highlightAll'       => 'Inhalt hervorheben',
					'stopAnimations'     => 'Animationen stoppen',
					'resetSettings'      => 'Einstellungen zurücksetzen',
				),
			),
			'es'    => array(
				'global'                 => array(
					'back'    => 'Volver',
					'default' => 'Predeterminado',
				),
				'hideToolbar'            => array(
					'title'   => '¿Cuánto tiempo desea ocultar la barra de accesibilidad?',
					'radio1'  => 'Solo para esta sesión',
					'radio2'  => '24 horas',
					'radio3'  => 'Una semana',
					'button1' => 'Ahora no',
					'button2' => 'Ocultar barra',
				),
				'unsupportedPageReader'  => array(
					'title' => 'El navegador necesita actualización',
					'desc'  => 'Tu navegador no admite salida de voz. Actualiza tu navegador o usa uno con síntesis de voz habilitada (por ejemplo, Chrome, Edge, Safari).',
					'link'  => '¿Cómo actualizar?',
				),
				'header'                 => array(
					'language'      => 'Español',
					'listLanguages' => $list_languages,
					'title'         => 'Ajustes de Accesibilidad',
					'desc'          => 'Desarrollado por',
					'anchor'        => 'OneTap',
					'statement'     => 'Declaración',
					'hideToolbar'   => 'Ocultar barra de herramientas',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Seleccione su perfil de accesibilidad',
					'visionImpairedMode' => array(
						'title' => 'Modo para personas con discapacidad visual',
						'desc'  => 'Mejora los elementos visuales del sitio web',
						'on'    => 'ENC',
						'off'   => 'APG',
					),
					'seizureSafeProfile' => array(
						'title' => 'Perfil seguro para convulsiones',
						'desc'  => 'Reduce los destellos y mejora los colores',
						'on'    => 'ENC',
						'off'   => 'APG',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Modo amigable para el TDAH',
						'desc'  => 'Navegación enfocada sin distracciones',
						'on'    => 'ENC',
						'off'   => 'APG',
					),
					'blindnessMode'      => array(
						'title' => 'Modo para ceguera',
						'desc'  => 'Reduce las distracciones y mejora el enfoque',
						'on'    => 'ENC',
						'off'   => 'APG',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Modo seguro para epilepsia',
						'desc'  => 'Reduce los colores y detiene el parpadeo',
						'on'    => 'ENC',
						'off'   => 'APG',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Módulos de contenido',
					'colorModules'       => 'Módulos de color',
					'orientationModules' => 'Módulos de orientación',
				),
				'features'               => array(
					'skipToContent'      => 'Saltar al contenido',
					'skipChoose'         => 'Elegir...',
					'skipMain'           => 'Contenido principal',
					'skipNavigation'     => 'Navegación',
					'skipFooter'         => 'Pie de página',
					'biggerText'         => 'Tamaño de fuente',
					'highlightLinks'     => 'Resaltar enlaces',
					'lineHeight'         => 'Altura de línea',
					'readableFont'       => 'Fuente legible',
					'cursor'             => 'Cursor grande',
					'textMagnifier'      => 'Lupa de texto',
					'dyslexicFont'       => 'Fuente para dislexia',
					'alignDefault'       => 'Alinear texto',
					'alignLeft'          => 'Izquierda',
					'alignCenter'        => 'Centrado',
					'alignRight'         => 'Derecha',
					'letterSpacing'      => 'Espaciado de letras',
					'fontWeight'         => 'Grosor de fuente',
					'darkContrast'       => 'Contraste oscuro',
					'lightContrast'      => 'Contraste claro',
					'highContrast'       => 'Alto contraste',
					'monochrome'         => 'Monocromo',
					'saturation'         => 'Saturación',
					'readingLine'        => 'Línea de lectura',
					'readingMask'        => 'Máscara de lectura',
					'readPage'           => 'Leer página',
					'keyboardNavigation' => 'Navegación por teclado',
					'hideImages'         => 'Ocultar imágenes',
					'muteSounds'         => 'Silenciar sonidos',
					'highlightTitles'    => 'Resaltar títulos',
					'highlightAll'       => 'Resaltar contenido',
					'stopAnimations'     => 'Detener animaciones',
					'resetSettings'      => 'Restablecer configuraciones',
				),
			),
			'fr'    => array(
				'global'                 => array(
					'back'    => 'Retour',
					'default' => 'Par défaut',
				),
				'hideToolbar'            => array(
					'title'   => 'Combien de temps souhaitez-vous masquer la barre d’accessibilité ?',
					'radio1'  => 'Seulement pour cette session',
					'radio2'  => '24 heures',
					'radio3'  => 'Une semaine',
					'button1' => 'Pas maintenant',
					'button2' => 'Masquer la barre',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Le navigateur doit être mis à jour',
					'desc'  => 'Votre navigateur ne prend pas en charge la synthèse vocale. Veuillez le mettre à jour ou utiliser un navigateur compatible (par exemple Chrome, Edge, Safari).',
					'link'  => 'Comment le mettre à jour ?',
				),
				'header'                 => array(
					'language'      => 'Français',
					'listLanguages' => $list_languages,
					'title'         => 'Réglages d\'accessibilité',
					'desc'          => 'Développé par',
					'anchor'        => 'OneTap',
					'statement'     => 'Déclaration',
					'hideToolbar'   => 'Masquer la barre d’outils',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Sélectionnez votre profil d\'accessibilité',
					'visionImpairedMode' => array(
						'title' => 'Mode pour malvoyants',
						'desc'  => 'Améliore les éléments visuels du site web',
						'on'    => 'ACT',
						'off'   => 'DÉSACT',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil sécurisé pour les crises',
						'desc'  => 'Réduit les éclairs et améliore les couleurs',
						'on'    => 'ACT',
						'off'   => 'DÉSACT',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Mode adapté pour le TDAH',
						'desc'  => 'Navigation concentrée sans distractions',
						'on'    => 'ACT',
						'off'   => 'DÉSACT',
					),
					'blindnessMode'      => array(
						'title' => 'Mode pour la cécité',
						'desc'  => 'Réduit les distractions et améliore la concentration',
						'on'    => 'ACT',
						'off'   => 'DÉSACT',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Mode sécurisé pour l\'épilepsie',
						'desc'  => 'Réduit les couleurs et arrête les clignotements',
						'on'    => 'ACT',
						'off'   => 'DÉSACT',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Modules de contenu',
					'colorModules'       => 'Modules de couleur',
					'orientationModules' => 'Modules d\'orientation',
				),
				'features'               => array(
					'skipToContent'      => 'Aller au contenu',
					'skipChoose'         => 'Choisir...',
					'skipMain'           => 'Contenu principal',
					'skipNavigation'     => 'Navigation',
					'skipFooter'         => 'Pied de page',
					'biggerText'         => 'Taille de police',
					'highlightLinks'     => 'Surligner les liens',
					'lineHeight'         => 'Hauteur de ligne',
					'readableFont'       => 'Police lisible',
					'cursor'             => 'Grand curseur',
					'textMagnifier'      => 'Loupe de texte',
					'dyslexicFont'       => 'Police pour dyslexie',
					'alignDefault'       => 'Aligner le texte',
					'alignLeft'          => 'Gauche',
					'alignCenter'        => 'Centré',
					'alignRight'         => 'Droite',
					'letterSpacing'      => 'Espacement des lettres',
					'fontWeight'         => 'Épaisseur de police',
					'darkContrast'       => 'Contraste sombre',
					'lightContrast'      => 'Contraste clair',
					'highContrast'       => 'Contraste élevé',
					'monochrome'         => 'Monochrome',
					'saturation'         => 'Saturation',
					'readingLine'        => 'Ligne de lecture',
					'readingMask'        => 'Masque de lecture',
					'readPage'           => 'Lire la page',
					'keyboardNavigation' => 'Navigation au clavier',
					'hideImages'         => 'Masquer les images',
					'muteSounds'         => 'Couper les sons',
					'highlightTitles'    => 'Surligner les titres',
					'highlightAll'       => 'Surligner le contenu',
					'stopAnimations'     => 'Arrêter les animations',
					'resetSettings'      => 'Réinitialiser les paramètres',
				),
			),
			'it'    => array(
				'global'                 => array(
					'back'    => 'Indietro',
					'default' => 'Predefinito',
				),
				'hideToolbar'            => array(
					'title'   => 'Per quanto tempo vuoi nascondere la barra di accessibilità?',
					'radio1'  => 'Solo per questa sessione',
					'radio2'  => '24 ore',
					'radio3'  => 'Una settimana',
					'button1' => 'Non ora',
					'button2' => 'Nascondi barra',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Il browser deve essere aggiornato',
					'desc'  => 'Il tuo browser non supporta la sintesi vocale. Aggiorna il browser o utilizza uno che supporti la sintesi vocale (es. Chrome, Edge, Safari).',
					'link'  => 'Come aggiornare?',
				),
				'header'                 => array(
					'language'      => 'Italiano',
					'listLanguages' => $list_languages,
					'title'         => 'Impostazioni di accessibilità',
					'desc'          => 'Sviluppato da',
					'anchor'        => 'OneTap',
					'statement'     => 'Dichiarazione',
					'hideToolbar'   => 'Nascondi barra degli strumenti',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Seleziona il tuo profilo di accessibilità',
					'visionImpairedMode' => array(
						'title' => 'Modalità per disabilità visive',
						'desc'  => 'Migliora gli elementi visivi del sito web',
						'on'    => 'ATTIVO',
						'off'   => 'DISATTIVO',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profilo sicuro per crisi',
						'desc'  => 'Riduce i lampi e migliora i colori',
						'on'    => 'ATTIVO',
						'off'   => 'DISATTIVO',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Modalità adatta per ADHD',
						'desc'  => 'Navigazione concentrata senza distrazioni',
						'on'    => 'ATTIVO',
						'off'   => 'DISATTIVO',
					),
					'blindnessMode'      => array(
						'title' => 'Modalità per cecità',
						'desc'  => 'Riduce le distrazioni e migliora la concentrazione',
						'on'    => 'ATTIVO',
						'off'   => 'DISATTIVO',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Modalità sicura per epilessia',
						'desc'  => 'Riduce i colori e ferma i lampeggiamenti',
						'on'    => 'ATTIVO',
						'off'   => 'DISATTIVO',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduli di contenuto',
					'colorModules'       => 'Moduli di colore',
					'orientationModules' => 'Moduli di orientamento',
				),
				'features'               => array(
					'skipToContent'      => 'Vai al contenuto',
					'skipChoose'         => 'Scegli...',
					'skipMain'           => 'Contenuto principale',
					'skipNavigation'     => 'Navigazione',
					'skipFooter'         => 'Piè di pagina',
					'biggerText'         => 'Dimensione del carattere',
					'highlightLinks'     => 'Evidenzia i link',
					'lineHeight'         => 'Altezza della linea',
					'readableFont'       => 'Carattere leggibile',
					'cursor'             => 'Cursore grande',
					'textMagnifier'      => 'Lente di ingrandimento del testo',
					'dyslexicFont'       => 'Carattere per dislessia',
					'alignDefault'       => 'Allinea testo',
					'alignLeft'          => 'Sinistra',
					'alignCenter'        => 'Centrato',
					'alignRight'         => 'Destra',
					'letterSpacing'      => 'Spaziatura delle lettere',
					'fontWeight'         => 'Spessore del carattere',
					'darkContrast'       => 'Contrasto scuro',
					'lightContrast'      => 'Contrasto chiaro',
					'highContrast'       => 'Alto contrasto',
					'monochrome'         => 'Monocromatico',
					'saturation'         => 'Saturazione',
					'readingLine'        => 'Linea di lettura',
					'readingMask'        => 'Maschera di lettura',
					'readPage'           => 'Leggi la pagina',
					'keyboardNavigation' => 'Navigazione con tastiera',
					'hideImages'         => 'Nascondi le immagini',
					'muteSounds'         => 'Disattiva i suoni',
					'highlightTitles'    => 'Evidenzia i titoli',
					'highlightAll'       => 'Evidenzia il contenuto',
					'stopAnimations'     => 'Ferma le animazioni',
					'resetSettings'      => 'Ripristina le impostazioni',
				),
			),
			'pl'    => array(
				'global'                 => array(
					'back'    => 'Wstecz',
					'default' => 'Domyślne',
				),
				'hideToolbar'            => array(
					'title'   => 'Na jak długo chcesz ukryć pasek dostępności?',
					'radio1'  => 'Tylko na tę sesję',
					'radio2'  => '24 godziny',
					'radio3'  => 'Tydzień',
					'button1' => 'Nie teraz',
					'button2' => 'Ukryj pasek',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Przeglądarka wymaga aktualizacji',
					'desc'  => 'Twoja przeglądarka nie obsługuje syntezy mowy. Zaktualizuj przeglądarkę lub użyj takiej, która obsługuje syntezę mowy (np. Chrome, Edge, Safari).',
					'link'  => 'Jak zaktualizować?',
				),
				'header'                 => array(
					'language'      => 'Polski',
					'listLanguages' => $list_languages,
					'title'         => 'Ustawienia dostępności',
					'desc'          => 'Zbudowane przez',
					'anchor'        => 'OneTap',
					'statement'     => 'Oświadczenie',
					'hideToolbar'   => 'Ukryj pasek narzędzi',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Wybierz swój profil dostępności',
					'visionImpairedMode' => array(
						'title' => 'Tryb dla osób z zaburzeniami wzroku',
						'desc'  => 'Poprawia wygląd strony',
						'on'    => 'WŁĄCZONE',
						'off'   => 'WYŁĄCZONE',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil bezpieczny dla osób z padaczką',
						'desc'  => 'Zmniejsza migające światła i poprawia kolory',
						'on'    => 'WŁĄCZONE',
						'off'   => 'WYŁĄCZONE',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Tryb przyjazny dla osób z ADHD',
						'desc'  => 'Skoncentrowana nawigacja, bez rozpraszania',
						'on'    => 'WŁĄCZONE',
						'off'   => 'WYŁĄCZONE',
					),
					'blindnessMode'      => array(
						'title' => 'Tryb dla osób niewidomych',
						'desc'  => 'Zmniejsza rozproszenia, poprawia koncentrację',
						'on'    => 'WŁĄCZONE',
						'off'   => 'WYŁĄCZONE',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Tryb bezpieczny dla osób z padaczką',
						'desc'  => 'Zmienia kolory i zatrzymuje miganie',
						'on'    => 'WŁĄCZONE',
						'off'   => 'WYŁĄCZONE',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduły treści',
					'colorModules'       => 'Moduły kolorów',
					'orientationModules' => 'Moduły orientacji',
				),
				'features'               => array(
					'skipToContent'      => 'Przejdź do treści',
					'skipChoose'         => 'Wybierz...',
					'skipMain'           => 'Główna treść',
					'skipNavigation'     => 'Nawigacja',
					'skipFooter'         => 'Stopka',
					'biggerText'         => 'Rozmiar czcionki',
					'highlightLinks'     => 'Wyróżnij linki',
					'lineHeight'         => 'Wysokość linii',
					'readableFont'       => 'Czytelna czcionka',
					'cursor'             => 'Duży kursor',
					'textMagnifier'      => 'Lupa tekstu',
					'dyslexicFont'       => 'Czcionka dla dyslektyków',
					'alignDefault'       => 'Wyrównaj tekst',
					'alignLeft'          => 'Do lewej',
					'alignCenter'        => 'Wyśrodkowany',
					'alignRight'         => 'Do prawej',
					'letterSpacing'      => 'Odstępy między literami',
					'fontWeight'         => 'Grubość czcionki',
					'darkContrast'       => 'Ciemny kontrast',
					'lightContrast'      => 'Jasny kontrast',
					'highContrast'       => 'Wysoki kontrast',
					'monochrome'         => 'Monochromatyczny',
					'saturation'         => 'Nasycenie',
					'readingLine'        => 'Linia czytania',
					'readingMask'        => 'Maska czytania',
					'readPage'           => 'Przeczytaj stronę',
					'keyboardNavigation' => 'Nawigacja klawiaturą',
					'hideImages'         => 'Ukryj obrazy',
					'muteSounds'         => 'Wycisz dźwięki',
					'highlightTitles'    => 'Wyróżnij tytuły',
					'highlightAll'       => 'Wyróżnij treść',
					'stopAnimations'     => 'Zatrzymaj animacje',
					'resetSettings'      => 'Resetuj ustawienia',
				),
			),
			'se'    => array(
				'global'                 => array(
					'back'    => 'Tillbaka',
					'default' => 'Standard',
				),
				'hideToolbar'            => array(
					'title'   => 'Hur länge vill du dölja tillgänglighetsverktygsfältet?',
					'radio1'  => 'Endast för denna session',
					'radio2'  => '24 timmar',
					'radio3'  => 'En vecka',
					'button1' => 'Inte nu',
					'button2' => 'Dölj verktygsfältet',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Webbläsaren behöver uppdateras',
					'desc'  => 'Din webbläsare stöder inte talsyntes. Uppdatera din webbläsare eller använd en som stöder talsyntes (t.ex. Chrome, Edge, Safari).',
					'link'  => 'Hur uppdaterar man?',
				),
				'header'                 => array(
					'language'      => 'Svenska',
					'listLanguages' => $list_languages,
					'title'         => 'Tillgänglighetsinställningar',
					'desc'          => 'Byggd av',
					'anchor'        => 'OneTap',
					'statement'     => 'Uttalande',
					'hideToolbar'   => 'Dölj verktygsfält',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Välj din tillgänglighetsprofil',
					'visionImpairedMode' => array(
						'title' => 'Synnedsättningläge',
						'desc'  => 'Förbättrar webbplatsens visuella element',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'seizureSafeProfile' => array(
						'title' => 'Säker profil för anfall',
						'desc'  => 'Minskar blinkningar och förbättrar färger',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-vänligt läge',
						'desc'  => 'Fokuserad surfning utan distraktioner',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'blindnessMode'      => array(
						'title' => 'Blindläge',
						'desc'  => 'Minskar distraktioner och förbättrar fokus',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsisäkert läge',
						'desc'  => 'Dämpar färger och stoppar blinkningar',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Innehållsmoduler',
					'colorModules'       => 'Färgmoduler',
					'orientationModules' => 'Orientationsmoduler',
				),
				'features'               => array(
					'skipToContent'      => 'Hoppa till innehåll',
					'skipChoose'         => 'Välj...',
					'skipMain'           => 'Huvudinnehåll',
					'skipNavigation'     => 'Navigering',
					'skipFooter'         => 'Sidfot',
					'biggerText'         => 'Teckenstorlek',
					'highlightLinks'     => 'Markera länkar',
					'lineHeight'         => 'Radhöjd',
					'readableFont'       => 'Läslig font',
					'cursor'             => 'Stor muspekare',
					'textMagnifier'      => 'Textförstorare',
					'dyslexicFont'       => 'Font för dyslexi',
					'alignDefault'       => 'Justera text',
					'alignLeft'          => 'Vänster',
					'alignCenter'        => 'Centrerad',
					'alignRight'         => 'Höger',
					'letterSpacing'      => 'Bokstavsavstånd',
					'fontWeight'         => 'Teckenvikt',
					'darkContrast'       => 'Mörk kontrast',
					'lightContrast'      => 'Ljus kontrast',
					'highContrast'       => 'Hög kontrast',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Mättnad',
					'readingLine'        => 'Läsrad',
					'readingMask'        => 'Läsmask',
					'readPage'           => 'Läs sida',
					'keyboardNavigation' => 'Tangentbordsnavigering',
					'hideImages'         => 'Dölj bilder',
					'muteSounds'         => 'Stäng av ljud',
					'highlightTitles'    => 'Markera titlar',
					'highlightAll'       => 'Markera innehåll',
					'stopAnimations'     => 'Stoppa animationer',
					'resetSettings'      => 'Återställ inställningar',
				),
			),
			'fi'    => array(
				'global'                 => array(
					'back'    => 'Takaisin',
					'default' => 'Oletus',
				),
				'hideToolbar'            => array(
					'title'   => 'Kuinka kauan haluat piilottaa saavutettavuuspalkin?',
					'radio1'  => 'Vain tälle istunnolle',
					'radio2'  => '24 tuntia',
					'radio3'  => 'Viikko',
					'button1' => 'Ei nyt',
					'button2' => 'Piilota palkki',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Selain täytyy päivittää',
					'desc'  => 'Selaimesi ei tue puhesynteesiä. Päivitä selain tai käytä sellaista, joka tukee puhesynteesiä (esim. Chrome, Edge, Safari).',
					'link'  => 'Kuinka päivitetään?',
				),
				'header'                 => array(
					'language'      => 'Suomi',
					'listLanguages' => $list_languages,
					'title'         => 'Saavutettavuusasetukset',
					'desc'          => 'Rakennettu',
					'anchor'        => 'OneTap',
					'statement'     => 'Lausunto',
					'hideToolbar'   => 'Piilota työkalupalkki',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Valitse saavutettavuusprofiilisi',
					'visionImpairedMode' => array(
						'title' => 'Näkövammaisten tila',
						'desc'  => 'Parantaa verkkosivuston visuaalisia elementtejä',
						'on'    => 'PÄÄLLÄ',
						'off'   => 'POIS',
					),
					'seizureSafeProfile' => array(
						'title' => 'Kouristuksia estävä profiili',
						'desc'  => 'Vähentää vilkkuvia valoja ja parantaa värejä',
						'on'    => 'PÄÄLLÄ',
						'off'   => 'POIS',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-ystävällinen tila',
						'desc'  => 'Keskittynyt selaaminen ilman häiriöitä',
						'on'    => 'PÄÄLLÄ',
						'off'   => 'POIS',
					),
					'blindnessMode'      => array(
						'title' => 'Sokeus tila',
						'desc'  => 'Vähentää häiriöitä ja parantaa keskittymistä',
						'on'    => 'PÄÄLLÄ',
						'off'   => 'POIS',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsiaturvallinen tila',
						'desc'  => 'Häiritsee värejä ja estää vilkkumisen',
						'on'    => 'PÄÄLLÄ',
						'off'   => 'POIS',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Sisältömoduulit',
					'colorModules'       => 'Värimoduulit',
					'orientationModules' => 'Orientointimoduulit',
				),
				'features'               => array(
					'skipToContent'      => 'Siirry sisältöön',
					'skipChoose'         => 'Valitse...',
					'skipMain'           => 'Pääsisältö',
					'skipNavigation'     => 'Navigointi',
					'skipFooter'         => 'Alatunniste',
					'biggerText'         => 'Fonttikoko',
					'highlightLinks'     => 'Korosta linkkejä',
					'lineHeight'         => 'Riviväli',
					'readableFont'       => 'Lukukelpoinen fontti',
					'cursor'             => 'Suuri hiiren osoitin',
					'textMagnifier'      => 'Tekstin suurennuslasia',
					'dyslexicFont'       => 'Dyslektikon fontti',
					'alignDefault'       => 'Tasaa teksti',
					'alignLeft'          => 'Vasen',
					'alignCenter'        => 'Keskitetty',
					'alignRight'         => 'Oikea',
					'letterSpacing'      => 'Kirjainväli',
					'fontWeight'         => 'Fontin paksuus',
					'darkContrast'       => 'Tumma kontrasti',
					'lightContrast'      => 'Vaalea kontrasti',
					'highContrast'       => 'Korkea kontrasti',
					'monochrome'         => 'Monokromi',
					'saturation'         => 'Kylläisyys',
					'readingLine'        => 'Lukulinja',
					'readingMask'        => 'Lukemismaski',
					'readPage'           => 'Lue sivu',
					'keyboardNavigation' => 'Näppäimistö navigointi',
					'hideImages'         => 'Piilota kuvat',
					'muteSounds'         => 'Mykistä äänet',
					'highlightTitles'    => 'Korosta otsikoita',
					'highlightAll'       => 'Korosta sisältö',
					'stopAnimations'     => 'Pysäytä animaatiot',
					'resetSettings'      => 'Nollaa asetukset',
				),
			),
			'pt'    => array(
				'global'                 => array(
					'back'    => 'Voltar',
					'default' => 'Padrão',
				),
				'hideToolbar'            => array(
					'title'   => 'Por quanto tempo deseja ocultar a barra de acessibilidade?',
					'radio1'  => 'Apenas para esta sessão',
					'radio2'  => '24 horas',
					'radio3'  => 'Uma semana',
					'button1' => 'Agora não',
					'button2' => 'Ocultar barra',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Selain täytyy päivittää',
					'desc'  => 'Selaimesi ei tue puhesynteesiä. Päivitä selain tai käytä sellaista, joka tukee puhesynteesiä (esim. Chrome, Edge, Safari).',
					'link'  => 'Kuinka päivitetään?',
				),
				'header'                 => array(
					'language'      => 'Português',
					'listLanguages' => $list_languages,
					'title'         => 'Configurações de Acessibilidade',
					'desc'          => 'Construído por',
					'anchor'        => 'OneTap',
					'statement'     => 'Declaração',
					'hideToolbar'   => 'Ocultar barra de ferramentas',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Escolha seu perfil de acessibilidade',
					'visionImpairedMode' => array(
						'title' => 'Modo de Deficiência Visual',
						'desc'  => 'Melhora os elementos visuais do site',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'seizureSafeProfile' => array(
						'title' => 'Perfil Seguro para Convulsões',
						'desc'  => 'Reduz luzes piscando e melhora as cores',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Modo Amigável para TDAH',
						'desc'  => 'Navegação focada sem distrações',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'blindnessMode'      => array(
						'title' => 'Modo para Deficientes Visuais',
						'desc'  => 'Reduz distrações e melhora a concentração',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Modo Seguro para Epilepsia',
						'desc'  => 'Diminui as cores e para os flashes',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Módulos de Conteúdo',
					'colorModules'       => 'Módulos de Cor',
					'orientationModules' => 'Módulos de Orientação',
				),
				'features'               => array(
					'skipToContent'      => 'Ir para o conteúdo',
					'skipChoose'         => 'Escolher...',
					'skipMain'           => 'Conteúdo principal',
					'skipNavigation'     => 'Navegação',
					'skipFooter'         => 'Rodapé',
					'biggerText'         => 'Tamanho da Fonte',
					'highlightLinks'     => 'Destacar Links',
					'lineHeight'         => 'Altura da Linha',
					'readableFont'       => 'Fonte Legível',
					'cursor'             => 'Cursor Grande',
					'textMagnifier'      => 'Lupa de Texto',
					'dyslexicFont'       => 'Fonte para Dislexia',
					'alignDefault'       => 'Alinhar texto',
					'alignLeft'          => 'Esquerda',
					'alignCenter'        => 'Centralizado',
					'alignRight'         => 'Direita',
					'letterSpacing'      => 'Espaçamento das Letras',
					'fontWeight'         => 'Peso da Fonte',
					'darkContrast'       => 'Contraste Escuro',
					'lightContrast'      => 'Contraste Claro',
					'highContrast'       => 'Alto Contraste',
					'monochrome'         => 'Monocromático',
					'saturation'         => 'Saturação',
					'readingLine'        => 'Linha de Leitura',
					'readingMask'        => 'Máscara de Leitura',
					'readPage'           => 'Ler Página',
					'keyboardNavigation' => 'Navegação pelo Teclado',
					'hideImages'         => 'Esconder Imagens',
					'muteSounds'         => 'Silenciar Sons',
					'highlightTitles'    => 'Destacar Títulos',
					'highlightAll'       => 'Destacar Conteúdo',
					'stopAnimations'     => 'Parar Animações',
					'resetSettings'      => 'Redefinir Configurações',
				),
			),
			'ro'    => array(
				'global'                 => array(
					'back'    => 'Înapoi',
					'default' => 'Implicit',
				),
				'hideToolbar'            => array(
					'title'   => 'Cât timp doriți să ascundeți bara de accesibilitate?',
					'radio1'  => 'Doar pentru această sesiune',
					'radio2'  => '24 de ore',
					'radio3'  => 'O săptămână',
					'button1' => 'Nu acum',
					'button2' => 'Ascunde bara',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browserul trebuie actualizat',
					'desc'  => 'Browserul tău nu suportă ieșirea vocală. Te rugăm să-l actualizezi sau să folosești unul care acceptă sinteza vocală (de exemplu, Chrome, Edge, Safari).',
					'link'  => 'Cum se actualizează?',
				),
				'header'                 => array(
					'language'      => 'Română',
					'listLanguages' => $list_languages,
					'title'         => 'Setări de Accesibilitate',
					'desc'          => 'Creat de',
					'anchor'        => 'OneTap',
					'statement'     => 'Declarație',
					'hideToolbar'   => 'Ascunde bara de instrumente',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Alegeți profilul de accesibilitate',
					'visionImpairedMode' => array(
						'title' => 'Mod pentru deficiențe de vedere',
						'desc'  => 'Îmbunătățește elementele vizuale ale site-ului',
						'on'    => 'ACTIVAT',
						'off'   => 'DEZACTIVAT',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil sigur pentru crize',
						'desc'  => 'Reduce luminile intermitente și îmbunătățește culorile',
						'on'    => 'ACTIVAT',
						'off'   => 'DEZACTIVAT',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Mod prietenos pentru TDAH',
						'desc'  => 'Navigare concentrată fără distrageri',
						'on'    => 'ACTIVAT',
						'off'   => 'DEZACTIVAT',
					),
					'blindnessMode'      => array(
						'title' => 'Mod pentru orbire',
						'desc'  => 'Reduce distragerile și îmbunătățește concentrarea',
						'on'    => 'ACTIVAT',
						'off'   => 'DEZACTIVAT',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Mod sigur pentru epilepsie',
						'desc'  => 'Reduce culorile și oprește clipirea',
						'on'    => 'ACTIVAT',
						'off'   => 'DEZACTIVAT',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Module de conținut',
					'colorModules'       => 'Module de culori',
					'orientationModules' => 'Module de orientare',
				),
				'features'               => array(
					'skipToContent'      => 'Salt la conținut',
					'skipChoose'         => 'Alege...',
					'skipMain'           => 'Conținut principal',
					'skipNavigation'     => 'Navigare',
					'skipFooter'         => 'Subsol',
					'biggerText'         => 'Dimensiunea fontului',
					'highlightLinks'     => 'Subliniază link-uri',
					'lineHeight'         => 'Înălțimea liniei',
					'readableFont'       => 'Font lizibil',
					'cursor'             => 'Cursor mare',
					'textMagnifier'      => 'Lupă text',
					'dyslexicFont'       => 'Font pentru dislexie',
					'alignDefault'       => 'Aliniere text',
					'alignLeft'          => 'Stânga',
					'alignCenter'        => 'Centrat',
					'alignRight'         => 'Dreapta',
					'letterSpacing'      => 'Spațierea literelor',
					'fontWeight'         => 'Grosimea fontului',
					'darkContrast'       => 'Contrast întunecat',
					'lightContrast'      => 'Contrast deschis',
					'highContrast'       => 'Contrast înalt',
					'monochrome'         => 'Monocrom',
					'saturation'         => 'Saturație',
					'readingLine'        => 'Linie de citire',
					'readingMask'        => 'Mască de citire',
					'readPage'           => 'Citește pagina',
					'keyboardNavigation' => 'Navigare cu tastatura',
					'hideImages'         => 'Ascunde imagini',
					'muteSounds'         => 'Opresc sunetele',
					'highlightTitles'    => 'Subliniază titluri',
					'highlightAll'       => 'Subliniază conținut',
					'stopAnimations'     => 'Oprire animații',
					'resetSettings'      => 'Resetați setările',
				),
			),
			'si'    => array(
				'global'                 => array(
					'back'    => 'Nazaj',
					'default' => 'Privzeto',
				),
				'hideToolbar'            => array(
					'title'   => 'Kako dolgo želite skriti orodno vrstico za dostopnost?',
					'radio1'  => 'Samo za to sejo',
					'radio2'  => '24 ur',
					'radio3'  => 'En teden',
					'button1' => 'Ne zdaj',
					'button2' => 'Skrij orodno vrstico',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Brskalnik je treba posodobiti',
					'desc'  => 'Vaš brskalnik ne podpira govorne sinteze. Posodobite svoj brskalnik ali uporabite takšnega, ki podpira govor (npr. Chrome, Edge, Safari).',
					'link'  => 'Kako posodobiti?',
				),
				'header'                 => array(
					'language'      => 'Slovenščina',
					'listLanguages' => $list_languages,
					'title'         => 'Nastavitve dostopnosti',
					'desc'          => 'Narejeno',
					'anchor'        => 'OneTap',
					'statement'     => 'Izjava',
					'hideToolbar'   => 'Skrij orodno vrstico',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Izberite svoj dostopnostni profil',
					'visionImpairedMode' => array(
						'title' => 'Način za motnje vida',
						'desc'  => 'Izboljša vizualne elemente spletnega mesta',
						'on'    => 'UKLOP',
						'off'   => 'IZKLOP',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil za varnost pred napadi',
						'desc'  => 'Zmanjša utripajoče luči in izboljša barve',
						'on'    => 'UKLOP',
						'off'   => 'IZKLOP',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Način, prijazen za ADHD',
						'desc'  => 'Osredotočena navigacija brez motenj',
						'on'    => 'UKLOP',
						'off'   => 'IZKLOP',
					),
					'blindnessMode'      => array(
						'title' => 'Način za slepoto',
						'desc'  => 'Zmanjša motnje in izboljša osredotočenost',
						'on'    => 'UKLOP',
						'off'   => 'IZKLOP',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Način varnosti pred epilepsijo',
						'desc'  => 'Zmanjša barve in ustavi utripanje',
						'on'    => 'UKLOP',
						'off'   => 'IZKLOP',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduli vsebine',
					'colorModules'       => 'Moduli barv',
					'orientationModules' => 'Moduli orientacije',
				),
				'features'               => array(
					'skipToContent'      => 'Preskoči na vsebino',
					'skipChoose'         => 'Izberite...',
					'skipMain'           => 'Glavna vsebina',
					'skipNavigation'     => 'Navigacija',
					'skipFooter'         => 'Noga',
					'biggerText'         => 'Velikost pisave',
					'highlightLinks'     => 'Poudari povezave',
					'lineHeight'         => 'Višina vrstice',
					'readableFont'       => 'Bralna pisava',
					'cursor'             => 'Velik kazalec',
					'textMagnifier'      => 'Lupa besedila',
					'dyslexicFont'       => 'Pisava za disleksijo',
					'alignDefault'       => 'Poravnaj besedilo',
					'alignLeft'          => 'Levo',
					'alignCenter'        => 'Sredinsko',
					'alignRight'         => 'Desno',
					'letterSpacing'      => 'Razmik med črkami',
					'fontWeight'         => 'Debelina pisave',
					'darkContrast'       => 'Temen kontrast',
					'lightContrast'      => 'Svetel kontrast',
					'highContrast'       => 'Visok kontrast',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Saturacija',
					'readingLine'        => 'Bralna linija',
					'readingMask'        => 'Maska za branje',
					'readPage'           => 'Preberi stran',
					'keyboardNavigation' => 'Navigacija s tipkovnico',
					'hideImages'         => 'Skrij slike',
					'muteSounds'         => 'Utišaj zvoke',
					'highlightTitles'    => 'Poudari naslove',
					'highlightAll'       => 'Poudari vsebino',
					'stopAnimations'     => 'Zaustavi animacije',
					'resetSettings'      => 'Ponastavi nastavitve',
				),
			),
			'sk'    => array(
				'global'                 => array(
					'back'    => 'Späť',
					'default' => 'Predvolené',
				),
				'hideToolbar'            => array(
					'title'   => 'Ako dlho chcete skryť panel prístupnosti?',
					'radio1'  => 'Len pre toto sedenie',
					'radio2'  => '24 hodín',
					'radio3'  => 'Jeden týždeň',
					'button1' => 'Nie teraz',
					'button2' => 'Skryť panel',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Prehliadač je potrebné aktualizovať',
					'desc'  => 'Váš prehliadač nepodporuje výstup reči. Aktualizujte prehliadač alebo použite taký, ktorý podporuje syntézu reči (napr. Chrome, Edge, Safari).',
					'link'  => 'Ako aktualizovať?',
				),
				'header'                 => array(
					'language'      => 'Slovenčina',
					'listLanguages' => $list_languages,
					'title'         => 'Nastavenia prístupnosti',
					'desc'          => 'Vytvorené',
					'anchor'        => 'OneTap',
					'statement'     => 'Vyhlásenie',
					'hideToolbar'   => 'Skryť panel nástrojov',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Vyberte svoj profil prístupnosti',
					'visionImpairedMode' => array(
						'title' => 'Režim pre zrakové postihnutie',
						'desc'  => 'Vylepšuje vizuálne prvky stránky',
						'on'    => 'AKTIVOVANÉ',
						'off'   => 'DEAKTIVOVANÉ',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil pre bezpečnosť pred záchvatmi',
						'desc'  => 'Znižuje blikanie a zlepšuje farby',
						'on'    => 'AKTIVOVANÉ',
						'off'   => 'DEAKTIVOVANÉ',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Režim priateľský k ADHD',
						'desc'  => 'Zameraná navigácia bez rozptýlení',
						'on'    => 'AKTIVOVANÉ',
						'off'   => 'DEAKTIVOVANÉ',
					),
					'blindnessMode'      => array(
						'title' => 'Režim pre slepotu',
						'desc'  => 'Znižuje rozptýlenia a zlepšuje koncentráciu',
						'on'    => 'AKTIVOVANÉ',
						'off'   => 'DEAKTIVOVANÉ',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Režim bezpečný pre epilepsiu',
						'desc'  => 'Znižuje farby a zastavuje blikanie',
						'on'    => 'AKTIVOVANÉ',
						'off'   => 'DEAKTIVOVANÉ',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduly obsahu',
					'colorModules'       => 'Moduly farieb',
					'orientationModules' => 'Moduly orientácie',
				),
				'features'               => array(
					'skipToContent'      => 'Preskočiť na obsah',
					'skipChoose'         => 'Vybrať...',
					'skipMain'           => 'Hlavný obsah',
					'skipNavigation'     => 'Navigácia',
					'skipFooter'         => 'Päta',
					'biggerText'         => 'Veľkosť písma',
					'highlightLinks'     => 'Zvýrazniť odkazy',
					'lineHeight'         => 'Výška riadku',
					'readableFont'       => 'Čitateľný font',
					'cursor'             => 'Veľký kurzor',
					'textMagnifier'      => 'Lupa textu',
					'dyslexicFont'       => 'Font pre dyslexiu',
					'alignDefault'       => 'Zarovnať text',
					'alignLeft'          => 'Vľavo',
					'alignCenter'        => 'Na stred',
					'alignRight'         => 'Vpravo',
					'letterSpacing'      => 'Medzera medzi písmenami',
					'fontWeight'         => 'Hrúbka písma',
					'darkContrast'       => 'Tmavý kontrast',
					'lightContrast'      => 'Svetlý kontrast',
					'highContrast'       => 'Vysoký kontrast',
					'monochrome'         => 'Monochróm',
					'saturation'         => 'Saturácia',
					'readingLine'        => 'Čítacia línia',
					'readingMask'        => 'Maska na čítanie',
					'readPage'           => 'Prečítajte stránku',
					'keyboardNavigation' => 'Navigácia pomocou klávesnice',
					'hideImages'         => 'Skryť obrázky',
					'muteSounds'         => 'Stlmiť zvuky',
					'highlightTitles'    => 'Zvýrazniť nadpisy',
					'highlightAll'       => 'Zvýrazniť obsah',
					'stopAnimations'     => 'Zastaviť animácie',
					'resetSettings'      => 'Obnoviť nastavenia',
				),
			),
			'nl'    => array(
				'global'                 => array(
					'back'    => 'Terug',
					'default' => 'Standaard',
				),
				'hideToolbar'            => array(
					'title'   => 'Hoe lang wilt u de toegankelijkheidstoolbalk verbergen?',
					'radio1'  => 'Alleen voor deze sessie',
					'radio2'  => '24 uur',
					'radio3'  => 'Een week',
					'button1' => 'Niet nu',
					'button2' => 'Verberg werkbalk',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browser moet worden bijgewerkt',
					'desc'  => 'Je browser ondersteunt geen spraaksynthese. Werk je browser bij of gebruik een browser die spraaksynthese ondersteunt (bijv. Chrome, Edge, Safari).',
					'link'  => 'Hoe bijwerken?',
				),
				'header'                 => array(
					'language'      => 'Nederlands',
					'listLanguages' => $list_languages,
					'title'         => 'Toegankelijkheidsinstellingen',
					'desc'          => 'Gemaakt door',
					'anchor'        => 'OneTap',
					'statement'     => 'Verklaring',
					'hideToolbar'   => 'Werkbalk verbergen',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Kies je toegankelijkheidsprofiel',
					'visionImpairedMode' => array(
						'title' => 'Modus voor visuele beperkingen',
						'desc'  => 'Verbetert de visuele elementen van de website',
						'on'    => 'INGESCHAKELD',
						'off'   => 'UITGESCHAKELD',
					),
					'seizureSafeProfile' => array(
						'title' => 'Veiligheidsprofiel voor aanvallen',
						'desc'  => 'Vermindert knipperende lichten en verbetert de kleuren',
						'on'    => 'INGESCHAKELD',
						'off'   => 'UITGESCHAKELD',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-vriendelijke modus',
						'desc'  => 'Gefocuste navigatie zonder afleidingen',
						'on'    => 'INGESCHAKELD',
						'off'   => 'UITGESCHAKELD',
					),
					'blindnessMode'      => array(
						'title' => 'Modus voor blindheid',
						'desc'  => 'Vermindert afleidingen en verbetert de focus',
						'on'    => 'INGESCHAKELD',
						'off'   => 'UITGESCHAKELD',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Veilige modus voor epilepsie',
						'desc'  => 'Vermindert kleuren en stopt knipperen',
						'on'    => 'INGESCHAKELD',
						'off'   => 'UITGESCHAKELD',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Inhoudsmodules',
					'colorModules'       => 'Kleurmodules',
					'orientationModules' => 'Oriëntatiemodules',
				),
				'features'               => array(
					'skipToContent'      => 'Naar inhoud springen',
					'skipChoose'         => 'Kiezen...',
					'skipMain'           => 'Hoofdinhoud',
					'skipNavigation'     => 'Navigatie',
					'skipFooter'         => 'Voettekst',
					'biggerText'         => 'Lettergrootte',
					'highlightLinks'     => 'Markeer links',
					'lineHeight'         => 'Regelhoogte',
					'readableFont'       => 'Leesbaar lettertype',
					'cursor'             => 'Grote cursor',
					'textMagnifier'      => 'Tekst vergrootglas',
					'dyslexicFont'       => 'Lettertype voor dyslexie',
					'alignDefault'       => 'Tekst uitlijnen',
					'alignLeft'          => 'Links',
					'alignCenter'        => 'Gecentreerd',
					'alignRight'         => 'Rechts',
					'letterSpacing'      => 'Letterafstand',
					'fontWeight'         => 'Lettergewicht',
					'darkContrast'       => 'Donker contrast',
					'lightContrast'      => 'Licht contrast',
					'highContrast'       => 'Hoog contrast',
					'monochrome'         => 'Monochroom',
					'saturation'         => 'Verzadiging',
					'readingLine'        => 'Leeslijn',
					'readingMask'        => 'Leesmasker',
					'readPage'           => 'Lees pagina',
					'keyboardNavigation' => 'Navigatie via toetsenbord',
					'hideImages'         => 'Verberg afbeeldingen',
					'muteSounds'         => 'Geluiden dempen',
					'highlightTitles'    => 'Markeer titels',
					'highlightAll'       => 'Markeer inhoud',
					'stopAnimations'     => 'Stop animaties',
					'resetSettings'      => 'Instellingen herstellen',
				),
			),
			'dk'    => array(
				'global'                 => array(
					'back'    => 'Tilbage',
					'default' => 'Standard',
				),
				'hideToolbar'            => array(
					'title'   => 'Hvor længe vil du skjule tilgængelighedsværktøjslinjen?',
					'radio1'  => 'Kun for denne session',
					'radio2'  => '24 timer',
					'radio3'  => 'En uge',
					'button1' => 'Ikke nu',
					'button2' => 'Skjul værktøjslinje',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browseren skal opdateres',
					'desc'  => 'Din browser understøtter ikke taleoutput. Opdater din browser, eller brug en med understøttelse af talesyntese (f.eks. Chrome, Edge, Safari).',
					'link'  => 'Sådan opdaterer du?',
				),
				'header'                 => array(
					'language'      => 'Dansk',
					'listLanguages' => $list_languages,
					'title'         => 'Tilgængelighedsindstillinger',
					'desc'          => 'Oprettet af',
					'anchor'        => 'OneTap',
					'statement'     => 'Erklæring',
					'hideToolbar'   => 'Skjul værktøjslinje',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Vælg din tilgængelighedsprofil',
					'visionImpairedMode' => array(
						'title' => 'Tilstand for synshandicap',
						'desc'  => 'Forbedrer de visuelle elementer på siden',
						'on'    => 'TÆNDT',
						'off'   => 'SLUKKET',
					),
					'seizureSafeProfile' => array(
						'title' => 'Sikkerhedsprofil for anfald',
						'desc'  => 'Reducerer blink og forbedrer farverne',
						'on'    => 'TÆNDT',
						'off'   => 'SLUKKET',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-venlig tilstand',
						'desc'  => 'Fokuseret navigation uden forstyrrelser',
						'on'    => 'TÆNDT',
						'off'   => 'SLUKKET',
					),
					'blindnessMode'      => array(
						'title' => 'Tilstand for blindhed',
						'desc'  => 'Reducerer distraktioner og forbedrer fokus',
						'on'    => 'TÆNDT',
						'off'   => 'SLUKKET',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Sikker tilstand for epilepsi',
						'desc'  => 'Reducerer farver og stopper blinkning',
						'on'    => 'TÆNDT',
						'off'   => 'SLUKKET',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Indholdsmoduler',
					'colorModules'       => 'Farve moduler',
					'orientationModules' => 'Orientationsmoduler',
				),
				'features'               => array(
					'skipToContent'      => 'Spring til indhold',
					'skipChoose'         => 'Vælg...',
					'skipMain'           => 'Hovedindhold',
					'skipNavigation'     => 'Navigation',
					'skipFooter'         => 'Sidefod',
					'biggerText'         => 'Skriftstørrelse',
					'highlightLinks'     => 'Fremhæv links',
					'lineHeight'         => 'Linjehøjde',
					'readableFont'       => 'Læsbar skrifttype',
					'cursor'             => 'Stor cursor',
					'textMagnifier'      => 'Tekstforstørrelse',
					'dyslexicFont'       => 'Skrifttype til dysleksi',
					'alignDefault'       => 'Juster tekst',
					'alignLeft'          => 'Venstre',
					'alignCenter'        => 'Centreret',
					'alignRight'         => 'Højre',
					'letterSpacing'      => 'Bogstavafstand',
					'fontWeight'         => 'Skriftvægt',
					'darkContrast'       => 'Mørk kontrast',
					'lightContrast'      => 'Lys kontrast',
					'highContrast'       => 'Høj kontrast',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Mætning',
					'readingLine'        => 'Læselinje',
					'readingMask'        => 'Læsemask',
					'readPage'           => 'Læs side',
					'keyboardNavigation' => 'Tastaturnavigation',
					'hideImages'         => 'Skjul billeder',
					'muteSounds'         => 'Lydløs',
					'highlightTitles'    => 'Fremhæv titler',
					'highlightAll'       => 'Fremhæv indhold',
					'stopAnimations'     => 'Stop animationer',
					'resetSettings'      => 'Nulstil indstillinger',
				),
			),
			'gr'    => array(
				'global'                 => array(
					'back'    => 'Πίσω',
					'default' => 'Προεπιλογή',
				),
				'hideToolbar'            => array(
					'title'   => 'Για πόσο καιρό θέλετε να αποκρύψετε τη γραμμή προσβασιμότητας;',
					'radio1'  => 'Μόνο για αυτή τη συνεδρία',
					'radio2'  => '24 ώρες',
					'radio3'  => 'Μία εβδομάδα',
					'button1' => 'Όχι τώρα',
					'button2' => 'Απόκρυψη γραμμής εργαλείων',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Το πρόγραμμα περιήγησης χρειάζεται ενημέρωση',
					'desc'  => 'Το πρόγραμμα περιήγησής σας δεν υποστηρίζει έξοδο ομιλίας. Ενημερώστε το ή χρησιμοποιήστε ένα πρόγραμμα περιήγησης με ενεργοποιημένη σύνθεση φωνής (π.χ. Chrome, Edge, Safari).',
					'link'  => 'Πώς να το ενημερώσετε;',
				),
				'header'                 => array(
					'language'      => 'Ελληνικά',
					'listLanguages' => $list_languages,
					'title'         => 'Ρυθμίσεις Προσβασιμότητας',
					'desc'          => 'Δημιουργήθηκε από',
					'anchor'        => 'OneTap',
					'statement'     => 'Δήλωση',
					'hideToolbar'   => 'Απόκρυψη γραμμής εργαλείων',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Επιλέξτε το προφίλ προσβασιμότητας σας',
					'visionImpairedMode' => array(
						'title' => 'Λειτουργία για άτομα με αναπηρία όρασης',
						'desc'  => 'Βελτιώνει τα οπτικά στοιχεία της σελίδας',
						'on'    => 'ΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
						'off'   => 'ΑΠΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
					),
					'seizureSafeProfile' => array(
						'title' => 'Προφίλ ασφαλείας για επιληψία',
						'desc'  => 'Μειώνει τις αναλαμπές και βελτιώνει τα χρώματα',
						'on'    => 'ΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
						'off'   => 'ΑΠΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Λειτουργία φιλική προς ADHD',
						'desc'  => 'Εστιασμένη πλοήγηση χωρίς περισπασμούς',
						'on'    => 'ΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
						'off'   => 'ΑΠΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
					),
					'blindnessMode'      => array(
						'title' => 'Λειτουργία για τύφλωση',
						'desc'  => 'Μειώνει τις περισπασμούς και βελτιώνει τη συγκέντρωση',
						'on'    => 'ΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
						'off'   => 'ΑΠΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Ασφαλής λειτουργία για επιληψία',
						'desc'  => 'Μειώνει τα χρώματα και σταματά τις αναλαμπές',
						'on'    => 'ΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
						'off'   => 'ΑΠΕΝΕΡΓΟΠΟΙΗΜΕΝΟ',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Μονάδες περιεχομένου',
					'colorModules'       => 'Μονάδες χρωμάτων',
					'orientationModules' => 'Μονάδες προσανατολισμού',
				),
				'features'               => array(
					'skipToContent'      => 'Μετάβαση στο περιεχόμενο',
					'skipChoose'         => 'Επιλέξτε...',
					'skipMain'           => 'Κύριο περιεχόμενο',
					'skipNavigation'     => 'Πλοήγηση',
					'skipFooter'         => 'Υποσέλιδο',
					'biggerText'         => 'Μέγεθος γραμματοσειράς',
					'highlightLinks'     => 'Επισήμανση συνδέσμων',
					'lineHeight'         => 'Ύψος γραμμής',
					'readableFont'       => 'Ευανάγνωστη γραμματοσειρά',
					'cursor'             => 'Μεγάλος δείκτης',
					'textMagnifier'      => 'Μεγεθυντικό φακό κειμένου',
					'dyslexicFont'       => 'Γραμματοσειρά για δυσλεξία',
					'alignDefault'       => 'Στοίχιση κειμένου',
					'alignLeft'          => 'Αριστερά',
					'alignCenter'        => 'Κεντραρισμένο',
					'alignRight'         => 'Δεξιά',
					'letterSpacing'      => 'Απόσταση γραμμάτων',
					'fontWeight'         => 'Βάρος γραμματοσειράς',
					'darkContrast'       => 'Σκούρη αντίθεση',
					'lightContrast'      => 'Ανοιχτή αντίθεση',
					'highContrast'       => 'Υψηλή αντίθεση',
					'monochrome'         => 'Μονόχρωμο',
					'saturation'         => 'Κορεσμός',
					'readingLine'        => 'Γραμμή ανάγνωσης',
					'readingMask'        => 'Μάσκα ανάγνωσης',
					'readPage'           => 'Διαβάστε τη σελίδα',
					'keyboardNavigation' => 'Πλοήγηση μέσω πληκτρολογίου',
					'hideImages'         => 'Απόκρυψη εικόνων',
					'muteSounds'         => 'Απενεργοποίηση ήχου',
					'highlightTitles'    => 'Επισήμανση τίτλων',
					'highlightAll'       => 'Επισήμανση περιεχομένου',
					'stopAnimations'     => 'Σταματήστε τις κινούμενες εικόνες',
					'resetSettings'      => 'Επαναφορά ρυθμίσεων',
				),
			),
			'cz'    => array(
				'global'                 => array(
					'back'    => 'Zpět',
					'default' => 'Výchozí',
				),
				'hideToolbar'            => array(
					'title'   => 'Na jak dlouho chcete skrýt panel usnadnění?',
					'radio1'  => 'Pouze pro tuto relaci',
					'radio2'  => '24 hodin',
					'radio3'  => 'Jeden týden',
					'button1' => 'Teď ne',
					'button2' => 'Skrýt panel',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Prohlížeč je třeba aktualizovat',
					'desc'  => 'Váš prohlížeč nepodporuje výstup řeči. Aktualizujte jej nebo použijte prohlížeč, který podporuje syntézu řeči (např. Chrome, Edge, Safari).',
					'link'  => 'Jak aktualizovat?',
				),
				'header'                 => array(
					'language'      => 'Čeština',
					'listLanguages' => $list_languages,
					'title'         => 'Nastavení přístupnosti',
					'desc'          => 'Vytvořeno',
					'anchor'        => 'OneTap',
					'statement'     => 'Prohlášení',
					'hideToolbar'   => 'Skrýt panel nástrojů',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Vyberte svůj přístupnostní profil',
					'visionImpairedMode' => array(
						'title' => 'Režim pro zrakově postižené',
						'desc'  => 'Zlepšuje vizuální prvky na stránce',
						'on'    => 'ZAŠKRTNUTO',
						'off'   => 'NEZAŠKRTNUTO',
					),
					'seizureSafeProfile' => array(
						'title' => 'Bezpečný profil pro epilepsii',
						'desc'  => 'Snižuje blikání a zlepšuje barvy',
						'on'    => 'ZAŠKRTNUTO',
						'off'   => 'NEZAŠKRTNUTO',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Režim přátelský k ADHD',
						'desc'  => 'Soustředěná navigace bez rozptýlení',
						'on'    => 'ZAŠKRTNUTO',
						'off'   => 'NEZAŠKRTNUTO',
					),
					'blindnessMode'      => array(
						'title' => 'Režim pro slepotu',
						'desc'  => 'Snižuje rozptýlení a zlepšuje soustředění',
						'on'    => 'ZAŠKRTNUTO',
						'off'   => 'NEZAŠKRTNUTO',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Bezpečný režim pro epilepsii',
						'desc'  => 'Snižuje barvy a zastavuje blikání',
						'on'    => 'ZAŠKRTNUTO',
						'off'   => 'NEZAŠKRTNUTO',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduly obsahu',
					'colorModules'       => 'Moduly barev',
					'orientationModules' => 'Moduly orientace',
				),
				'features'               => array(
					'skipToContent'      => 'Přejít na obsah',
					'skipChoose'         => 'Vybrat...',
					'skipMain'           => 'Hlavní obsah',
					'skipNavigation'     => 'Navigace',
					'skipFooter'         => 'Zápatí',
					'biggerText'         => 'Velikost písma',
					'highlightLinks'     => 'Zvýraznění odkazů',
					'lineHeight'         => 'Výška řádku',
					'readableFont'       => 'Čitelný font',
					'cursor'             => 'Velký ukazatel',
					'textMagnifier'      => 'Lupa na text',
					'dyslexicFont'       => 'Font pro dyslexii',
					'alignDefault'       => 'Zarovnat text',
					'alignLeft'          => 'Vlevo',
					'alignCenter'        => 'Na střed',
					'alignRight'         => 'Vpravo',
					'letterSpacing'      => 'Mezera mezi písmeny',
					'fontWeight'         => 'Tloušťka písma',
					'darkContrast'       => 'Tmavý kontrast',
					'lightContrast'      => 'Světlý kontrast',
					'highContrast'       => 'Vysoký kontrast',
					'monochrome'         => 'Monochromatický',
					'saturation'         => 'Sytost',
					'readingLine'        => 'Čtecí linka',
					'readingMask'        => 'Čtecí maska',
					'readPage'           => 'Přečíst stránku',
					'keyboardNavigation' => 'Navigace klávesnicí',
					'hideImages'         => 'Skrýt obrázky',
					'muteSounds'         => 'Ztlumit zvuky',
					'highlightTitles'    => 'Zvýraznit titulky',
					'highlightAll'       => 'Zvýraznit obsah',
					'stopAnimations'     => 'Zastavit animace',
					'resetSettings'      => 'Obnovit nastavení',
				),
			),
			'hu'    => array(
				'global'                 => array(
					'back'    => 'Vissza',
					'default' => 'Alapértelmezett',
				),
				'hideToolbar'            => array(
					'title'   => 'Meddig szeretné elrejteni az akadálymentesítési eszköztárat?',
					'radio1'  => 'Csak erre a munkamenetre',
					'radio2'  => '24 óra',
					'radio3'  => 'Egy hét',
					'button1' => 'Most nem',
					'button2' => 'Eszköztár elrejtése',
				),
				'unsupportedPageReader'  => array(
					'title' => 'A böngészőt frissíteni kell',
					'desc'  => 'A böngésző nem támogatja a beszédhang-kimenetet. Frissítse a böngészőt, vagy használjon olyat, amely támogatja a beszédfelismerést (pl. Chrome, Edge, Safari).',
					'link'  => 'Hogyan frissítsem?',
				),
				'header'                 => array(
					'language'      => 'Magyar',
					'listLanguages' => $list_languages,
					'title'         => 'Hozzáférhetőségi beállítások',
					'desc'          => 'Készítette',
					'anchor'        => 'OneTap',
					'statement'     => 'Nyilatkozat',
					'hideToolbar'   => 'Eszköztár elrejtése',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Válassza ki hozzáférhetőségi profilját',
					'visionImpairedMode' => array(
						'title' => 'Látássérült mód',
						'desc'  => 'Javítja az oldal vizuális elemeit',
						'on'    => 'BE',
						'off'   => 'KI',
					),
					'seizureSafeProfile' => array(
						'title' => 'Biztonságos profil epilepsziásoknak',
						'desc'  => 'Csökkenti a villogást és javítja a színeket',
						'on'    => 'BE',
						'off'   => 'KI',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-barát mód',
						'desc'  => 'Fókuszált navigáció zavaró tényezők nélkül',
						'on'    => 'BE',
						'off'   => 'KI',
					),
					'blindnessMode'      => array(
						'title' => 'Vak mód',
						'desc'  => 'Csökkenti a zavaró tényezőket és javítja a fókuszt',
						'on'    => 'BE',
						'off'   => 'KI',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Biztonságos epilepsziás mód',
						'desc'  => 'Csökkenti a színeket és megállítja a villogást',
						'on'    => 'BE',
						'off'   => 'KI',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Tartalom modulok',
					'colorModules'       => 'Szín modulok',
					'orientationModules' => 'Orientációs modulok',
				),
				'features'               => array(
					'skipToContent'      => 'Ugrás a tartalomhoz',
					'skipChoose'         => 'Válasszon...',
					'skipMain'           => 'Fő tartalom',
					'skipNavigation'     => 'Navigáció',
					'skipFooter'         => 'Lábléc',
					'biggerText'         => 'Betűméret',
					'highlightLinks'     => 'Linkek kiemelése',
					'lineHeight'         => 'Sormagasság',
					'readableFont'       => 'Olvasható betűtípus',
					'cursor'             => 'Nagy kurzor',
					'textMagnifier'      => 'Szöveg nagyító',
					'dyslexicFont'       => 'Diszlexiás betűtípus',
					'alignDefault'       => 'Szövegigazítás',
					'alignLeft'          => 'Balra',
					'alignCenter'        => 'Középre',
					'alignRight'         => 'Jobbra',
					'letterSpacing'      => 'Betűköz',
					'fontWeight'         => 'Betűvastagság',
					'darkContrast'       => 'Sötét kontraszt',
					'lightContrast'      => 'Világos kontraszt',
					'highContrast'       => 'Magas kontraszt',
					'monochrome'         => 'Monokróm',
					'saturation'         => 'Telítettség',
					'readingLine'        => 'Olvasási vonal',
					'readingMask'        => 'Olvasási maszk',
					'readPage'           => 'Oldal olvasása',
					'keyboardNavigation' => 'Billentyűzet navigáció',
					'hideImages'         => 'Képek elrejtése',
					'muteSounds'         => 'Hangok némítása',
					'highlightTitles'    => 'Címek kiemelése',
					'highlightAll'       => 'Tartalom kiemelése',
					'stopAnimations'     => 'Animációk leállítása',
					'resetSettings'      => 'Beállítások visszaállítása',
				),
			),
			'lt'    => array(
				'global'                 => array(
					'back'    => 'Atgal',
					'default' => 'Numatytasis',
				),
				'hideToolbar'            => array(
					'title'   => 'Kiek laiko norite slėpti prieinamumo įrankių juostą?',
					'radio1'  => 'Tik šiai sesijai',
					'radio2'  => '24 valandos',
					'radio3'  => 'Savaitė',
					'button1' => 'Ne dabar',
					'button2' => 'Slėpti juostą',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Naršyklę reikia atnaujinti',
					'desc'  => 'Jūsų naršyklė nepalaiko kalbos sintezės. Atnaujinkite naršyklę arba naudokite tokią, kuri palaiko kalbos sintezę (pvz., Chrome, Edge, Safari).',
					'link'  => 'Kaip atnaujinti?',
				),
				'header'                 => array(
					'language'      => 'Lietuvių',
					'listLanguages' => $list_languages,
					'title'         => 'Prieigos nustatymai',
					'desc'          => 'Sukūrė',
					'anchor'        => 'OneTap',
					'statement'     => 'Pareiškimas',
					'hideToolbar'   => 'Slėpti įrankių juostą',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Pasirinkite savo prieigos profilį',
					'visionImpairedMode' => array(
						'title' => 'Regėjimo sutrikimo režimas',
						'desc'  => 'Gerina vizualinius elementus puslapyje',
						'on'    => 'ĮJUNGTA',
						'off'   => 'IŠJUNGTA',
					),
					'seizureSafeProfile' => array(
						'title' => 'Saugi profilis epilepsijai',
						'desc'  => 'Mažina mirgėjimą ir gerina spalvas',
						'on'    => 'ĮJUNGTA',
						'off'   => 'IŠJUNGTA',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD draugiškas režimas',
						'desc'  => 'Fokusavimas be trikdžių',
						'on'    => 'ĮJUNGTA',
						'off'   => 'IŠJUNGTA',
					),
					'blindnessMode'      => array(
						'title' => 'Aklojo režimas',
						'desc'  => 'Mažina trikdžius ir gerina dėmesį',
						'on'    => 'ĮJUNGTA',
						'off'   => 'IŠJUNGTA',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsijai saugus režimas',
						'desc'  => 'Mažina spalvas ir sustabdo mirgėjimą',
						'on'    => 'ĮJUNGTA',
						'off'   => 'IŠJUNGTA',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Turinio moduliai',
					'colorModules'       => 'Spalvų moduliai',
					'orientationModules' => 'Orientacijos moduliai',
				),
				'features'               => array(
					'skipToContent'      => 'Pereiti prie turinio',
					'skipChoose'         => 'Pasirinkite...',
					'skipMain'           => 'Pagrindinis turinys',
					'skipNavigation'     => 'Naršymas',
					'skipFooter'         => 'Poraštė',
					'biggerText'         => 'Šrifto dydis',
					'highlightLinks'     => 'Nuorodų paryškinimas',
					'lineHeight'         => 'Eilutės aukštis',
					'readableFont'       => 'Lengvai skaitomas šriftas',
					'cursor'             => 'Didelis kursorius',
					'textMagnifier'      => 'Teksto didinamoji lupa',
					'dyslexicFont'       => 'Dysleksijai pritaikytas šriftas',
					'alignDefault'       => 'Lygiuoti tekstą',
					'alignLeft'          => 'Kairėje',
					'alignCenter'        => 'Centruota',
					'alignRight'         => 'Dešinėje',
					'letterSpacing'      => 'Rašto tarpai',
					'fontWeight'         => 'Šrifto storis',
					'darkContrast'       => 'Tamsus kontrastas',
					'lightContrast'      => 'Šviesus kontrastas',
					'highContrast'       => 'Aukštas kontrastas',
					'monochrome'         => 'Monochrominis',
					'saturation'         => 'Sotinimas',
					'readingLine'        => 'Skaitymo linija',
					'readingMask'        => 'Skaitymo uždanga',
					'readPage'           => 'Skaityti puslapį',
					'keyboardNavigation' => 'Klaviatūros navigacija',
					'hideImages'         => 'Slėpti nuotraukas',
					'muteSounds'         => 'Nutilinti garsus',
					'highlightTitles'    => 'Antraščių paryškinimas',
					'highlightAll'       => 'Turinio paryškinimas',
					'stopAnimations'     => 'Sustabdyti animacijas',
					'resetSettings'      => 'Atstatyti nustatymus',
				),
			),
			'lv'    => array(
				'global'                 => array(
					'back'    => 'Atpakaļ',
					'default' => 'Noklusējuma',
				),
				'hideToolbar'            => array(
					'title'   => 'Cik ilgi vēlaties paslēpt piekļūstamības rīkjoslu?',
					'radio1'  => 'Tikai šai sesijai',
					'radio2'  => '24 stundas',
					'radio3'  => 'Nedēļu',
					'button1' => 'Ne tagad',
					'button2' => 'Paslēpt rīkjoslu',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Pārlūkprogramma ir jāatjaunina',
					'desc'  => 'Jūsu pārlūkprogramma neatbalsta runas sintezatoru. Lūdzu, atjauniniet pārlūkprogrammu vai izmantojiet tādu, kas atbalsta runas sintēzi (piemēram, Chrome, Edge, Safari).',
					'link'  => 'Kā atjaunināt?',
				),
				'header'                 => array(
					'language'      => 'Latviešu',
					'listLanguages' => $list_languages,
					'title'         => 'Piekļuves iestatījumi',
					'desc'          => 'Izveidojis',
					'anchor'        => 'OneTap',
					'statement'     => 'Paziņojums',
					'hideToolbar'   => 'Slēpt rīkjoslu',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Izvēlieties savu piekļuves profilu',
					'visionImpairedMode' => array(
						'title' => 'Redzes traucējumu režīms',
						'desc'  => 'Uzlabos vizuālos elementus lapā',
						'on'    => 'IESLĒGTS',
						'off'   => 'IZSLĒGTS',
					),
					'seizureSafeProfile' => array(
						'title' => 'Drošais profils epilepsijas gadījumā',
						'desc'  => 'Samazina mirgošanu un uzlabo krāsas',
						'on'    => 'IESLĒGTS',
						'off'   => 'IZSLĒGTS',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD draudzīgs režīms',
						'desc'  => 'Fokusēta navigācija bez traucējumiem',
						'on'    => 'IESLĒGTS',
						'off'   => 'IZSLĒGTS',
					),
					'blindnessMode'      => array(
						'title' => 'Aklo režīms',
						'desc'  => 'Samazina traucējošos elementus un uzlabo fokusu',
						'on'    => 'IESLĒGTS',
						'off'   => 'IZSLĒGTS',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Drošais režīms epilepsijas gadījumā',
						'desc'  => 'Samazina krāsas un aptur mirgošanu',
						'on'    => 'IESLĒGTS',
						'off'   => 'IZSLĒGTS',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Satura moduļi',
					'colorModules'       => 'Krāsu moduļi',
					'orientationModules' => 'Orientācijas moduļi',
				),
				'features'               => array(
					'skipToContent'      => 'Pāriet uz saturu',
					'skipChoose'         => 'Izvēlieties...',
					'skipMain'           => 'Galvenais saturs',
					'skipNavigation'     => 'Navigācija',
					'skipFooter'         => 'Kājene',
					'biggerText'         => 'Fonta izmērs',
					'highlightLinks'     => 'Saistītās saites izcelšana',
					'lineHeight'         => 'Rindas augstums',
					'readableFont'       => 'Lasāms fonts',
					'cursor'             => 'Liels kursors',
					'textMagnifier'      => 'Teksta palielinātājs',
					'dyslexicFont'       => 'Dysleksijas fonts',
					'alignDefault'       => 'Izlīdzināt tekstu',
					'alignLeft'          => 'Pa kreisi',
					'alignCenter'        => 'Centrēts',
					'alignRight'         => 'Pa labi',
					'letterSpacing'      => 'Burbu attālums',
					'fontWeight'         => 'Fonta biezums',
					'darkContrast'       => 'Tumšs kontrasts',
					'lightContrast'      => 'Gaišs kontrasts',
					'highContrast'       => 'Augsts kontrasts',
					'monochrome'         => 'Monohroms',
					'saturation'         => 'Saturācija',
					'readingLine'        => 'Lasīšanas līnija',
					'readingMask'        => 'Lasīšanas maska',
					'readPage'           => 'Lasīt lapu',
					'keyboardNavigation' => 'Navigācija, izmantojot tastatūru',
					'hideImages'         => 'Slēpt attēlus',
					'muteSounds'         => 'Izslēgt skaņas',
					'highlightTitles'    => 'Virsrakstu izcelšana',
					'highlightAll'       => 'Satura izcelšana',
					'stopAnimations'     => 'Pārtraukt animācijas',
					'resetSettings'      => 'Atiestatīt iestatījumus',
				),
			),
			'ee'    => array(
				'global'                 => array(
					'back'    => 'Tagasi',
					'default' => 'Vaikimisi',
				),
				'hideToolbar'            => array(
					'title'   => 'Kui kauaks soovite juurdepääsetavuse tööriistariba peita?',
					'radio1'  => 'Ainult selleks seansiks',
					'radio2'  => '24 tundi',
					'radio3'  => 'Üheks nädalaks',
					'button1' => 'Mitte praegu',
					'button2' => 'Peida tööriistariba',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Brauserit tuleb uuendada',
					'desc'  => 'Teie brauser ei toeta kõneväljundit. Palun uuendage oma brauserit või kasutage sellist, mis toetab kõnesüntesaatorit (nt Chrome, Edge, Safari).',
					'link'  => 'Kuidas uuendada?',
				),
				'header'                 => array(
					'language'      => 'Eesti',
					'listLanguages' => $list_languages,
					'title'         => 'Juurdepääsu seaded',
					'desc'          => 'Loodud',
					'anchor'        => 'OneTap',
					'statement'     => 'Avaldus',
					'hideToolbar'   => 'Peida tööriistariba',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Valige oma ligipääsu profiil',
					'visionImpairedMode' => array(
						'title' => 'Nägemispuude režiim',
						'desc'  => 'Parandab visuaalseid elemente lehelt',
						'on'    => 'SEES',
						'off'   => 'VÄLJAS',
					),
					'seizureSafeProfile' => array(
						'title' => 'Epilepsiatõve profiil',
						'desc'  => 'Vähendab vilkumist ja parandab värve',
						'on'    => 'SEES',
						'off'   => 'VÄLJAS',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD sõbralik režiim',
						'desc'  => 'Parandab fookust ilma segajateta',
						'on'    => 'SEES',
						'off'   => 'VÄLJAS',
					),
					'blindnessMode'      => array(
						'title' => 'Pimeduse režiim',
						'desc'  => 'Vähendab häirivaid elemente ja parandab tähelepanu',
						'on'    => 'SEES',
						'off'   => 'VÄLJAS',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsia ohutu režiim',
						'desc'  => 'Vähendab värve ja peatab vilkumise',
						'on'    => 'SEES',
						'off'   => 'VÄLJAS',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Sisu moodulid',
					'colorModules'       => 'Värvi moodulid',
					'orientationModules' => 'Orientatsiooni moodulid',
				),
				'features'               => array(
					'skipToContent'      => 'Liigu sisule',
					'skipChoose'         => 'Vali...',
					'skipMain'           => 'Põhisisu',
					'skipNavigation'     => 'Navigeerimine',
					'skipFooter'         => 'Jalus',
					'biggerText'         => 'Fondi suurus',
					'highlightLinks'     => 'Linkide esiletõstmine',
					'lineHeight'         => 'Ridade kõrgus',
					'readableFont'       => 'Lugemisväline font',
					'cursor'             => 'Suur kursor',
					'textMagnifier'      => 'Teksti suurendaja',
					'dyslexicFont'       => 'Düslia font',
					'alignDefault'       => 'Joonda tekst',
					'alignLeft'          => 'Vasakule',
					'alignCenter'        => 'Keskele',
					'alignRight'         => 'Paremale',
					'letterSpacing'      => 'Tähe vaheline kaugus',
					'fontWeight'         => 'Fondi paksus',
					'darkContrast'       => 'Tume kontrast',
					'lightContrast'      => 'Hele kontrast',
					'highContrast'       => 'Kõrge kontrast',
					'monochrome'         => 'Monokroom',
					'saturation'         => 'Küllastus',
					'readingLine'        => 'Lugemislus',
					'readingMask'        => 'Lugemismask',
					'readPage'           => 'Loe lehekülge',
					'keyboardNavigation' => 'Klaviatuuri navigeerimine',
					'hideImages'         => 'Peida pildid',
					'muteSounds'         => 'Keela helid',
					'highlightTitles'    => 'Pealkirjade esiletõstmine',
					'highlightAll'       => 'Sisu esiletõstmine',
					'stopAnimations'     => 'Peata animatsioonid',
					'resetSettings'      => 'Lähtesta seaded',
				),
			),
			'hr'    => array(
				'global'                 => array(
					'back'    => 'Natrag',
					'default' => 'Zadano',
				),
				'hideToolbar'            => array(
					'title'   => 'Koliko dugo želite sakriti traku za pristupačnost?',
					'radio1'  => 'Samo za ovu sesiju',
					'radio2'  => '24 sata',
					'radio3'  => 'Tjedan dana',
					'button1' => 'Ne sada',
					'button2' => 'Sakrij traku',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Preglednik treba ažurirati',
					'desc'  => 'Vaš preglednik ne podržava govor. Ažurirajte preglednik ili koristite onaj koji podržava sintezu govora (npr. Chrome, Edge, Safari).',
					'link'  => 'Kako ažurirati?',
				),
				'header'                 => array(
					'language'      => 'Hrvatski',
					'listLanguages' => $list_languages,
					'title'         => 'Postavke pristupa',
					'desc'          => 'Izradio',
					'anchor'        => 'OneTap',
					'statement'     => 'Izjava',
					'hideToolbar'   => 'Sakrij alatnu traku',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Odaberite svoj pristupni profil',
					'visionImpairedMode' => array(
						'title' => 'Režim za osobe sa oštećenjem vida',
						'desc'  => 'Poboljšava vizualne elemente na stranici',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'seizureSafeProfile' => array(
						'title' => 'Siguran profil za epilepsiju',
						'desc'  => 'Smanjuje treperenje i poboljšava boje',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD prijateljski režim',
						'desc'  => 'Poboljšava fokus bez smetnji',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'blindnessMode'      => array(
						'title' => 'Režim za slijepe',
						'desc'  => 'Smanjuje smetnje i poboljšava fokus',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Siguran režim za epilepsiju',
						'desc'  => 'Smanjuje boje i zaustavlja treperenje',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduli sadržaja',
					'colorModules'       => 'Moduli boja',
					'orientationModules' => 'Moduli orijentacije',
				),
				'features'               => array(
					'skipToContent'      => 'Prijeđi na sadržaj',
					'skipChoose'         => 'Odaberite...',
					'skipMain'           => 'Glavni sadržaj',
					'skipNavigation'     => 'Navigacija',
					'skipFooter'         => 'Podnožje',
					'biggerText'         => 'Veličina fonta',
					'highlightLinks'     => 'Isticanje poveznica',
					'lineHeight'         => 'Visina linije',
					'readableFont'       => 'Čitljiv font',
					'cursor'             => 'Veliki kursor',
					'textMagnifier'      => 'Povećalo za tekst',
					'dyslexicFont'       => 'Font za disleksiju',
					'alignDefault'       => 'Poravnanje teksta',
					'alignLeft'          => 'Lijevo',
					'alignCenter'        => 'Centrirano',
					'alignRight'         => 'Desno',
					'letterSpacing'      => 'Razmak između slova',
					'fontWeight'         => 'Debljina fonta',
					'darkContrast'       => 'Tamni kontrast',
					'lightContrast'      => 'Svijetli kontrast',
					'highContrast'       => 'Visoki kontrast',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Zasićenost',
					'readingLine'        => 'Linija za čitanje',
					'readingMask'        => 'Maska za čitanje',
					'readPage'           => 'Čitaj stranicu',
					'keyboardNavigation' => 'Navigacija tipkovnicom',
					'hideImages'         => 'Sakrij slike',
					'muteSounds'         => 'Isključi zvukove',
					'highlightTitles'    => 'Isticanje naslova',
					'highlightAll'       => 'Isticanje sadržaja',
					'stopAnimations'     => 'Zaustavi animacije',
					'resetSettings'      => 'Vrati postavke',
				),
			),
			'ie'    => array(
				'global'                 => array(
					'back'    => 'Siar',
					'default' => 'Réamhshocraithe',
				),
				'hideToolbar'            => array(
					'title'   => 'Cé chomh fada is mian leat an barra inrochtaineachta a cheilt?',
					'radio1'  => 'Ach don seisiún seo amháin',
					'radio2'  => '24 uair an chloig',
					'radio3'  => 'Seachtain amháin',
					'button1' => 'Níl anois',
					'button2' => 'Folaigh an barra',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Your browser needs to be updated',
					'desc'  => 'Your browser does not support speech output. Please update your browser or use one with speech synthesis enabled (e.g. Chrome, Edge, Safari).',
					'link'  => 'How to update?',
				),
				'header'                 => array(
					'language'      => 'Gaeilge',
					'listLanguages' => $list_languages,
					'title'         => 'Socruithe Rochtana',
					'desc'          => 'Tógadh',
					'anchor'        => 'OneTap',
					'statement'     => 'Ráiteas',
					'hideToolbar'   => 'Folaigh an barra uirlisí',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Roghnaigh do phróifíl rochtana',
					'visionImpairedMode' => array(
						'title' => 'Modh do dhaoine le laige sa radharc',
						'desc'  => 'Feabhsaíonn na heilimintí amhairc ar an leathanach',
						'on'    => 'AS',
						'off'   => 'AMACH',
					),
					'seizureSafeProfile' => array(
						'title' => 'Próifíl sábháilte do ghalair scaoileadh',
						'desc'  => 'Ísliú ar na píosaí agus feabhsú na dathanna',
						'on'    => 'AS',
						'off'   => 'AMACH',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Modh comhchuí ADHD',
						'desc'  => 'Feabhsaíonn fócas gan cur isteach',
						'on'    => 'AS',
						'off'   => 'AMACH',
					),
					'blindnessMode'      => array(
						'title' => 'Modh do dhaoine le dall',
						'desc'  => 'Ísliú ar an bhfócas agus foirfeacht ar na heilimintí',
						'on'    => 'AS',
						'off'   => 'AMACH',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Modh sábháilte do eipiléipe',
						'desc'  => 'Ísliú ar na dathanna agus stop sé de na píosaí',
						'on'    => 'AS',
						'off'   => 'AMACH',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Modúil Ábhair',
					'colorModules'       => 'Modúil Dathanna',
					'orientationModules' => 'Modúil Treoshuíomh',
				),
				'features'               => array(
					'skipToContent'      => 'Léigh go dtí an t-ábhar',
					'skipChoose'         => 'Roghnaigh...',
					'skipMain'           => 'Príomhábhar',
					'skipNavigation'     => 'Nascleanúint',
					'skipFooter'         => 'Buntásc',
					'biggerText'         => 'Méid Cló',
					'highlightLinks'     => 'Samhlaigh Ceangail',
					'lineHeight'         => 'Airde Líne',
					'readableFont'       => 'Cló Léitheoireachta',
					'cursor'             => 'Cúrsóir Mór',
					'textMagnifier'      => 'Méadaí Téacs',
					'dyslexicFont'       => 'Cló do Dhiolachas',
					'alignDefault'       => 'Ailínigh téacs',
					'alignLeft'          => 'Ar chlé',
					'alignCenter'        => 'Láraithe',
					'alignRight'         => 'Ar dheis',
					'letterSpacing'      => 'Spásáil Litreach',
					'fontWeight'         => 'Tromas Cló',
					'darkContrast'       => 'Codarsnacht Dorcha',
					'lightContrast'      => 'Codarsnacht Éadrom',
					'highContrast'       => 'Codarsnacht Ard',
					'monochrome'         => 'Monachrómach',
					'saturation'         => 'Satail',
					'readingLine'        => 'Líne Léitheoireachta',
					'readingMask'        => 'Masg Léitheoireachta',
					'readPage'           => 'Léigh Leathanach',
					'keyboardNavigation' => 'Navigeacht Cnaipe',
					'hideImages'         => 'Folaigh Grianghraif',
					'muteSounds'         => 'Áthraigh na Gutha',
					'highlightTitles'    => 'Samhlaigh Teidil',
					'highlightAll'       => 'Samhlaigh Ábhar',
					'stopAnimations'     => 'Stop Animations',
					'resetSettings'      => 'Athshocraigh Socruithe',
				),
			),
			'bg'    => array(
				'global'                 => array(
					'back'    => 'Назад',
					'default' => 'По подразбиране',
				),
				'hideToolbar'            => array(
					'title'   => 'За колко време искате да скриете лентата за достъпност?',
					'radio1'  => 'Само за тази сесия',
					'radio2'  => '24 часа',
					'radio3'  => 'Една седмица',
					'button1' => 'Не сега',
					'button2' => 'Скрий лентата',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Браузърът трябва да бъде актуализиран',
					'desc'  => 'Вашият браузър не поддържа гласов изход. Моля, актуализирайте го или използвайте такъв с активирана речева синтеза (напр. Chrome, Edge, Safari).',
					'link'  => 'Как да актуализирате?',
				),
				'header'                 => array(
					'language'      => 'Български',
					'listLanguages' => $list_languages,
					'title'         => 'Настройки за достъп',
					'desc'          => 'Създадено от',
					'anchor'        => 'OneTap',
					'statement'     => 'Изявление',
					'hideToolbar'   => 'Скриване на лентата с инструменти',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Изберете вашия достъпен профил',
					'visionImpairedMode' => array(
						'title' => 'Режим за хора с увредено зрение',
						'desc'  => 'Подобряване на визуалните елементи на страницата',
						'on'    => 'ВКЛЮЧЕНО',
						'off'   => 'ИЗКЛЮЧЕНО',
					),
					'seizureSafeProfile' => array(
						'title' => 'Безопасен режим за епилепсия',
						'desc'  => 'Намаляване на мигването и подобряване на цветовете',
						'on'    => 'ВКЛЮЧЕНО',
						'off'   => 'ИЗКЛЮЧЕНО',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Приятелски режим за ADHD',
						'desc'  => 'Подобряване на фокуса без разсейване',
						'on'    => 'ВКЛЮЧЕНО',
						'off'   => 'ИЗКЛЮЧЕНО',
					),
					'blindnessMode'      => array(
						'title' => 'Режим за слепота',
						'desc'  => 'Намаляване на смущаващите елементи и подобряване на фокуса',
						'on'    => 'ВКЛЮЧЕНО',
						'off'   => 'ИЗКЛЮЧЕНО',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Безопасен режим за епилепсия',
						'desc'  => 'Намаляване на цветовете и спиране на мигането',
						'on'    => 'ВКЛЮЧЕНО',
						'off'   => 'ИЗКЛЮЧЕНО',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Модули за съдържание',
					'colorModules'       => 'Модули за цветове',
					'orientationModules' => 'Модули за ориентация',
				),
				'features'               => array(
					'skipToContent'      => 'Към съдържанието',
					'skipChoose'         => 'Изберете...',
					'skipMain'           => 'Основно съдържание',
					'skipNavigation'     => 'Навигация',
					'skipFooter'         => 'Долен колонтитул',
					'biggerText'         => 'Размер на шрифта',
					'highlightLinks'     => 'Подчертаване на линкове',
					'lineHeight'         => 'Височина на реда',
					'readableFont'       => 'Шрифт за четене',
					'cursor'             => 'Голям курсор',
					'textMagnifier'      => 'Лупа за текст',
					'dyslexicFont'       => 'Шрифт за дислексия',
					'alignDefault'       => 'Подравняване',
					'alignLeft'          => 'Наляво',
					'alignCenter'        => 'Центрирано',
					'alignRight'         => 'Надясно',
					'letterSpacing'      => 'Разстояние между буквите',
					'fontWeight'         => 'Дебелина на шрифта',
					'darkContrast'       => 'Тъмен контраст',
					'lightContrast'      => 'Светъл контраст',
					'highContrast'       => 'Висок контраст',
					'monochrome'         => 'Монохром',
					'saturation'         => 'Наситеност',
					'readingLine'        => 'Линия за четене',
					'readingMask'        => 'Маска за четене',
					'readPage'           => 'Прочетете страницата',
					'keyboardNavigation' => 'Навигация с клавиатура',
					'hideImages'         => 'Скриване на изображения',
					'muteSounds'         => 'Без звуци',
					'highlightTitles'    => 'Подчертаване на заглавия',
					'highlightAll'       => 'Подчертаване на съдържание',
					'stopAnimations'     => 'Спри анимациите',
					'resetSettings'      => 'Нулиране на настройките',
				),
			),
			'no'    => array(
				'global'                 => array(
					'back'    => 'Tilbake',
					'default' => 'Standard',
				),
				'hideToolbar'            => array(
					'title'   => 'Hvor lenge vil du skjule tilgjengelighetsverktøylinjen?',
					'radio1'  => 'Kun for denne økten',
					'radio2'  => '24 timer',
					'radio3'  => 'En uke',
					'button1' => 'Ikke nå',
					'button2' => 'Skjul verktøylinje',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Nettleseren må oppdateres',
					'desc'  => 'Nettleseren din støtter ikke taleutgang. Oppdater nettleseren eller bruk en som støtter talesyntese (f.eks. Chrome, Edge, Safari).',
					'link'  => 'Hvordan oppdatere?',
				),
				'header'                 => array(
					'language'      => 'Norsk',
					'listLanguages' => $list_languages,
					'title'         => 'Tilgjengelighetsinnstillinger',
					'desc'          => 'Drevet av',
					'anchor'        => 'OneTap',
					'statement'     => 'Erklæring',
					'hideToolbar'   => 'Skjul verktøylinje',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Velg din tilgjengelighetsprofil',
					'visionImpairedMode' => array(
						'title' => 'Synshemmet modus',
						'desc'  => 'Forbedrer nettstedets visuelle utseende',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'seizureSafeProfile' => array(
						'title' => 'Anfallsikker profil',
						'desc'  => 'Fjerner blink og reduserer farger',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD-vennlig modus',
						'desc'  => 'Fokusert og distraksjonsfri surfing',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'blindnessMode'      => array(
						'title' => 'Blindemodus',
						'desc'  => 'Reduserer distraksjoner, forbedrer fokus',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsisikker modus',
						'desc'  => 'Demper farger og stopper blinking',
						'on'    => 'PÅ',
						'off'   => 'AV',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Innholdsmoduler',
					'colorModules'       => 'Fargemoduler',
					'orientationModules' => 'Orientasjonsmoduler',
				),
				'features'               => array(
					'skipToContent'      => 'Hopp til innhold',
					'skipChoose'         => 'Velg...',
					'skipMain'           => 'Hovedinnhold',
					'skipNavigation'     => 'Navigasjon',
					'skipFooter'         => 'Bunntekst',
					'biggerText'         => 'Skriftstørrelse',
					'highlightLinks'     => 'Fremhev lenker',
					'lineHeight'         => 'Linjeavstand',
					'readableFont'       => 'Lesbar skrifttype',
					'cursor'             => 'Stor markør',
					'textMagnifier'      => 'Tekstforstørrelse',
					'dyslexicFont'       => 'Dysleksivennlig skrifttype',
					'alignDefault'       => 'Juster tekst',
					'alignLeft'          => 'Venstre',
					'alignCenter'        => 'Sentrert',
					'alignRight'         => 'Høyre',
					'letterSpacing'      => 'Bokstavavstand',
					'fontWeight'         => 'Skriftvekt',
					'darkContrast'       => 'Mørk kontrast',
					'lightContrast'      => 'Lys kontrast',
					'highContrast'       => 'Høy kontrast',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Metning',
					'readingLine'        => 'Leselinje',
					'readingMask'        => 'Lesemaske',
					'readPage'           => 'Les siden',
					'keyboardNavigation' => 'Tastaturnavigasjon',
					'hideImages'         => 'Skjul bilder',
					'muteSounds'         => 'Demp lyder',
					'highlightTitles'    => 'Fremhev titler',
					'highlightAll'       => 'Fremhev innhold',
					'stopAnimations'     => 'Stopp animasjoner',
					'resetSettings'      => 'Tilbakestill innstillinger',
				),
			),
			'tr'    => array(
				'global'                 => array(
					'back'    => 'Geri',
					'default' => 'Varsayılan',
				),
				'hideToolbar'            => array(
					'title'   => 'Erişilebilirlik araç çubuğunu ne kadar süre gizlemek istiyorsunuz?',
					'radio1'  => 'Yalnızca bu oturum için',
					'radio2'  => '24 saat',
					'radio3'  => 'Bir hafta',
					'button1' => 'Şimdi değil',
					'button2' => 'Araç çubuğunu gizle',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Tarayıcının güncellenmesi gerekiyor',
					'desc'  => 'Tarayıcınız konuşma çıkışını desteklemiyor. Lütfen tarayıcınızı güncelleyin veya konuşma sentezi destekleyen bir tarayıcı kullanın (ör. Chrome, Edge, Safari).',
					'link'  => 'Nasıl güncellenir?',
				),
				'header'                 => array(
					'language'      => 'Türkçe',
					'listLanguages' => $list_languages,
					'title'         => 'Erişilebilirlik Ayarları',
					'desc'          => 'Tarafından desteklenmektedir',
					'anchor'        => 'OneTap',
					'statement'     => 'Açıklama',
					'hideToolbar'   => 'Araç çubuğunu gizle',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Erişilebilirlik profilinizi seçin',
					'visionImpairedMode' => array(
						'title' => 'Görme Engelli Modu',
						'desc'  => 'Web sitesinin görselini geliştirir',
						'on'    => 'AÇIK',
						'off'   => 'KAPALI',
					),
					'seizureSafeProfile' => array(
						'title' => 'Nöbet Güvenli Profili',
						'desc'  => 'Parlamaları temizler ve renkleri azaltır',
						'on'    => 'AÇIK',
						'off'   => 'KAPALI',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'DEHB Dostu Mod',
						'desc'  => 'Odaklanmış ve dikkat dağıtmayan gezinme',
						'on'    => 'AÇIK',
						'off'   => 'KAPALI',
					),
					'blindnessMode'      => array(
						'title' => 'Körlük Modu',
						'desc'  => 'Dikkat dağınıklığını azaltır, odağı artırır',
						'on'    => 'AÇIK',
						'off'   => 'KAPALI',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsi Güvenli Mod',
						'desc'  => 'Renkleri kısar ve yanıp sönmeyi durdurur',
						'on'    => 'AÇIK',
						'off'   => 'KAPALI',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'İçerik Modülleri',
					'colorModules'       => 'Renk Modülleri',
					'orientationModules' => 'Yönlendirme Modülleri',
				),
				'features'               => array(
					'skipToContent'      => 'İçeriğe geç',
					'skipChoose'         => 'Seçin...',
					'skipMain'           => 'Ana içerik',
					'skipNavigation'     => 'Gezinme',
					'skipFooter'         => 'Alt bilgi',
					'biggerText'         => 'Yazı Boyutu',
					'highlightLinks'     => 'Bağlantıları Vurgula',
					'lineHeight'         => 'Satır Yüksekliği',
					'readableFont'       => 'Okunabilir Yazı Tipi',
					'cursor'             => 'Büyük İmleç',
					'textMagnifier'      => 'Metin Büyüteci',
					'dyslexicFont'       => 'Disleksi Dostu Yazı Tipi',
					'alignDefault'       => 'Metni hizala',
					'alignLeft'          => 'Sol',
					'alignCenter'        => 'Ortalanmış',
					'alignRight'         => 'Sağ',
					'letterSpacing'      => 'Harf Aralığı',
					'fontWeight'         => 'Yazı Kalınlığı',
					'darkContrast'       => 'Koyu Kontrast',
					'lightContrast'      => 'Açık Kontrast',
					'highContrast'       => 'Yüksek Kontrast',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Doygunluk',
					'readingLine'        => 'Okuma Satırı',
					'readingMask'        => 'Okuma Maskesi',
					'readPage'           => 'Sayfayı Oku',
					'keyboardNavigation' => 'Klavye ile Gezinme',
					'hideImages'         => 'Görüntüleri Gizle',
					'muteSounds'         => 'Sesleri Kapat',
					'highlightTitles'    => 'Başlıkları Vurgula',
					'highlightAll'       => 'İçeriği Vurgula',
					'stopAnimations'     => 'Animasyonları Durdur',
					'resetSettings'      => 'Ayarları Sıfırla',
				),
			),
			'id'    => array(
				'global'                 => array(
					'back'    => 'Kembali',
					'default' => 'Bawaan',
				),
				'hideToolbar'            => array(
					'title'   => 'Berapa lama Anda ingin menyembunyikan toolbar aksesibilitas?',
					'radio1'  => 'Hanya untuk sesi ini',
					'radio2'  => '24 jam',
					'radio3'  => 'Satu minggu',
					'button1' => 'Tidak sekarang',
					'button2' => 'Sembunyikan toolbar',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browser perlu diperbarui',
					'desc'  => 'Browser Anda tidak mendukung output suara. Silakan perbarui browser Anda atau gunakan yang mendukung sintesis suara (misalnya Chrome, Edge, Safari).',
					'link'  => 'Cara memperbarui?',
				),
				'header'                 => array(
					'language'      => 'Bahasa Indonesia',
					'listLanguages' => $list_languages,
					'title'         => 'Pengaturan Aksesibilitas',
					'desc'          => 'Didukung oleh',
					'anchor'        => 'OneTap',
					'statement'     => 'Pernyataan',
					'hideToolbar'   => 'Sembunyikan bilah alat',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Pilih profil aksesibilitas Anda',
					'visionImpairedMode' => array(
						'title' => 'Mode Penglihatan Terganggu',
						'desc'  => 'Meningkatkan visual situs web',
						'on'    => 'NYALAKAN',
						'off'   => 'MATIKAN',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil Aman dari Kejang',
						'desc'  => 'Menghilangkan kedipan & mengurangi warna',
						'on'    => 'NYALAKAN',
						'off'   => 'MATIKAN',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Mode Ramah DEHB',
						'desc'  => 'Menjelajahi dengan fokus, tanpa gangguan',
						'on'    => 'NYALAKAN',
						'off'   => 'MATIKAN',
					),
					'blindnessMode'      => array(
						'title' => 'Mode Kebutaan',
						'desc'  => 'Mengurangi gangguan, meningkatkan fokus',
						'on'    => 'NYALAKAN',
						'off'   => 'MATIKAN',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Mode Aman Epilepsi',
						'desc'  => 'Menurunkan kecerahan warna dan menghentikan kedipan',
						'on'    => 'NYALAKAN',
						'off'   => 'MATIKAN',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Modul Konten',
					'colorModules'       => 'Modul Warna',
					'orientationModules' => 'Modul Orientasi',
				),
				'features'               => array(
					'skipToContent'      => 'Langsung ke konten',
					'skipChoose'         => 'Pilih...',
					'skipMain'           => 'Konten Utama',
					'skipNavigation'     => 'Navigasi',
					'skipFooter'         => 'Footer',
					'biggerText'         => 'Ukuran Font',
					'highlightLinks'     => 'Sorot Tautan',
					'lineHeight'         => 'Tinggi Baris',
					'readableFont'       => 'Font yang Mudah Dibaca',
					'cursor'             => 'Kursor Besar',
					'textMagnifier'      => 'Pembesar Teks',
					'dyslexicFont'       => 'Font Ramah Disleksia',
					'alignDefault'       => 'Ratakan teks',
					'alignLeft'          => 'Kiri',
					'alignCenter'        => 'Tengah',
					'alignRight'         => 'Kanan',
					'letterSpacing'      => 'Jarak Huruf',
					'fontWeight'         => 'Ketebalan Font',
					'darkContrast'       => 'Kontras Gelap',
					'lightContrast'      => 'Kontras Terang',
					'highContrast'       => 'Kontras Tinggi',
					'monochrome'         => 'Monokrom',
					'saturation'         => 'Kejenuhan',
					'readingLine'        => 'Garis Bacaan',
					'readingMask'        => 'Masker Bacaan',
					'readPage'           => 'Baca Halaman',
					'keyboardNavigation' => 'Navigasi Keyboard',
					'hideImages'         => 'Sembunyikan Gambar',
					'muteSounds'         => 'Matikan Suara',
					'highlightTitles'    => 'Sorot Judul',
					'highlightAll'       => 'Sorot Konten',
					'stopAnimations'     => 'Hentikan Animasi',
					'resetSettings'      => 'Setel Ulang Pengaturan',
				),
			),
			'pt-br' => array(
				'global'                 => array(
					'back'    => 'Voltar',
					'default' => 'Padrão',
				),
				'hideToolbar'            => array(
					'title'   => 'Por quanto tempo você deseja ocultar a barra de acessibilidade?',
					'radio1'  => 'Apenas para esta sessão',
					'radio2'  => '24 horas',
					'radio3'  => 'Uma semana',
					'button1' => 'Agora não',
					'button2' => 'Ocultar barra',
				),
				'unsupportedPageReader'  => array(
					'title' => 'O navegador precisa ser atualizado',
					'desc'  => 'Seu navegador não é compatível com saída de voz. Atualize seu navegador ou use um que tenha síntese de voz ativada (por exemplo, Chrome, Edge, Safari).',
					'link'  => 'Como atualizar?',
				),
				'header'                 => array(
					'language'      => 'Português (Brasil)',
					'listLanguages' => $list_languages,
					'title'         => 'Ajustes de Acessibilidade',
					'desc'          => 'Desenvolvido por',
					'anchor'        => 'OneTap',
					'statement'     => 'Declaração',
					'hideToolbar'   => 'Ocultar barra de ferramentas',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Selecione seu perfil de acessibilidade',
					'visionImpairedMode' => array(
						'title' => 'Modo de Deficiência Visual',
						'desc'  => 'Melhora os visuais do site',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'seizureSafeProfile' => array(
						'title' => 'Perfil Seguro para Convulsões',
						'desc'  => 'Remove piscadas e reduz as cores',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Modo Amigável para TDAH',
						'desc'  => 'Navegação com foco, sem distrações',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'blindnessMode'      => array(
						'title' => 'Modo de Cegueira',
						'desc'  => 'Reduz distrações, melhora o foco',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Modo Seguro para Epilepsia',
						'desc'  => 'Diminui cores e para piscadas',
						'on'    => 'LIGADO',
						'off'   => 'DESLIGADO',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Módulos de Conteúdo',
					'colorModules'       => 'Módulos de Cor',
					'orientationModules' => 'Módulos de Orientação',
				),
				'features'               => array(
					'skipToContent'      => 'Pular para o conteúdo',
					'skipChoose'         => 'Escolher...',
					'skipMain'           => 'Conteúdo principal',
					'skipNavigation'     => 'Navegação',
					'skipFooter'         => 'Rodapé',
					'biggerText'         => 'Tamanho da Fonte',
					'highlightLinks'     => 'Destacar Links',
					'lineHeight'         => 'Altura da Linha',
					'readableFont'       => 'Fonte Legível',
					'cursor'             => 'Cursor Grande',
					'textMagnifier'      => 'Lupa de Texto',
					'dyslexicFont'       => 'Fonte para Dislexia',
					'alignDefault'       => 'Alinhar texto',
					'alignLeft'          => 'Esquerda',
					'alignCenter'        => 'Centralizado',
					'alignRight'         => 'Direita',
					'letterSpacing'      => 'Espaçamento de Letras',
					'fontWeight'         => 'Peso da Fonte',
					'darkContrast'       => 'Contraste Escuro',
					'lightContrast'      => 'Contraste Claro',
					'highContrast'       => 'Alto Contraste',
					'monochrome'         => 'Monocromático',
					'saturation'         => 'Saturação',
					'readingLine'        => 'Linha de Leitura',
					'readingMask'        => 'Máscara de Leitura',
					'readPage'           => 'Ler Página',
					'keyboardNavigation' => 'Navegação por Teclado',
					'hideImages'         => 'Ocultar Imagens',
					'muteSounds'         => 'Silenciar Sons',
					'highlightTitles'    => 'Destacar Títulos',
					'highlightAll'       => 'Destacar Conteúdo',
					'stopAnimations'     => 'Parar Animações',
					'resetSettings'      => 'Redefinir Configurações',
				),
			),
			'ja'    => array(
				'global'                 => array(
					'back'    => '戻る',
					'default' => 'デフォルト',
				),
				'hideToolbar'            => array(
					'title'   => 'アクセシビリティツールバーをどのくらい非表示にしますか？',
					'radio1'  => 'このセッションのみ',
					'radio2'  => '24時間',
					'radio3'  => '1週間',
					'button1' => '今はしない',
					'button2' => 'ツールバーを非表示',
				),
				'unsupportedPageReader'  => array(
					'title' => 'ブラウザの更新が必要です',
					'desc'  => 'ご利用のブラウザは音声出力に対応していません。ブラウザを更新するか、音声合成に対応したブラウザをご使用ください（例：Chrome、Edge、Safari）。',
					'link'  => '更新方法はこちら',
				),
				'header'                 => array(
					'language'      => '日本語',
					'listLanguages' => $list_languages,
					'title'         => 'アクセシビリティ設定',
					'desc'          => '提供元',
					'anchor'        => 'OneTap',
					'statement'     => '声明',
					'hideToolbar'   => 'ツールバーを非表示にする',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'アクセシビリティプロファイルを選択',
					'visionImpairedMode' => array(
						'title' => '視覚障害者モード',
						'desc'  => 'ウェブサイトの視覚的な改善',
						'on'    => 'オン',
						'off'   => 'オフ',
					),
					'seizureSafeProfile' => array(
						'title' => '発作防止プロファイル',
						'desc'  => '点滅を防ぎ、色を減らします',
						'on'    => 'オン',
						'off'   => 'オフ',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHDフレンドリーモード',
						'desc'  => '集中してブラウジング、気を散らさない',
						'on'    => 'オン',
						'off'   => 'オフ',
					),
					'blindnessMode'      => array(
						'title' => '視覚障害モード',
						'desc'  => '注意散漫を減らし、集中を改善',
						'on'    => 'オン',
						'off'   => 'オフ',
					),
					'epilepsySafeMode'   => array(
						'title' => 'てんかん安全モード',
						'desc'  => '色を暗くし、点滅を止める',
						'on'    => 'オン',
						'off'   => 'オフ',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'コンテンツモジュール',
					'colorModules'       => 'カラーモジュール',
					'orientationModules' => 'オリエンテーションモジュール',
				),
				'features'               => array(
					'skipToContent'      => 'コンテンツへスキップ',
					'skipChoose'         => '選択...',
					'skipMain'           => 'メインコンテンツ',
					'skipNavigation'     => 'ナビゲーション',
					'skipFooter'         => 'フッター',
					'biggerText'         => 'フォントサイズ',
					'highlightLinks'     => 'リンクを強調表示',
					'lineHeight'         => '行の高さ',
					'readableFont'       => '読みやすいフォント',
					'cursor'             => '大きなカーソル',
					'textMagnifier'      => 'テキスト拡大鏡',
					'dyslexicFont'       => 'ディスレクシア用フォント',
					'alignDefault'       => 'テキストの配置',
					'alignLeft'          => '左',
					'alignCenter'        => '中央',
					'alignRight'         => '右',
					'letterSpacing'      => '文字間隔',
					'fontWeight'         => 'フォントの太さ',
					'darkContrast'       => 'ダークコントラスト',
					'lightContrast'      => 'ライトコントラスト',
					'highContrast'       => 'ハイコントラスト',
					'monochrome'         => 'モノクローム',
					'saturation'         => '彩度',
					'readingLine'        => '読み取りライン',
					'readingMask'        => '読み取りマスク',
					'readPage'           => 'ページを読む',
					'keyboardNavigation' => 'キーボードナビゲーション',
					'hideImages'         => '画像を非表示',
					'muteSounds'         => '音をミュート',
					'highlightTitles'    => 'タイトルを強調表示',
					'highlightAll'       => 'コンテンツを強調表示',
					'stopAnimations'     => 'アニメーションを停止',
					'resetSettings'      => '設定をリセット',
				),
			),
			'ko'    => array(
				'global'                 => array(
					'back'    => '뒤로',
					'default' => '기본값',
				),
				'hideToolbar'            => array(
					'title'   => '접근성 도구 모음을 얼마나 숨기시겠습니까?',
					'radio1'  => '이번 세션만',
					'radio2'  => '24시간',
					'radio3'  => '일주일',
					'button1' => '나중에',
					'button2' => '도구 모음 숨기기',
				),
				'unsupportedPageReader'  => array(
					'title' => '브라우저를 업데이트해야 합니다',
					'desc'  => '사용 중인 브라우저는 음성 출력을 지원하지 않습니다. 브라우저를 업데이트하거나 음성 합성이 가능한 브라우저를 사용하세요 (예: Chrome, Edge, Safari).',
					'link'  => '업데이트 방법',
				),
				'header'                 => array(
					'language'      => '한국어',
					'listLanguages' => $list_languages,
					'title'         => '접근성 설정',
					'desc'          => '제공자',
					'anchor'        => 'OneTap',
					'statement'     => '성명',
					'hideToolbar'   => '도구 모음 숨기기',
				),
				'multiFunctionalFeature' => array(
					'title'              => '접근성 프로필 선택',
					'visionImpairedMode' => array(
						'title' => '시각 장애인 모드',
						'desc'  => '웹사이트의 시각적 개선',
						'on'    => '켜기',
						'off'   => '끄기',
					),
					'seizureSafeProfile' => array(
						'title' => '발작 예방 프로필',
						'desc'  => '점멸을 줄이고 색상을 조정',
						'on'    => '켜기',
						'off'   => '끄기',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD 친화적 모드',
						'desc'  => '집중적인 탐색, 방해 없이',
						'on'    => '켜기',
						'off'   => '끄기',
					),
					'blindnessMode'      => array(
						'title' => '시각 장애 모드',
						'desc'  => '방해 요소를 줄이고 집중력 향상',
						'on'    => '켜기',
						'off'   => '끄기',
					),
					'epilepsySafeMode'   => array(
						'title' => '간질 예방 모드',
						'desc'  => '색을 어둡게 하고 깜박임을 멈춤',
						'on'    => '켜기',
						'off'   => '끄기',
					),
				),
				'titles'                 => array(
					'contentModules'     => '콘텐츠 모듈',
					'colorModules'       => '색상 모듈',
					'orientationModules' => '방향 모듈',
				),
				'features'               => array(
					'skipToContent'      => '본문으로 건너뛰기',
					'skipChoose'         => '선택...',
					'skipMain'           => '본문',
					'skipNavigation'     => '탐색',
					'skipFooter'         => '바닥글',
					'biggerText'         => '글꼴 크기',
					'highlightLinks'     => '링크 강조 표시',
					'lineHeight'         => '줄 높이',
					'readableFont'       => '읽기 쉬운 글꼴',
					'cursor'             => '큰 커서',
					'textMagnifier'      => '텍스트 확대경',
					'dyslexicFont'       => '디스렉시아용 글꼴',
					'alignDefault'       => '텍스트 정렬',
					'alignLeft'          => '왼쪽',
					'alignCenter'        => '가운데',
					'alignRight'         => '오른쪽',
					'letterSpacing'      => '글자 간격',
					'fontWeight'         => '글꼴 굵기',
					'darkContrast'       => '어두운 대비',
					'lightContrast'      => '밝은 대비',
					'highContrast'       => '높은 대비',
					'monochrome'         => '단색',
					'saturation'         => '채도',
					'readingLine'        => '읽기 라인',
					'readingMask'        => '읽기 마스크',
					'readPage'           => '페이지 읽기',
					'keyboardNavigation' => '키보드 탐색',
					'hideImages'         => '이미지 숨기기',
					'muteSounds'         => '소리 음소거',
					'highlightTitles'    => '제목 강조 표시',
					'highlightAll'       => '콘텐츠 강조 표시',
					'stopAnimations'     => '애니메이션 중지',
					'resetSettings'      => '설정 초기화',
				),
				'footer'                 => array(
					'accessibilityStatement' => '접근성 선언문',
					'version'                => '버전 ' . $plugin_version,
				),
			),
			'zh'    => array(
				'global'                 => array(
					'back'    => '返回',
					'default' => '默认',
				),
				'hideToolbar'            => array(
					'title'   => '您想隐藏辅助工具栏多长时间？',
					'radio1'  => '仅此会话',
					'radio2'  => '24小时',
					'radio3'  => '一周',
					'button1' => '暂不',
					'button2' => '隐藏工具栏',
				),
				'unsupportedPageReader'  => array(
					'title' => '需要更新浏览器',
					'desc'  => '您的浏览器不支持语音输出。请更新浏览器，或使用支持语音合成功能的浏览器（例如 Chrome、Edge、Safari）。',
					'link'  => '如何更新？',
				),
				'header'                 => array(
					'language'      => '简体中文',
					'listLanguages' => $list_languages,
					'title'         => '无障碍调整',
					'desc'          => '提供者',
					'anchor'        => 'OneTap',
					'statement'     => '声明',
					'hideToolbar'   => '隐藏工具栏',
				),
				'multiFunctionalFeature' => array(
					'title'              => '选择您的无障碍配置文件',
					'visionImpairedMode' => array(
						'title' => '视力障碍模式',
						'desc'  => '增强网站视觉效果',
						'on'    => '开',
						'off'   => '关',
					),
					'seizureSafeProfile' => array(
						'title' => '抗癫痫模式',
						'desc'  => '减少闪烁和调整颜色',
						'on'    => '开',
						'off'   => '关',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD友好模式',
						'desc'  => '专注浏览，无干扰',
						'on'    => '开',
						'off'   => '关',
					),
					'blindnessMode'      => array(
						'title' => '盲人模式',
						'desc'  => '减少干扰，增强集中力',
						'on'    => '开',
						'off'   => '关',
					),
					'epilepsySafeMode'   => array(
						'title' => '癫痫安全模式',
						'desc'  => '调暗颜色，停止闪烁',
						'on'    => '开',
						'off'   => '关',
					),
				),
				'titles'                 => array(
					'contentModules'     => '内容模块',
					'colorModules'       => '颜色模块',
					'orientationModules' => '方向模块',
				),
				'features'               => array(
					'skipToContent'      => '跳到主要内容',
					'skipChoose'         => '选择...',
					'skipMain'           => '主要内容',
					'skipNavigation'     => '导航',
					'skipFooter'         => '页脚',
					'biggerText'         => '字体大小',
					'highlightLinks'     => '突出显示链接',
					'lineHeight'         => '行高',
					'readableFont'       => '可读字体',
					'cursor'             => '大光标',
					'textMagnifier'      => '文本放大器',
					'dyslexicFont'       => '阅读障碍字体',
					'alignDefault'       => '文字对齐',
					'alignLeft'          => '左',
					'alignCenter'        => '居中',
					'alignRight'         => '右',
					'letterSpacing'      => '字母间距',
					'fontWeight'         => '字体粗细',
					'darkContrast'       => '深色对比',
					'lightContrast'      => '浅色对比',
					'highContrast'       => '高对比度',
					'monochrome'         => '单色',
					'saturation'         => '饱和度',
					'readingLine'        => '阅读线',
					'readingMask'        => '阅读遮罩',
					'readPage'           => '朗读页面',
					'keyboardNavigation' => '键盘导航',
					'hideImages'         => '隐藏图片',
					'muteSounds'         => '静音声音',
					'highlightTitles'    => '突出显示标题',
					'highlightAll'       => '突出显示内容',
					'stopAnimations'     => '停止动画',
					'resetSettings'      => '重置设置',
				),
				'footer'                 => array(
					'accessibilityStatement' => '无障碍声明',
					'version'                => '版本 ' . $plugin_version,
				),
			),
			'ar'    => array(
				'global'                 => array(
					'back'    => 'رجوع',
					'default' => 'افتراضي',
				),
				'hideToolbar'            => array(
					'title'   => 'كم من الوقت تريد إخفاء شريط أدوات إمكانية الوصول؟',
					'radio1'  => 'فقط لهذه الجلسة',
					'radio2'  => '24 ساعة',
					'radio3'  => 'أسبوع واحد',
					'button1' => 'ليس الآن',
					'button2' => 'إخفاء الشريط',
				),
				'unsupportedPageReader'  => array(
					'title' => 'يجب تحديث المتصفح',
					'desc'  => 'المتصفح الخاص بك لا يدعم إخراج الصوت. يرجى تحديث المتصفح أو استخدام متصفح يدعم تحويل النص إلى كلام (مثل Chrome أو Edge أو Safari).',
					'link'  => 'كيف يتم التحديث؟',
				),
				'header'                 => array(
					'language'      => 'العربية',
					'listLanguages' => $list_languages,
					'title'         => 'تعديلات الوصول',
					'desc'          => 'مزود بواسطة',
					'anchor'        => 'OneTap',
					'statement'     => 'بيان',
					'hideToolbar'   => 'إخفاء شريط الأدوات',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'اختر ملف التعريف للوصول الخاص بك',
					'visionImpairedMode' => array(
						'title' => 'وضع ضعف البصر',
						'desc'  => 'تعزيز مرئيات الموقع',
						'on'    => 'تشغيل',
						'off'   => 'إيقاف',
					),
					'seizureSafeProfile' => array(
						'title' => 'وضع آمن للنوبات',
						'desc'  => 'تقليل الوميض وتعديل الألوان',
						'on'    => 'تشغيل',
						'off'   => 'إيقاف',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'وضع صديق لاضطراب فرط الحركة',
						'desc'  => 'تصفح مركز، بدون تشتيت',
						'on'    => 'تشغيل',
						'off'   => 'إيقاف',
					),
					'blindnessMode'      => array(
						'title' => 'وضع العمى',
						'desc'  => 'تقليل التشويش، وتعزيز التركيز',
						'on'    => 'تشغيل',
						'off'   => 'إيقاف',
					),
					'epilepsySafeMode'   => array(
						'title' => 'وضع آمن للصرع',
						'desc'  => 'خفض الألوان ووقف الوميض',
						'on'    => 'تشغيل',
						'off'   => 'إيقاف',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'وحدات المحتوى',
					'colorModules'       => 'وحدات الألوان',
					'orientationModules' => 'وحدات التوجه',
				),
				'features'               => array(
					'skipToContent'      => 'تخطي إلى المحتوى',
					'skipChoose'         => 'اختر...',
					'skipMain'           => 'المحتوى الرئيسي',
					'skipNavigation'     => 'التنقل',
					'skipFooter'         => 'تذييل الصفحة',
					'biggerText'         => 'حجم الخط',
					'highlightLinks'     => 'تسليط الضوء على الروابط',
					'lineHeight'         => 'ارتفاع السطر',
					'readableFont'       => 'خط قابل للقراءة',
					'cursor'             => 'مؤشر كبير',
					'textMagnifier'      => 'مكبر النص',
					'dyslexicFont'       => 'خط مخصص لمرضى الديسلكسيا',
					'alignDefault'       => 'محاذاة النص',
					'alignLeft'          => 'يسار',
					'alignCenter'        => 'توسيط',
					'alignRight'         => 'يمين',
					'letterSpacing'      => 'تباعد الحروف',
					'fontWeight'         => 'سمك الخط',
					'darkContrast'       => 'تباين داكن',
					'lightContrast'      => 'تباين فاتح',
					'highContrast'       => 'تباين عالي',
					'monochrome'         => 'أحادي اللون',
					'saturation'         => 'التشبع',
					'readingLine'        => 'خط القراءة',
					'readingMask'        => 'قناع القراءة',
					'readPage'           => 'قراءة الصفحة',
					'keyboardNavigation' => 'التنقل عبر لوحة المفاتيح',
					'hideImages'         => 'إخفاء الصور',
					'muteSounds'         => 'كتم الأصوات',
					'highlightTitles'    => 'تسليط الضوء على العناوين',
					'highlightAll'       => 'تسليط الضوء على المحتوى',
					'stopAnimations'     => 'إيقاف الأنيميشينات',
					'resetSettings'      => 'إعادة تعيين الإعدادات',
				),
				'footer'                 => array(
					'accessibilityStatement' => 'بيان الوصول',
					'version'                => 'الإصدار ' . $plugin_version,
				),
			),
			'ru'    => array(
				'global'                 => array(
					'back'    => 'Назад',
					'default' => 'По умолчанию',
				),
				'hideToolbar'            => array(
					'title'   => 'На сколько вы хотите скрыть панель доступности?',
					'radio1'  => 'Только для этой сессии',
					'radio2'  => '24 часа',
					'radio3'  => 'Одна неделя',
					'button1' => 'Не сейчас',
					'button2' => 'Скрыть панель',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Браузер нужно обновить',
					'desc'  => 'Ваш браузер не поддерживает голосовой вывод. Обновите браузер или используйте тот, который поддерживает синтез речи (например, Chrome, Edge, Safari).',
					'link'  => 'Как обновить?',
				),
				'header'                 => array(
					'language'      => 'Русский',
					'listLanguages' => $list_languages,
					'title'         => 'Настройки доступности',
					'desc'          => 'Предоставлено',
					'anchor'        => 'OneTap',
					'statement'     => 'Заявление',
					'hideToolbar'   => 'Скрыть панель инструментов',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Выберите ваш профиль доступности',
					'visionImpairedMode' => array(
						'title' => 'Режим для людей с нарушениями зрения',
						'desc'  => 'Улучшает визуальные элементы сайта',
						'on'    => 'ВКЛ',
						'off'   => 'ВЫКЛ',
					),
					'seizureSafeProfile' => array(
						'title' => 'Безопасный профиль для людей с эпилепсией',
						'desc'  => 'Очищает вспышки и снижает яркость',
						'on'    => 'ВКЛ',
						'off'   => 'ВЫКЛ',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Режим для людей с СДВГ',
						'desc'  => 'Целенаправленный просмотр, без отвлечений',
						'on'    => 'ВКЛ',
						'off'   => 'ВЫКЛ',
					),
					'blindnessMode'      => array(
						'title' => 'Режим для людей с слепотой',
						'desc'  => 'Снижает отвлекающие элементы, улучшает концентрацию',
						'on'    => 'ВКЛ',
						'off'   => 'ВЫКЛ',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Режим безопасности от эпилепсии',
						'desc'  => 'Уменьшает цвета и останавливает мигание',
						'on'    => 'ВКЛ',
						'off'   => 'ВЫКЛ',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Модули контента',
					'colorModules'       => 'Модули цветов',
					'orientationModules' => 'Модули ориентации',
				),
				'features'               => array(
					'skipToContent'      => 'К содержанию',
					'skipChoose'         => 'Выберите...',
					'skipMain'           => 'Основное содержание',
					'skipNavigation'     => 'Навигация',
					'skipFooter'         => 'Нижний колонтитул',
					'biggerText'         => 'Размер шрифта',
					'highlightLinks'     => 'Выделить ссылки',
					'lineHeight'         => 'Высота строки',
					'readableFont'       => 'Читаемый шрифт',
					'cursor'             => 'Большой курсор',
					'textMagnifier'      => 'Увеличитель текста',
					'dyslexicFont'       => 'Шрифт для людей с дислексией',
					'alignDefault'       => 'Выравнивание текста',
					'alignLeft'          => 'Слева',
					'alignCenter'        => 'По центру',
					'alignRight'         => 'Справа',
					'letterSpacing'      => 'Межбуквенный интервал',
					'fontWeight'         => 'Толщина шрифта',
					'darkContrast'       => 'Темный контраст',
					'lightContrast'      => 'Светлый контраст',
					'highContrast'       => 'Высокий контраст',
					'monochrome'         => 'Монохромный',
					'saturation'         => 'Насыщенность',
					'readingLine'        => 'Читаемая линия',
					'readingMask'        => 'Читаемая маска',
					'readPage'           => 'Читать страницу',
					'keyboardNavigation' => 'Навигация с клавиатуры',
					'hideImages'         => 'Скрыть изображения',
					'muteSounds'         => 'Отключить звуки',
					'highlightTitles'    => 'Выделить заголовки',
					'highlightAll'       => 'Выделить контент',
					'stopAnimations'     => 'Остановить анимацию',
					'resetSettings'      => 'Сбросить настройки',
				),
				'footer'                 => array(
					'accessibilityStatement' => 'Заявление о доступности',
					'version'                => 'Версия ' . $plugin_version,
				),
			),
			'hi'    => array(
				'global'                 => array(
					'back'    => 'वापस',
					'default' => 'डिफ़ॉल्ट',
				),
				'hideToolbar'            => array(
					'title'   => 'आप एक्सेसिबिलिटी टूलबार को कितनी देर तक छिपाना चाहते हैं?',
					'radio1'  => 'केवल इस सत्र के लिए',
					'radio2'  => '24 घंटे',
					'radio3'  => 'एक सप्ताह',
					'button1' => 'अभी नहीं',
					'button2' => 'टूलबार छिपाएं',
				),
				'unsupportedPageReader'  => array(
					'title' => 'ब्राउज़र को अपडेट करने की आवश्यकता है',
					'desc'  => 'आपका ब्राउज़र वाक् आउटपुट का समर्थन नहीं करता है। कृपया अपना ब्राउज़र अपडेट करें या ऐसा ब्राउज़र इस्तेमाल करें जो स्पीच सिंथेसिस को सपोर्ट करता हो (जैसे Chrome, Edge, Safari)।',
					'link'  => 'कैसे अपडेट करें?',
				),
				'header'                 => array(
					'language'      => 'हिन्दी',
					'listLanguages' => $list_languages,
					'title'         => 'सुगम्यता समायोजन',
					'desc'          => 'द्वारा संचालित',
					'anchor'        => 'OneTap',
					'statement'     => 'बयान',
					'hideToolbar'   => 'टूलबार छिपाएं',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'अपनी सुगम्यता प्रोफ़ाइल चुनें',
					'visionImpairedMode' => array(
						'title' => 'दृष्टिहीन मोड',
						'desc'  => 'वेबसाइट की दृश्यता को बेहतर बनाता है',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'seizureSafeProfile' => array(
						'title' => 'मिर्गी से सुरक्षित प्रोफ़ाइल',
						'desc'  => 'चमक को हटाता है और रंगों को कम करता है',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD अनुकूल मोड',
						'desc'  => 'ध्यान केंद्रित, बिना विकर्षण के ब्राउज़िंग',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'blindnessMode'      => array(
						'title' => 'अंधत्व मोड',
						'desc'  => 'विकर्षणों को कम करता है, ध्यान केंद्रित करने में मदद करता है',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'epilepsySafeMode'   => array(
						'title' => 'मिर्गी सुरक्षित मोड',
						'desc'  => 'रंगों को हल्का करता है और झपकी को रोकता है',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'सामग्री मॉड्यूल',
					'colorModules'       => 'रंग मॉड्यूल',
					'orientationModules' => 'अभिविन्यास मॉड्यूल',
				),
				'features'               => array(
					'skipToContent'      => 'सामग्री पर जाएं',
					'skipChoose'         => 'चुनें...',
					'skipMain'           => 'मुख्य सामग्री',
					'skipNavigation'     => 'नेविगेशन',
					'skipFooter'         => 'फ़ुटर',
					'biggerText'         => 'फ़ॉन्ट आकार',
					'highlightLinks'     => 'लिंक्स को हाइलाइट करें',
					'lineHeight'         => 'लाइन हाइट',
					'readableFont'       => 'पठनीय फ़ॉन्ट',
					'cursor'             => 'बड़ा कर्सर',
					'textMagnifier'      => 'पाठ मैग्नीफायर',
					'dyslexicFont'       => 'डिस्लेक्सिक फ़ॉन्ट',
					'alignDefault'       => 'पाठ संरेखण',
					'alignLeft'          => 'बाएं',
					'alignCenter'        => 'केंद्रित',
					'alignRight'         => 'दाएं',
					'letterSpacing'      => 'अक्षर स्पेसिंग',
					'fontWeight'         => 'फ़ॉन्ट वजन',
					'darkContrast'       => 'गहरा कांट्रास्ट',
					'lightContrast'      => 'हल्का कांट्रास्ट',
					'highContrast'       => 'उच्च कांट्रास्ट',
					'monochrome'         => 'मोनोक्रोम',
					'saturation'         => 'संतृप्ति',
					'readingLine'        => 'पढ़ने की लाइन',
					'readingMask'        => 'पढ़ने की मास्क',
					'readPage'           => 'पृष्ठ पढ़ें',
					'keyboardNavigation' => 'किबोर्ड नेविगेशन',
					'hideImages'         => 'चित्र छिपाएं',
					'muteSounds'         => 'ध्वनियों को म्यूट करें',
					'highlightTitles'    => 'शीर्षक हाइलाइट करें',
					'highlightAll'       => 'सामग्री हाइलाइट करें',
					'stopAnimations'     => 'एनिमेशन रोकें',
					'resetSettings'      => 'सेटिंग्स रीसेट करें',
				),
				'footer'                 => array(
					'accessibilityStatement' => 'सुगम्यता कथन',
					'version'                => 'संस्करण ' . $plugin_version,
				),
			),
			'uk'    => array(
				'global'                 => array(
					'back'    => 'Назад',
					'default' => 'За замовчуванням',
				),
				'hideToolbar'            => array(
					'title'   => 'На скільки ви хочете приховати панель доступності?',
					'radio1'  => 'Лише для цієї сесії',
					'radio2'  => '24 години',
					'radio3'  => 'Один тиждень',
					'button1' => 'Не зараз',
					'button2' => 'Приховати панель',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Потрібно оновити браузер',
					'desc'  => 'Ваш браузер не підтримує озвучування. Оновіть браузер або скористайтеся тим, що підтримує синтез мовлення (наприклад, Chrome, Edge, Safari).',
					'link'  => 'Як оновити?',
				),
				'header'                 => array(
					'language'      => 'Українська',
					'listLanguages' => $list_languages,
					'title'         => 'Налаштування доступності',
					'desc'          => 'Підтримується',
					'anchor'        => 'OneTap',
					'statement'     => 'Заява',
					'hideToolbar'   => 'Сховати панель інструментів',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Оберіть свій профіль доступності',
					'visionImpairedMode' => array(
						'title' => 'Режим для осіб з порушеннями зору',
						'desc'  => 'Поліпшує візуальне сприйняття вебсайту',
						'on'    => 'ВКЛ.',
						'off'   => 'ВИКЛ.',
					),
					'seizureSafeProfile' => array(
						'title' => 'Профіль для безпеки від судом',
						'desc'  => 'Зменшує мерехтіння та колірні ефекти',
						'on'    => 'ВКЛ.',
						'off'   => 'ВИКЛ.',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Режим для осіб з ADHD',
						'desc'  => 'Фокусована навігація без відволікань',
						'on'    => 'ВКЛ.',
						'off'   => 'ВИКЛ.',
					),
					'blindnessMode'      => array(
						'title' => 'Режим для сліпих',
						'desc'  => 'Зменшує відволікання, покращує концентрацію',
						'on'    => 'ВКЛ.',
						'off'   => 'ВИКЛ.',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Режим безпеки від епілепсії',
						'desc'  => 'Зменшує кольори і зупиняє мерехтіння',
						'on'    => 'ВКЛ.',
						'off'   => 'ВИКЛ.',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Модулі контенту',
					'colorModules'       => 'Модулі кольорів',
					'orientationModules' => 'Модулі орієнтації',
				),
				'features'               => array(
					'skipToContent'      => 'До вмісту',
					'skipChoose'         => 'Виберіть...',
					'skipMain'           => 'Основний вміст',
					'skipNavigation'     => 'Навігація',
					'skipFooter'         => 'Нижній колонтитул',
					'biggerText'         => 'Розмір шрифту',
					'highlightLinks'     => 'Підсвітити посилання',
					'lineHeight'         => 'Висота рядка',
					'readableFont'       => 'Читабельний шрифт',
					'cursor'             => 'Великий курсор',
					'textMagnifier'      => 'Магніфікатор тексту',
					'dyslexicFont'       => 'Шрифт для дислексії',
					'alignDefault'       => 'Вирівнювання тексту',
					'alignLeft'          => 'Ліворуч',
					'alignCenter'        => 'По центру',
					'alignRight'         => 'Праворуч',
					'letterSpacing'      => 'Міжлітерний інтервал',
					'fontWeight'         => 'Товщина шрифту',
					'darkContrast'       => 'Темний контраст',
					'lightContrast'      => 'Світлий контраст',
					'highContrast'       => 'Високий контраст',
					'monochrome'         => 'Монохромний',
					'saturation'         => 'Насиченість',
					'readingLine'        => 'Лінія читання',
					'readingMask'        => 'Маска для читання',
					'readPage'           => 'Читати сторінку',
					'keyboardNavigation' => 'Навігація за допомогою клавіатури',
					'hideImages'         => 'Сховати зображення',
					'muteSounds'         => 'Вимкнути звук',
					'highlightTitles'    => 'Підсвітити заголовки',
					'highlightAll'       => 'Підсвітити контент',
					'stopAnimations'     => 'Зупинити анімації',
					'resetSettings'      => 'Скинути налаштування',
				),
				'footer'                 => array(
					'accessibilityStatement' => 'Заява про доступність',
					'version'                => 'Версія ' . $plugin_version,
				),
			),
			'sr'    => array(
				'global'                 => array(
					'back'    => 'Назад',
					'default' => 'Подразумевано',
				),
				'hideToolbar'            => array(
					'title'   => 'Колико дуго желите да сакријете траку за приступачност?',
					'radio1'  => 'Само за ову сесију',
					'radio2'  => '24 сата',
					'radio3'  => 'Недељу дана',
					'button1' => 'Не сада',
					'button2' => 'Сакриј траку',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Pregledač treba da se ažurira',
					'desc'  => 'Vaš pregledač ne podržava govor. Ažurirajte ga ili koristite pregledač koji podržava sintezu govora (npr. Chrome, Edge, Safari).',
					'link'  => 'Kako ažurirati?',
				),
				'header'                 => array(
					'language'      => 'Srpski',
					'listLanguages' => $list_languages,
					'title'         => 'Podešavanja pristupačnosti',
					'desc'          => 'Pomaže',
					'anchor'        => 'OneTap',
					'statement'     => 'Изјава',
					'hideToolbar'   => 'Сакриј траку са алаткама',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Izaberite svoj profil pristupačnosti',
					'visionImpairedMode' => array(
						'title' => 'Režim za osobe sa oštećenjem vida',
						'desc'  => 'Poboljšava vizuelno iskustvo na sajtu',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profil za sigurnost od napada',
						'desc'  => 'Smanjuje treperenje i boje',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Režim za osobe sa ADHD',
						'desc'  => 'Fokusirano pretraživanje bez distrakcija',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'blindnessMode'      => array(
						'title' => 'Režim za slepe',
						'desc'  => 'Smanjuje distrakcije, poboljšava koncentraciju',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Režim za sigurnost od epilepsije',
						'desc'  => 'Smanjuje boje i zaustavlja treperenje',
						'on'    => 'UKLJUČENO',
						'off'   => 'ISKLJUČENO',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Moduli sadržaja',
					'colorModules'       => 'Moduli boja',
					'orientationModules' => 'Moduli orijentacije',
				),
				'features'               => array(
					'skipToContent'      => 'Прескочи на садржај',
					'skipChoose'         => 'Изаберите...',
					'skipMain'           => 'Главни садржај',
					'skipNavigation'     => 'Навигација',
					'skipFooter'         => 'Подножје',
					'biggerText'         => 'Veličina fonta',
					'highlightLinks'     => 'Istakni linkove',
					'lineHeight'         => 'Visina linije',
					'readableFont'       => 'Font koji je lako čitljiv',
					'cursor'             => 'Veliki kursor',
					'textMagnifier'      => 'Magnifikator teksta',
					'dyslexicFont'       => 'Font za disleksiju',
					'alignDefault'       => 'Poravnanje teksta',
					'alignLeft'          => 'Lijevo',
					'alignCenter'        => 'Centrirano',
					'alignRight'         => 'Desno',
					'letterSpacing'      => 'Razmak između slova',
					'fontWeight'         => 'Debljina fonta',
					'darkContrast'       => 'Tamni kontrast',
					'lightContrast'      => 'Svetli kontrast',
					'highContrast'       => 'Visoki kontrast',
					'monochrome'         => 'Monohromatski',
					'saturation'         => 'Saturacija',
					'readingLine'        => 'Linija za čitanje',
					'readingMask'        => 'Maska za čitanje',
					'readPage'           => 'Čitaj stranicu',
					'keyboardNavigation' => 'Navigacija tastaturom',
					'hideImages'         => 'Sakrij slike',
					'muteSounds'         => 'Isključi zvuke',
					'highlightTitles'    => 'Istakni naslove',
					'highlightAll'       => 'Istakni sadržaj',
					'stopAnimations'     => 'Zaustavi animacije',
					'resetSettings'      => 'Poništi podešavanja',
				),
				'footer'                 => array(
					'accessibilityStatement' => 'Izjava o pristupačnosti',
					'version'                => 'Verzija ' . $plugin_version,
				),
			),
			'gb'    => array(
				'global'                 => array(
					'back'    => 'Back',
					'default' => 'Default',
				),
				'hideToolbar'            => array(
					'title'   => 'How long do you want to hide the toolbar?',
					'radio1'  => 'Only for this session',
					'radio2'  => '24 hours',
					'radio3'  => 'A Week',
					'button1' => 'Not Now',
					'button2' => 'Hide Toolbar',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Browser needs to be updated',
					'desc'  => 'Your browser doesn\'t support speech output. Please update your browser or use one with speech synthesis enabled (e.g. Chrome, Edge, Safari).',
					'link'  => 'How to Update?',
				),
				'header'                 => array(
					'language'      => 'English (UK)',
					'listLanguages' => $list_languages,
					'title'         => 'Accessibility Adjustments',
					'desc'          => 'Powered by',
					'anchor'        => 'OneTap',
					'statement'     => 'Statement',
					'hideToolbar'   => 'Hide Toolbar',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Select your accessibility profile',
					'visionImpairedMode' => array(
						'title' => 'Vision Impaired Mode',
						'desc'  => "Enhances website's visuals",
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'seizureSafeProfile' => array(
						'title' => 'Seizure Safe Profile',
						'desc'  => 'Clear flashes & reduces colour',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD Friendly Mode',
						'desc'  => 'Focused browsing, distraction-free',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'blindnessMode'      => array(
						'title' => 'Blindness Mode',
						'desc'  => 'Reduces distractions, improves focus',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Epilepsy Safe Mode',
						'desc'  => 'Dims colours and stops blinking',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Content Modules',
					'colorModules'       => 'Colour Modules',
					'orientationModules' => 'Orientation Modules',
				),
				'features'               => array(
					'skipToContent'      => 'Skip to content',
					'skipChoose'         => 'Choose...',
					'skipMain'           => 'Main Content',
					'skipNavigation'     => 'Navigation',
					'skipFooter'         => 'Footer',
					'biggerText'         => 'Font Size',
					'highlightLinks'     => 'Highlight Links',
					'lineHeight'         => 'Line Height',
					'readableFont'       => 'Readable Font',
					'cursor'             => 'Big Cursor',
					'textMagnifier'      => 'Text Magnifier',
					'dyslexicFont'       => 'Dyslexic Font',
					'alignDefault'       => 'Align Text',
					'alignLeft'          => 'Left',
					'alignCenter'        => 'Centred',
					'alignRight'         => 'Right',
					'letterSpacing'      => 'Letter Spacing',
					'fontWeight'         => 'Font Weight',
					'darkContrast'       => 'Dark Contrast',
					'lightContrast'      => 'Light Contrast',
					'highContrast'       => 'High Contrast',
					'monochrome'         => 'Monochrome',
					'saturation'         => 'Saturation',
					'readingLine'        => 'Reading Line',
					'readingMask'        => 'Reading Mask',
					'readPage'           => 'Read Page',
					'keyboardNavigation' => 'Keyboard Navigation',
					'hideImages'         => 'Hide Images',
					'muteSounds'         => 'Mute Sounds',
					'highlightTitles'    => 'Highlight Titles',
					'highlightAll'       => 'Highlight Content',
					'stopAnimations'     => 'Stop Animations',
					'resetSettings'      => 'Reset Settings',
				),
			),
			'ir'    => array(
				'global'                 => array(
					'back'    => 'بازگشت',
					'default' => 'پیش‌فرض',
				),
				'hideToolbar'            => array(
					'title'   => 'چقدر می‌خواهید نوار ابزار دسترسی را مخفی کنید؟',
					'radio1'  => 'فقط برای این جلسه',
					'radio2'  => '24 ساعت',
					'radio3'  => 'یک هفته',
					'button1' => 'الان نه',
					'button2' => 'مخفی کردن نوار ابزار',
				),
				'unsupportedPageReader'  => array(
					'title' => 'مرورگر نیاز به به‌روزرسانی دارد',
					'desc'  => 'مرورگر شما از خروجی صوتی پشتیبانی نمی‌کند. لطفاً مرورگر خود را به‌روزرسانی کنید یا از مرورگری استفاده کنید که سنتز گفتار را پشتیبانی می‌کند (مثل Chrome، Edge، Safari).',
					'link'  => 'چگونه به‌روزرسانی کنیم؟',
				),
				'header'                 => array(
					'language'      => 'فارسی',
					'listLanguages' => $list_languages,
					'title'         => 'تنظیمات دسترسی',
					'desc'          => 'قدرت گرفته از',
					'anchor'        => 'OneTap',
					'statement'     => 'بیانیه',
					'hideToolbar'   => 'مخفی کردن نوار ابزار',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'پروفایل دسترسی خود را انتخاب کنید',
					'visionImpairedMode' => array(
						'title' => 'حالت اختلال بینایی',
						'desc'  => 'بهبود عناصر بصری وب‌سایت',
						'on'    => 'روشن',
						'off'   => 'خاموش',
					),
					'seizureSafeProfile' => array(
						'title' => 'پروفایل ایمن برای تشنج',
						'desc'  => 'کاهش فلش‌ها و بهبود رنگ‌ها',
						'on'    => 'روشن',
						'off'   => 'خاموش',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'حالت دوستانه ADHD',
						'desc'  => 'مرور متمرکز بدون حواس‌پرتی',
						'on'    => 'روشن',
						'off'   => 'خاموش',
					),
					'blindnessMode'      => array(
						'title' => 'حالت نابینایی',
						'desc'  => 'کاهش حواس‌پرتی، بهبود تمرکز',
						'on'    => 'روشن',
						'off'   => 'خاموش',
					),
					'epilepsySafeMode'   => array(
						'title' => 'حالت ایمن صرع',
						'desc'  => 'کاهش رنگ‌ها و توقف چشمک‌زدن',
						'on'    => 'روشن',
						'off'   => 'خاموش',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'ماژول‌های محتوا',
					'colorModules'       => 'ماژول‌های رنگ',
					'orientationModules' => 'ماژول‌های جهت‌گیری',
				),
				'features'               => array(
					'skipToContent'      => 'رفتن به محتوا',
					'skipChoose'         => 'انتخاب کنید...',
					'skipMain'           => 'محتوای اصلی',
					'skipNavigation'     => 'ناوبری',
					'skipFooter'         => 'پاورقی',
					'biggerText'         => 'اندازه فونت',
					'highlightLinks'     => 'برجسته کردن لینک‌ها',
					'lineHeight'         => 'ارتفاع خط',
					'readableFont'       => 'فونت خوانا',
					'cursor'             => 'مکان‌نما بزرگ',
					'textMagnifier'      => 'ذره‌بین متن',
					'dyslexicFont'       => 'فونت دیسلکسی',
					'alignDefault'       => 'تراز متن',
					'alignLeft'          => 'چپ',
					'alignCenter'        => 'وسط‌چین',
					'alignRight'         => 'راست',
					'letterSpacing'      => 'فاصله حروف',
					'fontWeight'         => 'ضخامت فونت',
					'darkContrast'       => 'کنتراست تیره',
					'lightContrast'      => 'کنتراست روشن',
					'highContrast'       => 'کنتراست بالا',
					'monochrome'         => 'تک‌رنگ',
					'saturation'         => 'اشباع',
					'readingLine'        => 'خط خواندن',
					'readingMask'        => 'ماسک خواندن',
					'readPage'           => 'خواندن صفحه',
					'keyboardNavigation' => 'ناوبری صفحه کلید',
					'hideImages'         => 'مخفی کردن تصاویر',
					'muteSounds'         => 'بی‌صدا کردن صداها',
					'highlightTitles'    => 'برجسته کردن عناوین',
					'highlightAll'       => 'برجسته کردن محتوا',
					'stopAnimations'     => 'توقف انیمیشن‌ها',
					'resetSettings'      => 'بازنشانی تنظیمات',
				),
			),
			'il'    => array(
				'global'                 => array(
					'back'    => 'חזרה',
					'default' => 'ברירת מחדל',
				),
				'hideToolbar'            => array(
					'title'   => 'לכמה זמן תרצה להסתיר את סרגל הכלים לנגישות?',
					'radio1'  => 'רק עבור הפעלה זו',
					'radio2'  => '24 שעות',
					'radio3'  => 'שבוע',
					'button1' => 'לא עכשיו',
					'button2' => 'הסתר סרגל כלים',
				),
				'unsupportedPageReader'  => array(
					'title' => 'יש לעדכן את הדפדפן',
					'desc'  => 'הדפדפן שלך אינו תומך בפלט קולי. אנא עדכן את הדפדפן שלך או השתמש באחד התומך בסינתזת דיבור (למשל Chrome, Edge, Safari).',
					'link'  => 'איך לעדכן?',
				),
				'header'                 => array(
					'language'      => 'עברית',
					'listLanguages' => $list_languages,
					'title'         => 'התאמות נגישות',
					'desc'          => 'מופעל על ידי',
					'anchor'        => 'OneTap',
					'statement'     => 'הצהרה',
					'hideToolbar'   => 'הסתר סרגל כלים',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'בחר את פרופיל הנגישות שלך',
					'visionImpairedMode' => array(
						'title' => 'מצב ליקוי ראייה',
						'desc'  => 'משפר את האלמנטים הוויזואליים של האתר',
						'on'    => 'פועל',
						'off'   => 'כבוי',
					),
					'seizureSafeProfile' => array(
						'title' => 'פרופיל בטוח להתקפים',
						'desc'  => 'מפחית הבזקים ומשפר צבעים',
						'on'    => 'פועל',
						'off'   => 'כבוי',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'מצב ידידותי ל-ADHD',
						'desc'  => 'גלישה ממוקדת ללא הסחות דעת',
						'on'    => 'פועל',
						'off'   => 'כבוי',
					),
					'blindnessMode'      => array(
						'title' => 'מצב עיוורון',
						'desc'  => 'מפחית הסחות דעת, משפר ריכוז',
						'on'    => 'פועל',
						'off'   => 'כבוי',
					),
					'epilepsySafeMode'   => array(
						'title' => 'מצב בטוח לאפילפסיה',
						'desc'  => 'מעמעם צבעים ועוצר הבהוב',
						'on'    => 'פועל',
						'off'   => 'כבוי',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'מודולי תוכן',
					'colorModules'       => 'מודולי צבע',
					'orientationModules' => 'מודולי כיוון',
				),
				'features'               => array(
					'skipToContent'      => 'דלג לתוכן',
					'skipChoose'         => 'בחר...',
					'skipMain'           => 'תוכן ראשי',
					'skipNavigation'     => 'ניווט',
					'skipFooter'         => 'כותרת תחתונה',
					'biggerText'         => 'גודל גופן',
					'highlightLinks'     => 'הדגש קישורים',
					'lineHeight'         => 'גובה שורה',
					'readableFont'       => 'גופן קריא',
					'cursor'             => 'סמן גדול',
					'textMagnifier'      => 'זכוכית מגדלת לטקסט',
					'dyslexicFont'       => 'גופן לדיסלקציה',
					'alignDefault'       => 'יישור טקסט',
					'alignLeft'          => 'שמאל',
					'alignCenter'        => 'ממורכז',
					'alignRight'         => 'ימין',
					'letterSpacing'      => 'מרווח אותיות',
					'fontWeight'         => 'עובי גופן',
					'darkContrast'       => 'ניגודיות כהה',
					'lightContrast'      => 'ניגודיות בהירה',
					'highContrast'       => 'ניגודיות גבוהה',
					'monochrome'         => 'מונוכרום',
					'saturation'         => 'רוויה',
					'readingLine'        => 'קו קריאה',
					'readingMask'        => 'מסכת קריאה',
					'readPage'           => 'קרא דף',
					'keyboardNavigation' => 'ניווט במקלדת',
					'hideImages'         => 'הסתר תמונות',
					'muteSounds'         => 'השתק צלילים',
					'highlightTitles'    => 'הדגש כותרות',
					'highlightAll'       => 'הדגש תוכן',
					'stopAnimations'     => 'עצור אנימציות',
					'resetSettings'      => 'איפוס הגדרות',
				),
			),
			'mk'    => array(
				'global'                 => array(
					'back'    => 'Назад',
					'default' => 'Стандардно',
				),
				'hideToolbar'            => array(
					'title'   => 'Колку долго сакате да ја скриете лентата за пристапност?',
					'radio1'  => 'Само за оваа сесија',
					'radio2'  => '24 часа',
					'radio3'  => 'Една недела',
					'button1' => 'Не сега',
					'button2' => 'Скриј лента',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Прелистувачот треба да се ажурира',
					'desc'  => 'Вашиот прелистувач не поддржува говорен излез. Ве молиме ажурирајте го вашиот прелистувач или користете таков што поддржува говорна синтеза (на пр. Chrome, Edge, Safari).',
					'link'  => 'Како да се ажурира?',
				),
				'header'                 => array(
					'language'      => 'Македонски',
					'listLanguages' => $list_languages,
					'title'         => 'Прилагодувања за пристапност',
					'desc'          => 'Овозможено од',
					'anchor'        => 'OneTap',
					'statement'     => 'Изјава',
					'hideToolbar'   => 'Скриј лента со алатки',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Изберете го вашиот профил за пристапност',
					'visionImpairedMode' => array(
						'title' => 'Режим за оштетен вид',
						'desc'  => 'Подобрува визуелни елементи на веб-страницата',
						'on'    => 'ВКЛ',
						'off'   => 'ИСКЛ',
					),
					'seizureSafeProfile' => array(
						'title' => 'Безбеден профил за напади',
						'desc'  => 'Намалува трепкање и подобрува бои',
						'on'    => 'ВКЛ',
						'off'   => 'ИСКЛ',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'ADHD пријателски режим',
						'desc'  => 'Фокусирана навигација без одвлекување',
						'on'    => 'ВКЛ',
						'off'   => 'ИСКЛ',
					),
					'blindnessMode'      => array(
						'title' => 'Режим за слепило',
						'desc'  => 'Намалува одвлекувања и подобрува фокус',
						'on'    => 'ВКЛ',
						'off'   => 'ИСКЛ',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Безбеден режим за епилепсија',
						'desc'  => 'Намалува бои и запира трепкање',
						'on'    => 'ВКЛ',
						'off'   => 'ИСКЛ',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Модули за содржина',
					'colorModules'       => 'Модули за бои',
					'orientationModules' => 'Модули за ориентација',
				),
				'features'               => array(
					'skipToContent'      => 'Прескокни на содржината',
					'skipChoose'         => 'Изберете...',
					'skipMain'           => 'Главна содржина',
					'skipNavigation'     => 'Навигација',
					'skipFooter'         => 'Подножје',
					'biggerText'         => 'Големина на фонт',
					'highlightLinks'     => 'Истакни врски',
					'lineHeight'         => 'Висина на линија',
					'readableFont'       => 'Читлив фонт',
					'cursor'             => 'Голем курсор',
					'textMagnifier'      => 'Лупа за текст',
					'dyslexicFont'       => 'Фонт за дислексија',
					'alignDefault'       => 'Порамнување',
					'alignLeft'          => 'Лево',
					'alignCenter'        => 'Центрирано',
					'alignRight'         => 'Десно',
					'letterSpacing'      => 'Растојание меѓу букви',
					'fontWeight'         => 'Дебелина на фонт',
					'darkContrast'       => 'Темен контраст',
					'lightContrast'      => 'Светол контраст',
					'highContrast'       => 'Висок контраст',
					'monochrome'         => 'Монохром',
					'saturation'         => 'Заситеност',
					'readingLine'        => 'Линија за читање',
					'readingMask'        => 'Маска за читање',
					'readPage'           => 'Прочитај страница',
					'keyboardNavigation' => 'Навигација со тастатура',
					'hideImages'         => 'Скриј слики',
					'muteSounds'         => 'Исклучи звуци',
					'highlightTitles'    => 'Истакни наслови',
					'highlightAll'       => 'Истакни содржина',
					'stopAnimations'     => 'Запирај анимации',
					'resetSettings'      => 'Ресетирај поставки',
				),
			),
			'th'    => array(
				'global'                 => array(
					'back'    => 'กลับ',
					'default' => 'ค่าเริ่มต้น',
				),
				'hideToolbar'            => array(
					'title'   => 'คุณต้องการซ่อนแถบเครื่องมือการเข้าถึงนานแค่ไหน?',
					'radio1'  => 'เฉพาะสำหรับเซสชันนี้',
					'radio2'  => '24 ชั่วโมง',
					'radio3'  => 'หนึ่งสัปดาห์',
					'button1' => 'ไม่ใช่ตอนนี้',
					'button2' => 'ซ่อนแถบเครื่องมือ',
				),
				'unsupportedPageReader'  => array(
					'title' => 'เบราว์เซอร์ต้องอัปเดต',
					'desc'  => 'เบราว์เซอร์ของคุณไม่รองรับการส่งออกเสียง กรุณาอัปเดตเบราว์เซอร์ของคุณหรือใช้เบราว์เซอร์ที่รองรับการสังเคราะห์เสียง (เช่น Chrome, Edge, Safari)',
					'link'  => 'วิธีอัปเดต?',
				),
				'header'                 => array(
					'language'      => 'ไทย',
					'listLanguages' => $list_languages,
					'title'         => 'การปรับแต่งการเข้าถึง',
					'desc'          => 'ขับเคลื่อนโดย',
					'anchor'        => 'OneTap',
					'statement'     => 'คำแถลง',
					'hideToolbar'   => 'ซ่อนแถบเครื่องมือ',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'เลือกโปรไฟล์การเข้าถึงของคุณ',
					'visionImpairedMode' => array(
						'title' => 'โหมดสำหรับผู้บกพร่องทางสายตา',
						'desc'  => 'ปรับปรุงองค์ประกอบภาพของเว็บไซต์',
						'on'    => 'เปิด',
						'off'   => 'ปิด',
					),
					'seizureSafeProfile' => array(
						'title' => 'โปรไฟล์ปลอดภัยสำหรับอาการชัก',
						'desc'  => 'ลดการกระพริบและปรับปรุงสี',
						'on'    => 'เปิด',
						'off'   => 'ปิด',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'โหมดที่เป็นมิตรกับ ADHD',
						'desc'  => 'การเรียกดูแบบโฟกัสโดยไม่มีสิ่งรบกวน',
						'on'    => 'เปิด',
						'off'   => 'ปิด',
					),
					'blindnessMode'      => array(
						'title' => 'โหมดสำหรับผู้ตาบอด',
						'desc'  => 'ลดสิ่งรบกวนและปรับปรุงโฟกัส',
						'on'    => 'เปิด',
						'off'   => 'ปิด',
					),
					'epilepsySafeMode'   => array(
						'title' => 'โหมดปลอดภัยสำหรับโรคลมชัก',
						'desc'  => 'ลดสีและหยุดการกระพริบ',
						'on'    => 'เปิด',
						'off'   => 'ปิด',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'โมดูลเนื้อหา',
					'colorModules'       => 'โมดูลสี',
					'orientationModules' => 'โมดูลการวางแนว',
				),
				'features'               => array(
					'skipToContent'      => 'ข้ามไปยังเนื้อหา',
					'skipChoose'         => 'เลือก...',
					'skipMain'           => 'เนื้อหาหลัก',
					'skipNavigation'     => 'การนำทาง',
					'skipFooter'         => 'ส่วนท้าย',
					'biggerText'         => 'ขนาดฟอนต์',
					'highlightLinks'     => 'เน้นลิงก์',
					'lineHeight'         => 'ความสูงบรรทัด',
					'readableFont'       => 'ฟอนต์ที่อ่านง่าย',
					'cursor'             => 'เคอร์เซอร์ขนาดใหญ่',
					'textMagnifier'      => 'แว่นขยายข้อความ',
					'dyslexicFont'       => 'ฟอนต์สำหรับผู้บกพร่องทางการอ่าน',
					'alignDefault'       => 'จัดข้อความ',
					'alignLeft'          => 'ซ้าย',
					'alignCenter'        => 'กึ่งกลาง',
					'alignRight'         => 'ขวา',
					'letterSpacing'      => 'ระยะห่างตัวอักษร',
					'fontWeight'         => 'ความหนาฟอนต์',
					'darkContrast'       => 'คอนทราสต์เข้ม',
					'lightContrast'      => 'คอนทราสต์อ่อน',
					'highContrast'       => 'คอนทราสต์สูง',
					'monochrome'         => 'ขาวดำ',
					'saturation'         => 'ความอิ่มตัว',
					'readingLine'        => 'เส้นอ่าน',
					'readingMask'        => 'หน้ากากอ่าน',
					'readPage'           => 'อ่านหน้า',
					'keyboardNavigation' => 'นำทางด้วยแป้นพิมพ์',
					'hideImages'         => 'ซ่อนรูปภาพ',
					'muteSounds'         => 'ปิดเสียง',
					'highlightTitles'    => 'เน้นหัวข้อ',
					'highlightAll'       => 'เน้นเนื้อหา',
					'stopAnimations'     => 'หยุดแอนิเมชัน',
					'resetSettings'      => 'รีเซ็ตการตั้งค่า',
				),
			),
			'vn'    => array(
				'global'                 => array(
					'back'    => 'Quay lại',
					'default' => 'Mặc định',
				),
				'hideToolbar'            => array(
					'title'   => 'Bạn muốn ẩn thanh công cụ truy cập trong bao lâu?',
					'radio1'  => 'Chỉ cho phiên này',
					'radio2'  => '24 giờ',
					'radio3'  => 'Một tuần',
					'button1' => 'Không phải bây giờ',
					'button2' => 'Ẩn thanh công cụ',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Trình duyệt cần được cập nhật',
					'desc'  => 'Trình duyệt của bạn không hỗ trợ đầu ra giọng nói. Vui lòng cập nhật trình duyệt của bạn hoặc sử dụng trình duyệt hỗ trợ tổng hợp giọng nói (ví dụ: Chrome, Edge, Safari).',
					'link'  => 'Cách cập nhật?',
				),
				'header'                 => array(
					'language'      => 'Tiếng Việt',
					'listLanguages' => $list_languages,
					'title'         => 'Điều chỉnh Khả năng Truy cập',
					'desc'          => 'Được cung cấp bởi',
					'anchor'        => 'OneTap',
					'statement'     => 'Tuyên bố',
					'hideToolbar'   => 'Ẩn thanh công cụ',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Chọn hồ sơ khả năng truy cập của bạn',
					'visionImpairedMode' => array(
						'title' => 'Chế độ Khiếm thị',
						'desc'  => 'Cải thiện hình ảnh của trang web',
						'on'    => 'BẬT',
						'off'   => 'TẮT',
					),
					'seizureSafeProfile' => array(
						'title' => 'Hồ sơ An toàn cho Co giật',
						'desc'  => 'Loại bỏ nhấp nháy và giảm màu sắc',
						'on'    => 'BẬT',
						'off'   => 'TẮT',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Chế độ Thân thiện với ADHD',
						'desc'  => 'Duyệt tập trung, không bị phân tâm',
						'on'    => 'BẬT',
						'off'   => 'TẮT',
					),
					'blindnessMode'      => array(
						'title' => 'Chế độ Mù',
						'desc'  => 'Giảm phiền nhiễu, cải thiện tập trung',
						'on'    => 'BẬT',
						'off'   => 'TẮT',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Chế độ An toàn cho Động kinh',
						'desc'  => 'Làm mờ màu sắc và dừng nhấp nháy',
						'on'    => 'BẬT',
						'off'   => 'TẮT',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Mô-đun Nội dung',
					'colorModules'       => 'Mô-đun Màu sắc',
					'orientationModules' => 'Mô-đun Định hướng',
				),
				'features'               => array(
					'skipToContent'      => 'Chuyển đến nội dung',
					'skipChoose'         => 'Chọn...',
					'skipMain'           => 'Nội dung chính',
					'skipNavigation'     => 'Điều hướng',
					'skipFooter'         => 'Chân trang',
					'biggerText'         => 'Kích thước Phông chữ',
					'highlightLinks'     => 'Làm nổi bật Liên kết',
					'lineHeight'         => 'Chiều cao Dòng',
					'readableFont'       => 'Phông chữ Dễ đọc',
					'cursor'             => 'Con trỏ Lớn',
					'textMagnifier'      => 'Kính lúp Văn bản',
					'dyslexicFont'       => 'Phông chữ cho Chứng khó đọc',
					'alignDefault'       => 'Căn chỉnh văn bản',
					'alignLeft'          => 'Trái',
					'alignCenter'        => 'Giữa',
					'alignRight'         => 'Phải',
					'letterSpacing'      => 'Khoảng cách Chữ cái',
					'fontWeight'         => 'Độ đậm Phông chữ',
					'darkContrast'       => 'Tương phản Tối',
					'lightContrast'      => 'Tương phản Sáng',
					'highContrast'       => 'Tương phản Cao',
					'monochrome'         => 'Đơn sắc',
					'saturation'         => 'Độ bão hòa',
					'readingLine'        => 'Dòng Đọc',
					'readingMask'        => 'Mặt nạ Đọc',
					'readPage'           => 'Đọc Trang',
					'keyboardNavigation' => 'Điều hướng Bàn phím',
					'hideImages'         => 'Ẩn Hình ảnh',
					'muteSounds'         => 'Tắt Âm thanh',
					'highlightTitles'    => 'Làm nổi bật Tiêu đề',
					'highlightAll'       => 'Làm nổi bật Nội dung',
					'stopAnimations'     => 'Dừng Hoạt hình',
					'resetSettings'      => 'Đặt lại Cài đặt',
				),
			),
			'bn'    => array(
				'global'                 => array(
					'back'    => 'ফিরে যান',
					'default' => 'ডিফল্ট',
				),
				'hideToolbar'            => array(
					'title'   => 'আপনি কতক্ষণ টুলবার লুকিয়ে রাখতে চান?',
					'radio1'  => 'শুধু এই সেশনের জন্য',
					'radio2'  => '২৪ ঘণ্টা',
					'radio3'  => 'এক সপ্তাহ',
					'button1' => 'এখন নয়',
					'button2' => 'টুলবার লুকান',
				),
				'unsupportedPageReader'  => array(
					'title' => 'ব্রাউজার হালনাগাদ করতে হবে',
					'desc'  => 'আপনার ব্রাউজার ভয়েস আউটপুট সমর্থন করে না। অনুগ্রহ করে আপনার ব্রাউজার হালনাগাদ করুন অথবা স্পিচ সিনথেসিস সমর্থন করে এমন ব্রাউজার ব্যবহার করুন (যেমন Chrome, Edge, Safari)।',
					'link'  => 'কীভাবে হালনাগাদ করবেন?',
				),
				'header'                 => array(
					'language'      => 'বাংলা',
					'listLanguages' => $list_languages,
					'title'         => 'অ্যাক্সেসিবিলিটি সমন্বয়',
					'desc'          => 'দ্বারা পরিচালিত',
					'anchor'        => 'OneTap',
					'statement'     => 'বিবৃতি',
					'hideToolbar'   => 'টুলবার লুকান',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'আপনার অ্যাক্সেসিবিলিটি প্রোফাইল নির্বাচন করুন',
					'visionImpairedMode' => array(
						'title' => 'দৃষ্টি প্রতিবন্ধী মোড',
						'desc'  => 'ওয়েবসাইটের ভিজ্যুয়াল উন্নত করে',
						'on'    => 'চালু',
						'off'   => 'বন্ধ',
					),
					'seizureSafeProfile' => array(
						'title' => 'খিঁচুনি নিরাপদ প্রোফাইল',
						'desc'  => 'ঝলকানি দূর করে ও রঙ কমায়',
						'on'    => 'চালু',
						'off'   => 'বন্ধ',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'এডিএইচডি বান্ধব মোড',
						'desc'  => 'মনোযোগী ব্রাউজিং, বিক্ষেপমুক্ত',
						'on'    => 'চালু',
						'off'   => 'বন্ধ',
					),
					'blindnessMode'      => array(
						'title' => 'অন্ধত্ব মোড',
						'desc'  => 'বিক্ষেপ কমায়, মনোযোগ বাড়ায়',
						'on'    => 'চালু',
						'off'   => 'বন্ধ',
					),
					'epilepsySafeMode'   => array(
						'title' => 'মৃগী নিরাপদ মোড',
						'desc'  => 'রঙ ম্লান করে ও ঝলকানি থামায়',
						'on'    => 'চালু',
						'off'   => 'বন্ধ',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'কনটেন্ট মডিউল',
					'colorModules'       => 'রঙ মডিউল',
					'orientationModules' => 'ওরিয়েন্টেশন মডিউল',
				),
				'features'               => array(
					'skipToContent'      => 'কনটেন্টে যান',
					'skipChoose'         => 'নির্বাচন করুন...',
					'skipMain'           => 'মূল কনটেন্ট',
					'skipNavigation'     => 'নেভিগেশন',
					'skipFooter'         => 'ফুটার',
					'biggerText'         => 'ফন্টের আকার',
					'highlightLinks'     => 'লিঙ্ক হাইলাইট',
					'lineHeight'         => 'লাইনের উচ্চতা',
					'readableFont'       => 'পাঠযোগ্য ফন্ট',
					'cursor'             => 'বড় কার্সার',
					'textMagnifier'      => 'টেক্সট ম্যাগনিফায়ার',
					'dyslexicFont'       => 'ডিসলেক্সিক ফন্ট',
					'alignDefault'       => 'টেক্সট সারিবদ্ধ',
					'alignLeft'          => 'বাঁদিকে',
					'alignCenter'        => 'মাঝখানে',
					'alignRight'         => 'ডানদিকে',
					'letterSpacing'      => 'অক্ষরের ব্যবধান',
					'fontWeight'         => 'ফন্টের পুরুত্ব',
					'darkContrast'       => 'গাঢ় কনট্রাস্ট',
					'lightContrast'      => 'হালকা কনট্রাস্ট',
					'highContrast'       => 'উচ্চ কনট্রাস্ট',
					'monochrome'         => 'মনোক্রোম',
					'saturation'         => 'স্যাচুরেশন',
					'readingLine'        => 'পড়ার রেখা',
					'readingMask'        => 'পড়ার মাস্ক',
					'readPage'           => 'পৃষ্ঠা পড়ুন',
					'keyboardNavigation' => 'কীবোর্ড নেভিগেশন',
					'hideImages'         => 'ছবি লুকান',
					'muteSounds'         => 'শব্দ বন্ধ',
					'highlightTitles'    => 'শিরোনাম হাইলাইট',
					'highlightAll'       => 'কনটেন্ট হাইলাইট',
					'stopAnimations'     => 'অ্যানিমেশন বন্ধ',
					'resetSettings'      => 'সেটিংস রিসেট',
				),
			),
			'tl'    => array(
				'global'                 => array(
					'back'    => 'Bumalik',
					'default' => 'Default',
				),
				'hideToolbar'            => array(
					'title'   => 'Gaano katagal mo gustong itago ang toolbar?',
					'radio1'  => 'Para lang sa session na ito',
					'radio2'  => '24 na oras',
					'radio3'  => 'Isang linggo',
					'button1' => 'Hindi ngayon',
					'button2' => 'Itago ang toolbar',
				),
				'unsupportedPageReader'  => array(
					'title' => 'Kailangang i-update ang browser',
					'desc'  => 'Hindi sinusuportahan ng iyong browser ang speech output. Mangyaring i-update ang iyong browser o gumamit ng browser na may speech synthesis (hal. Chrome, Edge, Safari).',
					'link'  => 'Paano mag-update?',
				),
				'header'                 => array(
					'language'      => 'Filipino',
					'listLanguages' => $list_languages,
					'title'         => 'Mga Pagsasaayos sa Accessibility',
					'desc'          => 'Pinapagana ng',
					'anchor'        => 'OneTap',
					'statement'     => 'Pahayag',
					'hideToolbar'   => 'Itago ang toolbar',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'Piliin ang iyong accessibility profile',
					'visionImpairedMode' => array(
						'title' => 'Mode para sa May Kapansanan sa Paningin',
						'desc'  => 'Pinapaganda ang visual ng website',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'seizureSafeProfile' => array(
						'title' => 'Profile na Ligtas sa Seizure',
						'desc'  => 'Inaalis ang pagkislap at binabawasan ang kulay',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'Mode na Angkop sa ADHD',
						'desc'  => 'Nakatuong pagba-browse, walang distraksyon',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'blindnessMode'      => array(
						'title' => 'Mode para sa Pagkabulag',
						'desc'  => 'Binabawasan ang distraksyon, pinapabuti ang pokus',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
					'epilepsySafeMode'   => array(
						'title' => 'Mode na Ligtas sa Epilepsy',
						'desc'  => 'Pinapalabo ang kulay at pinapahinto ang pagkurap',
						'on'    => 'ON',
						'off'   => 'OFF',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'Mga Content Module',
					'colorModules'       => 'Mga Color Module',
					'orientationModules' => 'Mga Orientation Module',
				),
				'features'               => array(
					'skipToContent'      => 'Lumaktaw sa Nilalaman',
					'skipChoose'         => 'Pumili...',
					'skipMain'           => 'Pangunahing Nilalaman',
					'skipNavigation'     => 'Nabigasyon',
					'skipFooter'         => 'Footer',
					'biggerText'         => 'Laki ng Font',
					'highlightLinks'     => 'I-highlight ang mga Link',
					'lineHeight'         => 'Taas ng Linya',
					'readableFont'       => 'Nababasang Font',
					'cursor'             => 'Malaking Cursor',
					'textMagnifier'      => 'Panlaki ng Teksto',
					'dyslexicFont'       => 'Font para sa Dyslexia',
					'alignDefault'       => 'Ihanay ang Teksto',
					'alignLeft'          => 'Kaliwa',
					'alignCenter'        => 'Gitna',
					'alignRight'         => 'Kanan',
					'letterSpacing'      => 'Agwat ng Letra',
					'fontWeight'         => 'Bigat ng Font',
					'darkContrast'       => 'Madilim na Kontrast',
					'lightContrast'      => 'Maliwanag na Kontrast',
					'highContrast'       => 'Mataas na Kontrast',
					'monochrome'         => 'Monochrome',
					'saturation'         => 'Saturation',
					'readingLine'        => 'Linya sa Pagbasa',
					'readingMask'        => 'Mask sa Pagbasa',
					'readPage'           => 'Basahin ang Pahina',
					'keyboardNavigation' => 'Nabigasyon sa Keyboard',
					'hideImages'         => 'Itago ang mga Larawan',
					'muteSounds'         => 'I-mute ang Tunog',
					'highlightTitles'    => 'I-highlight ang mga Pamagat',
					'highlightAll'       => 'I-highlight ang Nilalaman',
					'stopAnimations'     => 'Ihinto ang Animation',
					'resetSettings'      => 'I-reset ang Setting',
				),
			),
			'ur'    => array(
				'global'                 => array(
					'back'    => 'واپس',
					'default' => 'ڈیفالٹ',
				),
				'hideToolbar'            => array(
					'title'   => 'آپ ٹول بار کو کتنی دیر چھپانا چاہتے ہیں؟',
					'radio1'  => 'صرف اس سیشن کے لیے',
					'radio2'  => '24 گھنٹے',
					'radio3'  => 'ایک ہفتہ',
					'button1' => 'ابھی نہیں',
					'button2' => 'ٹول بار چھپائیں',
				),
				'unsupportedPageReader'  => array(
					'title' => 'براؤزر کو اپ ڈیٹ کرنے کی ضرورت ہے',
					'desc'  => 'آپ کا براؤزر آواز کی سہولت کو سپورٹ نہیں کرتا۔ براہ کرم اپنا براؤزر اپ ڈیٹ کریں یا ایسا براؤزر استعمال کریں جو اسپیچ سنتھیسس سپورٹ کرتا ہو (مثلاً Chrome، Edge، Safari)۔',
					'link'  => 'اپ ڈیٹ کیسے کریں؟',
				),
				'header'                 => array(
					'language'      => 'اردو',
					'listLanguages' => $list_languages,
					'title'         => 'رسائی کی ترتیبات',
					'desc'          => 'بذریعہ',
					'anchor'        => 'OneTap',
					'statement'     => 'بیان',
					'hideToolbar'   => 'ٹول بار چھپائیں',
				),
				'multiFunctionalFeature' => array(
					'title'              => 'اپنی رسائی پروفائل منتخب کریں',
					'visionImpairedMode' => array(
						'title' => 'کم بینائی موڈ',
						'desc'  => 'ویب سائٹ کے مناظر کو بہتر بناتا ہے',
						'on'    => 'آن',
						'off'   => 'آف',
					),
					'seizureSafeProfile' => array(
						'title' => 'دورے سے محفوظ پروفائل',
						'desc'  => 'چمک ختم کرتا اور رنگ کم کرتا ہے',
						'on'    => 'آن',
						'off'   => 'آف',
					),
					'aDHDFriendlyMode'   => array(
						'title' => 'اے ڈی ایچ ڈی دوست موڈ',
						'desc'  => 'یکسو براؤزنگ، خلفشار کے بغیر',
						'on'    => 'آن',
						'off'   => 'آف',
					),
					'blindnessMode'      => array(
						'title' => 'نابینا موڈ',
						'desc'  => 'خلفشار کم کرتا، توجہ بہتر بناتا ہے',
						'on'    => 'آن',
						'off'   => 'آف',
					),
					'epilepsySafeMode'   => array(
						'title' => 'مرگی سے محفوظ موڈ',
						'desc'  => 'رنگ مدھم کرتا اور جھپکنا روکتا ہے',
						'on'    => 'آن',
						'off'   => 'آف',
					),
				),
				'titles'                 => array(
					'contentModules'     => 'مواد ماڈیولز',
					'colorModules'       => 'رنگ ماڈیولز',
					'orientationModules' => 'سمت ماڈیولز',
				),
				'features'               => array(
					'skipToContent'      => 'مواد پر جائیں',
					'skipChoose'         => 'منتخب کریں...',
					'skipMain'           => 'اہم مواد',
					'skipNavigation'     => 'نیویگیشن',
					'skipFooter'         => 'فوٹر',
					'biggerText'         => 'فونٹ سائز',
					'highlightLinks'     => 'لنکس نمایاں کریں',
					'lineHeight'         => 'سطر کی اونچائی',
					'readableFont'       => 'پڑھنے کے قابل فونٹ',
					'cursor'             => 'بڑا کرسر',
					'textMagnifier'      => 'ٹیکسٹ میگنیفائر',
					'dyslexicFont'       => 'ڈسلیکسک فونٹ',
					'alignDefault'       => 'متن ترتیب دیں',
					'alignLeft'          => 'بائیں',
					'alignCenter'        => 'درمیان',
					'alignRight'         => 'دائیں',
					'letterSpacing'      => 'حروف کا فاصلہ',
					'fontWeight'         => 'فونٹ کی موٹائی',
					'darkContrast'       => 'گہرا کنٹراسٹ',
					'lightContrast'      => 'ہلکا کنٹراسٹ',
					'highContrast'       => 'اعلیٰ کنٹراسٹ',
					'monochrome'         => 'یک رنگ',
					'saturation'         => 'رنگ کی شدت',
					'readingLine'        => 'پڑھنے کی لکیر',
					'readingMask'        => 'پڑھنے کا ماسک',
					'readPage'           => 'صفحہ پڑھیں',
					'keyboardNavigation' => 'کی بورڈ نیویگیشن',
					'hideImages'         => 'تصاویر چھپائیں',
					'muteSounds'         => 'آوازیں خاموش',
					'highlightTitles'    => 'عنوانات نمایاں کریں',
					'highlightAll'       => 'مواد نمایاں کریں',
					'stopAnimations'     => 'اینیمیشن روکیں',
					'resetSettings'      => 'ترتیبات ری سیٹ کریں',
				),
			),
		);

		// Only the reachable ones are handed to the browser.
		$onetap_language_toggles = ( isset( $plugin_settings['toggle-language'] ) && is_array( $plugin_settings['toggle-language'] ) )
			? $plugin_settings['toggle-language']
			: array();

		$onetap_language_dictionaries = $this->filter_enabled_language_dictionaries(
			$onetap_language_dictionaries,
			$onetap_language_toggles,
			$language
		);

		wp_localize_script(
			$this->plugin_name,
			'onetapAjaxObject',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'ajax-nonce' ),
				'activeLanguage'  => $language,
				'localizedLabels' => get_option( 'onetap_localized_labels' ),
				'languageList'    => $language_list,
				'pluginInfo'      => array(
					'version'   => ACCESSIBILITY_ONETAP_VERSION,
					'pluginUrl' => ACCESSIBILITY_ONETAP_PLUGINS_URL,
					'imagesUrl' => ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/',
				),
				'languages'       => $onetap_language_dictionaries,
				'getSettings'     => array(
					'language'                   => $onetap_setting_language,
					'color'                      => $onetap_setting_color,
					'position-top-bottom'        => $onetap_setting_position_top_bottom,
					'position-left-right'        => $onetap_setting_position_left_right,
					'widge-position'             => $onetap_setting_widget_position,
					'position-top-bottom-tablet' => $onetap_setting_position_top_bottom_tablet,
					'position-left-right-tablet' => $onetap_setting_position_left_right_tablet,
					'widge-position-tablet'      => $onetap_setting_widget_position_tablet,
					'position-top-bottom-mobile' => $onetap_setting_position_top_bottom_mobile,
					'position-left-right-mobile' => $onetap_setting_position_left_right_mobile,
					'widge-position-mobile'      => $onetap_setting_widget_position_mobile,
					'hide-powered-by-onetap'     => $onetap_setting_hide_powered_by_onetap,
				),
				'showModules'     => array(
					'accessibility-profiles' => $module_settings['accessibility_profiles'],
					'bigger-text'            => $module_settings['bigger_text'],
					'highlight-links'        => $module_settings['highlight_links'],
					'line-height'            => $module_settings['line_height'],
					'cursor'                 => $module_settings['cursor'],
					'readable-font'          => $module_settings['readable_font'],
					'dyslexic-font'          => $module_settings['dyslexic_font'],
					'text-magnifier'         => $module_settings['text_magnifier'],
					'text-align'             => $module_settings['text_align'],
					'letter-spacing'         => $module_settings['letter_spacing'],
					'font-weight'            => $module_settings['font_weight'],
					'dark-contrast'          => $module_settings['dark_contrast'],
					'light-contrast'         => $module_settings['light_contrast'],
					'high-contrast'          => $module_settings['high_contrast'],
					'monochrome'             => $module_settings['monochrome'],
					'saturation'             => $module_settings['saturation'],
					'reading-line'           => $module_settings['reading_line'],
					'reading-mask'           => $module_settings['reading_mask'],
					'read-page'              => $module_settings['read_page'],
					'keyboard-navigation'    => $module_settings['keyboard_navigation'],
					'hide-images'            => $module_settings['hide_images'],
					'mute-sounds'            => $module_settings['mute_sounds'],
					'highlight-titles'       => $module_settings['highlight_titles'],
					'highlight-all'          => $module_settings['highlight_all'],
					'stop-animations'        => $module_settings['stop_animations'],
					'skip-to-content'        => $module_settings['skip_to_content'],
				),
			)
		);
	}

	/**
	 * Adds custom class to the body element.
	 *
	 * This function appends a custom class to the body tag, which can be used
	 * for additional styling or JavaScript targeting.
	 *
	 * @param array $classes An array of existing classes for the body tag.
	 * @return array Modified array of classes with the custom class added.
	 */
	public function add_custom_body_class( $classes ) {

		// Get module settings using helper method.
		$module_settings = $this->get_module_settings();

		// Add default classes to the $classes array.
		$classes[] = 'onetap-root onetap-accessibility-plugin onetap-body-class onetap-custom-class onetap-classes';

		// Check if specific accessibility modules are turned off.
		// If a module is 'off', add its corresponding class to the $classes array.

		// Define module to class mapping for efficient processing.
		$module_class_mapping = array(
			'bigger_text'         => 'onetap_hide_bigger_text',
			'highlight_links'     => 'onetap_hide_highlight_links',
			'line_height'         => 'onetap_hide_line_height',
			'readable_font'       => 'onetap_hide_readable_font',
			'cursor'              => 'onetap_hide_cursor',
			'text_magnifier'      => 'onetap_hide_text_magnifier',
			'dyslexic_font'       => 'onetap_hide_dyslexic_font',
			'text_align'          => 'onetap_hide_text_align',
			'align_center'        => 'onetap_hide_align_center',
			'letter_spacing'      => 'onetap_hide_letter_spacing',
			'font_weight'         => 'onetap_hide_font_weight',
			'dark_contrast'       => 'onetap_hide_dark_contrast',
			'light_contrast'      => 'onetap_hide_light_contrast',
			'high_contrast'       => 'onetap_hide_high_contrast',
			'monochrome'          => 'onetap_hide_monochrome',
			'saturation'          => 'onetap_hide_saturation',
			'reading_line'        => 'onetap_hide_reading_line',
			'reading_mask'        => 'onetap_hide_reading_mask',
			'read_page'           => 'onetap_hide_read_page',
			'keyboard_navigation' => 'onetap_hide_keyboard_navigation',
			'hide_images'         => 'onetap_hide_hide_images',
			'mute_sounds'         => 'onetap_hide_mute_sounds',
			'highlight_titles'    => 'onetap_hide_highlight_titles',
			'highlight_all'       => 'onetap_hide_highlight_all',
			'stop_animations'     => 'onetap_hide_stop_animations',
			'skip_to_content'     => 'onetap_hide_skip_content',
		);

		// Loop through module settings and add classes for disabled modules.
		foreach ( $module_class_mapping as $module_key => $class_name ) {
			if ( isset( $module_settings[ $module_key ] ) && 'off' === $module_settings[ $module_key ] ) {
				$classes[] = $class_name;
			}
		}

		// Return the updated array of classes.
		return $classes;
	}

	/**
	 * Renders an accessibility HTML template.
	 *
	 * This function generates an HTML template that includes accessibility features
	 * It ensures the template adheres to WCAG guidelines for better user experience
	 * for people with disabilities.
	 */
	public function render_accessibility_template() {
		$module_settings           = $this->get_module_settings();
		$onetap_skip_to_content_on = isset( $module_settings['skip_to_content'] ) && 'on' === $module_settings['skip_to_content'];
		?>
		<section class="onetap-container-toggle" style="display: none;">
			<?php
			$settings = get_option( 'onetap_settings' );

			// Which entries the toolbar footer ends up showing. The separators
			// between them are rendered from these flags further down, so there is
			// no CSS rule left that has to be kept in sync with the markup.
			$powered_by_is_visible = $this->is_powered_by_visible();
			$statement_is_visible  = $this->is_statement_visible();

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			$toggle_classes = array_filter(
				array(
					! empty( $settings['border'] ) ? $settings['border'] : '',
					isset( $settings['toggle-device-position-desktop'] ) && 'on' === $settings['toggle-device-position-desktop'] ? 'hide-on-desktop' : '',
					isset( $settings['toggle-device-position-tablet'] ) && 'on' === $settings['toggle-device-position-tablet'] ? 'hide-on-tablet' : '',
					isset( $settings['toggle-device-position-mobile'] ) && 'on' === $settings['toggle-device-position-mobile'] ? 'hide-on-mobile' : '',
				)
			);
			?>

			<button type="button" aria-label="Toggle Accessibility Toolbar" class="onetap-toggle <?php echo esc_attr( implode( ' ', $toggle_classes ) ); ?>">				
				<?php
				// Define SVG paths for each icon type.
				$icon_paths = array(
					'design1' => 'assets/images/admin/Original_Logo_Icon.svg',
					'design2' => 'assets/images/admin/Hand_Icon.svg',
					'design3' => 'assets/images/admin/Accessibility-Man-Icon.svg',
					'design4' => 'assets/images/admin/Settings-Filter-Icon.svg',
					'design5' => 'assets/images/admin/Switcher-Icon.svg',
					'design6' => 'assets/images/admin/Eye-Show-Icon.svg',
				);

				// Check if the selected icon exists in the array.
				$settings = get_option( 'onetap_settings' );
				if ( isset( $settings['icons'], $icon_paths[ $settings['icons'] ] ) ) {
					$icons = array(
						'design1' => 'Original_Logo_Icon.svg',
						'design2' => 'Hand_Icon.svg',
						'design3' => 'Accessibility-Man-Icon.svg',
						'design4' => 'Settings-Filter-Icon.svg',
						'design5' => 'Switcher-Icon.svg',
						'design6' => 'Eye-Show-Icon.svg',
					);
					foreach ( $icons as $icon_value => $icon_image ) {
						if ( $icon_value === $settings['icons'] ) {
							$class_size   = isset( $settings['size'] ) ? $settings['size'] : '';
							$class_border = isset( $settings['border'] ) ? $settings['border'] : '';
							echo '<img class="' . esc_attr( $class_size ) . ' ' . esc_attr( $class_border ) . '" src="' . esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/' . $icon_image ) . '" alt="" />';
						}
					}
				} else {
					echo '<img class="design-size2 design-border2" src="' . esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/Original_Logo_Icon.svg' ) . '" alt="" />';
				}
				?>
			</button>
		</section>
		<nav class="onetap-accessibility onetap-plugin-onetap" aria-label="Accessibility Options">
			<section class="onetap-container">
				<div class="onetap-accessibility-settings" data-lenis-prevent="true">
					<header class="onetap-header-top">
						<?php
						// Get language toggle settings.
						$language_toggles = isset( $settings['toggle-language'] ) && is_array( $settings['toggle-language'] ) ? $settings['toggle-language'] : array();

						// Get default language.
						$default_language = isset( $settings['language'] ) ? $settings['language'] : 'en';

						// Define all languages with their names and flag images.
						$all_languages = array(
							'en'    => array(
								'name'    => 'English',
								'flag'    => 'english.png',
								'country' => __( 'United States', 'accessibility-onetap' ),
							),
							'de'    => array(
								'name'    => 'Deutsch',
								'flag'    => 'german.png',
								'country' => __( 'Germany', 'accessibility-onetap' ),
							),
							'es'    => array(
								'name'    => 'Español',
								'flag'    => 'spanish.png',
								'country' => __( 'Spain', 'accessibility-onetap' ),
							),
							'fr'    => array(
								'name'    => 'Français',
								'flag'    => 'french.png',
								'country' => __( 'France', 'accessibility-onetap' ),
							),
							'it'    => array(
								'name'    => 'Italiano',
								'flag'    => 'italia.png',
								'country' => __( 'Italy', 'accessibility-onetap' ),
							),
							'pl'    => array(
								'name'    => 'Polski',
								'flag'    => 'poland.png',
								'country' => __( 'Poland', 'accessibility-onetap' ),
							),
							'se'    => array(
								'name'    => 'Svenska',
								'flag'    => 'swedish.png',
								'country' => __( 'Sweden', 'accessibility-onetap' ),
							),
							'fi'    => array(
								'name'    => 'Suomi',
								'flag'    => 'finnland.png',
								'country' => __( 'Finland', 'accessibility-onetap' ),
							),
							'pt'    => array(
								'name'    => 'Português',
								'flag'    => 'portugal.png',
								'country' => __( 'Portugal', 'accessibility-onetap' ),
							),
							'ro'    => array(
								'name'    => 'Română',
								'flag'    => 'rumania.png',
								'country' => __( 'Romania', 'accessibility-onetap' ),
							),
							'si'    => array(
								'name'    => 'Slovenščina',
								'flag'    => 'slowenien.png',
								'country' => __( 'Slovenia', 'accessibility-onetap' ),
							),
							'sk'    => array(
								'name'    => 'Slovenčina',
								'flag'    => 'slowakia.png',
								'country' => __( 'Slovakia', 'accessibility-onetap' ),
							),
							'nl'    => array(
								'name'    => 'Nederlands',
								'flag'    => 'netherland.png',
								'country' => __( 'Netherlands', 'accessibility-onetap' ),
							),
							'dk'    => array(
								'name'    => 'Dansk',
								'flag'    => 'danish.png',
								'country' => __( 'Denmark', 'accessibility-onetap' ),
							),
							'gr'    => array(
								'name'    => 'Ελληνικά',
								'flag'    => 'greece.png',
								'country' => __( 'Greece', 'accessibility-onetap' ),
							),
							'cz'    => array(
								'name'    => 'Čeština',
								'flag'    => 'czech.png',
								'country' => __( 'Czechia', 'accessibility-onetap' ),
							),
							'hu'    => array(
								'name'    => 'Magyar',
								'flag'    => 'hungarian.png',
								'country' => __( 'Hungary', 'accessibility-onetap' ),
							),
							'lt'    => array(
								'name'    => 'Lietuvių',
								'flag'    => 'lithuanian.png',
								'country' => __( 'Lithuania', 'accessibility-onetap' ),
							),
							'lv'    => array(
								'name'    => 'Latviešu',
								'flag'    => 'latvian.png',
								'country' => __( 'Latvia', 'accessibility-onetap' ),
							),
							'ee'    => array(
								'name'    => 'Eesti',
								'flag'    => 'estonian.png',
								'country' => __( 'Estonia', 'accessibility-onetap' ),
							),
							'hr'    => array(
								'name'    => 'Hrvatski',
								'flag'    => 'croatia.png',
								'country' => __( 'Croatia', 'accessibility-onetap' ),
							),
							'ie'    => array(
								'name'    => 'Gaeilge',
								'flag'    => 'ireland.png',
								'country' => __( 'Ireland', 'accessibility-onetap' ),
							),
							'bg'    => array(
								'name'    => 'Български',
								'flag'    => 'bulgarian.png',
								'country' => __( 'Bulgaria', 'accessibility-onetap' ),
							),
							'no'    => array(
								'name'    => 'Norsk',
								'flag'    => 'norwegan.png',
								'country' => __( 'Norway', 'accessibility-onetap' ),
							),
							'tr'    => array(
								'name'    => 'Türkçe',
								'flag'    => 'turkish.png',
								'country' => __( 'Türkiye', 'accessibility-onetap' ),
							),
							'id'    => array(
								'name'    => 'Bahasa Indonesia',
								'flag'    => 'indonesian.png',
								'country' => __( 'Indonesia', 'accessibility-onetap' ),
							),
							'pt-br' => array(
								'name'    => 'Português (Brasil)',
								'flag'    => 'brasilian.png',
								'country' => __( 'Brazil', 'accessibility-onetap' ),
							),
							'ja'    => array(
								'name'    => '日本語',
								'flag'    => 'japanese.png',
								'country' => __( 'Japan', 'accessibility-onetap' ),
							),
							'ko'    => array(
								'name'    => '한국어',
								'flag'    => 'korean.png',
								'country' => __( 'South Korea', 'accessibility-onetap' ),
							),
							'zh'    => array(
								'name'    => '简体中文',
								'flag'    => 'chinese-simplified.png',
								'country' => __( 'China', 'accessibility-onetap' ),
							),
							'ar'    => array(
								'name'    => 'العربية',
								'flag'    => 'arabic.png',
								'country' => __( 'United Arab Emirates', 'accessibility-onetap' ),
							),
							'ru'    => array(
								'name'    => 'Русский',
								'flag'    => 'russian.png',
								'country' => __( 'Russia', 'accessibility-onetap' ),
							),
							'hi'    => array(
								'name'    => 'हिन्दी',
								'flag'    => 'hindi.png',
								'country' => __( 'India', 'accessibility-onetap' ),
							),
							'uk'    => array(
								'name'    => 'Українська',
								'flag'    => 'ukrainian.png',
								'country' => __( 'Ukraine', 'accessibility-onetap' ),
							),
							'sr'    => array(
								'name'    => 'Srpski',
								'flag'    => 'serbian.png',
								'country' => __( 'Serbia', 'accessibility-onetap' ),
							),
							'gb'    => array(
								'name'    => 'English (UK)',
								'flag'    => 'england.png',
								'country' => __( 'United Kingdom', 'accessibility-onetap' ),
							),
							'ir'    => array(
								'name'    => 'فارسی',
								'flag'    => 'iran.png',
								'country' => __( 'Iran', 'accessibility-onetap' ),
							),
							'il'    => array(
								'name'    => 'עברית',
								'flag'    => 'israel.png',
								'country' => __( 'Israel', 'accessibility-onetap' ),
							),
							'mk'    => array(
								'name'    => 'Македонски',
								'flag'    => 'macedonia.png',
								'country' => __( 'North Macedonia', 'accessibility-onetap' ),
							),
							'th'    => array(
								'name'    => 'ไทย',
								'flag'    => 'thailand.png',
								'country' => __( 'Thailand', 'accessibility-onetap' ),
							),
							'vn'    => array(
								'name'    => 'Tiếng Việt',
								'flag'    => 'vietnam.png',
								'country' => __( 'Vietnam', 'accessibility-onetap' ),
							),
							'bn'    => array(
								'name'    => 'বাংলা',
								'flag'    => 'bangladesh.png',
								'country' => __( 'Bangladesh', 'accessibility-onetap' ),
							),
							'tl'    => array(
								'name'    => 'Filipino',
								'flag'    => 'philippines.png',
								'country' => __( 'Philippines', 'accessibility-onetap' ),
							),
							'ur'    => array(
								'name'    => 'اردو',
								'flag'    => 'pakistan.png',
								'country' => __( 'Pakistan', 'accessibility-onetap' ),
							),
						);

						// Filter enabled languages. On a fresh install every language is
						// offered; once the admin has curated the list, a language added by
						// a later update stays off until they switch it on. Display Options
						// renders the toggles from the same rule.
						$enabled_languages = array();
						foreach ( $all_languages as $lang_code => $lang_data ) {
							if ( Accessibility_Onetap_Config::is_language_enabled( $lang_code, $language_toggles ) ) {
								$enabled_languages[ $lang_code ] = $lang_data;
							}
						}

						// If no languages are enabled, default to English.
						if ( empty( $enabled_languages ) ) {
							$enabled_languages = array( 'en' => $all_languages['en'] );
						}

						// Count enabled languages.
						$enabled_count = count( $enabled_languages );

						// Determine which language to show as active (use default language if enabled, otherwise first enabled).
						$active_language = $default_language;
						if ( ! isset( $enabled_languages[ $active_language ] ) ) {
							$active_language = key( $enabled_languages );
						}

						// Always display the configured default language first; remaining languages keep their normal order.
						// The union operator preserves the order of the left operand keys, so the active language is moved
						// to the top while all other entries follow in their original sequence.
						if ( isset( $enabled_languages[ $active_language ] ) ) {
							$enabled_languages = array( $active_language => $enabled_languages[ $active_language ] ) + $enabled_languages;
						}
						?>
						
						<!-- Languages Dropdown (only show if more than one language is enabled) -->
						<button id="onetap-language-list" aria-controls="onetap-language-list" type="button" role="combobox" aria-expanded="false" aria-haspopup="listbox" class="onetap-languages<?php echo ( 1 === $enabled_count ) ? ' onetap-disable' : ''; ?>" aria-label="Select language">
							<div class="onetap-icon">
								<?php foreach ( $enabled_languages as $lang_code => $lang_data ) : ?>
									<?php
									$flag_url  = esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/' . $lang_data['flag'] );
									$is_active = ( $lang_code === $active_language );
									?>
									<img
										<?php if ( $is_active ) : ?>
											src="<?php echo esc_url( $flag_url ); ?>"
										<?php else : ?>
											data-src="<?php echo esc_url( $flag_url ); ?>"
										<?php endif; ?>
										class="<?php echo $is_active ? 'onetap-active' : ''; ?>"
										data-language="<?php echo esc_attr( $lang_code ); ?>"
										alt="<?php echo esc_attr( $lang_data['country'] ); ?>"
									>
								<?php endforeach; ?>
							</div>
							<p class="onetap-text">
								<span>
									<?php echo esc_html( $enabled_languages[ $active_language ]['name'] ); ?>
								</span>
								<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/icon-drop-down-menu.png' ); ?>" width="10" height="10" alt="">
							</p>
						</button>

						<!-- List of languages -->
						<div class="onetap-list-of-languages" style="display: none;">
							<ul>
								<?php foreach ( $enabled_languages as $lang_code => $lang_data ) : ?>
									<li role="listitem" data-language="<?php echo esc_attr( $lang_code ); ?>" class="<?php echo ( $lang_code === $active_language ) ? 'onetap-active' : ''; ?>">
										<button type="button">
											<?php echo esc_html( $lang_data['name'] ); ?>
											<img data-src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/' . $lang_data['flag'] ); ?>" alt="<?php echo esc_attr( $lang_data['country'] ); ?>">
										</button>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>

						<!-- Close -->
						<button role="button" aria-label="Close toolbar" class="onetap-close">
							<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
						</button>

						<!-- Info -->
						<div class="onetap-site-container">
							<div class="onetap-site-info">
								<div class="onetap-image">
									<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 701.81 701.83">
										<path fill="#FFFFFF" d="M331.08.55C190.9,8.53,70.26,97.88,20.83,230.33c-8.95,23.97-16.5,56.86-19.49,84.87-1.76,16.45-1.79,55.1-.07,71.18,8.8,81.9,43.7,155.21,101.34,212.85,57.73,57.73,132.07,93.03,213.44,101.34,16.84,1.72,54.16,1.69,70.59-.06,148.01-15.76,269.77-121.62,305.15-265.3,11.7-47.53,13.22-101.36,4.21-149.42-19.27-102.71-84.89-192.59-177.59-243.23C462.11,11.8,395.54-3.13,331.08.55ZM363.97,142.83c12.37,3.82,21.52,11.62,27.6,23.54,3.03,5.94,3.3,7.54,3.29,19.41-.01,12.48-.16,13.21-4.03,20.37-11.86,21.94-37.82,30.71-59.64,20.15-7.89-3.82-17.14-12.92-21.05-20.71-2.88-5.74-3.52-8.61-3.88-17.52-.53-13.01.78-18.23,6.86-27.33,11.17-16.72,31.5-23.89,50.84-17.91ZM239.63,230.98c56.8,8.19,67.86,9.37,95.7,10.22,36.3,1.11,59.67-.74,121.9-9.63,32.32-4.62,56.53-7.55,60.11-7.27,7.74.61,12.4,3.96,16.26,11.72,5.11,10.26,3.12,21.41-5.06,28.3-4.69,3.95-2.2,3.27-66.49,17.94-32.36,7.38-54.83,13.06-56.06,14.18-3.26,2.95-3.67,8.6-2.3,31.46,3.83,63.99,12.07,102.66,36.42,170.84,5.31,14.88,9.95,29.51,10.31,32.49,1.3,10.96-4.46,21.09-15.8,27.73-4.42,2.59-5.97,2.9-11.13,2.21-10.61-1.41-17.22-6.06-21.85-15.38-1.28-2.59-13.07-33.43-26.2-68.53-13.12-35.1-24.18-63.83-24.56-63.82-.39,0-11.27,28.17-24.19,62.6-12.92,34.43-24.93,65.63-26.68,69.34-8.74,18.47-36.45,20.12-45.98,2.74-5.48-9.99-4.95-13.08,9.64-56.7,22.94-68.59,30.75-106.34,34.2-165.25,1.57-26.79,1.21-28.53-6.51-31.25-2.59-.91-21.91-5.61-42.94-10.43-73.02-16.75-75.15-17.5-80.88-28.73-5.66-11.08-1.62-23.77,9.71-30.46,3.58-2.11,16.54-.93,62.4,5.68Z"/>
									</svg>
								</div>
								<div class="onetap-title">
									<span class="onetap-heading">
										<?php esc_html_e( 'Accessibility  Adjustments', 'accessibility-onetap' ); ?>
									</span>
								</div>					
								<div class="onetap-information">
									<?php
									// Each entry prints a separator before itself, except the first
									// one that actually makes it onto the page. Adding or hiding an
									// entry therefore needs no matching CSS change: whatever ends up
									// leftmost simply never gets a dot in front of it.
									$onetap_footer_entry_printed = false;
									?>

									<!-- Powered by -->
									<?php if ( $powered_by_is_visible ) : ?>
										<?php $this->render_footer_separator( $onetap_footer_entry_printed ); ?>
										<div class="onetap-desc">
											<p>
												<span>
													<?php esc_html_e( 'Powered by', 'accessibility-onetap' ); ?>
												</span>
												<a href="<?php echo esc_url( 'https://wponetap.com/?utm_source=frontend-link&utm_medium=link&utm_campaign=ref-link-toolbar' ); ?>" target="_blank">
													<?php esc_html_e( 'OneTap', 'accessibility-onetap' ); ?>
												</a>
											</p>
										</div>
									<?php endif; ?>

									<!-- Accessibility status -->
									<?php if ( $statement_is_visible ) : ?>
										<?php $this->render_footer_separator( $onetap_footer_entry_printed ); ?>
										<div class="onetap-statement">
											<button role="button" aria-label="Open toolbar" class="open-accessibility-message">
												<?php esc_html_e( 'Statement', 'accessibility-onetap' ); ?>
											</button>
										</div>
									<?php endif; ?>

									<!-- Hide Toolbar -->
									<?php $this->render_footer_separator( $onetap_footer_entry_printed ); ?>
									<div class="onetap-hide-toolbar">
										<button role="button" aria-label="Setting toolbar" class="open-form-hide-toolbar">
											<?php esc_html_e( 'Hide Toolbar', 'accessibility-onetap' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</header>

					<!-- Accessibility status -->
					<div class="accessibility-status-wrapper" style="display: none;">
						<div class="accessibility-status-text">
							<button role="button" aria-label="Close toolbar" class="close-accessibility-message">
								<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9.68 5.313c-.099.065-1.494 1.436-3.1 3.045-1.606 1.61-2.969 2.953-3.03 2.985-.061.033-.157.151-.217.268-.14.274-.143.515-.009.73.055.087 1.503 1.556 3.218 3.264 3.31 3.297 3.24 3.234 3.562 3.182a.828.828 0 0 0 .289-.132c.342-.246.487-.62.347-.898-.037-.076-1.169-1.239-2.514-2.587l-2.445-2.45h13.442l.178-.09c.25-.127.328-.281.328-.65s-.078-.523-.328-.65l-.178-.09H5.821l2.425-2.43c1.334-1.337 2.457-2.491 2.494-2.567.195-.388-.209-.989-.695-1.032-.154-.014-.215.003-.365.102" fill-rule="evenodd"></path></svg>
								<?php esc_html_e( 'Back', 'accessibility-onetap' ); ?>
							</button>							
							<?php
							$editor_generator   = get_option( 'onetap_editor_generator' );
							$show_accessibility = get_option( 'onetap_show_accessibility' );

							if ( ! empty( $editor_generator ) && $show_accessibility ) {
								echo wp_kses_post( $editor_generator );
							}
							?>
						</div>
					</div>						

					<!-- Toolbar hide duration -->
					<div class="toolbar-hide-duration" style="display: none;">
						<div class="box-hide-duration">
							<span class="onetap-title"><?php esc_html_e( 'How long do you want to hide the toolbar?', 'accessibility-onetap' ); ?></span>

							<form>
								<fieldset>
									<legend><?php esc_html_e( 'Hide Toolbar Duration', 'accessibility-onetap' ); ?></legend>
									<label class="toolbar-duration-option active" for="only-for-this-session" tabindex="0">
										<input type="radio" id="only-for-this-session" name="hide_toolbar_duration" tabindex="-1" checked>
										<span>
											<?php esc_html_e( 'Only for this session', 'accessibility-onetap' ); ?>
										</span>
									</label>

									<label class="toolbar-duration-option" for="only-for-24-hours" tabindex="0">
										<input type="radio" id="only-for-24-hours" name="hide_toolbar_duration" tabindex="-1">
										<span>
											<?php esc_html_e( '24 hours', 'accessibility-onetap' ); ?>
										</span>
									</label>

									<label class="toolbar-duration-option" for="only-for-a-week" tabindex="0">
										<input type="radio" id="only-for-a-week" name="hide_toolbar_duration" tabindex="-1">
										<span>
											<?php esc_html_e( 'A Week', 'accessibility-onetap' ); ?>
										</span>
									</label>
								</fieldset>
								<div class="box-btn-action">
									<button type="button" class="close-box-hide-duration"><?php esc_html_e( 'Not Now', 'accessibility-onetap' ); ?></button>
									<button type="button" class="hide-toolbar"><?php esc_html_e( 'Hide Toolbar', 'accessibility-onetap' ); ?></button>
								</div>
							</form>
						</div>
					</div>							

					<!-- Multi functional feature -->
					<div class="onetap-features-container onetap-multi-functional-feature">
						<div class="onetap-box-functions">
							<div class="onetap-box-title">
								<span><?php esc_html_e( 'Select your accessibility profile', 'accessibility-onetap' ); ?></span>
							</div>

							<!-- Vision Impaired Mode -->
							<div class="onetap-functional-feature onetap-box-vision-impaired-mode">
								<div class="onetap-left">
									<div class="onetap-icon">
										<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.46 4.283c-2.079.139-4.351 1.323-6.322 3.295a14.954 14.954 0 0 0-2.469 3.232c-.322.557-.397.783-.397 1.19 0 .417.077.641.427 1.237.979 1.67 2.179 3.091 3.525 4.174 1.94 1.561 3.82 2.315 5.776 2.315 1.248 0 2.307-.259 3.54-.865 1.758-.865 3.587-2.458 4.866-4.241.555-.774 1.085-1.653 1.233-2.045.123-.324.124-.824.002-1.144-.142-.378-.709-1.318-1.235-2.051-.698-.971-1.728-2.065-2.63-2.791C15.674 4.897 13.6 4.14 11.46 4.283m1.507 1.555c1.632.279 3.257 1.223 4.854 2.821.857.857 1.446 1.615 2.103 2.71.178.297.316.572.316.63 0 .117-.481.944-.885 1.522-.922 1.318-2.18 2.567-3.414 3.389-2.416 1.61-4.736 1.759-7.13.458-1.487-.808-3.054-2.255-4.16-3.84-.408-.584-.891-1.413-.891-1.527 0-.058.137-.333.31-.623a13.009 13.009 0 0 1 2.109-2.719c2.239-2.24 4.556-3.203 6.788-2.821m-1.422 2.567c-.339.044-.93.238-1.225.402-.96.535-1.602 1.383-1.868 2.464-.082.338-.093 1.216-.018 1.529.319 1.329 1.161 2.311 2.346 2.735 2.183.78 4.486-.544 4.927-2.834.072-.375.05-1.144-.042-1.501-.294-1.129-.95-1.945-1.973-2.456-.657-.328-1.363-.439-2.147-.339m1.107 1.455c.385.1.706.289 1.012.596.457.456.671.967.672 1.604a2.292 2.292 0 0 1-1.616 2.185c-.342.109-.923.117-1.258.018-.788-.232-1.405-.853-1.602-1.611-.076-.291-.077-.85-.002-1.139a2.33 2.33 0 0 1 1.638-1.653c.274-.074.874-.074 1.156 0" fill-rule="evenodd"/></svg>
									</div>
									<div class="onetap-text">
										<div class="onetap-title">
											<span>
												<?php esc_html_e( 'Vision Impaired Mode', 'accessibility-onetap' ); ?>
											</span>
										</div>
										<div class="onetap-desc">
											<span id="vision-impaired-desc">
												<?php esc_html_e( "Enhances website's visuals", 'accessibility-onetap' ); ?>
											</span>
										</div>
									</div>
								</div>
								<div class="onetap-right">
									<div class="box-swich">
										<?php
										// The checkbox carries role="switch": a <label> is not
										// allowed to take a role, so the switch semantics and the
										// checked state live on the control itself. The label keeps
										// naming it through the visually hidden text below.
										?>
										<label class="switch label-mode-switch" for="onetap-box-vision-impaired-mode">
											<input type="checkbox" name="onetap-box-vision-impaired-mode" id="onetap-box-vision-impaired-mode" value="1" role="switch" aria-checked="false">
											<span class="slider round"></span>
											<span class="onetap-visually-hidden"><?php esc_html_e( 'Vision Impaired Mode', 'accessibility-onetap' ); ?></span>
										</label>
									</div>
								</div>
							</div>

							<!-- Seizure Safe Profile -->
							<div class="onetap-functional-feature onetap-box-seizure-safe-profile-mode">
								<div class="onetap-left">
									<div class="onetap-icon">
										<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.815 2.277a.8.8 0 0 0-.462.354c-.087.139-.094.197-.107.899-.013.731-.016.751-.1.768-.047.01-.221.039-.386.064-1.283.194-2.647.805-3.66 1.64A11.39 11.39 0 0 0 5.932 7.18c-.776.961-1.384 2.346-1.57 3.58-.025.165-.054.339-.064.386-.017.084-.037.087-.771.1-.739.014-.756.016-.915.121a.7.7 0 0 0-.345.64c0 .196.019.263.116.401.208.294.33.33 1.167.346.71.013.731.016.748.1.01.047.039.221.064.386.186 1.234.794 2.619 1.57 3.58.249.308.857.921 1.168 1.178 1.014.836 2.417 1.462 3.68 1.641.176.025.35.054.386.065.058.017.068.112.08.768.013.7.02.758.107.897.357.574 1.223.443 1.363-.207.024-.113.044-.483.044-.821 0-.704-.049-.629.46-.702 1.263-.179 2.666-.805 3.68-1.641.311-.256.918-.869 1.168-1.178.778-.962 1.399-2.385 1.571-3.6.073-.509-.002-.46.702-.46.781 0 .979-.04 1.179-.24.398-.398.21-1.097-.331-1.234-.102-.025-.472-.046-.824-.046-.732 0-.653.05-.726-.46-.172-1.215-.793-2.638-1.571-3.6-.25-.309-.857-.922-1.168-1.178-1.013-.835-2.377-1.446-3.66-1.64-.541-.082-.48.008-.481-.713-.001-.699-.038-.928-.179-1.113-.159-.209-.502-.325-.765-.259m-.569 4.233c.013.66.021.72.107.859.357.574 1.223.443 1.363-.207a5.61 5.61 0 0 0 .044-.786v-.581l.19.026c.717.1 1.599.423 2.297.841.778.466 1.621 1.309 2.09 2.091.417.694.742 1.58.841 2.293l.026.186-.712.014c-.667.013-.722.02-.865.109a.714.714 0 0 0-.36.648c0 .2.019.267.116.405.206.29.334.33 1.129.346l.692.014-.026.186c-.099.713-.424 1.599-.841 2.293-.469.782-1.312 1.625-2.09 2.091-.698.418-1.58.741-2.297.841l-.19.026v-.581c0-.743-.042-.946-.238-1.142-.349-.349-.903-.279-1.169.149-.087.139-.094.199-.107.861l-.014.712-.186-.026c-.712-.099-1.596-.423-2.293-.84-.76-.456-1.641-1.331-2.076-2.061-.43-.722-.756-1.61-.856-2.327l-.026-.19h.581c.745 0 .946-.042 1.144-.24.398-.398.21-1.097-.331-1.234-.102-.025-.457-.046-.789-.046h-.605l.026-.19c.1-.716.427-1.605.855-2.324C7.107 8.001 8 7.107 8.723 6.677c.699-.416 1.563-.739 2.277-.85.249-.039.231-.09.246.683m.174 2.835a3.349 3.349 0 0 0-.62.225c-.276.135-.408.234-.702.528-.294.294-.393.425-.528.702a2.741 2.741 0 0 0 1.942 3.917c.965.196 2.078-.224 2.671-1.008.847-1.119.755-2.637-.218-3.618-.666-.671-1.599-.944-2.545-.746m1.126 1.554c.255.115.487.342.614.603.133.269.139.751.014 1.023a1.328 1.328 0 0 1-.608.624c-.31.152-.767.157-1.064.011-.776-.38-.962-1.383-.37-1.993.385-.398.905-.496 1.414-.268" fill-rule="evenodd"/></svg>
									</div>
									<div class="onetap-text">
										<div class="onetap-title">
											<span>
												<?php esc_html_e( 'Seizure Safe Profile', 'accessibility-onetap' ); ?>
											</span>
										</div>
										<div class="onetap-desc">
											<span id="seizure-safe-profile">
												<?php esc_html_e( 'Clear flashes & reduces color', 'accessibility-onetap' ); ?>
											</span>
										</div>
									</div>
								</div>
								<div class="onetap-right">
									<div class="box-swich">
										<label class="switch label-mode-switch" for="onetap-box-seizure-safe-profile">
											<input type="checkbox" name="onetap-box-seizure-safe-profile" id="onetap-box-seizure-safe-profile" value="1" role="switch" aria-checked="false">
											<span class="slider round"></span>
											<span class="onetap-visually-hidden"><?php esc_html_e( 'Seizure Safe Profile', 'accessibility-onetap' ); ?></span>
										</label>
									</div>
								</div>
							</div>								

							<!-- ADHD Friendly Mode -->
							<div class="onetap-functional-feature onetap-box-adhd-friendly-mode">
								<div class="onetap-left">
									<div class="onetap-icon">
										<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.08 2.281c-1.491.156-2.877.614-4.097 1.354C4.42 5.19 2.745 7.747 2.337 10.728c-.118.858-.082 2.127.085 3.042.153.841.536 1.987.77 2.304.198.27.596.357.918.201.355-.172.485-.556.329-.975a128.71 128.71 0 0 1-.227-.62c-.612-1.693-.609-3.662.009-5.44.698-2.009 2.228-3.723 4.159-4.661 1.21-.588 2.268-.831 3.62-.831 1.352 0 2.41.243 3.62.831a8.26 8.26 0 0 1 3.779 3.761 8.59 8.59 0 0 1 .804 2.737c.06.533.027 1.78-.06 2.263-.531 2.942-2.462 5.296-5.216 6.359-.939.363-1.84.52-2.967.517-1.142-.002-1.93-.149-2.983-.556-.505-.196-.623-.212-.848-.118a.734.734 0 0 0-.368 1.058c.083.151.147.204.392.325.653.324 1.779.627 2.747.739.684.079 1.854.059 2.54-.043a9.716 9.716 0 0 0 6.636-4.201c1.213-1.815 1.78-3.893 1.643-6.03a9.422 9.422 0 0 0-.977-3.69c-1.413-2.891-4.138-4.88-7.342-5.361-.5-.074-1.841-.108-2.32-.058m.42 3.582-.44.06a6.148 6.148 0 0 0-1.81.572c-1.811.912-3.031 2.613-3.331 4.645-.097.653-.039 1.83.104 2.105a.802.802 0 0 0 .463.357.732.732 0 0 0 .813-.313c.117-.178.121-.23.081-1.089-.024-.497-.017-.694.036-.997a4.667 4.667 0 0 1 4.136-3.84c2.404-.241 4.614 1.446 5.031 3.84.088.504.059 1.394-.061 1.875a4.692 4.692 0 0 1-3.075 3.322c-.669.224-1.3.283-2.071.194-.388-.045-.411-.043-.593.044a.745.745 0 0 0-.124 1.264c.285.217 1.466.288 2.343.139 2.382-.402 4.246-2.083 4.924-4.441.26-.907.272-2.122.028-3.1a6.14 6.14 0 0 0-4.974-4.577c-.303-.048-1.285-.087-1.48-.06m-1.667 4.578c-.342.181-.474.664-.28 1.026.07.131.274.286.438.331.082.023.365.042.629.042h.479l-3.027 3.03c-1.689 1.69-3.047 3.08-3.07 3.143-.215.575.317 1.147.92.987.162-.042.447-.315 3.208-3.072l3.03-3.026.001.519c.001.603.055.776.302.964.134.102.183.115.437.115s.303-.013.438-.116a.875.875 0 0 0 .228-.288c.072-.163.076-.27.065-1.731l-.011-1.558-.123-.153c-.238-.299-.207-.294-1.957-.292-1.464.001-1.569.006-1.707.079" fill-rule="evenodd"/></svg>
									</div>
									<div class="onetap-text">
										<div class="onetap-title">
											<span>
												<?php esc_html_e( 'ADHD Friendly Mode', 'accessibility-onetap' ); ?>
											</span>
										</div>
										<div class="onetap-desc">
											<span id="adhd-friendly-mode">
												<?php esc_html_e( 'Focused browsing, distraction-free', 'accessibility-onetap' ); ?>
											</span>
										</div>
									</div>
								</div>
								<div class="onetap-right">
									<div class="box-swich">
										<label class="switch label-mode-switch" for="onetap-box-adhd-friendly-mode">
											<input type="checkbox" name="onetap-box-adhd-friendly-mode" id="onetap-box-adhd-friendly-mode" value="1" role="switch" aria-checked="false">
											<span class="slider round"></span>
											<span class="onetap-visually-hidden"><?php esc_html_e( 'ADHD Friendly Mode', 'accessibility-onetap' ); ?></span>
										</label>
									</div>
								</div>
							</div>									

							<!-- Blindness Mode -->
							<div class="onetap-functional-feature onetap-box-blindness-mode">
								<div class="onetap-left">
									<div class="onetap-icon">
										<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M15.815 2.277a.8.8 0 0 0-.462.354l-.093.149v18.44l.093.149c.357.574 1.223.443 1.363-.207.06-.28.061-18.061.001-18.321a.747.747 0 0 0-.902-.564m-12 3a.8.8 0 0 0-.462.354l-.093.149v12.44l.093.149c.357.574 1.223.443 1.363-.207.06-.279.061-12.062.001-12.321a.747.747 0 0 0-.902-.564m8 0a.8.8 0 0 0-.462.354l-.093.149v12.44l.093.149c.357.574 1.223.443 1.363-.207.06-.279.061-12.062.001-12.321a.747.747 0 0 0-.902-.564m8 2a.8.8 0 0 0-.462.354l-.093.149v8.44l.093.149c.357.574 1.223.443 1.363-.207.059-.277.06-8.064.001-8.321a.747.747 0 0 0-.902-.564m-12 1a.8.8 0 0 0-.462.354l-.093.149v6.44l.093.149c.357.574 1.223.443 1.363-.207.059-.275.06-6.065.001-6.321a.747.747 0 0 0-.902-.564" fill-rule="evenodd"/></svg>
									</div>
									<div class="onetap-text">
										<div class="onetap-title">
											<span>
												<?php esc_html_e( 'Blindness Mode', 'accessibility-onetap' ); ?>
											</span>
										</div>
										<div class="onetap-desc">
											<span id="blindness-mode">
												<?php esc_html_e( 'Reduces distractions, improves focus', 'accessibility-onetap' ); ?>
											</span>
										</div>
									</div>
								</div>
								<div class="onetap-right">
									<div class="box-swich">
										<label class="switch label-mode-switch" for="onetap-box-blindness-mode">
											<input type="checkbox" name="onetap-box-blindness-mode" id="onetap-box-blindness-mode" value="1" role="switch" aria-checked="false">
											<span class="slider round"></span>
											<span class="onetap-visually-hidden"><?php esc_html_e( 'Blindness Mode', 'accessibility-onetap' ); ?></span>
										</label>
									</div>
								</div>
							</div>								

							<!-- Epilepsy Safe Mode -->
							<div class="onetap-functional-feature onetap-box-epilepsy-safe-mode">
								<div class="onetap-left">
									<div class="onetap-icon">
										<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.34 2.281C7.073 2.553 3.439 5.66 2.499 9.84a10.086 10.086 0 0 0 0 4.32 9.76 9.76 0 0 0 7.341 7.341c1.393.313 2.93.312 4.336-.003 3.289-.739 5.985-3.188 7.068-6.422a9.928 9.928 0 0 0 .257-5.236 9.76 9.76 0 0 0-7.341-7.341 10.445 10.445 0 0 0-2.82-.218m1.621 1.521a8.318 8.318 0 0 1 5.894 3.608c.543.802 1.034 1.968 1.222 2.899.124.611.163 1.019.163 1.691 0 1.332-.263 2.465-.845 3.642a8.146 8.146 0 0 1-3.753 3.753c-1.177.582-2.31.845-3.642.845a7.867 7.867 0 0 1-3.626-.836 8.266 8.266 0 0 1-4.572-6.443c-.054-.436-.054-1.486 0-1.922.195-1.582.857-3.123 1.846-4.299.337-.4.751-.811 1.168-1.159 1.084-.904 2.682-1.585 4.168-1.775.395-.051 1.579-.053 1.977-.004m-1.262 1.974c-.149.065-.367.308-.408.455-.017.06-.031.964-.031 2.009v1.9l.093.149c.361.582 1.228.441 1.365-.221.032-.15.042-.784.034-2.014-.013-1.965-.006-1.902-.258-2.141a.756.756 0 0 0-.795-.137M7.815 7.277a.802.802 0 0 0-.459.349c-.121.196-.124.547-.006.738.047.075.351.399.677.721.535.527.612.588.783.625.578.123 1.023-.322.9-.9-.037-.171-.098-.248-.625-.783-.322-.326-.639-.626-.705-.666a.855.855 0 0 0-.565-.084m8.085-.018a1.849 1.849 0 0 1-.157.04c-.13.029-1.247 1.101-1.393 1.337-.118.191-.115.542.006.738.176.285.484.41.833.337.175-.037.244-.093.837-.685.592-.593.648-.662.685-.837.071-.341-.053-.659-.322-.822-.124-.075-.406-.138-.489-.108M6.38 11.26a2.274 2.274 0 0 1-.149.037c-.147.032-.39.251-.457.411a.742.742 0 0 0 .139.786c.239.252.176.245 2.141.258 2.052.014 2.15.004 2.385-.231.399-.399.212-1.098-.33-1.235-.127-.032-.731-.045-1.937-.043-.963.002-1.77.009-1.792.017m7.515.017c-.485.119-.717.727-.432 1.131a.939.939 0 0 0 .277.248c.156.082.211.084 2.04.084 1.034 0 1.929-.014 1.989-.031.152-.042.392-.262.457-.417a.742.742 0 0 0-.139-.786c-.24-.254-.167-.245-2.207-.253-1.023-.004-1.916.007-1.985.024m-2.08 2.08a.8.8 0 0 0-.462.354l-.093.149v1.9c0 1.045.014 1.949.031 2.009.042.152.262.392.417.457a.742.742 0 0 0 .786-.139c.252-.239.245-.175.258-2.143.013-1.912-.001-2.104-.171-2.326-.16-.211-.502-.327-.766-.261m-2.915.902a1.849 1.849 0 0 1-.157.04c-.13.029-1.247 1.101-1.393 1.337-.118.191-.115.542.006.738.176.285.484.41.833.337.175-.037.244-.093.837-.685.592-.593.648-.662.685-.837.071-.341-.053-.659-.322-.822-.124-.075-.406-.138-.489-.108m5.915.018a.802.802 0 0 0-.459.349c-.121.196-.124.547-.006.738.047.075.351.399.677.721.535.527.612.588.783.625.578.123 1.023-.322.9-.9-.037-.171-.098-.248-.625-.783-.322-.326-.639-.626-.705-.666a.855.855 0 0 0-.565-.084" fill-rule="evenodd"/></svg>
									</div>
									<div class="onetap-text">
										<div class="onetap-title">
											<span>
												<?php esc_html_e( 'Epilepsy Safe Mode', 'accessibility-onetap' ); ?>
											</span>
										</div>
										<div class="onetap-desc">
											<span id="epilepsy-safe-mode">
												<?php esc_html_e( 'Dims colors and stops blinking', 'accessibility-onetap' ); ?>
											</span>
										</div>
									</div>
								</div>
								<div class="onetap-right">
									<div class="box-swich">
										<label class="switch label-mode-switch" for="onetap-box-epilepsy-safe-mode">
											<input type="checkbox" name="onetap-box-epilepsy-safe-mode" id="onetap-box-epilepsy-safe-mode" value="1" role="switch" aria-checked="false">
											<span class="slider round"></span>
											<span class="onetap-visually-hidden"><?php esc_html_e( 'Epilepsy Safe Mode', 'accessibility-onetap' ); ?></span>
										</label>
									</div>
								</div>
							</div>								
						</div>
					</div>					

					<!-- Features Content Modules-->
					<div class="onetap-features-container onetap-feature-content-modules">
						<div class="onetap-features">
							<div class="onetap-box-title">
								<span class="onetap-title"><?php esc_html_e( 'Content Modules', 'accessibility-onetap' ); ?></span>
							</div>
						
							<div class="onetap-box-features">
								<div class="onetap-box-step-controls onetap-font-size">
									<!-- Feature Bigger Text -->
									<div class="onetap-box-feature onetap-bigger-text onetap-new-level">

										<div class="onetap-title">
											<span class="onetap-heading"><?php esc_html_e( 'Font Size', 'accessibility-onetap' ); ?></span>
											<div class="box-btn">
												<button class="onetap-btn onetap-btn-increase" aria-label="<?php esc_html_e( 'Increase Font Size', 'accessibility-onetap' ); ?>">
													<span style="display: none !important;" class="onetap-screen-reader-text"><?php esc_html_e( '+', 'accessibility-onetap' ); ?></span>
													<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none"><path d="M8.5 1V15M1.5 8H15.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</button>
												<p class="onetap-info"><?php esc_html_e( 'Default', 'accessibility-onetap' ); ?></p>

												<button class="onetap-btn onetap-btn-decrease" aria-label="<?php esc_html_e( 'Decrease Font Size', 'accessibility-onetap' ); ?>">
													<span style="display: none !important;" class="onetap-screen-reader-text"><?php esc_html_e( '-', 'accessibility-onetap' ); ?></span>
													<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="17" height="2" viewBox="0 0 17 2" fill="none"><path d="M1.5 1H15.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</button>
											</div>
											<p class="onetap-option-levels">
												<span class="onetap-level onetap-level1"></span>
												<span class="onetap-level onetap-level2"></span>
												<span class="onetap-level onetap-level3"></span>
											</p>
										</div>
									</div>		
								</div>

								<!-- Feature Readable Font -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-readable-font">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.34 2.281C7.073 2.553 3.439 5.66 2.499 9.84a10.086 10.086 0 0 0 0 4.32 9.76 9.76 0 0 0 7.341 7.341c1.393.313 2.93.312 4.336-.003 3.289-.739 5.985-3.188 7.068-6.422a9.928 9.928 0 0 0 .257-5.236 9.76 9.76 0 0 0-7.341-7.341 10.445 10.445 0 0 0-2.82-.218m1.621 1.521a8.318 8.318 0 0 1 5.894 3.608c.543.802 1.034 1.968 1.222 2.899.124.611.163 1.019.163 1.691 0 1.332-.263 2.465-.845 3.642a8.146 8.146 0 0 1-3.753 3.753c-1.177.582-2.31.845-3.642.845a7.867 7.867 0 0 1-3.626-.836 8.266 8.266 0 0 1-4.572-6.443c-.054-.436-.054-1.486 0-1.922.195-1.582.857-3.123 1.846-4.299.337-.4.751-.811 1.168-1.159 1.084-.904 2.682-1.585 4.168-1.775.395-.051 1.579-.053 1.977-.004M11.614 7.62c-.134.08-.2.167-.345.45-.386.755-3.301 6.957-3.319 7.063a.892.892 0 0 0 .017.279c.101.448.57.699.984.526.244-.102.348-.238.612-.802l.251-.536h4.37l.237.508c.131.279.282.561.336.625a.84.84 0 0 0 .563.265c.29 0 .616-.238.699-.51.092-.305.097-.293-1.56-3.794-2.017-4.258-1.858-3.947-2.072-4.072a.771.771 0 0 0-.773-.002m1.117 3.92c.39.826.709 1.519.709 1.54 0 .026-.516.04-1.44.04-.991 0-1.44-.013-1.44-.043 0-.057 1.413-3.037 1.44-3.037.012 0 .341.675.731 1.5" fill-rule="evenodd"/></svg>
										</span>
									</div>

									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Readable Font', 'accessibility-onetap' ); ?></span>
									</div>
								</button>							

								<div class="onetap-box-step-controls onetap-line-height">
									<!-- Feature Line Height -->
									<div class="onetap-box-feature onetap-line-height onetap-new-level">
										<div class="onetap-title">
											<span class="onetap-heading"><?php esc_html_e( 'Line Height', 'accessibility-onetap' ); ?></span>
											<div class="box-btn">
												<button class="onetap-btn onetap-btn-increase" aria-label="<?php esc_html_e( 'Increase Line Height', 'accessibility-onetap' ); ?>">
													<span style="display: none !important;" class="onetap-screen-reader-text"><?php esc_html_e( '+', 'accessibility-onetap' ); ?></span>
													<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none"><path d="M8.5 1V15M1.5 8H15.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</button>
												<p class="onetap-info"><?php esc_html_e( 'Default', 'accessibility-onetap' ); ?></p>

												<button class="onetap-btn onetap-btn-decrease" aria-label="<?php esc_html_e( 'Decrease Line Height', 'accessibility-onetap' ); ?>">
													<span style="display: none !important;" class="onetap-screen-reader-text"><?php esc_html_e( '-', 'accessibility-onetap' ); ?></span>
													<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="17" height="2" viewBox="0 0 17 2" fill="none"><path d="M1.5 1H15.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
												</button>
											</div>
											<p class="onetap-option-levels">
												<span class="onetap-level onetap-level1"></span>
												<span class="onetap-level onetap-level2"></span>
												<span class="onetap-level onetap-level3"></span>
											</p>
										</div>
									</div>										
								</div>

								<!-- Feature Cursor -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-cursor">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7.922 3.562a1.577 1.577 0 0 0-1.089.908l-.093.23v13.006l.099.145c.21.306.614.408.943.237.087-.045.788-.478 1.558-.962a76.104 76.104 0 0 1 1.404-.873l.92 1.807c.717 1.412.95 1.836 1.082 1.968.355.355.908.515 1.373.395.314-.08 2.678-1.285 2.908-1.481.333-.285.538-.893.453-1.343-.027-.143-.337-.802-.943-2.009-.497-.988-.897-1.803-.889-1.811.007-.008.738-.302 1.623-.654.886-.352 1.662-.678 1.726-.723a.826.826 0 0 0 .205-.265.673.673 0 0 0-.072-.707c-.162-.221-10.122-7.686-10.388-7.786a1.646 1.646 0 0 0-.82-.082m9.155 8.078c0 .011-.629.269-1.399.574-.816.322-1.452.597-1.525.658-.156.131-.233.313-.233.551 0 .163.129.44 1.082 2.32l1.081 2.135-1.158.58-1.159.579-1.053-2.087c-.579-1.148-1.096-2.138-1.148-2.2-.137-.164-.292-.23-.537-.23-.247 0-.237-.005-1.888 1.034l-.96.604-.01-5.549c-.006-3.052-.002-5.572.009-5.601.013-.033 1.625 1.153 4.459 3.28a524.02 524.02 0 0 1 4.439 3.352" fill-rule="evenodd"/></svg>
										</span>
									</div>

									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Cursor', 'accessibility-onetap' ); ?></span>
									</div>
								</button>								

								<!-- Feature Letter Spacing -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-letter-spacing">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 256 256">
												<path d="M2.815 3.278c-.484.115-.717.726-.432 1.13.075.1.17.184.277.248.159.083.191.084 4.219.095 2.865.008 4.122-.002 4.274-.034.749-.155.777-1.244.036-1.431-.21-.052-8.155-.06-8.374-.008M17.9 3.259c-.053.016-.106.03-.16.04-.162.036-2.795 2.648-2.904 2.881a.907.907 0 0 0-.074.32c0 .18.108.446.224.548a.918.918 0 0 0 .514.192c.273 0 .424-.107 1.09-.771l.65-.648v12.358l-.65-.648c-.672-.669-.817-.772-1.099-.77-.173.001-.439.112-.539.225a.794.794 0 0 0-.116.834c.05.106.535.617 1.429 1.506 1.283 1.274 1.365 1.347 1.545 1.385a.935.935 0 0 0 .38 0c.18-.038.262-.111 1.545-1.385.894-.889 1.379-1.4 1.429-1.506a.794.794 0 0 0-.116-.834c-.1-.113-.366-.224-.539-.225-.282-.002-.427.101-1.099.77l-.65.648V5.821l.65.648c.666.664.817.771 1.09.771.16 0 .398-.089.514-.192.116-.102.224-.368.224-.548 0-.309-.099-.43-1.484-1.805-.734-.729-1.37-1.344-1.414-1.366-.091-.045-.38-.092-.44-.07M2.815 7.278c-.484.115-.717.726-.432 1.13.075.1.17.184.277.248.158.083.205.084 3.218.095C8.02 8.759 9 8.749 9.151 8.718c.751-.156.78-1.245.038-1.432-.21-.052-6.156-.06-6.374-.008m0 4c-.484.115-.717.726-.432 1.13.075.1.17.184.277.248.159.083.191.084 4.219.095 2.865.008 4.122-.002 4.274-.034.749-.155.777-1.244.036-1.431-.21-.052-8.155-.06-8.374-.008m0 4c-.484.115-.717.726-.432 1.13.075.1.17.184.277.248.158.083.205.084 3.218.095 2.142.008 3.122-.002 3.273-.033.751-.156.78-1.245.038-1.432-.21-.052-6.156-.06-6.374-.008m0 4c-.484.115-.717.726-.432 1.13.075.1.17.184.277.248.159.083.191.084 4.219.095 2.865.008 4.122-.002 4.274-.034.749-.155.777-1.244.036-1.431-.21-.052-8.155-.06-8.374-.008" transform="rotate(90 126.65 129.331) scale(10.66667)" fill-rule="evenodd" stroke-miterlimit="10" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode:normal"/>
											</svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Letter Spacing', 'accessibility-onetap' ); ?></span>
									</div>
								</button>									

								<!-- Feature Align Text -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-align-center onetap-text-align-mode-off">
									<div class="onetap-icon">
										<span class="onetap-icon-animation onetap-text-align-icons">
											<span class="onetap-text-align-icon onetap-text-align-icon--left" aria-hidden="true">
												<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3.72 3.805c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 16.376.06 16.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-16.403-.055-16.547.002m0 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 12.376.06 12.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-12.403-.055-12.547.002m0 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 16.376.06 16.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-16.403-.055-16.547.002m0 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.077.03 1.429.043 4.267.043s4.19-.013 4.267-.043a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.15-.057-8.404-.055-8.547.002" fill-rule="evenodd"/></svg>
											</span>
											<span class="onetap-text-align-icon onetap-text-align-icon--center" aria-hidden="true">
												<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3.72 3.805c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 16.376.06 16.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-16.403-.055-16.547.002m2 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 12.376.06 12.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-12.403-.055-12.547.002m-2 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 16.376.06 16.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-16.403-.055-16.547.002m4 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.077.03 1.429.043 4.267.043s4.19-.013 4.267-.043a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.15-.057-8.404-.055-8.547.002" fill-rule="evenodd"/></svg>
											</span>
											<span class="onetap-text-align-icon onetap-text-align-icon--right" aria-hidden="true">
												<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3.72 3.805c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 16.376.06 16.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-16.403-.055-16.547.002m4 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 12.376.06 12.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-12.403-.055-12.547.002m-4 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.158.06 16.376.06 16.534 0a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.151-.058-16.403-.055-16.547.002m8 5c-.262.104-.451.395-.451.695a.75.75 0 0 0 .464.697c.077.03 1.429.043 4.267.043s4.19-.013 4.267-.043a.75.75 0 0 0 .464-.697.75.75 0 0 0-.464-.697c-.15-.057-8.404-.055-8.547.002" fill-rule="evenodd"/></svg>
											</span>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Align Text', 'accessibility-onetap' ); ?></span>
									</div>
								</button>		

								<!-- Feature Letter Spacing -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-font-weight">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7.533 2.282c-2.527.207-4.649 2.073-5.15 4.529-.124.602-.142 1.271-.142 5.189s.018 4.587.142 5.189c.445 2.183 2.245 3.983 4.428 4.428.602.124 1.271.142 5.189.142s4.587-.018 5.189-.141c2.179-.445 3.984-2.25 4.429-4.429.123-.602.141-1.271.141-5.189s-.018-4.587-.141-5.189c-.292-1.427-1.211-2.78-2.438-3.589-.858-.566-1.705-.854-2.771-.942-.546-.045-8.323-.044-8.876.002m9.487 1.583c1.616.474 2.683 1.556 3.128 3.175.067.243.072.568.072 4.96s-.005 4.717-.072 4.96c-.229.832-.597 1.484-1.15 2.038-.554.553-1.206.921-2.038 1.15-.243.067-.568.072-4.96.072s-4.717-.005-4.96-.072c-.832-.229-1.484-.597-2.038-1.15a4.422 4.422 0 0 1-1.146-2.038c-.073-.286-.076-.511-.076-4.98V7.3l.09-.326a4.39 4.39 0 0 1 1.132-1.972A4.397 4.397 0 0 1 7.4 3.786c.055-.009 2.179-.013 4.72-.01 4.531.007 4.625.009 4.9.089M8.291 6.843c-.242.095-.525.353-.658.602l-.093.175v8.76l.093.175c.138.257.415.507.67.603.215.08.289.082 3.12.082 3.285 0 3.256.002 3.877-.3a2.893 2.893 0 0 0 1.074-.873c.385-.507.566-.99.612-1.627.064-.898-.234-1.658-.915-2.335l-.357-.355.099-.105c.191-.203.415-.6.526-.931.146-.436.184-1.135.087-1.602-.208-1.006-.997-1.88-2.006-2.223l-.32-.108-2.8-.01c-2.729-.008-2.805-.007-3.009.072m5.492 1.44c.31.057.576.205.801.445.712.762.466 1.961-.495 2.405-.187.086-.217.087-2.639.098L9 11.242V8.24h2.273c1.463 0 2.357.015 2.51.043m.637 4.529c.271.085.474.212.663.414.707.758.472 1.938-.474 2.387l-.269.127-2.67.012-2.67.011V12.76l2.63.001c2.005 0 2.668.012 2.79.051" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Font Weight', 'accessibility-onetap' ); ?></span>
									</div>
								</button>										
								
							</div>
						</div>
					</div>
					
					<!-- Features Color Modules -->
					<div class="onetap-features-container onetap-feature-color-modules">
						<div class="onetap-features">
							<div class="onetap-box-title">
								<span class="onetap-title"><?php esc_html_e( 'Color Modules', 'accessibility-onetap' ); ?></span>
							</div>

							<div class="onetap-box-features">

								<!-- Feature Light Contrast -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-light-contrast">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.66 1.276a.734.734 0 0 0-.398.413c-.097.232-.087 1.433.014 1.651.283.614 1.165.614 1.448 0 .063-.136.074-.263.074-.84s-.011-.704-.074-.84a.799.799 0 0 0-1.064-.384M4.701 4.149c-.135.035-.344.197-.447.348a.872.872 0 0 0-.094.687c.065.199.908 1.072 1.14 1.18a.847.847 0 0 0 .895-.136c.224-.206.305-.605.183-.899-.08-.195-.91-1.035-1.118-1.132a.924.924 0 0 0-.559-.048m14.039.045c-.21.102-1.039.942-1.118 1.135-.122.294-.041.693.183.899a.847.847 0 0 0 .895.136c.232-.108 1.075-.981 1.14-1.18a.838.838 0 0 0-.34-.932.838.838 0 0 0-.76-.058m-7.287 1.528a6.256 6.256 0 0 0-3.908 1.823 6.296 6.296 0 0 0 0 8.91 6.303 6.303 0 0 0 8.284.553c3.023-2.309 3.318-6.771.626-9.463-1.079-1.079-2.422-1.697-3.966-1.825-.511-.042-.503-.042-1.036.002m1.319 1.658a4.666 4.666 0 0 1 2.629 1.404 4.673 4.673 0 0 1 0 6.432c-2.251 2.371-6.145 1.779-7.612-1.156A4.765 4.765 0 0 1 7.32 12c0-2.28 1.62-4.209 3.877-4.618a5.652 5.652 0 0 1 1.575-.002M1.66 11.276c-.626.289-.608 1.196.029 1.462.232.097 1.433.087 1.651-.014.614-.283.614-1.165 0-1.448-.136-.063-.263-.074-.84-.074s-.704.011-.84.074m19 0c-.626.289-.608 1.196.029 1.462.232.097 1.433.087 1.651-.014.487-.224.614-.88.248-1.279-.191-.207-.351-.243-1.088-.243-.577 0-.704.011-.84.074M5.3 17.636c-.232.108-1.075.981-1.14 1.18-.198.612.412 1.222 1.024 1.024.199-.065 1.072-.908 1.18-1.14.139-.3.064-.714-.169-.928a.847.847 0 0 0-.895-.136m12.72 0a.796.796 0 0 0-.383 1.064c.097.208.937 1.038 1.132 1.118.223.093.433.077.675-.049a.797.797 0 0 0 .374-1c-.08-.195-.91-1.035-1.118-1.132a.843.843 0 0 0-.68-.001m-6.36 2.64a.734.734 0 0 0-.398.413c-.097.232-.087 1.433.014 1.651.224.487.88.614 1.279.248.207-.191.243-.351.243-1.088 0-.577-.011-.704-.074-.84a.799.799 0 0 0-1.064-.384" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Light Contrast', 'accessibility-onetap' ); ?></span>
									</div>
								</button>

								<!-- Feature High Contrast -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-high-contrast">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.34 2.281C7.073 2.553 3.439 5.66 2.499 9.84a10.086 10.086 0 0 0 0 4.32 9.76 9.76 0 0 0 7.341 7.341c1.393.313 2.93.312 4.336-.003 3.289-.739 5.985-3.188 7.068-6.422a9.928 9.928 0 0 0 .257-5.236 9.76 9.76 0 0 0-7.341-7.341 10.445 10.445 0 0 0-2.82-.218m1.621 1.521a8.318 8.318 0 0 1 5.894 3.608c.543.802 1.034 1.968 1.222 2.899.124.611.163 1.019.163 1.691 0 1.332-.263 2.465-.845 3.642a8.146 8.146 0 0 1-3.753 3.753c-1.177.582-2.31.845-3.642.845a7.867 7.867 0 0 1-3.626-.836 8.266 8.266 0 0 1-4.572-6.443c-.054-.436-.054-1.486 0-1.922.195-1.582.857-3.123 1.846-4.299.337-.4.751-.811 1.168-1.159 1.084-.904 2.682-1.585 4.168-1.775.395-.051 1.579-.053 1.977-.004m-1.262 2.011c-.15.069-.368.313-.408.458-.017.06-.031 2.656-.031 5.769 0 6.313-.025 5.767.277 6.032.179.157.335.186.852.154 2.505-.153 4.703-1.825 5.504-4.186.261-.767.323-1.159.323-2.04s-.062-1.273-.323-2.04C17.08 7.564 14.82 5.873 12.3 5.776c-.358-.014-.511-.005-.601.037m1.751 1.668a5.68 5.68 0 0 1 1.21.578c.309.202 1.079.972 1.281 1.281 1.272 1.95 1.013 4.444-.627 6.045a4.708 4.708 0 0 1-1.391.952c-.346.152-.954.343-1.087.343-.074 0-.076-.119-.076-4.685V7.31l.17.027c.093.015.328.08.52.144" fill-rule="evenodd"/></svg>
										</span>
										</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'High Contrast', 'accessibility-onetap' ); ?></span>
									</div>
								</button>

								<!-- Monochrome -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-monochrome">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.32 2.281a9.812 9.812 0 0 0-5.418 2.111c-.363.287-1.223 1.147-1.51 1.51-1.12 1.417-1.801 3.021-2.055 4.838-.09.647-.09 1.874.001 2.52.254 1.817.936 3.423 2.054 4.838.287.363 1.147 1.223 1.51 1.51A10.013 10.013 0 0 0 9.9 21.516c1.326.29 2.874.29 4.2 0a10.013 10.013 0 0 0 3.998-1.908c.363-.287 1.223-1.147 1.51-1.51a10.013 10.013 0 0 0 1.908-3.998c.29-1.326.29-2.874 0-4.2a10.013 10.013 0 0 0-1.908-3.998c-.287-.363-1.147-1.223-1.51-1.51a9.843 9.843 0 0 0-6.778-2.111m-.08 3.239v1.72H8.26c-1.639 0-2.98-.012-2.98-.026 0-.049.459-.598.778-.929a8.301 8.301 0 0 1 4.543-2.422c.165-.03.376-.056.469-.059l.17-.004v1.72m2.441-1.598c1.228.253 2.593.9 3.503 1.659.986.823 1.68 1.695 2.218 2.793A7.864 7.864 0 0 1 20.24 12a7.864 7.864 0 0 1-.838 3.626c-.538 1.098-1.232 1.97-2.218 2.793-1.083.904-2.829 1.644-4.173 1.769l-.251.024V3.788l.251.024c.138.013.44.062.67.11M11.24 10v1.24H3.8v-.133c0-.377.249-1.42.487-2.037l.119-.31h6.834V10m0 4v1.24H4.406l-.119-.31c-.238-.617-.487-1.66-.487-2.037v-.133h7.44V14m0 4.486v1.726l-.251-.024c-.761-.071-1.789-.38-2.615-.786-.875-.429-1.445-.833-2.167-1.537-.31-.303-.927-1.021-.927-1.079 0-.014 1.341-.026 2.98-.026h2.98v1.726" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Monochrome', 'accessibility-onetap' ); ?></span>
									</div>
								</button>									
							</div>
						</div>
					</div>						

					<!-- Features Orientation Modules-->
					<div class="onetap-features-container onetap-feature-orientation-modules">
						<div class="onetap-features">
							<div class="onetap-box-title">
								<span class="onetap-title"><?php esc_html_e( 'Orientation Modules', 'accessibility-onetap' ); ?></span>
							</div>

							<div class="onetap-box-features">

								<!-- Feature Reading Line -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-reading-line">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5.74 4.266a3.841 3.841 0 0 0-2.334 1.031c-.526.494-.95 1.287-1.093 2.045-.037.194-.053.671-.053 1.578 0 1.29.001 1.301.093 1.449.357.574 1.223.443 1.363-.207.026-.123.044-.667.044-1.356 0-1.271.021-1.425.25-1.863.165-.314.619-.768.933-.933.507-.266.065-.25 7.057-.25 6.994 0 6.554-.016 7.054.25.466.249.868.708 1.073 1.224.085.214.091.298.111 1.606.022 1.356.024 1.383.115 1.529a.74.74 0 0 0 1.368-.235c.071-.342.029-2.536-.056-2.909-.334-1.469-1.393-2.529-2.89-2.894-.251-.061-.828-.068-6.575-.073a830.09 830.09 0 0 0-6.46.008m-3.925 8.012c-.484.115-.717.726-.432 1.13.193.273.35.328.98.346.71.019.953-.03 1.156-.233.399-.399.212-1.098-.33-1.235-.201-.05-1.173-.056-1.374-.008m4.796.001a.858.858 0 0 0-.478.373c-.093.18-.087.542.012.712.043.074.156.189.25.255.167.118.182.12.741.135.74.019.978-.028 1.183-.233.41-.41.206-1.116-.357-1.237-.23-.049-1.151-.053-1.351-.005m4.615.005c-.338.08-.546.352-.546.716 0 .373.206.635.564.717.228.053 1.284.053 1.512 0 .358-.082.564-.344.564-.717s-.206-.635-.564-.717c-.215-.05-1.317-.049-1.53.001m4.781.001c-.533.126-.722.84-.326 1.236.205.205.444.252 1.179.233.535-.014.576-.021.729-.122a.699.699 0 0 0 .344-.632.7.7 0 0 0-.345-.633c-.157-.104-.182-.107-.785-.115-.343-.004-.701.011-.796.033m4.647-.007c-.645.154-.786 1.02-.22 1.353.178.104.213.11.83.123.819.018 1.046-.024 1.255-.233.399-.399.212-1.098-.33-1.235-.202-.05-1.331-.056-1.535-.008M2.815 15.277a.8.8 0 0 0-.462.354c-.089.143-.093.181-.092.949.002 1.092.093 1.531.458 2.208a3.736 3.736 0 0 0 2.623 1.899c.409.078 12.907.078 13.316 0a3.768 3.768 0 0 0 3.004-2.912c.084-.388.122-1.61.06-1.909a.74.74 0 0 0-1.369-.235c-.087.14-.094.201-.116 1.029-.021.777-.034.906-.112 1.106a2.426 2.426 0 0 1-1.071 1.224c-.5.266-.06.25-7.054.25-6.992 0-6.55.016-7.057-.25-.314-.165-.768-.619-.933-.933-.206-.394-.25-.633-.251-1.375-.001-.731-.037-.959-.179-1.146-.159-.209-.502-.325-.765-.259" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Reading Line', 'accessibility-onetap' ); ?></span>
									</div>
								</button>

								<!-- Feature Reading Mask -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-reading-mask">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" data-name="Layer 3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3.699 3.816c-.149.065-.367.308-.408.455-.017.06-.031.667-.031 1.349.001 1.086.01 1.27.074 1.48A2.326 2.326 0 0 0 4.9 8.666c.229.071.554.074 7.1.074 6.546 0 6.871-.003 7.1-.074A2.326 2.326 0 0 0 20.666 7.1c.064-.21.073-.394.074-1.48 0-.682-.014-1.289-.031-1.349-.042-.152-.262-.392-.417-.457a.742.742 0 0 0-.786.139c-.243.23-.244.236-.266 1.593l-.02 1.247-.121.149a1.064 1.064 0 0 1-.259.224c-.134.071-.389.074-6.84.074s-6.706-.003-6.84-.074a1.064 1.064 0 0 1-.259-.224l-.121-.149-.02-1.247c-.022-1.357-.023-1.363-.266-1.593a.756.756 0 0 0-.795-.137m1.116 7.462c-.484.115-.717.726-.432 1.13a.939.939 0 0 0 .277.248l.16.084 7.06.011c5.04.007 7.121-.002 7.274-.034.748-.155.775-1.244.035-1.431-.211-.053-14.154-.061-14.374-.008m.365 4.003c-.852.114-1.557.722-1.831 1.579-.084.265-.089.347-.089 1.52 0 .682.014 1.289.031 1.349.042.152.262.392.417.457a.742.742 0 0 0 .786-.139c.243-.23.244-.236.266-1.593l.02-1.247.121-.149c.067-.082.183-.183.259-.224.134-.071.389-.074 6.84-.074s6.706.003 6.84.074c.076.041.192.142.259.224l.121.149.02 1.247c.022 1.357.023 1.363.266 1.593.205.194.521.25.786.139.155-.065.375-.305.417-.457.017-.06.031-.667.031-1.349-.001-1.086-.01-1.27-.074-1.48-.228-.75-.782-1.31-1.546-1.566-.21-.07-.532-.074-6.96-.079-3.707-.003-6.848.009-6.98.026" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Reading Mask', 'accessibility-onetap' ); ?></span>
									</div>
								</button>

								<!-- Feature Hide Images -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-hide-images">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" data-name="Layer 3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10.815 2.277c-.486.121-.717.727-.432 1.131a.939.939 0 0 0 .277.248c.158.083.209.084 3.58.104l3.42.02.32.11c.538.184.878.399 1.304.826.427.426.642.766.826 1.304l.11.32.012 2.413.012 2.413-.252-.203c-.593-.481-1.196-.689-1.992-.689-.757 0-1.265.161-1.86.588-.132.095-1.112 1.046-2.179 2.114l-1.939 1.942-.441-.432c-.531-.521-.785-.715-1.181-.903a3.377 3.377 0 0 0-2.12-.243 4.121 4.121 0 0 0-1.147.502c-.106.071-.908.842-1.783 1.713l-1.59 1.583v-3.047c0-2.074-.014-3.113-.044-3.253-.141-.656-1.003-.787-1.363-.207l-.093.149v3.36c0 3.09.006 3.389.073 3.72.397 1.966 1.841 3.41 3.807 3.807.338.068.701.073 5.86.073 5.159 0 5.522-.005 5.86-.073 1.966-.397 3.41-1.841 3.807-3.807.068-.338.073-.701.073-5.86 0-5.159-.005-5.522-.073-5.86-.39-1.929-1.785-3.356-3.703-3.787-.374-.084-.467-.087-3.704-.097-1.826-.006-3.376.004-3.445.021M4.18 3.835a.61.61 0 0 0-.358.375.742.742 0 0 0 0 .581c.036.089.274.363.589.679l.528.53-.528.53c-.546.549-.652.707-.65.979.001.173.112.439.225.539a.918.918 0 0 0 .514.192c.263 0 .426-.109.97-.651L6 7.061l.53.528c.549.546.707.652.979.65.173-.001.439-.112.539-.225A.918.918 0 0 0 8.24 7.5c0-.263-.109-.426-.651-.97L7.061 6l.528-.53c.542-.544.651-.707.651-.97a.918.918 0 0 0-.192-.514c-.1-.113-.366-.224-.539-.225-.272-.002-.43.104-.979.65L6 4.939l-.53-.528c-.316-.315-.59-.553-.679-.589a.756.756 0 0 0-.611.013m14.515 8.075c.231.11.378.232.912.76l.637.628-.013 2.181-.012 2.181-.109.32c-.184.537-.399.878-.826 1.304-.534.535-1.13.846-1.787.934l-.203.028-2.11-2.13-2.11-2.13 1.913-1.914c1.052-1.053 1.979-1.96 2.06-2.016a2.49 2.49 0 0 1 .38-.2c.201-.084.285-.096.613-.087.333.01.414.027.655.141m-9.198 2.911c.108.032.279.107.38.165.179.104 1.16 1.071 3.943 3.885l1.356 1.371-4.418-.011-4.418-.011-.32-.11c-.552-.189-.877-.397-1.33-.852-.225-.227-.41-.435-.41-.464 0-.029.832-.884 1.85-1.899 1.86-1.856 1.965-1.949 2.368-2.076.225-.071.762-.07.999.002" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Hide Images', 'accessibility-onetap' ); ?></span>
									</div>
								</button>									

								<!-- Feature Highlight All -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-highlight-all">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3.84 2.265a1.794 1.794 0 0 0-1.514 1.23c-.056.173-.066.402-.066 1.485 0 1.412.013 1.515.243 1.9.223.372.597.673.972.78.102.03.316.066.475.081l.29.027v8.464l-.29.027c-.522.05-.833.182-1.163.496a1.996 1.996 0 0 0-.471.785c-.08.287-.08 2.633 0 2.92.154.55.624 1.034 1.179 1.214.173.056.402.066 1.485.066 1.412 0 1.515-.013 1.9-.243.372-.223.673-.597.78-.972.03-.102.066-.315.081-.475l.027-.29h8.462l.027.29c.015.159.052.373.082.475.11.377.409.75.781.972.385.23.488.243 1.9.243 1.083 0 1.312-.01 1.485-.066a1.852 1.852 0 0 0 1.179-1.214c.08-.287.08-2.633 0-2.92a1.996 1.996 0 0 0-.471-.785c-.33-.314-.641-.446-1.163-.496l-.29-.027V7.768l.29-.027c.16-.015.373-.051.475-.081.375-.107.749-.408.972-.78.23-.385.243-.488.243-1.9 0-1.083-.01-1.312-.066-1.485a1.852 1.852 0 0 0-1.214-1.179c-.287-.08-2.633-.08-2.92 0a1.996 1.996 0 0 0-.785.471c-.313.329-.448.645-.498 1.163l-.027.29H7.768l-.027-.29c-.037-.394-.109-.625-.273-.877a1.745 1.745 0 0 0-.582-.571c-.349-.217-.451-.231-1.726-.243a29.52 29.52 0 0 0-1.32.006m2.327 1.561c.067.061.073.152.073 1.167 0 .968-.008 1.11-.066 1.174-.061.067-.152.073-1.167.073-.968 0-1.11-.008-1.174-.066-.067-.061-.073-.152-.073-1.167 0-.968.008-1.11.066-1.174.061-.067.152-.073 1.167-.073.968 0 1.11.008 1.174.066m14 0c.067.061.073.152.073 1.167 0 .968-.008 1.11-.066 1.174-.061.067-.152.073-1.167.073-.968 0-1.11-.008-1.174-.066-.067-.061-.073-.152-.073-1.167 0-.968.008-1.11.066-1.174.061-.067.152-.073 1.167-.073.968 0 1.11.008 1.174.066m-3.91 2.224c.037.391.11.623.275.877.33.509.752.751 1.418.814l.29.027v8.464l-.29.027c-.394.037-.625.109-.877.273-.508.329-.753.755-.816 1.418l-.027.29H7.768l-.027-.29c-.063-.666-.305-1.088-.814-1.418-.252-.164-.483-.236-.877-.273l-.29-.027V7.768l.29-.027c.666-.063 1.088-.305 1.418-.814.164-.252.236-.483.273-.877l.027-.29h8.462l.027.29M6.167 17.826c.067.061.073.152.073 1.167 0 .968-.008 1.11-.066 1.174-.061.067-.152.073-1.167.073-.968 0-1.11-.008-1.174-.066-.067-.061-.073-.152-.073-1.167 0-.968.008-1.11.066-1.174.061-.067.152-.073 1.167-.073.968 0 1.11.008 1.174.066m14 0c.067.061.073.152.073 1.167 0 .968-.008 1.11-.066 1.174-.061.067-.152.073-1.167.073-.968 0-1.11-.008-1.174-.066-.067-.061-.073-.152-.073-1.167 0-.968.008-1.11.066-1.174.061-.067.152-.073 1.167-.073.968 0 1.11.008 1.174.066" fill-rule="evenodd"/></svg>
										</span>
									</div>

									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Highlight Content', 'accessibility-onetap' ); ?></span>
									</div>
								</button>	

								<!-- Stop Animations -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-stop-animations">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M8.98 2.266c-.7.065-1.263.26-1.86.646-.242.157-3.724 3.595-4.041 3.991a4.019 4.019 0 0 0-.766 1.679c-.077.403-.077 6.433 0 6.836.111.588.401 1.224.766 1.679.317.396 3.799 3.834 4.041 3.991.469.303.962.505 1.464.6.4.075 6.432.075 6.832 0a4.107 4.107 0 0 0 1.464-.6c.242-.157 3.724-3.595 4.041-3.991a4.019 4.019 0 0 0 .766-1.679c.076-.402.077-6.433.001-6.834a3.993 3.993 0 0 0-.619-1.484c-.175-.262-3.567-3.696-3.972-4.021a4.091 4.091 0 0 0-1.562-.747c-.241-.058-.652-.067-3.335-.073a180.917 180.917 0 0 0-3.22.007m6.358 1.555c.545.142.584.175 2.625 2.216 1.58 1.581 1.924 1.944 2.026 2.143.256.496.251.418.251 3.82 0 3.402.005 3.324-.251 3.82-.102.199-.446.562-2.026 2.143-2.046 2.046-2.076 2.071-2.629 2.214-.363.093-6.313.095-6.672.002-.545-.142-.584-.175-2.625-2.216-2.041-2.041-2.074-2.08-2.216-2.625-.092-.352-.092-6.324 0-6.676.142-.545.175-.584 2.216-2.625 2.029-2.029 2.08-2.072 2.607-2.214.332-.089 6.353-.091 6.694-.002m.562 3.438a1.795 1.795 0 0 1-.16.04c-.091.02-1.119 1.024-4.212 4.113-2.25 2.249-4.13 4.149-4.177 4.224-.119.19-.117.541.005.738.176.284.484.409.833.338.186-.039.304-.152 4.301-4.145 2.349-2.347 4.138-4.164 4.175-4.242a.765.765 0 0 0-.249-.932c-.142-.098-.417-.169-.516-.134" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Stop Animations', 'accessibility-onetap' ); ?></span>
									</div>
								</button>								

								<!-- Feature Highlight Links -->
								<button type="button" role="button" aria-pressed="false" class="onetap-box-feature onetap-highlight-links">
									<div class="onetap-icon">
										<span class="onetap-icon-animation">
											<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6.452 6.821c-1.416.259-2.595 1.015-3.339 2.14-.581.879-.837 1.726-.839 2.779-.002.844.139 1.459.505 2.205.721 1.468 2.074 2.466 3.718 2.744.184.032.812.049 1.803.05 1.341.001 1.534-.007 1.64-.065.242-.134.42-.419.42-.674a.886.886 0 0 0-.212-.513c-.212-.227-.197-.225-1.948-.249l-1.62-.022-.38-.128c-1.121-.377-1.923-1.179-2.284-2.288a3.75 3.75 0 0 1-.099-1.721c.14-.697.451-1.267.983-1.799.427-.427.794-.659 1.331-.843.494-.168.829-.197 2.299-.197 1.289 0 1.352-.004 1.52-.085.26-.126.39-.344.39-.655 0-.311-.13-.529-.39-.655-.17-.082-.223-.085-1.693-.081-1.064.003-1.603.02-1.805.057m7.595.025c-.258.127-.387.346-.387.654 0 .311.13.529.39.655.168.081.231.085 1.52.085 1.47 0 1.805.029 2.299.197.537.184.904.416 1.331.843.532.532.843 1.102.983 1.799a3.75 3.75 0 0 1-.099 1.721c-.361 1.109-1.163 1.911-2.284 2.288l-.38.128-1.62.022c-1.751.024-1.736.022-1.948.249a.886.886 0 0 0-.212.513c0 .255.178.54.42.674.106.058.299.066 1.64.065.991-.001 1.619-.018 1.803-.05.767-.129 1.614-.484 2.202-.921a4.935 4.935 0 0 0 2.021-4.026c-.003-1.057-.258-1.902-.839-2.781-.621-.939-1.674-1.709-2.738-2.001-.657-.18-.896-.2-2.449-.2-1.433 0-1.486.003-1.653.086m-6.232 4.432c-.484.115-.717.726-.432 1.13a.939.939 0 0 0 .277.248c.159.083.191.084 4.219.095 2.865.008 4.122-.002 4.274-.034.749-.155.777-1.244.036-1.431-.21-.052-8.155-.06-8.374-.008" fill-rule="evenodd"/></svg>
										</span>
									</div>
									<div class="onetap-title">
										<span class="onetap-heading"><?php esc_html_e( 'Highlight Links', 'accessibility-onetap' ); ?></span>
									</div>
								</button>								

							</div>
						</div>
					</div>

					<!-- Features Skip to Content -->
					<div class="onetap-features-container onetap-skip-content">
						<div class="onetap-features">
							<div class="onetap-skip-content-control">
								<label for="skip-content-select" class="onetap-skip-label">
									<?php esc_html_e( 'Skip To Content', 'accessibility-onetap' ); ?>
								</label>
								<select id="skip-content-select" class="onetap-skip-select">
									<option value=""><?php esc_html_e( 'Choose...', 'accessibility-onetap' ); ?></option>
									<option value="main"><?php esc_html_e( 'Main Content', 'accessibility-onetap' ); ?></option>
									<option value="navigation"><?php esc_html_e( 'Navigation', 'accessibility-onetap' ); ?></option>
									<option value="footer"><?php esc_html_e( 'Footer', 'accessibility-onetap' ); ?></option>
								</select>
							</div>
						</div>
					</div>						

					<!-- Reset settings -->
					<div class="onetap-reset-settings">
						<button type="button" aria-label="Reset all settings">
							<?php esc_html_e( 'Reset Settings', 'accessibility-onetap' ); ?>
						</button>
					</div>

					<!-- Footer bottom -->
					<footer class="onetap-footer-bottom">
						<!-- Accessibility -->
						<div class="onetap-accessibility-container">
							<ul class="onetap-icon-list-items">
								<li class="onetap-icon-list-item">
									<span class="onetap-icon-list-icon">
										<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128" fill="none">
											<path d="M116.627 101.688L99.2097 70.0115C104.87 61.406 107.22 51.0416 105.827 40.837C104.434 30.6312 99.3894 21.2763 91.6299 14.5043C83.869 7.73115 73.9186 4 63.6174 4C53.317 4 43.3665 7.73115 35.6049 14.5043C27.8451 21.2763 22.8004 30.631 21.4074 40.837C20.013 51.0419 22.3646 61.4063 28.025 70.0115L10.5892 101.688C10.1888 102.411 9.98603 103.226 10.0007 104.053C10.0155 104.879 10.2477 105.687 10.6732 106.395C11.0704 107.121 11.662 107.721 12.3828 108.125C13.1036 108.531 13.9242 108.725 14.7501 108.688L30.3124 108.57L38.5408 121.783C39.4003 123.162 40.9081 123.999 42.5326 124H42.664C44.3325 123.954 45.8509 123.028 46.6568 121.566L63.6074 90.7484L80.5537 121.548C81.3586 123.009 82.878 123.935 84.5455 123.981H84.6769C86.3013 123.98 87.81 123.143 88.6697 121.764L96.8981 108.551L112.46 108.669H112.459C114.113 108.636 115.643 107.784 116.542 106.395C116.967 105.687 117.199 104.879 117.214 104.053C117.229 103.226 117.026 102.411 116.626 101.688L116.627 101.688ZM63.609 13.4862C72.4111 13.4862 80.8517 16.983 87.0751 23.2066C93.2984 29.4302 96.7955 37.8719 96.7955 46.6727C96.7955 55.4748 93.2987 63.9154 87.0751 70.1398C80.8515 76.3634 72.4109 79.8592 63.609 79.8592C54.8072 79.8592 46.3663 76.3634 40.143 70.1398C33.9194 63.9151 30.4225 55.4745 30.4225 46.6727C30.432 37.8748 33.932 29.4396 40.1535 23.2171C46.3749 16.9957 54.8101 13.4967 63.609 13.4862V13.4862ZM42.2855 109.986L36.91 101.357H36.9089C36.0347 99.9766 34.5143 99.1402 32.8803 99.1402L22.7122 99.2159L34.5279 77.7366C40.0515 82.9313 46.8645 86.5532 54.2606 88.2293L42.2855 109.986ZM94.3402 99.1591H94.3391C92.7084 99.1717 91.1933 100.005 90.3105 101.376L84.9339 109.967L72.9586 88.2094V88.2105C80.3547 86.5346 87.1676 82.9128 92.6913 77.7178L104.507 99.1971L94.3402 99.1591ZM46.4656 64.707C46.3111 66.3619 47.0413 67.9758 48.3863 68.953C49.7312 69.9302 51.4912 70.1246 53.018 69.4658L63.6094 64.8991L74.2009 69.4658C75.7276 70.1246 77.4877 69.9302 78.8326 68.953C80.1776 67.9758 80.9078 66.3619 80.7534 64.707L79.6805 53.2024L87.2878 44.5452H87.2868C88.3848 43.298 88.7431 41.5643 88.2303 39.9829C87.7176 38.4026 86.4084 37.2099 84.7881 36.8443L73.5379 34.3163L67.6581 24.3836C66.746 23.0439 65.2309 22.2412 63.6095 22.2412C61.9881 22.2412 60.4731 23.0439 59.561 24.3836L53.6822 34.3026L42.432 36.8306H42.431C40.8107 37.1952 39.5014 38.3889 38.9887 39.9692C38.476 41.5495 38.8343 43.2831 39.9323 44.5315L47.5385 53.2021L46.4656 64.707ZM57.7252 43.0766V43.0776C58.9892 42.7939 60.0809 42.0016 60.7429 40.8879L63.6093 36.0114L66.4756 40.8499C67.1376 41.9637 68.2293 42.756 69.4934 43.0397L74.9824 44.2732L71.2679 48.5098V48.5088C70.4126 49.4817 69.9944 50.7636 70.1142 52.054L70.6364 57.6514L65.4584 55.4249H65.4595C64.269 54.9111 62.9209 54.9111 61.7305 55.4249L56.5524 57.6514L57.0746 52.054H57.0757C57.1955 50.7637 56.7783 49.4818 55.922 48.5088L52.2075 44.2722L57.7252 43.0766Z" fill="currentColor"></path>
										</svg>
									</span>
									<span class="onetap-icon-list-text"></span>
								</li>
							</ul>
						</div>
					</footer>
				</div>
			</section>
		</nav>
		<div class="onetap-markup-reading-line"></div>
		<div class="onetap-markup-reading-mask onetap-top"></div>
		<div class="onetap-markup-reading-mask onetap-bottom"></div>
		<div class="onetap-markup-text-magnifier" style="display: none;"></div>
		<?php
	}

	/**
	 * Register shortcodes for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'onetap_free_accessibility', array( $this, 'onetap_accessibility_shortcode' ) );
	}

	/**
	 * Register shortcode for accessibility toolbar.
	 *
	 * This shortcode allows users to display the accessibility toolbar
	 * anywhere on their website using [onetap_accessibility] shortcode.
	 *
	 * @since    1.0.0
	 * @param    array $atts Shortcode attributes.
	 * @return   string HTML output of the accessibility toolbar.
	 */
	public function onetap_accessibility_shortcode( $atts ) {
		// Parse shortcode attributes with defaults.
		$atts = shortcode_atts(
			array(
				'show_toggle' => 'true',  // Show/hide the toggle button.
				'position'    => 'inline', // Position: inline, fixed.
			),
			$atts,
			'onetap_accessibility'
		);

		// Start output buffering to capture the HTML.
		ob_start();

		?>
		<div class="onetap-shortcode-container" data-position="<?php echo esc_attr( $atts['position'] ); ?>">
			<?php
			// Render the complete accessibility template directly.
			$this->render_accessibility_template();
			?>
		</div>
		<?php

		// Return the buffered content.
		return ob_get_clean();
	}

	/**
	 * Exclude plugin CSS from WP Rocket optimization.
	 *
	 * @since    1.0.0
	 * @param    array $excluded_files Array of excluded CSS files.
	 * @return   array Modified array with plugin CSS excluded.
	 */
	public function exclude_css_from_wp_rocket( $excluded_files ) {
		// Ensure $excluded_files is an array.
		if ( ! is_array( $excluded_files ) ) {
			$excluded_files = array();
		}

		// Get the CSS file URL.
		$css_url = plugins_url( $this->plugin_name ) . '/assets/css/accessibility-onetap-front-end.min.css';

		// Get relative path from site URL.
		$css_path = str_replace( home_url(), '', $css_url );

		// Add multiple formats for better compatibility with WP Rocket.
		$excluded_files[] = $css_url; // Full URL.
		$excluded_files[] = $css_path; // Relative path.
		$excluded_files[] = '/wp-content/plugins/' . $this->plugin_name . '/assets/css/accessibility-onetap-front-end.min.css'; // Absolute path.

		// Remove duplicates.
		$excluded_files = array_unique( $excluded_files );

		return $excluded_files;
	}

	/**
	 * Exclude plugin JS from WP Rocket optimization.
	 *
	 * @since    1.0.0
	 * @param    array $excluded_files Array of excluded JS files.
	 * @return   array Modified array with plugin JS excluded.
	 */
	public function exclude_js_from_wp_rocket( $excluded_files ) {
		// Ensure $excluded_files is an array.
		if ( ! is_array( $excluded_files ) ) {
			$excluded_files = array();
		}

		// Get the JS file URL.
		$js_url = plugins_url( $this->plugin_name ) . '/assets/js/script.min.js';

		// Get relative path from site URL.
		$js_path = str_replace( home_url(), '', $js_url );

		// Add multiple formats for better compatibility with WP Rocket.
		$excluded_files[] = $js_url; // Full URL.
		$excluded_files[] = $js_path; // Relative path.
		$excluded_files[] = '/wp-content/plugins/' . $this->plugin_name . '/assets/js/script.min.js'; // Absolute path.

		// Remove duplicates.
		$excluded_files = array_unique( $excluded_files );

		return $excluded_files;
	}

	/**
	 * Exclude plugin JS from WP Rocket defer optimization.
	 * This is important because the script uses defer strategy.
	 *
	 * @since    1.0.0
	 * @param    array $excluded_files Array of excluded JS files from defer.
	 * @return   array Modified array with plugin JS excluded from defer.
	 */
	public function exclude_js_from_wp_rocket_defer( $excluded_files ) {
		// Ensure $excluded_files is an array.
		if ( ! is_array( $excluded_files ) ) {
			$excluded_files = array();
		}

		// Get the JS file URL.
		$js_url = plugins_url( $this->plugin_name ) . '/assets/js/script.min.js';

		// Get relative path from site URL.
		$js_path = str_replace( home_url(), '', $js_url );

		// Add multiple formats for better compatibility with WP Rocket.
		$excluded_files[] = $js_url; // Full URL.
		$excluded_files[] = $js_path; // Relative path.
		$excluded_files[] = '/wp-content/plugins/' . $this->plugin_name . '/assets/js/script.min.js'; // Absolute path.

		// Remove duplicates.
		$excluded_files = array_unique( $excluded_files );

		return $excluded_files;
	}
}
