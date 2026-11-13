<?php
/** MICADA reference homepage, editorial controls and public navigation. */
defined( 'ABSPATH' ) || exit;

function micada_home_data( $key ) {
	static $data = null;
	if ( null === $data ) {
		$data = json_decode( file_get_contents( __DIR__ . '/../assets/data/home-data.json' ), true );
	}
	return $data[ $key ] ?? array();
}

function micada_source_text( $value ) {
	return isset( $value[0] ) && '@' === $value[0] ? micada_text( substr( $value, 1 ) ) : $value;
}

function micada_source_image( $value ) {
	return 0 === strpos( $value, 'https://' ) ? $value : micada_asset( $value );
}

function micada_home_customizer( $customizer ) {
	$customizer->add_section( 'micada_home', array( 'title' => __( 'MICADA homepage', 'astra-child' ) ) );
	foreach ( array( 'en', 'hd' ) as $lang ) {
		foreach ( array( 'about' => 'About content page', 'news' => 'News and events content page' ) as $key => $label ) {
			$id = 'micada_home_' . $key . '_' . $lang;
			$customizer->add_setting( $id, array( 'sanitize_callback' => 'absint' ) );
			$customizer->add_control( $id, array( 'section' => 'micada_home', 'label' => $label . ' (' . $lang . ')', 'type' => 'dropdown-pages' ) );
		}
	}
	foreach ( array( 'banners' => array( 'previewImageSrc', 'description' ) ) as $collection => $fields ) {
		foreach ( micada_home_data( $collection ) as $index => $item ) {
			foreach ( $fields as $field ) {
				$image = in_array( $field, array( 'image', 'previewImageSrc' ), true );
				foreach ( $image ? array( '' ) : array( '_en', '_hd' ) as $suffix ) {
					$id = 'micada_' . $collection . '_' . $index . '_' . $field . $suffix;
					$customizer->add_setting( $id, array( 'sanitize_callback' => $image ? 'esc_url_raw' : 'sanitize_text_field' ) );
					$args = array( 'section' => 'micada_home', 'label' => ucfirst( $collection ) . ' ' . ( $index + 1 ) . ': ' . $field . $suffix );
					if ( $image ) {
						$customizer->add_control( new WP_Customize_Image_Control( $customizer, $id, $args ) );
					} else {
						$customizer->add_control( $id, $args );
					}
				}
			}
		}
	}
}
add_action( 'customize_register', 'micada_home_customizer' );

function micada_home_about_menu() {
	add_menu_page( __( 'About Us', 'astra-child' ), __( 'About Us', 'astra-child' ), 'edit_pages', 'micada-home-about', 'micada_home_about_admin_page', 'dashicons-info-outline', 21 );
}
add_action( 'admin_menu', 'micada_home_about_menu', 20 );

function micada_home_about_page_id( $language ) {
	$page_id = absint( get_theme_mod( 'micada_home_about_' . $language, 0 ) );
	if ( ! $page_id ) {
		$page = get_page_by_path( 'en' === $language ? 'about-us' : 'about-us-hi' );
		$page_id = $page ? $page->ID : 0;
	}
	return $page_id;
}

function micada_home_about_translation_content( $language ) {
	static $translations = null;
	if ( null === $translations ) {
		$file = __DIR__ . '/../assets/data/translations.json';
		$translations = is_readable( $file ) ? json_decode( file_get_contents( $file ), true ) : array();
	}
	$text = $translations[ $language ] ?? array();
	$line = static function ( $key ) use ( $text ) {
		return esc_html( $text[ $key ] ?? '' );
	};
	return '<h5 style="font-size:18px;color:black;font-weight:bold">' . $line( 'WELCOME_TO_MICADA' ) . '</h5>'
		. '<h4 class="px-3" style="color:#020202;background:#d3e1e7"><b>' . $line( 'ABOUT_CADA' ) . '</b></h4>'
		. '<p style="font-size:13px;margin-left:15px;margin-right:10px;color:black;text-align:justify"><b>'
		. $line( 'ABOUT_CADA_DETAILS' ) . '<br><br>'
		. $line( 'POINT_1' ) . '<br>' . $line( 'POINT_2' ) . '<br>' . $line( 'POINT_3' ) . '<br>'
		. $line( 'POINT_4' ) . '<br>' . $line( 'POINT_5' ) . '</b></p>';
}

