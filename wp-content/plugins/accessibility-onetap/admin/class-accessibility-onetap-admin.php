<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wponetap.com
 * @since      1.0.0
 *
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/admin
 * @author     OneTap <support@wponetap.com>
 */
class Accessibility_Onetap_Admin {

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
	 * The option group name used for registering accessibility status settings.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $accessibility_status_option_group    Option group ID for accessibility status settings.
	 */
	protected $accessibility_status_option_group = 'options_group_onetap_accessibility_status';

	/**
	 * The list of allowed languages for accessibility status settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @const    array     ALLOWED_LANGUAGES    Array of allowed language codes.
	 */
	const ALLOWED_LANGUAGES = array(
		'en',
		'de',
		'es',
		'fr',
		'it',
		'pl',
		'se',
		'fi',
		'pt',
		'ro',
		'si',
		'sk',
		'nl',
		'dk',
		'gr',
		'cz',
		'hu',
		'lt',
		'lv',
		'ee',
		'hr',
		'ie',
		'bg',
		'no',
		'tr',
		'id',
		'pt-br',
		'ja',
		'ko',
		'zh',
		'ar',
		'ru',
		'hi',
		'uk',
		'sr',
		'gb',
		'ir',
		'il',
		'mk',
		'th',
		'vn',
		'bn',
		'tl',
		'ur',
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @param string $hook The current admin page hook suffix passed by WordPress.
	 */
	public function enqueue_styles( $hook ) {

		if ( 'toplevel_page_accessibility-onetap-settings' === $hook ||
			'onetap_page_accessibility-onetap-general-settings' === $hook ||
			'onetap_page_accessibility-onetap-alt-text' === $hook ||
			'onetap_page_accessibility-onetap-modules' === $hook ||
			'admin_page_onetap-module-labels' === $hook ||
			'onetap_page_accessibility-onetap-accessibility-status' === $hook
		) {

			// Enqueue the main admin CSS for the plugin.
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/accessibility-onetap-admin.css', array(), $this->version, 'all' );

			// Enqueue Google Fonts (Inter) for the admin pages of the plugin.
			wp_enqueue_style(
				'onetap-google-fonts',
				'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap',
				array(),
				$this->version,
				'all'
			);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @param string $hook The current admin page hook suffix passed by WordPress.
	 */
	public function enqueue_scripts( $hook ) {

		// Both localize calls below expose the same `adminLocalize` JS object, and the
		// last one printed wins - so every one of them has to carry these strings.
		$i18n = array(
			'warning'               => __( 'Warning!', 'accessibility-onetap' ),
			'completeAllFields'     => __( 'Please complete all fields.', 'accessibility-onetap' ),
			'altPilotInstallFailed' => __( 'AltPilot could not be installed. Please try again from Plugins → Add New.', 'accessibility-onetap' ),
		);

		if ( 'toplevel_page_accessibility-onetap-settings' === $hook ||
			'onetap_page_accessibility-onetap-general-settings' === $hook ||
			'onetap_page_accessibility-onetap-alt-text' === $hook ||
			'onetap_page_accessibility-onetap-modules' === $hook ||
			'admin_page_onetap-module-labels' === $hook ||
			'onetap_page_accessibility-onetap-accessibility-status' === $hook
		) {
			wp_enqueue_script( $this->plugin_name, ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/js/admin-menu.min.js', array( 'jquery' ), $this->version, true );

			wp_enqueue_script( $this->plugin_name . '-sweetalert', ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/js/sweetalert.min.js', array(), $this->version, true );

			// Get plugin settings.
			$plugin_settings = get_option( 'onetap_settings' );

			// Get active language.
			$language = isset( $plugin_settings['language'] ) ? $plugin_settings['language'] : 'en';

			// Localize admin.
			wp_localize_script(
				$this->plugin_name,
				'adminLocalize',
				array(
					'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
					'ajaxNonce'       => wp_create_nonce( 'onetap-ajax-verification' ),
					'adminUrl'        => esc_url( admin_url() ),
					'pluginUrl'       => ACCESSIBILITY_ONETAP_PLUGINS_URL,
					'activeLanguage'  => $language,
					'localizedLabels' => get_option( 'apop_localized_labels' ),
					'i18n'            => $i18n,
				)
			);
		}

		// The Alt Text screen offers a one-click AltPilot install, which is run by
		// the updater that ships with WordPress. Loading its script here is what
		// keeps the install on the page instead of sending the admin to
		// update.php; without it the button falls back to that screen.
		if ( 'onetap_page_accessibility-onetap-alt-text' === $hook && current_user_can( 'install_plugins' ) ) {
			wp_enqueue_script( 'updates' );
		}

		wp_enqueue_script( $this->plugin_name . '-admin-global', ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/js/admin-global.min.js', array( 'jquery' ), $this->version, true );

		// Localize admin-global script for all admin pages.
		wp_localize_script(
			$this->plugin_name . '-admin-global',
			'adminLocalize',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'ajaxNonce'       => wp_create_nonce( 'onetap-ajax-verification' ),
				'adminUrl'        => esc_url( admin_url() ),
				'pluginUrl'       => ACCESSIBILITY_ONETAP_PLUGINS_URL,
				'localizedLabels' => get_option( 'apop_localized_labels' ),
				'i18n'            => $i18n,
			)
		);
	}

	/**
	 * Display review banner.
	 *
	 * Shown only on the Dashboard (Home) submenu. Shows a review banner with three action buttons:
	 * - "Leave a Review": Redirects to review page and permanently disables banner
	 * - "Maybe later": Dismisses banner and sets next show date to +14 days
	 * - "Don't show again": Dismisses banner and permanently disables it
	 *
	 * The banner respects existing database options and will not reset on plugin updates.
	 */
	public function display_review_banner() {
		// Show only on Dashboard (Home) submenu.
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return;
		}

		// Ensure the plugin has been in use for at least 14 days.
		$install_timestamp = (int) get_option( 'onetap_install_timestamp', 0 );
		if ( ! $install_timestamp ) {
			return;
		}

		$current_time = time();
		if ( ( $current_time - $install_timestamp ) < ( 14 * DAY_IN_SECONDS ) ) {
			return;
		}

		// Check if banner is permanently disabled (kill-switch).
		$banner_disabled = get_option( 'onetap_review_banner_disabled', false );
		if ( $banner_disabled ) {
			return;
		}

		// Check if we should show the banner based on next show date.
		$next_show_date = get_option( 'onetap_review_banner_next_show', '' );
		if ( ! empty( $next_show_date ) ) {
			$show_timestamp = strtotime( $next_show_date );
			if ( $current_time < $show_timestamp ) {
				return; // Not time to show yet.
			}
		}

		?>
		<div class="notice notice-onetap-review notice-info is-dismissible" id="onetap-review-banner" style="display: none;">
			<div class="onetap-review-notice onetap-review-notice--dismissible">
				<div class="onetap-review-notice__content">
					<div class="onetap-review-icon">
						<?php echo '<img  src="' . esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/global/icon.png' ) . '" alt="toggle icon" />'; ?>
					</div>
					<h3>
						<?php esc_html_e( 'Enjoying OneTap? If it makes your work easier, we’d love to get a quick review on WordPress.org – it would mean a lot to us and helps us keep improving the plugin for you ', 'accessibility-onetap' ); ?>
					</h3>
				</div>
				<div class="onetap-review-notice__actions">
					<button id="onetap-review-dont-show" type="button" class="onetap-review-button onetap-review-button-link button outline">
						<?php esc_html_e( 'Don\'t show again', 'accessibility-onetap' ); ?>
					</button>					
					<button id="onetap-review-maybe-later" type="button" class="onetap-review-button onetap-review-button-link button outline">
						<?php esc_html_e( 'Maybe later', 'accessibility-onetap' ); ?>
					</button>
					<a id="onetap-review-leave-review" href="https://wordpress.org/support/plugin/accessibility-onetap/reviews/#new-post" target="_blank" class="onetap-review-button onetap-review-button-primary button fake-save-changes">
						<?php esc_html_e( 'Leave a Review', 'accessibility-onetap' ); ?>
					</a>					
				</div>
			</div>
		</div>
		<style>
		.notice.notice-onetap-review {
			margin: 0 !important;
			padding: 12px !important;
			border: 1px solid #00000014 !important;
			max-width: 100% !important;
			background: #fff !important;
			border-radius: 12px !important;
			box-shadow: none !important;
			overflow: hidden !important;
			display: none !important;
		}

		.notice.notice-onetap-review .button.outline,
		.notice.notice-onetap-review .buttons.button.outline,
		.notice.notice-onetap-review .wp-core-ui .button.outline {
			border: none !important;
			color: #1f2937 !important;
			padding: 10px 14px !important;
			font-weight: 500 !important;
			border-radius: 10px !important;
			box-shadow:
				0 0 0 1px #e5e7eb inset,
				0 -2px 0 rgba(10,13,18,0.05) inset,
				0 1px 2px rgba(10,13,18,0.05) !important;

			min-height: 30px !important;
			background: 0 0 !important;
			font-size: 14px !important;
			margin: 0 !important;
			text-decoration: none !important;
			display: inline-block !important;
			height: 40px !important;		
			line-height: inherit !important;		
		}

		/* Outline button – hover & focus */
		.notice.notice-onetap-review .button.outline:hover,
		.notice.notice-onetap-review .buttons.button.outline:hover,
		.notice.notice-onetap-review .wp-core-ui .button.outline:hover,
		.notice.notice-onetap-review .button.outline:focus,
		.notice.notice-onetap-review .buttons.button.outline:focus,
		.notice.notice-onetap-review .wp-core-ui .button.outline:focus {
			box-shadow:
				0 0 0 1px #d1d5db inset,
				0 -2px 0 rgba(10,13,18,0.05) inset,
				0 1px 2px rgba(10,13,18,0.05);
			background: #f9fafb !important;
		} 

		.notice.notice-onetap-review .fake-save-changes {
			cursor: pointer !important;
			border: 2px solid rgba(255, 255, 255, 0.12) !important;
			border: none !important;
			background: #0048FE !important;
			color: #fff !important;
			font-size: 14px !important;
			line-height: 20px !important;
			outline: none !important;
			margin: 0 !important;
			font-weight: 500 !important;
			padding: 10px 14px !important;
			border-radius: 10px !important;
			box-shadow:
				0 0 0 1px #0048fe inset,
				0 -2px 0 rgba(10,13,18,0.05) inset,
				0 1px 2px rgba(10,13,18,0.05) !important;
			height: 40px !important;
		}

		.notice.notice-onetap-review .fake-save-changes:hover {
			background: #023FDC !important;
		}

		.notice.notice-onetap-review .fake-save-changes:active {
			background: #003ACB !important;
		}

		.notice.notice-onetap-review .notice-dismiss {
			display: none !important;
		}

		.notice.notice-onetap-review .onetap-review-notice {
			display: flex !important;
			justify-content: space-between !important;
			gap: 16px !important;
		}

		.notice.notice-onetap-review .onetap-review-notice .onetap-review-notice__content {
			display: flex !important;
			align-items: center !important;
			gap: 16px !important;
		}

		.onetap-review-icon {
			display: flex !important;
			flex-wrap: wrap !important;
			align-items: center !important;
		}

		.notice.notice-onetap-review .onetap-review-notice .onetap-review-notice__content .onetap-review-icon img {
			width: 36px !important;
			height: auto !important;
		}

		.notice.notice-onetap-review .onetap-review-notice .onetap-review-notice__content h3 {
			font-size: 14px !important;
			line-height: 20px !important;
			font-weight: 400 !important;
			color: #181D27 !important;
			margin: 0 !important;
			padding: 0 !important;
		}

		.notice.notice-onetap-review .onetap-review-notice .onetap-review-notice__content h3 span {
			font-weight: 500 !important;
		}

		.notice.notice-onetap-review .onetap-review-notice .onetap-review-notice__actions {
			display: flex !important;
			justify-content: space-between !important;
			align-items: center !important;
			gap: 12px !important;
		}
		@media only screen and (max-width: 998px) {
			.notice.notice-onetap-review .onetap-review-notice {
				flex-wrap: wrap !important;
			}
		}

		@media only screen and (max-width: 480px) {
			.notice.notice-onetap-review .onetap-review-notice .onetap-review-notice__actions {
				flex-direction: column !important;
				justify-content: left !important;
				align-items: flex-start !important;
			}
		}

		</style>
		<script>
		(function() {
			// Show banner after 1 second delay.
			setTimeout(function() {
				var banner = document.getElementById('onetap-review-banner');
				if (banner) {
					banner.style.setProperty('display', 'block', 'important');
				}
			}, 1500);
		})();
		</script>
		<?php
	}

	/**
	 * AJAX handler for "Leave a Review" action.
	 *
	 * Sets a permanent flag to disable the banner forever.
	 * This acts as a kill-switch and will not be reset on plugin updates.
	 */
	public function ajax_handle_leave_review() {
		// Security check: Ensure nonce is valid.
		check_ajax_referer( 'onetap-ajax-verification', 'mynonce' );

		// Check if already disabled - respect existing value (update-proof).
		$existing_value = get_option( 'onetap_review_banner_disabled', false );
		if ( ! $existing_value ) {
			// Only update if not already set - prevents reset on updates.
			update_option( 'onetap_review_banner_disabled', true );
		}

		// Terminate the AJAX request.
		wp_send_json( array( 'success' => true ) );
		wp_die();
	}

	/**
	 * AJAX handler for "Maybe later" action.
	 *
	 * Sets the next show date to +14 days from today.
	 * This value is preserved and checked before showing the banner.
	 */
	public function ajax_handle_maybe_later() {
		// Security check: Ensure nonce is valid.
		check_ajax_referer( 'onetap-ajax-verification', 'mynonce' );

		// Calculate next show date: +14 days from today.
		$next_show_date = gmdate( 'Y-m-d H:i:s', strtotime( '+14 days' ) );

		// Update the next show date option.
		// This will be checked in display_review_banner() before showing.
		update_option( 'onetap_review_banner_next_show', $next_show_date );

		// Terminate the AJAX request.
		wp_send_json( array( 'success' => true ) );
		wp_die();
	}

	/**
	 * AJAX handler for "Don't show again" action.
	 *
	 * Sets a permanent flag to disable the banner forever.
	 * This acts as a kill-switch and will not be reset on plugin updates.
	 */
	public function ajax_handle_dont_show_again() {
		// Security check: Ensure nonce is valid.
		check_ajax_referer( 'onetap-ajax-verification', 'mynonce' );

		// Check if already disabled - respect existing value (update-proof).
		$existing_value = get_option( 'onetap_review_banner_disabled', false );
		if ( ! $existing_value ) {
			// Only update if not already set - prevents reset on updates.
			update_option( 'onetap_review_banner_disabled', true );
		}

		// Terminate the AJAX request.
		wp_send_json( array( 'success' => true ) );
		wp_die();
	}

	/**
	 * Display admin notice.
	 */
	public function display_admin_notice() {
		$get_cookie_disable_admin_notices = isset( $_COOKIE['onetap_disable_admin_notices'] ) ? sanitize_key( $_COOKIE['onetap_disable_admin_notices'] ) : '';
		if ( 'disable' === $get_cookie_disable_admin_notices ) {
			return;
		}
		?>
		<div class="notice-success notice-onetap notice is-dismissible">
			<div class="box" style="display: none;">
				<div class="box-text">
					<p>
					<?php
					esc_html_e(
						"Hey!
					We’re happy that you’re using OneTap. We'd like to take this opportunity to kindly ask for a 5-star rating on WordPress.",
						'accessibility-onetap'
					);
					?>
						</p>
					<strong><?php esc_html_e( 'It would mean a lot to us.', 'accessibility-onetap' ); ?></strong>
				</div>
				<div class="box-btn">
					<a class="onetap-button" href="https://wordpress.org/support/plugin/accessibility-onetap/reviews/#new-post" target="_blank"><?php esc_html_e( 'Ok, you deserve it', 'accessibility-onetap' ); ?></a>
					<button class="outline already-did" type="button"><?php esc_html_e( 'Already did', 'accessibility-onetap' ); ?></button>
					<a class="onetap-button outline" href="https://wponetap.com/feedback" target="_blank"><?php esc_html_e( 'No, it’s not  good enough', 'accessibility-onetap' ); ?></a>
				</div>
			</div>

			<div class="otn-zya-notice otn-zya-notice--dismissible" data-notice_id="design_not_appearing">
				<i class="otn-zya-notice__dismiss" role="button" aria-label="Diesen Hinweis ausblenden." tabindex="0"></i>

				<div class="otn-zya-notice__aside-bg">
					<div class="otn-zya-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 283.5 283.5" width="20" height="20">
						<path d="M183.5 0H100C44.8 0 0 44.8 0 100v83.5c0 55.2 44.8 100 100 100h83.5c55.2 0 100-44.8 100-100V100c0-55.2-44.8-100-100-100Zm-55.2 61.5c19.6-14.9 41.7 7.2 26.8 26.8-19.6 14.9-41.7-7.2-26.8-26.8Zm80.7 46.7-42.9 9.7c-1.9.4-3.3 2-3.3 3.9 0 28.3 4.8 50.3 14.1 77.1l5.1 14.6c1.8 5.4-1.1 11.1-6.6 12.9-1.1.4 1 .5-3.4.5s-8.5-2.7-10-7L141.7 166l-20.3 53.9c-1.9 5.5-8.1 8.3-13.7 6.3-5.4-1.9-8.1-7.8-6.2-13.1l5-14.3c9.4-26.8 14.1-48.8 14.1-77.1s-1.4-3.5-3.3-3.9l-42.9-9.7c-5.1-1-8.7-5.6-7.9-10.6.8-5.2 5.9-8.7 11.3-7.7l35.6 5.5c18.7 2.9 37.8 2.9 56.6 0l35.6-5.5c5.3-.9 10.4 2.6 11.3 7.7.8 5-2.8 9.6-7.9 10.6Z" fill="#0048fe"/>
					</svg>
					</div>
				</div>

				<div class="otn-zya-notice__content">
					<h3><?php esc_html_e( 'Hey! We’re happy that you’re using OneTap!', 'accessibility-onetap' ); ?></h3>
					<p><?php esc_html_e( 'If OneTap helped you make your site more accessible, we’d truly appreciate a quick review – a good rating would mean a lot to us ❤️', 'accessibility-onetap' ); ?></p>

					<a id="otn-link-rate-positive" href="https://wordpress.org/support/plugin/accessibility-onetap/reviews/#new-post" target="_blank">
						<?php esc_html_e( 'Ok, you deserve it', 'accessibility-onetap' ); ?>
					</a>
					<a id="otn-link-rate-done" href="#" class="already-did">
						<?php esc_html_e( 'Already did', 'accessibility-onetap' ); ?>
					</a>
					<a id="otn-link-rate-negative" href="https://wponetap.com/feedback" target="_blank">
						<?php esc_html_e( 'No, it’s not good enough', 'accessibility-onetap' ); ?>
					</a>
				</div>
			</div>
		</div>
		<style>
		.notice-onetap .otn-zya-notice {
			display: flex;
			position: relative;
			border: 1px solid #eee;
			border-left: 4px solid #0048FE;
			background-color: #fff;
			font-family: Arial, sans-serif;
			box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
		}

		.notice-onetap .otn-zya-notice__dismiss {
			position: absolute;
			top: 0.75em;
			right: 0.75em;
			cursor: pointer;
			font-size: 16px;
			color: #3f444b;
		}

		.notice-onetap .otn-zya-notice__aside-bg {
			background-color: #ECF1F7; /* gräuliches grün wie im Screenshot */
			padding: 1em;
			display: flex;
			align-items: flex-start;
			justify-content: center;
		}

		.notice-onetap .otn-zya-icon {
			background-color: #fff; /* z. B. Schwarz für dunklen Kreis */
			border-radius: 100%;

			display: flex;
			align-items: center;
			justify-content: center;
		}

		.notice-onetap .otn-zya-notice__content {
			background-color: #fff;
			padding: 1em;
			flex-grow: 1;
		}

		.notice-onetap .otn-zya-notice__content h3 {
			margin: 0 0 0.5em;
			font-size: 1rem;
			font-weight: 600;
			color: #1e1e1e;
		}

		.notice-onetap .otn-zya-notice__content p {
			font-size: .875rem !important;
			color: #3c434a;
			margin-bottom: 0.75rem;
		}

		.notice-onetap .otn-zya-notice__content a {
			color: 0048FE;
			text-decoration: underline;
			margin-bottom: 0.75rem;
		}

		.notice-onetap #otn-link-rate-positive {
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 2.15384615;
			min-height: 30px;
			margin: 0;
			padding: 0 10px;
			cursor: pointer;
			border-width: 1px;
			border-style: solid;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			box-sizing: border-box;
			color: #fff;
			border-color: #2271b1;
			background: #2271b1;
			vertical-align: top;
			margin-right: 0.5rem;
			font-weight: 400;
		}

		.notice-onetap #otn-link-rate-done {
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 2.15384615;
			min-height: 30px;
			margin: 0;
			padding: 0 10px;
			cursor: pointer;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			box-sizing: border-box;
			color: #2271b1;
			vertical-align: top;
			margin-right: 0rem;
			text-decoration: underline !important;
			font-weight: 400;
		}

		.notice-onetap #otn-link-rate-negative {
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 2.15384615;
			min-height: 30px;
			margin: 0;
			padding: 0 10px;
			cursor: pointer;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			box-sizing: border-box;
			color: #2271b1;
			vertical-align: top;
			margin-right: 0rem;
			text-decoration: underline !important;
			font-weight: 400;
		}

			.notice-onetap {
				padding: 0 !important;
				border: none;
				/* font-family: system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Noto Sans, Ubuntu, Cantarell, Helvetica Neue, Oxygen, Fira Sans, Droid Sans, sans-serif !important; */
			}

			.notice-onetap .box {
				background: #0048FE;
				border-radius: 8px;
				padding: 25px;
			}

			.notice-onetap .notice-dismiss:focus {
				box-shadow: none;
				outline: 0;
			}

			.notice-onetap .notice-dismiss::before {
				color: #FFFFFF;
			}

			.notice-onetap .box .box-text p,
			.notice-onetap .box .box-text strong {
				color: #FFFFFF;
				font-size: 16px;
				font-weight: 400;
				line-height: 1.5rem;
				padding: 0;
				margin: 0;
			}

			.notice-onetap .box .box-text strong {
				font-weight: 600;
			}

			.notice-onetap .box .box-btn {
				margin-top: 20px;
				display: flex;
				flex-wrap: wrap;
				gap: 10px;
			}

			.notice-onetap .box .box-btn button,
			.notice-onetap .box .box-btn a.onetap-button {
				font-size: 0.875rem;
				line-height: 1.25rem;
				fill: #0048FE;
				color: #0048FE;
				background-color: #FFFFFF;
				border-style: solid;
				border-width: 1px 1px 1px 1px;
				border-color: #FFFFFF;
				padding: 0.625rem 0.875rem 0.625rem 0.875rem;
				border-radius: 6px;
				font-weight: 500;
				display: inline-block;
				cursor: pointer;
				text-decoration: none;
			}

			.notice-onetap .box .box-btn button.outline,
			.notice-onetap .box .box-btn a.outline {
				fill: #FFFFFF;
				color: #FFFFFF;
				background: transparent;
			}
		</style>
		<?php
	}

	/**
	 * Callback function for AJAX to dismiss admin notices and set a cookie.
	 */
	public function dismiss_notice_ajax_callback() {
		// Security check: Ensure nonce is valid.
		check_ajax_referer( 'onetap-ajax-verification', 'mynonce' );

		// Set a cookie to disable admin notices for 5 days.
		setcookie( 'onetap_disable_admin_notices', 'disable', time() + ( 5 * 24 * 60 * 60 ) );

		// Terminate the AJAX request.
		wp_send_json( array( 'success' => true ) );
		wp_die();
	}

	/**
	 * Allow external domain for safe redirect.
	 *
	 * @param string[] $hosts Array of allowed hosts.
	 * @return string[] Modified array with external host.
	 */
	public function onetap_allow_external_redirect_host( $hosts ) {
		$hosts[] = 'wponetap.com';
		return $hosts;
	}

	/**
	 * Redirect users accessing a specific admin page to an external pricing URL.
	 *
	 * This function runs on 'admin_init' and checks whether the current admin page
	 * matches a specific plugin slug.
	 *
	 * @return void
	 */
	public function onetap_redirect_admin_page_to_pricing() {
		global $plugin_page, $pagenow;

		// Check if we are in the admin and on admin.php.
		if ( is_admin() && 'admin.php' === $pagenow && 'accessibility-onetap-pro' === $plugin_page ) {
			// Redirect.
			wp_safe_redirect( 'https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar', 301 );
			exit;
		}
	}

	/**
	 * Add a custom "Settings" link below the plugin description metadata
	 * (next to "Version | By | View details") in the Plugins admin screen.
	 *
	 * This function hooks into 'plugin_row_meta' and adds a link to the plugin's settings page
	 * only for this specific plugin file.
	 *
	 * @param array  $links Array of existing plugin row meta links.
	 * @param string $file  The plugin file path (relative to the plugins directory).
	 *
	 * @return array Modified array of plugin row meta links.
	 */
	public function add_row_meta( $links, $file ) {

		// Only add the settings link for this specific plugin.
		if ( 'accessibility-onetap/accessibility-onetap.php' === $file ) {
			$settings_url  = admin_url( 'admin.php?page=accessibility-onetap-settings' );
			$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'accessibility-onetap' ) . '</a>';

			// Append the custom settings link to the plugin meta row.
			$links[] = $settings_link;
		}

		return $links;
	}

