import * as Util from "../../common/utils";

/**
 * The MultilingualPress Quicklinks module.
 */
class Quicklinks {
	/**
	 * Constructor. Sets up the properties.
	 * @param {string} selector - The form element selector.
	 * @param {Object} [util=null] - Optional. The set of utility methods. Defaults to MultilingualPress's Util object.
	 */
	constructor( selector, util = null ) {
		/**
		 * The form element selector.
		 * @type {string}
		 */
		this.selector = selector;

		/**
		 * The set of utility methods.
		 * @type {Object}
		 */
		this.Util = util || Util;
	}

	/**
	 * Initializes the module.
	 */
	initialize() {
		this.attachSubmitHandler();
	}

	/**
	 * Attaches the according handler to the form submit event.
	 * @returns {boolean} - Whether or not the event handler has been attached.
	 */
	attachSubmitHandler() {
		var $form = document.querySelector( this.selector );
		if ( null === $form ) {
			return false;
		}

		this.Util.addEventListener( $form, 'submit', this.submitForm.bind( this ) );

		return true;
	}

	/**
	 * Triggers a redirect on form submission.
	 * @param {Event} event - The submit event of the form.
	 * @returns {boolean} - Whether or not redirect has been triggered.
	 */
	submitForm( event ) {
		var $select = event.target.querySelector( 'select' );
		if ( null === $select ) {
			return false;
		}

		event.preventDefault();

		this.Util.setLocation( $select.value );

		// For testing only.
		return true;
	}
}

export default Quicklinks;
