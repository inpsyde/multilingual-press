/**
 * The MultilingualPress nav menu item model.
 */
class NavMenuItem extends Backbone.Model {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		this.urlRoot = options.urlRoot;
	}
}

export default NavMenuItem;
