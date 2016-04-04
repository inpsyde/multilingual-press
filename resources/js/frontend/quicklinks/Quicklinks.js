/**
 * The MultilingualPress Quicklinks module.
 */
class Quicklinks {
	/**
	 * Constructor. Sets up the properties.
	 * @param {string} [selector] - The form element selector.
	 */
	constructor( selector ) {
		/**
		 * The form element selector.
		 * @type {string}
		 */
		this.selector = selector || '';
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
		var $form = $( this.selector );
		if ( ! $form.length ) {
			return false;
		}

		$form.on( 'submit', this.submitForm );

		return true;
	}

	/**
	 * Triggers a redirect on form submission.
	 * @param {Event} event - The submit event of the form.
	 * @returns {boolean} - Whether or not redirect has been triggered.
	 */
	submitForm( event ) {
		var $select = $( event.target ).find( 'select' );
		if ( ! $select.length ) {
			return false;
		}

		event.preventDefault();

		window.MultilingualPress.setLocation( $select.val() );

		// For testing only.
		return true;
	}
}

export default Quicklinks;
