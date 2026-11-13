<?php
/** Native MICADA public content integration. */
defined( 'ABSPATH' ) || exit;

function micada_language() {
	return isset( $_GET['lang'] ) && 'hd' === sanitize_key( wp_unslash( $_GET['lang'] ) ) ? 'hd' : 'en';
}

function micada_text( $key ) {
	static $translations = null;
	if ( null === $translations ) {
		$translations = json_decode( file_get_contents( __DIR__ . '/../assets/data/translations.json' ), true );
	}
	return $translations[ micada_language() ][ $key ] ?? $key;
}

function micada_asset( $path ) {
	
	$path = ltrim( $path, '/' );
	if ( ! preg_match( '~^(css|js|images|fonts|documents|data)/~', $path ) ) {
		$extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$folders = array( 'css' => 'css', 'js' => 'js', 'json' => 'data', 'pdf' => 'documents', 'woff' => 'fonts', 'woff2' => 'fonts', 'ttf' => 'fonts', 'eot' => 'fonts' );
		$folder = $folders[ $extension ] ?? 'images';
		$path = $folder . '/' . ( in_array( $folder, array( 'css', 'js', 'data', 'fonts' ), true ) ? basename( $path ) : $path );
	}
	return get_stylesheet_directory_uri() . '/assets/' . $path;
}

