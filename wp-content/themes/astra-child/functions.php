<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// END ENQUEUE PARENT ACTION

require_once get_stylesheet_directory() . '/inc/cms.php';
require_once get_stylesheet_directory() . '/inc/navigation.php';
require_once get_stylesheet_directory() . '/inc/home.php';
require_once get_stylesheet_directory() . '/inc/api.php';
require_once get_stylesheet_directory() . '/inc/application.php';
require_once get_stylesheet_directory() . '/inc/dashboard.php';

/** Remove the two floating controls from the public frontend. */
function micada_remove_floating_controls() {
	global $wp_filter;

	if ( class_exists( 'Astra_Scroll_To_Top_Loader' ) ) {
		remove_action( 'wp_footer', array( Astra_Scroll_To_Top_Loader::get_instance(), 'html_markup_loader' ) );
	}

	foreach ( array( 'wp_enqueue_scripts', 'wp_footer', 'body_class' ) as $hook ) {
		if ( ! isset( $wp_filter[ $hook ] ) ) {
			continue;
		}
		foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'];
				if ( is_array( $function ) && is_object( $function[0] ) && is_a( $function[0], 'Accessibility_Onetap_Public' ) ) {
					remove_filter( $hook, $function, $priority );
				}
			}
		}
	}
}
add_action( 'wp', 'micada_remove_floating_controls' );
