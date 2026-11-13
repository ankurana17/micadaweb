/* eslint no-undef: "off", no-alert: "off" */
( function( $ ) {
	// Flag to prevent multiple executions
	let isProcessing = false;

	/**
	 * Handle save changes button click in header
	 * When the save changes button in the header is clicked,
	 * it triggers the actual submit button in the corresponding page form
	 */
	$( '.box-save-changes .save-changes, .box-save-changes .fake-save-changes' ).on( 'click', function( e ) {
		e.preventDefault();

		// Prevent multiple executions
		if ( isProcessing ) {
			return;
		}

		const $button = $( this );

		// Set processing flag to true
		isProcessing = true;

		// Check if button has fake-save-changes class, if yes, wait 2.7 seconds before proceeding
		if ( $button.hasClass( 'fake-save-changes' ) ) {
			setTimeout( function() {
				executeSaveChanges();
			}, 2700 );
		} else {
			executeSaveChanges();
		}
	} );

	/**
	 * Execute save changes functionality
	 */
	function executeSaveChanges() {
		// Save language toggles first
		saveLanguageToggles();

		// Get current page parameter from URL
		const urlParams = new URLSearchParams( window.location.search );
		let currentPageId = urlParams.get( 'page' );

		// Convert hyphens to underscores for CSS ID selector compatibility
		currentPageId = currentPageId.replace( /-/g, '_' );

		// Get the language select dropdown input and update localStorage
		const $languageInput = $( '.setting-control.language-select .language-select-input' );
		const languageValue = $languageInput.val();

		// Only update localStorage if dropdown has a value
		if ( languageValue && languageValue.trim() !== '' ) {
			try {
				// Get localStorage data
				const localStorageKey = 'onetap-accessibility-free';
				const storedData = localStorage.getItem( localStorageKey );

				if ( storedData ) {
					// Parse JSON data
					const data = JSON.parse( storedData );

					// Update language in information object
					if ( data.information ) {
						data.information.language = languageValue;

						// Save back to localStorage
						localStorage.setItem( localStorageKey, JSON.stringify( data ) );
					}
				}
			} catch ( error ) {
				console.error( 'Error updating language in localStorage:', error );
			}
		}

		// If contains "accessibility_onetap", remove only the "accessibility_" part
		if ( currentPageId.includes( 'accessibility_onetap' ) ) {
			currentPageId = currentPageId.replace( 'accessibility_', '' );
		}

		// Trigger click on the submit button within the current page form
		$( '#' + currentPageId + ' .submit-button .button' ).trigger( 'click' );

		// Reset processing flag after a short delay to allow form submission
		setTimeout( function() {
			isProcessing = false;
		}, 100 );
	}

	/**
	 * Save language toggles via AJAX
	 * Collects all language toggle states from all dropdowns and saves them
	 */
	function saveLanguageToggles() {
		// Find all language select dropdowns
		const $allDropdowns = $( '.language-select-dropdown' );

		// Collect all language toggle states from all dropdowns
		const allLanguageToggles = {};

		$allDropdowns.each( function() {
			const $dropdown = $( this );
			const $options = $dropdown.find( '.language-select-options' );

			$options.find( '.box-swich input[type="checkbox"]' ).each( function() {
				const $input = $( this );
				const $option = $input.closest( '.language-select-option' );
				const inputId = $input.attr( 'id' );

				// Extract language code from ID: apop_settings[toggle-language-en] -> en
				const match = inputId.match( /toggle-language-([^\]]+)/ );
				if ( match && match[ 1 ] ) {
					const langCode = match[ 1 ];
					// If option has .selected class, always set as 'on', otherwise check checkbox state
					const isSelected = $option.hasClass( 'selected' );
					allLanguageToggles[ langCode ] = ( isSelected || $input.is( ':checked' ) ) ? 'on' : 'off';
				}
			} );
		} );

		// Send AJAX request to save language toggles
		if ( typeof adminLocalize !== 'undefined' && adminLocalize.ajaxUrl && adminLocalize.ajaxNonce ) {
			$.ajax( {
				url: adminLocalize.ajaxUrl,
				type: 'POST',
				data: {
					action: 'save_language_toggles',
					nonce: adminLocalize.ajaxNonce,
					language_toggles: allLanguageToggles,
				},
				success( response ) {
					if ( response.success ) {
						console.log( 'Language toggles saved successfully' );
					} else {
						console.error( 'Failed to save language toggles:', response.error || 'Unknown error' );
					}
				},
				error( xhr, status, error ) {
					console.error( 'AJAX error saving language toggles:', error );
				},
			} );
		}
	}

	// Handle radio image selection - remove checked class from all labels in the same group and add to clicked one
	$( '.setting-control.radio-image .box .label' ).click( function() {
		$( this ).closest( '.setting-control' ).find( '.label' ).removeClass( 'checked' );
		$( this ).addClass( 'checked' );
	} );

	// Handle radio text selection - remove checked class from all labels in the same group and add to clicked one
	$( '.setting-control.radio-text .box .label' ).click( function() {
		$( this ).closest( '.setting-control' ).find( '.label' ).removeClass( 'checked' );
		$( this ).addClass( 'checked' );
	} );

	// Initialize WordPress color picker for all color picker fields
	$( '.color-picker-field' ).wpColorPicker( {
		change( event, ui ) {
			const color = ui.color.toString();
			const $input = $( this );
			const $boxes = $input.closest( '.boxes' );

			// Update background color of .box1
			$boxes.find( '.box1' ).css( '--outline-color', color );

			// Keep visible input value in sync (what user sees)
			$input.val( color ).attr( 'value', color );

			// Also sync the text preview value
			$boxes.find( '.color-result' ).text( color );

			// TODO: This code will be removed in future updates - preview functionality will be handled differently
			$( '.setting-control.color .boxes .box1 .wp-picker-container button.wp-color-result' ).css( 'outline-color', color );

			// TODO: This code will be removed in future updates - preview functionality will be handled differently
			$( '.sidebar-preview .preview-viewport button img' ).css( {
				'background-color': color,
			} );

			// TODO: This code will be removed in future updates - preview functionality will be handled differently
			$( '.setting-control.radio-image .boxes .box label img' ).css( {
				'background-color': color,
			} );

			// TODO: This code will be removed in future updates - preview functionality will be handled differently
			$( '.settings-group.border .setting-control.radio-image .boxes .box1 label img' ).css( {
				border: 'solid 2px #fff',
				'box-shadow': '0 0 0 4px ' + color,
			} );

			// TODO: This code will be removed in future updates - preview functionality will be handled differently
			if ( 'design-border1' === $( '.settings-group.border .boxes .box input[type="radio"]:checked' ).val() ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					border: 'solid 2px #fff',
					'box-shadow': '0 0 0 4px ' + color,
				} );

				$( '.settings-group.border .setting-control.radio-image .boxes .box1 label img' ).css( {
					// border: 'solid 2px #fff',
					'box-shadow': '0 0 0 4px ' + color,
				} );
			} else if ( 'design-border2' === $( '.settings-group.border .boxes .box input[type="radio"]:checked' ).val() ) {
				$( '.sidebar-preview .preview-viewport button img' ).css( {
					border: 'none',
					'box-shadow': 'none',
				} );

				$( '.setting-control.radio-image .boxes .box2 label img' ).css( {
					// border: 'solid 2px transparent',
					'box-shadow': '0 0 0 4px transparent',
				} );
			}
		},
	} );

	// Ensure the color picker result button uses onetap-button class on initial load
	$( '.color-picker-field' ).each( function() {
		const $boxes = $( this ).closest( '.boxes' );
		const $clearButton = $boxes.find( '.wp-picker-clear' );

		$boxes.find( '.wp-picker-container button.wp-color-result' )
			.removeClass( 'button' )
			.addClass( 'onetap-button' );

		if ( $clearButton.length ) {
			// Store the default Clear label for later restoration
			if ( ! $clearButton.data( 'default-label' ) ) {
				$clearButton.data( 'default-label', $clearButton.val() );
			}

			// Remove default wpColorPicker clear handler and replace with our own
			$clearButton.off( 'click.wpcolorpicker' ).off( 'click' ).on( 'click', function( event ) {
				event.preventDefault();

				const $btn = $( this );
				const $localBoxes = $btn.closest( '.boxes' );
				const $input = $localBoxes.find( '.color-picker-field' );
				const $box1 = $localBoxes.find( '.box1' );
				const defaultColor = $box1.data( 'default-color' ) || '';
				const clearLabel = $btn.data( 'default-label' ) || $btn.val() || 'Clear';

				// Reset to default color (or empty string if no default)
				$input.val( defaultColor ).attr( 'value', defaultColor ).trigger( 'change' );

				// Sync UI pieces explicitly
				$box1.css( '--outline-color', defaultColor );
				$localBoxes.find( '.box1 .wp-color-result' ).css( {
					'outline-color': defaultColor,
					'background-color': defaultColor || '',
				} );
				$localBoxes.find( '.color-result' ).text( defaultColor );

				// Ensure the Clear button text remains visible
				$btn.val( clearLabel );
			} );
		}
	} );

	// Open color picker when clicking on color result display
	$( document ).on( 'click', '.color-result', function() {
		$( this ).closest( '.boxes' ).find( '.color-picker-field' ).wpColorPicker( 'open' );
	} );

	// Handle color selection from predefined color list
	$( document ).on( 'click', '.boxes .box3 li', function() {
		const color = $( this ).data( 'color' );
		const $boxes = $( this ).closest( '.boxes' );

		// Set the selected color to the color picker field
		$boxes.find( '.color-picker-field' ).wpColorPicker( 'color', color );

		// Update the color result display text
		$boxes.find( '.color-result' ).text( color );

		// Update the outline color
		$boxes.find( '.box1 .wp-color-result' ).css( 'outline-color', color );
	} );

	/**
	 * Handle device button clicks for device-specific settings visibility
	 * This function manages the switching between desktop, tablet, and mobile device controls
	 * by updating button states and showing/hiding corresponding settings groups
	 */
	$( document ).on( 'click', '.devices-tabs button', function() {
		const $clickedButton = $( this );
		const selectedDevice = $clickedButton.attr( 'data-device-type' );
		const $allDeviceButtons = $( '.devices-tabs button' );

		// Validate device value
		if ( ! selectedDevice || ! [ 'desktop', 'tablet', 'mobile' ].includes( selectedDevice ) ) {
			console.warn( 'Invalid device type:', selectedDevice );
			return;
		}

		// Reset all device buttons to inactive state
		$allDeviceButtons.attr( 'aria-pressed', false ).removeClass( 'active' );

		// Set active state for the clicked button
		$clickedButton.attr( 'aria-pressed', true ).addClass( 'active' );

		// Remove active class from all preview-viewport elements
		$( '.preview-viewport' ).removeClass( 'active' );

		// Add active class to the corresponding preview element based on device type
		$( '.preview-' + selectedDevice ).addClass( 'active' );
		// Add active class to the corresponding viewport element based on device type
		$( '.viewport-' + selectedDevice ).addClass( 'active' );
	} );

	/**
	 * Inisialisasi semua slider-number pair yang ada di dalam .boxes
	 * Menghindari konflik antar elemen dengan melakukan binding berdasarkan konteks masing-masing .box
	 */
	function initNumberSliders() {
		$( '.setting-control.number-slider' ).each( function() {
			const $box = $( this );
			const $range = $box.find( 'input[type="range"]' );
			const $number = $box.find( 'input[type="number"]' );

			function updateSliderBackground( val, isHover = false ) {
				const min = parseFloat( $range.attr( 'min' ) );
				const max = parseFloat( $range.attr( 'max' ) );
				const value = parseFloat( val );
				const percentage = ( ( value - min ) / ( max - min ) ) * 100;

				// Warna default & hover
				const inactiveColor = isHover ? '#d5d7da' : '#E9EAEB';

				$range.css(
					'background',
					`linear-gradient(to right, #0048FE ${ percentage }%, ${ inactiveColor } ${ percentage }%)`
				);
			}

			// Initialize background on load
			updateSliderBackground( $range.val() );

			// Sync: range -> number
			$range.on( 'input', function() {
				const val = $( this ).val();
				$number.val( val );
				updateSliderBackground( val );
			} );

			// Sync: number -> range
			$number.on( 'input', function() {
				let val = parseFloat( $( this ).val() );
				const min = parseFloat( $range.attr( 'min' ) );
				const max = parseFloat( $range.attr( 'max' ) );

				val = Math.min( Math.max( val, min ), max );
				$range.val( val );
				updateSliderBackground( val );
			} );

			// Hover effect (range or number input)
			$range.add( $number ).hover(
				function() {
					updateSliderBackground( $range.val(), true ); // on hover
				},
				function() {
					updateSliderBackground( $range.val(), false ); // out hover
				}
			);
		} );
	}

	// Initialize number slider
	initNumberSliders();

	/**
	 * Handle copy button click for text copy functionality
	 * When the copy button is clicked, it copies the text from data attribute or adjacent .copy-text element
	 */
	$( document ).on( 'click', '.copy-button', function() {
		const $copyButton = $( this );

		// Get text to copy from data attribute first, then fallback to adjacent element
		let textToCopy = $copyButton.data( 'copy-text' );

		if ( ! textToCopy ) {
			const $copyText = $copyButton.prev( '.copy-text' );
			if ( $copyText.length ) {
				textToCopy = $copyText.text().trim();
			}
		}

		if ( textToCopy ) {
			// Use modern clipboard API if available
			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( textToCopy ).then( function() {
					// Show success feedback
					showCopyFeedback( $copyButton, true );
				} ).catch( function( err ) {
					console.error( 'Failed to copy text: ', err );
					// Fallback to old method
					fallbackCopyText( textToCopy, $copyButton );
				} );
			} else {
				// Fallback for older browsers or non-secure contexts
				fallbackCopyText( textToCopy, $copyButton );
			}
		}
	} );

	/**
	 * Fallback method for copying text using document.execCommand
	 * Used when modern clipboard API is not available
	 *
	 * @param {string} text    - The text to copy to clipboard
	 * @param {jQuery} $button - The copy button element
	 */
	function fallbackCopyText( text, $button ) {
		// Create temporary textarea element
		const textarea = document.createElement( 'textarea' );
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild( textarea );

		// Select and copy text
		textarea.select();
		const successful = document.execCommand( 'copy' );

		// Remove temporary element
		document.body.removeChild( textarea );

		// Show feedback
		showCopyFeedback( $button, successful );
	}

	/**
	 * Show visual feedback for copy operation
	 *
	 * @param {jQuery}  $button - The copy button element
	 * @param {boolean} success - Whether the copy operation was successful
	 */
	function showCopyFeedback( $button, success ) {
		const originalHTML = $button.html();

		if ( success ) {
			// Change button text to "Copied!" temporarily
			$button.text( 'Copied!' ).addClass( 'copied' );

			// Reset after 2 seconds
			setTimeout( function() {
				$button.html( originalHTML ).removeClass( 'copied' );
			}, 2000 );
		} else {
			// Show error feedback
			$button.text( 'Failed!' ).addClass( 'error' );

			// Reset after 2 seconds
			setTimeout( function() {
				$button.html( originalHTML ).removeClass( 'error' );
			}, 2000 );
		}
	}

	$( '.setting-manager-meida-browse' ).on( 'click', function( event ) {
		event.preventDefault();

		const self = $( this );

		// Create the media frame.
		const fileFrame = wp.media.frames.fileFrame = wp.media( {
			title: self.data( 'uploader_title' ),
			button: {
				text: self.data( 'uploader_button_text' ),
			},
			multiple: false,
		} );

		fileFrame.on( 'select', function() {
			attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			self.prev( '.setting-manager-url' ).val( attachment.url ).change();
		} );

		// Finally, open the modal.
		fileFrame.open();
	} );
}( jQuery ) );
