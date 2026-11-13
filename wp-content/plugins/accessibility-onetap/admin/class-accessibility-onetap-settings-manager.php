<?php
/**
 * Accessibility Settings Manager for Onetap.
 *
 * @link       https://wponetap.com
 * @since      1.0.0
 *
 * @package    Accessibility_Onetap_Settings_Manager
 * @subpackage Accessibility_Onetap_Settings_Manager/admin
 */

/**
 * Accessibility Settings Manager for Onetap.
 *
 * Handles the settings related to accessibility in the Onetap Pro plugin.
 *
 * @package    Accessibility_Onetap_Settings_Manager
 * @subpackage Accessibility_Onetap_Settings_Manager/admin
 * @author     OneTap <support@wponetap.com>
 */
class Accessibility_Onetap_Settings_Manager {

	/**
	 * Destination the "PRO" badges link to.
	 *
	 * The pricing page, which is where every other upgrade call to action on
	 * these screens already goes. A badge marks a feature the visitor cannot
	 * reach, so the plan that unlocks it is the answer to the click; the
	 * marketing home page is a detour.
	 *
	 * @var string
	 */
	const PRO_BADGE_URL = 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar';

	/**
	 * Slug AltPilot carries on the WordPress.org plugin repository.
	 *
	 * @var string
	 */
	const ALTPILOT_SLUG = 'altpilot';

	/**
	 * Marketing site used whenever AltPilot cannot be installed from here.
	 *
	 * @var string
	 */
	const ALTPILOT_SITE_URL = 'https://www.altpilot.ai/';

	/**
	 * Settings sections array.
	 *
	 * @var array
	 */
	protected $settings_sections = array();

	/**
	 * Settings fields array.
	 *
	 * @var array
	 */
	protected $settings_fields = array();

	/**
	 * Initialize __construct.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts and styles for the admin area.
	 *
	 * This function ensures that necessary scripts and styles are loaded on
	 * specific admin pages of the plugin. It loads the WordPress color picker,
	 * media uploader, jQuery, and a custom JavaScript file for managing settings.
	 *
	 * @param string $hook The current admin page hook suffix.
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Enqueue the WordPress color picker style.
		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue the media uploader script.
		wp_enqueue_media();

		// Enqueue the WordPress color picker script.
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue the jQuery library.
		wp_enqueue_script( 'jquery' );

		// Conditionally enqueue the settings manager script for specific plugin admin pages.
		if ( 'toplevel_page_accessibility-onetap-settings' === $hook ||
			'onetap_page_accessibility-onetap-general-settings' === $hook ||
			'onetap_page_accessibility-onetap-alt-text' === $hook ||
			'onetap_page_accessibility-onetap-modules' === $hook ||
			'admin_page_onetap-module-labels' === $hook ||
			'onetap_page_accessibility-onetap-accessibility-status' === $hook
		) {
			wp_enqueue_script( 'accessibility-onetap-settings-manager', ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/js/settings-manager.min.js', array( 'jquery' ), ACCESSIBILITY_ONETAP_VERSION, true );
		}
	}

	/**
	 * Set settings sections.
	 *
	 * @param array $sections setting sections array.
	 */
	public function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	/**
	 * Add a single section.
	 *
	 * @param array $section The section details.
	 */
	public function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set settings fields.
	 *
	 * @param array $fields settings fields array.
	 */
	public function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	/**
	 * Add settings fields.
	 *
	 * @param array $section settings section array.
	 * @param array $field settings fields array.
	 */
	public function add_field( $section, $field ) {
		$defaults = array(
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text',
		);

		$arg                                 = wp_parse_args( $field, $defaults );
		$this->settings_fields[ $section ][] = $arg;

		return $this;
	}

	/**
	 * Initialize and registers the settings sections and fileds to WordPress.
	 *
	 * Usually this should be called at `admin_init` hook.
	 *
	 * This function gets the initiated settings sections and fields. Then
	 * registers them to WordPress and ready for use.
	 */
	public function admin_init() {
		// register settings sections.
		foreach ( $this->settings_sections as $section ) {
			if ( false === get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}

			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				$callback        = function () use ( $section ) {
					echo esc_html( str_replace( '"', '\"', $section['desc'] ) );
				};
			} elseif ( isset( $section['callback'] ) ) {
				$callback = $section['callback'];
			} else {
				$callback = null;
			}

			add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
		}

