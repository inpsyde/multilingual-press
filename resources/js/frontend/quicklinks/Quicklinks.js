// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {};

/**
 * The MultilingualPress Quicklinks module.
 */
class Quicklinks {
	/**
	 * Constructor. Sets up the properties.
	 * @param {String} selector - The form element selector.
	 * @param {Object} Util - The set of utility methods.
	 */
	constructor( selector, Util ) {
		/**
		 * The form element selector.
		 * @type {String}
		 */
		_this.selector = selector;

		/**
		 * The set of utility methods.
		 * @type {Object}
		 */
		_this.Util = Util;
	}

	/**
	 * Returns the form element selector.
	 * @returns {String} The form element selector.
	 */
	get selector() {
		return _this.selector;
	}

	/**
	 * Initializes the module.
	 */
	initialize() {
		this.attachSubmitHandler();
	}

	/**
	 * Attaches the according handler to the form submit event.
	 * @returns {Boolean} Whether or not the event handler has been attached.
	 */
	attachSubmitHandler() {
		const $form = document.querySelector( this.selector );

		if ( ! $form ) {
			return false;
		}

		_this.Util.addEventListener( $form, 'submit', this.submitForm );

		return true;
	}

	/**
	 * Triggers a redirect on form submission.
	 * @param {Event} event - The submit event of the form.
	 */
	submitForm( event ) {
		const $select = event.target.querySelector( 'select' );

		if ( ! $select ) {
			return;
		}

		event.preventDefault();

		_this.Util.setLocation( $select.value );
	}
}

export default Quicklinks;
