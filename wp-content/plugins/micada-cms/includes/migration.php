<?php
/**
 * Content and Database Migration for MICADA CMS.
 */

defined( 'ABSPATH' ) || exit;

/** Return the table name or getter for each table in the bundled content snapshot. */
function micada_cms_snapshot_table_map() {
	return array(
		'quick_links'                       => 'micada_quick_links_table',
		'news_events'                       => 'micada_news_events_table',
		'micada_faq_documents'              => 'micada_faq_table',
		'micada_important_links'            => 'micada_important_links_table',
		'photo_gallery'                     => 'micada_gallery_photo_table',
		'video_gallery'                     => 'micada_gallery_video_table',
		'homepage_messages'                 => 'micada_home_ticker_table',
		'homepage_notices'                  => 'micada_home_notice_table',
		'important_messages'                => 'micada_important_message_table',
		'homepage_popup'                    => 'micada_popup_table',
		'micada_head_office_executives'     => static function() { return function_exists( 'micada_org_table' ) ? micada_org_table( 'head-office-executives' ) : ''; },
		'micada_field_executives'           => static function() { return function_exists( 'micada_org_table' ) ? micada_org_table( 'field-executives' ) : ''; },
		'micada_office_orders'              => static function() { return function_exists( 'micada_org_table' ) ? micada_org_table( 'office-orders' ) : ''; },
		'state_public_information_officers' => 'micada_rti_table',
	);
}

/** Resolve the real table name from callback or string. */
function micada_cms_resolve_table_name( $callback ) {
	if ( is_callable( $callback ) ) {
		return call_user_func( $callback );
	}
	if ( is_string( $callback ) && function_exists( $callback ) ) {
		return call_user_func( $callback );
	}
	if ( is_string( $callback ) && '' !== $callback ) {
		global $wpdb;
		return $wpdb->prefix . $callback;
	}
	return '';
}

/** Records from these legacy tables are checked before snapshot import. */
function micada_cms_snapshot_has_legacy_source( $suffix ) {
	global $wpdb;
	$legacy_suffixes = array(
		'quick_links'                       => array( 'micada_quick_links' ),
		'homepage_messages'                 => array( 'homepage_ticker' ),
		'state_public_information_officers' => array( 'rti_officers' ),
		'micada_head_office_executives'     => array( 'micada_organisation_items' ),
		'micada_field_executives'           => array( 'micada_organisation_items' ),
		'micada_office_orders'              => array( 'micada_organisation_items' ),
	);
	foreach ( $legacy_suffixes[ $suffix ] ?? array() as $legacy_suffix ) {
		$table = $wpdb->prefix . $legacy_suffix;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table && (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ) > 0 ) {
			return true;
		}
	}
	return false;
}

/** Rewrite URLs from the source site root to this WordPress installation. */
function micada_cms_rewrite_snapshot_urls( $value, $source_url, $target_url ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $nested ) {
			$value[ $key ] = micada_cms_rewrite_snapshot_urls( $nested, $source_url, $target_url );
		}
		return $value;
	}
	if ( is_string( $value ) && '' !== $source_url && 0 === strpos( $value, $source_url ) ) {
		return $target_url . substr( $value, strlen( $source_url ) );
	}
	return $value;
}

/** Import referenced upload records and map source attachment IDs to this site. */
function micada_cms_import_initial_attachments( $attachments ) {
	global $wpdb;
	$map = array();
	if ( empty( $attachments ) || ! is_array( $attachments ) ) {
		return $map;
	}
	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return $map;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	foreach ( $attachments as $attachment ) {
		$source_id = absint( $attachment['source_id'] ?? 0 );
		$relative_file = ltrim( str_replace( '\\', '/', (string) ( $attachment['file'] ?? '' ) ), '/' );
		if ( ! $source_id || '' === $relative_file ) {
			continue;
		}
		$existing_id = absint( $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
			'_wp_attached_file',
			$relative_file
		) ) );
		if ( $existing_id ) {
			$map[ $source_id ] = $existing_id;
			continue;
		}
		$file_path = trailingslashit( $uploads['basedir'] ) . $relative_file;
		if ( ! is_file( $file_path ) ) {
			continue;
		}
		$new_id = wp_insert_attachment( array(
			'post_mime_type' => sanitize_mime_type( $attachment['mime'] ?? 'image/jpeg' ),
			'post_title'     => sanitize_text_field( $attachment['title'] ?? '' ),
			'post_excerpt'   => sanitize_text_field( $attachment['caption'] ?? '' ),
			'post_content'   => sanitize_textarea_field( $attachment['description'] ?? '' ),
			'post_status'    => 'inherit',
		), $file_path, 0, true );
		if ( is_wp_error( $new_id ) || ! $new_id ) {
			continue;
		}
		update_post_meta( $new_id, '_wp_attached_file', $relative_file );
		if ( ! empty( $attachment['alt'] ) ) {
			update_post_meta( $new_id, '_wp_attachment_image_alt', sanitize_text_field( $attachment['alt'] ) );
		}
		$metadata = wp_generate_attachment_metadata( $new_id, $file_path );
		if ( ! empty( $metadata ) ) {
			wp_update_attachment_metadata( $new_id, $metadata );
		}
		$map[ $source_id ] = (int) $new_id;
	}
	return $map;
}

