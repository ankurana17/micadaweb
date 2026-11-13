<?php
/**
 * Module: FAQs
 * Description: Manages bilingual FAQ documents and navigation.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'micada_faq_table' ) ) {
function micada_faq_table() {
	global $wpdb;
	return $wpdb->prefix . 'micada_faq_documents';
}
}


if ( ! function_exists( 'micada_faq_install' ) ) {
function micada_faq_install() {
	global $wpdb;
	$table = micada_faq_table();
	if ( '2' === get_option( 'micada_faq_schema' ) && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		micada_faq_migrate_defaults();
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		question_en varchar(255) NOT NULL,
		question_hi varchar(255) NOT NULL DEFAULT '',
		document_url text NOT NULL,
		sort_order int(11) NOT NULL DEFAULT 0,
		display_status varchar(10) NOT NULL DEFAULT 'post',
		PRIMARY KEY  (id),
		KEY item_order (sort_order),
		KEY list_status (display_status)
	) {$charset_collate};";
	dbDelta( $sql );
	update_option( 'micada_faq_schema', '2', false );
	micada_faq_migrate_defaults();
}
}

add_action( 'init', 'micada_faq_install', 1 );

if ( ! function_exists( 'micada_faq_find_upload' ) ) {
function micada_faq_find_upload( $filename ) {
	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) || ! is_dir( $uploads['basedir'] ) ) {
		return '';
	}
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $uploads['basedir'], FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $file ) {
		if ( $file->isFile() && 0 === strcasecmp( $file->getFilename(), $filename ) ) {
			$relative = ltrim( str_replace( DIRECTORY_SEPARATOR, '/', substr( $file->getPathname(), strlen( $uploads['basedir'] ) ) ), '/' );
			return trailingslashit( $uploads['baseurl'] ) . implode( '/', array_map( 'rawurlencode', explode( '/', $relative ) ) );
		}
	}
	return '';
}
}


if ( ! function_exists( 'micada_faq_migrate_defaults' ) ) {
function micada_faq_migrate_defaults() {
	if ( '1' === get_option( 'micada_faq_migrated' ) ) {
		return;
	}
	global $wpdb;
	$table = micada_faq_table();
	$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
	if ( $count > 0 ) {
		update_option( 'micada_faq_migrated', '1', false );
		return;
	}
	$items = array(
		array( 'Question on benefits of Drip Irrigation', 'ड्रिप इरिगेशन के फायदों पर प्रश्न', 'FAQ1.pdf', 'https://micadaharyana.nic.in/FAQ1.pdf' ),
		array( 'Micro Irrigation schemes and their benefits to farmers', 'सूक्ष्म सिंचाई योजनाए और उनसे किसानो को होने वाले लाभ', 'FAQ2.pdf', 'https://micadaharyana.nic.in/FAQ2.pdf' ),
		array( 'Questions related to integrated MICADA portal', 'एकीकृत मिकाडा पोर्टल से संबंधित प्रश्न', 'FAQ3.pdf', 'https://micadaharyana.nic.in/FAQ3.pdf' ),
	);
	foreach ( $items as $index => $item ) {
		$url = micada_faq_find_upload( $item[2] );
		if ( ! $url ) {
			$url = esc_url_raw( $item[3] );
		}
		$result = $wpdb->insert( $table, array(
			'question_en' => $item[0],
			'question_hi' => $item[1],
			'document_url' => $url,
			'sort_order' => $index + 1,
		), array( '%s', '%s', '%s', '%d' ) );
		if ( false === $result ) {
			return;
		}
	}
	update_option( 'micada_faq_migrated', '1', false );
}
}


if ( ! function_exists( 'micada_faq_rows' ) ) {
function micada_faq_rows( $admin = false, $status = 'post' ) {
	global $wpdb;
	$order = $admin ? 'id DESC' : 'sort_order ASC, id ASC';
	return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . micada_faq_table() . ' WHERE display_status = %s ORDER BY ' . $order, $status ) );
}
}


if ( ! function_exists( 'micada_faq_admin_menu' ) ) {
function micada_faq_admin_menu() {
	add_menu_page( __( 'FAQs', 'micada' ), __( 'FAQs', 'micada' ), 'manage_options', 'micada-faqs', 'micada_faq_admin_page', 'dashicons-editor-help', 29 );
}
}

add_action( 'admin_menu', 'micada_faq_admin_menu' );

if ( ! function_exists( 'micada_faq_admin_url' ) ) {
function micada_faq_admin_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'micada-faqs' ), $args ), admin_url( 'admin.php' ) );
}
}


if ( ! function_exists( 'micada_faq_save' ) ) {
function micada_faq_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage FAQ documents.', 'micada' ) );
	}
	check_admin_referer( 'micada_faq_save' );
	global $wpdb;
	$id = absint( $_POST['id'] ?? 0 );
	$order_raw = trim( sanitize_text_field( wp_unslash( $_POST['sort_order'] ?? '' ) ) );
	$data = array(
		'question_en' => sanitize_text_field( wp_unslash( $_POST['question_en'] ?? '' ) ),
		'question_hi' => sanitize_text_field( wp_unslash( $_POST['question_hi'] ?? '' ) ),
		'document_url' => esc_url_raw( wp_unslash( $_POST['document_url'] ?? '' ) ),
		'sort_order' => 1 === preg_match( '/^\d+$/', $order_raw ) ? (int) $order_raw : -1,
	);
	if ( '' === $data['question_en'] || '' === $data['document_url'] || $data['sort_order'] < 0 ) {
		wp_safe_redirect( micada_faq_admin_url( array( 'message' => 'invalid', 'edit' => $id ) ) );
		exit;
	}
	$formats = array( '%s', '%s', '%s', '%d' );
	$saved = $id ? false !== $wpdb->update( micada_faq_table(), $data, array( 'id' => $id ), $formats, array( '%d' ) ) : false !== $wpdb->insert( micada_faq_table(), $data, $formats );
	wp_safe_redirect( micada_faq_admin_url( array( 'message' => $saved ? 'saved' : 'error' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_faq_save', 'micada_faq_save' );

if ( ! function_exists( 'micada_faq_delete' ) ) {
function micada_faq_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage FAQ documents.', 'micada' ) );
	}
	$id = absint( $_GET['id'] ?? 0 );
	check_admin_referer( 'micada_faq_delete_' . $id );
	global $wpdb;
	$deleted = false !== $wpdb->delete( micada_faq_table(), array( 'id' => $id, 'display_status' => 'trash' ), array( '%d', '%s' ) );
	wp_safe_redirect( micada_faq_admin_url( array( 'message' => $deleted ? 'deleted' : 'error' ) ) );
	exit;
}
}

add_action( 'admin_post_micada_faq_delete', 'micada_faq_delete' );

if ( ! function_exists( 'micada_faq_admin_page' ) ) {
function micada_faq_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$view = 'trash' === sanitize_key( wp_unslash( $_GET['view'] ?? 'post' ) ) ? 'trash' : 'post';
	$rows = micada_faq_rows( true, $view );
	$next_order = 1;
	foreach ( $rows as $row ) {
		$next_order = max( $next_order, (int) $row->sort_order + 1 );
	}
	$message = sanitize_key( wp_unslash( $_GET['message'] ?? '' ) );
	$messages = array(
		'saved' => array( 'success', __( 'FAQ document saved.', 'micada' ) ),
		'deleted' => array( 'success', __( 'FAQ document deleted.', 'micada' ) ),
		'invalid' => array( 'error', __( 'Enter an English title, a PDF or link URL, and a valid display order.', 'micada' ) ),
		'error' => array( 'error', __( 'The database could not save this FAQ document.', 'micada' ) ),
	);
	wp_enqueue_media();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FAQ Documents', 'micada' ); ?> <button type="button" class="page-title-action" id="micada-faq-add" data-next-order="<?php echo esc_attr( $next_order ); ?>"><?php esc_html_e( 'Add FAQ', 'micada' ); ?></button></h1>
		<p><?php esc_html_e( 'Manage the bilingual FAQ links shown in the website navigation. PDF files can be selected from the Media Library or linked by URL. Entries are saved in the FAQ database table.', 'micada' ); ?></p>
		<?php if ( isset( $messages[ $message ] ) ) : ?><div class="notice notice-<?php echo esc_attr( $messages[ $message ][0] ); ?> is-dismissible"><p><?php echo esc_html( $messages[ $message ][1] ); ?></p></div><?php endif; ?>
		<?php micada_cms_status_tabs( 'micada-faqs', micada_faq_table(), $view ); ?>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'ID', 'micada' ); ?></th><th><?php esc_html_e( 'English title', 'micada' ); ?></th><th><?php esc_html_e( 'Hindi title', 'micada' ); ?></th><th><?php esc_html_e( 'PDF / Link', 'micada' ); ?></th><th><?php esc_html_e( 'Order', 'micada' ); ?></th><th><?php esc_html_e( 'Status', 'micada' ); ?></th><th><?php esc_html_e( 'Actions', 'micada' ); ?></th></tr></thead><tbody>
		<?php foreach ( $rows as $row ) : ?><tr><td><?php echo esc_html( $row->id ); ?></td><td><?php echo esc_html( $row->question_en ); ?></td><td><?php echo esc_html( $row->question_hi ); ?></td><td><a href="<?php echo esc_url( $row->document_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( wp_basename( wp_parse_url( $row->document_url, PHP_URL_PATH ) ) ?: $row->document_url ); ?></a></td><td><?php echo esc_html( $row->sort_order ); ?></td><td><?php echo 'trash' === $row->display_status ? esc_html__( 'Trash', 'micada' ) : esc_html__( 'Post', 'micada' ); ?></td><td><button type="button" class="button-link micada-faq-edit" data-id="<?php echo esc_attr( $row->id ); ?>" data-en="<?php echo esc_attr( $row->question_en ); ?>" data-hi="<?php echo esc_attr( $row->question_hi ); ?>" data-url="<?php echo esc_attr( $row->document_url ); ?>" data-order="<?php echo esc_attr( $row->sort_order ); ?>"><?php esc_html_e( 'Edit', 'micada' ); ?></button> | <?php micada_cms_trash_action_links( 'faq', $row->id, $row->display_status ); ?></td></tr><?php endforeach; ?>
		<?php if ( ! $rows ) : ?><tr><td colspan="7"><?php esc_html_e( 'No FAQ documents have been added.', 'micada' ); ?></td></tr><?php endif; ?>
		</tbody></table>
		<div id="micada-faq-modal" class="micada-faq-modal" aria-hidden="true"><div class="micada-faq-backdrop" data-faq-close></div><section class="micada-faq-dialog" role="dialog" aria-modal="true" aria-labelledby="micada-faq-title"><header><h2 id="micada-faq-title"><?php esc_html_e( 'Add FAQ', 'micada' ); ?></h2><button type="button" class="micada-faq-close" aria-label="<?php esc_attr_e( 'Close', 'micada' ); ?>" data-faq-close>&times;</button></header>
			<form id="micada-faq-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="micada_faq_save"><input type="hidden" name="id" value="0"><?php wp_nonce_field( 'micada_faq_save' ); ?>
				<p><label><strong><?php esc_html_e( 'English title', 'micada' ); ?></strong><input class="widefat" required name="question_en"></label></p>
				<p><label><strong><?php esc_html_e( 'Hindi title (optional)', 'micada' ); ?></strong><input class="widefat" name="question_hi"></label></p>
				<p><label><strong><?php esc_html_e( 'PDF or link URL', 'micada' ); ?></strong><input class="widefat" required type="url" name="document_url" id="micada-faq-url"></label> <button type="button" class="button" id="micada-faq-select-pdf"><?php esc_html_e( 'Select PDF from Media Library', 'micada' ); ?></button></p>
				<p><label><strong><?php esc_html_e( 'Display order', 'micada' ); ?></strong><input class="small-text" type="number" min="0" step="1" required name="sort_order" value="<?php echo esc_attr( $next_order ); ?>"></label></p>
				<div class="micada-faq-actions"><button type="submit" class="button button-primary" id="micada-faq-submit"><?php esc_html_e( 'Add FAQ', 'micada' ); ?></button><button type="button" class="button" data-faq-close><?php esc_html_e( 'Cancel', 'micada' ); ?></button></div>
			</form>
		</section></div>
	</div>
	<style>.micada-faq-modal{display:none;position:fixed;inset:32px 0 0 160px;z-index:100100;align-items:center;justify-content:center;padding:24px}.micada-faq-modal.is-open{display:flex}.micada-faq-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55)}.micada-faq-dialog{position:relative;width:min(680px,100%);max-height:calc(100vh - 96px);overflow:auto;background:#fff;border-radius:8px;box-shadow:0 12px 45px rgba(0,0,0,.3);padding:24px}.micada-faq-dialog header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.micada-faq-dialog header h2{margin:0;font-size:21px}.micada-faq-close{border:0;background:transparent;font-size:28px;line-height:1;cursor:pointer;color:#50575e}.micada-faq-dialog form>p{margin:0 0 16px}.micada-faq-dialog form>p label strong{display:block;margin-bottom:6px}.micada-faq-actions{display:flex;gap:8px;margin-top:22px}body.micada-faq-modal-open{overflow:hidden}@media(max-width:782px){.micada-faq-modal{inset:46px 0 0;padding:12px}.micada-faq-dialog{max-height:calc(100vh - 70px);padding:18px}}</style>
	<script>(function(){const modal=document.getElementById('micada-faq-modal'),form=document.getElementById('micada-faq-form'),heading=document.getElementById('micada-faq-title'),submit=document.getElementById('micada-faq-submit'),urlField=document.getElementById('micada-faq-url');const open=function(){modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.body.classList.add('micada-faq-modal-open');form.elements.question_en.focus();};const close=function(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');document.body.classList.remove('micada-faq-modal-open');};document.getElementById('micada-faq-add').addEventListener('click',function(){form.reset();form.elements.id.value='0';form.elements.sort_order.value=this.dataset.nextOrder;heading.textContent='<?php echo esc_js( __( 'Add FAQ', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Add FAQ', 'micada' ) ); ?>';open();});document.querySelectorAll('.micada-faq-edit').forEach(function(button){button.addEventListener('click',function(){form.elements.id.value=button.dataset.id;form.elements.question_en.value=button.dataset.en;form.elements.question_hi.value=button.dataset.hi;urlField.value=button.dataset.url;form.elements.sort_order.value=button.dataset.order;heading.textContent='<?php echo esc_js( __( 'Edit FAQ', 'micada' ) ); ?>';submit.textContent='<?php echo esc_js( __( 'Save Changes', 'micada' ) ); ?>';open();});});document.querySelectorAll('[data-faq-close]').forEach(function(button){button.addEventListener('click',close);});document.addEventListener('keydown',function(event){if(event.key==='Escape'&&modal.classList.contains('is-open'))close();});document.getElementById('micada-faq-select-pdf').addEventListener('click',function(){const frame=wp.media({title:'<?php echo esc_js( __( 'Select or upload an FAQ PDF', 'micada' ) ); ?>',button:{text:'<?php echo esc_js( __( 'Use this PDF', 'micada' ) ); ?>'},library:{type:'application/pdf'},multiple:false});frame.on('select',function(){const attachment=frame.state().get('selection').first().toJSON();urlField.value=attachment.url;});frame.open();});})();</script>
	<?php
}
}


if ( ! function_exists( 'micada_faq_navigation_items' ) ) {
function micada_faq_navigation_items() {
	$items = array();
	foreach ( micada_faq_rows( false, 'post' ) as $row ) {
		$label = 'hd' === micada_language() && '' !== $row->question_hi ? $row->question_hi : $row->question_en;
		$items[] = array( $label, 'question-circle', $row->document_url );
	}
	return $items;
}
}

