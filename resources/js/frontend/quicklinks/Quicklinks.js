import * as Util from "../../common/utils";

// Internal pseudo-namespace for private data.
const _this = {
	Util
};

/**
 * The MultilingualPress Quicklinks module.
 */
class Quicklinks {
	/**
	 * Constructor. Sets up the properties.
	 * @param {string} selector - The form element selector.
	 * @param {Object} [Util=null] - Optional. The set of utility methods. Defaults to null.
	 */
	constructor( selector, Util ) {
		_this.selector = selector;

		Util && ( _this.Util = Util );
	}

	/**
	 * Returns the form element selector.
	 * @returns {string} The form element selector.
	 */
	get selector() {
		return _this.selector || '';
	}

	/**
	 * Initializes the module.
	 */
	initialize() {
		this.attachSubmitHandler();
	}

	/**
	 * Attaches the according handler to the form submit event.
	 * @returns {boolean} Whether or not the event handler has been attached.
	 */
	attachSubmitHandler() {
		const $form = document.querySelector( this.selector );

		if ( null === $form ) {
			return false;
		}

		_this.Util.addEventListener( $form, 'submit', this.submitForm.bind( this ) );

		return true;
	}

	/**
	 * Triggers a redirect on form submission.
	 * @param {Event} event - The submit event of the form.
	 */
	submitForm( event ) {
		const $select = event.target.querySelector( 'select' );

		if ( null === $select ) {
			return;
		}

		event.preventDefault();

		_this.Util.setLocation( $select.value );
	}
}

export default Quicklinks;
