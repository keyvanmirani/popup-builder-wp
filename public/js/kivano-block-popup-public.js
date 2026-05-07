(function() {
	'use strict';

	function ready( callback ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', callback );
			return;
		}

		callback();
	}

	ready( function() {
		var settings = window.kivanoBlockPopupSettings || {};
		var overlay = document.getElementById( 'kivano-block-popup-overlay' );
		var debug = true === settings.debug || '1' === settings.debug || 1 === settings.debug;

		function debugLog( message, data ) {
			if ( debug && window.console && window.console.log ) {
				window.console.log( message, data || '' );
			}
		}

		debugLog( 'Kivano Block Popup: script loaded' );
		debugLog( 'Kivano Block Popup: settings received', settings );

		if ( ! overlay ) {
			return;
		}

		var closeButton = overlay.querySelector( '.kivano-block-popup-close' );
		var delayEnabled = true === settings.delayEnabled || '1' === settings.delayEnabled || 1 === settings.delayEnabled;
		var delay = delayEnabled ? Math.max( 0, parseInt( settings.delay, 10 ) || 0 ) : 0;
		var oncePerSession = true === settings.oncePerSession || '1' === settings.oncePerSession || 1 === settings.oncePerSession;
		var repeatEnabled = ! oncePerSession && ( true === settings.repeatEnabled || '1' === settings.repeatEnabled || 1 === settings.repeatEnabled );
		var repeatInterval = repeatEnabled ? Math.max( 0, parseInt( settings.repeatInterval, 10 ) || 0 ) : 0;
		var sessionKey = settings.sessionKey || 'kivano_block_popup_closed';
		var repeatTimer;

		function wasClosedThisSession() {
			try {
				return '1' === window.sessionStorage.getItem( sessionKey );
			} catch ( error ) {
				return false;
			}
		}

		function markClosedThisSession() {
			try {
				window.sessionStorage.setItem( sessionKey, '1' );
			} catch ( error ) {
				// Storage can be unavailable in private browsing or locked-down contexts.
			}
		}

		function showPopup() {
			if ( oncePerSession && wasClosedThisSession() ) {
				return;
			}

			overlay.hidden = false;
			overlay.classList.add( 'is-visible' );
			document.body.classList.add( 'kivano-block-popup-open' );
			debugLog( 'Kivano Block Popup: popup opened' );
		}

		function hidePopup() {
			overlay.classList.remove( 'is-visible' );
			overlay.hidden = true;
			document.body.classList.remove( 'kivano-block-popup-open' );
			debugLog( 'Kivano Block Popup: popup closed' );

			if ( oncePerSession ) {
				markClosedThisSession();
				return;
			}

			if ( repeatInterval > 0 ) {
				window.clearTimeout( repeatTimer );
				repeatTimer = window.setTimeout( showPopup, repeatInterval );
			}
		}

		if ( oncePerSession && wasClosedThisSession() ) {
			return;
		}

		if ( delay > 0 ) {
			window.setTimeout( showPopup, delay );
		} else {
			showPopup();
		}

		if ( closeButton ) {
			closeButton.addEventListener( 'click', hidePopup );
		}

		overlay.addEventListener( 'click', function( event ) {
			if ( event.target === overlay ) {
				hidePopup();
			}
		} );

		document.addEventListener( 'keydown', function( event ) {
			if ( 'Escape' === event.key && ! overlay.hidden ) {
				hidePopup();
			}
		} );
	} );
})();
