//
// TODO: Complete refactoring of the Controller class as well as its dependencies.
//

import * as F from "./functions";
import Router from "./Router";

const $ = window.jQuery;
const _ = window._;

const mlpSettings = F.getSettings( 'mlpSettings' );

const modules = {};

const registry = {};

const router = new Router();

/**
 * Registers the module with the given data for the given route.
 * @param {Object} moduleData - The module data.
 * @param {string} route - The route.
 */
const registerModuleForRoute = ( moduleData, route ) => {
	registry[ route ] || ( registry[ route ] = [] );
	registry[ route ].push( moduleData );
};

/**
 * Sets up all routes with the according registered modules.
 */
const setUpRoutes = () => {
	$.each( registry, ( route, routeModules ) => {
		router.route( route, route, () => {
			$.each( routeModules, ( index, moduleData ) => {
				let Constructor = moduleData.Constructor;
				let module = new Constructor( moduleData.options );
				modules[ Constructor.name ] = module;
				moduleData.callback && moduleData.callback( module );
			} );
		} );
	} );
};

/**
 * Starts Backbone's history, unless it has been started already.
 * @returns {boolean}
 */
const maybeStartHistory = () => {
	if ( Backbone.History.started ) {
		return false;
	}

	Backbone.history.start( {
		root: mlpSettings.urlRoot,
		pushState: true,
		hashChange: false
	} );

	return true;
};

class Controller {
	/**
	 * Constructor. Sets up the properties.
	 */
	constructor() {
		/**
		 * The MultilingualPress admin module instances.
		 * @type {Object}
		 */
		this.modules = modules;
	}

	/**
	 * Registers a new module with the given Module callback under the given name for the given route.
	 * @param {string|string[]} routes - The routes for the module.
	 * @param {Function} Constructor - The constructor callback for the module.
	 * @param {Object} [options={}] - Optional. The options for the module. Default to {}.
	 * @param {Function} [callback=null] - Optional. The callback to execute after construction. Defaults to null.
	 */
	registerModule( routes, Constructor, options = {}, callback = null ) {
		const moduleData = {
			Constructor,
			options,
			callback
		};

		$.each( _.isArray( routes ) ? routes : [ routes ], ( index, route ) => {
			registerModuleForRoute( moduleData, route );
		} );
	}

	/**
	 * Initializes the instance.
	 */
	initialize() {
		setUpRoutes();
		maybeStartHistory();
	}
}

export default Controller;
