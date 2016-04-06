/**
 * The MultilingualPress model module.
 */
class Model extends Backbone.Model {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		this.urlRoot = options.urlRoot;
	}
}

export default Model;
