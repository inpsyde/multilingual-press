/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var Quicklinks = {
		initialize: function() {
			Quicklinks.$form = $( '#mlp-quicklink-form' );
			if ( Quicklinks.$form.length ) {
				Quicklinks.$select = Quicklinks.$form.find( 'select' );
				Quicklinks.$form.on( 'submit', Quicklinks.submitForm );
			}
		},

		submitForm: function( event ) {
			if ( Quicklinks.$select.length ) {
				event.preventDefault();
				Quicklinks.setLocation( Quicklinks.$select.val() );
			}
		},

		setLocation: function( url ) {
			window.location.href = url;
		}
	};

	MultilingualPress.Quicklinks = Quicklinks;

	$( MultilingualPress.Quicklinks.initialize );
})( jQuery );
