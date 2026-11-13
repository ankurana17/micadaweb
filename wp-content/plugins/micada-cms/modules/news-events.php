<?php
/**
 * Module: News & Events
 * Description: Stores and manages homepage news and events in custom DB table.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_news_events_table' ) ) {
function micada_news_events_table() {
	global $wpdb;
	return $wpdb->prefix . 'news_events';
}
}


if ( ! function_exists( 'micada_news_events_install' ) ) {
function micada_news_events_install() {
	global $wpdb;
	$table = micada_news_events_table();
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		title_en text NOT NULL,
		description_en text NOT NULL,
		title_hi text NOT NULL,
		description_hi text NOT NULL,
		url text NOT NULL,
		new_tab tinyint(1) NOT NULL DEFAULT 0,
		show_new tinyint(1) NOT NULL DEFAULT 0,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY sort_order (sort_order),
		KEY list_status (display_status)
	) {$charset_collate};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	if ( get_option( 'micada_news_events_seeded' ) ) {
		return;
	}
	if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) > 0 ) {
		update_option( 'micada_news_events_seeded', 1 );
		return;
	}
	$path = get_stylesheet_directory() . '/assets/data/translations.json';
	$translations = is_readable( $path ) ? json_decode( file_get_contents( $path ), true ) : array();
	$en = $translations['en'] ?? array();
	$hi = $translations['hd'] ?? array();
	$wpdb->insert(
		$table,
		array(
			'title_en' => $en['FARMER_ONLINE_APPLICATION_FOR_MI'] ?? 'Farmer Online Application for MI',
			'description_en' => $en['FARMER_ONLINE_APPLICATION_HAS_BEEN_INAUGURATED'] ?? '',
			'title_hi' => $hi['FARMER_ONLINE_APPLICATION_FOR_MI'] ?? '',
			'description_hi' => $hi['FARMER_ONLINE_APPLICATION_HAS_BEEN_INAUGURATED'] ?? '',
			'url' => '',
			'new_tab' => 0,
			'show_new' => 0,
			'sort_order' => 1,
		),
		array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d' )
	);
	update_option( 'micada_news_events_seeded', 1 );
}
}

add_action( 'init', 'micada_news_events_install', 1 );

if ( ! function_exists( 'micada_news_events_admin_menu' ) ) {
function micada_news_events_admin_menu() {
	add_menu_page( __( 'News & Events', 'micada' ), __( 'News & Events', 'micada' ), 'manage_options', 'micada-news-events', 'micada_news_events_admin_page', 'dashicons-megaphone', 23 );
}
}

add_action( 'admin_menu', 'micada_news_events_admin_menu' );

if ( ! function_exists( 'micada_news_events_admin_url' ) ) {
function micada_news_events_admin_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'micada-news-events' ), $args ), admin_url( 'admin.php' ) );
}
}


if ( ! function_exists( 'micada_news_events_save' ) ) {
function micada_news_events_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage News & Events.', 'micada' ) );
	}
	check_admin_referer( 'micada_news_events_save' );
	global $wpdb;
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$order_raw = isset( $_POST['sort_order'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ) ) ) : '0';
	$order_valid = 1 === preg_match( '/^\d+$/', $order_raw );
	$url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
	$data = array(
		'title_en' => sanitize_text_field( wp_unslash( $_POST['title_en'] ?? '' ) ),
		'description_en' => sanitize_textarea_field( wp_unslash( $_POST['description_en'] ?? '' ) ),
		'title_hi' => sanitize_text_field( wp_unslash( $_POST['title_hi'] ?? '' ) ),
		'description_hi' => sanitize_textarea_field( wp_unslash( $_POST['description_hi'] ?? '' ) ),
		'url' => $url,
		'new_tab' => isset( $_POST['new_tab'] ) ? 1 : 0,
		'show_new' => isset( $_POST['show_new'] ) ? 1 : 0,
		'sort_order' => $order_valid ? (int) $order_raw : 0,
	);
	if ( '' === $data['title_en'] || ! $order_valid || ( '' !== $url && ! wp_http_validate_url( $url ) ) ) {
		wp_safe_redirect( micada_news_events_admin_url( array( 'message' => 'invalid' ) ) );
		exit;
	}
	$table = micada_news_events_table();
	if ( $id ) {
		$wpdb->update( $table, $data, array( 'id' => $id ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d' ), array( '%d' ) );
	} else {
		$wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d' ) );
	}
	wp_safe_redirect( micada_news_events_admin_url( array( 'message' => 'saved' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_news_events_save', 'micada_news_events_save' );

if ( ! function_exists( 'micada_news_events_delete' ) ) {
function micada_news_events_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage News & Events.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	check_admin_referer( 'micada_news_events_delete_' . $id );
	global $wpdb;
	$wpdb->delete( micada_news_events_table(), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( micada_news_events_admin_url( array( 'message' => 'deleted' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_news_events_delete', 'micada_news_events_delete' );

if ( ! function_exists( 'micada_news_events_admin_page' ) ) {
function micada_news_events_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = micada_news_events_table();
	$next_order = 1 + (int) $wpdb->get_var( "SELECT COALESCE(MAX(sort_order), 0) FROM {$table}" );
	$edit_id = absint( $_GET['edit'] ?? 0 );
	$item = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id ), ARRAY_A ) : null;
	$defaults = array( 'id' => 0, 'title_en' => '', 'description_en' => '', 'title_hi' => '', 'description_hi' => '', 'url' => '', 'new_tab' => 0, 'show_new' => 0, 'sort_order' => $next_order );
	$item = $item ? $item : $defaults;
	if ( isset( $_GET['message'] ) ) {
		$messages = array( 'saved' => __( 'News item saved.', 'micada' ), 'deleted' => __( 'News item deleted.', 'micada' ), 'invalid' => __( 'Enter an English title, a non-negative whole number for display order, and a valid URL when provided.', 'micada' ) );
		$key = sanitize_key( wp_unslash( $_GET['message'] ) );
		if ( isset( $messages[ $key ] ) ) {
			echo '<div class="notice ' . ( 'invalid' === $key ? 'notice-error' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html( $messages[ $key ] ) . '</p></div>';
		}
	}
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE display_status = %s ORDER BY sort_order DESC, id DESC", $view ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'News & Events', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-news-event-add"><?php esc_html_e( 'Add New Event', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'Manage the bilingual news items shown in the homepage News & Events panel. Higher display order values appear first.', 'micada' ); ?></p>
		<hr><h2><?php esc_html_e( 'Homepage News & Events', 'micada' ); ?></h2>
		<?php micada_cms_status_tabs( 'micada-news-events', $table, $view ); ?>
		<table id="micada-news-events-table" class="widefat striped"><thead><tr><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php esc_html_e( 'English title', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi title', 'micada' ); ?></th><th><?php esc_html_e( 'Description', 'micada' ); ?></th><th><?php esc_html_e( 'URL', 'micada' ); ?></th><th><?php esc_html_e( 'Options', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php if ( $items ) : foreach ( $items as $event ) : ?>
			<tr><td><?php echo esc_html( $event->sort_order ); ?></td><td><?php echo esc_html( $event->title_en ); ?></td><td><?php echo esc_html( $event->title_hi ); ?></td><td><?php echo esc_html( $event->description_en ); ?></td><td><?php if ( $event->url ) : ?><a href="<?php echo esc_url( $event->url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $event->url ); ?></a><?php endif; ?></td><td><?php echo esc_html( implode( ', ', array_filter( array( $event->new_tab ? __( 'New tab', 'micada' ) : '', $event->show_new ? __( 'New badge', 'micada' ) : '' ) ) ) ); ?></td><td><?php echo 'trash' === $event->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><button type="button" class="button-link micada-news-event-edit" data-id="<?php echo esc_attr( $event->id ); ?>" data-title-en="<?php echo esc_attr( $event->title_en ); ?>" data-description-en="<?php echo esc_attr( $event->description_en ); ?>" data-title-hi="<?php echo esc_attr( $event->title_hi ); ?>" data-description-hi="<?php echo esc_attr( $event->description_hi ); ?>" data-url="<?php echo esc_attr( $event->url ); ?>" data-sort-order="<?php echo esc_attr( $event->sort_order ); ?>" data-new-tab="<?php echo esc_attr( $event->new_tab ); ?>" data-show-new="<?php echo esc_attr( $event->show_new ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <?php micada_cms_trash_action_links( 'news-events', $event->id, $event->display_status ); ?></td></tr>
		<?php endforeach; else : ?><tr><td colspan="7"><?php esc_html_e( 'No News & Events found.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div id="micada-news-event-modal" class="micada-news-event-modal<?php echo $edit_id ? ' is-open' : ''; ?>" aria-hidden="<?php echo $edit_id ? 'false' : 'true'; ?>">
			<div class="micada-news-event-backdrop" data-news-event-close></div>
			<section class="micada-news-event-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-news-event-modal-title">
				<header><h2 id="micada-news-event-modal-title"><?php echo $edit_id ? esc_html__( 'Edit News Item', 'micada' ) : esc_html__( 'Add New Event', 'micada' ); ?></h2><button type="button" class="micada-news-event-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-news-event-close>&times;</button></header>
				<form id="micada-news-event-form" method="post" autocomplete="off" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="micada_news_events_save"><input type="hidden" id="micada-news-event-id" name="id" value="<?php echo esc_attr( $item['id'] ); ?>">
					<?php wp_nonce_field( 'micada_news_events_save' ); ?>
					<p><label for="micada-news-event-title-en"><strong><?php esc_html_e( 'English title', 'micada' ); ?></strong></label><input class="widefat" required id="micada-news-event-title-en" name="title_en" value="<?php echo esc_attr( $item['title_en'] ); ?>"></p>
					<p><label for="micada-news-event-description-en"><strong><?php esc_html_e( 'English description', 'micada' ); ?></strong></label><textarea class="widefat" rows="3" id="micada-news-event-description-en" name="description_en"><?php echo esc_textarea( $item['description_en'] ); ?></textarea></p>
					<p><label for="micada-news-event-title-hi"><strong><?php esc_html_e( 'Hindi title', 'micada' ); ?></strong></label><input class="widefat" id="micada-news-event-title-hi" name="title_hi" value="<?php echo esc_attr( $item['title_hi'] ); ?>"></p>
					<p><label for="micada-news-event-description-hi"><strong><?php esc_html_e( 'Hindi description', 'micada' ); ?></strong></label><textarea class="widefat" rows="3" id="micada-news-event-description-hi" name="description_hi"><?php echo esc_textarea( $item['description_hi'] ); ?></textarea></p>
					<p><label for="micada-news-event-url"><strong><?php esc_html_e( 'URL (optional)', 'micada' ); ?></strong></label><input class="widefat" type="url" id="micada-news-event-url" name="url" value="<?php echo esc_attr( $item['url'] ); ?>"></p>
					<p><label for="micada-news-event-order"><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong></label><input type="number" min="0" step="1" required id="micada-news-event-order" name="sort_order" value="<?php echo esc_attr( $item['sort_order'] ); ?>" autocomplete="off"></p>
					<p><label><input type="checkbox" id="micada-news-event-new-tab" name="new_tab" value="1" <?php checked( $item['new_tab'], 1 ); ?>> <?php esc_html_e( 'Open in a new tab', 'micada' ); ?></label><br><label><input type="checkbox" id="micada-news-event-show-new" name="show_new" value="1" <?php checked( $item['show_new'], 1 ); ?>> <?php esc_html_e( 'Show “New” badge', 'micada' ); ?></label></p>
					<div class="micada-news-event-modal-actions"><button type="submit" class="button button-primary" id="micada-news-event-submit"><?php echo $edit_id ? esc_html__( 'Update Event', 'micada' ) : esc_html__( 'Add Event', 'micada' ); ?></button><button type="button" class="button" data-news-event-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
				</form>
			</section>
		</div>
	</div>
	<style>
		#micada-news-events-table th:last-child,#micada-news-events-table td:last-child{width:130px;min-width:130px;white-space:nowrap}
		.micada-news-event-modal{display:none;position:fixed;inset:0;z-index:100100;align-items:center;justify-content:center;padding:24px}
		.micada-news-event-modal.is-open{display:flex}
		.micada-news-event-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}
		.micada-news-event-dialog{position:relative;width:min(680px,100%);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}
		.micada-news-event-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
		.micada-news-event-dialog header h2{margin:0;font-size:21px}
		.micada-news-event-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}
		.micada-news-event-dialog form>p{margin:0 0 16px}
		.micada-news-event-dialog form>p>label{display:block;margin-bottom:6px}
		.micada-news-event-modal-actions{display:flex;gap:8px;margin-top:22px}
		body.micada-news-event-modal-open{overflow:hidden}
	</style>
	<script>
	(function(){
		const modal=document.getElementById('micada-news-event-modal');
		const form=document.getElementById('micada-news-event-form');
		const title=document.getElementById('micada-news-event-modal-title');
		const submit=document.getElementById('micada-news-event-submit');
		const open=()=>{modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-news-event-modal-open');document.getElementById('micada-news-event-title-en').focus();};
		const close=()=>{modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-news-event-modal-open');};
		document.getElementById('micada-news-event-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.title_en.value='';form.elements.description_en.value='';form.elements.title_hi.value='';form.elements.description_hi.value='';form.elements.url.value='';form.elements.sort_order.value='<?php echo esc_js( (string) $next_order ); ?>';form.elements.new_tab.checked=false;form.elements.show_new.checked=false;title.textContent='<?php echo esc_js( __( 'Add New Event', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Add Event', 'micada' ) ); ?>';open();});
		document.querySelectorAll('.micada-news-event-edit').forEach(function(button){button.addEventListener('click',function(){form.elements.id.value=button.dataset.id;form.elements.title_en.value=button.dataset.titleEn;form.elements.description_en.value=button.dataset.descriptionEn;form.elements.title_hi.value=button.dataset.titleHi;form.elements.description_hi.value=button.dataset.descriptionHi;form.elements.url.value=button.dataset.url;form.elements.sort_order.value=button.dataset.sortOrder;form.elements.new_tab.checked=button.dataset.newTab==='1';form.elements.show_new.checked=button.dataset.showNew==='1';title.textContent='<?php echo esc_js( __( 'Edit News Item', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Update Event', 'micada' ) ); ?>';open();});});
		modal.querySelectorAll('[data-news-event-close]').forEach(function(button){button.addEventListener('click',close);});
		document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});
	})();
	</script>
	<?php
}
}


if ( ! function_exists( 'micada_render_managed_news_events' ) ) {
function micada_render_managed_news_events() {
	global $wpdb;
	$table = micada_news_events_table();
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return false;
	}
	$items = $wpdb->get_results( "SELECT * FROM {$table} WHERE display_status = 'post' ORDER BY sort_order DESC, id DESC" );
	if ( ! $items ) {
		return false;
	}
	$language = function_exists( 'micada_language' ) ? micada_language() : 'en';
	echo '<marquee direction="up" scrollamount="3" data-pause-marquee style="max-height:300px"><ul class="w3-ul" style="font-size:12px">';
	foreach ( $items as $event ) {
		$title = 'hd' === $language && '' !== $event->title_hi ? $event->title_hi : $event->title_en;
		$description = 'hd' === $language && '' !== $event->description_hi ? $event->description_hi : $event->description_en;
		echo '<li class="w3-padding-16 w3-left-align"><b>';
		if ( '' !== $event->url ) {
			echo '<a href="' . esc_url( micada_resolve_app_link( $event->url ) ) . '"' . ( $event->new_tab ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $title ) . '</a>';
		} else {
			echo esc_html( $title );
		}
		if ( $event->show_new && function_exists( 'micada_asset' ) ) {
			echo ' <img src="' . esc_url( micada_asset( 'activitiesImages/newitem.gif' ) ) . '" alt="' . esc_attr__( 'New', 'micada' ) . '">';
		}
		echo '</b>';
		if ( '' !== $description ) {
			echo '<br><p>' . esc_html( $description ) . '</p>';
		}
		echo '</li>';
	}
	echo '<li class="w3-padding-24"></li><li class="w3-padding-16"></li>';
	echo '</ul></marquee>';
	return true;
}
}

