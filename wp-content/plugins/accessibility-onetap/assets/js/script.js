/* eslint no-undef: "off", no-alert: "off" */
( function( $ ) {
	'use strict';

	// Apply OneTap body classes as early as possible
	function onetapApplyBodyClasses() {
		try {
			if ( ! document || ! document.body ) {
				return;
			}

			'onetap-root onetap-accessibility-plugin onetap-body-class onetap-custom-class onetap-classes'.split( ' ' ).forEach( function( cls ) {
				document.body.classList.add( cls );
			} );

			const moduleClassMapping = {
				'bigger-text': 'onetap_hide_bigger_text',
				'highlight-links': 'onetap_hide_highlight_links',
				'line-height': 'onetap_hide_line_height',
				'readable-font': 'onetap_hide_readable_font',
				cursor: 'onetap_hide_cursor',
				'text-magnifier': 'onetap_hide_text_magnifier',
				'dyslexic-font': 'onetap_hide_dyslexic_font',
				'text-align': 'onetap_hide_text_align',
				'align-center': 'onetap_hide_align_center',
				'letter-spacing': 'onetap_hide_letter_spacing',
				'font-weight': 'onetap_hide_font_weight',
				'dark-contrast': 'onetap_hide_dark_contrast',
				'light-contrast': 'onetap_hide_light_contrast',
				'high-contrast': 'onetap_hide_high_contrast',
				monochrome: 'onetap_hide_monochrome',
				saturation: 'onetap_hide_saturation',
				'reading-line': 'onetap_hide_reading_line',
				'reading-mask': 'onetap_hide_reading_mask',
				'read-page': 'onetap_hide_read_page',
				'keyboard-navigation': 'onetap_hide_keyboard_navigation',
				'hide-images': 'onetap_hide_hide_images',
				'mute-sounds': 'onetap_hide_mute_sounds',
				'highlight-titles': 'onetap_hide_highlight_titles',
				'highlight-all': 'onetap_hide_highlight_all',
				'stop-animations': 'onetap_hide_stop_animations',
				'skip-to-content': 'onetap_hide_skip_content',
			};

			const showModules = ( typeof window !== 'undefined' && window.onetapAjaxObject && window.onetapAjaxObject.showModules ) ? window.onetapAjaxObject.showModules : null;
			if ( showModules ) {
				Object.keys( moduleClassMapping ).forEach( function( key ) {
					const value = showModules[ key ];
					if ( typeof value === 'string' && value.trim() === 'off' ) {
						document.body.classList.add( moduleClassMapping[ key ] );
					}
				} );
			}
		} catch ( e ) {
			console.log( 'OneTap: failed to add body classes via JS.', e );
		}
	}

	if ( document && document.body ) {
		onetapApplyBodyClasses();
	} else {
		document.addEventListener( 'DOMContentLoaded', onetapApplyBodyClasses );
	}

	const onetapToggleClose = $( '.onetap-accessibility-plugin .onetap-close' );
	const onetapToggleOpen = $( '.onetap-accessibility-plugin .onetap-toggle' );
	const onetapToggleOpenStatement = $( '.onetap-accessibility-plugin .onetap-statement button' );
	const onetapToggleCloseStatement = $( '.accessibility-status-text button' );
	const onetapToggleOpenHideToolbar = $( '.onetap-accessibility-plugin .onetap-hide-toolbar button' );
	const onetapToggleCloseHideToolbar = $( '.close-box-hide-duration' );
	const onetapAccessibility = $( '.onetap-accessibility-plugin .onetap-accessibility' );
	const onetapToggleLanguages = $( '.onetap-accessibility-plugin .onetap-languages' );
	const onetapLanguageList = $( '.onetap-accessibility-settings .onetap-list-of-languages' );
	const onetapSkipElements = '.onetap-plugin-onetap, .onetap-plugin-onetap *, .onetap-toggle, .onetap-toggle *, #wpadminbar, #wpadminbar *, rs-fullwidth-wrap, rs-fullwidth-wrap *, rs-module-wrap, rs-module-wrap *, sr7-module, sr7-module *, .onetap-markup-reading-mask';
	// Skip-to targets: same keys as <option value="…">; selectors match onetapSkipToLandmark / change handler.
	const onetapSkipLandmarkSelectors = {
		main: 'main, [role="main"]',
		navigation: 'nav:not(.onetap-plugin-onetap):not(.post-navigation)',
		footer: 'footer:not(.onetap-footer-bottom)',
	};
	// Toolbar languages written right to left. Read in both places that flip the
	// `onetap-rtl` class, so a new RTL language only has to be listed once.
	const onetapRtlLanguages = [ 'ar', 'ir', 'il', 'ur' ];
	// Standalone `visibility` only (after `^` or `;`) — not `content-visibility`, `backface-visibility`, or `transition: visibility …`.
	const onetapCssVisibilityDecl = /(^|;)\s*visibility\s*:\s*[^;]+;?/;
	const onetapCssVisibilityValue = /(?:^|;)\s*visibility\s*:\s*([^;]+);?/;

	/**
	 * Set or remove the inert attribute on the accessibility nav so that when the panel
	 * is closed it is skipped in keyboard tab order; when open it can be navigated.
	 *
	 * @param {boolean} isOpen - True when panel is open (remove inert), false when closed (set inert).
	 */
	function onetapSetAccessibilityNavInert( isOpen ) {
		if ( ! onetapAccessibility.length ) {
			return;
		}
		const nav = onetapAccessibility[ 0 ];
		if ( isOpen ) {
			nav.removeAttribute( 'inert' );
		} else {
			nav.setAttribute( 'inert', '' );
		}
	}

	/**
	 * Load flag images for the language list on demand by copying
	 * the URL from `data-src` into `src`. This prevents the browser
	 * from requesting all language flag images on initial page load.
	 */
	function onetapLoadLanguageImages() {
		if ( ! onetapLanguageList.length ) {
			return;
		}

		onetapLanguageList.find( 'img[data-src]' ).each( function() {
			const $img = $( this );
			// Only set src if it hasn't been set before.
			if ( ! $img.attr( 'src' ) ) {
				$img.attr( 'src', $img.attr( 'data-src' ) );
			}
		} );
	}

	/**
	 * Skip to the first matching page landmark (smooth scroll, then optional focus).
	 *
	 * @param {string} selector - CSS selector for the first matching element.
	 * @return {boolean} True if a target was found.
	 */
	function onetapSkipToLandmark( selector ) {
		const target = document.querySelector( selector );
		if ( ! target || ! target.isConnected ) {
			return false;
		}

		// Visible smooth scroll toward the landmark; instant when user prefers reduced motion.
		const reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		const scrollOpts = reduceMotion
			? { block: 'start', inline: 'nearest' }
			: { block: 'start', inline: 'nearest', behavior: 'smooth' };

		target.scrollIntoView( scrollOpts );

		const focusDelay = reduceMotion ? 0 : 450;
		window.setTimeout( function() {
			try {
				target.focus( { preventScroll: true } );
			} catch ( err ) {
				target.focus();
			}
		}, focusDelay );

		return true;
	}

	/**
	 * Whether the document has at least one element matching the skip landmark selector.
	 *
	 * @param {string} selector - CSS selector (same rules as onetapSkipToLandmark).
	 * @return {boolean} True when at least one connected element matches.
	 */
	function onetapSkipLandmarkExists( selector ) {
		const el = document.querySelector( selector );
		return Boolean( el && el.isConnected );
	}

	/**
	 * Remove skip-to options when no matching landmark exists on the page.
	 * Hides the whole skip block when only the empty "Choose…" option remains.
	 */
	function onetapSkipContentPruneOptions() {
		$( '.onetap-skip-select' ).each( function() {
			const $select = $( this );
			Object.keys( onetapSkipLandmarkSelectors ).forEach( function( key ) {
				const sel = onetapSkipLandmarkSelectors[ key ];
				if ( ! onetapSkipLandmarkExists( sel ) ) {
					$select.find( 'option[value="' + key + '"]' ).remove();
				}
			} );
			if ( $select.find( 'option' ).length <= 1 ) {
				$select.closest( '.onetap-skip-content' ).hide();
			}
		} );
	}

	// Skip to Content: first main / site nav / site footer only; no action if missing.
	// Delegate on class so behavior survives duplicate ids; exclude plugin chrome from footer/nav.
	$( document ).on( 'change', '.onetap-skip-select', function() {
		const value = $( this ).val();
		if ( ! value ) {
			return;
		}

		const selector = onetapSkipLandmarkSelectors[ value ];
		if ( ! selector ) {
			return;
		}

		onetapSkipToLandmark( selector );
	} );

	// Ensure body has required classes even if theme doesn't output body_class
	$( function() {
		onetapSkipContentPruneOptions();

		// When panel is closed, nav is inert (skipped in tab order); when open, navigable.
		onetapSetAccessibilityNavInert( onetapAccessibility.hasClass( 'onetap-toggle-open' ) );

		// Open Accessibility (delegated to handle timing/dynamic DOM)
		$( document ).on( 'click', '.onetap-accessibility-plugin .onetap-toggle', function( event ) {
			event.stopPropagation();
			$( '.onetap-accessibility-plugin .onetap-accessibility' ).removeClass( 'onetap-toggle-close' ).addClass( 'onetap-toggle-open' );
			onetapSetAccessibilityNavInert( true );
			$( '.onetap-accessibility-plugin .onetap-close' ).show( 100 );
			$( '.onetap-accessibility-plugin .onetap-languages' ).focus();
		} );
	} );

	$( 'a[href="#onetap-toolbar"], #onetap-toolbar' ).on( 'click', function( event ) {
		event.stopPropagation();
		onetapAccessibility.removeClass( 'onetap-toggle-close' ).addClass( 'onetap-toggle-open' );
		onetapSetAccessibilityNavInert( true );
		onetapToggleClose.show( 100 );
		onetapToggleLanguages.focus();
	} );

	$( document ).on( 'keydown', function( e ) {
		// Detect if the platform is macOS
		const isMac = navigator.platform.toUpperCase().indexOf( 'MAC' ) >= 0;
		const isShortcut = (
			( isMac && e.metaKey && e.key === '.' ) ||
			( ! isMac && e.ctrlKey && e.key === '.' ) ||
			( e.altKey && e.key === '.' )
		);

		// Trigger the accessibility panel if the correct shortcut is pressed
		if ( isShortcut ) {
			e.stopPropagation(); // Stop the event from bubbling up
			e.preventDefault(); // Prevent default behavior (optional)

			// Open the accessibility panel
			onetapAccessibility.removeClass( 'onetap-toggle-close' ).addClass( 'onetap-toggle-open' );
			onetapSetAccessibilityNavInert( true );
			onetapToggleClose.show( 100 ); // Show the close button
			onetapToggleLanguages.focus(); // Focus on the language toggle
		}
	} );

	// Close Accessibility.
	onetapToggleClose.click( function( event ) {
		event.stopPropagation();
		onetapAccessibility.removeClass( 'onetap-toggle-open' ).addClass( 'onetap-toggle-close' );
		onetapSetAccessibilityNavInert( false );
		onetapToggleClose.hide( 100 );
		onetapToggleOpen.focus();
	} );

	$( document ).on( 'keydown', function( e ) {
		// Close the accessibility panel with Escape key, but only when the panel
		// is actually open. Otherwise let the event pass through so it doesn't
		// interfere with other scripts or native dialogs (e.g. HTML5 <dialog>).
		if ( e.key === 'Escape' && onetapAccessibility.hasClass( 'onetap-toggle-open' ) ) {
			e.stopPropagation();
			e.preventDefault();

			onetapAccessibility.removeClass( 'onetap-toggle-open' ).addClass( 'onetap-toggle-close' );
			onetapSetAccessibilityNavInert( false );
			onetapToggleClose.hide( 100 );
			onetapToggleOpen.focus();
		}
	} );

	// When the close button of the language popup gains focus
	onetapToggleClose.on( 'focus', function( event ) {
		event.stopPropagation();
		$( this ).attr( 'aria-expanded', false );

		$( this ).removeClass( 'onetap-active' );
		onetapToggleLanguages.removeClass( 'onetap-active' );
		onetapLanguageList.fadeOut( 350 );
	} );

	// Open Statement
	onetapToggleOpenStatement.click( function() {
		$( '.onetap-accessibility' ).removeClass( 'active-hide-toolbar' );
		$( '.toolbar-hide-duration' ).hide();

		$( '.onetap-accessibility' ).addClass( 'active-statement' );
		$( '.accessibility-status-wrapper' ).show();
		$( '.accessibility-status-text button' ).focus();
	} );

	// Close Statement
	onetapToggleCloseStatement.click( function() {
		$( '.onetap-accessibility' ).removeClass( 'active-statement' );
		$( '.accessibility-status-wrapper' ).hide();
		$( '.onetap-statement button' ).focus();
	} );

	// Open Hide Toolbar
	onetapToggleOpenHideToolbar.click( function() {
		$( '.onetap-accessibility' ).removeClass( 'active-statement' );
		$( '.accessibility-status-wrapper' ).hide();

		$( '.onetap-accessibility' ).addClass( 'active-hide-toolbar' );
		$( '.toolbar-hide-duration' ).show();
	} );

	// Close Hide Toolbar
	onetapToggleCloseHideToolbar.click( function() {
		$( '.onetap-accessibility' ).removeClass( 'active-hide-toolbar' );
		$( '.toolbar-hide-duration' ).hide();
		$( '.open-form-hide-toolbar' ).focus();
	} );

	// Active input radio - Hide Toolbar
	$( '.toolbar-hide-duration .box-hide-duration form label' ).on( 'click', function() {
		$( '.toolbar-hide-duration .box-hide-duration form label' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
	} );

	// Prevent auto-close when clicking inside accessibility panel.
	onetapAccessibility.click( function( event ) {
		onetapLanguageList.fadeOut( 350 );
		onetapToggleLanguages.removeClass( 'onetap-active' );
		if ( ! $( event.target ).closest( '.onetap-reset-settings' ).length ) {
			event.stopPropagation();
		}
	} );

	// Toggle list of languages.
	onetapToggleLanguages.click( function( event ) {
		event.stopPropagation();

		// Load flag images only when the dropdown is interacted with.
		onetapLoadLanguageImages();

		// Toggle aria-expanded true/false
		const isExpanded = $( this ).attr( 'aria-expanded' ) === 'true';
		$( this ).attr( 'aria-expanded', ! isExpanded );

		$( this ).toggleClass( 'onetap-active' );
		onetapLanguageList.fadeToggle( 350 );
	} );

	// Auto-close elements when clicking outside
	$( document ).click( function( event ) {
		const isClickInsideAccessibility = $( event.target ).closest( '.onetap-accessibility' ).length > 0;
		const isClickInsideLanguages = $( event.target ).closest( '.onetap-languages, .onetap-list-of-languages' ).length > 0;

		// If clicking outside the accessibility panel, close accessibility
		if ( ! isClickInsideAccessibility ) {
			onetapAccessibility.removeClass( 'onetap-toggle-open' ).addClass( 'onetap-toggle-close' );
			onetapSetAccessibilityNavInert( false );
			onetapToggleClose.hide( 100 );
		}

		// If clicking outside the language list, close the language list
		if ( ! isClickInsideLanguages ) {
			onetapLanguageList.fadeOut( 350 );
			onetapToggleLanguages.removeClass( 'onetap-active' );
		}
	} );

	// Get the current date
	const onetapToday = new Date();

	// Extract the onetapYear, onetapMonth, and onetapDay
	const onetapYear = onetapToday.getFullYear(); // Get the full onetapYear (e.g., 2024)
	const onetapMonth = String( onetapToday.getMonth() + 1 ).padStart( 2, '0' ); // Get the onetapMonth (0-11) and add 1; pad with 0 if needed
	const onetapDay = String( onetapToday.getDate() ).padStart( 2, '0' ); // Get the onetapDay of the onetapMonth (1-31) and pad with 0 if needed

	// Create a formatted date string for the start date in the format YYYY-MM-DD
	const onetapStartDate = `${ onetapYear }-${ onetapMonth }-${ onetapDay }`;

	// Create a new date object for the end date by adding 2 days to the current date
	const onetapEndDateObject = new Date( onetapToday ); // Create a new Date object based on onetapToday
	onetapEndDateObject.setDate( onetapEndDateObject.getDate() + 2 ); // Add 2 days

	// Extract the year, month, and day for the end date
	const onetapEndYear = onetapEndDateObject.getFullYear();
	const onetapEndMonth = String( onetapEndDateObject.getMonth() + 1 ).padStart( 2, '0' );
	const onetapEndDay = String( onetapEndDateObject.getDate() ).padStart( 2, '0' );

	// Create a formatted date string for the end date
	const onetapEndDate = `${ onetapEndYear }-${ onetapEndMonth }-${ onetapEndDay }`;

	// console.log(onetapStartDate); // Output the start date
	// console.log(onetapEndDate);   // Output the end date

	// Default values for onetapLocalStorage
	const onetapAccessibilityDefault = {
		dynamicFeatureSet: {
			visionImpairedMode: false,
			seizureSafeProfileMode: false,
			adhdFriendlyMode: false,
			blindnessMode: false,
			epilepsySafeMode: false,
		},
		activeBorders: {
			biggerText: 0,
			lineHeight: 0,
		},
		biggerText: false,
		highlightLinks: false,
		lineHeight: false,
		readableFont: false,
		cursor: false,
		textMagnifier: false,
		dyslexicFont: false,
		alignLeft: false,
		alignCenter: false,
		alignRight: false,
		letterSpacing: false,
		fontWeight: false,
		darkContrast: false,
		lightContrast: false,
		highContrast: false,
		monochrome: false,
		saturation: false,
		readingLine: false,
		readingMask: false,
		readPage: false,
		keyboardNavigation: false,
		hideImages: false,
		muteSounds: false,
		highlightTitles: false,
		highlightAll: false,
		stopAnimations: false,
		information: {
			updated: 'onetap-version-23',
			language: onetapAjaxObject.getSettings.language,
			developer: 'Yuky Hendiawan',
			startDate: onetapStartDate,
			endDate: onetapEndDate,
		},
	};

	// If 'onetapLocalStorage' does not exist in localStorage, create it
	const onetapLocalStorage = 'onetap-accessibility-free';
	if ( ! localStorage.getItem( onetapLocalStorage ) ) {
		localStorage.setItem( onetapLocalStorage, JSON.stringify( onetapAccessibilityDefault ) );
	} else {
		// Retrieve the existing data from localStorage
		const accessibilityData = JSON.parse( localStorage.getItem( onetapLocalStorage ) );

		// Check if 'information.updated' exists and whether its value is 'onetap-version-23'
		if ( typeof accessibilityData.information === 'undefined' ||
			typeof accessibilityData.information.updated === 'undefined' ||
			accessibilityData.information.updated !== 'onetap-version-23' ) {
			localStorage.removeItem( onetapLocalStorage );
			localStorage.setItem( onetapLocalStorage, JSON.stringify( onetapAccessibilityDefault ) );
		}
	}

	// Retrieves accessibility data from local storage.
	function getDataAccessibilityData() {
		try {
			const storedData = localStorage.getItem( onetapLocalStorage );
			if ( ! storedData ) {
				return null;
			}
			const accessibilityData = JSON.parse( storedData );
			return accessibilityData;
		} catch ( error ) {
			console.warn( 'Error parsing accessibility data from localStorage:', error );
			return null;
		}
	}

	/**
	 * Updates the 'data-on' and 'data-off' attributes of elements with the class 'label-mode-switch-inner'.
	 * If the attribute value exceeds 3 characters, it truncates it and appends '...'.
	 */
	function updateLabelModeSwitch() {
		$( '.onetap-accessibility .label-mode-switch-inner' ).each( function() {
			const $this = $( this );
			let dataOn = $this.attr( 'data-on' );
			let dataOff = $this.attr( 'data-off' );

			if ( dataOn.length > 3 ) {
				dataOn = dataOn.substring( 0, 3 ) + '.';
			}

			if ( dataOff.length > 3 ) {
				dataOff = dataOff.substring( 0, 3 ) + '.';
			}

			$this.attr( 'data-on', dataOn );
			$this.attr( 'data-off', dataOff );
		} );
	}

	// Updates the country flag based on the selected language.
	updateLanguageFlag();
	function updateLanguageFlag() {
		const data = getDataAccessibilityData();
		if ( ! data || ! data.information || ! data.information.language ) {
			return;
		}

		const currentLanguage = data.information.language;
		const $headerFlags = $( 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-languages .onetap-icon img' );

		if ( ! $headerFlags.length ) {
			return;
		}

		$headerFlags.removeClass( 'onetap-active' );

		$headerFlags.each( function() {
			const $img = $( this );
			if ( $img.attr( 'data-language' ) === currentLanguage ) {
				// Ensure the active language image actually has a src so the flag is visible.
				if ( ! $img.attr( 'src' ) ) {
					const dataSrc = $img.attr( 'data-src' );
					if ( dataSrc ) {
						$img.attr( 'src', dataSrc );
					}
				}
				$img.addClass( 'onetap-active' );
			}
		} );
	}

	// Event handler for language selection
	$( 'nav.onetap-accessibility header.onetap-header-top .onetap-list-of-languages ul li' ).click( function() {
		// Update visual active state: clear from all items, then apply to the clicked one
		const $languageItems = $( 'nav.onetap-accessibility header.onetap-header-top .onetap-list-of-languages ul li' );
		$languageItems.removeClass( 'onetap-active' );
		$( this ).addClass( 'onetap-active' );

		const selectedLanguage = $( this ).attr( 'data-language' ); // Get the selected language from the data attribute
		const languageName = $( this ).text(); // Get the name of the selected language

		// Update header flag: switch active class and ensure src is set for the selected language.
		const $headerFlags = $( 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-languages .onetap-icon img' );
		$headerFlags.removeClass( 'onetap-active' );

		$headerFlags.each( function() {
			const $img = $( this );
			if ( $img.attr( 'data-language' ) === selectedLanguage ) {
				if ( ! $img.attr( 'src' ) ) {
					const dataSrc = $img.attr( 'data-src' );
					if ( dataSrc ) {
						$img.attr( 'src', dataSrc );
					}
				}
				$img.addClass( 'onetap-active' );
			}
		} );

		// Remove active class from the language toggle
		$( onetapToggleLanguages ).removeClass( 'onetap-active' );

		// Update the displayed language name
		$( 'nav.onetap-accessibility header.onetap-header-top .onetap-languages .onetap-text span' ).text( languageName );

		// Update the header content based on the selected language
		onetapUpdateContentBasedOnLanguage( selectedLanguage );

		// Fade out the language settings panel
		$( '.onetap-accessibility-settings header.onetap-header-top .onetap-list-of-languages' ).fadeOut( 350 );

		// Updates the 'data-on' and 'data-off' attributes
		updateLabelModeSwitch();

		const getDataAccessibilityDefault = getDataAccessibilityData();
		getDataAccessibilityDefault.information.language = selectedLanguage;
		localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );

		// The function to apply language translations to feature boxes
		applyLanguageTranslationsToFeatures( selectedLanguage );
		applyLanguageTranslationsToFunctionalFeatures( selectedLanguage );

		if ( onetapRtlLanguages.includes( selectedLanguage ) ) {
			$( 'nav.onetap-plugin-onetap' ).addClass( 'onetap-rtl' );
		} else {
			$( 'nav.onetap-plugin-onetap' ).removeClass( 'onetap-rtl' );
		}
	} );

	// Retrieve accessibility settings from localStorage
	const getDataLocalStorage = getDataAccessibilityData();

	// List of feature modes and their corresponding checkbox IDs
	const featureModes = {
		visionImpairedMode: '#onetap-box-vision-impaired-mode',
		seizureSafeProfileMode: '#onetap-box-seizure-safe-profile',
		adhdFriendlyMode: '#onetap-box-adhd-friendly-mode',
		blindnessMode: '#onetap-box-blindness-mode',
		epilepsySafeMode: '#onetap-box-epilepsy-safe-mode',
	};

	/**
	 * Keeps a mode-preset checkbox's reported state in step with its own
	 * checked property. The checkbox is the switch - the label around it may
	 * not carry a role, so it may not carry the state either.
	 *
	 * @param {Object}  $checkbox - jQuery-wrapped mode preset checkbox.
	 * @param {boolean} isChecked - True when the mode is enabled.
	 */
	function onetapSyncSwitchState( $checkbox, isChecked ) {
		$checkbox.attr( 'aria-checked', isChecked );
	}

	// Function to initialize checkboxes based on saved settings
	function initializeModePresetToggle() {
		$.each( featureModes, function( key, selector ) {
			const checkbox = $( selector );

			// Set the checkbox state based on saved feature settings
			if ( getDataLocalStorage.dynamicFeatureSet[ key ] ) {
				checkbox.prop( 'checked', true ).val( 1 );
				$( selector ).closest( '.onetap-functional-feature' ).addClass( 'onetap-active' );
				onetapSyncSwitchState( $( selector ), true );
			} else {
				onetapSyncSwitchState( $( selector ), false );
			}
		} );
	}

	// Function to handle checkbox state changes
	function handleModePresetToggle() {
		// Remove existing event listeners to prevent duplication
		$( '.onetap-functional-feature input' ).off( 'change' );

		$( '.onetap-functional-feature input' ).on( 'change', function() {
			// Find the feature key corresponding to the checkbox
			const featureKey = Object.keys( featureModes ).find( ( key ) => featureModes[ key ] === '#' + this.id );

			// Get attr by id.
			const getID = $( this ).attr( 'id' );

			if ( featureKey ) {
				const isChecked = $( this ).is( ':checked' );

				// Update the feature settings in localStorage
				const getDataAccessibilityDefault = getDataAccessibilityData();
				Object.assign( getDataAccessibilityDefault.dynamicFeatureSet, {
					visionImpairedMode: false,
					seizureSafeProfileMode: false,
					adhdFriendlyMode: false,
					blindnessMode: false,
					epilepsySafeMode: false,
					[ featureKey ]: isChecked,
				} );

				// If user is checking a mode, uncheck all other modes first
				if ( isChecked ) {
					const checkboxPresetToggle = [
						'#onetap-box-vision-impaired-mode',
						'#onetap-box-seizure-safe-profile',
						'#onetap-box-adhd-friendly-mode',
						'#onetap-box-blindness-mode',
						'#onetap-box-epilepsy-safe-mode',
					];

					checkboxPresetToggle.forEach( ( id ) => {
						const checkbox = document.querySelector( id );
						if ( checkbox && checkbox.id !== getID ) {
							checkbox.checked = false;
							$( id ).attr( 'aria-checked', false );
							$( id ).closest( '.onetap-functional-feature' ).removeClass( 'onetap-active' );
						}
					} );
				}

				// Set the checkbox state based on user action (checked or unchecked)
				$( this ).prop( 'checked', isChecked ).val( isChecked ? 1 : 0 );
				onetapSyncSwitchState( $( this ), isChecked );

				// Manage the onetap-active class on the container
				if ( isChecked ) {
					$( this ).closest( '.onetap-functional-feature' ).addClass( 'onetap-active' );
				} else {
					$( this ).closest( '.onetap-functional-feature' ).removeClass( 'onetap-active' );
				}

				localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );

				// If user is unchecking, disable all modes
				if ( ! isChecked ) {
					onetapVisionImpairedMode( false );
					onetapSeizureSafeProfileMode( false );
					onetapADHDFriendlyMode( false );
					onetapBlindnessMode( false );
					onetapEpilepsySafeMode( false );
				} else {
					// If user is checking a mode, disable all other modes first
					onetapVisionImpairedMode( false );
					onetapSeizureSafeProfileMode( false );
					onetapADHDFriendlyMode( false );
					onetapBlindnessMode( false );
					onetapEpilepsySafeMode( false );

					// Then enable the selected mode
					if ( featureKey === 'visionImpairedMode' && 'onetap-box-vision-impaired-mode' === getID ) {
						onetapVisionImpairedMode( true );
					} else if ( featureKey === 'seizureSafeProfileMode' && 'onetap-box-seizure-safe-profile' === getID ) {
						onetapSeizureSafeProfileMode( true );
					} else if ( featureKey === 'adhdFriendlyMode' && 'onetap-box-adhd-friendly-mode' === getID ) {
						onetapADHDFriendlyMode( true );
					} else if ( featureKey === 'blindnessMode' && 'onetap-box-blindness-mode' === getID ) {
						onetapBlindnessMode( true );
					} else if ( featureKey === 'epilepsySafeMode' && 'onetap-box-epilepsy-safe-mode' === getID ) {
						onetapEpilepsySafeMode( true );
					}
				}
			}
		} );

		// Keyboard support for the mode presets. Focus lands on the checkbox now,
		// which already toggles on Space, so only Enter has to be wired up to keep
		// the switches working the way the rest of the toolbar does.
		const modePresetCheckboxes = 'nav.onetap-accessibility .label-mode-switch input[type="checkbox"]';

		$( modePresetCheckboxes ).off( 'keydown' );
		$( modePresetCheckboxes ).on( 'keydown', function( e ) {
			if ( e.key !== 'Enter' ) {
				return;
			}

			e.preventDefault(); // Prevent form submit.

			this.checked = ! this.checked;
			$( this ).trigger( 'change' );
		} );
	}

	// Listen for any keydown event on the document
	$( document ).on( 'keydown', function( e ) {
		// Check if the user is pressing ALT + F11
		if ( e.altKey && e.key === 'F11' ) {
			e.preventDefault(); // Prevent the default browser action for F11

			// Get the configuration value for keyboard navigation from the global object
			const value = onetapAjaxObject?.showModules?.[ 'keyboard-navigation' ];

			// If the value is a non-empty string and is set to 'on'
			if ( 'string' === typeof value && '' !== value.trim() && 'on' === value ) {
				// Simulate a click on the keyboard navigation feature box
				$( 'nav.onetap-accessibility .onetap-features .onetap-box-feature.onetap-keyboard-navigation' ).trigger( 'click' );
			}
		}

		// Check if the user is pressing ALT + SHIFT + K
		if ( e.altKey && e.shiftKey && e.key.toLowerCase() === 'k' ) {
			e.preventDefault(); // Prevent the default browser action for F11

			// Get the configuration value for keyboard navigation from the global object
			const value = onetapAjaxObject?.showModules?.[ 'keyboard-navigation' ];

			// If the value is a non-empty string and is set to 'on'
			if ( 'string' === typeof value && '' !== value.trim() && 'on' === value ) {
				// Simulate a click on the keyboard navigation feature box
				$( 'nav.onetap-accessibility .onetap-features .onetap-box-feature.onetap-keyboard-navigation' ).trigger( 'click' );
			}
		}
	} );

	// Initialize checkboxes and event listeners when the DOM is ready
	$( document ).ready( function() {
		initializeModePresetToggle(); // Initialize checkboxes based on saved data
		handleModePresetToggle(); // Set up event listener for checkbox changes
	} );

	// Also initialize when the window loads (fallback)
	$( window ).on( 'load', function() {
		initializeModePresetToggle(); // Initialize checkboxes based on saved data
	} );

	// Function to manage Vision Impaired Mode
	function onetapVisionImpairedMode( status ) {
		if ( ! localStorage.getItem( onetapLocalStorage ) ) {
			return;
		}

		const getDataAccessibilityDefault = getDataAccessibilityData();
		const settings = [
			{ key: 'bigger-text', prop: 'biggerText', border: true },
			{ key: 'saturation', prop: 'saturation', border: true },
			{ key: 'readable-font', prop: 'readableFont', border: false },
		];

		settings.forEach( ( { key, prop, border } ) => {
			if ( onetapAjaxObject.showModules[ key ] === 'on' ) {
				getDataAccessibilityDefault[ prop ] = status;
				if ( border ) {
					getDataAccessibilityDefault.activeBorders[ prop ] = status ? 1 : 0;
				}
				localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );
			}
		} );

		location.reload();
	}

	// Function to manage Seizure Safe Profile Mode
	function onetapSeizureSafeProfileMode( status ) {
		if ( ! localStorage.getItem( onetapLocalStorage ) ) {
			return;
		}

		const getDataAccessibilityDefault = getDataAccessibilityData();
		const settings = [
			{ key: 'stop-animations', prop: 'stopAnimations', border: true },
			{ key: 'monochrome', prop: 'monochrome', border: true },
		];

		settings.forEach( ( { key, prop, border } ) => {
			if ( onetapAjaxObject.showModules[ key ] === 'on' ) {
				getDataAccessibilityDefault[ prop ] = status;
				if ( border ) {
					getDataAccessibilityDefault.activeBorders[ prop ] = status ? 2 : 0;
				}
				localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );
			}
		} );

		location.reload();
	}

	// Function to manage ADHD Friendly Mode
	function onetapADHDFriendlyMode( status ) {
		if ( ! localStorage.getItem( onetapLocalStorage ) ) {
			return;
		}

		const getDataAccessibilityDefault = getDataAccessibilityData();
		const settings = [
			{ key: 'stop-animations', prop: 'stopAnimations', border: true },
			{ key: 'saturation', prop: 'saturation', border: true },
			{ key: 'reading-mask', prop: 'readingMask', border: true },
		];

		settings.forEach( ( { key, prop, border } ) => {
			if ( onetapAjaxObject.showModules[ key ] === 'on' ) {
				getDataAccessibilityDefault[ prop ] = status;
				if ( border ) {
					getDataAccessibilityDefault.activeBorders[ prop ] = status ? 1 : 0;
				}
				localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );
			}
		} );

		location.reload();
	}

	// Function to manage Blindness Mode
	function onetapBlindnessMode( status ) {
		if ( ! localStorage.getItem( onetapLocalStorage ) ) {
			return;
		}

		const getDataAccessibilityDefault = getDataAccessibilityData();
		const settings = [
			{ key: 'read-page', prop: 'readPage', border: true },
			{ key: 'brightness', prop: 'brightness', border: true },
			{ key: 'high-contrast', prop: 'highContrast', border: false },
		];

		settings.forEach( ( { key, prop, border } ) => {
			if ( onetapAjaxObject.showModules[ key ] === 'on' ) {
				getDataAccessibilityDefault[ prop ] = status;
				if ( border ) {
					getDataAccessibilityDefault.activeBorders[ prop ] = status ? 3 : 0;
				}
				localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );
			}
		} );

		location.reload();
	}

	// Function to manage Epilepsy Safe Mode
	function onetapEpilepsySafeMode( status ) {
		if ( ! localStorage.getItem( onetapLocalStorage ) ) {
			return;
		}

		const getDataAccessibilityDefault = getDataAccessibilityData();
		const settings = [
			{ key: 'stop-animations', prop: 'stopAnimations', border: true },
			{ key: 'monochrome', prop: 'monochrome', border: true },
			{ key: 'mute-sounds', prop: 'muteSounds', border: true },
		];

		settings.forEach( ( { key, prop, border } ) => {
			if ( onetapAjaxObject.showModules[ key ] === 'on' ) {
				getDataAccessibilityDefault[ prop ] = status;
				if ( border ) {
					getDataAccessibilityDefault.activeBorders[ prop ] = status ? 2 : 0;
				}
				localStorage.setItem( onetapLocalStorage, JSON.stringify( getDataAccessibilityDefault ) );
			}
		} );

		location.reload();
	}

	// Function to update content based on the selected language
	onetapUpdateContentBasedOnLanguage( getDataAccessibilityData().information.language );
	function onetapUpdateContentBasedOnLanguage( language ) {
		// Define a list of valid languages
		const validLanguages = [ 'en', 'de', 'es', 'fr', 'it', 'pl', 'se', 'fi', 'pt', 'ro', 'si', 'sk', 'nl', 'dk', 'gr', 'cz', 'hu', 'lt', 'lv', 'ee', 'hr', 'ie', 'bg', 'no', 'tr', 'id', 'pt-br', 'ja', 'ko', 'zh', 'ar', 'ru', 'hi', 'uk', 'sr', 'gb', 'ir', 'il', 'mk', 'th', 'vn', 'bn', 'tl', 'ur' ];

		// Check if the provided language is valid
		if ( validLanguages.includes( language ) ) {
			// Only the languages the admin left enabled are shipped, so a visitor
			// carrying a language that has since been switched off falls back to
			// the configured default and then to English.
			const dictionaries = onetapAjaxObject.languages || {};
			const languageData = dictionaries[ language ] ||
				dictionaries[ onetapAjaxObject.activeLanguage ] ||
				dictionaries.en;

			if ( ! languageData ) {
				return;
			}

			if ( onetapRtlLanguages.includes( language ) ) {
				$( 'nav.onetap-plugin-onetap' ).addClass( 'onetap-rtl' );
			} else {
				$( 'nav.onetap-plugin-onetap' ).removeClass( 'onetap-rtl' );
			}

			// Update visual active state: clear from all items, then apply to the clicked one
			const $languageItems = $( 'nav.onetap-accessibility header.onetap-header-top .onetap-list-of-languages ul li' );
			$languageItems.removeClass( 'onetap-active' );
			$( 'nav.onetap-accessibility header.onetap-header-top .onetap-list-of-languages ul li[data-language="' + language + '"]' ).addClass( 'onetap-active' );

			// Update the language flag icon image with the full path from pluginInfo
			$( 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings header.onetap-header-top .onetap-languages.onetap-disable .onetap-icon img' ).attr( 'src', onetapAjaxObject.pluginInfo.imagesUrl + onetapAjaxObject.languageList[ language ].image );

			// Define an array of selectors and their corresponding data keys
			const updates = [
				// Global elements
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .accessibility-status-wrapper .accessibility-status-text button', text: languageData.global.back },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .accessibility-status-wrapper .accessibility-status-text button[data-default]', text: languageData.global.default },

				// Header elements
				{ selector: 'nav.onetap-accessibility header.onetap-header-top .onetap-languages .onetap-text span', text: languageData.header.language },
				{ selector: 'nav.onetap-accessibility header.onetap-header-top .onetap-site-container .onetap-site-info .onetap-title span', text: languageData.header.title },
				{ selector: 'nav.onetap-accessibility header.onetap-header-top .onetap-site-container .onetap-site-info .onetap-desc p span', text: languageData.header.desc },
				{ selector: 'nav.onetap-accessibility header.onetap-header-top .onetap-site-container .onetap-site-info .onetap-desc p a', text: languageData.header.anchor },
				{ selector: 'nav.onetap-accessibility header.onetap-header-top .onetap-site-container .onetap-site-info .onetap-statement button', text: languageData.header.statement },
				{ selector: 'nav.onetap-accessibility header.onetap-header-top .onetap-site-container .onetap-site-info .onetap-hide-toolbar button', text: languageData.header.hideToolbar },

				// Hide toolbar elements
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration span.onetap-title', text: languageData.hideToolbar.title },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration [for="only-for-this-session"] span', text: languageData.hideToolbar.radio1 },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration [for="only-for-24-hours"] span', text: languageData.hideToolbar.radio2 },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration [for="only-for-a-week"] span', text: languageData.hideToolbar.radio3 },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration button.close-box-hide-duration', text: languageData.hideToolbar.button1 },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .toolbar-hide-duration .box-hide-duration button.hide-toolbar', text: languageData.hideToolbar.button2 },

				// Unsupported page reader elements
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature .onetap-message .title', text: languageData.unsupportedPageReader.title },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature .onetap-message .desc', text: languageData.unsupportedPageReader.desc },
				{ selector: 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-container .onetap-accessibility-settings .onetap-features-container .onetap-features .onetap-box-feature .onetap-message .desc .link', text: languageData.unsupportedPageReader.link },

				// Multi-functional feature elements
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-title span', text: languageData.multiFunctionalFeature.title },

				// Vision impaired mode
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-vision-impaired-mode .onetap-left .onetap-text .onetap-title span', text: languageData.multiFunctionalFeature.visionImpairedMode.title },
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-vision-impaired-mode .onetap-left .onetap-text .onetap-desc span', text: languageData.multiFunctionalFeature.visionImpairedMode.desc },
				{ selectorOn: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-vision-impaired-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', on: languageData.multiFunctionalFeature.visionImpairedMode.on },
				{ selectorOff: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-vision-impaired-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', off: languageData.multiFunctionalFeature.visionImpairedMode.off },

				// Seizure safe profile mode
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-seizure-safe-profile-mode .onetap-left .onetap-text .onetap-title span', text: languageData.multiFunctionalFeature.seizureSafeProfile.title },
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-seizure-safe-profile-mode .onetap-left .onetap-text .onetap-desc span', text: languageData.multiFunctionalFeature.seizureSafeProfile.desc },
				{ selectorOn: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-seizure-safe-profile-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', on: languageData.multiFunctionalFeature.seizureSafeProfile.on },
				{ selectorOff: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-seizure-safe-profile-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', off: languageData.multiFunctionalFeature.seizureSafeProfile.off },

				// ADHD friendly mode
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-adhd-friendly-mode .onetap-left .onetap-text .onetap-title span', text: languageData.multiFunctionalFeature.aDHDFriendlyMode.title },
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-adhd-friendly-mode .onetap-left .onetap-text .onetap-desc span', text: languageData.multiFunctionalFeature.aDHDFriendlyMode.desc },
				{ selectorOn: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-adhd-friendly-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', on: languageData.multiFunctionalFeature.aDHDFriendlyMode.on },
				{ selectorOff: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-adhd-friendly-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', off: languageData.multiFunctionalFeature.aDHDFriendlyMode.off },

				// Blindness mode
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-blindness-mode .onetap-left .onetap-text .onetap-title span', text: languageData.multiFunctionalFeature.blindnessMode.title },
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-blindness-mode .onetap-left .onetap-text .onetap-desc span', text: languageData.multiFunctionalFeature.blindnessMode.desc },
				{ selectorOn: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-blindness-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', on: languageData.multiFunctionalFeature.blindnessMode.on },
				{ selectorOff: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-blindness-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', off: languageData.multiFunctionalFeature.blindnessMode.off },

				// Epilepsy safe mode
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-epilepsy-safe-mode .onetap-left .onetap-text .onetap-title span', text: languageData.multiFunctionalFeature.epilepsySafeMode.title },
				{ selector: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-epilepsy-safe-mode .onetap-left .onetap-text .onetap-desc span', text: languageData.multiFunctionalFeature.epilepsySafeMode.desc },
				{ selectorOn: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-epilepsy-safe-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', on: languageData.multiFunctionalFeature.epilepsySafeMode.on },
				{ selectorOff: 'nav.onetap-accessibility .onetap-container .onetap-accessibility-settings .onetap-multi-functional-feature .onetap-box-functions .onetap-box-epilepsy-safe-mode .onetap-right .onetap-toggle-container .label-mode-switch .label-mode-switch-inner', off: languageData.multiFunctionalFeature.epilepsySafeMode.off },

				// Titles section
				{ selector: 'nav.onetap-accessibility .onetap-features-container.onetap-feature-content-modules .onetap-box-title .onetap-title', text: languageData.titles.contentModules },
				{ selector: 'nav.onetap-accessibility .onetap-features-container.onetap-feature-color-modules .onetap-box-title .onetap-title', text: languageData.titles.colorModules },
				{ selector: 'nav.onetap-accessibility .onetap-features-container.onetap-feature-orientation-modules .onetap-box-title .onetap-title', text: languageData.titles.orientationModules },

				// Skip to Content (toolbar language pack)
				{ selector: 'nav.onetap-accessibility .onetap-skip-content .onetap-skip-label', text: languageData.features.skipToContent },
				{ selector: 'nav.onetap-accessibility .onetap-skip-content .onetap-skip-select option[value=""]', text: languageData.features.skipChoose },
				{ selector: 'nav.onetap-accessibility .onetap-skip-content .onetap-skip-select option[value="main"]', text: languageData.features.skipMain },
				{ selector: 'nav.onetap-accessibility .onetap-skip-content .onetap-skip-select option[value="navigation"]', text: languageData.features.skipNavigation },
				{ selector: 'nav.onetap-accessibility .onetap-skip-content .onetap-skip-select option[value="footer"]', text: languageData.features.skipFooter },

				// Features section - Content features
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-bigger-text .onetap-title > span', text: languageData.features.biggerText },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-bigger-text .onetap-title .onetap-info', text: languageData.global.default },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-highlight-links .onetap-title > span', text: languageData.features.highlightLinks },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-line-height .onetap-title > span', text: languageData.features.lineHeight },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-line-height .onetap-title .onetap-info', text: languageData.global.default },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-readable-font .onetap-title > span', text: languageData.features.readableFont },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-cursor .onetap-title > span', text: languageData.features.cursor },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-text-magnifier .onetap-title > span', text: languageData.features.textMagnifier },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-dyslexic-font .onetap-title > span', text: languageData.features.dyslexicFont },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-align-center .onetap-title > span', text: languageData.features.alignDefault },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-letter-spacing .onetap-title > span', text: languageData.features.letterSpacing },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-font-weight .onetap-title > span', text: languageData.features.fontWeight },

				// Features section - Color features
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-dark-contrast .onetap-title > span', text: languageData.features.darkContrast },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-light-contrast .onetap-title > span', text: languageData.features.lightContrast },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-high-contrast .onetap-title > span', text: languageData.features.highContrast },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-monochrome .onetap-title > span', text: languageData.features.monochrome },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-saturation .onetap-title > span', text: languageData.features.saturation },

				// Features section - Orientation features
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-reading-line .onetap-title > span', text: languageData.features.readingLine },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-reading-mask .onetap-title > span', text: languageData.features.readingMask },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-read-page .onetap-title > span', text: languageData.features.readPage },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-keyboard-navigation .onetap-title > span', text: languageData.features.keyboardNavigation },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-hide-images .onetap-title > span', text: languageData.features.hideImages },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-mute-sounds .onetap-title > span', text: languageData.features.muteSounds },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-highlight-titles .onetap-title > span', text: languageData.features.highlightTitles },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-highlight-all .onetap-title > span', text: languageData.features.highlightAll },
				{ selector: 'nav.onetap-accessibility .onetap-features .onetap-stop-animations .onetap-title > span', text: languageData.features.stopAnimations },

				// Reset settings
				{ selector: 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-reset-settings button', text: languageData.features.resetSettings },
			];

			// Update each element with the corresponding text
			updates.forEach( ( update ) => {
				$( update.selector ).text( update.text );

				// Update aria-label for all multi-functional feature title spans
				if ( update.selector && update.selector.includes( '.onetap-multi-functional-feature' ) && update.selector.includes( '.onetap-title span' ) ) {
					const $switchLabel = $( update.selector ).parent().parent().parent().parent().find( '.onetap-right .label-mode-switch' );
					$switchLabel.attr( 'aria-label', update.text );
					// Keep the visually hidden label text in sync so the control keeps a real label.
					$switchLabel.find( '.onetap-visually-hidden' ).text( update.text );
				}

				// Check if update.selectorOn exist.
				if ( update.selectorOn ) {
					$( update.selectorOn ).attr( 'data-on', update.on );
				}

				// Check if update.selectorOff exist.
				if ( update.selectorOff ) {
					$( update.selectorOff ).attr( 'data-off', update.off );
				}
			} );
		}
	}

	/**
	 * Applies translated labels to each accessibility feature box,
	 * based on the active language and predefined localized labels.
	 *
	 * @param {string} activeLang - The currently active language code.
	 */
	function applyLanguageTranslationsToFeatures( activeLang ) {
		// Get the localized labels and the currently active language from the global object
		const localizedLabels = onetapAjaxObject?.localizedLabels;
		const getActiveLang = activeLang;

		// Get the labels for the currently active language
		const labelsForCurrentLang = localizedLabels?.[ getActiveLang ];

		// Loop through each element with the class 'onetap-box-feature' inside accessibility settings
		$( '.onetap-accessibility-settings .onetap-features .onetap-box-feature' ).each( function() {
			// Get all class names applied to the current feature box
			const allClassNames = $( this ).attr( 'class' ).split( /\s+/ );

			// Filter out the base class 'onetap-box-feature' to isolate the actual feature class
			const featureClassNames = allClassNames.filter( ( className ) => className !== 'onetap-box-feature' );

			// Convert each class name into camelCase format after removing 'onetap-' prefix
			const camelCaseFeatureKeys = featureClassNames
				.map( ( className ) => className.replace( /^onetap-/, '' ) ) // Remove the 'onetap-' prefix
				.map( ( className ) => className.replace( /-([a-z])/g, ( _, letter ) => letter.toUpperCase() ) ); // Convert kebab-case to camelCase

			// Use the first camelCased key as the feature key for translation
			const featureKey = camelCaseFeatureKeys[ 0 ];

			// Get the translated label for the current feature key
			const translatedLabel = labelsForCurrentLang?.[ featureKey ];

			// If a valid translated label exists, update the text of the heading element inside the feature box
			if ( translatedLabel !== undefined && translatedLabel !== null && translatedLabel !== '' ) {
				$( '.' + featureClassNames.join( '.' ) + ' .onetap-heading' ).text( translatedLabel );
			}
		} );

		// Apply translation to "Default" text in .onetap-info elements
		// For bigger-text feature, use biggerTextDefault
		if ( labelsForCurrentLang?.biggerTextDefault ) {
			$( '.onetap-box-feature.onetap-bigger-text .onetap-info' ).text( labelsForCurrentLang.biggerTextDefault );
		}

		// For line-height feature, use lineHeightDefault
		if ( labelsForCurrentLang?.lineHeightDefault ) {
			$( '.onetap-box-feature.onetap-line-height .onetap-info' ).text( labelsForCurrentLang.lineHeightDefault );
		}

		// Apply translation to align-center feature
		if ( labelsForCurrentLang?.textAlign ) {
			$( '.onetap-box-feature.onetap-align-center .onetap-heading' ).text( labelsForCurrentLang.textAlign );
		}

		// Skip to Content: custom label from module labels (key skip-to-content → skipToContent).
		const skipToContentCustom = labelsForCurrentLang?.skipToContent || labelsForCurrentLang?.skipToContentDefault;
		if ( skipToContentCustom ) {
			$( '.onetap-skip-content .onetap-skip-label' ).text( skipToContentCustom );
		}
	}

	/**
	 * Apply language translations to functional features and box titles
	 *
	 * @param {string} activeLang - The currently active language code.
	 */
	function applyLanguageTranslationsToFunctionalFeatures( activeLang ) {
		// Get the localized labels and the currently active language from the global object
		const localizedLabels = onetapAjaxObject?.localizedLabels;
		const getActiveLang = activeLang;

		// Get the labels for the currently active language
		const labelsForCurrentLang = localizedLabels?.[ getActiveLang ];

		// Apply translations to .onetap-site-container .onetap-site-info .onetap-statement button
		$( '.onetap-site-container .onetap-site-info .onetap-statement button' ).each( function() {
			// Check if labelsForCurrentLang exists and has accessibilityInformation property
			if ( labelsForCurrentLang && labelsForCurrentLang.accessibilityInformation ) {
				const title = labelsForCurrentLang.accessibilityInformation;

				// Only update text if title has a valid value (not undefined, null, or empty)
				if ( title !== undefined && title !== null && title !== '' ) {
					$( this ).text( title );
				}
			}
		} );

		// Apply translations to .onetap-site-container .onetap-site-info .onetap-hide-toolbar button
		$( '.onetap-site-container .onetap-site-info .onetap-hide-toolbar button' ).each( function() {
			// Check if labelsForCurrentLang exists and has hideToolbar property
			if ( labelsForCurrentLang && labelsForCurrentLang.hideToolbar ) {
				const title = labelsForCurrentLang.hideToolbar;

				// Only update text if title has a valid value (not undefined, null, or empty)
				if ( title !== undefined && title !== null && title !== '' ) {
					$( this ).text( title );
				}
			}
		} );

		// Apply translations to .onetap-site-container .onetap-site-info .onetap-title span
		$( '.onetap-site-container .onetap-site-info .onetap-title span' ).each( function() {
			// Check if labelsForCurrentLang exists and has accessibilityAdjustments property
			if ( labelsForCurrentLang && labelsForCurrentLang.accessibilityAdjustments ) {
				const title = labelsForCurrentLang.accessibilityAdjustments;

				// Only update text if title has a valid value (not undefined, null, or empty)
				if ( title !== undefined && title !== null && title !== '' ) {
					$( this ).text( title );
				}
			}
		} );

		// Apply translations to .onetap-box-functions .onetap-box-title span
		$( '.onetap-box-functions .onetap-box-title span' ).each( function() {
			// Check if labelsForCurrentLang exists and has selectYourAccessibilityProfile property
			if ( labelsForCurrentLang && labelsForCurrentLang.selectYourAccessibilityProfile ) {
				const title = labelsForCurrentLang.selectYourAccessibilityProfile;

				// Only update text if title has a valid value (not undefined, null, or empty)
				if ( title !== undefined && title !== null && title !== '' ) {
					$( this ).text( title );
				}
			}
		} );

		// Apply translations to .onetap-functional-feature .onetap-title span
		$( '.onetap-box-functions .onetap-functional-feature' ).each( function() {
			if ( labelsForCurrentLang ) {
				// Get all class names applied to the current feature box
				const allClassNames = $( this ).attr( 'class' ).split( /\s+/ );

				// Filter out the base class 'onetap-functional-feature' to isolate the actual feature class
				const featureClassNames = allClassNames.filter( ( className ) => className !== 'onetap-functional-feature' );

				// Convert each class name into camelCase format after removing 'onetap-' prefix
				const camelCaseFeatureKeys = featureClassNames
					.map( ( className ) => className.replace( /^onetap-box/, '' ) ) // Remove the 'onetap-' prefix
					.map( ( className ) => className.replace( /-([a-z])/g, ( _, letter ) => letter.toUpperCase() ) ) // Convert kebab-case to camelCase
					.map( ( className ) => className.charAt( 0 ).toLowerCase() + className.slice( 1 ) ); // Make first letter lowercase

				// Use the first camelCased key as the feature key for translation
				const featureKey = camelCaseFeatureKeys[ 0 ];

				// Get the translated label for the current feature key
				const translatedLabel = labelsForCurrentLang?.[ featureKey ];

				// If a valid translated label exists, update the text of the heading element inside the feature box
				if ( translatedLabel !== undefined && translatedLabel !== null && translatedLabel !== '' ) {
					$( '.' + featureClassNames.join( '.' ) + ' .onetap-title span' ).text( translatedLabel );

					// Mapping for description translations based on feature class names
					const descriptionMapping = {
						'onetap-box-vision-impaired-mode': 'enhancesWebsitesVisuals',
						'onetap-box-seizure-safe-profile-mode': 'clearFlashesReducesColor',
						'onetap-box-adhd-friendly-mode': 'focusedBrowsingDistractionFree',
						'onetap-box-blindness-mode': 'reducesDistractionsImprovesFocus',
						'onetap-box-epilepsy-safe-mode': 'dimsColorsAndStopsBlinking',
					};

					// Get the description key for current feature
					const descriptionKey = descriptionMapping[ featureClassNames[ 0 ] ];

					// Apply description translation if key exists and has valid value
					if ( descriptionKey && labelsForCurrentLang[ descriptionKey ] ) {
						$( '.' + featureClassNames.join( '.' ) + ' .onetap-desc span' ).text( labelsForCurrentLang[ descriptionKey ] );
					}
				}
			}
		} );
	}

	// The function to apply language translations to feature boxes
	// applyLanguageTranslationsToFeatures( getDataAccessibilityData().information.language );

	// The function to apply language translations to feature boxes
	// applyLanguageTranslationsToFeatures();

	// The function to apply language translations to functional features
	// applyLanguageTranslationsToFunctionalFeatures( getDataAccessibilityData().information.language );

	// The function to apply language translations to feature boxes
	// applyLanguageTranslationsToFunctionalFeatures();

	// Clicking the left area toggles the corresponding switch (delegate on onetapAccessibility to bypass stopPropagation to document)
	onetapAccessibility.on( 'click', '.onetap-functional-feature .onetap-left', function( event ) {
		// Avoid interfering with other controls
		event.stopPropagation();
		const $feature = $( this ).closest( '.onetap-functional-feature' );
		const $checkbox = $feature.find( '.label-mode-switch input[type="checkbox"]' );
		if ( $checkbox.length ) {
			$checkbox.trigger( 'click' );
		}
	} );

	// Updates the letter-spacing of elements except for the excluded selectors
	function onetapUpdateLetterSpacing( letter, excludedSelectors, letterSpacing, activeBorderValue ) {
		$( '*' ).not( excludedSelectors ).each( function() {
			// Get the current inline style of the element, or use an empty string if none exists
			let currentStyle = $( this ).attr( 'style' ) || '';

			if ( 0 === activeBorderValue ) {
				// Remove the letter-spacing if activeBorderValue is 0
				currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/, '' );
			} else if ( [ 1, 2, 3 ].includes( activeBorderValue ) ) {
				// Check if the element has a style attribute and if it ends with a semicolon
				if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
					currentStyle += ';';
				}

				// Check if 'letter-spacing' is already defined in the style
				if ( /letter-spacing:\s*[^;]+;?/.test( currentStyle ) ) {
					// If it exists, replace the existing letter-spacing with the new value
					currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/, 'letter-spacing: ' + letterSpacing );
				} else {
					// If letter-spacing is not present, append it to the style attribute
					currentStyle += ' letter-spacing: ' + letterSpacing;
				}
			}

			// Trim any extra spaces and ensure there's no trailing space
			currentStyle = currentStyle.trim();

			// Set the updated style attribute back to the element
			$( this ).attr( 'style', currentStyle );
		} );
	}

	// This function adjusts the text size based on the 'biggerText'
	function onetapBiggerText( key, activeBorderValue, selector ) {
		// if value off, return
		if ( 'off' === onetapAjaxObject.showModules[ 'bigger-text' ] ) {
			return;
		}

		// Check if the key is 'fontSize'. If it is, the function will proceed with font size adjustments.
		if ( 'biggerText' === key ) {
			// Map steps to 10% per step; allow negative steps down to -10 and up to 10
			// Example: -10 => -100%, 0 => default, 10 => +100%
			let increasePercent = 0;
			if ( Number.isFinite( activeBorderValue ) ) {
				increasePercent = Math.max( -10, Math.min( 10, activeBorderValue ) ) * 0.10;
			}

			// Resolve related .onetap-info from DOM context when available, fallback to selector string
			let $trigger = $( selector );
			if ( ! $trigger.length ) {
				const selectorString = ( selector || '' );
				const selectorClassList = selectorString.split( /\s+/ ).filter( Boolean );
				const selectorAsCss = selectorClassList.length ? '.' + selectorClassList.join( '.' ) : '';
				$trigger = selectorAsCss ? $( selectorAsCss ).first() : $();
			}
			const $featureBox = $trigger.closest( '.onetap-box-feature' );
			const $info = $featureBox.find( '.onetap-info' );
			if ( $info.length ) {
				// Get the localized labels and the currently active language from the global object
				const localizedLabels = onetapAjaxObject?.languages;
				const getActiveLang = getDataAccessibilityData().information.language;

				// Get the labels for the currently active language
				const labelsForCurrentLang = localizedLabels?.[ getActiveLang ];
				const biggerTextDefault = onetapAjaxObject?.localizedLabels?.[ getActiveLang ]?.biggerTextDefault;
				const defaultText = ( biggerTextDefault && biggerTextDefault.trim() !== '' )
					? biggerTextDefault
					: ( labelsForCurrentLang?.global?.default || 'Default' );

				let infoText = defaultText;
				if ( activeBorderValue !== 0 ) {
					const clamped = Math.max( -10, Math.min( 10, activeBorderValue ) );
					infoText = ( clamped * 10 ) + '%';
				}
				$info.text( infoText );
			}

			// General
			const validTags = [
				'p', 'span', 'a', 'li', 'td', 'th', 'label', 'button', 'input', 'textarea',
				'strong', 'em', 'b', 'i', 'u', 'small', 'time', 'code', 'pre',
				'blockquote', 'cite', 'abbr', 'address', 'q', 'dt', 'dd',
				'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
				'mark', 'sup', 'sub', 'del', 'ins', 's',
				'kbd', 'samp', 'var',
				'legend', 'figcaption', 'summary', 'div', 'body',
			];

			// Collect targets first, then snapshot computed baselines, then mutate styles.
			// Otherwise e.g. `body` gets a larger font first and inheriting descendants read
			// an inflated `getComputedStyle().fontSize`, which is wrongly stored as the baseline
			// (noticeable after reload when bigger-text is restored from localStorage).
			const biggerTextTargets = [];
			$( 'body, body *' ).each( function() {
				const el = this;
				const tag = el.tagName ? el.tagName.toLowerCase() : '';

				// Skip if tag is not in validTags
				if ( ! validTags.includes( tag ) ) {
					return;
				}

				// Skip elements in onetapSkipElements if needed
				if ( $( this ).is( onetapSkipElements ) ) {
					return;
				}

				// Skip <li> elements that are inside another <li>.
				if ( tag === 'li' && $( this ).parents( 'li' ).length > 0 ) {
					return;
				}

				// Skip <div> elements that are inside another <div> (to avoid nested container compounding).
				if ( tag === 'div' && $( this ).parents( 'div' ).length > 0 ) {
					return;
				}

				// Skip <li> that contains both <a> and <span> (any depth)
				if ( tag === 'li' && $( this ).find( 'a' ).length > 0 && $( this ).find( 'span' ).length > 0 ) {
					return;
				}

				// Skip <a> that contains any other element (child or descendant)
				// if ( tag === 'a' && $( this ).find( '*' ).length > 0 ) {
				// 	return;
				// }

				// Skip <span> that contains any other element (child or descendant)
				if ( tag === 'span' && $( this ).find( '*' ).length > 0 ) {
					return;
				}

				biggerTextTargets.push( el );
			} );

			biggerTextTargets.forEach( function( el ) {
				const computedStyle = window.getComputedStyle( el );

				const currentFontSize = parseFloat( computedStyle.fontSize );

				if ( ! el.dataset.originalFontSize ) {
					el.dataset.originalFontSize = currentFontSize;
				}

				const inlineStyle = $( el ).attr( 'style' ) || '';

				// Capture original inline font-size value once for proper restore (e.g., clamp()).
				if ( ! el.dataset.originalInlineFontSize ) {
					const match = inlineStyle.match( /font-size:\s*([^;]+);?/ );
					if ( match && match[ 1 ] ) {
						const fontSizeValue = match[ 1 ].trim();
						// Check if this font-size was added by the plugin
						// Plugin adds font-size with format: "X.XXpx !important" where X.XX is calculated (2 decimal places)
						const isPluginAdded = fontSizeValue.includes( '!important' ) &&
							/^\d+\.\d{2}px\s+!important$/i.test( fontSizeValue.trim() );

						if ( ! isPluginAdded ) {
							// This is an original inline style from HTML, save it
							el.dataset.originalInlineFontSize = fontSizeValue;
						} else {
							// This is plugin-added, so original is empty (element had no original inline font-size)
							el.dataset.originalInlineFontSize = '';
						}
					} else {
						// No inline font-size found, original is empty
						el.dataset.originalInlineFontSize = '';
					}
				}
			} );

			biggerTextTargets.forEach( function( el ) {
				const $el = $( el );
				let newStyle = $el.attr( 'style' ) || '';

				const baseFontSize = parseFloat( el.dataset.originalFontSize );

				if ( 0 === activeBorderValue ) {
					// Reset to original state: restore original inline font-size if it existed, otherwise remove override.
					const originalInline = el.dataset.originalInlineFontSize || '';
					if ( originalInline ) {
						const restoreStr = `font-size: ${ originalInline };`;
						// Replace any existing font-size (including with !important)
						newStyle = newStyle.replace( /font-size:\s*[^;]+;?/gi, restoreStr );
						// If font-size wasn't found, add it
						if ( ! /font-size:\s*[^;]+;?/i.test( newStyle ) ) {
							newStyle += ( newStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreStr;
						}
					} else {
						// No original inline font-size, remove any font-size added by plugin
						newStyle = newStyle.replace( /font-size:\s*[^;]+;?/gi, '' );
						// Clean up: remove double semicolons and leading/trailing semicolons
						newStyle = newStyle.replace( /;;+/g, ';' ).replace( /^;+|;+$/g, '' ).trim();
					}
				} else if ( baseFontSize ) {
					const newFontSize = ( baseFontSize * ( 1 + increasePercent ) ).toFixed( 2 );
					const fontSizeStr = `font-size: ${ newFontSize }px !important;`;

					if ( /font-size:\s*[^;]+;?/.test( newStyle ) ) {
						newStyle = newStyle.replace( /font-size:\s*[^;]+;?/, fontSizeStr );
					} else {
						newStyle += ( newStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + fontSizeStr;
					}
				}

				$el.attr( 'style', newStyle.trim() );
			} );
		}
	}

	// This function adjusts the line height based on the 'lineHeight'
	function onetapLineHeight( key, activeBorderValue, selector ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'line-height' ] ) {
			return;
		}

		// Check if the key is 'lineHeight'. If it is, the function will proceed with line height adjustments.
		if ( 'lineHeight' === key ) {
			// Map steps to 10% per step; allow negative steps down to -10 and up to 10
			let increasePercent = 0;
			if ( Number.isFinite( activeBorderValue ) ) {
				increasePercent = Math.max( -10, Math.min( 10, activeBorderValue ) ) * 0.10;
			}

			// Resolve related .onetap-info from DOM context when available, fallback to selector string
			let $trigger = $( selector );
			if ( ! $trigger.length ) {
				const selectorString = ( selector || '' );
				const selectorClassList = selectorString.split( /\s+/ ).filter( Boolean );
				const selectorAsCss = selectorClassList.length ? '.' + selectorClassList.join( '.' ) : '';
				$trigger = selectorAsCss ? $( selectorAsCss ).first() : $();
			}
			const $featureBox = $trigger.closest( '.onetap-box-feature' );
			const $info = $featureBox.find( '.onetap-info' );
			if ( $info.length ) {
				// Get the localized labels and the currently active language from the global object
				const localizedLabels = onetapAjaxObject?.languages;
				const getActiveLang = getDataAccessibilityData().information.language;

				// Get the labels for the currently active language
				const labelsForCurrentLang = localizedLabels?.[ getActiveLang ];
				const lineHeightDefault = onetapAjaxObject?.localizedLabels?.[ getActiveLang ]?.lineHeightDefault;
				const defaultText = ( lineHeightDefault && lineHeightDefault.trim() !== '' )
					? lineHeightDefault
					: ( labelsForCurrentLang?.global?.default || 'Default' );

				let infoText = defaultText;
				if ( activeBorderValue !== 0 ) {
					const clamped = Math.max( -10, Math.min( 10, activeBorderValue ) );
					infoText = ( clamped * 10 ) + '%';
				}
				$info.text( infoText );
			}

			// General
			const validTags = [
				'p', 'span', 'a', 'li', 'td', 'th', 'label', 'button', 'input', 'textarea',
				'strong', 'em', 'b', 'i', 'u', 'small', 'time', 'code', 'pre',
				'blockquote', 'cite', 'abbr', 'address', 'q', 'dt', 'dd',
				'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
				'mark', 'sup', 'sub', 'del', 'ins', 's',
				'kbd', 'samp', 'var',
				'legend', 'figcaption', 'summary', 'body',
			];

			$( 'body, body *' ).each( function() {
				const el = this;
				const tag = el.tagName ? el.tagName.toLowerCase() : '';

				// Skip if tag is not in validTags
				if ( ! validTags.includes( tag ) ) {
					return;
				}

				// Skip elements in onetapSkipElements if needed
				if ( $( this ).is( onetapSkipElements ) ) {
					return;
				}

				const computedStyle = window.getComputedStyle( el );

				// Skip <li> elements that are inside another <li>.
				if ( tag === 'li' && $( this ).parents( 'li' ).length > 0 ) {
					return;
				}

				const currentLineHeight = parseFloat( computedStyle.lineHeight );

				if ( ! el.dataset.originalLineHeight ) {
					el.dataset.originalLineHeight = currentLineHeight;
				}

				const baseLineHeight = parseFloat( el.dataset.originalLineHeight );
				let newStyle = $( this ).attr( 'style' ) || '';

				// Capture original inline line-height value once for proper restore.
				if ( ! el.dataset.originalInlineLineHeight ) {
					const match = newStyle.match( /line-height:\s*([^;]+);?/ );
					if ( match && match[ 1 ] ) {
						const lineHeightValue = match[ 1 ].trim();
						// Check if this line-height was added by the plugin
						// Plugin adds line-height with format: "X.XXpx !important" where X.XX is calculated (2 decimal places)
						const isPluginAdded = lineHeightValue.includes( '!important' ) &&
							/^\d+\.\d{2}px\s+!important$/i.test( lineHeightValue.trim() );

						if ( ! isPluginAdded ) {
							// This is an original inline style from HTML, save it
							el.dataset.originalInlineLineHeight = lineHeightValue;
						} else {
							// This is plugin-added, so original is empty (element had no original inline line-height)
							el.dataset.originalInlineLineHeight = '';
						}
					} else {
						// No inline line-height found, original is empty
						el.dataset.originalInlineLineHeight = '';
					}
				}

				if ( 0 === activeBorderValue ) {
					// Reset to original state: restore original inline line-height if it existed, otherwise remove override.
					const originalInline = el.dataset.originalInlineLineHeight || '';
					if ( originalInline ) {
						const restoreStr = `line-height: ${ originalInline };`;
						// Replace any existing line-height (including with !important)
						newStyle = newStyle.replace( /line-height:\s*[^;]+;?/gi, restoreStr );
						// If line-height wasn't found, add it
						if ( ! /line-height:\s*[^;]+;?/i.test( newStyle ) ) {
							newStyle += ( newStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreStr;
						}
					} else {
						// No original inline line-height, remove any line-height added by plugin
						newStyle = newStyle.replace( /line-height:\s*[^;]+;?/gi, '' );
						// Clean up: remove double semicolons and leading/trailing semicolons
						newStyle = newStyle.replace( /;;+/g, ';' ).replace( /^;+|;+$/g, '' ).trim();
					}
				} else if ( baseLineHeight ) {
					const newLineHeight = ( baseLineHeight * ( 1 + increasePercent ) ).toFixed( 2 );
					const lineHeightStr = `line-height: ${ newLineHeight }px !important;`;

					if ( /line-height:\s*[^;]+;?/.test( newStyle ) ) {
						newStyle = newStyle.replace( /line-height:\s*[^;]+;?/, lineHeightStr );
					} else {
						newStyle += ( newStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + lineHeightStr;
					}
				}

				$( this ).attr( 'style', newStyle.trim() );
			} );
		}
	}

	// This function modifies the cursor size by adding and removing classes
	function onetapCursor( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules.cursor ) {
			return;
		}

		// Check if the key is 'Cursor'. If it is, the function will proceed with font size adjustments.
		if ( 'cursor' === key ) {
			if ( ! accessibilityDataKey ) {
				$( 'html' ).removeClass( 'onetap-cursor-feature1' );
				$( 'html' ).removeClass( 'onetap-cursor-feature2' );
				$( 'html' ).removeClass( 'onetap-cursor-feature3' );
			} else if ( accessibilityDataKey ) {
				$( 'html' ).addClass( 'onetap-cursor-feature2' );
			}
		}
	}

	// This function adjusts the font weight based on the 'fontWeight'
	function onetapFontWeight( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'font-weight' ] ) {
			return;
		}

		// Check if the key is 'fontWeight'. If it is, the function will proceed with font weight adjustments.
		if ( 'fontWeight' === key ) {
			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).each( function() {
				const el = this;
				let currentStyle = $( this ).attr( 'style' ) || '';

				// Capture original inline font-weight value once for proper restore
				if ( ! el.dataset.originalInlineFontWeight ) {
					const match = currentStyle.match( /font-weight:\s*([^;]+);?/ );
					if ( match && match[ 1 ] ) {
						const fontWeightValue = match[ 1 ].trim();
						// Check if this font-weight was added by the plugin
						// Plugin adds font-weight with format: "700 !important"
						const isPluginAdded = fontWeightValue.includes( '!important' ) &&
							/^700\s+!important$/i.test( fontWeightValue.trim() );

						if ( ! isPluginAdded ) {
							// This is an original inline style from HTML, save it
							el.dataset.originalInlineFontWeight = fontWeightValue;
						} else {
							// This is plugin-added, so original is empty (element had no original inline font-weight)
							el.dataset.originalInlineFontWeight = '';
						}
					} else {
						// No inline font-weight found, original is empty
						el.dataset.originalInlineFontWeight = '';
					}
				}

				if ( ! accessibilityDataKey ) {
					// Reset to original state: restore original inline font-weight if it existed, otherwise remove override
					const originalInline = el.dataset.originalInlineFontWeight || '';
					if ( originalInline ) {
						const restoreStr = `font-weight: ${ originalInline };`;
						// Replace any existing font-weight (including with !important)
						currentStyle = currentStyle.replace( /font-weight:\s*[^;]+;?/gi, restoreStr );
						// If font-weight wasn't found, add it
						if ( ! /font-weight:\s*[^;]+;?/i.test( currentStyle ) ) {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreStr;
						}
					} else {
						// No original inline font-weight, remove any font-weight added by plugin
						currentStyle = currentStyle.replace( /font-weight:\s*[^;]+;?/gi, '' );
						// Clean up: remove double semicolons and leading/trailing semicolons
						currentStyle = currentStyle.replace( /;;+/g, ';' ).replace( /^;+|;+$/g, '' ).trim();
					}
				} else if ( accessibilityDataKey ) {
					// Remove any existing font-weight first
					currentStyle = currentStyle.replace( /font-weight:\s*[^;]+;?/gi, '' );

					// Handle font-weight
					const fontWeightStr = 'font-weight: 700 !important;';
					if ( /font-weight:\s*[^;]+;?/i.test( currentStyle ) ) {
						// If it exists, replace the existing font-weight with the new value
						currentStyle = currentStyle.replace( /font-weight:\s*[^;]+;?/gi, fontWeightStr );
					} else {
						// If font-weight is not present, append it to the style attribute
						currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + fontWeightStr;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the letter spacing based on the 'letterSpacing'
	function onetapLetterSpacing( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'letter-spacing' ] ) {
			return;
		}

		// Check if the key is 'letterSpacing'. If it is, the function will proceed with letter spacing adjustments.
		if ( 'letterSpacing' === key ) {
			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).each( function() {
				const el = this;
				let currentStyle = $( this ).attr( 'style' ) || '';

				// Capture original inline letter-spacing value once for proper restore
				if ( ! el.dataset.originalInlineLetterSpacing ) {
					const match = currentStyle.match( /letter-spacing:\s*([^;]+);?/ );
					if ( match && match[ 1 ] ) {
						const letterSpacingValue = match[ 1 ].trim();
						// Check if this letter-spacing was added by the plugin
						// Plugin adds letter-spacing with format: "2px !important"
						const isPluginAdded = letterSpacingValue.includes( '!important' ) &&
							/^2px\s+!important$/i.test( letterSpacingValue.trim() );

						if ( ! isPluginAdded ) {
							// This is an original inline style from HTML, save it
							el.dataset.originalInlineLetterSpacing = letterSpacingValue;
						} else {
							// This is plugin-added, so original is empty (element had no original inline letter-spacing)
							el.dataset.originalInlineLetterSpacing = '';
						}
					} else {
						// No inline letter-spacing found, original is empty
						el.dataset.originalInlineLetterSpacing = '';
					}
				}

				if ( ! accessibilityDataKey ) {
					// Reset to original state: restore original inline letter-spacing if it existed, otherwise remove override
					const originalInline = el.dataset.originalInlineLetterSpacing || '';
					if ( originalInline ) {
						const restoreStr = `letter-spacing: ${ originalInline };`;
						// Replace any existing letter-spacing (including with !important)
						currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/gi, restoreStr );
						// If letter-spacing wasn't found, add it
						if ( ! /letter-spacing:\s*[^;]+;?/i.test( currentStyle ) ) {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreStr;
						}
					} else {
						// No original inline letter-spacing, remove any letter-spacing added by plugin
						currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/gi, '' );
						// Clean up: remove double semicolons and leading/trailing semicolons
						currentStyle = currentStyle.replace( /;;+/g, ';' ).replace( /^;+|;+$/g, '' ).trim();
					}
				} else if ( accessibilityDataKey ) {
					// Remove any existing letter-spacing first
					currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/gi, '' );

					// Handle letter-spacing
					const letterSpacingStr = 'letter-spacing: 2px !important;';
					if ( /letter-spacing:\s*[^;]+;?/i.test( currentStyle ) ) {
						// If it exists, replace the existing letter-spacing with the new value
						currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/gi, letterSpacingStr );
					} else {
						// If letter-spacing is not present, append it to the style attribute
						currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + letterSpacingStr;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the readablefont based on the 'readableFont'
	function onetapReadableFont( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'readable-font' ] ) {
			return;
		}

		// Check if the key is 'readableFont'. If it is, the function will proceed with font size adjustments.
		if ( 'readableFont' === key ) {
			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).not( 'i, i *' ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( ! accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( getDataAccessibilityData().dyslexicFont ) {
						currentStyle = currentStyle.replace( 'font-family: Roboto, sans-serif !important;', 'font-family: OpenDyslexic, sans-serif !important;' );
					} else {
						// Remove the font-family if accessibilityDataKey is 0
						currentStyle = currentStyle.replace( /font-family:\s*[^;]+;?/, '' );
					}
				} else if ( accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( /font-family:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing font-family with the new value
						currentStyle = currentStyle.replace( /font-family:\s*[^;]+;?/, 'font-family: Roboto, sans-serif !important;' );
					} else {
						// If font-family is not present, append it to the style attribute
						currentStyle += ' font-family: Roboto, sans-serif !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the dyslexicfont based on the 'dyslexicFont'
	function onetapDyslexicFont( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'dyslexic-font' ] ) {
			return;
		}

		// Check if the key is 'dyslexicFont'. If it is, the function will proceed with font size adjustments.
		if ( 'dyslexicFont' === key && ! getDataAccessibilityData().readableFont ) {
			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).not( 'i, i *' ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( ! accessibilityDataKey ) {
					// Remove the font-family if accessibilityDataKey is 0
					currentStyle = currentStyle.replace( /font-family:\s*[^;]+;?/, '' );
				} else if ( accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( /font-family:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing font-family with the new value
						currentStyle = currentStyle.replace( /font-family:\s*[^;]+;?/, 'font-family: OpenDyslexic, sans-serif !important;' );
					} else {
						// If font-family is not present, append it to the style attribute
						currentStyle += ' font-family: OpenDyslexic, sans-serif !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	/**
	 * Single "Align text" control: cycles left → center → right → off on each click.
	 * Uses alignLeft / alignCenter / alignRight in storage (mutually exclusive when applied).
	 *
	 * @param {string} key               Feature key from the accessibility handler.
	 * @param {Object} accessibilityData Persisted accessibility settings object.
	 */
	function onetapApplyTextAlign( key, accessibilityData ) {
		if ( 'off' === onetapAjaxObject.showModules[ 'align-center' ] ) {
			return;
		}

		if ( 'alignCenter' !== key ) {
			return;
		}

		let mode = 'off';
		if ( accessibilityData.alignLeft ) {
			mode = 'left';
		} else if ( accessibilityData.alignCenter ) {
			mode = 'center';
		} else if ( accessibilityData.alignRight ) {
			mode = 'right';
		}

		const $btn = $( 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-accessibility-settings .onetap-align-center' );
		const alignActive = 'off' !== mode;

		$btn.attr( 'aria-pressed', alignActive );
		$btn.toggleClass( 'onetap-active', alignActive );
		$btn
			.removeClass( 'onetap-text-align-mode-off onetap-text-align-mode-left onetap-text-align-mode-center onetap-text-align-mode-right' )
			.addClass( 'onetap-text-align-mode-' + mode );

		$( '*' ).not( onetapSkipElements ).each( function() {
			const $el = $( this );

			if ( 'off' === mode ) {
				// Only restore elements OneTap itself modified. Leave any pre-existing
				// inline styles in the page untouched.
				if ( undefined === $el.attr( 'data-onetap-orig-style' ) ) {
					return;
				}

				const originalStyle = $el.attr( 'data-onetap-orig-style' );
				if ( originalStyle ) {
					$el.attr( 'style', originalStyle );
				} else {
					$el.removeAttr( 'style' );
				}
				$el.removeAttr( 'data-onetap-orig-style' );
				return;
			}

			// Remember the original style once so it can be restored later.
			if ( undefined === $el.attr( 'data-onetap-orig-style' ) ) {
				$el.attr( 'data-onetap-orig-style', $el.attr( 'style' ) || '' );
			}

			let currentStyle = $el.attr( 'style' ) || '';
			const replacement = 'text-align: ' + mode + ' !important;';

			if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
				currentStyle += ';';
			}

			if ( /text-align:\s*[^;]+;?/.test( currentStyle ) ) {
				currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, replacement );
			} else {
				currentStyle += ' ' + replacement;
			}

			$el.attr( 'style', currentStyle.trim() );
		} );

		const currentLang = getDataAccessibilityData()?.information?.language || 'en';
		const langFeatures = onetapAjaxObject?.languages?.[ currentLang ]?.features;
		if ( langFeatures ) {
			let labelKey = 'alignDefault';
			if ( 'left' === mode ) {
				labelKey = 'alignLeft';
			} else if ( 'center' === mode ) {
				labelKey = 'alignCenter';
			} else if ( 'right' === mode ) {
				labelKey = 'alignRight';
			}
			$( 'nav.onetap-accessibility .onetap-features .onetap-align-center .onetap-title > span' ).text( langFeatures[ labelKey ] || langFeatures.alignDefault );
		}
	}

	// Align-center toggle: when ON forces center alignment globally (except skipped), when OFF clears it
	function onetapAlignCenter( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'align-center' ] ) {
			return;
		}

		if ( 'alignCenter' !== key ) {
			return;
		}

		// Enforce mutual exclusivity among align toggles
		$( '.onetap-align-center' ).attr( 'aria-pressed', !! accessibilityDataKey );
		$( '.onetap-align-left' ).attr( 'aria-pressed', false );
		$( '.onetap-align-right' ).attr( 'aria-pressed', false );

		// Ensure only this feature box is visibly active
		if ( accessibilityDataKey ) {
			$( '.onetap-box-feature.onetap-align-left, .onetap-box-feature.onetap-align-right' ).removeClass( 'onetap-active' );
			$( '.onetap-box-feature.onetap-align-center' ).addClass( 'onetap-active' );
		} else {
			$( '.onetap-box-feature.onetap-align-center' ).removeClass( 'onetap-active' );
		}

		$( '*' ).not( onetapSkipElements ).each( function() {
			let currentStyle = $( this ).attr( 'style' ) || '';

			if ( ! accessibilityDataKey ) {
				// Remove any text-align inline style
				currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, '' );
			} else {
				// Ensure trailing semicolon before appending/replacing
				if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
					currentStyle += ';';
				}

				if ( /text-align:\s*[^;]+;?/.test( currentStyle ) ) {
					currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, 'text-align: center !important;' );
				} else {
					currentStyle += ' text-align: center !important;';
				}
			}

			$( this ).attr( 'style', currentStyle.trim() );
		} );
	}

	// Align-right toggle: when ON forces right alignment globally (except skipped), when OFF clears it
	function onetapAlignRight( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'align-right' ] ) {
			return;
		}

		if ( 'alignRight' !== key ) {
			return;
		}

		// Enforce mutual exclusivity among align toggles
		$( '.onetap-align-right' ).attr( 'aria-pressed', !! accessibilityDataKey );
		$( '.onetap-align-left' ).attr( 'aria-pressed', false );
		$( '.onetap-align-center' ).attr( 'aria-pressed', false );

		// Ensure only this feature box is visibly active
		if ( accessibilityDataKey ) {
			$( '.onetap-box-feature.onetap-align-left, .onetap-box-feature.onetap-align-center' ).removeClass( 'onetap-active' );
			$( '.onetap-box-feature.onetap-align-right' ).addClass( 'onetap-active' );
		} else {
			$( '.onetap-box-feature.onetap-align-right' ).removeClass( 'onetap-active' );
		}

		$( '*' ).not( onetapSkipElements ).each( function() {
			let currentStyle = $( this ).attr( 'style' ) || '';

			if ( ! accessibilityDataKey ) {
				// Remove any text-align inline style
				currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, '' );
			} else {
				// Ensure trailing semicolon before appending/replacing
				if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
					currentStyle += ';';
				}

				if ( /text-align:\s*[^;]+;?/.test( currentStyle ) ) {
					currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, 'text-align: right !important;' );
				} else {
					currentStyle += ' text-align: right !important;';
				}
			}

			$( this ).attr( 'style', currentStyle.trim() );
		} );
	}

	// This function adjusts the text align based on the 'textAlign'
	function onetapTextAlign( key, activeBorderValue ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'text-align' ] ) {
			return;
		}

		// Check if the key is 'textAlign'. If it is, the function will proceed with font size adjustments.
		if ( 'textAlign' === key ) {
			let textAlign = null;

			// Determine textAlign value based on activeBorderValue
			if ( 1 === activeBorderValue ) {
				textAlign = 'left !important;';
			} else if ( 2 === activeBorderValue ) {
				textAlign = 'center !important;';
			} else if ( 3 === activeBorderValue ) {
				textAlign = 'right !important;';
			} else {
				textAlign = null;
			}

			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( 0 === activeBorderValue ) {
					// Remove the text-align if activeBorderValue is 0
					currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, '' );
				} else if ( 1 === activeBorderValue || 2 === activeBorderValue || 3 === activeBorderValue ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( /text-align:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing text-align with the new value
						currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, 'text-align: ' + textAlign );
					} else {
						// If text-align is not present, append it to the style attribute
						currentStyle += ' text-align: ' + textAlign;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the text magnifier based on the 'textMagnifier'
	function onetapTextMagnifier( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'text-magnifier' ] ) {
			return;
		}

		// Check if the key is 'textMagnifier'. If it is, the function will proceed with font size adjustments.
		if ( 'textMagnifier' === key ) {
			// Update style for all elements except specific ones
			if ( ! accessibilityDataKey ) {
				$( '.onetap-markup-text-magnifier' ).hide();
				$( document ).off( 'mousemove' );
			} else if ( accessibilityDataKey ) {
				$( document ).on( 'mousemove', function( event ) {
					const windowWidth = $( window ).width();
					const windowHeight = $( window ).height();

					// Get element position
					const hoveredElement = document.elementFromPoint( event.clientX, event.clientY );

					// Skip if hovered element has class 'onetap-markup-text-magnifier'
					if ( $( hoveredElement ).hasClass( 'onetap-markup-text-magnifier' ) ) {
						return; // Skip further execution
					}

					// Check hovered element position
					if ( hoveredElement ) {
						const $this = $( hoveredElement ); // Convert the DOM element to a jQuery object
						const text = $this.contents().filter( function() {
							return this.nodeType === 3; // Filter only text nodes
						} ).text().trim(); // Get and trim text from the direct text nodes

						// Add a title only if the element has direct text and no child elements
						if ( text ) {
							$( '.onetap-markup-text-magnifier' ).text( text );
							$( '.onetap-markup-text-magnifier' ).show();
						} else {
							$( '.onetap-markup-text-magnifier' ).text( '' );
							$( '.onetap-markup-text-magnifier' ).hide();
						}
					}

					// Determine quadrant and calculate position
					if ( event.pageX < windowWidth / 2 && event.pageY < windowHeight / 2 ) {
						// Left Top -> Right Bottom
						$( '.onetap-markup-text-magnifier' ).css( {
							left: event.pageX + 15 + 'px',
							top: event.pageY + 30 + 'px',
						} );
					} else if ( event.pageX >= windowWidth / 2 && event.pageY < windowHeight / 2 ) {
						// Right Top -> Left Bottom
						$( '.onetap-markup-text-magnifier' ).css( {
							left: event.pageX - 115 + 'px',
							top: event.pageY + 30 + 'px',
						} );
					} else if ( event.pageX < windowWidth / 2 && event.pageY >= windowHeight / 2 ) {
						// Left Bottom -> Right Top
						$( '.onetap-markup-text-magnifier' ).css( {
							left: event.pageX + 15 + 'px',
							top: event.pageY - 115 + 'px',
						} );
					} else {
						// Right Bottom -> Left Top
						$( '.onetap-markup-text-magnifier' ).css( {
							left: event.pageX - 115 + 'px',
							top: event.pageY - 115 + 'px',
						} );
					}
				} );
			}
		}
	}

	// This function adjusts the highlight links based on the 'highlightLinks'
	function onetapHighlightLinks( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'highlight-links' ] ) {
			return;
		}

		// Check if the key is 'highlightLinks'. If it is, the function will proceed with font size adjustments.
		if ( 'highlightLinks' === key ) {
			// Update style for all elements except specific ones
			$( 'a, a *' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( ! accessibilityDataKey ) {
					// Remove the background and color if accessibilityDataKey is 0
					currentStyle = currentStyle.replace( /background:\s*[^;]+;?/, '' );
					currentStyle = currentStyle.replace( /color:\s*[^;]+;?/, '' );
				} else if ( accessibilityDataKey ) {
					// Remove the background and color
					currentStyle = currentStyle.replace( /background:\s*[^;]+;?/, '' );
					currentStyle = currentStyle.replace( /color:\s*[^;]+;?/, '' );

					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					// Handle background
					if ( /background:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing background with the new value
						currentStyle = currentStyle.replace( /background:\s*[^;]+;?/, 'background: #f7ff00 !important;' );
					} else {
						// If background is not present, append it to the style attribute
						currentStyle += ' background: #f7ff00 !important;';
					}

					// Handle color
					if ( /color:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing color with the new value
						currentStyle = currentStyle.replace( /color:\s*[^;]+;?/, 'color: #000 !important;' );
					} else {
						// If color is not present, append it to the style attribute
						currentStyle += ' color: #000 !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );

			// SVG icons inside links are colored through `fill`/`stroke`
			$( 'a svg, a img[src*=".svg" i]' ).not( onetapSkipElements ).each( function() {
				let svgStyle = $( this ).attr( 'style' ) || '';

				// Always remove a previously injected color first so toggling the
				// feature off restores the original icon colors.
				svgStyle = svgStyle.replace( /color:\s*#000\s*!important;?/g, '' );

				if ( accessibilityDataKey ) {
					// Ensure the existing style ends with a semicolon before appending.
					if ( svgStyle.trim() && ! /;$/.test( svgStyle.trim() ) ) {
						svgStyle += ';';
					}
					svgStyle += ' color: #000 !important;';
				}

				svgStyle = svgStyle.trim();
				$( this ).attr( 'style', svgStyle );
			} );
		}
	}

	// This function adjusts the light contrast based on the 'lightContrast'
	function onetapLightContrast( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'light-contrast' ] ) {
			return;
		}

		// Check if the key is 'lightContrast'. If it is, the function will proceed with contrast adjustments.
		if ( 'lightContrast' === key ) {
			// Ensure only this feature box is visibly active
			if ( accessibilityDataKey ) {
				$( '.onetap-box-feature.onetap-dark-contrast' ).removeClass( 'onetap-active' );
				$( '.onetap-box-feature.onetap-light-contrast' ).addClass( 'onetap-active' );
				$( 'body' ).addClass( 'onetap-light-contrast' );
			} else {
				$( '.onetap-box-feature.onetap-light-contrast' ).removeClass( 'onetap-active' );
				$( 'body' ).removeClass( 'onetap-light-contrast' );
			}
		}
	}

	// This function adjusts the dark contrast based on the 'darkContrast'
	function onetapDarkContrast( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'dark-contrast' ] ) {
			return;
		}

		// Check if the key is 'darkContrast'. If it is, the function will proceed with contrast adjustments.
		if ( 'darkContrast' === key ) {
			// Ensure only this feature box is visibly active
			if ( accessibilityDataKey ) {
				$( '.onetap-box-feature.onetap-light-contrast' ).removeClass( 'onetap-active' );
				$( '.onetap-box-feature.onetap-dark-contrast' ).addClass( 'onetap-active' );
				$( 'body' ).addClass( 'onetap-dark-contrast' );
			} else {
				$( '.onetap-box-feature.onetap-dark-contrast' ).removeClass( 'onetap-active' );
				$( '.wp-block-kadence-image' ).css( 'zIndex', '' );
				$( 'body' ).removeClass( 'onetap-dark-contrast' );
			}
		}
	}

	// This function adjusts the invert colors based on the 'invertColors'
	function onetapInvertColors( key, activeBorderValue ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'invert-colors' ] ) {
			return;
		}

		// Check if the key is 'invertColors'. If it is, the function will proceed with font size adjustments.
		if ( 'invertColors' === key ) {
			let invertColors = null;

			// Determine invertColors value based on activeBorderValue
			if ( 1 === activeBorderValue ) {
				invertColors = 'invert(70%) !important;';
			} else if ( 2 === activeBorderValue ) {
				invertColors = 'invert(85%) !important;';
			} else if ( 3 === activeBorderValue ) {
				invertColors = 'invert(100%) !important;';
			} else {
				invertColors = null;
			}

			// Update style for all elements except specific ones
			$( 'html, img' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( 0 === activeBorderValue ) {
					// Remove the filter if activeBorderValue is 0
					currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, '' );
				} else if ( 1 === activeBorderValue || 2 === activeBorderValue || 3 === activeBorderValue ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( /filter:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing filter with the new value
						currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, 'filter: ' + invertColors );
					} else {
						// If filter is not present, append it to the style attribute
						currentStyle += ' filter: ' + invertColors;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the brightness based on the 'brightness'
	function onetapBrightness( key, activeBorderValue ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules.brightness ) {
			return;
		}

		// Check if the key is 'brightness'. If it is, the function will proceed with font size adjustments.
		if ( 'brightness' === key ) {
			let brightness = null;

			// Determine brightness value based on activeBorderValue
			if ( 1 === activeBorderValue ) {
				brightness = 'brightness(50%) !important;';
			} else if ( 2 === activeBorderValue ) {
				brightness = 'brightness(80%) !important;';
			} else if ( 3 === activeBorderValue ) {
				brightness = 'brightness(110%) !important;';
			} else {
				brightness = null;
			}

			// Update style for all elements except specific ones
			$( 'html' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( 0 === activeBorderValue ) {
					// Remove the filter if activeBorderValue is 0
					currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, '' );
				} else if ( 1 === activeBorderValue || 2 === activeBorderValue || 3 === activeBorderValue ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( /filter:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing filter with the new value
						currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, 'filter: ' + brightness );
					} else {
						// If filter is not present, append it to the style attribute
						currentStyle += ' filter: ' + brightness;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the high contrast based on the 'high contrast'
	function onetaphighContrast( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'high-contrast' ] ) {
			return;
		}

		// Check if the key is 'highContrast'. If it is, the function will proceed with contrast adjustments.
		if ( 'highContrast' === key ) {
			// Update style for all elements except specific ones
			$( 'html' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( ! accessibilityDataKey ) {
					// Remove any existing filter
					currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, '' );
				} else {
					// Ensure trailing semicolon before appending/replacing
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					const contrastValue = 'contrast(120%) !important;';
					if ( /filter:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing filter with the new value
						currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, 'filter: ' + contrastValue );
					} else {
						// If filter is not present, append it to the style attribute
						currentStyle += ' filter: ' + contrastValue;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the monochrome based on the 'monochrome'
	function onetapMonochrome( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules.monochrome ) {
			return;
		}

		// Check if the key is 'monochrome'. If it is, the function will proceed with monochrome adjustments.
		if ( 'monochrome' === key ) {
			// Update style for all elements except specific ones
			$( 'html' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( ! accessibilityDataKey ) {
					// Remove any existing filter
					currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, '' );
				} else {
					// Ensure trailing semicolon before appending/replacing
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					const monochromeValue = 'grayscale(100%) !important;';
					if ( /filter:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing filter with the new value
						currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, 'filter: ' + monochromeValue );
					} else {
						// If filter is not present, append it to the style attribute
						currentStyle += ' filter: ' + monochromeValue;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the saturation based on the 'saturation'
	function onetapSaturation( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules.saturation ) {
			return;
		}

		// Check if the key is 'saturation'. If it is, the function will proceed with saturation adjustments.
		if ( 'saturation' === key ) {
			// Update style for all elements except specific ones
			$( 'html' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';

				if ( ! accessibilityDataKey ) {
					// Remove any existing filter
					currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, '' );
				} else {
					// Ensure trailing semicolon before appending/replacing
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					const saturationValue = 'saturate(200%) !important;';
					if ( /filter:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing filter with the new value
						currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, 'filter: ' + saturationValue );
					} else {
						// If filter is not present, append it to the style attribute
						currentStyle += ' filter: ' + saturationValue;
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the reading line based on the 'readingLine'
	function onetapReadingLine( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'reading-line' ] ) {
			return;
		}

		// Check if the key is 'readingLine'. If it is, the function will proceed with font size adjustments.
		if ( 'readingLine' === key ) {
			// Update style for all elements except specific ones
			if ( ! accessibilityDataKey ) {
				$( '.onetap-markup-reading-line' ).removeClass( 'onetap-active' );
			} else if ( accessibilityDataKey ) {
				$( '.onetap-markup-reading-line' ).addClass( 'onetap-active' );
				$( document ).mousemove( function( event ) {
					// Get the X and Y coordinates of the mouse
					const mouseY = event.pageY; // Vertical position

					// Apply the Y position to the 'top' style of the '.onetap-markup-reading-line' element
					$( '.onetap-markup-reading-line' ).css( 'top', mouseY + 'px' );
				} );
			}
		}
	}

	/**
	 * Whether an element can actually take focus.
	 *
	 * Elements that render no box at all - display:none, a collapsed mobile menu,
	 * an inert or aria-hidden subtree - silently swallow .focus(). They have to be
	 * skipped before the next index is worked out, because the index is derived
	 * from document.activeElement: one unfocusable element in the list otherwise
	 * pins activeElement to the previous item and the cycle never advances again.
	 *
	 * @param {Element} element - Candidate element.
	 * @return {boolean} True when focus can land on it.
	 */
	function onetapCanReceiveFocus( element ) {
		if ( element.hasAttribute( 'inert' ) || element.closest( '[inert]' ) ) {
			return false;
		}

		if ( element.closest( '[aria-hidden="true"]' ) ) {
			return false;
		}

		// No layout box: the element itself or one of its ancestors is display:none.
		if ( ! element.getClientRects().length ) {
			return false;
		}

		return 'hidden' !== window.getComputedStyle( element ).visibility;
	}

	// hotkeys-js appends a new handler every time it is called, and the function
	// below runs again on every settings change. Registering once keeps a single
	// keypress from advancing focus once per accumulated registration; the
	// handlers themselves check the body class, so they go quiet when the feature
	// is switched off.
	let onetapHotkeysRegistered = false;

	// This function adjusts the keyboard navigation based on the 'keyboardNavigation'
	function onetapKeyboardNavigation( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'keyboard-navigation' ] ) {
			return;
		}

		// Ensure hotkeys library is loaded.
		if ( typeof hotkeys === 'undefined' ) {
			console.error( 'Hotkeys library is not loaded.' );
			return;
		}

		/**
		 * Initialize hotkeys.
		 * @param {Object} options - Configurable hotkey options.
		 */
		function hotKeys( options ) {
			/** Open popup by hotkey. */
			openInterface( options );

			/** Navigate to next/prev Menu. */
			focusElements( options.hotKeyMenu, 'nav, [role="navigation"]' );

			/** Navigate to next/prev Heading. */
			focusElements( options.hotKeyHeadings, 'h1, h2, h3, h4, h5, h6, [role="heading"]' );

			/** Navigate to next/prev Form. */
			focusElements( options.hotKeyForms, 'form:not([disabled])' );

			/** Navigate to next/prev Button. */
			focusElements( options.hotKeyButtons, 'button:not([disabled]), [role="button"]:not([disabled])' );

			/** Navigate to next/prev Graphic. */
			focusElements( options.hotKeyGraphics, 'img, picture, svg' );

			/**
			 * Enable/Disable controls by pressing Spacebar.
			 * @param {KeyboardEvent} e - The keyboard event object.
			 */
			document.body.onkeydown = function( e ) {
				const keyPressed = e.keyCode || e.charCode || e.which;

				/** Spacebar pressed. */
				// if ( keyPressed === 32 ) {
				// 	spacePressed( e );
				// }
			};
		}

		/**
		 * Set focus on next/prev elements.
		 * @param {string} shortcutKey - Key for triggering focus.
		 * @param {string} selector    - Selector for target elements.
		 */
		function focusElements( shortcutKey, selector ) {
			// Register hotkeys for both forward and backward navigation
			hotkeys( shortcutKey + ', shift+' + shortcutKey, function( event, handler ) {
				// Check if Keyboard Navigation mode is active
				if ( ! document.body.classList.contains( 'onetap-keyboard-navigation' ) ) {
					console.warn( 'Keyboard Navigation is not active.' );
					return;
				}

				// Prevent the default browser behavior
				event.preventDefault();

				// Select all elements based on the provided selector, keeping only the
				// ones focus can actually land on. The toolbar's own heading is part
				// of the widget rather than the page, so it stays out of the list.
				const elements = Array.from( document.querySelectorAll( selector ) ).filter(
					( element ) =>
						! element.classList.contains( 'onetap-heading' ) &&
						onetapCanReceiveFocus( element )
				);

				if ( ! elements.length ) {
					console.warn( `No elements found for selector: ${ selector }` );
					return;
				}

				// Determine navigation direction
				const forward = ! handler.key.startsWith( 'shift+' );

				// Get the currently focused element. When focus is somewhere else
				// entirely, start just outside the list so the first step lands on the
				// first element going forward, or the last one going backward.
				const currentIndex = elements.findIndex( ( el ) => el === document.activeElement );
				let startIndex = currentIndex;

				if ( -1 === currentIndex ) {
					startIndex = forward ? -1 : 0;
				}

				// Walk until focus actually lands. An element can still refuse focus
				// for reasons the filter cannot see, and stopping after one attempt is
				// what used to strand the user on a single element.
				for ( let step = 1; step <= elements.length; step++ ) {
					const offset = forward ? step : -step;
					const nextIndex =
						( ( ( startIndex + offset ) % elements.length ) + elements.length ) %
						elements.length;
					const nextElement = elements[ nextIndex ];

					nextElement.setAttribute( 'tabindex', '-1' ); // Ensure element is focusable
					nextElement.focus();

					if ( document.activeElement === nextElement ) {
						return;
					}
				}

				console.warn( `Could not move focus to any element for selector: ${ selector }` );
			} );
		}

		/**
		 * Placeholder function for opening a popup interface.
		 * @param {Object} options - Configurable options for popup behavior.
		 */
		function openInterface( options ) {
			/* eslint no-unused-vars: "off" */
			// console.log( 'Open interface triggered with options:', options );
		}

		/**
		 * Handle Spacebar pressed for enabling/disabling controls.
		 * @param {Event} event - The keydown event.
		 */
		function spacePressed( event ) {
			event.preventDefault();
			// console.log( 'Spacebar pressed. Toggle controls here.' );
			// Implement your logic for enabling/disabling controls.
		}

		// Check if the key is 'highlightTitles'. If it is, the function will proceed with font size adjustments.
		if ( 'keyboardNavigation' === key ) {
			// Update style for all elements except specific ones
			if ( ! accessibilityDataKey ) {
				$( 'body' ).removeClass( 'onetap-keyboard-navigation' );
			} else if ( accessibilityDataKey ) {
				$( 'body' ).addClass( 'onetap-keyboard-navigation' );
				if ( typeof accessibilityHotkeys === 'undefined' ) {
					console.error( 'accessibilityHotkeys object is undefined.' );
				} else if ( ! onetapHotkeysRegistered ) {
					onetapHotkeysRegistered = true;
					hotKeys( accessibilityHotkeys );
				}
			}
		}
	}

	// This function adjusts the highlight titles based on the 'highlightTitles'
	function onetapHighlightTitles( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'highlight-titles' ] ) {
			return;
		}

		// Check if the key is 'highlightTitles'. If it is, the function will proceed with font size adjustments.
		if ( 'highlightTitles' === key ) {
			// Update style for all elements except specific ones
			if ( ! accessibilityDataKey ) {
				$( 'body' ).removeClass( 'onetap-highlight-titles' );
			} else if ( accessibilityDataKey ) {
				$( 'body' ).addClass( 'onetap-highlight-titles' );
			}
		}
	}

	// This function adjusts the reading mask based on the 'readingmask'
	function onetapReadingMask( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'reading-mask' ] ) {
			return;
		}

		// Check if the key is 'readingmask'. If it is, the function will proceed with font size adjustments.
		if ( 'readingMask' === key ) {
			// Update style for all elements except specific ones
			if ( ! accessibilityDataKey ) {
				$( '.onetap-markup-reading-mask' ).removeClass( 'onetap-active' );
			} else if ( accessibilityDataKey ) {
				$( '.onetap-markup-reading-mask' ).addClass( 'onetap-active' );
				$( document ).mousemove( function( event ) {
					// Get the vertical position of the cursor within the viewport
					const cursorYPosition = event.clientY;

					// Define the height of the focus box (the area that remains visible)
					const focusHeight = 200;
					const halfFocusHeight = focusHeight / 2;

					// Calculate the top mask height, subtracting half of the focus area height
					const topMaskHeight = cursorYPosition - halfFocusHeight;

					// Calculate the bottom mask height based on the remaining space after the focus area
					const bottomMaskHeight = $( window ).height() - cursorYPosition - halfFocusHeight;

					// Apply the calculated height to the top mask
					$( '.onetap-markup-reading-mask.onetap-top' ).css( 'height', topMaskHeight + 'px' );

					// Apply the calculated height to the bottom mask
					$( '.onetap-markup-reading-mask.onetap-bottom' ).css( 'height', bottomMaskHeight + 'px' );
				} );
			}
		}
	}

	// This function adjusts the hide images based on the 'hideImages'
	function onetapHideImages( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'hide-images' ] ) {
			return;
		}

		// Check if the key is 'hideImages'. If it is, the function will proceed with font size adjustments.
		if ( 'hideImages' === key ) {
			// Update style for all elements except specific ones
			$( 'img' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';
				if ( ! accessibilityDataKey ) {
					// Remove the visibility if accessibilityDataKey is 0
					currentStyle = currentStyle.replace( onetapCssVisibilityDecl, ( match, delim ) => delim );
				} else if ( accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( onetapCssVisibilityDecl.test( currentStyle ) ) {
						// If it exists, replace the existing visibility with the new value
						currentStyle = currentStyle.replace( onetapCssVisibilityDecl, ( match, delim ) => delim + ( delim ? ' ' : '' ) + 'visibility: hidden !important;' );
					} else {
						// If visibility is not present, append it to the style attribute
						currentStyle += ' visibility: hidden !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );

			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).each( function() {
				let currentStyle = $( this ).attr( 'style' ) || '';
				if ( ! accessibilityDataKey ) {
					// Remove the background-size if accessibilityDataKey is 0
					currentStyle = currentStyle.replace( /background-size:\s*[^;]+;?/, '' );
				} else if ( accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle.trim() && ! /;$/.test( currentStyle.trim() ) ) {
						currentStyle += ';';
					}

					if ( /background-size:\s*[^;]+;?/.test( currentStyle ) ) {
						// If it exists, replace the existing background-size with the new value
						currentStyle = currentStyle.replace( /background-size:\s*[^;]+;?/, 'background-size: 0 0 !important;' );
					} else {
						// If background-size is not present, append it to the style attribute
						currentStyle += ' background-size: 0 0 !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle = currentStyle.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle );
			} );
		}
	}

	// This function adjusts the highlight all based on the 'highlightAll'
	function onetapHighlightAll( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'highlight-all' ] ) {
			return;
		}

		// Check if the key is 'highlightAll'. If it is, the function will proceed with font size adjustments.
		if ( 'highlightAll' === key ) {
			// Update style for all elements except specific ones
			if ( ! accessibilityDataKey ) {
				$( 'body' ).removeClass( 'onetap-highlight-all' );
			} else if ( accessibilityDataKey ) {
				$( 'body' ).addClass( 'onetap-highlight-all' );
			}
		}
	}

	// Check if Text-to-Speech is supported
	if ( ! ( 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window ) ) {
		$( '.onetap-read-page' ).addClass( 'unsupported-message' );
		$( '.onetap-read-page' ).removeClass( 'onetap-active' );
	}

	$( document ).on( 'mouseleave', '.unsupported-message', function() {
		const $el = $( this );

		// Clear any existing timeout to prevent overlap on repeated hovers
		clearTimeout( $el.data( 'hover-timeout' ) );

		// Add the 'active' class on hover
		$el.addClass( 'active' );

		// Remove the 'active' class after 400 milliseconds
		const timeoutId = setTimeout( function() {
			$el.removeClass( 'active' );
		}, 400 );

		// Store the timeout ID to clear it later if needed
		$el.data( 'hover-timeout', timeoutId );
	} );

	// This function adjusts the read page based on the 'readPage'
	function onetapReadPage( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'read-page' ] ) {
			return;
		}

		// Check if Text-to-Speech is supported
		if ( ! ( 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window ) ) {
			$( '.onetap-read-page' ).addClass( 'unsupported-message' );
			$( '.onetap-read-page' ).removeClass( 'onetap-active' );
			return; // stop further execution.
		}

		// Check if the key is 'readPage'. If it is, the function will proceed with font size adjustments.
		if ( 'readPage' === key ) {
			// Update style for all elements except some form elements
			if ( accessibilityDataKey ) {
				let currentlySpeakingElement = null; // To track the currently highlighted element

				// Event delegation to capture clicks on any element except some form elements
				// Use namespace 'readPage' so we can remove it later
				$( document ).on( 'click.readPage', '*', function( event ) {
					const $clickedElement = $( event.target );

					// Ignore clicks on toggle button - allow it to work normally
					if ( $clickedElement.closest( '.onetap-toggle' ).length > 0 || $clickedElement.is( '.onetap-toggle' ) ) {
						return; // Let the toggle button handler work normally
					}

					const isClickInsideAccessibility = $( event.target ).closest( '.onetap-accessibility' ).length > 0;
					const isClickInsideLanguages = $( event.target ).closest( '.onetap-languages, .onetap-list-of-languages' ).length > 0;

					// If clicking outside the accessibility panel, close accessibility
					if ( ! isClickInsideAccessibility ) {
						onetapAccessibility.removeClass( 'onetap-toggle-open' ).addClass( 'onetap-toggle-close' );
						onetapSetAccessibilityNavInert( false );
						onetapToggleClose.hide( 100 );
					}

					// If clicking outside the language list, close the language list
					if ( ! isClickInsideLanguages ) {
						onetapLanguageList.fadeOut( 350 );
						onetapToggleLanguages.removeClass( 'onetap-active' );
					}

					if ( ! getDataAccessibilityData()[ key ] ) {
						return;
					}

					// Ignore input, textarea, and select elements
					if ( $( this ).is( 'input, textarea, select' ) ) {
						return;
					}

					// Ignore clicks inside onetap plugin elements (panel accessibility and toggle button)
					// Ignore panel accessibility
					if ( $clickedElement.closest( 'nav.onetap-accessibility-plugin, .onetap-plugin-onetap' ).length > 0 ) {
						return;
					}
					// Ignore toggle button container and everything inside it
					if ( $clickedElement.closest( '.onetap-container-toggle' ).length > 0 ) {
						return;
					}

					// Now we will handle reading; stop propagation to avoid interfering with other handlers
					event.stopPropagation();

					// Get only the direct text from the clicked element
					const textToSpeak = $( this ).text().trim();

					// Check if the text is not empty
					if ( textToSpeak.length > 0 ) {
						// Stop any ongoing speech before continuing
						window.speechSynthesis.cancel();

						// Move the onetap-highlight to the new element
						if ( currentlySpeakingElement ) {
							$( currentlySpeakingElement ).removeClass( 'onetap-highlight' ); // Remove onetap-highlight from the previous element
						}
						$( this ).addClass( 'onetap-highlight' ); // Add onetap-highlight to the newly clicked element
						currentlySpeakingElement = this; // Store the currently highlighted element

						// Get the selected language from localStorage
						const accessibilityData = getDataAccessibilityData();
						const selectedLanguage = accessibilityData?.information?.language || 'en';

						// Map language codes to BCP 47 format for Web Speech API
						const languageMap = {
							en: 'en-US',
							de: 'de-DE',
							es: 'es-ES',
							fr: 'fr-FR',
							it: 'it-IT',
							pl: 'pl-PL',
							se: 'sv-SE',
							fi: 'fi-FI',
							pt: 'pt-PT',
							'pt-br': 'pt-BR',
							ro: 'ro-RO',
							si: 'sl-SI',
							sk: 'sk-SK',
							nl: 'nl-NL',
							dk: 'da-DK',
							gr: 'el-GR',
							cz: 'cs-CZ',
							hu: 'hu-HU',
							lt: 'lt-LT',
							lv: 'lv-LV',
							ee: 'et-EE',
							hr: 'hr-HR',
							ie: 'ga-IE',
							bg: 'bg-BG',
							no: 'nb-NO',
							tr: 'tr-TR',
							id: 'id-ID',
							ja: 'ja-JP',
							ko: 'ko-KR',
							zh: 'zh-CN',
							ar: 'ar-SA',
							ru: 'ru-RU',
							hi: 'hi-IN',
							uk: 'uk-UA',
							sr: 'sr-RS',
							gb: 'en-GB',
							ir: 'fa-IR',
							il: 'he-IL',
							mk: 'mk-MK',
							th: 'th-TH',
							vn: 'vi-VN',
							bn: 'bn-BD',
							tl: 'fil-PH',
							ur: 'ur-PK',
						};

						// Get the BCP 47 language code, fallback to 'en-US' if not found
						const speechLanguage = languageMap[ selectedLanguage ] || 'en-US';

						// Use Web Speech API to convert the text to speech
						const speech = new SpeechSynthesisUtterance( textToSpeak );

						// Set the language for speech synthesis
						speech.lang = speechLanguage;

						// Remove onetap-highlight when speech ends
						speech.onend = function() {
							$( currentlySpeakingElement ).removeClass( 'onetap-highlight' );
							currentlySpeakingElement = null; // Reset the highlighted element
						};

						window.speechSynthesis.speak( speech );
					}

					// Prevent the default action if the clicked element is a link
					// if ( $( this ).is( 'a' ) ) {
					// 	event.preventDefault();
					// }
				} );
			} else {
				// When readPage is disabled, remove the event handler and stop any ongoing speech
				$( document ).off( 'click.readPage', '*' );
				window.speechSynthesis.cancel();

				// Remove highlight from any currently speaking element
				$( '.onetap-highlight' ).removeClass( 'onetap-highlight' );
			}
		}
	}

	// This function adjusts the mute sounds based on the 'muteSounds'
	function onetapMuteSounds( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'mute-sounds' ] ) {
			return;
		}

		// Check if the key is 'muteSounds'.
		if ( 'muteSounds' === key ) {
			if ( ! accessibilityDataKey ) {
				// Unmute all video and audio
				$( 'audio, video' ).not( onetapSkipElements ).each( function() {
					$( this ).prop( 'muted', false );
				} );

				// Unmute all YouTube iframes
				$( 'iframe[src*="youtube.com"]' ).each( function() {
					const src = $( this ).attr( 'src' );
					if ( src.includes( 'mute=1' ) ) {
						$( this ).attr( 'src', src.replace( 'mute=1', '' ) );
					}
				} );
			} else if ( accessibilityDataKey ) {
				// Mute all video and audio
				$( 'audio, video' ).not( onetapSkipElements ).each( function() {
					$( this ).prop( 'muted', true );
				} );

				// Mute all YouTube iframes
				$( 'iframe[src*="youtube.com"]' ).each( function() {
					const src = $( this ).attr( 'src' );
					if ( ! src.includes( 'mute=1' ) ) {
						$( this ).attr( 'src', src + ( src.includes( '?' ) ? '&' : '?' ) + 'mute=1' );
					}
				} );
			}
		}
	}

	// A GIF cannot be paused, so the only way to stop one is to replace it with a
	// still picture of its first frame, drawn onto a canvas. The original source
	// is parked on the element so switching the feature back off can restore it.
	function onetapFreezeAnimatedGifs() {
		$( 'img' ).not( onetapSkipElements ).each( function() {
			const image = this;
			const source = image.getAttribute( 'src' ) || '';

			// A GIF is recognised by its extension or, when it is inlined, by the
			// media type it declares. An address that hides the format behind a
			// query string cannot be judged from here and is left alone.
			const isGif = /\.gif(\?|#|$)/i.test( source ) || /^data:image\/gif[;,]/i.test( source );

			if ( image.hasAttribute( 'data-onetap-animated-src' ) || ! isGif ) {
				return;
			}

			const freeze = function() {
				// A picture that never loaded has nothing to draw.
				if ( ! image.naturalWidth || ! image.naturalHeight ) {
					return;
				}

				const canvas = document.createElement( 'canvas' );
				canvas.width = image.naturalWidth;
				canvas.height = image.naturalHeight;

				try {
					canvas.getContext( '2d' ).drawImage( image, 0, 0 );

					// Reading back a canvas that holds a picture from another
					// domain throws, and there is no way around that from here.
					const stillFrame = canvas.toDataURL();

					image.setAttribute( 'data-onetap-animated-src', source );

					// A srcset would override the still frame we just set.
					if ( image.getAttribute( 'srcset' ) ) {
						image.setAttribute( 'data-onetap-animated-srcset', image.getAttribute( 'srcset' ) );
						image.removeAttribute( 'srcset' );
					}

					image.setAttribute( 'src', stillFrame );
				} catch ( error ) {
					// Cross-origin GIF: leave it playing rather than break it.
				}
			};

			if ( image.complete ) {
				freeze();
			} else {
				image.addEventListener( 'load', freeze, { once: true } );
			}
		} );
	}

	// Put the moving pictures back.
	function onetapRestoreAnimatedGifs() {
		$( 'img[data-onetap-animated-src]' ).each( function() {
			const image = this;
			const srcset = image.getAttribute( 'data-onetap-animated-srcset' );

			image.setAttribute( 'src', image.getAttribute( 'data-onetap-animated-src' ) );
			image.removeAttribute( 'data-onetap-animated-src' );

			if ( null !== srcset ) {
				image.setAttribute( 'srcset', srcset );
				image.removeAttribute( 'data-onetap-animated-srcset' );
			}
		} );
	}

	// Video that starts on its own is exactly what WCAG 2.2.2 asks to be
	// stoppable. Playback the visitor started themselves is left alone.
	function onetapPauseAutoplayingMedia() {
		$( 'video[autoplay]' ).not( onetapSkipElements ).each( function() {
			const video = this;

			if ( video.hasAttribute( 'data-onetap-autoplay-paused' ) ) {
				return;
			}

			// Remember whether it was actually running, so that turning the
			// feature off does not start a video that was already stopped.
			video.setAttribute( 'data-onetap-autoplay-paused', video.paused ? 'idle' : 'playing' );
			video.autoplay = false;

			if ( video.loop ) {
				video.loop = false;
				video.setAttribute( 'data-onetap-had-loop', '1' );
			}

			video.pause();
		} );

		// Embedded players live on another domain and cannot be reached, but
		// reloading them without the autoplay flag stops them all the same.
		$( 'iframe[src*="youtube.com"], iframe[src*="youtube-nocookie.com"], iframe[src*="player.vimeo.com"]' ).each( function() {
			const frame = this;
			const source = frame.getAttribute( 'src' ) || '';

			if ( frame.hasAttribute( 'data-onetap-autoplay-src' ) || ! /[?&]autoplay=1/i.test( source ) ) {
				return;
			}

			frame.setAttribute( 'data-onetap-autoplay-src', source );
			frame.setAttribute( 'src', source.replace( /([?&])autoplay=1/gi, '$1autoplay=0' ) );
		} );
	}

	// Hand playback back to the page.
	function onetapResumeAutoplayingMedia() {
		$( 'video[data-onetap-autoplay-paused]' ).each( function() {
			const video = this;
			const wasPlaying = 'playing' === video.getAttribute( 'data-onetap-autoplay-paused' );

			video.autoplay = true;
			video.removeAttribute( 'data-onetap-autoplay-paused' );

			if ( video.hasAttribute( 'data-onetap-had-loop' ) ) {
				video.loop = true;
				video.removeAttribute( 'data-onetap-had-loop' );
			}

			if ( wasPlaying ) {
				// Browsers refuse to start unmuted video without a gesture, and
				// a refusal here is not something the visitor needs to hear about.
				const resumed = video.play();
				if ( resumed && 'function' === typeof resumed.catch ) {
					resumed.catch( function() {} );
				}
			}
		} );

		$( 'iframe[data-onetap-autoplay-src]' ).each( function() {
			const frame = this;
			frame.setAttribute( 'src', frame.getAttribute( 'data-onetap-autoplay-src' ) );
			frame.removeAttribute( 'data-onetap-autoplay-src' );
		} );
	}

	// This function adjusts the stop animations based on the 'stopAnimations'
	function onetapStopAnimations( key, accessibilityDataKey ) {
		// if value off, return.
		if ( 'off' === onetapAjaxObject.showModules[ 'stop-animations' ] ) {
			return;
		}

		// Check if the key is 'stopAnimations'. If it is, the function will proceed with font size adjustments.
		if ( 'stopAnimations' === key ) {
			// A stylesheet only governs CSS animation. GIFs and video that start
			// by themselves keep moving no matter what is done to the styles, so
			// they are handled separately.
			if ( accessibilityDataKey ) {
				onetapFreezeAnimatedGifs();
				onetapPauseAutoplayingMedia();
			} else {
				onetapRestoreAnimatedGifs();
				onetapResumeAutoplayingMedia();
			}

			// Update style for all elements except specific ones
			$( '*' ).not( onetapSkipElements ).each( function() {
				// Transition.
				let currentStyle1 = $( this ).attr( 'style' ) || '';
				if ( ! accessibilityDataKey ) {
					// Remove the background and color if accessibilityDataKey is 0
					currentStyle1 = currentStyle1.replace( /transition:\s*[^;]+;?/, '' );
				} else if ( accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle1.trim() && ! /;$/.test( currentStyle1.trim() ) ) {
						currentStyle1 += ';';
					}

					if ( /transition:\s*[^;]+;?/.test( currentStyle1 ) ) {
						// If it exists, replace the existing transition with the new value
						currentStyle1 = currentStyle1.replace( /transition:\s*[^;]+;?/, 'transition: none !important;' );
					} else {
						// If transition is not present, append it to the style attribute
						currentStyle1 += ' transition: none !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle1 = currentStyle1.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle1 );

				// Animations.
				let currentStyle2 = $( this ).attr( 'style' ) || '';
				if ( ! accessibilityDataKey ) {
					// Remove the background and color if accessibilityDataKey is 0
					currentStyle2 = currentStyle2.replace( /animation:\s*[^;]+;?/, '' );
				} else if ( accessibilityDataKey ) {
					// Check if the element has a style attribute and if it ends with a semicolon
					if ( currentStyle2.trim() && ! /;$/.test( currentStyle2.trim() ) ) {
						currentStyle2 += ';';
					}

					if ( /animation:\s*[^;]+;?/.test( currentStyle2 ) ) {
						// If it exists, replace the existing animation with the new value
						currentStyle2 = currentStyle2.replace( /animation:\s*[^;]+;?/, 'animation: none !important;' );
					} else {
						// If animation is not present, append it to the style attribute
						currentStyle2 += ' animation: none !important;';
					}
				}

				// Trim any extra spaces and ensure there's no trailing space
				currentStyle2 = currentStyle2.trim();

				// Set the updated style attribute back to the element
				$( this ).attr( 'style', currentStyle2 );
			} );
		}
	}

	// List of onetapGetTlements and their keys
	const onetapGetTlements = [
		{ selector: '.onetap-bigger-text .onetap-btn', key: 'biggerText' },
		{ selector: '.onetap-highlight-links', key: 'highlightLinks' },
		{ selector: '.onetap-line-height .onetap-btn', key: 'lineHeight' },
		{ selector: '.onetap-readable-font', key: 'readableFont' },
		{ selector: '.onetap-cursor', key: 'cursor' },
		{ selector: '.onetap-text-magnifier', key: 'textMagnifier' },
		{ selector: '.onetap-dyslexic-font', key: 'dyslexicFont' },
		{ selector: '.onetap-align-center', key: 'alignCenter' },
		{ selector: '.onetap-letter-spacing', key: 'letterSpacing' },
		{ selector: '.onetap-font-weight', key: 'fontWeight' },
		{ selector: '.onetap-dark-contrast', key: 'darkContrast' },
		{ selector: '.onetap-light-contrast', key: 'lightContrast' },
		{ selector: '.onetap-high-contrast', key: 'highContrast' },
		{ selector: '.onetap-monochrome', key: 'monochrome' },
		{ selector: '.onetap-saturation', key: 'saturation' },
		{ selector: '.onetap-reading-line', key: 'readingLine' },
		{ selector: '.onetap-reading-mask', key: 'readingMask' },
		{ selector: '.onetap-read-page', key: 'readPage' },
		{ selector: '.onetap-keyboard-navigation', key: 'keyboardNavigation' },
		{ selector: '.onetap-hide-images', key: 'hideImages' },
		{ selector: '.onetap-mute-sounds', key: 'muteSounds' },
		{ selector: '.onetap-highlight-titles', key: 'highlightTitles' },
		{ selector: '.onetap-highlight-all', key: 'highlightAll' },
		{ selector: '.onetap-stop-animations', key: 'stopAnimations' },
	];

	// Utility function to update class based on current value
	function onetapToggleLevelClass( $element, currentValue ) {
		const levels = [ 'onetap-lv1', 'onetap-lv2', 'onetap-lv3' ];
		$element.removeClass( levels.join( ' ' ) );

		if ( currentValue >= 1 && currentValue <= 3 ) {
			$element.addClass( levels[ currentValue - 1 ] );
		}
	}

	// Toggles the 'onetap-active' class on the provided element
	function onetapToggleActiveClass( $element ) {
		$element.toggleClass( 'onetap-active ' );
	}

	// Utility function to handle click events
	let activeStagedValue = 0;
	function onetapHandleClick( $element, key, accessibilityData, useActiveBorder ) {
		$element.on( 'click', function() {
			accessibilityData = getDataAccessibilityData();

			// Check if accessibilityData is null or undefined
			if ( ! accessibilityData ) {
				console.warn( 'Accessibility data is null or undefined. Initializing with default data.' );
				accessibilityData = onetapAccessibilityDefault;
				localStorage.setItem( onetapLocalStorage, JSON.stringify( accessibilityData ) );
			}

			const kebabKey = key.replace( /([a-z])([A-Z])/g, '$1-$2' ).toLowerCase();
			const elementClassName = this.className || '';
			const selector = elementClassName;

			// Toggle inactive state based on current active class presence
			if ( selector.split( /\s+/ ).includes( 'onetap-active' ) ) {
				$( this ).addClass( 'onetap-inactive' );
			} else {
				$( this ).removeClass( 'onetap-inactive' );
			}

			// Determine button type and level from class list
			const classListArray = selector.split( /\s+/ ).filter( Boolean );
			const hasIncreaseClass = classListArray.includes( 'onetap-btn-increase' );
			const hasDecreaseClass = classListArray.includes( 'onetap-btn-decrease' );
			const levelMatch = selector.match( /\bonetap-lv(\d)\b/ );

			if ( useActiveBorder ) {
				// Ensure activeBorders exists
				if ( ! accessibilityData.activeBorders ) {
					accessibilityData.activeBorders = {};
				}
				const previousLevel = accessibilityData.activeBorders[ key ] || 0;
				let nextLevel = previousLevel;
				const maxStep = ( key === 'biggerText' || key === 'lineHeight' ) ? 10 : 3;
				const minStep = ( key === 'biggerText' || key === 'lineHeight' ) ? -10 : 0;
				if ( hasIncreaseClass ) {
					nextLevel = Math.min( previousLevel + 1, maxStep );
				} else if ( hasDecreaseClass ) {
					nextLevel = Math.max( previousLevel - 1, minStep );
				}

				activeStagedValue = accessibilityData.activeBorders[ key ] = nextLevel;
				accessibilityData[ key ] = nextLevel !== 0;

				onetapToggleLevelClass( $element, nextLevel );

				if ( hasIncreaseClass || hasDecreaseClass ) {
					if ( key === 'biggerText' ) {
						onetapBiggerText( key, nextLevel, this );
					} else if ( key === 'lineHeight' ) {
						onetapLineHeight( key, nextLevel, this );
					} else if ( key === 'invertColors' ) {
						onetapInvertColors( key, nextLevel );
					} else if ( key === 'brightness' ) {
						onetapBrightness( key, nextLevel );
					} else if ( key === 'saturation' ) {
						onetapSaturation( key, nextLevel );
					} else if ( key === 'textAlign' ) {
						onetapTextAlign( key, nextLevel );
					}
				} else {
					return;
				}

				// Changes attr aria pressed
				if ( 0 !== activeStagedValue ) {
					$( '.onetap-' + kebabKey ).attr( 'aria-pressed', true );
				} else {
					$( '.onetap-' + kebabKey ).attr( 'aria-pressed', false );
				}
			} else {
				if ( 'alignCenter' === key ) {
					if ( ! accessibilityData.alignLeft && ! accessibilityData.alignCenter && ! accessibilityData.alignRight ) {
						accessibilityData.alignLeft = true;
					} else if ( accessibilityData.alignLeft ) {
						accessibilityData.alignLeft = false;
						accessibilityData.alignCenter = true;
					} else if ( accessibilityData.alignCenter ) {
						accessibilityData.alignCenter = false;
						accessibilityData.alignRight = true;
					} else {
						accessibilityData.alignRight = false;
					}
				} else {
					accessibilityData[ key ] = ! accessibilityData[ key ];
				}

				if ( 'darkContrast' === key ) {
					// If dark contrast is activated, deactivate light contrast
					accessibilityData.lightContrast = false;
					$( 'body' ).removeClass( 'onetap-light-contrast' );
				} else if ( 'lightContrast' === key ) {
					// If light contrast is activated, deactivate dark contrast
					accessibilityData.darkContrast = false;
					$( 'body' ).removeClass( 'onetap-dark-contrast' );
				} else {
					accessibilityData.darkContrast = false;
					accessibilityData.lightContrast = false;
				}

				if ( 'alignCenter' !== key ) {
					onetapToggleActiveClass( $element, accessibilityData[ key ] );
				}
				onetapHighlightLinks( key, accessibilityData[ key ] );
				onetapReadableFont( key, accessibilityData[ key ] );
				onetapCursor( key, accessibilityData[ key ] );
				onetapTextMagnifier( key, accessibilityData[ key ] );
				onetapDyslexicFont( key, accessibilityData[ key ] );
				onetapApplyTextAlign( key, accessibilityData );
				onetapLetterSpacing( key, accessibilityData[ key ] );
				onetapFontWeight( key, accessibilityData[ key ] );
				onetapDarkContrast( key, accessibilityData[ key ] );
				onetapLightContrast( key, accessibilityData[ key ] );
				onetaphighContrast( key, accessibilityData[ key ] );
				onetapMonochrome( key, accessibilityData[ key ] );
				onetapSaturation( key, accessibilityData[ key ] );
				onetapReadingLine( key, accessibilityData[ key ] );
				onetapReadingMask( key, accessibilityData[ key ] );
				onetapReadPage( key, accessibilityData[ key ] );
				onetapKeyboardNavigation( key, accessibilityData[ key ] );
				onetapHideImages( key, accessibilityData[ key ] );
				onetapMuteSounds( key, accessibilityData[ key ] );
				onetapHighlightTitles( key, accessibilityData[ key ] );
				onetapHighlightAll( key, accessibilityData[ key ] );
				onetapStopAnimations( key, accessibilityData[ key ] );

				// Changes attr aria pressed
				if ( 'alignCenter' !== key ) {
					if ( accessibilityData[ key ] ) {
						$( '.onetap-' + kebabKey ).attr( 'aria-pressed', true );
					} else {
						$( '.onetap-' + kebabKey ).attr( 'aria-pressed', false );
					}
				}
			}

			localStorage.setItem( onetapLocalStorage, JSON.stringify( accessibilityData ) );
		} );
	}

	// Initialize functionality for multiple onetapGetTlements
	function onetapInitAccessibilityHandlers( accessibilityData ) {
		onetapGetTlements.forEach( ( { selector, key } ) => {
			const $element = $( `nav.onetap-accessibility.onetap-plugin-onetap .onetap-accessibility-settings ${ selector }` );
			if ( $element.length ) {
				// Use activeBorder for some keys, otherwise, just toggle true/false
				const useActiveBorder = ! [
					'highlightLinks',
					'readableFont',
					'cursor',
					'textMagnifier',
					'dyslexicFont',
					'alignCenter',
					'letterSpacing',
					'fontWeight',
					'darkContrast',
					'lightContrast',
					'highContrast',
					'monochrome',
					'saturation',
					'readingLine',
					'readingMask',
					'readPage',
					'keyboardNavigation',
					'hideImages',
					'muteSounds',
					'highlightTitles',
					'highlightAll',
					'stopAnimations',
				].includes( key );

				onetapHandleClick( $element, key, accessibilityData, useActiveBorder );
			}
		} );
	}

	// Handles the application of accessibility features on elements based on user settings
	function handleAccessibilityFeatures() {
		const accessibilityData = getDataAccessibilityData();

		// Check if accessibilityData is null or undefined
		if ( ! accessibilityData ) {
			console.warn( 'Accessibility data is null or undefined in handleAccessibilityFeatures. Initializing with default data.' );
			const defaultData = onetapAccessibilityDefault;
			localStorage.setItem( onetapLocalStorage, JSON.stringify( defaultData ) );
			return; // Exit early if no valid data
		}

		onetapGetTlements.forEach( ( { selector, key } ) => {
			const $element = $( `nav.onetap-accessibility.onetap-plugin-onetap .onetap-accessibility-settings ${ selector }` );
			const kebabKey = key.replace( /([a-z])([A-Z])/g, '$1-$2' ).toLowerCase();
			if ( $element.length && accessibilityData[ key ] !== undefined ) {
				const useActiveBorder = ! [
					'highlightLinks',
					'readableFont',
					'cursor',
					'textMagnifier',
					'dyslexicFont',
					'alignCenter',
					'letterSpacing',
					'fontWeight',
					'darkContrast',
					'lightContrast',
					'highContrast',
					'monochrome',
					'saturation',
					'readingLine',
					'readingMask',
					'readPage',
					'keyboardNavigation',
					'hideImages',
					'muteSounds',
					'highlightTitles',
					'highlightAll',
					'stopAnimations',
				].includes( key );

				if ( useActiveBorder ) {
					// Ensure activeBorders exists
					if ( ! accessibilityData.activeBorders ) {
						accessibilityData.activeBorders = {};
					}
					if ( accessibilityData.activeBorders[ key ] !== undefined ) {
						onetapToggleLevelClass( $element, accessibilityData.activeBorders[ key ] );
						if ( 0 !== accessibilityData.activeBorders[ key ] ) {
							onetapBiggerText( key, accessibilityData.activeBorders[ key ], selector );
							onetapLineHeight( key, accessibilityData.activeBorders[ key ], selector );
							onetapInvertColors( key, accessibilityData.activeBorders[ key ] );
							onetapBrightness( key, accessibilityData.activeBorders[ key ] );
							onetapSaturation( key, accessibilityData.activeBorders[ key ] );
							onetapTextAlign( key, accessibilityData.activeBorders[ key ] );

							// Changes attr aria pressed
							if ( accessibilityData.activeBorders[ key ] ) {
								$( '.onetap-' + kebabKey ).attr( 'aria-pressed', true );
							} else {
								$( '.onetap-' + kebabKey ).attr( 'aria-pressed', false );
							}
						}
					}
				} else if ( accessibilityData[ key ] !== undefined ) {
					if ( 'alignCenter' === key ) {
						onetapApplyTextAlign( key, accessibilityData );
					} else if ( accessibilityData[ key ] !== undefined && accessibilityData[ key ] ) {
						onetapToggleActiveClass( $element, accessibilityData[ key ] );
						onetapHighlightLinks( key, accessibilityData[ key ] );
						onetapReadableFont( key, accessibilityData[ key ] );
						onetapCursor( key, accessibilityData[ key ] );
						onetapTextMagnifier( key, accessibilityData[ key ] );
						onetapDyslexicFont( key, accessibilityData[ key ] );
						onetapLetterSpacing( key, accessibilityData[ key ] );
						onetapFontWeight( key, accessibilityData[ key ] );
						onetapDarkContrast( key, accessibilityData[ key ] );
						onetapLightContrast( key, accessibilityData[ key ] );
						onetaphighContrast( key, accessibilityData[ key ] );
						onetapMonochrome( key, accessibilityData[ key ] );
						onetapSaturation( key, accessibilityData[ key ] );
						onetapReadingLine( key, accessibilityData[ key ] );
						onetapReadingMask( key, accessibilityData[ key ] );
						onetapReadPage( key, accessibilityData[ key ] );
						onetapKeyboardNavigation( key, accessibilityData[ key ] );
						onetapHideImages( key, accessibilityData[ key ] );
						onetapMuteSounds( key, accessibilityData[ key ] );
						onetapHighlightTitles( key, accessibilityData[ key ] );
						onetapHighlightAll( key, accessibilityData[ key ] );
						onetapStopAnimations( key, accessibilityData[ key ] );

						// Changes attr aria pressed
						if ( accessibilityData[ key ] ) {
							$( '.onetap-' + kebabKey ).attr( 'aria-pressed', true );
						} else {
							$( '.onetap-' + kebabKey ).attr( 'aria-pressed', false );
						}
					}
				}
			}
		} );

		// Initialize handlers
		onetapInitAccessibilityHandlers( accessibilityData );
	}
	handleAccessibilityFeatures();

	// Updates the 'data-on' and 'data-off' attributes
	updateLabelModeSwitch();

	// Reset settings
	$( document ).on( 'click', 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-reset-settings button', function( event ) {
		event.stopPropagation(); // Ensure this doesn't trigger auto-close

		// Select all elements with the class .onetap-box-feature
		$( 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-box-feature' ).each( function() {
			// Remove specified classes
			$( this ).removeClass( 'onetap-lv1 onetap-lv2 onetap-lv3 onetap-active' );
		} );

		$( 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-align-center' )
			.removeClass( 'onetap-text-align-mode-left onetap-text-align-mode-center onetap-text-align-mode-right' )
			.addClass( 'onetap-text-align-mode-off' );

		const resetLang = getDataAccessibilityData()?.information?.language || 'en';
		const resetLangFeatures = onetapAjaxObject?.languages?.[ resetLang ]?.features;
		if ( resetLangFeatures?.alignDefault ) {
			$( 'nav.onetap-accessibility .onetap-features .onetap-align-center .onetap-title > span' ).text( resetLangFeatures.alignDefault );
		}

		// Check if the localStorage item exists
		if ( localStorage.getItem( onetapLocalStorage ) ) {
			// Parse the existing localStorage item
			const currentSettings = JSON.parse( localStorage.getItem( onetapLocalStorage ) );

			// Check if any of the specified values are true
			const hasActiveSettings =
				currentSettings.dynamicFeatureSet.visionImpairedMode ||
				currentSettings.dynamicFeatureSet.seizureSafeProfileMode ||
				currentSettings.dynamicFeatureSet.adhdFriendlyMode ||
				currentSettings.dynamicFeatureSet.blindnessMode ||
				currentSettings.dynamicFeatureSet.epilepsySafeMode ||
				currentSettings.biggerText ||
				currentSettings.highlightLinks ||
				currentSettings.lineHeight ||
				currentSettings.readableFont ||
				currentSettings.cursor ||
				currentSettings.textMagnifier ||
				currentSettings.dyslexicFont ||
				currentSettings.alignLeft ||
				currentSettings.alignCenter ||
				currentSettings.alignRight ||
				currentSettings.letterSpacing ||
				currentSettings.fontWeight ||
				currentSettings.darkContrast ||
				currentSettings.lightContrast ||
				currentSettings.highContrast ||
				currentSettings.monochrome ||
				currentSettings.saturation ||
				currentSettings.readingLine ||
				currentSettings.readingMask ||
				currentSettings.readPage ||
				currentSettings.keyboardNavigation ||
				currentSettings.hideImages ||
				currentSettings.muteSounds ||
				currentSettings.highlightTitles ||
				currentSettings.highlightAll ||
				currentSettings.stopAnimations ||
				currentSettings.information.language;

			if ( currentSettings.textMagnifier ) {
				$( document ).off( 'mousemove' );
			}

			if ( hasActiveSettings ) {
				// Remove the 'onetap-active' class from all country flag images
				$( 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-languages .onetap-icon img' ).removeClass( 'onetap-active' );

				// Add the 'onetap-active' class to the image whose data-language matches the selected language
				$( 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-languages .onetap-icon img[data-language="en"]' ).addClass( 'onetap-active' );

				// Remove the 'onetap-active' class from all country flag images
				$( 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-languages .onetap-icon img' ).removeClass( 'onetap-active' );

				// Add the 'onetap-active' class to the image whose data-language matches the selected language
				$( 'nav.onetap-accessibility .onetap-accessibility-settings .onetap-languages .onetap-icon img[data-language="' + onetapAjaxObject.getSettings.language + '"]' ).addClass( 'onetap-active' );

				// Reset language
				onetapUpdateContentBasedOnLanguage( onetapAjaxObject.getSettings.language );

				// The function to apply language translations to feature boxes
				applyLanguageTranslationsToFeatures( onetapAjaxObject.getSettings.language );
				applyLanguageTranslationsToFunctionalFeatures( onetapAjaxObject.getSettings.language );

				// Remove localStorage item if any value is true
				localStorage.removeItem( onetapLocalStorage );

				// Create a new localStorage item with default values
				localStorage.setItem( onetapLocalStorage, JSON.stringify( onetapAccessibilityDefault ) );

				// Reset Mode Preset Toggle.
				const checkboxPresetToggle = [
					'#onetap-box-vision-impaired-mode',
					'#onetap-box-seizure-safe-profile',
					'#onetap-box-adhd-friendly-mode',
					'#onetap-box-blindness-mode',
					'#onetap-box-epilepsy-safe-mode',
				];

				checkboxPresetToggle.forEach( ( id ) => {
					const checkbox = document.querySelector( id );
					if ( checkbox ) {
						checkbox.checked = false;
						$( id ).attr( 'aria-checked', false );
					}
				} );

				// Remove the active class for the currently active preset mode.
				$( 'nav.onetap-accessibility.onetap-plugin-onetap .onetap-functional-feature' ).removeClass( 'onetap-active' );

				// Remove style inline
				$( '*' ).not( onetapSkipElements ).each( function() {
					let currentStyle = $( this ).attr( 'style' ) || '';

					// Reset (Bigger Text): restore original inline font-size if available, otherwise remove only plugin-added font-size
					const originalInlineFontSize = this.dataset && this.dataset.originalInlineFontSize ? this.dataset.originalInlineFontSize : '';
					if ( originalInlineFontSize ) {
						const restoreFontSize = `font-size: ${ originalInlineFontSize };`;
						if ( /font-size:\s*[^;]+;?/.test( currentStyle ) ) {
							currentStyle = currentStyle.replace( /font-size:\s*[^;]+;?/, restoreFontSize );
						} else {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreFontSize;
						}
					} else {
						// Only remove font-size if it was added by the plugin (has !important)
						// Preserve original font-size values like clamp() that don't have !important
						const fontSizeMatch = currentStyle.match( /font-size:\s*([^;]+);?/ );
						if ( fontSizeMatch && fontSizeMatch[ 1 ] ) {
							const fontSizeValue = fontSizeMatch[ 1 ].trim();
							// If font-size has !important, it was added by the plugin, so remove it
							if ( fontSizeValue.includes( '!important' ) ) {
								currentStyle = currentStyle.replace( /font-size:\s*[^;]+;?/, '' );
							}
							// If font-size doesn't have !important, it's likely original, so preserve it (do nothing)
						}
					}

					// Reset (Line Height): restore original inline line-height if available, otherwise remove only plugin-added line-height
					const originalInlineLineHeight = this.dataset && this.dataset.originalInlineLineHeight ? this.dataset.originalInlineLineHeight : '';
					if ( originalInlineLineHeight ) {
						const restoreLineHeight = `line-height: ${ originalInlineLineHeight };`;
						if ( /line-height:\s*[^;]+;?/.test( currentStyle ) ) {
							currentStyle = currentStyle.replace( /line-height:\s*[^;]+;?/, restoreLineHeight );
						} else {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreLineHeight;
						}
					} else {
						// Only remove line-height if it was added by the plugin (has !important)
						// Preserve original line-height values that don't have !important
						const lineHeightMatch = currentStyle.match( /line-height:\s*([^;]+);?/ );
						if ( lineHeightMatch && lineHeightMatch[ 1 ] ) {
							const lineHeightValue = lineHeightMatch[ 1 ].trim();
							// If line-height has !important, it was added by the plugin, so remove it
							if ( lineHeightValue.includes( '!important' ) ) {
								currentStyle = currentStyle.replace( /line-height:\s*[^;]+;?/, '' );
							}
							// If line-height doesn't have !important, it's likely original, so preserve it (do nothing)
						}
					}

					// Reset (Letter Spacing): restore original inline letter-spacing if available, otherwise remove only plugin-added letter-spacing
					const originalInlineLetterSpacing = this.dataset && this.dataset.originalInlineLetterSpacing ? this.dataset.originalInlineLetterSpacing : '';
					if ( originalInlineLetterSpacing ) {
						const restoreLetterSpacing = `letter-spacing: ${ originalInlineLetterSpacing };`;
						if ( /letter-spacing:\s*[^;]+;?/.test( currentStyle ) ) {
							currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/, restoreLetterSpacing );
						} else {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreLetterSpacing;
						}
					} else {
						// Only remove letter-spacing if it was added by the plugin (has !important)
						// Preserve original letter-spacing values that don't have !important
						const letterSpacingMatch = currentStyle.match( /letter-spacing:\s*([^;]+);?/ );
						if ( letterSpacingMatch && letterSpacingMatch[ 1 ] ) {
							const letterSpacingValue = letterSpacingMatch[ 1 ].trim();
							// If letter-spacing has !important, it was added by the plugin, so remove it
							if ( letterSpacingValue.includes( '!important' ) ) {
								currentStyle = currentStyle.replace( /letter-spacing:\s*[^;]+;?/, '' );
							}
							// If letter-spacing doesn't have !important, it's likely original, so preserve it (do nothing)
						}
					}

					// Reset (Font Weight): restore original inline font-weight if available, otherwise remove only plugin-added font-weight
					const originalInlineFontWeight = this.dataset && this.dataset.originalInlineFontWeight ? this.dataset.originalInlineFontWeight : '';
					if ( originalInlineFontWeight ) {
						const restoreFontWeight = `font-weight: ${ originalInlineFontWeight };`;
						if ( /font-weight:\s*[^;]+;?/.test( currentStyle ) ) {
							currentStyle = currentStyle.replace( /font-weight:\s*[^;]+;?/, restoreFontWeight );
						} else {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreFontWeight;
						}
					} else {
						// Only remove font-weight if it was added by the plugin (has !important)
						// Preserve original font-weight values that don't have !important
						const fontWeightMatch = currentStyle.match( /font-weight:\s*([^;]+);?/ );
						if ( fontWeightMatch && fontWeightMatch[ 1 ] ) {
							const fontWeightValue = fontWeightMatch[ 1 ].trim();
							// If font-weight has !important, it was added by the plugin, so remove it
							if ( fontWeightValue.includes( '!important' ) ) {
								currentStyle = currentStyle.replace( /font-weight:\s*[^;]+;?/, '' );
							}
							// If font-weight doesn't have !important, it's likely original, so preserve it (do nothing)
						}
					}

					// Reset (Text Align): restore original inline text-align if available, otherwise remove only plugin-added text-align
					const originalInlineTextAlign = this.dataset && this.dataset.onetapOrigStyle ? this.dataset.onetapOrigStyle : '';
					if ( this.dataset && undefined !== this.dataset.onetapOrigStyle ) {
						// Element was modified by the align feature: restore exactly what it had before.
						const origTextAlignMatch = originalInlineTextAlign.match( /text-align:\s*[^;]+;?/ );
						if ( /text-align:\s*[^;]+;?/.test( currentStyle ) ) {
							currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, origTextAlignMatch ? origTextAlignMatch[ 0 ] : '' );
						} else if ( origTextAlignMatch ) {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + origTextAlignMatch[ 0 ];
						}
						delete this.dataset.onetapOrigStyle;
					} else {
						// Only remove text-align if it was added by the plugin (has !important); preserve original values.
						const textAlignMatch = currentStyle.match( /text-align:\s*([^;]+);?/ );
						if ( textAlignMatch && textAlignMatch[ 1 ] && textAlignMatch[ 1 ].includes( '!important' ) ) {
							currentStyle = currentStyle.replace( /text-align:\s*[^;]+;?/, '' );
						}
					}

					// Reset (Readable Font & Dyslexic Font)
					currentStyle = currentStyle.replace( /font-family:\s*[^;]+;?/, '' );

					// Reset (Hide Images)
					currentStyle = currentStyle.replace( /background-size:\s*[^;]+;?/, '' );

					// Reset (Visibility): restore original inline visibility if available, otherwise remove only plugin-added visibility
					const originalInlineVisibility = this.dataset && this.dataset.originalInlineVisibility ? this.dataset.originalInlineVisibility : '';
					if ( originalInlineVisibility ) {
						const restoreVisibility = `visibility: ${ originalInlineVisibility };`;
						if ( onetapCssVisibilityDecl.test( currentStyle ) ) {
							currentStyle = currentStyle.replace( onetapCssVisibilityDecl, ( match, delim ) => delim + ( delim ? ' ' : '' ) + restoreVisibility );
						} else {
							currentStyle += ( currentStyle.trim().endsWith( ';' ) ? ' ' : '; ' ) + restoreVisibility;
						}
					} else {
						const visibilityMatch = currentStyle.match( onetapCssVisibilityValue );
						if ( visibilityMatch && visibilityMatch[ 1 ] ) {
							const visibilityValue = visibilityMatch[ 1 ].trim();
							if ( visibilityValue.includes( '!important' ) ) {
								currentStyle = currentStyle.replace( onetapCssVisibilityDecl, ( match, delim ) => delim );
							}
						}
					}

					// Reset (Stop Animations)
					currentStyle = currentStyle.replace( /transition:\s*[^;]+;?/, '' );
					currentStyle = currentStyle.replace( /animation:\s*[^;]+;?/, '' );

					// Trim any extra spaces and ensure there's no trailing space
					currentStyle = currentStyle.trim();

					// Set the updated style attribute back to the element, or remove it entirely
					// when empty so untouched elements don't end up with a stray style="".
					if ( currentStyle ) {
						$( this ).attr( 'style', currentStyle );
					} else {
						$( this ).removeAttr( 'style' );
					}
				} );

				// Remove style inline
				$( 'a, a *' ).not( onetapSkipElements ).each( function() {
					let currentStyle = $( this ).attr( 'style' ) || '';

					// Reset (Highlight Links)
					currentStyle = currentStyle.replace( /background:\s*[^;]+;?/, '' );
					currentStyle = currentStyle.replace( /color:\s*[^;]+;?/, '' );

					// Trim any extra spaces and ensure there's no trailing space
					currentStyle = currentStyle.trim();

					// Set the updated style attribute back to the element
					$( this ).attr( 'style', currentStyle );
				} );

				// Remove style inline
				$( 'img' ).not( onetapSkipElements ).each( function() {
					let currentStyle = $( this ).attr( 'style' ) || '';

					// Reset (Hide Images)
					currentStyle = currentStyle.replace( onetapCssVisibilityDecl, ( match, delim ) => delim );

					// Trim any extra spaces and ensure there's no trailing space
					currentStyle = currentStyle.trim();

					// Set the updated style attribute back to the element
					$( this ).attr( 'style', currentStyle );
				} );

				// ============= Content =============

				// Reset (Cursor)
				$( 'html' ).removeClass( 'onetap-cursor-feature1' );
				$( 'html' ).removeClass( 'onetap-cursor-feature2' );
				$( 'html' ).removeClass( 'onetap-cursor-feature3' );

				// Dark/Ligh Contrast
				$( 'body' ).removeClass( 'onetap-dark-contrast' );
				$( 'body' ).removeClass( 'onetap-light-contrast' );

				// Reset (Highlight titles)
				$( 'body' ).removeClass( 'onetap-highlight-titles' );

				// Reset (Highlight all)
				$( 'body' ).removeClass( 'onetap-highlight-all' );

				// ============= Content Bottom =============

				// Reset (Text magnifier)
				$( '.onetap-markup-text-magnifier' ).hide();

				// ============= Colors =============

				$( 'html, img' ).not( onetapSkipElements ).each( function() {
					let currentStyle = $( this ).attr( 'style' ) || '';

					// Remove the filter if activeBorderValue is 0 (Colors)
					currentStyle = currentStyle.replace( /filter:\s*[^;]+;?/, '' );

					// Trim any extra spaces and ensure there's no trailing space
					currentStyle = currentStyle.trim();

					// Set the updated style attribute back to the element
					$( this ).attr( 'style', currentStyle );
				} );

				// ============= Orientation =============

				// Reset (Reading line)
				$( '.onetap-markup-reading-line' ).removeClass( 'onetap-active' );

				// Reset (Keyboard navigation)
				$( 'body' ).removeClass( 'onetap-keyboard-navigation' );

				// Reset (Reading mask)
				$( '.onetap-markup-reading-mask' ).removeClass( 'onetap-active' );

				// ============= Orientation bottom =============

				// Unmute all video and audio
				$( 'audio, video' ).not( onetapSkipElements ).each( function() {
					$( this ).prop( 'muted', false );
				} );

				// Unmute all YouTube iframes
				$( 'iframe[src*="youtube.com"]' ).each( function() {
					const src = $( this ).attr( 'src' );
					if ( src.includes( 'mute=1' ) ) {
						$( this ).attr( 'src', src.replace( 'mute=1', '' ) );
					}
				} );
			}
		} else {
			// Create localStorage item if it does not exist
			localStorage.setItem( onetapLocalStorage, JSON.stringify( onetapAccessibilityDefault ) );
		}
	} );

	// When the "Hide Toolbar" button is clicked
	$( '.hide-toolbar' ).on( 'click', function() {
		// Get the ID of the selected radio button
		const selected = $( 'input[name="hide_toolbar_duration"]:checked' ).attr( 'id' );

		// If no option is selected, alert and stop
		if ( ! selected ) {
			alert( 'Please select a duration!' );
			return;
		}

		// Define prefix and key name for storage
		const prefix = 'onetap_free_';
		const key = prefix + 'toolbar_hidden_until';
		let expireAt;

		if ( selected === 'only-for-this-session' ) {
			// Hide the toolbar and save flag in sessionStorage (clears on browser close)
			$( '.onetap-container-toggle' ).hide();
			$( '.onetap-accessibility' ).hide();
			sessionStorage.setItem( key, 'session' );
		} else {
			const now = new Date();
			if ( selected === 'only-for-24-hours' ) {
				// Set expiration to 24 hours from now
				expireAt = now.getTime() + ( 24 * 60 * 60 * 1000 );
			} else if ( selected === 'only-for-a-week' ) {
				// Set expiration to 7 days from now
				expireAt = now.getTime() + ( 7 * 24 * 60 * 60 * 1000 );
			}

			// Save the expiration time in localStorage and hide the toolbar
			localStorage.setItem( key, expireAt );
			$( '.onetap-container-toggle' ).each( function() {
				this.style.setProperty( 'display', 'none', 'important' );
			} );

			$( '.onetap-accessibility' ).each( function() {
				this.style.setProperty( 'display', 'none', 'important' );
			} );
		}
	} );

	// On page load, check if toolbar should be hidden
	const key = 'onetap_pro_toolbar_hidden_until';
	const sessionFlag = sessionStorage.getItem( key );
	const storedTime = localStorage.getItem( key );

	if ( sessionFlag === 'session' ) {
		// If sessionStorage has the flag, hide the toolbar
		$( '.onetap-container-toggle' ).hide();
	} else if ( storedTime ) {
		const now = new Date().getTime();
		if ( parseInt( storedTime, 10 ) > now ) {
			// If current time is before expiration, hide the toolbar
			$( '.onetap-container-toggle' ).hide();
		} else {
			// If expired, remove the flag
			localStorage.removeItem( key );
		}
	} else {
		$( '.onetap-container-toggle' ).show();
	}

	// This script enhances the accessibility of the "Hide Toolbar Duration" options.
	$( '.toolbar-duration-option' ).on( 'keydown', function( e ) {
		if ( e.key === 'Enter' || e.code === 'Space' || e.keyCode === 32 ) {
			e.preventDefault();
			// Uncheck all radios
			$( '.toolbar-duration-option' ).attr( 'aria-checked', 'false' ).find( 'input[type="radio"]' ).prop( 'checked', false );

			// Check the current one
			$( this ).attr( 'aria-checked', 'true' ).find( 'input[type="radio"]' ).prop( 'checked', true );
		}
	} );
}( jQuery ) );
