/* global MultilingualPress */
(function() {
	'use strict';

	/**
	 * Settings for the MultilingualPress UserBackendLanguage module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'UserBackendLanguage' );

	/**
	 * Constructor for the MultilingualPress UserBackendLanguage module.
	 * @constructor
	 */
	var UserBackendLanguage = Backbone.View.extend( {
		el: '#WPLANG',

		/**
		 * Initializes the UserBackendLanguage module.
		 */
		initialize: function() {
			this.$el.val( moduleSettings.locale );
		}
	} );

	// Register the UserBackendLanguage module for the General Settings admin page.
	MultilingualPress.registerModule( 'options-general.php', 'UserBackendLanguage', UserBackendLanguage );
})();
