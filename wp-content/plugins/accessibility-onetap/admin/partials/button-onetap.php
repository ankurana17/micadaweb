<?php
/**
 * Admin Button Template for Onetap plugin.
 *
 * This template is responsible for rendering the button section
 * of the plugin's admin pages, including logo, documentation links,
 * support links, and navigation menu.
 *
 * @package    Accessibility_Onetap
 * @subpackage Accessibility_Onetap/admin/partials
 */

$onetap_settings = get_option( 'onetap_settings' );
if ( ! is_array( $onetap_settings ) ) {
	$onetap_settings = array();
}

$onetap_toggle_classes = array_filter(
	array(
		! empty( $onetap_settings['border'] ) ? $onetap_settings['border'] : '',
		isset( $onetap_settings['toggle-device-position-desktop'] ) && 'on' === $onetap_settings['toggle-device-position-desktop'] ? 'hide-on-desktop' : '',
		isset( $onetap_settings['toggle-device-position-tablet'] ) && 'on' === $onetap_settings['toggle-device-position-tablet'] ? 'hide-on-tablet' : '',
		isset( $onetap_settings['toggle-device-position-mobile'] ) && 'on' === $onetap_settings['toggle-device-position-mobile'] ? 'hide-on-mobile' : '',
	)
);
?>

<div class="box-visibility-toggle-icon <?php echo esc_attr( implode( ' ', $onetap_toggle_classes ) ); ?>">
	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
		<path d="M7.16196 3.39488C7.4329 3.35482 7.7124 3.33333 8.00028 3.33333C11.4036 3.33333 13.6369 6.33656 14.3871 7.52455C14.4779 7.66833 14.5233 7.74023 14.5488 7.85112C14.5678 7.93439 14.5678 8.06578 14.5487 8.14905C14.5233 8.25993 14.4776 8.3323 14.3861 8.47705C14.1862 8.79343 13.8814 9.23807 13.4777 9.7203M4.48288 4.47669C3.0415 5.45447 2.06297 6.81292 1.61407 7.52352C1.52286 7.66791 1.47725 7.74011 1.45183 7.85099C1.43273 7.93426 1.43272 8.06563 1.45181 8.14891C1.47722 8.25979 1.52262 8.33168 1.61342 8.47545C2.36369 9.66344 4.59694 12.6667 8.00028 12.6667C9.37255 12.6667 10.5546 12.1784 11.5259 11.5177M2.00028 2L14.0003 14M6.58606 6.58579C6.22413 6.94772 6.00028 7.44772 6.00028 8C6.00028 9.10457 6.89571 10 8.00028 10C8.55256 10 9.05256 9.77614 9.41449 9.41421" stroke="#414651" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
	</svg>
</div>

<button type="button" aria-label="Toggle Accessibility Toolbar" class="onetap-toggle <?php echo esc_attr( implode( ' ', $onetap_toggle_classes ) ); ?>">
	<?php
	// Define SVG paths for each icon type.
	$onetap_icon_paths = array(
		'design1' => 'assets/images/admin/Original_Logo_Icon.svg',
		'design2' => 'assets/images/admin/Hand_Icon.svg',
		'design3' => 'assets/images/admin/Accessibility-Man-Icon.svg',
		'design4' => 'assets/images/admin/Settings-Filter-Icon.svg',
		'design5' => 'assets/images/admin/Switcher-Icon.svg',
		'design6' => 'assets/images/admin/Eye-Show-Icon.svg',
	);

	// Check if the selected icon exists in the array.
	$onetap_settings = get_option( 'onetap_settings' );
	if ( isset( $onetap_settings['icons'], $onetap_icon_paths[ $onetap_settings['icons'] ] ) ) {
		$onetap_icons = array(
			'design1' => 'Original_Logo_Icon.svg',
			'design2' => 'Hand_Icon.svg',
			'design3' => 'Accessibility-Man-Icon.svg',
			'design4' => 'Settings-Filter-Icon.svg',
			'design5' => 'Switcher-Icon.svg',
			'design6' => 'Eye-Show-Icon.svg',
		);
		foreach ( $onetap_icons as $onetap_icon_value => $onetap_icon_image ) {
			if ( $onetap_icon_value === $onetap_settings['icons'] ) {
				$onetap_class_size   = isset( $onetap_settings['size'] ) ? $onetap_settings['size'] : '';
				$onetap_class_border = isset( $onetap_settings['border'] ) ? $onetap_settings['border'] : '';
				echo '<img class="' . esc_attr( $onetap_class_size ) . ' ' . esc_attr( $onetap_class_border ) . '" src="' . esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/' . $onetap_icon_image ) . '" alt="toggle icon" />';
			}
		}
	} else {
		echo '<img class="design-size2 design-border2" src="' . esc_url( ACCESSIBILITY_ONETAP_PLUGINS_URL . 'assets/images/admin/Original_Logo_Icon.svg' ) . '" alt="toggle icon" />';
	}
	?>
</button>