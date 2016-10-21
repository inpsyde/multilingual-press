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
	const moduleName = getModuleName( module );

	if ( 'undefined' !== typeof window[ `mlp${moduleName}Settings` ] ) {
		return window[ `mlp${moduleName}Settings` ];
	}

	if ( 'undefined' !== typeof window[ moduleName ] ) {
		return window[ moduleName ];
	}

	return {};
};
