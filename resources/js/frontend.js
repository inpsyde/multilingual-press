'use strict';

import Quicklinks from './frontend/quicklinks/Quicklinks';

/**
 * The MultilingualPress front end namespace.
 * @namespace
 * @alias MultilingualPress
 */
const MLP = window.MultilingualPress = {};

const quicklinks = new Quicklinks( '#mlp-quicklink-form' );
/**
 * The MultilingualPress Quicklinks instance.
 * @type {Quicklinks}
 */
MLP.quicklinks = quicklinks;

// Initialize the Quicklinks module.
quicklinks.initialize();