	/**
	 * Register settings related to the accessibility status section.
	 *
	 * This method registers multiple options using the WordPress Settings API,
	 * including checkboxes, text inputs, select dropdowns, and rich text fields.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function register_settings_for_accessibility_status() {
		// Register a boolean setting for showing the accessibility status toggle.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_show_accessibility',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox_input' ),
			)
		);

		// Register a string setting for language selection.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_select_language',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_select_language' ),
			)
		);

		// Register a string setting for the company name input.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_company_name',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Register a string setting for the company website URL.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_company_website',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_company_website' ),
			)
		);

		// Register a string setting for the business email address.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_business_email',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
			)
		);

		// Register a boolean setting for the confirmation checkbox.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_confirmation_checkbox',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_checkbox_input' ),
			)
		);

		// Register a string setting for a WYSIWYG or rich text editor.
		register_setting(
			$this->accessibility_status_option_group,
			'onetap_editor_generator',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			)
		);
	}

	/**
	 * Sanitize checkbox input value.
	 *
	 * Converts any truthy value to integer 1, otherwise returns 0.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $value The input value to sanitize.
	 * @return int Sanitized checkbox value (1 or 0).
	 */
	public function sanitize_checkbox_input( $value ) {
		return ( isset( $value ) && (bool) $value ) ? 1 : 0;
	}

