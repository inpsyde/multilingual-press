/**
 * The MultilingualPress front end namespace object.
 * @namespace
 */
var MultilingualPress = {};

/**
 * Redirects the user to the given URL.
 * @param {string} url - The URL.
 */
MultilingualPress.setLocation = function( url ) {
	'use strict';

	window.location.href = url;
};

window.$ = window.jQuery;

window.module = window.module || {};
