(function() {
	'use strict';

	/**
	 * @class MultilingualPress
	 * @classdesc MultilingualPress front-end controller.
	 */
	var MultilingualPress = function() {
		return /** @lends MultilingualPress# */ {
			/**
			 * MultilingualPress module instances.
			 * @type {Object[]}
			 */
			Modules: []
		};
	};

	/**
	 * The MultilingualPress front-end instance.
	 * @type {MultilingualPress}
	 */
	window.MultilingualPress = new MultilingualPress();
})();

(function( $, MultilingualPress ) {
	'use strict';

	/**
	 * @class Quicklinks
	 * @classdesc MultilingualPress Quicklinks module.
	 */
	var Quicklinks = function() {
		/**
		 * Redirects the user to the given URL.
		 * @param {string} url - The URL.
		 */
		var setLocation = function( url ) {
			window.location.href = url;
		};

		/**
		 * Triggers a redirect on form submission.
		 * @param {Event} event - The submit event of the form.
		 */
		var submitForm = function( event ) {
			var $select = $( event.target ).find( 'select' );
			if ( $select.length ) {
				event.preventDefault();

				setLocation( $select.val() );
			}
		};

		return /** @lends Quicklinks# */ {
			/**
			 * Initializes the module.
			 */
			initialize: function() {
				var $form = $( '#mlp-quicklink-form' );
				if ( $form.length ) {
					$form.on( 'submit', submitForm );
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
})( jQuery, window.MultilingualPress );
