<?php
/** Local application login routes and server-held sessions, separate from WordPress admin. */
defined( 'ABSPATH' ) || exit;

function micada_app_url( $route ) {
	return add_query_arg( array( 'micada_app' => $route, 'lang' => micada_language() ), home_url( '/' ) );
}

function micada_app_route() {
	return isset( $_GET['micada_app'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['micada_app'] ) ), '/' ) : '';
}

function micada_app_session() {
	if ( PHP_SESSION_ACTIVE !== session_status() ) {
		session_name( 'MICADA_APPLICATION' );
		session_start( array( 'use_strict_mode' => 1, 'use_only_cookies' => 1, 'cookie_httponly' => true, 'cookie_secure' => is_ssl(), 'cookie_samesite' => 'Lax', 'cookie_path' => trailingslashit( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/' ) ) );
	}
	if ( empty( $_SESSION['micada_csrf'] ) ) {
		$_SESSION['micada_csrf'] = bin2hex( random_bytes( 32 ) );
	}
}

function micada_app_account() {
	$account = $_SESSION['micada_account'] ?? null;
	if ( ! $account || empty( $account['expires'] ) || $account['expires'] <= time() ) {
		unset( $_SESSION['micada_account'] );
		return null;
	}
	return $account;
}

function micada_accept_login( $response, $kind, $identifier, $scheme = '' ) {
	$plain = micada_decrypt_api_token( $response['data'] ?? null );
	$data = is_string( $plain ) ? json_decode( $plain, true ) : null;
	if ( ! is_array( $data ) || empty( $data['authToken'] ) || ! empty( $data['msg'] ) ) {
		return new WP_Error( 'invalid_login', 'The API did not return a valid login. Please check your details.' );
	}
	$parts = explode( '.', $data['authToken'] );
	$claims = count( $parts ) === 3 ? json_decode( base64_decode( strtr( $parts[1], '-_', '+/' ) ), true ) : null;
	// The token is accepted only from the HTTPS backend response, never from browser input.
	if ( ! is_array( $claims ) || empty( $claims['exp'] ) || $claims['exp'] <= time() || ! isset( $claims['sub'] ) ) {
		return new WP_Error( 'expired_api_login', 'The API returned an expired or invalid token. Please retry signing in.' );
	}
	session_regenerate_id( true );
	$_SESSION['micada_account'] = array( 'kind' => $kind, 'role' => $data['role'] ?? ( 'farmer' === $kind ? 'Farmer' : 'Vendor Registration' ), 'identifier' => $identifier, 'subject' => (string) $claims['sub'], 'expires' => (int) $claims['exp'], 'authToken' => $data['authToken'], 'profile' => $data, 'scheme' => $scheme );
	unset( $_SESSION['micada_pending_otp'] );
	$_SESSION['micada_csrf'] = bin2hex( random_bytes( 32 ) );
	return true;
}

function micada_authenticate_password( $route, $username, $password ) {
	$initial = micada_bootstrap_token();
	if ( is_wp_error( $initial ) ) {
		return $initial;
	}
	$registration = 'vendor-registration-login' === $route;
	$payload = array( $registration ? 'email_ID' : 'username' => $username, 'password' => micada_encrypt_password( $password ) );
	$result = micada_api_request( $registration ? 'Auth/VendorLogin' : 'auth/login', micada_encrypt_payload( $payload ), $initial );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	$result = micada_accept_login( $result, $registration ? 'vendor-registration' : 'staff', $username );
	if ( ! is_wp_error( $result ) ) {
		$account = micada_app_account();
		micada_login_audit( $username, $registration ? 'Vendor Reg Login' : 'login', 'Success', $account['authToken'] );
	}
	return $result;
}

function micada_process_farmer_login( $action, $scheme ) {
	if ( 'captcha-refresh' === $action ) {
		$_SESSION['micada_captcha'] = strtoupper( bin2hex( random_bytes( 3 ) ) );
		return 'CAPTCHA refreshed.';
	}
	if ( 'farmer-send-otp' === $action ) {
		$aadhaar = sanitize_text_field( wp_unslash( $_POST['aadhaar'] ?? '' ) );
		if ( ! preg_match( '/^\d{12}$/', $aadhaar ) ) {
			return new WP_Error( 'invalid_aadhaar', 'Enter a valid 12-digit Aadhaar number.' );
		}
		if ( ! empty( $_SESSION['micada_pending_otp']['expires'] ) && $_SESSION['micada_pending_otp']['expires'] > time() ) {
			return new WP_Error( 'otp_wait', 'An OTP has already been requested. Please wait for the five-minute timer.' );
		}
		$initial = micada_bootstrap_token();
		if ( is_wp_error( $initial ) ) {
			return $initial;
		}
		$result = micada_api_request( 'FarmerApplication/GenerateAadharOTP', micada_encrypt_payload( array( 'AadharNo' => $aadhaar ) ), $initial );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( 'OTP sent' !== $result ) {
			return new WP_Error( 'otp_not_sent', is_string( $result ) ? wp_strip_all_tags( $result ) : 'The API did not confirm that an OTP was sent.' );
		}
		$_SESSION['micada_pending_otp'] = array( 'aadhaar' => $aadhaar, 'scheme' => $scheme, 'expires' => time() + 300, 'token' => $initial );
		$_SESSION['micada_captcha'] = strtoupper( bin2hex( random_bytes( 3 ) ) );
		return 'OTP sent to your Aadhaar-linked mobile number.';
	}
	$pending = $_SESSION['micada_pending_otp'] ?? null;
	if ( ! $pending || $pending['expires'] <= time() || $pending['scheme'] !== $scheme ) {
		unset( $_SESSION['micada_pending_otp'] );
		return new WP_Error( 'otp_expired', 'Your OTP request has expired. Please request another OTP.' );
	}
	$otp = sanitize_text_field( wp_unslash( $_POST['otp'] ?? '' ) );
	if ( ! preg_match( '/^\d{6}$/', $otp ) ) {
		return new WP_Error( 'invalid_otp', 'Enter the six-digit OTP.' );
	}
	$captcha = strtoupper( sanitize_text_field( wp_unslash( $_POST['captcha'] ?? '' ) ) );
	if ( ! hash_equals( $_SESSION['micada_captcha'] ?? '', $captcha ) || '' === $captcha ) {
		$_SESSION['micada_captcha'] = strtoupper( bin2hex( random_bytes( 3 ) ) );
		return new WP_Error( 'invalid_captcha', 'CAPTCHA does not match. Please try the new code.' );
	}
	$result = micada_api_request( 'FarmerApplication/ValidateAadharOTP', micada_encrypt_payload( array( 'AadharNo' => $pending['aadhaar'], 'OTP' => $otp ) ), $pending['token'] );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return micada_accept_login( $result, 'farmer', $pending['aadhaar'], $scheme );
}

function micada_process_recovery( $route, $action ) {
	$vendor = 'vendor-reg/forgot-password' === $route;
	$username = sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) );
	if ( '' === $username ) {
		return new WP_Error( 'username_required', 'Enter your username or email.' );
	}
	$initial = micada_bootstrap_token();
	if ( is_wp_error( $initial ) ) {
		return $initial;
	}
	if ( 'recovery-otp' === $action ) {
		$result = micada_api_request( $vendor ? 'Login/GenerateOtpForUserForVenReg' : 'Login/GetOtpForUser', micada_encrypt_payload( array( $vendor ? 'Email' : 'UserName' => $username ) ), $initial );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( is_string( $result ) ) {
			$decoded = json_decode( $result, true );
			$result = is_array( $decoded ) ? $decoded : $result;
		}
		$message = is_array( $result ) ? micada_decrypt_api_token( $result['data'] ?? '' ) : $result;
		if ( ! is_string( $message ) || false === stripos( $message, 'OTP' ) || false === stripos( $message, 'sent' ) ) {
			return new WP_Error( 'recovery_otp', 'The API did not confirm the recovery OTP request.' );
		}
		$_SESSION['micada_recovery'] = array( 'username' => $username, 'route' => $route, 'expires' => time() + 180 );
		return wp_strip_all_tags( $message );
	}
	$pending = $_SESSION['micada_recovery'] ?? null;
	$password = (string) wp_unslash( $_POST['password'] ?? '' );
	$confirm = (string) wp_unslash( $_POST['confirm_password'] ?? '' );
	if ( ! $pending || $pending['route'] !== $route || $pending['username'] !== $username || $pending['expires'] <= time() ) {
		return new WP_Error( 'recovery_expired', 'Please request a new recovery OTP.' );
	}
	if ( '' === $password || $password !== $confirm || ( $vendor && strlen( $password ) < 8 ) ) {
		return new WP_Error( 'password_mismatch', $vendor ? 'Passwords must match and contain at least eight characters.' : 'Enter matching passwords.' );
	}
	$sms = sanitize_text_field( wp_unslash( $_POST['otp'] ?? '' ) );
	$email = sanitize_text_field( wp_unslash( $_POST['email_otp'] ?? '' ) );
	if ( '' === $sms || '' === $email ) {
		return new WP_Error( 'otp_required', 'Enter both SMS and email OTPs.' );
	}
	$result = micada_api_request( $vendor ? 'Login/ForgotPasswordVendorReg' : 'Login/ForgotPassword', micada_encrypt_payload( array( $vendor ? 'Email' : 'userName' => $username, 'passWord' => micada_encrypt_password( $password ), 'otp' => micada_encrypt_password( $sms ), 'otpEmail' => micada_encrypt_password( $email ) ) ), $initial );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( 'Updated Successfully!.' === $result ) {
		unset( $_SESSION['micada_recovery'] );
		return 'Password updated successfully. You can sign in locally.';
	}
	return new WP_Error( 'password_update', is_string( $result ) ? wp_strip_all_tags( $result ) : 'The API did not confirm the password update.' );
}

function micada_local_application() {
	$route = micada_app_route();
	if ( '' === $route ) {
		return;
	}
	micada_app_session();
	nocache_headers();
	if ( 'captcha' === $route ) {
		header( 'Content-Type: image/svg+xml; charset=utf-8' );
		echo '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="60"><rect width="100%" height="100%" fill="#f2efd2"/><path d="M0 15L220 45M0 45L220 15" stroke="#2f9688"/><text x="12" y="41" font-family="monospace" font-size="32" letter-spacing="6">' . esc_html( $_SESSION['micada_captcha'] ?? '' ) . '</text></svg>';
		exit;
	}
	$notice = null;
	$scheme = 0 === strpos( $route, 'phone-login/' ) ? substr( $route, 12 ) : ( 'phone-login' === $route ? 'stp' : 'noc' );
	$farmer = 0 === strpos( $route, 'phone-login' ) || 'tubewell-connection-login' === $route;
	$recovery = in_array( $route, array( 'forgot-password', 'vendor-reg/forgot-password' ), true );
	$dashboard = 'dashboard' === $route;
	$login = in_array( $route, array( 'login', 'vendor-login', 'vendor-registration-login' ), true );
	if ( ! $login && ! $farmer && ! $recovery && ! $dashboard ) {
		status_header( 404 );
		$notice = new WP_Error( 'not_converted', 'This application workflow has not yet been converted. You will remain on the local site.' );
	}
	if ( $farmer && ! in_array( $scheme, array( 'stp', 'pond', 'canal', 'tubewell', 'noc' ), true ) ) {
		status_header( 404 );
		$farmer = false;
		$notice = new WP_Error( 'invalid_scheme', 'This farmer login route is not valid.' );
	}
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		$csrf = sanitize_text_field( wp_unslash( $_POST['micada_csrf'] ?? '' ) );
		$action = sanitize_key( wp_unslash( $_POST['action'] ?? '' ) );
		if ( ! hash_equals( $_SESSION['micada_csrf'], $csrf ) ) {
			$notice = new WP_Error( 'invalid_form', 'This form is no longer valid. Please reload and try again.' );
		} elseif ( 'logout' === $action ) {
			unset( $_SESSION['micada_account'], $_SESSION['micada_pending_otp'], $_SESSION['micada_recovery'] );
			session_regenerate_id( true );
			wp_safe_redirect( micada_app_url( 'login' ), 303 );
			exit;
		} elseif ( $login && 'password-login' === $action ) {
			$username = sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) );
			$password = (string) wp_unslash( $_POST['password'] ?? '' );
			$notice = '' !== $username && '' !== $password ? micada_authenticate_password( $route, $username, $password ) : new WP_Error( 'required', 'Enter your username and password.' );
		} elseif ( $farmer && in_array( $action, array( 'farmer-send-otp', 'farmer-verify-otp', 'captcha-refresh' ), true ) ) {
			$notice = micada_process_farmer_login( $action, $scheme );
		} elseif ( $recovery && in_array( $action, array( 'recovery-otp', 'recovery-change' ), true ) ) {
			$notice = micada_process_recovery( $route, $action );
		}
		if ( true === $notice && micada_app_account() ) {
			wp_safe_redirect( micada_app_url( 'dashboard' ), 303 );
			exit;
		}
	}
	if ( $dashboard && ! micada_app_account() ) {
		status_header( 401 );
		$notice = new WP_Error( 'login_required', 'Please sign in to access your dashboard. You have not been redirected to the homepage.' );
		$login = true;
		$dashboard = false;
		$route = 'login';
	}
	$directory = get_stylesheet_directory() . '/assets/';
	wp_enqueue_style( 'micada-application', get_stylesheet_directory_uri() . '/assets/css/application.css', array(), filemtime( $directory . 'css/application.css' ) );
	wp_enqueue_script( 'micada-application', get_stylesheet_directory_uri() . '/assets/js/application.js', array(), filemtime( $directory . 'js/application.js' ), true );
	require get_stylesheet_directory() . '/page-templates/application.php';
	exit;
}
add_action( 'template_redirect', 'micada_local_application', 1 );
