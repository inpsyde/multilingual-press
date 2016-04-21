import Backbone from 'backbone';

/**
 * The MultilingualPress Model module.
 */
class Model extends Backbone.Model {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The URL root.
		 * @type {string}
		 */
		this.urlRoot = options.urlRoot;
	}
}

export default Model;
