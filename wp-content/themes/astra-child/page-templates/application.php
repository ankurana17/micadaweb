<?php
/** Local application shell; selected by the application router, not page assignment. */
defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'micada-app' ); ?>>
<?php wp_body_open(); ?>
<nav class="app-navigation"><a href="<?php echo esc_url( micada_url( 'home' ) ); ?>">← MICADA Home</a><a href="<?php echo esc_url( micada_app_url( 'login' ) ); ?>">Officer Login</a><a href="<?php echo esc_url( micada_app_url( 'vendor-login' ) ); ?>">Vendor Login</a><a href="<?php echo esc_url( micada_app_url( 'phone-login/stp' ) ); ?>">Farmer Login</a><a href="<?php echo esc_url( micada_app_url( 'vendor-registration-login' ) ); ?>">Registration Login</a></nav>
<main>
<?php if ( $dashboard ) : ?>
	<?php require get_stylesheet_directory() . '/template-parts/application/dashboard.php'; ?>
<?php else : ?>
	<?php if ( $farmer ) : ?>
	<div class="farmer-layout scheme-<?php echo esc_attr( $scheme ); ?>">
		<section class="scheme-introduction"><?php
		$copy = array( 'stp' => array( 'Micro Irrigation With STP / TWW', 'Treated Waste Water.', 'Smart Micro Irrigation.', 'Leverage Sewage Treatment Plant & Treated Waste Water for efficient micro irrigation across Haryana farmlands.' ), 'pond' => array( 'Overflow Pond Based MI', 'OFWT/ Overflow Pond.', 'Best Option For Irrigation.', 'Leverage overflow pond water for efficient micro irrigation across Haryana farmlands.' ), 'canal' => array( 'Canal Based MI', 'Canal Water.', 'Available Near Fields', "Channeling the ancient lifeline of Haryana’s irrigation canals into modern precision farming." ), 'tubewell' => array( 'Tubewell Based MI', 'Tubewell Based.', 'Precision Irrigation.', 'Leveraging groundwater from tubewells for efficient micro irrigation across Haryana farmlands.' ), 'noc' => array( 'Apply for NOC (MI)', 'Apply for', 'NOC (MI)', 'Official portal for farmers to apply for No Objection Certificate under Micro Irrigation schemes.' ) );
		$text = $copy[ $scheme ];
		?><p class="scheme-chip"><?php echo esc_html( $text[0] ); ?></p><h1><?php echo esc_html( $text[1] ); ?><br><span><?php echo esc_html( $text[2] ); ?></span></h1><p><?php echo esc_html( $text[3] ); ?></p></section>
	<?php else : ?><div class="password-layout"><?php endif; ?>
	<section class="app-card">
		<div class="app-brand"><img src="<?php echo esc_url( micada_asset( 'activitiesImages/new_logo.png' ) ); ?>" alt="MICADA Haryana"><div><strong>MICADA Haryana</strong><span>Per Drop More Crop</span></div></div>
		<?php if ( $notice && true !== $notice ) : ?><div class="app-message <?php echo is_wp_error( $notice ) ? 'is-error' : 'is-success'; ?>" role="alert"><?php echo esc_html( is_wp_error( $notice ) ? $notice->get_error_message() : $notice ); ?></div><?php endif; ?>
		<?php if ( $login ) : ?>
			<h1><?php echo 'vendor-registration-login' === $route ? 'Vendor Registration Login' : ( 'vendor-login' === $route ? 'Vendor Login' : 'Welcome' ); ?></h1><p>Sign in to continue</p>
			<form method="post" action="<?php echo esc_url( micada_app_url( $route ) ); ?>">
			<input type="hidden" name="micada_csrf" value="<?php echo esc_attr( $_SESSION['micada_csrf'] ); ?>"><input type="hidden" name="action" value="password-login">
			<label for="username">Email / Username</label><input id="username" name="username" autocomplete="username" required value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) ) ); ?>">
			<label for="password">Password</label><div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" data-reveal="password" aria-label="Show password">Show</button></div>
			<div class="form-links"><label class="inline-label"><input type="checkbox" name="remember" value="1"> Remember me</label><a href="<?php echo esc_url( micada_app_url( 'vendor-registration-login' === $route ? 'vendor-reg/forgot-password' : 'forgot-password' ) ); ?>">Forgot password?</a></div>
			<button type="submit" class="app-primary">Sign In</button>
			</form>
		<?php elseif ( $farmer ) : ?>
			<?php require get_stylesheet_directory() . '/template-parts/application/farmer-login.php'; ?>
		<?php elseif ( $recovery ) : ?>
			<?php require get_stylesheet_directory() . '/template-parts/application/recovery.php'; ?>
		<?php endif; ?>
	</section></div>
<?php endif; ?>
</main>
<?php wp_footer(); ?>
</body></html>
