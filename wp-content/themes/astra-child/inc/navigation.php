<?php
/** Public menus, page links and route destinations. */
defined( 'ABSPATH' ) || exit;

function micada_public_page( $slug ) {
	$aliases = array( 'photo-gallery' => 'micada-photo-gallery', 'video-gallery' => 'micada-video-gallery', 'contact-us' => 'micada-contact-us' );
	return get_page_by_path( $aliases[ $slug ] ?? $slug );
}

function micada_url( $route ) {
	$route = trim( $route, '/' );
	if ( 'home' === $route ) {
		return add_query_arg( 'lang', micada_language(), home_url( '/' ) );
	}
	if ( 'contactUs' === $route || 'aboutUs' === $route ) {
		return micada_url( 'home' ) . ( 'contactUs' === $route ? '#micada-contact' : '#micada-panels' );
	}
	if ( in_array( $route, array( 'rti', 'head-office-executives', 'field-executives', 'office-orders' ), true ) ) {
		return add_query_arg( 'section', $route, micada_url( 'home' ) ) . '#micada-panels';
	}
	if ( preg_match( '~^(phone-login(?:/|$)|login$|vendor-login$|vendor-registration-login$|tubewell-connection-login$|forgot-password$|vendor-reg/forgot-password$|vendor-reg-signup$|dashboard$)~', $route ) ) {
		return micada_app_url( $route );
	}
	if ( in_array( $route, array( 'new-target', 'target-report' ), true ) ) {
		return 'https://micada.haryana.gov.in/' . $route;
	}
	$page = micada_public_page( $route );
	if ( $page && 'publish' === $page->post_status ) {
		return add_query_arg( array( 'page_id' => $page->ID, 'lang' => micada_language() ), home_url( '/' ) );
	}
	return 'https://micada.haryana.gov.in/' . $route;
}

