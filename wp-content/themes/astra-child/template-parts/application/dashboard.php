<?php
defined( 'ABSPATH' ) || exit;
$account = micada_app_account();
$result = micada_dashboard_data();
$rows = is_wp_error( $result ) ? array() : micada_dashboard_rows( $result );
$columns = array();
foreach ( $rows as $row ) {
	foreach ( $row as $field => $value ) {
		if ( is_scalar( $value ) || null === $value ) {
			$columns[ $field ] = $field;
		}
	}
}
?>
<div class="app-dashboard">
<header class="dashboard-header"><div><h1><?php echo esc_html( $account['role'] ); ?> Dashboard</h1><p>Welcome, <?php echo esc_html( $account['profile']['officerName'] ?? ( 'farmer' === $account['kind'] ? 'Farmer' : $account['identifier'] ) ); ?></p></div><form method="post" action="<?php echo esc_url( micada_app_url( 'dashboard' ) ); ?>"><input type="hidden" name="micada_csrf" value="<?php echo esc_attr( $_SESSION['micada_csrf'] ); ?>"><button name="action" value="logout">Sign Out</button></form></header>
<form class="dashboard-filters" method="get"><input type="hidden" name="micada_app" value="dashboard"><input type="hidden" name="lang" value="<?php echo esc_attr( micada_language() ); ?>"><label>Financial Year<input name="year" placeholder="e.g. 2026-27" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['year'] ?? '' ) ) ); ?>"></label><label>Application Type<input name="app_type" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['app_type'] ?? '' ) ) ); ?>"></label><button type="submit">Load Dashboard</button></form>
<?php if ( is_wp_error( $result ) ) : ?><div class="app-message is-error" role="alert"><?php echo esc_html( $result->get_error_message() ); ?></div><?php endif; ?>
<?php if ( ! is_wp_error( $result ) ) : ?>
<div class="dashboard-tools"><label>Search<input type="search" data-table-search placeholder="Search applications"></label><button type="button" data-export-table>Download CSV</button><button type="button" data-print-table>Print / Save PDF</button><span data-row-count><?php echo count( $rows ); ?> records</span></div>
<div class="dashboard-table-wrap"><table data-dashboard-table><thead><tr><?php foreach ( $columns as $field ) : ?><th scope="col"><button type="button" data-sort-column><?php echo esc_html( $field ); ?></button></th><?php endforeach; ?></tr></thead><tbody><?php foreach ( $rows as $row ) : ?><tr><?php foreach ( $columns as $field ) : ?><td><?php echo esc_html( (string) ( $row[ $field ] ?? '' ) ); ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<?php if ( ! $rows ) : ?><p>No applications returned by the API for these filters.</p><?php endif; ?>
<?php endif; ?>
<p class="dashboard-limit">Application editing, approvals, KYC and other detailed role workflows are still being converted. This page does not submit changes to application records.</p>
</div>
