/* eslint no-undef: "off", no-alert: "off" */
( function( $ ) {
	/**
	 * Display a lightweight toast notification in the bottom-right corner.
	 *
	 * Replaces the previous SweetAlert popups. Toasts stack vertically,
	 * auto-dismiss after a delay, and can also be closed manually.
	 *
	 * @param {string} message    - The text to display.
	 * @param {string} [type]     - One of 'success', 'error', 'info'. Defaults to 'success'.
	 * @param {number} [duration] - Milliseconds before auto-dismiss. Defaults to 4000.
	 */
	function showToast( message, type = 'success', duration = 4000 ) {
		// Ensure a single shared container exists to stack toasts.
		let $container = $( '.onetap-toast-container' );
		if ( ! $container.length ) {
			$container = $( '<div class="onetap-toast-container" role="region" aria-live="polite"></div>' );
			$( 'body' ).append( $container );
		}

		// Icon markup per toast type.
		const icons = {
			success: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12.5l4 4 10-10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			error: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			info: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8h.01M11 12h1v5h1" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		};
		const toastType = icons[ type ] ? type : 'success';

		// Close (X) icon for manual dismissal.
		const closeIcon = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		// Build the toast element.
		const $toast = $(
			'<div class="onetap-toast onetap-toast--' + toastType + '" role="alert">' +
				'<span class="onetap-toast__icon">' + icons[ toastType ] + '</span>' +
				'<span class="onetap-toast__message"></span>' +
				'<button type="button" class="onetap-toast__close" aria-label="Close">' + closeIcon + '</button>' +
			'</div>'
		);

		// Use text() so the message is never interpreted as HTML.
		$toast.find( '.onetap-toast__message' ).text( message );
		$container.append( $toast );

		// Trigger the slide-in transition on the next frame.
		window.requestAnimationFrame( function() {
			$toast.addClass( 'onetap-toast--visible' );
		} );

		// Slide-out then remove from the DOM.
		let dismissed = false;
		function dismiss() {
			if ( dismissed ) {
				return;
			}
			dismissed = true;
			$toast.removeClass( 'onetap-toast--visible' );
			$toast.one( 'transitionend', function() {
				$toast.remove();
			} );
			// Fallback removal in case transitionend never fires.
			setTimeout( function() {
				$toast.remove();
			}, 400 );
		}

		$toast.find( '.onetap-toast__close' ).on( 'click', dismiss );

		if ( duration > 0 ) {
			setTimeout( dismiss, duration );
		}
	}

	// Event handler for click on label within icon settings group
	$( '.settings-group.icons .boxes .box label' ).on( 'click', function() {
		// Get the image URL from the img element inside the clicked label
		const getIcon = $( this ).children().attr( 'src' );

		// Update the image in size settings group with the selected image
		$( '.settings-group.size .boxes .box label img' ).attr( 'src', getIcon );

		// Update the image in border settings group with the selected image
		$( '.settings-group.border .boxes .box label img' ).attr( 'src', getIcon );
		$( '.sidebar-preview .preview-viewport button img' ).attr( 'src', getIcon );
	} );

	$( '.settings-group.size .boxes .box label' ).on( 'click', function() {
		if ( 'design-size1' === $( this ).find( 'input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				padding: '10px',
				width: '50px',
				height: '50px',
			} );
		} else if ( 'design-size2' === $( this ).find( 'input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				padding: '15px',
				width: '65px',
				height: '65px',
			} );
		} else if ( 'design-size3' === $( this ).find( 'input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				padding: '17.5px',
				width: '80px',
				height: '80px',
			} );
		} else {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				padding: '15px',
				width: '65px',
				height: '65px',
			} );
		}
	} );

	$( '.settings-group.border .boxes .box label' ).on( 'click', function() {
		const OutlineColor = $( '.settings-group.color .box1' ).attr( 'style' );

		// Extract color value from CSS custom property
		const colorMatch = OutlineColor ? OutlineColor.match( /--outline-color:\s*(#[0-9a-fA-F]{6}|#[0-9a-fA-F]{3})/ ) : null;
		const colorValue = colorMatch ? colorMatch[ 1 ] : null;

		if ( 'design-border1' === $( this ).find( 'input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				border: 'solid 2px #fff',
				'box-shadow': '0 0 0 4px ' + colorValue,
			} );
		} else if ( 'design-border2' === $( this ).find( 'input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				border: 'none',
				'box-shadow': 'none',
			} );
		} else {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				border: 'none',
				'box-shadow': 'none',
			} );
		}
	} );

	// Predefined color options
	$( '.settings-group.color .box3 ul li' ).on( 'click', function() {
		const selectedColor = $( this ).data( 'color' );
		$( '.sidebar-preview .preview-viewport button img' ).css( {
			'background-color': selectedColor,
		} );

		$( '.setting-control.radio-image .boxes .box label img' ).css( {
			'background-color': selectedColor,
		} );

		if ( 'design-border1' === $( '.settings-group.border .boxes .box label input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				border: 'solid 2px #fff',
				'box-shadow': '0 0 0 4px ' + selectedColor,
			} );

			$( '.settings-group.border .setting-control.radio-image .boxes .box1 label img' ).css( {
				border: 'solid 2px #fff',
				'box-shadow': '0 0 0 4px ' + selectedColor,
			} );
		} else if ( 'design-border2' === $( '.settings-group.border .boxes .box label input' ).val() ) {
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				border: 'none',
				'box-shadow': 'none',
			} );
		}

		$( '.settings-group.color .color-result' ).text( selectedColor );
		$( '.settings-group.color .wp-color-picker' ).val( selectedColor ).trigger( 'change' );
	} );

	/**
	 * Widget position functionality - refactored for efficiency
	 * Handles position changes for all device types (desktop, tablet, mobile)
	 */

	// Position styles configuration object
	const positionStyles = {
		'top-right': {
			bottom: '83%',
			'margin-top': '40px',
			right: '0',
			'margin-right': '40px',
		},
		'top-left': {
			bottom: '83%',
			'margin-top': '40px',
			left: '0',
			'margin-left': '40px',
		},
		'middle-right': {
			right: '0',
			'margin-right': '40px',
			bottom: '50%',
			'margin-bottom': '40px',
		},
		'middle-left': {
			left: '0',
			'margin-left': '40px',
			bottom: '50%',
			'margin-bottom': '40px',
		},
		'bottom-right': {
			right: '0',
			'margin-right': '40px',
			bottom: '0',
			'margin-bottom': '40px',
		},
		'bottom-left': {
			left: '0',
			'margin-left': '40px',
			bottom: '0',
			'margin-bottom': '40px',
		},
	};

	// Reset styles object
	const resetStyles = {
		top: '',
		right: '',
		bottom: '',
		left: '',
		'margin-top': '',
		'margin-right': '',
		'margin-bottom': '',
		'margin-left': '',
	};

	/**
	 * Update widget position for specific device
	 * @param {string} selectedPosition - The selected position value
	 * @param {string} deviceType       - The device type (desktop, tablet, mobile)
	 */
	function updateWidgetPosition( selectedPosition, deviceType ) {
		const $previewButton = $( `.sidebar-preview .viewport-${ deviceType } button` );

		// Reset all position styles first
		$previewButton.css( resetStyles );

		// Apply position styles based on selection
		if ( positionStyles[ selectedPosition ] ) {
			$previewButton.css( positionStyles[ selectedPosition ] );
		}
	}

	// Event listeners for all device types
	$( '.settings-group.widge-position.desktop .widget-position-input' ).on( 'change', function() {
		const selectedPosition = $( this ).val();
		updateWidgetPosition( selectedPosition, 'desktop' );

		// Trigger position adjustments when widget position changes
		const topBottomValue = $( '.settings-group.position-top-bottom.desktop input' ).val();
		const leftRightValue = $( '.settings-group.position-left-right.desktop input' ).val();

		if ( topBottomValue ) {
			updatePosition( 'desktop', 'top-bottom', topBottomValue );
		}
		if ( leftRightValue ) {
			updatePosition( 'desktop', 'left-right', leftRightValue );
		}
	} );

	$( '.settings-group.widge-position-tablet.tablet .widget-position-input' ).on( 'change', function() {
		const selectedPosition = $( this ).val();
		updateWidgetPosition( selectedPosition, 'tablet' );

		// Trigger position adjustments when widget position changes
		const topBottomValue = $( '.settings-group.position-top-bottom-tablet.tablet input' ).val();
		const leftRightValue = $( '.settings-group.position-left-right-tablet.tablet input' ).val();

		if ( topBottomValue ) {
			updatePosition( 'tablet', 'top-bottom', topBottomValue );
		}
		if ( leftRightValue ) {
			updatePosition( 'tablet', 'left-right', leftRightValue );
		}
	} );

	$( '.settings-group.widge-position-mobile.mobile .widget-position-input' ).on( 'change', function() {
		const selectedPosition = $( this ).val();
		updateWidgetPosition( selectedPosition, 'mobile' );

		// Trigger position adjustments when widget position changes
		const topBottomValue = $( '.settings-group.position-top-bottom-mobile.mobile input' ).val();
		const leftRightValue = $( '.settings-group.position-left-right-mobile.mobile input' ).val();

		if ( topBottomValue ) {
			updatePosition( 'mobile', 'top-bottom', topBottomValue );
		}
		if ( leftRightValue ) {
			updatePosition( 'mobile', 'left-right', leftRightValue );
		}
	} );

	/**
	 * Position adjustment functionality - refactored for efficiency
	 * Handles top-bottom and left-right positioning for all device types
	 */

	/**
	 * Update position based on input value and widget position
	 * @param {string} deviceType - The device type (desktop, tablet, mobile)
	 * @param {string} direction  - The direction (top-bottom or left-right)
	 * @param {number} value      - The input value
	 */
	function updatePosition( deviceType, direction, value ) {
		// Get the correct selector for widget position based on device type
		let widgetPositionSelector;
		if ( deviceType === 'desktop' ) {
			widgetPositionSelector = `.settings-group.widge-position.${ deviceType } .widget-position-input`;
		} else if ( deviceType === 'tablet' ) {
			widgetPositionSelector = `.settings-group.widge-position-tablet.${ deviceType } .widget-position-input`;
		} else if ( deviceType === 'mobile' ) {
			widgetPositionSelector = `.settings-group.widge-position-mobile.${ deviceType } .widget-position-input`;
		}

		const selectedPosition = $( widgetPositionSelector ).val();

		// Device-specific configuration with separate multipliers for each direction
		const deviceConfig = {
			desktop: {
				leftRight: {
					baseValue: 20,
					multiplier: 0.8,
				},
				topBottom: {
					baseValue: 40,
					multiplier: 1.1,
				},
			},
			tablet: {
				leftRight: {
					baseValue: 230,
					multiplier: 1,
				},
				topBottom: {
					baseValue: 20,
					multiplier: 1.1,
				},
			},
			mobile: {
				leftRight: {
					baseValue: 325,
					multiplier: 1.1,
				},
				topBottom: {
					baseValue: 15,
					multiplier: 1,
				},
			},
		};

		// Get configuration for current device
		const config = deviceConfig[ deviceType ];
		if ( ! config ) {
			console.warn( `No configuration found for device type: ${ deviceType }` );
			return;
		}

		// Calculate values based on direction
		let calculatedValue;
		if ( direction === 'left-right' ) {
			calculatedValue = ( config.leftRight.baseValue + ( parseInt( value ) * config.leftRight.multiplier ) ) + 'px';
		} else if ( direction === 'top-bottom' ) {
			calculatedValue = ( config.topBottom.baseValue + ( parseInt( value ) * config.topBottom.multiplier ) ) + 'px';
		} else {
			console.warn( `Invalid direction: ${ direction }` );
			return;
		}

		const $previewButton = $( `.sidebar-preview .viewport-${ deviceType } button` );

		if ( direction === 'top-bottom' ) {
			// Handle top-bottom positioning (half values)
			if ( selectedPosition && selectedPosition.includes( 'top' ) ) {
				$previewButton.css( {
					'margin-top': calculatedValue,
					top: 0,
				} );
			} else {
				$previewButton.css( 'margin-bottom', calculatedValue );
			}
		} else if ( direction === 'left-right' ) {
			// Handle left-right positioning (full values)
			if ( selectedPosition && selectedPosition.includes( 'left' ) ) {
				$previewButton.css( {
					'margin-left': calculatedValue,
					left: 0,
				} );
			} else {
				$previewButton.css( 'margin-right', calculatedValue );
			}
		}
	}

	/**
	 * Toggle button visibility for specific device
	 * @param {string}  deviceType - The device type (desktop, tablet, mobile)
	 * @param {boolean} isChecked  - Whether the checkbox is checked
	 */
	function toggleButtonVisibility( deviceType, isChecked ) {
		const $previewButton = $( `.sidebar-preview .viewport-${ deviceType } button` );
		$previewButton.css( 'display', isChecked ? 'none' : 'block' );
		$( `.sidebar-preview .viewport-${ deviceType } .box-visibility-toggle-icon` ).css( 'display', isChecked ? 'flex' : 'none' );
	}

	// Event listeners for position adjustments
	$( '.settings-group.position-top-bottom.desktop input' ).on( 'change', function() {
		updatePosition( 'desktop', 'top-bottom', $( this ).val() );
	} );

	$( '.settings-group.position-top-bottom-tablet.tablet input' ).on( 'change', function() {
		updatePosition( 'tablet', 'top-bottom', $( this ).val() );
	} );

	$( '.settings-group.position-top-bottom-mobile.mobile input' ).on( 'change', function() {
		updatePosition( 'mobile', 'top-bottom', $( this ).val() );
	} );

	$( '.settings-group.position-left-right.desktop input' ).on( 'change', function() {
		updatePosition( 'desktop', 'left-right', $( this ).val() );
	} );

	$( '.settings-group.position-left-right-tablet.tablet input' ).on( 'change', function() {
		updatePosition( 'tablet', 'left-right', $( this ).val() );
	} );

	$( '.settings-group.position-left-right-mobile.mobile input' ).on( 'change', function() {
		updatePosition( 'mobile', 'left-right', $( this ).val() );
	} );

	// Event listeners for toggle visibility
	$( '.settings-group.toggle-device-position-desktop input' ).on( 'change', function() {
		toggleButtonVisibility( 'desktop', $( this ).is( ':checked' ) );
	} );

	$( '.settings-group.toggle-device-position-tablet.tablet input' ).on( 'change', function() {
		toggleButtonVisibility( 'tablet', $( this ).is( ':checked' ) );
	} );

	$( '.settings-group.toggle-device-position-mobile.mobile input' ).on( 'change', function() {
		toggleButtonVisibility( 'mobile', $( this ).is( ':checked' ) );
	} );

	/**
	 * Handle company website input focus/blur events
	 * Adds/removes focus class to protocol element for visual feedback
	 */
	function initCompanyWebsiteInput() {
		const $input = $( '#company_website' );
		const $protocol = $( '.protocol' );

		$input.on( 'focus', function() {
			$protocol.addClass( 'focus' );
		} );

		$input.on( 'blur', function() {
			$protocol.removeClass( 'focus' );
		} );
	}

	// Initialize company website input functionality
	initCompanyWebsiteInput();

	/**
	 * Initialize positions and visibility on page load
	 * Apply current settings when the page is loaded
	 */
	$( document ).ready( function() {
		const outlineColor = $( '.settings-group.color .box1' ).attr( 'style' );
		const colorMatch = outlineColor ? outlineColor.match( /--outline-color:\s*(#[0-9a-fA-F]{6}|#[0-9a-fA-F]{3})/ ) : null;
		const colorValue = colorMatch ? colorMatch[ 1 ] : null;

		// Get URL parameter and show corresponding element
		const urlParams = new URLSearchParams( window.location.search );
		const pageParam = urlParams.get( 'page' );

		if ( pageParam ) {
			// Convert hyphens to underscores for CSS ID selector compatibility
			let elementId = pageParam.replace( /-/g, '_' );

			// If contains "accessibility_onetap", remove only the "accessibility_" part
			if ( elementId.includes( 'accessibility_onetap' ) ) {
				elementId = elementId.replace( 'accessibility_', '' );
			}

			// Show the element with the converted ID
			$( `#${ elementId }` ).fadeIn( 400 );
			if ( 'apop_settings' === elementId || 'onetap_settings' === elementId ) {
				$( '.sidebar-preview' ).fadeIn( 400 );
			}

			if ( 'apop_accessibility_status' === elementId || 'accessibility-onetap-accessibility-status' === elementId ) {
				$( '#apop-accessibility-status' ).fadeIn( 400 );
			}
		}

		// Initialize widget positions for all devices
		[ 'desktop', 'tablet', 'mobile' ].forEach( function( deviceType ) {
			// Get the correct selector for widget position based on device type
			let widgetPositionSelector;
			if ( deviceType === 'desktop' ) {
				widgetPositionSelector = `.settings-group.widge-position.${ deviceType } .widget-position-input`;
			} else if ( deviceType === 'tablet' ) {
				widgetPositionSelector = `.settings-group.widge-position-tablet.${ deviceType } .widget-position-input`;
			} else if ( deviceType === 'mobile' ) {
				widgetPositionSelector = `.settings-group.widge-position-mobile.${ deviceType } .widget-position-input`;
			}

			const selectedPosition = $( widgetPositionSelector ).val();
			if ( selectedPosition ) {
				updateWidgetPosition( selectedPosition, deviceType );
			}

			// Initialize position adjustments
			let topBottomValue, leftRightValue;

			if ( deviceType === 'desktop' ) {
				topBottomValue = $( `.settings-group.position-top-bottom.${ deviceType } input` ).val();
				leftRightValue = $( `.settings-group.position-left-right.${ deviceType } input` ).val();
			} else if ( deviceType === 'tablet' ) {
				topBottomValue = $( `.settings-group.position-top-bottom-tablet.tablet input` ).val();
				leftRightValue = $( `.settings-group.position-left-right-tablet.tablet input` ).val();
			} else if ( deviceType === 'mobile' ) {
				topBottomValue = $( `.settings-group.position-top-bottom-mobile.mobile input` ).val();
				leftRightValue = $( `.settings-group.position-left-right-mobile.mobile input` ).val();
			}

			if ( topBottomValue ) {
				updatePosition( deviceType, 'top-bottom', topBottomValue );
			}
			if ( leftRightValue ) {
				updatePosition( deviceType, 'left-right', leftRightValue );
			}

			// Initialize toggle visibility
			const isChecked = $( `.settings-group.toggle-device-position-${ deviceType } input` ).is( ':checked' );
			toggleButtonVisibility( deviceType, isChecked );
		} );

		// Initialize size settings
		const selectedSize = $( '.settings-group.size .boxes .box input[type="radio"]:checked' ).val();
		if ( selectedSize ) {
			if ( 'design-size1' === selectedSize ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					padding: '10px',
					width: '50px',
					height: '50px',
				} );
			} else if ( 'design-size2' === selectedSize ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					padding: '15px',
					width: '65px',
					height: '65px',
				} );
			} else if ( 'design-size3' === selectedSize ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					padding: '17.5px',
					width: '80px',
					height: '80px',
				} );
			} else {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					padding: '15px',
					width: '65px',
					height: '65px',
				} );
			}
		}

		// Initialize color settings
		if ( colorValue ) {
			$( '.sidebar-preview .preview-frame .preview-container .preview-viewport button img' ).css( {
				'background-color': colorValue,
			} );

			$( '.setting-control.radio-image .boxes .box label img' ).css( {
				'background-color': colorValue,
			} );

			$( '.settings-group.border .boxes .box1 img' ).css( {
				'box-shadow': '0 0 0 4px ' + colorValue,
			} );
		}

		// Initialize border settings
		const selectedBorder = $( '.settings-group.border .boxes .box input[type="radio"]:checked' ).val();
		if ( selectedBorder ) {
			if ( 'design-border1' === selectedBorder ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					border: 'solid 2px #fff',
					'box-shadow': '0 0 0 4px ' + colorValue,
					'background-color': colorValue,
				} );
			} else if ( 'design-border2' === selectedBorder ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					border: 'none',
					'box-shadow': 'none',
				} );
			} else {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					border: 'none',
					'box-shadow': 'none',
				} );
			}
		}
	} );

	// Get the src attribute from the currently selected/checked icon
	const checkedIcon = $( '.settings-group.icons .boxes .box input[type="radio"]:checked' ).closest( 'label' ).find( 'img' ).attr( 'src' );

	// If a checked icon is found, update the size and border settings with the same icon
	if ( checkedIcon ) {
		// Update the image in size settings group to match the selected icon
		$( '.settings-group.size .boxes .box label img' ).attr( 'src', checkedIcon );
		// Update the image in border settings group to match the selected icon
		$( '.settings-group.border .boxes .box label img' ).attr( 'src', checkedIcon );
	}

	// Device configuration for settings visibility
	const deviceConfig = [
		{
			device: 'desktop',
			checkbox: $( 'input[name="onetap_settings[toggle-device-position-desktop]"]' ),
			elements: [
				$( '.settings-group.widge-position.desktop' ),
				$( '.settings-group.position-top-bottom.desktop' ),
				$( '.settings-group.position-left-right.desktop' ),
			],
		},
		{
			device: 'tablet',
			checkbox: $( 'input[name="onetap_settings[toggle-device-position-tablet]"]' ),
			elements: [
				$( '.settings-group.widge-position-tablet.tablet' ),
				$( '.settings-group.position-top-bottom-tablet.tablet' ),
				$( '.settings-group.position-left-right-tablet.tablet' ),
			],
		},
		{
			device: 'mobile',
			checkbox: $( 'input[name="onetap_settings[toggle-device-position-mobile]"]' ),
			elements: [
				$( '.settings-group.widge-position-mobile.mobile' ),
				$( '.settings-group.position-top-bottom-mobile.mobile' ),
				$( '.settings-group.position-left-right-mobile.mobile' ),
			],
		},
	];

	// Generic function to toggle device settings visibility
	function toggleDeviceSettings( config ) {
		const isChecked = config.checkbox.is( ':checked' );
		const action = isChecked ? 'addClass' : 'removeClass';

		// Toggle visibility for all elements in this device config
		// If checkbox is checked, hide elements (add 'hide' class)
		// If checkbox is unchecked, show elements (remove 'hide' class)
		config.elements.forEach( ( element ) => {
			element[ action ]( 'hide' );
		} );
	}

	// Initialize settings visibility on page load
	deviceConfig.forEach( ( config ) => {
		toggleDeviceSettings( config );

		// Add event listener for checkbox changes
		config.checkbox.on( 'change', function() {
			toggleDeviceSettings( config );
		} );
	} );

	/**
	 * Positions the badge based on the width of the selected text in the dropdown
	 */
	function positionBadge() {
		// Get the select element or language input and the badge
		const $select = $( 'select[name="onetap_settings[language]"]' );
		const $input = $( 'input[name="onetap_settings[language]"]' );
		const $element = $select.length ? $select : $input;

		if ( $element.length ) {
			const $badge = $element.closest( '.settings-group.language .box1' ).find( '.badge' );

			// Remove any inline left positioning to use CSS right positioning
			$badge.css( {
				left: 'auto',
				display: 'inline-block',
			} );
		}
	}

	// Call when page loads for initial position
	positionBadge();

	// Call when select or input changes to update badge position
	$( 'select[name="onetap_settings[language]"], input[name="onetap_settings[language]"]' ).on( 'change', function() {
		positionBadge();
	} );

	// Call the function to apply the active language labels
	getActiveLanguage();
	function getActiveLanguage() {
		// Check if the global object 'adminLocalize' and required properties exist
		if (
			typeof adminLocalize !== 'undefined' &&
			typeof adminLocalize.activeLanguage === 'string' &&
			adminLocalize.activeLanguage.trim() !== '' &&
			typeof adminLocalize.localizedLabels === 'object' &&
			adminLocalize.localizedLabels.hasOwnProperty( adminLocalize.activeLanguage )
		) {
			// Get the currently active language
			const activeLanguage = adminLocalize.activeLanguage;

			// Get the localized labels for the active language
			const labels = adminLocalize.localizedLabels[ activeLanguage ];

			// Loop through each label key
			for ( const key in labels ) {
				if ( labels.hasOwnProperty( key ) ) {
					// Find the input element with the corresponding class and set its value
					$( '.admin_page_apop-module-labels input.' + key ).attr( 'value', labels[ key ] );
				}
			}
		}
	}

	// Get the currently active language from the localized admin data
	const activeLanguage = adminLocalize.activeLanguage;
	if ( activeLanguage ) {
		// Get the display name (text) of the active language
		const languageName = $( `.current-language li[data-language="${ activeLanguage }"]` ).text();

		// Get the image source URL for the active language icon
		const languageImageSrc = $( `.current-language li[data-language="${ activeLanguage }"] img` ).attr( 'src' );

		// If both the language name and image source exist, update the UI accordingly
		if ( languageName && languageImageSrc ) {
			$( 'a.current-language .text-current-language strong.language-name' ).text( languageName );
			$( 'a.current-language .image img.active' ).attr( 'src', languageImageSrc );
			$( 'a.current-language' ).show();
		}
	}

	/**
	 * Copy content from a TinyMCE editor or textarea and give visual feedback.
	 *
	 * @param {string} editorId - The ID of the TinyMCE editor or textarea.
	 * @param {jQuery} $button  - The jQuery object for the copy button.
	 */
	$( '.copy' ).on( 'click', function() {
		const editorId = 'editor_generator';
		let content = '';

		// Try to get content from TinyMCE editor if available
		if ( typeof tinymce !== 'undefined' && tinymce.get( editorId ) ) {
			content = tinymce.get( editorId ).getContent();
		} else {
			// Fallback to plain textarea value if TinyMCE is not available
			content = $( '#' + editorId ).val();
		}

		const $button = $( this ); // The copy button element
		const $textElement = $button.find( '.copy-text' ); // The span that holds the button text
		const originalText = $button.data( 'default-text' ) || 'Copy'; // Default button text
		const copiedText = $button.data( 'copied-text' ) || 'Copied!'; // Text shown after copying

		// Copy the editor content to clipboard
		navigator.clipboard.writeText( content ).then( () => {
			// Update the button UI to show 'Copied!' and apply visual feedback
			$textElement.text( copiedText );
			$button.addClass( 'copied' );

			// Reset the button UI after 2 seconds
			setTimeout( () => {
				$textElement.text( originalText );
				$button.removeClass( 'copied' );
			}, 2000 );
		} );
	} );

	/**
	 * Copyable text box: add/remove focus class on .box-copy-text
	 * Add focus when .box-copy-text is clicked; remove when clicking outside.
	 */
	$( document ).on( 'click', '.setting-control.copyable_text .box-copy-text', function( e ) {
		e.stopPropagation();
		$( '.setting-control.copyable_text .box-copy-text' ).removeClass( 'focus' );
		$( this ).addClass( 'focus' );
	} );

	$( document ).on( 'click', function() {
		$( '.setting-control.copyable_text .box-copy-text' ).removeClass( 'focus' );
	} );

	/**
	 * Initialize form validation and interactivity for the Accessibility Statement form.
	 *
	 * This function:
	 * - Caches relevant form elements.
	 * - Validates input fields and checkbox in real-time.
	 * - Enables or disables the submit button based on validation.
	 * - Prevents form submission if validation fails.
	 *
	 * Assumes the DOM is already loaded before this function is called.
	 */
	function handleAccessibilityStatusForm() {
		// Cache selectors to avoid querying the DOM repeatedly
		const $selectLanguage = $( 'input[name="onetap_select_language"]' );
		const $companyName = $( 'input[name="onetap_company_name"]' );
		const $companyWebsite = $( 'input[name="onetap_company_website"]' );
		const $businessEmail = $( 'input[name="onetap_business_email"]' );
		const $confirmationCheckbox = $( 'input[name="onetap_confirmation_checkbox"]' );
		const $submitButton = $( 'button.save-changes.generate-accessibility-statement' );

		// Function to check if all form fields are valid
		function checkFormFields() {
			const selectLanguage = ( $selectLanguage.val() || '' ).trim();
			const companyName = ( $companyName.val() || '' ).trim();
			const companyWebsite = ( $companyWebsite.val() || '' ).trim();
			const businessEmail = ( $businessEmail.val() || '' ).trim();
			const confirm = $confirmationCheckbox.is( ':checked' );

			// Enable or disable the submit button based on input validation
			if ( selectLanguage && companyName && companyWebsite && businessEmail && confirm ) {
				$submitButton.addClass( 'active' );
			} else {
				$submitButton.removeClass( 'active' );
			}
		}

		// Attach event listeners for inputs and checkbox
		$selectLanguage.on( 'input', checkFormFields );
		$companyName.on( 'input', checkFormFields );
		$companyWebsite.on( 'input', checkFormFields );
		$businessEmail.on( 'input', checkFormFields );
		$confirmationCheckbox.on( 'change', checkFormFields );

		// Initial check on page load
		checkFormFields();

		// Prevent form submission if fields are not valid
		$submitButton.on( 'click', function( e ) {
			const selectLanguage = ( $selectLanguage.val() || 'en' ).trim();
			const companyName = ( $companyName.val() || '' ).trim();
			let companyWebsite = ( $companyWebsite.val() || '' ).trim();
			const businessEmail = ( $businessEmail.val() || '' ).trim();
			const confirm = $confirmationCheckbox.is( ':checked' );

			// Sanitize company website
			// Remove trailing slash
			companyWebsite = companyWebsite.replace( /\/$/, '' );
			// Trim any remaining whitespace
			companyWebsite = companyWebsite.trim();
			// Ensure URL has protocol: if missing, prepend https://
			if ( companyWebsite && ! /^https?:\/\//i.test( companyWebsite ) ) {
				companyWebsite = 'https://' + companyWebsite;
			}

			// Validate that all required fields are filled before proceeding
			if ( ! selectLanguage || ! companyName || ! companyWebsite || ! businessEmail || ! confirm ) {
				e.preventDefault();
				// Show warning using SweetAlert
				const i18n = ( typeof adminLocalize !== 'undefined' && adminLocalize.i18n ) || {};
				swal( {
					title: i18n.warning || 'Warning!',
					text: i18n.completeAllFields || 'Please complete all fields.',
					icon: 'info',
					showCloseButton: true,
				} );
				return;
			}

			// Find the status message element that matches the selected language
			const $matchingStatusMessage = $( '.status-message-accessibility[data-content-lang="' + selectLanguage + '"]' );

			// Generate current date in localized format: [MonthName Day, Year]
			const now = new Date();
			const options = { year: 'numeric', month: 'long', day: 'numeric' };
			const locale = selectLanguage || 'en'; // fallback to 'en' if not selected
			const formattedDate = `${ now.toLocaleDateString( locale, options ) }`;

			// Get the HTML content of the matching status message
			let htmlContent = $matchingStatusMessage.html(); // Use html() to preserve HTML formatting

			// Replace placeholders in the HTML with actual values
			htmlContent = htmlContent.replace( /\[Company Name\]/g, companyName )
				.replace( /\[Company Website\]/g, companyWebsite )
				.replace( /\[Company E-Mail\]/g, businessEmail )
				.replace( /\[March 9, 2025\]/g, formattedDate );

			// Set the final content into the TinyMCE editor if it's initialized
			const editor = tinymce.get( 'editor_generator' );
			if ( editor ) {
				editor.setContent( htmlContent );
			}

			e.preventDefault();
		} );
	}
	handleAccessibilityStatusForm();

	/**
	 * Handle edit button functionality for ALT TEXT  editing
	 */
	$( '.box-image-alt .button.edit-btn' ).on( 'click', function() {
		const $button = $( this );
		const $row = $button.closest( '.row' );
		const $altTextCol = $row.find( '.col.alt-text' );
		const $textSpan = $altTextCol.find( '.text' );
		const imageId = $button.data( 'image-id' );

		// Get current text content
		const currentText = $textSpan.text();

		// Hide the edit button
		$button.addClass( 'hide' );

		// Show the save button
		$button.siblings( '.save-btn' ).removeClass( 'hide' );

		// Replace text span with textarea
		$textSpan.replaceWith( '<textarea data-image-id="' + imageId + '">' + currentText + '</textarea>' );
	} );

	/**
	 * Handle save button functionality for ALT TEXT saving
	 */
	$( document ).on( 'click', '.box-image-alt .button.save-btn', function() {
		const $button = $( this );
		const $row = $button.closest( '.row' );
		const $altTextCol = $row.find( '.col.alt-text' );
		const $textarea = $altTextCol.find( 'textarea' );
		const imageId = $button.data( 'image-id' );

		// Get textarea value
		const newText = $textarea.val();

		// Send AJAX request to save alt text
		$.ajax( {
			url: adminLocalize.ajaxUrl,
			method: 'POST',
			data: {
				nonce: adminLocalize.ajaxNonce,
				action: 'onetap_save_alt_text',
				image_id: imageId,
				alt_text: newText,
			},
			success( response ) {
				if ( response.success ) {
					// Hide the save button
					$button.addClass( 'hide' );

					// Show the edit button
					$button.siblings( '.edit-btn' ).removeClass( 'hide' );

					// Replace textarea with text span
					$textarea.replaceWith( '<span class="text">' + newText + '</span>' );

					// Show success message
					showToast( 'Your changes have been saved.', 'success' );
				} else {
					// Show error message
					showToast( response.error || 'Failed to save alt text', 'error' );
				}
			},
			error( xhr, textStatus, errorThrown ) {
				console.error( 'Error saving alt text:', errorThrown );

				// Show error message
				showToast( 'Failed to save alt text. Please try again.', 'error' );
			},
		} );
	} );

	/**
	 * Prevent navigation when disabled pagination buttons are clicked
	 * Handles both previous and next buttons that are disabled
	 */
	$( document ).on( 'click', '.button.disable.prev-btn, .button.disable.next-btn', function( e ) {
		// Prevent the default link behavior (redirection)
		e.preventDefault();

		// Return false to ensure no further action
		return false;
	} );

	/**
	 * Append current language box into module labels control
	 * Clones .box-current-language (with descendants) and appends to .setting-control.module-labels
	 * Ensures the appended box displays as flex and avoids duplicate insertion
	 */
	function appendCurrentLanguageBoxToModuleLabels() {
		const $sourceBox = $( '.box-current-language' );
		const $targetControl = $( '.settings-group.current-language .setting-control.module-labels' );

		if ( $sourceBox.length && $targetControl.length ) {
			// Avoid duplicate insertion if already appended
			if ( $targetControl.find( '.box-current-language' ).length === 0 ) {
				const $cloned = $sourceBox.first().clone( true, true );
				$cloned.css( 'display', 'flex' );
				$targetControl.append( $cloned );
			} else {
				$targetControl.find( '.box-current-language' ).css( 'display', 'flex' );
			}
		}
	}

	// Call the function when document is ready
	$( document ).ready( function() {
		appendCurrentLanguageBoxToModuleLabels();
	} );

	/**
	 * Handle widget position dropdown functionality
	 * Manages opening/closing dropdown and selecting position options
	 */
	$( document ).on( 'click', '.widget-position-trigger', function( e ) {
		e.stopPropagation();
		const $trigger = $( this );
		const $dropdown = $trigger.closest( '.widget-position-dropdown' );
		const $options = $dropdown.find( '.widget-position-options' );
		const isExpanded = $trigger.attr( 'aria-expanded' ) === 'true';

		// Close all other dropdowns
		$( '.widget-position-dropdown' ).each( function() {
			const $otherDropdown = $( this );
			if ( $otherDropdown[ 0 ] !== $dropdown[ 0 ] ) {
				$otherDropdown.find( '.widget-position-trigger' ).attr( 'aria-expanded', 'false' );
				$otherDropdown.find( '.widget-position-options' ).removeClass( 'open' );
			}
		} );

		// Toggle current dropdown
		if ( isExpanded ) {
			$trigger.attr( 'aria-expanded', 'false' );
			$options.removeClass( 'open' );
		} else {
			$trigger.attr( 'aria-expanded', 'true' );
			$options.addClass( 'open' );
		}
	} );

	// Handle option selection
	$( document ).on( 'click', '.widget-position-option', function( e ) {
		e.stopPropagation();
		const $option = $( this );
		const $dropdown = $option.closest( '.widget-position-dropdown' );
		const $trigger = $dropdown.find( '.widget-position-trigger' );
		const $input = $dropdown.find( '.widget-position-input' );
		const $selected = $trigger.find( '.widget-position-selected' );
		const $options = $dropdown.find( '.widget-position-options' );

		const value = $option.data( 'value' );
		const label = $option.find( '.widget-position-option-label' ).text();
		const icon = $option.find( '.widget-position-option-icon' ).html();

		// Update input value
		$input.val( value );

		// Update selected display
		$selected.find( '.widget-position-icon' ).html( icon );
		$selected.find( '.widget-position-label' ).text( label );

		// Update selected state in options
		$dropdown.find( '.widget-position-option' ).removeClass( 'selected' );
		$option.addClass( 'selected' );

		// Close dropdown
		$trigger.attr( 'aria-expanded', 'false' );
		$options.removeClass( 'open' );

		// Trigger change event on input
		$input.trigger( 'change' );
	} );

	// Close dropdown when clicking outside
	$( document ).on( 'click', function( e ) {
		if ( ! $( e.target ).closest( '.widget-position-dropdown' ).length ) {
			$( '.widget-position-dropdown' ).each( function() {
				$( this ).find( '.widget-position-trigger' ).attr( 'aria-expanded', 'false' );
				$( this ).find( '.widget-position-options' ).removeClass( 'open' );
			} );
		}
	} );

	/**
	 * ============================================================================
	 * LANGUAGE SELECT SIMPLE - Control JavaScript
	 * ============================================================================
	 * JavaScript for language-select-simple control
	 * Specific selector: .setting-control.language-select-simple .language-select-wrapper
	 */

	// Step 1: Toggle dropdown when .language-select-wrapper is clicked (for simple)
	$( document ).on( 'click', '.setting-control.language-select-simple .language-select-wrapper', function( e ) {
		e.stopPropagation();
		const $wrapper = $( this );
		const $dropdown = $wrapper.closest( '.language-select-dropdown' );
		const $trigger = $wrapper.find( '.language-select-trigger' );
		const $options = $dropdown.find( '.language-select-options' );

		// Ensure SIMPLE control is visible and its corresponding TOGGLES control is hidden.
		const $simpleControl = $wrapper.closest( '.setting-control.language-select-simple' );
		let $togglesControl = $simpleControl.siblings( '.setting-control.language-select-toggles' ).first();

		// Fallback: if no sibling found, use global selector (in case of different markup).
		if ( ! $togglesControl.length ) {
			$togglesControl = $( '.setting-control.language-select-toggles' ).first();
		}

		$simpleControl.css( 'display', 'block' );
		if ( $togglesControl.length ) {
			$togglesControl.css( 'display', 'none' );
		}

		// Close all other language select simple dropdowns
		$( '.setting-control.language-select-simple .language-select-dropdown' ).each( function() {
			const $otherDropdown = $( this );
			if ( $otherDropdown[ 0 ] !== $dropdown[ 0 ] ) {
				$otherDropdown.find( '.language-select-trigger' ).attr( 'aria-expanded', 'false' );
				$otherDropdown.find( '.language-select-options' ).removeClass( 'open' );
			}
		} );

		// Always open current dropdown when wrapper is clicked.
		$trigger.attr( 'aria-expanded', 'true' );
		$options.addClass( 'open' );
	} );

	// Step 1b: When "display options" label on SIMPLE control is clicked,
	// hide simple control and show toggles control.
	$( document ).on( 'click', '.setting-control.language-select-simple .language-select-label-display-options', function( e ) {
		e.stopPropagation();

		// Find current SIMPLE control and its related TOGGLES control (prefer sibling).
		const $simpleControl = $( this ).closest( '.setting-control.language-select-simple' );
		let $togglesControl = $simpleControl.siblings( '.setting-control.language-select-toggles' ).first();

		// Before switching view, fully "close" the SIMPLE dropdown so that
		// the CSS transition tied to `.language-select-options.open` can re-run
		// the next time we open it.
		const $simpleDropdown = $simpleControl.find( '.language-select-dropdown' ).first();
		const $simpleTrigger = $simpleDropdown.find( '.language-select-trigger' );
		const $simpleOptions = $simpleDropdown.find( '.language-select-options' );

		$simpleTrigger.attr( 'aria-expanded', 'false' );
		$simpleOptions.removeClass( 'open' );

		// Fallback: if no sibling found, use global selector (in case of different markup).
		if ( ! $togglesControl.length ) {
			$togglesControl = $( '.setting-control.language-select-toggles' ).first();
		}

		// Hide simple selector, show toggles selector.
		$simpleControl.css( 'display', 'none' );
		$togglesControl.css( 'display', 'block' );

		// Smoothly open the TOGGLES dropdown with CSS transition on `.open`.
		const $togglesDropdown = $togglesControl.find( '.language-select-dropdown' ).first();
		const $togglesTrigger = $togglesDropdown.find( '.language-select-trigger' );
		const $togglesOptions = $togglesDropdown.find( '.language-select-options' );

		// Ensure the TOGGLES dropdown is in the "closed" state first.
		$togglesTrigger.attr( 'aria-expanded', 'false' );
		$togglesOptions.removeClass( 'open' );

		// Add `.open` on the next frame so the CSS transition can run.
		const openWithAnimation = function() {
			$togglesTrigger.attr( 'aria-expanded', 'true' );
			$togglesOptions.addClass( 'open' );
		};

		if ( window.requestAnimationFrame ) {
			window.requestAnimationFrame( openWithAnimation );
		} else {
			setTimeout( openWithAnimation, 0 );
		}
	} );

	// Step 2: Handle option selection for simple
	$( document ).on( 'click', '.setting-control.language-select-simple .language-select-option', function( e ) {
		e.stopPropagation();
		const $option = $( this );
		const $dropdown = $option.closest( '.language-select-dropdown' );
		const $trigger = $dropdown.find( '.language-select-trigger' );
		const $input = $dropdown.find( '.language-select-input' );
		const $selected = $trigger.find( '.language-select-selected' );
		const $options = $dropdown.find( '.language-select-options' );

		const value = $option.data( 'value' );
		const label = $option.find( '.language-select-option-label' ).text();
		const flagImg = $option.find( '.language-select-option-flag img' ).attr( 'src' );

		// Update input value
		$input.val( value );
		$input.attr( 'data-lang', value );

		// Update trigger data-lang
		$trigger.attr( 'data-lang', value );

		// Update selected display
		$selected.find( '.language-select-label' ).text( label );
		$selected.find( '.language-select-flag img' ).attr( 'src', flagImg );

		// Update selected state in options
		$dropdown.find( '.language-select-option' ).removeClass( 'selected' );
		$option.addClass( 'selected' );

		// Sync selected state to language-select-toggles control
		$( '.setting-control.language-select-toggles .language-select-dropdown' ).each( function() {
			const $togglesDropdown = $( this );
			const $togglesTrigger = $togglesDropdown.find( '.language-select-trigger' );
			const $togglesInput = $togglesDropdown.find( '.language-select-input' );
			const $togglesSelected = $togglesTrigger.find( '.language-select-selected' );
			const $togglesOptions = $togglesDropdown.find( '.language-select-options' );

			// Remove selected class from all options
			$togglesOptions.find( '.language-select-option' ).removeClass( 'selected' );

			// Add selected class to matching option by data-value
			const $matchingOption = $togglesOptions.find( '.language-select-option[data-value="' + value + '"]' );
			if ( $matchingOption.length ) {
				$matchingOption.addClass( 'selected' );

				// Update trigger display (flag and label)
				$togglesTrigger.attr( 'data-lang', value );
				$togglesSelected.find( '.language-select-label' ).text( label );
				$togglesSelected.find( '.language-select-flag img' ).attr( 'src', flagImg );

				// Update input value
				$togglesInput.val( value );
				$togglesInput.attr( 'data-lang', value );

				// Set toggle checkbox to ON (checked)
				const $matchingCheckbox = $matchingOption.find( 'input[type="checkbox"]' );
				if ( $matchingCheckbox.length ) {
					$matchingCheckbox.prop( 'checked', true );
					// Trigger change event on checkbox to ensure form submission works
					$matchingCheckbox.trigger( 'change' );
				}
			}
		} );

		// Close dropdown
		$trigger.attr( 'aria-expanded', 'false' );
		$options.removeClass( 'open' );

		// Trigger change event on input
		$input.trigger( 'change' );
	} );

	// Step 3: Close dropdown when clicking outside (for simple)
	$( document ).on( 'click', function( e ) {
		if ( ! $( e.target ).closest( '.setting-control.language-select-simple .language-select-dropdown' ).length ) {
			$( '.setting-control.language-select-simple .language-select-dropdown' ).each( function() {
				$( this ).find( '.language-select-trigger' ).attr( 'aria-expanded', 'false' );
				$( this ).find( '.language-select-options' ).removeClass( 'open' );
			} );
		}
	} );

	// Step 4: Close simple dropdown when clicking on the labels wrapper (title + "Display Options").
	$( document ).on( 'click', '.setting-control.language-select-simple .language-select-labels-wrapper', function( e ) {
		e.stopPropagation();

		const $dropdown = $( this ).closest( '.language-select-dropdown' );
		const $trigger = $dropdown.find( '.language-select-trigger' );
		const $options = $dropdown.find( '.language-select-options' );

		$trigger.attr( 'aria-expanded', 'false' );
		$options.removeClass( 'open' );
	} );

	/**
	 * ============================================================================
	 * SYNC LANGUAGE-SELECT-SIMPLE WITH FRONTEND LOCALSTORAGE
	 * ============================================================================
	 * When the settings form is submitted, update localStorage
	 * `onetap-accessibility-free.information.language` to match the
	 * selected value from language-select-simple.
	 */

	$( document ).on( 'submit', 'form[action="options.php"]', function() {
		try {
			// Get selected language from the simple language control.
			const selectedLanguage = $( '.setting-control.language-select-simple .language-select-input' ).val();

			if ( ! selectedLanguage ) {
				return true; // Nothing to sync.
			}

			// Read existing localStorage value.
			const storageKey = 'onetap-accessibility-free';
			const raw = window.localStorage ? window.localStorage.getItem( storageKey ) : null;

			if ( ! raw ) {
				return true; // Nothing stored yet on the frontend.
			}

			let data;
			try {
				data = JSON.parse( raw );
			} catch ( parseError ) {
				// Invalid JSON, don't block form submit.
				return true;
			}

			// Ensure information object exists.
			if ( typeof data.information !== 'object' || data.information === null ) {
				data.information = {};
			}

			// Update language value.
			data.information.language = selectedLanguage;

			// Save back to localStorage.
			if ( window.localStorage ) {
				window.localStorage.setItem( storageKey, JSON.stringify( data ) );
			}
		} catch ( e ) {
			// Silently ignore errors to avoid breaking WP settings submit.
		}

		return true; // Allow form submission to continue.
	} );

	/**
	 * ============================================================================
	 * LANGUAGE SELECT TOGGLES - Control JavaScript
	 * ============================================================================
	 * JavaScript for language-select-toggles control
	 * Specific selector: .setting-control.language-select-toggles .language-select-wrapper
	 */

	// Step 1: Toggle dropdown when .language-select-wrapper is clicked (for toggles)
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-select-wrapper', function( e ) {
		e.stopPropagation();
		const $wrapper = $( this );
		const $dropdown = $wrapper.closest( '.language-select-dropdown' );
		const $trigger = $wrapper.find( '.language-select-trigger' );
		const $options = $dropdown.find( '.language-select-options' );
		const isExpanded = $trigger.attr( 'aria-expanded' ) === 'true';

		// Close all other language select toggles dropdowns
		$( '.setting-control.language-select-toggles .language-select-dropdown' ).each( function() {
			const $otherDropdown = $( this );
			if ( $otherDropdown[ 0 ] !== $dropdown[ 0 ] ) {
				$otherDropdown.find( '.language-select-trigger' ).attr( 'aria-expanded', 'false' );
				$otherDropdown.find( '.language-select-options' ).removeClass( 'open' );
			}
		} );

		// Toggle current dropdown
		if ( isExpanded ) {
			$trigger.attr( 'aria-expanded', 'false' );
			$options.removeClass( 'open' );
		} else {
			$trigger.attr( 'aria-expanded', 'true' );
			$options.addClass( 'open' );
		}
	} );

	// Step 1a: When the trigger itself is clicked inside TOGGLES control,
	// hide the entire language-select-toggles control and show SIMPLE control back.
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-select-trigger', function( e ) {
		e.stopPropagation();

		const $trigger = $( this );
		const $dropdown = $trigger.closest( '.language-select-dropdown' );
		const $togglesControl = $trigger.closest( '.setting-control.language-select-toggles' );

		// Close its dropdown (for safety).
		$trigger.attr( 'aria-expanded', 'false' );
		$dropdown.find( '.language-select-options' ).removeClass( 'open' );

		// Hide the toggles control.
		$togglesControl.css( 'display', 'none' );

		// Show the corresponding SIMPLE control (prefer sibling).
		let $simpleControl = $togglesControl.siblings( '.setting-control.language-select-simple' ).first();

		// Fallback: if no sibling found, use first global simple control.
		if ( ! $simpleControl.length ) {
			$simpleControl = $( '.setting-control.language-select-simple' ).first();
		}

		if ( $simpleControl.length ) {
			$simpleControl.css( 'display', 'block' );

			// Ensure we re-trigger the SIMPLE dropdown transition on `.open`.
			const $simpleDropdown = $simpleControl.find( '.language-select-dropdown' ).first();
			const $simpleTrigger = $simpleDropdown.find( '.language-select-trigger' );
			const $simpleOptions = $simpleDropdown.find( '.language-select-options' );

			// Set to "closed" state first so the transition can replay.
			$simpleTrigger.attr( 'aria-expanded', 'false' );
			$simpleOptions.removeClass( 'open' );

			const openSimpleWithAnimation = function() {
				$simpleTrigger.attr( 'aria-expanded', 'true' );
				$simpleOptions.addClass( 'open' );
			};

			if ( window.requestAnimationFrame ) {
				window.requestAnimationFrame( openSimpleWithAnimation );
			} else {
				setTimeout( openSimpleWithAnimation, 0 );
			}
		}
	} );

	// Step 2: Handle option selection for toggles
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-select-option', function( e ) {
		// Don't trigger if clicking on toggle switch
		if ( $( e.target ).closest( '.box-swich, .switch, input[type="checkbox"]' ).length ) {
			return;
		}

		e.stopPropagation();
		const $option = $( this );

		// If this option is marked as selected (default language),
		// do not change the checkbox state when clicking the option row.
		if ( $option.hasClass( 'selected' ) ) {
			return;
		}

		// Find checkbox in this option
		const $checkbox = $option.find( 'input[type="checkbox"]' );

		// Toggle checkbox checked state
		if ( $checkbox.length ) {
			const isChecked = $checkbox.is( ':checked' );
			$checkbox.prop( 'checked', ! isChecked );
			// Trigger change event on checkbox to ensure form submission works
			$checkbox.trigger( 'change' );
		}
	} );

	// Step 3: Close dropdown when clicking outside (for toggles)
	$( document ).on( 'click', function( e ) {
		if ( ! $( e.target ).closest( '.setting-control.language-select-toggles .language-select-dropdown' ).length ) {
			$( '.setting-control.language-select-toggles .language-select-dropdown' ).each( function() {
				$( this ).find( '.language-select-trigger' ).attr( 'aria-expanded', 'false' );
				$( this ).find( '.language-select-options' ).removeClass( 'open' );
			} );
		}
	} );

	// Step 4: Close toggles dropdown when clicking on the labels wrapper (title + "Display Options").
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-select-labels-wrapper', function( e ) {
		e.stopPropagation();

		const $dropdown = $( this ).closest( '.language-select-dropdown' );
		const $trigger = $dropdown.find( '.language-select-trigger' );
		const $options = $dropdown.find( '.language-select-options' );

		$trigger.attr( 'aria-expanded', 'false' );
		$options.removeClass( 'open' );
	} );

	/**
	 * ============================================================================
	 * DISPLAY OPTIONS MODE SWITCH - Toggle switch visibility
	 * ============================================================================
	 * Show/hide toggle switches when .language-select-label-display-options is clicked
	 * Only works for language-select-toggles control (which has .box-swich elements)
	 */

	// Step 1: Toggle switch mode when .language-select-label-display-options is clicked (for toggles only)
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-select-label-display-options', function( e ) {
		e.stopPropagation();
		const $label = $( this );
		const $dropdown = $label.closest( '.language-select-dropdown' );
		const $options = $dropdown.find( '.language-select-options' );
		const $switches = $options.find( '.box-swich' );

		// Check if switches exist (only in language-select-toggles control)
		if ( $switches.length === 0 ) {
			return;
		}

		// Toggle active class on label
		$label.toggleClass( 'active' );

		// Toggle visibility of all switches in this dropdown
		if ( $label.hasClass( 'active' ) ) {
			$switches.addClass( 'show' );
		} else {
			$switches.removeClass( 'show' );
		}

		// If the dropdown is currently closed (no `.open` class),
		// ensure it is opened again with the `.open` transition.
		if ( ! $options.hasClass( 'open' ) ) {
			const $trigger = $dropdown.find( '.language-select-trigger' );

			// First, reset to the "closed" state.
			$trigger.attr( 'aria-expanded', 'false' );
			$options.removeClass( 'open' );

			const openTogglesWithAnimation = function() {
				$trigger.attr( 'aria-expanded', 'true' );
				$options.addClass( 'open' );
			};

			if ( window.requestAnimationFrame ) {
				window.requestAnimationFrame( openTogglesWithAnimation );
			} else {
				setTimeout( openTogglesWithAnimation, 0 );
			}
		}
	} );

	/**
	 * ============================================================================
	 * LANGUAGE SELECT ACTIONS - Disable All / Enable All
	 * ============================================================================
	 * Handle Disable All and Enable All button clicks
	 */

	// Step 1: Handle Disable All button click
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-disable-all', function( e ) {
		e.stopPropagation();
		const $button = $( this );
		const $dropdown = $button.closest( '.language-select-dropdown' );
		const $input = $dropdown.find( '.language-select-input' );
		const $options = $dropdown.find( '.language-select-options' );

		// Get default language value from input
		const defaultValue = $input.val() || $input.attr( 'data-default' ) || '';

		// Uncheck all checkboxes except the default language
		$options.find( '.language-select-option' ).each( function() {
			const $option = $( this );
			const $checkbox = $option.find( 'input[type="checkbox"]' );
			const optionValue = $option.data( 'value' );

			// Skip if this is the default language
			if ( optionValue === defaultValue ) {
				return;
			}

			// Uncheck checkbox
			if ( $checkbox.length ) {
				$checkbox.prop( 'checked', false );
				$checkbox.trigger( 'change' );
			}
		} );
	} );

	// Step 2: Handle Enable All button click
	$( document ).on( 'click', '.setting-control.language-select-toggles .language-enable-all', function( e ) {
		e.stopPropagation();
		const $button = $( this );
		const $dropdown = $button.closest( '.language-select-dropdown' );
		const $options = $dropdown.find( '.language-select-options' );

		// Check all checkboxes
		$options.find( '.language-select-option input[type="checkbox"]' ).each( function() {
			const $checkbox = $( this );
			$checkbox.prop( 'checked', true );
			$checkbox.trigger( 'change' );
		} );
	} );

	/**
	 * ============================================================================
	 * ACCESSIBILITY STATUS - SIMPLE LANGUAGE DROPDOWN
	 * ============================================================================
	 * Handles the simple language dropdown used in the Accessibility Status form.
	 * Markup example:
	 * .accessibility-status-container
	 *   .language-select-dropdown
	 *     button.language-select-trigger
	 *     ul.language-select-options > li.language-select-option[data-value]
	 *     input.language-select-input#select_language (hidden)
	 */
	function initAccessibilityStatusLanguageDropdown() {
		// Ensure the container exists before binding behaviour.
		if ( ! $( '.accessibility-status-container .language-select-dropdown' ).length ) {
			return;
		}

		// Toggle dropdown when trigger is clicked.
		$( document ).on( 'click', '.accessibility-status-container .language-select-trigger', function( e ) {
			e.stopPropagation();

			const $trigger = $( this );
			const $dropdown = $trigger.closest( '.language-select-dropdown' );
			const $options = $dropdown.find( '.language-select-options' );
			const isOpen = $options.hasClass( 'open' );

			// Close any other accessibility-status language dropdowns.
			$( '.accessibility-status-container .language-select-dropdown' ).each( function() {
				const $dd = $( this );
				$dd.find( '.language-select-trigger' ).attr( 'aria-expanded', 'false' );
				$dd.find( '.language-select-options' ).removeClass( 'open' );
			} );

			// Toggle current dropdown.
			if ( isOpen ) {
				$trigger.attr( 'aria-expanded', 'false' );
				$options.removeClass( 'open' );
			} else {
				$trigger.attr( 'aria-expanded', 'true' );
				$options.addClass( 'open' );
			}
		} );

		// Handle option selection.
		$( document ).on( 'click', '.accessibility-status-container .language-select-option', function( e ) {
			e.stopPropagation();

			const $option = $( this );
			const value = $option.data( 'value' );
			const label = $option.find( '.language-select-option-label' ).text();
			const flagSrc = $option.find( '.language-select-option-flag img' ).attr( 'src' );

			const $dropdown = $option.closest( '.language-select-dropdown' );
			const $input = $dropdown.find( '.language-select-input' );
			const $trigger = $dropdown.find( '.language-select-trigger' );
			const $selected = $trigger.find( '.language-select-selected' );
			const $options = $dropdown.find( '.language-select-options' );

			// Update hidden input value and trigger form validation.
			$input.val( value ).trigger( 'input change' );

			// Update selected label and flag in trigger.
			$selected.find( '.language-select-label' ).text( label );
			if ( flagSrc ) {
				$selected.find( '.language-select-flag img' ).attr( 'src', flagSrc );
			}

			// Update selected state and checkmarks.
			$dropdown.find( '.language-select-option' ).each( function() {
				const $opt = $( this );
				const isSelected = $opt.is( $option );

				$opt.toggleClass( 'selected', isSelected );
				$opt.find( '.language-select-checkmark' ).toggle( isSelected );
			} );

			// Close dropdown.
			$trigger.attr( 'aria-expanded', 'false' );
			$options.removeClass( 'open' );
		} );

		// Close dropdown when clicking outside.
		$( document ).on( 'click', function( e ) {
			if ( ! $( e.target ).closest( '.accessibility-status-container .language-select-dropdown' ).length ) {
				$( '.accessibility-status-container .language-select-dropdown' ).each( function() {
					const $dd = $( this );
					$dd.find( '.language-select-trigger' ).attr( 'aria-expanded', 'false' );
					$dd.find( '.language-select-options' ).removeClass( 'open' );
				} );
			}
		} );
	}

	// Initialize Accessibility Status language dropdown.
	initAccessibilityStatusLanguageDropdown();

	/**
	 * ============================================================================
	 * SCROLL TO LANGUAGE ROW ON #change-language HASH
	 * ============================================================================
	 * Scroll smoothly to the language settings row when URL contains #change-language
	 */
	function scrollToBottomOnLanguageChange() {
		// Check if URL contains #change-language hash
		if ( window.location.hash === '#change-language' ) {
			// Try to scroll to the specific language settings row
			const $targetRow = $( 'tr.language' );

			if ( $targetRow.length ) {
				const offsetTop = $targetRow.offset().top || 0;

				// Scroll to the language row (a bit above it for nicer view)
				$( 'html, body' ).animate(
					{
						scrollTop: Math.max( offsetTop - 120, 0 ),
					},
					800, // Animation duration in milliseconds
					'swing' // Easing function
				);
			}
		}
	}

	// Execute scroll on page load
	$( document ).ready( function() {
		scrollToBottomOnLanguageChange();
	} );

	// Also execute when hash changes (in case user navigates with hash)
	$( window ).on( 'hashchange', function() {
		scrollToBottomOnLanguageChange();
	} );

	/**
	 * ============================================================================
	 * HIDE RANK MATH SEO NOTICES
	 * ============================================================================
	 * Hide Rank Math notices using JavaScript as additional fallback.
	 */
	// Function to hide Rank Math notices
	function hideRankMathNotices() {
		const notices = document.querySelectorAll( '.notice' );
		notices.forEach( function( notice ) {
			notice.remove();
		} );
	}

	// Run immediately and after DOM is ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', hideRankMathNotices );
	} else {
		hideRankMathNotices();
	}

	// Also run after a short delay to catch dynamically added notices
	setTimeout( hideRankMathNotices, 500 );
	setTimeout( hideRankMathNotices, 1000 );

	/**
	 * ============================================================================
	 * ALTPILOT ONE-CLICK INSTALL (ALT TEXT SCREEN)
	 * ============================================================================
	 * Hands the install over to the updater that ships with WordPress so the
	 * admin never leaves the Alt Text page, then turns the button into the
	 * activation link WordPress hands back.
	 *
	 * The button already links to update.php, so everything here is an upgrade
	 * on top of a working link: with no JavaScript, or on a WordPress where the
	 * updater script is unavailable, clicking it still installs AltPilot through
	 * the regular WordPress installer screen.
	 */
	function initAltPilotInstall() {
		const $button = $( '[data-onetap-altpilot-install]' );

		if ( ! $button.length || ! window.wp || ! wp.updates || ! wp.updates.installPlugin ) {
			return;
		}

		const $feedback = $button.closest( '.myrow' ).find( '.button-alt-pilot-feedback' );
		const installLabel = $button.text();

		$button.on( 'click', function( event ) {
			// A plain click would open update.php; the install runs here instead.
			event.preventDefault();

			if ( $button.hasClass( 'is-busy' ) ) {
				return;
			}

			$button
				.addClass( 'is-busy' )
				.attr( 'aria-disabled', 'true' )
				.text( $button.data( 'busy-label' ) );
			$feedback.text( '' );

			wp.updates.installPlugin( {
				slug: $button.data( 'slug' ),

				success( response ) {
					// WordPress returns the activation link, nonce included, so
					// the customer only has to click once more.
					$button
						.removeClass( 'is-busy' )
						.removeAttr( 'aria-disabled' )
						.removeAttr( 'data-onetap-altpilot-install' )
						.text( $button.data( 'done-label' ) )
						.off( 'click' );

					if ( response && response.activateUrl ) {
						$button.attr( 'href', response.activateUrl );
					}
				},

				error( response ) {
					// Written to our own element rather than a .notice, which the
					// notice sweeper above would delete.
					$button
						.removeClass( 'is-busy' )
						.removeAttr( 'aria-disabled' )
						.text( installLabel );

					$feedback.text(
						( response && response.errorMessage ) ||
							adminLocalize.i18n.altPilotInstallFailed
					);
				},
			} );
		} );
	}

	$( initAltPilotInstall );
}( jQuery ) );