function micada_home_about_admin_page() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$message = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	$english_page = get_post( micada_home_about_page_id( 'en' ) );
	$hindi_page = get_post( micada_home_about_page_id( 'hd' ) );
	$english_content = $english_page && 'page' === $english_page->post_type ? $english_page->post_content : micada_home_about_translation_content( 'en' );
	$hindi_content = $hindi_page && 'page' === $hindi_page->post_type ? $hindi_page->post_content : micada_home_about_translation_content( 'hd' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage About Us', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit the About Us content shown in the homepage center panel. Content is managed as regular WordPress pages.', 'astra-child' ); ?></p>
		<?php if ( 'saved' === $message ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'About Us content saved.', 'astra-child' ); ?></p></div><?php elseif ( 'error' === $message ) : ?><div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'About Us content could not be saved.', 'astra-child' ); ?></p></div><?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="micada_home_about_save">
			<?php wp_nonce_field( 'micada_home_about_save' ); ?>
			<div style="max-width:1000px;background:#fff;border:1px solid #c3c4c7;padding:20px;margin:18px 0">
				<h2><?php esc_html_e( 'English About Us', 'astra-child' ); ?></h2>
				<?php wp_editor( $english_content, 'micada_home_about_en', array( 'textarea_name' => 'about_en', 'textarea_rows' => 14, 'media_buttons' => true, 'teeny' => false ) ); ?>
			</div>
			<div style="max-width:1000px;background:#fff;border:1px solid #c3c4c7;padding:20px;margin:18px 0">
				<h2><?php esc_html_e( 'Hindi About Us', 'astra-child' ); ?></h2>
				<?php wp_editor( $hindi_content, 'micada_home_about_hd', array( 'textarea_name' => 'about_hd', 'textarea_rows' => 14, 'media_buttons' => true, 'teeny' => false ) ); ?>
			</div>
			<?php submit_button( __( 'Save About Us', 'astra-child' ) ); ?>
		</form>
	</div>
	<?php
}

function micada_home_about_save() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'You are not allowed to edit homepage About Us content.', 'astra-child' ) );
	}
	check_admin_referer( 'micada_home_about_save' );
	$contents = array(
		'en' => wp_kses_post( wp_unslash( $_POST['about_en'] ?? '' ) ),
		'hd' => wp_kses_post( wp_unslash( $_POST['about_hd'] ?? '' ) ),
	);
	foreach ( $contents as $language => $content ) {
		$page_id = micada_home_about_page_id( $language );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page || 'page' !== $page->post_type ) {
			$page_id = 0;
		}
		$postarr = array(
			'ID' => $page_id,
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_content' => $content,
		);
		if ( ! $page_id ) {
			$postarr['post_title'] = 'en' === $language ? __( 'About Us', 'astra-child' ) : __( 'About Us (Hindi)', 'astra-child' );
			$postarr['post_name'] = 'en' === $language ? 'about-us' : 'about-us-hi';
		}
		$saved_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $saved_id ) || ! $saved_id ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'micada-home-about', 'message' => 'error' ), admin_url( 'admin.php' ) ) );
			exit;
		}
		set_theme_mod( 'micada_home_about_' . $language, absint( $saved_id ) );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-home-about', 'message' => 'saved' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_micada_home_about_save', 'micada_home_about_save' );

function micada_home_items( $collection ) {
	if ( 'leaders' === $collection && function_exists( 'micada_get_officials' ) ) {
		$db_officials = micada_get_officials();
		if ( ! empty( $db_officials ) ) {
			return $db_officials;
		}
	}
	if ( 'banners' === $collection && function_exists( 'micada_get_banners' ) ) {
		$db_banners = micada_get_banners();
		if ( ! empty( $db_banners ) ) {
			return $db_banners;
		}
	}
	$items = micada_home_data( $collection );
	foreach ( $items as $index => &$item ) {
		foreach ( $item as $field => &$value ) {
			$image = in_array( $field, array( 'image', 'previewImageSrc' ), true );
			$default = $image ? micada_source_image( $value ) : micada_source_text( $value );
			$value = get_theme_mod( 'micada_' . $collection . '_' . $index . '_' . $field . ( $image ? '' : '_' . micada_language() ), '' ) ?: $default;
		}
		unset( $value );
	}
	unset( $item );
	return $items;
}

function micada_home_editor_content( $kind ) {
	$id = absint( get_theme_mod( 'micada_home_' . $kind . '_' . micada_language(), 0 ) );
	if ( ! $id && 'about' === $kind && 'en' === micada_language() ) {
		$page = get_page_by_path( 'about-us' );
		$id = $page ? $page->ID : 0;
	}
	$page = $id ? get_post( $id ) : null;
	if ( $page && 'publish' === $page->post_status && ! post_password_required( $page ) ) {
		echo apply_filters( 'the_content', $page->post_content );
		return true;
	}
	return false;
}

function micada_home_panel() {
	$section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
	if ( in_array( $section, array( 'rti', 'head-office-executives', 'field-executives', 'office-orders' ), true ) ) {
		$page = micada_public_page( $section );
		if ( $page && 'publish' === $page->post_status && ! post_password_required( $page ) ) {
			$content = apply_filters( 'the_content', $page->post_content );
			if ( 'rti' === $section && function_exists( 'micada_rti_replace_page_list' ) ) {
				$content = micada_rti_replace_page_list( $content );
			}
			if ( in_array( $section, array( 'head-office-executives', 'field-executives', 'office-orders' ), true ) && function_exists( 'micada_org_replace_page_content' ) ) {
				$content = micada_org_replace_page_content( $section, $content );
			}
			echo '<div class="micada-cms-panel">' . $content . '</div>';
			return;
		}
	}
	if ( ! micada_home_editor_content( 'about' ) ) {
		require get_stylesheet_directory() . '/template-parts/home/about-default.php';
	}
}
