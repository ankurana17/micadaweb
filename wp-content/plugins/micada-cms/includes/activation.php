<?php
/**
 * Setup and Activation for MICADA CMS.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/migration.php';

/** Make the MICADA child theme and homepage the site defaults when available. */
function micada_cms_set_default_theme_and_homepage() {
	if ( function_exists( 'wp_get_theme' ) && function_exists( 'switch_theme' ) ) {
		$child_theme  = wp_get_theme( 'astra-child' );
		$parent_theme = wp_get_theme( 'astra' );
		if ( $child_theme->exists() && $parent_theme->exists() && 'astra' === $child_theme->get( 'Template' ) ) {
			switch_theme( 'astra-child' );
		}
	}

	$front_page_id = absint( get_option( 'page_on_front' ) );
	if ( ! $front_page_id ) {
		$home_page = get_page_by_path( 'home', OBJECT, 'page' );
		if ( $home_page && 'publish' === $home_page->post_status ) {
			$front_page_id = absint( $home_page->ID );
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front_page_id );
		}
	}

	if ( $front_page_id && 'page' === get_post_type( $front_page_id ) ) {
		update_post_meta( $front_page_id, '_wp_page_template', 'page-micada.php' );
	}
}

/** Install all tables and register all post types across all modules. */
function micada_cms_install_all_tables() {
	$registrars = array(
		'micada_register_officials_post_type',
		'micada_register_banners_post_type',
	);
	foreach ( $registrars as $registrar ) {
		if ( function_exists( $registrar ) ) {
			call_user_func( $registrar );
		}
	}

	$installers = array(
		'micada_quick_links_install',
		'micada_news_events_install',
		'micada_faq_install',
		'micada_important_links_install',
		'micada_gallery_install',
		'micada_home_ticker_install',
		'micada_popup_install_table',
		'micada_org_install',
		'micada_rti_install',
	);
	foreach ( $installers as $installer ) {
		if ( function_exists( $installer ) ) {
			call_user_func( $installer );
		}
	}
}

/** Set up and import the snapshot data owned by one CMS component (backward compatibility). */
function micada_cms_activate_component( $component ) {
	micada_cms_install_all_tables();
	micada_cms_migrate_all_data( false );
}

/** Register a component-specific activation (backward compatibility). */
function micada_cms_register_component_activation( $plugin_file, $component ) {
	register_activation_hook(
		$plugin_file,
		static function () use ( $component ) {
			micada_cms_activate_component( $component );
		}
	);
}

/** Core plugin activation: makes tables in DB and migrates all data. */
function micada_cms_activate_core() {
	micada_cms_set_default_theme_and_homepage();
	micada_cms_install_all_tables();
	micada_cms_migrate_all_data( false );
	update_option( 'micada_cms_db_version', MICADA_CMS_VERSION );
	flush_rewrite_rules();
}
register_activation_hook( MICADA_CMS_PLUGIN_FILE, 'micada_cms_activate_core' );

/** Core plugin deactivation. */
function micada_cms_deactivate_core() {
	flush_rewrite_rules();
}
register_deactivation_hook( MICADA_CMS_PLUGIN_FILE, 'micada_cms_deactivate_core' );

/** Check on admin_init to ensure tables exist and data is migrated. */
function micada_cms_maybe_auto_migrate() {
	$installed_version = get_option( 'micada_cms_db_version', '' );
	if ( version_compare( (string) $installed_version, MICADA_CMS_VERSION, '<' ) ) {
		micada_cms_install_all_tables();
		micada_cms_migrate_all_data( false );
		update_option( 'micada_cms_db_version', MICADA_CMS_VERSION );
	}
}
add_action( 'admin_init', 'micada_cms_maybe_auto_migrate' );