/** Import Officials and Banners records if target post type is empty or forced. */
function micada_cms_import_initial_posts( $posts, $empty_post_types = array(), $attachment_map = array(), $post_types = array( 'micada_official', 'micada_banner' ), $force = false ) {
	if ( empty( $posts ) || ! is_array( $posts ) ) {
		return;
	}
	global $wpdb;
	$allowed_statuses = array( 'publish', 'draft', 'pending', 'private', 'trash' );
	foreach ( $post_types as $post_type ) {
		$option_key = 'micada_cms_posts_' . sanitize_key( $post_type );
		$current_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $post_type ) );
		if ( ! $force && $current_count > 0 ) {
			update_option( $option_key, 'imported', false );
			continue;
		}
		if ( $force && $current_count > 0 ) {
			$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", $post_type ) );
			foreach ( $post_ids as $pid ) {
				wp_delete_post( $pid, true );
			}
		}

		$success = true;
		foreach ( $posts as $record ) {
			if ( $post_type !== ( $record['post_type'] ?? '' ) ) {
				continue;
			}
			$status = in_array( $record['post_status'] ?? '', $allowed_statuses, true ) ? $record['post_status'] : 'draft';
			$new_id = wp_insert_post( array(
				'post_type'    => $post_type,
				'post_title'   => sanitize_text_field( $record['post_title'] ?? '' ),
				'post_content' => wp_kses_post( $record['post_content'] ?? '' ),
				'post_excerpt' => sanitize_textarea_field( $record['post_excerpt'] ?? '' ),
				'post_status'  => $status,
				'menu_order'   => (int) ( $record['menu_order'] ?? 0 ),
			), true );
			if ( is_wp_error( $new_id ) || ! $new_id ) {
				$success = false;
				break;
			}
			foreach ( (array) ( $record['meta'] ?? array() ) as $key => $values ) {
				if ( in_array( $key, array( '_thumbnail_id', '_edit_lock', '_edit_last' ), true ) ) {
					continue;
				}
				foreach ( (array) $values as $meta_value ) {
					update_post_meta( $new_id, sanitize_key( $key ), maybe_unserialize( $meta_value ) );
				}
			}
			$source_thumbnail = absint( $record['thumbnail_source_id'] ?? 0 );
			if ( $source_thumbnail && ! empty( $attachment_map[ $source_thumbnail ] ) ) {
				set_post_thumbnail( $new_id, $attachment_map[ $source_thumbnail ] );
			}
		}
		update_option( $option_key, $success ? 'imported' : 'partial', false );
	}
}

