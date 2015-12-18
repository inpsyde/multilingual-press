/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var Quicklinks = {

		/**
		 * Initializes the Quicklinks module.
		 */
		initialize: function() {
			Quicklinks.$form = $( '#mlp-quicklink-form' );
			if ( Quicklinks.$form.length ) {
				Quicklinks.$select = Quicklinks.$form.find( 'select' );

				Quicklinks.$form.on( 'submit', function( event ) {
					Quicklinks.submitForm( event.originalEvent );
				} );
			}
		},

		/**
		 * Triggers a redirect on form submit.
		 * @param {event} event - The submit event of the form.
		 */
		submitForm: function( event ) {
			if ( Quicklinks.$select.length ) {
				event.preventDefault();

				Quicklinks.setLocation( Quicklinks.$select.val() );
			}
		},

		/**
		 * Redirects the user to the given URL.
		 * @param {string} url - The URL.
		 */
		setLocation: function( url ) {
			window.location.href = url;
		}
	};

	MultilingualPress.Quicklinks = Quicklinks;

	$( MultilingualPress.Quicklinks.initialize );
})( jQuery );
