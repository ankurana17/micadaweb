<?php defined( 'ABSPATH' ) || exit; ?>
<section class="micada-hero" aria-label="<?php esc_attr_e( 'Micro irrigation projects', 'astra-child' ); ?>">
	<div class="banner-content-overlay hide-small mt-2"><?php require get_stylesheet_directory() . '/template-parts/home/leaders.php'; ?></div>
	<?php micada_render_vendor_banner(); ?>
	<div class="micada-slides" data-carousel>
		<div class="micada-slides-track">
		<?php foreach ( $micada_banners as $index => $banner ) : ?>
			<figure class="micada-slide" data-slide-index="<?php echo esc_attr( $index ); ?>">
				<img src="<?php echo esc_url( $banner['previewImageSrc'] ); ?>" alt="<?php echo esc_attr( $banner['description'] ); ?>" <?php echo $index ? 'loading="lazy"' : 'fetchpriority="high"'; ?>>
				<figcaption><?php echo esc_html( $banner['description'] ); ?></figcaption>
			</figure>
		<?php endforeach; ?>
		</div>
	</div>
	<div class="micada-dots" aria-label="<?php esc_attr_e( 'Choose banner', 'astra-child' ); ?>"><?php foreach ( $micada_banners as $index => $banner ) : ?><button type="button" data-slide="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( $banner['description'] ); ?>" aria-pressed="<?php echo $index ? 'false' : 'true'; ?>"></button><?php endforeach; ?></div>
</section>
<div class="micada-mobile-leaders"><?php require get_stylesheet_directory() . '/template-parts/home/leaders.php'; ?></div>
