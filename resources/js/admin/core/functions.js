/**
 * Returns the settings object for the given module or settings name.
 * @param {Object|string} module - The instance of a MulitilingualPress module or the name of the settings object.
 * @returns {Object} The settings object.
 */
export const getSettings = module => {
	if ( 'object' === typeof module ) {
		module = module.constructor.name;
	}

	if ( 'undefined' !== typeof window[ 'mlp' + module + 'Settings' ] ) {
		return window[ 'mlp' + module + 'Settings' ];
	}

	if ( 'undefined' !== typeof window[ module ] ) {
		return window[ module ];
	}

	return {};
};
