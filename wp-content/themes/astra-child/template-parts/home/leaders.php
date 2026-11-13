<?php defined( 'ABSPATH' ) || exit; ?>
<div class="container-leader">
<?php foreach ( micada_home_items( 'leaders' ) as $person ) : ?>
	<div class="d-flex-leader align-items-center">
		<div class="image-container-leader"><img src="<?php echo esc_url( $person['image'] ); ?>" alt="<?php echo esc_attr( $person['name'] ); ?>"></div>
		<div class="text-box-leader"><div class="text-box-leader-content"><h5 class="mb-1"><?php echo esc_html( $person['name'] ); ?></h5><p class="mb-1"><?php echo esc_html( $person['title'] ); ?></p><p class="location"><?php echo esc_html( $person['location'] ); ?></p></div></div>
	</div>
<?php endforeach; ?>
</div>
