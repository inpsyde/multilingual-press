'use strict';

// Externalize the jQuery alias.
window.$ = window.jQuery;

import MultilingualPress from './frontend/MultilingualPress';

import Quicklinks from './frontend/quicklinks/Quicklinks';

/**
 * The MultilingualPress Quicklinks instance.
 * @type {Quicklinks}
 */
MultilingualPress.quicklinks = new Quicklinks( '#mlp-quicklink-form' );
MultilingualPress.quicklinks.initialize();

// Externalize the MultilingualPress namespace object.
window.MultilingualPress = MultilingualPress;
