import Settings from '../core/Settings';

/**
 * MultilingualPress UserBackEndLanguage module.
 */
class UserBackEndLanguage extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}]  - Optional. The module settings. Defaults to what the Settings module returns.
	 * @param {Object} [moduleSettings=null] - Optional. The module settings. Defaults to what the Settings returns.
	 */
	constructor( options = {}, moduleSettings = null ) {
		super( options );

		this.moduleSettings = moduleSettings || Settings.get( this );
	}

	/**
	 * Initializes the module.
	 */
	initialize() {
		this.$el.val( this.moduleSettings.locale );
	}
}

export default UserBackEndLanguage;
