const $ = window.jQuery;

// Internal pseudo-namespace for private data.
// NOTE: _this is shared between ALL instances of this module! So far, there is only one instance, so no problem NOW.
const _this = {};

/**
 * The MultilingualPress NavMenus module.
 */
class NavMenus extends Backbone.View {
	/**
	 * Constructor. Sets up the properties.
	 * @param {Object} [options={}] - Optional. The constructor options. Defaults to an empty object.
	 */
	constructor( options = {} ) {
		super( options );

		/**
		 * The jQuery object representing the MultilingualPress language checkboxes.
		 * @type {jQuery}
		 */
		_this.$languages = this.$el.find( 'li [type="checkbox"]' );

		/**
		 * The jQuery object representing the input element that contains the currently edited menu's ID.
		 * @type {jQuery}
		 */
		_this.$menu = $( '#menu' );

		/**
		 * The jQuery object representing the currently edited menu.
		 * @type {jQuery}
		 */
		_this.$menuToEdit = $( '#menu-to-edit' );

		/**
		 * The jQuery object representing the Languages meta box spinner.
		 * @type {jQuery}
		 */
		_this.$spinner = this.$el.find( '.spinner' );

		/**
		 * The jQuery object representing the Languages meta box submit button.
		 * @type {jQuery}
		 */
		_this.$submit = this.$el.find( '#submit-mlp-language' );

		/**
		 * The settings.
		 * @type {Object}
		 */
		_this.settings = options.settings;

		this.listenTo( this.model, 'change', this.render );
	}

	/**
	 * Returns the settings.
	 * @returns {Object} The settings.
	 */
	get settings() {
		return _this.settings;
	}

	/**
	 * Requests the according markup for the checked languages in the Languages meta box.
	 * @param {Event} event - The click event of the submit button.
	 */
	sendRequest( event ) {
		const data = {
			action: this.settings.action,
			menu: _this.$menu.val(),
			mlp_sites: this.getSiteIDs()
		};
		data[ this.settings.nonceName ] = this.settings.nonce;

		event.preventDefault();

		_this.$submit.prop( 'disabled', true );

		_this.$spinner.addClass( 'is-active' );

		this.model.fetch( {
			data,
			processData: true
		} );
	}

	/**
	 * Returns the site IDs for the checked languages in the Languages meta box.
	 * @returns {Number[]} The site IDs.
	 */
	getSiteIDs() {
		const ids = [];

		_this.$languages.filter( ':checked' ).each( ( i, element ) => ids.push( Number( $( element ).val() || 0 ) ) );

		return ids;
	}

	/**
	 * Renders the nav menu item to the currently edited menu.
	 * @returns {Boolean} Whether or not the nav menu item was rendered.
	 */
	render() {
		const success = this.model.get( 'success' );

		if ( success ) {
			_this.$menuToEdit.append( this.model.get( 'data' ) );
		}

		_this.$languages.prop( 'checked', false );

		_this.$spinner.removeClass( 'is-active' );

		_this.$submit.prop( 'disabled', false );

		return success;
	}
}

export default NavMenus;
