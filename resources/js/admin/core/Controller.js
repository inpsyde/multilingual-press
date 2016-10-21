// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {};

/**
 * The MultilingualPress admin controller.
 */
class Controller {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Registry} registry - The registry object.
	 * @param {Object} settings - The controller settings.
	 */
	constructor( registry, settings ) {
		/**
		 * The registry object.
		 * @type {Registry}
		 */
		_this.registry = registry;

		/**
		 * The controller settings.
		 * @type {Object}
		 */
		_this.settings = settings;
	}

	/**
	 * Returns the settings object.
	 * @returns {Object} The settings object.
	 */
	get settings() {
		return _this.settings;
	}

	/**
	 * Initializes the instance.
	 * @returns {Object} The module instances registered for the current admin page.
	 */
	initialize() {
		const modules = _this.registry.initializeRoutes();

		this.maybeStartHistory();

		return modules;
	}

	/**
	 * Starts Backbone's history, unless it has been started already.
	 * @returns {Boolean} Whether or not the history has been started right now.
	 */
	maybeStartHistory() {
		if ( Backbone.History.started ) {
			return false;
		}

		Backbone.history.start( {
			root: this.settings.urlRoot,
			pushState: true,
			hashChange: false
		} );

		return true;
	}

	/**
	 * Registers a new module with the given Module callback under the given name for the given routes.
	 * @param {String|String[]} routes - One or more routes.
	 * @param {Function} Constructor - The constructor callback for the module.
	 * @param {Object} [options={}] - Optional. The options for the module. Default to an empty object.
	 * @param {Function} [callback=null] - Optional. The callback to execute after construction. Defaults to null.
	 */
	registerModule( routes, Constructor, options = {}, callback = null ) {
		const moduleData = {
			Constructor,
			options,
			callback
		};

		const routesArray = Array.isArray( routes ) ? routes : [ routes ];

		routesArray.forEach( ( route ) => _this.registry.registerModuleForRoute( moduleData, route ) );
	}
}

export default Controller;