/** Import bundled CMS rows into tables. */
function micada_cms_import_initial_content( $empty_before_install = array(), $attachment_map = array(), $only_suffixes = array(), $force = false ) {
	$snapshot_path = dirname( __DIR__ ) . '/data/initial-content.json';
	if ( ! is_readable( $snapshot_path ) ) {
		return;
	}
	$snapshot = json_decode( file_get_contents( $snapshot_path ), true );
	if ( ! is_array( $snapshot ) || empty( $snapshot['tables'] ) || ! is_array( $snapshot['tables'] ) ) {
		return;
	}

	global $wpdb;
	$table_map = micada_cms_snapshot_table_map();
	$source_url = untrailingslashit( (string) ( $snapshot['source_url'] ?? '' ) );
	$target_url = untrailingslashit( home_url( '/' ) );

	foreach ( $table_map as $suffix => $table_callback ) {
		if ( ! empty( $only_suffixes ) && ! in_array( $suffix, $only_suffixes, true ) ) {
			continue;
		}
		$option_key = 'micada_cms_snapshot_' . sanitize_key( $suffix );
		$table = micada_cms_resolve_table_name( $table_callback );
		if ( ! $table ) {
			continue;
		}

		// Check if table exists in DB. If not, try to install it.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		if ( ! $table_exists ) {
			if ( function_exists( 'micada_cms_install_all_tables' ) ) {
				micada_cms_install_all_tables();
			}
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		}
		if ( ! $table_exists ) {
			continue;
		}

		$rows = $snapshot['tables'][ $suffix ] ?? array();
		if ( empty( $rows ) ) {
			continue;
		}

		$current_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );

		// If table is present and has data, and not forced, check if rows need upgrade
		if ( ! $force && $current_count > 0 ) {
			if ( 'quick_links' === $suffix && function_exists( 'micada_quick_links_upgrade_existing_rows' ) ) {
				micada_quick_links_upgrade_existing_rows( $table );
			}
			update_option( $option_key, 'imported', false );
			continue;
		}

		// If table is present but has 0 rows (or force is true), migrate data!
		if ( $force && $current_count > 0 ) {
			$wpdb->query( "DELETE FROM `{$table}`" );
		}

		$columns = $wpdb->get_col( "DESCRIBE `{$table}`", 0 );
		$allowed_columns = array_fill_keys( $columns, true );
		$success = true;

		foreach ( $rows as $row ) {
			$row = micada_cms_rewrite_snapshot_urls( $row, $source_url, $target_url );

			// Quick Links field mapping for backward & forward compatibility
			if ( 'quick_links' === $suffix ) {
				if ( empty( $row['label_en'] ) && ! empty( $row['title_en'] ) ) {
					$row['label_en'] = $row['title_en'];
				}
				if ( ( ! isset( $row['label_hi'] ) || '' === $row['label_hi'] ) && ! empty( $row['title_hd'] ) ) {
					$row['label_hi'] = $row['title_hd'];
				}
				if ( ! isset( $row['new_tab'] ) ) {
					$row['new_tab'] = ( '_blank' === ( $row['target'] ?? '' ) ) ? 1 : 0;
				}
				if ( ! isset( $row['show_new'] ) && isset( $row['is_new'] ) ) {
					$row['show_new'] = absint( $row['is_new'] );
				}
				if ( ! isset( $row['sort_order'] ) || 0 === (int) $row['sort_order'] ) {
					if ( isset( $row['display_order'] ) ) {
						$row['sort_order'] = absint( $row['display_order'] );
					}
				}
				if ( empty( $row['display_status'] ) || 'active' === $row['display_status'] ) {
					$row['display_status'] = ( 'trash' === ( $row['status'] ?? '' ) ) ? 'trash' : 'post';
				}
			}

			if ( is_array( $row ) ) {
				$row = array_intersect_key( $row, $allowed_columns );
			}

			if ( 'micada_important_links' === $suffix && ! empty( $row['image_id'] ) ) {
				$old_attachment_id = absint( $row['image_id'] );
				$row['image_id'] = $attachment_map[ $old_attachment_id ] ?? 0;
			}
			if ( 'homepage_popup' === $suffix ) {
				foreach ( array( 'image_attachment_id', 'default_image_attachment_id' ) as $attachment_column ) {
					$old_attachment_id = absint( $row[ $attachment_column ] ?? 0 );
					if ( ! empty( $attachment_map[ $old_attachment_id ] ) ) {
						$row[ $attachment_column ] = $attachment_map[ $old_attachment_id ];
					} elseif ( $old_attachment_id ) {
						$row[ $attachment_column ] = 0;
					}
				}
			}

			if ( ! is_array( $row ) || false === $wpdb->insert( $table, $row ) ) {
				$success = false;
				break;
			}
		}

		if ( $success ) {
			if ( 'quick_links' === $suffix && function_exists( 'micada_quick_links_upgrade_existing_rows' ) ) {
				micada_quick_links_upgrade_existing_rows( $table );
			}
			update_option( $option_key, 'imported', false );
		}
	}
}

