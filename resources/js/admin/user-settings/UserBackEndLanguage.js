// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {};

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
		 * The settings.
		 * @type {Object}
		 */
		_this.settings = options.settings;
	}

	/**
	 * Returns the settings.
	 * @returns {Object} The settings.
	 */
	get settings() {
		return _this.settings;
	}

	/**
	 * Sets the Site Language value to what it should be.
	 */
	updateSiteLanguage() {
		this.$el.val( this.settings.locale );
	}
}

export default UserBackEndLanguage;
