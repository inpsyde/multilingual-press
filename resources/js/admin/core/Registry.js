// Internal pseudo-namespace for private data.
const _this = {
	/**
	 * The registry data (i.e., module-per-route).
	 * @type {Object}
	 */
	data: {},

	/**
	 * The module instances registered for the current admin page.
	 * @type {Object}
	 */
	modules: {}
};

/**
 * The MultilingualPress Registry module.
 */
class Registry {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Router} router - The router object.
	 */
	constructor( router ) {
		/**
		 * The router object.
		 * @type {Router}
		 */
		_this.router = router;
	}

	/**
	 * Creates and stores the module instance for the given module data.
	 * @param {Object} data - The module data.
	 */
	createModule( data ) {
		const Constructor = data.Constructor,
			module = new Constructor( data.options );

		_this.modules[ Constructor.name ] = module;

		if ( 'function' === typeof data.callback ) {
			data.callback( module );
		}
	}

	/**
	 * Creates and stores the module instances for the given modules data.
	 * @param {Object[]} modules - The modules data.
	 */
	createModules( modules ) {
		for ( let route in modules ) {
			if ( modules.hasOwnProperty( route ) ) {
				this.createModule( modules[ route ] );
			}
		}
	}

	/**
	 * Initializes the given route.
	 * @param {string} route - The route.
	 * @param {Object[]} modules - The modules data.
	 */
	initializeRoute( route, modules ) {
		_this.router.route( route, route, () => this.createModules( modules ) );
	}

	/**
	 * Sets up all routes with the according registered modules.
	 * @returns {Object} The module instances registered for the current admin page.
	 */
	initializeRoutes() {
		for ( let route in _this.data ) {
			if ( _this.data.hasOwnProperty( route ) ) {
				this.initializeRoute( route, _this.data[ route ] );
			}
		}

		return _this.modules;
	}

	/**
	 * Registers the module with the given data for the given route.
	 * @param {Object} module - The module data.
	 * @param {string} route - The route.
	 * @returns {number} The number of the currently registered routes.
	 */
	registerModuleForRoute( module, route ) {
		if ( ! _this.data[ route ] ) {
			_this.data[ route ] = [];
		}

		return _this.data[ route ].push( module );
	}
}

export default Registry;
