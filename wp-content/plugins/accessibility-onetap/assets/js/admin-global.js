( function( $ ) {
	// PRO upgrade banner: dismiss button.
	$( document ).on( 'click', '#onetap-pro-banner-close', function() {
		// Collapse the banner smoothly so the content below moves up seamlessly.
		$( '#onetap-pro-banner' ).slideUp( 300, function() {
			$( this ).remove();
		} );

		// Keep the banner hidden for 1 week.
		const oneWeekInSeconds = 7 * 24 * 60 * 60;
		document.cookie = 'onetap_pro_banner_dismissed=1; max-age=' + oneWeekInSeconds + '; path=/; SameSite=Lax';
	} );

	// Notice close.
	$( document ).on( 'click', '.notice-onetap .notice-dismiss, .notice-onetap .already-did', function() {
		// Hide notice.
		$( '.notice-onetap' ).slideUp( 300 );

		$.ajax( {
			// eslint-disable-next-line no-undef
			url: adminLocalize.ajaxUrl,
			type: 'POST',
			data: {
				action: 'onetap_action_dismiss_notice',
				// eslint-disable-next-line no-undef
				mynonce: adminLocalize.ajaxNonce,
			},
			success( response ) {
				// eslint-disable-next-line no-console
				console.log( 'Ajax success:', response );
			},
			error( error ) {
				// eslint-disable-next-line no-console
				console.error( 'Ajax error:', error );
			},
		} );
	} );

	// Review banner: "Leave a Review" button.
	$( document ).on( 'click', '#onetap-review-leave-review', function() {
		// Hide banner immediately.
		$( '#onetap-review-banner' ).slideUp( 300 );

		// Send AJAX request to permanently disable banner.
		$.ajax( {
			// eslint-disable-next-line no-undef
			url: adminLocalize.ajaxUrl,
			type: 'POST',
			data: {
				action: 'onetap_review_leave_review',
				// eslint-disable-next-line no-undef
				mynonce: adminLocalize.ajaxNonce,
			},
			success( response ) {
				// eslint-disable-next-line no-console
				console.log( 'Review banner disabled:', response );
			},
			error( error ) {
				// eslint-disable-next-line no-console
				console.error( 'Ajax error:', error );
			},
		} );

		// Allow default link behavior (redirect to review page).
		// The banner is already hidden and disabled via AJAX.
	} );

	// Review banner: "Maybe later" button.
	$( document ).on( 'click', '#onetap-review-maybe-later', function( e ) {
		e.preventDefault();

		// Hide banner immediately.
		$( '#onetap-review-banner' ).slideUp( 300 );

		// Send AJAX request to set next show date to +1 hour.
		$.ajax( {
			// eslint-disable-next-line no-undef
			url: adminLocalize.ajaxUrl,
			type: 'POST',
			data: {
				action: 'onetap_review_maybe_later',
				// eslint-disable-next-line no-undef
				mynonce: adminLocalize.ajaxNonce,
			},
			success( response ) {
				// eslint-disable-next-line no-console
				console.log( 'Review banner postponed:', response );
			},
			error( error ) {
				// eslint-disable-next-line no-console
				console.error( 'Ajax error:', error );
			},
		} );
	} );

	// Review banner: "Don't show again" button.
	$( document ).on( 'click', '#onetap-review-dont-show', function( e ) {
		e.preventDefault();

		// Hide banner immediately.
		$( '#onetap-review-banner' ).slideUp( 300 );

		// Send AJAX request to permanently disable banner.
		$.ajax( {
			// eslint-disable-next-line no-undef
			url: adminLocalize.ajaxUrl,
			type: 'POST',
			data: {
				action: 'onetap_review_dont_show_again',
				// eslint-disable-next-line no-undef
				mynonce: adminLocalize.ajaxNonce,
			},
			success( response ) {
				// eslint-disable-next-line no-console
				console.log( 'Review banner disabled:', response );
			},
			error( error ) {
				// eslint-disable-next-line no-console
				console.error( 'Ajax error:', error );
			},
		} );
	} );
}( jQuery ) );
