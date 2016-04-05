'use strict';

import Quicklinks from './frontend/quicklinks/Quicklinks';

/**
 * The MultilingualPress front end namespace object.
 * @namespace
 * @alias MultilingualPress
 */
const MLP = {};

/**
 * The MultilingualPress Quicklinks instance.
 * @type {Quicklinks}
 */
MLP.quicklinks = new Quicklinks( '#mlp-quicklink-form' );
MLP.quicklinks.initialize();

// Externalize the MultilingualPress namespace object.
window.MultilingualPress = MLP;
