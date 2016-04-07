'use strict';

import * as Util from "./common/utils";
import Quicklinks from './frontend/quicklinks/Quicklinks';

/**
 * The MultilingualPress front end namespace.
 * @namespace
 * @alias MultilingualPress
 */
const MLP = {
	/**
	 * The MultilingualPress Quicklinks instance.
	 * @type {Quicklinks}
	 */
	quicklinks: new Quicklinks( '#mlp-quicklink-form', Util )
};

const { quicklinks } = MLP;

// Initialize the Quicklinks module.
quicklinks.initialize();

// Externalize the MultilingualPress front end namespace.
window.MultilingualPress = MLP;
