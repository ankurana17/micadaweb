<?php
defined( 'ABSPATH' ) || exit;
$pending = $_SESSION['micada_pending_otp'] ?? null;
$has_otp = $pending && $pending['expires'] > time() && $pending['scheme'] === $scheme;
?>
<h2><?php echo $has_otp ? 'Verify OTP' : 'Farmer Login'; ?></h2><p><?php echo $has_otp ? 'OTP sent to your Aadhaar-linked mobile number' : 'Login via Aadhaar OTP'; ?></p>
<form method="post" action="<?php echo esc_url( micada_app_url( $route ) ); ?>">
<input type="hidden" name="micada_csrf" value="<?php echo esc_attr( $_SESSION['micada_csrf'] ); ?>">
<?php if ( ! $has_otp ) : ?>
	<label for="aadhaar">Aadhaar Number</label><div class="password-field"><input id="aadhaar" name="aadhaar" type="password" inputmode="numeric" pattern="[0-9]{12}" minlength="12" maxlength="12" placeholder="XXXX XXXX XXXX" autocomplete="off" required><button type="button" data-reveal="aadhaar" aria-label="Show Aadhaar number">Show</button></div>
	<button class="app-primary" name="action" value="farmer-send-otp">Get OTP →</button>
<?php else : ?>
	<p class="otp-timer" data-otp-expires="<?php echo esc_attr( $pending['expires'] ); ?>"></p>
	<label for="otp">Six-digit OTP</label><input id="otp" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required>
	<div class="captcha"><img src="<?php echo esc_url( add_query_arg( 'v', time(), micada_app_url( 'captcha' ) ) ); ?>" alt="CAPTCHA security code"><button type="submit" name="action" value="captcha-refresh" formnovalidate>Refresh</button></div>
	<label for="captcha">Enter CAPTCHA</label><input id="captcha" name="captcha" autocomplete="off" maxlength="6" required>
	<button class="app-primary" name="action" value="farmer-verify-otp">Verify OTP &amp; Login →</button>
<?php endif; ?>
</form>
<p class="privacy-note">Do not share your Aadhaar OTP with anyone.</p>
