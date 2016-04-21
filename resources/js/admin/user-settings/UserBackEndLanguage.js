import $ from 'jquery';
import Backbone from 'backbone';
Backbone.$ = $;

/**
 * MultilingualPress UserBackEndLanguage module.
 */
class UserBackEndLanguage extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The module settings.
		 * @type {Object}
		 */
		this.moduleSettings = options.moduleSettings;
	}

	/**
	 * Sets the Site Language value to what it should be.
	 */
	updateSiteLanguage() {
		this.$el.val( this.moduleSettings.locale );
	}
}

export default UserBackEndLanguage;
