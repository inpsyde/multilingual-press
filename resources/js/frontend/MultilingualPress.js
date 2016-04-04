/**
 * The MultilingualPress front end namespace object.
 * @namespace
 */
const MultilingualPress = {
	/**
	 * Redirects the user to the given URL.
	 * @param {string} url - The URL.
	 */
	setLocation: function( url ) {
		window.location.href = url;
	}
};

export default MultilingualPress;
