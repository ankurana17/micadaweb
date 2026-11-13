<?php
/** Template Name: MICADA Reference Homepage */
defined( 'ABSPATH' ) || exit;
$micada_banners = micada_home_items( 'banners' );
$micada_photos = micada_gallery_items( 'photo' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'micada-public micada-home' ); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#micada-panels"><?php esc_html_e( 'Skip to content', 'astra-child' ); ?></a>
<?php require __DIR__ . '/template-parts/header.php'; ?>
<nav class="sticky-bar micada-navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'astra-child' ); ?>">
	<button type="button" class="micada-menu-toggle" aria-expanded="false" aria-controls="micada-primary"><?php echo micada_icon_svg( 'bars', 'micada-menu-toggle-icon' ); ?> <?php esc_html_e( 'Menu', 'astra-child' ); ?></button>
	<div id="micada-primary"><?php wp_nav_menu( array( 'theme_location' => 'micada_primary', 'container' => false, 'menu_class' => 'micada-menu', 'fallback_cb' => 'micada_default_navigation' ) ); ?></div>
</nav>
<main>
<section class="w3-hover-shadow w3-borderB mt-1 mb-1 micada-applications">
	<div class="micada-farmer-line"><div class="marquee-static-text"><p><?php echo esc_html( micada_text( 'FARMER_CORNER' ) ); ?></p></div><?php micada_render_home_ticker(); ?></div>
	<?php micada_render_home_notice(); ?>
	<div class="login-button-section d-flex">
	<?php foreach ( array( 'stp' => 'STP/TWW_BASED_MI', 'pond' => 'OVERFLOW_POND_BASED_MI', 'canal' => 'CANAL_BASED_MI', 'tubewell' => 'TUBEWELL_BASED_MI', 'noc' => 'APPLY_FOR_NOC(MI)' ) as $type => $key ) : ?>
		<a class="button-33" href="<?php echo esc_url( micada_app_url( 'phone-login/' . $type ) ); ?>" target="_blank" rel="noopener noreferrer" <?php echo 'stp' === $type ? 'data-login-ws="STP"' : ''; ?>><b><?php echo esc_html( micada_text( $key ) ); ?></b></a>
	<?php endforeach; ?>
	</div>
</section>
<?php require __DIR__ . '/template-parts/home/banner.php'; ?>
<section id="micada-panels" class="micada-panels p-fluid p-formgrid justify-content-center mt-2 mb-0">
<?php require __DIR__ . '/template-parts/home/quick-links.php'; ?>

	<div class="w3-col m8 w3-container info-container2 w3-center w3-hover-shadow w3-border" id="micada-information">
	<?php micada_home_panel(); ?>
	</div>
<?php require __DIR__ . '/template-parts/home/news-events.php'; ?>

</section>
<?php require __DIR__ . '/template-parts/home/galleries.php'; ?>

</main>
<div id="micada-contact"><?php require __DIR__ . '/template-parts/footer.php'; ?></div>
<?php if ( micada_popup_is_enabled() ) : ?>
<dialog class="portal-modal-overlay micada-notice" data-notice aria-label="<?php esc_attr_e( 'Portal notice', 'astra-child' ); ?>"><div class="portal-modal-content"><button type="button" class="popup-modal-close" data-notice-close aria-label="<?php esc_attr_e( 'Close notice', 'astra-child' ); ?>"><span>×</span></button><img class="popup-modal-image" src="<?php echo esc_url( micada_popup_image_url() ); ?>" alt="<?php esc_attr_e( 'MICADA portal notice', 'astra-child' ); ?>"></div></dialog>
<?php endif; ?>
<dialog class="micada-lightbox" data-lightbox aria-label="<?php esc_attr_e( 'Photo preview', 'astra-child' ); ?>"><button type="button" data-lightbox-close aria-label="<?php esc_attr_e( 'Close photo', 'astra-child' ); ?>">×</button><button type="button" data-photo-previous aria-label="<?php esc_attr_e( 'Previous photo', 'astra-child' ); ?>">❮</button><figure><img alt=""><figcaption></figcaption></figure><button type="button" data-photo-next aria-label="<?php esc_attr_e( 'Next photo', 'astra-child' ); ?>">❯</button></dialog>
<?php wp_footer(); ?>
</body></html>
