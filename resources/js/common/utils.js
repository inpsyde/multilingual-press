/**
 * Attaches the given listener to the given DOM element for the event with the given type.
 * @param {Element} $element - The DOM element.
 * @param {string} type - The type of the event.
 * @param {Function} listener - The event listener callback.
 */
export const addEventListener = ( $element, type, listener ) => {
	if ( $element.addEventListener ) {
		$element.addEventListener( type, listener );
	} else {
		$element.attachEvent( 'on' + type, () => {
			listener.call( $element );
		} );
	}
};

/**
 * Redirects the user to the given URL.
 * @param {string} url - The URL.
 */
export const setLocation = ( url ) => {
	window.location.href = url;
};
