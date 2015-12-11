/* global MultilingualPress */
(function() {
	'use strict';

	var Quicklink = {
		initialize: function() {
			var form = document.getElementById( 'mlp-quicklink-form' );
			if ( form ) {
				form.onsubmit = Quicklink.submitForm;
			}
		},
		submitForm: function( event ) {
			var select = document.getElementById( 'mlp-quicklink-select' );
			if ( select ) {
				event.preventDefault();
				document.location.href = Quicklink.getSelectValue( select );
			}
		},
		getSelectValue: function( select ) {
			return select.options[ select.selectedIndex ].value;
		}
	};

	Quicklink.initialize();

	MultilingualPress.Quicklink = Quicklink;
})();
