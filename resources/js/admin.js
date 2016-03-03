/* global mlpSettings */
(function( $ ) {
	'use strict';

	/**
	 * Constructor for the MultilingualPress router.
	 * @constructor
	 */
	var MultilingualPressRouter = Backbone.Router.extend( {
		/** @lends MultilingualPressRouter.prototype */

		/**
		 * MLP-specific routing
		 *
		 * @augments Backbone.Router
		 * @constructor
		 * @name MultilingualPressRouter
		 */
	} );

	/**
	 * Constructor for the MultilingualPress admin controller.
	 * @returns {{Modules: Array, registerModule: registerModule, initialize: initialize}}
	 * @constructor
	 */
	var MultilingualPress = function() {
		var Modules = [],
			Registry = {},
			Router = new MultilingualPressRouter();

		/**
		 * Registers the module with the given data for the given route.
		 * @param {Object} moduleData - The module data.
		 * @param {string} route - The route.
		 */
		var registerModuleForRoute = function( moduleData, route ) {
			if ( Registry[ route ] ) {
				Registry[ route ].modules.push( moduleData );
			} else {
				Registry[ route ] = {
					modules: [ moduleData ]
				};
			}
		};

		/**
		 * Sets up all routes with the according registered modules.
		 */
		var setUpRoutes = function() {
			$.each( Registry, function( route, routeData ) {
				Router.route( route, route, function() {
					$.each( routeData.modules, function( index, module ) {
						Modules[ module.name ] = new module.Callback( module.options );
					} );
				} );
			} );
		};

		return {
			/**
			 * @memberof MultilingualPress
			 * @augments Backbone.Events
			 */
			Events: _.extend( {}, Backbone.Events ),
			/**
			 * @memberof MultilingualPress
			 */
			Modules: Modules,

			/**
			 * Returns the settings object for the given module or settings name.
			 * @memberof MultilingualPress
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
			 * @memberof MultilingualPress
			 * @param {string|string[]} routes - The routes for the module.
			 * @param {string} name - The name of the module.
			 * @param {Function} Module - The constructor callback for the module.
			 * @param {Object} [options={}] - Optional. The options for the module. Default to {}.
			 */
			registerModule: function( routes, name, Module, options ) {
				var moduleData = {
					name: name,
					Callback: Module,
					options: options || {}
				};

				$.each( _.isArray( routes ) ? routes : [ routes ], function( index, route ) {
					registerModuleForRoute( moduleData, route );
				} );
			},

			/**
			 * Initializes the instance.
			 * @memberof MultilingualPress
			 */
			initialize: function() {
				setUpRoutes();

				Backbone.history.start( {
					root: mlpSettings.urlRoot,
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
