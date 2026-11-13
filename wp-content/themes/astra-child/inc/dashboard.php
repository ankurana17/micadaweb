<?php
/** Native, authenticated read-only dashboard data using original service contracts. */
defined( 'ABSPATH' ) || exit;

function micada_dashboard_data() {
	$account = micada_app_account();
	if ( ! $account ) {
		return new WP_Error( 'login_required', 'Please sign in.' );
	}
	$year = sanitize_text_field( wp_unslash( $_GET['year'] ?? '' ) );
	$type = sanitize_text_field( wp_unslash( $_GET['app_type'] ?? '' ) );
	$role = $account['role'];
	if ( 'farmer' === $account['kind'] ) {
		$id = $account['profile']['UserId_New'] ?? null;
		if ( ! $id ) {
			return new WP_Error( 'kyc_required', 'Complete your KYC first. The KYC workflow still requires a dedicated native conversion.' );
		}
		return micada_api_authenticated( 'DashBoard/GetFarmerDashboardDataByFarmerID', micada_encrypt_payload( array( 'FarmerID' => $id ) ) );
	}
	if ( 'Vendor' === $role ) {
		return micada_api_authenticated( 'DashBoard/GetVendorApplicationForMIEstimation?' . http_build_query( array( 'UserId' => $account['subject'], 'AppType' => $type, 'financialYear' => $year ) ) );
	}
	if ( in_array( $role, array( 'DD Associate', 'DMICADA', 'DDMICADA', 'ADVISOR', 'DDMI', 'DDTank', 'ADMIN' ), true ) ) {
		return micada_api_authenticated( 'DashBoard/GetFarmerApplicationForApproval?' . http_build_query( array( 'UserId' => $account['subject'], 'AppType' => $type, 'financialyear' => $year, 'AppCount' => '', 'RequestType' => '' ) ) );
	}
	return new WP_Error( 'role_workflow_pending', 'Your login is valid. This role’s detailed dashboard workflow has not yet been ported; no production redirect will occur.' );
}

function micada_dashboard_rows( $value ) {
	if ( is_string( $value ) ) {
		$value = json_decode( $value, true );
	}
	if ( ! is_array( $value ) ) {
		return array();
	}
	foreach ( array( 'data', 'Data', 'items', 'Items' ) as $key ) {
		if ( isset( $value[ $key ] ) ) {
			return micada_dashboard_rows( $value[ $key ] );
		}
	}
	return array_values( array_filter( $value, 'is_array' ) );
}
