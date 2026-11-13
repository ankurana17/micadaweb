<?php
/**
 * Content template for submenu page.
 *
 * @package    Accessibility_Onetap
 * @since      1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<style>
		#apop_alt_text,
		#onetap_alt_text {
			overflow: auto;
		}

		@media only screen and (max-width: 1280px) {
			#apop_alt_text,
			#onetap_alt_text {
				margin-bottom: 15px;
			}
		}
	</style>
	<?php onetap_load_template( 'admin/partials/header.php' ); ?>

	<div class="box box-alt-pilot">
		<div class="mycontainer">
			<div class="myrow">
				<div class="logo">
					<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/logo-altpilot2.svg' ); ?>" alt="AltPilot.ai Logo" />
					<span class="save-hours"><?php esc_html_e( 'Save hours', 'accessibility-onetap' ); ?></span>
					<span><?php esc_html_e( 'and generate your Alt-text with AltPilot.ai.', 'accessibility-onetap' ); ?></span>
					<span class="try-free"><?php esc_html_e( 'Try 25 Images for Free', 'accessibility-onetap' ); ?></span>
				</div>

				<?php
				$apop_altpilot_cta = $this->settings_api->get_altpilot_banner_cta();
				$apop_altpilot_rel = ( '_blank' === $apop_altpilot_cta['target'] ) ? 'noopener noreferrer' : '';

				// The href alone already installs AltPilot through the WordPress
				// installer screen. These attributes let the script do the same
				// step over AJAX so the admin never leaves the Alt Text page.
				$apop_altpilot_install_attrs = '';
				if ( 'install' === $apop_altpilot_cta['state'] ) {
					$apop_altpilot_install_attrs = sprintf(
						' data-onetap-altpilot-install="1" data-slug="%1$s" data-busy-label="%2$s" data-done-label="%3$s"',
						esc_attr( $apop_altpilot_cta['slug'] ),
						esc_attr( $apop_altpilot_cta['busy_label'] ),
						esc_attr( $apop_altpilot_cta['done_label'] )
					);
				}
				?>
				<div class="link">
					<a href="<?php echo esc_url( $apop_altpilot_cta['url'] ); ?>" class="button-alt-pilot"<?php echo $apop_altpilot_cta['target'] ? ' target="' . esc_attr( $apop_altpilot_cta['target'] ) . '" rel="' . esc_attr( $apop_altpilot_rel ) . '"' : ''; ?><?php echo $apop_altpilot_install_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts just above. ?>><?php echo esc_html( $apop_altpilot_cta['label'] ); ?></a>
				</div>
				<p class="button-alt-pilot-feedback" role="status" aria-live="polite"></p>
			</div>
		</div>
	</div>
	
	<?php $this->settings_api->show_forms(); ?>

	<?php
	// Sites that cannot write to the filesystem directly need this dialog before
	// the install can run; WordPress fills it in and the updates script opens it.
	// The function lives in an admin include that is not guaranteed to be loaded.
	if ( 'install' === $apop_altpilot_cta['state'] ) {
		if ( ! function_exists( 'wp_print_request_filesystem_credentials_modal' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		wp_print_request_filesystem_credentials_modal();
	}
	?>

	<?php onetap_load_template( 'admin/partials/footer.php' ); ?>
</div>