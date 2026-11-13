<?php
/**
 * Template Name: MICADA Public Page
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'micada-public' ); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#micada-content"><?php esc_html_e( 'Skip to content', 'astra-child' ); ?></a>
<?php require get_stylesheet_directory() . '/template-parts/header.php'; ?>
<?php $micada_is_mi_circular = 'page-templates/mi-circular.php' === get_page_template_slug(); ?>
<main id="micada-content" class="body-container p-fluid p-formgrid justify-content-center mt-1<?php echo 'page-templates/mi-circular.php' === get_page_template_slug() ? ' micada-mi-circular-page' : ''; ?>" style="display:flex;align-items:center;flex-wrap:wrap">
<?php while ( have_posts() ) : the_post(); ?>
	<?php
	$kind = micada_page_kind( get_the_ID() );
	$micada_video_meta = get_post_meta( get_the_ID(), '_micada_videos', true );
	$micada_native_videos = 'video' === $kind && ( '' === trim( get_the_content() ) || '' !== trim( $micada_video_meta ) );
	$keys = array( 'photo' => 'PHOTO_GALLERY', 'video' => 'VIDEO_GALLERY', 'stories' => 'SUCCESS_STORIES' );
	$micada_circular_years = array();
	if ( $micada_is_mi_circular ) {
		foreach ( parse_blocks( get_the_content() ) as $year_block ) {
			if ( 'core/group' !== ( $year_block['blockName'] ?? '' ) ) {
				continue;
			}
			$year_name = '';
			$documents = array();
			foreach ( $year_block['innerBlocks'] ?? array() as $content_block ) {
				if ( 'core/heading' === ( $content_block['blockName'] ?? '' ) ) {
					$year_name = trim( wp_strip_all_tags( $content_block['innerHTML'] ?? '' ) );
				} elseif ( 'core/paragraph' === ( $content_block['blockName'] ?? '' ) && preg_match( '~<a\\b[^>]*href=("|\\\')(.*?)\\1[^>]*>(.*?)</a>~is', $content_block['innerHTML'] ?? '', $link_match ) ) {
					$url = html_entity_decode( $link_match[2], ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' );
					$title = trim( wp_strip_all_tags( $link_match[3] ) );
					$title = preg_replace( '/\\s*Download PDF\\s*$/i', '', $title );
					$title = preg_replace( '/[\\s\\p{Pd}\\p{P}]+$/u', '', $title );
					if ( '' !== $title && '' !== $url ) {
						$documents[] = array( 'title' => $title, 'url' => $url );
					}
				}
			}
			if ( '' !== $year_name && $documents ) {
				$micada_circular_years[] = array( 'year' => $year_name, 'documents' => $documents );
			}
		}
		usort( $micada_circular_years, static function ( $first, $second ) {
			preg_match( '/\\d{4}/', $first['year'], $first_year );
			preg_match( '/\\d{4}/', $second['year'], $second_year );
			return (int) ( $first_year[0] ?? 0 ) <=> (int) ( $second_year[0] ?? 0 );
		} );
	}
	?>
	<div class="field sm:col-12 md:col-12 lg:col-12 my-0 py-2 mx-0 px-2<?php echo $micada_is_mi_circular ? ' micada-mi-circular-heading' : ''; ?>" style="display:flex;align-items:center">
		<div class="static-button"><button type="button" class="back-button text-sm" data-micada-back>← <?php echo esc_html( micada_text( 'BACK' ) ); ?></button></div>
		<div class="field sm:col-12 md:col-6 lg:col-10 px-2 photo-gallery-header"><p><?php echo esc_html( isset( $keys[ $kind ] ) ? micada_text( $keys[ $kind ] ) : get_the_title() ); ?></p><?php if ( $micada_is_mi_circular ) : ?><em><?php esc_html_e( 'Select Financial Year', 'astra-child' ); ?></em><?php endif; ?></div>
	</div>
	<?php if ( $micada_is_mi_circular && $micada_circular_years ) : ?>
		<?php $micada_year_labels = array_map( static function ( $group ) { return preg_replace( '/^Financial Year\\s*/i', '', $group['year'] ); }, $micada_circular_years ); $micada_active_year = in_array( '2021-22', $micada_year_labels, true ) ? '2021-22' : $micada_year_labels[0]; ?>
		<nav class="micada-circular-year-tabs" aria-label="<?php esc_attr_e( 'Select financial year', 'astra-child' ); ?>">
			<?php foreach ( $micada_year_labels as $year_label ) : $is_active_year = $year_label === $micada_active_year; ?>
				<button type="button" class="micada-circular-year-tab<?php echo $is_active_year ? ' is-active' : ''; ?>" data-circular-year="<?php echo esc_attr( $year_label ); ?>" aria-pressed="<?php echo $is_active_year ? 'true' : 'false'; ?>"><?php echo esc_html( $year_label ); ?></button>
			<?php endforeach; ?>
		</nav>
		<div class="micada-circular-card-grid">
			<?php foreach ( $micada_circular_years as $year_group ) : $year_label = preg_replace( '/^Financial Year\\s*/i', '', $year_group['year'] ); foreach ( $year_group['documents'] as $document ) : ?>
				<article class="micada-circular-card" data-circular-card="<?php echo esc_attr( $year_label ); ?>"<?php echo $year_label === $micada_active_year ? '' : ' hidden'; ?>>
					<div class="micada-circular-pdf-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2.75h8l5 5V21a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2V4.75a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M7 15.5h2.3a1.5 1.5 0 0 0 0-3H7v5m5 0v-5h1.2a2.5 2.5 0 0 1 0 5H12m5-5h3m-3 0v5m0-2.5h2"/></svg></div>
					<h2><?php echo esc_html( $document['title'] ); ?></h2>
					<a class="micada-circular-download" href="<?php echo esc_url( $document['url'] ); ?>" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg> <?php echo esc_html( sprintf( __( 'Download %s', 'astra-child' ), $document['title'] ) ); ?></a>
				</article>
			<?php endforeach; endforeach; ?>
		</div>
		<script>
		(function(){const tabs=document.querySelectorAll('.micada-circular-year-tab'),cards=document.querySelectorAll('.micada-circular-card');tabs.forEach(function(tab){tab.addEventListener('click',function(){const year=tab.dataset.circularYear;tabs.forEach(function(item){const active=item===tab;item.classList.toggle('is-active',active);item.setAttribute('aria-pressed',active?'true':'false');});cards.forEach(function(card){card.hidden=card.dataset.circularCard!==year;});});});})();
		</script>
	<?php elseif ( '' !== trim( get_the_content() ) && ! $micada_native_videos ) : ?>
		<div class="micada-editor-content field col-12"><?php the_content(); ?></div>
	<?php elseif ( 'photo' === $kind ) : ?>
		<?php $micada_gallery_photos = micada_gallery_items( 'photo' ); ?>
		<div class="micada-public-photo-gallery">
		<?php foreach ( $micada_gallery_photos as $photo ) : ?>
			<figure class="micada-public-photo-item"><img loading="lazy" src="<?php echo esc_url( $photo['previewImageSrc'] ?? '' ); ?>" alt="<?php echo esc_attr( $photo['description'] ?? '' ); ?>"><figcaption><?php echo esc_html( $photo['description'] ?? '' ); ?></figcaption></figure>
		<?php endforeach; ?>
		</div>
	<?php elseif ( 'stories' === $kind ) : ?>
		<div class="img-conatiner"><p>Available soon!</p></div>
	<?php endif; ?>
	<?php if ( $micada_native_videos ) : ?>
		<?php $micada_gallery_videos = '' !== trim( $micada_video_meta ) ? micada_page_videos( get_the_ID() ) : micada_gallery_items( 'video' ); ?>
		<div class="video-gallery">
		<?php foreach ( $micada_gallery_videos as $video ) : $video_url = $video['url'] ?? $video['link'] ?? ''; ?>
			<div class="video-container">
				<iframe loading="lazy" src="<?php echo esc_url( $video_url ); ?>" title="<?php echo esc_attr( $video['description'] ?? '' ); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				<div class="video-description"><?php echo esc_html( $video['description'] ?? '' ); ?></div>
			</div>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
<?php endwhile; ?>
</main>
<?php require get_stylesheet_directory() . '/template-parts/footer.php'; ?>
<?php wp_footer(); ?>
</body>
</html>
