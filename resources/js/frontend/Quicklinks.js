/* global MultilingualPress */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress Quicklinks module.
	 * @returns {{initialize: initialize}}
	 * @constructor
	 */
	var Quicklinks = function() {
		var $form = $( '#mlp-quicklink-form' ),
			$select = $form.find( 'select' );

		/**
		 * Triggers a redirect on form submission.
		 * @param {Event} event - The submit event of the form.
		 */
		var submitForm = function( event ) {
			if ( $select.length ) {
				event.preventDefault();

				setLocation( $select.val() );
			}
		};

		/**
		 * Redirects the user to the given URL.
		 * @param {string} url - The URL.
		 */
		var setLocation = function( url ) {
			window.location.href = url;
		};

		return {
			/**
			 * Initializes the module.
			 */
			initialize: function() {
				if ( $form.length ) {
					$form.on( 'submit', function( event ) {
						submitForm( event.originalEvent );
					} );
				}
			}
		};
	};

	/**
	 * The MultilingualPress Quicklinks instance.
	 * @type {Quicklinks}
	 */
	MultilingualPress.Modules.Quicklinks = new Quicklinks();

	$( MultilingualPress.Modules.Quicklinks.initialize );
})( jQuery );