function micada_public_assets() {
	if ( function_exists( 'micada_app_route' ) && '' !== micada_app_route() ) {
		return;
	}
	$template = get_page_template_slug();
	if ( 'page-micada.php' !== $template && ( 0 !== strpos( $template, 'page-templates/' ) || ! is_file( get_stylesheet_directory() . '/' . $template ) ) ) {
		return;
	}
	$root = get_stylesheet_directory_uri() . '/assets/';
	foreach ( array( 'primeflex', 'public' ) as $name ) {
		wp_enqueue_style( 'micada-' . $name, $root . 'css/' . $name . '.css', array(), filemtime( __DIR__ . '/../assets/css/' . $name . '.css' ) );
	}
	wp_enqueue_script( 'micada-public', $root . 'js/public.js', array(), filemtime( __DIR__ . '/../assets/js/public.js' ), true );
	if ( 'page-micada.php' === $template ) {
		foreach ( array( 'home-source', 'home', 'home-typography' ) as $name ) {
			wp_enqueue_style( 'micada-' . $name, $root . 'css/' . $name . '.css', array( 'micada-public' ), filemtime( __DIR__ . '/../assets/css/' . $name . '.css' ) );
		}
		wp_enqueue_script( 'micada-home', $root . 'js/home.js', array( 'micada-public' ), filemtime( __DIR__ . '/../assets/js/home.js' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'micada_public_assets', 30 );

function micada_customize( $customizer ) {
	$customizer->add_section( 'micada_public', array( 'title' => __( 'MICADA public content', 'astra-child' ) ) );
	$fields = array(
		'heading' => array( 'MICADA_HEADING', 'sanitize_text_field' ),
		'address' => array( 'BAYS_39_40_SECTOR_4_PANCHKULA', 'sanitize_text_field' ),
		'phone' => array( '0172–2562275', 'sanitize_text_field' ),
		'email' => array( 'cmda@hry.nic.in', 'sanitize_email' ),
	);
	foreach ( $fields as $key => $field ) {
		foreach ( array( 'en', 'hd' ) as $language ) {
			$id = 'micada_' . $key . '_' . $language;
			$customizer->add_setting( $id, array( 'sanitize_callback' => $field[1] ) );
			$customizer->add_control( $id, array( 'section' => 'micada_public', 'label' => ucfirst( $key ) . ' (' . $language . ')', 'type' => 'text' ) );
		}
	}
}
add_action( 'customize_register', 'micada_customize' );

function micada_editorial_text( $name, $fallback ) {
	$value = get_theme_mod( 'micada_' . $name . '_' . micada_language(), '' );
	return '' !== $value ? $value : micada_text( $fallback );
}

function micada_page_fields( $post_type, $post ) {
	$template = $post ? get_page_template_slug( $post->ID ) : '';
	if ( 'page' !== $post_type || 0 !== strpos( $template, 'page-templates/' ) || ! is_file( get_stylesheet_directory() . '/' . $template ) ) {
		return;
	}
	add_meta_box( 'micada-public', __( 'MICADA public page', 'astra-child' ), 'micada_page_fields_render', 'page', 'normal' );
}
add_action( 'add_meta_boxes', 'micada_page_fields', 10, 2 );

function micada_page_fields_render( $post ) {
	wp_nonce_field( 'micada_public_save', 'micada_public_nonce' );
	$kind = micada_page_kind( $post->ID );
	echo '<p><label for="micada-kind">Page type</label> <select id="micada-kind" name="micada_kind">';
	foreach ( array( 'content' => 'CMS content', 'photo' => 'Photo gallery', 'video' => 'Video gallery', 'stories' => 'Success stories' ) as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '" ' . selected( $kind, $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p><p>Use the MICADA Public Page template. The page editor manages content. Leave it empty to preserve the original gallery/story default.</p>';
	echo '<p><label for="micada-videos">Video embeds: one HTTPS URL | description per line. Leave empty for the original 19 videos.</label></p>';
	echo '<textarea id="micada-videos" name="micada_videos" rows="8" class="widefat">' . esc_textarea( get_post_meta( $post->ID, '_micada_videos', true ) ) . '</textarea>';
}

function micada_page_kind( $post_id ) {
	$kind = get_post_meta( $post_id, '_micada_kind', true );
	if ( '' !== $kind ) {
		return $kind;
	}
	$defaults = array( 'success-stories.php' => 'stories', 'photo-gallery.php' => 'photo', 'video-gallery.php' => 'video' );
	return $defaults[ basename( get_page_template_slug( $post_id ) ) ] ?? 'content';
}

function micada_allowed_embed( $url ) {
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) ) {
		return false;
	}
	$host = strtolower( $parts['host'] ?? '' );
	$path = $parts['path'] ?? '';
	return ( 'www.youtube.com' === $host && preg_match( '~^/embed/[a-zA-Z0-9_-]+$~', $path ) ) || ( 'drive.google.com' === $host && preg_match( '~^/file/d/[a-zA-Z0-9_-]+/preview$~', $path ) );
}

function micada_save_page_fields( $post_id ) {
	if ( ! isset( $_POST['micada_public_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['micada_public_nonce'] ) ), 'micada_public_save' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}
	$kind = isset( $_POST['micada_kind'] ) ? sanitize_key( wp_unslash( $_POST['micada_kind'] ) ) : 'content';
	if ( in_array( $kind, array( 'content', 'photo', 'video', 'stories' ), true ) ) {
		update_post_meta( $post_id, '_micada_kind', $kind );
	}
	$lines = preg_split( '/\R/', isset( $_POST['micada_videos'] ) ? wp_unslash( $_POST['micada_videos'] ) : '' );
	$clean = array();
	foreach ( $lines as $line ) {
		$parts = explode( '|', $line, 2 );
		$url = esc_url_raw( trim( $parts[0] ) );
		if ( micada_allowed_embed( $url ) ) {
			$clean[] = $url . ' | ' . sanitize_text_field( trim( $parts[1] ?? '' ) );
		}
	}
	update_post_meta( $post_id, '_micada_videos', implode( "\n", $clean ) );
}
add_action( 'save_post_page', 'micada_save_page_fields' );

function micada_page_videos( $post_id ) {
	$value = get_post_meta( $post_id, '_micada_videos', true );
	if ( '' === trim( $value ) ) {
		$gallery = function_exists( 'micada_gallery_items' ) ? micada_gallery_items( 'video' ) : array();
		return array_map( static function ( $video ) {
			return array( 'link' => $video['url'] ?? '', 'description' => $video['description'] ?? '' );
		}, $gallery );
	}
	$videos = array();
	foreach ( preg_split( '/\R/', $value ) as $line ) {
		$parts = explode( '|', $line, 2 );
		$url = trim( $parts[0] );
		if ( micada_allowed_embed( $url ) ) {
			$videos[] = array( 'link' => $url, 'description' => trim( $parts[1] ?? '' ) );
		}
	}
	return $videos;
}

/** Graceful fallbacks when MICADA CMS plugin is deactivated. */
if ( ! function_exists( 'micada_gallery_items' ) ) {
	function micada_gallery_items( $type = '' ) {
		return array();
	}
}
if ( ! function_exists( 'micada_render_home_ticker' ) ) {
	function micada_render_home_ticker() {}
}
if ( ! function_exists( 'micada_render_home_notice' ) ) {
	function micada_render_home_notice() {}
}
if ( ! function_exists( 'micada_popup_is_enabled' ) ) {
	function micada_popup_is_enabled() {
		return false;
	}
}
if ( ! function_exists( 'micada_popup_image_url' ) ) {
	function micada_popup_image_url() {
		return '';
	}
}
if ( ! function_exists( 'micada_render_managed_news_events' ) ) {
	function micada_render_managed_news_events() {
		return false;
	}
}
if ( ! function_exists( 'micada_render_managed_quick_links' ) ) {
	function micada_render_managed_quick_links() {
		return false;
	}
}
