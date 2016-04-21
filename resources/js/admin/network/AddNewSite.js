import $ from 'jquery';
import Backbone from 'backbone';
Backbone.$ = $;
const { _ } = window;

/**
 * MultilingualPress AddNewSite module.
 */
class AddNewSite extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * As of WordPress 4.5.0, there is now an appropriate action hook on the Add New Site network admin page.
		 * Due to our BC policy, we have to wait for WordPress 4.5.0 + 2 in order to make use of it, though.
		 * TODO: Remove the following (and adapt the according PHP parts) with the release of WordPress 4.5.0 + 2.
		 */
		const markup = $( '#mlp-add-new-site-template' ).html() || '';
		if ( '' !== markup ) {
			/**
			 * The templating function.
			 * @type {Function}
			 */
			this.template = _.template( markup );

			// FIRST render the template, THEN set up the properties using elements that just got injected into the DOM.
			this.$el.find( '.submit' ).before( this.template() );
		}

		/**
		 * The jQuery object representing the MultilingualPress language select.
		 * @type {jQuery}
		 */
		this.$language = $( '#mlp-site-language' );

		/**
		 * The jQuery object representing the table row that contains the plugin activation checkbox.
		 * @type {jQuery}
		 */
		this.$pluginsRow = $( '#mlp-activate-plugins' ).closest( 'tr' );
	}

	/**
	 * Sets MultilingualPress's language select to the currently selected site language.
	 * @param {Event} event - The change event of the site language select element.
	 * @returns {boolean} Whether or not the languages has been adapted.
	 */
	adaptLanguage( event ) {
		const language = this.getLanguage( $( event.target ) );

		if ( this.$language.find( '[value="' + language + '"]' ).length ) {
			this.$language.val( language );
		}
	}

	/**
	 * Returns the selected language of the given select element.
	 * @param {HTMLElement} $select - A select element.
	 * @returns {string} The selected language.
	 */
	getLanguage( $select ) {
		const language = $select.val();

		if ( language ) {
			return language.replace( '_', '-' );
		}

		return 'en-US';
	}

	/**
	 * Toggles the Plugins row according to the source site ID select element's value.
	 * @param {Event} event - The change event of the source site ID select element.
	 */
	togglePluginsRow( event ) {
		this.$pluginsRow.toggle( 0 < $( event.target ).val() );
	}
}

export default AddNewSite;
