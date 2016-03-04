(function() {
	'use strict';

	/**
	 * Settings for the MultilingualPress UserBackendLanguage module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'UserBackendLanguage' );

	var UserBackendLanguage = Backbone.View.extend( /** @lends UserBackendLanguage.prototype */ {
		/**
		 * @type {string}
		 */
		el: '#WPLANG',

		/**
		 * Initializes the UserBackendLanguage module.
		 *
		 * @classdesc Constructor for the MultilingualPress UserBackendLanguage module.
		 * @extends Backbone.View
		 * @constructs
		 * @name UserBackendLanguage
		 */
		initialize: function() {
			this.$el.val( moduleSettings.locale );
		}
	} );

	// Register the UserBackendLanguage module for the General Settings admin page.
	MultilingualPress.registerModule( 'options-general.php', 'UserBackendLanguage', UserBackendLanguage );
})();