/**
 * Migrate all data for all modules:
 * - If tables are not present, make tables in DB.
 * - If tables are present, migrate all data.
 * - If legacy tables exist, import legacy records.
 */
function micada_cms_migrate_all_data( $force = false ) {
	global $wpdb;

	// 1. Ensure all tables are created first
	if ( function_exists( 'micada_cms_install_all_tables' ) ) {
		micada_cms_install_all_tables();
	}

	// 2. Read snapshot
	$snapshot_path = dirname( __DIR__ ) . '/data/initial-content.json';
	$snapshot = is_readable( $snapshot_path ) ? json_decode( file_get_contents( $snapshot_path ), true ) : array();

	// 3. Attachments map
	$attachment_map = micada_cms_import_initial_attachments( $snapshot['attachments'] ?? array() );

	// 4. Custom Post Types (Officials & Banners)
	$cpt_definitions = array( 'micada_official', 'micada_banner' );
	$empty_post_types = array();
	foreach ( $cpt_definitions as $pt ) {
		$empty_post_types[ $pt ] = 0 === (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $pt ) );
	}
	micada_cms_import_initial_posts( $snapshot['posts'] ?? array(), $empty_post_types, $attachment_map, $cpt_definitions, $force );

	// Seed banners if still empty
	if ( function_exists( 'micada_seed_banners_if_empty' ) ) {
		micada_seed_banners_if_empty();
	}

	// Migrate official Hindi fields if needed
	if ( function_exists( 'micada_migrate_official_hindi_fields' ) ) {
		micada_migrate_official_hindi_fields();
	}

	// 5. Migrate custom tables
	micada_cms_import_initial_content( array(), $attachment_map, array(), $force );

	// 6. Ensure Quick Links legacy & column upgrades
	if ( function_exists( 'micada_quick_links_upgrade_existing_rows' ) ) {
		micada_quick_links_upgrade_existing_rows();
	}

	// 7. Migrate display_status columns & indexes
	if ( function_exists( 'micada_cms_migrate_display_status_columns' ) ) {
		micada_cms_migrate_display_status_columns();
	}

	// Flush rewrites
	flush_rewrite_rules();
}

/** Get migration status overview for all tables and post types. */
function micada_cms_get_status_overview() {
	global $wpdb;
	$snapshot_path = dirname( __DIR__ ) . '/data/initial-content.json';
	$snapshot = is_readable( $snapshot_path ) ? json_decode( file_get_contents( $snapshot_path ), true ) : array();

	$overview = array();

	// Tables
	$table_map = micada_cms_snapshot_table_map();
	foreach ( $table_map as $suffix => $callback ) {
		$table = micada_cms_resolve_table_name( $callback );
		$exists = $table && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		$count = $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ) : 0;
		$snapshot_count = count( $snapshot['tables'][ $suffix ] ?? array() );
		$import_status = get_option( 'micada_cms_snapshot_' . sanitize_key( $suffix ), 'pending' );

		$overview[] = array(
			'type'           => 'table',
			'key'            => $suffix,
			'name'           => ucwords( str_replace( array( 'micada_', '_' ), ' ', $suffix ) ),
			'target'         => $table,
			'exists'         => $exists,
			'count'          => $count,
			'snapshot_count' => $snapshot_count,
			'status'         => $exists && $count > 0 ? 'active' : ( $exists ? 'empty' : 'missing' ),
			'import_status'  => $import_status,
		);
	}

	// Post Types
	foreach ( array( 'micada_official' => 'Officials', 'micada_banner' => 'Banners' ) as $pt => $label ) {
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", $pt ) );
		$snap_count = 0;
		foreach ( (array) ( $snapshot['posts'] ?? array() ) as $sp ) {
			if ( ( $sp['post_type'] ?? '' ) === $pt ) {
				$snap_count++;
			}
		}
		$import_status = get_option( 'micada_cms_posts_' . sanitize_key( $pt ), 'pending' );

		$overview[] = array(
			'type'           => 'post_type',
			'key'            => $pt,
			'name'           => $label,
			'target'         => "post_type: $pt",
			'exists'         => true,
			'count'          => $count,
			'snapshot_count' => $snap_count,
			'status'         => $count > 0 ? 'active' : 'empty',
			'import_status'  => $import_status,
		);
	}

	return $overview;
}

