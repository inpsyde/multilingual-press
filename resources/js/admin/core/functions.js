/**
 * Returns the name of the given module.
 * @param {Function|String|Object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {String} The name of the module.
 */
const getModuleName = ( module ) => {
	switch ( typeof module ) {
		case 'function':
			return module.name;

		case 'string':
			return module;

		case 'object':
			return module.constructor.name;
	}

	return '';
};

/**
 * Returns the settings for the given module or settings name.
 * @param {Function|String|Object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {Object} The settings.
 */
export const getSettings = ( module ) => {
	module = getModuleName( module );

	if ( 'undefined' !== typeof window[ 'mlp' + module + 'Settings' ] ) {
		return window[ 'mlp' + module + 'Settings' ];
	}

	if ( 'undefined' !== typeof window[ module ] ) {
		return window[ module ];
	}

	return {};
};