function micada_resolve_app_link( $url ) {
	if ( ! is_string( $url ) || '' === trim( $url ) || ! function_exists( 'micada_app_url' ) ) {
		return $url;
	}
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) ) {
		return $url;
	}
	$app_routes = '~^(phone-login(?:/(?:stp|pond|canal|tubewell|noc))?|login|vendor-login|vendor-registration-login|tubewell-connection-login|forgot-password|vendor-reg/forgot-password|vendor-reg-signup|dashboard)$~';
	$query = array();
	if ( ! empty( $parts['query'] ) ) {
		parse_str( $parts['query'], $query );
	}
	$query_route = trim( (string) ( $query['micada_app'] ?? '' ), '/' );
	if ( '' !== $query_route && preg_match( $app_routes, $query_route ) ) {
		return micada_app_url( $query_route );
	}
	$host = strtolower( (string) ( $parts['host'] ?? '' ) );
	$site_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	if ( ! in_array( $host, array( 'micada.haryana.gov.in', $site_host ), true ) ) {
		return $url;
	}
	$route = trim( (string) ( $parts['path'] ?? '' ), '/' );
	$base_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( '' !== $base_path && 0 === strpos( $route, $base_path . '/' ) ) {
		$route = substr( $route, strlen( $base_path ) + 1 );
	}
	return preg_match( $app_routes, $route ) ? micada_app_url( $route ) : $url;
}
function micada_icon_svg( $name, $class = '' ) {
	$paths = array(
		'home' => '<path d="m3 10 9-7 9 7M5 9v12h14V9M9 21v-6h6v6"/>',
		'external-link' => '<rect x="3" y="5" width="15" height="16" rx="1"/><path d="M13 3h8v8M21 3l-9 9"/>',
		'star' => '<path d="m12 3 2.8 5.7 6.3.9-4.6 4.5 1.1 6.3-5.6-3-5.6 3 1.1-6.3-4.6-4.5 6.3-.9L12 3Z"/>',
		'flag' => '<path d="M5 21V4m0 1c5-4 9 3 14 0v10c-5 3-9-4-14 0"/>',
		'server' => '<rect x="3" y="3" width="18" height="5" rx="1"/><rect x="3" y="9.5" width="18" height="5" rx="1"/><rect x="3" y="16" width="18" height="5" rx="1"/><path d="M6 5.5h.01M6 12h.01M6 18.5h.01M10 5.5h7M10 12h7M10 18.5h7"/>',
		'question-circle' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.5 2.5 0 1 1 4.4 1.6c-1.1 1-2 1.4-2 3M12 17h.01"/>',
		'sign-in' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
		'envelope' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
		'angle-down' => '<path d="m6 9 6 6 6-6"/>',
		'bars' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'exclamation-triangle' => '<path d="M10.3 3.9 2.6 17.3A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.7L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 3h.01"/>',
		'map-marker' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
		'phone' => '<path d="M7 3H5a2 2 0 0 0-2 2c0 8.8 7.2 16 16 16a2 2 0 0 0 2-2v-2l-5-2-2 3a15 15 0 0 1-8-8l3-2-2-5Z"/>',
		'building' => '<path d="M4 21V4l8-2 8 2v17M2 21h20M8 7h1m6 0h1M8 11h1m6 0h1M10 21v-5h4v5"/>',
		'th-large' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
		'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.9M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-8a4 4 0 0 1 0 8"/>',
		'file-text' => '<path d="M6 2h8l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 0v5h5M8 12h7M8 16h7"/>',
		'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
		'video' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3V9Z"/>',
		'user' => '<circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/>',
		'info-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/>',
		'book-open' => '<path d="M12 7v14m0-14C9 4 5 4 2 6v13c3-2 7-2 10 0m0-12c3-3 7-3 10-1v13c-3-2-7-2-10 0"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	$classes = 'micada-icon' . ( '' !== $class ? ' ' . sanitize_html_class( $class ) : '' );
	return '<svg class="' . esc_attr( $classes ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</svg>';
}

function micada_default_navigation() {
	$items = array(
		array( 'HOME', 'home', 'home' ),
		array( 'ABOUT_US', '', '', array( array( 'ABOUT_US', 'info-circle', 'aboutUs' ), array( 'SUCCESS_STORIES', 'book-open', 'success-stories' ) ) ),
		array( 'CADMIS', 'external-link', 'https://wms.micada.haryana.gov.in/' ),
		array( 'RTI', 'star', 'rti' ),
		array( 'ORGANISATION', '', '', array( array( 'HEAD_OFF_EXECUTIVES', 'building', 'head-office-executives' ), array( 'FIELD_EXECUTIVES', 'users', 'field-executives' ), array( 'OFFICE_ORDERS', 'file-text', 'office-orders' ) ) ),
		array( 'MI_CIRCULAR', 'star', 'mi-circular' ),
		array( 'TARGET', 'flag', 'target-report' ),
		array( 'PHOTO_VIDEO_GALLERY', 'server', '', array( array( 'PHOTO_GALLERY', 'image', 'photo-gallery' ), array( 'VIDEO_GALLERY', 'video', 'video-gallery' ) ) ),
		array( 'FAQs', 'question-circle', '', array() ),
		array( 'LOGIN', 'sign-in', '', array( array( 'OFFICER_LOGIN', 'user', 'login' ), array( 'VENDOR_LOGIN', 'user', 'vendor-login' ), array( 'NEW_REGISTRATION', 'user', 'vendor-registration-login' ) ) ),
		array( 'CONTACT_US', 'envelope', 'contactUs' ),
	);
	echo '<ul class="micada-menu">';
	foreach ( $items as $item ) {
		if ( 'FAQs' === $item[0] && function_exists( 'micada_faq_navigation_items' ) ) {
			$item[3] = micada_faq_navigation_items();
		}
		echo '<li>';
		$icon = $item[1] ? micada_icon_svg( $item[1], 'micada-nav-icon' ) . ' ' : '';
		if ( isset( $item[3] ) ) {
			echo '<details><summary>' . $icon . esc_html( micada_text( $item[0] ) ) . ' ' . micada_icon_svg( 'angle-down', 'micada-menu-chevron' ) . '</summary><ul>';
			foreach ( $item[3] as $child ) {
				$is_external = (bool) preg_match( '~^https?://~i', $child[2] );
				$url = $is_external ? $child[2] : micada_url( $child[2] );
				$child_icon = ! empty( $child[1] ) ? micada_icon_svg( $child[1], 'micada-submenu-icon' ) . ' ' : '';
				$child_label = 'FAQs' === $item[0] ? $child[0] : micada_text( $child[0] );
				echo '<li><a href="' . esc_url( $url ) . '"' . ( $is_external ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $child_icon . esc_html( $child_label ) . '</a></li>';
			}
			echo '</ul></details>';
		} else {
			$url = 0 === strpos( $item[2], 'https://' ) ? $item[2] : micada_url( $item[2] );
			echo '<a href="' . esc_url( $url ) . '"' . ( 0 === strpos( $url, 'https://' ) ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $icon . esc_html( micada_text( $item[0] ) ) . '</a>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

function micada_home_setup() {
	register_nav_menus( array( 'micada_primary' => __( 'MICADA homepage navigation', 'astra-child' ), 'micada_quick' => __( 'MICADA quick links', 'astra-child' ) ) );
}
add_action( 'after_setup_theme', 'micada_home_setup' );

