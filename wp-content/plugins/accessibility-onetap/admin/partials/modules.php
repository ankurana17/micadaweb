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
	<?php onetap_load_template( 'admin/partials/header.php' ); ?>
	<?php $this->settings_api->show_forms(); ?>
	<style>
		.banner-bottom {
			padding: 0 28px;
			margin: 0 auto;
			width: calc(100% - 56px);
			max-width: 100%;
			display: none;
		}

		.banner-bottom.is-visible {
			display: block;
		}

		.banner-bottom img {
			width: 100%;
			height: auto;
			border-radius: 16px;
			margin-bottom: 24px;
		}
	</style>
	<div class="banner-bottom" id="onetap-banner-bottom">
		<a href="https://wponetap.com/pricing/?utm_source=dashboard-link&utm_medium=link&utm_campaign=ref-link-toolbar" target="_blank">
			<img src="<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/global/banner-onetap.png' ); ?>" alt="<?php esc_html_e( 'Banner Bottom', 'accessibility-onetap' ); ?>">
		</a>
	</div>
	<script>
		(function() {
			var banner = document.getElementById('onetap-banner-bottom');
			if (banner) {
				setTimeout(function() {
					banner.classList.add('is-visible');
				}, 600);
			}
		})();
	</script>	
	<?php onetap_load_template( 'admin/partials/footer.php' ); ?>
</div>