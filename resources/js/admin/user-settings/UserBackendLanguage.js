(function( MultilingualPress ) {
	'use strict';

	/**
	 * Settings for the MultilingualPress UserBackendLanguage module. Only available on the targeted admin pages.
	 * @type {Object}
	 */
	var moduleSettings = MultilingualPress.getSettings( 'UserBackendLanguage' );

	var UserBackendLanguage = Backbone.View.extend( /** @lends UserBackendLanguage# */ {
		/**
		 * @constructs UserBackendLanguage
		 * @classdesc MultilingualPress UserBackendLanguage module.
		 * @extends Backbone.View
		 */
		initialize: function() {
			this.$el.val( moduleSettings.locale );
		}
	} );

	// Register the UserBackendLanguage module for the General Settings admin page.
	MultilingualPress.registerModule( 'options-general.php', 'UserBackendLanguage', UserBackendLanguage, {
		el: '#WPLANG'
	} );
})( window.MultilingualPress );
