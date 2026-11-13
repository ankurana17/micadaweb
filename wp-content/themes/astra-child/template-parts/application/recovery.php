<?php
defined( 'ABSPATH' ) || exit;
$pending = $_SESSION['micada_recovery'] ?? null;
$has_recovery = $pending && $pending['route'] === $route && $pending['expires'] > time();
?>
<h1>Forgot Password</h1><p>Verify the OTPs sent to your registered mobile and email.</p>
<form method="post" action="<?php echo esc_url( micada_app_url( $route ) ); ?>">
<input type="hidden" name="micada_csrf" value="<?php echo esc_attr( $_SESSION['micada_csrf'] ); ?>">
<label for="username">Username / Email</label><input id="username" name="username" autocomplete="username" value="<?php echo esc_attr( $has_recovery ? $pending['username'] : '' ); ?>" <?php echo $has_recovery ? 'readonly' : ''; ?> required>
<?php if ( $has_recovery ) : ?>
	<label for="otp">SMS OTP</label><input id="otp" name="otp" autocomplete="one-time-code" required>
	<label for="email-otp">Email OTP</label><input id="email-otp" name="email_otp" required>
	<label for="password">New Password</label><input id="password" type="password" name="password" autocomplete="new-password" <?php echo 'vendor-reg/forgot-password' === $route ? 'minlength="8"' : ''; ?> required>
	<label for="confirm-password">Re-enter Password</label><input id="confirm-password" type="password" name="confirm_password" autocomplete="new-password" required>
	<button class="app-primary" name="action" value="recovery-change">Change Password</button>
<?php else : ?><button class="app-primary" name="action" value="recovery-otp">Get OTP</button><?php endif; ?>
</form>
<p><a href="<?php echo esc_url( micada_app_url( 'vendor-reg/forgot-password' === $route ? 'vendor-registration-login' : 'login' ) ); ?>">Back to login</a></p>
