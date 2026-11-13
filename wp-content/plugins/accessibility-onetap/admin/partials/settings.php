<?php
/**
 * Content template for submenu page.
 *
 * @package    Accessibility_Onetap
 * @since      1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<?php onetap_load_template( 'admin/partials/header.php' ); ?>
	<div class="settings-container">
		<div class="settings-row">
			<?php $this->settings_api->show_forms(); ?>
			<div class="sidebar-preview">
				<div class="box-title">
					<h3><?php esc_html_e( 'Live Preview', 'accessibility-onetap' ); ?></h3>
				</div>
				<div class="devices-tabs">
					<button type="button" class="preview-desktop active" aria-pressed="true" data-device-type="desktop">
						<svg xmlns="http://www.w3.org/2000/svg" width="19" height="18" fill="none"><path d="M6.5 16.5H13.1667M9.83333 13.1667V16.5M5.5 13.1667H14.1667C15.5668 13.1667 16.2669 13.1667 16.8016 12.8942C17.272 12.6545 17.6545 12.272 17.8942 11.8016C18.1667 11.2669 18.1667 10.5668 18.1667 9.16667V5.5C18.1667 4.09987 18.1667 3.3998 17.8942 2.86502C17.6545 2.39462 17.272 2.01217 16.8016 1.77248C16.2669 1.5 15.5668 1.5 14.1667 1.5H5.5C4.09987 1.5 3.3998 1.5 2.86502 1.77248C2.39462 2.01217 2.01217 2.39462 1.77248 2.86502C1.5 3.3998 1.5 4.09987 1.5 5.5V9.16667C1.5 10.5668 1.5 11.2669 1.77248 11.8016C2.01217 12.272 2.39462 12.6545 2.86502 12.8942C3.3998 13.1667 4.09987 13.1667 5.5 13.1667Z" stroke="#717680" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					<?php esc_html_e( 'Desktop', 'accessibility-onetap' ); ?>
					</button>
					<button type="button" class="preview-tablet" aria-pressed="false" data-device-type="tablet">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="20" fill="none"><path d="M7.50004 14.5833H7.50837M3.50004 18.3333H11.5C12.4335 18.3333 12.9002 18.3333 13.2567 18.1516C13.5703 17.9918 13.8253 17.7369 13.9851 17.4233C14.1667 17.0668 14.1667 16.6 14.1667 15.6666V4.33329C14.1667 3.39987 14.1667 2.93316 13.9851 2.57664C13.8253 2.26304 13.5703 2.00807 13.2567 1.84828C12.9002 1.66663 12.4335 1.66663 11.5 1.66663H3.50004C2.56662 1.66663 2.09991 1.66663 1.74339 1.84828C1.42979 2.00807 1.17482 2.26304 1.01503 2.57664C0.833374 2.93316 0.833374 3.39987 0.833374 4.33329V15.6666C0.833374 16.6 0.833374 17.0668 1.01503 17.4233C1.17482 17.7369 1.42979 17.9918 1.74339 18.1516C2.09991 18.3333 2.56662 18.3333 3.50004 18.3333ZM7.91671 14.5833C7.91671 14.8134 7.73016 15 7.50004 15C7.26992 15 7.08337 14.8134 7.08337 14.5833C7.08337 14.3532 7.26992 14.1666 7.50004 14.1666C7.73016 14.1666 7.91671 14.3532 7.91671 14.5833Z" stroke="#A4A7AE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path></svg>
						<?php esc_html_e( 'Tablet', 'accessibility-onetap' ); ?>
					</button>
					<button type="button" class="preview-mobile" aria-pressed="false" data-device-type="mobile">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="20" fill="none"><path d="M6.66659 14.5833H6.67492M3.49992 18.3333H9.83325C10.7667 18.3333 11.2334 18.3333 11.5899 18.1516C11.9035 17.9918 12.1585 17.7369 12.3183 17.4233C12.4999 17.0668 12.4999 16.6 12.4999 15.6666V4.33329C12.4999 3.39987 12.4999 2.93316 12.3183 2.57664C12.1585 2.26304 11.9035 2.00807 11.5899 1.84828C11.2334 1.66663 10.7667 1.66663 9.83325 1.66663H3.49992C2.5665 1.66663 2.09979 1.66663 1.74327 1.84828C1.42966 2.00807 1.1747 2.26304 1.01491 2.57664C0.833252 2.93316 0.833252 3.39987 0.833252 4.33329V15.6666C0.833252 16.6 0.833252 17.0668 1.01491 17.4233C1.1747 17.7369 1.42966 17.9918 1.74327 18.1516C2.09979 18.3333 2.5665 18.3333 3.49992 18.3333ZM7.08325 14.5833C7.08325 14.8134 6.8967 15 6.66659 15C6.43647 15 6.24992 14.8134 6.24992 14.5833C6.24992 14.3532 6.43647 14.1666 6.66659 14.1666C6.8967 14.1666 7.08325 14.3532 7.08325 14.5833Z" stroke="#A4A7AE" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path></svg>
						<?php esc_html_e( 'Mobile', 'accessibility-onetap' ); ?>
					</button>
				</div>
				<div class="preview-frame">
					<div class="preview-container">
						<div style="background-image: url(<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/viewport-desktop.png' ); ?>);" class="preview-viewport viewport-desktop active" data-device="desktop">
							<?php onetap_load_template( 'admin/partials/button-onetap.php' ); ?>
						</div>

						<div style="background-image: url(<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/viewport-tablet.png' ); ?>);" class="preview-viewport viewport-tablet" data-device="tablet">
							<?php onetap_load_template( 'admin/partials/button-onetap.php' ); ?>
						</div>
						
						<div style="background-image: url(<?php echo esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/viewport-mobile.png' ); ?>);" class="preview-viewport viewport-mobile" data-device="mobile">
							<?php onetap_load_template( 'admin/partials/button-onetap.php' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php onetap_load_template( 'admin/partials/footer.php' ); ?>
</div>