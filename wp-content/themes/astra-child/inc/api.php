<?php
/** Existing MICADA API protocol. No application database or endpoints are replaced. */
defined( 'ABSPATH' ) || exit;

function micada_api_base() {
	return 'https://micada.haryana.gov.in:8443/api/';
}

function micada_encrypt_password( $value ) {
	$key = '/<du3]@D33Asr3@X';
	return openssl_encrypt( $value, 'aes-128-cbc', $key, 0, $key );
}

function micada_encrypt_payload( $value ) {
	$suffix = substr( (string) ( (int) gmdate( 'ymd' ) * 7412 ), -6 );
	return array( 'payload' => openssl_encrypt( base64_encode( wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ), 'aes-128-ecb', '/>Z{%oHa#A' . $suffix, 0 ) );
}

function micada_decrypt_api_token( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return null;
	}
	$plain = openssl_decrypt( $value, 'aes-128-ecb', '/M<i]C@A#D&A25@1', 0 );
	return false === $plain ? null : base64_decode( $plain, true );
}

function micada_api_request( $endpoint, $body = null, $token = '' ) {
	// Only server-defined endpoints are passed here; never accept a caller-supplied URL.
	$args = array( 'method' => null === $body ? 'GET' : 'POST', 'timeout' => 25, 'redirection' => 0, 'sslverify' => true, 'headers' => array( 'Accept' => 'application/json' ) );
	if ( $token ) {
		$args['headers']['Authorization'] = 'Bearer ' . $token;
	}
	if ( null !== $body ) {
		$args['headers']['Content-Type'] = 'application/json';
		$args['body'] = wp_json_encode( $body );
	}
	$response = wp_remote_request( micada_api_base() . $endpoint, $args );
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'api_connection', 'The MICADA API could not be reached. Please retry. Your login page remains local.' );
	}
	$status = wp_remote_retrieve_response_code( $response );
	$text = wp_remote_retrieve_body( $response );
	$value = json_decode( $text, true );
	if ( JSON_ERROR_NONE !== json_last_error() ) {
		$value = $text;
	}
	if ( $status < 200 || $status >= 300 ) {
		$messages = array( 401 => 'The API did not accept these credentials or this session. Please sign in again.', 403 => 'The API denied access to this operation. Your session has not been discarded.', 429 => 'Too many requests. Please wait and try again.' );
		return new WP_Error( 'api_' . $status, $messages[ $status ] ?? 'The MICADA API returned an error. Please retry later.', array( 'status' => $status ) );
	}
	return $value;
}

function micada_bootstrap_token() {
	$result = micada_api_request( 'Auth/GetTokenInternal', new stdClass() );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$plain = micada_decrypt_api_token( $result['data'] ?? null );
	$data = is_string( $plain ) ? json_decode( $plain, true ) : null;
	return ! empty( $data['authToken'] ) ? $data['authToken'] : new WP_Error( 'invalid_bootstrap', 'The API returned an invalid initial token. No session has been created.' );
}

function micada_api_authenticated( $endpoint, $body = null ) {
	$account = micada_app_account();
	if ( ! $account ) {
		return new WP_Error( 'login_required', 'Please sign in to view this information.' );
	}
	$result = micada_api_request( $endpoint, $body, $account['authToken'] );
	if ( is_wp_error( $result ) && 'api_401' === $result->get_error_code() ) {
		unset( $_SESSION['micada_account'] );
	}
	return $result;
}

function micada_login_audit( $identifier, $action, $status, $token ) {
	// Retain the original audit operation without including passwords, OTPs or tokens in its payload.
	micada_api_request( 'Login/InsertUserActivityTracker', array( 'LoginID' => $identifier, 'ActionType' => $action, 'SpecificId' => '', 'NetworkId' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ), 'OccurrenceDateTime' => gmdate( 'Y-m-d\TH:i:s.000\Z' ), 'Status' => $status, 'RequestPayload' => '' ), $token );
}