	/**
	 * Sanitize the selected language input.
	 *
	 * Ensures the selected language is in the list of allowed values.
	 * Defaults to 'en' if the input is not valid.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $value The selected language value.
	 * @return string Sanitized language code.
	 */
	public function sanitize_select_language( $value ) {
		return in_array( $value, self::ALLOWED_LANGUAGES, true ) ? $value : 'en';
	}

	/**
	 * Sanitize company website input by removing protocol prefixes.
	 *
	 * Removes "https://" or "http://" from the beginning of the URL
	 * and sanitizes the remaining value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $value The company website URL value.
	 * @return string Sanitized website URL without protocol.
	 */
	public function sanitize_company_website( $value ) {
		// Convert to string and trim whitespace.
		$value = trim( (string) $value );

		// Remove "https://" or "http://" from the beginning (case-insensitive).
		$value = preg_replace( '#^https?://#i', '', $value );

		// Remove any trailing slashes.
		$value = rtrim( $value, '/' );

		// Sanitize the remaining value.
		return sanitize_text_field( $value );
	}

	/**
	 * Register the "Missing Alt-Text" widget on the Dashboard home screen.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_missing_alt_text_dashboard_widget() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Skip the widget when the Alt Text feature is hidden.
		if ( Accessibility_Onetap_Config::is_alt_text_feature_hidden() ) {
			return;
		}

		wp_add_dashboard_widget(
			'onetap_missing_alt_text',
			__( 'Missing Alt-Text', 'accessibility-onetap' ),
			array( $this, 'render_missing_alt_text_dashboard_widget' ),
			null,
			null,
			'side',
			'high'
		);
	}

	/**
	 * Output the Dashboard widget body (missing vs total images + CTA).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_missing_alt_text_dashboard_widget() {
		$missing = $this->count_attachment_images_missing_alt_text();
		$total   = $this->count_attachment_images_total();

		$alt_url = admin_url( 'admin.php?page=accessibility-onetap-alt-text' );
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( 'altpilot/altpilot.php' ) ) {
			$alt_url = admin_url( 'admin.php?page=altpilot' );
		}
		?>
		<div class="onetap-dashboard-missing-alt">
			<p class="onetap-dashboard-missing-alt__stats">
				<?php
				printf(
					/* translators: 1: number of images without alt text, 2: total number of images in the media library */
					esc_html__( '%1$s / %2$s Images', 'accessibility-onetap' ),
					esc_html( number_format_i18n( $missing ) ),
					esc_html( number_format_i18n( $total ) )
				);
				?>
			</p>
			<p class="onetap-dashboard-missing-alt__desc">
				<?php esc_html_e( 'Alt text improves SEO and makes your content accessible to screen readers.', 'accessibility-onetap' ); ?>
			</p>
			<p class="onetap-dashboard-missing-alt__actions">
				<a href="<?php echo esc_url( $alt_url ); ?>" class="button button-primary onetap-dashboard-missing-alt__cta">
					<?php esc_html_e( 'Add Alt-Texts', 'accessibility-onetap' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Inline styles for the Missing Alt-Text dashboard widget.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin screen hook.
	 * @return void
	 */
	public function enqueue_dashboard_missing_alt_widget_styles( $hook ) {
		if ( 'index.php' !== $hook || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$handle = 'onetap-dashboard-missing-alt-text';
		wp_register_style( $handle, false, array(), $this->version );
		wp_enqueue_style( $handle );

		$css = '
			#onetap_missing_alt_text .onetap-dashboard-missing-alt__stats {
				margin: 0 0 8px;
				font-size: 20px;
				font-weight: 600;
				line-height: 1.3;
				color: #1d2327;
			}
			#onetap_missing_alt_text .onetap-dashboard-missing-alt__desc {
				margin: 0 0 16px;
				font-size: 13px;
				line-height: 1.5;
				color: #646970;
			}
			#onetap_missing_alt_text .onetap-dashboard-missing-alt__actions {
				margin: 0;
			}
			#onetap_missing_alt_text .onetap-dashboard-missing-alt__cta {
				text-align: center;
			}
		';

		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Count image attachments in the media library.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	private function count_attachment_images_total() {
		$query = new WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_mime_type'         => 'image',
				'post_status'            => 'inherit',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * Count image attachments with empty or missing alt text meta.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	private function count_attachment_images_missing_alt_text() {
		$query = new WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_mime_type'         => 'image',
				'post_status'            => 'inherit',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array(
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
				),
			)
		);

		return (int) $query->found_posts;
	}
}
