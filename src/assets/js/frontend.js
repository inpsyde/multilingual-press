/**
 * The MultilingualPress front end namespace object.
 * @namespace
 */
var MultilingualPress = {};

/**
 * Redirects the user to the given URL.
 * @param {string} url - The URL.
 */
MultilingualPress.setLocation = function( url ) {
	'use strict';

	window.location.href = url;
};
var MultilingualPress = window.MultilingualPress || {};

var Quicklinks = (function( $, MultilingualPress ) {
	'use strict';

	/**
	 * @classdesc The MultilingualPress Quicklinks module.
	 * @class
	 * @alias Quicklinks
	 * @param {string} [selector] - The form element selector.
	 */
	function Module( selector ) {
		/**
		 * The form element selector.
		 * @type {string}
		 */
		this.selector = selector || '';
	}

	/**
	 * Attaches the given handler to the form submit event.
	 * @param {string} selector - The form element selector.
	 * @param {function} handler - The event handler.
	 */
	function attachSubmitHandler( selector, handler ) {
		var $form = $( selector );
		if ( ! $form.length ) {
			return;
		}

		$form.on( 'submit', handler );
	}

	/**
	 * Initializes the module (as soon as jQuery is ready).
	 */
	Module.prototype.initialize = function initialize() {
		$( attachSubmitHandler.call( this, this.selector, this.submitForm ) );
	};

	/**
	 * Triggers a redirect on form submission.
	 * @param {Event} event - The submit event of the form.
	 */
	Module.prototype.submitForm = function submitForm( event ) {
		var $select = $( event.target ).find( 'select' );
		if ( ! $select.length ) {
			return;
		}

		event.preventDefault();

		MultilingualPress.setLocation( $select.val() );
	};

	return Module;
})( jQuery, MultilingualPress );

/**
 * The MultilingualPress Quicklinks instance.
 * @type {Quicklinks}
 */
MultilingualPress.quicklinks = new Quicklinks( '#mlp-quicklink-form' );
MultilingualPress.quicklinks.initialize();
