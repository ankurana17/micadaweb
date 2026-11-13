<?php
/** Read-only native-template smoke checks. Run using PHP CLI. */
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 404 );
	exit;
}
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require dirname( __DIR__, 4 ) . '/wp-load.php';

function micada_check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, 'FAIL: ' . $message . PHP_EOL );
		exit( 1 );
	}
	echo 'PASS: ', $message, PHP_EOL;
}

micada_check( 'astra-child' === get_stylesheet(), 'Existing Astra child theme active' );
$videos = micada_page_videos( 0 );
micada_check( 19 === count( $videos ), 'All 19 source videos retained, including original duplicate' );
foreach ( $videos as $video ) {
	micada_check( micada_allowed_embed( $video['link'] ), 'Allowed source embed: ' . $video['description'] );
}
foreach ( array( 'javascript:alert(1)', 'https://evil.example/embed/a', 'https://www.youtube.com.evil.example/embed/a', 'http://www.youtube.com/embed/a', 'https://www.youtube.com@evil.example/embed/a' ) as $url ) {
	micada_check( ! micada_allowed_embed( $url ), 'Reject invalid embed URL' );
}

foreach ( array( 'en', 'hd' ) as $language ) {
	$_GET['lang'] = $language;
	micada_check( 'BACK' !== micada_text( 'BACK' ), $language . ' translation loaded' );
	$query = new WP_Query( array( 'page_id' => 470 ) );
	micada_check( $query->have_posts(), 'Existing video gallery available for read-only render' );
	$GLOBALS['wp_query'] = $query;
	$query->posts[0]->post_content = '';
	$GLOBALS['post'] = $query->posts[0];
	setup_postdata( $GLOBALS['post'] );
	wp_scripts()->done = array();
	wp_styles()->done = array();
	$metadata = static function ( $value, $object_id, $key, $single ) {
		if ( 470 === $object_id && '_micada_kind' === $key ) {
			return $single ? 'video' : array( 'video' );
		}
		if ( 470 === $object_id && '_wp_page_template' === $key ) {
			return $single ? 'page-templates/public.php' : array( 'page-templates/public.php' );
		}
		return $value;
	};
	add_filter( 'get_post_metadata', $metadata, 10, 4 );
	ob_start();
	require dirname( __DIR__ ) . '/page-templates/public.php';
	$html = ob_get_clean();
	remove_filter( 'get_post_metadata', $metadata, 10 );
	micada_check( 19 === substr_count( $html, '<div class="video-container">' ), $language . ' template renders 19 video cards' );
	micada_check( false === strpos( $html, '{{' ) && false === strpos( $html, '*ng' ), $language . ' template has no Angular bindings' );
	micada_check( false !== strpos( $html, 'data-micada-language' ) && false !== strpos( $html, 'data-micada-back' ), $language . ' native language and history controls rendered' );
	micada_check( false !== strpos( $html, 'micada-public-js' ), $language . ' WordPress enqueues native JavaScript' );
}
echo 'Read-only checks complete. Browser and authenticated workflow testing remain outstanding.', PHP_EOL;
