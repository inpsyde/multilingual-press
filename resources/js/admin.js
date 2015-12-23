/* global _, Backbone, mlpSettings */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress router.
	 * @constructor
	 */
	var MultilingualPressRouter = Backbone.Router.extend( {} );

	/**
	 * Constructor for the MultilingualPress admin controller.
	 * @returns {{Modules: Array, registerModule: registerModule, initialize: initialize}}
	 * @constructor
	 */
	var MultilingualPress = function() {
		var Modules = [],
			Router = new MultilingualPressRouter();

		return {
			Modules: Modules,

			/**
			 * Returns the settings object for the given module or settings name.
			 * @param {string} name - The name of either the MulitilingualPress module or the settings object itself.
			 * @returns {Object} - The settings object.
			 */
			getSettings: function( name ) {
				if ( 'undefined' !== typeof window[ 'mlp' + name + 'Settings' ] ) {
					return window[ 'mlp' + name + 'Settings' ];
				}

				if ( 'undefined' !== typeof window[ name ] ) {
					return window[ name ];
				}

				return {};
			},

			/**
			 * Registers a new module with the given Module callback under the given name for the given route.
			 * @param {string|string[]} routes - The routes for the module.
			 * @param {string} name - The name of the module.
			 * @param {Function} Module - The constructor callback for the module.
			 * @param {Object} [options={}] - Optional. The options for the module. Default to {}.
			 */
			registerModule: function( routes, name, Module, options ) {
				if ( _.isFunction( Module ) ) {
					options = options || {};
					$.each( _.isArray( routes ) ? routes : [ routes ], function( index, route ) {
						Router.route( route, name, function() {
							Modules[ name ] = new Module( options );
						} );
					} );
				}
			},

			/**
			 * Initializes the instance.
			 */
			initialize: function() {
				Backbone.history.start( {
					root: mlpSettings.adminUrl,
					pushState: true,
					hashChange: false
				} );
			}
		};
	};

	/**
	 * The MultilingualPress admin instance.
	 * @type {MultilingualPress}
	 */
	window.MultilingualPress = new MultilingualPress();

	$( window.MultilingualPress.initialize );
})( jQuery );

// TODO: Refactor the following ... mess.
(function( $ ) {
	"use strict";

	var multilingualPress = {

		init: function() {
			$( document ).on( 'click', '[data-toggle_selector]', function() {
				if ( 'INPUT' === this.tagName ) {
					return true;
				}

				$( $( this ).data( 'toggle_selector' ) ).toggle();

				return false;
			} );

			$( 'label.mlp_toggler' ).each( function() {
				var $inputs = $( 'input[name="' + $( '#' + $( this ).attr( 'for' ) ).attr( 'name' ) + '"]' ),
					$toggler = $inputs.filter( '[data-toggle_selector]' );

				if ( $toggler.length ) {
					$inputs.on( 'change', function() {
						$( $toggler.data( 'toggle_selector' ) ).toggle( $toggler.is( ':checked' ) );

						return true;
					} );
				}
			} );
		}
	};

	$( function() {
		multilingualPress.init();
	} );

})( jQuery );
