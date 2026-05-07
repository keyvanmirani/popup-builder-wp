(function() {
	'use strict';

	var settings = window.kivanoBlockPopupAdmin || {};
	var messages = settings.i18n || {};

	messages.enable = messages.enable || 'فعال‌سازی پاپ‌آپ';
	messages.disable = messages.disable || 'غیرفعال‌سازی پاپ‌آپ';
	messages.enabled = messages.enabled || 'Enabled';
	messages.disabled = messages.disabled || 'Disabled';
	messages.saving = messages.saving || 'Saving...';
	messages.error = messages.error || 'Could not update popup status. Please try again.';

	function updateToggle( toggle, enabled ) {
		var status = toggle.querySelector( '.kivano-block-popup-toggle-status' );

		toggle.classList.toggle( 'is-enabled', enabled );
		toggle.setAttribute( 'aria-checked', enabled ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', enabled ? messages.disable : messages.enable );
		toggle.dataset.enabled = enabled ? '1' : '0';

		if ( status ) {
			status.textContent = enabled ? messages.enabled : messages.disabled;
		}
	}

	function setSaving( toggle, saving ) {
		toggle.classList.toggle( 'is-saving', saving );
		toggle.disabled = saving;

		if ( saving ) {
			toggle.setAttribute( 'aria-label', messages.saving );
		}
	}

	function showError( toggle ) {
		toggle.classList.add( 'has-error' );
		window.setTimeout( function() {
			toggle.classList.remove( 'has-error' );
		}, 1600 );

		if ( window.wp && window.wp.a11y && window.wp.a11y.speak ) {
			window.wp.a11y.speak( messages.error, 'assertive' );
		}
	}

	function saveToggle( toggle ) {
		var currentEnabled = toggle.dataset.enabled === '1';
		var nextEnabled = ! currentEnabled;
		var payload = new window.URLSearchParams();

		updateToggle( toggle, nextEnabled );
		setSaving( toggle, true );

		payload.append( 'action', 'kivano_block_popup_toggle_enabled' );
		payload.append( 'kivano_block_popup_toggle_enabled_nonce', settings.nonce || '' );
		payload.append( 'post_id', toggle.dataset.postId || '' );
		payload.append( 'enabled', nextEnabled ? '1' : '0' );

		window.fetch( settings.ajaxUrl || window.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: payload.toString()
		} )
			.then( function( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Request failed.' );
				}

				return response.json();
			} )
			.then( function( response ) {
				if ( ! response || ! response.success ) {
					throw new Error( 'Save failed.' );
				}

				updateToggle( toggle, !! response.data.enabled );
			} )
			.catch( function() {
				updateToggle( toggle, currentEnabled );
				showError( toggle );
			} )
			.finally( function() {
				setSaving( toggle, false );
				updateToggle( toggle, toggle.dataset.enabled === '1' );
			} );
	}

	function syncControlledField( control ) {
		var field = document.getElementById( control.dataset.kivanoBlockPopupControls || '' );

		if ( field ) {
			field.disabled = ! control.checked;
		}
	}

	function activateTab( tab ) {
		var tabsRoot = tab.closest( '[data-kivano-block-popup-tabs]' );
		var panelId = tab.getAttribute( 'aria-controls' );

		if ( ! tabsRoot || ! panelId ) {
			return;
		}

		tabsRoot.querySelectorAll( '[role="tab"]' ).forEach( function( currentTab ) {
			var isActive = currentTab === tab;

			currentTab.classList.toggle( 'is-active', isActive );
			currentTab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
			currentTab.tabIndex = isActive ? 0 : -1;
		} );

		tabsRoot.querySelectorAll( '[role="tabpanel"]' ).forEach( function( panel ) {
			var isActive = panel.id === panelId;

			panel.classList.toggle( 'is-active', isActive );
			panel.hidden = ! isActive;
		} );
	}

	document.querySelectorAll( '[data-kivano-block-popup-tabs]' ).forEach( function( tabsRoot ) {
		var tabs = Array.prototype.slice.call( tabsRoot.querySelectorAll( '[role="tab"]' ) );
		var isRtl = document.documentElement.dir === 'rtl' || document.body.classList.contains( 'rtl' );

		tabs.forEach( function( tab, index ) {
			tab.addEventListener( 'click', function() {
				activateTab( tab );
			} );

			tab.addEventListener( 'keydown', function( event ) {
				var nextIndex;

				if ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) {
					nextIndex = 'ArrowRight' === event.key && isRtl
						? ( index - 1 + tabs.length ) % tabs.length
						: ( index + 1 ) % tabs.length;
				} else if ( 'ArrowLeft' === event.key || 'ArrowUp' === event.key ) {
					nextIndex = 'ArrowLeft' === event.key && isRtl
						? ( index + 1 ) % tabs.length
						: ( index - 1 + tabs.length ) % tabs.length;
				} else if ( 'Home' === event.key ) {
					nextIndex = 0;
				} else if ( 'End' === event.key ) {
					nextIndex = tabs.length - 1;
				} else {
					return;
				}

				event.preventDefault();
				activateTab( tabs[ nextIndex ] );
				tabs[ nextIndex ].focus();
			} );
		} );
	} );

	document.querySelectorAll( '[data-kivano-block-popup-controls]' ).forEach( function( control ) {
		syncControlledField( control );

		control.addEventListener( 'change', function() {
			syncControlledField( control );
		} );
	} );

	document.addEventListener( 'click', function( event ) {
		var toggle = event.target.closest( '.kivano-block-popup-enabled-toggle' );

		if ( ! toggle || toggle.disabled ) {
			return;
		}

		saveToggle( toggle );
	} );
})();