/** Admin menu for migration tools. */
function micada_cms_migration_admin_menu() {
	add_management_page(
		__( 'MICADA CMS Migration', 'micada' ),
		__( 'MICADA CMS Migration', 'micada' ),
		'manage_options',
		'micada-cms-migration',
		'micada_cms_migration_admin_page'
	);
}
add_action( 'admin_menu', 'micada_cms_migration_admin_menu' );

/** Handle migration actions. */
function micada_cms_handle_migration_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'micada' ) );
	}
	check_admin_referer( 'micada_cms_run_migration' );
	$force = isset( $_POST['force_remigrate'] );
	micada_cms_migrate_all_data( $force );
	wp_safe_redirect( add_query_arg( array( 'page' => 'micada-cms-migration', 'migrated' => $force ? 'forced' : 'success' ), admin_url( 'tools.php' ) ) );
	exit;
}
add_action( 'admin_post_micada_cms_run_migration', 'micada_cms_handle_migration_action' );

/** Render migration admin page. */
function micada_cms_migration_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$migrated = sanitize_key( wp_unslash( $_GET['migrated'] ?? '' ) );
	$overview = micada_cms_get_status_overview();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'MICADA CMS Migration & Database Tools', 'micada' ); ?></h1>
		<p><?php esc_html_e( 'Manage custom database tables and initial content migrations for all MICADA CMS modules.', 'micada' ); ?></p>
		
		<?php if ( 'success' === $migrated ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Migration completed successfully! All tables verified and missing data imported.', 'micada' ); ?></strong></p></div>
		<?php elseif ( 'forced' === $migrated ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Full re-sync completed! All tables reset and fresh initial content imported.', 'micada' ); ?></strong></p></div>
		<?php endif; ?>

		<div style="margin: 20px 0; display: flex; gap: 12px; align-items: center;">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="micada_cms_run_migration">
				<?php wp_nonce_field( 'micada_cms_run_migration' ); ?>
				<?php submit_button( __( 'Migrate Missing Data / Create Tables', 'micada' ), 'primary', 'submit', false ); ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to re-import all snapshot content? Existing CMS records will be refreshed.', 'micada' ) ); ?>');">
				<input type="hidden" name="action" value="micada_cms_run_migration">
				<input type="hidden" name="force_remigrate" value="1">
				<?php wp_nonce_field( 'micada_cms_run_migration' ); ?>
				<?php submit_button( __( 'Force Full Re-sync from Snapshot', 'micada' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Module / Component', 'micada' ); ?></th>
					<th><?php esc_html_e( 'Database Table / Target', 'micada' ); ?></th>
					<th><?php esc_html_e( 'Table Status', 'micada' ); ?></th>
					<th><?php esc_html_e( 'Current Rows', 'micada' ); ?></th>
					<th><?php esc_html_e( 'Snapshot Rows', 'micada' ); ?></th>
					<th><?php esc_html_e( 'Migration Status', 'micada' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $overview as $item ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $item['name'] ); ?></strong></td>
						<td><code><?php echo esc_html( $item['target'] ); ?></code></td>
						<td>
							<?php if ( $item['exists'] ) : ?>
								<span style="color: #007017; font-weight: bold;">&#10004; <?php esc_html_e( 'Present', 'micada' ); ?></span>
							<?php else : ?>
								<span style="color: #d63638; font-weight: bold;">&#10008; <?php esc_html_e( 'Missing', 'micada' ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $item['count'] ); ?></td>
						<td><?php echo esc_html( $item['snapshot_count'] ); ?></td>
						<td>
							<?php if ( 'active' === $item['status'] ) : ?>
								<span class="badge" style="background:#e7f7ed;color:#007017;padding:3px 8px;border-radius:4px;font-weight:600;"><?php esc_html_e( 'Migrated', 'micada' ); ?></span>
							<?php elseif ( 'empty' === $item['status'] ) : ?>
								<span class="badge" style="background:#fff8e5;color:#b26200;padding:3px 8px;border-radius:4px;font-weight:600;"><?php esc_html_e( 'Empty / Needs Data', 'micada' ); ?></span>
							<?php else : ?>
								<span class="badge" style="background:#fce8e6;color:#d63638;padding:3px 8px;border-radius:4px;font-weight:600;"><?php esc_html_e( 'Not Created', 'micada' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
