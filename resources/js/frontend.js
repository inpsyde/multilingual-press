(function() {
	'use strict';

	/**
	 * @class MultilingualPress
	 * @classdesc MultilingualPress front-end controller.
	 */
	var MultilingualPress = function() {
		return /** @lends MultilingualPress# */ {
			/**
			 * MultilingualPress module instances.
			 * @type {Object[]}
			 */
			Modules: []
		};
	};

	/**
	 * The MultilingualPress front-end instance.
	 * @type {MultilingualPress}
	 */
	window.MultilingualPress = new MultilingualPress();
})();
