/**
 * Returns the name of the given thing.
 * @param {Function|string|object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {string} The name of the module.
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
 * Returns the settings object for the given module or settings name.
 * @param {Function|string|object} module - The instance or constructor or name of a MulitilingualPress module.
 * @returns {Object} The settings object.
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