		// register settings fields.
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {

				$anchor                   = isset( $option['anchor'] ) ? $option['anchor'] : '';
				$color_list               = isset( $option['color_list'] ) ? $option['color_list'] : '';
				$device_visibility        = isset( $option['device_visibility'] ) ? $option['device_visibility'] : '';
				$feature_desc             = isset( $option['feature_desc'] ) ? $option['feature_desc'] : '';
				$feature_name             = isset( $option['feature_name'] ) ? $option['feature_name'] : '';
				$first_control            = isset( $option['first_control'] ) ? $option['first_control'] : '';
				$is_pro                   = isset( $option['is_pro'] ) ? $option['is_pro'] : '';
				$is_beta                  = ! empty( $option['is_beta'] );
				$label                    = isset( $option['label'] ) ? $option['label'] : '';
				$last_control             = isset( $option['last_control'] ) ? $option['last_control'] : '';
				$name                     = $option['name'];
				$radio_image_list         = isset( $option['radio_image_list'] ) ? $option['radio_image_list'] : '';
				$radio_text_list          = isset( $option['radio_text_list'] ) ? $option['radio_text_list'] : '';
				$select_badge             = isset( $option['select_badge'] ) ? $option['select_badge'] : '';
				$select_options           = isset( $option['select_options'] ) ? $option['select_options'] : '';
				$setting_description      = isset( $option['setting_description'] ) ? $option['setting_description'] : '';
				$setting_title            = isset( $option['setting_title'] ) ? $option['setting_title'] : '';
				$show_save_button         = isset( $option['show_save_button'] ) ? $option['show_save_button'] : '';
				$status                   = isset( $option['status'] ) ? $option['status'] : '';
				$switch_icon              = isset( $option['switch_icon'] ) ? $option['switch_icon'] : '';
				$switch_style             = isset( $option['switch_style'] ) ? $option['switch_style'] : '';
				$module_icon              = isset( $option['module_icon'] ) ? $option['module_icon'] : '';
				$module_style             = isset( $option['module_style'] ) ? $option['module_style'] : '';
				$copyable_icon            = isset( $option['copyable_icon'] ) ? $option['copyable_icon'] : '';
				$copyable_style           = isset( $option['copyable_style'] ) ? $option['copyable_style'] : '';
				$copy_text                = isset( $option['copy_text'] ) ? $option['copy_text'] : '';
				$button_text              = isset( $option['button_text'] ) ? $option['button_text'] : '';
				$button_icon              = isset( $option['button_icon'] ) ? $option['button_icon'] : '';
				$button_class             = isset( $option['button_class'] ) ? $option['button_class'] : '';
				$button_link              = isset( $option['button_link'] ) ? $option['button_link'] : '';
				$hide_setting_description = isset( $option['hide_setting_description'] ) ? (bool) $option['hide_setting_description'] : false;
				$type                     = isset( $option['type'] ) ? $option['type'] : 'text';
				$callback                 = isset( $option['callback'] ) ? array( $this, $option['callback'] ) : array( $this, 'callback_' . $type );

				$args = array(
					'anchor'                   => $anchor,
					'class'                    => isset( $option['class'] ) ? $option['class'] : $name,
					'color_list'               => $color_list,
					'device_visibility'        => $device_visibility,
					'feature_desc'             => $feature_desc,
					'feature_name'             => $feature_name,
					'first_control'            => $first_control,
					'id'                       => $name,
					'is_pro'                   => $is_pro,
					'is_beta'                  => $is_beta,
					'label'                    => $label,
					'label_for'                => "{$section}[{$name}]",
					'last_control'             => $last_control,
					'max'                      => isset( $option['max'] ) ? $option['max'] : '',
					'min'                      => isset( $option['min'] ) ? $option['min'] : '',
					'name'                     => $label,
					'placeholder'              => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
					'radio_image_list'         => $radio_image_list,
					'radio_text_list'          => $radio_text_list,
					'sanitize_callback'        => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'section'                  => $section,
					'select_badge'             => $select_badge,
					'select_options'           => $select_options,
					'search_placeholder'       => isset( $option['search_placeholder'] ) ? $option['search_placeholder'] : '',
					'empty_text'               => isset( $option['empty_text'] ) ? $option['empty_text'] : '',
					'setting_description'      => $setting_description,
					'setting_title'            => $setting_title,
					'show_save_button'         => $show_save_button,
					'status'                   => $status,
					'step'                     => isset( $option['step'] ) ? $option['step'] : '',
					'std'                      => isset( $option['default'] ) ? $option['default'] : '',
					'switch_icon'              => $switch_icon,
					'switch_style'             => $switch_style,
					'module_icon'              => $module_icon,
					'module_style'             => $module_style,
					'copyable_icon'            => $copyable_icon,
					'copyable_style'           => $copyable_style,
					'copy_text'                => $copy_text,
					'button_text'              => $button_text,
					'button_icon'              => $button_icon,
					'button_class'             => $button_class,
					'button_link'              => $button_link,
					'hide_setting_description' => $hide_setting_description,
					'type'                     => $type,
					'units'                    => isset( $option['units'] ) ? $option['units'] : '',
				);

				add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
			}
		}

		// creates our settings in the options table.
		foreach ( $this->settings_sections as $section ) {
			register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
		}
	}

	/**
	 * Build the "PRO" badge that marks a locked feature.
	 *
	 * The badge doubles as an upgrade link. "PRO" on its own is not a
	 * meaningful link name out of context, so the anchor carries an explicit
	 * aria-label for screen readers while the visible text stays short.
	 *
	 * @return string Badge HTML.
	 */
	protected function get_pro_badge() {
		return '<a class="pro" href="' . esc_url( self::PRO_BADGE_URL ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr__( 'Upgrade to OneTap PRO', 'accessibility-onetap' ) . '">' . esc_html__( 'PRO', 'accessibility-onetap' ) . '</a>';
	}

	/**
	 * Get common allowed HTML tags for wp_kses.
	 *
	 * @return array Allowed HTML tags array.
	 */
	protected function get_allowed_html() {
		return array(
			'h3'       => array(
				'class' => array(),
			),
			'h4'       => array(
				'class' => array(),
			),
			'p'        => array(
				'class' => array(),
			),
			'a'        => array(
				'class'         => array(),
				'href'          => array(),
				'target'        => array(),
				'rel'           => array(),
				'title'         => array(),
				'data-image-id' => array(),
				'aria-current'  => array(),
				'aria-label'    => array(),
			),
			'div'      => array(
				'class'              => array(),
				'style'              => array(),
				'data-device'        => array(),
				'data-default-color' => array(),
				'data-name'          => array(),
				'data-placeholder'   => array(),
				'role'               => array(),
				'aria-label'         => array(),
				'aria-hidden'        => array(),
			),
			'ul'       => array(
				'class'                => array(),
				'id'                   => array(),
				'role'                 => array(),
				'aria-multiselectable' => array(),
				'aria-label'           => array(),
			),
			'li'       => array(
				'class'         => array(),
				'data-color'    => array(),
				'data-value'    => array(),
				'data-label'    => array(),
				'data-lang'     => array(),
				'style'         => array(),
				'role'          => array(),
				'aria-selected' => array(),
			),
			'img'      => array(
				'class' => array(),
				'src'   => array(),
				'alt'   => array(),
			),
			'span'     => array(
				'id'             => array(),
				'class'          => array(),
				'license-status' => array(),
				'data-value'     => array(),
				'aria-hidden'    => array(),
				'aria-label'     => array(),
				'role'           => array(),
			),
			'label'    => array(
				'class' => array(),
				'id'    => array(),
			),
			'input'    => array(
				'type'           => array(),
				'class'          => array(),
				'id'             => array(),
				'name'           => array(),
				'value'          => array(),
				'style'          => array(),
				'placeholder'    => array(),
				'checked'        => array(),
				'min'            => array(),
				'max'            => array(),
				'step'           => array(),
				'license-status' => array(),
				'license-key'    => array(),
				'readonly'       => array(),
				'data-lang'      => array(),
				'data-default'   => array(),
				'data-name'      => array(),
				'autocomplete'   => array(),
				'aria-label'     => array(),
			),
			'textarea' => array(
				'rows'          => array(),
				'cols'          => array(),
				'class'         => array(),
				'id'            => array(),
				'name'          => array(),
				'value'         => array(),
				'placeholder'   => array(),
				'data-image-id' => array(),
			),
			'select'   => array(
				'class' => array(),
				'name'  => array(),
				'id'    => array(),
			),
			'option'   => array(
				'value'    => array(),
				'selected' => array(),
			),
			'button'   => array(
				'type'             => array(),
				'class'            => array(),
				'style'            => array(),
				'title'            => array(),
				'data-image-id'    => array(),
				'aria-pressed'     => array(),
				'aria-expanded'    => array(),
				'aria-haspopup'    => array(),
				'disabled'         => array(),
				'aria-controls'    => array(),
				'aria-label'       => array(),
				'data-device-type' => array(),
				'data-lang'        => array(),
				'data-value'       => array(),
			),
			'svg'      => array(
				'class'   => array(),
				// KSES lower-cases attribute names before matching, so the key has
				// to be lower case too or viewBox gets stripped from every icon.
				// The HTML parser maps `viewbox` back to `viewBox` for SVG.
				'viewbox' => array(),
				'width'   => array(),
				'height'  => array(),
				'fill'    => array(),
				'xmlns'   => array(),
			),
			'g'        => array(
				'clip-path' => array(),
			),
			'clipPath' => array(
				'id'   => array(),
				'rect' => array(),
			),
			'rect'     => array(
				'width'     => array(),
				'height'    => array(),
				'fill'      => array(),
				'transform' => array(),
			),
			'defs'     => array(),
			'path'     => array(
				'd'               => array(),
				'fill'            => array(),
				'stroke'          => array(),
				'stroke-width'    => array(),
				'stroke-linecap'  => array(),
				'stroke-linejoin' => array(),
			),
			'hr'       => array(
				'class' => array(),
				'style' => array(),
			),
		);
	}

	/**
	 * Parse common arguments for template rendering.
	 *
	 * @param array $args Settings field arguments.
	 * @return array Parsed arguments.
	 */
	protected function parse_template_args( $args ) {
		// Multi-value controls (e.g. multiselect) store an array, which cannot be
		// run through esc_attr(). Those callbacks escape each entry themselves.
		$raw_value                = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$value                    = is_array( $raw_value ) ? $raw_value : esc_attr( $raw_value );
		$label                    = isset( $args['label'] ) ? $args['label'] : '';
		$setting_title            = isset( $args['setting_title'] ) ? $args['setting_title'] : '';
		$setting_description      = isset( $args['setting_description'] ) ? $args['setting_description'] : '';
		$first_control            = ! empty( $args['first_control'] ) ? ' first-control' : '';
		$last_control             = ! empty( $args['last_control'] ) ? ' last-control' : '';
		$show_save_button         = ! empty( $args['show_save_button'] );
		$hide_setting_description = isset( $args['hide_setting_description'] ) ? (bool) $args['hide_setting_description'] : false;
		$type                     = isset( $args['type'] ) ? $args['type'] : 'default';
		$classes                  = 'setting-control' . $first_control . ' ' . $last_control . ' ' . esc_attr( $type );

		return array(
			'value'                    => $value,
			'label'                    => $label,
			'setting_title'            => $setting_title,
			'setting_description'      => $setting_description,
			'first_control'            => $first_control,
			'last_control'             => $last_control,
			'show_save_button'         => $show_save_button,
			'hide_setting_description' => $hide_setting_description,
			'type'                     => $type,
			'classes'                  => $classes,
			'args'                     => $args,
		);
	}

	/**
	 * Build common HTML structure for settings templates.
	 *
	 * @param array  $parsed_args Parsed template arguments.
	 * @param string $content Inner content for the template.
	 * @return string Generated HTML.
	 */
	protected function build_template_structure( $parsed_args, $content = '' ) {
		// Build device visibility classes.
		$device_visibility_class = '';
		$active_class            = '';
		$device_visibility_attr  = '';
		$viewport_class          = '';
		$hide_description_class  = '';

		if ( isset( $parsed_args['args']['device_visibility'] ) && ! empty( $parsed_args['args']['device_visibility'] ) ) {
			$device_visibility_class = $parsed_args['args']['device_visibility'] . ' device';

			// Add data attribute for device when device_visibility is set.
			$device_visibility_attr = ' data-device="' . esc_attr( $parsed_args['args']['device_visibility'] ) . '"';

			// Add preview viewport classes similar to the preview markup.
			$viewport_class = ' preview-viewport viewport-' . esc_attr( $parsed_args['args']['device_visibility'] );

			// Add active class if device_visibility is desktop.
			if ( 'desktop' === $parsed_args['args']['device_visibility'] ) {
				$active_class = ' active';
			}
		}

		// Add hide description class if hide_setting_description is true.
		if ( $parsed_args['hide_setting_description'] ) {
			$hide_description_class = ' hide-setting-description';
		}

		$html  = '<div class="settings-group ' . esc_attr( $parsed_args['args']['id'] ) . ' ' . $device_visibility_class . $viewport_class . $active_class . $hide_description_class . '"' . $device_visibility_attr . '>';
		$html .= '<div class="' . $parsed_args['classes'] . '">';

		if ( $parsed_args['setting_title'] ) {
			if ( ! empty( $parsed_args['args']['setting_header_after_title'] ) ) {
				$html .= '<div class="setting-header-row setting-header-row--image-alt-list">';
				$html .= '<span class="setting-title">' . $parsed_args['setting_title'] . '</span>';
				$html .= $parsed_args['args']['setting_header_after_title'];
				$html .= '</div>';
			} else {
				$html .= '<span class="setting-title">' . $parsed_args['setting_title'] . '</span>';
			}
		}

		if ( $parsed_args['setting_description'] && ! $parsed_args['hide_setting_description'] ) {
			$html .= '<span class="setting-description">' . $parsed_args['setting_description'] . '</span>';
		}

		if ( $parsed_args['label'] ) {
			$html .= '<span class="control-heading">' . esc_html( $parsed_args['label'] ) . '</span>';
		}

		$html .= '<div class="boxes">';
		$html .= $content;
		$html .= '</div>'; // .boxes

		if ( $parsed_args['show_save_button'] ) {
			$html .= '<button class="save-changes">' . esc_html__( 'Save Changes', 'accessibility-onetap' ) . '</button>';
		}

		$html .= '</div>'; // .setting-control
		$html .= '</div>'; // .settings-group

		return $html;
	}

	/**
	 * Render template with common structure.
	 *
	 * @param array  $args Settings field arguments.
	 * @param string $content Inner content for the template.
	 * @param string $type Template type for default type fallback.
	 */
	protected function render_template( $args, $content = '', $type = 'default' ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = $type;
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $type );

		$html = $this->build_template_structure( $parsed_args, $content );
		echo wp_kses( $html, $this->get_allowed_html() );
	}

	/**
	 * Callback function for rendering divider template in settings.
	 *
	 * This method generates HTML for a visual divider element used to separate
	 * different sections or groups of settings. The divider provides visual
	 * separation and improves the overall layout and readability of the settings page.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_divider( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'divider';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build input HTML.
		$input_html  = '<div class="box box1">';
		$input_html .= '<div class="divider"></div>';
		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'divider' );
	}

	/**
	 * Callback function to render the save changes button template.
	 *
	 * This method creates a save changes button with proper styling and classes.
	 * It parses the provided arguments, sets default values, and renders the button
	 * using the template rendering system.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_save_changes( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'submit';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build input HTML.
		$input_html  = '<div class="box box1">';
		$input_html .= '<button class="save-changes">' . esc_html__( 'Save Changes', 'accessibility-onetap' ) . '</button>';
		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'submit' );
	}

	/**
	 * Callback function for rendering switch template in settings.
	 *
	 * This method generates HTML for a toggle switch control with customizable content
	 * including optional icon, PRO status badge, feature name, and description.
	 * The switch supports various styling options and can be configured for different
	 * feature types with conditional display of elements.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_switch( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'switch';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build switch content attributes.
		$switch_icon  = isset( $args['switch_icon'] ) ? esc_url( $args['switch_icon'] ) : '';
		$is_pro       = isset( $args['is_pro'] ) ? (bool) $args['is_pro'] : false;
		$is_beta      = ! empty( $args['is_beta'] );
		$feature_name = isset( $args['feature_name'] ) ? esc_html( $args['feature_name'] ) : '';
		$feature_desc = isset( $args['feature_desc'] ) ? esc_html( $args['feature_desc'] ) : '';
		$switch_style = isset( $args['switch_style'] ) ? esc_attr( $args['switch_style'] ) : '';

		// Build PRO feature information attributes.
		$pro_title     = isset( $args['pro_title'] ) ? esc_html( $args['pro_title'] ) : __( 'This is a PRO Feature', 'accessibility-onetap' );
		$pro_desc      = isset( $args['pro_desc'] ) ? esc_html( $args['pro_desc'] ) : __( ' to use this Feature.', 'accessibility-onetap' );
		$pro_link_text = isset( $args['pro_link_text'] ) ? esc_html( $args['pro_link_text'] ) : __( 'Upgrade to Pro', 'accessibility-onetap' );
		$upgrade_link  = isset( $args['upgrade_link'] ) ? esc_url( $args['upgrade_link'] ) : 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar';

		// Check if label exists and is not empty, then add padding-top-0 class (except for switch2 style).
		$label_class = ( isset( $args['label'] ) && ! empty( $args['label'] ) && 'switch2' !== $switch_style ) ? ' padding-top-0' : '';

		// Build input HTML.
		$input_html = '<div class="box box1' . ( ! empty( $switch_style ) ? ' ' . $switch_style : '' ) . ( $is_pro ? ' pro' : '' ) . $label_class . '">';

		$input_html .= '<div class="left">';

		// Icon section - only show if switch_icon is provided.
		if ( ! empty( $switch_icon ) ) {
			$input_html .= '<div class="icon">';
			$input_html .= '<img src="' . $switch_icon . '" alt="icon">';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="text">';

		// Display feature name - only show if feature_name exists (regardless of is_pro status).
		if ( ! empty( $feature_name ) ) {
			$input_html .= '<span class="feature-name">' . $feature_name;
			if ( $is_pro ) {
				$input_html .= $this->get_pro_badge();
			}
			if ( $is_beta ) {
				$input_html .= '<span class="onetap-admin-beta-badge">' . esc_html__( 'Beta', 'accessibility-onetap' ) . '</span>';
			}
			$input_html .= '</span>';
		}

		// Feature description - only show if feature_desc is provided (regardless of is_pro status).
		if ( ! empty( $feature_desc ) ) {
			$input_html .= '<span class="feature-desc">' . $feature_desc . '</span>';
		}

		$input_html .= '</div>';

		$input_html .= '</div>';

		$input_html .= '<div class="right">';

		// Show PRO feature information if is_pro is true.
		if ( $is_pro ) {
			$input_html .= '<div class="pro-info">';
			$input_html .= '<h4 class="pro-title">' . $pro_title . '</h4>';
			$input_html .= '<p class="pro-desc">';
			$input_html .= '<a target="_blank" href="' . $upgrade_link . '" class="upgrade-link">' . $pro_link_text . '</a> ' . $pro_desc;
			$input_html .= '</p>';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="box-swich">';
		$input_html .= '<label class="switch" for="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']">';
		$input_html .= '<input type="checkbox" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="on"' . checked( $parsed_args['value'], 'on', false ) . '>';
		$input_html .= '<span class="slider round"></span>';
		$input_html .= '</label>';
		$input_html .= '</div>';
		$input_html .= '</div>';

		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'switch' );
	}

	/**
	 * Callback function for rendering number input template in settings.
	 *
	 * This method generates HTML for a number input field with customizable attributes
	 * such as min, max, step values, and optional save button. It supports various
	 * styling options and accessibility features.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_number( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'number';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build input attributes.
		$placeholder = ! empty( $args['placeholder'] ) ? 'placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';
		$min         = ( '' === $args['min'] ) ? '' : ' min="' . esc_attr( $args['min'] ) . '"';
		$max         = ( '' === $args['max'] ) ? '' : ' max="' . esc_attr( $args['max'] ) . '"';
		$step        = ( '' === $args['step'] ) ? '' : ' step="' . esc_attr( $args['step'] ) . '"';

		// Build input HTML.
		$input_html  = '<div class="box box1">';
		$input_html .= '<input type="' . esc_attr( $parsed_args['type'] ) . '" class="number" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . absint( $parsed_args['value'] ) . '"' . $placeholder . $min . $max . $step . '/>';
		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'number' );
	}

	/**
	 * Callback function for rendering number slider template in settings.
	 *
	 * This method generates HTML for a number slider input field with range slider and number input.
	 * It supports customizable min, max, and step values with proper sanitization for security.
	 * The slider provides visual feedback while the number input allows precise value entry.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_number_slider( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'number-slider';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build input attributes.
		$min  = ( '' === $args['min'] ) ? '' : ' min="' . esc_attr( $args['min'] ) . '"';
		$max  = ( '' === $args['max'] ) ? '' : ' max="' . esc_attr( $args['max'] ) . '"';
		$step = ( '' === $args['step'] ) ? '' : ' step="' . esc_attr( $args['step'] ) . '"';

		// Get unit value with fallback to 'px'.
		$unit = isset( $args['units'] ) && ! empty( $args['units'] ) ? esc_attr( $args['units'] ) : 'px';

		// Build input HTML.
		$input_html  = '<div class="box box1">';
		$input_html .= '<div class="box-range">';
		$input_html .= '<input type="range" value="' . floatval( $parsed_args['value'] ) . '"' . $min . $max . $step . '>';
		$input_html .= '</div>';

		$input_html .= '<div class="box-number">';
		$input_html .= '<input type="number" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . absint( $parsed_args['value'] ) . '"' . $min . $max . $step . '>';
		$input_html .= '<span class="unit">' . $unit . '</span>';
		$input_html .= '</div>';
		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'number-slider' );
	}

	/**
	 * Callback function for rendering text input template in settings.
	 *
	 * This method generates HTML for a text input field with customizable attributes
	 * and optional save button. It supports various styling options and accessibility features.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_text( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'text';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build input attributes.
		$placeholder = ! empty( $args['placeholder'] ) ? 'placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';

		// Build input HTML.
		$input_html  = '<div class="box box1">';
		$input_html .= '<input type="' . esc_attr( $parsed_args['type'] ) . '" class="number" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $parsed_args['value'] ) . '"' . $placeholder . '/>';
		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'text' );
	}

	/**
	 * Render a title and description section for settings forms.
	 *
	 * Outputs a settings section with optional title, description, label, and save button.
	 * This template is used for displaying informational content or section headers
	 * in the settings page without any input fields.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_title_and_description( $args ) {
		$this->render_template( $args, '', 'title-and-description' );
	}

	/**
	 * Render a device preview selector for settings forms.
	 *
	 * Outputs a device preview section with desktop, tablet, and mobile options.
	 * Allows users to preview how the accessibility widget will appear on different devices.
	 * Includes SVG icons for each device type and optional save button.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_devices_tabs( $args ) {
		// Build devices HTML.
		$devices_html  = '<div class="box box1">';
		$devices_html .= '<div class="devices-tabs">';

		// Desktop.
		$devices_html .= '<button type="button" class="preview-desktop active" aria-pressed="true" data-device-type="desktop">';
		$devices_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" viewBox="0 0 19 18" fill="none"><path d="M6.5 16.5H13.1667M9.83333 13.1667V16.5M5.5 13.1667H14.1667C15.5668 13.1667 16.2669 13.1667 16.8016 12.8942C17.272 12.6545 17.6545 12.272 17.8942 11.8016C18.1667 11.2669 18.1667 10.5668 18.1667 9.16667V5.5C18.1667 4.09987 18.1667 3.3998 17.8942 2.86502C17.6545 2.39462 17.272 2.01217 16.8016 1.77248C16.2669 1.5 15.5668 1.5 14.1667 1.5H5.5C4.09987 1.5 3.3998 1.5 2.86502 1.77248C2.39462 2.01217 2.01217 2.39462 1.77248 2.86502C1.5 3.3998 1.5 4.09987 1.5 5.5V9.16667C1.5 10.5668 1.5 11.2669 1.77248 11.8016C2.01217 12.272 2.39462 12.6545 2.86502 12.8942C3.3998 13.1667 4.09987 13.1667 5.5 13.1667Z" stroke="#717680" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$devices_html .= esc_html__( 'Desktop', 'accessibility-onetap' );
		$devices_html .= '</button>';

		// Tablet.
		$devices_html .= '<button type="button" class="preview-tablet" aria-pressed="false" data-device-type="tablet">';
		$devices_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="20" viewBox="0 0 15 20" fill="none"><path d="M7.50004 14.5833H7.50837M3.50004 18.3333H11.5C12.4335 18.3333 12.9002 18.3333 13.2567 18.1516C13.5703 17.9918 13.8253 17.7369 13.9851 17.4233C14.1667 17.0668 14.1667 16.6 14.1667 15.6666V4.33329C14.1667 3.39987 14.1667 2.93316 13.9851 2.57664C13.8253 2.26304 13.5703 2.00807 13.2567 1.84828C12.9002 1.66663 12.4335 1.66663 11.5 1.66663H3.50004C2.56662 1.66663 2.09991 1.66663 1.74339 1.84828C1.42979 2.00807 1.17482 2.26304 1.01503 2.57664C0.833374 2.93316 0.833374 3.39987 0.833374 4.33329V15.6666C0.833374 16.6 0.833374 17.0668 1.01503 17.4233C1.17482 17.7369 1.42979 17.9918 1.74339 18.1516C2.09991 18.3333 2.56662 18.3333 3.50004 18.3333ZM7.91671 14.5833C7.91671 14.8134 7.73016 15 7.50004 15C7.26992 15 7.08337 14.8134 7.08337 14.5833C7.08337 14.3532 7.26992 14.1666 7.50004 14.1666C7.73016 14.1666 7.91671 14.3532 7.91671 14.5833Z" stroke="#A4A7AE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$devices_html .= esc_html__( 'Tablet', 'accessibility-onetap' );
		$devices_html .= '</button>';

		// Mobile.
		$devices_html .= '<button type="button" class="preview-mobile" aria-pressed="false" data-device-type="mobile">';
		$devices_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="20" viewBox="0 0 14 20" fill="none"><path d="M6.66659 14.5833H6.67492M3.49992 18.3333H9.83325C10.7667 18.3333 11.2334 18.3333 11.5899 18.1516C11.9035 17.9918 12.1585 17.7369 12.3183 17.4233C12.4999 17.0668 12.4999 16.6 12.4999 15.6666V4.33329C12.4999 3.39987 12.4999 2.93316 12.3183 2.57664C12.1585 2.26304 11.9035 2.00807 11.5899 1.84828C11.2334 1.66663 10.7667 1.66663 9.83325 1.66663H3.49992C2.5665 1.66663 2.09979 1.66663 1.74327 1.84828C1.42966 2.00807 1.1747 2.26304 1.01491 2.57664C0.833252 2.93316 0.833252 3.39987 0.833252 4.33329V15.6666C0.833252 16.6 0.833252 17.0668 1.01491 17.4233C1.1747 17.7369 1.42966 17.9918 1.74327 18.1516C2.09979 18.3333 2.5665 18.3333 3.49992 18.3333ZM7.08325 14.5833C7.08325 14.8134 6.8967 15 6.66659 15C6.43647 15 6.24992 14.8134 6.24992 14.5833C6.24992 14.3532 6.43647 14.1666 6.66659 14.1666C6.8967 14.1666 7.08325 14.3532 7.08325 14.5833Z" stroke="#A4A7AE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$devices_html .= esc_html__( 'Mobile', 'accessibility-onetap' );
		$devices_html .= '</button>';

		$devices_html .= '</div>';
		$devices_html .= '</div>';

		$this->render_template( $args, $devices_html, 'devices' );
	}

	/**
	 * Render a radio field with image/icon options for settings forms.
	 *
	 * Outputs a group of radio buttons where each option is represented by an image/icon.
	 * Optionally displays a 'Save Changes' button below the options.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_radio_image( $args ) {
		$parsed_args      = $this->parse_template_args( $args );
		$radio_image_list = isset( $args['radio_image_list'] ) && is_array( $args['radio_image_list'] ) ? $args['radio_image_list'] : array();

		// Build radio image HTML.
		$radio_html = '';
		$index      = 1;
		foreach ( $radio_image_list as $image_value => $image ) {
			$checked     = ( $parsed_args['value'] === $image_value ) ? 'checked' : '';
			$input_id    = esc_attr( $args['id'] ) . esc_attr( str_replace( 'design-icon', '', $image_value ) );
			$radio_html .= '<div class="box box' . $index . '">';
			$radio_html .= '<label class="label ' . $checked . '">';
			$radio_html .= '<img src="' . ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/' . esc_attr( $image ) . '" />';
			$radio_html .= '<input type="radio" class="' . $checked . '" id="' . $input_id . '" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $image_value ) . '" ' . $checked . ' />';
			$radio_html .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 4C0 1.79086 1.79086 0 4 0H12C14.2091 0 16 1.79086 16 4V12C16 14.2091 14.2091 16 12 16H4C1.79086 16 0 14.2091 0 12V4Z" fill="#0048FE"/><path d="M12 5L6.5 10.5L4 8" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			$radio_html .= '</label>';
			$radio_html .= '</div>';
			++$index;
		}

		$this->render_template( $args, $radio_html, 'radio-image' );
	}

	/**
	 * Render a radio field with text options for settings forms.
	 *
	 * Outputs a group of radio buttons where each option is represented by an text.
	 * Optionally displays a 'Save Changes' button below the options.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_radio_text( $args ) {
		$parsed_args     = $this->parse_template_args( $args );
		$radio_text_list = isset( $args['radio_text_list'] ) && is_array( $args['radio_text_list'] ) ? $args['radio_text_list'] : array();

		// Build radio text HTML.
		$radio_html = '';
		$index      = 1;
		foreach ( $radio_text_list as $text_value => $text ) {
			$checked     = ( $parsed_args['value'] === $text_value ) ? 'checked' : '';
			$input_id    = esc_attr( $args['id'] ) . esc_attr( str_replace( 'design-text', '', $text_value ) );
			$radio_html .= '<div class="box box' . $index . '">';
			$radio_html .= '<label class="label ' . $checked . '">';
			$radio_html .= '<span class="text">';
			$radio_html .= $text;
			$radio_html .= '</span>';
			$radio_html .= '<input type="radio" class="' . $checked . '" id="' . $input_id . '" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $text_value ) . '" ' . $checked . ' />';
			$radio_html .= '</label>';
			$radio_html .= '</div>';
			++$index;
		}

		$this->render_template( $args, $radio_html, 'radio-text' );
	}

	/**
	 * Render a copyable text field for settings forms.
	 *
	 * Outputs a text field with copy functionality that allows users to copy text to clipboard.
	 * This template is used for displaying text that can be copied, such as URLs or codes.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_copyable_text( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'copyable_text';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build copyable text content attributes.
		$copyable_icon  = isset( $args['copyable_icon'] ) ? esc_url( $args['copyable_icon'] ) : '';
		$is_pro         = isset( $args['is_pro'] ) ? (bool) $args['is_pro'] : false;
		$feature_name   = isset( $args['feature_name'] ) ? esc_html( $args['feature_name'] ) : '';
		$feature_desc   = isset( $args['feature_desc'] ) ? esc_html( $args['feature_desc'] ) : '';
		$copyable_style = isset( $args['copyable_style'] ) ? esc_attr( $args['copyable_style'] ) : '';

		// Build PRO feature information attributes.
		$pro_title     = isset( $args['pro_title'] ) ? esc_html( $args['pro_title'] ) : __( 'This is a PRO Feature', 'accessibility-onetap' );
		$pro_desc      = isset( $args['pro_desc'] ) ? esc_html( $args['pro_desc'] ) : __( ' to use this Feature.', 'accessibility-onetap' );
		$pro_link_text = isset( $args['pro_link_text'] ) ? esc_html( $args['pro_link_text'] ) : __( 'Upgrade to Pro', 'accessibility-onetap' );
		$upgrade_link  = isset( $args['upgrade_link'] ) ? esc_url( $args['upgrade_link'] ) : 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar';

		// Check if label exists and is not empty, then add padding-top-0 class (except for style2 style).
		$label_class = ( isset( $args['label'] ) && ! empty( $args['label'] ) && 'style2' !== $copyable_style ) ? ' padding-top-0' : '';

		// Build input HTML.
		$input_html = '<div class="box box1' . ( ! empty( $copyable_style ) ? ' ' . $copyable_style : '' ) . ( $is_pro ? ' pro' : '' ) . $label_class . '">';

		$input_html .= '<div class="left">';

		// Icon section - only show if copyable_icon is provided.
		if ( ! empty( $copyable_icon ) ) {
			$input_html .= '<div class="icon">';
			$input_html .= '<img src="' . $copyable_icon . '" alt="icon">';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="text">';

		// Display feature name - only show if feature_name exists (regardless of is_pro status).
		if ( ! empty( $feature_name ) ) {
			$input_html .= '<span class="feature-name">' . $feature_name . ( $is_pro ? $this->get_pro_badge() : '' ) . '</span>';
		}

		// Feature description - only show if feature_desc is provided (regardless of is_pro status).
		if ( ! empty( $feature_desc ) ) {
			$input_html .= '<span class="feature-desc">' . $feature_desc . '</span>';
		}

		$input_html .= '</div>';

		$input_html .= '</div>';

		$input_html .= '<div class="right">';

		// Show PRO feature information if is_pro is true.
		if ( $is_pro ) {
			$input_html .= '<div class="pro-info">';
			$input_html .= '<h4 class="pro-title">' . $pro_title . '</h4>';
			$input_html .= '<p class="pro-desc">';
			$input_html .= '<a target="_blank" href="' . $upgrade_link . '" class="upgrade-link">' . $pro_link_text . '</a> ' . $pro_desc;
			$input_html .= '</p>';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="box-copy-text">';

		$input_html .= '<div class="copy-text">';
		$input_html .= $args['copy_text'];
		$input_html .= '</div>';

		$input_html .= '<button type="button" class="button outline copy-button" data-copy-text="' . esc_attr( $args['copy_text'] ) . '" data-success-text="' . esc_attr( __( 'Copied!', 'accessibility-onetap' ) ) . '" data-error-text="' . esc_attr( __( 'Failed!', 'accessibility-onetap' ) ) . '"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><g clip-path="url(#clip0_622_885)"><path d="M4.1665 12.5C3.38993 12.5 3.00165 12.5 2.69536 12.3731C2.28698 12.204 1.96253 11.8795 1.79337 11.4711C1.6665 11.1648 1.6665 10.7766 1.6665 9.99999V4.33332C1.6665 3.3999 1.6665 2.93319 1.84816 2.57667C2.00795 2.26307 2.26292 2.0081 2.57652 1.84831C2.93304 1.66666 3.39975 1.66666 4.33317 1.66666H9.99984C10.7764 1.66666 11.1647 1.66666 11.471 1.79352C11.8794 1.96268 12.2038 2.28714 12.373 2.69552C12.4998 3.0018 12.4998 3.39009 12.4998 4.16666M10.1665 18.3333H15.6665C16.5999 18.3333 17.0666 18.3333 17.4232 18.1517C17.7368 17.9919 17.9917 17.7369 18.1515 17.4233C18.3332 17.0668 18.3332 16.6001 18.3332 15.6667V10.1667C18.3332 9.23324 18.3332 8.76653 18.1515 8.41001C17.9917 8.0964 17.7368 7.84143 17.4232 7.68165C17.0666 7.49999 16.5999 7.49999 15.6665 7.49999H10.1665C9.23308 7.49999 8.76637 7.49999 8.40985 7.68165C8.09625 7.84143 7.84128 8.0964 7.68149 8.41001C7.49984 8.76653 7.49984 9.23324 7.49984 10.1667V15.6667C7.49984 16.6001 7.49984 17.0668 7.68149 17.4233C7.84128 17.7369 8.09625 17.9919 8.40985 18.1517C8.76637 18.3333 9.23308 18.3333 10.1665 18.3333Z" stroke="#A4A7AE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_622_885"><rect width="20" height="20" fill="white"/></clipPath></defs></svg>' . __( 'Copy', 'accessibility-onetap' ) . '</button>';

		$input_html .= '</div>';

		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'copyable_text' );
	}



	/**
	 * Render a module labels input field for settings forms.
	 *
	 * Creates a custom input field specifically designed for module label customization.
	 * The function supports both free and PRO features, with different visual indicators
	 * and functionality based on the feature status.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_module_labels( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$camel_case_id          = lcfirst( str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', $args['id'] ) ) ) );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'module-labels';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build module labels content attributes.
		$module_icon  = isset( $args['module_icon'] ) ? esc_url( $args['module_icon'] ) : '';
		$is_pro       = isset( $args['is_pro'] ) ? (bool) $args['is_pro'] : false;
		$is_beta      = ! empty( $args['is_beta'] );
		$feature_name = isset( $args['feature_name'] ) ? esc_html( $args['feature_name'] ) : '';
		$feature_desc = isset( $args['feature_desc'] ) ? esc_html( $args['feature_desc'] ) : '';
		$module_style = isset( $args['module_style'] ) ? esc_attr( $args['module_style'] ) : '';

		// Build PRO feature information attributes.
		$pro_title     = isset( $args['pro_title'] ) ? esc_html( $args['pro_title'] ) : __( 'This is a PRO Feature', 'accessibility-onetap' );
		$pro_desc      = isset( $args['pro_desc'] ) ? esc_html( $args['pro_desc'] ) : __( ' to use this Feature.', 'accessibility-onetap' );
		$pro_link_text = isset( $args['pro_link_text'] ) ? esc_html( $args['pro_link_text'] ) : __( 'Upgrade to Pro', 'accessibility-onetap' );
		$upgrade_link  = isset( $args['upgrade_link'] ) ? esc_url( $args['upgrade_link'] ) : 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar';

		// Check if label exists and is not empty, then add padding-top-0 class (except for switch2 style).
		$label_class = ( isset( $args['label'] ) && ! empty( $args['label'] ) && 'switch2' !== $module_style ) ? ' padding-top-0' : '';

		// Build input attributes.
		$placeholder_text = ! empty( $args['placeholder'] ) ? $args['placeholder'] : __( 'Enter Custom Label', 'accessibility-onetap' );

		// Build input HTML.
		$input_html = '<div class="box box1' . ( ! empty( $module_style ) ? ' ' . $module_style : '' ) . ( $is_pro ? ' pro' : '' ) . $label_class . '">';

		$input_html .= '<div class="left">';

		// Icon section - only show if module_icon is provided.
		if ( ! empty( $module_icon ) ) {
			$input_html .= '<div class="icon">';
			$input_html .= '<img src="' . $module_icon . '" alt="icon">';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="text">';

		// Display feature name - only show if feature_name exists (regardless of is_pro status).
		if ( ! empty( $feature_name ) ) {
			$input_html .= '<span class="feature-name">' . $feature_name;
			if ( $is_pro ) {
				$input_html .= $this->get_pro_badge();
			}
			if ( $is_beta ) {
				$input_html .= '<span class="onetap-admin-beta-badge">' . esc_html__( 'Beta', 'accessibility-onetap' ) . '</span>';
			}
			$input_html .= '</span>';
		}

		// Feature description - only show if feature_desc is provided (regardless of is_pro status).
		if ( ! empty( $feature_desc ) ) {
			$input_html .= '<span class="feature-desc">' . $feature_desc . '</span>';
		}

		$input_html .= '</div>';

		$input_html .= '</div>';

		$input_html .= '<div class="right">';

		// Show PRO feature information if is_pro is true.
		if ( $is_pro ) {
			$input_html .= '<div class="pro-info">';
			$input_html .= '<h4 class="pro-title">' . $pro_title . '</h4>';
			$input_html .= '<p class="pro-desc">';
			$input_html .= '<a target="_blank" href="' . $upgrade_link . '" class="upgrade-link">' . $pro_link_text . '</a> ' . $pro_desc;
			$input_html .= '</p>';
			$input_html .= '</div>';
		}

		$input_html .= '<input type="text" class="text ' . esc_attr( $camel_case_id ) . '" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" placeholder="' . esc_attr( $placeholder_text ) . '"/>';

		$input_html .= '<button type="submit" class="button outline save-changes">';
		$input_html .= esc_html__( 'Save', 'accessibility-onetap' );
		$input_html .= '</button>';

		$input_html .= '</div>';

		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'module-labels' );
	}

	/**
	 * Render a select dropdown field for settings forms.
	 *
	 * Outputs a select dropdown with predefined options from the select_options array.
	 * Includes optional title, description, label, and save button.
	 * The select field allows users to choose from a list of predefined options.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_select( $args ) {
		$parsed_args    = $this->parse_template_args( $args );
		$select_options = isset( $args['select_options'] ) ? $args['select_options'] : array();
		$select_badge   = isset( $args['select_badge'] ) ? $args['select_badge'] : '';

		// Build select HTML.
		$select_html  = '<div class="box box1">';
		$select_html .= '<select id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']">';

		foreach ( $select_options as $key => $label ) {
			$select_html .= '<option value="' . esc_attr( $key ) . '"' . selected( $parsed_args['value'], $key, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$select_html .= '</select>';
		$select_html .= $select_badge ? ' <span class="badge">' . $select_badge . '</span>' : '';
		$select_html .= '</div>';

		$this->render_template( $args, $select_html, 'select' );
	}

	/**
	 * Render a searchable multiselect field for settings forms.
	 *
	 * Shows the current selection as removable tags above a searchable option
	 * list, and submits it as a list of hidden inputs (`section[field][]`) so
	 * it persists as an array.
	 *
	 * In this plugin the control is only used for PRO features, so it renders
	 * with the same locked treatment as the PRO switches: the badge, the
	 * upgrade tooltip, and a transparent overlay that swallows clicks. There is
	 * deliberately no script behind it here.
	 *
	 * Recognised args on top of the shared ones:
	 * - select_options      array  Map of value => label to offer.
	 * - placeholder         string Text shown while nothing is selected.
	 * - search_placeholder  string Placeholder for the search input.
	 * - empty_text          string Message shown when the search matches nothing.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_multiselect( $args ) {
		$select_options = isset( $args['select_options'] ) && is_array( $args['select_options'] ) ? $args['select_options'] : array();

		// Read the saved selection and drop anything that no longer exists so a
		// deleted page cannot leave an unremovable tag behind.
		$raw_value = $this->get_option( $args['id'], $args['section'], isset( $args['std'] ) ? $args['std'] : array() );
		$raw_value = is_array( $raw_value ) ? $raw_value : array();

		$available = array_map( 'strval', array_keys( $select_options ) );
		$selected  = array();
		foreach ( $raw_value as $value ) {
			$value = (string) $value;
			if ( '' !== $value && in_array( $value, $available, true ) && ! in_array( $value, $selected, true ) ) {
				$selected[] = $value;
			}
		}

		$field_name         = $args['section'] . '[' . $args['id'] . ']';
		$placeholder        = ! empty( $args['placeholder'] ) ? $args['placeholder'] : __( 'Select…', 'accessibility-onetap' );
		$search_placeholder = ! empty( $args['search_placeholder'] ) ? $args['search_placeholder'] : __( 'Search…', 'accessibility-onetap' );
		$empty_text         = ! empty( $args['empty_text'] ) ? $args['empty_text'] : __( 'No results found', 'accessibility-onetap' );

		$feature_name = isset( $args['feature_name'] ) ? $args['feature_name'] : '';
		$feature_desc = isset( $args['feature_desc'] ) ? $args['feature_desc'] : '';

		// PRO feature information attributes, mirroring callback_template_switch.
		$is_pro        = isset( $args['is_pro'] ) ? (bool) $args['is_pro'] : false;
		$pro_title     = isset( $args['pro_title'] ) ? esc_html( $args['pro_title'] ) : __( 'This is a PRO Feature', 'accessibility-onetap' );
		$pro_desc      = isset( $args['pro_desc'] ) ? esc_html( $args['pro_desc'] ) : __( ' to use this Feature.', 'accessibility-onetap' );
		$pro_link_text = isset( $args['pro_link_text'] ) ? esc_html( $args['pro_link_text'] ) : __( 'Upgrade to Pro', 'accessibility-onetap' );
		$upgrade_link  = isset( $args['upgrade_link'] ) ? esc_url( $args['upgrade_link'] ) : 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar';

		// Reusable icons.
		$icon_chevron = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 6L8 3.33333L5.33333 6" stroke="#A4A7AE" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.33333 10L8 12.6667L10.6667 10" stroke="#A4A7AE" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$icon_search  = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 14L11.1 11.1" stroke="#717680" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.6667 7.33333C12.6667 10.2789 10.2789 12.6667 7.33333 12.6667C4.38781 12.6667 2 10.2789 2 7.33333C2 4.38781 4.38781 2 7.33333 2C10.2789 2 12.6667 4.38781 12.6667 7.33333Z" stroke="#717680" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$icon_check   = '<svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 1L4.75 9.25L1 5.5" stroke="#0048FE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$icon_remove  = '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 2.5L2.5 7.5" stroke="#717680" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.5 2.5L7.5 7.5" stroke="#717680" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		$html = '<div class="box box1' . ( $is_pro ? ' pro' : '' ) . '">';

		// Left column: the feature name and its description.
		if ( ! empty( $feature_name ) || ! empty( $feature_desc ) ) {
			$html .= '<div class="left">';
			$html .= '<div class="text">';

			if ( ! empty( $feature_name ) ) {
				$html .= '<span class="feature-name">' . esc_html( $feature_name );
				if ( $is_pro ) {
					$html .= $this->get_pro_badge();
				}
				$html .= '</span>';
			}

			if ( ! empty( $feature_desc ) ) {
				$html .= '<span class="feature-desc">' . esc_html( $feature_desc ) . '</span>';
			}

			$html .= '</div>';
			$html .= '</div>';
		}

		// Right column: the combobox itself.
		$html .= '<div class="right">';

		if ( $is_pro ) {
			$html .= '<div class="pro-info">';
			$html .= '<h4 class="pro-title">' . $pro_title . '</h4>';
			$html .= '<p class="pro-desc">';
			$html .= '<a target="_blank" href="' . $upgrade_link . '" class="upgrade-link">' . $pro_link_text . '</a> ' . $pro_desc;
			$html .= '</p>';
			$html .= '</div>';
		}

		$html .= '<div class="apop-multiselect" data-name="' . esc_attr( $field_name ) . '" data-placeholder="' . esc_attr( $placeholder ) . '">';

		// Always submit an empty entry so clearing the selection is persisted.
		$html .= '<input type="hidden" name="' . esc_attr( $field_name ) . '[]" value="" />';

		// Hidden inputs holding the current selection.
		$html .= '<div class="apop-multiselect-inputs">';
		foreach ( $selected as $value ) {
			$html .= '<input type="hidden" class="apop-multiselect-value" name="' . esc_attr( $field_name ) . '[]" value="' . esc_attr( $value ) . '" />';
		}
		$html .= '</div>';

		// Trigger showing the selected tags. Locked PRO controls have no script
		// behind them, so the button is disabled rather than left focusable and
		// inert — the overlay alone would not keep it out of the tab order.
		$html .= '<button type="button" class="apop-multiselect-trigger" aria-expanded="false" aria-haspopup="listbox"' . ( $is_pro ? ' disabled' : '' ) . '>';
		$html .= '<span class="apop-multiselect-tags">';

		$html .= '<span class="apop-multiselect-placeholder"' . ( empty( $selected ) ? '' : ' aria-hidden="true"' ) . '>' . esc_html( $placeholder ) . '</span>';

		foreach ( $selected as $value ) {
			$label = isset( $select_options[ $value ] ) ? $select_options[ $value ] : $value;

			$html .= '<span class="apop-multiselect-tag" data-value="' . esc_attr( $value ) . '">';
			$html .= '<span class="apop-multiselect-tag-label">' . esc_html( $label ) . '</span>';
			/* translators: %s: name of the selected item. */
			$html .= '<span class="apop-multiselect-tag-remove" data-value="' . esc_attr( $value ) . '" role="button" aria-label="' . esc_attr( sprintf( __( 'Remove %s', 'accessibility-onetap' ), $label ) ) . '">' . $icon_remove . '</span>';
			$html .= '</span>';
		}

		$html .= '</span>';
		$html .= '<span class="apop-multiselect-chevron">' . $icon_chevron . '</span>';
		$html .= '</button>';

		// Popup with search field and option list.
		$html .= '<div class="apop-multiselect-popup">';
		$html .= '<div class="apop-multiselect-search">';
		$html .= '<span class="apop-multiselect-search-icon">' . $icon_search . '</span>';
		$html .= '<input type="text" class="apop-multiselect-search-input" placeholder="' . esc_attr( $search_placeholder ) . '" autocomplete="off" aria-label="' . esc_attr( $search_placeholder ) . '" />';
		$html .= '</div>';

		$html .= '<ul class="apop-multiselect-options" role="listbox" aria-multiselectable="true">';
		foreach ( $select_options as $key => $label ) {
			$key         = (string) $key;
			$is_selected = in_array( $key, $selected, true );

			$html .= '<li class="apop-multiselect-option' . ( $is_selected ? ' selected' : '' ) . '" data-value="' . esc_attr( $key ) . '" data-label="' . esc_attr( $label ) . '" role="option" aria-selected="' . ( $is_selected ? 'true' : 'false' ) . '">';
			$html .= '<span class="apop-multiselect-check">' . $icon_check . '</span>';
			$html .= '<span class="apop-multiselect-option-label">' . esc_html( $label ) . '</span>';
			$html .= '</li>';
		}
		$html .= '</ul>';

		$html .= '<div class="apop-multiselect-empty">' . esc_html( $empty_text ) . '</div>';
		$html .= '</div>'; // .apop-multiselect-popup

		$html .= '</div>'; // .apop-multiselect
		$html .= '</div>'; // .right
		$html .= '</div>'; // .box

		$this->render_template( $args, $html, 'multiselect' );
	}

	/**
	 * Sanitize a multiselect value that holds numeric IDs (pages, posts).
	 *
	 * Declared static so it can be referenced as a string callable from the
	 * field definitions and resolved by get_sanitize_callback().
	 *
	 * @param mixed $value Raw submitted value.
	 * @return array List of unique, positive integers.
	 */
	public static function sanitize_multiselect_ids( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$ids = array();
		foreach ( $value as $item ) {
			// Reject anything that is not already a plain number so garbage input
			// cannot be coerced into an unrelated ID (e.g. "-3" becoming 3).
			if ( ! is_numeric( $item ) ) {
				continue;
			}

			$id = absint( $item );
			if ( $id > 0 && (int) $item === $id && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Sanitize a multiselect value that holds slugs (post types).
	 *
	 * @param mixed $value Raw submitted value.
	 * @return array List of unique, sanitized slugs.
	 */
	public static function sanitize_multiselect_slugs( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$slugs = array();
		foreach ( $value as $item ) {
			$slug = sanitize_key( $item );
			if ( '' !== $slug && ! in_array( $slug, $slugs, true ) ) {
				$slugs[] = $slug;
			}
		}

		return $slugs;
	}

	/**
	 * Render a custom widget position dropdown field for settings forms.
	 *
	 * Outputs a custom dropdown with icons for each position option.
	 * Includes optional title, description, label, and save button.
	 * The dropdown allows users to choose widget position with visual icons.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_widget_position( $args ) {
		$parsed_args    = $this->parse_template_args( $args );
		$select_options = isset( $args['select_options'] ) ? $args['select_options'] : array();

		// Get current selected value or default.
		$current_value = $parsed_args['value'];
		if ( empty( $current_value ) && isset( $args['default'] ) ) {
			$current_value = $args['default'];
		}

		// Get current selected label.
		$current_label = isset( $select_options[ $current_value ] ) ? $select_options[ $current_value ] : '';

		// Icon mapping for each position (directional arrows).
		$position_icons = array(
			'top-left'     => '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.75 10.75L0.75 0.75M0.75 0.75V10.75M0.75 0.75H10.75" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'top-right'    => '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.75 10.75L10.75 0.75M10.75 0.75H0.75M10.75 0.75V10.75" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'middle-left'  => '<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 6.75L0 6.75M0 6.75L6 12.75M0 6.75L6 0.75" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'middle-right' => '<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.24537e-07 6.75L16 6.75M16 6.75L10 0.749999M16 6.75L10 12.75" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'bottom-left'  => '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.75 0.75L0.75 10.75M0.75 10.75H10.75M0.75 10.75V0.75" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			'bottom-right' => '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.75 0.75L10.75 10.75M10.75 10.75V0.75M10.75 10.75H0.75" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		);

		// Build custom dropdown HTML.
		$dropdown_html  = '<div class="box box1">';
		$dropdown_html .= '<div class="widget-position-dropdown">';

		// Hidden input for form submission.
		$default_value  = isset( $args['default'] ) ? $args['default'] : '';
		$dropdown_html .= '<input type="hidden" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $current_value ) . '" class="widget-position-input" data-default="' . esc_attr( $default_value ) . '"/>';

		// Dropdown wrapper.
		$dropdown_html .= '<div class="widget-position-wrapper">';

		// Dropdown trigger button.
		$current_icon   = isset( $position_icons[ $current_value ] ) ? $position_icons[ $current_value ] : '';
		$dropdown_html .= '<button type="button" class="widget-position-trigger" aria-expanded="false">';
		$dropdown_html .= '<span class="widget-position-selected">';
		$dropdown_html .= '<span class="widget-position-icon">' . $current_icon . '</span>';
		$dropdown_html .= '<span class="widget-position-label">' . esc_html( $current_label ) . '</span>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="widget-position-chevron">';
		$dropdown_html .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '</button>';

		// Default button (separate from trigger).
		$dropdown_html .= '<button type="button" class="widget-label-default badge-default">' . esc_html__( 'Default', 'accessibility-onetap' ) . '</button>';

		$dropdown_html .= '</div>'; // .widget-position-wrapper

		// Dropdown options list.
		$dropdown_html .= '<ul class="widget-position-options">';
		$index          = 0;
		foreach ( $select_options as $key => $label ) {
			++$index;
			$is_selected    = ( $current_value === $key );
			$icon           = isset( $position_icons[ $key ] ) ? $position_icons[ $key ] : '';
			$dropdown_html .= '<li class="widget-position-option' . ( $is_selected ? ' selected' : '' ) . '" data-value="' . esc_attr( $key ) . '">';
			$dropdown_html .= '<span class="widget-position-option-icon">' . $icon . '</span>';
			$dropdown_html .= '<span class="widget-position-option-label">' . esc_html( $label ) . '</span>';
			$dropdown_html .= '<span class="widget-position-checkmark">';
			$dropdown_html .= '<svg width="15" height="11" viewBox="0 0 15 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.1666 0.833344L4.99998 10L0.833313 5.83334" stroke="#0048FE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			$dropdown_html .= '</span>';
			$dropdown_html .= '</li>';
			if ( in_array( $index, array( 2, 4 ), true ) ) {
				$dropdown_html .= '<hr />';
			}
		}
		$dropdown_html .= '</ul>';

		$dropdown_html .= '</div>'; // .widget-position-dropdown
		$dropdown_html .= '</div>'; // .box

		$this->render_template( $args, $dropdown_html, 'widget-position' );
	}

	/**
	 * Render a custom language select dropdown field for settings forms.
	 *
	 * Outputs a custom dropdown with flag icons for each language option.
	 * Includes optional title, description, label, and save button.
	 * The dropdown allows users to choose language with visual flag icons.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_language_select( $args ) {
		$parsed_args    = $this->parse_template_args( $args );
		$select_options = isset( $args['select_options'] ) ? $args['select_options'] : array();
		$select_badge   = isset( $args['select_badge'] ) ? $args['select_badge'] : '';

		// Get current selected value - use raw value from get_option to avoid esc_attr issues.
		$raw_value     = $this->get_option( $args['id'], $args['section'], isset( $args['std'] ) ? $args['std'] : ( isset( $args['default'] ) ? $args['default'] : '' ) );
		$current_value = ! empty( $raw_value ) ? trim( $raw_value ) : '';

		// If still empty, use default from args.
		if ( empty( $current_value ) ) {
			if ( isset( $args['default'] ) && ! empty( $args['default'] ) ) {
				$current_value = trim( $args['default'] );
			} elseif ( isset( $args['std'] ) && ! empty( $args['std'] ) ) {
				$current_value = trim( $args['std'] );
			}
		}

		// Get current selected label with fallback.
		$current_label = '';
		if ( ! empty( $current_value ) && isset( $select_options[ $current_value ] ) ) {
			$current_label = $select_options[ $current_value ];
		} elseif ( ! empty( $current_value ) ) {
			// If value exists but not in options, try to find it (case-insensitive or with different format).
			foreach ( $select_options as $key => $label ) {
				if ( strtolower( trim( $key ) ) === strtolower( $current_value ) ) {
					$current_value = $key; // Normalize to the correct key.
					$current_label = $label;
					break;
				}
			}
		}

		// Final fallback: use default value if label is still empty.
		if ( empty( $current_label ) ) {
			// Try default first, then std.
			$fallback_value = '';
			if ( isset( $args['default'] ) && ! empty( $args['default'] ) ) {
				$fallback_value = trim( $args['default'] );
			} elseif ( isset( $args['std'] ) && ! empty( $args['std'] ) ) {
				$fallback_value = trim( $args['std'] );
			}

			if ( ! empty( $fallback_value ) && isset( $select_options[ $fallback_value ] ) ) {
				$current_value = $fallback_value;
				$current_label = $select_options[ $fallback_value ];
			} elseif ( ! empty( $select_options ) ) {
				// Last resort: use first available option.
				$first_key = key( $select_options );
				if ( $first_key ) {
					$current_value = $first_key;
					$current_label = $select_options[ $first_key ];
				}
			}
		}

		// Mapping language codes to flag image filenames.
		$flag_images = array(
			'en'    => 'english.png',
			'de'    => 'german.png',
			'es'    => 'spanish.png',
			'fr'    => 'french.png',
			'it'    => 'italia.png',
			'pl'    => 'poland.png',
			'se'    => 'swedish.png',
			'fi'    => 'finnland.png',
			'pt'    => 'portugal.png',
			'ro'    => 'rumania.png',
			'si'    => 'slowenien.png',
			'sk'    => 'slowakia.png',
			'nl'    => 'netherland.png',
			'dk'    => 'danish.png',
			'gr'    => 'greece.png',
			'cz'    => 'czech.png',
			'hu'    => 'hungarian.png',
			'lt'    => 'lithuanian.png',
			'lv'    => 'latvian.png',
			'ee'    => 'estonian.png',
			'hr'    => 'croatia.png',
			'ie'    => 'ireland.png',
			'bg'    => 'bulgarian.png',
			'no'    => 'norwegan.png',
			'tr'    => 'turkish.png',
			'id'    => 'indonesian.png',
			'pt-br' => 'brasilian.png',
			'ja'    => 'japanese.png',
			'ko'    => 'korean.png',
			'zh'    => 'chinese-simplified.png',
			'ar'    => 'arabic.png',
			'ru'    => 'russian.png',
			'hi'    => 'hindi.png',
			'uk'    => 'ukrainian.png',
			'sr'    => 'serbian.png',
			'gb'    => 'england.png',
			'ir'    => 'iran.png',
			'il'    => 'israel.png',
			'mk'    => 'macedonia.png',
			'th'    => 'thailand.png',
			'vn'    => 'vietnam.png',
			'bn'    => 'bangladesh.png',
			'tl'    => 'philippines.png',
			'ur'    => 'pakistan.png',
		);

		// Get plugin URL for flag images.
		$plugin_url = ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/';

		// Get current flag image.
		$current_flag = isset( $flag_images[ $current_value ] ) ? $flag_images[ $current_value ] : $flag_images['en'];

		// Build custom dropdown HTML.
		$dropdown_html  = '<div class="box box1">';
		$dropdown_html .= '<div class="language-select-dropdown">';

		// Hidden input for form submission.
		$default_value  = isset( $args['default'] ) ? $args['default'] : '';
		$dropdown_html .= '<input type="hidden" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $current_value ) . '" class="language-select-input" data-default="' . esc_attr( $default_value ) . '" data-lang="' . esc_attr( $current_value ) . '"/>';

		// Dropdown wrapper.
		$dropdown_html .= '<div class="language-select-wrapper">';

		// Dropdown trigger button.
		$dropdown_html .= '<button type="button" class="language-select-trigger" aria-expanded="false" data-lang="' . esc_attr( $current_value ) . '">';
		$dropdown_html .= '<span class="language-select-selected">';
		$dropdown_html .= '<span class="language-select-flag">';
		$dropdown_html .= '<img src="' . esc_url( $plugin_url . $current_flag ) . '" alt="' . esc_attr( $current_value ) . '" />';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="language-select-label">' . esc_html( $current_label ) . '</span>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="language-select-chevron">';
		$dropdown_html .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '</button>';

		// Default button (separate from trigger).
		if ( $select_badge ) {
			$dropdown_html .= '<button type="button" class="language-label-default badge-default">' . esc_html( $select_badge ) . '</button>';
		}

		$dropdown_html .= '</div>'; // .language-select-wrapper

		// Dropdown options list.
		$dropdown_html .= '<ul class="language-select-options">';
		foreach ( $select_options as $key => $label ) {
			$is_selected    = ( $current_value === $key );
			$flag_image     = isset( $flag_images[ $key ] ) ? $flag_images[ $key ] : 'english.png';
			$dropdown_html .= '<li class="language-select-option' . ( $is_selected ? ' selected' : '' ) . '" data-value="' . esc_attr( $key ) . '" data-lang="' . esc_attr( $key ) . '">';
			$dropdown_html .= '<span class="language-select-option-flag">';
			$dropdown_html .= '<img src="' . esc_url( $plugin_url . $flag_image ) . '" alt="' . esc_attr( $key ) . '" />';
			$dropdown_html .= '</span>';
			$dropdown_html .= '<span class="language-select-option-label">' . esc_html( $label ) . '</span>';
			$dropdown_html .= '<span class="language-select-checkmark"' . ( $is_selected ? '' : ' style="display: none;"' ) . '>';
			$dropdown_html .= '<svg width="15" height="11" viewBox="0 0 15 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.1666 0.833344L4.99992 10L0.833252 5.83334" stroke="#0048FE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			$dropdown_html .= '</span>';
			$dropdown_html .= '</li>';
		}
		$dropdown_html .= '</ul>';

		$dropdown_html .= '</div>'; // .language-select-dropdown
		$dropdown_html .= '</div>'; // .box

		$this->render_template( $args, $dropdown_html, 'language-select' );
	}

	/**
	 * Callback template for simple language select with flags and country names.
	 * Similar to callback_template_language_select_with_actions but without labels wrapper, toggles, and action buttons.
	 *
	 * @param array $args Arguments for the template.
	 */
	public function callback_template_language_select_simple( $args ) {
		$parsed_args    = $this->parse_template_args( $args );
		$select_options = isset( $args['select_options'] ) ? $args['select_options'] : array();
		$select_badge   = isset( $args['select_badge'] ) ? $args['select_badge'] : '';

		// Get current selected value - use raw value from get_option to avoid esc_attr issues.
		$raw_value     = $this->get_option( $args['id'], $args['section'], isset( $args['std'] ) ? $args['std'] : ( isset( $args['default'] ) ? $args['default'] : '' ) );
		$current_value = ! empty( $raw_value ) ? trim( $raw_value ) : '';

		// If still empty, use default from args.
		if ( empty( $current_value ) ) {
			if ( isset( $args['default'] ) && ! empty( $args['default'] ) ) {
				$current_value = trim( $args['default'] );
			} elseif ( isset( $args['std'] ) && ! empty( $args['std'] ) ) {
				$current_value = trim( $args['std'] );
			}
		}

		// Get current selected label with fallback.
		$current_label = '';
		if ( ! empty( $current_value ) && isset( $select_options[ $current_value ] ) ) {
			$current_label = $select_options[ $current_value ];
		} elseif ( ! empty( $current_value ) ) {
			// If value exists but not in options, try to find it (case-insensitive or with different format).
			foreach ( $select_options as $key => $label ) {
				if ( strtolower( trim( $key ) ) === strtolower( $current_value ) ) {
					$current_value = $key; // Normalize to the correct key.
					$current_label = $label;
					break;
				}
			}
		}

		// Final fallback: use default value if label is still empty.
		if ( empty( $current_label ) ) {
			// Try default first, then std.
			$fallback_value = '';
			if ( isset( $args['default'] ) && ! empty( $args['default'] ) ) {
				$fallback_value = trim( $args['default'] );
			} elseif ( isset( $args['std'] ) && ! empty( $args['std'] ) ) {
				$fallback_value = trim( $args['std'] );
			}

			if ( ! empty( $fallback_value ) && isset( $select_options[ $fallback_value ] ) ) {
				$current_value = $fallback_value;
				$current_label = $select_options[ $fallback_value ];
			} elseif ( ! empty( $select_options ) ) {
				// Last resort: use first available option.
				$first_key = key( $select_options );
				if ( $first_key ) {
					$current_value = $first_key;
					$current_label = $select_options[ $first_key ];
				}
			}
		}

		// Mapping language codes to flag image filenames.
		$flag_images = array(
			'en'    => 'english.png',
			'de'    => 'german.png',
			'es'    => 'spanish.png',
			'fr'    => 'french.png',
			'it'    => 'italia.png',
			'pl'    => 'poland.png',
			'se'    => 'swedish.png',
			'fi'    => 'finnland.png',
			'pt'    => 'portugal.png',
			'ro'    => 'rumania.png',
			'si'    => 'slowenien.png',
			'sk'    => 'slowakia.png',
			'nl'    => 'netherland.png',
			'dk'    => 'danish.png',
			'gr'    => 'greece.png',
			'cz'    => 'czech.png',
			'hu'    => 'hungarian.png',
			'lt'    => 'lithuanian.png',
			'lv'    => 'latvian.png',
			'ee'    => 'estonian.png',
			'hr'    => 'croatia.png',
			'ie'    => 'ireland.png',
			'bg'    => 'bulgarian.png',
			'no'    => 'norwegan.png',
			'tr'    => 'turkish.png',
			'id'    => 'indonesian.png',
			'pt-br' => 'brasilian.png',
			'ja'    => 'japanese.png',
			'ko'    => 'korean.png',
			'zh'    => 'chinese-simplified.png',
			'ar'    => 'arabic.png',
			'ru'    => 'russian.png',
			'hi'    => 'hindi.png',
			'uk'    => 'ukrainian.png',
			'sr'    => 'serbian.png',
			'gb'    => 'england.png',
			'ir'    => 'iran.png',
			'il'    => 'israel.png',
			'mk'    => 'macedonia.png',
			'th'    => 'thailand.png',
			'vn'    => 'vietnam.png',
			'bn'    => 'bangladesh.png',
			'tl'    => 'philippines.png',
			'ur'    => 'pakistan.png',
		);

		// Get plugin URL for flag images.
		$plugin_url = ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/';

		// Get current flag image.
		$current_flag = isset( $flag_images[ $current_value ] ) ? $flag_images[ $current_value ] : $flag_images['en'];

		// Build custom dropdown HTML.
		$dropdown_html  = '<div class="box box1">';
		$dropdown_html .= '<div class="language-select-dropdown">';

		// Hidden input for form submission.
		$default_value  = isset( $args['default'] ) ? $args['default'] : '';
		$dropdown_html .= '<input type="hidden" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $current_value ) . '" class="language-select-input" data-default="' . esc_attr( $default_value ) . '" data-lang="' . esc_attr( $current_value ) . '"/>';

		// Labels wrapper.
		$dropdown_html .= '<div class="language-select-labels-wrapper">';
		$dropdown_html .= '<span class="language-select-label-default">' . esc_html__( 'Default Language', 'accessibility-onetap' ) . '</span>';
		$dropdown_html .= '<span class="language-select-label-display-options">' . esc_html__( 'Display Options', 'accessibility-onetap' ) . '</span>';
		$dropdown_html .= '</div>';

		// Dropdown wrapper.
		$dropdown_html .= '<div class="language-select-wrapper">';

		// Dropdown trigger button.
		$dropdown_html .= '<button type="button" class="language-select-trigger" aria-expanded="false" data-lang="' . esc_attr( $current_value ) . '">';
		$dropdown_html .= '<span class="language-select-selected">';
		$dropdown_html .= '<span class="language-select-flag">';
		$dropdown_html .= '<img src="' . esc_url( $plugin_url . $current_flag ) . '" alt="' . esc_attr( $current_value ) . '" />';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="language-select-label">' . esc_html( $current_label ) . '</span>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="language-select-chevron">';
		$dropdown_html .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '</button>';

		// Default button (separate from trigger).
		if ( $select_badge ) {
			$dropdown_html .= '<button type="button" class="language-label-default badge-default">' . esc_html( $select_badge ) . '</button>';
		}

		$dropdown_html .= '</div>'; // .language-select-wrapper

		// Dropdown options list.
		$dropdown_html .= '<ul class="language-select-options">';
		foreach ( $select_options as $key => $label ) {
			$is_selected = ( $current_value === $key );
			$flag_image  = isset( $flag_images[ $key ] ) ? $flag_images[ $key ] : 'english.png';

			$dropdown_html .= '<li class="language-select-option' . ( $is_selected ? ' selected' : '' ) . '" data-value="' . esc_attr( $key ) . '" data-lang="' . esc_attr( $key ) . '">';
			$dropdown_html .= '<span class="language-select-option-flag">';
			$dropdown_html .= '<img src="' . esc_url( $plugin_url . $flag_image ) . '" alt="' . esc_attr( $key ) . '" />';
			$dropdown_html .= '</span>';
			$dropdown_html .= '<span class="language-select-option-label">' . esc_html( $label ) . '</span>';
			$dropdown_html .= '<span class="language-select-check-icon"><svg width="15" height="11" viewBox="0 0 15 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.1666 0.833344L4.99992 10L0.833252 5.83334" stroke="#0048FE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
			$dropdown_html .= '<button type="button" class="language-select-option-default-label badge-default">' . esc_html__( 'Default', 'accessibility-onetap' ) . '</button>';
			$dropdown_html .= '</li>';
		}
		$dropdown_html .= '</ul>';

		$dropdown_html .= '</div>'; // .language-select-dropdown
		$dropdown_html .= '</div>'; // .box

		$this->render_template( $args, $dropdown_html, 'language-select-simple' );
	}

	/**
	 * Callback template for language select with flags, country names, and toggle checkboxes.
	 * Similar to callback_template_language_select_with_actions but without action buttons.
	 *
	 * @param array $args Arguments for the template.
	 */
	public function callback_template_language_select_with_toggles( $args ) {
		$parsed_args    = $this->parse_template_args( $args );
		$select_options = isset( $args['select_options'] ) ? $args['select_options'] : array();
		$select_badge   = isset( $args['select_badge'] ) ? $args['select_badge'] : '';

		// Get current selected value - use raw value from get_option to avoid esc_attr issues.
		$raw_value     = $this->get_option( $args['id'], $args['section'], isset( $args['std'] ) ? $args['std'] : ( isset( $args['default'] ) ? $args['default'] : '' ) );
		$current_value = ! empty( $raw_value ) ? trim( $raw_value ) : '';

		// If still empty, use default from args.
		if ( empty( $current_value ) ) {
			if ( isset( $args['default'] ) && ! empty( $args['default'] ) ) {
				$current_value = trim( $args['default'] );
			} elseif ( isset( $args['std'] ) && ! empty( $args['std'] ) ) {
				$current_value = trim( $args['std'] );
			}
		}

		// Get current selected label with fallback.
		$current_label = '';
		if ( ! empty( $current_value ) && isset( $select_options[ $current_value ] ) ) {
			$current_label = $select_options[ $current_value ];
		} elseif ( ! empty( $current_value ) ) {
			// If value exists but not in options, try to find it (case-insensitive or with different format).
			foreach ( $select_options as $key => $label ) {
				if ( strtolower( trim( $key ) ) === strtolower( $current_value ) ) {
					$current_value = $key; // Normalize to the correct key.
					$current_label = $label;
					break;
				}
			}
		}

		// Final fallback: use default value if label is still empty.
		if ( empty( $current_label ) ) {
			// Try default first, then std.
			$fallback_value = '';
			if ( isset( $args['default'] ) && ! empty( $args['default'] ) ) {
				$fallback_value = trim( $args['default'] );
			} elseif ( isset( $args['std'] ) && ! empty( $args['std'] ) ) {
				$fallback_value = trim( $args['std'] );
			}

			if ( ! empty( $fallback_value ) && isset( $select_options[ $fallback_value ] ) ) {
				$current_value = $fallback_value;
				$current_label = $select_options[ $fallback_value ];
			} elseif ( ! empty( $select_options ) ) {
				// Last resort: use first available option.
				$first_key = key( $select_options );
				if ( $first_key ) {
					$current_value = $first_key;
					$current_label = $select_options[ $first_key ];
				}
			}
		}

		// Mapping language codes to flag image filenames.
		$flag_images = array(
			'en'    => 'english.png',
			'de'    => 'german.png',
			'es'    => 'spanish.png',
			'fr'    => 'french.png',
			'it'    => 'italia.png',
			'pl'    => 'poland.png',
			'se'    => 'swedish.png',
			'fi'    => 'finnland.png',
			'pt'    => 'portugal.png',
			'ro'    => 'rumania.png',
			'si'    => 'slowenien.png',
			'sk'    => 'slowakia.png',
			'nl'    => 'netherland.png',
			'dk'    => 'danish.png',
			'gr'    => 'greece.png',
			'cz'    => 'czech.png',
			'hu'    => 'hungarian.png',
			'lt'    => 'lithuanian.png',
			'lv'    => 'latvian.png',
			'ee'    => 'estonian.png',
			'hr'    => 'croatia.png',
			'ie'    => 'ireland.png',
			'bg'    => 'bulgarian.png',
			'no'    => 'norwegan.png',
			'tr'    => 'turkish.png',
			'id'    => 'indonesian.png',
			'pt-br' => 'brasilian.png',
			'ja'    => 'japanese.png',
			'ko'    => 'korean.png',
			'zh'    => 'chinese-simplified.png',
			'ar'    => 'arabic.png',
			'ru'    => 'russian.png',
			'hi'    => 'hindi.png',
			'uk'    => 'ukrainian.png',
			'sr'    => 'serbian.png',
			'gb'    => 'england.png',
			'ir'    => 'iran.png',
			'il'    => 'israel.png',
			'mk'    => 'macedonia.png',
			'th'    => 'thailand.png',
			'vn'    => 'vietnam.png',
			'bn'    => 'bangladesh.png',
			'tl'    => 'philippines.png',
			'ur'    => 'pakistan.png',
		);

		// Get plugin URL for flag images.
		$plugin_url = ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/';

		// Get current flag image.
		$current_flag = isset( $flag_images[ $current_value ] ) ? $flag_images[ $current_value ] : $flag_images['en'];

		// Build custom dropdown HTML.
		$dropdown_html  = '<div class="box box1">';
		$dropdown_html .= '<div class="language-select-dropdown ">';

		// Hidden input for form submission.
		$default_value  = isset( $args['default'] ) ? $args['default'] : '';
		$dropdown_html .= '<input type="hidden" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $current_value ) . '" class="language-select-input" data-default="' . esc_attr( $default_value ) . '" data-lang="' . esc_attr( $current_value ) . '"/>';

		// Labels wrapper.
		$dropdown_html .= '<div class="language-select-labels-wrapper">';
		$dropdown_html .= '<span class="language-select-label-default">' . esc_html__( 'Default Language', 'accessibility-onetap' ) . '</span>';
		$dropdown_html .= '<span class="language-select-label-display-options">' . esc_html__( 'Display Options', 'accessibility-onetap' ) . '</span>';
		$dropdown_html .= '</div>';

		// Dropdown wrapper.
		$dropdown_html .= '<div class="language-select-wrapper">';

		// Dropdown trigger button.
		$dropdown_html .= '<button type="button" class="language-select-trigger" aria-expanded="false" data-lang="' . esc_attr( $current_value ) . '">';
		$dropdown_html .= '<span class="language-select-selected">';
		$dropdown_html .= '<span class="language-select-flag">';
		$dropdown_html .= '<img src="' . esc_url( $plugin_url . $current_flag ) . '" alt="' . esc_attr( $current_value ) . '" />';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="language-select-label">' . esc_html( $current_label ) . '</span>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '<span class="language-select-chevron">';
		$dropdown_html .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="#A4A7AE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$dropdown_html .= '</span>';
		$dropdown_html .= '</button>';

		// Default button (separate from trigger).
		if ( $select_badge ) {
			$dropdown_html .= '<button type="button" class="language-label-default badge-default">' . esc_html( $select_badge ) . '</button>';
		}

		$dropdown_html .= '</div>'; // .language-select-wrapper

		// Get saved language toggle settings.
		$onetap_settings  = get_option( 'onetap_settings', array() );
		$language_toggles = isset( $onetap_settings['toggle-language'] ) && is_array( $onetap_settings['toggle-language'] ) ? $onetap_settings['toggle-language'] : array();

		// Check if toggle-language key exists (if not, means first time, default all ON).
		$is_first_time = ! isset( $onetap_settings['toggle-language'] );

		// If first time, set default all languages as ON (but don't save yet to avoid issues during rendering).
		if ( $is_first_time && ! empty( $select_options ) ) {
			$default_toggles = array();
			foreach ( $select_options as $key => $label ) {
				$default_toggles[ $key ] = 'on';
			}
			$language_toggles = $default_toggles;
		}

		// Dropdown options list.
		$dropdown_html .= '<ul class="language-select-options">';
		foreach ( $select_options as $key => $label ) {
			$is_selected = ( $current_value === $key );
			$flag_image  = isset( $flag_images[ $key ] ) ? $flag_images[ $key ] : 'english.png';
			$toggle_id   = 'onetap_settings[toggle-language-' . esc_attr( $key ) . ']';
			$toggle_name = 'onetap_settings[toggle-language][' . esc_attr( $key ) . ']';

			// Whether this language is offered, decided by the same rule the
			// toolbar reads. A language the admin has never been shown - one a
			// plugin update added - appears here switched off, waiting to be
			// chosen, rather than turning itself on across their site.
			$is_toggle_on = $is_first_time || Accessibility_Onetap_Config::is_language_enabled( $key, $language_toggles );
			$checked_attr = $is_toggle_on ? ' checked="checked"' : '';

			$dropdown_html .= '<li class="language-select-option' . ( $is_selected ? ' selected' : '' ) . '" data-value="' . esc_attr( $key ) . '" data-lang="' . esc_attr( $key ) . '">';
			$dropdown_html .= '<span class="language-select-option-flag">';
			$dropdown_html .= '<img src="' . esc_url( $plugin_url . $flag_image ) . '" alt="' . esc_attr( $key ) . '" />';
			$dropdown_html .= '</span>';
			$dropdown_html .= '<span class="language-select-option-label">' . esc_html( $label ) . '</span>';
			$dropdown_html .= '<span class="language-select-check-icon"><svg width="15" height="11" viewBox="0 0 15 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.1666 0.833344L4.99992 10L0.833252 5.83334" stroke="#0048FE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
			$dropdown_html .= '<button type="button" class="language-select-option-default-label badge-default">' . esc_html__( 'Default', 'accessibility-onetap' ) . '</button>';
			$dropdown_html .= '<div class="box-swich"><label class="switch"><input type="checkbox" id="' . esc_attr( $toggle_id ) . '" name="' . esc_attr( $toggle_name ) . '" value="on"' . $checked_attr . '><span class="slider round"></span></label></div>';
			$dropdown_html .= '</li>';
		}
		$dropdown_html .= '</ul>';

		// Language select actions (Disable All / Enable All buttons).
		$dropdown_html .= '<div class="language-select-actions">';
		$dropdown_html .= '<button type="button" class="button outline language-disable-all">' . esc_html__( 'Disable All', 'accessibility-onetap' ) . '</button>';
		$dropdown_html .= '<button type="button" class="button solid language-enable-all">' . esc_html__( 'Enable All', 'accessibility-onetap' ) . '</button>';
		$dropdown_html .= '</div>';

		$dropdown_html .= '</div>'; // .language-select-dropdown
		$dropdown_html .= '</div>'; // .box

		$this->render_template( $args, $dropdown_html, 'language-select-toggles' );
	}

	/**
	 * Callback template for image list with alt text management.
	 *
	 * @param array $args Arguments for the template.
	 */
	public function callback_template_image_alt_list( $args ) {
		$parsed_args         = $this->parse_template_args( $args );
		$parsed_args['type'] = isset( $args['type'] ) ? $args['type'] : 'alt-text';

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$allowed_filters = array( 'all', 'with', 'without' );
		$alt_filter      = isset( $_GET['alt_text_filter'] ) ? sanitize_key( wp_unslash( $_GET['alt_text_filter'] ) ) : 'all';
		if ( ! in_array( $alt_filter, $allowed_filters, true ) ) {
			$alt_filter = 'all';
		}

		$current_page = isset( $_GET['alt_text_page'] ) ? max( 1, intval( $_GET['alt_text_page'] ) ) : 1;
		$per_page     = 10; // Number of images per page.

		$total_images = $this->get_total_media_count( $alt_filter );
		$total_pages  = $total_images > 0 ? (int) ceil( $total_images / $per_page ) : 0;
		if ( $total_pages > 0 ) {
			$current_page = min( $current_page, $total_pages );
		} else {
			$current_page = 1;
		}
		$offset = ( $current_page - 1 ) * $per_page;

		$image_list = $this->get_media_library_images( $per_page, $offset, $alt_filter );

		$filter_links   = array();
		$filter_configs = array(
			'all'     => __( 'All', 'accessibility-onetap' ),
			'with'    => __( 'With Alt Text', 'accessibility-onetap' ),
			'without' => __( 'Without Alt Text', 'accessibility-onetap' ),
		);
		foreach ( $filter_configs as $key => $label ) {
			$is_active      = ( $key === $alt_filter );
			$filter_href    = add_query_arg(
				array(
					'alt_text_filter' => 'all' === $key ? false : $key,
					'alt_text_page'   => false,
				)
			);
			$link_class     = 'apop-alt-text-filter__link' . ( $is_active ? ' is-active' : '' );
			$aria_current   = $is_active ? ' aria-current="page"' : '';
			$filter_links[] = '<a class="' . esc_attr( $link_class ) . '" href="' . esc_url( $filter_href ) . '"' . $aria_current . '>' . esc_html( $label ) . '</a>';
		}
		$args['setting_header_after_title'] = '<div class="apop-alt-text-filter" role="group" aria-label="' . esc_attr__( 'Filter images by alt text', 'accessibility-onetap' ) . '">' . implode( '<span class="apop-alt-text-filter__sep"> | </span>', $filter_links ) . '</div>';

		// Append a Beta badge next to the "Edit Alt Text" title.
		$args['setting_title'] .= ' <span class="onetap-admin-beta-badge">' . esc_html__( 'Beta', 'accessibility-onetap' ) . '</span>';

		$apop_altpilot_ai_cta   = $this->get_altpilot_cta_link_attrs();
		$apop_altpilot_ai_extra = ( '_blank' === $apop_altpilot_ai_cta['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';

		// Build image list HTML with dynamic data.
		$list_html  = '<div class="box box1">';
		$list_html .= '<div class="box-image-alt">';

		// Table header.
		$list_html .= '<div class="table-header">';
		$list_html .= '<div class="row">';
		$list_html .= '<div class="table-heading col thumbnail">' . __( 'Thumbnail', 'accessibility-onetap' ) . '</div>';
		$list_html .= '<div class="table-heading col alt-text">' . __( 'Alt Text', 'accessibility-onetap' ) . '</div>';
		$list_html .= '<div class="table-heading col uploaded">' . __( 'Uploaded', 'accessibility-onetap' ) . '</div>';
		$list_html .= '<div class="table-heading col actions">' . __( 'Actions', 'accessibility-onetap' ) . '</div>';
		$list_html .= '</div>';
		$list_html .= '</div>';

		// Table body.
		$list_html .= '<div class="table-body">';

		if ( ! empty( $image_list ) ) {
			foreach ( $image_list as $image ) {
				$thumbnail_url = isset( $image['thumbnail'] ) ? $image['thumbnail'] : '';
				$alt_text      = isset( $image['alt_text'] ) ? $image['alt_text'] : '';
				$upload_date   = isset( $image['upload_date'] ) ? $image['upload_date'] : '';
				$image_id      = isset( $image['id'] ) ? $image['id'] : '';

				$list_html .= '<div class="row" data-image-id="' . esc_attr( $image_id ) . '">';

				// Thumbnail column.
				$list_html .= '<div class="col thumbnail">';
				if ( $thumbnail_url ) {
					$list_html .= '<div class="image-thumbnail" style="background-image: url(' . esc_url( $thumbnail_url ) . '); background-size: cover; background-position: center; background-repeat: no-repeat;" title="' . esc_attr( $alt_text ) . '"></div>';
				}
				$list_html .= '</div>';

				// Alt text column.
				$list_html .= '<div class="col alt-text">';
				if ( $alt_text && 'edit' !== $alt_text ) {
					$list_html .= '<span class="text">' . esc_html( $alt_text ) . '</span>';
				} elseif ( 'edit' === $alt_text ) {
					$list_html .= '<textarea placeholder="' . __( 'Enter a Alt-text...', 'accessibility-onetap' ) . '" data-image-id="' . esc_attr( $image_id ) . '"></textarea>';
				} else {
					$list_html .= '<span class="text">' . esc_html( $alt_text ) . '</span>';
				}
				$list_html .= '</div>';

				// Uploaded column.
				$list_html .= '<div class="col uploaded">';
				$list_html .= '<span class="upload-date">' . esc_html( $upload_date ) . '</span>';
				$list_html .= '</div>';

				// Actions column.
				$list_html .= '<div class="col actions">';
				$list_html .= '<div class="action-buttons">';

				// AI button (AltPilot admin when plugin active, else marketing site).
				$list_html .= '<a href="' . esc_url( $apop_altpilot_ai_cta['url'] ) . '"' . $apop_altpilot_ai_extra . ' class="button outline ai-btn" title="' . esc_attr__( 'Generate with AltPilot.ai', 'accessibility-onetap' ) . '" data-image-id="' . esc_attr( $image_id ) . '">';
				$list_html .= '<img src="' . ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/ai-generate.svg" alt="AltPilot.ai" />';
				$list_html .= '</a>';

				// Edit button.
				$list_html .= '<button type="button" class="button outline edit-btn" title="Edit alt text" data-image-id="' . esc_attr( $image_id ) . '">';
				$list_html .= '<img src="' . ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/pencil.svg" alt="edit" />';
				$list_html .= '</button>';

				// Save button.
				$list_html .= '<button type="button" class="hide button outline save-btn" title="Save alt text" data-image-id="' . esc_attr( $image_id ) . '">';
				$list_html .= '<img src="' . ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/save.svg" alt="save" />';
				$list_html .= '</button>';

				$list_html .= '</div>';
				$list_html .= '</div>';

				$list_html .= '</div>'; // End row.
			}
		} else {
			$empty_msg  = 'all' === $alt_filter
				? __( 'No images found in Media Library.', 'accessibility-onetap' )
				: __( 'No images match this filter.', 'accessibility-onetap' );
			$empty_hint = 'all' === $alt_filter
				? __( 'Upload images to your Media Library to start managing their alt text here.', 'accessibility-onetap' )
				: __( 'Try choosing a different filter to see more of your images.', 'accessibility-onetap' );

			$empty_image = ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/no-images-placeholder.svg';

			$list_html .= '<div class="row no-images">';
			$list_html .= '<div class="col empty-state">';
			$list_html .= '<div class="empty-state__media">';
			$list_html .= '<img class="empty-state__image" src="' . esc_url( $empty_image ) . '" alt="" />';
			$list_html .= '</div>';
			$list_html .= '<h3 class="empty-state__title">' . esc_html( $empty_msg ) . '</h3>';
			$list_html .= '<p class="empty-state__text">' . esc_html( $empty_hint ) . '</p>';
			$list_html .= '</div>';
			$list_html .= '</div>';
		}

		$list_html .= '</div>'; // End table-body.
		$list_html .= '</div>'; // End box-image-alt.

		// Pagination.
		if ( $total_pages > 1 ) {
			$list_html .= '<div class="box-navigation">';

			// Previous button.
			$list_html .= '<div class="col prev">';
			if ( $current_page > 1 ) {
				$prev_url   = add_query_arg( $this->get_alt_text_list_query_args( $current_page - 1, $alt_filter ) );
				$list_html .= '<a href="' . esc_url( $prev_url ) . '" class="button prev-btn">';
				$list_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none"><path d="M18.0279 11.4872H5.02222M5.02222 11.4872L11.5251 17.99M5.02222 11.4872L11.5251 4.98431" stroke="#fff" stroke-width="1.85796" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				$list_html .= __( 'Previous', 'accessibility-onetap' );
				$list_html .= '</a>';
			} else {
				$list_html .= '<a href="#" class="button disable prev-btn">';
				$list_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none"><path d="M18.0279 11.4872H5.02222M5.02222 11.4872L11.5251 17.99M5.02222 11.4872L11.5251 4.98431" stroke="#fff" stroke-width="1.85796" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				$list_html .= __( 'Previous', 'accessibility-onetap' );
				$list_html .= '</a>';
			}
			$list_html .= '</div>';

			// Page numbers.
			$list_html .= '<div class="col links">';
			$list_html .= '<ul>';

			// Show page numbers with ellipsis for large numbers.
			$start_page = max( 1, $current_page - 2 );
			$end_page   = min( $total_pages, $current_page + 2 );

			if ( $start_page > 1 ) {
				$list_html .= '<li><a href="' . esc_url( add_query_arg( $this->get_alt_text_list_query_args( 1, $alt_filter ) ) ) . '">1</a></li>';
				if ( $start_page > 2 ) {
					$list_html .= '<li>...</li>';
				}
			}

			for ( $i = $start_page; $i <= $end_page; $i++ ) {
				if ( $i === $current_page ) {
					$list_html .= '<li><a href="#" class="current">' . $i . '</a></li>';
				} else {
					$list_html .= '<li><a href="' . esc_url( add_query_arg( $this->get_alt_text_list_query_args( $i, $alt_filter ) ) ) . '">' . $i . '</a></li>';
				}
			}

			if ( $end_page < $total_pages ) {
				if ( $end_page < $total_pages - 1 ) {
					$list_html .= '<li>...</li>';
				}
				$list_html .= '<li><a href="' . esc_url( add_query_arg( $this->get_alt_text_list_query_args( $total_pages, $alt_filter ) ) ) . '">' . $total_pages . '</a></li>';
			}

			$list_html .= '</ul>';
			$list_html .= '</div>';

			// Next button.
			$list_html .= '<div class="col next">';
			if ( $current_page < $total_pages ) {
				$next_url   = add_query_arg( $this->get_alt_text_list_query_args( $current_page + 1, $alt_filter ) );
				$list_html .= '<a href="' . esc_url( $next_url ) . '" class="button next-btn">';
				$list_html .= __( 'Next', 'accessibility-onetap' );
				$list_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none"><path d="M4.97217 11.4872H17.9779M17.9779 11.4872L11.475 4.98431M17.9779 11.4872L11.475 17.99" stroke="#fff" stroke-width="1.85796" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				$list_html .= '</a>';
			} else {
				$list_html .= '<a href="#" class="button disable next-btn">';
				$list_html .= __( 'Next', 'accessibility-onetap' );
				$list_html .= '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 23 23" fill="none"><path d="M4.97217 11.4872H17.9779M17.9779 11.4872L11.475 4.98431M17.9779 11.4872L11.475 17.99" stroke="#fff" stroke-width="1.85796" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				$list_html .= '</a>';
			}
			$list_html .= '</div>';

			$list_html .= '</div>'; // End box-navigation.
		}

		$list_html .= '</div>'; // End box.

		$this->render_template( $args, $list_html, 'image-alt-list' );
	}

	/**
	 * AltPilot CTA: plugin settings in admin when active, otherwise marketing URL.
	 *
	 * @since 1.0.0
	 * @return array{url:string,target:string} target is empty for same-window admin links.
	 */
	public function get_altpilot_cta_link_attrs() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( 'altpilot/altpilot.php' ) ) {
			return array(
				'url'    => admin_url( 'admin.php?page=altpilot' ),
				'target' => '',
			);
		}
		return array(
			'url'    => self::ALTPILOT_SITE_URL,
			'target' => '_blank',
		);
	}

	/**
	 * Locate AltPilot among the installed plugins.
	 *
	 * The main file is looked up rather than assumed so that the button keeps
	 * working if AltPilot ever renames it; only the directory has to stay put,
	 * and that one is fixed by the repository slug.
	 *
	 * @since  2.13.1
	 * @return string Plugin file relative to the plugins directory, empty when not installed.
	 */
	private function get_altpilot_plugin_file() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( get_plugins() ) as $plugin_file ) {
			if ( 0 === strpos( $plugin_file, self::ALTPILOT_SLUG . '/' ) ) {
				return $plugin_file;
			}
		}

		return '';
	}

	/**
	 * Work out what the AltPilot button on the Alt Text screen should offer.
	 *
	 * The banner used to send everyone to altpilot.ai, which is a detour for
	 * anyone who simply does not have the plugin yet. AltPilot is hosted on the
	 * WordPress.org repository, so the install can be handed to the installer
	 * that ships with WordPress instead - OneTap never downloads anything
	 * itself. The button therefore follows the plugin through its three states:
	 * install, activate, then open.
	 *
	 * Every returned URL is a normal, nonce-protected admin link, so the button
	 * still works with JavaScript disabled; the script only upgrades the install
	 * step to happen without leaving the page.
	 *
	 * @since  2.13.1
	 * @return array {
	 *     @type string $state        One of install|activate|open|external.
	 *     @type string $label        Text for the button.
	 *     @type string $url          Where the button points.
	 *     @type string $target       Link target, empty unless the link leaves wp-admin.
	 *     @type string $slug         Repository slug, only on the install state.
	 *     @type string $busy_label   Label shown while installing, only on the install state.
	 *     @type string $done_label   Label shown once installed, only on the install state.
	 * }
	 */
	public function get_altpilot_banner_cta() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file = $this->get_altpilot_plugin_file();

		if ( $plugin_file && is_plugin_active( $plugin_file ) ) {
			return array(
				'state'  => 'open',
				'label'  => __( 'Generate with AI', 'accessibility-onetap' ),
				'url'    => admin_url( 'admin.php?page=' . self::ALTPILOT_SLUG ),
				'target' => '',
			);
		}

		if ( $plugin_file && current_user_can( 'activate_plugin', $plugin_file ) ) {
			return array(
				'state'  => 'activate',
				'label'  => __( 'Activate AltPilot', 'accessibility-onetap' ),
				'url'    => wp_nonce_url(
					self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin_file ) ),
					'activate-plugin_' . $plugin_file
				),
				'target' => '',
			);
		}

		if ( ! $plugin_file && current_user_can( 'install_plugins' ) ) {
			return array(
				'state'      => 'install',
				'label'      => __( 'Install AltPilot for free', 'accessibility-onetap' ),
				'url'        => wp_nonce_url(
					self_admin_url( 'update.php?action=install-plugin&plugin=' . self::ALTPILOT_SLUG ),
					'install-plugin_' . self::ALTPILOT_SLUG
				),
				'target'     => '',
				'slug'       => self::ALTPILOT_SLUG,
				'busy_label' => __( 'Installing…', 'accessibility-onetap' ),
				'done_label' => __( 'Activate AltPilot', 'accessibility-onetap' ),
			);
		}

		// Not installed and not allowed to install, or installed and not allowed
		// to activate: nothing useful can happen here, so point at the site.
		return array(
			'state'  => 'external',
			'label'  => __( 'Generate with AI', 'accessibility-onetap' ),
			'url'    => self::ALTPILOT_SITE_URL,
			'target' => '_blank',
		);
	}

	/**
	 * Build query arguments for alt-text list URLs (pagination + filter).
	 *
	 * @param int    $page   Page number (1-based).
	 * @param string $filter Filter key: all|with|without.
	 * @return array Arguments for add_query_arg().
	 */
	private function get_alt_text_list_query_args( $page, $filter ) {
		$args = array();
		if ( $page > 1 ) {
			$args['alt_text_page'] = $page;
		} else {
			$args['alt_text_page'] = false;
		}
		if ( 'all' !== $filter ) {
			$args['alt_text_filter'] = $filter;
		} else {
			$args['alt_text_filter'] = false;
		}
		return $args;
	}

	/**
	 * Get images from WordPress Media Library with pagination.
	 *
	 * @param int    $per_page Number of images per page.
	 * @param int    $offset   Offset for pagination.
	 * @param string $filter   Filter: all, with, or without (alt text present / missing).
	 * @return array Array of image data.
	 */
	private function get_media_library_images( $per_page = 10, $offset = 0, $filter = 'all' ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		);

		if ( 'without' === $filter ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_wp_attachment_image_alt',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_wp_attachment_image_alt',
					'value'   => '',
					'compare' => '=',
				),
			);
		} elseif ( 'with' === $filter ) {
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => '_wp_attachment_image_alt',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_wp_attachment_image_alt',
					'value'   => '',
					'compare' => '!=',
				),
			);
		}

		$query  = new WP_Query( $args );
		$images = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$attachment_id = get_the_ID();
				$attachment    = get_post( $attachment_id );

				// Get thumbnail URL.
				$thumbnail_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
				$thumbnail_url = $thumbnail_url ? $thumbnail_url[0] : '';

				// Get alt text.
				$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				if ( empty( $alt_text ) ) {
					$alt_text = '-';
				}

				// Get upload date.
				$upload_date = get_the_date( 'd M Y, H:i', $attachment_id );

				$images[] = array(
					'id'          => $attachment_id,
					'thumbnail'   => $thumbnail_url,
					'alt_text'    => $alt_text,
					'upload_date' => $upload_date,
				);
			}
			wp_reset_postdata();
		}

		return $images;
	}

	/**
	 * Get total count of images in Media Library.
	 *
	 * @param string $filter Filter: all, with, or without (alt text present / missing).
	 * @return int Total number of images.
	 */
	private function get_total_media_count( $filter = 'all' ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( 'without' === $filter ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_wp_attachment_image_alt',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_wp_attachment_image_alt',
					'value'   => '',
					'compare' => '=',
				),
			);
		} elseif ( 'with' === $filter ) {
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => '_wp_attachment_image_alt',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_wp_attachment_image_alt',
					'value'   => '',
					'compare' => '!=',
				),
			);
		}

		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Render a color picker field with predefined color options for settings forms.
	 *
	 * Outputs a color picker input with a list of predefined color options.
	 * Includes a color preview display and allows users to select from predefined colors
	 * or use the WordPress color picker for custom colors.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_color( $args ) {
		$parsed_args = $this->parse_template_args( $args );
		$color_list  = isset( $args['color_list'] ) ? $args['color_list'] : array();

		// Default color untuk fallback & tombol Clear (std dari config).
		$default_color = isset( $args['std'] ) ? $args['std'] : $parsed_args['value'];

		// Build color picker HTML.
		$color_html  = '<div class="box box1" style="--outline-color:' . esc_attr( $parsed_args['value'] ) . '" data-default-color="' . esc_attr( $default_color ) . '">';
		$color_html .= '<input type="text" class="color-picker-field" id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $parsed_args['value'] ) . '" />';
		$color_html .= '</div>';
		$color_html .= '<div class="box box2">';
		$color_html .= '<span class="color-result">' . esc_attr( $parsed_args['value'] ) . '</span>';
		$color_html .= '</div>';
		$color_html .= '<div class="box box3">';
		if ( ! empty( $color_list ) ) {
			$color_html .= '<ul>';
			foreach ( $color_list as $color ) {
				$color_html .= '<li data-color="' . esc_attr( $color ) . '" style="background: ' . esc_attr( $color ) . ';"></li>';
			}
			$color_html .= '</ul>';
		}
		$color_html .= '</div>';

		$this->render_template( $args, $color_html, 'color' );
	}

	/**
	 * Render a feature card for settings forms.
	 *
	 * Outputs a feature card with icon, description, PRO information, and action button.
	 * This template is used for displaying feature information with copyable text and edit functionality.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_feature_card( $args ) {
		$parsed_args            = $this->parse_template_args( $args );
		$parsed_args['type']    = isset( $args['type'] ) ? $args['type'] : 'feature_card';
		$parsed_args['classes'] = 'setting-control' . $parsed_args['first_control'] . ' ' . $parsed_args['last_control'] . ' ' . esc_attr( $parsed_args['type'] );

		// Build feature card content attributes.
		$feature_icon  = isset( $args['feature_icon'] ) ? esc_url( $args['feature_icon'] ) : '';
		$is_pro        = isset( $args['is_pro'] ) ? (bool) $args['is_pro'] : false;
		$feature_name  = isset( $args['feature_name'] ) ? esc_html( $args['feature_name'] ) : '';
		$feature_desc  = isset( $args['feature_desc'] ) ? esc_html( $args['feature_desc'] ) : '';
		$feature_style = isset( $args['feature_style'] ) ? esc_attr( $args['feature_style'] ) : '';

		// Build PRO feature information attributes.
		$pro_title     = isset( $args['pro_title'] ) ? esc_html( $args['pro_title'] ) : __( 'This is a PRO Feature', 'accessibility-onetap' );
		$pro_desc      = isset( $args['pro_desc'] ) ? esc_html( $args['pro_desc'] ) : __( ' to use this Feature.', 'accessibility-onetap' );
		$pro_link_text = isset( $args['pro_link_text'] ) ? esc_html( $args['pro_link_text'] ) : __( 'Upgrade to Pro', 'accessibility-onetap' );
		$upgrade_link  = isset( $args['upgrade_link'] ) ? esc_url( $args['upgrade_link'] ) : 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar';

		// Build button attributes.
		$button_text  = isset( $args['button_text'] ) ? esc_html( $args['button_text'] ) : '';
		$button_icon  = isset( $args['button_icon'] ) ? esc_url( $args['button_icon'] ) : '';
		$button_class = isset( $args['button_class'] ) ? esc_attr( $args['button_class'] ) : '';
		$button_link  = isset( $args['button_link'] ) ? esc_url( $args['button_link'] ) : '';

		$button_link_new_tab = true;
		if ( isset( $args['name'] ) && 'alt-text-ai' === $args['name'] ) {
			$altpilot_cta        = $this->get_altpilot_cta_link_attrs();
			$button_link         = esc_url( $altpilot_cta['url'] );
			$button_link_new_tab = ( '_blank' === $altpilot_cta['target'] );
		}

		// Check if label exists and is not empty, then add padding-top-0 class (except for style2 style).
		$label_class = ( isset( $args['label'] ) && ! empty( $args['label'] ) && 'style2' !== $feature_style ) ? ' padding-top-0' : '';

		// Build input HTML.
		$input_html = '<div class="box box1' . ( ! empty( $feature_style ) ? ' ' . $feature_style : '' ) . ( $is_pro ? ' pro' : '' ) . $label_class . '">';

		$input_html .= '<div class="left">';

		// Icon section - only show if feature_icon is provided.
		if ( ! empty( $feature_icon ) ) {
			$input_html .= '<div class="icon">';
			$input_html .= '<img src="' . $feature_icon . '" alt="icon">';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="text">';

		// Display feature name - only show if feature_name exists (regardless of is_pro status).
		if ( ! empty( $feature_name ) ) {
			$input_html .= '<span class="feature-name">' . $feature_name . ( $is_pro ? $this->get_pro_badge() : '' ) . '</span>';
		}

		// Feature description - only show if feature_desc is provided (regardless of is_pro status).
		if ( ! empty( $feature_desc ) ) {
			$input_html .= '<span class="feature-desc">' . $feature_desc . '</span>';
		}

		$input_html .= '</div>';

		$input_html .= '</div>';

		$input_html .= '<div class="right">';

		// Show PRO feature information if is_pro is true.
		if ( $is_pro ) {
			$input_html .= '<div class="pro-info">';
			$input_html .= '<h4 class="pro-title">' . $pro_title . '</h4>';
			$input_html .= '<p class="pro-desc">';
			$input_html .= '<a target="_blank" href="' . $upgrade_link . '" class="upgrade-link">' . $pro_link_text . '</a> ' . $pro_desc;
			$input_html .= '</p>';
			$input_html .= '</div>';
		}

		$input_html .= '<div class="box-button-link">';

		// Build button/link element based on whether link is provided.
		if ( ! empty( $button_link ) ) {
			$link_target = $button_link_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
			$input_html .= '<a href="' . $button_link . '"' . $link_target . ( ! empty( $button_class ) ? ' class="button outline ' . $button_class . '"' : ' class="button outline"' ) . '>';
		} else {
			// Create button if no link provided.
			$input_html .= '<button' . ( ! empty( $button_class ) ? ' class="button outline ' . $button_class . '"' : ' class="button outline"' ) . '>';
		}

		// Add icon if provided.
		if ( ! empty( $button_icon ) ) {
			$input_html .= '<img src="' . $button_icon . '" alt="button icon" class="button-icon">';
		}

		$input_html .= $button_text;

		// Close the appropriate element.
		if ( ! empty( $button_link ) ) {
			$input_html .= '</a>';
		} else {
			$input_html .= '</button>';
		}

		$input_html .= '</div>';

		$input_html .= '</div>';

		$this->render_template( $args, $input_html, 'feature_card' );
	}

	/**
	 * Render a license key input field with status display and activation controls.
	 *
	 * Outputs a license key input field with status indicator and activation/deactivation button.
	 * Includes license status display and proper validation attributes.
	 *
	 * @param array $args settings field args.
	 */
	public function callback_template_license( $args ) {
		$parsed_args = $this->parse_template_args( $args );

		// Get license status and configuration.
		$license_status = $this->get_license_status_config();

		// Build license input HTML.
		$html = $this->build_license_input_html( $args, $parsed_args, $license_status );

		$this->render_template( $args, $html, 'text' );
	}

	/**
	 * Get license status configuration.
	 *
	 * @return array License status configuration.
	 */
	private function get_license_status_config() {
		$status = get_option( 'onetap_license_status' );

		if ( $status ) {
			return array(
				'label'       => __( 'Active', 'accessibility-onetap' ),
				'button_text' => __( 'Deactivate License', 'accessibility-onetap' ),
				'status'      => 1,
				'readonly'    => ' readonly="readonly"',
			);
		}

		return array(
			'label'       => __( 'Inactive', 'accessibility-onetap' ),
			'button_text' => __( 'Activate License', 'accessibility-onetap' ),
			'status'      => 0,
			'readonly'    => '',
		);
	}

	/**
	 * Build license input HTML.
	 *
	 * @param array $args Settings arguments.
	 * @param array $parsed_args Parsed arguments.
	 * @param array $license_status License status configuration.
	 * @return string HTML output.
	 */
	private function build_license_input_html( $args, $parsed_args, $license_status ) {

		$html = '<div class="box box1">';

		// Compute readonly based on label being exactly "Active".
		$label_text    = isset( $license_status['label'] ) ? (string) $license_status['label'] : '';
		$is_active     = ( '' !== $label_text && 'Active' === $label_text );
		$readonly_attr = $is_active ? ' readonly="readonly"' : '';

		// License status label.
		$html .= '<span class="license-status-label" license-status="' . esc_attr( $license_status['status'] ) . '">' . esc_html( $license_status['label'] ) . '</span>';

		// License input field.
		$html .= '<input 
			type="text" 
			id="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']"
			name="' . esc_attr( $args['section'] ) . '[' . esc_attr( $args['id'] ) . ']"
			value="' . esc_attr( $parsed_args['value'] ) . '"
			placeholder="XXXXX-XXXXX-XXXXX-XXXXX"
			license-status="' . esc_attr( $license_status['status'] ) . '"
			license-key="' . esc_attr( $parsed_args['value'] ) . '"'
			. $readonly_attr
			. '/>';

		// License activation button.
		$button_class = 'button primary save-changes license';
		if ( $license_status['status'] ) {
			$button_class .= ' license-active';
		}

		$html .= '<button class="' . esc_attr( $button_class ) . '">' . esc_html( $license_status['button_text'] ) . '</button>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Sanitize callback for Settings API.
	 *
	 * @param object $options sanitize options.
	 */
	public function sanitize_options( $options ) {

		if ( ! $options ) {
			$options = array();
		}

		// Handle toggle-language array: ensure all available languages are included.
		$all_languages = $this->get_all_available_languages();

		if ( ! empty( $all_languages ) ) {
			// Get existing settings.
			$existing_settings = get_option( 'onetap_settings', array() );
			$existing_toggles  = isset( $existing_settings['toggle-language'] ) && is_array( $existing_settings['toggle-language'] )
				? $existing_settings['toggle-language']
				: array();

			// Get toggle-language from form submission (if any).
			// Checkboxes that are unchecked are not sent in form submission, so we need to handle this.
			$form_toggles = isset( $options['toggle-language'] ) && is_array( $options['toggle-language'] )
				? $options['toggle-language']
				: array();

			// Check if this is first time (no existing data).
			$is_first_time = empty( $existing_toggles );

			// Check if form was submitted with toggle-language data.
			// If form_toggles is not empty, it means user interacted with language toggles.
			$has_toggle_submission = ! empty( $form_toggles );

			// Process toggle-language based on different scenarios.
			$merged_toggles = array();

			// SCENARIO ANALYSIS:.
			// 1. Data kosong / Fresh install: Default all 'on'.
			// 2. Data sebelumnya ada + form tanpa toggle data: Preserve existing.
			// 3. Form dengan toggle data (Enable All): All checked = all 'on'.
			// 4. Form dengan toggle data (Disable All): Only default checked = others 'off'.
			// 5. Form dengan toggle data (Enable Some): Checked = 'on', unchecked = 'off'.

			foreach ( $all_languages as $lang_code ) {
				if ( isset( $form_toggles[ $lang_code ] ) ) {
					// SCENARIO: Checkbox is checked (in form submission).
					// Cases: Enable All, Enable Some, etc.
					$sanitized_value              = sanitize_text_field( $form_toggles[ $lang_code ] );
					$merged_toggles[ $lang_code ] = ( 'on' === $sanitized_value ) ? 'on' : 'off';
				} elseif ( $has_toggle_submission ) {
					// SCENARIO: Form was submitted with toggle data, but this checkbox is NOT in it (was unchecked).
					// Cases: Disable All, Enable Some (where this one is off), etc.
					// When user interacts with toggles and this one is not checked, it means 'off'.
					$merged_toggles[ $lang_code ] = 'off';
				} elseif ( ! $is_first_time ) {
					// SCENARIO: Not first time, but form was submitted WITHOUT toggle-language data.
					// This means user submitted form but didn't interact with language toggles.
					// Preserve existing value (don't change anything).
					$merged_toggles[ $lang_code ] = isset( $existing_toggles[ $lang_code ] ) && 'on' === $existing_toggles[ $lang_code ] ? 'on' : 'off';
				} else {
					// SCENARIO: First time, no toggle submission, no existing data.
					// Default to 'on' for initial display.
					$merged_toggles[ $lang_code ] = 'on';
				}
			}

			// Safety check: If first time and no toggle submission, ensure all are set to 'on' (default).
			// This handles: Data kosong, Fresh install scenarios.
			if ( $is_first_time && ! $has_toggle_submission ) {
				foreach ( $all_languages as $lang_code ) {
					if ( ! isset( $merged_toggles[ $lang_code ] ) ) {
						$merged_toggles[ $lang_code ] = 'on';
					}
				}
			}

			$options['toggle-language'] = $merged_toggles;
		} elseif ( isset( $existing_settings['toggle-language'] ) && is_array( $existing_settings['toggle-language'] ) && ! isset( $options['toggle-language'] ) ) {
			// If no languages found, preserve existing toggle-language if it exists.
			$options['toggle-language'] = $existing_settings['toggle-language'];
		}

		// Get all registered switch fields to check for missing checkboxes.
		$all_switch_fields = array();
		foreach ( $this->settings_fields as $section => $section_options ) {
			foreach ( $section_options as $option ) {
				if ( isset( $option['type'] ) && 'switch' === $option['type'] ) {
					$all_switch_fields[ $option['name'] ] = $option;
				}
			}
		}

		// Handle missing checkbox values (set to 'off' if not present).
		foreach ( $all_switch_fields as $field_name => $field_config ) {
			if ( ! isset( $options[ $field_name ] ) ) {
				$options[ $field_name ] = 'off';
			}
		}

		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it.
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				continue;
			}
		}

		return $options;
	}

	/**
	 * Get sanitization callback for given option slug.
	 *
	 * @param string $slug option slug.
	 *
	 * @return mixed string or bool false.
	 */
	public function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback.
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] !== $slug ) {
					continue;
				}

				// Return the callback name.
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	/**
	 * Get the value of a settings field.
	 *
	 * @param string $option  settings field name.
	 * @param string $section the section name this field belongs to.
	 * @param string $default_value default value text if it's not found.
	 * @return string
	 */
	public function get_option( $option, $section, $default_value = '' ) {

		$options = get_option( $section );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		// For switch/checkbox type fields, return 'off' as default if no default is specified.
		if ( empty( $default_value ) ) {
			// Check if this is a switch field.
			foreach ( $this->settings_fields as $section_name => $section_options ) {
				foreach ( $section_options as $field ) {
					if ( $field['name'] === $option && isset( $field['type'] ) && 'switch' === $field['type'] ) {
						return 'off';
					}
				}
			}
		}

		return $default_value;
	}

	/**
	 * Show navigations as tab.
	 *
	 * Shows all the settings section labels as tab.
	 */
	public function show_navigation() {
		$html = '<h2 class="nav-tab-wrapper">';

		$count = count( $this->settings_sections );

		// Don't show the navigation if only one section exists.
		if ( 1 === $count ) {
			return;
		}

		foreach ( $this->settings_sections as $tab ) {
			$html .= '<a href="' . esc_url( '#' . $tab['id'] ) . '" class="nav-tab" id="' . esc_attr( $tab['id'] ) . '-tab">' . esc_html( $tab['title'] ) . '</a>';
		}

		$html .= '</h2>';

		echo wp_kses(
			$html,
			array(
				'h2' => array(),
				'a'  => array(
					'href'  => array(),
					'class' => array(),
					'id'    => array(),
				),
			)
		);
	}

	/**
	 * Show the section settings forms.
	 *
	 * This function displays every sections in a different form.
	 */
	public function show_forms() {
		?>
		<div class="options-wrapper">
			<?php foreach ( $this->settings_sections as $form ) { ?>
				<div id="<?php echo esc_attr( $form['id'] ); ?>" class="group" style="display: none;">
					<form method="post" action="options.php">
						<?php
						do_action( 'accessibility_plugin_onetap_pro_settings_manager_form_top_' . $form['id'], $form );
						settings_fields( $form['id'] );
						do_settings_sections( $form['id'] );
						do_action( 'accessibility_plugin_onetap_pro_settings_manager_form_bottom_' . $form['id'], $form );
						if ( isset( $this->settings_fields[ $form['id'] ] ) ) :
							?>
						<div class="submit-button">
							<?php submit_button(); ?>
						</div>
						<?php endif; ?>
					</form>
				</div>
			<?php } ?>
		</div>
		<?php
	}


	/**
	 * Get all available language codes from settings fields.
	 *
	 * @return array Array of language codes.
	 */
	protected function get_all_available_languages() {
		$all_languages = array();

		// Find the language field in settings fields.
		foreach ( $this->settings_fields as $section => $section_options ) {
			foreach ( $section_options as $option ) {
				if ( isset( $option['name'] ) && 'language' === $option['name'] && isset( $option['select_options'] ) && is_array( $option['select_options'] ) ) {
					$all_languages = array_keys( $option['select_options'] );
					break 2; // Break out of both loops.
				}
			}
		}

		// Fallback: if settings_fields not yet initialized, get from options class directly.
		if ( empty( $all_languages ) ) {
			// Try to get from Onetap_Pro_Accessibility_Settings_Options if available.
			if ( class_exists( 'Onetap_Pro_Accessibility_Settings_Options' ) ) {
				$options_instance = new Onetap_Pro_Accessibility_Settings_Options();
				$fields           = $options_instance->get_settings_api_fields();

				// Find language field.
				if ( isset( $fields['onetap_settings'] ) ) {
					foreach ( $fields['onetap_settings'] as $field ) {
						if ( isset( $field['name'] ) && 'language' === $field['name'] && isset( $field['select_options'] ) && is_array( $field['select_options'] ) ) {
							$all_languages = array_keys( $field['select_options'] );
							break;
						}
					}
				}
			}
		}

		return $all_languages;
	}
}