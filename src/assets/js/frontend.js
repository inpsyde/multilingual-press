(function() {
	'use strict';

	window.MultilingualPress = function() {
		return this;
	};
})();

/* global MultilingualPress */
(function( $ ) {
	'use strict';

	var Quicklink = function( formID ) {
		this.initialize = function() {
			var $form = $( '#' + formID );
			if ( $form.length ) {
				$form.on( 'submit', Quicklink.submitForm );
			}
		};

		this.submitForm = function( event ) {
			var $select = $( this ).find( 'select' );
			if ( $select.length ) {
				event.preventDefault();
				document.location.href = $select.val();
			}
		};
	};

	MultilingualPress.Quicklink = new Quicklink( 'mlp-quicklink-form' );

	$( MultilingualPress.Quicklink.initialize );
})( jQuery );
